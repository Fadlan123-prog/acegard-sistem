<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\Part;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;

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

        // Filter berdasarkan cabang
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Ambil semua cabang (jika ingin menyesuaikan bisa pakai cabang user saja)
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

        return view('customer.create', compact('categories', 'products', 'parts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'required',
                'address' => 'required|string',
                'vehicle_brand' => 'required|string',
                'vehicle_model' => 'required|string',
                'plat_number' => 'required|string',
                'vehicle_year' => 'required|integer',
                'dealer_name' => 'required|string',
                'applicator' => 'required|string',
                'city' => 'required|string',
                'country' => 'required|string',
                'wsn' => 'required|string',
                'card_number' => 'required|numeric',
                'warantee_duration' => 'required|in:5,7',

                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.category_product_id' => 'required|exists:category_products,id',
                'products.*.part_id' => 'required|exists:parts,id',

                'admin_password' => 'required|string',
            ]);

            // Konfirmasi password admin yang login
            $admin = Auth::user();
            if (!Hash::check($request->admin_password, $admin->password)) {
                return back()
                    ->withErrors(['admin_password' => 'Password salah. Data tidak disimpan.'])
                    ->withInput();
            }

            // Hitung tanggal garansi
            $start = Carbon::now();
            $end = $start->copy()->addYears((int) $request->warranty_duration);

            // Simpan customer
            $customer = Customer::create([
                'branch_id' => $admin->branches()->first()->id ?? null, // sesuaikan relasi jika perlu
                'wsn' => $request->wsn,
                'card_number' => $request->card_number,
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'vehicle_brand' => $request->vehicle_brand,
                'vehicle_model' => $request->vehicle_model,
                'plat_number' => $request->plat_number,
                'vehicle_year' => $request->vehicle_year,
                'dealer_name' => $request->dealer_name,
                'applicator' => $request->applicator,
                'city' => $request->city,
                'country' => $request->country,
                'warantee_start' => $start->format('Y-m-d'),
                'warantee_end' => $end->format('Y-m-d'),
                'warantee_duration' => $request->warrantee_duration,
            ]);

            // Simpan relasi ke customer_product
            foreach ($request->products as $prod) {
                CustomerProduct::create([
                    'customer_id' => $customer->id,
                    'product_id' => $prod['product_id'],
                    'category_product_id' => $prod['category_product_id'],
                    'part_id' => $prod['part_id'],
                ]);
            }

            return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan.');
        }catch(\Exception $e){
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        // Validasi input
    }

    public function edit(Customer $customer)
    {
        $customer->load(['products']); // pastikan relasi dimuat

        return view('customer.edit', [
            'customer' => $customer,
            'customerProduct' => $customer->customerProduct, // jika ini juga dipakai
            'categories' => CategoryProduct::all(),
            'parts' => Part::all(),
            'products' => Product::all(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'wsn' => 'required|string',
            'card_number' => 'required|numeric',
            'warranty_duration' => 'required|in:5,7',
            'part_id' => 'required|exists:parts,id',
            'category_product_id' => 'required|exists:category_products,id',
            'product_id' => 'required|exists:products,id',
            'admin_password' => 'required',
        ]);

        // Verifikasi password admin
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'Password tidak valid.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Update customer
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'wsn' => $request->wsn,
                'card_number' => $request->card_number,
                'warantee_start' => now()->toDateString(),
                'warantee_end' => now()->addYears($request->warranty_duration)->toDateString(),
                'warantee_duration' => $request->warranty_duration,
            ]);

            // Update relasi produk
            $customerProduct = CustomerProduct::where('customer_id', $customer->id)->first();
            if ($customerProduct) {
                $customerProduct->update([
                    'product_id' => $request->product_id,
                    'part_id' => $request->part_id,
                    'category_product_id' => $request->category_product_id,
                ]);
            } else {
                CustomerProduct::create([
                    'customer_id' => $customer->id,
                    'product_id' => $request->product_id,
                    'part_id' => $request->part_id,
                    'category_product_id' => $request->category_product_id,
                ]);
            }

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
}
