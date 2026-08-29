@extends('emails.layouts.email-myango')

@section('title', 'Payment Confirmed - MyanGo Travel')

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        We are pleased to inform you that your payment receipt has been verified and approved. Your booking is now fully paid and confirmed! We look forward to welcoming you on your upcoming travel adventure.
    </p>
    
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
            <div class="summary-label">Total Paid</div>
            <div class="summary-value">USD {{ number_format($booking->total_price, 2) }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Booking Status</div>
            <div class="summary-value">
                <span class="status-badge status-paid">Paid</span>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <p>If you have any further questions or require assistance, please contact our support team.</p>
    <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
@endsection
