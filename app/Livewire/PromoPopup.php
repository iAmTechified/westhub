<?php

namespace App\Livewire;

use App\Models\PromoClaim;
use App\Models\Service;
use App\Services\Promotions\PromoClaimService;
use App\Support\PromoOffer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * The "2 Weeks Free Healthcare Services" popup.
 *
 * Rendered once in the site layout. Whether it is ever shown is decided in two
 * places: the server decides whether the campaign is live at all (so a paused
 * promo ships no markup), and the browser decides the timing and the
 * once-per-visitor frequency cap.
 */
class PromoPopup extends Component
{
    public bool $open = false;
    public bool $submitted = false;

    public string $fullName = '';
    public string $email = '';
    public string $phone = '';
    public string $serviceId = '';
    public bool $consent = false;
    public string $honeypot = '';

    public ?string $voucherCode = null;
    public ?string $voucherExpiresAt = null;
    public ?int $claimId = null;
    public bool $alreadyClaimed = false;

    protected PromoOffer $offerCache;

    public function offer(): PromoOffer
    {
        return $this->offerCache ??= PromoOffer::fromSettings();
    }

    public function open(): void
    {
        if (! $this->offer()->isLive()) {
            return;
        }

        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    /**
     * Hand off to the booking modal, carrying the voucher across.
     *
     * The booking form is rendered once in the layout inside an Alpine modal
     * that opens on the `open-appointment` window event, so the voucher and
     * prefill travel as a Livewire event and the modal is opened in the browser.
     */
    public function bookAppointment(): void
    {
        $this->open = false;

        $this->dispatch(
            'westhub-book-with-promo',
            promoCode: $this->voucherCode,
            prefillName: $this->fullName,
            prefillEmail: $this->email,
            prefillPhone: $this->phone,
            prefillServiceId: $this->serviceId,
        );

        $this->js("window.dispatchEvent(new CustomEvent('open-appointment'))");
    }

    public function submit(PromoClaimService $claims): void
    {
        $offer = $this->offer();

        if (! $offer->isLive()) {
            $this->addError('email', 'This offer has now closed.');

            return;
        }

        // Silently accept and do nothing: a bot filled the hidden field.
        if ($this->honeypot !== '') {
            $this->submitted = true;

            return;
        }

        $validated = $this->validate($this->rules());

        $throttleKey = 'promo-claim:' . sha1(mb_strtolower($validated['email']) . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', 'Too many attempts. Please try again in a few minutes.');

            return;
        }

        RateLimiter::hit($throttleKey, 900);

        $existing = $claims->existingClaimFor($validated['email']);

        $claim = $claims->claim([
            'full_name' => $validated['fullName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'service_id' => $validated['serviceId'] !== '' ? (int) $validated['serviceId'] : null,
            'consent' => $this->consent,
            'source_page' => $this->currentPath(),
            'meta' => [
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
                'referrer' => mb_substr((string) request()->headers->get('referer'), 0, 500),
            ],
        ], $offer);

        $this->alreadyClaimed = $existing !== null;
        $this->claimId = $claim->id;
        $this->voucherCode = $claim->voucher_code;
        $this->voucherExpiresAt = $claim->expires_at?->format('j M Y');
        $this->submitted = true;

        $this->dispatch('promo-claimed', code: $claim->voucher_code);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'serviceId' => ['nullable', 'string', Rule::in($this->services()->pluck('id')->map(fn ($id) => (string) $id)->push('')->all())],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'fullName.required' => 'Please tell us your name.',
            'email.required' => 'We need an email to send your voucher to.',
            'email.email' => 'That email address does not look right.',
            'consent.accepted' => 'Please agree to be contacted so we can send your voucher.',
        ];
    }

    /**
     * The service picker is a nicety, not a requirement. If the table is
     * unavailable the popup still works, just without the dropdown, rather than
     * taking down every page it is rendered on.
     */
    public function services(): Collection
    {
        return once(function (): Collection {
            try {
                return Service::query()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name']);
            } catch (Throwable $e) {
                report($e);

                return collect();
            }
        });
    }

    protected function currentPath(): string
    {
        $referer = (string) request()->headers->get('referer');

        return mb_substr($referer !== '' ? $referer : request()->fullUrl(), 0, 255);
    }

    #[On("promo-reset")]
    public function resetPromo(): void
    {
        $this->reset(['submitted', 'fullName', 'email', 'phone', 'serviceId', 'consent', 'voucherCode', 'claimId', 'alreadyClaimed']);
    }

    public function render()
    {
        $offer = $this->offer();

        return view('livewire.promo-popup', [
            'offer' => $offer,
            'isLive' => $offer->isLive(),
            'services' => $offer->isLive() ? $this->services() : collect(),
        ]);
    }
}
