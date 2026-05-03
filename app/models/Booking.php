<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'tour_id',
        'hotel_id',
        'available_date_id',
        'customer_name',
        'email',
        'phone',
        'num_persons',
        'total_price',
        'status',
        'ref_code',
        'cancellation_token',
        'payment_deadline'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function availableDate()
    {
        return $this->belongsTo(AvailableDate::class);
    }
}