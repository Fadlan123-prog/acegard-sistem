<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\CustomerBuilding;
use App\Models\CategoryProductBuilding;
use App\Models\CustomerBuildingProduct;
use App\Models\ProductBuilding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerBuildingController extends Controller
{
    public function index(Request $request){
         $query = CustomerBuilding::query();

        // Filter berdasarkan keyword pencarian (nama)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $user = auth()->user();
        $branchId = session('active_branch_id');

        // Filter berdasarkan cabang
        if (!$user->hasRole('superadmin')) {
            $query->where('branch_id', $branchId);
        }

        // Ambil semua cabang (jika ingin menyesuaikan bisa pakai cabang user saja)
        $branches = Branch::all();

        // Ambil data customer dengan paginasi
        $customerBuildings = $query->latest()->paginate(10);
        return view('customer.building.index', compact('customerBuildings', 'branches'));
    }

    public function create(){
        $branches = Branch::all();
        $categories = CategoryProductBuilding::all();
        $products = ProductBuilding::all();
        return view('customer.building.create', compact('branches', 'categories', 'products'));
    }

    public function store(Request $request)
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

            'products'                                  => 'required|array|min:1',
            'products.*.category_product_building_id'   => 'required|integer|exists:category_product_building,id',
            'products.*.product_building_id'            => 'required|integer|exists:product_building,id',
            'products.*.meters'                         => 'required|numeric|min:0.01',
            'products.*.warantee_duration'              => 'required|integer|in:3,5,7',

            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'Password admin salah.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // ====== Generate card_number: BD + YYYY + 5 digit urut per tahun ======
            $year   = now()->year;
            $prefix = 'BD' . $year; // contoh: BD2025

            // Ambil nomor terakhir (lock untuk cegah race condition)
            $lastCard = \App\Models\CustomerBuilding::query()
                ->where('card_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('card_number')   // aman karena suffix zero-padded 5 digit
                ->value('card_number');

            $seq = 1;
            if ($lastCard && preg_match('/^' . preg_quote($prefix, '/') . '(\d{5})$/', $lastCard, $m)) {
                $seq = (int)$m[1] + 1;
            }
            $cardNumber = $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT); // contoh: BD202500001
            // =====================================================================

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

            foreach ($request->input('products', []) as $row) {
                $duration = (int) $row['warantee_duration'];
                \App\Models\CustomerBuildingProduct::create([
                    'customer_building_id'         => $customer->id,
                    'category_product_building_id' => (int)$row['category_product_building_id'],
                    'product_building_id'          => (int)$row['product_building_id'],
                    'meters'                       => (float)$row['meters'],
                    'warantee_duration'            => $duration,
                    'warantee_start'               => now(),
                    'warantee_end'                 => now()->copy()->addYears($duration),
                ]);
            }

            DB::commit();
            return redirect()->route('customer.building.index')
                ->with('success', "Customer berhasil ditambahkan. Card Number: {$cardNumber}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }


    public function edit(CustomerBuilding $customer)
    {
        $branches = Branch::all();
        $categories = CategoryProductBuilding::all();
        $products = ProductBuilding::all();
        $customer->load('products.category', 'products.product');

        return view('customer.building.edit', compact('customer', 'branches', 'categories', 'products'));
    }


    public function update(Request $request, CustomerBuilding $customer)
    {
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

            DB::commit();
            return redirect()->route('customer.building.index')->with('success', 'Customer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function getProductsByCategory($id)
    {
        $products = \App\Models\ProductBuilding::where('category_product_building_id', $id)->get();
        return response()->json($products);
    }

    public function bulkDelete(Request $request)
    {
        CustomerBuilding::whereIn('id', $request->selected ?? [])->delete();
        return redirect()->route('customer.index')->with('success', 'Data berhasil dihapus.');
    }
}
