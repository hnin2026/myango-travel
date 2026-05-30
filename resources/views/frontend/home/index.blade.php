@extends('frontend.layouts.app')

@section('title', 'MyanGo Travel - Discover Myanmar')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

@endpush

@section('content')

{{-- Hero Section --}}
<section class="hero-section">
    <div class="w-100 d-flex justify-content-between align-items-end">
        <div class="hero-content">
            <div class="hero-left">
                <h1 class="hero-title">
                    Discover the Beauty of Myanmar
                </h1>

                <p class="hero-subtitle">
                    Hidden gems, breathtaking views, unforgettable adventures where will you go next?
                </p>
            </div>

            <div class="hero-right">
                <a href="#tours" class="hero-button">
                    Book Now
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </div>

        </div>
    </div>
</section>

{{-- Tours Section --}}
<section class="tours-section" id="tours">
    <div class="container">
        <h2 class="section-title">Tour Packages</h2>

        <div class="row g-4">
            @forelse($tours as $tour)
                <div class="col-sm-6 col-lg-4">
                    <div class="tour-card">
                        {{-- Image --}}
                        <div class="tour-image-wrapper">
                            @if($tour->images->first())
                                <img src="{{ asset('storage/' . $tour->images->first()->image_path) }}"
                                     class="tour-image" alt="{{ $tour->title }}">
                            @else
                                <img src="https://placehold.co/400x260/111827/white?text=MyanGo"
                                     class="tour-image" alt="{{ $tour->title }}">
                            @endif
                            <span class="location-badge">
                                <i class="bi bi-geo-alt-fill"></i> {{ $tour->location }}
                            </span>
                        </div>

                        {{-- Content --}}
                        <div class="tour-content">
                            <h3 class="tour-title">
                                <span class="lang-en">{{ $tour->title }}</span>
                                <span class="lang-mm" style="display:none;">
                                    {{ $tour->title_mm ?? $tour->title }}
                                </span>
                            </h3>
                            <p class="tour-info">
                                <i class="bi bi-clock"></i>
                                {{ $tour->duration_days }} Days /
                                {{ $tour->duration_days - 1 }} Nights
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="tour-price mb-0">
                                        ${{ number_format($tour->base_price, 0) }}
                                        <small class="text-muted fw-normal">/person</small>
                                    </p>
                                    <small class="text-muted">
                                        {{ $tour->availableDates->count() }} dates available
                                    </small>
                                </div>
                                <a href="{{ route('tours.show', $tour) }}" class="tour-link">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-compass display-1 text-muted"></i>
                    <p class="text-muted mt-3">No tours available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@endsection