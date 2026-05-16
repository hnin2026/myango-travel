<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailableDate;
use App\Models\Tour;
use App\Models\SeasonPeriod;
use Illuminate\Http\Request;

class AvailableDateController extends Controller
{
    public function index(Tour $tour)
    {
        $dates = $tour->availableDates()->latest()->get();

        return view('admin.tours.dates.index', compact('tour', 'dates'));
    }

    public function create(Tour $tour)
    {
        $seasonPeriods = \App\Models\SeasonPeriod::all();
        return view('admin.tours.dates.create', compact('tour', 'seasonPeriods'));
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

        $tour->availableDates()->create([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_seats' => $validated['total_seats'],
            'booked_seats' => 0,
        ]);

        return redirect()->route('admin.tours.dates.index', $tour)
            ->with('success', 'Date added successfully! Season: ' . ucfirst($season));
    }

    public function edit(Tour $tour, AvailableDate $date)
    {
        return view('admin.tours.dates.edit', compact('tour', 'date'));
    }

    public function update(Request $request, Tour $tour, AvailableDate $date)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_seats' => 'required|integer|min:1',
        ]);

        $date->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_seats' => $validated['total_seats'],
        ]);

        return redirect()->route('admin.tours.dates.index', $tour)
            ->with('success', 'Date updated successfully!');
    }

    public function destroy(Tour $tour, AvailableDate $date)
    {
        $date->delete();

        return redirect()->route('admin.tours.dates.index', $tour)
            ->with('success', 'Date deleted successfully!');
    }

    
}