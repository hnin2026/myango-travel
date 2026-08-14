<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TravelPeriod;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartLocationHotelTourTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test hotel location normalization and duplicate prevention on creation.
     */
    public function test_hotel_location_normalization_and_duplicate_prevention(): void
    {
        // 1. Create a hotel with location "Bagan"
        $response1 = $this->actingAs($this->admin)->post(route('admin.hotels.store'), [
            'name' => 'Bagan Lodge',
            'category' => '4-star',
            'location' => '  bagan ',
            'price_low' => 10.00,
            'price_normal' => 20.00,
            'price_peak' => 30.00,
        ]);

        $response1->assertRedirect(route('admin.hotels.index'));
        $this->assertDatabaseHas('hotels', [
            'name' => 'Bagan Lodge',
            'location' => 'Bagan', // Should be normalized to Title Case
        ]);

        // 2. Create another hotel with location "BAGAN"
        $response2 = $this->actingAs($this->admin)->post(route('admin.hotels.store'), [
            'name' => 'Heritage Bagan',
            'category' => '5-star',
            'location' => 'BAGAN',
            'price_low' => 15.00,
            'price_normal' => 25.00,
            'price_peak' => 35.00,
        ]);

        $response2->assertRedirect(route('admin.hotels.index'));
        // It should match existing "Bagan" case-insensitively and reuse exactly "Bagan"
        $this->assertDatabaseHas('hotels', [
            'name' => 'Heritage Bagan',
            'location' => 'Bagan',
        ]);

        // Verify distinct count of locations in database is 1
        $uniqueLocationsCount = Hotel::distinct()->pluck('location')->count();
        $this->assertEquals(1, $uniqueLocationsCount);
    }

    /**
     * Test editing a hotel location is normalized.
     */
    public function test_edit_hotel_location_normalization(): void
    {
        $hotel = Hotel::create([
            'name' => 'Sedona Hotel Yangon',
            'category' => '5-star',
            'location' => 'Yangon',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.hotels.update', $hotel), [
            'name' => 'Sedona Hotel Yangon Updated',
            'category' => '5-star',
            'location' => '   yangon   ',
            'price_low' => 10.00,
            'price_normal' => 20.00,
            'price_peak' => 30.00,
        ]);

        $response->assertRedirect(route('admin.hotels.index'));
        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'location' => 'Yangon', // Trimmed and Standardized
        ]);
    }

    /**
     * Test JSON hotels by location endpoint.
     */
    public function test_hotels_by_location_json_endpoint(): void
    {
        Hotel::create(['name' => 'Bagan Hotel A', 'category' => '3-star', 'location' => 'Bagan']);
        Hotel::create(['name' => 'Bagan Hotel B', 'category' => '5-star', 'location' => 'Bagan']);
        Hotel::create(['name' => 'Yangon Hotel', 'category' => '5-star', 'location' => 'Yangon']);

        $response = $this->actingAs($this->admin)->get(route('admin.hotels.by-location', ['location' => 'bagan']));

        $response->assertStatus(200);
        $hotels = $response->json();

        // Should return 2 hotels, sorted by category (5-star -> 3-star)
        $this->assertCount(2, $hotels);
        $this->assertEquals('Bagan Hotel B', $hotels[0]['name']);
        $this->assertEquals('Bagan Hotel A', $hotels[1]['name']);
    }

    /**
     * Test creating a tour with a non-existent location is blocked.
     */
    public function test_tour_with_invalid_location_is_blocked(): void
    {
        // No hotel in Pyin Oo Lwin exists yet
        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Pyin Oo Lwin Escape',
            'duration_days' => 2,
            'location' => 'Pyin Oo Lwin',
        ]);

        $response->assertSessionHasErrors('location');
    }

    /**
     * Test creating a tour with an existing location is allowed.
     */
    public function test_tour_with_valid_location_is_allowed(): void
    {
        Hotel::create([
            'name' => 'Bagan Lodge',
            'category' => '4-star',
            'location' => 'Bagan',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Bagan Culture Tour',
            'duration_days' => 3,
            'location' => 'bagan', // Different casing
        ]);

        // Standard redirect is to travel periods index of the newly created tour
        $this->assertDatabaseHas('tours', [
            'title' => 'Bagan Culture Tour',
            'location' => 'Bagan', // Normalized to existing casing
        ]);
    }

    /**
     * Test attaching a hotel from another location to a tour is blocked.
     */
    public function test_attaching_hotel_from_another_location_is_blocked(): void
    {
        $baganHotel = Hotel::create([
            'name' => 'Bagan Lodge',
            'category' => '4-star',
            'location' => 'Bagan',
        ]);

        $yangonHotel = Hotel::create([
            'name' => 'Lotte Hotel Yangon',
            'category' => '5-star',
            'location' => 'Yangon',
        ]);

        // Attempt to create a tour in Bagan, attaching Yangon Hotel
        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Bagan Tour with Yangon Hotel',
            'duration_days' => 3,
            'location' => 'Bagan',
            'hotels' => [$baganHotel->id, $yangonHotel->id],
        ]);

        $response->assertSessionHasErrors('hotels');
    }

    /**
     * Test customer tour detail page filters hotels matching location.
     */
    public function test_customer_tour_page_filters_hotels(): void
    {
        $tour = Tour::create([
            'title' => 'Bagan Classic',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 200.00,
            'status' => 'active',
        ]);

        $baganHotel = Hotel::create([
            'name' => 'Bagan Lodge',
            'category' => '4-star',
            'location' => 'Bagan',
        ]);

        $yangonHotel = Hotel::create([
            'name' => 'Lotte Hotel Yangon',
            'category' => '5-star',
            'location' => 'Yangon',
        ]);

        // Attach both to tour_hotels (simulating legacy data or incorrect bypass)
        $tour->hotels()->sync([$baganHotel->id, $yangonHotel->id]);

        $response = $this->get(route('tours.show', $tour));

        $response->assertStatus(200);
        $response->assertSee('Bagan Lodge');
        $response->assertDontSee('Lotte Hotel Yangon');
    }
}
