<?php

namespace App\Http\Controllers;

use App\Models\ImportOrder;
use App\Models\InternationalPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportOrderController extends Controller
{
    public function index()
    {
        $orders = ImportOrder::with('partner')->latest('order_date')->paginate(20);

        return view('import-orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = InternationalPartner::suppliers()->orderBy('name')->get();

        return view('import-orders.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data, $request) {
            $subtotalForeign = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_price_foreign']);
            $totalCostIdr = ($subtotalForeign * $data['exchange_rate'])
                + $data['duty_cost'] + $data['tax_cost'] + $data['freight_cost'] + $data['insurance_cost'];

            $order = ImportOrder::create([
                'international_partner_id' => $data['international_partner_id'],
                'order_number'             => $this->generateOrderNumber(),
                'currency'                 => $data['currency'],
                'exchange_rate'            => $data['exchange_rate'],
                'subtotal_foreign'         => $subtotalForeign,
                'duty_cost'                => $data['duty_cost'],
                'tax_cost'                 => $data['tax_cost'],
                'freight_cost'             => $data['freight_cost'],
                'insurance_cost'           => $data['insurance_cost'],
                'total_cost_idr'           => (int) round($totalCostIdr),
                'order_date'               => $data['order_date'],
                'estimated_arrival'        => $data['estimated_arrival'] ?? null,
                'created_by'               => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }

            foreach ($request->file('documents', []) as $docType => $file) {
                if ($file) {
                    $order->documents()->create([
                        'doc_type'  => $docType,
                        'file_path' => $file->store('import-order-documents', 'public'),
                    ]);
                }
            }

            return $order;
        });

        return redirect()->route('import-orders.show', $order)->with('success', 'Import order berhasil dibuat.');
    }

    public function show(ImportOrder $importOrder)
    {
        $importOrder->load(['partner', 'items', 'documents', 'creator']);

        return view('import-orders.show', ['order' => $importOrder]);
    }

    public function updateStatus(Request $request, ImportOrder $importOrder)
    {
        $data = $request->validate([
            'status' => ['required', 'in:ordered,shipped,customs,received'],
        ]);

        $updates = ['status' => $data['status']];
        if ($data['status'] === 'received') {
            $updates['received_date'] = now()->toDateString();
        }

        $importOrder->update($updates);

        return back()->with('success', 'Status import order diperbarui menjadi "' . ImportOrder::$statusLabels[$data['status']] . '". Catatan: barang belum otomatis masuk ke Roll Stock/Supporting Goods — itu keputusan desain yang masih perlu difinalisasi (pemetaan item ke katalog produk), silakan input manual di modul stok untuk saat ini.');
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'IMP-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (ImportOrder::where('order_number', $number)->exists());

        return $number;
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'international_partner_id' => ['required', 'exists:international_partners,id'],
            'currency'                 => ['required', 'string', 'size:3'],
            'exchange_rate'            => ['required', 'numeric', 'min:0.0001'],
            'duty_cost'                => ['nullable', 'integer', 'min:0'],
            'tax_cost'                 => ['nullable', 'integer', 'min:0'],
            'freight_cost'             => ['nullable', 'integer', 'min:0'],
            'insurance_cost'           => ['nullable', 'integer', 'min:0'],
            'order_date'               => ['required', 'date'],
            'estimated_arrival'        => ['nullable', 'date', 'after_or_equal:order_date'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.item_name'        => ['required', 'string', 'max:255'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'             => ['required', 'string', 'max:50'],
            'items.*.unit_price_foreign' => ['required', 'integer', 'min:0'],
        ]);

        foreach (['duty_cost', 'tax_cost', 'freight_cost', 'insurance_cost'] as $costField) {
            $data[$costField] = (int) ($data[$costField] ?? 0);
        }

        return $data;
    }
}
