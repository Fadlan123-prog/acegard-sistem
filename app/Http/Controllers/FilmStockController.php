<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\CategoryProductBuilding;
use App\Models\ProductBuilding;
use App\Models\FilmStockPool;
use App\Models\FilmStockUsage;
use App\Services\BranchContext;
use App\Services\FilmStockPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilmStockController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = FilmStockPool::with(['branch']);

        if (!$user->hasRole('superadmin')) {
            $query->where('branch_id', $this->branch->id());
        } else {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        if ($request->filled('usage_for')) {
            $query->where('usage_for', $request->usage_for);
        }

        if ($request->filled('low_stock')) {
            $query->where('remaining_length_meter', '<=', 10);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('film_type', 'like', "%{$search}%")
                    ->orWhere('darkness_code', 'like', "%{$search}%")
                    ->orWhereHas('branch', function ($branchQuery) use ($search) {
                        $branchQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $pools = $query
            ->orderBy('film_type')
            ->orderBy('darkness_code')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();

        return view('film-stock.index', compact('pools', 'branches'));
    }

    public function create()
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $categoryProducts = CategoryProduct::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $categoryProductBuildings = CategoryProductBuilding::orderBy('name')->get();
        $productBuildings = ProductBuilding::orderBy('name')->get();

        return view('film-stock.create', compact(
            'branches',
            'categoryProducts',
            'products',
            'categoryProductBuildings',
            'productBuildings'
        ));
    }

    public function store(Request $request, FilmStockPoolService $filmStock)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'exists:branches,id'],
            'usage_for' => ['required', 'in:car,building,universal'],

            'category_product_id' => ['nullable', 'exists:category_products,id'],
            'product_id' => ['nullable', 'exists:products,id'],

            'category_product_building_id' => ['nullable', 'exists:category_product_building,id'],
            'product_building_id' => ['nullable', 'exists:product_building,id'],

            'qty_roll' => ['required', 'integer', 'min:1'],
            'width_meter' => ['nullable', 'numeric', 'min:0.01'],
            'length_meter' => ['nullable', 'numeric', 'min:0.01'],

            'roll_codes' => ['required', 'array', 'min:1'],
            'roll_codes.*' => ['required', 'string', 'distinct', 'unique:film_roll_entries,roll_code'],

            'received_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $usageFor = $request->input('usage_for');

            if ($usageFor === 'car' || $usageFor === 'universal') {
                if (!$request->filled('category_product_id')) {
                    $validator->errors()->add('category_product_id', 'Kategori produk mobil wajib dipilih.');
                }

                if (!$request->filled('product_id')) {
                    $validator->errors()->add('product_id', 'Produk mobil / kode kegelapan wajib dipilih.');
                }
            }

            if ($usageFor === 'building') {
                if (!$request->filled('category_product_building_id')) {
                    $validator->errors()->add('category_product_building_id', 'Kategori produk gedung wajib dipilih.');
                }

                if (!$request->filled('product_building_id')) {
                    $validator->errors()->add('product_building_id', 'Produk gedung / kode kegelapan wajib dipilih.');
                }
            }

            $qtyRoll = (int) $request->input('qty_roll', 0);
            $rollCodes = $request->input('roll_codes', []);

            if ($qtyRoll !== count($rollCodes)) {
                $validator->errors()->add('roll_codes', 'Jumlah kode roll harus sama dengan jumlah roll masuk.');
            }
        });

        $data = $validator->validate();

        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);

            if (!$allowedBranchIds->contains((int) $data['branch_id'])) {
                abort(403, 'Tidak boleh input roll untuk cabang lain.');
            }
        }

        try {
            $filmStock->addRolls([
                'branch_id' => (int) $data['branch_id'],
                'usage_for' => $data['usage_for'],

                'category_product_id' => $data['category_product_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,

                'category_product_building_id' => $data['category_product_building_id'] ?? null,
                'product_building_id' => $data['product_building_id'] ?? null,

                'qty_roll' => (int) $data['qty_roll'],
                'width_meter' => $data['width_meter'] ?? 1.5,
                'length_meter' => $data['length_meter'] ?? 30,

                'roll_codes' => $data['roll_codes'],
                'received_date' => $data['received_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            return redirect()
                ->route('film-stock.index')
                ->with('success', 'Roll masuk berhasil disimpan dan stock pool berhasil diperbarui.');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan roll masuk: ' . $e->getMessage());
        }
    }

    public function show(FilmStockPool $pool)
    {
        $this->authorizeBranch($pool);

        $pool->load([
            'branch',
            'rollEntries' => function ($q) {
                $q->latest('received_date')->latest('id');
            },
            'usages' => function ($q) {
                $q->latest('usage_date')->latest('id');
            },
            'usages.customer',
            'usages.customerBuilding',
        ]);

        return view('film-stock.show', compact('pool'));
    }

    public function usages(Request $request)
    {
        $user = auth()->user();

        $query = FilmStockUsage::with([
            'branch',
            'stockPool',
            'customer',
            'customerBuilding',
        ]);

        if (!$user->hasRole('superadmin')) {
            $query->where('branch_id', $this->branch->id());
        } else {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('usage_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('usage_date', '<=', $request->to);
        }

        if ($request->filled('usage_type')) {
            $query->where('usage_type', $request->usage_type);
        }

        if ($request->filled('film_type')) {
            $query->where('film_type', 'like', '%' . $request->film_type . '%');
        }

        if ($request->filled('darkness_code')) {
            $query->where('darkness_code', 'like', '%' . $request->darkness_code . '%');
        }

        $usages = $query
            ->latest('usage_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();

        return view('film-stock.usages', compact('usages', 'branches'));
    }

    protected function authorizeBranch(FilmStockPool $pool): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return;
        }

        if ((int) $pool->branch_id !== (int) $this->branch->id()) {
            abort(403, 'Tidak boleh mengakses stok cabang lain.');
        }
    }
}
