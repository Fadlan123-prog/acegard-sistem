<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportOrderDocument extends Model
{
    protected $fillable = ['export_order_id', 'doc_type', 'file_path'];

    public static array $docTypeLabels = [
        'invoice'              => 'Invoice',
        'packing_list'         => 'Packing List',
        'customs_declaration'  => 'Dokumen Kepabeanan',
        'other'                => 'Lainnya',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ExportOrder::class, 'export_order_id');
    }
}
