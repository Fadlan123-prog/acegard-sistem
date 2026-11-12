<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTarget extends Model
{
    protected $fillable = [
        'division', 'metric_key', 'metric_label', 'target_value',
        'weight', 'direction', 'period_month', 'period_year', 'created_by',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'weight'       => 'decimal:2',
    ];

    // Divisi yang sudah didefinisikan di "Kartu KPI per Divisi — Acegard".
    const DIVISIONS = [
        'advertising'      => 'Advertising',
        'customer_service' => 'Customer Service',
        'konten'           => 'Konten',
        'teknisi'          => 'Teknisi',
        'admin'            => 'Admin',
        'warehouse'        => 'Warehouse',
        'purchasing'       => 'Purchasing',
        'keuangan'         => 'Keuangan',
        'it'               => 'IT',
        'development'      => 'Development',
        'strategi'         => 'Strategi',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scores()
    {
        return $this->hasMany(KpiScore::class);
    }

    public function getDivisionLabelAttribute(): string
    {
        return self::DIVISIONS[$this->division] ?? $this->division;
    }

    /**
     * Pencapaian (%) dari sebuah nilai aktual terhadap target KPI ini,
     * mengikuti rumus di "Kartu KPI per Divisi": aktual ÷ target untuk KPI
     * "makin besar makin baik", dibalik untuk KPI "makin kecil makin baik"
     * (rework, waste, dll), dan dibatasi maksimal 100%.
     */
    public function achievementPercent(float $actual): float
    {
        if ((float) $this->target_value <= 0) {
            return 0;
        }

        if ($this->direction === 'lower_better') {
            return $actual <= $this->target_value
                ? 100.0
                : round((float) $this->target_value / $actual * 100, 1);
        }

        return min(100.0, round($actual / (float) $this->target_value * 100, 1));
    }
}
