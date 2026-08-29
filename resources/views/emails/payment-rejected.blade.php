@extends('emails.layouts.email-myango')

@section('title', 'Payment Rejected - MyanGo Travel')

@section('styles')
    <style>
        .intro {
            color: #c0392b !important;
            font-weight: 600;
        }
        .message-body {
            font-size: 15px;
            color: #55688a;
            line-height: 1.6;
            margin-bottom: 30px;
        }
    </style>
@endsection

@section('content')
    <p class="greeting">Dear {{ $booking->customer_name }},</p>
    <p class="intro">
        We were unable to verify the payment receipt uploaded for booking {{ $booking->ref_code }}.
    </p>
    <p class="message-body">
        Please ensure that the details match the payment instructions. If you made a bank transfer or mobile banking payment, please check that the transaction reference and receipt clearly show the transfer details. Please use the link below to re-upload a valid payment receipt.
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
            <div class="summary-label">Booking Status</div>
            <div class="summary-value">
                <span class="status-badge status-rejected">Payment Required</span>
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
@endsection

@section('footer')
    <p>If you need assistance, please reply to this email or contact our customer support.</p>
    <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
@endsection
