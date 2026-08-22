<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingSearchAndPaginationTest extends TestCase
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
            'title' => 'Yangon Adventure',
            'duration_days' => 2,
            'base_price' => 120.00,
            'location' => 'Yangon',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Rose Garden Hotel',
            'category' => '4-star',
            'location' => 'Yangon'
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'total_seats' => 20,
            'booked_seats' => 0
        ]);
    }

    /**
     * Create a dummy booking helper.
     */
    protected function createBooking(array $attributes = []): Booking
    {
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $booking = Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0912345678',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(5)->toDateString(),
            'checkout_date' => now()->addDays(7)->toDateString(),
            'base_price' => 120.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 240.00,
            'status' => 'pending',
            'ref_code' => 'MYG-' . rand(10000, 99999),
        ], $attributes));

        if ($createdAt) {
            $booking->created_at = $createdAt;
            $booking->save();
        }

        return $booking;
    }

    /**
     * Test admin can access booking list page and view pagination.
     */
    public function test_admin_can_access_bookings_list_with_pagination(): void
    {
        // Create 15 bookings
        for ($i = 0; $i < 15; $i++) {
            $this->createBooking([
                'ref_code' => "MYG-REF" . sprintf("%02d", $i),
                'created_at' => now()->addMinutes($i)
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('Booking Management');
        
        // Should only see 10 bookings on the first page
        // Let's assert that page 1 shows MYG-REF14 down to MYG-REF05 (latest first)
        $response->assertSee('MYG-REF14');
        $response->assertSee('MYG-REF05');
        $response->assertDontSee('MYG-REF04');

        // Go to page 2
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('MYG-REF04');
        $responsePage2->assertSee('MYG-REF00');
        $responsePage2->assertDontSee('MYG-REF14');
    }

    /**
     * Test searching by booking reference code.
     */
    public function test_search_by_ref_code(): void
    {
        $booking1 = $this->createBooking(['ref_code' => 'MYG-11111']);
        $booking2 = $this->createBooking(['ref_code' => 'MYG-22222']);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'MYG-11111']));

        $response->assertStatus(200);
        $response->assertSee('MYG-11111');
        $response->assertDontSee('MYG-22222');
    }

    /**
     * Test searching by customer name (case-insensitive).
     */
    public function test_search_by_customer_name_case_insensitive(): void
    {
        $booking1 = $this->createBooking(['customer_name' => 'Jenny Smith']);
        $booking2 = $this->createBooking(['customer_name' => 'John Doe']);

        // Search lowercase
        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'jenny']));

        $response->assertStatus(200);
        $response->assertSee('Jenny Smith');
        $response->assertDontSee('John Doe');

        // Search uppercase
        $response2 = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'JENNY']));
        $response2->assertStatus(200);
        $response2->assertSee('Jenny Smith');
    }

    /**
     * Test searching by customer email.
     */
    public function test_search_by_email(): void
    {
        $booking1 = $this->createBooking(['email' => 'jenny@example.com']);
        $booking2 = $this->createBooking(['email' => 'john@example.com']);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'jenny@example.com']));

        $response->assertStatus(200);
        $response->assertSee('jenny@example.com');
        $response->assertDontSee('john@example.com');
    }

    /**
     * Test searching by customer phone.
     */
    public function test_search_by_phone(): void
    {
        $booking1 = $this->createBooking(['phone' => '099999999']);
        $booking2 = $this->createBooking(['phone' => '091111111']);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => '099999999']));

        $response->assertStatus(200);
        $response->assertSee('099999999');
        $response->assertDontSee('091111111');
    }

    /**
     * Test search and pagination together.
     */
    public function test_search_and_pagination_together(): void
    {
        // Create 15 bookings for "Jenny"
        for ($i = 0; $i < 15; $i++) {
            $this->createBooking([
                'customer_name' => "Jenny Smith $i",
                'ref_code' => "JENNY-" . sprintf("%02d", $i),
                'created_at' => now()->addMinutes($i)
            ]);
        }

        // Create 5 other bookings
        for ($i = 0; $i < 5; $i++) {
            $this->createBooking([
                'customer_name' => "John Doe $i",
                'ref_code' => "JOHN-" . sprintf("%02d", $i),
                'created_at' => now()->addMinutes($i + 15)
            ]);
        }

        // Search for Jenny - page 1
        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'Jenny']));
        $response->assertStatus(200);
        $response->assertSee('JENNY-14');
        $response->assertSee('JENNY-05');
        $response->assertDontSee('JENNY-04');
        $response->assertDontSee('JOHN-');

        // Check if pagination links preserve the search query
        $response->assertSee('search=Jenny');
        $response->assertSee('page=2');

        // Go to page 2 with search
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'Jenny', 'page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('JENNY-04');
        $responsePage2->assertSee('JENNY-00');
        $responsePage2->assertDontSee('JENNY-14');
        $responsePage2->assertDontSee('JOHN-');
    }

    /**
     * Test empty results message is displayed when no records match search.
     */
    public function test_empty_search_results(): void
    {
        $this->createBooking(['customer_name' => 'John Doe']);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['search' => 'NonExistent']));

        $response->assertStatus(200);
        $response->assertSee('No bookings found.');
        $response->assertDontSee('John Doe');
    }
}
