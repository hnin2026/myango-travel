const checkoutField = document.getElementById('checkout-date');

let currentSeason = 'normal';
let baseTourPrice = window.baseTourPrice || 0;
let seasonPeriods = window.seasonPeriods || [];


/*
|--------------------------------------------------------------------------
| Disable manual checkout editing
|--------------------------------------------------------------------------
*/
if (checkoutField) {
    checkoutField.addEventListener('keydown', function (e) {
        e.preventDefault();
    });
}


/*
|--------------------------------------------------------------------------
| Auto checkout date
|--------------------------------------------------------------------------
*/
window.updateCheckoutDate = function () {
    const checkinField = document.getElementById('checkin-date');
    if (!checkinField || !checkoutField) return;
    const checkin = checkinField.value;

    if (!checkin) {
        checkoutField.value = '';
        return;
    }

    const durationDays = window.durationDays;
    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkinDate);

    checkoutDate.setDate(
        checkoutDate.getDate() + durationDays - 1
    );

    const year = checkoutDate.getFullYear();
    const month = String(checkoutDate.getMonth() + 1).padStart(2, '0');
    const day = String(checkoutDate.getDate()).padStart(2, '0');

    checkoutField.value = `${year}-${month}-${day}`;

    calculatePrice();
};

window.getSeasonForDate = function (dateStr) {
    const selected = new Date(dateStr);

    for (const period of seasonPeriods) {
        const start = new Date(period.start_date);
        const end = new Date(period.end_date);

        if (selected >= start && selected <= end) {
            return period.season;
        }
    }

    return 'normal';
};

window.updateSeason = function () {
    const checkinElement = document.getElementById('checkin-date');
    if (!checkinElement) return;
    const checkin = checkinElement.value;

    if (!checkin) {
        currentSeason = 'normal';
        return;
    }

    currentSeason = getSeasonForDate(checkin);

    calculatePrice();
};

/*
|--------------------------------------------------------------------------
| Hotel card select
|--------------------------------------------------------------------------
*/
window.selectHotelCard = function (el, hotelId) {
    document.querySelectorAll('.hotel-card-new')
        .forEach(card => card.classList.remove('selected'));

    el.classList.add('selected');

    const hotelSelect = document.getElementById('hotel-select');
    if (hotelSelect) {
        hotelSelect.value = hotelId;
    }

    calculatePrice();
};


/*
|--------------------------------------------------------------------------
| Dynamic child age fields
|--------------------------------------------------------------------------
*/
window.updateBookingChildrenAges = function () {
    const numChildrenEl = document.getElementById('booking-num-children');
    const container = document.getElementById('booking-children-ages');
    if (!numChildrenEl || !container) return;

    const numChildren =
        parseInt(numChildrenEl.value) || 0;

    container.innerHTML = '';

    for (let i = 1; i <= numChildren; i++) {
        container.innerHTML += `
            <div class="booking-field mb-2">
                <label>Child ${i} Age</label>
                <input
                    type="number"
                    class="child-age"
                    min="0"
                    max="12"
                    placeholder="Enter age"
                    onchange="calculatePrice()"
                >
            </div>
        `;
    }

    calculatePrice();
};

window.updateInquiryChildrenAges = function (input) {
    const numChildren = parseInt(input.value) || 0;

    const container =
        input.closest('form')
             .querySelector('.inquiry-children-ages');

    container.innerHTML = '';

    for (let i = 1; i <= numChildren; i++) {
        container.innerHTML += `
            <div class="booking-field mb-2">
                <label>Child ${i} Age</label>
                <input
                    type="number"
                    name="child_ages[]"
                    min="0"
                    max="12"
                    placeholder="Enter age"
                >
            </div>
        `;
    }
};

/*
|--------------------------------------------------------------------------
| Price calculation
|--------------------------------------------------------------------------
*/
window.calculatePrice = function () {
    const numAdultsEl = document.getElementById('num-adults');
    const hotelSelect = document.getElementById('hotel-select');
    const priceDisplay = document.getElementById('price-display');
    if (!numAdultsEl || !hotelSelect || !priceDisplay) return;

    const numAdults =
        parseInt(numAdultsEl.value) || 1;

    const childAges =
        [...document.querySelectorAll('.child-age')]
        .map(el => parseInt(el.value) || 0);

    let payableChildren = 0;
    let occupiedSeats = numAdults;

    childAges.forEach(age => {
        if (age >= 5) {
            payableChildren++;
            occupiedSeats++;
        }
    });

    if (!hotelSelect.value) {
        priceDisplay.style.display = 'none';
        return;
    }

    const selectedOption =
        hotelSelect.options[hotelSelect.selectedIndex];

    const hotelUpgrade =
        parseFloat(selectedOption.dataset[currentSeason]) || 0;

    const payableTravelers =
        numAdults + payableChildren;

    const pricePerPerson =
        baseTourPrice + hotelUpgrade;

    const totalPrice =
        pricePerPerson * payableTravelers;

    const showBasePrice = document.getElementById('show-base-price');
    const showHotelPrice = document.getElementById('show-hotel-price');
    const showPerPerson = document.getElementById('show-per-person');
    const showPayable = document.getElementById('show-payable');
    const showSeats = document.getElementById('show-seats');
    const showTotal = document.getElementById('show-total');

    if (showBasePrice) showBasePrice.textContent = baseTourPrice.toFixed(0);
    if (showHotelPrice) showHotelPrice.textContent = hotelUpgrade.toFixed(0);
    if (showPerPerson) showPerPerson.textContent = pricePerPerson.toFixed(0);
    if (showPayable) showPayable.textContent = payableTravelers;
    if (showSeats) showSeats.textContent = occupiedSeats;
    if (showTotal) showTotal.textContent = totalPrice.toFixed(0);

    priceDisplay.style.display = 'block';
};


/*
|--------------------------------------------------------------------------
| Proceed booking
|--------------------------------------------------------------------------
*/
window.proceedBooking = function (tourId) {
    const checkinEl = document.getElementById('checkin-date');
    const checkoutEl = document.getElementById('checkout-date');
    const adultsEl = document.getElementById('num-adults');
    const hotelEl = document.getElementById('hotel-select');
    const childrenEl = document.getElementById('booking-num-children');
    const showTotalEl = document.getElementById('show-total');

    if (!checkinEl || !checkoutEl || !adultsEl || !hotelEl || !childrenEl || !showTotalEl) {
        return;
    }

    const checkin = checkinEl.value;
    const checkout = checkoutEl.value;
    const adults = adultsEl.value;
    const hotel = hotelEl.value;
    const children = childrenEl.value;

    const childAges =
        [...document.querySelectorAll('.child-age')]
        .map(el => el.value)
        .join(',');

    if (!checkin) {
        alert('Please select a check-in date!');
        return;
    }

    if (!hotel) {
        alert('Please select a hotel!');
        return;
    }

window.location.href =
    `/booking/${tourId}?checkin=${checkin}&checkout=${checkout}&adults=${adults}&hotel=${hotel}&children=${children}&ages=${childAges}&total=${showTotalEl.textContent}`;
};