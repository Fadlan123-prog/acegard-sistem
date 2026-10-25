<?php

namespace App\Http\Controllers;

use App\Models\ExportOrder;
use App\Models\InternationalPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportOrderController extends Controller
{
    public function index()
    {
        $orders = ExportOrder::with('partner')->latest('order_date')->paginate(20);

        return view('export-orders.index', compact('orders'));
    }

    public function create()
    {
        $buyers = InternationalPartner::buyers()->orderBy('name')->get();

        return view('export-orders.create', compact('buyers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data, $request) {
            $subtotalForeign = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_price_foreign']);

            $order = ExportOrder::create([
                'international_partner_id' => $data['international_partner_id'],
                'order_number'             => $this->generateOrderNumber(),
                'currency'                 => $data['currency'],
                'exchange_rate'            => $data['exchange_rate'],
                'subtotal_foreign'         => $subtotalForeign,
                'total_revenue_idr'        => (int) round($subtotalForeign * $data['exchange_rate']),
                'order_date'               => $data['order_date'],
                'created_by'               => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }

            foreach ($request->file('documents', []) as $docType => $file) {
                if ($file) {
                    $order->documents()->create([
                        'doc_type'  => $docType,
                        'file_path' => $file->store('export-order-documents', 'public'),
                    ]);
                }
            }

            return $order;
        });

        return redirect()->route('export-orders.show', $order)->with('success', 'Export order berhasil dibuat.');
    }

    public function show(ExportOrder $exportOrder)
    {
        $exportOrder->load(['partner', 'items', 'documents', 'creator']);

        return view('export-orders.show', ['order' => $exportOrder]);
    }

    public function updateStatus(Request $request, ExportOrder $exportOrder)
    {
        $data = $request->validate([
            'status' => ['required', 'in:confirmed,shipped,customs,delivered'],
        ]);

        $updates = ['status' => $data['status']];
        if ($data['status'] === 'shipped' && !$exportOrder->shipped_date) {
            $updates['shipped_date'] = now()->toDateString();
        }

        $exportOrder->update($updates);

        return back()->with('success', 'Status export order diperbarui menjadi "' . ExportOrder::$statusLabels[$data['status']] . '".');
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'EXP-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (ExportOrder::where('order_number', $number)->exists());

        return $number;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'international_partner_id' => ['required', 'exists:international_partners,id'],
            'currency'                 => ['required', 'string', 'size:3'],
            'exchange_rate'            => ['required', 'numeric', 'min:0.0001'],
            'order_date'               => ['required', 'date'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.item_name'        => ['required', 'string', 'max:255'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'             => ['required', 'string', 'max:50'],
            'items.*.unit_price_foreign' => ['required', 'integer', 'min:0'],
        ]);
    }
}
