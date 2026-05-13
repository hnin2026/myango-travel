<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    // Show all hotels
    public function index()
    {
        $hotels = Hotel::all();
        return view('admin.hotels.index', compact('hotels'));
    }

    // Show create form
    public function create()
    {
        return view('admin.hotels.create');
    }

    // Save new hotel
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:3-star,4-star,5-star',
            'location'     => 'nullable|string|max:255',
            'price_low'    => 'required|numeric|min:0',
            'price_normal' => 'required|numeric|min:0',
            'price_peak'   => 'required|numeric|min:0',
        ]);

        // Create hotel
        $hotel = Hotel::create([
            'name'     => $request->name,
            'category' => $request->category,
            'location' => $request->location,
        ]);

        // Save season prices
        $hotel->seasonPrices()->createMany([
            ['season' => 'low',    'upgrade_price' => $request->price_low],
            ['season' => 'normal', 'upgrade_price' => $request->price_normal],
            ['season' => 'peak',   'upgrade_price' => $request->price_peak],
        ]);

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel created successfully!');
    }

    //Edit and  Update hotel
    public function edit(Hotel $hotel)
    {
        $seasonPrices = $hotel->seasonPrices->keyBy('season');
        return view('admin.hotels.edit', compact('hotel', 'seasonPrices'));
    }

    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:3-star,4-star,5-star',
            'location'     => 'nullable|string|max:255',
            'price_low'    => 'required|numeric|min:0',
            'price_normal' => 'required|numeric|min:0',
            'price_peak'   => 'required|numeric|min:0',
        ]);

        $hotel->update([
            'name'     => $request->name,
            'category' => $request->category,
            'location' => $request->location,
        ]);

        // Update season prices
        foreach (['low', 'normal', 'peak'] as $season) {
            $hotel->seasonPrices()->updateOrCreate(
                ['season' => $season],
                ['upgrade_price' => $request->{"price_{$season}"}]
            );
        }

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel updated successfully!');
    }

    // Delete hotel
    public function destroy(Hotel $hotel)
    {
        $hotel->delete();

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel deleted successfully!');
    }
}