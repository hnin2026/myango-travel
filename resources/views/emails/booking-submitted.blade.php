@extends('emails.layouts.email-myango')

@section('title', 'Booking Request Received - MyanGo Travel')

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        Your booking request has been received successfully. Our staff will review your booking and contact you soon.
    </p>
    
    <div class="summary-card">
        <div class="summary-title">Booking Details</div>
        
        <div class="summary-row">
            <div class="summary-label">Reference Code</div>
            <div class="summary-value">{{ $booking->ref_code }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Tour</div>
            <div class="summary-value">{{ $booking->tour?->title }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Travel Date</div>
            <div class="summary-value">{{ $booking->checkin_date }} &rarr; {{ $booking->checkout_date }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Total Price</div>
            <div class="summary-value">${{ number_format($booking->total_price, 2) }}</div>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ url('/booking/cancel/' . $booking->cancellation_token) }}" class="btn-secondary" target="_blank">
            Cancel Booking
        </a>
    </div>

    <p class="intro" style="margin-top: 25px;">
        Thank you for choosing MyanGo Travel.
    </p>
@endsection