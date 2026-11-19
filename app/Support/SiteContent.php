<?php

namespace App\Support;

/** Konten bersama situs publik: testimoni, klien, jam operasional, dan media sosial. */
class SiteContent
{
    public const HOURS = 'Buka setiap hari pukul 09.00 sampai 18.00';

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
