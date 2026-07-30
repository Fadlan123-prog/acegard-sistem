<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'branch_id', 'bank_name', 'account_name', 'account_number',
        'opening_balance', 'current_balance', 'is_active', 'notes',
    ];

    protected $casts = [
        'opening_balance' => 'integer',
        'current_balance' => 'integer',
        'is_active'       => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function entries()
    {
        return $this->hasMany(BankStatementEntry::class);
    }

    public function unreconciledCount(): int
    {
        return $this->entries()->where('is_reconciled', false)->count();
    }
}
