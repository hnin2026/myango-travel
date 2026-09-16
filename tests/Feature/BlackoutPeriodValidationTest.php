<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourBlackoutPeriod;
use App\Models\TravelPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlackoutPeriodValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Tour $tour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->tour = Tour::create([
            'title'         => 'Bagan Temple Tour',
            'duration_days' => 3,
            'base_price'    => 200.00,
            'location'      => 'Bagan',
            'status'        => 'active',
        ]);

        // Define operating period covering December 2026
        TravelPeriod::create([
            'tour_id'     => $this->tour->id,
            'start_date'  => '2026-12-01',
            'end_date'    => '2026-12-31',
            'total_seats' => 20,
            'booked_seats' => 0,
        ]);
    }

    /**
     * Test 1 — Creating a Blackout Period with End Date before Start Date fails validation.
     * Start Date: 2026-12-25, End Date: 2026-12-01
     */
    public function test_blackout_period_end_date_before_start_date_fails_validation(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.tours.blackouts.store', $this->tour),
            [
                'start_date' => '2026-12-25',
                'end_date'   => '2026-12-01',
            ]
        );

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseMissing('tour_blackout_periods', [
            'tour_id'    => $this->tour->id,
            'start_date' => '2026-12-25 00:00:00',
            'end_date'   => '2026-12-01 00:00:00',
        ]);
    }

    /**
     * Test 2 — Creating a Blackout Period with End Date after Start Date succeeds.
     * Start Date: 2026-12-01, End Date: 2026-12-25
     */
    public function test_blackout_period_end_date_after_start_date_succeeds(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.tours.blackouts.store', $this->tour),
            [
                'start_date' => '2026-12-01',
                'end_date'   => '2026-12-25',
            ]
        );

        $response->assertRedirect(route('admin.tours.travel-periods.index', $this->tour));
        $this->assertDatabaseHas('tour_blackout_periods', [
            'tour_id'    => $this->tour->id,
            'start_date' => '2026-12-01 00:00:00',
            'end_date'   => '2026-12-25 00:00:00',
        ]);
    }

    /**
     * Test 3 — Creating a Blackout Period with same-day Start and End Date succeeds.
     * Start Date: 2026-12-25, End Date: 2026-12-25
     */
    public function test_blackout_period_same_day_start_and_end_date_succeeds(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.tours.blackouts.store', $this->tour),
            [
                'start_date' => '2026-12-25',
                'end_date'   => '2026-12-25',
            ]
        );

        $response->assertRedirect(route('admin.tours.travel-periods.index', $this->tour));
        $this->assertDatabaseHas('tour_blackout_periods', [
            'tour_id'    => $this->tour->id,
            'start_date' => '2026-12-25 00:00:00',
            'end_date'   => '2026-12-25 00:00:00',
        ]);
    }

    /**
     * Test 4 — Updating an existing Blackout Period with End Date earlier than Start Date fails validation.
     * Target: Existing valid Blackout Period (2026-12-05 to 2026-12-10)
     * Action: Update End Date to date earlier than Start Date (Start: 2026-12-25, End: 2026-12-01)
     */
    public function test_edit_blackout_period_with_invalid_date_order_fails(): void
    {
        $blackout = TourBlackoutPeriod::create([
            'tour_id'    => $this->tour->id,
            'start_date' => '2026-12-05',
            'end_date'   => '2026-12-10',
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.tours.blackouts.update', [$this->tour, $blackout]),
            [
                'start_date' => '2026-12-25',
                'end_date'   => '2026-12-01',
            ]
        );

        $response->assertSessionHasErrors(['end_date']);
        $blackout->refresh();
        $this->assertEquals('2026-12-05 00:00:00', $blackout->start_date->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-12-10 00:00:00', $blackout->end_date->format('Y-m-d H:i:s'));
    }
}
