let currentSeason = 'normal';
let baseTourPrice = 0;
let seasonPeriods = [];

function initPriceCalculator(price, periods) {
    baseTourPrice = price;
    seasonPeriods = periods;
}

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

function updateSeason() {
    const dateSelect = document.getElementById('travel-date');
    if (!dateSelect.value) {
        document.getElementById('season-display').innerHTML = '';
        return;
    }
    currentSeason = getSeasonForDate(dateSelect.value);
    const labels = {
        low: '<span class="season-badge low">🟢 Low Season</span>',
        normal: '<span class="season-badge normal">🟡 Normal Season</span>',
        peak: '<span class="season-badge peak">🔴 Peak Season</span>'
    };
    document.getElementById('season-display').innerHTML = labels[currentSeason];
    calculatePrice();
}

function updateChildrenAges() {
    const num = parseInt(document.getElementById('num-children').value) || 0;
    const container = document.getElementById('children-ages');
    container.innerHTML = '';
    for (let i = 0; i < num; i++) {
        container.innerHTML += `
            <div class="booking-field mb-2">
                <label>Child ${i + 1} Age</label>
                <input type="number" class="child-age" min="0" max="17" value="0"
                       onchange="calculatePrice()">
            </div>`;
    }
    calculatePrice();
}

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