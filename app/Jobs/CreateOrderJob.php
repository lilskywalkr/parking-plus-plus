<?php

namespace App\Jobs;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateOrderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $registration_plates,
        public float $total_price,
        public string $session_id,
        public int $user_id
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = new Order();

        $order['status'] = OrderStatusEnum::UNPAID;
        $order['registration_plates'] = $this->registration_plates;
        $order['total_price'] = $this->total_price;
        $order['user_id'] = $this->user_id;
        $order['session_id'] = $this->session_id;

        $order->save();
    }
}
