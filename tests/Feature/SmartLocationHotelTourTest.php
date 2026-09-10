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

    /**
     * Test a tour can be saved with one selected hotel.
     */
    public function test_tour_can_be_saved_with_one_selected_hotel(): void
    {
        $hotel = Hotel::create(['name' => 'Bagan View', 'category' => '3-star', 'location' => 'Bagan']);

        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Single Hotel Tour',
            'duration_days' => 2,
            'base_price' => 150.00,
            'location' => 'Bagan',
            'hotels' => [$hotel->id],
        ]);

        $tour = Tour::where('title', 'Single Hotel Tour')->firstOrFail();
        $this->assertDatabaseHas('tour_hotels', [
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
        ]);
        $this->assertCount(1, $tour->hotels);
    }

    /**
     * Test a tour can be saved with multiple selected hotels.
     */
    public function test_tour_can_be_saved_with_multiple_selected_hotels(): void
    {
        $hotel1 = Hotel::create(['name' => 'Bagan View A', 'category' => '3-star', 'location' => 'Bagan']);
        $hotel2 = Hotel::create(['name' => 'Bagan View B', 'category' => '4-star', 'location' => 'Bagan']);

        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Multi Hotel Tour',
            'duration_days' => 3,
            'base_price' => 250.00,
            'location' => 'Bagan',
            'hotels' => [$hotel1->id, $hotel2->id],
        ]);

        $tour = Tour::where('title', 'Multi Hotel Tour')->firstOrFail();
        $this->assertDatabaseHas('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel1->id]);
        $this->assertDatabaseHas('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel2->id]);
        $this->assertCount(2, $tour->hotels);
    }

    /**
     * Test opening Edit Tour loads the previously selected hotels as selected.
     */
    public function test_edit_tour_loads_previously_selected_hotels_as_selected(): void
    {
        $hotel = Hotel::create(['name' => 'Bagan Resort', 'category' => '5-star', 'location' => 'Bagan']);
        $tour = Tour::create([
            'title' => 'Bagan Explorer',
            'duration_days' => 4,
            'base_price' => 300.00,
            'location' => 'Bagan',
        ]);
        $tour->hotels()->sync([$hotel->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.tours.edit', $tour));

        $response->assertStatus(200);
        // Verify selected hotels array is rendered with hotel id in JS via @js
        $response->assertSee("window.selectedHotels = (JSON.parse('[{$hotel->id}]') || []).map(id => parseInt(id));", false);
    }

    /**
     * Test updating a tour from one set of hotels to another correctly synchronizes associations.
     */
    public function test_updating_tour_synchronizes_hotel_associations(): void
    {
        $hotel1 = Hotel::create(['name' => 'Hotel Alpha', 'category' => '3-star', 'location' => 'Bagan']);
        $hotel2 = Hotel::create(['name' => 'Hotel Beta', 'category' => '4-star', 'location' => 'Bagan']);

        $tour = Tour::create([
            'title' => 'Bagan Sync Tour',
            'duration_days' => 2,
            'base_price' => 100.00,
            'location' => 'Bagan',
        ]);
        $tour->hotels()->sync([$hotel1->id]);

        $response = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour), [
            'title' => 'Bagan Sync Tour Updated',
            'duration_days' => 2,
            'base_price' => 100.00,
            'location' => 'Bagan',
            'hotels' => [$hotel2->id],
        ]);

        $response->assertRedirect(route('admin.tours.index'));
        $this->assertDatabaseMissing('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel1->id]);
        $this->assertDatabaseHas('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel2->id]);
    }

    /**
     * Test removing all selected hotels removes only tour hotel associations and preserves hotel records.
     */
    public function test_removing_all_selected_hotels_detaches_associations_without_deleting_hotels(): void
    {
        $hotel = Hotel::create(['name' => 'Bagan Sanctuary', 'category' => '4-star', 'location' => 'Bagan']);
        $tour = Tour::create([
            'title' => 'Bagan Detach Tour',
            'duration_days' => 2,
            'base_price' => 120.00,
            'location' => 'Bagan',
        ]);
        $tour->hotels()->sync([$hotel->id]);

        $response = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour), [
            'title' => 'Bagan Detach Tour Updated',
            'duration_days' => 2,
            'base_price' => 120.00,
            'location' => 'Bagan',
            // No 'hotels' sent in request
        ]);

        $response->assertRedirect(route('admin.tours.index'));
        $this->assertDatabaseMissing('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel->id]);
        $this->assertDatabaseHas('hotels', ['id' => $hotel->id]);
    }

    /**
     * Test multi-word location such as "Kalaw & Inle Lake" works for hotels and tours end-to-end.
     */
    public function test_multi_word_location_with_ampersand_display_and_association(): void
    {
        $location = 'Kalaw & Inle Lake';

        // 1. Create Hotel with multi-word location containing '&'
        $hotel = Hotel::create([
            'name' => 'Inle Lake Resort',
            'category' => '4-star',
            'location' => $location,
        ]);

        // 2. Create Tour with location "Kalaw & Inle Lake" and select hotel
        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Shan Hills Explorer',
            'duration_days' => 4,
            'base_price' => 350.00,
            'location' => $location,
            'hotels' => [$hotel->id],
        ]);

        $tour = Tour::where('title', 'Shan Hills Explorer')->firstOrFail();
        $this->assertDatabaseHas('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel->id]);

        // 3. Test hotels-by-location JSON endpoint returns hotel for "Kalaw & Inle Lake"
        $jsonResponse = $this->actingAs($this->admin)->get(route('admin.hotels.by-location', ['location' => $location]));
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonFragment(['id' => $hotel->id, 'name' => 'Inle Lake Resort']);

        // 4. Test Edit Tour page loads and renders valid JS string in script tag and HTML attribute in x-data
        $editResponse = $this->actingAs($this->admin)->get(route('admin.tours.edit', $tour));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('initialValue: &quot;Kalaw &amp; Inle Lake&quot;', false);
        $editResponse->assertSee("const initialLocation = 'Kalaw \\u0026 Inle Lake';", false);
        $editResponse->assertSee("window.selectedHotels = (JSON.parse('[{$hotel->id}]') || []).map(id => parseInt(id));", false);

        // 5. Update tour and verify hotel association remains correct
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour), [
            'title' => 'Shan Hills Explorer Updated',
            'duration_days' => 4,
            'base_price' => 380.00,
            'location' => $location,
            'hotels' => [$hotel->id],
        ]);

        $updateResponse->assertRedirect(route('admin.tours.index'));
        $this->assertDatabaseHas('tour_hotels', ['tour_id' => $tour->id, 'hotel_id' => $hotel->id]);
    }

    /**
     * Test existing unrelated hotel/tour associations are not affected.
     */
    public function test_existing_unrelated_hotel_tour_associations_are_not_affected(): void
    {
        $hotelA = Hotel::create(['name' => 'Hotel A', 'category' => '3-star', 'location' => 'Bagan']);
        $hotelB = Hotel::create(['name' => 'Hotel B', 'category' => '4-star', 'location' => 'Bagan']);

        $tourA = Tour::create(['title' => 'Tour A', 'duration_days' => 2, 'base_price' => 100, 'location' => 'Bagan']);
        $tourB = Tour::create(['title' => 'Tour B', 'duration_days' => 3, 'base_price' => 200, 'location' => 'Bagan']);

        $tourA->hotels()->sync([$hotelA->id]);
        $tourB->hotels()->sync([$hotelB->id]);

        // Update Tour A to detach all hotels
        $this->actingAs($this->admin)->put(route('admin.tours.update', $tourA), [
            'title' => 'Tour A Updated',
            'duration_days' => 2,
            'base_price' => 100,
            'location' => 'Bagan',
        ]);

        // Tour A association removed, but Tour B association with Hotel B must remain intact
        $this->assertDatabaseMissing('tour_hotels', ['tour_id' => $tourA->id]);
        $this->assertDatabaseHas('tour_hotels', ['tour_id' => $tourB->id, 'hotel_id' => $hotelB->id]);
    }
}
