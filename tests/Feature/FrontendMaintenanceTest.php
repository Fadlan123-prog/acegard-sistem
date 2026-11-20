<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendMaintenanceTest extends TestCase
{
    public function test_halaman_publik_menampilkan_error_maintenance_kecuali_halaman_iklan(): void
    {
        config(['app.frontend_maintenance' => true]);

        foreach (['/', '/service/car', '/service/building', '/service/franchise', '/about', '/contact', '/lokasi-dealer', '/faq', '/artikel', '/ppf', '/simulator-vlt'] as $url) {
            $this->get($url)->assertStatus(503)->assertSee('sedang dalam maintenance')->assertHeader('Retry-After');
        }

        foreach (['/kaca-film-mobil', '/kaca-film-rumah', '/ppf-mobil', '/promo/kaca-film-mobil'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('sedang dalam maintenance');
        }
    }

    public function test_login_and_warranty_stay_accessible_during_maintenance(): void
    {
        config(['app.frontend_maintenance' => true]);

        $this->get('/login')->assertOk();
        $this->get(route('warranty.form'))->assertOk();
    }

    public function test_site_is_normal_when_maintenance_is_off(): void
    {
        config(['app.frontend_maintenance' => false]);

        $this->get('/')->assertOk()->assertDontSee('sedang dalam maintenance');
    }
}
