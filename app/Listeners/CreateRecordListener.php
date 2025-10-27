<?php

namespace App\Listeners;

use App\Enums\ParkingRecordActionEnum;
use App\Events\ParkingActionRecorded;
use App\Models\Record;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateRecordListener implements ShouldQueue
{
    use Queueable;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ParkingActionRecorded $event): void
    {
        $user_id = $event->user_id;
        $parking_space_id = $event->parking_space_id;
        $action = $event->action;
        $registration_plates = $event->registration_plates;

        Record::create([
            'user_id' => $user_id,
            'parking_space_id' => $parking_space_id,
            'action' => $action,
            'registration_plates' => $registration_plates
        ]);
    }
}
