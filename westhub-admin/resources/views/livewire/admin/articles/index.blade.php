@php
    $statusOptions = ['draft', 'published', 'archived', 'trashed'];
@endphp

<div
    class="space-y-4 relative"
    wire:init="loadData"
    x-data="{
        uiViewMode: '{{ $viewMode }}',
        viewPreferenceTimer: null,
        viewPreferenceRequestId: 0,
        inlineSequences: {},
        createArticleOpen: false,
        createCategoryOpen: false,
        editCategoryOpen: false,
        deleteCategoryOpen: false,
        isLoadingArticle: false,
        previewOpen: false,
        isLoadingPreview: false,
        deleteArticleOpen: false,
        statusTimer: null,
        statusRequestId: 0,
        categoryTimer: null,
        categoryRequestId: 0,
        init() {
            this.$watch('$wire.status', v => {
                if (this.statusRequestId === 0 || v === this.localStatus) {
                    this.localStatus = v;
                }
            });
            this.$watch('$wire.categoryId', v => {
                if (this.categoryRequestId === 0 || v === this.localCategoryId) {
                    this.localCategoryId = v;
                }
            });
            const localViewMode = window.localStorage.getItem('westhub:articles:view-mode');
            if (['table', 'cards', 'list', 'grid'].includes(localViewMode)) {
                this.uiViewMode = localViewMode;
            }
            window.addEventListener('category-created', () => { this.createCategoryOpen = false; });
            window.addEventListener('category-updated', () => { this.editCategoryOpen = false; });
            window.addEventListener('category-deleted', () => { this.deleteCategoryOpen = false; });
            window.addEventListener('article-deleted', () => { this.deleteArticleOpen = false; });
        },
        switchView(mode) {
            if (!['table', 'cards', 'list', 'grid'].includes(mode)) return;
            this.uiViewMode = mode;
            window.localStorage.setItem('westhub:articles:view-mode', mode);
            this.persistViewPreference(mode);
        },
        persistViewPreference(mode) {
            if (this.viewPreferenceTimer) {
                window.clearTimeout(this.viewPreferenceTimer);
            }
            const requestId = ++this.viewPreferenceRequestId;
            this.viewPreferenceTimer = window.setTimeout(async () => {
                await $wire.saveViewModePreference(mode);
                if (requestId !== this.viewPreferenceRequestId) return;
            }, 180);
        },
        nextInlineSequence(articleId, field) {
            const key = `${articleId}:${field}`;
            const current = this.inlineSequences[key] || 0;
            const next = current + 1;
            this.inlineSequences[key] = next;
            return next;
        },
        quickUpdate(articleId, field, value) {
            const sequence = this.nextInlineSequence(articleId, field);
            $wire.quickUpdate(articleId, field, value, sequence);
        },
        switchStatus(status) {
            if (this.localStatus === status) return;
            this.localStatus = status;
            
            if (this.statusTimer) clearTimeout(this.statusTimer);
            const rid = ++this.statusRequestId;
            this.statusTimer = setTimeout(async () => {
                await $wire.setStatus(status);
                if (rid === this.statusRequestId) {
                    this.statusTimer = null;
                }
            }, 250);
        },
        switchCategory(id) {
            if (this.localCategoryId === id) return;
            this.localCategoryId = id;
            
            if (this.categoryTimer) clearTimeout(this.categoryTimer);
            const rid = ++this.categoryRequestId;
            this.categoryTimer = setTimeout(async () => {
                await $wire.setCategory(id);
                if (rid === this.categoryRequestId) {
                    this.categoryTimer = null;
                }
            }, 250);
        }
    }"
>
    <div class="glass-card p-5 space-y-4 relative z-[60]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-admin-muted">Publishing</p>
                <h2 class="inline-flex items-center gap-2 text-2xl font-semibold">
                    <x-admin.icon name="article" class="h-5 w-5 text-primary-100" />
                    Article Control Center
                </h2>
            </div>
            <button
                type="button"
                class="admin-primary-btn gap-2"
                @click="createArticleOpen = true; isLoadingArticle = false; $wire.openCreateArticleModal()"
            >
                <x-admin.icon name="plus" class="h-4 w-4" />
                <span>Create Article</span>
            </button>
        </div>
    </div>

    <section class="glass-card p-5 space-y-3 relative z-[50]">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-lg font-semibold">Category Management</h3>
            <button type="button" class="admin-primary-btn gap-2" @click="createCategoryOpen = true; $wire.openCreateCategoryModal()">
                <x-admin.icon name="plus" class="h-4 w-4" />
                <span>New Category</span>
            </button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="admin-chip" :class="localCategoryId === null ? 'is-active' : ''" @click="switchCategory(null)">All Categories</button>
            @forelse($categories as $category)
                <div class="admin-chip inline-flex items-center gap-2" :class="localCategoryId === {{ $category->id }} ? 'is-active' : ''">
                    <button type="button" @click="switchCategory({{ $category->id }})">{{ $category->name }}</button>
                    <button type="button" class="opacity-70 hover:opacity-100 transition-opacity" @click="$wire.openEditCategoryModal({{ $category->id }}).then(() => editCategoryOpen = true)" title="Edit Category">
                        <x-admin.icon name="edit" class="h-3.5 w-3.5" />
                    </button>
                    <button type="button" class="opacity-70 hover:opacity-100 transition-opacity text-rose-400" @click="deleteCategoryOpen = true; $wire.openDeleteCategoryModal({{ $category->id }})" title="Delete Category">
                        <x-admin.icon name="trash" class="h-3.5 w-3.5" />
                    </button>
                </div>
            @empty
                <p class="text-sm text-admin-muted">No categories yet. Create one to organize articles.</p>
            @endforelse
        </div>
    </section>

    {{-- Status rail and compact controls --}}
    @if($readyToLoad && ! $loadError)
        <div class="glass-card p-2 space-y-2">
            <div class="admin-status-rail w-full" wire:ignore>
                <button type="button" class="admin-status-rail-item flex-1" :class="localStatus === 'draft' ? 'is-active' : ''" @click="switchStatus('draft')">
                    Draft
                </button>
                <button type="button" class="admin-status-rail-item flex-1" :class="localStatus === 'published' ? 'is-active' : ''" @click="switchStatus('published')">
                    Published
                </button>
                <button type="button" class="admin-status-rail-item flex-1" :class="localStatus === 'archived' ? 'is-active' : ''" @click="switchStatus('archived')">
                    Archived
                </button>
                <button type="button" class="admin-status-rail-item is-trash flex-1" :class="localStatus === 'trashed' ? 'is-active' : ''" @click="switchStatus('trashed')">
                    Trash
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-1 border-t border-admin-stroke/50">
                <div class="relative flex-1 max-w-[280px]">
                    <x-admin.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-admin-muted" />
                    <input type="text" wire:model.live.debounce.300ms="search" class="admin-input !pl-9 !h-9 !text-xs" placeholder="Search articles...">
                </div>

                <div class="flex items-center gap-1">
                    <x-admin.select wire:model.live="sortBy" class="w-40 !h-9 !text-xs" placeholder="Sort By">
                        <x-admin.option value="title">Title</x-admin.option>
                        <x-admin.option value="published_at">Published Date</x-admin.option>
                        <x-admin.option value="updated_at">Last Updated</x-admin.option>
                        <x-admin.option value="created_at">Created Date</x-admin.option>
                        <x-admin.option value="status">Status</x-admin.option>
                    </x-admin.select>
                    <button type="button" wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')" class="admin-icon-btn !h-9 !w-9" title="Toggle Direction">
                        <x-admin.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4" />
                    </button>
                </div>

                <button class="admin-icon-btn !h-9 !w-9" wire:click="retryLoading" type="button" wire:loading.attr="disabled" wire:target="retryLoading" title="Refresh list">
                    <x-admin.icon name="refresh" class="h-4 w-4" wire:loading.class="animate-spin" wire:target="retryLoading" />
                </button>

                <div class="h-6 w-px bg-admin-stroke mx-1"></div>

                <div class="flex bg-white/5 p-1 rounded-lg border border-admin-stroke gap-0.5">
                    <button type="button" class="p-1.5 rounded-md transition-all duration-200" :class="uiViewMode === 'table' ? 'bg-primary-500/30 text-primary-200 shadow-sm' : 'text-admin-muted hover:text-admin-ink hover:bg-white/5'" @click="switchView('table')" title="Table View">
                        <x-admin.icon name="table" class="h-4 w-4" />
                    </button>
                    <button type="button" class="p-1.5 rounded-md transition-all duration-200" :class="uiViewMode === 'cards' ? 'bg-primary-500/30 text-primary-200 shadow-sm' : 'text-admin-muted hover:text-admin-ink hover:bg-white/5'" @click="switchView('cards')" title="Cards View">
                        <x-admin.icon name="cards" class="h-4 w-4" />
                    </button>
                    <button type="button" class="p-1.5 rounded-md transition-all duration-200" :class="uiViewMode === 'list' ? 'bg-primary-500/30 text-primary-200 shadow-sm' : 'text-admin-muted hover:text-admin-ink hover:bg-white/5'" @click="switchView('list')" title="List View">
                        <x-admin.icon name="list" class="h-4 w-4" />
                    </button>
                    <button type="button" class="p-1.5 rounded-md transition-all duration-200" :class="uiViewMode === 'grid' ? 'bg-primary-500/30 text-primary-200 shadow-sm' : 'text-admin-muted hover:text-admin-ink hover:bg-white/5'" @click="switchView('grid')" title="Grid View">
                        <x-admin.icon name="grid" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="relative min-h-[400px]">
        {{-- Loading State: View-mode aware skeletons --}}
        <div wire:loading wire:target="search,status,sortBy,sortDirection,retryLoading,setStatus,loadData,setCategory" class="absolute inset-0 z-10 bg-admin-surface/50 backdrop-blur-[1px]">
            <div x-show="uiViewMode === 'table'" class="glass-card overflow-hidden">
                <div class="p-3 bg-white/5 flex gap-4">
                    <div class="admin-skeleton h-4 w-24"></div>
                    <div class="admin-skeleton h-4 w-24"></div>
                    <div class="admin-skeleton h-4 w-24"></div>
                </div>
                @foreach(range(1, 8) as $i)
                    <div class="p-4 border-t border-admin-stroke/50 flex items-center justify-between">
                        <div class="flex-1 space-y-2">
                            <div class="admin-skeleton h-4 w-2/3"></div>
                            <div class="admin-skeleton h-3 w-1/3 opacity-50"></div>
                        </div>
                        <div class="w-32 admin-skeleton h-8"></div>
                    </div>
                @endforeach
            </div>

            <div x-show="uiViewMode === 'cards'" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach(range(1, 6) as $i)
                    <div class="glass-card p-4 space-y-4">
                        <div class="admin-skeleton h-40 w-full rounded-xl"></div>
                        <div class="space-y-2">
                            <div class="admin-skeleton h-5 w-3/4"></div>
                            <div class="admin-skeleton h-4 w-1/2"></div>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1 admin-skeleton h-8"></div>
                            <div class="w-8 admin-skeleton h-8"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="uiViewMode === 'grid'" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach(range(1, 8) as $i)
                    <div class="glass-card p-4 space-y-3">
                        <div class="admin-skeleton h-28 w-full rounded-xl"></div>
                        <div class="admin-skeleton h-4 w-full"></div>
                        <div class="admin-skeleton h-3 w-2/3"></div>
                        <div class="admin-skeleton h-8 w-full mt-2"></div>
                    </div>
                @endforeach
            </div>

            <div x-show="uiViewMode === 'list'" class="glass-card p-4 space-y-4">
                @foreach(range(1, 8) as $i)
                    <div class="flex items-center gap-4">
                        <div class="flex-1 space-y-2">
                            <div class="admin-skeleton h-4 w-1/2"></div>
                            <div class="admin-skeleton h-3 w-1/3"></div>
                        </div>
                        <div class="w-36 admin-skeleton h-9"></div>
                        <div class="w-9 admin-skeleton h-9"></div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Content State --}}
        <div wire:loading.remove wire:target="search,status,sortBy,sortDirection,retryLoading,setStatus,loadData,setCategory">
            @if(! $readyToLoad)
                <div class="glass-card p-5 space-y-3">
                    <div class="admin-skeleton h-8 w-1/3"></div>
                    @foreach(range(1, 3) as $i)
                        <div class="admin-skeleton h-12"></div>
                    @endforeach
                </div>
            @elseif($loadError)
                <div class="admin-error-state">
                    <h3 class="text-xl font-semibold">We hit a loading problem</h3>
                    <p class="mt-2 text-admin-muted">{{ $loadErrorMessage }}</p>
                    <button class="admin-primary-btn mt-4" wire:click="retryLoading" type="button">Retry</button>
                </div>
            @elseif($articles->count() === 0)
                <div class="admin-empty-state">
                    <h3 class="text-xl font-semibold">{{ $status === 'trashed' ? 'Trash is empty' : 'No articles found' }}</h3>
                    <p class="mt-2 text-admin-muted">{{ $status === 'trashed' ? 'Deleted articles will show here.' : 'Try a different search/filter, or create a new article to get started.' }}</p>
                    @if($status === 'trashed')
                        <button type="button" class="admin-ghost-btn mt-4 inline-flex gap-2" wire:click="setStatus('published')" @click="localStatus = 'published'">
                            <x-admin.icon name="chevron-left" class="h-4 w-4" />
                            Back to Articles
                        </button>
                    @else
                        <button type="button" class="admin-primary-btn mt-4 inline-flex" @click="createArticleOpen = true; isLoadingArticle = false; $wire.openCreateArticleModal()">Create your first article</button>
                    @endif
                </div>
            @else
                {{-- Actual Article Content (Tables, Cards, etc.) --}}
                <div x-show="uiViewMode === 'table'">
                    <div class="glass-card relative z-[95] overflow-visible">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1000px] text-sm">
                                <thead class="bg-white/5 text-admin-muted">
                                    <tr>
                                        <th class="text-left p-3">Headline</th>
                                        <th class="text-left p-3">Slug</th>
                                        <th class="text-left p-3">Category</th>
                                        <th class="text-left p-3">Status</th>
                                        <th class="text-left p-3">Updated</th>
                                        <th class="text-right p-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($articles as $article)
                                        <tr class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms]">
                                            <td class="p-3">
                                                <input
                                                    type="text"
                                                    class="w-full bg-transparent border-0 px-0 py-1 text-sm font-medium text-admin-ink focus:outline-none focus:ring-0"
                                                    value="{{ $article->title }}"
                                                    @blur="quickUpdate({{ $article->id }}, 'title', $event.target.value)"
                                                    @keydown.enter.prevent="$event.target.blur()"
                                                >
                                            </td>
                                            <td class="p-3 text-xs text-admin-muted">{{ $article->slug ?: '-' }}</td>
                                            <td class="p-3">{{ $article->category?->name ?? 'Uncategorized' }}</td>
                                            <td class="p-3">
                                                <x-admin.select class="w-[150px]" value="{{ $article->status }}" placeholder="Status" @change="quickUpdate({{ $article->id }}, 'status', $event.target.value)">
                                                    @foreach($statusOptions as $state)
                                                        <x-admin.option value="{{ $state }}" :selected="$article->status === $state">{{ str($state)->replace('_', ' ')->title() }}</x-admin.option>
                                                    @endforeach
                                                </x-admin.select>
                                            </td>
                                            <td class="p-3 text-xs text-admin-muted">{{ optional($article->updated_at)->diffForHumans() }}</td>
                                            <td class="p-3 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <button @click="previewOpen = true; isLoadingPreview = true; $wire.openPreview({{ $article->id }}).then(() => isLoadingPreview = false)" class="admin-icon-btn" title="Preview">
                                                        <x-admin.icon name="eye" class="h-4 w-4" />
                                                    </button>
                                                    @if($article->trashed())
                                                        <button type="button" class="admin-icon-btn text-emerald-300" wire:click="restoreArticle({{ $article->id }})" title="Restore">
                                                            <x-admin.icon name="refresh" class="h-4 w-4" />
                                                        </button>
                                                        <button type="button" class="admin-icon-btn text-rose-400" wire:click="forceDeleteArticle({{ $article->id }})" title="Delete Permanently">
                                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                                        </button>
                                                    @else
                                                        <button type="button" class="admin-icon-btn" @click="createArticleOpen = true; isLoadingArticle = true; $wire.openEditArticleModal({{ $article->id }}).then(() => isLoadingArticle = false)" title="Edit">
                                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                                        </button>
                                                        <button type="button" class="admin-icon-btn text-rose-400" @click="deleteArticleOpen = true; $wire.confirmDeleteArticle({{ $article->id }})" title="Delete">
                                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div x-show="uiViewMode === 'cards'">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($articles as $article)
                            <article class="glass-card p-4 space-y-3 lift-on-hover">
                                @if($article->headline_image_path)
                                    <img src="{{ $article->headlineImageUrl() }}" alt="{{ $article->title }}" class="rounded-xl border border-admin-stroke w-full h-40 object-cover">
                                @endif
                                <input
                                    type="text"
                                    class="w-full bg-transparent border-0 px-0 py-1 text-base font-semibold text-admin-ink focus:outline-none focus:ring-0"
                                    value="{{ $article->title }}"
                                    @blur="quickUpdate({{ $article->id }}, 'title', $event.target.value)"
                                    @keydown.enter.prevent="$event.target.blur()"
                                >
                                <p class="text-xs text-admin-muted">/{{ $article->slug ?: '-' }}</p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-admin-muted">
                                    <p>Category: <span class="text-admin-ink">{{ $article->category?->name ?? 'Uncategorized' }}</span></p>
                                    <p>Updated: <span class="text-admin-ink">{{ optional($article->updated_at)->diffForHumans() }}</span></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-admin.select class="flex-1" value="{{ $article->status }}" placeholder="Status" @change="quickUpdate({{ $article->id }}, 'status', $event.target.value)">
                                        @foreach($statusOptions as $state)
                                            <x-admin.option value="{{ $state }}" :selected="$article->status === $state">{{ str($state)->replace('_', ' ')->title() }}</x-admin.option>
                                        @endforeach
                                    </x-admin.select>
                                    <button @click="previewOpen = true; isLoadingPreview = true; $wire.openPreview({{ $article->id }}).then(() => isLoadingPreview = false)" class="admin-icon-btn" title="Preview">
                                        <x-admin.icon name="eye" class="h-4 w-4" />
                                    </button>
                                    @if($article->trashed())
                                        <button type="button" class="admin-icon-btn text-emerald-300" wire:click="restoreArticle({{ $article->id }})" title="Restore">
                                            <x-admin.icon name="refresh" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="admin-icon-btn text-rose-400" wire:click="forceDeleteArticle({{ $article->id }})" title="Delete Permanently">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button type="button" class="admin-icon-btn" @click="createArticleOpen = true; isLoadingArticle = true; $wire.openEditArticleModal({{ $article->id }}).then(() => isLoadingArticle = false)" title="Edit">
                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="admin-icon-btn text-rose-400" @click="deleteArticleOpen = true; $wire.confirmDeleteArticle({{ $article->id }})" title="Delete">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div x-show="uiViewMode === 'grid'">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($articles as $article)
                            <article class="glass-card p-4 space-y-2 lift-on-hover">
                                @if($article->headline_image_path)
                                    <img src="{{ $article->headlineImageUrl() }}" alt="{{ $article->title }}" class="rounded-xl border border-admin-stroke w-full h-28 object-cover">
                                @endif
                                <input
                                    type="text"
                                    class="w-full bg-transparent border-0 px-0 py-1 text-sm font-semibold text-admin-ink focus:outline-none focus:ring-0"
                                    value="{{ $article->title }}"
                                    @blur="quickUpdate({{ $article->id }}, 'title', $event.target.value)"
                                    @keydown.enter.prevent="$event.target.blur()"
                                >
                                <p class="text-[11px] text-admin-muted truncate">/{{ $article->slug ?: '-' }}</p>
                                <p class="text-[11px] text-admin-muted truncate">{{ $article->category?->name ?? 'Uncategorized' }}</p>
                                <p class="text-[11px] text-admin-muted">{{ optional($article->updated_at)->diffForHumans() }}</p>
                                <x-admin.select value="{{ $article->status }}" placeholder="Status" @change="quickUpdate({{ $article->id }}, 'status', $event.target.value)">
                                    @foreach($statusOptions as $state)
                                        <x-admin.option value="{{ $state }}" :selected="$article->status === $state">{{ str($state)->replace('_', ' ')->title() }}</x-admin.option>
                                    @endforeach
                                </x-admin.select>
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="previewOpen = true; isLoadingPreview = true; $wire.openPreview({{ $article->id }}).then(() => isLoadingPreview = false)" class="admin-icon-btn" title="Preview">
                                        <x-admin.icon name="eye" class="h-4 w-4" />
                                    </button>
                                    @if($article->trashed())
                                        <button type="button" class="admin-icon-btn text-emerald-300" wire:click="restoreArticle({{ $article->id }})" title="Restore">
                                            <x-admin.icon name="refresh" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="admin-icon-btn text-rose-400" wire:click="forceDeleteArticle({{ $article->id }})" title="Delete Permanently">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button type="button" class="admin-icon-btn" @click="createArticleOpen = true; isLoadingArticle = true; $wire.openEditArticleModal({{ $article->id }}).then(() => isLoadingArticle = false)" title="Edit">
                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="admin-icon-btn text-rose-400" @click="deleteArticleOpen = true; $wire.confirmDeleteArticle({{ $article->id }})" title="Delete">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div x-show="uiViewMode === 'list'">
                    <div class="glass-card p-4 space-y-2">
                        @foreach($articles as $article)
                            <div class="admin-row-item">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <input
                                        type="text"
                                        class="w-full bg-transparent border-0 px-0 py-1 text-sm font-semibold text-admin-ink focus:outline-none focus:ring-0"
                                        value="{{ $article->title }}"
                                        @blur="quickUpdate({{ $article->id }}, 'title', $event.target.value)"
                                        @keydown.enter.prevent="$event.target.blur()"
                                    >
                                    <p class="text-xs text-admin-muted truncate">/{{ $article->slug ?: '-' }}</p>
                                    <p class="text-xs text-admin-muted truncate">{{ $article->category?->name ?? 'Uncategorized' }} · {{ optional($article->updated_at)->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-admin.select class="w-[145px]" value="{{ $article->status }}" placeholder="Status" @change="quickUpdate({{ $article->id }}, 'status', $event.target.value)">
                                        @foreach($statusOptions as $state)
                                            <x-admin.option value="{{ $state }}" :selected="$article->status === $state">{{ str($state)->replace('_', ' ')->title() }}</x-admin.option>
                                        @endforeach
                                    </x-admin.select>
                                    <button @click="previewOpen = true; isLoadingPreview = true; $wire.openPreview({{ $article->id }}).then(() => isLoadingPreview = false)" class="admin-icon-btn" title="Preview">
                                        <x-admin.icon name="eye" class="h-4 w-4" />
                                    </button>
                                    @if($article->trashed())
                                        <button type="button" class="admin-icon-btn text-emerald-300" wire:click="restoreArticle({{ $article->id }})" title="Restore">
                                            <x-admin.icon name="refresh" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="admin-icon-btn text-rose-400" wire:click="forceDeleteArticle({{ $article->id }})" title="Delete Permanently">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button type="button" class="admin-icon-btn" @click="createArticleOpen = true; isLoadingArticle = true; $wire.openEditArticleModal({{ $article->id }}).then(() => isLoadingArticle = false)" title="Edit">
                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="admin-icon-btn text-rose-400" @click="deleteArticleOpen = true; $wire.confirmDeleteArticle({{ $article->id }})" title="Delete">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="pt-2">
            {{ $articles->onEachSide(1)->links('livewire.admin-pagination') }}
        </div>
    </div>

    <div x-show="previewOpen" class="admin-modal-backdrop" @click="previewOpen = false; $wire.closePreview()" x-cloak></div>
    <aside x-show="previewOpen" class="admin-modal-panel max-h-[80vh] overflow-auto" x-cloak>
        <div x-show="isLoadingPreview" class="flex flex-col gap-3">
            <div class="admin-skeleton h-8 w-1/2"></div>
            <div class="admin-skeleton h-16"></div>
            <div class="admin-skeleton h-32"></div>
        </div>
        <div x-show="!isLoadingPreview">
            @if($previewArticle)
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Quick Preview</h3>
                    <button @click="previewOpen = false; $wire.closePreview()" class="admin-ghost-btn">Close</button>
                </div>
                <h4 class="mt-3 text-xl font-semibold">{{ $previewArticle->title }}</h4>
                <p class="mt-1 text-xs text-admin-muted">/{{ $previewArticle->slug ?: '-' }}</p>
                <p class="mt-2 text-admin-muted">{{ $previewArticle->excerpt }}</p>
                @if($previewArticle->headline_image_path)
                    <img src="{{ $previewArticle->headlineImageUrl() }}" alt="{{ $previewArticle->title }}" class="mt-3 rounded-xl border border-admin-stroke w-full h-52 object-cover">
                @endif
                <div class="mt-4 prose prose-invert max-w-none">{!! str($previewArticle->body)->limit(1200) !!}</div>
            @endif
        </div>
    </aside>

    <div x-show="createCategoryOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="createCategoryOpen = false; $wire.closeCategoryModals()"></div>
        <div class="admin-modal-panel">
            <h3 class="text-xl font-semibold">Create Category</h3>
            <div class="mt-4 space-y-3">
                <input class="admin-input" wire:model="categoryName" placeholder="Category name">
                @error('categoryName') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                <textarea class="admin-input min-h-20" wire:model="categoryDescription" placeholder="Category description (optional)"></textarea>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button class="admin-ghost-btn" type="button" @click="createCategoryOpen = false; $wire.closeCategoryModals()" wire:loading.attr="disabled" wire:target="createCategory">Cancel</button>
                <button class="admin-primary-btn" wire:click="createCategory" wire:loading.attr="disabled" wire:target="createCategory" type="button">
                    <span wire:loading.remove wire:target="createCategory">Create</span>
                    <span wire:loading wire:target="createCategory">Creating...</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="editCategoryOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="editCategoryOpen = false; $wire.closeCategoryModals()"></div>
        <div class="admin-modal-panel">
            <h3 class="text-xl font-semibold">Edit Category</h3>
            <div class="mt-4 space-y-3">
                <input class="admin-input" wire:model.live="categoryName" wire:key="category-name-{{ $editingCategoryId }}" placeholder="Category name">
                @error('categoryName') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                <textarea class="admin-input min-h-20" wire:model.live="categoryDescription" wire:key="category-desc-{{ $editingCategoryId }}" placeholder="Category description (optional)"></textarea>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button class="admin-ghost-btn" type="button" @click="editCategoryOpen = false; $wire.closeCategoryModals()" wire:loading.attr="disabled" wire:target="updateCategory">Cancel</button>
                <button class="admin-primary-btn" wire:click="updateCategory" wire:loading.attr="disabled" wire:target="updateCategory" type="button">
                    <span wire:loading.remove wire:target="updateCategory">Save Changes</span>
                    <span wire:loading wire:target="updateCategory">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="deleteCategoryOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="deleteCategoryOpen = false; $wire.closeCategoryModals()"></div>
        <div class="admin-modal-panel max-w-md">
            <h3 class="text-xl font-semibold">Delete Category</h3>
            <p class="mt-2 text-sm text-admin-muted">This action cannot be undone. Articles in this category will be uncategorized.</p>
            <div class="mt-4 flex justify-end gap-2">
                <button class="admin-ghost-btn" type="button" @click="deleteCategoryOpen = false; $wire.closeCategoryModals()" wire:loading.attr="disabled" wire:target="deleteCategory">Cancel</button>
                <button class="admin-primary-btn" wire:click="deleteCategory" wire:loading.attr="disabled" wire:target="deleteCategory" type="button">
                    <span wire:loading.remove wire:target="deleteCategory">Delete</span>
                    <span wire:loading wire:target="deleteCategory">Deleting...</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="deleteArticleOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="deleteArticleOpen = false"></div>
        <div class="admin-modal-panel max-w-md">
            <h3 class="text-xl font-semibold">Delete Article</h3>
            <p class="mt-2 text-sm text-admin-muted">Are you sure you want to move this article to trash? It can be restored later or permanently deleted from the trash filter.</p>
            <div class="mt-4 flex justify-end gap-2">
                <button class="admin-ghost-btn" type="button" @click="deleteArticleOpen = false" wire:loading.attr="disabled" wire:target="deleteArticle">Cancel</button>
                <button class="admin-primary-btn !bg-rose-500/20 !border-rose-500/50 !text-rose-300" wire:click="deleteArticle" wire:loading.attr="disabled" wire:target="deleteArticle" type="button">
                    <span wire:loading.remove wire:target="deleteArticle">Move to Trash</span>
                    <span wire:loading wire:target="deleteArticle">Moving...</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="createArticleOpen" x-cloak>
        <div class="admin-modal-backdrop" @click="createArticleOpen = false; $wire.closeCreateArticleModal()"></div>
        <div class="admin-modal-panel !top-[4vh] !left-1/2 !w-[min(1250px,95vw)] !max-w-none !max-h-[92vh] !translate-x-[-50%] !translate-y-0 overflow-auto p-0">
            <div x-show="isLoadingArticle" class="p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="admin-skeleton h-8 w-1/3"></div>
                    <div class="admin-skeleton h-10 w-24"></div>
                </div>
                <div class="space-y-4">
                    <div class="admin-skeleton h-12 w-full"></div>
                    <div class="admin-skeleton h-24 w-full"></div>
                    <div class="admin-skeleton h-64 w-full"></div>
                </div>
            </div>
            <div x-show="!isLoadingArticle">
                <div class="p-4 border-b border-admin-stroke flex items-center justify-between sticky top-0 bg-admin-surface z-10">
                    <h3 class="text-lg font-semibold">{{ $editingArticleId ? 'Edit Article' : 'Create Article' }}</h3>
                    <button type="button" class="admin-ghost-btn" @click="createArticleOpen = false; $wire.closeCreateArticleModal()">Close</button>
                </div>
                <div class="p-4">
                    <livewire:admin.articles.studio :article="$modalArticle" :embedded="true" :key="'article-studio-'.$createArticleModalKey.'-'.($editingArticleId ?? 'new')" />
                </div>
            </div>
        </div>
    </div>
</div>
