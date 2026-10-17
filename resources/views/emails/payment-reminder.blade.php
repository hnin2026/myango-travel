@extends('emails.layouts.email-myango')

@section('title', 'Payment Reminder - MyanGo Travel')

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        This is a friendly reminder that the payment deadline for your booking (<strong>{{ $booking->ref_code }}</strong>) is approaching in <strong>2 days</strong>. To ensure your seats remain reserved, please complete your payment and upload the transaction receipt before the deadline expires.
    </p>
    
    <div class="summary-card">
        <div class="summary-title">Booking Details & Payment Deadline</div>
        
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
            <div class="summary-label">Payment Deadline</div>
            <div class="summary-value" style="color: #d9534f;">
                @if($booking->payment_deadline instanceof \Carbon\Carbon || $booking->payment_deadline instanceof \DateTime)
                    {{ $booking->payment_deadline->format('F d, Y') }} (2 Days Remaining)
                @else
                    {{ \Carbon\Carbon::parse($booking->payment_deadline)->format('F d, Y') }} (2 Days Remaining)
                @endif
            </div>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ url('/payment/' . $booking->cancellation_token) }}" class="btn-primary" target="_blank">
            Upload Payment Receipt Now
        </a>
        <a href="{{ url('/booking/cancel/' . $booking->cancellation_token) }}" class="btn-secondary" target="_blank">
            Cancel Booking
        </a>
    </div>

    <div class="methods-section">
        <div class="section-title">Payment Methods</div>
        
        <!-- International Customers -->
        <div class="method-card">
            <div class="method-header">International Customers</div>
            <div class="method-details">
                <strong>Bank Transfer (USD)</strong><br>
                Bank Name: <strong>AYA Bank</strong><br>
                Account Name: <strong>MyanGo Travel</strong><br>
                Account Number: <strong>123456789</strong><br>
                SWIFT Code: <strong>AYABMMMY</strong><br>
                Reference: <strong>{{ $booking->ref_code }}</strong>
            </div>
        </div>
        
        <!-- Myanmar Customers -->
        <div class="method-card">
            <div class="method-header">Myanmar Customers</div>
            <div class="method-details">
                <strong>KBZPay</strong> | <strong>WavePay</strong> | <strong>AYA Mobile Banking</strong><br>
                Reference: <strong>{{ $booking->ref_code }}</strong><br>
                Amount: <strong>USD {{ number_format($booking->total_price, 2) }}</strong>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <p>Please note: If payment is not received and confirmed by the deadline, your reservation will be automatically cancelled by the system to release seats to other travelers.</p>
    <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
@endsection
