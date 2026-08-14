<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Hotel;
use App\Models\Tour;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fetch all hotels and tours
        $hotels = Hotel::all();
        $tours = Tour::all();

        // Helper to normalize format: trim, collapse spaces, title case
        $normalize = function ($str) {
            if (empty($str)) return '';
            $cleaned = preg_replace('/\s+/', ' ', trim($str));
            return ucwords(strtolower($cleaned));
        };

        // Normalize hotels
        foreach ($hotels as $hotel) {
            if ($hotel->location) {
                $hotel->location = $normalize($hotel->location);
                $hotel->save();
            }
        }

        // Normalize tours
        foreach ($tours as $tour) {
            if ($tour->location) {
                $tour->location = $normalize($tour->location);
                $tour->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse action required
    }
};
