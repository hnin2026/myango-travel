<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
class Booking extends Model
{
    protected $fillable = [
        'tour_id',
        'travel_period_id',
        'hotel_id',
        'customer_name',
        'nationality',
        'email',
        'phone',
        'num_persons',
        'num_children',
        'child_ages',
        'checkin_date',
        'checkout_date',
        'base_price',
        'hotel_upgrade_price',
        'total_price',
        'message',
        'status',
        'ref_code',
        'cancellation_token',
        'payment_deadline'
    ];

    protected $casts = [
        'payment_deadline' => 'date'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function travelPeriod()
    {
        return $this->belongsTo(TravelPeriod::class);
    }

    
}