<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecordResource;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RecordController extends Controller
{
    public function index() {
        $records = Record::latest()
            ->with('user:id,name')
            ->simplePaginate(15)
            ->withQueryString();

        return Inertia::render('record/Index', [
            'records' => RecordResource::collection($records),
        ]);
    }

    public function filter(Request $request) {
        // Validating the search string and sorting option
        $attributes = $request->validate([
            'q' => ['nullable', 'string', 'max:255', 'min:1'],
            'option' => ['nullable', Rule::in(['date', 'time', 'user', 'parking_space_id', 'registration_plates']), 'string'],
            'direction' => ['nullable', 'required_with:option', Rule::in([-1, 1]), 'integer'],
        ]);

        // Inner joining the records and users tables
        $records = Record::query()->select('records.*')
            ->with('user:id,name')
            ->join('users', 'records.user_id', '=', 'users.id');

        // If the user passed a search query then look for records that match with the query
        if ( isset($attributes['q']) ) {
            $q = $attributes['q'];

            $records = $records
                ->where('users.name', 'LIKE', "%$q%")
                ->orWhere('records.registration_plates', 'LIKE', "%$q%")
                ->orWhere('records.parking_space_id', 'LIKE', "%$q%");
        }

        // If the user passed a sorting option with the direction then sort it
        if ( isset($attributes['option']) ) {
            $option = $attributes['option'];
            $direction = $attributes['direction'];

            if ($option === 'user') {
                $option = 'users.name';
            } else if ($option === 'date' || $option === 'time') {
                $option = 'created_at';
            }

            $records = $records
                ->orderBy($option, $direction > 0 ? 'asc' : 'desc');
        }

        // Paginating the records
        $records = $records
            ->simplePaginate(15)
            ->withQueryString();

        return Inertia::render('record/Index', [
            'records' => RecordResource::collection($records),
        ]);
    }
}
