<?php

namespace Tests\Feature;

use App\Jobs\Promotions\AppendPromoClaimToGoogleSheet;
use App\Models\PromoClaim;
use App\Services\Google\GoogleSheets;
use App\Services\Promotions\PromoClaimService;
use App\Support\Sheets\PromoClaimSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Sheet rows are written during the request, so they appear even where no queue
 * worker is running. The queue is the retry path, and a queued run checks for
 * the row first so a lost reply cannot produce a duplicate.
 */
class SheetSyncRunsImmediatelyTest extends TestCase
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

    private function makeClaim(): PromoClaim
    {
        return PromoClaim::query()->create([
            'campaign' => 'free_month',
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'voucher_code' => 'WH-FREE30-ABCDE',
            'status' => PromoClaim::STATUS_NEW,
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function test_a_claim_reaches_the_sheet_with_no_queue_worker_running(): void
    {
        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'row' => 2])]);
        // A real queue, so 'nothing was queued' means the jobs table stayed empty.
        config()->set('queue.default', 'database');

        $claim = $this->makeClaim();

        app(PromoClaimService::class)->syncToSheet($claim);

        Http::assertSent(fn (Request $request): bool => json_decode($request->body(), true)['action'] === 'append');
        $this->assertNotNull($claim->fresh()->synced_at);
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_a_failed_immediate_attempt_falls_back_to_the_queue(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));
        config()->set('queue.default', 'database');

        $claim = $this->makeClaim();

        app(PromoClaimService::class)->syncToSheet($claim);

        $queued = DB::table('jobs')->get();

        $this->assertCount(1, $queued);
        $this->assertStringContainsString('AppendPromoClaimToGoogleSheet', $queued->first()->payload);

        // The claim is untouched and still marked unsynced for the retry.
        $this->assertNull($claim->fresh()->synced_at);
    }

    public function test_a_queued_run_does_not_duplicate_a_row_the_lost_attempt_already_wrote(): void
    {
        $claim = $this->makeClaim();

        // The existence probe reports the row is already there.
        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'updated' => true, 'row' => 2])]);

        (new AppendPromoClaimToGoogleSheet($claim->id))->handle(app(GoogleSheets::class));

        Http::assertSent(fn (Request $request): bool => json_decode($request->body(), true)['action'] === 'update');
        Http::assertNotSent(fn (Request $request): bool => json_decode($request->body(), true)['action'] === 'append');

        $this->assertNotNull($claim->fresh()->synced_at);
    }

    public function test_a_queued_run_appends_when_the_row_is_genuinely_missing(): void
    {
        $claim = $this->makeClaim();

        Http::fakeSequence('script.google.com/*')
            ->push(['ok' => true, 'updated' => false])
            ->push(['ok' => true, 'row' => 2]);

        (new AppendPromoClaimToGoogleSheet($claim->id))->handle(app(GoogleSheets::class));

        Http::assertSent(fn (Request $request): bool => json_decode($request->body(), true)['action'] === 'append');
        $this->assertNotNull($claim->fresh()->synced_at);
    }

    public function test_the_immediate_attempt_skips_the_probe_so_it_stays_one_call(): void
    {
        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'row' => 2])]);

        $claim = $this->makeClaim();

        (new AppendPromoClaimToGoogleSheet($claim->id, false, true))->handle(app(GoogleSheets::class));

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => json_decode($request->body(), true)['action'] === 'append');
    }

    public function test_the_probe_writes_the_key_back_unchanged(): void
    {
        Http::fake(['script.google.com/*' => Http::response(['ok' => true, 'updated' => true])]);

        $this->assertTrue(app(GoogleSheets::class)->rowExists('Promo Claims', PromoClaimSheet::HEADERS, '7'));

        Http::assertSent(function (Request $request): bool {
            $body = json_decode($request->body(), true);

            // Same column, same value: nothing in the sheet changes.
            return $body['action'] === 'update'
                && $body['key'] === '7'
                && $body['header'] === PromoClaimSheet::HEADERS[0]
                && $body['value'] === '7';
        });
    }

    public function test_a_shorter_timeout_is_a_copy_and_leaves_the_original_alone(): void
    {
        $sheets = app(GoogleSheets::class);
        $fast = $sheets->usingTimeout(5);

        $this->assertNotSame($sheets, $fast);
        $this->assertTrue($fast->usesAppsScript());
    }
}
