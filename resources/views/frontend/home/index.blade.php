@extends('frontend.layouts.app')

@section('title', 'MyanGo Travel - Discover Myanmar')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                    url('/images/hero-bg.jpg') center/cover no-repeat;
        min-height: 550px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    .tour-card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        height: 100%;
    }
    .tour-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .tour-card img {
        height: 220px;
        object-fit: cover;
        width: 100%;
    }
    .location-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(0,0,0,0.6);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
    }
    .arrow-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #2563eb;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    .arrow-btn:hover {
        background: #1d4ed8;
        color: white;
    }
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Discover the Beauty of Myanmar</h1>
        <p class="lead mb-4">Experience unforgettable journeys with MyanGo Travel</p>
        <a href="#tours" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-compass"></i> Explore Tours
        </a>
    </div>
</section>

{{-- Tours Section --}}
<section class="py-5 bg-light" id="tours">
    <div class="container">
        <h2 class="text-center fw-bold mb-2">Our Tour Packages</h2>
        <p class="text-center text-muted mb-5">
            Choose from our carefully curated Myanmar travel experiences
        </p>

        <div class="row g-4">
            @forelse($tours as $tour)
                <div class="col-md-4">
                    <div class="card tour-card">
                        {{-- Tour Image --}}
                        <div class="position-relative">
                            @if($tour->images->first())
                                <img src="{{ asset('storage/' . $tour->images->first()->image_path) }}"
                                     alt="{{ $tour->title }}">
                            @else
                                <img src="https://placehold.co/400x220/2563eb/white?text=MyanGo+Travel"
                                     alt="{{ $tour->title }}">
                            @endif
                            <span class="location-badge">
                                <i class="bi bi-geo-alt-fill"></i> {{ $tour->location }}
                            </span>
                        </div>

                        {{-- Tour Info --}}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold mb-1">{{ $tour->title }}</h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-clock"></i>
                                {{ $tour->duration_days }} Days /
                                {{ $tour->duration_days - 1 }} Nights
                            </p>
                            <p class="text-primary fw-bold mb-0">
                                Starting from ${{ number_format($tour->base_price, 0) }} per person
                            </p>
                            <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check"></i>
                                    {{ $tour->availableDates->count() }} dates available
                                </small>
                                <a href="{{ route('tours.show', $tour) }}" class="arrow-btn">
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