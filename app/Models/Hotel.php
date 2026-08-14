<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'category',
        'location'
    ];

    public static function normalizeLocation(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value));

        $existing = self::whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location')
            ->distinct()
            ->get();

        foreach ($existing as $hotel) {
            if (strcasecmp($hotel->location, $normalized) === 0) {
                return $hotel->location;
            }
        }

        return ucwords(strtolower($normalized));
    }

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_hotels');
    }

    public function seasonPrices()
    {
        return $this->hasMany(HotelSeasonPrice::class);
    }

    public function getPriceForSeason(string $season): float
    {
        $seasonPrice = $this->seasonPrices()
            ->where('season', $season)
            ->first();
        return $seasonPrice ? (float) $seasonPrice->upgrade_price : 0;
    }
}