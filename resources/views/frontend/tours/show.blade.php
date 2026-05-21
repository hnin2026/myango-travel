@extends('frontend.layouts.app')

@section('title', $tour->title . ' - MyanGo Travel')

@push('styles')
<style>
    .gallery-img {
        height: 400px;
        object-fit: cover;
        width: 100%;
        border-radius: 12px;
    }
    .hotel-card {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .hotel-card:hover { border-color: #2563eb; }
    .hotel-card.selected {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .booking-card {
        position: sticky;
        top: 80px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .price-display {
        background: #eff6ff;
        border-radius: 10px;
        padding: 16px;
    }
    .lang-switcher {
        display: inline-flex;
        background: #f3f4f6;
        border-radius: 20px;
        padding: 3px;
    }
    .lang-btn {
        padding: 4px 16px;
        border-radius: 16px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .lang-btn.active {
        background: #2563eb;
        color: white;
    }
    .day-timeline {
        position: relative;
        padding-left: 40px;
    }
    .day-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .day-item {
        position: relative;
        margin-bottom: 24px;
    }
    .day-dot {
        position: absolute;
        left: -32px;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #2563eb;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #2563eb;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row g-4">

        {{-- Left Side --}}
        <div class="col-lg-8">

            {{-- Image Gallery --}}
            @if($tour->images->count() > 0)
                <div id="tourGallery" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($tour->images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     class="gallery-img" alt="{{ $tour->title }}">
                            </div>
                        @endforeach
                    </div>
                    @if($tour->images->count() > 1)
                        <button class="carousel-control-prev" type="button"
                                data-bs-target="#tourGallery" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button"
                                data-bs-target="#tourGallery" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <img src="https://placehold.co/800x400/2563eb/white?text=MyanGo+Travel"
                     class="gallery-img mb-4" alt="{{ $tour->title }}">
            @endif

            {{-- Tour Overview --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="fw-bold mb-1">
                            <span class="lang-en">{{ $tour->title }}</span>
                            <span class="lang-mm" style="display:none;">
                                {{ $tour->title_mm ?? $tour->title }}
                            </span>
                        </h2>
                        <div class="d-flex gap-3 text-muted flex-wrap">
                            <span><i class="bi bi-geo-alt"></i> {{ $tour->location }}</span>
                            <span>
                                <i class="bi bi-clock"></i>
                                {{ $tour->duration_days }} Days /
                                {{ $tour->duration_days - 1 }} Nights
                            </span>
                        </div>
                        <p class="text-primary fw-bold mt-2 mb-0">
                            Starting from ${{ number_format($tour->base_price, 0) }} per person
                        </p>
                    </div>
                    {{-- Language Switcher --}}
                    <div class="lang-switcher flex-shrink-0">
                        <button class="lang-btn active" id="btn-en" onclick="switchLang('en')">EN</button>
                        <button class="lang-btn" id="btn-mm" onclick="switchLang('mm')">မြန်မာ</button>
                    </div>
                </div>
            </div>

            {{-- 3 Tabs --}}
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-description">
                                <i class="bi bi-file-text"></i> Description
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-schedule">
                                <i class="bi bi-calendar3"></i> Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-additional">
                                <i class="bi bi-info-circle"></i> Additional Info
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content pt-2">

                        {{-- Description --}}
                        <div class="tab-pane fade show active" id="tab-description">
                            <div class="lang-en">{!! $tour->description_en !!}</div>
                            <div class="lang-mm" style="display:none;">
                                {!! $tour->description_mm ?? $tour->description_en !!}
                            </div>
                        </div>

                        {{-- Schedule --}}
                        <div class="tab-pane fade" id="tab-schedule">
                            <div class="day-timeline">
                                @forelse($tour->itineraries as $itinerary)
                                    <div class="day-item">
                                        <div class="day-dot"></div>
                                        <h6 class="fw-bold text-primary mb-1">
                                            Day {{ $itinerary->day_number }}
                                        </h6>
                                        <div class="lang-en">
                                            <p class="mb-0 text-muted">{{ $itinerary->description_en }}</p>
                                        </div>
                                        <div class="lang-mm" style="display:none;">
                                            <p class="mb-0 text-muted">
                                                {{ $itinerary->description_mm ?? $itinerary->description_en }}
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">Schedule not available yet.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Additional Info --}}
                        <div class="tab-pane fade" id="tab-additional">
                            <div class="lang-en">{!! $tour->additional_info_en !!}</div>
                            <div class="lang-mm" style="display:none;">
                                {!! $tour->additional_info_mm ?? $tour->additional_info_en !!}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Available Hotels --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-building"></i> Available Hotels
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($tour->hotels as $hotel)
                            <div class="col-md-4">
                                <div class="hotel-card"
                                     onclick="selectHotelCard(this, '{{ $hotel->id }}')">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 small">{{ $hotel->name }}</h6>
                                        <span class="badge bg-warning text-dark">
                                            {{ $hotel->category }}
                                        </span>
                                    </div>
                                    @if($hotel->location)
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-geo-alt"></i> {{ $hotel->location }}
                                        </p>
                                    @endif
                                    <div class="mt-2">
                                        <small class="text-success d-block">
                                            Low: +${{ $hotel->getPriceForSeason('low') }}
                                        </small>
                                        <small class="text-warning d-block">
                                            Normal: +${{ $hotel->getPriceForSeason('normal') }}
                                        </small>
                                        <small class="text-danger d-block">
                                            Peak: +${{ $hotel->getPriceForSeason('peak') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Inquiry Form --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-chat-dots"></i> Send an Inquiry
                </div>
                <div class="card-body">
                    @if(session('inquiry_success'))
                        <div class="alert alert-success">
                            {{ session('inquiry_success') }}
                        </div>
                    @endif
                    <form action="{{ route('inquiry.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="customer_name"
                                       class="form-control @error('customer_name') is-invalid @enderror"
                                       value="{{ old('customer_name') }}"
                                       placeholder="e.g. John Smith">
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="e.g. john@email.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone"
                                       class="form-control"
                                       value="{{ old('phone') }}"
                                       placeholder="e.g. +95 9 123 456 789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tour</label>
                                <input type="text" class="form-control"
                                       value="{{ $tour->title }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" rows="4"
                                          class="form-control @error('message') is-invalid @enderror"
                                          placeholder="Ask us anything about this tour...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-send"></i> Send Inquiry
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right Side - Booking Card --}}
        <div class="col-lg-4">
            <div class="card booking-card">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-calendar-check"></i> Book This Tour
                </div>
                <div class="card-body">

                    {{-- Travel Date --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Travel Date</label>
                        <select class="form-select" id="travel-date" onchange="updateSeason()">
                            <option value="">Select a date</option>
                            @foreach($tour->availableDates as $date)
                                @if($date->availableSeats() > 0)
                                    <option value="{{ $date->start_date->format('Y-m-d') }}"
                                            data-seats="{{ $date->availableSeats() }}">
                                        {{ $date->start_date->format('d M Y') }}-{{ $date->end_date->format('d M Y') }}
                                        ({{ $date->availableSeats() }} seats left)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block" id="season-display"></small>
                    </div>

                    {{-- Adults --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Number of Adults</label>
                        <input type="number" id="num-adults" class="form-control"
                               min="1" value="1" onchange="calculatePrice()">
                    </div>

                    {{-- Children --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Number of Children</label>
                        <input type="number" id="num-children" class="form-control"
                               min="0" value="0" onchange="updateChildrenAges()">
                        <div id="children-ages" class="mt-2"></div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Age 0-4: Free, no seat. Age 5+: Adult fare.
                        </small>
                    </div>

                    {{-- Hotel --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hotel</label>
                        <select class="form-select" id="hotel-select" onchange="calculatePrice()">
                            <option value="">Select a hotel</option>
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
                    <div class="price-display mb-3" id="price-display" style="display:none;">
                        <h6 class="fw-bold mb-3">Price Breakdown</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Base Price</small>
                            <small>$<span id="show-base-price">0</span>/person</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Hotel Upgrade</small>
                            <small>+$<span id="show-hotel-price">0</span>/person</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Price Per Person</small>
                            <small>$<span id="show-per-person">0</span></small>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Payable Travelers</small>
                            <small><span id="show-payable">0</span> persons</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Occupied Seats</small>
                            <small><span id="show-seats">0</span> seats</small>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong>Total Price</strong>
                            <strong class="text-primary">
                                $<span id="show-total">0</span>
                            </strong>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100"
                            onclick="proceedBooking()">
                        <i class="bi bi-calendar-check"></i> Book Now
                    </button>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const seasonPeriods = tourData.seasonPeriods;
    const baseTourPrice = tourData.baseTourPrice;
    let currentSeason = 'normal';

    // Language switcher
    function switchLang(lang) {
        document.querySelectorAll('.lang-en').forEach(el => {
            el.style.display = lang === 'en' ? '' : 'none';
        });
        document.querySelectorAll('.lang-mm').forEach(el => {
            el.style.display = lang === 'mm' ? '' : 'none';
        });
        document.getElementById('btn-en').classList.toggle('active', lang === 'en');
        document.getElementById('btn-mm').classList.toggle('active', lang === 'mm');
    }

    // Detect season
    function getSeasonForDate(dateStr) {
        const selected = new Date(dateStr);
        for (const period of seasonPeriods) {
            const start = new Date(period.start_date);
            const end = new Date(period.end_date);
            let inRange = start > end
                ? selected >= start || selected <= end
                : selected >= start && selected <= end;
            if (inRange) return period.season;
        }
        return 'normal';
    }

    // Update season display
    function updateSeason() {
        const dateSelect = document.getElementById('travel-date');
        if (!dateSelect.value) {
            document.getElementById('season-display').textContent = '';
            return;
        }
        currentSeason = getSeasonForDate(dateSelect.value);
        const labels = {
            low: '🟢 Low Season',
            normal: '🟡 Normal Season',
            peak: '🔴 Peak Season'
        };
        document.getElementById('season-display').textContent = labels[currentSeason];
        calculatePrice();
    }

    // Children age inputs
    function updateChildrenAges() {
        const num = parseInt(document.getElementById('num-children').value) || 0;
        const container = document.getElementById('children-ages');
        container.innerHTML = '';
        for (let i = 0; i < num; i++) {
            container.innerHTML += `
                <div class="mb-2">
                    <label class="form-label small fw-bold">Child ${i + 1} Age</label>
                    <input type="number" class="form-control form-control-sm child-age"
                           min="0" max="17" value="0" onchange="calculatePrice()">
                </div>`;
        }
        calculatePrice();
    }

    // Calculate price
    function calculatePrice() {
        const numAdults = parseInt(document.getElementById('num-adults').value) || 0;
        const childAges = [...document.querySelectorAll('.child-age')]
            .map(el => parseInt(el.value) || 0);
        const hotelSelect = document.getElementById('hotel-select');

        if (!hotelSelect.value) {
            document.getElementById('price-display').style.display = 'none';
            return;
        }

        const selectedOption = hotelSelect.options[hotelSelect.selectedIndex];
        const hotelUpgrade = parseFloat(selectedOption.dataset[currentSeason]) || 0;

        let paidChildren = 0;
        childAges.forEach(age => { if (age >= 5) paidChildren++; });

        const payableTravelers = numAdults + paidChildren;
        const occupiedSeats = numAdults + paidChildren;
        const pricePerPerson = baseTourPrice + hotelUpgrade;
        const totalPrice = pricePerPerson * payableTravelers;

        document.getElementById('show-base-price').textContent = baseTourPrice.toFixed(0);
        document.getElementById('show-hotel-price').textContent = hotelUpgrade.toFixed(0);
        document.getElementById('show-per-person').textContent = pricePerPerson.toFixed(0);
        document.getElementById('show-payable').textContent = payableTravelers;
        document.getElementById('show-seats').textContent = occupiedSeats;
        document.getElementById('show-total').textContent = totalPrice.toFixed(0);
        document.getElementById('price-display').style.display = 'block';
    }

    // Select hotel from cards
    function selectHotelCard(el, hotelId) {
        document.querySelectorAll('.hotel-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('hotel-select').value = hotelId;
        calculatePrice();
    }

    // Proceed to booking
    function proceedBooking() {
        const date = document.getElementById('travel-date').value;
        const adults = document.getElementById('num-adults').value;
        const hotel = document.getElementById('hotel-select').value;
        const children = document.getElementById('num-children').value;
        const childAges = [...document.querySelectorAll('.child-age')]
            .map(el => el.value).join(',');

        if (!date) { alert('Please select a travel date!'); return; }
        if (!hotel) { alert('Please select a hotel!'); return; }

        window.location.href = `/booking/{{ $tour->id }}?date=${date}&adults=${adults}&hotel=${hotel}&children=${children}&ages=${childAges}`;
    }
</script>
@endpush