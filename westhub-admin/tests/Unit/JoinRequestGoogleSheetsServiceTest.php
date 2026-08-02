<?php

namespace Tests\Unit;

use App\Services\JoinRequests\GoogleSheetsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JoinRequestGoogleSheetsServiceTest extends TestCase
{
    public function test_it_updates_the_decision_column_for_the_matching_join_request_row(): void
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
                return Http::response([
                    'values' => [GoogleSheetsService::HEADERS],
                ], 200);
            }

            if ($request->method() === 'GET' && str_contains($request->url(), 'Join%20Requests%21A%3AA')) {
                return Http::response([
                    'values' => [
                        ['westhub_id'],
                        ['101'],
                        ['102'],
                    ],
                ], 200);
            }

            if ($request->method() === 'PUT' && str_contains($request->url(), 'Join%20Requests%21J3')) {
                return Http::response(['updatedRange' => 'Join Requests!J3'], 200);
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

        $updated = $service->updateWesthubDecision(102, 'accepted');

        $this->assertTrue($updated);

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_contains($request->url(), 'Join%20Requests%21J3')
            && $request['values'] === [['accepted']]);
    }
}
