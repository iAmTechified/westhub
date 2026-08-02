@php
    $serviceValue = (string) $this->serviceId;
    $countyValue = (string) $this->countyId;
    $townshipValue = (string) $this->townshipId;
    $selectedService = collect($services)->first(fn ($service) => (string) $service->id === $serviceValue);
    $selectedCounty = collect($counties)->first(fn ($county) => (string) $county->id === $countyValue);
    $selectedTownship = collect($townships)->first(fn ($township) => (string) $township->id === $townshipValue);
@endphp

<div class="overflow-visible bg-white text-neutral-600">
    <div class="w-full flex items-start justify-between gap-4 border-b border-neutral-100 px-5 py-4 md:px-7">
        <div class="w-full flex flex-col items-center justify-center flex-grow">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-primary-100">WestHub Healthcare</p>
            <h2 class="mt-1 font-display text-2xl font-bold text-primary-300">Book Appointment</h2>
        </div>
        <button
            type="button"
            @click="$dispatch('close-appointment')"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-300 transition hover:bg-primary-100 hover:text-white"
            aria-label="Close appointment form"
        >
            <span class="text-2xl leading-none">&times;</span>
        </button>
    </div>

    @if ($submitted)
        <div class="grid gap-5 px-5 py-6 md:px-7">
            <div class="rounded-lg border border-primary-100/30 bg-primary-50 p-5">
                <p class="font-display text-xl font-bold text-primary-300">
                    @if ($calendlyScheduled || $calendlyStatus === 'scheduled')
                        Appointment scheduled
                    @elseif ($calendlyStatus === 'closed')
                        Calendly closed
                    @elseif ($calendlyStatus === 'failed')
                        Calendly did not open
                    @else
                        Request saved
                    @endif
                </p>
                <p class="mt-2 text-sm leading-6 text-neutral-600">
                    @if ($calendlyScheduled || $calendlyStatus === 'scheduled')
                        Calendly confirmed the booking. Your request has been updated in our appointment queue.
                    @elseif ($calendlyStatus === 'closed')
                        Calendly was closed before the booking finished. Your request is saved, and you can open Calendly again.
                    @elseif ($calendlyStatus === 'failed')
                        We could not open Calendly. Please try again or check that popups are allowed.
                    @else
                        Your details are in our appointment queue. Calendly should open so you can choose the final time.
                    @endif
                </p>
            </div>

            <div class="grid gap-4 rounded-lg bg-neutral-50 p-5 text-sm md:grid-cols-2">
                <div>
                    <p class="font-bold text-primary-300">Name</p>
                    <p class="mt-1">{{ $fullName }}</p>
                </div>
                <div>
                    <p class="font-bold text-primary-300">Email</p>
                    <p class="mt-1 break-all">{{ $email }}</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @if ($calendlyUrl)
                    <button
                        type="button"
                        data-calendly-trigger
                        data-calendly-url="{{ $calendlyUrl }}"
                        data-appointment-id="{{ $appointmentId }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-primary-100 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary-100/25 transition hover:bg-primary-300"
                    >
                        <x-icon-calendar class="h-5 w-5" />
                        Open Calendly
                    </button>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 sm:col-span-2">
                        Calendly is not configured yet. We saved the request for follow-up.
                    </div>
                @endif

                <button
                    type="button"
                    @click="$dispatch('close-appointment')"
                    class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-5 py-3 text-sm font-bold text-neutral-500 transition hover:border-primary-100 hover:text-primary-300"
                >
                    Close
                </button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="px-5 py-6 md:px-7">
            <div class="hidden">
                <label>
                    Leave this field empty
                    <input type="text" wire:model="honeypot" tabindex="-1" autocomplete="off">
                </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="appointment-full-name" class="mb-1 block text-sm font-bold text-neutral-500">Full Name</label>
                    <input
                        id="appointment-full-name"
                        type="text"
                        wire:model="fullName"
                        autocomplete="name"
                        class="w-full rounded-lg border-neutral-200 px-4 py-3 text-sm focus:border-primary-100 focus:ring-primary-100/30"
                    >
                    @error('fullName') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="appointment-email" class="mb-1 block text-sm font-bold text-neutral-500">Email</label>
                    <input
                        id="appointment-email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        class="w-full rounded-lg border-neutral-200 px-4 py-3 text-sm focus:border-primary-100 focus:ring-primary-100/30"
                    >
                    @error('email') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="appointment-phone" class="mb-1 block text-sm font-bold text-neutral-500">Phone</label>
                    <input
                        id="appointment-phone"
                        type="tel"
                        wire:model="phone"
                        autocomplete="tel"
                        class="w-full rounded-lg border-neutral-200 px-4 py-3 text-sm focus:border-primary-100 focus:ring-primary-100/30"
                    >
                    @error('phone') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="join-field">
                    <label class="mb-1 block text-sm font-bold text-neutral-500">Service</label>
                    <div x-data="{ open: false }" @click.away="open = false" class="join-custom-select" :class="{ 'is-open': open }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="join-select-button !h-[46px] !rounded-lg !px-4 !text-sm"
                            :class="{ 'border-primary-100 shadow-[0_0_0_3px_rgba(20,171,213,0.14)]': open }"
                        >
                            <span class="join-select-label">{{ $selectedService?->name ?? 'General consultation' }}</span>
                            <x-icon-chevron-down class="h-3 w-3 shrink-0 text-neutral-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open" x-cloak class="join-select-menu appointment-select-menu !z-[1200] !w-full">
                            <button type="button" @click="open = false" wire:click="$set('serviceId', '')" class="join-select-option {{ $serviceValue === '' ? 'is-selected' : '' }}">
                                General consultation
                            </button>
                            @foreach ($services as $service)
                                <button
                                    type="button"
                                    @click="open = false"
                                    wire:key="appointment-service-option-{{ $service->id }}"
                                    wire:click="$set('serviceId', @js((string) $service->id))"
                                    class="join-select-option {{ $serviceValue === (string) $service->id ? 'is-selected' : '' }}"
                                >
                                    {{ $service->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @error('serviceId') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="join-field">
                    <label class="mb-1 block text-sm font-bold text-neutral-500">County</label>
                    <div x-data="{ open: false }" @click.away="open = false" class="join-custom-select" :class="{ 'is-open': open }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="join-select-button !h-[46px] !rounded-lg !px-4 !text-sm"
                            :class="{ 'border-primary-100 shadow-[0_0_0_3px_rgba(20,171,213,0.14)]': open }"
                        >
                            <span class="join-select-label">{{ $selectedCounty?->name ?? 'Select County' }}</span>
                            <x-icon-chevron-down class="h-3 w-3 shrink-0 text-neutral-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open" x-cloak class="join-select-menu appointment-select-menu !z-[1200] !w-full">
                            <button type="button" @click="open = false" wire:click="selectCounty('')" class="join-select-option {{ $countyValue === '' ? 'is-selected' : '' }}">
                                Select County
                            </button>
                            @foreach ($counties as $county)
                                <button
                                    type="button"
                                    @click="open = false"
                                    wire:key="appointment-county-option-{{ str($county->id)->slug() }}"
                                    wire:click="selectCounty(@js((string) $county->id))"
                                    class="join-select-option {{ $countyValue === (string) $county->id ? 'is-selected' : '' }}"
                                >
                                    {{ $county->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @error('countyId') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="join-field">
                    <label class="mb-1 block text-sm font-bold text-neutral-500">City/Town</label>
                    <div
                        x-data="{ open: false, label: @js($selectedTownship?->name ?? 'Select City/Town') }"
                        @click.away="open = false"
                        x-on:livewire:navigated.window="label = @js($selectedTownship?->name ?? 'Select City/Town')"
                        class="join-custom-select"
                        :class="{ 'is-open': open }"
                    >
                        <button
                            type="button"
                            @click="open = !open"
                            class="join-select-button !h-[46px] !rounded-lg !px-4 !text-sm"
                            :class="{ 'border-primary-100 shadow-[0_0_0_3px_rgba(20,171,213,0.14)]': open }"
                            wire:loading.attr="disabled"
                            wire:target="selectCounty"
                        >
                            <span class="join-select-label" wire:loading.remove wire:target="selectCounty" x-text="label"></span>
                            <span class="join-select-label flex flex-row items-center gap-2" wire:loading.flex wire:target="selectCounty">
                                <svg class="h-3.5 w-3.5 animate-spin text-primary-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Loading...
                            </span>
                            <x-icon-chevron-down class="h-3 w-3 shrink-0 text-neutral-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="open" x-cloak class="join-select-menu appointment-select-menu !z-[1200] !w-full">
                            <button
                                type="button"
                                @click="open = false; label = 'Select City/Town'; $wire.set('townshipId', '')"
                                class="join-select-option {{ $townshipValue === '' ? 'is-selected' : '' }}"
                            >
                                Select City/Town
                            </button>
                            @foreach ($townships as $township)
                                <button
                                    type="button"
                                    @click="open = false; label = @js($township->name); $wire.set('townshipId', @js((string) $township->id))"
                                    wire:key="appointment-township-option-{{ str($township->id)->slug() }}"
                                    class="join-select-option {{ $townshipValue === (string) $township->id ? 'is-selected' : '' }}"
                                >
                                    {{ $township->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @error('townshipId') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            @if (! $calendlyUrl)
                <p class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                    Calendly is not configured yet. Requests will still be saved.
                </p>
            @endif

            <div class="mt-6 flex justify-center">
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary-100 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-primary-100/25 transition hover:bg-primary-300 disabled:opacity-60 sm:w-auto"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >
                    <x-icon-calendar class="h-5 w-5" />
                    <span wire:loading.remove wire:target="submit">Book</span>
                    <span wire:loading wire:target="submit">Saving...</span>
                </button>
            </div>
        </form>
    @endif
</div>
