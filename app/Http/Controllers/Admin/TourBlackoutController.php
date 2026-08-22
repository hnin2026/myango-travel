<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourBlackoutPeriod;
use Illuminate\Http\Request;

class TourBlackoutController extends Controller
{
    public function create(Tour $tour)
    {
        return view('admin.tours.blackouts.create', compact('tour'));
    }

    public function store(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $earliest = $tour->travelPeriods()->min('start_date');
        $latest = $tour->travelPeriods()->max('end_date');

        if (!$earliest || !$latest) {
            return back()->withErrors(['start_date' => 'Please add at least one travel period/available date before adding blackout periods.'])->withInput();
        }

        if ($validated['start_date'] < $earliest || $validated['end_date'] > $latest) {
            return back()->withErrors(['start_date' => "Blackout dates must be within the tour operating period ({$earliest} to {$latest})."])->withInput();
        }

        // Overlap Check (Rule 3)
        $exists = TourBlackoutPeriod::where('tour_id', $tour->id)
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['start_date' => 'This blackout period overlaps with an existing blackout period.'])->withInput();
        }

        $tour->blackoutPeriods()->create($validated);

        return redirect()->route('admin.tours.travel-periods.index', $tour)
            ->with('success', 'Blackout period added successfully!');
    }

    public function edit(Tour $tour, TourBlackoutPeriod $blackout)
    {
        return view('admin.tours.blackouts.edit', compact('tour', 'blackout'));
    }

    public function update(Request $request, Tour $tour, TourBlackoutPeriod $blackout)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $earliest = $tour->travelPeriods()->min('start_date');
        $latest = $tour->travelPeriods()->max('end_date');

        if (!$earliest || !$latest) {
            return back()->withErrors(['start_date' => 'Please add at least one travel period/available date before adding blackout periods.'])->withInput();
        }

        if ($validated['start_date'] < $earliest || $validated['end_date'] > $latest) {
            return back()->withErrors(['start_date' => "Blackout dates must be within the tour operating period ({$earliest} to {$latest})."])->withInput();
        }

        // Overlap Check (Rule 3)
        $exists = TourBlackoutPeriod::where('tour_id', $tour->id)
            ->where('id', '!=', $blackout->id)
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['start_date' => 'This blackout period overlaps with an existing blackout period.'])->withInput();
        }

        $blackout->update($validated);

        return redirect()->route('admin.tours.travel-periods.index', $tour)
            ->with('success', 'Blackout period updated successfully!');
    }

    public function destroy(Tour $tour, TourBlackoutPeriod $blackout)
    {
        $blackout->delete();

        return redirect()->route('admin.tours.travel-periods.index', $tour)
            ->with('success', 'Blackout period removed successfully!');
    }
}
