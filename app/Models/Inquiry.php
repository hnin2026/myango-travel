<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'tour_id',
        'customer_name',
        'nationality',
        'phone',
        'email',
        'number_of_adults',
        'number_of_children',
        'checkin_date',
        'checkout_date',
        'message',
        'status',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}