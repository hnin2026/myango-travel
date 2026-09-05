<?php

namespace Tests\Feature;

use App\Models\SeasonPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonPeriodValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 — Creating a season period with end date before start date fails validation.
     */
    public function test_end_date_before_start_date_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.season-periods.store'), [
            'name'       => 'Invalid Season',
            'season'     => 'peak',
            'start_date' => '2026-12-25',
            'end_date'   => '2026-12-01',
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseMissing('season_periods', [
            'name' => 'Invalid Season',
        ]);
    }

    /**
     * Test 2 — Creating a season period with end date equal to start date succeeds.
     */
    public function test_end_date_equal_to_start_date_succeeds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.season-periods.store'), [
            'name'       => 'One Day Peak Season',
            'season'     => 'peak',
            'start_date' => '2026-12-25',
            'end_date'   => '2026-12-25',
        ]);

        $response->assertRedirect(route('admin.season-periods.index'));
        $this->assertDatabaseHas('season_periods', [
            'name'       => 'One Day Peak Season',
            'season'     => 'peak',
            'start_date' => '2026-12-25 00:00:00',
            'end_date'   => '2026-12-25 00:00:00',
        ]);
    }

    /**
     * Test 3 — Creating a season period with end date after start date succeeds.
     */
    public function test_end_date_after_start_date_succeeds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.season-periods.store'), [
            'name'       => 'Valid Peak Season',
            'season'     => 'peak',
            'start_date' => '2026-12-01',
            'end_date'   => '2026-12-25',
        ]);

        $response->assertRedirect(route('admin.season-periods.index'));
        $this->assertDatabaseHas('season_periods', [
            'name'       => 'Valid Peak Season',
            'season'     => 'peak',
            'start_date' => '2026-12-01 00:00:00',
            'end_date'   => '2026-12-25 00:00:00',
        ]);
    }

    /**
     * Test 4 — Editing an existing season period with end date before start date fails validation.
     */
    public function test_edit_season_period_with_invalid_date_order_fails(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $seasonPeriod = SeasonPeriod::create([
            'name'       => 'Existing Season',
            'season'     => 'low',
            'start_date' => '2026-06-01',
            'end_date'   => '2026-08-31',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.season-periods.update', $seasonPeriod), [
            'name'       => 'Updated Season',
            'season'     => 'low',
            'start_date' => '2026-12-25',
            'end_date'   => '2026-12-01',
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $seasonPeriod->refresh();
        $this->assertEquals('Existing Season', $seasonPeriod->name);
        $this->assertEquals('2026-06-01 00:00:00', $seasonPeriod->start_date->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-08-31 00:00:00', $seasonPeriod->end_date->format('Y-m-d H:i:s'));
    }
}
