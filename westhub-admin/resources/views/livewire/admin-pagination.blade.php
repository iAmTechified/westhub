@php
    $currentPage = $paginator->currentPage();
    $lastPage = max(1, $paginator->lastPage());
    $startPage = max(1, $currentPage - 2);
    $endPage = min($lastPage, $currentPage + 2);

    if (($endPage - $startPage) < 4) {
        if ($startPage === 1) {
            $endPage = min($lastPage, $startPage + 4);
        } elseif ($endPage === $lastPage) {
            $startPage = max(1, $endPage - 4);
        }
    }
@endphp

<nav role="navigation" aria-label="Pagination Navigation" class="admin-pagination-shell flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-xs text-admin-muted uppercase tracking-widest hidden sm:block">
        @if($paginator->total() > 0)
            Showing
            <span class="font-bold text-admin-ink">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-bold text-admin-ink">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-bold text-admin-ink">{{ $paginator->total() }}</span>
            results
        @else
            No results
        @endif
    </p>

    <div
        class="relative flex flex-wrap items-center justify-start gap-1.5 sm:justify-end"
        wire:loading.class="opacity-75 pointer-events-none"
        wire:target="previousPage,nextPage,gotoPage"
    >
        <div
            class="admin-pagination-loading"
            wire:loading.flex
            wire:target="previousPage,nextPage,gotoPage"
            aria-live="polite"
        >
            <span class="tiny-orb-loader"></span>
            <span>Loading page...</span>
        </div>

        @if ($paginator->onFirstPage())
            <span class="admin-ghost-btn h-9 px-3 opacity-50 cursor-not-allowed inline-flex items-center gap-1.5">
                <x-admin.icon name="chevron-up" class="h-3.5 w-3.5 -rotate-90" />
                <span class="hidden sm:inline">Previous</span>
            </span>
        @else
            <button
                wire:click="previousPage"
                wire:loading.attr="disabled"
                wire:target="previousPage,nextPage,gotoPage"
                rel="prev"
                class="admin-ghost-btn h-9 px-3 inline-flex items-center gap-1.5"
            >
                <x-admin.icon name="chevron-up" class="h-3.5 w-3.5 -rotate-90" />
                <span class="hidden sm:inline">Previous</span>
            </button>
        @endif

        @if($startPage > 1)
            <button wire:click="gotoPage(1)" wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage" class="admin-ghost-btn h-9 min-w-[2.25rem] px-3" aria-label="Go to page 1">1</button>
            @if($startPage > 2)
                <span class="px-2 text-sm text-admin-muted">...</span>
            @endif
        @endif

        @for($page = $startPage; $page <= $endPage; $page++)
            @if ($page === $currentPage)
                <span class="admin-primary-btn h-9 min-w-[2.25rem] px-3" aria-current="page">{{ $page }}</span>
            @else
                <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage" class="admin-ghost-btn h-9 min-w-[2.25rem] px-3" aria-label="Go to page {{ $page }}">
                    {{ $page }}
                </button>
            @endif
        @endfor

        @if($endPage < $lastPage)
            @if($endPage < ($lastPage - 1))
                <span class="px-2 text-sm text-admin-muted">...</span>
            @endif
            <button wire:click="gotoPage({{ $lastPage }})" wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage" class="admin-ghost-btn h-9 min-w-[2.25rem] px-3" aria-label="Go to page {{ $lastPage }}">{{ $lastPage }}</button>
        @endif

        @if ($paginator->hasMorePages())
            <button
                wire:click="nextPage"
                wire:loading.attr="disabled"
                wire:target="previousPage,nextPage,gotoPage"
                rel="next"
                class="admin-ghost-btn h-9 px-3 inline-flex items-center gap-1.5"
            >
                <span class="hidden sm:inline">Next</span>
                <x-admin.icon name="chevron-down" class="h-3.5 w-3.5 -rotate-90" />
            </button>
        @else
            <span class="admin-ghost-btn h-9 px-3 opacity-50 cursor-not-allowed inline-flex items-center gap-1.5">
                <span class="hidden sm:inline">Next</span>
                <x-admin.icon name="chevron-down" class="h-3.5 w-3.5 -rotate-90" />
            </span>
        @endif
    </div>
</nav>
