<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Inquiry;
use App\Models\SeasonPeriod;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $tours = Tour::where('status', 'active')
            ->with(['images', 'availableDates'])
            ->get();
        return view('frontend.home.index', compact('tours'));
    }

public function show(Tour $tour)
    {
        $tour->load(['images', 'hotels.seasonPrices', 'itineraries', 'availableDates']);
        $seasonPeriods = SeasonPeriod::all();
        return view('frontend.tours.show', compact('tour', 'seasonPeriods'));
    }

    public function inquiry(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'email'         => 'required|email',
            'phone'         => 'nullable|string|max:20',
            'message'       => 'required|string',
        ]);

        Inquiry::create([
            'customer_name' => $request->customer_name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'message'       => $request->message,
            'status'        => 'new',
        ]);

        return back()->with('inquiry_success', 'Your inquiry has been sent successfully! We will contact you soon.');
    }
}