<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'title',
        'description',
        'description_mm',
        'duration_days',
        'location',
        'thumbnail',
        'status'
    ];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'tour_hotels');
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function availableDates()
    {
        return $this->hasMany(AvailableDate::class);
    }
}