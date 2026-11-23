<?php

namespace App\Support;

/** Konten bersama situs publik: testimoni, klien, jam operasional, dan media sosial. */
class SiteContent
{
    public const HOURS = 'Buka setiap hari pukul 09.00 sampai 18.00';

    /**
     * Reel Instagram @acegard.id: pemasangan untuk artis. Isi 'id' dengan kode setelah /reel/ pada link.
     * Judul dan nama artis diisi bila pemilik sudah mengizinkan.
     *
     * @return array<int, array{id:string,title:string}>
     */
    public static function celebrityReels(): array
    {
        return [
            ['id' => 'C-7RwF8pkxr', 'title' => 'Pemasangan untuk artis'],
            ['id' => 'DMMh6xuzF__', 'title' => 'Pemasangan untuk artis'],
            ['id' => 'DdWLa64zWMT', 'title' => 'Pemasangan untuk artis'],
            ['id' => 'DerA_R8u22C', 'title' => 'Pemasangan untuk artis'],
        ];
    }

    /**
     * Perjalanan ACEGARD dari pendirian sampai sekarang, urut dari tahun terlama.
     * Diisi dari informasi resmi pemilik. Selama kosong, bagian "Perjalanan" tidak ditampilkan di halaman About.
     *
     * Contoh isi satu baris (image dan stat opsional; gambar ada di public/assets/frontend/ag/):
     *   ['year' => '20XX', 'title' => 'Judul tonggak', 'text' => 'Uraian singkat.', 'image' => 'nama-file.webp', 'stat' => ['Cabang', '3']]
     *
     * @return array<int, array{year:string,title:string,text:string,image?:string,stat?:array{0:string,1:string}}>
     */
    public static function history(): array
    {
        return [
            [
                'year' => '2023',
                'title' => 'ACEGARD berdiri',
                'text' => 'Hendy Tanpratama mendirikan ACEGARD. Gerai pertama resmi dibuka di Tanjung Barat.',
            ],
            [
                'year' => '2023',
                'title' => 'Masuk ke kaca film gedung',
                'text' => 'Menjelang akhir tahun, ACEGARD mulai mengerjakan kaca film untuk gedung.',
            ],
            [
                'year' => '2025',
                'title' => 'Pindah ke Cipondoh, Tangerang',
                'text' => 'Di awal tahun, ACEGARD pindah dari Tanjung Barat ke lokasi baru di Cipondoh, Tangerang.',
            ],
            [
                'year' => '2025',
                'title' => 'Layanan PPF resmi dibuka',
                'text' => 'ACEGARD resmi membuka layanan PPF (Paint Protection Film) untuk perlindungan cat bodi mobil.',
                'stat' => ['mobil dan 230 gedung sudah ditangani', '4.000'],
            ],
        ];
    }

    /** @return array<int, array{0:string,1:string,2:?string,3:string}> [nama, peran, foto, kutipan] */
    public static function testimonials(): array
    {
        return [
            ['Jessica Iskandar', 'Pelanggan', asset('assets/frontend/building/img/faces/1.jpg'), 'Suka banget sama kaca film nya bikin tampilan kaca rumah lebih elegan dan ruangan jadi lebih adem'],
            ['Presdir Bank INA', 'Pelanggan korporasi', null, 'Ac jadi lebih cepat dingin dan kinerja listrik berkurang drastis, kualitas Acegard memang bukan kaleng-kaleng'],
            ['Bos Daikin', 'Pelanggan', null, 'Awalnya hanya coba-coba, ternyata adem banget ac central saya jadi lebih dingin, siang pun tidak silau'],
        ];
    }

    /** @return array<int, string> */
    public static function clients(): array
    {
        return ['BTN', 'Jasamarga', 'Kemenko Marves', 'Mercure Hotels', 'Cengkareng Heliport'];
    }

    /** @return array<int, array{0:string,1:string,2:string}> [nama, url, ikon] */
    public static function social(): array
    {
        return [
            ['Facebook', 'https://www.facebook.com/acegard', 'mdi:facebook'],
            ['Instagram', 'https://www.instagram.com/acegard.id', 'mdi:instagram'],
            ['TikTok', 'https://www.tiktok.com/@acegard', 'simple-icons:tiktok'],
            ['YouTube', 'https://www.youtube.com/@acegard', 'mdi:youtube'],
        ];
    }
}
