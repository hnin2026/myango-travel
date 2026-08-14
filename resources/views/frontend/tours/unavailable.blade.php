@extends('frontend.layouts.app')

@section('title', 'Tour Unavailable - MyanGo Travel')

@section('content')
<div class="booking-page">
    <div class="success-wrapper text-center">
        <div class="card p-5 border-0 shadow-sm" style="border-radius: 20px; background: white;">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
            </div>
            <h2 class="fw-bold mb-3" style="color: #111844;">This tour is currently unavailable.</h2>
            <p class="text-muted mb-4">The tour package you are trying to view is not currently open for bookings or is inactive.</p>
            <a href="{{ route('home') }}" class="book-now-btn" style="display: inline-block; max-width: 250px; text-decoration: none;">
                <i class="bi bi-compass me-2"></i> View Tours
            </a>
        </div>
    </div>
</div>
@endsection
