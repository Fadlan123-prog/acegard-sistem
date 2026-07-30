<?php

namespace App\Services;

use App\Models\FilmRollEntry;
use App\Models\FilmStockPool;
use App\Models\FilmStockTransfer;
use App\Models\FilmStockTransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FilmStockTransferService
{
    public function transfer(array $data): FilmStockTransfer
    {
        return DB::transaction(function () use ($data) {

            $rollIds = $data['roll_entry_ids'];

            $rolls = FilmRollEntry::whereIn('id', $rollIds)
                ->lockForUpdate()
                ->get();

            if ($rolls->count() !== count($rollIds)) {
                throw ValidationException::withMessages([
                    'roll_entry_ids' => 'Sebagian roll tidak ditemukan.',
                ]);
            }

            foreach ($rolls as $roll) {
                if ((int) $roll->current_branch_id !== (int) $data['from_branch_id']) {
                    throw ValidationException::withMessages([
                        'roll_entry_ids' => "Roll {$roll->roll_code} tidak berada di cabang/gudang asal.",
                    ]);
                }

                if (!in_array($roll->status, ['in_warehouse', 'in_branch'])) {
                    throw ValidationException::withMessages([
                        'roll_entry_ids' => "Roll {$roll->roll_code} tidak bisa dikirim karena status {$roll->status}.",
                    ]);
                }
            }

            $transfer = FilmStockTransfer::create([
                'transfer_number' => $data['transfer_number'] ?? $this->generateTransferNumber(),
                'from_branch_id'  => (int) $data['from_branch_id'],
                'to_branch_id'    => (int) $data['to_branch_id'],
                'transfer_date'   => $data['transfer_date'] ?? now()->toDateString(),
                'status'          => 'sent',
                'created_by'      => auth()->id(),
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($rolls as $roll) {
                $length = (float) $roll->length_meter;

                $sourcePool = FilmStockPool::where('id', $roll->film_stock_pool_id)
                    ->lockForUpdate()
                    ->first();

                if (!$sourcePool) {
                    throw ValidationException::withMessages([
                        'roll_entry_ids' => "Stock pool asal untuk roll {$roll->roll_code} tidak ditemukan.",
                    ]);
                }

                if ((float) $sourcePool->remaining_length_meter < $length) {
                    throw ValidationException::withMessages([
                        'roll_entry_ids' => "Sisa stock pool asal tidak cukup untuk roll {$roll->roll_code}.",
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 1. Kurangi stock pool asal
                |--------------------------------------------------------------------------
                */
                $sourcePool->remaining_length_meter -= $length;
                $sourcePool->remaining_area_meter = $sourcePool->remaining_length_meter * (float) $sourcePool->width_meter;
                $sourcePool->save();

                /*
                |--------------------------------------------------------------------------
                | 2. Tambahkan stock pool tujuan
                |--------------------------------------------------------------------------
                */
                $destinationPool = FilmStockPool::firstOrCreate(
                    [
                        'branch_id' => (int) $data['to_branch_id'],

                        'category_product_id' => $sourcePool->category_product_id,
                        'product_id' => $sourcePool->product_id,

                        'category_product_building_id' => $sourcePool->category_product_building_id,
                        'product_building_id' => $sourcePool->product_building_id,

                        'usage_for' => $sourcePool->usage_for,
                    ],
                    [
                        'film_type' => $sourcePool->film_type,
                        'darkness_code' => $sourcePool->darkness_code,
                        'width_meter' => $sourcePool->width_meter,

                        'total_received_length_meter' => 0,
                        'total_used_length_meter' => 0,
                        'remaining_length_meter' => 0,
                        'remaining_area_meter' => 0,
                    ]
                );

                $destinationPool->film_type = $sourcePool->film_type;
                $destinationPool->darkness_code = $sourcePool->darkness_code;
                $destinationPool->width_meter = $sourcePool->width_meter;

                $destinationPool->total_received_length_meter += $length;
                $destinationPool->remaining_length_meter += $length;
                $destinationPool->remaining_area_meter = $destinationPool->remaining_length_meter * (float) $destinationPool->width_meter;
                $destinationPool->save();

                /*
                |--------------------------------------------------------------------------
                | 3. Simpan item transfer
                |--------------------------------------------------------------------------
                */
                FilmStockTransferItem::create([
                    'film_stock_transfer_id' => $transfer->id,
                    'film_roll_entry_id'     => $roll->id,
                    'film_stock_pool_id'     => $destinationPool->id,

                    'roll_code'     => $roll->roll_code,
                    'film_type'     => $roll->film_type,
                    'darkness_code' => $roll->darkness_code,

                    'width_meter'  => $roll->width_meter,
                    'length_meter' => $roll->length_meter,
                    'area_meter'   => $roll->area_meter,
                ]);

                /*
                |--------------------------------------------------------------------------
                | 4. Update posisi roll fisik
                |--------------------------------------------------------------------------
                */
                $roll->film_stock_pool_id = $destinationPool->id;
                $roll->branch_id = (int) $data['to_branch_id'];
                $roll->current_branch_id = (int) $data['to_branch_id'];
                $roll->status = 'in_branch';
                $roll->save();
            }

            return $transfer;
        });
    }

    protected function generateTransferNumber(): string
    {
        return 'TRF-' . now()->format('YmdHis') . '-' . rand(100, 999);
    }
}
