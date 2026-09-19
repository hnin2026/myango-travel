<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tour;
    protected $hotel;
    protected $travelPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->tour = Tour::create([
            'title' => 'Bagan Temple Explorer',
            'duration_days' => 3,
            'base_price' => 200.00,
            'location' => 'Bagan',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Bagan Hotel',
            'category' => '4-star',
            'location' => 'Bagan'
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'total_seats' => 20,
            'booked_seats' => 0
        ]);
    }

    protected function createBooking(array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0912345678',
            'num_persons' => 2,
            'num_children' => 0,
            'child_ages' => null,
            'checkin_date' => now()->addDays(5)->toDateString(),
            'checkout_date' => now()->addDays(8)->toDateString(),
            'base_price' => 200.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 400.00,
            'status' => 'pending',
            'ref_code' => 'MYG-' . rand(10000, 99999),
        ], $attributes));
    }

    /**
     * Test 1: Booking with adults only displays correct Adults, Children, and Total counts on admin list.
     */
    public function test_admin_booking_list_displays_adults_only_booking(): void
    {
        $this->createBooking([
            'customer_name' => 'Adults Only Guest',
            'num_persons' => 2,
            'num_children' => 0,
            'child_ages' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('Adults Only Guest');
        $response->assertSee('Adults: 2');
        $response->assertSee('Children: 0');
        $response->assertSee('Total: 2');
    }

    /**
     * Test 2: Booking with adults and children displays both adult and child counts, and total on admin list.
     */
    public function test_admin_booking_list_displays_adults_and_children_booking(): void
    {
        $this->createBooking([
            'customer_name' => 'Family Guest',
            'num_persons' => 2,
            'num_children' => 2,
            'child_ages' => '5, 8',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('Family Guest');
        $response->assertSee('Adults: 2');
        $response->assertSee('Children: 2');
        $response->assertSee('Total: 4');
    }

    /**
     * Test 3: Booking with multiple children preserves all child ages on booking detail page.
     */
    public function test_admin_booking_detail_displays_child_ages(): void
    {
        $booking = $this->createBooking([
            'customer_name' => 'Multi Child Family',
            'num_persons' => 2,
            'num_children' => 3,
            'child_ages' => '4, 7, 10',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.show', $booking));

        $response->assertStatus(200);
        $response->assertSee('Multi Child Family');
        $response->assertSee('2 pax'); // Adults
        $response->assertSee('3 pax'); // Children
        $response->assertSee('4, 7, 10'); // Child ages preserved
    }

    /**
     * Test 4: Verify the admin booking list does not display the adult count as though it were the total number of persons.
     */
    public function test_admin_booking_list_does_not_display_adult_count_alone_as_total(): void
    {
        // Booking with 2 adults and 3 children (Total 5 persons)
        $this->createBooking([
            'customer_name' => 'Disambiguation Guest',
            'num_persons' => 2,
            'num_children' => 3,
            'child_ages' => '3, 6, 9',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('Disambiguation Guest');
        // Total must be 5, not just 2 (the adult count)
        $response->assertSee('Total: 5');
        $response->assertSee('Adults: 2');
        $response->assertSee('Children: 3');
    }
}
