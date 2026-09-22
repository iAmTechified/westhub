<?php

namespace Tests\Feature;

use App\Livewire\PromoPopup;
use App\Mail\PromoClaimInternalAlert;
use App\Mail\PromoVoucherIssued;
use App\Models\PromoClaim;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PromoPopupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    protected function enablePromo(array $overrides = []): void
    {
        $this->setSettings('promotions', array_merge([
            'enabled' => '1',
            'starts_at' => now()->subWeek()->toDateString(),
            'ends_at' => now()->addWeek()->toDateString(),
            'voucher_prefix' => 'WH-FREE30',
            'voucher_validity_days' => '30',
            'notify_emails' => 'ops@westhub.test',
        ], $overrides));
    }

    public function test_the_popup_renders_nothing_while_the_campaign_is_switched_off(): void
    {
        $this->setSettings('promotions', ['enabled' => '0']);

        Livewire::test(PromoPopup::class)
            ->assertSet('open', false)
            ->assertDontSee('Claim My Free 2 Weeks');
    }

    public function test_the_popup_renders_nothing_once_the_campaign_end_date_has_passed(): void
    {
        $this->enablePromo([
            'starts_at' => now()->subMonths(2)->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);

        Livewire::test(PromoPopup::class)->assertDontSee('Claim My Free 2 Weeks');
    }

    public function test_a_live_campaign_renders_the_offer(): void
    {
        $this->enablePromo();

        Livewire::test(PromoPopup::class)
            ->assertSee('Claim My Free 2 Weeks')
            ->assertSee('FREE');
    }

    public function test_claiming_stores_the_claim_and_issues_a_voucher(): void
    {
        $this->enablePromo();

        $component = Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'Jane.Doe@Example.com')
            ->set('phone', '+1 555 0100')
            ->set('consent', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $claim = PromoClaim::query()->firstOrFail();

        $this->assertSame('jane.doe@example.com', $claim->email, 'Emails are stored lowercased so duplicates are caught.');
        $this->assertSame('Jane Doe', $claim->full_name);
        $this->assertStringStartsWith('WH-FREE30-', $claim->voucher_code);
        $this->assertSame(PromoClaim::STATUS_NEW, $claim->status);
        $this->assertNotNull($claim->expires_at);
        $this->assertNotNull($claim->consent_at);

        $component->assertSet('voucherCode', $claim->voucher_code);

        Mail::assertSent(PromoVoucherIssued::class, fn ($mail) => $mail->hasTo('jane.doe@example.com'));
        Mail::assertSent(PromoClaimInternalAlert::class, fn ($mail) => $mail->hasTo('ops@westhub.test'));
    }

    public function test_consent_is_required(): void
    {
        $this->enablePromo();

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('consent', false)
            ->call('submit')
            ->assertHasErrors(['consent']);

        $this->assertSame(0, PromoClaim::query()->count());
    }

    public function test_a_repeat_claim_returns_the_original_voucher_instead_of_issuing_a_second(): void
    {
        $this->enablePromo();

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('consent', true)
            ->call('submit');

        $original = PromoClaim::query()->firstOrFail();

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane D')
            ->set('email', 'JANE@example.com')
            ->set('consent', true)
            ->call('submit')
            ->assertSet('alreadyClaimed', true)
            ->assertSet('voucherCode', $original->voucher_code);

        $this->assertSame(1, PromoClaim::query()->count(), 'One household, one voucher.');
    }

    public function test_consenting_claimants_join_the_newsletter_when_that_is_enabled(): void
    {
        $this->enablePromo(['subscribe_on_consent' => '1']);

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('consent', true)
            ->call('submit');

        $subscriber = Subscriber::query()->where('email', 'jane@example.com')->first();

        $this->assertNotNull($subscriber);
        $this->assertSame('promo_free_month', $subscriber->source);
    }

    public function test_the_newsletter_opt_in_can_be_switched_off(): void
    {
        $this->enablePromo(['subscribe_on_consent' => '0']);

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('consent', true)
            ->call('submit');

        $this->assertSame(0, Subscriber::query()->count());
    }

    public function test_a_bot_filling_the_honeypot_creates_no_claim(): void
    {
        $this->enablePromo();

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Bot')
            ->set('email', 'bot@example.com')
            ->set('consent', true)
            ->set('honeypot', 'gotcha')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertSame(0, PromoClaim::query()->count());
    }

    public function test_a_claim_cannot_be_made_after_the_campaign_closes(): void
    {
        $this->enablePromo(['ends_at' => now()->subDay()->toDateString()]);

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('consent', true)
            ->call('submit')
            ->assertHasErrors(['email']);

        $this->assertSame(0, PromoClaim::query()->count());
    }
}
