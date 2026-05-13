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