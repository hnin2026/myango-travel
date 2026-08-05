<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelSeasonPrice extends Model
{
    protected $fillable = [
        'hotel_id',
        'season',
        'upgrade_price'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}