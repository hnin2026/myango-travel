<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create hotels since tours location is validated against hotel locations
        Hotel::create([
            'name' => 'Bagan Lodge Test',
            'category' => '4-star',
            'location' => 'Bagan',
        ]);
        Hotel::create([
            'name' => 'Yangon Hotel Test',
            'category' => '5-star',
            'location' => 'Yangon',
        ]);
        Hotel::create([
            'name' => 'Mandalay Hotel Test',
            'category' => '3-star',
            'location' => 'Mandalay',
        ]);
    }

    /**
     * Test homepage displays a maximum of 6 tours, newest first, active only.
     */
    public function test_homepage_shows_latest_6_active_tours_newest_first(): void
    {
        // Create 8 active tours and 1 inactive tour
        for ($i = 1; $i <= 8; $i++) {
            $tour = Tour::create([
                'title' => "Active Tour {$i}",
                'duration_days' => 3,
                'location' => 'Bagan',
                'status' => 'active',
            ]);
            $tour->created_at = now()->addMinutes($i);
            $tour->save();
        }

        $inactiveTour = Tour::create([
            'title' => 'Inactive Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'inactive',
        ]);
        $inactiveTour->created_at = now()->addMinutes(10);
        $inactiveTour->save();

        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Should see the 6 newest active tours: Active Tour 8 down to Active Tour 3
        for ($i = 8; $i >= 3; $i--) {
            $response->assertSee("Active Tour {$i}");
        }

        // Should NOT see older active tours or the inactive tour
        $response->assertDontSee('Active Tour 2');
        $response->assertDontSee('Active Tour 1');
        $response->assertDontSee('Inactive Tour');
    }

    /**
     * Test homepage see more button works and links to tours index.
     */
    public function test_homepage_see_more_button_works(): void
    {
        Tour::create([
            'title' => 'Active Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('See More');
        $response->assertSee(route('tours.index'));
    }

    /**
     * Test tour listing page displays all active tours, paginated by 12.
     */
    public function test_tour_listing_page_displays_all_active_tours_paginated(): void
    {
        // Create 15 active tours (A to O)
        $letters = range('A', 'O');
        foreach ($letters as $index => $letter) {
            $tour = Tour::create([
                'title' => "Active Tour {$letter}",
                'duration_days' => 3,
                'location' => 'Bagan',
                'status' => 'active',
            ]);
            $tour->created_at = now()->addMinutes($index + 1);
            $tour->save();
        }

        $response = $this->get(route('tours.index'));

        $response->assertStatus(200);

        // Since newest first, page 1 shows Active Tour O down to D (12 tours)
        // range('D', 'O')
        foreach (range('D', 'O') as $letter) {
            $response->assertSee("Active Tour {$letter}");
        }

        // Page 1 should not see Active Tour C, B, A
        foreach (range('A', 'C') as $letter) {
            $response->assertDontSee("Active Tour {$letter}");
        }

        // Navigate to page 2
        $responsePage2 = $this->get(route('tours.index', ['page' => 2]));
        $responsePage2->assertStatus(200);

        // Page 2 shows Active Tour C, B, A
        foreach (range('A', 'C') as $letter) {
            $responsePage2->assertSee("Active Tour {$letter}");
        }
    }

    /**
     * Test filtering by destination works case-insensitively and updates list.
     */
    public function test_tour_listing_can_filter_by_destination_case_insensitively(): void
    {
        Tour::create([
            'title' => 'Bagan Adventure',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);

        Tour::create([
            'title' => 'Yangon Sightseeing',
            'duration_days' => 2,
            'location' => 'Yangon',
            'status' => 'active',
        ]);

        // Filter by lowercase 'bagan'
        $response = $this->get(route('tours.index', ['destination' => 'bagan']));

        $response->assertStatus(200);
        $response->assertSee('Bagan Adventure');
        $response->assertDontSee('Yangon Sightseeing');

        // Filter by uppercase 'YANGON'
        $responseYangon = $this->get(route('tours.index', ['destination' => 'YANGON']));

        $responseYangon->assertStatus(200);
        $responseYangon->assertSee('Yangon Sightseeing');
        $responseYangon->assertDontSee('Bagan Adventure');
    }

    /**
     * Test inactive tours are never shown publicly.
     */
    public function test_inactive_tours_are_never_shown_publicly(): void
    {
        Tour::create([
            'title' => 'Inactive Bagan Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'inactive',
        ]);

        // Homepage
        $this->get(route('home'))->assertDontSee('Inactive Bagan Tour');

        // Tours index
        $this->get(route('tours.index'))->assertDontSee('Inactive Bagan Tour');

        // Filtered tours index
        $this->get(route('tours.index', ['destination' => 'Bagan']))->assertDontSee('Inactive Bagan Tour');
    }

    /**
     * Test inactive destinations (no active tours) do not show in dynamic filters.
     */
    public function test_inactive_destinations_are_excluded_from_filter_list(): void
    {
        Tour::create([
            'title' => 'Mandalay Temple Tour',
            'duration_days' => 3,
            'location' => 'Mandalay',
            'status' => 'inactive',
        ]);

        Tour::create([
            'title' => 'Active Yangon Tour',
            'duration_days' => 2,
            'location' => 'Yangon',
            'status' => 'active',
        ]);

        // Mandalay location should not appear in destinations dropdown of navbar or page dropdown because its tour is inactive
        $response = $this->get(route('tours.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Yangon');
        $response->assertDontSee('Mandalay');
    }

    /**
     * Test pagination preserves the destination query parameter.
     */
    public function test_pagination_preserves_destination_filter(): void
    {
        // Create 15 active Bagan tours
        for ($i = 1; $i <= 15; $i++) {
            Tour::create([
                'title' => "Bagan Tour {$i}",
                'duration_days' => 3,
                'location' => 'Bagan',
                'status' => 'active',
            ]);
        }

        $response = $this->get(route('tours.index', ['destination' => 'Bagan']));

        $response->assertStatus(200);
        
        // Assert that the page 2 pagination link includes destination=Bagan
        $response->assertSee('destination=Bagan');
    }

    /**
     * Test empty results handled gracefully for destinations with no active tours.
     */
    public function test_empty_destination_results_handled_gracefully(): void
    {
        // Let's query Yangon where no active tours exist
        $response = $this->get(route('tours.index', ['destination' => 'Yangon']));

        $response->assertStatus(200);
        $response->assertSee('No tour packages are currently available for this destination.');
        $response->assertSee('Return to All Destinations');
    }

    /**
     * Test dynamic active destination dropdown list displays in the navbar.
     */
    public function test_destinations_dropdown_displays_correctly_on_navbar(): void
    {
        // 1. Create an active Tour in Bagan and an inactive Tour in Mandalay
        Tour::create([
            'title' => 'Bagan Active Tour',
            'duration_days' => 3,
            'location' => 'Bagan',
            'status' => 'active',
        ]);
        
        Tour::create([
            'title' => 'Mandalay Inactive Tour',
            'duration_days' => 4,
            'location' => 'Mandalay',
            'status' => 'inactive',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        
        // Assert Bagan (active tour location) is visible in the dropdown
        $response->assertSee('Bagan');
        
        // Assert Mandalay (inactive tour location) is NOT visible in the dropdown
        $response->assertDontSee('Mandalay');

        // Assert navbar contains Destinations positioned correctly (between Tours and Contact)
        $html = $response->getContent();
        
        // Let's assert the structural ordering: Tours, then Destinations, then Contact
        $toursPos = strpos($html, 'Tours</a>');
        $destinationsPos = strpos($html, 'Destinations');
        $contactPos = strpos($html, 'Contact</a>');
        
        $this->assertNotFalse($toursPos, 'Tours link not found in navbar');
        $this->assertNotFalse($destinationsPos, 'Destinations dropdown not found in navbar');
        $this->assertNotFalse($contactPos, 'Contact link not found in navbar');
        
        $this->assertTrue($toursPos < $destinationsPos, 'Tours link must appear before Destinations');
        $this->assertTrue($destinationsPos < $contactPos, 'Destinations must appear before Contact');
    }

    /**
     * Test mobile navigation toggler element is present on the layout.
     */
    public function test_mobile_navigation_toggler_is_present(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('nav-mobile-toggle');
        $response->assertSee('toggleMobileMenu');
    }
}
