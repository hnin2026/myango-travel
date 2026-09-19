<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Str;
use App\Models\TravelPeriod;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\BookingSubmittedMail;
use App\Mail\PaymentReceiptReceivedMail;


class BookingController extends Controller
{
    public function create(Request $request, Tour $tour)
    {
        if ($tour->status !== 'active') {
            return redirect()->route('tours.show', $tour);
        }
        return view('frontend.booking.create', compact(
            'tour',
            'request'
        ));
    }

    public function store(Request $request, Tour $tour)
    {
        if ($tour->status !== 'active') {
            return back()->withErrors(['tour' => 'This tour is currently unavailable for booking.'])->withInput();
        }

        $checkinDate = $request->input('checkin_date', $request->input('checkin'));
        $checkoutDate = $request->input('checkout_date', $request->input('checkout'));

        $isBlackout = \App\Models\TourBlackoutPeriod::where('tour_id', $tour->id)
            ->where('start_date', '<=', $checkinDate)
            ->where('end_date', '>=', $checkinDate)
            ->exists();

        if ($isBlackout) {
            return back()->withErrors(['checkin_date' => 'The selected check-in date falls within a blackout period and is not bookable.'])->withInput();
        }

        $result = DB::transaction(function () use ($request, $tour, $checkinDate, $checkoutDate) {
            $travelPeriod = TravelPeriod::where('tour_id', $tour->id)
                ->where('start_date', '<=', $checkinDate)
                ->where('end_date', '>=', $checkinDate)
                ->lockForUpdate()
                ->first();

            if (!$travelPeriod) {
                return ['error_key' => 'checkin_date', 'message' => 'No travel period found for selected date.'];
            }

            // Capacity Check using existing adult/child seat-counting rules
            $adults = intval($request->input('adults', $request->input('num_persons', 1)));
            $childAgesInput = $request->input('child_ages', $request->input('ages', ''));
            $requestedSeats = TravelPeriod::calculateRequestedSeats($adults, $childAgesInput);

            $availableSeats = $travelPeriod->availableSeats();

            if ($requestedSeats > $availableSeats) {
                return [
                    'error_key' => 'seats',
                    'message'   => "Insufficient seats available. Only {$availableSeats} seats remain for the selected travel period."
                ];
            }

            // Independent server-side price calculation
            $season = \App\Models\SeasonPeriod::getSeasonForDate($checkinDate);

            $hotelId = $request->input('hotel_id', $request->input('hotel'));
            $hotelUpgradePrice = 0.0;
            if ($hotelId) {
                $hotel = \App\Models\Hotel::find($hotelId);
                if ($hotel) {
                    $hotelUpgradePrice = $hotel->getPriceForSeason($season);
                }
            }

            $pricePerPerson = (float) $tour->base_price + $hotelUpgradePrice;
            $payableTravelers = $requestedSeats;
            $calculatedTotalPrice = $pricePerPerson * $payableTravelers;

            $booking = Booking::create([
                'tour_id'             => $tour->id,
                'travel_period_id'    => $travelPeriod->id,
                'hotel_id'            => $hotelId,
                'customer_name'       => $request->customer_name,
                'nationality'         => $request->nationality,
                'email'               => $request->email,
                'phone'               => $request->phone,
                'num_persons'         => $adults,
                'num_children'        => $request->input('children', $request->input('num_children', 0)),
                'child_ages'          => is_array($childAgesInput) ? implode(',', $childAgesInput) : $childAgesInput,
                'checkin_date'        => $checkinDate,
                'checkout_date'       => $checkoutDate,
                'base_price'          => $tour->base_price,
                'hotel_upgrade_price' => $hotelUpgradePrice,
                'total_price'         => $calculatedTotalPrice,
                'message'             => $request->message,
                'status'              => 'pending',
                'ref_code'            => 'MYG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'cancellation_token'  => Str::uuid(),
                'payment_deadline'    => now()->addDays(7),
            ]);

            return ['booking' => $booking];
        });

        if (isset($result['error_key'])) {
            return back()->withErrors([$result['error_key'] => $result['message']])->withInput();
        }

        $booking = $result['booking'];
        Mail::to($booking->email)->send(new BookingSubmittedMail($booking));

        return redirect()->route('booking.success', ['booking' => $booking->id]);
    }

public function success(Booking $booking)
{
    return view('frontend.booking.success', compact('booking'));
}

public function paymentShow($token)
{
    $booking = Booking::where('cancellation_token', $token)->first();

    if (!$booking) {
        abort(404);
    }

    $booking->load(['tour']);

    return view('frontend.booking.payment', compact('booking'));
}

public function paymentUpload(Request $request, $token)
{
    $booking = Booking::where('cancellation_token', $token)->first();

    if (!$booking) {
        abort(404);
    }

    $request->validate([
        'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    if ($request->hasFile('receipt')) {
        $file = $request->file('receipt');
        $path = $file->store('payment_receipts', 'public');

        $booking->update([
            'payment_receipt' => $path,
            'payment_uploaded_at' => now(),
            'status' => 'confirmed',
        ]);

        $booking->load(['tour']);
        Mail::to($booking->email)->send(new PaymentReceiptReceivedMail($booking));
    }

    return redirect()->route('payment.success');
}

public function paymentSuccess()
{
    return view('frontend.booking.payment-success');
}

public function cancelShow($token)
{
    $booking = Booking::where('cancellation_token', $token)->first();

    if (!$booking) {
        abort(404);
    }

    $booking->load(['tour']);

    return view('frontend.booking.cancel', compact('booking'));
}

public function cancelSubmit(Request $request, $token)
{
    $booking = Booking::where('cancellation_token', $token)->first();

    if (!$booking) {
        abort(404);
    }

    if (!in_array($booking->status, ['pending', 'confirmed']) || $booking->is_payment_uploaded) {
        return redirect()->route('booking.cancel.show', $token);
    }

    $request->validate([
        'cancel_reason' => 'required|string|max:1000',
    ]);

    $booking->update([
        'status' => 'cancelled',
        'cancelled_by' => 'customer',
        'cancel_reason' => $request->cancel_reason,
        'cancelled_at' => now(),
    ]);

    $booking->load(['tour']);
    Mail::to($booking->email)->send(new \App\Mail\BookingCancelledMail($booking));

    return redirect()->route('booking.cancel.success', $token);
}

public function cancelSuccess($token)
{
    $booking = Booking::where('cancellation_token', $token)->first();

    if (!$booking) {
        abort(404);
    }

    if ($booking->status !== 'cancelled') {
        return redirect()->route('booking.cancel.show', $token);
    }

    return view('frontend.booking.cancel-success', compact('booking'));
}
}
