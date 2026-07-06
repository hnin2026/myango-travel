<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelPeriod;
use App\Models\Tour;
use App\Models\SeasonPeriod;
use Illuminate\Http\Request;

class TravelPeriodController extends Controller
{
    public function index(Tour $tour)
    {
        $dates = $tour->travelPeriods()->latest()->get();

        return view('admin.tours.travel-periods.index', compact('tour', 'dates'));
    }

    public function create(Tour $tour)
    {
        $seasonPeriods = \App\Models\SeasonPeriod::all();
        return view('admin.tours.travel-periods.create', compact('tour', 'seasonPeriods'));
    }

    public function store(Request $request, Tour $tour)
    {
         $validated = $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'total_seats' => 'required|integer|min:1',
    ]);

        // Auto detect season
        $season = SeasonPeriod::getSeasonForDate($validated['start_date']);

        $tour->travelPeriods()->create([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_seats' => $validated['total_seats'],
            'booked_seats' => 0,
        ]);

        return redirect()->route('admin.tours.travel-periods.index', $tour)
            ->with('success', 'Date added successfully! Season: ' . ucfirst($season));
    }

    public function edit(Tour $tour, TravelPeriod $travel_period)
    {
        return view('admin.tours.travel-periods.edit', compact('tour', 'travel_period'));
    }

    public function update(Request $request, Tour $tour, TravelPeriod $travel_period)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_seats' => 'required|integer|min:1',
        ]);

        $travel_period->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_seats' => $validated['total_seats'],
        ]);

        return redirect()->route('admin.tours.travel-periods.index', $tour)
            ->with('success', 'Date updated successfully!');
    }

    public function destroy(Tour $tour, TravelPeriod $travel_period)
    {
        $travel_period->delete();

        return redirect()->route('admin.tours.travel-periods.index', $tour)
            ->with('success', 'Date deleted successfully!');
    }

    
}