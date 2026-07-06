@vite('resources/css/frontend/booking-success.css')

@extends('frontend.layouts.app')

@section('content')
<div class="booking-success-page">
    <div class="container">

        <div class="success-wrapper">

            <div class="success-icon">
                ✓
            </div>

            <h1 class="success-title">
                Booking Successful!
            </h1>

            <p class="success-subtitle">
                Your booking has been received successfully.
                Our team will contact you soon.
            </p>

            <div class="booking-success-card">

                <div class="success-card-header">
                    Booking Details
                </div>

                <div class="success-detail-row">
                    <span>Reference Code</span>
                    <strong>{{ $booking->ref_code }}</strong>
                </div>

                <div class="success-detail-row">
                    <span>Name</span>
                    <strong>{{ $booking->customer_name }}</strong>
                </div>

                <div class="success-detail-row">
                    <span>Email</span>
                    <strong>{{ $booking->email }}</strong>
                </div>

                <div class="success-detail-row">
                    <span>Travel Date</span>
                    <strong>
                        {{ $booking->checkin_date }}
                        →
                        {{ $booking->checkout_date }}
                    </strong>
                </div>

                <div class="success-detail-row">
                    <span>Total Price</span>
                    <strong>${{ $booking->total_price }}</strong>
                </div>

                <div class="success-detail-row">
                    <span>Status</span>
                    <span class="booking-status-badge">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>

            </div>

            <div class="success-actions">
                <a href="/" class="success-home-btn">
                    Back to Home
                </a>

                <a href="{{ route('tours.show', $booking->tour_id) }}" class="success-tour-btn">
                    View Tour
                </a>
            </div>

        </div>

    </div>
</div>
@endsection