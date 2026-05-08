<?php

namespace App\Listeners;

use App\Enums\ParkingRecordActionEnum;
use App\Events\ParkingActionRecorded;
use App\Models\Record;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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

        try {
            Log::info('CreateRecordListener listens to the event ParkingActionRecorded');

            Record::create([
                'user_id' => $user_id,
                'parking_space_id' => $parking_space_id,
                'action' => $action,
                'registration_plates' => $registration_plates
            ]);

            Log::info('CreateRecordListener finished listening to the event ParkingActionRecorded');
        } catch (\Exception $e) {
            Log::error("Error occurred at recording the action in CreateRecordListener");
            Log::error($e);
        }
    }
}
