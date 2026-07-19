@extends('frontend.layouts.app')

@section('title', 'Booking Cancelled Successfully - MyanGo Travel')

@section('content')
<div class="booking-cancel-page">
    <div class="container">
        <div class="cancel-wrapper">

            <div class="alert-card">
                <div class="alert-card-icon" style="color: #27ae60;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                
                <h1 class="alert-card-title">🎉 Booking Cancelled Successfully</h1>
                
                <p class="alert-card-text" style="font-size: 16px; margin-bottom: 25px;">
                    Your booking has been cancelled successfully.
                </p>

                <!-- Booking Info Card inside Success -->
                <div class="booking-cancel-card" style="text-align: left; margin-bottom: 25px;">
                    <div class="cancel-detail-row">
                        <span>Booking Reference</span>
                        <strong>{{ $booking->ref_code }}</strong>
                    </div>
                    <div class="cancel-detail-row">
                        <span>Status</span>
                        <span class="badge bg-danger" style="padding: 6px 14px; font-size: 13px; border-radius: 50px;">Cancelled</span>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('home') }}" class="cancel-btn" style="background: #111844; text-decoration: none; display: block;">
                        Home
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
