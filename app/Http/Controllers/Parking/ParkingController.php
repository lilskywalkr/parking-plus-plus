<?php

namespace App\Http\Controllers\Parking;

use App\Events\ParkingActionRecorded;
use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ParkingController extends Controller
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
        $stall = ParkingSpace::find($request['stall_id']); // Getting the stall of the given id

        // If the stall of the given id does not exist
        if (!$stall) {
            return redirect('/parking');
        }

        // If the request was from an admin
        if (Auth::user()->is_admin) {
            $stall['available'] = (boolean) $stall['available']; // Converting the available field to a boolean type

            // redirect to blocking page and pass parking space model as a prop
            return Inertia::render('parking/Block', [
                'stall' => $stall
            ]);
        }

        // If the request was from a user then redirect to the reserve page
        return Inertia::render('parking/Reserve', [
            'stall_id' => $request['stall_id']
        ]);
    }
}
