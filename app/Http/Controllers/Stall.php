<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Mail\PaymentSucceeded;
use App\Models\Order;
use App\Models\ParkingSpace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Stall extends Controller
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
            'id' => ['nullable', 'integer']
        ]);

        // Creating a stripe session
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $user_reserved_stalls = null;

        // If the user wants to pay for a specific reserved parking stall
        if ($request['id'] !== null) {
            $user_reserved_stalls = ParkingSpace::where('id', $request['id'])->get(); // Get the specific reserved parking stall (as a collection)
        } else {
            // If the user wants to pay for all the reserved stalls
            $user_reserved_stalls = Auth::user()->parking_spaces;
        }

        $user_registration_plates = $user_reserved_stalls->select('registration_plates')->toJson(); // Selecting registered cars' registration plates
        /*$user_orders = Auth::user()->orders->where('status', OrderStatus::UNPAID); // Getting user's unpaid orders
        $unpaid_pending_order = $user_orders->where('registration_plates', $user_registration_plates)->first(); // Selecting the latest existing pending order

        // If the order exists and is valid (the number of registered cars are the same as before)
        if ($unpaid_pending_order) {
            // Extracting registration plates from the array of js objects from the user's order
            $plates = array();
            foreach (json_decode($unpaid_pending_order['registration_plates']) as $registration_plate) {
                $plates[] = $registration_plate->registration_plates;
            }

            // Extracting the drive_in records from the table based on registration plates into an array
            $reserved_stalls_drive_in = ParkingSpace::whereIn('registration_plates', $plates)->pluck('drive_in')->toArray();
            $new_total_price = 0;

            // Calculating the new total price based on the drive in time of each parked car in the stall
            array_map(function ($drive_in) use (&$new_total_price) {
                $time_passed = Carbon::parse($drive_in)->diffInSeconds(now()); // Car's parked time in seconds

                if ($time_passed <= 3_600 || ($time_passed > 10_800)) {
                    $new_total_price += env('PARKING_STALL_PRICE_UP_TO_ONE_HOUR');
                } else if ($time_passed > 3_600 && $time_passed <= 7_200) {
                    $new_total_price += env('PARKING_STALL_PRICE_UP_TO_TWO_HOURS');
                } else if ($time_passed > 7_200 && $time_passed <= 10_800) {
                    $new_total_price += env('PARKING_STALL_PRICE_UP_TO_THREE_HOURS');
                }
            }, $reserved_stalls_drive_in);



            $unpaid_pending_order['total_price'] = $new_total_price;
            $unpaid_pending_order->save();

            $session = $stripe->checkout->sessions->retrieve($unpaid_pending_order['session_id']);

            try {
                // If the session doesn't exist or has expired the throw an exception
                if (!$session || $session->status === OrderStatus::EXPIRED) {
                    throw new NotFoundHttpException();
                }

                $session->amount_total = $new_total_price * 100; // If it exists set the new total price
            } catch (\Exception $e) {
                throw new NotFoundHttpException;
            }

            return redirect( $session->url );
        }*/

        // If an unpaid pending order doesn't exist then create a new checkout session
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
            'cancel_url' => route('stall.checkout.cancel', [], true),
        ]);

        // Creating a new order for the current checkout order unto the database
        $order = new Order();
        $order['status'] = OrderStatus::UNPAID;
        $order['registration_plates'] = $user_registration_plates;
        $order['total_price'] = $total_price;
        $order['user_id'] = Auth::id();
        $order['session_id'] = $checkout_session->id;
        $order->save();

        return redirect($checkout_session->url);
    }

    public function success(Request $request) {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        $session_id = $request->get('session_id');

        try {
            $session = $stripe->checkout->sessions->retrieve($session_id);

            if (!$session) {
                throw new NotFoundHttpException;
            }

            // Getting the order which was not displayed on the success page for the user's payment summary
            $order = Order::where('session_id', $session['id'])->where('payment_summarized', false)->first();
            if (!$order) {
                throw new NotFoundHttpException; // If the summary was displayed by the user then return an exception
            }
            $order['payment_summarized'] = true;
            $order->save();

        } catch (\Exception $e) {
            throw new NotFoundHttpException;
        }

        $customer = $stripe->customers->retrieve($session->customer);

        return Inertia::render('stall/CheckoutSuccess', [
            'customer' => $customer->toArray(),
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
                        $order = Order::where('session_id', $session->id)->where('status', OrderStatus::UNPAID)->first();
                        if (!$order) {
                            throw new NotFoundHttpException;
                        }
                        $order['status'] = OrderStatus::PAID; // Updating the status to true
                        $order['receipt_url'] = $charge->receipt_url; // Storing the receipt url for the customer
                        $order['customer_email'] = $session->customer_details->email; // Storing customer email
                        $order['customer_name'] = $session->customer_details->name; // Storing customer name
                        $order->save();

                        // Extracting registration plates from the array of js objects from the user's order
                        $plates = array();
                        foreach (json_decode($order['registration_plates']) as $registration_plate) {
                            $plates[] = $registration_plate->registration_plates;
                        }

                        // Freeing the user's reserved parking stalls after the successful payment (avoiding n+1 problem by using whereIn instead of where inside a loop)
                        ParkingSpace::whereIn('registration_plates', $plates)->update([
                            'available' => true,
                            'registration_plates' => null,
                            'user_id' => null,
                            'drive_in' => null,
                        ]);

                        // If the user doesn't have any reserved parking spaces but has irrelevant pending checkout sessions, delete them
                        $user = $order->user;
                        if (!$user->parking_spaces->count()) {
                            Order::where('status', OrderStatus::UNPAID)->where('user_id', $user->id)->delete();
                        }

                        // Sending the receipt to the email of the user in case if they didn't receive it from stripe
                        Mail::to($user->email)->send(
                            new \App\Mail\PaymentSucceeded($order)
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
