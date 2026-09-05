<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeasonPeriod;
use Illuminate\Http\Request;

class SeasonPeriodController extends Controller
{
    public function index()
    {
        $seasons = SeasonPeriod::orderBy('start_date')->get();
        return view('admin.seasons.index', compact('seasons'));
    }

    public function create()
    {
        return view('admin.seasons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'season'     => 'required|in:low,normal,peak',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ]);

        SeasonPeriod::create($request->all());

        return redirect()->route('admin.season-periods.index')
            ->with('success', 'Season period created successfully!');
    }

    public function edit(SeasonPeriod $seasonPeriod)
    {
        return view('admin.seasons.edit', compact('seasonPeriod'));
    }

    public function update(Request $request, SeasonPeriod $seasonPeriod)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'season'     => 'required|in:low,normal,peak',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ]);

        $seasonPeriod->update($request->all());

        return redirect()->route('admin.season-periods.index')
            ->with('success', 'Season period updated successfully!');
    }

    public function destroy(SeasonPeriod $seasonPeriod)
    {
        $seasonPeriod->delete();

        return redirect()->route('admin.season-periods.index')
            ->with('success', 'Season period deleted successfully!');
    }
}