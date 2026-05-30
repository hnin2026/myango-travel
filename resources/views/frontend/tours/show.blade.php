@extends('frontend.layouts.app')

@section('title', $tour->title . ' - MyanGo Travel')

@push('styles')
<style>
    body { padding-top: 0; }
    .content-wrapper { max-width: 1200px; margin: 0 auto; padding: 48px 24px; }
</style>
@endpush

@push('styles')
    @vite([
        'resources/css/frontend/tour-detail.css',
        'resources/css/frontend/responsive.css'
    ])
@endpush

@section('content')

<div class="content-wrapper">
    <div class="row g-5">

        {{-- LEFT SIDE --}}
        <div class="col-lg-8">

            {{-- GALLERY --}}
            <div class="gallery-section">
                @php $images = $tour->images; @endphp

                @if($images->count() > 0)
                    <div class="row g-3">
                        {{-- Main large image --}}
                        <div class="col-md-7">
                            <div class="gallery-main" onclick="openLightbox(0)" style="cursor:pointer;">
                                <img src="{{ asset('storage/' . $images->first()->image_path) }}"
                                     alt="{{ $tour->title }}">
                            </div>
                        </div>

                        {{-- Grid of 4 smaller images --}}
                        <div class="col-md-5">
                            <div class="gallery-grid">
                                @foreach($images->skip(1)->take(4) as $index => $image)
                                    <div class="gallery-grid-item"
                                         onclick="openLightbox({{ $index + 1 }})">
                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                             alt="{{ $tour->title }}">
                                        @if($index === 3 && $images->count() > 5)
                                            <div class="gallery-overlay">
                                                <span>+{{ $images->count() - 5 }} Photos</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Fill empty slots with placeholder --}}
                                @for($i = $images->count() - 1; $i < 4; $i++)
                                    <div class="gallery-grid-item">
                                        <img src="https://placehold.co/300x240/111844/EAE0CF?text=MyanGo"
                                             alt="MyanGo Travel">
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                @else
                    <div class="gallery-main">
                        <img src="https://placehold.co/900x480/111844/EAE0CF?text=MyanGo+Travel"
                             alt="{{ $tour->title }}">
                    </div>
                @endif
            </div>

            {{-- TOUR OVERVIEW --}}
            <div class="mb-5">
                <h1 class="section-heading">
                    <span class="lang-en">{{ $tour->title }}</span>
                    <span class="lang-mm" style="display:none;">
                        {{ $tour->title_mm ?? $tour->title }}
                    </span>
                </h1>
                <div class="d-flex flex-wrap gap-4 mb-4">
                    <span style="color:#6b7280; font-size:15px;">
                        <i class="bi bi-geo-alt me-1" style="color:var(--mid-blue)"></i>
                        {{ $tour->location }}
                    </span>
                    <span style="color:#6b7280; font-size:15px;">
                        <i class="bi bi-clock me-1" style="color:var(--mid-blue)"></i>
                        {{ $tour->duration_days }} Days / {{ $tour->duration_days - 1 }} Nights
                    </span>
                    <span style="color:#6b7280; font-size:15px;">
                        <i class="bi bi-calendar-check me-1" style="color:var(--mid-blue)"></i>
                        {{ $tour->availableDates->count() }} dates available
                    </span>
                </div>

                {{-- Description preview --}}
                <div style="color:#374151; font-size:15px; line-height:1.8;">
                    <div class="lang-en">
                        {!! Str::limit(strip_tags($tour->description_en), 300) !!}
                    </div>
                    <div class="lang-mm" style="display:none;">
                        {!! Str::limit(strip_tags($tour->description_mm ?? $tour->description_en), 300) !!}
                    </div>
                </div>
            </div>

            {{-- MODERN TABS --}}
            <div class="modern-tabs">
                <button class="modern-tab active" onclick="switchTab('description', this)">
                    Description
                </button>
                <button class="modern-tab" onclick="switchTab('schedule', this)">
                    Schedule
                </button>
                <button class="modern-tab" onclick="switchTab('additional', this)">
                    Additional Info
                </button>
            </div>

            {{-- Description Tab --}}
            <div class="tab-panel active" id="tab-description">
                <div class="lang-en" style="color:#374151; line-height:1.9; font-size:15px;">
                    {!! $tour->description_en !!}
                </div>
                <div class="lang-mm" style="display:none; color:#374151; line-height:1.9; font-size:15px;">
                    {!! $tour->description_mm ?? $tour->description_en !!}
                </div>
            </div>

            {{-- Schedule Tab --}}
            <div class="tab-panel" id="tab-schedule">
                <div class="schedule-grid">
                    @forelse($tour->itineraries as $itinerary)
                        <div class="schedule-card">
                            <div class="schedule-day">Day {{ $itinerary->day_number }}</div>
                            <div class="lang-en">
                                <p class="schedule-desc">{{ $itinerary->description_en }}</p>
                            </div>
                            <div class="lang-mm" style="display:none;">
                                <p class="schedule-desc">
                                    {{ $itinerary->description_mm ?? $itinerary->description_en }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p style="color:#6b7280;">Schedule not available yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Additional Info Tab --}}
            <div class="tab-panel" id="tab-additional">
                <div class="lang-en" style="color:#374151; line-height:1.9; font-size:15px;">
                    {!! $tour->additional_info_en !!}
                </div>
                <div class="lang-mm" style="display:none; color:#374151; line-height:1.9; font-size:15px;">
                    {!! $tour->additional_info_mm ?? $tour->additional_info_en !!}
                </div>
            </div>

            {{-- HOTELS SECTION --}}
            <div class="mt-5 mb-5">
                <h2 class="section-heading">Available Hotels</h2>
                <div class="hotel-grid">
                    @foreach($tour->hotels as $hotel)
                        @php
                            $stars = intval($hotel->category[0]);
                        @endphp
                        <div class="hotel-card-new"
                             onclick="selectHotelCard(this, '{{ $hotel->id }}')">
                            <div class="hotel-name">{{ $hotel->name }}</div>
                            <div class="hotel-stars">
                                @for($s = 0; $s < $stars; $s++)★@endfor
                                <span style="color:#9ca3af; margin-left:4px;">
                                    {{ $hotel->category }}
                                </span>
                            </div>
                            @if($hotel->location)
                                <div style="font-size:13px; color:#9ca3af; margin-bottom:12px;">
                                    <i class="bi bi-geo-alt"></i> {{ $hotel->location }}
                                </div>
                            @endif
                            <div class="hotel-price-row">
                                <span class="season-label">
                                    <span style="color:#16a34a;">●</span> Low Season
                                </span>
                                <span class="price">+${{ $hotel->getPriceForSeason('low') }}</span>
                            </div>
                            <div class="hotel-price-row">
                                <span class="season-label">
                                    <span style="color:#d97706;">●</span> Normal Season
                                </span>
                                <span class="price">+${{ $hotel->getPriceForSeason('normal') }}</span>
                            </div>
                            <div class="hotel-price-row">
                                <span class="season-label">
                                    <span style="color:#dc2626;">●</span> Peak Season
                                </span>
                                <span class="price">+${{ $hotel->getPriceForSeason('peak') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- INQUIRY FORM --}}
            <div class="inquiry-section">
                <h2 class="section-heading">Send an Inquiry</h2>
                <p style="color:#6b7280; margin-bottom:32px; font-size:15px;">
                    Have questions about this tour? We'd love to hear from you.
                </p>

                @if(session('inquiry_success'))
                    <div class="alert alert-success mb-4" style="border-radius:12px;">
                        {{ session('inquiry_success') }}
                    </div>
                @endif

                <form action="{{ route('inquiry.store') }}" method="POST" class="inquiry-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label style="font-size:12px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:0.8px; display:block; margin-bottom:6px;">
                                Your Name
                            </label>
                            <input type="text" name="customer_name"
                                   value="{{ old('customer_name') }}"
                                   placeholder="e.g. John Smith"
                                   class="@error('customer_name') is-invalid @enderror">
                            @error('customer_name')
                                <div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label style="font-size:12px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:0.8px; display:block; margin-bottom:6px;">
                                Email
                            </label>
                            <input type="email" name="email"
                                   value="{{ old('email') }}"
                                   placeholder="e.g. john@email.com"
                                   class="@error('email') is-invalid @enderror">
                            @error('email')
                                <div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label style="font-size:12px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:0.8px; display:block; margin-bottom:6px;">
                                Phone
                            </label>
                            <input type="text" name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="e.g. +95 9 123 456 789">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size:12px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:0.8px; display:block; margin-bottom:6px;">
                                Tour
                            </label>
                            <input type="text" value="{{ $tour->title }}" disabled
                                   style="background:#f3f4f6; color:#6b7280;">
                        </div>
                        <div class="col-12">
                            <label style="font-size:12px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:0.8px; display:block; margin-bottom:6px;">
                                Message
                            </label>
                            <textarea name="message" rows="5"
                                      placeholder="Ask us anything about this tour..."
                                      class="@error('message') is-invalid @enderror"
                                      style="resize:vertical;">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="inquiry-submit-btn">
                                <i class="bi bi-send me-2"></i> Send Inquiry
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

        {{-- RIGHT SIDE --}}
        <div class="col-lg-4">

            {{-- Tour Overview Card --}}
            <div class="tour-overview-card mb-4">
                <div class="tour-name">
                    <span class="lang-en">{{ $tour->title }}</span>
                    <span class="lang-mm" style="display:none;">
                        {{ $tour->title_mm ?? $tour->title }}
                    </span>
                </div>
                <div class="tour-meta-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>{{ $tour->location }}</span>
                </div>
                <div class="tour-meta-item">
                    <i class="bi bi-clock-fill"></i>
                    <span>{{ $tour->duration_days }} Days / {{ $tour->duration_days - 1 }} Nights</span>
                </div>
                <div class="tour-meta-item">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>{{ $tour->availableDates->count() }} dates available</span>
                </div>
                <div class="starting-price">
                    <div class="label">Starting from</div>
                    <div class="amount">${{ number_format($tour->base_price, 0) }}</div>
                    <div class="per">per person</div>
                </div>
            </div>

            {{-- Booking Card --}}
            <div class="booking-card">
                <div class="booking-card-header">
                    <h5><i class="bi bi-calendar-check me-2"></i>Book This Tour</h5>
                </div>
                <div class="booking-card-body">

                    {{-- Travel Date --}}
                    <div class="booking-field mb-3">
                        <label>Travel Date</label>
                        <select id="travel-date" onchange="updateSeason()">
                            <option value="">Select a date</option>
                            @foreach($tour->availableDates as $date)
                                @if($date->availableSeats() > 0)
                                    <option value="{{ $date->start_date->format('Y-m-d') }}"
                                            data-seats="{{ $date->availableSeats() }}">
                                        {{ $date->start_date->format('d M Y') }}
                                        ({{ $date->availableSeats() }} seats left)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div id="season-display" style="margin-top:6px;"></div>
                    </div>

                    {{-- Adults --}}
                    <div class="booking-field mb-3">
                        <label>Number of Adults</label>
                        <input type="number" id="num-adults" min="1" value="1"
                               onchange="calculatePrice()">
                    </div>

                    {{-- Children --}}
                    <div class="booking-field mb-2">
                        <label>Number of Children</label>
                        <input type="number" id="num-children" min="0" value="0"
                               onchange="updateChildrenAges()">
                    </div>
                    <div id="children-ages" class="mb-3"></div>

                    {{-- Pricing Note --}}
                    <div class="pricing-note mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Pricing Rules:</strong><br>
                        Children age 0–4: Free, no seat occupied.<br>
                        Children age 5+: Charged adult fare, seat occupied.
                    </div>

                    {{-- Hotel --}}
                    <div class="booking-field mb-3">
                        <label>Select Hotel</label>
                        <select id="hotel-select" onchange="calculatePrice()">
                            <option value="">Choose a hotel</option>
                            @foreach($tour->hotels as $hotel)
                                <option value="{{ $hotel->id }}"
                                        data-low="{{ $hotel->getPriceForSeason('low') }}"
                                        data-normal="{{ $hotel->getPriceForSeason('normal') }}"
                                        data-peak="{{ $hotel->getPriceForSeason('peak') }}">
                                    {{ $hotel->name }} ({{ $hotel->category }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price Breakdown --}}
                    <div class="price-breakdown" id="price-display" style="display:none;">
                        <div class="price-row">
                            <span class="label">Base Price</span>
                            <span class="value">$<span id="show-base-price">0</span>/person</span>
                        </div>
                        <div class="price-row">
                            <span class="label">Hotel Upgrade</span>
                            <span class="value">+$<span id="show-hotel-price">0</span>/person</span>
                        </div>
                        <div class="price-row">
                            <span class="label">Price Per Person</span>
                            <span class="value">$<span id="show-per-person">0</span></span>
                        </div>
                        <hr class="price-divider">
                        <div class="price-row">
                            <span class="label">Payable Travelers</span>
                            <span class="value"><span id="show-payable">0</span> persons</span>
                        </div>
                        <div class="price-row">
                            <span class="label">Occupied Seats</span>
                            <span class="value"><span id="show-seats">0</span> seats</span>
                        </div>
                        <hr class="price-divider">
                        <div class="total-row">
                            <span class="label">Total</span>
                            <span class="amount">$<span id="show-total">0</span></span>
                        </div>
                    </div>

                    <button class="book-now-btn mt-2" onclick="proceedBooking()">
                        Book Now →
                    </button>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Lightbox Modal --}}
<div class="lightbox-modal" id="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()">×</button>
    <button class="lightbox-prev" onclick="changeLightbox(-1)">
        <i class="bi bi-chevron-left"></i>
    </button>
    <img class="lightbox-img" id="lightbox-img" src="" alt="">
    <button class="lightbox-next" onclick="changeLightbox(1)">
        <i class="bi bi-chevron-right"></i>
    </button>
</div>

@push('scripts')
<script>
    window.seasonPeriods = @json($seasonPeriods);
    window.baseTourPrice = {{ $tour->base_price }};
    window.tourImages = @json($tour->images->pluck('image_path'));
    window.tourId = {{ $tour->id }};
</script>
@endpush
@endsection

