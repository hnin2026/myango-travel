<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTourSearchAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $hotelYangon;
    protected $hotelBagan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create hotels to allow setting locations on tours
        $this->hotelYangon = Hotel::create([
            'name' => 'Yangon Garden Hotel',
            'category' => '4-star',
            'location' => 'Yangon'
        ]);

        $this->hotelBagan = Hotel::create([
            'name' => 'Bagan Lodge',
            'category' => '4-star',
            'location' => 'Bagan'
        ]);
    }

    /**
     * Create a dummy tour helper.
     */
    protected function createTour(array $attributes = []): Tour
    {
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $tour = Tour::create(array_merge([
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
            'base_price' => 100.00,
        ], $attributes));

        if ($createdAt) {
            $tour->created_at = $createdAt;
            $tour->save();
        }

        return $tour;
    }

    /**
     * Test admin can access tour list page and view pagination.
     */
    public function test_admin_can_access_tours_list_with_pagination(): void
    {
        // Create 15 tours
        for ($i = 0; $i < 15; $i++) {
            $this->createTour([
                'title' => "Tour Title " . sprintf("%02d", $i),
                'created_at' => now()->addMinutes($i)
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.tours.index'));

        $response->assertStatus(200);
        $response->assertSee('Tour Management');
        
        // Assert page 1 shows newest first (Title 14 down to Title 05)
        $response->assertSee('Tour Title 14');
        $response->assertSee('Tour Title 05');
        $response->assertDontSee('Tour Title 04');

        // Go to page 2
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.tours.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Tour Title 04');
        $responsePage2->assertSee('Tour Title 00');
        $responsePage2->assertDontSee('Tour Title 14');
    }

    /**
     * Test searching by tour name (case-insensitive).
     */
    public function test_search_by_tour_name(): void
    {
        $tour1 = $this->createTour(['title' => 'Bagan Magic Sunset']);
        $tour2 = $this->createTour(['title' => 'Yangon Explorer']);

        // Case-insensitive search
        $response = $this->actingAs($this->admin)->get(route('admin.tours.index', ['search' => 'magic']));

        $response->assertStatus(200);
        $response->assertSee('Bagan Magic Sunset');
        $response->assertDontSee('Yangon Explorer');

        // Upper case search
        $response2 = $this->actingAs($this->admin)->get(route('admin.tours.index', ['search' => 'MAGIC']));
        $response2->assertStatus(200);
        $response2->assertSee('Bagan Magic Sunset');
    }

    /**
     * Test searching by destination/location (case-insensitive).
     */
    public function test_search_by_location(): void
    {
        $tour1 = $this->createTour(['title' => 'Bagan Magic', 'location' => 'Bagan']);
        $tour2 = $this->createTour(['title' => 'Yangon Explorer', 'location' => 'Yangon']);

        // Search lowercase
        $response = $this->actingAs($this->admin)->get(route('admin.tours.index', ['search' => 'yangon']));

        $response->assertStatus(200);
        $response->assertSee('Yangon Explorer');
        $response->assertDontSee('Bagan Magic');
    }

    /**
     * Test search and pagination work together.
     */
    public function test_search_and_pagination_together(): void
    {
        // Create 15 tours with "Bagan" in location/destination
        for ($i = 0; $i < 15; $i++) {
            $this->createTour([
                'title' => "Bagan Adventure " . sprintf("%02d", $i),
                'location' => 'Bagan',
                'created_at' => now()->addMinutes($i)
            ]);
        }

        // Create 5 tours in "Yangon"
        for ($i = 0; $i < 5; $i++) {
            $this->createTour([
                'title' => "Yangon City Tour " . sprintf("%02d", $i),
                'location' => 'Yangon',
                'created_at' => now()->addMinutes($i + 15)
            ]);
        }

        // Search for Bagan - page 1
        $response = $this->actingAs($this->admin)->get(route('admin.tours.index', ['search' => 'Bagan']));
        $response->assertStatus(200);
        $response->assertSee('Bagan Adventure 14');
        $response->assertSee('Bagan Adventure 05');
        $response->assertDontSee('Bagan Adventure 04');
        $response->assertDontSee('Yangon City Tour');

        // Check search query is preserved in pagination links
        $response->assertSee('search=Bagan');
        $response->assertSee('page=2');

        // Page 2
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.tours.index', ['search' => 'Bagan', 'page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Bagan Adventure 04');
        $responsePage2->assertSee('Bagan Adventure 00');
        $responsePage2->assertDontSee('Bagan Adventure 14');
        $responsePage2->assertDontSee('Yangon City Tour');
    }

    /**
     * Test empty results message is displayed when no records match search.
     */
    public function test_empty_search_results(): void
    {
        $this->createTour(['title' => 'Yangon Tour']);

        $response = $this->actingAs($this->admin)->get(route('admin.tours.index', ['search' => 'NonExistent']));

        $response->assertStatus(200);
        $response->assertSee('No tours found.');
        $response->assertDontSee('Yangon Tour');
    }
}
