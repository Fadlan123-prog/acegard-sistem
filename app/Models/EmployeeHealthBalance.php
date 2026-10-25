<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeHealthBalance extends Model
{
    protected $fillable = ['employee_id', 'year', 'allocated_amount', 'used_amount'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function remaining(): int
    {
        return max(0, $this->allocated_amount - $this->used_amount);
    }
}
