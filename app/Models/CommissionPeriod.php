<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CommissionPeriod extends Model
{
    protected $fillable = ['period_start', 'period_end'];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * Find or create the period record that $date falls into (16th–15th boundary).
     */
    public static function resolveForDate(Carbon $date): static
    {
        if ($date->day >= 16) {
            $start = $date->copy()->setDay(16)->startOfDay();
            $end   = $date->copy()->addMonth()->setDay(15)->endOfDay();
        } else {
            $start = $date->copy()->subMonth()->setDay(16)->startOfDay();
            $end   = $date->copy()->setDay(15)->endOfDay();
        }

        return static::firstOrCreate(
            ['period_start' => $start->toDateString()],
            ['period_end'   => $end->toDateString()]
        );
    }

    /** Resolve the period for the current moment. */
    public static function resolveForNow(): static
    {
        return static::resolveForDate(now());
    }
}
