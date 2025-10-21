<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerBuilding;
use App\Models\InvoiceBuilding;
use App\Models\CustomerBuildingProduct;
use Illuminate\Support\Facades\DB;
use PDF;

class InvoiceBuildingController extends Controller
{
    public function create(CustomerBuilding $customer)
    {
        $customerProducts = $customer->products()->with('product', 'category')->get();

        return view('invoice.building.create', [
            'customer' => $customer,
            'customerProducts' => $customerProducts
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            // ✅ pastikan nama tabel sesuai konvensi (plural)
            'customer_building_id' => 'required|exists:customer_building,id',
            'branch_id'            => 'required|exists:branches,id',
            'user_id'              => 'required|exists:users,id',
            'invoice_date'         => 'required|date',
            'type'                 => 'required|string',
            'products'             => 'required|array',
            // ✅ plural
            'products.*.customer_building_product_id' => 'required|exists:customer_building_product,id',
            'products.*.price'     => 'required|integer|min:0',
            'discount'             => 'nullable|integer|min:0',
            // input dari form tetap down_payment (snake_case)
            'down_payment'         => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice = collect($request->products)->sum('price');

            $discount     = (int) ($request->discount ?? 0);
            $downPayment  = (int) ($request->down_payment ?? 0);
            $subTotal     = $totalPrice - $discount;
            $remaining    = $subTotal - $downPayment;

            $invoice = InvoiceBuilding::create([
                'customer_building_id' => $request->customer_building_id,
                'branch_id'            => $request->branch_id,
                'user_id'              => $request->user_id,
                'type'                 => $request->type,
                'price'                => $totalPrice,
                'discount'             => $discount,
                // ✅ konsisten: kolom di DB bernama downpayment (tanpa underscore)
                'down_payment'          => $downPayment,
                'total_price'          => $subTotal,
                'remaining_payment'    => $remaining,
                'status'               => 'draft',
                'invoice_number'       => 'INV-' . now()->format('YmdHis'),
                'invoice_date'         => $request->invoice_date,
            ]);

            foreach ($request->products as $item) {
                $invoice->products()->attach(
                    $item['customer_building_product_id'],
                    ['price' => (int) $item['price']]
                );
            }

            DB::commit();
            return redirect()
                ->route('customer.building.index')
                ->with('success', 'Invoice berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage());
        }
    }

    public function show(InvoiceBuilding $invoice)
    {
        $customer = CustomerBuilding::with([
            'products',
            'products.product',
            'products.category',
            'products.categoryProductBuilding'
        ])->findOrFail($invoice->customer_building_id);
        $invoice = InvoiceBuilding::with('customer')->findOrFail($invoice->id);
        return view('invoice.building.show', compact('invoice', 'customer'));
    }

    public function edit($id)
    {
        $invoice = InvoiceBuilding::with([
            'products', // relasi pivot
            'products.product',
            'products.category',
            'customer' // agar bisa ambil data customer
        ])->findOrFail($id);

        $customer = $invoice->customer;

        // Ambil semua product milik customer ini
        $customerProducts = CustomerBuildingProduct::with(['product', 'category'])
            ->where('customer_building_id', $customer->id)
            ->get();

        // Inject harga (pivot price) dari invoice ke dalam collection customerProducts
        foreach ($customerProducts as $prod) {
            $pivot = $invoice->products->firstWhere('id', $prod->id);
            if ($pivot) {
                $prod->pivot = $pivot->pivot; // inject pivot jika ada
            }
        }

        return view('invoice.building.edit', compact('invoice', 'customer', 'customerProducts'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'invoice_number' => 'required',
            'invoice_date'   => 'required|date',
            'type'           => 'required|in:cash,credit',
            'discount'       => 'nullable|numeric',
            'down_payment'   => 'nullable|numeric', // input form
            'status'         => 'required|in:unpaid,partial,paid',
            'products'       => 'required|array',
            // ✅ plural
            'products.*.customer_building_product_id' => 'required|exists:customer_building_products,id',
            'products.*.price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $invoice = InvoiceBuilding::findOrFail($id);
            $invoice->update([
                'invoice_number' => $request->invoice_number,
                'invoice_date'   => $request->invoice_date,
                'type'           => $request->type,
                'discount'       => $request->discount ?? 0,
                // ✅ simpan ke kolom `downpayment`
                'down_payment'    => $request->down_payment ?? 0,
                'status'         => $request->status,
            ]);

            // Sync ulang produk dengan harga
            $syncData = [];
            foreach ($request->products as $prod) {
                $syncData[$prod['customer_building_product_id']] = ['price' => (int) $prod['price']];
            }
            $invoice->products()->sync($syncData);

            // (opsional) hitung ulang total/remaining jika ingin konsisten setelah update:
            $totalPrice = $invoice->products()->sum('customer_building_product_invoice.price');
            $discount   = (int) ($invoice->discount ?? 0);
            $downPay    = (int) ($invoice->downpayment ?? 0);

            $invoice->update([
                'price'             => $totalPrice,
                'total_price'       => $totalPrice - $discount,
                'remaining_payment' => ($totalPrice - $discount) - $downPay,
            ]);

            DB::commit();
            return redirect()->route('invoice.building.index')->with('success', 'Invoice berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, InvoiceBuilding $invoice)
    {
        // Opsional: batasi penghapusan saat status tertentu
        if (in_array($invoice->status, ['paid'])) {
            return back()->with('error', 'Invoice yang sudah "paid" tidak boleh dihapus.');
        }

        $force = (bool) $request->query('force', false);

        try {
            DB::beginTransaction();

            // Lepas relasi pivot (invoice <-> customer_building_products)
            // aman walau tidak ada data pivot
            $invoice->products()->detach();

            // Soft delete (default) atau force delete (opsional)
            if ($force && method_exists($invoice, 'forceDelete')) {
                $invoice->forceDelete();
            } else {
                $invoice->delete();
            }

            DB::commit();
            return redirect()
                ->route('invoice.building.index')
                ->with('success', 'Invoice berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus invoice: ' . $e->getMessage());
        }
    }

    public function download(InvoiceBuilding $invoice)
    {
        // Muat customer + tree produk (gedung)
        $customer = CustomerBuilding::with([
            'products.product',
            'products.category',
        ])->findOrFail($invoice->customer_building_id);

        $customerProduct = $customer->products ?? collect();

        // ---- Hard-bind public path (aman untuk shared hosting) ----
        $publicPath = '/home/acegardi/public_html';
        app()->usePublicPath($publicPath);
        app()->instance('path.public', $publicPath);
        config([
            'dompdf.public_path'     => $publicPath,
            'dompdf.chroot'          => $publicPath,
            'dompdf.isRemoteEnabled' => true,
        ]);
        // -----------------------------------------------------------

        // Render PDF
        $pdf = Pdf::setPaper('A4', 'portrait')
            ->setOptions([
                'chroot'          => $publicPath,
                'isRemoteEnabled' => true,
                // 'dpi' => 120,              // opsional
                // 'defaultFont' => 'DejaVu Sans', // opsional, kalau ada karakter spesial
            ])
            ->loadView('invoice.building.pdf', [
                'invoice'         => $invoice,
                'customer'        => $customer,
                'customerProduct' => $customerProduct,
            ]);

        $filename = 'invoice-building-' . ($invoice->invoice_number ?? $invoice->number ?? $invoice->id) . '.pdf';

        return $pdf->stream($filename);
    }
}
