<?php

namespace App\Support;

/**
 * Data terstruktur (JSON-LD) untuk halaman publik. Hanya memuat fakta yang memang tampil di situs:
 * tidak ada harga, rating, atau koordinat yang tidak dimiliki.
 */
class Seo
{
    public static function script(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /** @param array<int, array{0:string,1:string}> $pairs pasangan [pertanyaan, jawaban] */
    public static function faq(array $pairs): string
    {
        return self::script([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($p) => [
                '@type' => 'Question',
                'name' => $p[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $p[1]],
            ], $pairs),
        ]);
    }

    /** @param array<int, array{0:string,1:string}> $crumbs pasangan [nama, url] */
    public static function breadcrumbs(array $crumbs): string
    {
        return self::script([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($c, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1],
            ], $crumbs, array_keys($crumbs)),
        ]);
    }

    /** Artikel blog. Penulis dan penerbit adalah ACEGARD; tidak ada data pribadi. */
    public static function article(\App\Models\Article $article): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'inLanguage' => 'id-ID',
            'mainEntityOfPage' => route('frontend.artikel.show', $article),
            'author' => ['@type' => 'Organization', 'name' => 'ACEGARD Indonesia'],
            'publisher' => ['@type' => 'Organization', 'name' => 'ACEGARD Indonesia', 'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/frontend/ag/logo-white.webp')]],
        ];
        if ($article->published_at) {
            $data['datePublished'] = $article->published_at->toIso8601String();
        }
        if ($article->updated_at) {
            $data['dateModified'] = $article->updated_at->toIso8601String();
        }
        if ($article->cover_image) {
            $data['image'] = asset('storage/' . $article->cover_image);
        }

        return self::script($data);
    }

    /** Organisasi dan situs, dipakai di beranda. */
    public static function organization(): string
    {
        $url = url('/');

        return self::script([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'LocalBusiness',
                    '@id' => $url . '#organization',
                    'name' => 'ACEGARD Indonesia',
                    'url' => $url,
                    'logo' => asset('assets/frontend/ag/logo-white.webp'),
                    'image' => asset('assets/frontend/ag/hero-car.webp'),
                    'description' => 'Kaca film mobil, kaca film gedung, dan Paint Protection Film (PPF) dengan pemasangan oleh teknisi ACEGARD di Tangerang.',
                    'telephone' => '+' . Whatsapp::number(),
                    'email' => config('acegard.email'),
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => config('acegard.address'),
                        'addressLocality' => 'Kota Tangerang',
                        'addressRegion' => 'Banten',
                        'addressCountry' => 'ID',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $url . '#website',
                    'url' => $url,
                    'name' => 'ACEGARD Indonesia',
                    'inLanguage' => 'id-ID',
                    'publisher' => ['@id' => $url . '#organization'],
                ],
            ],
        ]);
    }
}
