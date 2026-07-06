<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\SeasonPeriod;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $tours = Tour::where('status', 'active')
            ->with(['images', 'travelPeriods'])
            ->get();
        return view('frontend.home.index', compact('tours'));
    }

public function show(Tour $tour)
    {
        $tour->load(['images', 'hotels.seasonPrices', 'itineraries', 'travelPeriods']);
        $seasonPeriods = SeasonPeriod::all();
        return view('frontend.tours.show', compact('tour', 'seasonPeriods'));
    }

}