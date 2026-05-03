<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price_per_person',
        'location'
    ];

    public function tours()
    {
        return $this->belongsToMany(Tour::class, 'tour_hotels');
    }
}