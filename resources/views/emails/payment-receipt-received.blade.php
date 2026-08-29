@extends('emails.layouts.email-myango')

@section('title', 'Payment Receipt Received - MyanGo Travel')

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        We have successfully received your payment receipt for booking <strong>{{ $booking->ref_code }}</strong>. Our finance department is currently verifying the transaction. Once verified, we will update your booking status and send you a confirmation.
    </p>
    
    <div class="summary-card">
        <div class="summary-title">Booking Summary</div>
        
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
            <div class="summary-label">Current Status</div>
            <div class="summary-value">
                <span class="status-badge status-pending">Receipt Uploaded</span>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <p>If you have any questions or require assistance, please contact our customer support team.</p>
    <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
@endsection
