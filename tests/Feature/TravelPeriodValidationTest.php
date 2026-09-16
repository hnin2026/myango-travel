<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TravelPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelPeriodValidationTest extends TestCase
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
            'title'         => 'Test Paradise Tour',
            'duration_days' => 5,
            'base_price'    => 300.00,
            'location'      => 'Mandalay',
            'status'        => 'active',
        ]);
    }

    /**
     * Test 1 — Creating a travel period with end date before start date fails validation.
     */
    public function test_end_date_before_start_date_fails_validation(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.tours.travel-periods.store', $this->tour),
            [
                'start_date'  => '2026-12-25',
                'end_date'    => '2026-12-01',
                'total_seats' => 20,
            ]
        );

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseMissing('travel_periods', [
            'tour_id'    => $this->tour->id,
            'start_date' => '2026-12-25 00:00:00',
            'end_date'   => '2026-12-01 00:00:00',
        ]);
    }

    /**
     * Test 2 — Creating a travel period with end date after start date succeeds.
     */
    public function test_end_date_after_start_date_succeeds(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.tours.travel-periods.store', $this->tour),
            [
                'start_date'  => '2026-12-01',
                'end_date'    => '2026-12-25',
                'total_seats' => 20,
            ]
        );

        $response->assertRedirect(route('admin.tours.travel-periods.index', $this->tour));
        $this->assertDatabaseHas('travel_periods', [
            'tour_id'     => $this->tour->id,
            'start_date'  => '2026-12-01 00:00:00',
            'end_date'    => '2026-12-25 00:00:00',
            'total_seats' => 20,
        ]);
    }

    /**
     * Test 3 — Creating a travel period with end date equal to start date succeeds.
     */
    public function test_end_date_equal_to_start_date_succeeds(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.tours.travel-periods.store', $this->tour),
            [
                'start_date'  => '2026-12-25',
                'end_date'    => '2026-12-25',
                'total_seats' => 20,
            ]
        );

        $response->assertRedirect(route('admin.tours.travel-periods.index', $this->tour));
        $this->assertDatabaseHas('travel_periods', [
            'tour_id'     => $this->tour->id,
            'start_date'  => '2026-12-25 00:00:00',
            'end_date'    => '2026-12-25 00:00:00',
            'total_seats' => 20,
        ]);
    }

    /**
     * Test 4 — Editing an existing travel period with end date before start date fails validation.
     */
    public function test_edit_travel_period_with_invalid_date_order_fails(): void
    {
        $travelPeriod = TravelPeriod::create([
            'tour_id'     => $this->tour->id,
            'start_date'  => '2026-06-01',
            'end_date'    => '2026-08-31',
            'total_seats' => 15,
            'booked_seats' => 0,
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.tours.travel-periods.update', [$this->tour, $travelPeriod]),
            [
                'start_date'  => '2026-12-25',
                'end_date'    => '2026-12-01',
                'total_seats' => 15,
            ]
        );

        $response->assertSessionHasErrors(['end_date']);
        $travelPeriod->refresh();
        $this->assertEquals('2026-06-01 00:00:00', $travelPeriod->start_date->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-08-31 00:00:00', $travelPeriod->end_date->format('Y-m-d H:i:s'));
    }
}
