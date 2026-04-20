<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StripeCheckoutService
{
    protected \Stripe\StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public function summarizeCheckout(string $session_id) {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($session_id);

            // If the session of the provided id does not exist throw not found error
            if (!$session) {
                Log::error("Stripe checkout session not found");
                throw new NotFoundHttpException;
            }

            // Getting the order for the user's payment summary
            $order = Order::where('session_id', $session['id'])->where('user_id', Auth::id())->first();

            // If the order of the given session id does not exist
            if (!$order) {
                Log::error("Order not found");
                throw new NotFoundHttpException;
            }

            // If the summary was displayed by the user
            if ($order['payment_summarized']) {
                Log::error("The summary has been already displayed");
                throw new NotFoundHttpException;
            }

            // If the order status is open mark it as complete
            if ($order['status'] === OrderStatusEnum::OPEN->value) {
                $order['status'] = OrderStatusEnum::COMPLETE->value;
            }

            $order['payment_summarized'] = true;
            $order->save();

            return $order;
        } catch (\Exception $e) {
            Log::error("Error occurred when summarizing the payment");
            Log::error($e);

            throw new NotFoundHttpException;
        }
    }
}
