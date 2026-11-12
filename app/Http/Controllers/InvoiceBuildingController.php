<?php

namespace App\Http\Controllers;

use App\Models\CustomerBuilding;
use App\Models\InvoiceBuilding;
use App\Services\FilmStockPoolService;
use App\Services\InvoiceCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvoiceBuildingController extends Controller
{
    public function __construct(
        private InvoiceCalculationService $calc,
        private FilmStockPoolService $filmStock,
    ) {}

    public function create(CustomerBuilding $customer)
    {
        $this->authorizeBranchId($customer->branch_id);

        $customer->load([
            'products.category',
            'products.product',
            'branch',
        ]);

        return view('invoice.building.create', compact('customer'));
    }

    private function authorizeBranchId(?int $branchId): void
    {
        if (auth()->user()->hasRole('superadmin')) {
            return;
        }

        $allowed = auth()->user()
            ->branches()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (!in_array((int) $branchId, $allowed, true)) {
            abort(403, 'Anda tidak memiliki akses ke data dari cabang lain.');
        }
    }

    private function authorizeBranchForInvoice(InvoiceBuilding $invoice): void
    {
        $invoice->loadMissing('customer');
        $this->authorizeBranchId($invoice->customer?->branch_id ?? $invoice->branch_id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_building_id' => [
                'required',
                'integer',
                'exists:customer_building,id',
            ],

            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:invoice_building,invoice_number',
            ],

            'invoice_date' => [
                'required',
                'date',
            ],

            'type' => [
                'required',
                'in:cash,credit,transfer',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'down_payment' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:unpaid,partial,paid',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.customer_building_product_id' => [
                'required',
                'integer',
                'exists:customer_building_product,id',
            ],

            'products.*.price' => [
                'required',
                'numeric',
                'min:' . config('pricing.minimum_transaction_amount'),
            ],
        ], [
            'products.*.price.min' => 'Harga produk tampak tidak wajar. Periksa kembali sebelum menyimpan.',
        ]);

        $customer = CustomerBuilding::findOrFail($data['customer_building_id']);
        $this->authorizeBranchId($customer->branch_id);

        $invoiceDate = Carbon::parse($data['invoice_date'])->format('Y-m-d H:i:s');

        $priceCalc        = $this->calc->calculateFromProducts($data['products'], (float) ($data['discount'] ?? 0), (float) ($data['down_payment'] ?? 0));
        $price            = $priceCalc['price'];
        $discount         = $priceCalc['discount'];
        $downPayment      = $priceCalc['downpayment'];
        $totalPrice       = $priceCalc['total_price'];
        $remainingPayment = $priceCalc['remaining_payment'];

        if ($priceCalc['discount'] > $priceCalc['price']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi total harga.']);
        }
        if ($data['status'] === 'paid' && $priceCalc['remaining_payment'] > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Status Lunas hanya boleh bila DP sudah menutup total harga.']);
        }

        // Guard against accidental double-submit creating two near-identical
        // invoices for the same job (see InvoiceController::store() — same pattern).
        $recentDuplicate = InvoiceBuilding::where('customer_building_id', $customer->id)
            ->where('price', $price)
            ->where('total_price', $totalPrice)
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();

        if ($recentDuplicate) {
            return redirect()
                ->route('invoice.building.show', $recentDuplicate->id)
                ->with('success', 'Invoice ini baru saja tersimpan.');
        }

        $invoiceNumber = !empty($data['invoice_number'])
            ? $data['invoice_number']
            : $this->generateInvoiceNumber();

        DB::beginTransaction();

        try {
            $invoice = InvoiceBuilding::create([
                'customer_building_id' => $customer->id,
                'branch_id'            => $customer->branch_id ?? null,
                'user_id'              => auth()->id(),
                'type'                 => $data['type'],
                'price'                => $price,
                'discount'             => $discount,
                'down_payment'         => $downPayment,
                'total_price'          => $totalPrice,
                'remaining_payment'    => $remainingPayment,
                'status'               => $data['status'],
                'invoice_number'       => $invoiceNumber,
                'invoice_date'         => $invoiceDate,
                'waste_percent'        => (float) ($data['waste_percent'] ?? 10),
            ]);

            $syncData = [];

            foreach ($data['products'] as $product) {
                $syncData[(int) $product['customer_building_product_id']] = [
                    'price'      => (float) $product['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $invoice->products()->sync($syncData);

            DB::commit();

            return redirect()
                ->route('invoice.building.show', $invoice->id)
                ->with('success', 'Invoice building berhasil dibuat.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[Invoice Building Store] gagal', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);

            return back()
                ->with('error', 'Gagal menyimpan invoice building: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $invoice = InvoiceBuilding::with([
            'customer.branch',
            'products.category',
            'products.product',
            'branch',
            'user',
        ])->findOrFail($id);
        $this->authorizeBranchForInvoice($invoice);

        $customer = $invoice->customer;

        if (!$customer) {
            abort(404, 'Customer building untuk invoice ini tidak ditemukan.');
        }

        return view('invoice.building.show', compact('invoice', 'customer'));
    }

    public function edit($id)
    {
        $invoice = InvoiceBuilding::with([
            'customer.branch',
            'customer.products.category',
            'customer.products.product',
            'products',
            'branch',
            'user',
        ])->findOrFail($id);
        $this->authorizeBranchForInvoice($invoice);

        $customer = $invoice->customer;

        if (!$customer) {
            abort(404, 'Customer building untuk invoice ini tidak ditemukan.');
        }

        return view('invoice.building.edit', compact('invoice', 'customer'));
    }

    public function update(Request $request, $id)
    {
        $invoice = InvoiceBuilding::with('customer')->findOrFail($id);
        $this->authorizeBranchForInvoice($invoice);

        $data = $request->validate([
            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('invoice_building', 'invoice_number')->ignore($invoice->id),
            ],

            'invoice_date' => [
                'required',
                'date',
            ],

            'type' => [
                'required',
                'in:cash,credit,debit,transfer',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'down_payment' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:unpaid,partial,paid',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.customer_building_product_id' => [
                'required',
                'integer',
                'exists:customer_building_product,id',
            ],

            'products.*.price' => [
                'required',
                'numeric',
                'min:' . config('pricing.minimum_transaction_amount'),
            ],
        ], [
            'products.*.price.min' => 'Harga produk tampak tidak wajar. Periksa kembali sebelum menyimpan.',
        ]);

        $invoiceDate = Carbon::parse($data['invoice_date'])->format('Y-m-d H:i:s');

        $priceCalc        = $this->calc->calculateFromProducts($data['products'], (float) ($data['discount'] ?? 0), (float) ($data['down_payment'] ?? 0));
        $price            = $priceCalc['price'];
        $discount         = $priceCalc['discount'];
        $downPayment      = $priceCalc['downpayment'];
        $totalPrice       = $priceCalc['total_price'];
        $remainingPayment = $priceCalc['remaining_payment'];

        if ($priceCalc['discount'] > $priceCalc['price']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount' => 'Diskon tidak boleh melebihi total harga.']);
        }
        if ($data['status'] === 'paid' && $priceCalc['remaining_payment'] > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Status Lunas hanya boleh bila DP sudah menutup total harga.']);
        }

        $invoiceNumber = !empty($data['invoice_number'])
            ? $data['invoice_number']
            : $invoice->invoice_number;

        if (empty($invoiceNumber)) {
            $invoiceNumber = $this->generateInvoiceNumber();
        }

        DB::beginTransaction();

        try {
            $invoice->update([
                'type'              => $data['type'],
                'price'             => $price,
                'discount'          => $discount,
                'down_payment'      => $downPayment,
                'total_price'       => $totalPrice,
                'remaining_payment' => $remainingPayment,
                'status'            => $data['status'],
                'invoice_number'    => $invoiceNumber,
                'invoice_date'      => $invoiceDate,
            ]);

            $syncData = [];

            foreach ($data['products'] as $product) {
                $syncData[(int) $product['customer_building_product_id']] = [
                    'price'      => (float) $product['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $invoice->products()->sync($syncData);

            DB::commit();

            return redirect()
                ->route('invoice.building.show', $invoice->id)
                ->with('success', 'Invoice building berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[Invoice Building Update] gagal', [
                'message'    => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'data'       => $data,
            ]);

            return back()
                ->with('error', 'Gagal memperbarui invoice building: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function recordPelunasan(Request $request, $id)
    {
        $invoice = InvoiceBuilding::with(['customer.products'])->findOrFail($id);
        $this->authorizeBranchForInvoice($invoice);

        if ($invoice->isStep2Done()) {
            return back()->with('error', 'Pelunasan sudah pernah dicatat sebelumnya.');
        }

        $request->validate([
            'type'         => ['nullable', 'in:cash,credit,transfer'],
            'waste_percent'=> ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $now          = now();
        $wastePercent = (float) ($request->input('waste_percent') ?? $invoice->waste_percent ?? 10);
        $customer     = $invoice->customer;

        DB::beginTransaction();

        try {
            // Re-check under lock inside the transaction — the outer isStep2Done()
            // check above is only a fast-path; without this, two concurrent
            // requests can both pass it and double-deduct film stock below.
            $locked = InvoiceBuilding::whereKey($invoice->id)->lockForUpdate()->first();
            if ($locked->isStep2Done()) {
                DB::rollBack();
                return back()->with('error', 'Pelunasan sudah pernah dicatat sebelumnya.');
            }

            // Step 2: catat pelunasan
            $invoice->update([
                'remaining_payment' => 0,
                'status'            => 'paid',
                'pelunasan_at'      => $now,
                'pelunasan_by'      => auth()->id(),
                'type'              => $request->input('type') ?? $invoice->type,
                'waste_percent'     => $wastePercent,
            ]);

            // Komisi teknisi gedung (per meter) — eligible saat lunas
            app(\App\Services\BuildingCommissionService::class)->release($invoice->fresh());

            // Potong stok saat pelunasan
            if ($customer && $customer->products->isNotEmpty()) {
                $buildingProducts = $customer->products->map(fn ($p) => [
                    'customer_building_product' => $p,
                    'data' => [
                        'meters'                       => $p->meters,
                        'category_product_building_id' => $p->category_product_building_id,
                        'product_building_id'          => $p->product_building_id,
                    ],
                ])->toArray();

                $this->filmStock->consumeForBuilding($customer, $buildingProducts, $wastePercent);

                $invoice->update([
                    'stock_deducted_at' => $now,
                    'stock_deducted_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Pelunasan berhasil dicatat dan stok bahan telah dikurangi.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[InvoiceBuilding Pelunasan] gagal', [
                'message'    => $e->getMessage(),
                'invoice_id' => $id,
            ]);

            return back()->with('error', 'Gagal mencatat pelunasan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $invoice = InvoiceBuilding::findOrFail($id);
        $this->authorizeBranchForInvoice($invoice);

        DB::beginTransaction();

        try {
            $invoice->products()->detach();

            $invoice->delete();

            DB::commit();

            return redirect()
                ->route('customer.building.index')
                ->with('success', 'Invoice building berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menghapus invoice building: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        $invoice = InvoiceBuilding::with([
            'customer.branch',
            'products.category',
            'products.product',
            'branch',
            'user',
        ])->findOrFail($id);
        $this->authorizeBranchForInvoice($invoice);

        $customer = $invoice->customer;

        if (!$customer) {
            abort(404, 'Customer building untuk invoice ini tidak ditemukan.');
        }

        $branch = $customer->branch;
        $branchKey = $branch ? Str::slug($branch->name) : 'default';

        $view = $this->resolveInvoiceViewForBranch($branchKey);

        $publicPath = public_path();

        config([
            'dompdf.public_path'     => $publicPath,
            'dompdf.chroot'          => $publicPath,
            'dompdf.isRemoteEnabled' => true,
        ]);

        $pdf = Pdf::setPaper('A4', 'portrait')
            ->setOptions([
                'chroot'          => $publicPath,
                'isRemoteEnabled' => true,
            ])
            ->loadView($view, [
                'invoice'  => $invoice,
                'customer' => $customer,
                'branch'   => $branch,
            ]);

        return $pdf->stream('invoice-building-' . $invoice->invoice_number . '.pdf');
    }

    protected function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'INVB-' . now()->format('YmdHis') . '-' . rand(100, 999);
        } while (InvoiceBuilding::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

    protected function resolveInvoiceViewForBranch(string $branchKey): string
    {
        $candidate1 = "invoice.building.branches.$branchKey";

        if (view()->exists($candidate1)) {
            return $candidate1;
        }

        $candidate2 = "invoice.building.pdf_{$branchKey}";

        if (view()->exists($candidate2)) {
            return $candidate2;
        }

        return 'invoice.building.pdf';
    }
}
