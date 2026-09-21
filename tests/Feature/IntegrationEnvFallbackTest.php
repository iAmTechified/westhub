<?php

namespace Tests\Feature;

use App\Livewire\BookAppointment;
use App\Services\Appointments\AppointmentProviderManager;
use App\Services\Google\GoogleSheets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The booking page link and the Apps Script connection can live in .env, and
 * the site uses them until someone saves a value in the admin.
 */
class IntegrationEnvFallbackTest extends TestCase
{
    use RefreshDatabase;

    private const PAGE = 'https://calendar.google.com/calendar/appointments/schedules/AcZssZ1FromEnv';
    private const SCRIPT = 'https://script.google.com/macros/s/AKfycbx_FROM-ENV/exec';

    public function test_the_booking_page_link_falls_back_to_env(): void
    {
        Mail::fake();
        config()->set('services.appointments.provider', 'google_booking_page');
        config()->set('services.google_booking_page.url', self::PAGE);

        $this->assertTrue(app(AppointmentProviderManager::class)->isConfigured());

        Livewire::test(BookAppointment::class)
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->assertSee(self::PAGE . '?gv=true', false);
    }

    public function test_a_saved_booking_page_link_wins_over_env(): void
    {
        config()->set('services.google_booking_page.url', self::PAGE);
        $this->setSettings('appointments', [
            'provider' => 'google_booking_page',
            'google_booking_page_url' => 'https://calendar.app.google/SavedInAdmin',
        ]);

        Livewire::test(BookAppointment::class)
            ->assertSet('bookingPageUrl', 'https://calendar.app.google/SavedInAdmin');
    }

    public function test_the_apps_script_connection_falls_back_to_env(): void
    {
        config()->set('services.google_sheets.method', 'apps_script');
        config()->set('services.google_sheets.apps_script_url', self::SCRIPT);
        config()->set('services.google_sheets.apps_script_secret', 'env-secret');

        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'row' => 2])]);

        $sheets = app(GoogleSheets::class);

        $this->assertTrue($sheets->usesAppsScript());
        $this->assertTrue($sheets->isEnabled());

        $sheets->appendRow('Join Requests', ['westhub_id'], ['westhub_id' => '1']);

        Http::assertSent(fn (Request $request): bool => $request->url() === self::SCRIPT
            && json_decode($request->body(), true)['secret'] === 'env-secret');
    }

    public function test_an_unreachable_google_gives_staff_a_plain_message_and_keeps_the_detail(): void
    {
        $this->setSettings('integrations', [
            'google_sheets_method' => 'apps_script',
            'google_sheets_apps_script_url' => self::SCRIPT,
            'google_sheets_apps_script_secret' => 'secret',
        ]);

        Http::fake(fn () => throw new ConnectionException('cURL error 60: SSL certificate problem: unable to get local issuer certificate for https://script.googleusercontent.com/macros/echo?user_content_key=abc'));

        $result = app(GoogleSheets::class)->testConnection();

        $this->assertFalse($result['ok']);
        $this->assertStringStartsWith('Could not reach Google Apps Script', $result['message']);
        $this->assertStringNotContainsString('cURL', $result['message']);
        $this->assertStringContainsString('cURL error 60', $result['detail']);
    }
}
