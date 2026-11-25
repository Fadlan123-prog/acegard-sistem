<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRollInvoiceItem extends Model
{
    protected $fillable = ['branch_roll_invoice_id', 'film_stock_pool_id', 'item_name', 'qty', 'unit', 'unit_price', 'total_price'];

    public function invoice() { return $this->belongsTo(BranchRollInvoice::class, 'branch_roll_invoice_id'); }
    public function pool()    { return $this->belongsTo(FilmStockPool::class, 'film_stock_pool_id'); }
    public function rolls()   { return $this->hasMany(BranchRollInvoiceRoll::class); }
}
