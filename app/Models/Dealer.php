<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    protected $fillable = [
        'name',
        'type',
        'location',
        'image',
        'admin_whatsapp',
        'maps_url',
        'instagram_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->admin_whatsapp) {
            return null;
        }

        return 'https://wa.me/' . preg_replace('/\D/', '', $this->admin_whatsapp);
    }
}
