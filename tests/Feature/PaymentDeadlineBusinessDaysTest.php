<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentRequiredMail;
use App\Mail\PaymentReminderMail;
use App\Mail\BookingCancelledMail;
use Carbon\Carbon;
use Tests\TestCase;

class PaymentDeadlineBusinessDaysTest extends TestCase
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
            'title' => 'Inle Lake Explorer',
            'duration_days' => 3,
            'base_price' => 200.00,
            'location' => 'Inle',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Inle Heritage Resort',
            'category' => '4-star',
            'location' => 'Inle'
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-25',
            'total_seats' => 20,
            'booked_seats' => 0
        ]);
    }

    private function createPendingBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Traveler',
            'nationality' => 'Myanmar',
            'email' => 'traveler@example.com',
            'phone' => '0912345678',
            'num_persons' => 2,
            'checkin_date' => '2026-11-20',
            'checkout_date' => '2026-11-23',
            'base_price' => 200.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 400.00,
            'status' => 'pending',
            'ref_code' => 'MYG-' . rand(10000, 99999),
            'cancellation_token' => uniqid('token_'),
            'payment_deadline' => null,
        ], $overrides));
    }

    /**
     * TEST 1: Booking confirmed on a Monday gets a payment deadline of the following Wednesday.
     * (Counts 7 business days starting Tuesday: Tue, Wed, Thu, Fri, Mon, Tue, Wed).
     */
    public function test_confirmation_on_monday_sets_deadline_to_following_wednesday(): void
    {
        Mail::fake();

        // Travel to Monday, 19 October 2026
        $monday = Carbon::parse('2026-10-19 10:00:00');
        $this->travelTo($monday);

        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)->put(route('admin.bookings.update', $booking), [
            'status' => 'confirmed'
        ]);

        $booking->refresh();

        // Expected: Wednesday, 28 October 2026
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals('2026-10-28', $booking->payment_deadline->format('Y-m-d'));
        $this->assertEquals('Wednesday', $booking->payment_deadline->format('l'));

        Mail::assertSent(PaymentRequiredMail::class, function ($mail) {
            return $mail->booking->payment_deadline->format('Y-m-d') === '2026-10-28';
        });
    }

    /**
     * TEST 2: Booking confirmed on a Friday gets a payment deadline of the Tuesday after next.
     * (Counts 7 business days starting Monday: Mon, Tue, Wed, Thu, Fri, Mon, Tue).
     */
    public function test_confirmation_on_friday_sets_deadline_to_tuesday_after_next(): void
    {
        Mail::fake();

        // Travel to Friday, 23 October 2026
        $friday = Carbon::parse('2026-10-23 14:00:00');
        $this->travelTo($friday);

        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)->put(route('admin.bookings.update', $booking), [
            'status' => 'confirmed'
        ]);

        $booking->refresh();

        // Expected: Tuesday, 3 November 2026 (crosses 2 weekends and month boundary)
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals('2026-11-03', $booking->payment_deadline->format('Y-m-d'));
        $this->assertEquals('Tuesday', $booking->payment_deadline->format('l'));
    }

    /**
     * TEST 3: Business days calculation excludes Saturdays and Sundays.
     */
    public function test_business_days_calculation_excludes_weekends(): void
    {
        // Wednesday, 21 October 2026
        // Day 1: Thu (22), Day 2: Fri (23), [Sat (24), Sun (25) excluded],
        // Day 3: Mon (26), Day 4: Tue (27), Day 5: Wed (28), Day 6: Thu (29), Day 7: Fri (30)
        $deadline = Booking::calculatePaymentDeadline('2026-10-21');
        $this->assertEquals('2026-10-30', $deadline->format('Y-m-d'));
        $this->assertEquals('Friday', $deadline->format('l'));
    }

    /**
     * TEST 4: Confirmation near the end of a month correctly crosses the month boundary.
     */
    public function test_confirmation_near_end_of_month_correctly_crosses_month_boundary(): void
    {
        // Monday, 26 October 2026
        // Day 1: Tue (27), Day 2: Wed (28), Day 3: Thu (29), Day 4: Fri (30),
        // [Sat 31 Oct, Sun 1 Nov excluded],
        // Day 5: Mon (2 Nov), Day 6: Tue (3 Nov), Day 7: Wed (4 Nov)
        $deadline = Booking::calculatePaymentDeadline('2026-10-26');
        $this->assertEquals('2026-11-04', $deadline->format('Y-m-d'));
        $this->assertEquals('Wednesday', $deadline->format('l'));
    }

    /**
     * TEST 5: Confirmation near the end of a year correctly crosses the year boundary.
     */
    public function test_confirmation_near_end_of_year_correctly_crosses_year_boundary(): void
    {
        // Thursday, 31 December 2026
        // Day 1: Fri 1 Jan 2027, [Sat 2 Jan, Sun 3 Jan excluded],
        // Day 2: Mon 4 Jan, Day 3: Tue 5 Jan, Day 4: Wed 6 Jan, Day 5: Thu 7 Jan, Day 6: Fri 8 Jan,
        // [Sat 9 Jan, Sun 10 Jan excluded],
        // Day 7: Mon 11 Jan 2027
        $deadline = Booking::calculatePaymentDeadline('2026-12-31');
        $this->assertEquals('2027-01-11', $deadline->format('Y-m-d'));
        $this->assertEquals('Monday', $deadline->format('l'));
    }

    /**
     * TEST 6: A booking is NOT cancelled on its deadline date.
     */
    public function test_booking_is_not_cancelled_on_deadline_date(): void
    {
        Mail::fake();

        $deadline = '2026-10-28';
        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Active On Deadline',
            'nationality' => 'Myanmar',
            'email' => 'active@example.com',
            'phone' => '0911111111',
            'num_persons' => 2,
            'checkin_date' => '2026-11-20',
            'checkout_date' => '2026-11-23',
            'base_price' => 200.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 400.00,
            'status' => 'confirmed',
            'ref_code' => 'MYG-DEADLINE-01',
            'cancellation_token' => uniqid(),
            'payment_deadline' => $deadline,
        ]);

        // Run scheduler during the deadline date (e.g. 15:30:00 on 2026-10-28)
        $this->travelTo(Carbon::parse('2026-10-28 15:30:00'));

        $this->artisan('bookings:cancel-expired')
            ->expectsOutputToContain('Expired bookings checked: 1')
            ->expectsOutputToContain('Bookings cancelled: 0')
            ->expectsOutputToContain('Bookings skipped: 1')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertNull($booking->cancelled_at);
        Mail::assertNothingSent();
    }

    /**
     * TEST 7: A booking IS automatically cancelled strictly after the deadline date has passed.
     */
    public function test_booking_is_cancelled_after_deadline_has_passed(): void
    {
        Mail::fake();

        $deadline = '2026-10-28';
        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Expired Past Deadline',
            'nationality' => 'Myanmar',
            'email' => 'expired@example.com',
            'phone' => '0922222222',
            'num_persons' => 2,
            'checkin_date' => '2026-11-20',
            'checkout_date' => '2026-11-23',
            'base_price' => 200.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 400.00,
            'status' => 'confirmed',
            'ref_code' => 'MYG-DEADLINE-02',
            'cancellation_token' => uniqid(),
            'payment_deadline' => $deadline,
        ]);

        // Run scheduler on the day after the deadline (2026-10-29 00:05:00)
        $this->travelTo(Carbon::parse('2026-10-29 00:05:00'));

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

        Mail::assertSent(BookingCancelledMail::class);
    }

    /**
     * TEST 8: Payment reminder scheduling relative to the new business-day deadline.
     * Deadline is Wednesday 2026-10-28; reminder fires 2 calendar days before (Monday 2026-10-26).
     */
    public function test_payment_reminder_relative_to_business_day_deadline(): void
    {
        Mail::fake();

        $deadline = '2026-10-28'; // Wednesday
        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Reminder Candidate',
            'nationality' => 'Myanmar',
            'email' => 'candidate@example.com',
            'phone' => '0933333333',
            'num_persons' => 2,
            'checkin_date' => '2026-11-20',
            'checkout_date' => '2026-11-23',
            'base_price' => 200.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 400.00,
            'status' => 'confirmed',
            'ref_code' => 'MYG-REMIND-01',
            'cancellation_token' => uniqid(),
            'payment_deadline' => $deadline,
            'payment_reminder_sent_at' => null,
        ]);

        // On Sunday Oct 25 (3 calendar days before): no reminder
        $this->travelTo(Carbon::parse('2026-10-25 08:00:00'));
        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Payment reminders sent: 0');
        $this->assertNull($booking->refresh()->payment_reminder_sent_at);
        Mail::assertNothingSent();

        // On Monday Oct 26 (exactly 2 calendar days before): reminder fires
        $this->travelTo(Carbon::parse('2026-10-26 08:00:00'));
        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Payment reminders sent: 1');
        $this->assertNotNull($booking->refresh()->payment_reminder_sent_at);
        Mail::assertSent(PaymentReminderMail::class);
    }
}
