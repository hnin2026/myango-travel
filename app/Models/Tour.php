<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'title',
        'title_mm',
        'description_en',
        'description_mm',
        'additional_info_en',
        'additional_info_mm',
        'duration_days',
        'base_price',
        'location',
        'thumbnail',
        'status'
    ];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'tour_hotels');
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class)->orderBy('day_number');
    }

    public function travelPeriods()
    {
        return $this->hasMany(TravelPeriod::class);
    }

    public function blackoutPeriods()
    {
        return $this->hasMany(TourBlackoutPeriod::class);
    }

    public function getAvailableDepartureDates(): array
    {
        $dates = [];
        $travelPeriods = $this->travelPeriods()->orderBy('start_date')->get();
        $blackouts = $this->blackoutPeriods()->get();

        foreach ($travelPeriods as $period) {
            $start = $period->start_date;
            $end = $period->end_date;
            if (!$start || !$end) {
                continue;
            }

            // Loop through each day in the period
            $current = clone $start;
            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');

                // Check if date is blacked out
                $isBlackout = false;
                foreach ($blackouts as $blackout) {
                    if ($current >= $blackout->start_date && $current <= $blackout->end_date) {
                        $isBlackout = true;
                        break;
                    }
                }

                if ($isBlackout) {
                    $dates[$dateStr] = [
                        'date' => $current->format('d M Y'),
                        'status' => 'unavailable',
                        'available_seats' => 0,
                        'travel_period' => $period,
                    ];
                } else {
                    $availableSeats = $period->total_seats - $period->booked_seats;
                    if ($availableSeats <= 0) {
                        $dates[$dateStr] = [
                            'date' => $current->format('d M Y'),
                            'status' => 'full',
                            'available_seats' => 0,
                            'travel_period' => $period,
                        ];
                    } else {
                        $dates[$dateStr] = [
                            'date' => $current->format('d M Y'),
                            'status' => 'available',
                            'available_seats' => $availableSeats,
                            'travel_period' => $period,
                        ];
                    }
                }

                $current->addDay();
            }
        }

        return $dates;
    }

    public function images()
    {
        return $this->hasMany(TourImage::class)->orderBy('order');
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}