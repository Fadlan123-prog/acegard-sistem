<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRollInvoiceRoll extends Model
{
    protected $fillable = ['branch_roll_invoice_item_id', 'film_roll_entry_id', 'roll_code', 'length_meter'];

    public function item()  { return $this->belongsTo(BranchRollInvoiceItem::class, 'branch_roll_invoice_item_id'); }
    public function entry() { return $this->belongsTo(FilmRollEntry::class, 'film_roll_entry_id'); }
}
