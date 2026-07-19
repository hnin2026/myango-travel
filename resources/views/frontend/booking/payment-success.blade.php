@extends('frontend.layouts.app')

@section('title', 'Payment Receipt Uploaded - MyanGo Travel')

@section('content')
<div class="booking-success-page">
    <div class="container">

        <div class="success-wrapper">

            <div class="success-icon" style="background: #28a745;">
                ✓
            </div>

            <h1 class="success-title">
                Payment Receipt Uploaded Successfully
            </h1>

            <p class="success-subtitle">
                Thank you! We have received your payment receipt.
                Our team will verify the payment and update your booking status shortly.
            </p>

            <div class="success-actions" style="margin-top: 40px;">
                <a href="/" class="success-home-btn">
                    Back to Home
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
