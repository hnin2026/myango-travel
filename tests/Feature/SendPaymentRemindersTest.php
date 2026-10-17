<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReminderMail;
use Tests\TestCase;

class SendPaymentRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected $tour;
    protected $hotel;
    protected $travelPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tour = Tour::create([
            'title' => 'Yangon Heritage Tour',
            'duration_days' => 3,
            'base_price' => 180.00,
            'location' => 'Yangon',
            'status' => 'active'
        ]);

        $this->hotel = Hotel::create([
            'name' => 'Strand Hotel Yangon',
            'category' => '5-star',
            'location' => 'Yangon'
        ]);

        $this->travelPeriod = TravelPeriod::create([
            'tour_id' => $this->tour->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(13),
            'total_seats' => 15,
            'booked_seats' => 0
        ]);
    }

    private function createBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'John Reminder',
            'nationality' => 'British',
            'email' => 'customer@example.com',
            'phone' => '0912345678',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 180.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 360.00,
            'status' => 'confirmed',
            'cancellation_token' => uniqid('token_'),
            'ref_code' => 'MYG-' . rand(10000, 99999),
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
            'payment_reminder_sent_at' => null,
        ], $overrides));
    }

    /**
     * TEST 1: An eligible booking (confirmed, no receipt, 2 days before deadline) receives a reminder.
     */
    public function test_eligible_booking_receives_reminder_two_days_before_deadline(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 1')
            ->expectsOutputToContain('Payment reminders sent: 1')
            ->assertExitCode(0);

        $booking->refresh();

        $this->assertNotNull($booking->payment_reminder_sent_at);
        $this->assertEquals('confirmed', $booking->status);

        Mail::assertSent(PaymentReminderMail::class, function ($mail) use ($booking) {
            $mail->build();
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id &&
                   str_contains($mail->subject, 'Payment Reminder: 2 Days Left - Booking ' . $booking->ref_code);
        });
    }

    /**
     * TEST 2: No reminder is sent before the configured reminder window (e.g. 5 days away).
     */
    public function test_no_reminder_sent_before_configured_reminder_window(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'payment_deadline' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 0')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertNull($booking->payment_reminder_sent_at);
        Mail::assertNothingSent();
    }

    /**
     * TEST 3: No reminder is sent after the deadline has already passed.
     */
    public function test_no_reminder_sent_after_deadline_has_passed(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'payment_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 0')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertNull($booking->payment_reminder_sent_at);
        Mail::assertNothingSent();
    }

    /**
     * TEST 4: No reminder is sent when deadline is too close (e.g., 1 day away or today).
     */
    public function test_no_reminder_sent_when_deadline_is_too_close(): void
    {
        Mail::fake();

        $bookingTomorrow = $this->createBooking([
            'payment_deadline' => now()->addDay()->format('Y-m-d'),
        ]);

        $bookingToday = $this->createBooking([
            'payment_deadline' => now()->format('Y-m-d'),
        ]);

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 0')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->assertExitCode(0);

        $this->assertNull($bookingTomorrow->refresh()->payment_reminder_sent_at);
        $this->assertNull($bookingToday->refresh()->payment_reminder_sent_at);
        Mail::assertNothingSent();
    }

    /**
     * TEST 5: A booking with an uploaded payment receipt is skipped.
     */
    public function test_booking_with_uploaded_payment_receipt_is_skipped(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
            'payment_receipt' => 'payment_receipts/sample.jpg',
            'payment_uploaded_at' => now(),
        ]);

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 0')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->assertExitCode(0);

        $booking->refresh();
        $this->assertNull($booking->payment_reminder_sent_at);
        Mail::assertNothingSent();
    }

    /**
     * TEST 6: Paid, cancelled, and completed bookings are skipped.
     */
    public function test_paid_cancelled_and_completed_bookings_are_skipped(): void
    {
        Mail::fake();

        $paidBooking = $this->createBooking([
            'status' => 'paid',
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $cancelledBooking = $this->createBooking([
            'status' => 'cancelled',
            'cancelled_by' => 'customer',
            'cancel_reason' => 'Changed plans',
            'cancelled_at' => now(),
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $completedBooking = $this->createBooking([
            'status' => 'completed',
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 0')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->assertExitCode(0);

        $this->assertNull($paidBooking->refresh()->payment_reminder_sent_at);
        $this->assertNull($cancelledBooking->refresh()->payment_reminder_sent_at);
        $this->assertNull($completedBooking->refresh()->payment_reminder_sent_at);
        Mail::assertNothingSent();
    }

    /**
     * TEST 7: A reminder is not sent twice when command executes repeatedly (Idempotence).
     */
    public function test_reminder_is_not_sent_twice_when_command_executed_more_than_once(): void
    {
        Mail::fake();

        $booking = $this->createBooking([
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        // First run
        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 1')
            ->expectsOutputToContain('Payment reminders sent: 1')
            ->assertExitCode(0);

        $booking->refresh();
        $firstSentAt = $booking->payment_reminder_sent_at;
        $this->assertNotNull($firstSentAt);
        Mail::assertSentCount(1);

        // Second run
        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 0')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->assertExitCode(0);

        $booking->refresh();
        // Sent timestamp unchanged and no duplicate emails
        $this->assertEquals($firstSentAt->format('Y-m-d H:i:s'), $booking->payment_reminder_sent_at->format('Y-m-d H:i:s'));
        Mail::assertSentCount(1);
    }

    /**
     * TEST 8: A failed email dispatch does not incorrectly mark the reminder as sent.
     */
    public function test_failed_email_dispatch_does_not_mark_reminder_as_sent(): void
    {
        $booking = $this->createBooking([
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        // Mock Mail facade to throw an exception
        Mail::shouldReceive('to')
            ->once()
            ->with($booking->email)
            ->andThrow(new \Exception('SMTP connection timeout'));

        $this->artisan('bookings:send-payment-reminders')
            ->expectsOutputToContain('Eligible bookings checked: 1')
            ->expectsOutputToContain('Payment reminders sent: 0')
            ->expectsOutputToContain('Payment reminders failed: 1')
            ->assertExitCode(0);

        $booking->refresh();
        // Crucial requirement: payment_reminder_sent_at MUST remain null
        $this->assertNull($booking->payment_reminder_sent_at);
    }

    /**
     * TEST 9: Payment reminder email content and links verification.
     */
    public function test_reminder_email_content_and_links(): void
    {
        $booking = $this->createBooking([
            'cancellation_token' => 'unique-token-12345',
            'ref_code' => 'MYG-98765',
            'payment_deadline' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $mailable = new PaymentReminderMail($booking);
        $rendered = $mailable->render();

        $this->assertStringContainsString('MYG-98765', $rendered);
        $this->assertStringContainsString('Yangon Heritage Tour', $rendered);
        $this->assertStringContainsString(now()->addDays(2)->format('F d, Y'), $rendered);
        $this->assertStringContainsString('/payment/unique-token-12345', $rendered);
        $this->assertStringContainsString('/booking/cancel/unique-token-12345', $rendered);
        $this->assertStringContainsString('2 days', $rendered);
    }
}
