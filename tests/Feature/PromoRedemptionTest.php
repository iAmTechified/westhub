<?php

namespace Tests\Feature;

use App\Livewire\BookAppointment;
use App\Livewire\PromoPopup;
use App\Models\Appointment;
use App\Models\PromoClaim;
use App\Services\Google\GoogleCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The booking form is rendered once in the layout with no mount arguments, so
 * a voucher reaches it through the `westhub-book-with-promo` event.
 */
class PromoRedemptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    protected function makeClaim(array $overrides = []): PromoClaim
    {
        return PromoClaim::query()->create(array_merge([
            'campaign' => 'free_month',
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 555 0100',
            'voucher_code' => 'WH-FREE30-ABCDE',
            'status' => PromoClaim::STATUS_NEW,
            'expires_at' => now()->addDays(30),
        ], $overrides));
    }

    public function test_a_voucher_event_prefills_the_claimant_details(): void
    {
        $claim = $this->makeClaim();

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->assertSet('promoValid', true)
            ->assertSet('promoCode', 'WH-FREE30-ABCDE')
            ->assertSet('fullName', 'Jane Doe')
            ->assertSet('email', 'jane@example.com')
            ->assertSee('Voucher WH-FREE30-ABCDE applied');
    }

    public function test_explicit_prefill_wins_over_the_claim_details(): void
    {
        $claim = $this->makeClaim();

        Livewire::test(BookAppointment::class)
            ->call('prefillFromPromo', $claim->voucher_code, 'Janet Doe', 'janet@example.com')
            ->assertSet('promoValid', true)
            ->assertSet('fullName', 'Janet Doe')
            ->assertSet('email', 'janet@example.com')
            ->assertSet('phone', '+1 555 0100');
    }

    public function test_voucher_codes_are_normalised(): void
    {
        $this->makeClaim();

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: '  wh-free30-abcde ')
            ->assertSet('promoValid', true)
            ->assertSet('promoCode', 'WH-FREE30-ABCDE');
    }

    public function test_an_unknown_voucher_is_reported_without_blocking_the_booking(): void
    {
        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: 'WH-FREE30-NOPE')
            ->assertSet('promoValid', false)
            ->assertSet('promoCode', 'WH-FREE30-NOPE')
            ->assertSee('Voucher not recognised');
    }

    public function test_an_expired_voucher_is_not_accepted(): void
    {
        $claim = $this->makeClaim(['expires_at' => now()->subDay()]);

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->assertSet('promoValid', false);
    }

    public function test_an_already_redeemed_voucher_is_not_accepted_twice(): void
    {
        $claim = $this->makeClaim(['status' => PromoClaim::STATUS_REDEEMED]);

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->assertSet('promoValid', false);
    }

    public function test_reopening_with_a_voucher_resets_a_previous_submission(): void
    {
        $claim = $this->makeClaim();

        Livewire::test(BookAppointment::class)
            ->set('fullName', 'First Booking')
            ->set('email', 'first@example.com')
            ->call('submit')
            ->assertSet('submitted', true)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->assertSet('submitted', false)
            ->assertSet('appointmentId', null)
            ->assertSet('calendlyStatus', 'idle')
            ->assertSet('promoValid', true);
    }

    public function test_the_voucher_code_is_recorded_on_the_appointment(): void
    {
        $claim = $this->makeClaim();

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('phone', '+1 555 0100')
            ->call('submit')
            ->assertHasNoErrors();

        $appointment = Appointment::query()->firstOrFail();

        $this->assertSame('WH-FREE30-ABCDE', data_get($appointment->meta, 'promo_code'));
        $this->assertSame('calendly', data_get($appointment->meta, 'provider'));
    }

    public function test_calendly_confirmation_redeems_the_voucher(): void
    {
        $claim = $this->makeClaim();

        $component = Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->call('submit');

        $appointment = Appointment::query()->firstOrFail();

        $component->dispatch('calendlyScheduled', appointmentId: $appointment->id, payload: [])
            ->assertSet('calendlyStatus', 'scheduled');

        $claim->refresh();

        $this->assertSame(PromoClaim::STATUS_REDEEMED, $claim->status);
        $this->assertSame($appointment->id, $claim->appointment_id);
    }

    public function test_booking_a_google_slot_redeems_the_voucher_and_links_the_appointment(): void
    {
        Http::fake();

        $this->setSettings('appointments', [
            'provider' => 'google',
            'google_calendar_id' => 'care@westhub.test',
            'timezone' => 'UTC',
            'google_business_days' => '1,2,3,4,5,6,7',
            'google_min_notice_hours' => '0',
        ]);

        $this->app->instance(GoogleCalendar::class, new class extends GoogleCalendar
        {
            public function isConfigured(): bool
            {
                return true;
            }

            public function availableSlots(Carbon $day): array
            {
                return [[
                    'start' => $day->copy()->setTime(10, 0)->toIso8601String(),
                    'end' => $day->copy()->setTime(11, 0)->toIso8601String(),
                    'label' => '10:00 AM',
                ]];
            }

            public function upsertEvent(array $details, ?string $existingEventId = null): array
            {
                return ['id' => 'evt_promo', 'html_link' => 'https://calendar.test/evt', 'meet_url' => 'https://meet.test/abc'];
            }
        });

        $claim = $this->makeClaim();
        $slot = Carbon::now('UTC')->addDay()->setTime(10, 0);

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSee('Pick a time that suits you')
            ->call('confirmSlot', $slot->toIso8601String())
            ->assertSet('scheduleError', null)
            ->assertSet('meetUrl', 'https://meet.test/abc')
            ->assertSee('Appointment confirmed');

        $appointment = Appointment::query()->firstOrFail();
        $claim->refresh();

        $this->assertSame(PromoClaim::STATUS_REDEEMED, $claim->status);
        $this->assertSame($appointment->id, $claim->appointment_id);
        $this->assertNotNull($claim->redeemed_at);
        $this->assertNotNull($appointment->scheduled_at);
        $this->assertNotNull($appointment->end_time);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame('evt_promo', data_get($appointment->meta, 'google_event_id'));
        $this->assertSame('google', data_get($appointment->meta, 'provider'));
    }

    public function test_the_booking_form_still_works_when_no_voucher_is_supplied(): void
    {
        Livewire::test(BookAppointment::class)
            ->set('fullName', 'No Promo')
            ->set('email', 'noprom@example.com')
            ->call('submit')
            ->assertHasNoErrors();

        $appointment = Appointment::query()->firstOrFail();

        $this->assertNull(data_get($appointment->meta, 'promo_code'));
    }

    public function test_the_popup_hands_the_voucher_to_the_layout_booking_modal(): void
    {
        $this->setSettings('promotions', [
            'enabled' => '1',
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        Livewire::test(PromoPopup::class)
            ->set('fullName', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('phone', '+1 555 0100')
            ->set('voucherCode', 'WH-FREE30-ABCDE')
            ->call('bookAppointment')
            ->assertSet('open', false)
            ->assertNotDispatched('openModal')
            ->assertDispatched('westhub-book-with-promo', function (string $event, array $params): bool {
                return ($params['promoCode'] ?? null) === 'WH-FREE30-ABCDE'
                    && ($params['prefillName'] ?? null) === 'Jane Doe'
                    && ($params['prefillEmail'] ?? null) === 'jane@example.com';
            });
    }
}
