<?php

namespace Tests\Unit;

use App\Services\Google\GoogleServiceAccount;
use App\Services\Google\GoogleSheets;
use App\Services\JoinRequests\GoogleSheetsService;
use App\Support\Sheets\JoinRequestSheet;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JoinRequestGoogleSheetsServiceTest extends TestCase
{
    use RefreshDatabase;

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

        // Skip the RS256 assertion so these tests cover the Sheets contract
        // rather than OpenSSL.
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

    public function test_the_contract_has_sixteen_columns(): void
    {
        $this->assertCount(16, JoinRequestSheet::HEADERS);
        $this->assertSame(JoinRequestSheet::HEADERS, GoogleSheetsService::HEADERS);
        $this->assertSame(['resume_url', 'signature_url', 'document_urls'], array_slice(JoinRequestSheet::HEADERS, 13));
    }

    public function test_it_writes_the_header_row_when_the_tab_is_empty(): void
    {
        $this->fakeSheets(existingHeaders: []);

        app(GoogleSheetsService::class)->appendJoinRequestRow($this->payload());

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), 'Join%20Requests%21A1%3AP1')
            && $request['values'][0] === JoinRequestSheet::HEADERS);
    }

    public function test_it_appends_the_row_in_the_expected_column_order(): void
    {
        $this->fakeSheets();

        app(GoogleSheetsService::class)->appendJoinRequestRow($this->payload());

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), ':append')) {
                return false;
            }

            $row = $request['values'][0] ?? [];

            return $row === [
                '42',
                '2026-04-25T08:15:00+00:00',
                'Mr. Alex Carter',
                'alex@example.com',
                '+1 2015550122',
                'Skilled Professional',
                'Skilled Professional - Registered Nurse',
                '2026-05-15',
                'new',
                '',
                '',
                '',
                'https://admin.westhub.test/admin/join-requests',
                'https://files.westhub.test/resume.pdf',
                'https://files.westhub.test/signature.png',
                "https://files.westhub.test/licence.pdf\nhttps://files.westhub.test/id.pdf",
            ];
        });
    }

    public function test_it_never_leaks_sensitive_application_fields_into_the_sheet(): void
    {
        $this->fakeSheets();

        app(GoogleSheetsService::class)->appendJoinRequestRow($this->payload() + [
            'ssn' => '123-45-6789',
            'home_address' => 'Home address 12',
            'date_of_birth' => 'Date of birth 1980-01-01',
        ]);

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), ':append')) {
                return false;
            }

            $serialized = json_encode($request['values'][0] ?? [], JSON_THROW_ON_ERROR);

            return ! str_contains($serialized, '123-45-6789')
                && ! str_contains($serialized, 'Home address')
                && ! str_contains($serialized, 'Date of birth');
        });
    }

    /**
     * Applicant text must reach the sheet as text, never as a live formula.
     */
    public function test_it_appends_with_raw_input_so_formulas_are_not_evaluated(): void
    {
        $this->fakeSheets();

        app(GoogleSheetsService::class)->appendJoinRequestRow(
            ['westhub_id' => 9, 'full_name' => '=1+1'] + $this->payload()
        );

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_contains($request->url(), ':append')
            && str_contains($request->url(), 'valueInputOption=RAW'));
    }

    /**
     * Production synced from env alone before the admin toggle existed. A deploy
     * must not silently switch that off.
     */
    public function test_sheets_are_enabled_for_an_env_only_setup_when_the_toggle_was_never_saved(): void
    {
        $this->assertTrue(app(GoogleSheets::class)->isEnabled());
        $this->assertTrue(app(GoogleSheetsService::class)->isConfigured());
    }

    public function test_sheets_are_disabled_when_the_toggle_is_saved_as_off(): void
    {
        $this->setSettings('integrations', ['google_sheets_enabled' => '0']);

        $this->assertFalse(app(GoogleSheets::class)->isEnabled());
        $this->assertFalse(app(GoogleSheetsService::class)->isConfigured());
    }

    public function test_sheets_are_enabled_when_the_toggle_is_saved_as_on(): void
    {
        $this->setSettings('integrations', ['google_sheets_enabled' => '1']);

        $this->assertTrue(app(GoogleSheets::class)->isEnabled());
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return [
            'westhub_id' => 42,
            'submitted_at' => '2026-04-25T08:15:00+00:00',
            'full_name' => 'Mr. Alex Carter',
            'email' => 'alex@example.com',
            'phone' => '+1 2015550122',
            'applicant_type' => 'Skilled Professional',
            'position_profession' => 'Skilled Professional - Registered Nurse',
            'date_available' => '2026-05-15',
            'sheet_stage' => 'new',
            'westhub_decision' => '',
            'admin_url' => 'https://admin.westhub.test/admin/join-requests',
            'resume_url' => 'https://files.westhub.test/resume.pdf',
            'signature_url' => 'https://files.westhub.test/signature.png',
            'document_urls' => [
                'https://files.westhub.test/licence.pdf',
                'https://files.westhub.test/id.pdf',
            ],
        ];
    }

    /**
     * @param  array<int, string>|null  $existingHeaders
     */
    protected function fakeSheets(?array $existingHeaders = null): void
    {
        $existingHeaders ??= JoinRequestSheet::HEADERS;

        Http::fake(function ($request) use ($existingHeaders) {
            $url = $request->url();

            if (str_contains($url, 'oauth2.googleapis.com/token')) {
                return Http::response(['access_token' => 'token-123', 'expires_in' => 3600], 200);
            }

            if (str_contains($url, 'fields=sheets.properties.title')) {
                return Http::response(['sheets' => [['properties' => ['title' => 'Join Requests']]]], 200);
            }

            if ($request->method() === 'GET' && str_contains($url, 'Join%20Requests%211%3A1')) {
                return Http::response($existingHeaders === [] ? [] : ['values' => [$existingHeaders]], 200);
            }

            return Http::response(['updates' => ['updatedRows' => 1]], 200);
        });
    }
}
