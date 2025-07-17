<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\CustomerBuilding;
use App\Models\CategoryProductBuilding;
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

        // Filter berdasarkan cabang
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
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
            'warantee_duration' => 'required|integer',
            'admin_password' => 'required|string',
        ]);

        // Verifikasi password admin
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('error', 'Password admin salah.');
        }

        DB::beginTransaction();
        try {
            // Simpan Customer Gedung
            $customer = CustomerBuilding::create([
                'branch_id' => auth()->user()->branches->first()->id ?? 1,
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
                'warantee_duration' => $request->warantee_duration,
                'warantee_start' => now(),
                'warantee_end' => now()->addYears($request->warantee_duration),
            ]);

            // Simpan Produk-produk yang dipilih
            foreach ($request->input('products', []) as $index => $product) {
                CustomerBuildingProduct::create([
                    'customer_building_id' => $customer->id,
                    'category_building_id' => $product['category_product_id'],
                    'product_building_id' => $product['product_id'],
                    'meters' => $product['meters'],
                ]);
            }

            DB::commit();
            return redirect()->route('customer.building.index')->with('success', 'Customer berhasil ditambahkan.');
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
