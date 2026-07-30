<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankStatementEntry extends Model
{
    protected $fillable = [
        'bank_account_id', 'entry_date', 'description',
        'debit', 'credit', 'balance_after', 'reference_number',
        'is_reconciled', 'reconciled_by', 'reconciled_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'entry_date'     => 'date',
        'debit'          => 'integer',
        'credit'         => 'integer',
        'balance_after'  => 'integer',
        'is_reconciled'  => 'boolean',
        'reconciled_at'  => 'datetime',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function netAmount(): int
    {
        return $this->credit - $this->debit;
    }
}
