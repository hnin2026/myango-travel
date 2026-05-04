<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Hotel;
use Illuminate\Http\Request;

class TourController extends Controller
{
    // Show all tours
    public function index()
    {
        $tours = Tour::all();
        return view('admin.tours.index', compact('tours'));
    }

    // Show create form
    public function create()
    {
        $hotels = Hotel::all();
        return view('admin.tours.create', compact('hotels'));
    }

    // Save new tour
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'location'      => 'required|string|max:255',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Create tour
        $tour = Tour::create([
            'title'               => $request->title,
            'title_mm'            => $request->title_mm,
            'description_en'      => $request->description_en,
            'description_mm'      => $request->description_mm,
            'additional_info_en'  => $request->additional_info_en,
            'additional_info_mm'  => $request->additional_info_mm,
            'duration_days'       => $request->duration_days,
            'location'            => $request->location,
            'status'              => 'active',
        ]);

        // Attach hotels
        if ($request->has('hotels')) {
            $tour->hotels()->sync($request->hotels);
        }

        // Save itineraries
        if ($request->has('itineraries')) {
            foreach ($request->itineraries as $index => $itinerary) {
                if (!empty($itinerary['title'])) {
                    $tour->itineraries()->create([
                        'day_number'      => $index + 1,
                        'title'           => $itinerary['title'],
                        'title_mm'        => $itinerary['title_mm'] ?? null,
                        'description_en'  => $itinerary['description_en'] ?? null,
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

        return redirect()->route('admin.tours.index')
            ->with('success', 'Tour created successfully!');
    }
    // Show edit form
    public function edit(Tour $tour)
    {
        $hotels = Hotel::all();
        return view('admin.tours.edit', compact('tour', 'hotels'));
    }

    // Update tour
    public function update(Request $request, Tour $tour)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'location'      => 'required|string|max:255',
            'status'        => 'required|in:active,inactive',
            'thumbnail'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('tours', 'public');
        }

        $tour->update($data);

        // Sync hotels
        if ($request->has('hotels')) {
            $tour->hotels()->sync($request->hotels);
        } else {
            $tour->hotels()->detach();
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