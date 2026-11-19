<?php

namespace App\Support;

/**
 * Pertanyaan yang sering diajukan, satu sumber untuk halaman kategori, beranda, halaman FAQ,
 * dan data terstruktur FAQPage. Hanya memuat fakta yang sudah dikonfirmasi pemilik.
 */
class FaqCatalog
{
    /** @return array<int, array{0:string,1:string}> */
    public static function home(): array
    {
        return [
            ['Apa bedanya kaca film ACEGARD dengan kaca film biasa?', 'ACEGARD memakai teknologi Nano Ceramic Matrix Composite dengan lapisan Titanium dan Tungsten. Teknologi ini menolak panas inframerah dan sinar UV tanpa lapisan logam yang mengganggu sinyal seluler, WiFi, dan GPS.'],
            ['Apakah kaca film ACEGARD mengganggu sinyal?', 'Tidak. ACEGARD memakai lapisan non-metal yang signal friendly, jadi sinyal seluler, WiFi, dan GPS tetap berfungsi normal.'],
            ['Apa itu VLT?', 'VLT (Visible Light Transmission) adalah persentase cahaya tampak yang menembus kaca film. Semakin rendah angkanya, semakin gelap filmnya. Pilihan VLT sebaiknya disesuaikan dengan kebutuhan privasi, kenyamanan, dan aturan berkendara.'],
            ['Apakah kaca film aman untuk berkendara malam hari?', 'Lapisan Nano Ceramic ACEGARD dirancang menjaga VLT tetap optimal dan mengurangi glare lampu kendaraan lain, sehingga detail jalan tetap terlihat. Tim kami dapat membantu memilih VLT yang sesuai untuk tiap kaca.'],
            ['Di mana pemasangan dilakukan dan bagaimana garansinya?', 'Pemasangan dilakukan oleh teknisi ACEGARD di workshop kami di Cipondoh, Kota Tangerang. Setiap pemasangan mendapat garansi, dan Anda bisa memeriksanya di halaman Cek Garansi.'],
            ['Berapa harga pemasangannya?', 'Harga bergantung pada jenis kendaraan atau luas bangunan dan tipe film. Hubungi kami lewat WhatsApp untuk mendapat penawaran yang sesuai.'],
        ];
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function car(): array
    {
        return [
            ['Apa itu VLT dan TSER?', 'VLT (Visible Light Transmission) adalah persentase cahaya tampak yang menembus film, makin rendah makin gelap. TSER (Total Solar Energy Rejected) adalah persentase total energi matahari yang ditolak film, makin tinggi makin sejuk.'],
            ['Seri mana yang cocok untuk saya?', 'NOTCH UV400 cocok bila Anda mengutamakan Night Vision dan proteksi UV. 4K ALPHA PRO menawarkan penolakan panas tinggi dengan Nano Ceramic Tungsten dan Tin. BLACK MASTER cocok untuk tampilan Charcoal Black. Tim kami membantu mencocokkan seri dengan kebutuhan Anda.'],
            ['Apakah kaca film mengganggu sinyal?', 'Tidak. Lapisan Nano Ceramic ACEGARD bersifat non-metal sehingga sinyal seluler, WiFi, dan GPS tetap berfungsi.'],
            ['Apakah aman dipakai berkendara malam hari?', 'Seri NOTCH UV400 dilengkapi Night Vision. Untuk kaca depan, pilih VLT yang lebih tinggi agar visibilitas tetap baik dan sesuai aturan setempat. Konsultasikan dengan tim kami.'],
            ['Berapa harga dan di mana pemasangannya?', 'Harga bergantung pada tipe kendaraan dan varian film. Hubungi kami lewat WhatsApp untuk penawaran. Pemasangan dilakukan oleh teknisi ACEGARD di workshop kami di Cipondoh, Kota Tangerang, dan setiap pemasangan mendapat garansi.'],
        ];
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function building(): array
    {
        return [
            ['Apa itu VLT, TSER, IRR, dan UVR?', 'VLT adalah persentase cahaya tampak yang menembus film, makin rendah makin gelap. TSER adalah total energi matahari yang ditolak. IRR adalah penolakan sinar inframerah, dan UVR adalah penolakan sinar ultraviolet. SHGC adalah koefisien panas matahari yang masuk, makin kecil makin sedikit panas masuk.'],
            ['Seri mana yang cocok untuk jendela saya?', 'Untuk pandangan tetap terang, pilih varian VLT tinggi seperti Notch UV400 Sky Blue (71%) atau Soft Green (68%). Untuk privasi dan penolakan panas lebih besar, pilih varian lebih gelap seperti Black Master atau 4K Alpha Pro. Tim kami membantu mencocokkan seri dengan kebutuhan Anda.'],
            ['Berapa lama garansinya?', 'Flex One Way 3 tahun, Black Master dan Notch UV400 5 tahun, 4K Alpha Pro 7 tahun, sesuai daftar produk ACEGARD.'],
            ['Bagaimana cara menghitung harga?', 'Harga dihitung per meter persegi dan berbeda untuk tiap seri. Kirim luas atau jumlah kaca Anda lewat WhatsApp untuk mendapat penawaran.'],
            ['Apakah simulator menunjukkan hasil yang persis?', 'Simulator adalah ilustrasi untuk membandingkan tingkat kegelapan, warna, dan panas yang masuk. Hasil nyata bergantung pada jenis kaca, arah matahari, dan pencahayaan ruangan.'],
        ];
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function ppf(): array
    {
        return [
            ['Apa bedanya Instan dan Heat Self Healing?', 'Instan Self Healing memulihkan goresan ringan tanpa bantuan panas. Heat Self Healing memulihkan goresan ringan setelah film terkena panas, misalnya dari sinar matahari atau air hangat. Simulator di atas menunjukkan perbedaannya.'],
            ['Apa bedanya ARMOR, ARMOR PLUS, AEGIS, AEGIS MAX +, dan MATTE?', 'Perbedaan ada pada jenis self healing, garansi, bahan urethane, dan ketebalan film. ARMOR dan ARMOR PLUS memakai Heat Self Healing dengan garansi 5 dan 7 tahun. AEGIS dan AEGIS MAX + memakai Instan Self Healing dan urethane Lubrizol Estane USA dengan garansi 8 tahun. AEGIS MAX + juga memiliki film paling tebal dan kekuatan sobek tertinggi. MATTE memiliki spesifikasi yang sama dengan ARMOR, hanya tampilan akhirnya doff.'],
            ['Apakah PPF mengubah warna cat?', 'PPF ACEGARD berupa film bening yang menjaga tampilan asli cat. Tingkat MATTE sengaja memberi hasil doff.'],
            ['Berapa lama pemasangannya?', 'Pemasangan full body umumnya memerlukan 5 sampai 7 hari kerja. Untuk pemasangan parsial seperti kap mesin dan bumper, tanyakan estimasinya kepada tim kami.'],
            ['Berapa harganya?', 'Harga bergantung pada tipe kendaraan, tingkat PPF, dan cakupan pemasangan. Hubungi kami lewat WhatsApp untuk penawaran.'],
        ];
    }

    /** Pertanyaan umum untuk halaman FAQ: beranda ditambah informasi workshop, kontak, garansi, dan simulator. */
    public static function general(): array
    {
        return array_merge(self::home(), [
            ['Di mana workshop ACEGARD dan jam berapa buka?', 'Workshop ACEGARD berada di ' . config('acegard.address') . '. ' . SiteContent::HOURS . '.'],
            ['Bagaimana cara menghubungi ACEGARD?', 'Hubungi kami lewat WhatsApp di ' . Whatsapp::display() . ' atau email ' . config('acegard.email') . '. Tombol WhatsApp di pojok kanan bawah setiap halaman juga membawa konteks halaman yang sedang Anda lihat.'],
            ['Bagaimana memeriksa garansi pemasangan saya?', 'Buka halaman Cek Garansi di menu atas, lalu masukkan data pemasangan Anda untuk melihat status garansi.'],
            ['Apakah ada simulator untuk mencoba kaca film?', 'Ada tiga: simulator VLT untuk kaca mobil, simulator jendela untuk kaca film gedung dan rumah, serta simulator goresan untuk PPF. Hasilnya hanya ilustrasi visual.'],
        ]);
    }

    /** @return array<string, array{label:string, items: array}> kategori untuk halaman FAQ */
    public static function categories(): array
    {
        return [
            'umum' => ['label' => 'Umum', 'items' => self::general()],
            'mobil' => ['label' => 'Kaca Film Mobil', 'items' => self::car()],
            'gedung' => ['label' => 'Kaca Film Gedung', 'items' => self::building()],
            'ppf' => ['label' => 'PPF', 'items' => self::ppf()],
        ];
    }
}
