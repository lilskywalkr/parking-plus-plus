<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OrderViewResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'session_id' => $this->session_id,
            'payment_summarized' => $this->payment_summarized,
            'created_at' => Carbon::parse($this->created_at)->getPreciseTimestamp(3),
            'registration_plates' => collect(json_decode($this->registration_plates))->pluck('registration_plates')
        ];
    }
}
