<?php

namespace App\Http\Controllers\Parking;

use App\Enums\ParkingRecordActionEnum;
use App\Events\ParkingActionRecorded;
use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParkingSpaceAvailabilityChangeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request)
    {
        $attributes = $request->validate([
            'stall_id' => ['required', 'integer', 'min:1']
        ]);

        DB::transaction(function () use ($attributes) {
            $space = ParkingSpace::where('id', $attributes['stall_id']) // Getting the stall of the given id
                ->lockForUpdate()
                ->first();

            // If the stall of the provided id does not exist (the query above returns null if so)
            if (!$space) {
                throw ValidationException::withMessages([
                    'stall_id' => 'Stall No. '. $attributes['stall_id'] .' is non-existent.',
                ]);
            }

            // If the stall is reserved by a user throw an exception
            if ($space['user_id']) {
                throw ValidationException::withMessages([
                    'reserved' => 'Cannot block this stall, because it is already reserved.',
                ]);
            }

            $space->update([
                'available' => !$space['available'], // Toggle false to true and vice versa to block/unblock the stall
            ]);

            // Firing the event that will create a record in the database about the blocked stall by an admin
            event(new ParkingActionRecorded(
                Auth::id(),
                $space['id'],
                $space['available'] ? ParkingRecordActionEnum::UNBLOCKED : ParkingRecordActionEnum::BLOCKED,
                null
            ));
        });

        return redirect('/parking');
    }
}
