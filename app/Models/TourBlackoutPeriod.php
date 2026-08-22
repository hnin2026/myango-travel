<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourBlackoutPeriod extends Model
{
    protected $fillable = [
        'tour_id',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
