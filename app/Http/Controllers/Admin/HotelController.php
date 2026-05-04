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
            'name'             => 'required|string|max:255',
            'category'         => 'required|in:3-star,4-star,5-star',
            'price_per_person' => 'required|numeric|min:0',
            'location'         => 'nullable|string|max:255',
        ]);

        Hotel::create($request->all());

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel created successfully!');
    }

    // Show edit form
    public function edit(Hotel $hotel)
    {
        return view('admin.hotels.edit', compact('hotel'));
    }

    // Update hotel
    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'category'         => 'required|in:3-star,4-star,5-star',
            'price_per_person' => 'required|numeric|min:0',
            'location'         => 'nullable|string|max:255',
        ]);

        $hotel->update($request->all());

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