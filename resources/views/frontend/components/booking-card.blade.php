<div class="booking-card">
                <div class="booking-card-header">
                    <h5><i class="bi bi-calendar-check me-2"></i>Book This Tour</h5>
                </div>
                <div class="booking-card-body">
                    {{-- Travel Date --}}
                    <div class="booking-field mb-3">
                    <label>Check-in Date</label>
                    <input
                        type="date"
                        id="checkin-date"
                        class="form-control"
                        onchange="updateCheckoutDate()"
                    >
                </div>

                <div class="booking-field mb-3">

                    <label>Check-out Date</label>

                    <input
                        type="date"
                        id="checkout-date"
                        class="form-control"
                    >

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
                        Book Now
                    </button>

                </div>
            </div>