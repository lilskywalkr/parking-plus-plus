<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class Parking extends Controller
{
    public function index() {
        // Retrieving all the parking spaces from the table
        $parking_spaces = ParkingSpace::all();

        // Converting the "available" field to a real boolean instead of a number type
        foreach ($parking_spaces as $space) {
            $space['available'] = (boolean) $space['available'];
        }

        // Breaking the collection of (array of) parking spaces into two-dimensional collection of 5 elements per row array
        $parking_spaces = $parking_spaces->chunk(5);


        // Rendering the view and passing the refactored array of parking spaces as props
        return Inertia::render('parking/Index', [
            'parking_spaces' => $parking_spaces,
        ]);
    }

    public function edit(Request $request) {
        return Inertia::render('parking/Reserve', [
            'stall_id' => $request['stall_id']
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request) {
        $attributes = $request->validate([
            'plate' => ['required', 'string', 'min:3'],
            'stall_id' => ['required', 'integer', 'min:1'],
        ]);

        // Getting all unavailable parking stalls
        $registered_spaces = ParkingSpace::all()->where('available', false);

        // Check if the provided registration plate or the stall id exist in the unavailable parking stalls
        foreach ($registered_spaces as $space) {
            if ($space['id'] == $attributes['stall_id']) {
                throw ValidationException::withMessages([
                    'stall_id' => 'Stall No. '. $attributes['stall_id'] .' is taken',
                ]);
            }

            if ($space['registration_plates'] == $attributes['plate']) {
                throw ValidationException::withMessages([
                    'plate' => 'This plate is already registered.',
                ]);
            }
        }

        // If the provided data by user is valid then reserve the particular parking space
        ParkingSpace::find($attributes['stall_id'])->update([
            'available' => false,
            'user_id' => Auth::id(),
            'registration_plates' => $attributes['plate'],
            'drive_in' => Date::now()
        ]);

        return redirect('/parking');
    }
}
