@php
    // Server-side gate: a paused or out-of-window campaign ships no markup at all.
    $promoConfig = $isLive ? [
        'delay' => $offer->delaySeconds * 1000,
        'scroll' => $offer->scrollPercent,
        'frequencyDays' => $offer->frequencyDays,
        'storageKey' => 'westhub_promo_' . \App\Support\PromoOffer::CAMPAIGN,
    ] : null;
@endphp

<div @class(['pointer-events-none' => ! $isLive])>
    @if($isLive)
        <div
            x-data="westhubPromoGate(@js($promoConfig))"
            x-on:promo-claimed.window="markClaimed($event.detail?.code)"
            x-on:keydown.escape.window="if (shown) dismiss()"
        >
            {{-- ================= Popup ================= --}}
            <div
                x-show="shown"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="promo-headline"
            >
                {{-- Backdrop --}}
                <div
                    class="absolute inset-0 bg-primary-300/60 backdrop-blur-[6px]"
                    x-on:click="dismiss()"
                    aria-hidden="true"
                ></div>

                {{-- Modal: the wrapper scales to fit short windows, so the panel itself never needs to scroll --}}
                <div class="relative w-full max-w-[980px]" x-init="westhubFitPanel($el)">
                <div
                    x-show="shown"
                    x-transition:enter="transition ease-out duration-300 delay-75"
                    x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95"
                    class="relative max-h-[92dvh] overflow-y-auto overflow-x-hidden rounded-t-[28px] sm:max-h-none sm:overflow-hidden sm:rounded-[14px] bg-white shadow-[0_24px_64px_-12px_rgba(28,63,120,0.45)] flex flex-col sm:flex-row"
                >
                    {{-- Drag handle (mobile) --}}
                    <div class="absolute left-1/2 top-2.5 z-20 h-1.5 w-11 -translate-x-1/2 rounded-full bg-white/60 sm:hidden" aria-hidden="true"></div>

                    {{-- ---------- Left / offer visual ---------- --}}
                    <div class="relative flex shrink-0 flex-col overflow-hidden bg-[linear-gradient(184deg,#14264F_22%,#0D4E74_68%,#0C7A66_100%)] sm:w-[400px]">
                        {{-- Mobile: full-bleed banner with a left-to-right scrim so the copy stays legible --}}
                        <img
                            src="{{ asset('assets/images/promo/free-month-care-wide.jpg') }}"
                            alt=""
                            aria-hidden="true"
                            loading="lazy"
                            decoding="async"
                            class="absolute inset-0 h-full w-full object-cover object-[70%_center] sm:hidden"
                        >
                        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(20,38,79,0.97)_0%,rgba(18,57,95,0.9)_42%,rgba(15,94,107,0.52)_72%,rgba(13,114,104,0.18)_100%)] sm:hidden" aria-hidden="true"></div>

                        {{-- Desktop: photo band across the top, fading into the panel gradient --}}
                        <div class="relative hidden h-[278px] shrink-0 overflow-hidden sm:block">
                            <img
                                src="{{ asset('assets/images/promo/free-month-care.jpg') }}"
                                alt="A WestHub nurse going through a care plan with a client at home"
                                loading="lazy"
                                decoding="async"
                                class="absolute inset-0 h-full w-full scale-[1.19] object-cover object-center"
                            >
                            <div class="absolute inset-x-0 bottom-0 h-[110px] bg-[linear-gradient(180deg,rgba(15,114,104,0)_0%,rgba(14,106,112,0.55)_40%,#11345C_72%)]" aria-hidden="true"></div>
                        </div>

                        <div class="relative z-10 flex flex-col gap-3 px-6 pb-5 pt-7 pr-28 sm:gap-4 sm:px-[34px] sm:pb-[26px] sm:pt-[18px] sm:pr-[34px]">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-[#34D399]"></span>
                                <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white sm:text-xs">WestHub Healthcare</span>
                            </div>

                            <div class="space-y-1">
                                <p class="font-display text-[34px] font-extrabold leading-[1.08] tracking-tight text-white sm:text-[44px]">
                                    {{ $offer->offerAmount }} <span class="text-[#34D399]">{{ $offer->offerHighlight }}</span>
                                </p>
                                <p class="max-w-[19rem] text-[13px] leading-snug text-white/90 sm:text-[15px]">{{ $offer->offerSubline }}</p>
                            </div>

                            @if($offer->includedServices !== [])
                                <ul class="mt-1 hidden space-y-2.5 sm:block">
                                    <li class="text-[11px] font-semibold uppercase tracking-[0.12em] text-white/70">What's included</li>
                                    @foreach($offer->includedServices as $included)
                                        <li class="flex items-center gap-3">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/20">
                                                <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                                    <path d="M2 6L5 9L10 3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                            <span class="text-[15px] font-medium text-white">{{ $included }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($offer->endsAtLabel())
                                <span class="mt-1 inline-flex w-fit items-center gap-2 rounded-full border border-white/30 bg-white/15 px-3.5 py-2 backdrop-blur-sm">
                                    <span class="h-2 w-2 rounded-full bg-[#34D399]"></span>
                                    <span class="text-[12px] font-semibold text-white sm:text-[13px]">Offer ends {{ $offer->endsAtLabel() }}</span>
                                </span>
                            @endif
                        </div>

                    </div>

                    {{-- ---------- Right / content ---------- --}}
                    <div class="relative flex min-w-0 flex-1 flex-col gap-4 px-6 pb-7 pt-5 sm:gap-[18px] sm:px-11 sm:pb-8 sm:pt-9">

                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#ECFDF5] px-3 py-1.5">
                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M3 9.5h14V18H3V9.5ZM1.8 6h16.4v3.5H1.8V6ZM10 6v12M10 6C7.6 1.8 4 3.2 5.2 6M10 6c2.4-4.2 6-2.8 4.8 0"
                                          stroke="#059669" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#047857] sm:text-xs">
                                    {{ $submitted ? "You're in" : $offer->eyebrow }}
                                </span>
                            </span>

                            <button
                                type="button"
                                x-on:click="dismiss()"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-300 transition hover:bg-primary-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-100/50"
                                aria-label="Close the offer"
                            >
                                <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                    <path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        @if(! $submitted)
                            {{-- ============ Claim form ============ --}}
                            <div class="space-y-2.5">
                                <h2 id="promo-headline" class="font-display text-[26px] font-bold leading-[1.18] tracking-tight text-primary-300 sm:text-[32px]">
                                    {{ $offer->headline }}
                                </h2>
                                <p class="text-sm leading-relaxed text-neutral-500 sm:text-[15px]">{{ $offer->body }}</p>
                            </div>

                            <form wire:submit="submit" class="space-y-3" novalidate>
                                <div class="hidden" aria-hidden="true">
                                    <label>Leave this field empty
                                        <input type="text" wire:model="honeypot" tabindex="-1" autocomplete="off">
                                    </label>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="promo-name" class="sr-only">Full name</label>
                                        <input
                                            id="promo-name"
                                            type="text"
                                            wire:model="fullName"
                                            placeholder="Full name"
                                            autocomplete="name"
                                            class="w-full rounded-full border border-neutral-200 px-5 py-3.5 text-[15px] text-neutral-600 placeholder:text-neutral-400 focus:border-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-100/20"
                                        >
                                        @error('fullName') <p class="mt-1 pl-4 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="promo-phone" class="sr-only">Phone number</label>
                                        <input
                                            id="promo-phone"
                                            type="tel"
                                            wire:model="phone"
                                            placeholder="Phone"
                                            autocomplete="tel"
                                            class="w-full rounded-full border border-neutral-200 px-5 py-3.5 text-[15px] text-neutral-600 placeholder:text-neutral-400 focus:border-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-100/20"
                                        >
                                        @error('phone') <p class="mt-1 pl-4 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="promo-email" class="sr-only">Email address</label>
                                    <input
                                        id="promo-email"
                                        type="email"
                                        wire:model="email"
                                        placeholder="Email address"
                                        autocomplete="email"
                                        class="w-full rounded-full border border-neutral-200 px-5 py-3.5 text-[15px] text-neutral-600 placeholder:text-neutral-400 focus:border-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-100/20"
                                    >
                                    @error('email') <p class="mt-1 pl-4 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                @if($services->isNotEmpty())
                                    <div>
                                        <label for="promo-service" class="sr-only">Service you are interested in</label>
                                        <select
                                            id="promo-service"
                                            wire:model="serviceId"
                                            class="w-full appearance-none rounded-full border border-neutral-200 bg-white bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20viewBox%3D%220%200%2012%2012%22%20fill%3D%22none%22%3E%3Cpath%20d%3D%22M1%201l5%205%205-5%22%20stroke%3D%22%231C3F78%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:12px_12px] bg-[right_1.35rem_center] bg-no-repeat px-5 py-3.5 pr-12 text-[15px] text-neutral-600 focus:border-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-100/20"
                                        >
                                            <option value="">Service you are interested in</option>
                                            @foreach($services as $service)
                                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('serviceId') <p class="mt-1 pl-4 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif

                                <div class="flex items-start gap-2.5 pl-1 pt-0.5">
                                    <input
                                        id="promo-consent"
                                        type="checkbox"
                                        wire:model="consent"
                                        class="mt-0.5 h-[18px] w-[18px] shrink-0 rounded-[5px] border-neutral-300 text-primary-100 focus:ring-primary-100/40"
                                    >
                                    <label for="promo-consent" class="text-xs leading-snug text-neutral-400 sm:text-[13px]">
                                        I agree to be contacted about this offer and accept the
                                        <a href="{{ route('privacy.policy') }}" class="text-primary-100 underline hover:text-primary-200">Privacy Policy</a>.
                                    </label>
                                </div>
                                @error('consent') <p class="-mt-1 pl-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                                <div class="flex flex-col items-center gap-3 pt-1">
                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="submit"
                                        class="inline-flex w-full items-center justify-center gap-2.5 rounded-full bg-primary-100 px-7 py-4 text-[15px] font-bold tracking-tight text-white shadow-lg shadow-primary-100/30 transition hover:bg-primary-100/90 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70 sm:text-base"
                                    >
                                        <span wire:loading.remove wire:target="submit">{{ $offer->ctaLabel }}</span>
                                        <span wire:loading wire:target="submit">Sending your voucher...</span>
                                        <svg wire:loading.remove wire:target="submit" class="h-3 w-3" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                            <path d="M1 6h10M6.5 1.5L11 6l-4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>

                                    <button type="button" x-on:click="dismiss()" class="text-[13px] font-medium text-neutral-400 underline transition hover:text-neutral-500 sm:text-sm">
                                        {{ $offer->dismissLabel }}
                                    </button>

                                    <p class="text-center text-[11px] leading-snug text-neutral-400 sm:text-xs">{{ $offer->finePrint }}</p>
                                </div>
                            </form>
                        @else
                            {{-- ============ Success ============ --}}
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-50">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2 12l7 7L22 5" stroke="#14ABD5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>

                            <div class="space-y-2.5">
                                <h2 id="promo-headline" class="font-display text-[26px] font-bold leading-[1.18] tracking-tight text-primary-300 sm:text-[32px]">
                                    @if($alreadyClaimed)
                                        You already have a free {{ \Illuminate\Support\Str::lower($offer->offerAmount) }}, {{ \Illuminate\Support\Str::of($fullName)->trim()->explode(' ')->first() }}.
                                    @else
                                        Your free {{ \Illuminate\Support\Str::lower($offer->offerAmount) }} is reserved, {{ \Illuminate\Support\Str::of($fullName)->trim()->explode(' ')->first() }}.
                                    @endif
                                </h2>
                                <p class="text-sm leading-relaxed text-neutral-500 sm:text-[15px]">
                                    @if($alreadyClaimed)
                                        This email already has a voucher, so we have shown you the original code rather than issuing a second one.
                                    @else
                                        We have emailed your voucher to <span class="font-semibold text-neutral-600">{{ $email }}</span>.
                                    @endif
                                    A care coordinator will call within one business day, or you can book a time now.
                                </p>
                            </div>

                            <div
                                x-data="{ copied: false, copy() { navigator.clipboard?.writeText(@js($voucherCode)).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000) }) } }"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-[20px] border border-dashed border-primary-100/40 bg-primary-50 px-5 py-4"
                            >
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-primary-200">Your voucher code</p>
                                    <p class="mt-0.5 break-all font-display text-[22px] font-extrabold tracking-wide text-primary-300 sm:text-[26px]">{{ $voucherCode }}</p>
                                </div>
                                <button
                                    type="button"
                                    x-on:click="copy()"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-2.5 text-sm font-semibold text-primary-300 transition hover:border-primary-100"
                                >
                                    <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                        <path d="M4 4v8h7V4H4ZM1 9V1h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span x-text="copied ? 'Copied' : 'Copy code'">Copy code</span>
                                </button>
                            </div>

                            <div class="flex flex-col items-center gap-3 pt-1">
                                <button
                                    type="button"
                                    wire:click="bookAppointment"
                                    class="inline-flex w-full items-center justify-center gap-2.5 rounded-full bg-primary-100 px-7 py-4 text-[15px] font-bold tracking-tight text-white shadow-lg shadow-primary-100/30 transition hover:bg-primary-100/90 active:scale-[0.99] sm:text-base"
                                >
                                    Book My First Appointment
                                    <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                        <path d="M1 6h10M6.5 1.5L11 6l-4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>

                                <button type="button" x-on:click="dismiss()" class="text-[13px] font-medium text-neutral-400 underline transition hover:text-neutral-500 sm:text-sm">
                                    Close and keep browsing
                                </button>

                                <p class="text-center text-[11px] leading-snug text-neutral-400 sm:text-xs">
                                    @if($voucherExpiresAt) Voucher valid until {{ $voucherExpiresAt }}. @endif
                                    Quote the code when booking or share it with your care coordinator.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
                </div>
            </div>

            {{-- ================= Sticky reminder after dismissal ================= --}}
            <div
                x-show="tabVisible"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="fixed bottom-5 right-4 z-[60] sm:bottom-6 sm:right-6"
            >
                <div class="flex items-center gap-3 rounded-full bg-primary-300 py-3 pl-3.5 pr-3 shadow-[0_12px_28px_-8px_rgba(28,63,120,0.5)]">
                    <button type="button" x-on:click="reopen()" class="flex items-center gap-3 text-left focus:outline-none">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#34D399]">
                            <svg class="h-[14px] w-[14px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M3 9.5h14V18H3V9.5ZM1.8 6h16.4v3.5H1.8V6ZM10 6v12M10 6C7.6 1.8 4 3.2 5.2 6M10 6c2.4-4.2 6-2.8 4.8 0"
                                      stroke="#14264F" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="pr-1">
                            <span class="block text-[13px] font-bold leading-tight text-white sm:text-sm">
                                <span x-show="!claimed">{{ $offer->offerAmount }} {{ strtolower($offer->offerHighlight) }} &mdash; still available</span>
                                <span x-show="claimed" x-cloak>Your voucher is ready</span>
                            </span>
                            <span class="block text-[11px] leading-tight text-white/70 sm:text-xs">
                                <span x-show="!claimed">{{ $offer->endsAtLabel() ? 'Claim before ' . $offer->endsAtLabel() : 'Tap to claim' }}</span>
                                <span x-show="claimed" x-cloak>Tap to book your first visit</span>
                            </span>
                        </span>
                    </button>
                    <button
                        type="button"
                        x-on:click="hideTabForSession()"
                        class="inline-flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25 focus:outline-none"
                        aria-label="Hide this reminder"
                    >
                        <svg class="h-2.5 w-2.5" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                            <path d="M1 1l8 8M9 1L1 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
