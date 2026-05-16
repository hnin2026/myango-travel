<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'title',
        'title_mm',
        'description_en',
        'description_mm',
        'additional_info_en',
        'additional_info_mm',
        'duration_days',
        'base_price',
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
        return $this->hasMany(Itinerary::class)->orderBy('day_number');
    }

    public function availableDates()
    {
        return $this->hasMany(AvailableDate::class);
    }

    public function images()
    {
        return $this->hasMany(TourImage::class)->orderBy('order');
    }
}