@props([
    'name',
    'class' => 'h-5 w-5',
])

@switch($name)
    @case('dashboard')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3.75 4.75H10.5V11.5H3.75V4.75ZM13.5 4.75H20.25V8.75H13.5V4.75ZM13.5 11.75H20.25V19.25H13.5V11.75ZM3.75 14.5H10.5V19.25H3.75V14.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
        @break

    @case('article')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6C4 4.89543 4.89543 4 6 4Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 9H16M8 13H16M8 17H12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('sun')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 2.5V5M12 19V21.5M21.5 12H19M5 12H2.5M18.7 5.3L16.9 7.1M7.1 16.9L5.3 18.7M18.7 18.7L16.9 16.9M7.1 7.1L5.3 5.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('moon')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M20 14.2C19.3008 16.9484 16.8676 19 13.9 19C10.3892 19 7.5 16.1108 7.5 12.6C7.5 9.63235 9.55164 7.19916 12.3 6.5C11.8525 7.27272 11.6 8.17033 11.6 9.125C11.6 12.0368 13.9632 14.4 16.875 14.4C17.8297 14.4 18.7273 14.1475 19.5 13.7C19.6016 13.8649 19.7699 14.0533 20 14.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
        @break

    @case('profile')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M5 19.5C6.55886 16.6079 9.07976 15 12 15C14.9202 15 17.4411 16.6079 19 19.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('settings')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 9.2C10.4536 9.2 9.2 10.4536 9.2 12C9.2 13.5464 10.4536 14.8 12 14.8C13.5464 14.8 14.8 13.5464 14.8 12C14.8 10.4536 13.5464 9.2 12 9.2Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M19.4 12C19.4 11.5684 19.3643 11.1453 19.2966 10.7333L21 9.4L19.6 7L17.5 7.5C16.8495 6.95434 16.089 6.53192 15.26 6.26L14.8 4H12H9.2L8.74 6.26C7.91098 6.53192 7.15053 6.95434 6.5 7.5L4.4 7L3 9.4L4.70339 10.7333C4.63567 11.1453 4.6 11.5684 4.6 12C4.6 12.4316 4.63567 12.8547 4.70339 13.2667L3 14.6L4.4 17L6.5 16.5C7.15053 17.0457 7.91098 17.4681 8.74 17.74L9.2 20H12H14.8L15.26 17.74C16.089 17.4681 16.8495 17.0457 17.5 16.5L19.6 17L21 14.6L19.2966 13.2667C19.3643 12.8547 19.4 12.4316 19.4 12Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        </svg>
        @break

    @case('logout')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9.5 5H6C4.89543 5 4 5.89543 4 7V17C4 18.1046 4.89543 19 6 19H9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M13 8L17 12L13 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('team')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="8" cy="8.5" r="2.5" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="16" cy="7.5" r="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M3.8 19C4.8 16.6 6.8 15.2 9.2 15.2C11.6 15.2 13.6 16.6 14.6 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M14.2 18.6C15 17.2 16.3 16.4 17.8 16.4C19.1 16.4 20.2 17 21 18.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('search')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M16 16L20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('filter')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 6H20M7 12H17M10 18H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('sort')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 6V18M8 6L5.5 8.5M8 6L10.5 8.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 18V6M16 18L13.5 15.5M16 18L18.5 15.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('chevron-up')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 14L12 8L18 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('chevron-down')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 10L12 16L18 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('eye')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 12C4.8 8.8 8 7 12 7C16 7 19.2 8.8 21 12C19.2 15.2 16 17 12 17C8 17 4.8 15.2 3 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.8"/>
        </svg>
        @break

    @case('check')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12.5L9.2 16.5L19 7.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('x')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
        </svg>
        @break

    @case('mail')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="5.5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M4.5 7L12 12.2L19.5 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('phone')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6.8 4.8L9.5 7.2L8.2 9.6C8.8 11.2 9.9 12.4 11.5 13.1L13.8 11.7L16.2 14.4L14.5 17.2C13.9 18.2 12.7 18.7 11.6 18.4C7.9 17.4 4.6 14.1 3.6 10.4C3.3 9.3 3.8 8.1 4.8 7.5L6.8 4.8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        </svg>
        @break

    @case('briefcase')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="7.5" width="17" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9 7.5V6.5C9 5.67157 9.67157 5 10.5 5H13.5C14.3284 5 15 5.67157 15 6.5V7.5M3.5 12.5H20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('location')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 21C12 21 18 15.4 18 10.5C18 7.18629 15.3137 4.5 12 4.5C8.68629 4.5 6 7.18629 6 10.5C6 15.4 12 21 12 21Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <circle cx="12" cy="10.5" r="2.2" stroke="currentColor" stroke-width="1.6"/>
        </svg>
        @break

    @case('pulse')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 12H7L9.2 7.5L12.5 16.5L14.8 11.5H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="2.8" y="4.8" width="18.4" height="14.4" rx="3" stroke="currentColor" stroke-width="1.4" opacity="0.75"/>
        </svg>
        @break

    @case('calendar')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="5.5" width="17" height="15" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M7.5 3.8V7M16.5 3.8V7M3.5 9.5H20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('close')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
        </svg>
        @break

    @case('edit')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M11 4H4C2.89543 4 2 4.89543 2 6V20C2 21.1046 2.89543 22 4 22H18C19.1046 22 20 21.1046 20 20V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M18.5 2.5C19.3284 2.5 20 3.17157 20 4C20 4.82843 19.3284 5.5 18.5 5.5C17.6716 5.5 17 4.82843 17 4C17 3.17157 17.6716 2.5 18.5 2.5Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9 15L20 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('trash')
    @case('delete')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 6H21M19 6V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20V6M8 6V4C8 2.89543 8.89543 2 10 2H14C15.1046 2 16 2.89543 16 4V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M10 11V17M14 11V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('plus')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('menu')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('grid')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 4H10V10H4V4ZM14 4H20V10H14V4ZM4 14H10V20H4V14ZM14 14H20V20H14V14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
        @break

    @case('table')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="4.5" width="17" height="15" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M3.5 9.5H20.5M8.5 9.5V19.5M14.5 9.5V19.5" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        @break

    @case('list')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 7H19M8 12H19M8 17H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="5" cy="7" r="1" fill="currentColor"/>
            <circle cx="5" cy="12" r="1" fill="currentColor"/>
            <circle cx="5" cy="17" r="1" fill="currentColor"/>
        </svg>
        @break

    @case('cards')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="4" y="5" width="9" height="14" rx="1.8" stroke="currentColor" stroke-width="1.8"/>
            <rect x="11" y="5" width="9" height="14" rx="1.8" stroke="currentColor" stroke-width="1.8"/>
        </svg>
        @break

    @case('image')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="4.5" width="17" height="15" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="9" cy="10" r="1.8" stroke="currentColor" stroke-width="1.6"/>
            <path d="M5.5 17L10.5 12L13.5 15L16 12.5L18.5 17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('bold')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('italic')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <line x1="19" y1="4" x2="10" y2="4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="14" y1="20" x2="5" y2="20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="15" y1="4" x2="9" y2="20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('list-ordered')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <line x1="10" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="10" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="10" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M4 6h1v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M4 10h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('align-left')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <line x1="17" y1="10" x2="3" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="6" x2="3" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="14" x2="3" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="17" y1="18" x2="3" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('align-center')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <line x1="18" y1="10" x2="6" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="6" x2="3" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="14" x2="3" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="18" y1="18" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('align-right')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <line x1="21" y1="10" x2="7" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="6" x2="3" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="14" x2="3" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="21" y1="18" x2="7" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('link')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('info')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break

    @case('spinner')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        @break

    @case('refresh')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M20 6V11H15M4 18V13H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7.2 8.8C8.3 7.6 10 6.8 11.8 6.8C14.9 6.8 17.4 8.9 18 11.8M16.8 15.2C15.7 16.4 14 17.2 12.2 17.2C9.1 17.2 6.6 15.1 6 12.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break

    @case('lock')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="1.8"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="1.8"></path>
        </svg>
        @break

    @case('globe')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"></circle>
            <line x1="2" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="1.8"></line>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke="currentColor" stroke-width="1.8"></path>
        </svg>
        @break

    @case('save')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        @break

    @case('brush')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19.5 2.5a2.5 2.5 0 0 1 0 5L12 15l-4-1 1-4 7.5-7.5z" stroke="currentColor" stroke-width="1.8"></path>
            <path d="M7 14l-4 4v4h4l4-4" stroke="currentColor" stroke-width="1.8"></path>
        </svg>
        @break

    @default
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
        </svg>
@endswitch
