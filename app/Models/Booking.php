<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\TravelPeriod;
class Booking extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

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
        'payment_deadline',
        'payment_receipt',
        'payment_uploaded_at',
        'cancelled_by',
        'cancel_reason',
        'cancelled_at'
    ];

    protected $casts = [
        'payment_deadline' => 'date',
        'payment_uploaded_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    public function getIsPaymentUploadedAttribute(): bool
    {
        return $this->status === self::STATUS_CONFIRMED && !is_null($this->payment_receipt);
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->is_payment_uploaded ? 'payment_uploaded' : $this->status;
    }

    public function getDisplayStatusLabelAttribute(): string
    {
        if ($this->is_payment_uploaded) {
            return 'Payment Uploaded';
        }
        return ucfirst($this->status);
    }

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