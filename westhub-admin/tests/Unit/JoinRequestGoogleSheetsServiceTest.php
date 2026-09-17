<?php

namespace Tests\Unit;

use App\Services\Google\GoogleServiceAccount;
use App\Services\Google\GoogleSheets;
use App\Services\JoinRequests\GoogleSheetsService;
use App\Support\Sheets\JoinRequestSheet;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JoinRequestGoogleSheetsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        SiteSettings::flush();

        config()->set('services.google_sheets', [
            'spreadsheet_id' => 'sheet-123',
            'sheet_name' => 'Join Requests',
            'service_account_email' => 'sheet-bot@example.com',
            'private_key' => 'stubbed-in-test',
        ]);

        // Swap in a client that skips the RS256 assertion so these tests cover
        // the Sheets contract rather than OpenSSL.
        $this->app->bind(GoogleSheets::class, fn (): GoogleSheets => new class extends GoogleSheets
        {
            public function account(): GoogleServiceAccount
            {
                return new class('sheet-bot@example.com', 'stub') extends GoogleServiceAccount
                {
                    public function isUsable(): bool
                    {
                        return true;
                    }

                    public function accessToken(string $scope): string
                    {
                        return 'token-123';
                    }
                };
            }
        });
    }

    protected function tearDown(): void
    {
        SiteSettings::flush();

        parent::tearDown();
    }

    public function test_the_contract_has_sixteen_columns(): void
    {
        $this->assertCount(16, JoinRequestSheet::HEADERS);
        $this->assertSame(JoinRequestSheet::HEADERS, GoogleSheetsService::HEADERS);
        $this->assertSame(['resume_url', 'signature_url', 'document_urls'], array_slice(JoinRequestSheet::HEADERS, 13));
    }

    public function test_it_updates_the_decision_column_for_the_matching_row(): void
    {
        $this->fakeSheets(columnA: [['westhub_id'], ['101'], ['102']]);

        $updated = app(GoogleSheetsService::class)->updateWesthubDecision(102, 'accepted');

        $this->assertTrue($updated);

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), 'Join%20Requests%21J3')
            && str_contains($request->url(), 'valueInputOption=RAW')
            && $request['values'] === [['accepted']]);
    }

    public function test_it_reports_false_when_the_row_is_not_in_the_sheet(): void
    {
        $this->fakeSheets(columnA: [['westhub_id'], ['101']]);

        $this->assertFalse(app(GoogleSheetsService::class)->updateWesthubDecision(999, 'accepted'));
    }

    /**
     * A human reordering columns used to break every submission. Rows are now
     * positioned by header name instead.
     */
    public function test_it_follows_the_sheet_when_columns_have_been_reordered(): void
    {
        // westhub_decision (normally J) moved into the second column.
        $reordered = ['westhub_id', 'westhub_decision', 'submitted_at', 'full_name', 'email'];

        $this->fakeSheets(headers: $reordered, columnA: [['westhub_id'], ['55']]);

        $this->assertTrue(app(GoogleSheetsService::class)->updateWesthubDecision(55, 'declined'));

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), 'Join%20Requests%21B2'));
    }

    public function test_it_appends_all_sixteen_columns_in_order(): void
    {
        $this->fakeSheets(columnA: [['westhub_id']]);

        app(GoogleSheetsService::class)->appendJoinRequestRow([
            'westhub_id' => 7,
            'full_name' => 'Alex Carter',
            'email' => 'alex@example.com',
            'admin_url' => 'https://admin.westhub.test/admin/join-requests',
            'resume_url' => 'https://files.westhub.test/resume.pdf',
            'signature_url' => 'https://files.westhub.test/signature.png',
            'document_urls' => ['https://files.westhub.test/a.pdf', 'https://files.westhub.test/b.pdf'],
        ]);

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), ':append')) {
                return false;
            }

            $row = $request['values'][0] ?? [];

            return count($row) === 16
                && $row[0] === '7'
                && $row[12] === 'https://admin.westhub.test/admin/join-requests'
                && $row[13] === 'https://files.westhub.test/resume.pdf'
                && $row[14] === 'https://files.westhub.test/signature.png'
                && $row[15] === "https://files.westhub.test/a.pdf\nhttps://files.westhub.test/b.pdf";
        });
    }

    /**
     * User-entered text must never be evaluated as a spreadsheet formula.
     */
    public function test_appended_values_are_written_raw(): void
    {
        $this->fakeSheets(columnA: [['westhub_id']]);

        app(GoogleSheetsService::class)->appendJoinRequestRow([
            'westhub_id' => 7,
            'full_name' => '=HYPERLINK("http://evil.test","click")',
            'email' => 'applicant@example.com',
        ]);

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), ':append')
            && str_contains($request->url(), 'valueInputOption=RAW'));
    }

    /**
     * Env-only setups synced before the admin toggle existed and must keep
     * syncing after deploy.
     */
    public function test_sheets_are_enabled_for_an_env_only_setup_when_the_toggle_was_never_saved(): void
    {
        $this->assertTrue(app(GoogleSheetsService::class)->isConfigured());
    }

    /**
     * @param  array<int, string>|null  $headers
     * @param  array<int, array<int, string>>  $columnA
     */
    protected function fakeSheets(?array $headers = null, array $columnA = []): void
    {
        $headers ??= JoinRequestSheet::HEADERS;

        Http::fake(function ($request) use ($headers, $columnA) {
            $url = $request->url();

            if (str_contains($url, 'oauth2.googleapis.com/token')) {
                return Http::response(['access_token' => 'token-123', 'expires_in' => 3600], 200);
            }

            if (str_contains($url, 'fields=sheets.properties.title')) {
                return Http::response(['sheets' => [['properties' => ['title' => 'Join Requests']]]], 200);
            }

            if ($request->method() === 'GET' && str_contains($url, 'Join%20Requests%211%3A1')) {
                return Http::response(['values' => [$headers]], 200);
            }

            if ($request->method() === 'GET' && str_contains($url, 'Join%20Requests%21A%3AA')) {
                return Http::response(['values' => $columnA], 200);
            }

            return Http::response(['updatedRange' => 'ok'], 200);
        });
    }
}
