<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Hotel;
use Illuminate\Http\Request;

class TourController extends Controller
{
    
    // Show all tours
    public function index(Request $request)
    {
        $query = Tour::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $tours = $query->latest()->paginate(10)->withQueryString();

        return view('admin.tours.index', compact('tours'));
    }

    // Show create form
    public function create()
    {
        $hotels = Hotel::all();
        $existingLocations = Hotel::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();
        return view('admin.tours.create', compact('hotels', 'existingLocations'));
    }

    // Save new tour
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'status'        => 'nullable|in:active,inactive',
            'location'      => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $norm = preg_replace('/\s+/', ' ', trim($value));
                    $exists = Hotel::whereRaw('LOWER(location) = ?', [strtolower($norm)])->exists();
                    if (!$exists) {
                        $fail('No existing location found. Please create a hotel with this location first.');
                    }
                }
            ],
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'hotels'        => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    $norm = preg_replace('/\s+/', ' ', trim($request->location));
                    $invalidHotels = Hotel::whereIn('id', $value ?? [])
                        ->whereRaw('LOWER(location) != ?', [strtolower($norm)])
                        ->pluck('name');
                    if ($invalidHotels->isNotEmpty()) {
                        $fail('Hotels from another location cannot be attached to a Tour.');
                    }
                }
            ]
        ]);

        $normalized = preg_replace('/\s+/', ' ', trim($request->location));
        $existingHotel = Hotel::whereRaw('LOWER(location) = ?', [strtolower($normalized)])->first();
        $location = $existingHotel ? $existingHotel->location : $normalized;

        // Create tour
        $tour = Tour::create([
        'title'               => $request->title,
        'title_mm'            => $request->title_mm,
        'description_en'      => $request->description_en,
        'description_mm'      => $request->description_mm,
        'additional_info_en'  => $request->additional_info_en,
        'additional_info_mm'  => $request->additional_info_mm,
        'duration_days'       => $request->duration_days,
        'base_price'          => $request->base_price ?? 0,
        'location'            => $location,
        'status'              => $request->status ?? 'active',
    ]);

        // Attach hotels
        if ($request->has('hotels')) {
            $tour->hotels()->sync($request->hotels);
        }

        // Save itineraries
        if ($request->has('itineraries')) {
            foreach ($request->itineraries as $index => $itinerary) {
                if (!empty($itinerary['description_en'])) {
                    $tour->itineraries()->create([
                        'day_number'      => $index + 1,
                        'title'           => $itinerary['title'] ?? null,
                        'title_mm'        => $itinerary['title_mm'] ?? null,
                        'description_en'  => $itinerary['description_en'],
                        'description_mm'  => $itinerary['description_mm'] ?? null,
                    ]);
                }
            }
        }

        // Save images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('tours', 'public');
                $tour->images()->create([
                    'image_path' => $path,
                    'order'      => $index,
                ]);
            }
        }

        return redirect()->route('admin.tours.travel-periods.index', $tour)
    ->with('success', 'Tour created successfully! Now add available dates.');
    }
    
    // Show edit form
    public function edit(Tour $tour)
    {
        $hotels = Hotel::all();
        $selectedHotels = $tour->hotels->pluck('id')->toArray();
        $itineraries = $tour->itineraries;
        $existingLocations = Hotel::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();
        return view('admin.tours.edit', compact('tour', 'hotels', 'selectedHotels', 'itineraries', 'existingLocations'));
    }

    // Update tour
    public function update(Request $request, Tour $tour)
{ 
    $request->validate([
        'title'         => 'required|string|max:255',
        'duration_days' => 'required|integer|min:1',
        'status'        => 'nullable|in:active,inactive',
        'location'      => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                $norm = preg_replace('/\s+/', ' ', trim($value));
                $exists = Hotel::whereRaw('LOWER(location) = ?', [strtolower($norm)])->exists();
                if (!$exists) {
                    $fail('No existing location found. Please create a hotel with this location first.');
                }
            }
        ],
        'base_price'    => 'required|numeric|min:0',
        'images.*'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'hotels'        => [
            'nullable',
            'array',
            function ($attribute, $value, $fail) use ($request) {
                $norm = preg_replace('/\s+/', ' ', trim($request->location));
                $invalidHotels = Hotel::whereIn('id', $value ?? [])
                    ->whereRaw('LOWER(location) != ?', [strtolower($norm)])
                    ->pluck('name');
                if ($invalidHotels->isNotEmpty()) {
                    $fail('Hotels from another location cannot be attached to a Tour.');
                }
            }
        ]
    ]);

    $normalized = preg_replace('/\s+/', ' ', trim($request->location));
    $existingHotel = Hotel::whereRaw('LOWER(location) = ?', [strtolower($normalized)])->first();
    $location = $existingHotel ? $existingHotel->location : $normalized;

    // Update tour
    $tour->update([
    'title' => $request->title,
    'title_mm' => $request->title_mm,
    'description_en' => $request->description_en,
    'description_mm' => $request->description_mm,
    'additional_info_en' => $request->additional_info_en,
    'additional_info_mm' => $request->additional_info_mm,
    'duration_days' => $request->duration_days,
    'base_price' => $request->base_price ?? 0,
    'location' => $location,
    'status' => $request->status ?? $tour->status,
]);

    // Sync hotels
    if ($request->has('hotels')) {
        $tour->hotels()->sync($request->hotels);
    } else {
        $tour->hotels()->detach();
    }

    // Update itineraries
    $tour->itineraries()->delete();
    if ($request->has('itineraries')) {
        foreach ($request->itineraries as $index => $itinerary) {
            if (!empty($itinerary['description_en'])) {
                $tour->itineraries()->create([
                    'day_number'     => $index + 1,
                    'title'          => $itinerary['title'] ?? null,
                    'title_mm'       => $itinerary['title_mm'] ?? null,
                    'description_en' => $itinerary['description_en'],
                    'description_mm' => $itinerary['description_mm'] ?? null,
                ]);
            }
        }
    }

    // Save new images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('tours', 'public');
            $tour->images()->create([
                'image_path' => $path,
                'order'      => $tour->images()->count() + $index,
            ]);
        }
    }

    return redirect()->route('admin.tours.index')
        ->with('success', 'Tour updated successfully!');
}

    // Delete tour
    public function destroy(Tour $tour)
    {
        $tour->hotels()->detach();
        $tour->delete();

        return redirect()->route('admin.tours.index')
            ->with('success', 'Tour deleted successfully!');
    }
}