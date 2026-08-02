@props([
    'placeholder' => 'Select date',
    'min' => null,
    'max' => null,
])

@php
    $containerClass = trim('admin-date-picker relative '.$attributes->get('class', ''));
    $inputAttributes = $attributes->except('class');
@endphp

<div
    class="{{ $containerClass }}"
    x-data="westhubAdminDatePicker({
        placeholder: @js($placeholder),
        min: @js($min),
        max: @js($max),
    })"
    x-on:keydown.escape.window.prevent.stop="open = false"
>
    <input type="hidden" x-ref="input" {{ $inputAttributes }}>

    <button
        type="button"
        class="admin-custom-select-trigger"
        x-on:click="toggle()"
        x-bind:aria-expanded="open"
        x-bind:disabled="isDisabled()"
    >
        <span class="admin-custom-select-label" x-text="formatDisplay(value)"></span>
        <span class="inline-flex items-center gap-2 text-admin-muted">
            <x-admin.icon name="calendar" class="h-4 w-4" />
            <span aria-hidden="true" class="admin-custom-select-caret"></span>
        </span>
    </button>

    <div
        class="admin-date-picker-panel"
        x-cloak
        x-show="open"
        x-on:click.outside="open = false"
        x-transition:enter="transition ease-out duration-120ms"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100ms"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="admin-date-picker-head">
            <button type="button" class="admin-date-picker-nav" x-on:click="moveMonth(-1)" aria-label="Previous month">&#8249;</button>
            <p class="admin-date-picker-month" x-text="monthLabel()"></p>
            <button type="button" class="admin-date-picker-nav" x-on:click="moveMonth(1)" aria-label="Next month">&#8250;</button>
        </div>

        <div class="admin-date-picker-weekdays">
            <template x-for="weekday in weekdayLabels" :key="weekday">
                <span x-text="weekday"></span>
            </template>
        </div>

        <div class="admin-date-picker-grid">
            <template x-for="day in days()" :key="day.key">
                <button
                    type="button"
                    class="admin-date-picker-day"
                    x-bind:disabled="!day.iso || day.disabled"
                    x-bind:class="{
                        'is-selected': isSelected(day.iso),
                        'is-today': isToday(day.iso),
                        'is-blank': !day.iso,
                    }"
                    x-on:click="selectDay(day)"
                    x-text="day.label"
                ></button>
            </template>
        </div>

        <div class="admin-date-picker-actions">
            <button type="button" class="admin-ghost-btn h-8 px-3 text-xs" x-on:click="selectToday()">Today</button>
            <button type="button" class="admin-ghost-btn h-8 px-3 text-xs" x-on:click="clear()">Clear</button>
        </div>
    </div>
</div>
