<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\SalesOrder;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\Part;
use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Exports\CustomerExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Intervention\Image\Laravel\Facades\Image;
use App\Services\BranchContext;
use App\Services\InstallTypeResolver;
use App\Services\CommissionService;
use App\Services\CustomerCommissionSyncService;
use App\Services\FilmStockPoolService;
use App\Services\CardNumberService;
use App\Services\CustomerProductSyncService;
use App\Services\AditCommissionService;

class CustomerController extends Controller
{
    public function __construct(
        private CardNumberService $cardNumbers,
        private CustomerProductSyncService $productSync,
        private BranchContext $branch,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Filter berdasarkan keyword pencarian (nama)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ambil user & session cabang
        $user = auth()->user();
        $branchId = $this->branch->idOrNull();

        // Filter cabang HANYA jika bukan superadmin
        if ($user->hasRole('superadmin')) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            // Admin biasa hanya bisa melihat cabang aktifnya
            $query->where('branch_id', $branchId);
        }

        // Marketing hanya melihat customer miliknya sendiri
        if ($user->hasRole('marketing') && $user->employee) {
            $query->where('marketing_id', $user->employee->id);
        }

        // Ambil semua cabang (hanya jika perlu tampil di filter UI)
        $branches = Branch::all();

        // Ambil data customer dengan paginasi
        $customers = $query->with(['branch', 'invoice', 'customerProducts.product', 'customerProducts.categoryProduct'])->latest()->paginate(10);

        return view('customer.index', compact('customers', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categories = CategoryProduct::all();
        $products   = Product::all();
        $parts      = Part::all();
        $employees  = Employee::where('is_active', true)->orderBy('name')->get();

        $fromSo = $request->filled('from_so')
            ? SalesOrder::find($request->integer('from_so'))
            : null;

        return view('customer.create', compact('categories', 'products', 'parts', 'employees', 'fromSo'));
    }
public function store(Request $request, InstallTypeResolver $resolver, CommissionService $commission, CustomerCommissionSyncService $commissionSync, FilmStockPoolService $filmStock)
{
    // SO yang sudah ditugaskan teknisi/kenek-nya (lewat SalesOrderController::assign())
    // jadi sumber kebenaran untuk tukang_id/kenek_id/marketing_id Customer ini —
    // tidak boleh dipilih ulang manual di sini. Lihat juga customer/create.blade.php
    // yang menampilkannya read-only ketika $fromSo->tukang_id sudah terisi.
    $fromSo = $request->filled('from_so') ? SalesOrder::find($request->integer('from_so')) : null;
    $tukangLockedFromSo = (bool) $fromSo?->tukang_id;

    // 1) Validasi
    $request->validate([
        'wsn'               => ['required','string'],
        'name'              => ['required','string','max:255'],
        'email'             => ['nullable','email'],
        'phone_number'      => ['required','string'],
        'address'           => ['required','string'],
        'vehicle_brand'     => ['required','string'],
        'vehicle_model'     => ['required','string'],
        'plat_number'       => ['required','string'],
        'vehicle_year'      => ['required','integer'],
        'dealer_name'       => ['required','string'],
        'city'              => ['required','string'],
        'country'           => ['required','string'],

        'install_type'      => ['required', Rule::in(['fullset','skkb','dsp'])],
        'has_panoramic'     => ['nullable','boolean'],
        'install_date'      => ['required', 'date', 'before_or_equal:today'],

        'share_mode'                 => ['required', Rule::in(['auto','custom'])],
        'installer_share'            => ['nullable','array'],
        'installer_share.tukang'     => ['nullable','integer','min:0','max:100'],
        'installer_share.kenek'      => ['nullable','integer','min:0','max:100'],
        'normalize_100'              => ['nullable','boolean'],

        // required hanya kalau tidak datang dari SO yang sudah ditugaskan —
        // nilainya sendiri diambil paksa dari $fromSo di bawah, bukan dari input ini.
        'tukang_id'         => [$tukangLockedFromSo ? 'nullable' : 'required', 'exists:employees,id'],
        'kenek_id'          => ['nullable','exists:employees,id'],
        'marketing_id'      => ['nullable','exists:employees,id'],

        'warantee_duration' => ['required', Rule::in([5,7])],

        'products'                       => ['required','array','min:1'],
        'products.*.product_id'          => ['required','exists:products,id'],
        'products.*.category_product_id' => ['required','exists:category_products,id'],
        'products.*.part_id'             => ['required','exists:parts,id'],

        'products.*.used_length_meter' => ['nullable', 'numeric', 'min:0'],
        'deduct_roll_stock' => ['nullable', 'boolean'],

        'admin_password'     => ['required','string'],
    ]);

    $tukangId    = $tukangLockedFromSo ? (int) $fromSo->tukang_id : (int) $request->tukang_id;
    $kenekId     = $tukangLockedFromSo ? $fromSo->kenek_id : ($request->filled('kenek_id') ? (int) $request->kenek_id : null);
    $marketingId = $tukangLockedFromSo && $fromSo->marketing_id
        ? $fromSo->marketing_id
        : ($request->filled('marketing_id') ? (int) $request->marketing_id : null);

    // 2) Konfirmasi admin
    $admin = Auth::user();
    if (!Hash::check($request->admin_password, $admin->password)) {
        return back()->withErrors(['admin_password' => 'Password salah.'])->withInput();
    }

    $start = Carbon::now();
    $end   = $start->copy()->addYears((int)$request->warantee_duration);

    DB::beginTransaction();
    try {
        // 3) Card number & simpan customer
        $cardNumber = $this->cardNumbers->generateCustomerCardNumber();

        $branchId = $this->branch->idOrNull() ?? optional($admin->branches()->first())->id;

        $customer = Customer::create([
            'branch_id'         => $branchId,
            'wsn'               => $request->wsn,
            'card_number'       => $cardNumber,
            'name'              => $request->name,
            'email'             => $request->email,
            'phone_number'      => $request->phone_number,
            'address'           => $request->address,
            'vehicle_brand'     => $request->vehicle_brand,
            'vehicle_model'     => $request->vehicle_model,
            'plat_number'       => $request->plat_number,
            'vehicle_year'      => $request->vehicle_year,
            'dealer_name'       => $request->dealer_name,
            'city'              => $request->city,
            'country'           => $request->country,
            'warantee_start'    => $start->toDateString(),
            'warantee_end'      => $end->toDateString(),
            'warantee_duration' => (int)$request->warantee_duration,

            'install_type'      => $request->install_type,
            'has_panoramic'     => $request->boolean('has_panoramic'),
            'tukang_id'         => $tukangId,
            'kenek_id'          => $kenekId,
            'marketing_id'      => $marketingId,
            'install_date'      => $request->install_date,
        ]);

        // 4) Simpan produk
        $createdCustomerProducts = [];

        foreach ($request->products as $prod) {
            $customerProduct = CustomerProduct::create([
                'customer_id'         => $customer->id,
                'product_id'          => (int) $prod['product_id'],
                'category_product_id' => (int) $prod['category_product_id'],
                'part_id'             => (int) $prod['part_id'],
            ]);

            $createdCustomerProducts[] = [
                'customer_product' => $customerProduct,
                'data' => $prod,
            ];
        }

        // 5) Final type (abaikan row customer yang baru dibuat)
        $finalType = $resolver->decideAfterInsert(
            $tukangId,
            $request->install_type,
            $request->boolean('has_panoramic'),
            $customer->id,
            $request->install_date,
        );

        // Stock deduction dipindah ke saat pelunasan invoice (InvoiceController::recordPelunasan)
        // Komisi installer & marketing dibuat saat CommissionReleaseService::release() setelah pelunasan

        DB::commit();

        if ($fromSo && $fromSo->isCompleted() && !$fromSo->isConverted()) {
            $fromSo->update([
                'converted_to_type' => Customer::class,
                'converted_to_id'   => $customer->id,
                'converted_at'      => now(),
            ]);
        }

        return redirect()
            ->route('customer.index')
            ->with('success', "Customer berhasil ditambahkan. Card Number: {$cardNumber}");

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
    }
}

public function edit(Customer $customer)
{
    $user = auth()->user();

    if (!$user->hasRole('superadmin')) {
        $activeBranchId = $this->branch->id();

        if ((int) $customer->branch_id !== $activeBranchId) {
            abort(403, 'Tidak boleh mengakses customer dari cabang lain.');
        }
    }

    $customer->load([
        'customerProducts.part',
        'customerProducts.categoryProduct',
        'customerProducts.product',
        'tukang',
        'kenek',
        'marketing',
        'branch',
    ]);

    $hasPano = (bool) $customer->has_panoramic;

    $categories = CategoryProduct::orderBy('name')->get();
    $products   = Product::orderBy('name')->get();
    $parts      = Part::orderBy('name')->get();
    $employees  = Employee::where('is_active', true)->orderBy('name')->get();

    return view('customer.edit', compact(
        'customer',
        'hasPano',
        'categories',
        'products',
        'parts',
        'employees'
    ));
}


    public function update(
    Request $request,
    Customer $customer,
    InstallTypeResolver $resolver,
    CommissionService $commission,
    CustomerCommissionSyncService $commissionSync,
    AditCommissionService $aditCommission
) {
    $user = auth()->user();
    if (!$user->hasRole('superadmin')) {
        $activeBranchId = $this->branch->id();

        if ((int) $customer->branch_id !== $activeBranchId) {
            abort(403, 'Tidak boleh mengubah customer dari cabang lain.');
        }
    }

    // 1) Validasi input
    $request->validate([
        'wsn'               => ['required','string'],
        'name'              => ['required','string','max:255'],
        'email'             => ['nullable','email'],
        'phone_number'      => ['required','string'],
        'address'           => ['required','string'],
        'vehicle_brand'     => ['required','string'],
        'vehicle_model'     => ['required','string'],
        'plat_number'       => ['required','string'],
        'vehicle_year'      => ['required','integer'],
        'dealer_name'       => ['required','string'],
        'city'              => ['required','string'],
        'country'           => ['required','string'],

        'install_type'      => ['required', Rule::in(['fullset','skkb','dsp'])],
        'has_panoramic'     => ['nullable','boolean'],
        'install_date'      => ['required', 'date', 'before_or_equal:today'],

        'share_mode'                 => ['required', Rule::in(['auto','custom'])],
        'installer_share'            => ['nullable','array'],
        'installer_share.tukang'     => ['nullable','integer','min:0','max:100'],
        'installer_share.kenek'      => ['nullable','integer','min:0','max:100'],
        'normalize_100'              => ['nullable','boolean'],

        'tukang_id'         => ['required','exists:employees,id'],
        'kenek_id'          => ['nullable','exists:employees,id'],
        'marketing_id'      => ['nullable','exists:employees,id'],

        'warantee_duration' => ['required', Rule::in([5,7])],

        'products'                          => ['required','array','min:1'],
        'products.*.id'                     => ['nullable','integer','exists:customer_product,id'],
        'products.*.product_id'             => ['required','exists:products,id'],
        'products.*.category_product_id'    => ['required','exists:category_products,id'],
        'products.*.part_id'                => ['required','exists:parts,id'],

        'admin_password'     => ['required','string'],
    ]);

    // 2) Konfirmasi admin
    $admin = Auth::user();
    if (!Hash::check($request->admin_password, $admin->password)) {
        return back()->withErrors(['admin_password' => 'Password salah.'])->withInput();
    }

    // 3) Hitung ulang masa garansi (mengikuti pola di store)
    $start = Carbon::now();
    $end   = $start->copy()->addYears((int)$request->warantee_duration);

    $oldMarketingId = $customer->marketing_id ? (int)$customer->marketing_id : null;
    $newMarketingId = $request->filled('marketing_id') ? (int)$request->marketing_id : null;

    DB::beginTransaction();
    try {
        // === Update master customer (card_number TIDAK diubah) ===
        $customer->update([
            'wsn'               => $request->wsn,
            'name'              => $request->name,
            'email'             => $request->email,
            'phone_number'      => $request->phone_number,
            'address'           => $request->address,
            'vehicle_brand'     => $request->vehicle_brand,
            'vehicle_model'     => $request->vehicle_model,
            'plat_number'       => $request->plat_number,
            'vehicle_year'      => $request->vehicle_year,
            'dealer_name'       => $request->dealer_name,
            'city'              => $request->city,
            'country'           => $request->country,

            'warantee_start'    => $start->toDateString(),
            'warantee_end'      => $end->toDateString(),
            'warantee_duration' => (int)$request->warantee_duration,

            'install_type'      => $request->install_type,
            'has_panoramic'     => $request->boolean('has_panoramic'),
            'tukang_id'         => (int)$request->tukang_id,
            'kenek_id'          => $request->filled('kenek_id') ? (int)$request->kenek_id : null,
            'marketing_id'      => $newMarketingId,
            'install_date'      => $request->install_date,
        ]);

        // === Sinkronisasi produk (create/update/delete) ===
        $this->productSync->syncCarProducts($customer->id, $request->products);

        // === Re-evaluasi final type (menggunakan rule resolver yang sama) ===
        $finalType = $resolver->decideAfterInsert(
            (int)$request->tukang_id,
            $request->install_type,
            $request->boolean('has_panoramic'),
            $customer->id,
            $request->install_date,
        );

        // === Sync marketing + Adit override jika sudah dirilis dan marketing berubah ===
        if ($oldMarketingId !== $newMarketingId) {
            $invoice = $customer->invoice;
            if ($invoice && $invoice->commission_released_at) {
                // Ambil install_type dari komisi marketing lama (sudah dalam format base: fullset/dsp/skkb)
                $oldRow   = DB::table('commissions')
                    ->where('invoice_id', $invoice->id)
                    ->where('role', 'marketing')
                    ->first();
                $baseType = $oldRow?->install_type ?? 'fullset';

                // Hapus komisi marketing lama
                DB::table('commissions')
                    ->where('invoice_id', $invoice->id)
                    ->where('role', 'marketing')
                    ->delete();

                // Buat komisi marketing baru untuk marketing yang baru
                if ($newMarketingId) {
                    $commission->createMarketingForCustomerAutoCategory(
                        $newMarketingId,
                        $customer->id,
                        $invoice->id,
                        $baseType,
                        (int) $invoice->price,
                    );
                }

                // Sync Adit override — triggernya dari marketing,
                // jadi harus dijalankan ulang setelah marketing berubah.
                // Reload relasi supaya AditCommissionService membaca marketing yang baru.
                $invoice->load('customer.marketing');
                $aditCommission->sync($invoice, $invoice->price);
            }
        }

        DB::commit();

        return redirect()
            ->route('customer.index')
            ->with('success', "Customer {$customer->card_number} berhasil diperbarui.");
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal memperbarui data: '.$e->getMessage());
    }
}



    public function destroy(Customer $customer)
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $activeBranchId = $this->branch->id();

            if ((int) $customer->branch_id !== $activeBranchId) {
                abort(403, 'Tidak boleh menghapus customer dari cabang lain.');
            }
        }

        $customer->delete();

        return redirect()->route('customer.index')->with('success', 'Customer berhasil dihapus.');
    }

    public function getProductsByCategory($id)
    {
        $products = \App\Models\Product::where('category_id', $id)->get();
        return response()->json($products);
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'customers.xlsx');
    }

    public function bulkDelete(Request $request)
    {
        $query = Customer::whereIn('id', $request->selected ?? []);

        if (!auth()->user()->hasRole('superadmin')) {
            $query->where('branch_id', $this->branch->id());
        }

        $query->delete();

        return redirect()->route('customer.index')->with('success', 'Data berhasil dihapus.');
    }

    public function download(string $cardNumber, ?int $cp = null): BinaryFileResponse
    {
        $raw = $cardNumber;
        $normalize = preg_replace('/[\s-]+/', '', trim((string) $raw));

        // === MOBIL ===
        $car = DB::table('customers as c')
            ->leftJoin('customer_product as cp', 'cp.customer_id', '=', 'c.id')
            ->leftJoin('category_products as cat', 'cat.id', '=', 'cp.category_product_id')
            ->leftJoin('products as p', 'p.id', '=', 'cp.product_id')
            ->when($cp, fn($q) => $q->where('cp.id', $cp)) // <-- filter item jika ada
            ->where(function ($q) use ($normalize) {
                if (ctype_digit($normalize)) $q->where('c.card_number', (int) $normalize);
                $q->orWhereRaw('CAST(c.card_number AS CHAR) = ?', [$normalize]);
            })
            ->select([
                'c.id as customer_id',
                'c.branch_id',
                'c.name',
                DB::raw('CAST(c.card_number AS CHAR) as card_number'),
                'c.vehicle_brand',
                'c.plat_number',
                'c.warantee_start',
                'c.warantee_end',
                'c.warantee_duration',
                'cat.id as cat_id',
                'cat.duration as cat_duration',
                'p.name as product_name',
            ])
            ->first();

        $isBuilding = false;
        $row = $car;

        if(!$row){
            $building = DB::table('customer_building as cb')
             ->leftJoin('customer_building_product as cbp', 'cbp.customer_building_id', '=', 'cb.id')
             ->leftJoin('category_building as ccat', 'ccat.id', '=', 'cbp.category_building_id')
             ->leftJoin('product_building as pb', 'pb.id', '=', 'cbp.product_building_id')
             ->whereRaw('REPLACE(REPLACE(cb.card_number," ", ""),"-","") = ?', [$normalize])
             ->select([
                'cb.id as customer_building_id',
                    'cb.branch_id',
                    'cb.name',
                    'cb.card_number',
                    'cb.address',
                    'cb.city',
                    'cb.warantee_start',
                    'cb.warantee_end',
                    'cb.warantee_duration',
                    'ccat.id as cat_id',
                    'ccat.duration as cat_duration',
                    'pb.name as product_name',
             ])
             ->first();

        if(!$building) {
            abort(404);
        }
        $isBuilding = true;
        $row = $building;
        }

        // Authenticated route, but not branch-scoped by default — without
        // this, any logged-in staffer (any branch) could enumerate other
        // branches' customer warranty cards by card number (audit finding M-5).
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array((int) $row->branch_id, $allowedBranchIds), 403);
        }

        [$colors, $template] = $this->styleByCategory((int) ($row->cat_id ?? 1));

        $templatePath = public_path('assets/' . $template);
        $fontSemiBold = resource_path('fonts/Montserrat-semibold.ttf');
        $fontRegular = resource_path('fonts/Montserrat-regular.ttf');
        $fontHeavy = resource_path('fonts/Mont-HeavyDEMO.otf');

        $img = Image::read($templatePath);

        $putText = function (
            $text,
            $fontPath,
            int $size,
            string $hexColor,
            string $align,
            int $xOffset,
            int $yOffset
        ) use ($img) {
            $baseX = match ($align) {
                'left'   => 0,
                'center' => (int) ($img->width() / 2),
                'right'  => $img->width(),
            };

            // FIX: pakai $xOffset untuk X (bukan $yOffset)
            $img->text($text ?? '-', $baseX + $xOffset, $yOffset, function ($font) use ($fontPath, $size, $hexColor, $align) {
                $font->filename($fontPath);
                $font->size($size);
                $font->color('#' . ltrim($hexColor, '#'));
                $font->align(match ($align) {
                    'left' => 'left', 'right' => 'right', default => 'center'
                });
                $font->valign('top');
            });
        };
        $putText($row->name,        $fontSemiBold, 40, $colors['text1'], 'center', -13, 280);
        $putText($row->card_number, $fontSemiBold, 30, $colors['text2'], 'center', -13, 340); // FIX warna

        // Kolom kiri (mobil) — kedua-duanya pakai x=80
        if (!$isBuilding) {
            $putText($row->vehicle_brand, $fontRegular, 30, $colors['left1'], 'left', 80, 411);
            $putText($row->plat_number,   $fontRegular, 30, $colors['left2'], 'left', 80, 450); // FIX xOffset 80
        } else {
            $putText($row->city,    $fontRegular, 30, $colors['left1'], 'left', 80, 411);
            $putText($row->address, $fontRegular, 30, $colors['left2'], 'left', 80, 450);
        }

        // Kolom tengah (label + tanggal)
        $putText('Valid From', $fontRegular, 30, $colors['center1'], 'center',  5, 411);
        $putText(
            $row->warantee_start ? date('d/m/Y', strtotime($row->warantee_start)) : '-',
            $fontRegular, 30, $colors['center2'], 'center', 5, 450
        );

        // Kolom kanan (label + tanggal)
        $putText('Valid Until', $fontRegular, 30, $colors['right1'], 'right',  -80, 411);
        $putText(
            $row->warantee_end ? date('d/m/Y', strtotime($row->warantee_end)) : '-',
            $fontRegular, 30, $colors['right2'], 'right', -80, 450
        );

        // Angka durasi (tahun) di kanan atas
        $putText((string)($row->warantee_duration ?? $row->cat_duration ?? '-'),
            $fontHeavy, 90, $colors['year'], 'right', -140, 60);

        // ========= 6) Simpan ke temp & download =========
        $tmpPath = tempnam(sys_get_temp_dir(), 'card_') . '.jpg';
        $filename = 'card_' . Str::slug($row->card_number ?? 'acegard') . '.jpg';
        $img->save($tmpPath, 90, 'jpg');

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    private function styleByCategory(int $catId): array
    {
        // Default (kartu1)
        $colors = [
            'text1'   => 'ffffff',
            'text2'   => 'ffffff',
            'left1'   => '000000',
            'left2'   => '000000',
            'center1' => '000000',
            'center2' => '000000',
            'right1'  => '000000',
            'right2'  => '000000',
            'year'    => '055995',
        ];
        $template = 'kartu1.jpg';

        if ($catId === 2) {
            $colors = [
                'text1'   => '000000',
                'text2'   => '000000',
                'left1'   => 'ffffff',
                'left2'   => 'ffffff',
                'center1' => 'ffffff',
                'center2' => 'ffffff',
                'right1'  => 'ffffff',
                'right2'  => 'ffffff',
                'year'    => 'ffffff',
            ];
            $template = 'kartu2.jpg';
        } elseif ($catId === 3) {
            $colors = [
                'text1'   => '000000',
                'text2'   => '000000',
                'left1'   => 'ffffff',
                'left2'   => 'ffffff',
                'center1' => 'ffffff',
                'center2' => 'ffffff',
                'right1'  => 'ffffff',
                'right2'  => 'ffffff',
                'year'    => 'ffffff',
            ];
            $template = 'kartu3.jpg';
        }

        return [$colors, $template];
    }

    public function previewCommission(Request $request, InstallTypeResolver $resolver, CustomerCommissionSyncService $commissionSync)
    {
        $data = $request->validate([
            'install_type'  => ['required', Rule::in(['fullset','skkb','dsp'])],
            'has_panoramic' => ['nullable','boolean'],
            'tukang_id'     => ['nullable','exists:employees,id'],
            'kenek_id'      => ['nullable','exists:employees,id'],
            'share_mode'    => ['nullable', Rule::in(['auto','custom'])],
            'p_tukang'      => ['nullable','integer','min:0','max:100'],
            'p_kenek'       => ['nullable','integer','min:0','max:100'],
            'normalize'     => ['nullable','boolean'],
            'date'          => ['nullable','date'],
        ]);

        $installType  = $data['install_type'];
        $hasPanoramic = (bool) ($data['has_panoramic'] ?? false);
        $tukangId     = $request->integer('tukang_id') ?: null;
        $kenekId      = $request->integer('kenek_id')  ?: null;
        $forDate      = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $finalType = $resolver->decideFromBase($tukangId ?: 0, $installType, $hasPanoramic, $forDate);

        try {
            $preview = $commissionSync->previewSplit(
                finalType: $finalType,
                tukangId:  $tukangId,
                kenekId:   $kenekId,
                shareMode: $data['share_mode'] ?? 'auto',
                pTukang:   (int) ($data['p_tukang'] ?? 0),
                pKenek:    (int) ($data['p_kenek']  ?? 0),
                normalize: (bool) ($data['normalize'] ?? true),
            );
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'final_type' => $finalType], 422);
        }

        return response()->json([
            'ok'         => true,
            'base_type'  => $installType,
            'final_type' => $finalType,
            'hint'       => $installType === 'fullset'
                              ? ($hasPanoramic ? 'Aturan panoramic aktif' : 'Aturan fullset harian (bisa ekstra/plus)')
                              : 'Aturan tetap (SKKB/DSP)',
            'nominal'    => ['teknisi' => $preview['amtT'], 'kenek' => $preview['amtK'], 'pool' => $preview['pool']],
            'split'      => [
                'mode'   => $preview['modeUsed'],
                'tukang' => $preview['tPct'],
                'kenek'  => $preview['kPct'],
                'auto'   => ['tukang' => $preview['autoSplit'], 'kenek' => $preview['autoK']],
            ],
            'preview'    => ['tukang' => $preview['tNom'], 'kenek' => $preview['kNom']],
        ]);
    }

}
