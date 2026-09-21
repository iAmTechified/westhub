<?php

namespace Tests\Feature;

use App\Livewire\BookAppointment;
use App\Models\Appointment;
use App\Models\PromoClaim;
use App\Services\Appointments\AppointmentProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The "google_booking_page" provider: our form captures the lead, then Google
 * Calendar's own appointment schedule page is framed for the visitor to pick a
 * time. No service account is involved.
 */
class GoogleBookingPageTest extends TestCase
{
    use RefreshDatabase;

    private const PAGE = 'https://calendar.google.com/calendar/appointments/schedules/AcZssZ1TestSchedule';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->setSettings('appointments', [
            'provider' => 'google_booking_page',
            'google_booking_page_url' => self::PAGE,
        ]);
    }

    public function test_submitting_saves_the_request_and_frames_the_booking_page(): void
    {
        Livewire::test(BookAppointment::class)
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertSee('Request saved. Now choose a time.')
            ->assertSee(self::PAGE . '?gv=true', false)
            ->assertSee('ada@example.com')
            ->assertDontSee('Calendly');

        $appointment = Appointment::query()->sole();

        $this->assertSame(Appointment::STATUS_NEW, $appointment->status);
        $this->assertSame('google_booking_page', data_get($appointment->meta, 'provider'));
        $this->assertSame(self::PAGE, data_get($appointment->meta, 'google_booking_page_url'));
    }

    public function test_a_short_share_link_is_framed_as_it_is(): void
    {
        $this->setSettings('appointments', ['google_booking_page_url' => 'https://calendar.app.google/uXbq7kEYq2']);

        Livewire::test(BookAppointment::class)
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->assertSee('src="https://calendar.app.google/uXbq7kEYq2"', false);
    }

    public function test_without_a_link_the_request_is_still_saved_and_no_calendly_warning_shows(): void
    {
        $this->setSettings('appointments', ['google_booking_page_url' => null]);

        Livewire::test(BookAppointment::class)
            ->assertDontSee('Calendly is not configured')
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->assertSee('A care coordinator will call you to arrange a time.')
            ->assertDontSee('<iframe', false);

        $this->assertSame(1, Appointment::query()->count());
    }

    /**
     * Google reports nothing back, so a voucher must not be burned on submit:
     * the visitor may never finish booking. Staff redeem it in Promo Claims.
     */
    public function test_a_voucher_is_recorded_but_not_redeemed(): void
    {
        $claim = PromoClaim::query()->create([
            'campaign' => 'free_month',
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'voucher_code' => 'WH-FREE30-ABCDE',
            'status' => PromoClaim::STATUS_NEW,
            'expires_at' => now()->addDays(30),
        ]);

        Livewire::test(BookAppointment::class)
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->call('submit')
            ->assertSee('is saved with your request');

        $this->assertSame('WH-FREE30-ABCDE', data_get(Appointment::query()->sole()->meta, 'promo_code'));
        $this->assertSame(PromoClaim::STATUS_NEW, $claim->fresh()->status);
    }

    public function test_the_manager_reports_the_provider_and_tests_the_link(): void
    {
        Http::fake(['calendar.google.com/*' => Http::response('<html>Book an appointment</html>')]);

        $manager = app(AppointmentProviderManager::class);

        $this->assertTrue($manager->isGoogleBookingPage());
        $this->assertFalse($manager->isGoogle());
        $this->assertTrue($manager->isConfigured());
        $this->assertSame('Google booking page', $manager->label());
        $this->assertTrue($manager->testConnection()['ok']);
    }

    public function test_the_connection_test_reports_a_missing_schedule(): void
    {
        Http::fake(['calendar.google.com/*' => Http::response('Not found', 404)]);

        $result = app(AppointmentProviderManager::class)->testConnection();

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('HTTP 404', $result['message']);
    }

    public function test_a_non_google_link_is_rejected_without_calling_out(): void
    {
        Http::fake();

        $this->setSettings('appointments', ['google_booking_page_url' => 'https://calendly.com/westhub/appointment']);

        $manager = app(AppointmentProviderManager::class);

        $this->assertFalse($manager->isConfigured());
        $this->assertFalse($manager->testConnection()['ok']);
        Http::assertNothingSent();
    }

    public function test_the_frame_shows_a_loading_state_until_google_answers(): void
    {
        Livewire::test(BookAppointment::class)
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->assertSee('Loading available times from Google...')
            // The overlay clears on the frame's own load event.
            ->assertSee('x-on:load="loaded = true"', false)
            ->assertSee('Still loading. You can open the booking page in a new tab instead.');
    }

    public function test_saying_the_booking_is_done_replaces_the_frame_with_a_final_state(): void
    {
        $component = Livewire::test(BookAppointment::class)
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->assertSee('<iframe', false)
            ->call('markBookingPageBooked')
            ->assertSet('bookingPageDone', true)
            ->assertSee("That's everything, thank you.", false)
            // The frame is gone, so nobody is left looking at Google's page.
            ->assertDontSee('<iframe', false)
            ->assertDontSee("I've booked my time", false);

        $component->assertSee('Close');

        $appointment = Appointment::query()->sole();

        $this->assertNotNull(data_get($appointment->meta, 'booking_page_confirmed_at'));
        $this->assertDatabaseHas('appointment_events', [
            'appointment_id' => $appointment->id,
            'event_type' => 'booking_page_confirmed',
        ]);
    }

    public function test_a_fresh_voucher_booking_starts_from_the_form_again(): void
    {
        $claim = PromoClaim::query()->create([
            'campaign' => 'free_month',
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'voucher_code' => 'WH-FREE30-ZZZZZ',
            'status' => PromoClaim::STATUS_NEW,
            'expires_at' => now()->addDays(30),
        ]);

        Livewire::test(BookAppointment::class)
            ->set('fullName', 'Ada Client')
            ->set('email', 'ada@example.com')
            ->call('submit')
            ->call('markBookingPageBooked')
            ->assertSet('bookingPageDone', true)
            // Reopening with a voucher must not leave the previous final state up.
            ->dispatch('westhub-book-with-promo', promoCode: $claim->voucher_code)
            ->assertSet('bookingPageDone', false)
            ->assertSet('submitted', false);
    }
}
