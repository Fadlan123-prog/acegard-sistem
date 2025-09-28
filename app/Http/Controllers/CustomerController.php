<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerProduct;
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
use App\Services\InstallTypeResolver;
use App\Services\CommissionService;

class CustomerController extends Controller
{
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
        $branchId = session('active_branch_id');

        // Filter cabang HANYA jika bukan superadmin
        if ($user->hasRole('superadmin')) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            // Admin biasa hanya bisa melihat cabang aktifnya
            $query->where('branch_id', $activeBranchId);
        }

        // Ambil semua cabang (hanya jika perlu tampil di filter UI)
        $branches = Branch::all();

        // Ambil data customer dengan paginasi
        $customers = $query->latest()->paginate(10);

        return view('customer.index', compact('customers', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CategoryProduct::all();
        $products = Product::all();
        $parts = Part::all();
        $employees = Employee::orderBy('name')->get();

        return view('customer.create', compact('categories', 'products', 'parts', 'employees'));
    }
public function store(Request $request, InstallTypeResolver $resolver, CommissionService $commission)
{
    // 1) Validasi
    $request->validate([
        'wsn'               => ['required','string'],
        'name'              => ['required','string','max:255'],
        'email'             => ['required','email'],
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

        'share_mode'                 => ['required', Rule::in(['auto','custom'])],
        'installer_share'            => ['nullable','array'],
        'installer_share.tukang'     => ['nullable','integer','min:0','max:100'],
        'installer_share.kenek'      => ['nullable','integer','min:0','max:100'],
        'normalize_100'              => ['nullable','boolean'],

        'tukang_id'         => ['required','exists:employees,id'],
        'kenek_id'          => ['nullable','exists:employees,id'],
        'marketing_id'      => ['nullable','exists:employees,id'],

        'warantee_duration' => ['required', Rule::in([5,7])],

        'products'                       => ['required','array','min:1'],
        'products.*.product_id'          => ['required','exists:products,id'],
        'products.*.category_product_id' => ['required','exists:category_products,id'],
        'products.*.part_id'             => ['required','exists:parts,id'],

        'admin_password'     => ['required','string'],
    ]);

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
        $year = now()->year;
        $seq  = Customer::whereYear('created_at', $year)->lockForUpdate()->count() + 1;
        $cardNumber = sprintf('%d%04d', $year, $seq);

        $customer = Customer::create([
            'branch_id'         => optional($admin->branches()->first())->id,
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
            'tukang_id'         => (int)$request->tukang_id,
            'kenek_id'          => $request->filled('kenek_id') ? (int)$request->kenek_id : null,
            'marketing_id'      => $request->filled('marketing_id') ? (int)$request->marketing_id : null,
        ]);

        // 4) Simpan produk
        foreach ($request->products as $prod) {
            CustomerProduct::create([
                'customer_id'         => $customer->id,
                'product_id'          => (int)$prod['product_id'],
                'category_product_id' => (int)$prod['category_product_id'],
                'part_id'             => (int)$prod['part_id'],
            ]);
        }

        // 5) Final type (abaikan row customer yang baru dibuat)
        $finalType = $resolver->decideAfterInsert(
            (int)$request->tukang_id,
            $request->install_type,
            $request->boolean('has_panoramic'),
            $customer->id
        );

        // 6) Ambil nominal teknisi/kenek dari config → pool
        $map = config('commission.fixed', []);
        $row = $map[$finalType] ?? null;
        if (!$row) {
            DB::rollBack();
            return back()->withInput()->with('error', "Tipe komisi '$finalType' tidak ditemukan di config/commission.php");
        }
        $amtTukangCfg = (int)($row['Teknisi'] ?? $row['tukang'] ?? 0);
        $amtKenekCfg  = (int)($row['Kenek']   ?? $row['kenek']  ?? 0);
        $pool         = $amtTukangCfg + $amtKenekCfg;

        // 7) Hitung & insert komisi installer
        if ($pool > 0) {
            $now        = now();
            $hasTukang  = (bool)$customer->tukang_id;
            $hasKenek   = (bool)$customer->kenek_id;
            $mode       = $request->input('share_mode','auto');
            $rowsToInsert = [];

            if ($mode === 'custom' && ($hasTukang || $hasKenek)) {
                $pT = (int)$request->input('installer_share.tukang', 0);
                $pK = (int)$request->input('installer_share.kenek',  0);

                // pasangan sesuai yang hadir
                $pairs = [];
                if ($hasTukang) $pairs[] = ['id'=>(int)$customer->tukang_id, 'p'=>$pT];
                if ($hasKenek)  $pairs[] = ['id'=>(int)$customer->kenek_id,  'p'=>$pK];

                $sumP = array_sum(array_column($pairs, 'p'));

                if ($sumP <= 0) {
                    $mode = 'auto'; // fallback ke auto bila input kosong
                } else {
                    // NORMALISASI opsional
                    if ($request->boolean('normalize_100')) {
                        foreach ($pairs as $i => $pr) {
                            $pairs[$i]['p'] = (int) round($pr['p'] * 100 / $sumP);
                        }
                        // koreksi delta kecil agar total = 100
                        $deltaPct = 100 - array_sum(array_column($pairs,'p'));
                        if ($deltaPct !== 0) $pairs[0]['p'] += $deltaPct;
                    }

                    // alokasi (tanpa memaksa total = 100 bila normalize OFF)
                    $sumAmt = 0;
                    foreach ($pairs as $pr) {
                        $pct = max(0, (int)$pr['p']);
                        if ($pct <= 0) continue;
                        $amount = (int) floor($pool * $pct / 100);
                        if ($amount > 0) {
                            $rowsToInsert[] = [
                                'employees_id' => $pr['id'],
                                'customer_id'  => (int)$customer->id,
                                'invoice_id'   => null,
                                'amount'       => $amount,
                                'role'         => 'installer',
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];
                            $sumAmt += $amount;
                        }
                    }

                    // Jika NORMALIZE ON → tambahkan delta pembulatan ke tukang (agar total = pool)
                    if ($request->boolean('normalize_100')) {
                        $deltaAmt = $pool - $sumAmt;
                        if ($deltaAmt !== 0) {
                            foreach ($rowsToInsert as &$r) {
                                if ($hasTukang && $r['employees_id'] === (int)$customer->tukang_id) { $r['amount'] += $deltaAmt; break; }
                                if (!$hasTukang && $hasKenek && $r['employees_id'] === (int)$customer->kenek_id) { $r['amount'] += $deltaAmt; break; }
                            }
                            unset($r);
                        }
                    }
                }
            }
            elseif ($mode === 'auto') { // <<<<<< penting: ELSEIF agar tidak override custom
                $autoT = (int)config('commission.auto_split.tukang', 70);
                if ($hasTukang && $hasKenek) {
                    $tAmt = (int) floor($pool * $autoT / 100);
                    $kAmt = $pool - $tAmt;
                    $rowsToInsert[] = [
                        'employees_id' => (int)$customer->tukang_id,
                        'customer_id'  => (int)$customer->id,
                        'invoice_id'   => null,
                        'amount'       => $tAmt,
                        'role'         => 'installer',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                    $rowsToInsert[] = [
                        'employees_id' => (int)$customer->kenek_id,
                        'customer_id'  => (int)$customer->id,
                        'invoice_id'   => null,
                        'amount'       => $kAmt,
                        'role'         => 'installer',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                } elseif ($hasTukang) {
                    $rowsToInsert[] = [
                        'employees_id' => (int)$customer->tukang_id,
                        'customer_id'  => (int)$customer->id,
                        'invoice_id'   => null,
                        'amount'       => $pool,
                        'role'         => 'installer',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                } elseif ($hasKenek) {
                    $rowsToInsert[] = [
                        'employees_id' => (int)$customer->kenek_id,
                        'customer_id'  => (int)$customer->id,
                        'invoice_id'   => null,
                        'amount'       => $pool,
                        'role'         => 'installer',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }
            }

            if (!empty($rowsToInsert)) {
                DB::table('commissions')->insert($rowsToInsert);
            }
        }

        // 8) Komisi marketing
        if ($request->filled('marketing_id')) {
            $commission->createMarketingForCustomerAutoCategory(
                (int)$request->marketing_id,
                $customer->id,
                null,
                $finalType
            );
        }

        DB::commit();
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
        if (
            !auth()->user()->hasRole('superadmin') &&
            $customer->branch_id != session('active_branch_id')
        ) {
            abort(403, 'Kamu tidak memiliki akses ke data ini.');
        }

        $customer->load(['products']);
        return view('customer.edit', [
            'customer' => $customer,
            'categories' => CategoryProduct::all(),
            'parts' => Part::all(),
            'products' => Product::all(),
        ]);

    }

    public function update(Request $request, Customer $customer)
    {
        if (
            !auth()->user()->hasRole('superadmin') &&
            $customer->branch_id != session('active_branch_id')
        ) {
            abort(403, 'Kamu tidak memiliki akses ke data ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'wsn' => 'required|string',
            'card_number' => 'required|numeric',
            'warrantee_duration' => 'required|in:5,7',

            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.category_product_id' => 'required|exists:category_products,id',
            'products.*.part_id' => 'required|exists:parts,id',

            'admin_password' => 'required',
        ]);

        // Verifikasi password admin
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'Password tidak valid.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Update data customer
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'wsn' => $request->wsn,
                'card_number' => $request->card_number,
                'warantee_start' => now()->toDateString(),
                'warantee_end' => now()->addYears((int) $request->warranty_duration)->toDateString(),
                'warantee_duration' => (int) $request->warranty_duration,
            ]);

            // Ambil semua ID customer_product lama
            $existingIds = CustomerProduct::where('customer_id', $customer->id)->pluck('id')->toArray();
            $newIds = [];

            // Loop produk dari form
            foreach ($request->products as $prod) {
                if (!empty($prod['id'])) {
                    // Update jika ID ada
                    CustomerProduct::where('id', $prod['id'])
                        ->where('customer_id', $customer->id)
                        ->update([
                            'product_id' => $prod['product_id'],
                            'part_id' => $prod['part_id'],
                            'category_product_id' => $prod['category_product_id'],
                        ]);
                    $newIds[] = $prod['id'];
                } else {
                    // Create jika ID tidak ada
                    $created = CustomerProduct::create([
                        'customer_id' => $customer->id,
                        'product_id' => $prod['product_id'],
                        'part_id' => $prod['part_id'],
                        'category_product_id' => $prod['category_product_id'],
                    ]);
                    $newIds[] = $created->id;
                }
            }

            // Hapus yang tidak lagi ada
            $toDelete = array_diff($existingIds, $newIds);
            CustomerProduct::whereIn('id', $toDelete)->delete();

            DB::commit();

            return redirect()->route('customer.index')->with('success', 'Customer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }


    public function destroy(Customer $customer)
    {
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
        Customer::whereIn('id', $request->selected ?? [])->delete();
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

    public function previewCommission(Request $request, InstallTypeResolver $resolver)
{
    $data = $request->validate([
        'install_type'  => ['required', Rule::in(['fullset','skkb','dsp'])],
        'has_panoramic' => ['nullable','boolean'],
        'tukang_id'     => ['nullable','exists:employees,id'],
        'kenek_id'      => ['nullable','exists:employees,id'], // untuk preview pembagian persen
        'share_mode'    => ['nullable', Rule::in(['auto','custom'])],
        'p_tukang'      => ['nullable','integer','min:0','max:100'], // custom percent tukang
        'p_kenek'       => ['nullable','integer','min:0','max:100'], // custom percent kenek
        'normalize'     => ['nullable','boolean'],
        'date'          => ['nullable','date'], // opsional: simulasi tanggal
    ]);

    $installType  = $data['install_type'];
    $hasPanoramic = (bool)($data['has_panoramic'] ?? false);
    $tukangId     = $request->integer('tukang_id') ?: null;
    $kenekId      = $request->integer('kenek_id') ?: null;
    $shareMode    = $data['share_mode'] ?? 'auto';
    $norm         = (bool)($data['normalize'] ?? true);
    $pTukang      = (int)($data['p_tukang'] ?? 0);
    $pKenek       = (int)($data['p_kenek']  ?? 0);
    $forDate      = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();

    // 1) Tentukan final type via resolver PREVIEW (sebelum insert customer)
    $finalType = $resolver->decideFromBase(
        $tukangId ?: 0,
        $installType,
        $hasPanoramic,
        $forDate
    );

    // 2) Ambil config angka teknisi/kenek untuk finalType
    $row = config("commission.fixed.$finalType");
    if (!$row) {
        return response()->json([
            'ok'         => false,
            'message'    => "Config komisi untuk '$finalType' tidak ditemukan.",
            'final_type' => $finalType,
        ], 422);
    }

    $amtT = (int)($row['Teknisi'] ?? $row['tukang'] ?? 0);
    $amtK = (int)($row['Kenek']   ?? $row['kenek']  ?? 0);
    $pool = $amtT + $amtK;

    // 3) Hitung preview pembagian persen
    $hasTukang = !empty($tukangId);
    $hasKenek  = !empty($kenekId);
    $autoT     = (int)config('commission.auto_split.tukang', 70);
    $autoK     = 100 - $autoT;

    $tPct = 0; $kPct = 0;
    $modeUsed = $shareMode;

    if ($shareMode === 'custom' && ($hasTukang || $hasKenek)) {
        $tPct = $hasTukang ? $pTukang : 0;
        $kPct = $hasKenek  ? $pKenek  : 0;
        $sum  = $tPct + $kPct;

        if ($sum <= 0) {
            // fallback ke AUTO bila input kosong
            $modeUsed = 'auto';
        } else if ($norm) {
            // normalisasi ke 100
            $tPct = (int) round($tPct * 100 / $sum);
            $kPct = 100 - $tPct;
        }
    }

    if ($modeUsed === 'auto') {
        if ($hasTukang && $hasKenek) {
            $tPct = $autoT; $kPct = $autoK; // 70:30 default
        } elseif ($hasTukang) {
            $tPct = 100; $kPct = 0;
        } elseif ($hasKenek) {
            $tPct = 0;   $kPct = 100;
        } else {
            // tidak ada installer terpilih
            $tPct = 0; $kPct = 0;
        }
    }

    // 4) Nominal preview
    $tNom = (int) floor($pool * $tPct / 100);
    $kNom = (int) floor($pool * $kPct / 100);
    $delta = $pool - ($tNom + $kNom);
    // Tambahkan delta pembulatan ke tukang bila tukang ada
    if ($delta !== 0 && $hasTukang) $tNom += $delta;
    elseif ($delta !== 0 && $hasKenek) $kNom += $delta;

    return response()->json([
        'ok'         => true,
        'base_type'  => $installType,
        'final_type' => $finalType,
        'hint'       => $installType === 'fullset'
                          ? ($hasPanoramic ? 'Aturan panoramic aktif' : 'Aturan fullset harian (bisa ekstra/plus)')
                          : 'Aturan tetap (SKKB/DSP)',
        'nominal'    => [
            'teknisi' => $amtT,
            'kenek'   => $amtK,
            'pool'    => $pool,
        ],
        'split'      => [
            'mode'   => $modeUsed,     // 'auto' atau 'custom' (setelah fallback)
            'tukang' => $tPct,         // persen
            'kenek'  => $kPct,         // persen
            'auto'   => ['tukang'=>$autoT, 'kenek'=>$autoK],
        ],
        'preview'    => [
            'tukang' => $tNom,         // rupiah
            'kenek'  => $kNom,
        ],
    ]);
}
}
