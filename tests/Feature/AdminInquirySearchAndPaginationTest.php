<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInquirySearchAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Create dummy inquiries helper.
     */
    protected function createInquiry(array $attributes = []): Inquiry
    {
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $inquiry = Inquiry::create(array_merge([
            'customer_name' => 'Inquiry Customer',
            'email' => 'customer@example.com',
            'phone' => '0912345678',
            'status' => 'new',
        ], $attributes));

        if ($createdAt) {
            $inquiry->created_at = $createdAt;
            $inquiry->save();
        }

        return $inquiry;
    }

    /**
     * Test admin can access inquiries list with pagination.
     */
    public function test_admin_can_access_inquiries_list_with_pagination(): void
    {
        // Create 15 inquiries
        for ($i = 0; $i < 15; $i++) {
            $this->createInquiry([
                'created_at' => now()->addMinutes($i)
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Inquiries');

        // Should see 10 items on page 1 (INQ-0015 down to INQ-0006 due to latest first)
        $response->assertSee('INQ-0015');
        $response->assertSee('INQ-0006');
        $response->assertDontSee('INQ-0005');

        // Go to page 2
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('INQ-0005');
        $responsePage2->assertSee('INQ-0001');
        $responsePage2->assertDontSee('INQ-0015');
    }

    /**
     * Test filtering by status.
     */
    public function test_filter_by_status(): void
    {
        $inquiryNew = $this->createInquiry(['status' => 'new']); // ID 1 -> INQ-0001
        $inquiryConfirmed = $this->createInquiry(['status' => 'confirmed']); // ID 2 -> INQ-0002

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'confirmed']));

        $response->assertStatus(200);
        $response->assertSee('INQ-0002');
        $response->assertDontSee('INQ-0001');
    }

    /**
     * Test filter status and pagination work together.
     */
    public function test_filter_and_pagination_together(): void
    {
        // Create 15 confirmed inquiries
        for ($i = 0; $i < 15; $i++) {
            $this->createInquiry([
                'status' => 'confirmed',
                'created_at' => now()->addMinutes($i)
            ]);
        }

        // Create 5 new inquiries
        for ($i = 0; $i < 5; $i++) {
            $this->createInquiry([
                'status' => 'new',
                'created_at' => now()->addMinutes($i + 15)
            ]);
        }

        // Filter for confirmed - page 1
        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'confirmed']));
        $response->assertStatus(200);
        $response->assertSee('INQ-0015');
        $response->assertSee('INQ-0006');
        $response->assertDontSee('INQ-0005');
        $response->assertDontSee('INQ-0016');

        // Check if query is preserved in links
        $response->assertSee('status=confirmed');
        $response->assertSee('page=2');

        // Page 2
        $responsePage2 = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'confirmed', 'page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('INQ-0005');
        $responsePage2->assertSee('INQ-0001');
        $responsePage2->assertDontSee('INQ-0015');
    }

    /**
     * Test empty results message displays.
     */
    public function test_empty_filter_results(): void
    {
        $this->createInquiry(['status' => 'new']);

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'confirmed']));

        $response->assertStatus(200);
        $response->assertSee('No inquiries found');
    }

    /**
     * Test admin inquiry list displays sequential row numbers without gaps after deletion.
     */
    public function test_admin_inquiry_list_displays_sequential_row_numbers_without_gaps(): void
    {
        $inq1 = $this->createInquiry(['customer_name' => 'Alice']);
        $inq2 = $this->createInquiry(['customer_name' => 'Bob']);
        $inq3 = $this->createInquiry(['customer_name' => 'Charlie']);

        // Delete middle inquiry (ID 2)
        $inq2->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));

        $response->assertStatus(200);
        // Verify references are preserved
        $response->assertSee($inq3->reference);
        $response->assertSee($inq1->reference);
        // Verify DB IDs remain unchanged
        $this->assertEquals(1, $inq1->id);
        $this->assertEquals(3, $inq3->id);

        // Total remaining is 2. Top row (newest) gets 2, bottom row (oldest) gets 1
        $response->assertSee('<td>2</td>', false);
        $response->assertSee('<td>1</td>', false);
    }

    /**
     * Test admin inquiry list row numbers are pagination aware in reverse total order.
     */
    public function test_admin_inquiry_list_row_numbers_are_pagination_aware(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->createInquiry([
                'created_at' => now()->addMinutes($i)
            ]);
        }

        // Page 1 (top 10 newest items) should display 15 down to 6
        $response1 = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));
        $response1->assertStatus(200);
        $response1->assertSee('<td>15</td>', false);
        $response1->assertSee('<td>6</td>', false);

        // Page 2 (bottom 5 oldest items) should display 5 down to 1
        $response2 = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['page' => 2]));
        $response2->assertStatus(200);
        $response2->assertSee('<td>5</td>', false);
        $response2->assertSee('<td>1</td>', false);
    }
}
