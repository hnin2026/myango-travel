const checkoutField = document.getElementById('checkout-date');

let currentSeason = 'normal';
let baseTourPrice = window.baseTourPrice || 0;
let seasonPeriods = window.seasonPeriods || [];


/*
|--------------------------------------------------------------------------
| Disable manual checkout editing
|--------------------------------------------------------------------------
*/
checkoutField.addEventListener('keydown', function (e) {
    e.preventDefault();
});


/*
|--------------------------------------------------------------------------
| Auto checkout date
|--------------------------------------------------------------------------
*/
window.updateCheckoutDate = function () {
    const checkinField = document.getElementById('checkin-date');
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
    const checkin =
        document.getElementById('checkin-date').value;

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

    document.getElementById('hotel-select').value = hotelId;

    calculatePrice();
};


/*
|--------------------------------------------------------------------------
| Dynamic child age fields
|--------------------------------------------------------------------------
*/
window.updateBookingChildrenAges = function () {
    const numChildren =
        parseInt(document.getElementById('booking-num-children').value) || 0;

    const container =
        document.getElementById('booking-children-ages');

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
    const numAdults =
        parseInt(document.getElementById('num-adults').value) || 1;

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

    const payableTravelers =
        numAdults + payableChildren;

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

    document.getElementById('price-display').style.display = 'block';
};


/*
|--------------------------------------------------------------------------
| Proceed booking
|--------------------------------------------------------------------------
*/
window.proceedBooking = function (tourId) {
    const checkin =
        document.getElementById('checkin-date').value;

    const checkout =
        document.getElementById('checkout-date').value;

    const adults =
        document.getElementById('num-adults').value;

    const hotel =
        document.getElementById('hotel-select').value;

    const children =
        document.getElementById('booking-num-children').value;

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
    `/booking/${tourId}?checkin=${checkin}&checkout=${checkout}&adults=${adults}&hotel=${hotel}&children=${children}&ages=${childAges}&total=${document.getElementById('show-total').textContent}`;
};