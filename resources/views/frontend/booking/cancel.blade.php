@extends('frontend.layouts.app')

@section('title', 'Cancel Booking - MyanGo Travel')

@section('content')
<div class="booking-cancel-page">
    <div class="container">
        <div class="cancel-wrapper">

            @if($booking->status === 'cancelled')
                <!-- Booking Already Cancelled State -->
                <div class="alert-card">
                    <div class="alert-card-icon info-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <h1 class="alert-card-title">Booking Cancelled</h1>
                    <p class="alert-card-text">This booking has already been cancelled.</p>
                    <div class="mt-4">
                        <a href="{{ route('home') }}" class="back-btn">Home</a>
                    </div>
                </div>

            @elseif($booking->status === 'payment_uploaded' || $booking->status === 'paid')
                <!-- Payment Processing / Already Paid State -->
                <div class="alert-card">
                    <div class="alert-card-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h1 class="alert-card-title">Cancellation Blocked</h1>
                    <p class="alert-card-text">
                        This booking cannot be cancelled online because payment processing has already started. Please contact MyanGo Travel for assistance.
                    </p>

                    <div class="contact-info">
                        <div class="contact-info-title">Contact Support</div>
                        <div class="contact-item">
                            <i class="bi bi-envelope-fill"></i>
                            <span>Email: <strong>info@myango.com</strong></span>
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-telephone-fill"></i>
                            <span>Phone: <strong>+95 9 123 456 789</strong></span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('home') }}" class="back-btn">Home</a>
                    </div>
                </div>

            @else
                <!-- Booking Cancellation Confirmation Form -->
                <div class="cancel-header">
                    <div class="cancel-icon">
                        <i class="bi bi-x-lg"></i>
                    </div>
                    <h1 class="cancel-title">Cancel Booking?</h1>
                    <p class="cancel-subtitle">Please review your booking details before proceeding.</p>
                </div>

                <!-- Booking Details Card -->
                <div class="booking-cancel-card">
                    <div class="cancel-card-header">
                        Booking Details
                    </div>
                    <div class="cancel-detail-row">
                        <span>Booking Reference</span>
                        <strong>{{ $booking->ref_code }}</strong>
                    </div>
                    <div class="cancel-detail-row">
                        <span>Tour</span>
                        <strong>{{ $booking->tour?->title }}</strong>
                    </div>
                    <div class="cancel-detail-row">
                        <span>Travel Date</span>
                        <strong>{{ $booking->checkin_date }} – {{ $booking->checkout_date }}</strong>
                    </div>
                    <div class="cancel-detail-row">
                        <span>Total</span>
                        <strong>USD {{ $booking->total_price }}</strong>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="reason-section">
                    <form action="{{ route('booking.cancel.submit', $booking->cancellation_token) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="cancel_reason" class="reason-label">Cancellation Reason *</label>
                            <textarea 
                                name="cancel_reason" 
                                id="cancel_reason" 
                                class="reason-textarea @error('cancel_reason') is-invalid @enderror" 
                                placeholder="Please explain why you want to cancel your booking..." 
                                required>{{ old('cancel_reason') }}</textarea>
                            @error('cancel_reason')
                                <div class="text-danger mt-1" style="font-size: 14px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="confirm-text">
                            Are you sure you want to cancel this booking?
                        </div>

                        <div class="button-group">
                            <button type="submit" class="cancel-btn">
                                Cancel Booking
                            </button>
                            <a href="{{ route('home') }}" class="back-btn">
                                Back
                            </a>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
