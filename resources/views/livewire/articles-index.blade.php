<div 
    x-data="{ 
        activeCategory: '{{ $category }}', 
        isFiltering: false,
        filterTimeout: null,
        requestId: 0,

        async setCategory(slug) {
            // Immediate UI update for the active tab
            this.activeCategory = slug;
            this.isFiltering = true;
            
            // Increment request ID to track this specific call
            const currentId = ++this.requestId;

            // Clear any pending debounce timeout
            if (this.filterTimeout) clearTimeout(this.filterTimeout);

            // Debounce the server request to prevent spamming if user clicks rapidly
            this.filterTimeout = setTimeout(async () => {
                try {
                    await $wire.setCategory(slug);
                } finally {
                    // Only hide filtering state if this was the latest request
                    // This handles the 'last-one-wins' pattern for in-flight requests
                    if (currentId === this.requestId) {
                        this.isFiltering = false;
                    }
                }
            }, 180); // 180ms is a sweet spot for perceived responsiveness vs network efficiency
        }
    }" 
    class="relative"
>
    <x-articles.hero />

    <section class="py-16 lg:py-24">
        <div class="container px-4 mx-auto md:px-6">
            <!-- Tab Navigation -->
            <div class="mb-12 overflow-x-auto border-b border-neutral-100 scrollbar-hide">
                <div class="flex min-w-max items-center gap-6 md:gap-10">
                    @php
                        $tabs = collect([(object) ['name' => 'View All', 'slug' => 'all']])->merge($categories);
                    @endphp

                    @foreach($tabs as $tab)
                        @php
                            $href = $tab->slug === 'all'
                                ? route('articles.index')
                                : route('articles.index', ['category' => $tab->slug]);
                        @endphp

                        <a
                            href="{{ $href }}"
                            wire:key="article-tab-{{ $tab->slug }}"
                            @click.prevent="setCategory('{{ $tab->slug }}')"
                            class="relative inline-flex h-14 items-center text-sm font-bold tracking-tight transition-all duration-300"
                            :class="activeCategory === '{{ $tab->slug }}' ? 'text-primary-300' : 'text-neutral-400 hover:text-primary-200'"
                            aria-current="{{ $category === $tab->slug ? 'page' : 'false' }}"
                        >
                            {{ $tab->name }}
                            <span 
                                class="absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-primary-100 transition-all duration-300"
                                :class="activeCategory === '{{ $tab->slug }}' ? 'opacity-100' : 'opacity-0'"
                            ></span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-primary-100">Editorial</p>
                    <h2 class="mt-2 text-4xl font-bold text-primary-300 font-display md:text-5xl tracking-tight">
                        <template x-if="activeCategory === 'all'"><span>All Articles</span></template>
                        @foreach($categories as $cat)
                            <template x-if="activeCategory === '{{ $cat->slug }}'"><span>{{ $cat->name }}</span></template>
                        @endforeach
                    </h2>
                </div>

                @if($articles->total() > 0)
                    <div class="flex items-center gap-2 px-4 py-2 bg-neutral-50 rounded-full border border-neutral-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-100"></span>
                        <p class="text-xs font-bold text-neutral-500 uppercase tracking-wider">
                            {{ $articles->total() }} {{ Str::plural('article', $articles->total()) }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="relative min-h-[600px]">
                <!-- Skeleton Shimmer -->
                <div
                    x-show="isFiltering"
                    x-cloak
                    class="grid grid-cols-1 gap-10 md:grid-cols-2 md:gap-14"
                >
                    @for($index = 0; $index < 6; $index++)
                        <div class="overflow-hidden rounded-[2.5rem] bg-white border border-neutral-50">
                            <div class="p-3 pb-0">
                                <div class="article-shimmer aspect-[4/3] w-full rounded-[2rem]"></div>
                            </div>
                            <div class="space-y-6 p-8 md:p-10 md:pt-8">
                                <div class="article-shimmer h-8 w-4/5 rounded-xl"></div>
                                <div class="article-shimmer h-8 w-2/3 rounded-xl"></div>
                                <div class="space-y-3 pt-2">
                                    <div class="article-shimmer h-3 w-full rounded-full"></div>
                                    <div class="article-shimmer h-3 w-11/12 rounded-full"></div>
                                    <div class="article-shimmer h-3 w-4/5 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Livewire Loading State -->
                <div
                    wire:loading.delay
                    wire:target="gotoPage,nextPage,previousPage"
                    class="grid grid-cols-1 gap-10 md:grid-cols-2 md:gap-14"
                >
                    @for($index = 0; $index < 6; $index++)
                        <div class="overflow-hidden rounded-[2.5rem] bg-white border border-neutral-50">
                            <div class="p-3 pb-0">
                                <div class="article-shimmer aspect-[4/3] w-full rounded-[2rem]"></div>
                            </div>
                            <div class="space-y-6 p-8 md:p-10 md:pt-8">
                                <div class="article-shimmer h-8 w-4/5 rounded-xl"></div>
                                <div class="article-shimmer h-8 w-2/3 rounded-xl"></div>
                                <div class="space-y-3 pt-2">
                                    <div class="article-shimmer h-3 w-full rounded-full"></div>
                                    <div class="article-shimmer h-3 w-11/12 rounded-full"></div>
                                    <div class="article-shimmer h-3 w-4/5 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Articles Grid -->
                <div x-show="!isFiltering" wire:loading.remove.delay wire:target="gotoPage,nextPage,previousPage">
                    @if($articles->count() > 0)
                        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 md:gap-14">
                            @foreach($articles as $article)
                                <div wire:key="article-{{ $article->id }}">
                                    <x-articles.card :article="$article" />
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($articles->hasPages())
                            <nav class="mt-20 flex flex-col items-center justify-between gap-8 border-t border-neutral-100 pt-10 sm:flex-row" aria-label="Article pagination">
                                <p class="text-sm font-bold text-neutral-400 tracking-tight">
                                    Showing <span class="text-primary-300">{{ $articles->firstItem() }}</span> to <span class="text-primary-300">{{ $articles->lastItem() }}</span> of {{ $articles->total() }} results
                                </p>

                                <div class="inline-flex items-center gap-3">
                                    @php
                                        $currentPage = $articles->currentPage();
                                        $lastPage = $articles->lastPage();
                                        $visiblePages = collect(range(1, $lastPage))
                                            ->filter(fn ($page) => $page === 1 || $page === $lastPage || abs($page - $currentPage) <= 1)
                                            ->values();
                                        $previousVisiblePage = null;
                                    @endphp

                                    <button
                                        type="button"
                                        wire:click="previousPage"
                                        wire:loading.attr="disabled"
                                        @disabled($articles->onFirstPage())
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-neutral-100 bg-white text-primary-300 transition-all hover:border-primary-100 hover:text-primary-100 hover:shadow-lg hover:shadow-primary-100/10 disabled:cursor-not-allowed disabled:opacity-30"
                                        aria-label="Previous page"
                                    >
                                        <x-icon-chevron-left class="h-5 w-5" />
                                    </button>

                                    <div class="hidden items-center gap-2 md:flex">
                                        @foreach($visiblePages as $page)
                                            @if($previousVisiblePage && $page > $previousVisiblePage + 1)
                                                <span class="inline-flex h-12 w-8 items-center justify-center text-sm font-black text-neutral-300">...</span>
                                            @endif

                                            <button
                                                type="button"
                                                wire:key="article-page-{{ $page }}"
                                                wire:click="gotoPage({{ $page }})"
                                                wire:loading.attr="disabled"
                                                class="inline-flex h-12 min-w-[3rem] items-center justify-center rounded-2xl px-4 text-sm font-black transition-all {{ $page === $currentPage ? 'bg-primary-300 text-white shadow-xl shadow-primary-300/20' : 'border border-neutral-100 bg-white text-neutral-500 hover:border-primary-100 hover:text-primary-100 hover:shadow-lg hover:shadow-primary-100/10' }}"
                                                aria-current="{{ $page === $currentPage ? 'page' : 'false' }}"
                                            >
                                                {{ $page }}
                                            </button>

                                            @php $previousVisiblePage = $page; @endphp
                                        @endforeach
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="nextPage"
                                        wire:loading.attr="disabled"
                                        @disabled(! $articles->hasMorePages())
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-neutral-100 bg-white text-primary-300 transition-all hover:border-primary-100 hover:text-primary-100 hover:shadow-lg hover:shadow-primary-100/10 disabled:cursor-not-allowed disabled:opacity-30"
                                        aria-label="Next page"
                                    >
                                        <x-icon-chevron-right class="h-5 w-5" />
                                    </button>
                                </div>
                            </nav>
                        @endif
                    @else
                        <div class="flex min-h-[480px] items-center justify-center rounded-[3rem] border-2 border-dashed border-neutral-100 bg-neutral-50/30 px-6 text-center">
                            <div class="max-w-md">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-xl shadow-primary-100/10">
                                    <svg class="h-8 w-8 text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <h3 class="mt-8 text-2xl font-bold text-primary-300 font-display">No articles found</h3>
                                <p class="mt-4 text-sm leading-relaxed text-neutral-400">
                                    We couldn't find any articles in this category. Check back later or try a different category.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
