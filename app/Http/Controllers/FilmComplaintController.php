<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\FilmComplaint;
use App\Models\FilmComplaintItem;
use App\Models\FilmPartUsageEstimate;
use App\Models\Part;
use App\Models\Product;
use App\Models\ProductBuilding;
use App\Services\BranchContext;
use App\Services\FilmStockPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilmComplaintController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = FilmComplaint::with([
            'branch',
            'customer',
            'customerBuilding',
            'creator',
            'items',
        ]);

        if (!$user->hasRole('superadmin')) {
            $query->where('branch_id', $this->branch->id());
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('complaint_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('plat_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customerBuilding', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('complaint_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('complaint_date', '<=', $request->to);
        }

        $complaints = $query
            ->latest('complaint_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();

        return view('film-complaints.index', compact('complaints', 'branches'));
    }

    public function create()
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $customers = Customer::query()
            ->when(!$user->hasRole('superadmin'), function ($q) {
                $q->where('branch_id', $this->branch->id());
            })
            ->orderBy('name')
            ->get();

        $customerBuildings = CustomerBuilding::query()
            ->when(!$user->hasRole('superadmin'), function ($q) {
                $q->where('branch_id', $this->branch->id());
            })
            ->orderBy('name')
            ->get();

        $categoryProducts = CategoryProduct::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $parts = Part::orderBy('name')->get();

        $categoryProductBuildings = CategoryProductBuilding::orderBy('name')->get();
        $productBuildings = ProductBuilding::orderBy('name')->get();

        return view('film-complaints.create', compact(
            'branches',
            'customers',
            'customerBuildings',
            'categoryProducts',
            'products',
            'parts',
            'categoryProductBuildings',
            'productBuildings'
        ));
    }

    public function store(Request $request, FilmStockPoolService $filmStock)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'complaint_type' => ['required', 'in:car,building'],

            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_building_id' => ['nullable', 'exists:customer_building,id'],

            'complaint_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            // Mobil
            'items.*.category_product_id' => ['nullable', 'exists:category_products,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.part_id' => ['nullable', 'exists:parts,id'],
            'items.*.replacement_side' => ['nullable', 'in:not_applicable,left,right,both'],
            'items.*.replacement_scope' => ['nullable', 'in:full,partial,manual'],
            'items.*.coverage_percent' => ['nullable', 'numeric', 'min:1', 'max:100'],

            // Gedung
            'items.*.category_product_building_id' => ['nullable', 'exists:category_product_building,id'],
            'items.*.product_building_id' => ['nullable', 'exists:product_building,id'],
            'items.*.area_meter' => ['nullable', 'numeric', 'min:0'],

            // Pemakaian
            'items.*.used_length_meter' => ['nullable', 'numeric', 'min:0'],
            'items.*.waste_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        if ($data['complaint_type'] === 'car' && empty($data['customer_id'])) {
            return back()->withInput()->with('error', 'Customer mobil wajib dipilih.');
        }

        if ($data['complaint_type'] === 'building' && empty($data['customer_building_id'])) {
            return back()->withInput()->with('error', 'Customer gedung wajib dipilih.');
        }

        $user = auth()->user();

        if (!$user->hasRole('superadmin')) {
            if ((int) $data['branch_id'] !== (int) $this->branch->id()) {
                abort(403, 'Tidak boleh membuat complain untuk cabang lain.');
            }
        }

        DB::beginTransaction();

        try {
            $complaint = FilmComplaint::create([
                'complaint_number' => $this->generateComplaintNumber(),
                'branch_id' => (int) $data['branch_id'],
                'customer_id' => $data['complaint_type'] === 'car' ? $data['customer_id'] : null,
                'customer_building_id' => $data['complaint_type'] === 'building' ? $data['customer_building_id'] : null,
                'complaint_type' => $data['complaint_type'],
                'complaint_date' => $data['complaint_date'],
                'status' => 'completed',
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if ($data['complaint_type'] === 'car') {
                $this->storeCarComplaintItems($data, $complaint, $filmStock);
            }

            if ($data['complaint_type'] === 'building') {
                $this->storeBuildingComplaintItems($data, $complaint, $filmStock);
            }

            DB::commit();

            return redirect()
                ->route('film-complaints.show', $complaint->id)
                ->with('success', 'Complain berhasil disimpan dan stok roll berhasil dikurangi.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[Film Complaint Store] gagal', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan complain: ' . $e->getMessage());
        }
    }

    protected function storeCarComplaintItems(array $data, FilmComplaint $complaint, FilmStockPoolService $filmStock): void
    {
        $customer = Customer::findOrFail($data['customer_id']);

        foreach ($data['items'] as $item) {
            if (empty($item['category_product_id']) || empty($item['product_id']) || empty($item['part_id'])) {
                throw new \Exception('Kategori, produk, dan part mobil wajib diisi.');
            }

            $replacementSide = $item['replacement_side'] ?? 'not_applicable';
            $replacementScope = $item['replacement_scope'] ?? 'full';
            $coveragePercent = (float) ($item['coverage_percent'] ?? 100);
            $manualLength = isset($item['used_length_meter']) && $item['used_length_meter'] !== ''
                ? (float) $item['used_length_meter']
                : null;

            $calculation = $this->calculateComplaintCarUsedLength(
                (int) $item['part_id'],
                $replacementSide,
                $replacementScope,
                $coveragePercent,
                $manualLength
            );

            $usedLength = $calculation['used_length'];
            $usageSource = $calculation['usage_source'];
            $wastePercent = $calculation['waste_percent'];

            $complaintItem = FilmComplaintItem::create([
                'film_complaint_id' => $complaint->id,

                'category_product_id' => (int) $item['category_product_id'],
                'product_id' => (int) $item['product_id'],
                'part_id' => (int) $item['part_id'],

                'replacement_side' => $replacementSide,
                'replacement_scope' => $replacementScope,
                'coverage_percent' => $coveragePercent,

                'used_length_meter' => $usedLength,
                'waste_percent' => $wastePercent,
                'usage_source' => $usageSource,
                'notes' => $item['notes'] ?? null,
            ]);

            $filmStock->consumeFromPool(
                [
                    'branch_id' => (int) $data['branch_id'],
                    'category_product_id' => (int) $item['category_product_id'],
                    'product_id' => (int) $item['product_id'],
                    'usage_for' => 'car',
                ],
                $usedLength,
                [
                    'film_complaint_id' => $complaint->id,

                    'customer_id' => $customer->id,

                    'category_product_id' => (int) $item['category_product_id'],
                    'product_id' => (int) $item['product_id'],
                    'part_id' => (int) $item['part_id'],

                    'usage_type' => 'car',
                    'usage_reason' => 'complaint',
                    'install_type' => 'complaint_replacement',

                    'vehicle_brand' => $customer->vehicle_brand,
                    'vehicle_model' => $customer->vehicle_model,
                    'plat_number' => $customer->plat_number,

                    'area_meter' => $usedLength * 1.5,
                    'waste_percent' => $wastePercent,
                    'usage_source' => $usageSource,

                    'notes' => 'Complain replacement item ID: ' . $complaintItem->id
                        . ' | side=' . $replacementSide
                        . ' | scope=' . $replacementScope
                        . ' | coverage=' . $coveragePercent . '%',
                ]
            );
        }
    }

    protected function storeBuildingComplaintItems(array $data, FilmComplaint $complaint, FilmStockPoolService $filmStock): void
    {
        $customerBuilding = CustomerBuilding::findOrFail($data['customer_building_id']);

        foreach ($data['items'] as $item) {
            if (empty($item['category_product_building_id']) || empty($item['product_building_id'])) {
                throw new \Exception('Kategori dan produk gedung wajib diisi.');
            }

            $areaMeter = (float) ($item['area_meter'] ?? 0);
            $manualLength = isset($item['used_length_meter']) && $item['used_length_meter'] !== ''
                ? (float) $item['used_length_meter']
                : null;

            $wastePercent = (float) ($item['waste_percent'] ?? 10);

            if ($manualLength && $manualLength > 0) {
                $usedLength = $manualLength;
                $usageSource = 'manual';
            } else {
                if ($areaMeter <= 0) {
                    throw new \Exception('Ukuran m² wajib diisi untuk complain gedung.');
                }

                $baseLength = $areaMeter / 1.5;
                $usedLength = round($baseLength + ($baseLength * ($wastePercent / 100)), 2);
                $usageSource = 'estimate';
            }

            $complaintItem = FilmComplaintItem::create([
                'film_complaint_id' => $complaint->id,

                'category_product_building_id' => (int) $item['category_product_building_id'],
                'product_building_id' => (int) $item['product_building_id'],

                'area_meter' => $areaMeter,
                'used_length_meter' => $usedLength,
                'waste_percent' => $wastePercent,
                'usage_source' => $usageSource,
                'notes' => $item['notes'] ?? null,
            ]);

            $filmStock->consumeFromPool(
                [
                    'branch_id' => (int) $data['branch_id'],
                    'category_product_building_id' => (int) $item['category_product_building_id'],
                    'product_building_id' => (int) $item['product_building_id'],
                    'usage_for' => 'building',
                ],
                $usedLength,
                [
                    'film_complaint_id' => $complaint->id,

                    'customer_building_id' => $customerBuilding->id,

                    'category_product_building_id' => (int) $item['category_product_building_id'],
                    'product_building_id' => (int) $item['product_building_id'],

                    'usage_type' => 'building',
                    'usage_reason' => 'complaint',
                    'install_type' => 'complaint_replacement',

                    'area_meter' => $areaMeter,
                    'waste_percent' => $wastePercent,
                    'usage_source' => $usageSource,

                    'notes' => 'Complain building replacement item ID: ' . $complaintItem->id,
                ]
            );
        }
    }

    protected function calculateComplaintCarUsedLength(
        int $partId,
        string $replacementSide,
        string $replacementScope,
        float $coveragePercent,
        ?float $manualLength = null
    ): array {
        if ($replacementScope === 'manual') {
            if (!$manualLength || $manualLength <= 0) {
                throw new \Exception('Manual meter wajib diisi jika tipe pergantian manual.');
            }

            return [
                'used_length' => round($manualLength, 2),
                'usage_source' => 'manual',
                'waste_percent' => 0,
            ];
        }

        $estimate = FilmPartUsageEstimate::where('part_id', $partId)
            ->whereNull('install_type')
            ->first();

        if (!$estimate) {
            throw new \Exception('Estimasi pemakaian part belum tersedia.');
        }

        $baseLength = (float) $estimate->estimated_length_meter;
        $wastePercent = (float) $estimate->waste_percent;

        $sideFactor = match ($replacementSide) {
            'left', 'right' => 0.5,
            'both' => 1.0,
            default => 1.0,
        };

        $coveragePercent = max(1, min($coveragePercent, 100));
        $coverageFactor = $coveragePercent / 100;

        $usedBeforeWaste = $baseLength * $sideFactor * $coverageFactor;
        $usedLength = $usedBeforeWaste + ($usedBeforeWaste * ($wastePercent / 100));

        return [
            'used_length' => round($usedLength, 2),
            'usage_source' => 'estimate',
            'waste_percent' => $wastePercent,
        ];
    }

    public function show(FilmComplaint $filmComplaint)
    {
        $this->authorizeComplaintBranch($filmComplaint);

        $filmComplaint->load([
            'branch',
            'customer',
            'customerBuilding',
            'creator',
            'items.product',
            'items.categoryProduct',
            'items.part',
            'items.productBuilding',
            'items.categoryProductBuilding',
            'stockUsages.stockPool',
        ]);

        return view('film-complaints.show', [
            'complaint' => $filmComplaint,
        ]);
    }

    public function edit(FilmComplaint $filmComplaint)
    {
        $this->authorizeComplaintBranch($filmComplaint);

        return view('film-complaints.edit', [
            'complaint' => $filmComplaint,
        ]);
    }

    public function update(Request $request, FilmComplaint $filmComplaint)
    {
        $this->authorizeComplaintBranch($filmComplaint);

        $data = $request->validate([
            'complaint_date' => ['required', 'date'],
            'reason'         => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string'],
        ]);

        $filmComplaint->update($data);

        return redirect()
            ->route('film-complaints.show', $filmComplaint->id)
            ->with('success', 'Complain berhasil diperbarui.');
    }

    // Status is a label only — changing to 'cancelled' does NOT reverse the film stock
    // already deducted at creation. A real cancellation-with-stock-return flow would need
    // to walk stockUsages() and restore each pool, which is out of scope here.
    public function updateStatus(Request $request, FilmComplaint $filmComplaint)
    {
        $this->authorizeComplaintBranch($filmComplaint);

        $data = $request->validate([
            'status' => ['required', 'in:open,process,completed,cancelled'],
        ]);

        $filmComplaint->update(['status' => $data['status']]);

        return back()->with('success', 'Status complain diperbarui menjadi ' . ucfirst($data['status']) . '.');
    }

    private function authorizeComplaintBranch(FilmComplaint $filmComplaint): void
    {
        $user = auth()->user();

        if (!$user->hasRole('superadmin') && (int) $filmComplaint->branch_id !== (int) $this->branch->id()) {
            abort(403, 'Tidak boleh mengubah complain dari cabang lain.');
        }
    }

    protected function generateComplaintNumber(): string
    {
        do {
            $number = 'CMP-' . now()->format('YmdHis') . '-' . rand(100, 999);
        } while (FilmComplaint::where('complaint_number', $number)->exists());

        return $number;
    }

    public function searchCustomers(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'complaint_type' => ['required', 'in:car,building,mobil,gedung'],
        ]);

        $user = auth()->user();

        $keyword = trim((string) $request->input('q', ''));
        $branchId = $request->input('branch_id');
        $type = $request->input('complaint_type');

        /*
        |--------------------------------------------------------------------------
        | Untuk admin cabang, paksa branch_id sesuai akses user
        |--------------------------------------------------------------------------
        */
        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches->pluck('id')->map(fn ($id) => (int) $id);

            if (!$branchId) {
                $branchId = $this->branch->id();
            }

            if (!$allowedBranchIds->contains((int) $branchId)) {
                return response()->json([
                    'results' => [],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Mobil
        |--------------------------------------------------------------------------
        */
        if (in_array($type, ['car', 'mobil'])) {
            $query = \App\Models\Customer::query()
                ->with('branch');

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($keyword !== '') {
                $cleanKeyword = str_replace([' ', '-', '.', '/', '\\'], '', strtolower($keyword));

                $query->where(function ($q) use ($keyword, $cleanKeyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('card_number', 'like', "%{$keyword}%")
                        ->orWhere('phone_number', 'like', "%{$keyword}%")
                        ->orWhere('plat_number', 'like', "%{$keyword}%")
                        ->orWhere('vehicle_brand', 'like', "%{$keyword}%")
                        ->orWhere('vehicle_model', 'like', "%{$keyword}%")
                        ->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '.', '')) LIKE ?", ["%{$cleanKeyword}%"])
                        ->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE(plat_number, ' ', ''), '-', ''), '.', '')) LIKE ?", ["%{$cleanKeyword}%"]);
                });
            }

            $customers = $query
                ->orderBy('name')
                ->limit(20)
                ->get();

            return response()->json([
                'results' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'text' =>
                            ($customer->name ?? '-') .
                            ' | Kartu: ' . ($customer->card_number ?? '-') .
                            ' | Plat: ' . ($customer->plat_number ?? '-') .
                            ' | HP: ' . ($customer->phone_number ?? '-') .
                            ' | Cabang: ' . ($customer->branch->name ?? '-'),
                    ];
                })->values(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Gedung
        |--------------------------------------------------------------------------
        */
        $query = \App\Models\CustomerBuilding::query()
            ->with('branch');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($keyword !== '') {
            $cleanKeyword = str_replace([' ', '-', '.', '/', '\\'], '', strtolower($keyword));

            $query->where(function ($q) use ($keyword, $cleanKeyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('card_number', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '.', '')) LIKE ?", ["%{$cleanKeyword}%"]);
            });
        }

        $customers = $query
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,

                    // text cukup nama saja, jangan gabungan panjang
                    'text' => $customer->name ?? '-',

                    // data detail untuk tampilan Select2
                    'name' => $customer->name ?? '-',
                    'card_number' => $customer->card_number ?? '-',
                    'plat_number' => $customer->plat_number ?? '-',
                    'phone_number' => $customer->phone_number ?? '-',
                    'branch_name' => $customer->branch->name ?? '-',
                ];
            })->values(),
        ]);
    }
}
