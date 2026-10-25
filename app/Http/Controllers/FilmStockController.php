<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\CategoryProductBuilding;
use App\Models\ProductBuilding;
use App\Models\FilmRollEntry;
use App\Models\FilmStockPool;
use App\Models\FilmStockUsage;
use App\Services\BranchContext;
use App\Services\FilmStockPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * Auto-generate the next available roll codes for a product prefix (e.g. "AP05"),
     * so Roll Masuk no longer needs the code typed by hand — which is what let
     * duplicate/typo'd codes slip through until the unique-constraint error on submit.
     * Looks at ALL existing roll_code rows for that prefix (not just this form's
     * session) so suggestions never collide with roll codes from earlier batches.
     */
    public function suggestRollCodes(Request $request)
    {
        $data = $request->validate([
            'prefix' => ['required', 'string', 'max:50'],
            'qty'    => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $prefix = strtoupper(trim($data['prefix']));

        $maxSeq = FilmRollEntry::where('roll_code', 'like', $prefix . '-%')
            ->pluck('roll_code')
            ->reduce(function ($max, $code) use ($prefix) {
                if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $code, $m)) {
                    return max($max, (int) $m[1]);
                }
                return $max;
            }, 0);

        $codes = [];
        for ($i = 1; $i <= $data['qty']; $i++) {
            $codes[] = $prefix . '-' . str_pad($maxSeq + $i, 3, '0', STR_PAD_LEFT);
        }

        return response()->json(['codes' => $codes]);
    }

    /**
     * Satu kiriman roll masuk bisa berisi beberapa varian (kategori & persentase
     * kegelapan berbeda) sekaligus — "batches" me-loop addRolls() per varian,
     * semua dalam satu transaksi supaya kiriman tersimpan atomik (semua varian
     * masuk, atau tidak sama sekali kalau salah satu gagal).
     */
    public function store(Request $request, FilmStockPoolService $filmStock)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'exists:branches,id'],
            'usage_for' => ['required', 'in:car,building,universal'],
            'received_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'batches' => ['required', 'array', 'min:1'],
            'batches.*.category_product_id' => ['nullable', 'exists:category_products,id'],
            'batches.*.product_id' => ['nullable', 'exists:products,id'],
            'batches.*.category_product_building_id' => ['nullable', 'exists:category_product_building,id'],
            'batches.*.product_building_id' => ['nullable', 'exists:product_building,id'],
            'batches.*.width_meter' => ['nullable', 'numeric', 'min:0.01'],
            'batches.*.length_meter' => ['nullable', 'numeric', 'min:0.01'],
            'batches.*.qty_roll' => ['required', 'integer', 'min:1'],
            'batches.*.roll_codes' => ['required', 'array', 'min:1'],
            'batches.*.roll_codes.*' => ['required', 'string', 'unique:film_roll_entries,roll_code'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $usageFor = $request->input('usage_for');
            $batches = $request->input('batches', []);
            $seenCodes = [];

            foreach ($batches as $i => $batch) {
                $label = 'Varian #' . ($i + 1);

                if ($usageFor === 'car' || $usageFor === 'universal') {
                    if (empty($batch['category_product_id'])) {
                        $validator->errors()->add("batches.$i.category_product_id", "$label: kategori produk mobil wajib dipilih.");
                    }
                    if (empty($batch['product_id'])) {
                        $validator->errors()->add("batches.$i.product_id", "$label: produk / kode kegelapan wajib dipilih.");
                    }
                }

                if ($usageFor === 'building') {
                    if (empty($batch['category_product_building_id'])) {
                        $validator->errors()->add("batches.$i.category_product_building_id", "$label: kategori produk gedung wajib dipilih.");
                    }
                    if (empty($batch['product_building_id'])) {
                        $validator->errors()->add("batches.$i.product_building_id", "$label: produk gedung / kode kegelapan wajib dipilih.");
                    }
                }

                $qtyRoll = (int) ($batch['qty_roll'] ?? 0);
                $rollCodes = $batch['roll_codes'] ?? [];

                if ($qtyRoll !== count($rollCodes)) {
                    $validator->errors()->add("batches.$i.roll_codes", "$label: jumlah kode roll harus sama dengan jumlah roll masuk.");
                }

                foreach ($rollCodes as $code) {
                    $key = strtoupper(trim((string) $code));
                    if ($key === '') {
                        continue;
                    }
                    if (isset($seenCodes[$key])) {
                        $validator->errors()->add("batches.$i.roll_codes", "$label: kode roll \"$code\" duplikat dengan {$seenCodes[$key]}.");
                    }
                    $seenCodes[$key] = $label;
                }
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
            DB::transaction(function () use ($data, $filmStock) {
                foreach ($data['batches'] as $batch) {
                    $filmStock->addRolls([
                        'branch_id' => (int) $data['branch_id'],
                        'usage_for' => $data['usage_for'],

                        'category_product_id' => $batch['category_product_id'] ?? null,
                        'product_id' => $batch['product_id'] ?? null,

                        'category_product_building_id' => $batch['category_product_building_id'] ?? null,
                        'product_building_id' => $batch['product_building_id'] ?? null,

                        'qty_roll' => (int) $batch['qty_roll'],
                        'width_meter' => $batch['width_meter'] ?? 1.5,
                        'length_meter' => $batch['length_meter'] ?? 30,

                        'roll_codes' => $batch['roll_codes'],
                        'received_date' => $data['received_date'] ?? now()->toDateString(),
                        'notes' => $data['notes'] ?? null,
                    ]);
                }
            });

            $varianCount = count($data['batches']);

            return redirect()
                ->route('film-stock.index')
                ->with('success', "Roll masuk berhasil disimpan ({$varianCount} varian) dan stock pool berhasil diperbarui.");

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
