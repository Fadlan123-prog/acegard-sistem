<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    use Auditable, HasBranchScope;

    protected $fillable = [
        'branch_id',
        'sales_employee_id',
        'sales_product_id',
        'customer_name',
        'customer_phone',
        'price',
        'discount',
        'total_price',
        'downpayment',
        'remaining_payment',
        'status',
        'paid_at',
        'pelunasan_at',
        'commission_released_at',
        'commission_released_by',
        'created_by',
    ];

    protected $casts = [
        'price'                   => 'integer',
        'discount'                => 'integer',
        'total_price'             => 'integer',
        'downpayment'             => 'integer',
        'remaining_payment'       => 'integer',
        'paid_at'                 => 'datetime',
        'pelunasan_at'            => 'datetime',
        'commission_released_at'  => 'datetime',
    ];

    public static array $statusLabels = [
        'unpaid'  => 'Belum Bayar',
        'partial' => 'DP',
        'paid'    => 'Lunas',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesEmployee()
    {
        return $this->belongsTo(Employee::class, 'sales_employee_id');
    }

    public function product()
    {
        return $this->belongsTo(SalesProduct::class, 'sales_product_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'sales_transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaidOff(): bool
    {
        return $this->status === 'paid';
    }
}
