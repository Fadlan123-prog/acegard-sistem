<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\SalesOrder;
use App\Models\CustomerBuilding;
use App\Models\CategoryProductBuilding;
use App\Models\CustomerBuildingProduct;
use App\Models\ProductBuilding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\BranchContext;
use App\Services\CardNumberService;
use App\Services\FilmStockPoolService;

class CustomerBuildingController extends Controller
{
    public function __construct(
        private CardNumberService $cardNumbers,
        private BranchContext $branch,
    ) {}

    public function index(Request $request){
         $query = CustomerBuilding::query();

        // Filter berdasarkan keyword pencarian (nama)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $user = auth()->user();
        $branchId = $this->branch->idOrNull();

        // Filter berdasarkan cabang
        if (!$user->hasRole('superadmin')) {
            $query->where('branch_id', $branchId);
        }

        // Ambil semua cabang (jika ingin menyesuaikan bisa pakai cabang user saja)
        $branches = Branch::all();

        // Ambil data customer dengan paginasi
        $customerBuildings = $query->with('invoices')->latest()->paginate(10);
        return view('customer.building.index', compact('customerBuildings', 'branches'));
    }

    public function create(Request $request)
    {
        $branches   = Branch::all();
        $categories = CategoryProductBuilding::all();
        $products   = ProductBuilding::all();

        $fromSo = $request->filled('from_so')
            ? SalesOrder::find($request->integer('from_so'))
            : null;

        $branchId = auth()->user()->branches->first()->id ?? 1;
        $employees = \App\Models\Employee::where('branch_id', $branchId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'job_position']);
        $fromSo?->loadMissing('technicians');

        return view('customer.building.create', compact('branches', 'categories', 'products', 'fromSo', 'employees'));
    }

    public function store(Request $request, FilmStockPoolService $filmStock)
    {
        $request->validate([
            'wsn'          => 'required|string',
            // 'card_number'  => 'required|string', // ❌ HAPUS: diisi otomatis
            'name'         => 'required|string',
            'phone_number' => 'required|string',
            'email'        => 'nullable|email',
            'address'      => 'nullable|string',
            'dealer_name'  => 'nullable|string',
            'applicator'   => 'nullable|string',
            'city'         => 'nullable|string',
            'country'      => 'nullable|string',
            'technician_meters'                                => 'nullable|array',
            'technician_meters.*.employee_id'                  => ['required', 'integer', \Illuminate\Validation\Rule::exists('employees', 'id')
                ->where('branch_id', auth()->user()->branches->first()->id ?? 1)->where('is_active', true)],
            'technician_meters.*.category_product_building_id' => 'required|integer|exists:category_product_building,id',
            'technician_meters.*.meters'                       => 'required|numeric|min:0.01',

            'products'                                  => 'required|array|min:1',
            'products.*.category_product_building_id'   => 'required|integer|exists:category_product_building,id',
            'products.*.product_building_id'            => 'required|integer|exists:product_building,id',
            'products.*.meters'                         => 'required|numeric|min:0.01',
            'products.*.warantee_duration'              => 'required|integer|in:0,3,5,7',

            'products.*.used_length_meter' => 'nullable|numeric|min:0',
            'deduct_roll_stock' => 'nullable|boolean',
            'building_waste_percent' => 'nullable|numeric|min:0|max:100',

            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'Password admin salah.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $cardNumber = $this->cardNumbers->generateBuildingCardNumber();

            $customer = \App\Models\CustomerBuilding::create([
                'branch_id'    => auth()->user()->branches->first()->id ?? 1,
                'wsn'          => $request->wsn,
                'card_number'  => $cardNumber, // ✅ pakai hasil generate
                'name'         => $request->name,
                'email'        => $request->email,
                'phone_number' => $request->phone_number,
                'address'      => $request->address,
                'dealer_name'  => $request->dealer_name,
                'applicator'   => $request->applicator,
                'city'         => $request->city,
                'country'      => $request->country,
            ]);

            $createdBuildingProducts = [];

            foreach ($request->input('products', []) as $row) {
                $duration = (int) $row['warantee_duration'];

                $customerBuildingProduct = \App\Models\CustomerBuildingProduct::create([
                    'customer_building_id'         => $customer->id,
                    'category_product_building_id' => (int) $row['category_product_building_id'],
                    'product_building_id'          => (int) $row['product_building_id'],
                    'meters'                       => (float) $row['meters'],
                    'warantee_duration'            => $duration,
                    'warantee_start'               => now(),
                    'warantee_end'                 => now()->copy()->addYears($duration),
                ]);

                $createdBuildingProducts[] = [
                    'customer_building_product' => $customerBuildingProduct,
                    'data' => $row,
                ];
            }

            $this->syncTechnicianMeters($customer, $request);

            // Stock deduction dipindah ke saat pelunasan invoice (InvoiceBuildingController::recordPelunasan)

            DB::commit();

            if ($request->filled('from_so')) {
                $so = SalesOrder::find($request->integer('from_so'));
                if ($so && $so->isCompleted() && !$so->isConverted()) {
                    $so->update([
                        'converted_to_type' => \App\Models\CustomerBuilding::class,
                        'converted_to_id'   => $customer->id,
                        'converted_at'      => now(),
                    ]);
                }
            }

            return redirect()->route('customer.building.index')
                ->with('success', "Customer berhasil ditambahkan. Card Number: {$cardNumber}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }


    public function edit(CustomerBuilding $customer)
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $activeBranchId = $this->branch->id();

            if ((int) $customer->branch_id !== $activeBranchId) {
                abort(403, 'Tidak boleh mengakses customer dari cabang lain.');
            }
        }

        $branches = Branch::all();
        $categories = CategoryProductBuilding::all();
        $products = ProductBuilding::all();
        $customer->load('products.category', 'products.product', 'technicianMeters');
        $employees = \App\Models\Employee::where('branch_id', $customer->branch_id)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'job_position']);

        return view('customer.building.edit', compact('customer', 'branches', 'categories', 'products', 'employees'));
    }


    public function update(Request $request, CustomerBuilding $customer)
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $activeBranchId = $this->branch->id();

            if ((int) $customer->branch_id !== $activeBranchId) {
                abort(403, 'Tidak boleh mengubah customer dari cabang lain.');
            }
        }

        $request->validate([
            'wsn' => 'required|string',
            'card_number' => 'required|string',
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'dealer_name' => 'nullable|string',
            'applicator' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'technician_meters'                                => 'nullable|array',
            'technician_meters.*.employee_id'                  => ['required', 'integer', \Illuminate\Validation\Rule::exists('employees', 'id')
                ->where('branch_id', $customer->branch_id)->where('is_active', true)],
            'technician_meters.*.category_product_building_id' => 'required|integer|exists:category_product_building,id',
            'technician_meters.*.meters'                       => 'required|numeric|min:0.01',
            'products' => 'required|array|min:1',
            'products.*.id' => 'nullable|exists:customer_building_product,id',
            'products.*.category_product_building_id' => 'required|exists:category_product_building,id',
            'products.*.product_building_id' => 'required|exists:product_building,id',
            'products.*.meters' => 'required|numeric',
            'products.*.warantee_duration' => 'required|integer',
            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('error', 'Password admin salah.');
        }

        DB::beginTransaction();
        try {
            // Update data customer
            $customer->update([
                'wsn' => $request->wsn,
                'card_number' => $request->card_number,
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'dealer_name' => $request->dealer_name,
                'applicator' => $request->applicator,
                'city' => $request->city,
                'country' => $request->country,
            ]);

            // Update data produk
            $existingIds = [];
            foreach ($request->products as $product) {
                $duration = (int) $product['warantee_duration'];
                $data = [
                    'customer_building_id' => $customer->id,
                    'category_product_building_id' => $product['category_product_building_id'],
                    'product_building_id' => $product['product_building_id'],
                    'meters' => $product['meters'],
                    'warantee_duration' => $duration,
                    'warantee_start' => now(),
                    'warantee_end' => now()->addYears($duration),
                ];

                if (!empty($product['id'])) {
                    $cbProduct = CustomerBuildingProduct::find($product['id']);
                    $cbProduct->update($data);
                    $existingIds[] = $cbProduct->id;
                } else {
                    $new = CustomerBuildingProduct::create($data);
                    $existingIds[] = $new->id;
                }
            }

            // Hapus produk yang dihapus dari form
            $customer->products()->whereNotIn('id', $existingIds)->delete();

            $this->syncTechnicianMeters($customer, $request);

            DB::commit();
            return redirect()->route('customer.building.index')->with('success', 'Customer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(CustomerBuilding $customer)
    {
        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            $activeBranchId = $this->branch->id();

            if ((int) $customer->branch_id !== $activeBranchId) {
                abort(403, 'Tidak boleh menghapus data dari cabang lain.');
            }
        }

        DB::beginTransaction();

        try {
            CustomerBuildingProduct::where('customer_building_id', $customer->id)->delete();

            $customer->delete();

            DB::commit();

            return redirect()
                ->route('customer.building.index')
                ->with('success', 'Customer building berhasil dihapus.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }


    public function getProductsByCategory($id)
    {
        $products = \App\Models\ProductBuilding::where('category_product_building_id', $id)->get();
        return response()->json($products);
    }

    public function bulkDelete(Request $request)
    {
        $query = CustomerBuilding::whereIn('id', $request->selected ?? []);

        if (!auth()->user()->hasRole('superadmin')) {
            $query->where('branch_id', $this->branch->id());
        }

        $query->delete();

        return redirect()->route('customer.index')->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Simpan meter per teknisi per kategori. Jumlah meter per kategori tidak boleh melebihi
     * meter produk kategori itu di customer; meter yang belum dialokasikan tidak dikomisi.
     * Dipanggil di dalam transaksi; melempar ValidationException agar transaksi dibatalkan.
     */
    private function syncTechnicianMeters(CustomerBuilding $customer, Request $request): void
    {
        $rows = collect($request->input('technician_meters', []))
            ->map(fn ($r) => [
                'employee_id' => (int) $r['employee_id'],
                'category_product_building_id' => (int) $r['category_product_building_id'],
                'meters' => round((float) $r['meters'], 2),
            ]);

        $available = collect($request->input('products', []))
            ->groupBy(fn ($p) => (int) $p['category_product_building_id'])
            ->map(fn ($g) => round($g->sum(fn ($p) => (float) $p['meters']), 2));

        foreach ($rows->groupBy('category_product_building_id') as $cat => $group) {
            if ($group->sum('meters') > ($available[$cat] ?? 0) + 0.001) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'technician_meters' => 'Total meter teknisi untuk satu kategori film melebihi meter pada produk customer.',
                ]);
            }
        }

        // satu baris per (teknisi, kategori): gabungkan duplikat
        $merged = $rows->groupBy(fn ($r) => $r['employee_id'] . ':' . $r['category_product_building_id'])
            ->map(fn ($g) => array_merge($g->first(), ['meters' => round($g->sum('meters'), 2)]));

        $customer->technicianMeters()->delete();

        foreach ($merged as $row) {
            $customer->technicianMeters()->create($row);
        }
    }
}
