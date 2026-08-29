<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSuccessPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_success_page_displays_correct_total_price(): void
    {
        $tour = Tour::create([
            'title' => 'Yangon City Tour',
            'duration_days' => 3,
            'base_price' => 150.00,
            'location' => 'Yangon',
            'status' => 'active'
        ]);

        $hotel = Hotel::create([
            'name' => 'Lotte Hotel Yangon',
            'category' => '5-star',
            'location' => 'Yangon'
        ]);

        $travelPeriod = TravelPeriod::create([
            'tour_id' => $tour->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(13),
            'total_seats' => 10,
            'booked_seats' => 0
        ]);

        $booking = Booking::create([
            'tour_id' => $tour->id,
            'travel_period_id' => $travelPeriod->id,
            'hotel_id' => $hotel->id,
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

        $response = $this->get(route('booking.success', $booking));
        $response->assertStatus(200);

        // Assert that the success page contains the correct total price
        $response->assertSee('$300.00');
    }

    public function test_booking_creation_via_request_displays_correct_total_on_success_page(): void
    {
        $tour = Tour::create([
            'title' => 'Bagan Temple Tour',
            'duration_days' => 3,
            'base_price' => 200.00,
            'location' => 'Bagan',
            'status' => 'active'
        ]);

        $hotel = Hotel::create([
            'name' => 'Bagan Heritage Hotel',
            'category' => '4-star',
            'location' => 'Bagan'
        ]);

        $travelPeriod = TravelPeriod::create([
            'tour_id' => $tour->id,
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-10',
            'total_seats' => 5,
            'booked_seats' => 0
        ]);

        $response = $this->post(route('booking.store', $tour), [
            'checkin_date' => '2026-04-03',
            'checkout_date' => '2026-04-05',
            'adults' => 2,
            'children' => 1,
            'child_ages' => '6',
            'hotel_id' => $hotel->id,
            'total_price' => 600.00, // (2 adults + 1 child >= 5 = 3 payable) * 200 base price
            'customer_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '12345678',
            'message' => 'Hello'
        ]);

        $response->assertRedirect();
        
        // Find the created booking
        $booking = Booking::where('customer_name', 'Test User')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(600.00, $booking->total_price);

        // Assert redirect is to the success page for this booking
        $response->assertRedirect(route('booking.success', $booking));

        // Follow redirect or get the success page
        $successResponse = $this->get(route('booking.success', $booking));
        $successResponse->assertStatus(200);
        
        // Check that the correct price is displayed
        $successResponse->assertSee('$600.00');
    }

    public function test_booking_submitted_email_renders_with_redesign_styles(): void
    {
        $tour = Tour::create([
            'title' => 'Mandalay Heritage Tour',
            'duration_days' => 2,
            'base_price' => 120.00,
            'location' => 'Mandalay',
            'status' => 'active'
        ]);

        $hotel = Hotel::create([
            'name' => 'Mandalay Hill Resort',
            'category' => '5-star',
            'location' => 'Mandalay'
        ]);

        $travelPeriod = TravelPeriod::create([
            'tour_id' => $tour->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'total_seats' => 10,
            'booked_seats' => 0
        ]);

        $booking = Booking::create([
            'tour_id' => $tour->id,
            'travel_period_id' => $travelPeriod->id,
            'hotel_id' => $hotel->id,
            'customer_name' => 'Alice Green',
            'nationality' => 'Singaporean',
            'email' => 'alice@example.com',
            'phone' => '987654321',
            'num_persons' => 2,
            'checkin_date' => '2026-05-10',
            'checkout_date' => '2026-05-12',
            'base_price' => 120.00,
            'hotel_upgrade_price' => 0.00,
            'total_price' => 240.00,
            'status' => 'pending',
            'ref_code' => 'MYG-999999',
            'cancellation_token' => 'uuid-token-alice',
            'payment_deadline' => now()->addDays(7)->format('Y-m-d')
        ]);

        $mailable = new \App\Mail\BookingSubmittedMail($booking);
        $html = $mailable->render();

        // Check content elements
        $this->assertStringContainsString('MyanGo Travel', $html);
        $this->assertStringContainsString('Booking Details', $html);
        $this->assertStringContainsString($booking->ref_code, $html);
        $this->assertStringContainsString('Alice Green', $html);
        $this->assertStringContainsString('Mandalay Heritage Tour', $html);
        $this->assertStringContainsString('$240.00', $html);
        $this->assertStringContainsString('Cancel Booking', $html);

        // Check CSS style elements of the redesign
        $this->assertStringContainsString('background-color: #111844;', $html); // header bg
        $this->assertStringContainsString('font-family: \'Inter\'', $html); // body font
        $this->assertStringContainsString('background-color: #f4f6f8;', $html); // wrapper bg
        $this->assertStringContainsString('background-color: #c0392b;', $html); // btn-secondary
    }

    public function test_admin_inquiry_submitted_email_renders_with_redesign_styles(): void
    {
        $inquiry = \App\Models\Inquiry::create([
            'customer_name' => 'Bob Brown',
            'email' => 'bob@example.com',
            'phone' => '111222333',
            'number_of_adults' => 3,
            'number_of_children' => 0,
            'message' => 'Hello, I want to book a private customized tour for 3 adults.',
            'status' => 'new'
        ]);

        $mailable = new \App\Mail\AdminInquirySubmittedMail($inquiry);
        $html = $mailable->render();

        // Check content elements
        $this->assertStringContainsString('MyanGo Travel', $html);
        $this->assertStringContainsString('Inquiry Details', $html);
        $this->assertStringContainsString($inquiry->reference, $html);
        $this->assertStringContainsString('Bob Brown', $html);
        $this->assertStringContainsString('bob@example.com', $html);
        $this->assertStringContainsString('111222333', $html);
        $this->assertStringContainsString('Hello, I want to book a private customized tour for 3 adults.', $html);

        // Check CSS style elements of the redesign
        $this->assertStringContainsString('background-color: #111844;', $html); // header bg
        $this->assertStringContainsString('font-family: \'Inter\'', $html); // body font
        $this->assertStringContainsString('background-color: #f4f6f8;', $html); // wrapper bg
        $this->assertStringContainsString('border-left: 4px solid #111844;', $html); // message left border
    }
}
