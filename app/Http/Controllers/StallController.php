<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\ParkingRecordActionEnum;
use App\Events\ParkingActionRecorded;
use App\Http\Resources\OrderViewResource;
use App\Jobs\CreateOrderJob;
use App\Mail\PaymentSucceeded;
use App\Models\Order;
use App\Models\ParkingSpace;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StallController extends Controller
{
    public function index() {
        $user_reserved_stalls = Auth::user()->parking_spaces;

        // Converting the "available" field to a real boolean instead of a number type
        foreach ($user_reserved_stalls as $space) {
            $space['available'] = (boolean) $space['available'];
        }

        // Refactoring the collection of user's reserved parking spaces into smaller collections of 5 elements per row
        $user_reserved_stalls = $user_reserved_stalls->chunk(5);

        return Inertia::render('stall/Index', [
            'reserved_stalls' => $user_reserved_stalls,
        ]);
    }

    public function checkout(Request $request) {
        $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
        ]);

        // Creating a stripe session
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $user_reserved_stalls = null;

        try {
            // If the user wants to pay for a specific reserved parking stall
            if ($request['id'] !== null) {
                // Get the specific reserved parking stall (get it as a collection)
                $user_reserved_stalls = ParkingSpace::where('id', $request['id'])->where('user_id', Auth::id())->get();
            } else {
                // If the user wants to pay for all the reserved stalls
                $user_reserved_stalls = Auth::user()->parking_spaces;
            }

            // If the reserved stall(s) was/were not found in the database (e.g. the stall id was invalid)
            if (!$user_reserved_stalls->count()) {
                throw new NotFoundHttpException;
            }
        } catch (\Exception $e) {
            Log::error('Error occurred at checkout');
            Log::error($e);
            throw new NotFoundHttpException;
        }

        $user_registration_plates = $user_reserved_stalls->select('registration_plates')->toJson(); // Selecting registered cars' registration plates

        // If an unpaid checkout session is present in the db for the user's registration plates
        if ( Order::where('status', OrderStatusEnum::OPEN)->where('registration_plates', $user_registration_plates)->exists() ) {
            // Getting the session id of the unpaid checkout
            $checkout_session_id = Order::where('status', OrderStatusEnum::OPEN)->where('registration_plates', $user_registration_plates)->first()->session_id;
            $checkout_session = $stripe->checkout->sessions->retrieve($checkout_session_id); // Retrieving the session based on the session id

            // If the session is still open on stripe (the session has not expired) then redirect the user to the session
            if ($checkout_session['status'] === OrderStatusEnum::OPEN->value) {
                return redirect($checkout_session->url);
            };
        }

        // create a new checkout session if there is no open checkout for the user's registration plates
        $line_items = [];
        $total_price = 0;

        // List of items (parking stall places) for which the user should pay
        foreach ($user_reserved_stalls as $stall) {
            $tariff = '';
            $time_passed = Carbon::parse($stall['drive_in'])->diffInSeconds(now()); // Car's parked time in seconds

            if ($time_passed <= 3_600 || ($time_passed > 10_800)) {
                $tariff = 'ONE_HOUR';
            } else if ($time_passed > 3_600 && $time_passed <= 7_200) {
                $tariff = 'TWO_HOURS';
            } else if ($time_passed > 7_200 && $time_passed <= 10_800) {
                $tariff = 'THREE_HOURS';
            }

            $total_price += env('PARKING_STALL_PRICE_UP_TO_' . $tariff);
            $line_items[] = [
                'price_data' => [
                    'currency' => env('STRIPE_CURRENCY'),
                    'product_data' => [
                        'name' => $stall['registration_plates'],
                    ],
                    'unit_amount' => env('PARKING_STALL_PRICE_UP_TO_' . $tariff) * 100,
                ],
                'quantity' => 1,
            ];
        }

        // Creating a Stripe customer
        $customer = $stripe->customers->create();

        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'customer' => $customer->id,
            'customer_update' => [
                'name' => 'auto'
            ],
            'success_url' => route('stall.checkout.success', [], true)."?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => route('stall.checkout.cancel', [], true)."?session_id={CHECKOUT_SESSION_ID}",
        ]);

        // Creating a new order for the current checkout order unto the database via a job (queue)
        CreateOrderJob::dispatch($user_registration_plates, $total_price, $checkout_session->id, Auth::id());

        return redirect($checkout_session->url);
    }

    public function success(Request $request, StripeCheckoutService $service) {
        // Calling the service method that will update the order record and return customer and order information
        $order =  $service->summarizeCheckout($request->get('session_id'));

        return Inertia::render('stall/CheckoutSuccess', [
            'order' => new OrderViewResource($order), // passing an order resource class as a prop
        ]);
    }

    public function cancel(Request $request) {
        return Inertia::render('stall/CheckoutCancel', []);
    }

    public function webhook(Request $request) {
        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );

            Log::info("===================");
            Log::info("===================");
            Log::info($event->type);
            Log::info($event);

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;

                    try {
                        $session_payment_intent_id = $session->payment_intent; // Getting the payment id from the session
                        $payment_intent = $stripe->paymentIntents->retrieve($session_payment_intent_id, []); // Retrieving a specific payment intent by id

                        $charge_id = $payment_intent->latest_charge; // Getting the charge id object from the payment intent
                        $charge = $stripe->charges->retrieve($charge_id, []); // Retrieving a specific charge object by id

                        // Getting the user's order which was paid but has an unpaid status
                        $order = Order::where('session_id', $session->id)->where('status', OrderStatusEnum::OPEN)->first();
                        if (!$order) {
                            Log::error("The order was not found in the db");
                            throw new NotFoundHttpException;
                        }

                        $order['status'] = OrderStatusEnum::COMPLETE; // Updating the status to 'complete'
                        $order['receipt_url'] = $charge->receipt_url; // Storing the receipt url for the customer
                        $order['customer_email'] = $session->customer_details->email; // Storing customer email
                        $order['customer_name'] = $session->customer_details->name; // Storing customer name
                        $order->save();

                        $user = $order->user; // Getting the user reference from the order model relation

                        // Getting the registration plates from the order
                        $order_registration_plates = collect(json_decode($order->registration_plates))->pluck('registration_plates');
                        // Getting the paid parking spaces in a query based on the registration plates
                        $order_parking_spaces = ParkingSpace::whereIn('registration_plates', $order_registration_plates);

                        // Firing an event for each reserved stall that will create a record in the database about the freed stall by a user (when they drive out)
                        $order_parking_spaces->get()->each(function ($parking_space) use ($user) {
                            event(new ParkingActionRecorded(
                                $user->id,
                                $parking_space->id,
                                ParkingRecordActionEnum::DRIVE_OUT,
                                $parking_space->registration_plates,
                            ));
                        });

                        // Freeing the user's reserved parking stalls after the successful payment
                        $order_parking_spaces->update([
                            'available' => true,
                            'registration_plates' => null,
                            'user_id' => null,
                            'drive_in' => null,
                        ]);

                        // If the user doesn't have any reserved parking spaces but has irrelevant pending checkout sessions, delete them
                        if (!$user->parking_spaces()->exists()) {
                            Order::where('status', OrderStatusEnum::OPEN)->where('user_id', $user->id)->delete();
                        }

                        // Sending the receipt to the email of the user in case if they didn't receive it from stripe
                        Mail::to($user->email)->queue(
                            new PaymentSucceeded($order)
                        );
                    } catch (\Exception $e) {
                        Log::error('Webhook processing failed at checkout.session.completed', [
                            'session' => $session,
                            'error' => $e->getMessage(),
                        ]);
                    }
                default:
                    echo 'Received unknown event type ' . $event->type;
            }
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('', 400);
        }

        return response('Checkout succeeded.', 200);
    }

}
