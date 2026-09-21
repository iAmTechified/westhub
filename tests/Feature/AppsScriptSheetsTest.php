<?php

namespace Tests\Feature;

use App\Services\Google\GoogleSheets;
use App\Support\Sheets\PromoClaimSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * The Apps Script route into Sheets: same GoogleSheets API for the jobs, but
 * every write goes to the web app bound to the spreadsheet instead of the
 * Sheets API, and no service account is involved.
 */
class AppsScriptSheetsTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://script.google.com/macros/s/AKfycbx_TEST-deployment_1/exec';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setSettings('integrations', [
            'google_sheets_method' => 'apps_script',
            'google_sheets_apps_script_url' => self::URL,
            'google_sheets_apps_script_secret' => 'shared-secret-123',
        ]);
    }

    public function test_rows_are_appended_through_the_web_app_with_the_secret(): void
    {
        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'row' => 2])]);

        app(GoogleSheets::class)->appendRow('Promo Claims', PromoClaimSheet::HEADERS, [
            'westhub_id' => '7',
            'full_name' => '=HYPERLINK("http://evil")',
            'phone' => null,
        ]);

        Http::assertSent(function (Request $request): bool {
            // Read the wire format: values must be a JSON object, never [].
            $body = json_decode($request->body(), true);

            return $request->url() === self::URL
                && $request->method() === 'POST'
                && str_contains($request->body(), '"values":{')
                && $body['secret'] === 'shared-secret-123'
                && $body['action'] === 'append'
                && $body['tab'] === 'Promo Claims'
                && $body['headers'] === PromoClaimSheet::HEADERS
                // Sent as-is: the script stores it as literal text.
                && $body['values']['full_name'] === '=HYPERLINK("http://evil")'
                && $body['values']['phone'] === '';
        });

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'sheets.googleapis.com'));
    }

    public function test_an_update_reports_whether_the_row_was_found(): void
    {
        Http::fakeSequence('script.google.com/*')
            ->push(['ok' => true, 'updated' => true, 'row' => 5])
            ->push(['ok' => true, 'updated' => false]);

        $sheets = app(GoogleSheets::class);

        $this->assertTrue($sheets->updateRowColumn('Join Requests', ['westhub_id', 'westhub_decision'], '42', 'westhub_decision', 'approved'));
        $this->assertFalse($sheets->updateRowColumn('Join Requests', ['westhub_id', 'westhub_decision'], '43', 'westhub_decision', 'approved'));

        Http::assertSent(fn (Request $request): bool => $request['action'] === 'update'
            && $request['key'] === '42'
            && $request['header'] === 'westhub_decision'
            && $request['value'] === 'approved');
    }

    public function test_a_script_error_is_thrown_so_the_queued_job_retries(): void
    {
        Http::fake(['script.google.com/*' => Http::response(['ok' => false, 'error' => 'Secret rejected. It must match.'])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Apps Script: Secret rejected.');

        app(GoogleSheets::class)->appendRow('Promo Claims', ['westhub_id'], ['westhub_id' => '1']);
    }

    public function test_a_sign_in_page_instead_of_json_names_the_deployment_setting_to_fix(): void
    {
        Http::fake(['script.google.com/*' => Http::response('<html><title>Sign in</title></html>', 200, ['Content-Type' => 'text/html'])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Who has access: Anyone');

        app(GoogleSheets::class)->appendRow('Promo Claims', ['westhub_id'], ['westhub_id' => '1']);
    }

    public function test_it_is_only_configured_with_a_deployed_url_and_a_secret(): void
    {
        $this->assertTrue(app(GoogleSheets::class)->isConfigured());
        $this->assertTrue(app(GoogleSheets::class)->isEnabled());

        // A /dev test URL only works for a signed-in editor, never the server.
        $this->setSettings('integrations', ['google_sheets_apps_script_url' => 'https://script.google.com/macros/s/AKfycbx_TEST/dev']);
        $this->assertFalse(app(GoogleSheets::class)->isConfigured());

        $this->setSettings('integrations', [
            'google_sheets_apps_script_url' => self::URL,
            'google_sheets_apps_script_secret' => '',
        ]);
        $this->assertFalse(app(GoogleSheets::class)->isConfigured());
    }

    public function test_an_unset_method_keeps_existing_service_account_setups_unchanged(): void
    {
        $this->setSettings('integrations', ['google_sheets_method' => null]);

        $sheets = app(GoogleSheets::class);

        $this->assertSame(GoogleSheets::METHOD_SERVICE_ACCOUNT, $sheets->method());
        // The Apps Script URL is ignored, and there is no service account yet.
        $this->assertFalse($sheets->isConfigured());
    }

    public function test_the_connection_test_names_the_spreadsheet_and_its_tabs(): void
    {
        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'title' => 'WestHub Intake', 'tabs' => ['Join Requests', 'Promo Claims']])]);

        $result = app(GoogleSheets::class)->testConnection();

        $this->assertTrue($result['ok']);
        $this->assertStringContainsString('"WestHub Intake"', $result['message']);
        $this->assertStringContainsString('Join Requests, Promo Claims', $result['message']);

        Http::assertSent(fn (Request $request): bool => $request['action'] === 'ping');
    }

    public function test_the_connection_test_explains_a_wrong_url_without_calling_google(): void
    {
        Http::fake();

        $this->setSettings('integrations', ['google_sheets_apps_script_url' => 'https://docs.google.com/spreadsheets/d/abc/edit']);

        $result = app(GoogleSheets::class)->testConnection();

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('ends in /exec', $result['message']);
        Http::assertNothingSent();
    }
}
