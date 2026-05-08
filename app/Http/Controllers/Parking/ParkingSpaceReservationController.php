<?php

namespace App\Http\Controllers\Parking;

use App\Enums\ParkingRecordActionEnum;
use App\Events\ParkingActionRecorded;
use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ParkingSpaceReservationController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request)
    {
        $attributes = $request->validate([
            'plate' => ['required', 'string', 'min:3'],
            'stall_id' => ['required', 'integer', 'min:1'],
        ]);

        // Using db transaction to prevent race condition
        DB::transaction(function () use ($attributes) {
            // Getting the stall by id that is available and locking it for updates (this prevents race condition)
            $stall = ParkingSpace::where('id', $attributes['stall_id'])
                ->lockForUpdate()
                ->first();

            // If the stall of the provided id does not exist (the query above returns null if so)
            if (!$stall) {
                throw ValidationException::withMessages([
                    'stall_id' => 'Stall No. '. $attributes['stall_id'] .' is non-existent.',
                ]);
            }

            // If the stall of the provided id is unavailable
            if (!$stall['available']) {
                throw ValidationException::withMessages([
                    'stall_id' => 'Stall No. '. $attributes['stall_id'] .' is unavailable.',
                ]);
            }

            // If the provided registration plates already exists in the parking stalls
            if (ParkingSpace::where('registration_plates', $attributes['plate'])->exists()) {
                throw ValidationException::withMessages([
                    'plate' => 'This plate is already registered.',
                ]);
            }

            // If the provided data by user is valid then reserve the particular parking space
            $stall->update([
                'available' => false,
                'user_id' => Auth::id(),
                'registration_plates' => $attributes['plate'],
                'drive_in' => Date::now()
            ]);

            // Firing the event that will create a record in the database about the blocked stall by an admin
            event(new ParkingActionRecorded(
                Auth::id(),
                $stall['id'],
                ParkingRecordActionEnum::DRIVE_IN,
                $attributes['plate']
            ));
        });

        return redirect('/parking');
    }
}
