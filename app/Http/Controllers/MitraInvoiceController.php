<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Mitra;
use App\Models\MitraInvoice;
use App\Models\MitraInvoiceItem;
use App\Models\MitraRollPrice;
use App\Models\ProductBuilding;
use App\Services\DocumentNumberService;
use App\Services\FilmStockPoolService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MitraInvoiceController extends Controller
{
    public function __construct(
        private DocumentNumberService $docNumbers,
        private FilmStockPoolService $filmStock,
    ) {}

    public function index(Request $request)
    {
        $query = MitraInvoice::with(['mitra', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('mitra', fn ($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('mitra_id')) {
            $query->where('mitra_id', $request->mitra_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        $totals = (clone $query)->selectRaw('
            COUNT(*) as total_count,
            SUM(total_amount) as total_amount,
            SUM(CASE WHEN status = "unpaid" THEN total_amount ELSE 0 END) as total_unpaid
        ')->first();

        $invoices = (clone $query)->withCount('items')->latest('invoice_date')->paginate(15)->withQueryString();

        $mitras = Mitra::orderBy('name')->get(['id', 'name']);

        return view('mitra-invoices.index', compact('invoices', 'mitras', 'totals'));
    }

    public function create()
    {
        $mitras     = Mitra::where('status', 'active')->orderBy('name')->get();
        $rollPrices = MitraRollPrice::with('category')->get()
            ->sortBy(fn ($rp) => $rp->category->name ?? '');

        $productBuildings = ProductBuilding::orderBy('name')->get();

        return view('mitra-invoices.create', compact('mitras', 'rollPrices', 'productBuildings'));
    }

    /**
     * Stok roll utuh yang sungguhan tersedia di pusat untuk kategori + persentase
     * tertentu — dipakai form create untuk menampilkan angka stok hidup sebelum submit.
     */
    public function availableStock(Request $request)
    {
        $data = $request->validate([
            'category_product_building_id' => ['required', 'exists:category_product_building,id'],
            'product_building_id'          => ['required', 'exists:product_building,id'],
        ]);

        $available = $this->filmStock->availableRollCount([
            'branch_id'                     => $this->pusatBranch()->id,
            'category_product_building_id'  => $data['category_product_building_id'],
            'product_building_id'           => $data['product_building_id'],
        ]);

        return response()->json(['available' => $available]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mitra_id'      => ['required', 'exists:mitras,id'],
            'invoice_date'  => ['required', 'date'],
            'due_date'      => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes'         => ['nullable', 'string'],

            'items'                        => ['required', 'array', 'min:1'],
            'items.*.mitra_roll_price_id'  => ['nullable', 'exists:mitra_roll_prices,id'],
            'items.*.product_building_id'  => ['nullable', 'required_with:items.*.mitra_roll_price_id', 'exists:product_building,id'],
            'items.*.item_name'            => ['required', 'string', 'max:255'],
            'items.*.qty'                  => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'                 => ['nullable', 'string', 'max:50'],
            'items.*.unit_price'           => ['required', 'numeric', 'min:0'],
        ]);

        $mitra  = Mitra::findOrFail($data['mitra_id']);
        $branch = $this->pusatBranch();

        DB::beginTransaction();

        try {
            // Harga untuk item katalog selalu dihitung ulang di server dari tipe mitra —
            // unit_price yang dikirim client untuk baris katalog tidak pernah dipercaya.
            $rollPriceIds = collect($data['items'])->pluck('mitra_roll_price_id')->filter()->unique();
            $rollPrices   = MitraRollPrice::with('category')->whereIn('id', $rollPriceIds)->get()->keyBy('id');

            $items = [];

            foreach ($data['items'] as $item) {
                $qty = (float) $item['qty'];

                if (!empty($item['mitra_roll_price_id'])) {
                    $rollPrice = $rollPrices->get($item['mitra_roll_price_id']);

                    if (!$rollPrice) {
                        throw new \RuntimeException("Tipe roll \"{$item['item_name']}\" tidak valid.");
                    }

                    $product = ProductBuilding::find($item['product_building_id']);

                    if (!$product || (int) $product->category_product_building_id !== (int) $rollPrice->category_product_building_id) {
                        throw new \RuntimeException("Persentase kegelapan untuk \"{$item['item_name']}\" tidak valid.");
                    }

                    $unitPrice = $rollPrice->priceFor($mitra->type);
                    $unit      = $rollPrice->unit;
                } else {
                    $unitPrice = (float) $item['unit_price'];
                    $unit      = $item['unit'] ?? null;
                }

                $items[] = [
                    'mitra_roll_price_id' => $item['mitra_roll_price_id'] ?? null,
                    'product_building_id' => $item['product_building_id'] ?? null,
                    'item_name'           => $item['item_name'],
                    'qty'                 => $qty,
                    'unit'                => $unit,
                    'unit_price'          => $unitPrice,
                    'total_price'         => $qty * $unitPrice,
                ];
            }

            $invoiceNumber = $this->docNumbers->generate('mitra_invoices', 'invoice_number', 'MINV');
            $totalAmount   = collect($items)->sum('total_price');

            $invoice = MitraInvoice::create([
                'mitra_id'       => $data['mitra_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date'   => $data['invoice_date'],
                'due_date'       => $data['due_date'] ?? null,
                'status'         => 'unpaid',
                'total_amount'   => $totalAmount,
                'notes'          => $data['notes'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            foreach ($items as $item) {
                MitraInvoiceItem::create($item + ['mitra_invoice_id' => $invoice->id]);

                if (!empty($item['mitra_roll_price_id'])) {
                    $rollPrice = $rollPrices->get($item['mitra_roll_price_id']);

                    // Jual N roll utuh dari stok pusat sungguhan — melempar ValidationException
                    // kalau stok kurang, dan itu akan membatalkan seluruh transaksi invoice ini.
                    $this->filmStock->sellRollsToMitra([
                        'branch_id'                    => $branch->id,
                        'category_product_building_id' => $rollPrice->category_product_building_id,
                        'product_building_id'          => $item['product_building_id'],
                    ], (int) round($item['qty']), [
                        'notes' => "Terjual ke {$mitra->name} ({$mitra->type_label}) — Invoice {$invoiceNumber}",
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('mitra-invoices.show', $invoice)
                ->with('success', 'Invoice mitra berhasil dibuat.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menyimpan invoice: ' . collect($e->errors())->flatten()->first())
                ->withInput();

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(MitraInvoice $mitraInvoice)
    {
        $mitraInvoice->load(['mitra', 'creator', 'items.rollPrice.category', 'items.productBuilding']);

        return view('mitra-invoices.show', ['invoice' => $mitraInvoice]);
    }

    public function download(MitraInvoice $mitraInvoice)
    {
        $mitraInvoice->load(['mitra', 'creator', 'items.rollPrice.category', 'items.productBuilding']);

        // Lokasi public/ yang benar di lokal vs produksi, supaya dompdf bisa muat logo —
        // pola yang sama persis dipakai PpfInvoiceController::download().
        $publicPath = realpath(env('PDF_PUBLIC_PATH', public_path()));

        if (!$publicPath || !is_dir($publicPath)) {
            $publicPath = realpath(public_path());
        }

        if (!$publicPath || !is_dir($publicPath)) {
            abort(500, 'Public path tidak ditemukan. Cek PDF_PUBLIC_PATH atau folder public/.');
        }

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
            ->loadView('mitra-invoices.pdf', ['invoice' => $mitraInvoice]);

        return $pdf->stream('invoice-mitra-' . $mitraInvoice->invoice_number . '.pdf');
    }

    public function markPaid(MitraInvoice $mitraInvoice)
    {
        if ($mitraInvoice->status === 'paid') {
            return back()->with('error', 'Invoice ini sudah berstatus lunas.');
        }

        $mitraInvoice->markAsPaid();

        return back()->with('success', 'Invoice ditandai sebagai lunas.');
    }

    /**
     * Mitra bukan branch, tapi stok roll yang dijual ke mitra tetap bersumber dari satu
     * cabang: gudang pusat. Dicari dari namanya karena branches.type belum pernah
     * dipakai untuk menandai head office di data yang ada (semuanya 'branch').
     */
    private function pusatBranch(): Branch
    {
        $branch = Branch::where('name', 'like', '%pusat%')->first();

        abort_if(!$branch, 500, 'Cabang pusat tidak ditemukan — tidak bisa menentukan sumber stok untuk invoice mitra.');

        return $branch;
    }
}
