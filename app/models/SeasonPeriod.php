<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SeasonPeriod extends Model
{
    protected $fillable = [
        'name',
        'season',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date'
    ];

    public static function getSeasonForDate(string $date): string
    {
        $carbon = Carbon::parse($date);

        $period = self::all()->first(function ($period) use ($carbon) {
            $start = Carbon::parse($period->start_date);
            $end   = Carbon::parse($period->end_date);

            if ($start->gt($end)) {
                return $carbon->gte($start) || $carbon->lte($end);
            }

            return $carbon->between($start, $end);
        });

        return $period ? $period->season : 'normal';
    }
}