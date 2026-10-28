<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TravelPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompletePastBookingsTest extends TestCase
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
            'title' => 'Bagan Heritage Tour',
            'duration_days' => 3,
            'base_price' => 200.00,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Aureum Palace Bagan',
            'category' => '5-star',
            'location' => 'Bagan',
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'total_seats' => 20,
            'booked_seats' => 0,
        ]);
    }

    private function createBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Test Traveler',
            'nationality' => 'Myanmar',
            'email' => 'traveler@example.com',
            'phone' => '0912345678',
            'num_persons' => 2,
            'num_children' => 0,
            'checkin_date' => now()->subDays(5)->toDateString(),
            'checkout_date' => now()->subDays(3)->toDateString(),
            'base_price' => 200.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 400.00,
            'status' => 'paid',
            'ref_code' => 'MYG-'.uniqid(),
            'cancellation_token' => (string) Str::uuid(),
            'payment_receipt' => 'payment_receipts/test.png',
            'payment_uploaded_at' => now()->subDays(6),
        ], $overrides));
    }

    /**
     * TEST 1: An eligible (paid) booking whose tour end date is in the past becomes completed.
     */
    public function test_eligible_paid_booking_with_past_checkout_date_becomes_completed(): void
    {
        $booking = $this->createBooking([
            'status' => 'paid',
            'checkout_date' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('bookings:complete-past')
            ->expectsOutputToContain('Paid bookings checked: 1')
            ->expectsOutputToContain('Bookings marked completed: 1')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    /**
     * TEST 2: An eligible booking whose tour end date is today or in the future does not become completed.
     */
    public function test_paid_booking_with_today_or_future_checkout_date_does_not_become_completed(): void
    {
        $todayBooking = $this->createBooking([
            'status' => 'paid',
            'checkout_date' => now()->toDateString(),
        ]);

        $futureBooking = $this->createBooking([
            'status' => 'paid',
            'checkout_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->artisan('bookings:complete-past')
            ->expectsOutputToContain('Paid bookings checked: 2')
            ->expectsOutputToContain('Bookings marked completed: 0')
            ->expectsOutputToContain('Bookings skipped: 2')
            ->assertExitCode(0);

        $todayBooking->refresh();
        $futureBooking->refresh();

        $this->assertEquals('paid', $todayBooking->status);
        $this->assertEquals('paid', $futureBooking->status);
    }

    /**
     * TEST 3: A pending booking is not marked completed even if its tour end date is in the past.
     */
    public function test_pending_booking_is_not_marked_completed(): void
    {
        $pendingBooking = $this->createBooking([
            'status' => 'pending',
            'checkout_date' => now()->subDays(5)->toDateString(),
        ]);

        $this->artisan('bookings:complete-past')
            ->expectsOutputToContain('Paid bookings checked: 0')
            ->expectsOutputToContain('Bookings marked completed: 0')
            ->assertExitCode(0);

        $pendingBooking->refresh();
        $this->assertEquals('pending', $pendingBooking->status);
    }

    /**
     * TEST 4: A cancelled booking remains cancelled even if its tour end date is in the past.
     */
    public function test_cancelled_booking_remains_cancelled(): void
    {
        $cancelledBooking = $this->createBooking([
            'status' => 'cancelled',
            'cancelled_by' => 'customer',
            'cancel_reason' => 'Changed plans',
            'cancelled_at' => now()->subDays(6),
            'checkout_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->artisan('bookings:complete-past')
            ->expectsOutputToContain('Paid bookings checked: 0')
            ->expectsOutputToContain('Bookings marked completed: 0')
            ->assertExitCode(0);

        $cancelledBooking->refresh();
        $this->assertEquals('cancelled', $cancelledBooking->status);
        $this->assertEquals('customer', $cancelledBooking->cancelled_by);
        $this->assertEquals('Changed plans', $cancelledBooking->cancel_reason);
    }

    /**
     * TEST 5: Running the completion command twice is idempotent and does not produce unwanted changes.
     */
    public function test_running_completion_command_twice_is_idempotent(): void
    {
        $booking = $this->createBooking([
            'status' => 'paid',
            'checkout_date' => now()->subDays(2)->toDateString(),
        ]);

        // First run
        $this->artisan('bookings:complete-past')
            ->expectsOutputToContain('Bookings marked completed: 1')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);

        // Second run
        $this->artisan('bookings:complete-past')
            ->expectsOutputToContain('Bookings marked completed: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    /**
     * TEST 6: Existing booking reference codes, payment records, and cancellation info are preserved.
     */
    public function test_existing_booking_details_are_preserved_after_completion(): void
    {
        $refCode = 'MYG-20261010-TEST';
        $paymentReceipt = 'payment_receipts/receipt123.jpg';

        $booking = $this->createBooking([
            'status' => 'paid',
            'ref_code' => $refCode,
            'payment_receipt' => $paymentReceipt,
            'checkout_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->artisan('bookings:complete-past')->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
        $this->assertEquals($refCode, $booking->ref_code);
        $this->assertEquals($paymentReceipt, $booking->payment_receipt);
    }

    /**
     * TEST 7: The admin panel displays completed bookings correctly on list and show pages.
     */
    public function test_admin_panel_displays_completed_bookings_correctly(): void
    {
        $booking = $this->createBooking([
            'status' => 'completed',
            'checkout_date' => now()->subDays(2)->toDateString(),
            'customer_name' => 'Completed Tour Guest',
        ]);

        // Admin index list page
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.bookings.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Completed Tour Guest');
        $indexResponse->assertSee('Completed');

        // Admin show page
        $showResponse = $this->actingAs($this->admin)->get(route('admin.bookings.show', $booking));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Completed Tour Guest');
        $showResponse->assertSee('Completed');
        $showResponse->assertSee('Tour Completed');
    }
}
