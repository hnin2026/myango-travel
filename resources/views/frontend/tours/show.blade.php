@extends('frontend.layouts.app')

@section('title', $tour->title . ' - MyanGo Travel')

@push('styles')
    @vite([
        'resources/css/frontend/tour-detail.css',
        'resources/css/frontend/inquiry-form.css',
        'resources/css/frontend/responsive.css'
    ])
@endpush

@section('content')

<div class="content-wrapper">
    <div class="row g-5">
        {{-- TOUR OVERVIEW --}}
            <div>
                <h1  class="tour-detail-title">
                    <span class="lang-en">{{ $tour->title }}</span>
                    <span class="lang-mm" style="display:none;">
                        {{ $tour->title_mm ?? $tour->title }}
                    </span>
                </h1>
                <div class="d-flex flex-wrap gap-4 mb-2">
                    <span style="color:#6b7280; font-size:15px;">
                        <i class="bi bi-geo-alt me-1" style="color:var(--mid-blue)"></i>
                        {{ $tour->location }}
                    </span>
                    <span style="color:#6b7280; font-size:15px;">
                        <i class="bi bi-clock me-1" style="color:var(--mid-blue)"></i>
                        {{ $tour->duration_days }} Days / {{ $tour->duration_days - 1 }} Nights
                    </span>
                </div>
            </div>

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

        {{-- LEFT SIDE --}}
        <div class="col-lg-8">

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
                <div>
                    @forelse($tour->itineraries as $itinerary)
                        <div class="schedule-card">
                            <div class="schedule-day">Day {{ $itinerary->day_number }}</div>
                            <div class="lang-en">
                                <div class="schedule-desc">{!! $itinerary->description_en !!}</div>
                            </div>
                            <div class="lang-mm" style="display:none;">
                                <div class="schedule-desc">
                                    {!! $itinerary->description_mm ?? $itinerary->description_en !!}
                                </div>
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
            <div class="inquiry-section mt-5 d-none d-lg-block">
                @include('frontend.components.inquiry-form', ['tour' => $tour])

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
                <div class="tour-meta-item mb-4">
                    <i class="bi bi-clock-fill"></i>
                    <span>{{ $tour->duration_days }} Days / {{ $tour->duration_days - 1 }} Nights</span>
                </div>
                
                {{-- Available Travel Period --}}
                <div class="tour-meta-section mb-3">
                    <div class="tour-meta-label" style="font-weight: 600; font-size: 14px; color: white; display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-calendar-check-fill" style="color: var(--cream); font-size: 16px; width: 20px;"></i>
                        <span>Available Travel Period</span>
                    </div>
                    <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-left: 30px; margin-top: 4px;">
                        {{ $tour->travelPeriods->min('start_date')?->format('d M Y') }}
                        -
                        {{ $tour->travelPeriods->max('end_date')?->format('d M Y') }}
                    </div>
                </div>

                {{-- Blackout Dates --}}
                @if($tour->blackoutPeriods->isNotEmpty())
                    <div class="tour-meta-section mb-3">
                        <div class="tour-meta-label" style="font-weight: 600; font-size: 14px; color: white; display: flex; align-items: center; gap: 10px;">
                            <i class="bi bi-slash-circle-fill" style="color: #f87171; font-size: 16px; width: 20px;"></i>
                            <span>Blackout Dates</span>
                        </div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-left: 30px; margin-top: 4px; display: flex; flex-direction: column; gap: 2px;">
                            @foreach($tour->blackoutPeriods as $blackout)
                                <div>
                                    {{ $blackout->start_date?->format('d M Y') }}
                                    -
                                    {{ $blackout->end_date?->format('d M Y') }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="starting-price">
                    <div class="label">Starting from</div>
                    <div class="amount">${{ number_format($tour->base_price, 0) }}</div>
                    <div class="per">per person</div>
                </div>
            </div>

            {{-- Booking Card --}}
            @include('frontend.components.booking-card', ['tour' => $tour])

            <div class="inquiry-section mt-4 d-block d-lg-none">
            @include('frontend.components.inquiry-form', ['tour' => $tour])
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
    window.durationDays = {{ $tour->duration_days }};
</script>

@vite('resources/js/frontend/booking.js')
@endpush
@endsection
