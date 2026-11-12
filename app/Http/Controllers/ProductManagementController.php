<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use App\Models\PpfProduct;
use App\Models\Product;
use App\Models\ProductBuilding;
use App\Services\CatalogUsage;
use App\Services\CommissionRates;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Manajemen katalog produk: kategori film (mobil & gedung) beserta produknya (kode + persentase kegelapan),
 * dan daftar produk PPF. Penghapusan ditolak bila masih dipakai data lain.
 */
class ProductManagementController extends Controller
{
    private const TYPES = [
        'car' => [
            'label' => 'Kaca Film Mobil', 'category' => CategoryProduct::class, 'product' => Product::class,
            'category_table' => 'category_products', 'product_table' => 'products', 'fk' => 'category_id',
        ],
        'building' => [
            'label' => 'Kaca Film Gedung', 'category' => CategoryProductBuilding::class, 'product' => ProductBuilding::class,
            'category_table' => 'category_product_building', 'product_table' => 'product_building', 'fk' => 'category_product_building_id',
        ],
    ];

    public function __construct(private CatalogUsage $usage, private CommissionRates $rates) {}

    private function cfg(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['car', 'building', 'ppf'], true) ? $request->query('tab') : 'car';
        $data = ['title' => 'Manajemen Produk', 'tab' => $tab, 'types' => self::TYPES];

        if ($tab === 'ppf') {
            $data['ppfProducts'] = PpfProduct::orderBy('brand')->orderBy('name')->get();
            $data['ppfUsage'] = $data['ppfProducts']->mapWithKeys(fn ($p) => [$p->id => $this->usage->usage('ppf_products', $p->id)]);
        } else {
            $cfg = self::TYPES[$tab];
            $categories = $cfg['category']::orderBy('name')->get();
            $products = $cfg['product']::orderBy('name')->get()->groupBy($cfg['fk']);

            $data['categories'] = $categories->map(function ($c) use ($tab, $products) {
                $c->setRelation('catalogProducts', $products->get($c->id, collect())->map(function ($p) use ($tab) {
                    $p->usageSummary = $this->usage->usage($tab === 'car' ? 'products' : 'product_building', $p->id)
                        + $this->usage->extraUsage('product', $tab, $p->id);

                    return $p;
                }));
                $c->commissionRates = $this->rates->forCategory($tab, $c->id);
                $c->usageSummary = $this->usage->usage(
                    $tab === 'car' ? 'category_products' : 'category_product_building', $c->id,
                    [$tab === 'car' ? 'products' : 'product_building']
                ) + $this->usage->extraUsage('category', $tab, $c->id);

                return $c;
            });
        }

        return view('product-management.index', $data);
    }

    // ── Kategori ───────────────────────────────────────────────────────────

    public function storeCategory(Request $request, string $type)
    {
        $cfg = $this->cfg($type);
        $data = $this->validateCategory($request, $cfg);

        $category = new $cfg['category'];
        $category->forceFill(['name' => $data['name'], 'duration' => $data['duration']])->save();

        return redirect()->route('product-management.index', ['tab' => $type])
            ->with('success', "Kategori \"{$data['name']}\" ditambahkan.");
    }

    public function updateCategory(Request $request, string $type, int $id)
    {
        $cfg = $this->cfg($type);
        $category = $cfg['category']::findOrFail($id);
        $data = $this->validateCategory($request, $cfg, $id);

        $category->forceFill(['name' => $data['name'], 'duration' => $data['duration']])->save();

        return redirect()->route('product-management.index', ['tab' => $type])
            ->with('success', "Kategori \"{$data['name']}\" diperbarui.");
    }

    public function destroyCategory(string $type, int $id)
    {
        $cfg = $this->cfg($type);
        $category = $cfg['category']::findOrFail($id);

        $productCount = $cfg['product']::where($cfg['fk'], $id)->count();
        if ($productCount > 0) {
            return back()->with('error', "Kategori \"{$category->name}\" masih memiliki {$productCount} produk. Hapus produknya dulu.");
        }

        $usage = $this->usage->usage($cfg['category_table'], $id, [$cfg['product_table']]) + $this->usage->extraUsage('category', $type, $id);
        if ($usage) {
            return back()->with('error', "Kategori \"{$category->name}\" tidak bisa dihapus karena masih dipakai: " . CatalogUsage::describe($usage) . '.');
        }

        $name = $category->name;
        $category->delete();
        $this->rates->forget($type, $id);

        return redirect()->route('product-management.index', ['tab' => $type])->with('success', "Kategori \"{$name}\" dihapus.");
    }

    // ── Tarif komisi per kategori ──────────────────────────────────────────

    public function updateCommission(Request $request, string $type, int $id)
    {
        $cfg = $this->cfg($type);
        $category = $cfg['category']::findOrFail($id);

        $keys = array_keys($type === 'car' ? CommissionRates::CAR_KEYS : CommissionRates::BUILDING_KEYS);
        $labels = $type === 'car' ? CommissionRates::CAR_KEYS : CommissionRates::BUILDING_KEYS;

        $rules = [];
        $messages = [];
        foreach ($keys as $key) {
            $rules[$key] = ['required', 'integer', 'min:0', 'max:50000000'];
            $messages["$key.required"] = "Tarif {$labels[$key]} wajib diisi (isi 0 bila tidak ada komisi).";
            $messages["$key.integer"]  = "Tarif {$labels[$key]} harus berupa angka rupiah tanpa titik atau koma.";
            $messages["$key.min"]      = "Tarif {$labels[$key]} tidak boleh negatif.";
            $messages["$key.max"]      = "Tarif {$labels[$key]} terlalu besar (maksimal Rp50.000.000).";
        }

        // Angka boleh diketik dengan titik ribuan (mis. 120.000): bersihkan dulu.
        $request->merge(collect($keys)->mapWithKeys(fn ($k) => [$k => preg_replace('/[^\d]/', '', (string) $request->input($k, ''))])->all());
        $data = $request->validate($rules, $messages);

        $this->rates->save($type, $id, $data, $request->user()->id);

        return redirect()->route('product-management.index', ['tab' => $type])
            ->with('success', "Tarif komisi kategori \"{$category->name}\" disimpan. Berlaku untuk komisi yang dirilis mulai sekarang.");
    }

    // ── Produk dalam kategori ──────────────────────────────────────────────

    public function storeProduct(Request $request, string $type, int $categoryId)
    {
        $cfg = $this->cfg($type);
        $cfg['category']::findOrFail($categoryId);
        $data = $this->validateProduct($request, $cfg, $categoryId);

        $product = new $cfg['product'];
        $product->forceFill([$cfg['fk'] => $categoryId, 'name' => $data['name'], 'description' => $data['description']])->save();

        return redirect()->route('product-management.index', ['tab' => $type])
            ->with('success', "Produk \"{$data['name']}\" ditambahkan.");
    }

    public function updateProduct(Request $request, string $type, int $id)
    {
        $cfg = $this->cfg($type);
        $product = $cfg['product']::findOrFail($id);
        $data = $this->validateProduct($request, $cfg, (int) $product->{$cfg['fk']}, $id);

        $product->forceFill(['name' => $data['name'], 'description' => $data['description']])->save();

        return redirect()->route('product-management.index', ['tab' => $type])
            ->with('success', "Produk \"{$data['name']}\" diperbarui.");
    }

    public function destroyProduct(string $type, int $id)
    {
        $cfg = $this->cfg($type);
        $product = $cfg['product']::findOrFail($id);

        $usage = $this->usage->usage($cfg['product_table'], $id) + $this->usage->extraUsage('product', $type, $id);
        if ($usage) {
            return back()->with('error', "Produk \"{$product->name}\" tidak bisa dihapus karena masih dipakai: " . CatalogUsage::describe($usage) . '.');
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('product-management.index', ['tab' => $type])->with('success', "Produk \"{$name}\" dihapus.");
    }

    // ── Validasi ───────────────────────────────────────────────────────────

    private function validateCategory(Request $request, array $cfg, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100', Rule::unique($cfg['category_table'], 'name')->ignore($ignoreId)],
            'duration' => ['required', 'integer', 'min:0', 'max:20'],
        ], [
            'name.required'  => 'Nama kategori wajib diisi.',
            'name.unique'    => 'Nama kategori sudah dipakai.',
            'duration.required' => 'Lama garansi wajib diisi (tahun).',
        ]);
    }

    private function validateProduct(Request $request, array $cfg, int $categoryId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique($cfg['product_table'], 'name')->where($cfg['fk'], $categoryId)->ignore($ignoreId)],
            'description' => ['required', 'string', 'max:50'],
        ], [
            'name.required'        => 'Kode produk wajib diisi.',
            'name.unique'          => 'Kode produk sudah ada di kategori ini.',
            'description.required' => 'Persentase kegelapan wajib diisi (mis. 20%).',
        ]);
    }
}
