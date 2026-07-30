<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = ['lead_id', 'type', 'notes', 'created_by'];

    const TYPES = [
        'call'          => 'Telepon',
        'whatsapp'      => 'WhatsApp',
        'visit'         => 'Kunjungan',
        'email'         => 'Email',
        'note'          => 'Catatan',
        'status_change' => 'Perubahan Status',
    ];

    const TYPE_ICONS = [
        'call'          => 'solar:phone-outline',
        'whatsapp'      => 'ri:whatsapp-line',
        'visit'         => 'solar:map-point-wave-outline',
        'email'         => 'solar:letter-outline',
        'note'          => 'solar:note-text-outline',
        'status_change' => 'solar:refresh-circle-outline',
    ];

    const TYPE_COLORS = [
        'call'          => 'success',
        'whatsapp'      => 'success',
        'visit'         => 'info',
        'email'         => 'primary',
        'note'          => 'secondary',
        'status_change' => 'warning',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'solar:info-circle-outline';
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? 'secondary';
    }
}
