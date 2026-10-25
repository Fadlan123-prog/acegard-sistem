<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraInvoice extends Model
{
    protected $fillable = [
        'mitra_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'paid_at',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(MitraInvoiceItem::class);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }
}
