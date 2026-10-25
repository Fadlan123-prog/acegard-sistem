<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportOrderDocument extends Model
{
    protected $fillable = ['import_order_id', 'doc_type', 'file_path'];

    public static array $docTypeLabels = [
        'invoice'               => 'Invoice',
        'packing_list'          => 'Packing List',
        'bill_of_lading'        => 'Bill of Lading',
        'certificate_of_origin' => 'Certificate of Origin',
        'other'                 => 'Lainnya',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ImportOrder::class, 'import_order_id');
    }
}
