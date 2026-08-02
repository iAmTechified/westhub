<?php

namespace Tests\Unit;

use App\Services\JoinRequests\GoogleSheetsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JoinRequestGoogleSheetsServiceTest extends TestCase
{
    public function test_it_appends_a_safe_join_request_row_using_the_expected_contract(): void
    {
        Cache::flush();

        config()->set('services.google_sheets', [
            'spreadsheet_id' => 'sheet-123',
            'sheet_name' => 'Join Requests',
            'service_account_email' => 'sheet-bot@example.com',
            'private_key' => 'unused-in-test',
        ]);

        Http::fake(function ($request) {
            if ($request->method() === 'GET' && str_contains($request->url(), 'Join%20Requests%21A1%3AM1')) {
                return Http::response([], 200);
            }

            if ($request->method() === 'PUT' && str_contains($request->url(), 'Join%20Requests%21A1%3AM1')) {
                return Http::response(['updatedRange' => 'Join Requests!A1:M1'], 200);
            }

            if ($request->method() === 'POST' && str_contains($request->url(), ':append')) {
                return Http::response(['updates' => ['updatedRows' => 1]], 200);
            }

            return Http::response([], 404);
        });

        $service = new class extends GoogleSheetsService
        {
            protected function accessToken(): string
            {
                return 'token-123';
            }
        };

        $service->appendJoinRequestRow([
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
            'owner' => '',
            'notes' => '',
            'admin_url' => 'https://admin.westhub.test/admin/join-requests',
        ]);

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), 'Join%20Requests%21A1%3AM1')
            && $request['values'][0] === GoogleSheetsService::HEADERS);

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), ':append')) {
                return false;
            }

            $row = $request['values'][0] ?? [];
            $serialized = json_encode($row, JSON_THROW_ON_ERROR);

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
            ] && ! str_contains($serialized, '123-45-6789')
                && ! str_contains($serialized, 'Home address')
                && ! str_contains($serialized, 'Date of birth');
        });
    }
}
