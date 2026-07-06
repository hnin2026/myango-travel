<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::with([
            'tour',
            'hotel',
            'travelPeriod'
        ])->latest()->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
     
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        $booking->load([
            'tour',
            'hotel',
            'travelPeriod'
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:confirmed,paid,cancelled'
        ]);

        $booking->update([
            'status' => $request->status
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
