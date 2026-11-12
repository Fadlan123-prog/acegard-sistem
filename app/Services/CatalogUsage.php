<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pemakaian sebuah baris katalog produk (kategori/produk) di tabel lain. Dipakai untuk menolak penghapusan
 * agar riwayat customer, stok roll, klaim, dan penawaran tidak rusak. Memakai peta foreign key dari database
 * (lengkap, otomatis ikut bila ada tabel baru) ditambah referensi tanpa FK (SO dan rincian paket JSON).
 */
class CatalogUsage
{
    private const LABELS = [
        'customer_product'                     => 'customer mobil',
        'customer_building_product'            => 'customer gedung',
        'customer_building_technician_meters'  => 'alokasi meter teknisi gedung',
        'customer_ppf_installations'           => 'instalasi PPF',
        'film_stock_pools'                     => 'stok roll',
        'film_stock_usages'                    => 'pemakaian roll',
        'film_complaint_items'                 => 'item komplain/replacement',
        'mitra_roll_prices'                    => 'harga roll mitra',
        'mitra_invoice_items'                  => 'invoice mitra',
        'purchase_items'                       => 'item pembelian',
        'commissions'                          => 'komisi',
        'sales_orders'                         => 'sales order',
        'sales_order_items'                    => 'item sales order',
        'quotation_items'                      => 'item quotation',
    ];

    /**
     * @param  array<int,string>  $ignoreTables  tabel yang diabaikan (mis. produk milik kategori itu sendiri)
     * @return array<string,int>  label => jumlah baris yang memakai
     */
    public function usage(string $table, int $id, array $ignoreTables = []): array
    {
        $result = [];

        foreach ($this->referencingColumns($table) as [$refTable, $refColumn]) {
            if (in_array($refTable, $ignoreTables, true)) {
                continue;
            }

            $count = DB::table($refTable)->where($refColumn, $id)->count();
            if ($count > 0) {
                $label = self::LABELS[$refTable] ?? $refTable;
                $result[$label] = ($result[$label] ?? 0) + $count;
            }
        }

        return $result;
    }

    /** Referensi tanpa FK: SO (film_category_id/film_product_id) dan rincian paket JSON pada quotation/SO. */
    public function extraUsage(string $kind, string $scope, int $id): array
    {
        $result = [];

        // $scope: 'car' | 'building'; $kind: 'category' | 'product'
        $soColumn = $kind === 'category' ? 'film_category_id' : 'film_product_id';
        if (Schema::hasColumn('sales_orders', $soColumn)) {
            $soCategory = $scope === 'car' ? 'car_film' : 'building_film';
            $n = DB::table('sales_orders')->where('category', $soCategory)->where($soColumn, $id)->count();
            if ($n > 0) {
                $result[self::LABELS['sales_orders']] = $n;
            }
        }

        if ($scope === 'car') {
            $jsonKey = $kind === 'category' ? 'category_id' : 'product_id';
            foreach (['quotation_items', 'sales_order_items'] as $t) {
                if (! Schema::hasColumn($t, 'film_lines')) {
                    continue;
                }
                $n = DB::table($t)->whereRaw('JSON_CONTAINS(film_lines, JSON_ARRAY(JSON_OBJECT(?, ?)))', [$jsonKey, $id])->count();
                if ($n > 0) {
                    $result[self::LABELS[$t]] = $n;
                }
            }
        }

        return $result;
    }

    /** @return array<int,array{0:string,1:string}> [tabel, kolom] yang punya foreign key ke $table */
    private function referencingColumns(string $table): array
    {
        $rows = DB::select(
            'SELECT TABLE_NAME AS t, COLUMN_NAME AS c FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = ?',
            [$table]
        );

        return array_map(fn ($r) => [$r->t, $r->c], $rows);
    }

    /** "3 customer mobil, 2 stok roll" */
    public static function describe(array $usage): string
    {
        return collect($usage)->map(fn ($n, $label) => "$n $label")->implode(', ');
    }
}
