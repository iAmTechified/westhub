@props([
    'placeholder' => 'Select an option',
])

@php
    $containerClass = trim('admin-custom-select relative '.$attributes->get('class', ''));
    $inputAttributes = $attributes->except('class');
@endphp

<div
    class="{{ $containerClass }}"
    x-data="westhubAdminSelect({ placeholder: @js($placeholder) })"
    x-on:keydown.escape.window.prevent.stop="close()"
>
    <select x-ref="source" class="hidden" tabindex="-1" aria-hidden="true">
        {{ $slot }}
    </select>

    <input type="hidden" x-ref="input" {{ $inputAttributes }}>

    <button
        type="button"
        class="admin-custom-select-trigger"
        x-ref="trigger"
        x-on:click="toggle()"
        x-bind:aria-expanded="open"
        x-bind:disabled="isDisabled()"
        x-bind:class="{ 'is-open': open }"
    >
        <span class="admin-custom-select-label" x-text="selectedLabel()"></span>
        <span aria-hidden="true" class="admin-custom-select-caret"></span>
    </button>

    <template x-teleport="body">
        <div
            class="admin-custom-select-teleport"
            x-cloak
            x-ref="menu"
            x-show="open"
            x-bind:style="teleportStyle"
            x-transition:enter="transition ease-out duration-120ms"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100ms"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <template x-for="option in options" :key="option.key">
                <button
                    type="button"
                    class="admin-custom-select-option"
                    x-bind:disabled="option.disabled"
                    x-bind:class="{ 'is-selected': selectedValue() === option.value }"
                    x-on:click="choose(option)"
                >
                    <span x-text="option.label"></span>
                </button>
            </template>
        </div>
    </template>
</div>
