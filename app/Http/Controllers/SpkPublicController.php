<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;

class SpkPublicController extends Controller
{
    public function show(string $token)
    {
        $salesOrder = SalesOrder::with(['branch', 'items', 'tukang', 'kenek', 'marketing'])
            ->where('spk_token', $token)
            ->firstOrFail();

        return view('spk.public', compact('salesOrder'));
    }
}
