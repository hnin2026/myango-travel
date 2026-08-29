@extends('emails.layouts.email-myango')

@section('title')
    @if(($booking->cancelled_by ?? '') === 'customer')
        Booking Cancellation Confirmation - MyanGo Travel
    @else
        Booking Cancellation Notice - MyanGo Travel
    @endif
@endsection

@section('header-style', 'background-color: #c0392b;')

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        @if(($booking->cancelled_by ?? '') === 'customer')
            Your booking has been successfully cancelled.
        @elseif(($booking->cancelled_by ?? '') === 'system')
            Your booking has been cancelled because payment was not received before the payment deadline. If you have already made a payment, please contact MyanGo Travel immediately.
        @else
            We regret to inform you that your booking has been cancelled. Below are the details of the cancelled booking.
        @endif
    </p>
    
    <div class="reason-box">
        <div class="reason-title">Cancellation Reason</div>
        <div>{{ $booking->cancel_reason }}</div>
    </div>

    <div class="summary-card">
        <div class="summary-title">Booking Details</div>
        
        <div class="summary-row">
            <div class="summary-label">Booking Reference</div>
            <div class="summary-value">{{ $booking->ref_code }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Tour Title</div>
            <div class="summary-value">{{ $booking->tour?->title }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Travel Dates</div>
            <div class="summary-value">{{ $booking->checkin_date }} &rarr; {{ $booking->checkout_date }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Total Amount</div>
            <div class="summary-value">USD {{ number_format($booking->total_price, 2) }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Cancellation Status</div>
            <div class="summary-value">{{ ucfirst($booking->status) }}</div>
        </div>
    </div>
@endsection

@section('footer')
    <p>If you have any questions, please contact MyanGo Travel.</p>
    <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
@endsection
