<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendMaintenanceTest extends TestCase
{
    public function test_home_shows_maintenance_and_navbar_pages_redirect_to_warranty(): void
    {
        config(['app.frontend_maintenance' => true]);

        $this->get('/')->assertStatus(503)->assertSee('sedang dalam maintenance')->assertHeader('Retry-After');

        foreach (['/service/car', '/service/building', '/service/franchise', '/about', '/contact', '/lokasi-dealer', '/faq', '/artikel', '/ppf', '/ppf-mobil', '/kaca-film-mobil', '/kaca-film-rumah', '/promo/kaca-film-mobil'] as $url) {
            $this->get($url)->assertRedirect(route('warranty.form'));
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
