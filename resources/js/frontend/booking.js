const checkoutField =
    document.getElementById('checkout-date');

checkoutField.addEventListener('keydown', function(e) {
    e.preventDefault();
});

window.updateCheckoutDate = function () {

    const checkinField =
        document.getElementById('checkin-date');

    const checkin =
        checkinField.value;

    if (!checkin) {

        checkoutField.value = '';
        return;
    }

    const durationDays =
        window.durationDays;

    const checkinDate =
        new Date(checkin);

    const checkoutDate =
        new Date(checkinDate);

    checkoutDate.setDate(
        checkoutDate.getDate() + durationDays - 1
    );

    const year =
        checkoutDate.getFullYear();

    const month =
        String(checkoutDate.getMonth() + 1)
        .padStart(2, '0');

    const day =
        String(checkoutDate.getDate())
        .padStart(2, '0');

    checkoutField.value =
        `${year}-${month}-${day}`;
};

function selectHotelCard(el, hotelId) {

    document
        .querySelectorAll('.hotel-card-new')
        .forEach(card => card.classList.remove('selected'));

    el.classList.add('selected');

    document.getElementById('hotel-select').value =
        hotelId;

    calculatePrice();
}

function proceedBooking(tourId) {

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
}