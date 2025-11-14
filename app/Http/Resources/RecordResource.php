<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class RecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'parking_space_id' => $this->parking_space_id,
            'registration_plates' => $this->registration_plates,
            'date' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'time' => Carbon::parse($this->created_at)->format('H:i:s'),
            'user' => $this->user->name,
        ];
    }
}
