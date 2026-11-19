<?php

namespace App\Support;

/**
 * Katalog kaca film mobil ACEGARD. Sumber: lembar spesifikasi produk.
 * Angka mengikuti daftar produk arsitektural (gedung) sebagai acuan tunggal; dipakai halaman Automotive Film dan simulator VLT.
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
            $v('BM20', 'Charcoal Black', '25%', '67%', '40%', '0,143'),
            $v('BM15', 'Charcoal Black', '15%', '70%', '60%', '0,093'),
            $v('BM05', 'Charcoal Black', '6%',  '87%', '80%', '0,000'),
          ],
        ],
        [
          'name' => 'NOTCH UV400', 'image' => 'film-notch-uv400.webp', 'tag' => 'Night Vision dan proteksi UV hingga 400 nm',
          'desc' => 'NOTCH UV400 memakai Titanium dan Nano Ceramic dengan teknologi sputter pada PET Film berkualitas tinggi. Dilengkapi Night Vision, film ini menangkal UVA, UVB, dan UVC hingga 99,9% sampai 400 nm.',
          'variants' => [
            $v('NV70', 'Sky Blue',       '71%', '53%', '20%', '0,373'),
            $v('NV72', 'Soft Green',     '68%', '61%', '30%', '0,422'),
            $v('NV40', 'Graphic Black',  '32%', '65%', '40%', '0,204'),
            $v('NV20', 'Graphic Black',  '22%', '68%', '60%', '0,141'),
            $v('NV05', 'Graphic Black',  '6%',  '89%', '80%', '0,000'),
          ],
        ],
        [
          'name' => '4K ALPHA PRO', 'image' => 'film-4k-alpha-pro.webp', 'tag' => 'Nano Ceramic Tungsten & Tin hingga 2500 nm',
          'desc' => '4K ALPHA PRO memakai teknologi Nano Ceramic berbahan Tungsten dan Tin dengan proses sputter. Film ini menolak panas dan spektrum sinar matahari hingga 2500 nm.',
          'variants' => [
            $v('AP50', 'Black Tungsten', '49%', '60%', '30%', '0,276'),
            $v('AP35', 'Black Tungsten', '34%', '64%', '40%', '0,181'),
            $v('AP10', 'Black Tungsten', '11%', '82%', '60%', '0,071'),
            $v('AP05', 'Black Tungsten', '5%',  '90%', '80%', '0,000'),
            $v('AP02', 'Black Tungsten', '3%',  '95%', '90%', '0,000'),
          ],
        ],
        ];

        return $series;
    }

    /**
     * Kaca film arsitektural (gedung, kantor, rumah). Sumber: daftar produk window film architectural ACEGARD.
     * Harga sengaja tidak dimuat. "dark" adalah kolom % pada lembar (tingkat kegelapan).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function architectural(): array
    {
        // [label, dark, tser, irr, uvr, vlt, shgc, warna overlay, reflektif?]
        $row = fn ($label, $dark, $tser, $irr, $uvr, $vlt, $shgc, $tone, $sheen = false) =>
            compact('label', 'dark', 'tser', 'irr', 'uvr', 'vlt', 'shgc', 'tone', 'sheen');

        return [
            [
                'name' => 'FLEX ONE WAY', 'image' => 'film-flex-one-way.webp', 'warranty' => '3 tahun',
                'tag' => 'Privasi dengan tampilan satu arah',
                'desc' => 'Film reflektif satu arah untuk jendela yang membutuhkan privasi. Tersedia dalam enam varian Silver dengan tingkat kegelapan 40% sampai 80%.',
                'variants' => [
                    $row('Black Silver', '60%', '65%', '74%', '99,9%', '14%', '0,213', '#3A3E45', true),
                    $row('Black Silver', '80%', '77%', '90%', '99,9%', '3%',  '0,085', '#2B2E34', true),
                    $row('Dark Gray Silver', '60%', '64%', '70%', '89,5%', '15%', '0,181', '#5A5E66', true),
                    $row('Light Gray Silver', '40%', '55%', '54%', '70,1%', '36%', '0,411', '#A5ABB3', true),
                    $row('Double Silver', '60%', '68%', '92%', '99,9%', '17%', '0,160', '#8C939C', true),
                    $row('Double Silver', '80%', '84%', '99%', '99,9%', '6%',  '0,062', '#7A818B', true),
                ],
            ],
            [
                'name' => 'BLACK MASTER', 'image' => 'film-black-master.webp', 'warranty' => '5 tahun',
                'tag' => 'Penyaringan panas dengan tampilan gelap',
                'desc' => 'BLACK MASTER memakai teknologi penyaringan panas yang membantu mengurangi paparan panas matahari dan menjaga ruangan tetap nyaman, dengan tampilan Charcoal Black yang tegas.',
                'variants' => [
                    $row('BM20', '40%', '67%', '99%', '99,9%', '25%', '0,143', '#1B1D21'),
                    $row('BM15', '60%', '70%', '99%', '99,9%', '15%', '0,093', '#1B1D21'),
                    $row('BM05', '80%', '87%', '99%', '99,9%', '6%',  '0,000', '#1B1D21'),
                ],
            ],
            [
                'name' => 'NOTCH UV400', 'image' => 'film-notch-uv400.webp', 'warranty' => '5 tahun',
                'tag' => 'Proteksi UV hingga 400 nm',
                'desc' => 'NOTCH UV400 memakai Titanium dan Nano Ceramic dengan teknologi sputter pada PET Film berkualitas tinggi. Film ini menangkal UVA, UVB, dan UVC hingga 99,9% sampai 400 nm.',
                'variants' => [
                    $row('Sky Blue', '20%', '53%', '99%', '99,9%', '71%', '0,373', '#7DB8F0'),
                    $row('Soft Green', '30%', '61%', '99%', '99,9%', '68%', '0,422', '#9FD3B4'),
                    $row('NV40', '40%', '65%', '99%', '99,9%', '32%', '0,204', '#1E2024'),
                    $row('NV20', '60%', '68%', '99%', '99,9%', '22%', '0,141', '#1E2024'),
                    $row('NV05', '80%', '89%', '99%', '99,9%', '6%',  '0,000', '#1E2024'),
                ],
            ],
            [
                'name' => '4K ALPHA PRO', 'image' => 'film-4k-alpha-pro.webp', 'warranty' => '7 tahun',
                'tag' => 'Nano Ceramic Tungsten & Tin hingga 2500 nm',
                'desc' => '4K ALPHA PRO memakai teknologi Nano Ceramic berbahan Tungsten dan Tin dengan proses sputter. Film ini menolak panas dan spektrum sinar matahari hingga 2500 nm.',
                'variants' => [
                    $row('AP50', '30%', '60%', '99%', '99,9%', '49%', '0,276', '#15171A'),
                    $row('AP35', '40%', '64%', '99%', '99,9%', '34%', '0,181', '#15171A'),
                    $row('AP10', '60%', '82%', '99%', '99,9%', '11%', '0,071', '#15171A'),
                    $row('AP05', '80%', '90%', '99%', '99,9%', '5%',  '0,000', '#15171A'),
                    $row('AP02', '90%', '95%', '99%', '99,9%', '3%',  '0,000', '#15171A'),
                ],
            ],
        ];
    }

    /**
     * Tingkat Paint Protection Film. Sumber: lembar Specification dan Features ACEGARD PPF.
     * Harga sengaja tidak dimuat. "heal" bernilai instant atau heat (perilaku di simulator goresan).
     * MATTE tidak ada di lembar spesifikasi, jadi "spec" dan "rate" bernilai null.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function ppf(): array
    {
        $t = fn ($name, $heal, $warranty, $urethane, $mils, $best, $durability = null, $spec = null, $rate = null, $finish = 'Glossy') =>
            compact('name', 'finish', 'heal', 'warranty', 'urethane', 'mils', 'best', 'durability', 'spec', 'rate');

        // [urethane (mils), clear coating, acrylic adhesive, elongation, peel adhesion, tear strength, made in]
        $sp = fn (...$v) => array_combine(['urethane_mils', 'clear', 'adhesive', 'elongation', 'peel', 'tear', 'made'], $v);
        // [hydrophobic, UV protection, self healing, scratch resistance, flexibility, clarity] skala 1-5
        $rt = fn (...$v) => array_combine(['hydrophobic', 'uv', 'healing', 'scratch', 'flexibility', 'clarity'], $v);

        return [
            $t('ARMOR',       'heat',    '5 tahun', 'Lubrizol Mix Grade',  '7,5 mils', 'Perlindungan harian',   '6 tahun',  $sp('6,5 mils', '0,5 mils', '1,3 mils', '>280%', '680 n/m', '3800 psi', 'Korea'), $rt(3, 5, 3, 3, 4, 5)),
            $t('ARMOR PLUS',  'heat',    '7 tahun', 'Lubrizol Mix Grade',  '8,5 mils', 'Perlindungan harian',   '8 tahun',  $sp('7,5 mils', '0,5 mils', '1,4 mils', '>400%', '720 n/m', '4200 psi', 'Korea'), $rt(4, 5, 4, 4, 4, 4)),
            $t('AEGIS',       'instant', '8 tahun', 'Lubrizol Estane USA', '7,5 mils', 'Perlindungan maksimal', '10 tahun', $sp('6,5 mils', '0,7 mils', '1,5 mils', '>380%', '710 n/m', '4000 psi', 'China'), $rt(5, 5, 5, 5, 5, 5)),
            $t('AEGIS MAX +', 'instant', '8 tahun', 'Lubrizol Estane USA', '8,5 mils', 'Perlindungan maksimal', '10 tahun', $sp('7,5 mils', '0,8 mils', '1,5 mils', '>490%', '830 n/m', '5500 psi', 'China'), $rt(5, 5, 5, 5, 5, 5)),
            $t('MATTE',       'heat',    '5 tahun', 'Lubrizol Mix Grade',  '7,5 mils', 'Perlindungan harian',   null, null, null, 'Matte'),
        ];
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
