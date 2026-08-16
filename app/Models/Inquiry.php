<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_NOT_BOOKED = 'not_booked';

    const STATUSES = [
        self::STATUS_NEW => 'NEW',
        self::STATUS_IN_PROGRESS => 'IN PROGRESS',
        self::STATUS_CONFIRMED => 'CONFIRMED',
        self::STATUS_UNAVAILABLE => 'UNAVAILABLE',
        self::STATUS_NOT_BOOKED => 'NOT BOOKED',
    ];

    protected $fillable = [
        'reference',
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

    protected static function booted()
    {
        static::created(function ($inquiry) {
            $inquiry->reference = 'INQ-' . str_pad($inquiry->id, 4, '0', STR_PAD_LEFT);
            $inquiry->saveQuietly();
        });
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? strtoupper($this->status);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}