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
use Tests\TestCase;

class ManualPaymentWorkflowTest extends TestCase
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

    public function test_email_is_sent_when_booking_status_changes_from_pending_to_confirmed(): void
    {
        Mail::fake();

        $admin = User::factory()->create();

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
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'test-token-uuid-123',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.bookings.update', $booking), [
                'status' => 'confirmed'
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking));
        $this->assertEquals('confirmed', $booking->fresh()->status);

        Mail::assertSent(PaymentRequiredMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id;
        });
    }

    public function test_email_is_not_sent_when_status_changes_to_something_else(): void
    {
        Mail::fake();

        $admin = User::factory()->create();

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
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'test-token-uuid-123',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.bookings.update', $booking), [
                'status' => 'cancelled'
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking));
        $this->assertEquals('cancelled', $booking->fresh()->status);

        Mail::assertNotSent(PaymentRequiredMail::class);
    }

    public function test_payment_page_can_be_accessed_with_valid_token(): void
    {
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
            'status' => 'confirmed',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $response = $this->get('/payment/my-uuid-token-xyz');

        $response->assertOk();
        $response->assertViewHas('booking');
        $response->assertSee('MYG-123456');
        $response->assertSee('Yangon City Tour');
        $response->assertSee('USD 300.00');
    }

    public function test_payment_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get('/payment/invalid-token-here');

        $response->assertStatus(404);
    }

    public function test_payment_receipt_can_be_uploaded_successfully(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Storage::fake('public');

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
            'status' => 'confirmed',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg');

        $response = $this->post('/payment/my-uuid-token-xyz', [
            'receipt' => $file
        ]);

        $response->assertRedirect(route('payment.success'));
        
        $booking = $booking->fresh();
        $this->assertEquals('payment_uploaded', $booking->status);
        $this->assertNotNull($booking->payment_receipt);
        $this->assertNotNull($booking->payment_uploaded_at);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($booking->payment_receipt);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PaymentReceiptReceivedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id;
        });
    }

    public function test_payment_upload_fails_validation_for_invalid_file_type(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

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
            'status' => 'confirmed',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('receipt.txt', 100);

        $response = $this->post('/payment/my-uuid-token-xyz', [
            'receipt' => $file
        ]);

        $response->assertSessionHasErrors(['receipt']);
        $this->assertEquals('confirmed', $booking->fresh()->status);
    }

    public function test_payment_upload_fails_validation_for_oversized_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

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
            'status' => 'confirmed',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('receipt.pdf', 6144);

        $response = $this->post('/payment/my-uuid-token-xyz', [
            'receipt' => $file
        ]);

        $response->assertSessionHasErrors(['receipt']);
        $this->assertEquals('confirmed', $booking->fresh()->status);
    }

    public function test_payment_success_page_displays_successfully(): void
    {
        $response = $this->get('/payment/success');

        $response->assertOk();
        $response->assertSee('Payment Receipt Uploaded Successfully');
    }

    public function test_admin_can_approve_uploaded_payment_receipt(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create();

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
            'status' => 'payment_uploaded',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d'),
            'payment_receipt' => 'payment_receipts/test.jpg',
            'payment_uploaded_at' => now()
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.bookings.update', $booking), [
                'status' => 'paid'
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking));
        $this->assertEquals('paid', $booking->fresh()->status);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PaymentConfirmedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id;
        });
    }

    public function test_admin_can_reject_uploaded_payment_receipt(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create();

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
            'status' => 'payment_uploaded',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d'),
            'payment_receipt' => 'payment_receipts/test.jpg',
            'payment_uploaded_at' => now()
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.bookings.update', $booking), [
                'status' => 'confirmed'
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking));
        $this->assertEquals('confirmed', $booking->fresh()->status);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PaymentRejectedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id;
        });
    }

    public function test_admin_can_cancel_booking_with_reason(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create();

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
            'status' => 'confirmed',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.bookings.cancel', $booking), [
                'cancel_reason' => 'Customer requested cancellation'
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking));
        
        $booking = $booking->fresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('admin', $booking->cancelled_by);
        $this->assertEquals('Customer requested cancellation', $booking->cancel_reason);
        $this->assertNotNull($booking->cancelled_at);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\BookingCancelledMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email) &&
                   $mail->booking->id === $booking->id;
        });
    }

    public function test_admin_cannot_cancel_booking_without_reason(): void
    {
        $admin = User::factory()->create();

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
            'status' => 'confirmed',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.bookings.cancel', $booking), [
                'cancel_reason' => ''
            ]);

        $response->assertSessionHasErrors(['cancel_reason']);
        $this->assertEquals('confirmed', $booking->fresh()->status);
    }

    public function test_already_cancelled_booking_cannot_be_cancelled_again(): void
    {
        $admin = User::factory()->create();

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
            'status' => 'cancelled',
            'ref_code' => 'MYG-123456',
            'cancellation_token' => 'my-uuid-token-xyz',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d'),
            'cancelled_by' => 'admin',
            'cancel_reason' => 'Previous cancellation reason',
            'cancelled_at' => now()
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.bookings.cancel', $booking), [
                'cancel_reason' => 'New reason'
            ]);

        $response->assertRedirect(route('admin.bookings.show', $booking));
        $response->assertSessionHas('error');
        $this->assertEquals('Previous cancellation reason', $booking->fresh()->cancel_reason);
    }
}
