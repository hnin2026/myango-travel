@extends('emails.layouts.email-myango')

@section('title', 'New Inquiry Received - MyanGo Travel')

@section('content')
    <p class="greeting">New Inquiry Received</p>
    <p class="intro">
        A new customer inquiry has been submitted with the following details:
    </p>
    
    <div class="summary-card">
        <div class="summary-title">Inquiry Details</div>
        
        <div class="summary-row">
            <div class="summary-label">Reference</div>
            <div class="summary-value">{{ $inquiry->reference }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Customer</div>
            <div class="summary-value">{{ $inquiry->customer_name }}</div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Email</div>
            <div class="summary-value">
                <a href="mailto:{{ $inquiry->email }}" style="color: #111844; text-decoration: underline;">{{ $inquiry->email }}</a>
            </div>
        </div>
        
        @if($inquiry->phone)
        <div class="summary-row">
            <div class="summary-label">Phone</div>
            <div class="summary-value">{{ $inquiry->phone }}</div>
        </div>
        @endif
        
        <div class="summary-row">
            <div class="summary-label">Tour</div>
            <div class="summary-value">
                {{ $inquiry->tour->title ?? 'General Inquiry / Not specified' }}
            </div>
        </div>
        
        <div class="summary-row">
            <div class="summary-label">Travel Date</div>
            <div class="summary-value">
                @if($inquiry->checkin_date)
                    {{ \Carbon\Carbon::parse($inquiry->checkin_date)->format('d M Y') }}
                    @if($inquiry->checkout_date)
                        &rarr; {{ \Carbon\Carbon::parse($inquiry->checkout_date)->format('d M Y') }}
                    @endif
                @else
                    Flexible
                @endif
            </div>
        </div>
        
        @if($inquiry->number_of_adults || $inquiry->number_of_children)
        <div class="summary-row">
            <div class="summary-label">Guests</div>
            <div class="summary-value">
                {{ $inquiry->number_of_adults }} Adult(s)
                @if($inquiry->number_of_children > 0)
                    , {{ $inquiry->number_of_children }} Child(ren)
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="message-section">
        <div class="section-title">Message:</div>
        <div class="message-card">{{ $inquiry->message }}</div>
    </div>

    <p class="intro" style="margin-top: 30px; font-size: 13px; color: #666666;">
        This is an automated notification. To reply to this customer, please log in to the admin panel and use the "Reply via Email" feature.
    </p>
@endsection
