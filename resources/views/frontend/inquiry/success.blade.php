@extends('frontend.layouts.app')

@section('content')
<div class="booking-success-page">
    <div class="container">

        <div class="success-wrapper">

            <div class="success-icon" style="background: #111844; color: #ffffff;">
                ✓
            </div>

            <h1 class="success-title">
                Inquiry Submitted!
            </h1>

            <p class="success-subtitle" style="margin-bottom: 24px;">
                Your inquiry has been submitted successfully.<br>
                Our team will contact you by email. Please keep your inquiry reference for future communication.
            </p>

            <div class="booking-success-card" style="background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px;">

                <div class="success-card-header" style="background: #111844; padding: 18px 24px; color: #ffffff; font-weight: 600; font-size: 16px;">
                    Inquiry Details
                </div>

                <div style="padding: 24px;">
                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                        <span style="color: #6b7280;">Reference Code</span>
                        <strong style="color: #111844; font-family: monospace; font-size: 16px;">{{ $inquiry->reference }}</strong>
                    </div>

                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                        <span style="color: #6b7280;">Name</span>
                        <strong style="color: #111844;">{{ $inquiry->customer_name }}</strong>
                    </div>

                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                        <span style="color: #6b7280;">Email</span>
                        <strong style="color: #111844;">{{ $inquiry->email }}</strong>
                    </div>

                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                        <span style="color: #6b7280;">Tour / Request</span>
                        <strong style="color: #111844;">{{ $inquiry->tour->title ?? 'Custom Tour' }}</strong>
                    </div>

                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                        <span style="color: #6b7280;">Travel Date</span>
                        <strong style="color: #111844;">
                            @if($inquiry->checkin_date)
                                {{ \Carbon\Carbon::parse($inquiry->checkin_date)->format('d M Y') }}
                                @if($inquiry->checkout_date)
                                    → {{ \Carbon\Carbon::parse($inquiry->checkout_date)->format('d M Y') }}
                                @endif
                            @else
                                Flexible
                            @endif
                        </strong>
                    </div>

                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                        <span style="color: #6b7280;">Guests</span>
                        <strong style="color: #111844;">
                            {{ $inquiry->number_of_adults }} Adult(s)
                            @if($inquiry->number_of_children > 0)
                                , {{ $inquiry->number_of_children }} Child(ren)
                            @endif
                        </strong>
                    </div>

                    <div class="success-detail-row" style="display: flex; justify-content: space-between; padding: 12px 0; font-size: 14px;">
                        <span style="color: #6b7280;">Status</span>
                        <span class="booking-status-badge" style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            {{ $inquiry->status_label }}
                        </span>
                    </div>
                </div>

            </div>

            <div class="success-actions" style="display: flex; gap: 15px; justify-content: center;">
                <a href="/" class="success-home-btn" style="background: #111844; color: #ffffff !important; text-decoration: none; padding: 12px 24px; font-weight: 600; border-radius: 12px; transition: 0.2s;">
                    Back to Home
                </a>

                @if($inquiry->tour_id)
                <a href="{{ route('tours.show', $inquiry->tour_id) }}" class="success-tour-btn" style="background: transparent; color: #111844 !important; border: 1.5px solid #111844; text-decoration: none; padding: 12px 24px; font-weight: 600; border-radius: 12px; transition: 0.2s;">
                    View Tour
                </a>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection
