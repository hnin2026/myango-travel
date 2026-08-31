<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\Itinerary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourItineraryRichTextTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        
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
     * Test admin can create a tour with rich text itineraries and they are saved correctly.
     */
    public function test_admin_can_create_tour_with_itineraries(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tours.store'), [
            'title' => 'Test Tour Bagan',
            'duration_days' => 2,
            'location' => 'Bagan',
            'status' => 'active',
            'itineraries' => [
                [
                    'description_en' => '<p><strong>Day 1:</strong> Arrival in Yangon</p>',
                    'description_mm' => '<p><strong>နေ့ ၁:</strong> ရန်ကုန်သို့ ရောက်ရှိခြင်း</p>',
                ],
                [
                    'description_en' => '<p><strong>Day 2:</strong> Yangon Pagoda Tour</p>',
                    'description_mm' => '<p><strong>နေ့ ၂:</strong> ရန်ကုန် ဘုရားဖူး ခရီးစဉ်</p>',
                ]
            ]
        ]);

        $response->assertStatus(302);

        $tour = Tour::where('title', 'Test Tour Bagan')->first();
        $this->assertNotNull($tour);

        $this->assertCount(2, $tour->itineraries);
        $this->assertDatabaseHas('itineraries', [
            'tour_id' => $tour->id,
            'day_number' => 1,
            'description_en' => '<p><strong>Day 1:</strong> Arrival in Yangon</p>',
            'description_mm' => '<p><strong>နေ့ ၁:</strong> ရန်ကုန်သို့ ရောက်ရှိခြင်း</p>',
        ]);
    }

    /**
     * Test admin can update a tour's itineraries correctly.
     */
    public function test_admin_can_update_tour_itineraries(): void
    {
        $tour = Tour::create([
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100.00,
            'status' => 'active',
        ]);

        $tour->itineraries()->create([
            'day_number' => 1,
            'description_en' => 'Old Day 1',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.tours.update', $tour), [
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'base_price' => 100.00,
            'status' => 'active',
            'itineraries' => [
                [
                    'description_en' => '<p>New Day 1 content</p>',
                    'description_mm' => '<p>နေ့ ၁ အသစ်</p>',
                ],
                [
                    'description_en' => '<p>New Day 2 content</p>',
                ]
            ]
        ]);

        $response->assertRedirect(route('admin.tours.index'));

        $this->assertCount(2, $tour->fresh()->itineraries);
        $this->assertDatabaseHas('itineraries', [
            'tour_id' => $tour->id,
            'day_number' => 1,
            'description_en' => '<p>New Day 1 content</p>',
        ]);
        $this->assertDatabaseMissing('itineraries', [
            'tour_id' => $tour->id,
            'description_en' => 'Old Day 1',
        ]);
    }

    /**
     * Test customer tour detail page renders the itinerary html safely (unescaped).
     */
    public function test_customer_tour_page_renders_itinerary_html_safely(): void
    {
        $tour = Tour::create([
            'title' => 'Active Tour Bagan',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $tour->itineraries()->create([
            'day_number' => 1,
            'description_en' => '<h3>Day 1: Arrival</h3><p>Transfer to hotel.</p>',
            'description_mm' => '<h3>နေ့ ၁: ရောက်ရှိခြင်း</h3><p>ဟိုတယ်သို့ ပို့ဆောင်ခြင်း။</p>',
        ]);

        $response = $this->get(route('tours.show', $tour));

        $response->assertStatus(200);
        $response->assertSee('<h3>Day 1: Arrival</h3><p>Transfer to hotel.</p>', false);
    }

    /**
     * Test admin can delete tour image and file.
     */
    public function test_admin_can_delete_tour_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        
        $tour = Tour::create([
            'title' => 'Bagan Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $imageFile = \Illuminate\Http\UploadedFile::fake()->image('tour1.jpg');
        $path = $imageFile->store('tours', 'public');

        $image = $tour->images()->create([
            'image_path' => $path,
            'order' => 0,
        ]);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->admin)->delete(route('admin.tours.images.destroy', $image));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('tour_images', [
            'id' => $image->id,
        ]);

        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($path);
    }

    /**
     * Test guest/unauthorized cannot delete tour image.
     */
    public function test_guest_cannot_delete_tour_image(): void
    {
        $tour = Tour::create([
            'title' => 'Bagan Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $image = $tour->images()->create([
            'image_path' => 'tours/fake.jpg',
            'order' => 0,
        ]);

        $response = $this->delete(route('admin.tours.images.destroy', $image));

        $response->assertStatus(302); // Redirects to login
        $this->assertDatabaseHas('tour_images', [
            'id' => $image->id,
        ]);
    }
}
