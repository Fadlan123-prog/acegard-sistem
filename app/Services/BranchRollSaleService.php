<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchRollInvoice;
use App\Models\BranchRollInvoiceItem;
use App\Models\BranchRollInvoiceRoll;
use App\Models\BranchRollPrice;
use App\Models\CategoryProduct;
use App\Models\CategoryProductBuilding;
use App\Models\FilmRollEntry;
use App\Models\FilmStockPool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Penjualan roll utuh antar cabang (cabang penjual ke cabang pembeli) dengan invoice.
 *
 * Alur stok:
 *  - create : roll tertua (FIFO) milik cabang penjual ditandai in_transit, stok pool penjual berkurang.
 *  - receive: cabang pembeli menerima; entri roll yang sama dipindah ke pool pembeli (kode roll tetap).
 *  - cancel : sebelum diterima dan belum lunas, roll kembali ke stok penjual.
 *
 * Invariant pool: total_received = total_used + remaining. Roll yang keluar mengurangi total_received
 * dan remaining (bukan total_used, karena bukan dipakai pemasangan); roll yang masuk menambah keduanya.
 */
class BranchRollSaleService
{
    public function __construct(private DocumentNumberService $docNumbers) {}

    /** Kategori cabang (franchise / non-franchise), null bila belum diatur. */
    public function ownershipOf(?int $branchId): ?string
    {
        return $branchId ? Branch::whereKey($branchId)->value('ownership_type') : null;
    }

    /** Nama film pool (dari kategori katalog, cadangan film_type) dalam bentuk kunci harga. */
    public function filmKeyOf(FilmStockPool $pool): string
    {
        $name = null;

        if ($pool->category_product_building_id) {
            $name = CategoryProductBuilding::whereKey($pool->category_product_building_id)->value('name');
        } elseif ($pool->category_product_id) {
            $name = CategoryProduct::whereKey($pool->category_product_id)->value('name');
        }

        return BranchRollPrice::keyFor($name ?: $pool->film_type);
    }

    /** Harga roll untuk pool ini menurut kategori kepemilikan cabang pembeli. */
    public function priceFor(FilmStockPool $pool, ?string $ownershipType): ?BranchRollPrice
    {
        if (!$ownershipType) {
            return null;
        }

        return BranchRollPrice::where('ownership_type', $ownershipType)
            ->where('film_key', $this->filmKeyOf($pool))
            ->first();
    }

    /** Pool milik cabang yang masih punya roll utuh di gudang, lengkap dengan jumlah roll dan harga. */
    public function availablePools(int $branchId, ?string $buyerOwnership = null): Collection
    {
        return FilmStockPool::where('branch_id', $branchId)
            ->orderBy('film_type')->orderBy('darkness_code')
            ->get()
            ->map(function (FilmStockPool $pool) use ($buyerOwnership) {
                $pool->available_rolls = FilmRollEntry::where('film_stock_pool_id', $pool->id)
                    ->where('branch_id', $pool->branch_id)
                    ->where('status', 'in_warehouse')
                    ->count();
                $price = $this->priceFor($pool, $buyerOwnership);
                $pool->roll_price = $price ? (float) $price->price : null;
                $pool->roll_unit = $price->unit ?? 'roll';

                return $pool;
            })
            ->filter(fn ($p) => $p->available_rolls > 0)
            ->values();
    }

    /**
     * @param  array{from_branch_id:int,to_branch_id:int,invoice_date:string,due_date?:?string,notes?:?string,items:array<int,array{film_stock_pool_id:int,qty:int}>}  $data
     */
    public function create(array $data, int $userId): BranchRollInvoice
    {
        if ((int) $data['from_branch_id'] === (int) $data['to_branch_id']) {
            throw ValidationException::withMessages(['to_branch_id' => 'Cabang pembeli harus berbeda dari cabang penjual.']);
        }

        $buyerOwnership = $this->ownershipOf((int) $data['to_branch_id']);

        if (!$buyerOwnership) {
            throw ValidationException::withMessages(['to_branch_id' => 'Kategori cabang pembeli (franchise / non-franchise) belum diatur. Atur di menu Cabang.']);
        }

        return DB::transaction(function () use ($data, $userId, $buyerOwnership) {
            $number = $this->docNumbers->generate('branch_roll_invoices', 'invoice_number', 'BRINV');

            $invoice = BranchRollInvoice::create([
                'invoice_number' => $number,
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id'   => $data['to_branch_id'],
                'buyer_ownership_type' => $buyerOwnership,
                'invoice_date'   => $data['invoice_date'],
                'due_date'       => $data['due_date'] ?? null,
                'delivery_status' => 'sent',
                'payment_status' => 'unpaid',
                'total_amount'   => 0,
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $userId,
            ]);

            $total = 0.0;

            foreach ($data['items'] as $row) {
                $pool = FilmStockPool::lockForUpdate()->find($row['film_stock_pool_id']);

                if (!$pool || (int) $pool->branch_id !== (int) $data['from_branch_id']) {
                    throw ValidationException::withMessages(['items' => 'Stok yang dipilih bukan milik cabang penjual.']);
                }

                $qty = (int) $row['qty'];
                $label = trim("{$pool->film_type} {$pool->darkness_code}");
                $price = $this->priceFor($pool, $buyerOwnership);

                if (!$price) {
                    $ownLabel = Branch::OWNERSHIP_TYPES[$buyerOwnership] ?? $buyerOwnership;
                    throw ValidationException::withMessages(['items' => "Harga roll {$pool->film_type} untuk kategori {$ownLabel} belum diatur."]);
                }

                $entries = FilmRollEntry::where('film_stock_pool_id', $pool->id)
                    ->where('branch_id', $pool->branch_id)
                    ->where('status', 'in_warehouse')
                    ->orderBy('received_date')->orderBy('id')
                    ->lockForUpdate()
                    ->limit($qty)
                    ->get();

                if ($entries->count() < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$label} tidak cukup. Dibutuhkan {$qty} roll, tersedia {$entries->count()} roll.",
                    ]);
                }

                $unitPrice = (float) $price->price;
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                $item = BranchRollInvoiceItem::create([
                    'branch_roll_invoice_id' => $invoice->id,
                    'film_stock_pool_id'     => $pool->id,
                    'item_name'              => $label,
                    'qty'                    => $qty,
                    'unit'                   => $price->unit ?: 'roll',
                    'unit_price'             => $unitPrice,
                    'total_price'            => $lineTotal,
                ]);

                $length = 0.0;

                foreach ($entries as $entry) {
                    $entry->status = 'in_transit';
                    $entry->current_branch_id = null;
                    $entry->notes = trim(($entry->notes ? $entry->notes . ' | ' : '') . "Dijual ke cabang lain, invoice {$number}");
                    $entry->save();

                    BranchRollInvoiceRoll::create([
                        'branch_roll_invoice_item_id' => $item->id,
                        'film_roll_entry_id'          => $entry->id,
                        'roll_code'                   => $entry->roll_code,
                        'length_meter'                => $entry->length_meter,
                    ]);

                    $length += (float) $entry->length_meter;
                }

                $this->adjustPool($pool, -$length);
            }

            $invoice->update(['total_amount' => $total]);

            return $invoice;
        });
    }

    /** Cabang pembeli menerima roll: entri roll pindah ke pool pembeli. */
    public function receive(BranchRollInvoice $invoice, int $userId): void
    {
        DB::transaction(function () use ($invoice, $userId) {
            $invoice = BranchRollInvoice::lockForUpdate()->findOrFail($invoice->id);

            if ($invoice->delivery_status !== 'sent') {
                throw ValidationException::withMessages(['invoice' => 'Invoice ini sudah diproses.']);
            }

            foreach ($invoice->items()->with(['rolls', 'pool'])->get() as $item) {
                $source = $item->pool;

                $target = FilmStockPool::lockForUpdate()->firstOrCreate(
                    [
                        'branch_id'                    => $invoice->to_branch_id,
                        'category_product_id'          => $source->category_product_id,
                        'product_id'                   => $source->product_id,
                        'category_product_building_id' => $source->category_product_building_id,
                        'product_building_id'          => $source->product_building_id,
                        'usage_for'                    => $source->usage_for,
                    ],
                    [
                        'film_type'                   => $source->film_type,
                        'darkness_code'               => $source->darkness_code,
                        'width_meter'                 => $source->width_meter,
                        'total_received_length_meter' => 0,
                        'total_used_length_meter'     => 0,
                        'remaining_length_meter'      => 0,
                        'remaining_area_meter'        => 0,
                    ]
                );

                $length = 0.0;

                foreach ($item->rolls as $roll) {
                    $entry = FilmRollEntry::lockForUpdate()->find($roll->film_roll_entry_id);

                    if (!$entry || $entry->status !== 'in_transit') {
                        throw ValidationException::withMessages(['invoice' => "Roll {$roll->roll_code} tidak dalam status pengiriman."]);
                    }

                    $entry->film_stock_pool_id = $target->id;
                    $entry->branch_id = $invoice->to_branch_id;
                    $entry->current_branch_id = $invoice->to_branch_id;
                    $entry->status = 'in_warehouse';
                    $entry->received_date = now()->toDateString();
                    $entry->notes = trim(($entry->notes ? $entry->notes . ' | ' : '') . "Diterima dari invoice {$invoice->invoice_number}");
                    $entry->save();

                    $length += (float) $entry->length_meter;
                }

                $this->adjustPool($target, $length);
            }

            $invoice->update([
                'delivery_status' => 'received',
                'received_by'     => $userId,
                'received_at'     => now(),
            ]);
        });
    }

    /** Batalkan sebelum diterima dan sebelum lunas: roll kembali ke stok penjual. */
    public function cancel(BranchRollInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice = BranchRollInvoice::lockForUpdate()->findOrFail($invoice->id);

            if ($invoice->delivery_status !== 'sent') {
                throw ValidationException::withMessages(['invoice' => 'Hanya invoice yang belum diterima yang bisa dibatalkan.']);
            }

            if ($invoice->payment_status === 'paid') {
                throw ValidationException::withMessages(['invoice' => 'Invoice yang sudah lunas tidak bisa dibatalkan.']);
            }

            foreach ($invoice->items()->with('rolls')->get() as $item) {
                $pool = FilmStockPool::lockForUpdate()->find($item->film_stock_pool_id);
                $length = 0.0;

                foreach ($item->rolls as $roll) {
                    $entry = FilmRollEntry::lockForUpdate()->find($roll->film_roll_entry_id);

                    if (!$entry || $entry->status !== 'in_transit') {
                        continue;
                    }

                    $entry->status = 'in_warehouse';
                    $entry->current_branch_id = $invoice->from_branch_id;
                    $entry->notes = trim(($entry->notes ? $entry->notes . ' | ' : '') . "Invoice {$invoice->invoice_number} dibatalkan");
                    $entry->save();

                    $length += (float) $entry->length_meter;
                }

                if ($pool) {
                    $this->adjustPool($pool, $length);
                }
            }

            $invoice->update(['delivery_status' => 'cancelled', 'cancelled_at' => now()]);
        });
    }

    public function markPaid(BranchRollInvoice $invoice): void
    {
        if ($invoice->delivery_status === 'cancelled') {
            throw ValidationException::withMessages(['invoice' => 'Invoice yang dibatalkan tidak bisa dilunasi.']);
        }

        if ($invoice->payment_status === 'paid') {
            return;
        }

        $invoice->update(['payment_status' => 'paid', 'paid_at' => now()]);
    }

    /** Tambah/kurangi total diterima dan sisa pool dengan panjang tertentu (meter). */
    private function adjustPool(FilmStockPool $pool, float $deltaLength): void
    {
        $pool->total_received_length_meter = round(max(0, (float) $pool->total_received_length_meter + $deltaLength), 2);
        $pool->remaining_length_meter = round(max(0, (float) $pool->remaining_length_meter + $deltaLength), 2);
        $pool->remaining_area_meter = round($pool->remaining_length_meter * (float) $pool->width_meter, 2);
        $pool->save();
    }
}
