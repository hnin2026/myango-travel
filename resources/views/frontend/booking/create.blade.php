@extends('frontend.layouts.app')

@section('title', 'Complete Your Booking - MyanGo Travel')

@section('content')
<div class="booking-page">
    <div class="container">
        <div class="booking-layout">

            {{-- LEFT --}}
            <div class="booking-left">
                <div class="booking-summary-card">

                    <img src="{{ asset('storage/' . $tour->images->first()->image_path) }}"
                         alt="{{ $tour->title }}"
                         class="summary-image">

                    <div class="summary-content">

                        <div class="summary-badge">
                            Confirmed Booking
                        </div>

                        <h3 class="summary-title">
                            {{ $tour->title }}
                        </h3>

                        <div class="summary-item">
                            <strong>Check-in</strong>
                            <span>{{ request('checkin') }}</span>
                        </div>

                        <div class="summary-item">
                            <strong>Check-out</strong>
                            <span>{{ request('checkout') }}</span>
                        </div>

                        <div class="summary-item">
                            <strong>Adults</strong>
                            <span>{{ request('adults') }}</span>
                        </div>

                        <div class="summary-item">
                            <strong>Children</strong>
                            <span>{{ request('children') }}</span>
                        </div>

                        @if(request('ages'))
                            <div class="summary-item">
                                <strong>Child Ages</strong>
                                <span>{{ request('ages') }}</span>
                            </div>
                        @endif

                        <div class="summary-total">
                            <div>
                                <div class="summary-total-label">Total Price</div>
                            </div>
                            <div class="summary-total-amount">
                                ${{ request('total') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="booking-right">
                <div class="booking-form-card">

                    {{-- Hero card --}}
                    <div class="confirm-hero-card">
                        <div class="confirm-check">✓</div>
                        <div class="confirm-label">Booking Details</div>

                        <h2 class="booking-form-title">
                            Complete Your Reservation
                        </h2>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger mx-4 mt-3 mb-0" style="border-radius: 12px;">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Customer Info --}}
                    <div class="info-section">
                        <div class="info-section-header">
                            Customer Information
                        </div>

                        <div class="info-section-body">
                            <form action="{{ route('booking.store', $tour) }}" method="POST">
                                @csrf

                                <input type="hidden" name="checkin_date" value="{{ request('checkin') }}">
                                <input type="hidden" name="checkout_date" value="{{ request('checkout') }}">
                                <input type="hidden" name="adults" value="{{ request('adults') }}">
                                <input type="hidden" name="children" value="{{ request('children') }}">
                                <input type="hidden" name="child_ages" value="{{ request('ages') }}">
                                <input type="hidden" name="hotel_id" value="{{ request('hotel') }}">
                                <input type="hidden" name="total_price" value="{{ request('total') }}">

                                <div class="booking-fields">

                                    <div class="booking-field">
                                        <label>Full Name</label>
                                        <input type="text" name="customer_name" required>
                                    </div>

                                    <div class="booking-field">
                                        <label>Nationality</label>
                                        <input type="text" name="nationality">
                                    </div>

                                    <div class="booking-field">
                                        <label>Email</label>
                                        <input type="email" name="email" required>
                                    </div>

                                    <div class="booking-field">
                                        <label>Phone</label>
                                        <input type="text" name="phone" required>
                                    </div>

                                    <div class="booking-field">
                                        <label>Special Request</label>
                                        <textarea name="message"></textarea>
                                    </div>

                                    <div class="booking-field">
                                        <button type="submit" class="confirm-booking-btn">
                                            Confirm Booking
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection