<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\AppointmentEvent;
use App\Models\County;
use App\Models\Service;
use App\Models\Township;
use App\Support\LocationData;
use App\Support\SiteSettings;
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

    public function mount(): void
    {
        $this->calendlyUrl = SiteSettings::calendlyAppointmentUrl();
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
        $this->calendlyUrl = SiteSettings::calendlyAppointmentUrlFor($appointment);
        $this->submitted = true;
        $this->calendlyStatus = $this->calendlyUrl ? 'opening' : 'unconfigured';

        if ($this->calendlyUrl) {
            $this->dispatch('westhub-open-calendly', url: $this->calendlyUrl, appointmentId: $appointment->id);
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
        $meta = [
            'calendly_url' => SiteSettings::calendlyAppointmentUrl(),
            'calendly_integration' => 'javascript_embed',
            'submitted_from' => request()->fullUrl(),
            'timezone' => SiteSettings::appointmentTimezone(),
            'user_agent' => str(request()->userAgent() ?? '')->limit(500)->toString(),
        ];

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
        ]);
    }
}
