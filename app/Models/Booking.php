<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
        'payment_reminder_sent_at',
        'payment_receipt',
        'payment_uploaded_at',
        'cancelled_by',
        'cancel_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'payment_deadline' => 'date',
        'payment_reminder_sent_at' => 'datetime',
        'payment_uploaded_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getIsPaymentUploadedAttribute(): bool
    {
        return $this->status === self::STATUS_CONFIRMED && ! is_null($this->payment_receipt);
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
        if ($this->status === self::STATUS_CANCELLED && $this->cancelled_by === 'system') {
            return 'Cancelled (System)';
        }

        return ucfirst($this->status);
    }

    /**
     * Calculate a payment deadline of seven business days (excluding Saturdays and Sundays),
     * counting starting from the day after the booking confirmation date.
     */
    public static function calculatePaymentDeadline(\DateTimeInterface|string|null $fromDate = null): Carbon
    {
        $start = $fromDate ? Carbon::parse($fromDate) : now();

        return $start->copy()->addWeekdays(7);
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
