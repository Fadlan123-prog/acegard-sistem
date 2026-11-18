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
        $res->assertSee('Protection,', false)->assertSee('Clarity.', false)
            ->assertSee('Our Services')->assertSee('Why ACEGARD')->assertSee('Our Products')
            ->assertSee('ag-design.css', false)->assertSee('Segera hadir');

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
}
