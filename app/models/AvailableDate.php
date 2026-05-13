<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableDate extends Model
{
    protected $fillable = [
        'tour_id',
        'date',
        'total_seats',
        'booked_seats'
    ];

    protected $casts = [
        'date' => 'date'
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