function selectHotelCard(el, hotelId) {
    document.querySelectorAll('.hotel-card-new').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('hotel-select').value = hotelId;
    calculatePrice();
}

function proceedBooking(tourId) {
    const date = document.getElementById('travel-date').value;
    const adults = document.getElementById('num-adults').value;
    const hotel = document.getElementById('hotel-select').value;
    const children = document.getElementById('num-children').value;
    const childAges = [...document.querySelectorAll('.child-age')]
        .map(el => el.value).join(',');

    if (!date) { alert('Please select a travel date!'); return; }
    if (!hotel) { alert('Please select a hotel!'); return; }

    window.location.href = `/booking/${tourId}?date=${date}&adults=${adults}&hotel=${hotel}&children=${children}&ages=${childAges}`;
}