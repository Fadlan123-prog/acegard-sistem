<?php

namespace App\Support;

/** Konten bersama situs publik: testimoni, klien, jam operasional, dan media sosial. */
class SiteContent
{
    public const HOURS = 'Buka setiap hari pukul 09.00 sampai 18.00';

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
        return [];
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
