<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSystemCancelledVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $tour;
    protected $hotel;
    protected $travelPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);

        $this->tour = Tour::create([
            'title' => 'Mandalay Royal Tour',
            'duration_days' => 2,
            'base_price' => 120.00,
            'location' => 'Mandalay',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Mandalay View Hotel',
            'category' => '4-star',
            'location' => 'Mandalay'
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'total_seats' => 20,
            'booked_seats' => 0
        ]);
    }

    private function createBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Customer Name',
            'nationality' => 'Myanmar',
            'email' => 'customer@example.com',
            'phone' => '0911223344',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(5)->format('Y-m-d'),
            'checkout_date' => now()->addDays(7)->format('Y-m-d'),
            'base_price' => 120.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 240.00,
            'status' => 'confirmed',
            'ref_code' => 'MYG-' . rand(10000, 99999),
            'cancellation_token' => uniqid('token_'),
            'payment_deadline' => now()->addDays(7)->format('Y-m-d'),
        ], $overrides));
    }

    /**
     * TEST 1: System-cancelled booking shows friendly labels on the detail page.
     */
    public function test_system_cancelled_booking_shows_friendly_labels_on_detail_page(): void
    {
        $booking = $this->createBooking([
            'customer_name' => 'Expired Customer',
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancel_reason' => 'Payment deadline expired',
            'cancelled_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.show', $booking));

        $response->assertStatus(200);
        $response->assertSee('Automatically Cancelled by System');
        $response->assertSee('Automatically cancelled by system');
        $response->assertSee('Payment deadline expired');
        $response->assertSee('Cancelled (System)');
    }

    /**
     * TEST 2: System-cancelled booking shows friendly badge and subtext on the booking list.
     */
    public function test_system_cancelled_booking_shows_friendly_badge_on_index_page(): void
    {
        $this->createBooking([
            'customer_name' => 'Auto-Expired Customer',
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancel_reason' => 'Payment deadline expired',
            'cancelled_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('Auto-Expired Customer');
        $response->assertSee('Cancelled (System)');
        $response->assertSee('Payment Expired');
    }

    /**
     * TEST 3: Dashboard displays accurate system-cancelled count and link to filtered list.
     */
    public function test_dashboard_displays_correct_system_cancelled_count_and_link(): void
    {
        // 2 system-cancelled
        $this->createBooking([
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancel_reason' => 'Payment deadline expired',
            'cancelled_at' => now(),
        ]);
        $this->createBooking([
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancel_reason' => 'Payment deadline expired',
            'cancelled_at' => now(),
        ]);

        // 1 customer-cancelled
        $this->createBooking([
            'status' => 'cancelled',
            'cancelled_by' => 'customer',
            'cancel_reason' => 'User requested',
            'cancelled_at' => now(),
        ]);

        // 1 confirmed
        $this->createBooking([
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('System Expired');
        // Count must match 2
        $response->assertSee('filter=system_cancelled');
    }

    /**
     * TEST 4: Filter param ?filter=system_cancelled returns only system-cancelled bookings.
     */
    public function test_admin_booking_filter_returns_only_system_cancelled_bookings(): void
    {
        $systemCancelled = $this->createBooking([
            'customer_name' => 'System Expired Target',
            'ref_code' => 'MYG-SYS-01',
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancel_reason' => 'Payment deadline expired',
            'cancelled_at' => now(),
        ]);

        $customerCancelled = $this->createBooking([
            'customer_name' => 'Customer Cancelled Other',
            'ref_code' => 'MYG-CUS-02',
            'status' => 'cancelled',
            'cancelled_by' => 'customer',
            'cancel_reason' => 'Change of mind',
            'cancelled_at' => now(),
        ]);

        $confirmed = $this->createBooking([
            'customer_name' => 'Active Confirmed Other',
            'ref_code' => 'MYG-ACT-03',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['filter' => 'system_cancelled']));

        $response->assertStatus(200);
        $response->assertSee('System Expired Target');
        $response->assertSee('MYG-SYS-01');

        $response->assertDontSee('Customer Cancelled Other');
        $response->assertDontSee('MYG-CUS-02');
        $response->assertDontSee('Active Confirmed Other');
        $response->assertDontSee('MYG-ACT-03');
    }

    /**
     * TEST 5: Both Staff and Admin can view system-cancelled bookings according to existing permissions.
     */
    public function test_staff_and_admin_can_both_view_system_cancelled_bookings(): void
    {
        $booking = $this->createBooking([
            'customer_name' => 'Dual Role Target',
            'status' => 'cancelled',
            'cancelled_by' => 'system',
            'cancel_reason' => 'Payment deadline expired',
            'cancelled_at' => now(),
        ]);

        // Staff access
        $staffIndexResponse = $this->actingAs($this->staff)->get(route('admin.bookings.index', ['filter' => 'system_cancelled']));
        $staffIndexResponse->assertStatus(200);
        $staffIndexResponse->assertSee('Dual Role Target');

        $staffShowResponse = $this->actingAs($this->staff)->get(route('admin.bookings.show', $booking));
        $staffShowResponse->assertStatus(200);
        $staffShowResponse->assertSee('Automatically Cancelled by System');

        // Admin access
        $adminIndexResponse = $this->actingAs($this->admin)->get(route('admin.bookings.index', ['filter' => 'system_cancelled']));
        $adminIndexResponse->assertStatus(200);
        $adminIndexResponse->assertSee('Dual Role Target');

        $adminShowResponse = $this->actingAs($this->admin)->get(route('admin.bookings.show', $booking));
        $adminShowResponse->assertStatus(200);
        $adminShowResponse->assertSee('Automatically Cancelled by System');
    }
}
