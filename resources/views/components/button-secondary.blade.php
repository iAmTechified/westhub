@props(['href' => null, 'variant' => 'outline'])

@php
$variantClasses = [
    'outline' => 'border border-primary-300 text-primary-300 hover:bg-primary-300 hover:text-white',
    'hero' => 'border border-white text-white bg-white/10 backdrop-blur-md hover:bg-white/20',
];

$classes = 'inline-flex items-center justify-center px-8 py-3 text-base font-medium transition-all duration-300 rounded-full active:scale-95 tracking-tight ' . ($variantClasses[$variant] ?? $variantClasses['outline']);
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
