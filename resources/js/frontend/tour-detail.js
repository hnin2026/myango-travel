const seasonPeriods = window.seasonPeriods || [];
const baseTourPrice = window.baseTourPrice || 0;
const tourImages = window.tourImages || [];
const tourId = window.tourId || null;

let currentSeason = 'normal';
let lightboxIndex = 0;

/* =========================
   TAB SWITCHING
========================= */

window.switchTab = function(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });

    document.querySelectorAll('.modern-tab').forEach(button => {
        button.classList.remove('active');
    });

    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
};

/* =========================
   LIGHTBOX
========================= */

window.openLightbox = function(index) {
    lightboxIndex = index;
    updateLightboxImg();

    document.getElementById('lightbox')
        .classList.add('show');
};

window.closeLightbox = function() {
    document.getElementById('lightbox')
        .classList.remove('show');
};

window.changeLightbox = function(dir) {
    lightboxIndex =
        (lightboxIndex + dir + tourImages.length) % tourImages.length;

    updateLightboxImg();
};

function updateLightboxImg() {
    document.getElementById('lightbox-img').src =
        '/storage/' + tourImages[lightboxIndex];
}

const lightbox = document.getElementById('lightbox');

if (lightbox) {
    lightbox.addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });
}

/* =========================
   SEASON DETECTION
========================= */

function getSeasonForDate(dateStr) {

    const selected = new Date(dateStr);

    for (const period of seasonPeriods) {

        const start = new Date(period.start_date);
        const end = new Date(period.end_date);

        let inRange;

        if (start > end) {
            inRange = selected >= start || selected <= end;
        } else {
            inRange = selected >= start && selected <= end;
        }

        if (inRange) {
            return period.season;
        }
    }

    return 'normal';
}

/* =========================
   UPDATE SEASON
========================= */

window.updateSeason = function() {

    const dateSelect = document.getElementById('travel-date');

    if (!dateSelect || !dateSelect.value) {
        document.getElementById('season-display').innerHTML = '';
        return;
    }

    currentSeason = getSeasonForDate(dateSelect.value);

    const labels = {
        low: '<span class="season-badge low">🟢 Low Season</span>',
        normal: '<span class="season-badge normal">🟡 Normal Season</span>',
        peak: '<span class="season-badge peak">🔴 Peak Season</span>'
    };

    document.getElementById('season-display').innerHTML =
        labels[currentSeason];

    calculatePrice();
};

/* =========================
   CHILDREN AGES
========================= */

window.updateChildrenAges = function() {

    const num =
        parseInt(document.getElementById('num-children').value) || 0;

    const container =
        document.getElementById('children-ages');

    container.innerHTML = '';

    for (let i = 0; i < num; i++) {

        container.innerHTML += `
            <div class="booking-field mb-2">
                <label>Child ${i + 1} Age</label>

                <input
                    type="number"
                    class="child-age"
                    min="0"
                    max="17"
                    value="0"
                    onchange="calculatePrice()"
                >
            </div>
        `;
    }

    calculatePrice();
};

/* =========================
   CALCULATE PRICE
========================= */

window.calculatePrice = function() {

    const numAdults =
        parseInt(document.getElementById('num-adults').value) || 0;

    const childAges =
        [...document.querySelectorAll('.child-age')]
            .map(el => parseInt(el.value) || 0);

    const hotelSelect =
        document.getElementById('hotel-select');

    if (!hotelSelect.value) {
        document.getElementById('price-display').style.display = 'none';
        return;
    }

    const selectedOption =
        hotelSelect.options[hotelSelect.selectedIndex];

    const hotelUpgrade =
        parseFloat(selectedOption.dataset[currentSeason]) || 0;

    let paidChildren = 0;

    childAges.forEach(age => {
        if (age >= 5) {
            paidChildren++;
        }
    });

    const payableTravelers =
        numAdults + paidChildren;

    const occupiedSeats =
        numAdults + paidChildren;

    const pricePerPerson =
        baseTourPrice + hotelUpgrade;

    const totalPrice =
        pricePerPerson * payableTravelers;

    document.getElementById('show-base-price').textContent =
        baseTourPrice.toFixed(0);

    document.getElementById('show-hotel-price').textContent =
        hotelUpgrade.toFixed(0);

    document.getElementById('show-per-person').textContent =
        pricePerPerson.toFixed(0);

    document.getElementById('show-payable').textContent =
        payableTravelers;

    document.getElementById('show-seats').textContent =
        occupiedSeats;

    document.getElementById('show-total').textContent =
        totalPrice.toFixed(0);

    document.getElementById('price-display').style.display =
        'block';
};

/* =========================
   HOTEL CARD SELECT
========================= */

window.selectHotelCard = function(el, hotelId) {

    document.querySelectorAll('.hotel-card-new')
        .forEach(card => {
            card.classList.remove('selected');
        });

    el.classList.add('selected');

    document.getElementById('hotel-select').value =
        hotelId;

    calculatePrice();
};

/* =========================
   PROCEED BOOKING
========================= */

window.proceedBooking = function() {

    const date =
        document.getElementById('travel-date').value;

    const adults =
        document.getElementById('num-adults').value;

    const hotel =
        document.getElementById('hotel-select').value;

    const children =
        document.getElementById('num-children').value;

    const childAges =
        [...document.querySelectorAll('.child-age')]
            .map(el => el.value)
            .join(',');

    if (!date) {
        alert('Please select a travel date!');
        return;
    }

    if (!hotel) {
        alert('Please select a hotel!');
        return;
    }

    window.location.href =
        `/booking/${tourId}?date=${date}&adults=${adults}&hotel=${hotel}&children=${children}&ages=${childAges}`;
};