<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class TourStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Location-based hotel requirement: tours require an existing hotel location
        $this->hotel = Hotel::create([
            'name' => 'Bagan Lodge Test',
            'category' => '4-star',
            'location' => 'Bagan',
            'price_low' => 10.00,
            'price_normal' => 20.00,
            'price_peak' => 30.00,
        ]);
    }

    /**
     * Test admin can create a new tour and status defaults to active if sent.
     */
    public function test_admin_can_create_tour_with_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Test Tour Bagan',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tours', [
            'title' => 'Test Tour Bagan',
            'status' => 'active',
        ]);
    }

    /**
     * Test admin can edit tour status from active to inactive and vice versa.
     */
    public function test_admin_can_change_tour_status(): void
    {
        $tour = Tour::create([
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100.00,
            'status' => 'active',
        ]);

        // Active -> Inactive
        $response = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour), [
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100.00,
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('admin.tours.index'));
        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'status' => 'inactive',
        ]);

        // Inactive -> Active
        $response = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour), [
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100.00,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.tours.index'));
        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test customer tour listing only displays active tours.
     */
    public function test_customer_tour_listing_only_shows_active_tours(): void
    {
        $activeTour = Tour::create([
            'title' => 'Active Tour Bagan',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $inactiveTour = Tour::create([
            'title' => 'Inactive Tour Bagan',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'inactive',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Active Tour Bagan');
        $response->assertDontSee('Inactive Tour Bagan');
    }

    /**
     * Test customer cannot access inactive tour directly.
     */
    public function test_inactive_tour_direct_url_is_unavailable(): void
    {
        $inactiveTour = Tour::create([
            'title' => 'Unavailable Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'inactive',
        ]);

        $response = $this->get(route('tours.show', $inactiveTour));

        $response->assertStatus(200);
        $response->assertSee('This tour is currently unavailable.');
        $response->assertSee('View Tours');
        $response->assertDontSee('Book This Tour');
    }

    /**
     * Test backend prevents booking creation for inactive tours.
     */
    public function test_backend_prevents_booking_for_inactive_tour(): void
    {
        $inactiveTour = Tour::create([
            'title' => 'Inactive Tour For Booking Check',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'inactive',
        ]);

        $travelPeriod = TravelPeriod::create([
            'tour_id' => $inactiveTour->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
            'total_seats' => 20,
        ]);

        // Attempt to access booking form directly
        $responseCreate = $this->get(route('booking.create', [
            'tour' => $inactiveTour->id,
            'checkin' => '2026-09-02',
            'checkout' => '2026-09-05',
        ]));
        
        $responseCreate->assertRedirect(route('tours.show', $inactiveTour));

        // Attempt to submit a booking to store route
        $responseStore = $this->post(route('booking.store', $inactiveTour), [
            'checkin_date' => '2026-09-02',
            'checkout_date' => '2026-09-05',
            'adults' => 2,
            'children' => 0,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'total_price' => 200,
        ]);

        $responseStore->assertSessionHasErrors(['tour']);
        $this->assertDatabaseMissing('bookings', [
            'tour_id' => $inactiveTour->id,
            'customer_name' => 'John Doe',
        ]);
    }

    /**
     * Test active tours remain bookable.
     */
    public function test_active_tour_can_still_be_booked(): void
    {
        Mail::fake();

        $activeTour = Tour::create([
            'title' => 'Active Tour Bookable',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100,
            'status' => 'active',
        ]);

        $travelPeriod = TravelPeriod::create([
            'tour_id' => $activeTour->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
            'total_seats' => 20,
        ]);

        $responseStore = $this->post(route('booking.store', $activeTour), [
            'checkin_date' => '2026-09-02',
            'checkout_date' => '2026-09-05',
            'adults' => 2,
            'children' => 0,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456789',
            'total_price' => 200,
        ]);

        $responseStore->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'tour_id' => $activeTour->id,
            'customer_name' => 'Jane Doe',
        ]);
    }

    /**
     * Test existing bookings are unaffected when a tour is deactivated.
     */
    public function test_existing_bookings_unaffected_on_deactivation(): void
    {
        Mail::fake();

        $tour = Tour::create([
            'title' => 'Tour To Deactivate',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100,
            'status' => 'active',
        ]);

        $travelPeriod = TravelPeriod::create([
            'tour_id' => $tour->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
            'total_seats' => 20,
        ]);

        $booking = Booking::create([
            'tour_id' => $tour->id,
            'travel_period_id' => $travelPeriod->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '12345',
            'num_persons' => 2,
            'num_children' => 0,
            'checkin_date' => '2026-09-02',
            'checkout_date' => '2026-09-05',
            'base_price' => $tour->base_price,
            'hotel_upgrade_price' => 0,
            'total_price' => 200,
            'status' => 'confirmed',
            'ref_code' => 'MYG-1234',
            'cancellation_token' => 'uuid-token-123',
        ]);

        // Deactivate tour
        $tour->update(['status' => 'inactive']);

        // Verify the existing booking and email fake checks
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed', // Should remain unchanged
            'customer_name' => 'Existing Customer',
        ]);

        Mail::assertNothingSent(); // Changing status should trigger no emails
    }
}
