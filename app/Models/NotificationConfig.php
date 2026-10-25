<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationConfig extends Model
{
    protected $fillable = [
        'branch_id', 'type', 'days_before', 'channels',
        'is_active', 'email_recipients', 'whatsapp_numbers',
        'whatsapp_api_url', 'whatsapp_api_token',
    ];

    protected $casts = [
        'channels'           => 'array',
        'email_recipients'   => 'array',
        'whatsapp_numbers'   => 'array',
        'is_active'          => 'boolean',
        'days_before'        => 'integer',
    ];

    protected $attributes = [
        'days_before' => 3,
        'is_active'   => false,
    ];

    public function getChannelsAttribute($value): array
    {
        return $value ? json_decode($value, true) : ['in_app'];
    }

    public function getEmailRecipientsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function getWhatsappNumbersAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static array $types = [
        'hutang_due'         => 'Hutang Jatuh Tempo',
        'piutang_due'        => 'Piutang Jatuh Tempo',
        'warranty_expiry'    => 'Garansi Pelanggan',
        'low_film_stock'     => 'Stok Film Kritis',
        'low_goods_stock'    => 'Stok Barang Pendukung',
        'employee_birthday'  => 'Ulang Tahun Karyawan',
    ];

    public static array $channels = [
        'in_app'    => 'In-App',
        'email'     => 'Email',
        'whatsapp'  => 'WhatsApp',
    ];

    // Types that use days_before (time-based reminders)
    public static array $timeBased = ['hutang_due', 'piutang_due', 'warranty_expiry', 'employee_birthday'];
}
