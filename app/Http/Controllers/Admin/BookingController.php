<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentRequiredMail;
use App\Mail\PaymentConfirmedMail;
use App\Mail\PaymentRejectedMail;
use App\Mail\BookingCancelledMail;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::with([
            'tour',
            'hotel',
            'travelPeriod'
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ref_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

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

        $oldStatus = $booking->status;
        $newStatus = $request->status;

        if ($oldStatus === 'cancelled' && in_array($newStatus, ['confirmed', 'paid', 'pending'])) {
            $travelPeriod = $booking->travelPeriod;
            if ($travelPeriod) {
                $requestedSeats = $booking->num_persons;
                if (!empty($booking->child_ages)) {
                    $ages = explode(',', $booking->child_ages);
                    foreach ($ages as $age) {
                        if (is_numeric($age) && intval($age) >= 5) {
                            $requestedSeats++;
                        }
                    }
                }

                $availableSeats = $travelPeriod->total_seats - $travelPeriod->booked_seats;
                if ($requestedSeats > $availableSeats) {
                    return back()->with('error', "Not enough seats available on the travel period. Only {$availableSeats} seats remaining, but this booking requires {$requestedSeats} seats.");
                }
            }
        }

        $booking->update([
            'status' => $request->status
        ]);

        if ($oldStatus === 'pending' && $booking->status === 'confirmed') {
            $booking->load(['tour']);
            Mail::to($booking->email)->send(new PaymentRequiredMail($booking));
        }

        if ($oldStatus === 'payment_uploaded' && $booking->status === 'paid') {
            $booking->load(['tour']);
            Mail::to($booking->email)->send(new PaymentConfirmedMail($booking));
        }

        if ($oldStatus === 'payment_uploaded' && $booking->status === 'confirmed') {
            $booking->load(['tour']);
            Mail::to($booking->email)->send(new PaymentRejectedMail($booking));
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking status updated successfully.');
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('error', 'This booking is already cancelled.');
        }

        $request->validate([
            'cancel_reason' => 'required|string'
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => 'admin',
            'cancel_reason' => $request->cancel_reason,
            'cancelled_at' => now(),
        ]);

        $booking->load(['tour']);
        Mail::to($booking->email)->send(new BookingCancelledMail($booking));

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
