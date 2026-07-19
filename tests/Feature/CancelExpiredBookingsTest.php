<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCancelledMail;
use Tests\TestCase;

class CancelExpiredBookingsTest extends TestCase
{
    use RefreshDatabase;

    protected $tour;
    protected $hotel;
    protected $travelPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tour = Tour::create([
            'title' => 'Yangon City Tour',
            'duration_days' => 3,
            'base_price' => 150.00,
            'location' => 'Yangon',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Lotte Hotel Yangon',
            'category' => '5-star',
            'location' => 'Yangon'
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(13),
            'total_seats' => 10,
            'booked_seats' => 0
        ]);
    }

    private function createBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Test Customer',
            'nationality' => 'Myanmar',
            'email' => 'test@example.com',
            'phone' => '091234567',
            'num_persons' => 1,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 150.00,
            'status' => 'confirmed',
            'cancellation_token' => uniqid(),
            'ref_code' => 'MYG-' . uniqid(),
        ], $overrides));
    }

    /**
     * TEST 1: status = confirmed, payment_deadline = yesterday gets cancelled and email sent.
     */
    public function test_expired_confirmed_booking_is_cancelled_and_email_is_sent(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'status' => 'confirmed',
            'payment_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 1')
            ->expectsOutputToContain('Bookings cancelled: 1')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();

        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('system', $booking->cancelled_by);
        $this->assertEquals('Payment deadline expired', $booking->cancel_reason);
        $this->assertNotNull($booking->cancelled_at);

        Mail::assertSent(BookingCancelledMail::class, function ($mail) use ($booking) {
            $mail->build();
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id &&
                   $mail->subject === 'Booking Cancelled - Payment Deadline Expired';
        });
    }

    /**
     * TEST 2: status = confirmed, payment_deadline = tomorrow remains confirmed, no email sent.
     */
    public function test_future_confirmed_booking_remains_confirmed_and_no_email_sent(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'status' => 'confirmed',
            'payment_deadline' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 1')
            ->expectsOutputToContain('Bookings cancelled: 0')
            ->expectsOutputToContain('Bookings skipped: 1')
            ->assertExitCode(0);

        $booking->refresh();

        $this->assertEquals('confirmed', $booking->status);
        $this->assertNull($booking->cancelled_by);

        Mail::assertNothingSent();
    }

    /**
     * TEST 3: status = payment_uploaded, payment_deadline = yesterday remains payment_uploaded, no automatic cancellation.
     */
    public function test_expired_payment_uploaded_booking_remains_unchanged(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'status' => 'payment_uploaded',
            'payment_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 0')
            ->expectsOutputToContain('Bookings cancelled: 0')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();

        $this->assertEquals('payment_uploaded', $booking->status);
        Mail::assertNothingSent();
    }

    /**
     * TEST 4: status = paid, payment_deadline = yesterday remains paid, no automatic cancellation.
     */
    public function test_expired_paid_booking_remains_unchanged(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'status' => 'paid',
            'payment_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 0')
            ->expectsOutputToContain('Bookings cancelled: 0')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();

        $this->assertEquals('paid', $booking->status);
        Mail::assertNothingSent();
    }

    /**
     * TEST 5: status = cancelled, payment_deadline = yesterday remains cancelled, no duplicate email.
     */
    public function test_expired_already_cancelled_booking_remains_unchanged_and_no_email_is_sent(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'status' => 'cancelled',
            'cancelled_by' => 'customer',
            'cancel_reason' => 'User cancelled',
            'cancelled_at' => now()->subDay(),
            'payment_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 0')
            ->expectsOutputToContain('Bookings cancelled: 0')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();

        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('customer', $booking->cancelled_by);
        Mail::assertNothingSent();
    }

    /**
     * TEST 6: Run twice: first cancels, second does nothing. No duplicate email.
     */
    public function test_running_command_twice_is_idempotent(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'status' => 'confirmed',
            'payment_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        // First Run
        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 1')
            ->expectsOutputToContain('Bookings cancelled: 1')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        Mail::assertSentCount(1);

        // Second Run
        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 0')
            ->expectsOutputToContain('Bookings cancelled: 0')
            ->expectsOutputToContain('Bookings skipped: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        // Mail count remains 1
        Mail::assertSentCount(1);
    }
}
