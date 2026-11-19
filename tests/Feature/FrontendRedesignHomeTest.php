<?php

namespace Tests\Feature;

use App\Support\Whatsapp;
use Tests\TestCase;

class FrontendRedesignHomeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Saklar maintenance aktif secara default; uji tampilan redesign dengan situs terbuka.
        config(['app.frontend_maintenance' => false]);
    }

    public function test_home_renders_redesign_sections_with_configured_whatsapp(): void
    {
        config(['acegard.whatsapp' => '6281234567890']);

        $res = $this->get('/')->assertOk();
        $res->assertSee('Kaca film premium', false)->assertSee('Cooler Cabins, Clearer Views', false)
            ->assertSee('Our Services')->assertSee('Why ACEGARD')->assertSee('Our Products')
            ->assertSee('ag-design.css', false)->assertDontSee('Segera hadir');

        $html = $res->getContent();
        $this->assertStringContainsString('https://wa.me/6281234567890', $html);
        $this->assertStringNotContainsString('628212003331', $html);
        $this->assertStringNotContainsString('consectetur adipiscing', $html);
        // APP_URL tes memang localhost, jadi periksa sumber template: tidak boleh ada URL localhost yang di-hardcode.
        foreach (['home/index', 'partials/navbar', 'partials/footer', 'partials/wa-float'] as $v) {
            $this->assertStringNotContainsString('localhost', file_get_contents(resource_path("views/frontend/$v.blade.php")), $v);
        }
    }

    public function test_navbar_has_six_main_links_and_footer_is_present_on_other_pages(): void
    {
        foreach (['/', '/about', '/contact'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            foreach (['Home', 'Automotive Film', 'Building Film', 'PPF', 'About Us', 'Contact'] as $label) {
                $this->assertStringContainsString('>' . $label . '</a>', $html, "$url $label");
            }
            $this->assertStringContainsString('ag-footer', $html);
            $this->assertStringContainsString('ag-wa', $html);
        }
    }

    public function test_whatsapp_helper_formats_number_and_message(): void
    {
        config(['acegard.whatsapp' => '6282121212093']);

        $this->assertSame('https://wa.me/6282121212093', Whatsapp::url());
        $this->assertSame('https://wa.me/6282121212093?text=Halo%20ACEGARD', Whatsapp::url('Halo ACEGARD'));
        $this->assertSame('0821 2121 2093', Whatsapp::display());
    }

    public function test_landing_pages_keep_their_own_layout_without_new_navbar(): void
    {
        $html = $this->get('/kaca-film-rumah')->assertOk()->getContent();

        $this->assertStringNotContainsString('ag-nav', $html);
        $this->assertStringNotContainsString('class="ag-wa', $html);
    }

    public function test_home_menampilkan_konten_lengkap_acegard(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('About ACEGARD')
            ->assertSee('Nano Ceramic Matrix Composite')
            ->assertSeeText('Teknologi di balik kaca film ACEGARD')
            ->assertSeeText('Dari konsultasi sampai garansi')
            ->assertSee('Mercure Hotels')
            ->assertSeeText('Pertanyaan yang sering diajukan')
            ->assertSeeText('Kunjungi workshop kami')
            ->assertSee(config('acegard.address'));
    }

    public function test_halaman_kaca_film_mobil_menampilkan_spesifikasi_dan_iklan_tidak_berubah(): void
    {
        $this->get(route('frontend.service.car'))->assertOk()
            ->assertSee('ag-nav', false)
            ->assertSee('BM20')->assertSee('NV72')->assertSee('AP02')
            ->assertSee('Charcoal Black')->assertSee('Black Tungsten')
            ->assertSee('99,9%');

        // Flex One Way khusus gedung
        $this->get(route('frontend.service.car'))->assertDontSee('FLEX ONE WAY');
        $this->get('/')->assertDontSee('FLEX ONE WAY');

        $this->get('/kaca-film-mobil')->assertOk()->assertDontSee('ag-nav', false);
        $this->get('/promo/kaca-film-mobil')->assertOk()->assertDontSee('ag-nav', false);
    }

    public function test_simulator_vlt_menampilkan_pilihan_vlt_dan_varian(): void
    {
        $res = $this->get(route('frontend.simulator'))->assertOk()
            ->assertSee('ag-nav', false)
            ->assertSeeText('Coba kegelapan kaca film')
            ->assertSee('agSim', false)
            ->assertSee('Tanpa film');

        // semua VLT unik dari lembar spesifikasi tersedia sebagai pilihan
        foreach ([71, 68, 49, 34, 32, 25, 22, 15, 11, 6, 5, 3] as $vlt) {
            $res->assertSee('value="' . $vlt . '"', false);
        }

        // varian dengan VLT 6% (BM05 dan NV05) berbagi satu pilihan, keduanya harus ikut terkirim ke skrip
        $res->assertSee('BM05')->assertSee('NV05')->assertSee('AP02')->assertSee('NV70');
    }

    public function test_katalog_film_konsisten_untuk_13_varian(): void
    {
        $variants = \App\Support\FilmCatalog::variants();

        $this->assertCount(13, $variants);
        $this->assertSame(71, (int) $variants->first()['vlt']);
        $this->assertSame(3, (int) $variants->last()['vlt']);
    }

    public function test_halaman_gedung_menampilkan_19_varian_dan_simulator_jendela(): void
    {
        $this->assertSame(19, collect(\App\Support\FilmCatalog::architectural())->sum(fn ($s) => count($s['variants'])));

        $res = $this->get(route('frontend.service.building'))->assertOk()
            ->assertSee('ag-nav', false)
            ->assertSee('agWin', false)
            ->assertSeeText('Spesifikasi 19 varian')
            ->assertSee('Black Silver')->assertSee('Double Silver')->assertSee('BM20')
            ->assertSee('Sky Blue')->assertSee('Soft Green')->assertSee('AP02')
            ->assertSee('0,411')->assertSee('89,5%');

        // harga tidak ditampilkan di situs
        $this->assertStringNotContainsString('Rp.', $res->getContent());
        $this->assertStringNotContainsString('230.000', $res->getContent());
    }

    public function test_halaman_ppf_menampilkan_lima_tingkat_dan_simulator_goresan(): void
    {
        $res = $this->get(route('frontend.ppf.catalog'))->assertOk()
            ->assertSee('ag-nav', false)
            ->assertSee('agPpf', false)
            ->assertSee('ARMOR PLUS')->assertSee('AEGIS MAX +')->assertSee('MATTE')
            ->assertSee('Lubrizol Estane USA')->assertSee('8,5 mils')->assertSee('5500 psi')->assertSee('830 n/m')->assertSee('5 sampai 7 hari kerja')
            ->assertSee('Instan Self Healing');

        // harga tidak ditampilkan; klaim garansi 6-8 tahun dari poster lama tidak dipakai
        $this->assertStringNotContainsString('Rp.', $res->getContent());
        $this->assertStringNotContainsString('Rp ', $res->getContent());
        $this->assertStringNotContainsString('6 - 8 Tahun', $res->getContent());
        $tiers = collect(\App\Support\FilmCatalog::ppf())->keyBy('name');
        $this->assertCount(5, $tiers);
        // MATTE sama dengan ARMOR kecuali finish
        $this->assertSame(
            collect($tiers['ARMOR'])->except(['name', 'finish'])->all(),
            collect($tiers['MATTE'])->except(['name', 'finish'])->all()
        );
        $this->assertSame('Matte', $tiers['MATTE']['finish']);
    }

    public function test_perbandingan_produk_tersedia_di_tiap_kategori(): void
    {
        foreach (['car' => route('frontend.service.car'), 'building' => route('frontend.service.building'), 'ppf' => route('frontend.ppf.catalog')] as $key => $url) {
            $this->get($url)->assertOk()->assertSee('agCmp-' . $key, false)->assertSeeText('Bandingkan');
        }

        $this->assertCount(13, \App\Support\FilmCatalog::compareCar()['items']);
        $this->assertCount(19, \App\Support\FilmCatalog::compareBuilding()['items']);
        $this->assertCount(5, \App\Support\FilmCatalog::comparePpf()['items']);

        // angka diambil dari teks spesifikasi
        $this->assertSame(280.0, \App\Support\FilmCatalog::num('>280%'));
        $this->assertSame(0.143, \App\Support\FilmCatalog::num('0,143'));
        $this->assertSame(5.0, \App\Support\FilmCatalog::num('5 tahun'));
        $this->assertNull(\App\Support\FilmCatalog::num('Transparan'));

        // semua id bawaan ada di daftar
        foreach (['compareCar', 'compareBuilding', 'comparePpf'] as $m) {
            $d = \App\Support\FilmCatalog::$m();
            $ids = array_column($d['items'], 'id');
            foreach ($d['defaults'] as $def) {
                $this->assertContains($def, $ids);
            }
        }
    }

    public function test_panel_konsultasi_whatsapp_membawa_konteks_halaman(): void
    {
        config(['acegard.whatsapp' => '6281234567890']);

        $cases = [
            route('frontend.home') => ['Beranda', ''],
            route('frontend.service.car') => ['Kaca Film Mobil', 'car'],
            route('frontend.service.building') => ['Kaca Film Gedung', 'building'],
            route('frontend.ppf.catalog') => ['PPF', 'ppf'],
        ];
        foreach ($cases as $url => [$page, $cat]) {
            $this->get($url)->assertOk()
                ->assertSee('id="agWaPanel"', false)
                ->assertSee('data-page="' . $page . '"', false)
                ->assertSee('data-cat="' . $cat . '"', false)
                ->assertSee('id="agWaCity"', false)
                ->assertSee('https://wa.me/6281234567890', false);
        }

        // halaman iklan tidak ikut memakai panel
        $this->get('/kaca-film-mobil')->assertOk()->assertDontSee('agWaPanel', false);
        $this->get('/promo/kaca-film-mobil')->assertOk()->assertDontSee('agWaPanel', false);
    }

    public function test_seo_meta_canonical_dan_data_terstruktur(): void
    {
        foreach ([route('frontend.home'), route('frontend.service.car'), route('frontend.simulator'), route('frontend.service.building'), route('frontend.ppf.catalog'), route('frontend.about'), route('frontend.contact'), route('frontend.faq')] as $url) {
            $res = $this->get($url)->assertOk()
                ->assertSee('<link rel="canonical" href="' . $url . '">', false)
                ->assertSee('property="og:site_name"', false)
                ->assertSee('name="twitter:card"', false);

            preg_match('/<meta name="description" content="([^"]*)"/', $res->getContent(), $m);
            $this->assertArrayHasKey(1, $m, $url . ' tanpa meta description');
            $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($m[1])), $url . ' deskripsi terlalu panjang');
        }

        // JSON-LD: FAQPage dan BreadcrumbList valid dan memuat pertanyaan halaman
        foreach ([route('frontend.service.car'), route('frontend.service.building'), route('frontend.ppf.catalog'), route('frontend.home')] as $url) {
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->getContent(), $blocks);
            $types = [];
            foreach ($blocks[1] as $json) {
                $data = json_decode($json, true);
                $this->assertNotNull($data, 'JSON-LD tidak valid di ' . $url);
                $types[] = $data['@type'] ?? 'graph';
            }
            $this->assertContains('FAQPage', $types, $url);
        }

        $home = $this->get(route('frontend.home'))->getContent();
        $this->assertStringContainsString('LocalBusiness', $home);
        $this->assertStringNotContainsString('aggregateRating', $home);
        $this->assertStringNotContainsString('"price"', $home);
    }

    public function test_sitemap_dan_robots(): void
    {
        $xml = $this->get(route('frontend.sitemap'))->assertOk()->getContent();
        foreach (['/service/car', '/service/building', '/ppf', '/simulator-vlt', '/faq'] as $path) {
            $this->assertStringContainsString($path . '</loc>', $xml);
        }
        // halaman iklan tidak masuk sitemap
        $this->assertStringNotContainsString('/kaca-film-mobil', $xml);
        $this->assertStringNotContainsString('/promo/', $xml);
        $this->assertStringNotContainsString('/ppf-mobil', $xml);

        $this->assertStringContainsString('Sitemap: https://acegard.id/sitemap.xml', file_get_contents(public_path('robots.txt')));
    }

    public function test_artikel_punya_deskripsi_dan_masuk_sitemap(): void
    {
        $a = \App\Models\Article::create([
            'title' => 'Tips merawat kaca film', 'slug' => 'tips-merawat-kaca-film', 'category' => 'Tips',
            'excerpt' => null, 'body' => '<p>Bersihkan dengan kain mikrofiber dan cairan tanpa amonia.</p>',
            'is_published' => true, 'published_at' => now(),
        ]);

        $this->get(route('frontend.artikel.show', $a))->assertOk()
            ->assertSee('<meta name="description" content="Bersihkan dengan kain mikrofiber', false);
        $this->assertStringContainsString('tips-merawat-kaca-film</loc>', $this->get(route('frontend.sitemap'))->getContent());

        $a->delete();
    }

    public function test_halaman_lama_didesain_ulang_tanpa_teks_placeholder(): void
    {
        config(['acegard.whatsapp' => '6281234567890']);

        foreach ([route('frontend.about'), route('frontend.contact'), route('frontend.faq'), route('frontend.artikel')] as $url) {
            $res = $this->get($url)->assertOk()->assertSee('ag-nav', false);
            $html = $res->getContent();

            $this->assertSame(1, preg_match_all('/<h1\b/', $html), $url . ' harus punya tepat satu h1');
            $this->assertStringNotContainsString('Lorem', $html, $url);
            $this->assertStringNotContainsString('Lorem ipsum', $html);
            $this->assertStringNotContainsString('index.html', $html);
            $this->assertStringNotContainsString('class="ace-', $html, $url . ' masih memakai komponen lama');
            $this->assertStringNotContainsString('Massage*', $html);
        }

        // About memakai angka lembar spesifikasi, bukan klaim lama
        $about = $this->get(route('frontend.about'))->getContent();
        $this->assertStringNotContainsString('88%', $about);
        $this->assertStringNotContainsString('99,9% dengan TSER', $about);

        // Contact: nomor dari konfigurasi dan formulir yang membuka WhatsApp
        $this->get(route('frontend.contact'))
            ->assertSee('id="agContactForm"', false)
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('0812 3456 7890')
            ->assertSee(config('acegard.email'));
    }

    public function test_faq_berisi_empat_kategori_nyata_dan_data_terstruktur(): void
    {
        $cats = \App\Support\FaqCatalog::categories();
        $this->assertSame(['umum', 'mobil', 'gedung', 'ppf'], array_keys($cats));

        $res = $this->get(route('frontend.faq'))->assertOk()->assertSee('id="agFaqSearch"', false);
        foreach ($cats as $cat) {
            foreach ($cat['items'] as [$q, $a]) {
                $res->assertSee(e($q), false);
            }
        }

        // halaman kategori memakai sumber yang sama
        $this->get(route('frontend.service.car'))->assertSee(e(\App\Support\FaqCatalog::car()[0][0]), false);

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $res->getContent(), $b);
        $faq = collect($b[1])->map(fn ($j) => json_decode($j, true))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertGreaterThanOrEqual(15, count($faq['mainEntity']));
    }

    public function test_skrip_reveal_ada_sekali_di_setiap_halaman_yang_memakainya(): void
    {
        // Elemen .ag-reveal tersembunyi sampai skrip menampilkannya. Tanpa skrip, halaman tampak kosong.
        foreach ([route('frontend.home'), route('frontend.service.car'), route('frontend.simulator'), route('frontend.service.building'),
                  route('frontend.ppf.catalog'), route('frontend.about'), route('frontend.contact'), route('frontend.faq')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            if (str_contains($html, 'ag-reveal')) {
                $this->assertSame(1, substr_count($html, 'new IntersectionObserver'), $url . ' harus memuat skrip reveal tepat satu kali');
                $this->assertStringContainsString('setTimeout(function () { els.forEach(show); }, 3000);', $html, $url . ' tanpa pengaman');
            }
        }
    }

    public function test_animasi_scroll_terpasang_dan_konten_tetap_ada_tanpa_js(): void
    {
        $home = $this->get(route('frontend.home'))->assertOk()->getContent();

        // pustaka dengan SRI, berkas sendiri, dan penjaga kilatan (dipasang JS, bukan server)
        $this->assertStringContainsString('gsap/3.12.5/gsap.min.js', $home);
        $this->assertStringContainsString('gsap/3.12.5/ScrollTrigger.min.js', $home);
        $this->assertSame(2, substr_count($home, 'integrity="sha384-'));
        $this->assertStringContainsString('assets/frontend/js/ag-motion.js', $home);
        $this->assertStringContainsString('assets/frontend/css/ag-motion.css', $home);
        $this->assertStringContainsString("classList.add('ag-m-wait')", $home);
        $this->assertStringNotContainsString('<html lang="id" class="ag-m-wait"', $home);

        // pernyataan, layanan sticky (3 item + 3 visual), expertise horizontal (3 panel), dari katalog
        $this->assertSame(3, substr_count($home, 'data-statement'));
        $this->assertStringContainsString('data-ps', $home);
        $this->assertSame(3, substr_count($home, 'class="ag-ps-item"'));
        $this->assertSame(3, substr_count($home, 'class="ag-ps-img"'));
        $this->assertStringContainsString('data-hs', $home);
        $this->assertSame(3, substr_count($home, 'class="ag-hs-panel"'));
        $this->assertStringContainsString('13 varian', $home);
        $this->assertStringContainsString('19 varian', $home);
        $this->assertStringContainsString('5 tingkat', $home);

        // konten layanan tetap ada di HTML (tanpa JS): teks dan tautan
        foreach (['Automotive', 'Building', 'Paint', route('frontend.service.car'), route('frontend.service.building'), route('frontend.ppf.catalog')] as $needle) {
            $this->assertStringContainsString($needle, $home);
        }

        // tombol matikan animasi di footer
        $this->assertStringContainsString('data-motion-toggle', $home);

        // cerita PPF: 3 tahap, satu ilustrasi di panggung dan satu per tahap
        $ppf = $this->get(route('frontend.ppf.catalog'))->getContent();
        $this->assertStringContainsString('data-story', $ppf);
        $this->assertSame(3, substr_count($ppf, 'class="ag-story-item"'));
        $this->assertSame(4, substr_count($ppf, 'class="ag-story-art"'));
        foreach (['Original', 'Protection', 'Final'] as $w) {
            $this->assertStringContainsString($w, $ppf);
        }

        // simulator mobil: pembatas sebelum/sesudah
        $sim = $this->get(route('frontend.simulator'))->getContent();
        $this->assertStringContainsString('id="agSimRange"', $sim);
        $this->assertStringContainsString('ag-sim-tint', $sim);
        $this->assertStringContainsString('id="agSimCar"', $sim);

        // halaman iklan tidak memuat animasi baru
        foreach (['/kaca-film-mobil', '/promo/kaca-film-mobil'] as $url) {
            $ad = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('ag-motion', $ad, $url);
            $this->assertStringNotContainsString('gsap', $ad, $url);
        }
    }

    public function test_animasi_scrub_hero_memakai_nilai_awal_eksplisit(): void
    {
        // Regresi: scrub hero dengan gsap.to() membaca opacity 0 dari animasi pembuka, sehingga hero kosong
        // setelah pengunjung menggulir kembali ke atas. Harus fromTo dengan immediateRender:false.
        $js = file_get_contents(public_path('assets/frontend/js/ag-motion.js'));
        $this->assertStringContainsString('gsap.fromTo(texts', $js);
        $this->assertStringContainsString('immediateRender: false', $js);
        $this->assertStringNotContainsString('gsap.to(container.children', $js);
    }
}
