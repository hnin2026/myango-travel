@extends('frontend.layouts.app')

@section('title', 'Tour Packages - MyanGo Travel')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/frontend/tours.css'])
@endpush

@section('content')

<section class="tours-section" id="tours">
    <div class="container">
        
        {{-- Header & Dropdown Filter --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 border-bottom pb-4">
            <div>
                @if(request('destination'))
                    <h1 class="fw-bold mb-0 text-capitalize" style="color: #111844; font-size: 32px;">
                        {{ ucwords(strtolower(request('destination'))) }} Tours
                    </h1>
                @else
                    <h1 class="fw-bold mb-0" style="color: #111844; font-size: 32px;">Tour Packages</h1>
                @endif
                <p class="text-muted mb-0">Explore our curated active travel packages</p>
            </div>
        </div>

        {{-- Tours Grid --}}
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
                            <div class="tour-footer-row">
                                <div class="tour-price-wrapper">
                                    <span class="tour-price-label">Starting from</span>
                                    <div class="tour-price">
                                        ${{ number_format($tour->base_price, 0) }}
                                        <small>USD</small>
                                    </div>
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
                    <div class="p-5 bg-white rounded-4 shadow-sm" style="border-radius: 24px;">
                        <i class="bi bi-compass display-1 text-muted"></i>
                        <h4 class="fw-bold mt-4" style="color: #111844;">No Tour Packages Found</h4>
                        <p class="text-muted mb-4">No tour packages are currently available for this destination.</p>
                        <a href="{{ route('tours.index') }}" class="btn-see-more">
                            <i class="bi bi-arrow-left me-2"></i> Return to All Destinations
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($tours->hasPages())
            <div class="pagination-wrapper">
                {{ $tours->links() }}
            </div>
        @endif
        
    </div>
</section>

@endsection
