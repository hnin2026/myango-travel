<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelPeriod extends Model
{
    protected $fillable = [
        'tour_id',
        'start_date',
        'end_date',
        'total_seats',
        'booked_seats'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function availableSeats(): int
    {
        return $this->total_seats - $this->booked_seats;
    }
}