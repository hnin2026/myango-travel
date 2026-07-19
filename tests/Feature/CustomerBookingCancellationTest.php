<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCancelledMail;
use Tests\TestCase;

class CustomerBookingCancellationTest extends TestCase
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

    /**
     * TEST 1 & 8: Booking status = pending, customer can cancel.
     */
    public function test_customer_can_cancel_pending_booking_with_valid_reason(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'John Doe',
            'nationality' => 'American',
            'email' => 'customer@example.com',
            'phone' => '123456789',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'pending',
            'cancellation_token' => 'my-uuid-token-xyz',
            'ref_code' => 'MYG-11111',
        ]);

        // Verify cancellation page shows details
        $response = $this->get(route('booking.cancel.show', 'my-uuid-token-xyz'));
        $response->assertStatus(200);
        $response->assertSee($booking->ref_code);
        $response->assertSee($this->tour->title);
        $response->assertSee('Are you sure you want to cancel this booking?');

        // Cancel the booking
        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-xyz'), [
            'cancel_reason' => 'Change of travel plans',
        ]);

        $response->assertRedirect(route('booking.cancel.success', 'my-uuid-token-xyz'));

        // Refresh and assert model fields
        $booking = $booking->fresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('customer', $booking->cancelled_by);
        $this->assertEquals('Change of travel plans', $booking->cancel_reason);
        $this->assertNotNull($booking->cancelled_at);

        // Verify success page details
        $successResponse = $this->get(route('booking.cancel.success', 'my-uuid-token-xyz'));
        $successResponse->assertStatus(200);
        $successResponse->assertSee($booking->ref_code);
        $successResponse->assertSee('🎉 Booking Cancelled Successfully');

        // Assert cancellation email sent to customer
        Mail::assertSent(BookingCancelledMail::class, function ($mail) use ($booking) {
            $mail->build();
            return $mail->hasTo('customer@example.com') &&
                   $mail->booking->id === $booking->id &&
                   $mail->subject === 'Booking Cancellation Confirmation';
        });
    }

    /**
     * TEST 2: Booking status = confirmed, customer can cancel.
     */
    public function test_customer_can_cancel_confirmed_booking(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'confirmed',
            'cancellation_token' => 'my-uuid-token-abc',
            'ref_code' => 'MYG-22222',
        ]);

        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-abc'), [
            'cancel_reason' => 'Emergency cancellation',
        ]);

        $response->assertRedirect(route('booking.cancel.success', 'my-uuid-token-abc'));

        $booking = $booking->fresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('customer', $booking->cancelled_by);
        $this->assertEquals('Emergency cancellation', $booking->cancel_reason);
    }

    /**
     * TEST 3: Booking status = payment_uploaded, customer cannot cancel.
     */
    public function test_customer_cannot_cancel_payment_uploaded_booking(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '987654321',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'payment_uploaded',
            'cancellation_token' => 'my-uuid-token-upload',
            'ref_code' => 'MYG-33333',
        ]);

        // Expect page to show blocking warning
        $response = $this->get(route('booking.cancel.show', 'my-uuid-token-upload'));
        $response->assertStatus(200);
        $response->assertSee('This booking cannot be cancelled online because payment processing has already started. Please contact MyanGo Travel for assistance.');
        $response->assertSee('info@myango.com');
        $response->assertSee('+95 9 123 456 789');

        // Submit action should redirect back to show page without changing status
        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-upload'), [
            'cancel_reason' => 'Trying to cancel anyway',
        ]);
        $response->assertRedirect(route('booking.cancel.show', 'my-uuid-token-upload'));

        $booking = $booking->fresh();
        $this->assertEquals('payment_uploaded', $booking->status);
        Mail::assertNothingSent();
    }

    /**
     * TEST 4: Booking status = paid, customer cannot cancel.
     */
    public function test_customer_cannot_cancel_paid_booking(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '987654321',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'paid',
            'cancellation_token' => 'my-uuid-token-paid',
            'ref_code' => 'MYG-44444',
        ]);

        $response = $this->get(route('booking.cancel.show', 'my-uuid-token-paid'));
        $response->assertStatus(200);
        $response->assertSee('This booking cannot be cancelled online because payment processing has already started. Please contact MyanGo Travel for assistance.');

        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-paid'), [
            'cancel_reason' => 'Trying to cancel paid booking',
        ]);
        $response->assertRedirect(route('booking.cancel.show', 'my-uuid-token-paid'));

        $booking = $booking->fresh();
        $this->assertEquals('paid', $booking->status);
        Mail::assertNothingSent();
    }

    /**
     * TEST 5: Booking status = cancelled, customer cannot cancel again.
     */
    public function test_customer_cannot_cancel_already_cancelled_booking(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '987654321',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'cancelled',
            'cancellation_token' => 'my-uuid-token-cancelled',
            'cancelled_by' => 'customer',
            'cancel_reason' => 'Original cancellation reason',
            'cancelled_at' => now(),
            'ref_code' => 'MYG-55555',
        ]);

        $response = $this->get(route('booking.cancel.show', 'my-uuid-token-cancelled'));
        $response->assertStatus(200);
        $response->assertSee('This booking has already been cancelled.');

        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-cancelled'), [
            'cancel_reason' => 'Another cancellation reason',
        ]);
        $response->assertRedirect(route('booking.cancel.show', 'my-uuid-token-cancelled'));

        $booking = $booking->fresh();
        $this->assertEquals('Original cancellation reason', $booking->cancel_reason);
        Mail::assertNothingSent();
    }

    /**
     * TEST 6: Invalid token.
     */
    public function test_cancellation_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('booking.cancel.show', 'invalid-token-123'));
        $response->assertStatus(404);

        $response = $this->post(route('booking.cancel.submit', 'invalid-token-123'), [
            'cancel_reason' => 'Reason',
        ]);
        $response->assertStatus(404);
    }

    /**
     * TEST 7: Empty cancellation reason.
     */
    public function test_cancellation_reason_is_required_and_validated(): void
    {
        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'pending',
            'cancellation_token' => 'my-uuid-token-validation',
            'ref_code' => 'MYG-66666',
        ]);

        // Submit empty reason
        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-validation'), [
            'cancel_reason' => '',
        ]);
        $response->assertSessionHasErrors(['cancel_reason']);

        // Submit too long reason (greater than 1000 characters)
        $response = $this->post(route('booking.cancel.submit', 'my-uuid-token-validation'), [
            'cancel_reason' => str_repeat('a', 1001),
        ]);
        $response->assertSessionHasErrors(['cancel_reason']);

        $this->assertEquals('pending', $booking->fresh()->status);
    }

    /**
     * TEST 9: Click Back link (ensure home link exists) and Home button on success page.
     */
    public function test_cancel_page_has_home_and_back_links(): void
    {
        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'pending',
            'cancellation_token' => 'my-uuid-token-links',
            'ref_code' => 'MYG-77777',
        ]);

        $response = $this->get(route('booking.cancel.show', 'my-uuid-token-links'));
        // Verify Back link is pointing to home
        $response->assertSee(route('home'));

        $booking->update(['status' => 'cancelled']);
        $successResponse = $this->get(route('booking.cancel.success', 'my-uuid-token-links'));
        // Verify success page has Home button
        $successResponse->assertSee(route('home'));
    }

    /**
     * Test that cancel link is present in booking-submitted, payment-required, and payment-rejected emails.
     */
    public function test_cancel_link_is_present_in_customer_emails(): void
    {
        $booking = Booking::create([
            'tour_id' => $this->tour->id,
            'travel_period_id' => $this->travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'nationality' => 'British',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'num_persons' => 2,
            'checkin_date' => now()->addDays(10)->format('Y-m-d'),
            'checkout_date' => now()->addDays(13)->format('Y-m-d'),
            'base_price' => 150.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 300.00,
            'status' => 'pending',
            'cancellation_token' => 'my-uuid-token-email-link',
            'ref_code' => 'MYG-88888',
        ]);

        // 1. BookingSubmittedMail
        $mail = new \App\Mail\BookingSubmittedMail($booking);
        $rendered = $mail->render();
        $this->assertStringContainsString('/booking/cancel/my-uuid-token-email-link', $rendered);

        // 2. PaymentRequiredMail
        $mail = new \App\Mail\PaymentRequiredMail($booking);
        $rendered = $mail->render();
        $this->assertStringContainsString('/booking/cancel/my-uuid-token-email-link', $rendered);

        // 3. PaymentRejectedMail
        $mail = new \App\Mail\PaymentRejectedMail($booking);
        $rendered = $mail->render();
        $this->assertStringContainsString('/booking/cancel/my-uuid-token-email-link', $rendered);
    }
}
