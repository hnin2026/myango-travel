<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Str;
use App\Models\TravelPeriod;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingSubmittedMail;


class BookingController extends Controller
{
    public function create(Request $request, Tour $tour)
    {
        return view('frontend.booking.create', compact(
            'tour',
            'request'
        ));
    }

public function store(Request $request, Tour $tour)
{
    $travelPeriod = TravelPeriod::where('tour_id', $tour->id)
        ->where('start_date', '<=', $request->checkin_date)
        ->where('end_date', '>=', $request->checkin_date)
        ->first();

    if (!$travelPeriod) {
        return back()->with('error', 'No travel period found for selected date.');
    }

    $booking = Booking::create([
    'tour_id' => $tour->id,
    'travel_period_id' => $travelPeriod->id,
    'hotel_id' => $request->hotel_id,
    'customer_name' => $request->customer_name,
    'nationality' => $request->nationality,
    'email' => $request->email,
    'phone' => $request->phone,
    'num_persons' => $request->adults,
    'num_children' => $request->children,
    'child_ages' => $request->child_ages,
    'checkin_date' => $request->checkin_date,
    'checkout_date' => $request->checkout_date,
    'base_price' => $tour->base_price,
    'hotel_upgrade_price' => 0,
    'total_price' => $request->total_price,
    'message' => $request->message,
    'status' => 'pending',
    'ref_code' => 'MYG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
    'cancellation_token' => Str::uuid(),
    'payment_deadline' => now()->addDays(7),
]);
Mail::to($booking->email)->send(new BookingSubmittedMail($booking));
return redirect()->route('booking.success', ['booking' => $booking->id]);
}

public function success(Booking $booking)
{
    return view('frontend.booking.success', compact('booking'));
}
}
