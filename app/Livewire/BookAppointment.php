<?php

namespace App\Livewire;

use App\Exceptions\Appointments\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\AppointmentEvent;
use App\Models\County;
use App\Models\Service;
use App\Models\Township;
use App\Services\Appointments\AppointmentProviderManager;
use App\Services\Promotions\PromoClaimService;
use App\Support\LocationData;
use App\Support\SiteSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class BookAppointment extends Component
{
    /** @var array<string, bool> Per-request cache for Schema::hasTable checks. */
    private static array $tableExists = [];

    public string $fullName = '';
    public string $email = '';
    public string $phone = '';
    public string $countyId = '';
    public string $townshipId = '';
    public string $serviceId = '';
    public string $honeypot = '';
    public bool $submitted = false;
    public bool $calendlyScheduled = false;
    public string $calendlyStatus = 'idle';
    public ?int $appointmentId = null;
    public ?string $calendlyUrl = null;

    /** Google appointment schedule, framed after the form when that provider is active. */
    public ?string $bookingPageUrl = null;

    /** Set when the visitor says they finished booking on Google's page. */
    public bool $bookingPageDone = false;

    /** "calendly", "google" or "google_booking_page", chosen in the admin and read live from settings. */
    public string $provider = AppointmentProviderManager::CALENDLY;

    /* ---- Google Calendar scheduling state ---- */
    public array $availableDates = [];
    public string $selectedDate = '';
    /** Never name this $slots: it collides with Livewire v4's slot handling. */
    public array $timeSlots = [];
    public string $selectedSlot = '';
    public ?string $scheduledLabel = null;
    public ?string $meetUrl = null;
    public ?string $calendarLink = null;
    public ?string $scheduleError = null;

    /* ---- Promo voucher ---- */
    public ?string $promoCode = null;
    public ?string $promoMessage = null;
    public bool $promoValid = false;

    public function mount(): void
    {
        $this->provider = SiteSettings::appointmentProvider();
        $this->calendlyUrl = SiteSettings::calendlyAppointmentUrl();
        $this->bookingPageUrl = SiteSettings::googleBookingPageEmbedUrl();
    }

    /**
     * The booking modal lives once in the layout and persists across opens, so
     * the promo popup (or a ?promo= voucher link) hands the voucher over with
     * this event instead of mount arguments.
     */
    #[On('westhub-book-with-promo')]
    public function prefillFromPromo(
        ?string $promoCode = null,
        ?string $prefillName = null,
        ?string $prefillEmail = null,
        ?string $prefillPhone = null,
        ?string $prefillServiceId = null,
    ): void {
        $this->resetErrorBag();
        $this->reset([
            'submitted',
            'calendlyScheduled',
            'calendlyStatus',
            'appointmentId',
            'availableDates',
            'selectedDate',
            'timeSlots',
            'selectedSlot',
            'scheduledLabel',
            'meetUrl',
            'calendarLink',
            'scheduleError',
            'bookingPageDone',
            'promoCode',
            'promoMessage',
            'promoValid',
        ]);

        $this->provider = SiteSettings::appointmentProvider();
        $this->calendlyUrl = SiteSettings::calendlyAppointmentUrl();
        $this->bookingPageUrl = SiteSettings::googleBookingPageEmbedUrl();

        if (filled($prefillName)) {
            $this->fullName = (string) $prefillName;
        }

        if (filled($prefillEmail)) {
            $this->email = (string) $prefillEmail;
        }

        if (filled($prefillPhone)) {
            $this->phone = (string) $prefillPhone;
        }

        if (filled($prefillServiceId)) {
            $this->serviceId = (string) $prefillServiceId;
        }

        $this->applyPromoCode($promoCode);
    }

    /**
     * Validate a voucher up front so the visitor sees it is recognised before
     * they commit to filling in the form.
     */
    public function applyPromoCode(?string $code): void
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return;
        }

        $this->promoCode = $code;

        $claim = app(PromoClaimService::class)->findRedeemable($code);

        if ($claim) {
            $this->promoValid = true;
            $this->promoMessage = 'Voucher applied.';

            $this->fullName = $this->fullName ?: (string) $claim->full_name;
            $this->email = $this->email ?: (string) $claim->email;
            $this->phone = $this->phone ?: (string) ($claim->phone ?? '');
            $this->serviceId = $this->serviceId ?: (string) ($claim->service_id ?? '');

            return;
        }

        $this->promoValid = false;
        $this->promoMessage = 'We could not find an active voucher with that code. You can still book as normal.';
    }

    public function updatedCountyId(): void
    {
        $this->townshipId = '';
    }

    public function selectCounty(string $id): void
    {
        $this->countyId = $id;
        $this->townshipId = '';
    }

    public function submit(): void
    {
        $validated = $this->validate($this->rules());

        if ($this->honeypot !== '') {
            return;
        }

        $countySelection = $this->resolveCountySelection($validated['countyId'] ?? '');
        $townshipSelection = $this->resolveTownshipSelection($validated['townshipId'] ?? '');

        if (($validated['countyId'] ?? '') !== '' && ! $countySelection['name']) {
            $this->addError('countyId', 'Please choose a valid county.');

            return;
        }

        if (($validated['townshipId'] ?? '') !== '' && ! $townshipSelection['name']) {
            $this->addError('townshipId', 'Please choose a valid township.');

            return;
        }

        if (! $countySelection['name'] && $townshipSelection['county_name']) {
            $countySelection = [
                'id' => $townshipSelection['county_id'],
                'name' => $townshipSelection['county_name'],
                'slug' => $townshipSelection['county_slug'],
                'source' => $townshipSelection['source'],
            ];
        }

        $appointment = Appointment::query()->create([
            'full_name' => $validated['fullName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'county_id' => $countySelection['id'],
            'township_id' => $townshipSelection['id'],
            'service_id' => $validated['serviceId'] ?: null,
            'preferred_date' => null,
            'preferred_time' => null,
            'message' => null,
            'source' => 'website',
            'status' => Appointment::STATUS_NEW,
            'scheduled_at' => null,
            'meta' => $this->appointmentMeta($countySelection, $townshipSelection),
        ]);

        $this->recordEvent($appointment, 'created', null, $appointment->status);

        $this->appointmentId = $appointment->id;
        $this->submitted = true;

        if ($this->provider === AppointmentProviderManager::GOOGLE) {
            $this->startGoogleScheduling();

            return;
        }

        if ($this->provider === AppointmentProviderManager::GOOGLE_BOOKING_PAGE) {
            // The view frames Google's booking page next. Google sends nothing
            // back to this site, so the request stays "new" and staff match it
            // to the calendar entry, and redeem any voucher, by hand.
            return;
        }

        $this->calendlyUrl = SiteSettings::calendlyAppointmentUrlFor($appointment);
        $this->calendlyStatus = $this->calendlyUrl ? 'opening' : 'unconfigured';

        if ($this->calendlyUrl) {
            $this->dispatch('westhub-open-calendly', url: $this->calendlyUrl, appointmentId: $appointment->id);
        }
    }

    /* ------------------------------------------------------------------
     | Google Calendar scheduling
     ------------------------------------------------------------------ */

    protected function providers(): AppointmentProviderManager
    {
        return app(AppointmentProviderManager::class);
    }

    protected function startGoogleScheduling(): void
    {
        $manager = $this->providers();

        if (! $manager->isConfigured()) {
            // Lead is captured; we just cannot offer times yet.
            $this->scheduleError = 'unconfigured';

            return;
        }

        $this->availableDates = $manager->availableDates();

        if ($this->availableDates === []) {
            $this->scheduleError = 'no_availability';

            return;
        }

        $this->selectDate($this->availableDates[0]['date']);
    }

    public function selectDate(string $date): void
    {
        $this->scheduleError = null;
        $this->selectedSlot = '';
        $this->selectedDate = $date;
        $this->timeSlots = $this->providers()->slotsFor($date);

        if ($this->timeSlots === []) {
            $this->scheduleError = 'no_slots_for_day';
        }
    }

    public function confirmSlot(string $start, PromoClaimService $promos): void
    {
        if (! $this->appointmentId) {
            return;
        }

        $appointment = Appointment::query()->with(['service', 'county', 'township'])->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        try {
            $result = $this->providers()->schedule($appointment, Carbon::parse($start));
        } catch (SlotUnavailableException) {
            $this->scheduleError = 'slot_taken';
            $this->selectDate($this->selectedDate);

            return;
        } catch (Throwable $e) {
            report($e);
            $this->scheduleError = 'failed';

            return;
        }

        $this->selectedSlot = $start;
        $this->meetUrl = $result['meet_url'];
        $this->calendarLink = $result['html_link'];
        $this->scheduledLabel = Carbon::parse($start)
            ->setTimezone($this->providers()->google()->timezone())
            ->format('l j F Y \a\t g:i A');
        $this->scheduleError = null;

        $this->recordEvent($appointment->fresh(), 'google_scheduled', Appointment::STATUS_NEW, $appointment->status);
        $this->redeemPromo($appointment, $promos);
    }

    /**
     * Google's booking page never tells this site that a booking happened, so
     * the visitor tells us. It is self-reported, and recorded as such: staff
     * still match the request to the calendar entry.
     */
    public function markBookingPageBooked(): void
    {
        $this->bookingPageDone = true;

        if ($this->provider !== AppointmentProviderManager::GOOGLE_BOOKING_PAGE || ! $this->appointmentId) {
            return;
        }

        $appointment = Appointment::query()->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        $appointment->update([
            'meta' => array_merge((array) ($appointment->meta ?? []), [
                'booking_page_confirmed_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->recordEvent($appointment->fresh(), 'booking_page_confirmed', $appointment->status, $appointment->status);
    }

    public function chooseAnotherTime(): void
    {
        $this->selectedSlot = '';
        $this->scheduledLabel = null;
        $this->startGoogleScheduling();
    }

    /**
     * Mark the voucher used and link it to the appointment that consumed it.
     */
    protected function redeemPromo(Appointment $appointment, PromoClaimService $promos): void
    {
        if (! $this->promoCode) {
            return;
        }

        $claim = $promos->findRedeemable($this->promoCode);

        if (! $claim) {
            return;
        }

        try {
            $promos->markRedeemed($claim, $appointment->id);
        } catch (Throwable $e) {
            report($e);
        }
    }

    #[On('calendlyOpened')]
    public function markCalendlyOpened(int $appointmentId): void
    {
        if ($this->appointmentId !== $appointmentId || $this->calendlyScheduled) {
            return;
        }

        $this->calendlyStatus = 'opening';
    }

    #[On('calendlyScheduled')]
    public function markCalendlyScheduled(int $appointmentId, array $payload = []): void
    {
        if ($this->appointmentId !== $appointmentId) {
            return;
        }

        $appointment = Appointment::query()->find($appointmentId);

        if (! $appointment) {
            return;
        }

        $oldStatus = $appointment->status;
        $meta = array_merge((array) ($appointment->meta ?? []), [
            'calendly_browser_event_at' => now()->toIso8601String(),
            'calendly_event_uri' => data_get($payload, 'event.uri'),
            'calendly_invitee_uri' => data_get($payload, 'invitee.uri'),
        ]);

        $appointment->update([
            'status' => Appointment::STATUS_CONFIRMED,
            'meta' => array_filter($meta, fn ($value): bool => ! is_null($value)),
        ]);

        $this->recordEvent($appointment->fresh(), 'calendly_scheduled_browser', $oldStatus, Appointment::STATUS_CONFIRMED);
        $this->calendlyScheduled = true;
        $this->calendlyStatus = 'scheduled';

        $this->redeemPromo($appointment->fresh(), app(PromoClaimService::class));
    }

    #[On('calendlyClosed')]
    public function markCalendlyClosed(int $appointmentId, bool $scheduled = false): void
    {
        if ($this->appointmentId !== $appointmentId) {
            return;
        }

        if ($scheduled || $this->calendlyScheduled) {
            $this->calendlyStatus = 'scheduled';

            return;
        }

        $this->calendlyStatus = 'closed';
    }

    #[On('calendlyOpenFailed')]
    public function markCalendlyOpenFailed(int $appointmentId): void
    {
        if ($this->appointmentId !== $appointmentId || $this->calendlyScheduled) {
            return;
        }

        $this->calendlyStatus = 'failed';
    }

    protected function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'countyId' => ['nullable', 'string', 'max:120'],
            'townshipId' => ['nullable', 'string', 'max:160'],
            'serviceId' => ['nullable', 'integer'],
            'honeypot' => ['nullable', 'max:0'],
        ];
    }

    protected function appointmentMeta(array $countySelection, array $townshipSelection): array
    {
        $meta = array_filter([
            'provider' => $this->provider,
            'calendly_url' => $this->provider === AppointmentProviderManager::CALENDLY ? SiteSettings::calendlyAppointmentUrl() : null,
            'calendly_integration' => $this->provider === AppointmentProviderManager::CALENDLY ? 'javascript_embed' : null,
            'google_booking_page_url' => $this->provider === AppointmentProviderManager::GOOGLE_BOOKING_PAGE ? SiteSettings::googleBookingPageUrl() : null,
            'promo_code' => $this->promoValid ? $this->promoCode : null,
            'submitted_from' => request()->fullUrl(),
            'timezone' => SiteSettings::appointmentTimezone(),
            'user_agent' => str(request()->userAgent() ?? '')->limit(500)->toString(),
        ], static fn ($value): bool => ! is_null($value));

        $locationPreference = array_filter([
            'county_name' => $countySelection['name'],
            'county_slug' => $countySelection['slug'],
            'county_source' => $countySelection['source'],
            'township_name' => $townshipSelection['name'],
            'township_slug' => $townshipSelection['slug'],
            'township_source' => $townshipSelection['source'],
        ], fn ($value): bool => $value !== null && $value !== '');

        if ($locationPreference !== []) {
            $meta['location_preference'] = $locationPreference;
        }

        return $meta;
    }

    protected function recordEvent(Appointment $appointment, string $eventType, ?string $oldStatus, ?string $newStatus): void
    {
        try {
            $key = $appointment->getConnectionName() . '.appointment_events';

            if (! array_key_exists($key, self::$tableExists)) {
                self::$tableExists[$key] = Schema::connection($appointment->getConnectionName())->hasTable('appointment_events');
            }

            if (! self::$tableExists[$key]) {
                return;
            }

            AppointmentEvent::query()->create([
                'appointment_id' => $appointment->id,
                'event_type' => $eventType,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => 'Appointment request submitted from the public website.',
                'meta' => ['source' => 'website'],
                'event_at' => now(),
            ]);
        } catch (Throwable) {
            //
        }
    }

    protected function options(string $model): Collection
    {
        try {
            $cacheKey = 'book_appointment_options_' . class_basename($model);

            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($model) {
                $query = $model::query();

                if (in_array(class_basename($model), ['Service', 'County'])) {
                    $query->where('is_active', true);
                }

                return $query->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name']);
            });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function countyOptions(): Collection
    {
        $counties = $this->options(County::class);

        if ($counties->isNotEmpty()) {
            return $counties;
        }

        return collect(LocationData::countyLinks())
            ->map(fn (array $county): object => (object) [
                'id' => $this->fallbackCountyValue($county['slug']),
                'name' => $county['name'],
            ]);
    }

    protected function townshipOptions(): Collection
    {
        if ($this->isFallbackCountySelection($this->countyId)) {
            return $this->fallbackTownshipOptions();
        }

        try {
            $townships = Township::query()
                ->when($this->countyId !== '' && ctype_digit($this->countyId), function ($query): void {
                    $query->where('county_id', $this->countyId);
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']);

            if ($townships->isNotEmpty()) {
                return $townships;
            }
        } catch (Throwable) {
            //
        }

        return $this->fallbackTownshipOptions();
    }

    protected function fallbackTownshipOptions(): Collection
    {
        $countySlug = $this->selectedCountySlug();

        if ($countySlug) {
            return collect(LocationData::townshipsForCounty($countySlug))
                ->map(fn (array $township): object => (object) [
                    'id' => $this->fallbackTownshipValue($countySlug, $township['slug']),
                    'name' => $township['name'],
                ]);
        }

        return collect(LocationData::all())
            ->flatMap(fn (array $county, string $slug) => collect($county['townships'])
                ->map(fn (array $township): object => (object) [
                    'id' => $this->fallbackTownshipValue($slug, $township['slug']),
                    'name' => "{$township['name']} ({$county['name']})",
                ]))
            ->values();
    }

    protected function selectedCountySlug(): ?string
    {
        $value = trim($this->countyId);

        if ($this->isFallbackCountySelection($value)) {
            return substr($value, strlen('fallback-county:'));
        }

        if ($value !== '' && ctype_digit($value)) {
            try {
                return County::query()->find((int) $value)?->slug;
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function resolveCountySelection(string $value): array
    {
        $value = trim($value);
        $selection = $this->emptyLocationSelection();

        if ($value === '') {
            return $selection;
        }

        if ($this->isFallbackCountySelection($value)) {
            $slug = substr($value, strlen('fallback-county:'));
            $county = LocationData::get($slug);

            if ($county) {
                return [
                    ...$selection,
                    'name' => $county['name'],
                    'slug' => $slug,
                    'source' => 'codebase',
                ];
            }

            return $selection;
        }

        if (ctype_digit($value)) {
            try {
                $county = County::query()->find((int) $value);

                if ($county) {
                    return [
                        ...$selection,
                        'id' => $county->id,
                        'name' => $county->name,
                        'slug' => $county->slug,
                        'source' => 'database',
                    ];
                }
            } catch (Throwable) {
                //
            }
        }

        return $selection;
    }

    protected function resolveTownshipSelection(string $value): array
    {
        $value = trim($value);
        $selection = $this->emptyLocationSelection();

        if ($value === '') {
            return $selection;
        }

        if (str_starts_with($value, 'fallback-township:')) {
            $parts = explode(':', $value, 3);

            if (count($parts) === 3) {
                [, $countySlug, $townshipSlug] = $parts;
                $county = LocationData::get($countySlug);
                $township = collect($county['townships'] ?? [])
                    ->first(fn (array $township): bool => $township['slug'] === $townshipSlug);

                if ($county && $township) {
                    return [
                        ...$selection,
                        'name' => $township['name'],
                        'slug' => $townshipSlug,
                        'source' => 'codebase',
                        'county_name' => $county['name'],
                        'county_slug' => $countySlug,
                    ];
                }
            }

            return $selection;
        }

        if (ctype_digit($value)) {
            try {
                $township = Township::query()->with('county')->find((int) $value);

                if ($township) {
                    return [
                        ...$selection,
                        'id' => $township->id,
                        'name' => $township->name,
                        'slug' => $township->slug,
                        'source' => 'database',
                        'county_id' => $township->county_id,
                        'county_name' => $township->county?->name,
                        'county_slug' => $township->county?->slug,
                    ];
                }
            } catch (Throwable) {
                //
            }
        }

        return $selection;
    }

    protected function emptyLocationSelection(): array
    {
        return [
            'id' => null,
            'name' => null,
            'slug' => null,
            'source' => null,
            'county_id' => null,
            'county_name' => null,
            'county_slug' => null,
        ];
    }

    protected function isFallbackCountySelection(string $value): bool
    {
        return str_starts_with($value, 'fallback-county:');
    }

    protected function fallbackCountyValue(string $countySlug): string
    {
        return "fallback-county:{$countySlug}";
    }

    protected function fallbackTownshipValue(string $countySlug, string $townshipSlug): string
    {
        return "fallback-township:{$countySlug}:{$townshipSlug}";
    }

    public function render()
    {
        return view('livewire.book-appointment', [
            'counties' => $this->countyOptions(),
            'services' => $this->options(Service::class),
            'townships' => $this->townshipOptions(),
            'usesGoogle' => $this->provider === AppointmentProviderManager::GOOGLE,
            'usesBookingPage' => $this->provider === AppointmentProviderManager::GOOGLE_BOOKING_PAGE,
            'bookingTimezone' => SiteSettings::appointmentTimezone(),
        ]);
    }
}
