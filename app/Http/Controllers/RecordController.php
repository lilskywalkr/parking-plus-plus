<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecordController extends Controller
{
    public function index() {
        $records = Record::latest()
            ->with('user')
            ->simplePaginate(15)
            ->withQueryString();

        return Inertia::render('record/Index', [
            'records' => $records
        ]);
    }

    public function search(Request $request) {
        // Validating the search string
        $attributes = $request->validate([
            'q' => ['required', 'string', 'max:255', 'min:1'],
        ]);

        $q = $attributes['q'];

        // Eager loading the users and joining the records table with users and searching for records that match the query string
        $records = Record::query()->latest()->select('records.*')
            ->with('user')
            ->join('users', 'records.user_id', '=', 'users.id')
            ->where('users.name', 'LIKE', "%$q%")
            ->orWhere('records.registration_plates', 'LIKE', "%$q%")
            ->orWhere('records.parking_space_id', 'LIKE', "%$q%")
            ->simplePaginate(15)
            ->withQueryString();


        return Inertia::render('record/Index', [
            'records' => $records
        ]);
    }
}
