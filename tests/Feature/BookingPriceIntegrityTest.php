<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Hotel;
use App\Models\HotelSeasonPrice;
use App\Models\SeasonPeriod;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPriceIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected Tour $tour;
    protected TravelPeriod $travelPeriod;
    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tour = Tour::create([
            'title'         => 'Bagan Paradise Tour',
            'duration_days' => 4,
            'base_price'    => 200.00,
            'location'      => 'Bagan',
            'status'        => 'active',
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id'     => $this->tour->id,
            'start_date'  => '2026-10-01',
            'end_date'    => '2026-10-31',
            'total_seats' => 20,
            'booked_seats' => 0,
        ]);

        $this->hotel = Hotel::create([
            'name'     => 'Grand Bagan Hotel',
            'category' => '4-star',
            'location' => 'Bagan',
        ]);

        // Attach hotel to tour
        $this->tour->hotels()->attach($this->hotel->id);

        // Peak Season from Oct 1 to Oct 15
        SeasonPeriod::create([
            'name'       => 'October Peak',
            'season'     => 'peak',
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-15',
        ]);

        // Hotel peak season upgrade price = $50
        HotelSeasonPrice::create([
            'hotel_id'      => $this->hotel->id,
            'season'        => 'peak',
            'upgrade_price' => 50.00,
        ]);

        // Hotel normal season upgrade price = $0
        HotelSeasonPrice::create([
            'hotel_id'      => $this->hotel->id,
            'season'        => 'normal',
            'upgrade_price' => 0.00,
        ]);
    }

    /**
     * Test 1 — Normal valid booking calculates and stores exact expected price.
     */
    public function test_normal_booking_stores_server_calculated_price(): void
    {
        // Date 2026-10-20 (Normal season, $0 hotel upgrade)
        // 2 adults -> Base price = $200 * 2 = $400
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-10-20',
            'checkout_date' => '2026-10-23',
            'adults'        => 2,
            'children'      => 0,
            'hotel_id'      => $this->hotel->id,
            'customer_name' => 'John Normal',
            'email'         => 'john@example.com',
            'phone'         => '0912345678',
            'total_price'   => 400.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'John Normal',
            'total_price'   => 400.00,
        ]);
    }

    /**
     * Test 2 — Tampered low price (client sends total_price = 1) is ignored and server-calculated price is stored.
     */
    public function test_tampered_low_price_is_recalculated_by_server(): void
    {
        // Date 2026-10-20 (Normal season, $0 upgrade)
        // 2 adults -> expected price = 200 * 2 = 400
        // Client sends manipulated total_price = 1
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-10-20',
            'checkout_date' => '2026-10-23',
            'adults'        => 2,
            'children'      => 0,
            'hotel_id'      => $this->hotel->id,
            'customer_name' => 'Low Price Hacker',
            'email'         => 'hacker@example.com',
            'phone'         => '0912345678',
            'total_price'   => 1.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', [
            'total_price' => 1.00,
        ]);
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Low Price Hacker',
            'total_price'   => 400.00,
        ]);
    }

    /**
     * Test 3 — Tampered high price (client sends total_price = 999999) is ignored and server-calculated price is stored.
     */
    public function test_tampered_high_price_is_recalculated_by_server(): void
    {
        // Date 2026-10-20 (Normal season, $0 upgrade)
        // 1 adult -> expected price = 200
        // Client sends manipulated total_price = 999999
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-10-20',
            'checkout_date' => '2026-10-23',
            'adults'        => 1,
            'children'      => 0,
            'hotel_id'      => $this->hotel->id,
            'customer_name' => 'High Price Tester',
            'email'         => 'high@example.com',
            'phone'         => '0912345678',
            'total_price'   => 999999.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', [
            'total_price' => 999999.00,
        ]);
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'High Price Tester',
            'total_price'   => 200.00,
        ]);
    }

    /**
     * Test 4 — Hotel upgrade price is correctly included in server-side price calculation.
     */
    public function test_hotel_upgrade_included_in_server_calculation(): void
    {
        // Date: 2026-10-05 (Peak season -> $50 hotel upgrade per person)
        // Base price = $200, Hotel upgrade = $50 -> Price per person = $250
        // 2 adults -> Expected total = $500
        // Client sends tampered total_price = 10
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-10-05',
            'checkout_date' => '2026-10-08',
            'adults'        => 2,
            'children'      => 0,
            'hotel_id'      => $this->hotel->id,
            'customer_name' => 'Hotel Upgrade Customer',
            'email'         => 'upgrade@example.com',
            'phone'         => '0912345678',
            'total_price'   => 10.00,
        ]);

        $response->assertRedirect();
        $booking = Booking::where('customer_name', 'Hotel Upgrade Customer')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(50.00, $booking->hotel_upgrade_price);
        $this->assertEquals(500.00, $booking->total_price);
    }

    /**
     * Test 5 — Adult and child traveller count pricing rules (child < 5 free, child >= 5 charged adult fare).
     */
    public function test_traveller_count_adult_and_child_pricing_rules(): void
    {
        // Date 2026-10-20 (Normal season, $0 upgrade)
        // 1 adult ($200) + 1 child age 3 (free) + 1 child age 7 ($200) = 2 payable travelers = $400
        // Client sends manipulated total_price = 1
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-10-20',
            'checkout_date' => '2026-10-23',
            'adults'        => 1,
            'children'      => 2,
            'child_ages'    => '3,7',
            'hotel_id'      => $this->hotel->id,
            'customer_name' => 'Family Customer',
            'email'         => 'family@example.com',
            'phone'         => '0912345678',
            'total_price'   => 1.00,
        ]);

        $response->assertRedirect();
        $booking = Booking::where('customer_name', 'Family Customer')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(400.00, $booking->total_price);
    }

    /**
     * Test 6 — Booking Success page displays the server-calculated/stored total price.
     */
    public function test_booking_success_page_displays_server_calculated_price(): void
    {
        // Date 2026-10-20 (Normal season, $0 upgrade) -> 2 adults = $400
        // Submit tampered low price = 1
        $response = $this->post(route('booking.store', $this->tour), [
            'checkin_date'  => '2026-10-20',
            'checkout_date' => '2026-10-23',
            'adults'        => 2,
            'children'      => 0,
            'hotel_id'      => $this->hotel->id,
            'customer_name' => 'Success Page Test',
            'email'         => 'success@example.com',
            'phone'         => '0912345678',
            'total_price'   => 1.00,
        ]);

        $booking = Booking::where('customer_name', 'Success Page Test')->first();
        $response->assertRedirect(route('booking.success', ['booking' => $booking->id]));

        $successResponse = $this->get(route('booking.success', ['booking' => $booking->id]));
        $successResponse->assertStatus(200);
        $successResponse->assertSee('$400.00');
        $successResponse->assertDontSee('$1.00');
    }
}
