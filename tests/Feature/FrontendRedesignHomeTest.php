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
        foreach ([71, 68, 51, 34, 30, 25, 18, 16, 10, 6, 4] as $vlt) {
            $res->assertSee('value="' . $vlt . '"', false);
        }

        // dua varian berbagi VLT 4%, keduanya harus ikut terkirim ke skrip
        $res->assertSee('AP05')->assertSee('AP02')->assertSee('NV72');
    }

    public function test_katalog_film_konsisten_untuk_13_varian(): void
    {
        $variants = \App\Support\FilmCatalog::variants();

        $this->assertCount(13, $variants);
        $this->assertSame(71, (int) $variants->first()['vlt']);
        $this->assertSame(4, (int) $variants->last()['vlt']);
    }
}
