<?php

namespace App\Support;

/**
 * Katalog kaca film mobil ACEGARD. Sumber: lembar spesifikasi produk.
 * Angka ditampilkan apa adanya; dipakai halaman Automotive Film dan simulator VLT.
 */
class FilmCatalog
{
    /** @return array<int, array<string, mixed>> */
    public static function series(): array
    {
        // fn closure memudahkan penulisan baris varian
        $v = fn ($code, $color, $vlt, $tser, $dark, $shgc) => compact('code', 'color', 'vlt', 'tser', 'dark', 'shgc') + ['ir9' => '99%', 'ir14' => '99%', 'uv' => '99,9%'];

        $series = [
        [
          'name' => 'BLACK MASTER', 'image' => 'film-black-master.webp', 'tag' => 'Penyaringan panas dengan tampilan gelap',
          'desc' => 'BLACK MASTER memakai teknologi penyaringan panas yang membantu mengurangi paparan panas matahari dan menjaga kabin tetap nyaman dalam berbagai kondisi cuaca.',
          'variants' => [
            $v('BM20', 'Charcoal Black', '25%', '67%', '40%', '0,16%'),
            $v('BM15', 'Charcoal Black', '16%', '70%', '60%', '0,1%'),
            $v('BM05', 'Charcoal Black', '6%',  '87%', '80%', '0,04%'),
          ],
        ],
        [
          'name' => 'NOTCH UV400', 'image' => 'film-notch-uv400.webp', 'tag' => 'Night Vision dan proteksi UV hingga 400 nm',
          'desc' => 'NOTCH UV400 memakai Titanium dan Nano Ceramic dengan teknologi sputter pada PET Film berkualitas tinggi. Dilengkapi Night Vision, film ini menangkal UVA, UVB, dan UVC hingga 99,9% sampai 400 nm.',
          'variants' => [
            $v('NV70', 'Sky Blue',       '68%', '53%', '20%', '0,43%'),
            $v('NV72', 'Soft Green',     '71%', '61%', '30%', '0,4%'),
            $v('NV30', 'Graphic Black',  '30%', '65%', '40%', '0,19%'),
            $v('NV20', 'Graphic Black',  '10%', '68%', '60%', '0,13%'),
            $v('NV05', 'Graphic Black',  '6%',  '89%', '80%', '0,03%'),
          ],
        ],
        [
          'name' => '4K ALPHA PRO', 'image' => 'film-4k-alpha-pro.webp', 'tag' => 'Nano Ceramic Tungsten & Tin hingga 2500 nm',
          'desc' => '4K ALPHA PRO memakai teknologi Nano Ceramic berbahan Tungsten dan Tin dengan proses sputter. Film ini menolak panas dan spektrum sinar matahari hingga 2500 nm.',
          'variants' => [
            $v('AP50', 'Black Tungsten', '51%', '60%', '30%', '0,29%'),
            $v('AP35', 'Black Tungsten', '34%', '64%', '40%', '0,21%'),
            $v('AP10', 'Black Tungsten', '18%', '82%', '60%', '0,1%'),
            $v('AP05', 'Black Tungsten', '4%',  '90%', '80%', '0,03%'),
            $v('AP02', 'Black Tungsten', '4%',  '95%', '90%', '0,01%'),
          ],
        ],
        ];

        return $series;
    }

    /** @return array<string, string> warna film => kode hex untuk titik warna */
    public static function swatch(): array
    {
        return ['Charcoal Black' => '#2B2D31', 'Sky Blue' => '#8EC5F2', 'Soft Green' => '#A9D8BC', 'Graphic Black' => '#1E2024', 'Black Tungsten' => '#15171A'];
    }

    /**
     * Semua varian, dari VLT tertinggi ke terendah. Tiap item memuat nama seri.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public static function variants()
    {
        return collect(self::series())
            ->flatMap(fn ($s) => collect($s['variants'])->map(fn ($x) => $x + ['series' => $s['name']]))
            ->sortByDesc(fn ($x) => (int) $x['vlt'])
            ->values();
    }
}
