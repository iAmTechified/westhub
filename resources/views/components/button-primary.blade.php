@props(['href' => null, 'variant' => 'primary'])

@php
$variants = [
    'primary' => 'bg-primary-100 hover:bg-primary-100/90 text-white',
    'white' => 'bg-white hover:bg-neutral-50 text-primary-300 shadow-xl',
];

$classes = 'inline-flex items-center justify-center px-8 py-3 text-base font-bold transition-all duration-300 rounded-full active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary-100/50 tracking-tight ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
