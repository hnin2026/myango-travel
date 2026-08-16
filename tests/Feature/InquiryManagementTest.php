<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\Inquiry;
use App\Mail\AdminInquirySubmittedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tour;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create a hotel and tour for testing
        $hotel = Hotel::create([
            'name' => 'Bagan Lodge Test',
            'category' => '4-star',
            'location' => 'Bagan',
            'price_low' => 10.00,
            'price_normal' => 20.00,
            'price_peak' => 30.00,
        ]);

        $this->tour = Tour::create([
            'title' => 'Bagan Cultural Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);
    }

    /**
     * Test a customer can submit an inquiry for an existing tour.
     */
    public function test_customer_can_submit_inquiry_with_tour(): void
    {
        Mail::fake();

        $response = $this->post(route('inquiry.store'), [
            'tour_id' => $this->tour->id,
            'customer_name' => 'Jenny',
            'nationality' => 'Myanmar',
            'email' => 'customer@example.com',
            'phone' => '+66 999 888 777',
            'number_of_adults' => 2,
            'number_of_children' => 1,
            'checkin_date' => '2026-09-19',
            'checkout_date' => '2026-09-22',
            'message' => 'I want to know whether I can extend my stay by one night.',
        ]);

        // Verifies redirect to success route
        $inquiry = Inquiry::latest()->first();
        $this->assertNotNull($inquiry);
        $response->assertRedirect(route('inquiry.success', $inquiry->id));

        // Verifies database record
        $this->assertEquals('INQ-0001', $inquiry->reference);
        $this->assertEquals('new', $inquiry->status);

        // Verifies email was sent to admin
        Mail::assertSent(AdminInquirySubmittedMail::class, function ($mail) use ($inquiry) {
            return $mail->inquiry->id === $inquiry->id &&
                   $mail->hasTo(env('ADMIN_NOTIFICATION_EMAIL', 'admin@myango.com'));
        });
    }

    /**
     * Test a customer can submit a general inquiry (tour_id is null/optional).
     */
    public function test_customer_can_submit_general_inquiry(): void
    {
        Mail::fake();

        $response = $this->post(route('inquiry.store'), [
            'tour_id' => null,
            'customer_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'number_of_adults' => 1,
            'message' => 'This is a general inquiry about Myanmar tours.',
        ]);

        $inquiry = Inquiry::latest()->first();
        $this->assertNotNull($inquiry);
        $response->assertRedirect(route('inquiry.success', $inquiry->id));
        $this->assertNull($inquiry->tour_id);
    }

    /**
     * Test validation requires customer name, email, etc.
     */
    public function test_submit_inquiry_validation_errors(): void
    {
        $response = $this->post(route('inquiry.store'), [
            'tour_id' => 99999, // Non-existent tour
            'customer_name' => '',
            'email' => 'not-an-email',
            'number_of_adults' => 0, // Min is 1
        ]);

        $response->assertSessionHasErrors(['tour_id', 'customer_name', 'email', 'number_of_adults']);
    }

    /**
     * Test success page renders correctly with details.
     */
    public function test_inquiry_success_page_renders(): void
    {
        $inquiry = Inquiry::create([
            'customer_name' => 'Jenny',
            'email' => 'customer@example.com',
            'number_of_adults' => 2,
            'status' => 'new',
        ]);

        $response = $this->get(route('inquiry.success', $inquiry->id));
        $response->assertStatus(200);
        $response->assertSee($inquiry->reference);
        $response->assertSee('Jenny');
        $response->assertSee('Inquiry Submitted!');
        $response->assertSee('Custom Tour');
    }

    /**
     * Test admin can view and filter the list of inquiries.
     */
    public function test_admin_can_view_and_filter_inquiries(): void
    {
        // Create 2 inquiries with different statuses
        $inq1 = Inquiry::create([
            'customer_name' => 'Inquiry One',
            'email' => 'one@example.com',
            'status' => 'new',
        ]);

        $inq2 = Inquiry::create([
            'customer_name' => 'Inquiry Two',
            'email' => 'two@example.com',
            'status' => 'in_progress',
        ]);

        // Access index page
        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));
        $response->assertStatus(200);
        $response->assertSee('Inquiry One');
        $response->assertSee('Inquiry Two');
        $response->assertSee('Custom Tour');

        // Filter by 'new'
        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'new']));
        $response->assertStatus(200);
        $response->assertSee('Inquiry One');
        $response->assertDontSee('Inquiry Two');

        // Filter by 'in_progress'
        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'in_progress']));
        $response->assertStatus(200);
        $response->assertDontSee('Inquiry One');
        $response->assertSee('Inquiry Two');
    }

    /**
     * Test admin can update inquiry status from details page.
     */
    public function test_admin_can_update_inquiry_status(): void
    {
        $inquiry = Inquiry::create([
            'customer_name' => 'Jenny',
            'email' => 'customer@example.com',
            'status' => 'new',
        ]);

        // Verify show page displays info
        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.show', $inquiry->id));
        $response->assertStatus(200);
        $response->assertSee('Reply via Email');
        $response->assertSee('Custom Tour');

        // Update status to in_progress
        $response = $this->actingAs($this->admin)->put(route('admin.inquiries.update', $inquiry->id), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('admin.inquiries.show', $inquiry->id));
        $this->assertEquals('in_progress', $inquiry->fresh()->status);

        // Update status to confirmed
        $response = $this->actingAs($this->admin)->put(route('admin.inquiries.update', $inquiry->id), [
            'status' => 'confirmed',
        ]);
        $this->assertEquals('confirmed', $inquiry->fresh()->status);
    }
}
