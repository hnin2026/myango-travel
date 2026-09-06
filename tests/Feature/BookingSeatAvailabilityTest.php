<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSeatAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $tour;
    protected $hotel;
    protected $travelPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tour = Tour::create([
            'title' => 'Mandalay Cultural Tour',
            'duration_days' => 3,
            'base_price' => 150.00,
            'location' => 'Mandalay',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Mandalay Hill Resort',
            'category' => '4-star',
            'location' => 'Mandalay'
        ]);

        // Create travel period with total_seats = 2
        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-10',
            'total_seats' => 2,
            'booked_seats' => 0
        ]);
    }

    /**
     * TEST 1: Remaining seats = 2, requested adults = 5 -> rejected and no booking created.
     */
    public function test_booking_rejected_when_requested_adults_exceed_remaining_seats(): void
    {
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-05-02',
            'checkout_date' => '2026-05-05',
            'adults'        => 5,
            'children'      => 0,
            'child_ages'    => '',
            'hotel_id'      => $this->hotel->id,
            'total_price'   => 750,
            'customer_name' => 'Alice Oversized',
            'email'         => 'alice@example.com',
            'phone'         => '0911111111',
            'message'       => 'Group booking'
        ]);

        $response->assertSessionHasErrors(['seats']);
        $this->assertDatabaseMissing('bookings', [
            'customer_name' => 'Alice Oversized'
        ]);
        $this->assertEquals(0, Booking::count());
    }

    /**
     * TEST 2: Remaining seats = 2, requested adults = 2 -> accepted (exact match).
     */
    public function test_booking_accepted_when_requested_adults_equals_remaining_seats(): void
    {
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-05-02',
            'checkout_date' => '2026-05-05',
            'adults'        => 2,
            'children'      => 0,
            'child_ages'    => '',
            'hotel_id'      => $this->hotel->id,
            'total_price'   => 300,
            'customer_name' => 'Bob Exact',
            'email'         => 'bob@example.com',
            'phone'         => '0922222222',
            'message'       => 'Two adults'
        ]);

        $this->assertEquals(1, Booking::count());
        $booking = Booking::first();
        $response->assertRedirect(route('booking.success', ['booking' => $booking->id]));
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Bob Exact',
            'num_persons'   => 2
        ]);
        $this->assertEquals(0, $this->travelPeriod->refresh()->availableSeats());
    }

    /**
     * TEST 3: Remaining seats = 2, requested adults = 1 -> accepted.
     */
    public function test_booking_accepted_when_requested_adults_less_than_remaining_seats(): void
    {
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-05-02',
            'checkout_date' => '2026-05-05',
            'adults'        => 1,
            'children'      => 0,
            'child_ages'    => '',
            'hotel_id'      => $this->hotel->id,
            'total_price'   => 150,
            'customer_name' => 'Charlie Solo',
            'email'         => 'charlie@example.com',
            'phone'         => '0933333333',
            'message'       => 'Solo trip'
        ]);

        $this->assertEquals(1, Booking::count());
        $booking = Booking::first();
        $response->assertRedirect(route('booking.success', ['booking' => $booking->id]));
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Charlie Solo',
            'num_persons'   => 1
        ]);
        $this->assertEquals(1, $this->travelPeriod->refresh()->availableSeats());
    }

    /**
     * TEST 4: Child seat-counting rules (child age >= 5 takes 1 seat, age < 5 takes 0 seats).
     */
    public function test_child_seat_counting_rules(): void
    {
        // 1 adult + 1 child age 3 (< 5) + 1 child age 6 (>= 5) = 2 seats requested against 2 remaining seats -> accepted
        $response1 = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-05-02',
            'checkout_date' => '2026-05-05',
            'adults'        => 1,
            'children'      => 2,
            'child_ages'    => '3,6',
            'hotel_id'      => $this->hotel->id,
            'total_price'   => 300,
            'customer_name' => 'Family Accept',
            'email'         => 'family1@example.com',
            'phone'         => '0944444444'
        ]);

        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Family Accept'
        ]);
        $this->assertEquals(0, $this->travelPeriod->refresh()->availableSeats());

        // Reset travel period to 2 seats for second sub-test
        Booking::query()->delete();
        $this->assertEquals(2, $this->travelPeriod->refresh()->availableSeats());

        // 1 adult + 2 children aged 6, 8 (both >= 5) = 3 seats requested against 2 remaining seats -> rejected
        $response2 = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-05-02',
            'checkout_date' => '2026-05-05',
            'adults'        => 1,
            'children'      => 2,
            'child_ages'    => '6,8',
            'hotel_id'      => $this->hotel->id,
            'total_price'   => 450,
            'customer_name' => 'Family Overbook',
            'email'         => 'family2@example.com',
            'phone'         => '0955555555'
        ]);

        $response2->assertSessionHasErrors(['seats']);
        $this->assertDatabaseMissing('bookings', [
            'customer_name' => 'Family Overbook'
        ]);
        $this->assertEquals(0, Booking::count());
    }

    /**
     * TEST 5: Verify insufficient-seat request does not create a booking record.
     */
    public function test_insufficient_seats_does_not_create_booking_record(): void
    {
        $initialCount = Booking::count();

        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-05-02',
            'checkout_date' => '2026-05-05',
            'adults'        => 3,
            'children'      => 0,
            'child_ages'    => '',
            'hotel_id'      => $this->hotel->id,
            'total_price'   => 450,
            'customer_name' => 'David Excessive',
            'email'         => 'david@example.com',
            'phone'         => '0966666666'
        ]);

        $response->assertSessionHasErrors(['seats']);
        $this->assertEquals($initialCount, Booking::count());
    }
}
