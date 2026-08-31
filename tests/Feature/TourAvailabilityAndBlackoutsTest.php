<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\TourBlackoutPeriod;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourAvailabilityAndBlackoutsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tour;
    protected $hotel;
    protected $travelPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $this->tour = Tour::create([
            'title' => 'Bagan Temple Tour',
            'duration_days' => 3,
            'base_price' => 200.00,
            'location' => 'Bagan',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Bagan Heritage Hotel',
            'category' => '4-star',
            'location' => 'Bagan'
        ]);

        // Operating period: April 1 -> April 10
        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-10',
            'total_seats' => 5,
            'booked_seats' => 0
        ]);
    }

    /**
     * TEST: Travel Period Edit form renders correct inputs and updates successfully.
     */
    public function test_travel_period_edit_form_renders_and_updates(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.tours.travel-periods.edit', [$this->tour, $this->travelPeriod]));

        $response->assertStatus(200);
        $response->assertSee('2026-04-01');
        $response->assertSee('2026-04-10');
        $response->assertSee('5');

        $updateResponse = $this->actingAs($this->admin)
            ->put(route('admin.tours.travel-periods.update', [$this->tour, $this->travelPeriod]), [
                'start_date' => '2026-04-02',
                'end_date' => '2026-04-09',
                'total_seats' => 15
            ]);

        $updateResponse->assertRedirect(route('admin.tours.travel-periods.index', $this->tour));
        $this->travelPeriod->refresh();
        $this->assertEquals('2026-04-02', $this->travelPeriod->start_date->format('Y-m-d'));
        $this->assertEquals('2026-04-09', $this->travelPeriod->end_date->format('Y-m-d'));
        $this->assertEquals(15, $this->travelPeriod->total_seats);
    }

    /**
     * TEST: Dynamic seat capacity calculation (Total - Booked).
     */
    public function test_dynamic_seat_capacity_calculation(): void
    {
        // Initially 0 booked
        $this->assertEquals(0, $this->travelPeriod->booked_seats);
        $this->assertEquals(5, $this->travelPeriod->availableSeats());

        // Create booking with 2 adults (status pending)
        $booking1 = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '12345678',
            'num_persons' => 2,
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'base_price' => 200,
            'hotel_upgrade_price' => 0,
            'total_price' => 400,
            'status' => 'pending',
            'ref_code' => 'TEST-001'
        ]);

        $this->travelPeriod->refresh();
        $this->assertEquals(2, $this->travelPeriod->booked_seats);
        $this->assertEquals(3, $this->travelPeriod->availableSeats());

        // Create booking with 1 adult + 1 child aged 6 (status confirmed)
        $booking2 = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '87654321',
            'num_persons' => 1,
            'num_children' => 1,
            'child_ages' => '6',
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'base_price' => 200,
            'hotel_upgrade_price' => 0,
            'total_price' => 400,
            'status' => 'confirmed',
            'ref_code' => 'TEST-002'
        ]);

        $this->travelPeriod->refresh();
        // 2 (from booking 1) + 1 (adult from booking 2) + 1 (child from booking 2 aged 6) = 4 booked
        $this->assertEquals(4, $this->travelPeriod->booked_seats);
        $this->assertEquals(1, $this->travelPeriod->availableSeats());

        // Booking 2 is cancelled -> should release seats
        $booking2->update(['status' => 'cancelled']);
        $this->travelPeriod->refresh();
        $this->assertEquals(2, $this->travelPeriod->booked_seats);
    }

    /**
     * TEST: Blackout validation rules (Start > End, outside operating period, overlap).
     */
    public function test_blackout_validation_rules(): void
    {
        // 1. Invalid: Start > End
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tours.blackouts.store', $this->tour), [
                'start_date' => '2026-04-05',
                'end_date' => '2026-04-02'
            ]);
        $response->assertSessionHasErrors(['end_date']);

        // 2. Invalid: Outside operating period (earliest is April 1, latest is April 10)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tours.blackouts.store', $this->tour), [
                'start_date' => '2026-03-25',
                'end_date' => '2026-03-30'
            ]);
        $response->assertSessionHasErrors(['start_date']);

        // 3. Valid blackout period: April 4 to April 6
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tours.blackouts.store', $this->tour), [
                'start_date' => '2026-04-04',
                'end_date' => '2026-04-06'
            ]);
        $response->assertRedirect(route('admin.tours.travel-periods.index', $this->tour));
        $this->assertDatabaseHas('tour_blackout_periods', [
            'tour_id' => $this->tour->id,
            'start_date' => '2026-04-04 00:00:00',
            'end_date' => '2026-04-06 00:00:00'
        ]);

        // 4. Overlap: April 5 to April 8 overlaps with April 4 to April 6
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tours.blackouts.store', $this->tour), [
                'start_date' => '2026-04-05',
                'end_date' => '2026-04-08'
            ]);
        $response->assertSessionHasErrors(['start_date']);
    }

    /**
     * TEST: Overbooking protection on frontend booking creation.
     */
    public function test_frontend_overbooking_protection(): void
    {
        // Travel period capacity is 5.
        // Book 4 seats.
        Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'First Customer',
            'email' => 'first@example.com',
            'phone' => '11111111',
            'num_persons' => 4,
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'base_price' => 200,
            'hotel_upgrade_price' => 0,
            'total_price' => 800,
            'status' => 'pending',
            'ref_code' => 'TEST-003'
        ]);

        // Customer attempts to book 2 seats (total becomes 6 > 5)
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'adults' => 2,
            'children' => 0,
            'child_ages' => '',
            'hotel_id' => $this->hotel->id,
            'total_price' => 400,
            'customer_name' => 'Overbook Customer',
            'email' => 'overbook@example.com',
            'phone' => '22222222',
            'message' => 'Hello'
        ]);

        $response->assertSessionHasErrors(['seats']);
        $this->assertDatabaseMissing('bookings', [
            'customer_name' => 'Overbook Customer'
        ]);
    }

    /**
     * TEST: Booking during blackout date fails validation.
     */
    public function test_blackout_date_booking_is_blocked(): void
    {
        // Add blackout period: April 4 to April 6
        TourBlackoutPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => '2026-04-04',
            'end_date' => '2026-04-06'
        ]);

        // Attempt to book for April 5
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date' => '2026-04-05',
            'checkout_date' => '2026-04-07',
            'adults' => 1,
            'children' => 0,
            'child_ages' => '',
            'hotel_id' => $this->hotel->id,
            'total_price' => 200,
            'customer_name' => 'Blackout Customer',
            'email' => 'blackout@example.com',
            'phone' => '33333333',
            'message' => 'Hello'
        ]);

        $response->assertSessionHasErrors(['checkin_date']);
        $this->assertDatabaseMissing('bookings', [
            'customer_name' => 'Blackout Customer'
        ]);
    }

    /**
     * TEST: Admin capacity check when restoring cancelled booking.
     */
    public function test_admin_overbooking_protection_on_restore(): void
    {
        // Create active booking consuming 3 seats
        Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Active Customer',
            'email' => 'active@example.com',
            'phone' => '44444444',
            'num_persons' => 3,
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'base_price' => 200,
            'hotel_upgrade_price' => 0,
            'total_price' => 600,
            'status' => 'confirmed',
            'ref_code' => 'TEST-004'
        ]);

        // Create cancelled booking of 3 seats
        $cancelledBooking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Cancelled Customer',
            'email' => 'cancelled@example.com',
            'phone' => '55555555',
            'num_persons' => 3,
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'base_price' => 200,
            'hotel_upgrade_price' => 0,
            'total_price' => 600,
            'status' => 'cancelled',
            'ref_code' => 'TEST-005'
        ]);

        // Restoring cancelled booking (total seats would become 6 > 5)
        $response = $this->actingAs($this->admin)
            ->put(route('admin.bookings.update', $cancelledBooking), [
                'status' => 'confirmed'
            ]);

        $response->assertSessionHas('error');
        $cancelledBooking->refresh();
        $this->assertEquals('cancelled', $cancelledBooking->status);
    }

    /**
     * TEST: Tour detail overview card displays travel period and blackout dates.
     */
    public function test_tour_detail_overview_card_displays_travel_and_blackout_periods(): void
    {
        // Add blackout period: April 4 to April 6
        TourBlackoutPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => '2026-04-04',
            'end_date' => '2026-04-06'
        ]);

        $response = $this->get(route('tours.show', $this->tour));

        $response->assertStatus(200);
        
        // Assert travel period is shown in the new section
        $response->assertSee('Available Travel Period');
        $response->assertSee('01 Apr 2026');
        $response->assertSee('10 Apr 2026');

        // Assert blackout dates are shown in the new section
        $response->assertSee('Blackout Dates');
        $response->assertSee('04 Apr 2026');
        $response->assertSee('06 Apr 2026');
    }
}
