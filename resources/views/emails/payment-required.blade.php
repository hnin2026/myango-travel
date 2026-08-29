@extends('emails.layouts.email-myango')

@section('title', 'Payment Required - MyanGo Travel')

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        Your booking request has been reviewed and is now <strong>Confirmed</strong>! To secure your seats, please complete your payment and upload the transaction receipt using the link below before the payment deadline.
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
            <div class="summary-label">Total Amount</div>
            <div class="summary-value">USD {{ number_format($booking->total_price, 2) }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Payment Deadline</div>
            <div class="summary-value">
                @if($booking->payment_deadline instanceof \Carbon\Carbon || $booking->payment_deadline instanceof \DateTime)
                    {{ $booking->payment_deadline->format('F d, Y') }}
                @else
                    {{ \Carbon\Carbon::parse($booking->payment_deadline)->format('F d, Y') }}
                @endif
            </div>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ url('/payment/' . $booking->cancellation_token) }}" class="btn-primary" target="_blank">
            Upload Payment Receipt
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
    <p>If you have any questions or require assistance, please contact our customer support team.</p>
    <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
@endsection
