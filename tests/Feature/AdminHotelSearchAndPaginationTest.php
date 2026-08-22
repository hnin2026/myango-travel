<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHotelSearchAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Helper to create a hotel.
     */
    protected function createHotel(array $attributes = []): Hotel
    {
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $hotel = Hotel::create(array_merge([
            'name' => 'Default Hotel',
            'category' => '3-star',
            'location' => 'Yangon',
        ], $attributes));

        if ($createdAt) {
            $hotel->created_at = $createdAt;
            $hotel->save();
        }

        return $hotel;
    }

    /**
     * Test admin can access hotels list with pagination.
     */
    public function test_admin_can_access_hotels_list_with_pagination(): void
    {
        // Create 15 hotels
        for ($i = 0; $i < 15; $i++) {
            $this->createHotel([
                'name' => "Hotel Name " . sprintf("%02d", $i),
                'created_at' => now()->addMinutes($i)
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.hotels.index'));

        $response->assertStatus(200);
        $response->assertSee('Hotel Management');

        // Page 1 (latest first: Hotel Name 14 down to 05)
        $response->assertSee('Hotel Name 14');
        $response->assertSee('Hotel Name 05');
        $response->assertDontSee('Hotel Name 04');

        // Page 2 (Hotel Name 04 down to 00)
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.hotels.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Hotel Name 04');
        $responsePage2->assertSee('Hotel Name 00');
        $responsePage2->assertDontSee('Hotel Name 14');
    }

    /**
     * Test search by hotel name.
     */
    public function test_search_by_hotel_name(): void
    {
        $this->createHotel(['name' => 'Bagan Lodge']);
        $this->createHotel(['name' => 'Yangon Hotel']);

        $response = $this->actingAs($this->admin)->get(route('admin.hotels.index', ['search' => 'bagan']));

        $response->assertStatus(200);
        $response->assertSee('Bagan Lodge');
        $response->assertDontSee('Yangon Hotel');
    }


    /**
     * Test search by location.
     */
    public function test_search_by_location(): void
    {
        $this->createHotel(['name' => 'Hotel In Bagan', 'location' => 'Bagan']);
        $this->createHotel(['name' => 'Hotel In Mandalay', 'location' => 'Mandalay']);

        $response = $this->actingAs($this->admin)->get(route('admin.hotels.index', ['search' => 'mandalay']));

        $response->assertStatus(200);
        $response->assertSee('Hotel In Mandalay');
        $response->assertDontSee('Hotel In Bagan');
    }

    /**
     * Test combined search and pagination preservation.
     */
    public function test_search_and_pagination_together(): void
    {
        // Create 15 Bagan hotels
        for ($i = 0; $i < 15; $i++) {
            $this->createHotel([
                'name' => "Bagan Hotel " . sprintf("%02d", $i),
                'location' => 'Bagan',
                'created_at' => now()->addMinutes($i)
            ]);
        }

        // Create 5 Yangon hotels
        for ($i = 0; $i < 5; $i++) {
            $this->createHotel([
                'name' => "Yangon Hotel " . sprintf("%02d", $i),
                'location' => 'Yangon',
                'created_at' => now()->addMinutes($i + 15)
            ]);
        }

        // Search for Bagan - page 1
        $response = $this->actingAs($this->admin)->get(route('admin.hotels.index', ['search' => 'bagan']));
        $response->assertStatus(200);
        $response->assertSee('Bagan Hotel 14');
        $response->assertSee('Bagan Hotel 05');
        $response->assertDontSee('Bagan Hotel 04');
        $response->assertDontSee('Yangon Hotel');

        // Check query param in links
        $response->assertSee('search=bagan');
        $response->assertSee('page=2');

        // Page 2
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.hotels.index', ['search' => 'bagan', 'page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Bagan Hotel 04');
        $responsePage2->assertSee('Bagan Hotel 00');
        $responsePage2->assertDontSee('Bagan Hotel 14');
        $responsePage2->assertDontSee('Yangon Hotel');
    }

    /**
     * Test empty results message.
     */
    public function test_empty_search_results(): void
    {
        $this->createHotel(['name' => 'Unique Hotel Name']);

        $response = $this->actingAs($this->admin)->get(route('admin.hotels.index', ['search' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertSee('No hotels found');
    }
}
