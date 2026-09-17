<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoPopupRendersOnSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_live_campaign_reaches_a_real_page(): void
    {
        $this->setSettings('promotions', [
            'enabled' => '1',
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Claim My Free Month', false);
        $response->assertSee('westhubPromoGate', false);
    }

    public function test_a_paused_campaign_ships_no_popup_markup(): void
    {
        $this->setSettings('promotions', ['enabled' => '0']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Claim My Free Month', false);
        $response->assertDontSee('westhubPromoGate', false);
    }

    /**
     * Calendly's widget should only be pulled in when Calendly is the provider.
     */
    public function test_calendly_assets_are_only_loaded_for_the_calendly_provider(): void
    {
        $this->setSettings('appointments', ['provider' => 'google']);

        $this->get('/')->assertDontSee('assets.calendly.com', false);

        $this->setSettings('appointments', ['provider' => 'calendly']);

        $this->get('/')->assertSee('assets.calendly.com', false);
    }
}
