<?php

namespace App\Services;

use App\Models\CustomerProduct;

class CustomerProductSyncService
{
    /**
     * Sync car customer products (create/update/delete orphans).
     *
     * @param  int    $customerId
     * @param  array  $products  Each item: ['id' => nullable, 'product_id', 'category_product_id', 'part_id']
     * @return int[]  IDs that were kept/created
     */
    public function syncCarProducts(int $customerId, array $products): array
    {
        $existingIds = CustomerProduct::where('customer_id', $customerId)->pluck('id')->all();
        $keepIds     = [];

        foreach ($products as $row) {
            $rowId = $row['id'] ?? null;

            if ($rowId) {
                CustomerProduct::where('id', $rowId)
                    ->where('customer_id', $customerId)
                    ->update([
                        'product_id'          => (int) $row['product_id'],
                        'category_product_id' => (int) $row['category_product_id'],
                        'part_id'             => (int) $row['part_id'],
                    ]);
                $keepIds[] = (int) $rowId;
            } else {
                $created   = CustomerProduct::create([
                    'customer_id'         => $customerId,
                    'product_id'          => (int) $row['product_id'],
                    'category_product_id' => (int) $row['category_product_id'],
                    'part_id'             => (int) $row['part_id'],
                ]);
                $keepIds[] = $created->id;
            }
        }

        $toDelete = array_diff($existingIds, $keepIds);
        if (!empty($toDelete)) {
            CustomerProduct::whereIn('id', $toDelete)->delete();
        }

        return $keepIds;
    }
}
