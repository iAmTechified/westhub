@props([
    'value' => '',
    'disabled' => false,
    'selected' => false,
])

@php
    $isDisabled = filter_var($disabled, FILTER_VALIDATE_BOOL) || $attributes->has('disabled');
    $isSelected = filter_var($selected, FILTER_VALIDATE_BOOL) || $attributes->has('selected');
@endphp

<option
    value="{{ $value }}"
    @disabled($isDisabled)
    @selected($isSelected)
    {{ $attributes->except(['value', 'disabled', 'selected']) }}
>
    {{ $slot }}
</option>
