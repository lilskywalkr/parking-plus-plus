<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index() {
        return Inertia::render('statistics/Index', []);
    }

    public function show(Request $request) {
        $attributes = $request->validate([
            'date' => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $date = $attributes['date'];

        $statistics = ParkingSpace::query()->select("parking_spaces.id")
            // loading a relation and counting the number of records for a specific date and where action is "drive in"
            ->withCount([
                'records as drive_in_count' => function ($query) use ($date) {
                    $query
                        ->where('action', 'drive in')
                        ->where('created_at', 'LIKE', "%$date%");
                },
            ])
            ->simplePaginate(5)
            ->withQueryString();

        return Inertia::render('statistics/Index', [
            'statistics' => $statistics,
        ]);
    }
}
