<?php

namespace App\Support;

class Whatsapp
{
    /** Nomor tampilan, mis. 0821 2121 2093. */
    public static function display(): string
    {
        $n = self::number();
        $local = str_starts_with($n, '62') ? '0' . substr($n, 2) : $n;

        return trim(chunk_split($local, 4, ' '));
    }

    public static function number(): string
    {
        return (string) config('acegard.whatsapp');
    }

    /** Link wa.me dengan pesan awal opsional. */
    public static function url(?string $message = null): string
    {
        $url = 'https://wa.me/' . self::number();

        return $message ? $url . '?text=' . rawurlencode($message) : $url;
    }
}
