<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    // Show all hotels
    public function index(Request $request)
    {
        $query = Hotel::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $hotels = $query->latest()->paginate(10)->withQueryString();
        return view('admin.hotels.index', compact('hotels'));
    }

    // Show create form
    public function create()
    {
        $existingLocations = Hotel::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();

        return view('admin.hotels.create', compact('existingLocations'));
    }

    // Save new hotel
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:3-star,4-star,5-star',
            'location'     => 'required|string|max:255',
            'price_low'    => 'required|numeric|min:0',
            'price_normal' => 'required|numeric|min:0',
            'price_peak'   => 'required|numeric|min:0',
        ]);

        $location = Hotel::normalizeLocation($request->location);

        // Create hotel
        $hotel = Hotel::create([
            'name'     => $request->name,
            'category' => $request->category,
            'location' => $location,
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
        $existingLocations = Hotel::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();

        return view('admin.hotels.edit', compact('hotel', 'seasonPrices', 'existingLocations'));
    }

    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:3-star,4-star,5-star',
            'location'     => 'required|string|max:255',
            'price_low'    => 'required|numeric|min:0',
            'price_normal' => 'required|numeric|min:0',
            'price_peak'   => 'required|numeric|min:0',
        ]);

        $location = Hotel::normalizeLocation($request->location);

        $hotel->update([
            'name'     => $request->name,
            'category' => $request->category,
            'location' => $location,
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

    // Get hotels by location in JSON format
    public function byLocation(Request $request)
    {
        $location = $request->query('location');
        if (!$location) {
            return response()->json([]);
        }

        $normalized = preg_replace('/\s+/', ' ', trim($location));

        $hotels = Hotel::with('seasonPrices')
            ->whereRaw('LOWER(location) = ?', [strtolower($normalized)])
            ->get()
            ->map(function ($hotel) {
                return [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'category' => $hotel->category,
                    'stars_count' => intval($hotel->category[0]),
                    'price_low' => $hotel->getPriceForSeason('low'),
                    'price_normal' => $hotel->getPriceForSeason('normal'),
                    'price_peak' => $hotel->getPriceForSeason('peak'),
                ];
            })
            ->sortBy([
                ['category', 'desc'],
                ['name', 'asc']
            ])
            ->values();

        return response()->json($hotels);
    }
}