<div class="space-y-6" wire:init="loadData" 
    x-data="{ 
        itemModalOpen: $wire.entangle('showItemModal'),
        isLoadingItem: false, 
        deleteModalOpen: false, 
        deleteItemTitle: '', 
        bulkDeleteModalOpen: false, 
        activeTab: '{{ $status }}', 
        activeCategory: {{ $categoryId ?? 'null' }},
        
        {{-- Selection State --}}
        selectedIds: [],
        {{-- Read live from the component so it tracks paging/filtering (Alpine state survives morphs). --}}
        get allVisibleIds() { return (this.$wire.pageItemIds || []).map(id => String(id)); },

        init() {
            {{-- Clear the selection whenever the visible page of items changes (paging, filters, search, sort). --}}
            this.$watch('$wire.pageItemIds', () => { this.selectedIds = []; });
        },

        isSelected(id) { return this.selectedIds.includes(String(id)); },
        
        toggleSelect(id) {
            id = String(id);
            if (this.isSelected(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds.push(id);
            }
        },
        
        toggleSelectAll() {
            if (this.selectedIds.length === this.allVisibleIds.length && this.allVisibleIds.length > 0) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.allVisibleIds.map(id => String(id));
            }
        },
        
        isAllSelected() {
            return this.allVisibleIds.length > 0 && this.allVisibleIds.every(id => this.selectedIds.includes(String(id)));
        },

        setTab(tab) { this.activeTab = tab; $wire.setStatus(tab).then(() => { this.selectedIds = []; }); },
        setCat(id) { this.activeCategory = id; $wire.setCategory(id).then(() => { this.selectedIds = []; }); }
    }"
    x-on:gallery-selection-reset.window="selectedIds = []"
>
    
    {{-- Header & View Modes --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-admin-muted">Content Studio</p>
            <h2 class="inline-flex items-center gap-2 text-3xl font-bold tracking-tight">
                <x-admin.icon name="image" class="h-6 w-6 text-primary-400" />
                Gallery Studio
            </h2>
        </div>
        <div class="flex items-center gap-2 bg-white/5 p-1 rounded-xl border border-admin-stroke">
            <button type="button" class="admin-chip !h-9 !px-3 {{ $viewMode === 'masonry' ? 'is-active' : '' }}" wire:click="setViewMode('masonry')" title="Masonry View">
                <x-admin.icon name="cards" class="h-4 w-4" />
            </button>
            <button type="button" class="admin-chip !h-9 !px-3 {{ $viewMode === 'grid' ? 'is-active' : '' }}" wire:click="setViewMode('grid')" title="Grid View">
                <x-admin.icon name="grid" class="h-4 w-4" />
            </button>
            <button type="button" class="admin-chip !h-9 !px-3 {{ $viewMode === 'list' ? 'is-active' : '' }}" wire:click="setViewMode('list')" title="List View">
                <x-admin.icon name="list" class="h-4 w-4" />
            </button>
            <div class="w-px h-4 bg-admin-stroke mx-1"></div>
            <button type="button" @click="itemModalOpen = true; isLoadingItem = true; $wire.openCreateModal().then(() => isLoadingItem = false)" class="admin-primary-btn !h-9 !px-4">
                <x-admin.icon name="plus" class="h-4 w-4 mr-2" />
                Add Media
            </button>
        </div>
    </div>

    {{-- Filters & Status Rail --}}
    <div class="glass-card overflow-hidden">
        <div class="p-4 border-b border-admin-stroke flex flex-wrap items-center justify-between gap-4 bg-white/[0.02]">
            <div class="admin-status-rail !p-0 !bg-transparent">
                <button type="button" class="admin-status-rail-item" :class="activeTab === 'all' ? 'is-active' : ''" @click="setTab('all')">
                    All <span class="opacity-40 ml-1 text-[10px]">({{ array_sum($statusTotals) }})</span>
                </button>
                <button type="button" class="admin-status-rail-item" :class="activeTab === 'draft' ? 'is-active' : ''" @click="setTab('draft')">
                    Drafts <span class="opacity-40 ml-1 text-[10px]">({{ $statusTotals['draft'] ?? 0 }})</span>
                </button>
                <button type="button" class="admin-status-rail-item" :class="activeTab === 'published' ? 'is-active' : ''" @click="setTab('published')">
                    Published <span class="opacity-40 ml-1 text-[10px]">({{ $statusTotals['published'] ?? 0 }})</span>
                </button>
                <button type="button" class="admin-status-rail-item" :class="activeTab === 'archived' ? 'is-active' : ''" @click="setTab('archived')">
                    Archived <span class="opacity-40 ml-1 text-[10px]">({{ $statusTotals['archived'] ?? 0 }})</span>
                </button>
            </div>
            
            <div class="flex flex-wrap items-center justify-between gap-3 w-full sm:w-auto flex-1">
                <div class="relative w-full sm:w-64">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                    <input type="text" class="admin-input pl-9 !h-10 !rounded-xl" wire:model.live.debounce.300ms="search" placeholder="Search gallery...">
                </div>
                <button type="button" class="admin-ghost-btn !h-10 !w-10 !p-0 justify-center" wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')">
                    <x-admin.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4" />
                </button>
            </div>
        </div>
        
        <div class="p-3 flex flex-wrap items-center gap-2">
            <button type="button" class="admin-chip" :class="activeCategory === null ? 'is-active' : ''" @click="setCat(null)">
                All Categories
            </button>
            @foreach($categories as $category)
                <button type="button" class="admin-chip" :class="activeCategory === {{ $category->id }} ? 'is-active' : ''" @click="setCat({{ $category->id }})">
                    {{ $category->name }}
                    <span class="opacity-50 ml-1 text-[10px]">({{ $categoryTotals[$category->id] ?? 0 }})</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="relative min-h-[500px]">
        {{-- Skeleton Loading --}}
        <div wire:loading.delay.longer wire:target="setStatus,setCategory,search,sortBy,sortDirection" class="absolute inset-0 z-50 bg-admin-bg/50 backdrop-blur-sm rounded-2xl flex items-center justify-center">
            <div class="flex flex-col items-center gap-3">
                <x-admin.icon name="refresh" class="h-10 w-10 animate-spin text-primary-500" />
                <p class="text-sm font-medium text-admin-muted uppercase tracking-widest">Refreshing...</p>
            </div>
        </div>

        @if(! $readyToLoad)
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @for($i = 0; $i < 8; $i++)
                    <div class="glass-card p-4 space-y-3">
                        <div class="admin-skeleton h-48 rounded-xl"></div>
                        <div class="admin-skeleton h-5 w-3/4"></div>
                        <div class="admin-skeleton h-4 w-1/2"></div>
                    </div>
                @endfor
            </div>
        @elseif($loadError)
            <div class="admin-error-state glass-card p-12">
                <x-admin.icon name="alert" class="h-12 w-12 text-red-500 mb-4" />
                <h3 class="text-xl font-bold">Connection Error</h3>
                <p class="mt-2 text-admin-muted">{{ $loadErrorMessage }}</p>
                <button class="admin-primary-btn mt-6" wire:click="retryLoading" type="button">Try Again</button>
            </div>
        @elseif($items->count() === 0)
            <div class="admin-empty-state glass-card p-20">
                <div class="h-20 w-20 rounded-full bg-white/5 flex items-center justify-center mb-6">
                    <x-admin.icon name="image" class="h-10 w-10 text-admin-muted" />
                </div>
                <h3 class="text-xl font-bold">Studio Empty</h3>
                <p class="mt-2 text-admin-muted max-w-md mx-auto">No media assets found matching your current filter criteria.</p>
                <button type="button" @click="itemModalOpen = true; isLoadingItem = true; $wire.openCreateModal().then(() => isLoadingItem = false)" class="admin-primary-btn mt-8">
                    Upload Your First Asset
                </button>
            </div>
        @else
            <div class="space-y-6 pb-24">
                @if($viewMode === 'list')
                    <div class="glass-card divide-y divide-admin-stroke overflow-hidden">
                        @foreach($items as $item)
                            @php($imageUrl = $item->getFirstMediaUrl('gallery'))
                            <article wire:key="gallery-list-{{ $item->id }}" class="flex items-center justify-between p-4 hover:bg-white/[0.02] transition-colors group">
                                <div class="flex items-center gap-4">
                                    <label class="admin-check-wrap !mb-0 cursor-pointer">
                                        <input type="checkbox" :checked="isSelected('{{ $item->id }}')" @change="toggleSelect('{{ $item->id }}')" class="peer sr-only">
                                        <span class="admin-check-box" aria-hidden="true">
                                            <x-admin.icon name="check" class="h-3 w-3" />
                                        </span>
                                    </label>
                                    
                                    <div class="relative h-14 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-admin-stroke bg-admin-bg" x-data="{ loaded: false }">
                                        <div x-show="!loaded" class="absolute inset-0 admin-skeleton"></div>
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->alt_text ?: $item->title }}" @load="loaded = true" class="h-full w-full object-cover transition-opacity duration-300" :class="loaded ? 'opacity-100' : 'opacity-0'">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <x-admin.icon name="image" class="h-6 w-6 text-admin-muted/30" />
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <h3 class="font-bold text-sm">{{ $item->title }}</h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-admin-muted uppercase tracking-wider">{{ $item->category?->name ?? 'Uncategorized' }}</span>
                                            <span class="h-1 w-1 rounded-full bg-admin-stroke"></span>
                                            <span class="admin-status-badge !text-[9px] !py-0.5 {{ $item->status === 'published' ? 'is-published' : ($item->status === 'archived' ? 'is-archived' : 'is-draft') }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                    <button type="button" wire:click="updateItemStatus({{ $item->id }}, '{{ $item->status === 'published' ? 'draft' : 'published' }}')" class="admin-ghost-btn !h-8 !px-3 text-[10px] font-bold uppercase tracking-wider">
                                        {{ $item->status === 'published' ? 'Set Draft' : 'Publish' }}
                                    </button>
                                    <button type="button" @click="itemModalOpen = true; isLoadingItem = true; $wire.openEditModal({{ $item->id }}).then(() => isLoadingItem = false)" class="admin-ghost-btn !h-8 !w-8 !p-0 flex items-center justify-center">
                                        <x-admin.icon name="edit" class="h-3.5 w-3.5" />
                                    </button>
                                    <button type="button" @click="deleteModalOpen = true; deleteItemTitle = '{{ addslashes($item->title) }}'; $wire.promptDelete({{ $item->id }})" class="admin-ghost-btn !h-8 !w-8 !p-0 flex items-center justify-center text-red-400 hover:!border-red-500/50 hover:!bg-red-500/5">
                                        <x-admin.icon name="trash" class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @elseif($viewMode === 'masonry')
                    <div class="columns-1 gap-6 sm:columns-2 xl:columns-4">
                        @foreach($items as $item)
                            @php($imageUrl = $item->getFirstMediaUrl('gallery'))
                            <article wire:key="gallery-masonry-{{ $item->id }}" class="glass-card mb-6 break-inside-avoid overflow-hidden group hover:border-primary-500/50 transition-all duration-300 relative" :class="isSelected('{{ $item->id }}') ? 'border-primary-500/50 bg-primary-500/[0.03]' : ''">
                                <div class="relative min-h-[180px] bg-admin-bg" x-data="{ loaded: false }">
                                    <div x-show="!loaded" class="absolute inset-0 admin-skeleton"></div>
                                    
                                    {{-- Selection Always Visible --}}
                                    <div class="absolute top-3 left-3 z-20">
                                        <label class="admin-check-wrap !mb-0 cursor-pointer">
                                            <input type="checkbox" :checked="isSelected('{{ $item->id }}')" @change="toggleSelect('{{ $item->id }}')" class="peer sr-only">
                                            <span class="admin-check-box !bg-black/40 backdrop-blur-md !border-white/20 peer-checked:!border-primary-500 peer-checked:!bg-primary-500 shadow-lg" aria-hidden="true">
                                                <x-admin.icon name="check" class="h-3 w-3 text-white" />
                                            </span>
                                        </label>
                                    </div>

                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item->alt_text ?: $item->title }}" @load="loaded = true" class="w-full object-cover transition-all duration-500" :class="loaded ? 'opacity-100' : 'opacity-0'">
                                    @else
                                        <div class="h-48 flex items-center justify-center">
                                            <x-admin.icon name="image" class="h-10 w-10 text-admin-muted/30" />
                                        </div>
                                    @endif
                                    
                                    {{-- Overlay Actions --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                        <div class="flex items-center gap-2 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                            <button type="button" @click="itemModalOpen = true; isLoadingItem = true; $wire.openEditModal({{ $item->id }}).then(() => isLoadingItem = false)" class="flex-1 bg-white/10 hover:bg-white/20 backdrop-blur-md text-[10px] font-bold py-2.5 rounded-lg transition-colors border border-white/10 tracking-widest">EDIT</button>
                                            <button type="button" wire:click="updateItemStatus({{ $item->id }}, '{{ $item->status === 'published' ? 'draft' : 'published' }}')" class="flex-1 bg-primary-500/80 hover:bg-primary-500 backdrop-blur-md text-[10px] font-bold py-2.5 rounded-lg transition-colors border border-primary-400/30 tracking-widest text-white">
                                                {{ $item->status === 'published' ? 'DRAFT' : 'PUBLISH' }}
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="isSelected('{{ $item->id }}')" class="absolute inset-0 bg-primary-500/10 pointer-events-none border-2 border-primary-500/50 rounded-inherit"></div>
                                </div>
                                <div class="p-3">
                                    <h3 class="font-bold text-sm truncate">{{ $item->title }}</h3>
                                    <p class="text-[10px] text-admin-muted mt-1 uppercase tracking-tighter">{{ $item->category?->name ?? 'Uncategorized' }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($items as $item)
                            @php($imageUrl = $item->getFirstMediaUrl('gallery'))
                            <article wire:key="gallery-grid-{{ $item->id }}" class="glass-card overflow-hidden group hover:border-primary-500/50 transition-all duration-300 lift-on-hover relative" :class="isSelected('{{ $item->id }}') ? 'border-primary-500/50 bg-primary-500/[0.03]' : ''">
                                <div class="relative h-56 bg-admin-bg overflow-hidden" x-data="{ loaded: false }">
                                    <div x-show="!loaded" class="absolute inset-0 admin-skeleton"></div>
                                    
                                    {{-- Selection Always Visible --}}
                                    <div class="absolute top-3 left-3 z-20">
                                        <label class="admin-check-wrap !mb-0 cursor-pointer">
                                            <input type="checkbox" :checked="isSelected('{{ $item->id }}')" @change="toggleSelect('{{ $item->id }}')" class="peer sr-only">
                                            <span class="admin-check-box !bg-black/40 backdrop-blur-md !border-white/20 peer-checked:!border-primary-500 peer-checked:!bg-primary-500 shadow-lg" aria-hidden="true">
                                                <x-admin.icon name="check" class="h-3 w-3 text-white" />
                                            </span>
                                        </label>
                                    </div>

                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item->alt_text ?: $item->title }}" @load="loaded = true" class="h-full w-full object-cover transition-all duration-500 group-hover:scale-110" :class="loaded ? 'opacity-100' : 'opacity-0'">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <x-admin.icon name="image" class="h-10 w-10 text-admin-muted/30" />
                                        </div>
                                    @endif
                                    
                                    <div class="absolute top-3 right-3 z-20">
                                        <span class="admin-status-badge !text-[9px] !py-1 !px-2 backdrop-blur-md bg-black/40 border-white/10 {{ $item->status === 'published' ? 'is-published' : ($item->status === 'archived' ? 'is-archived' : 'is-draft') }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </div>

                                    <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3 backdrop-blur-[2px]">
                                        <button type="button" @click="itemModalOpen = true; isLoadingItem = true; $wire.openEditModal({{ $item->id }}).then(() => isLoadingItem = false)" class="admin-icon-btn !bg-white/10 hover:!bg-white/20 !border-white/20 h-11 w-11 shadow-xl transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button type="button" wire:click="updateItemStatus({{ $item->id }}, '{{ $item->status === 'published' ? 'draft' : 'published' }}')" class="admin-icon-btn !bg-primary-500/80 hover:!bg-primary-500 !border-primary-400/30 h-11 w-11 shadow-xl transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300 delay-75">
                                            <x-admin.icon name="{{ $item->status === 'published' ? 'close' : 'check' }}" class="h-4 w-4 text-white" />
                                        </button>
                                        <button type="button" @click="deleteModalOpen = true; deleteItemTitle = '{{ addslashes($item->title) }}'; $wire.promptDelete({{ $item->id }})" class="admin-icon-btn !bg-red-500/20 hover:!bg-red-500/40 !border-red-500/30 h-11 w-11 shadow-xl transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300 delay-150">
                                            <x-admin.icon name="trash" class="h-4 w-4 text-red-400" />
                                        </button>
                                    </div>

                                    <div x-show="isSelected('{{ $item->id }}')" class="absolute inset-0 bg-primary-500/15 pointer-events-none border-2 border-primary-500/60 rounded-inherit"></div>
                                </div>
                                <div class="p-4 bg-white/[0.01]">
                                    <h3 class="font-bold text-sm truncate">{{ $item->title }}</h3>
                                    <p class="text-[10px] text-admin-muted mt-1 uppercase tracking-widest">{{ $item->category?->name ?? 'Uncategorized' }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-8 pb-32">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Drag & Drop Section --}}
    <section 
        class="glass-card p-10 border-dashed border-2 hover:border-primary-500/50 transition-all group relative overflow-hidden"
        x-data="{ dragActive: false }"
        x-on:dragenter.prevent="dragActive = true"
        x-on:dragover.prevent="dragActive = true"
        x-on:dragleave.prevent="dragActive = false"
        x-on:drop.prevent="dragActive = false; $wire.uploadMultiple('dropUploads', $event.dataTransfer.files)"
    >
        <div x-show="dragActive" class="absolute inset-0 bg-primary-500/10 backdrop-blur-[2px] z-10 flex items-center justify-center">
            <p class="text-xl font-bold text-primary-400 animate-bounce">Release to Stage Files</p>
        </div>
        
        <div class="flex flex-col items-center justify-center text-center space-y-5">
            <div class="h-20 w-20 rounded-3xl bg-primary-500/10 flex items-center justify-center text-primary-400 group-hover:scale-110 transition-transform duration-500 shadow-inner">
                <x-admin.icon name="upload" class="h-10 w-10" />
            </div>
            <div>
                <h4 class="text-xl font-bold tracking-tight">Bulk Upload Assets</h4>
                <p class="text-sm text-admin-muted max-w-sm mt-1">Drag images directly onto this area to begin staging your gallery updates.</p>
            </div>
            <label class="admin-primary-btn !h-11 !px-8 cursor-pointer shadow-lg shadow-primary-500/20">
                <input type="file" multiple class="hidden" @change="$wire.uploadMultiple('dropUploads', $event.target.files)">
                Select Media Files
            </label>
        </div>
        
        {{-- Staged Files --}}
        @if(count($dropUploads) > 0)
            <div class="mt-10 pt-10 border-t border-admin-stroke space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h5 class="text-lg font-bold">Staging Area</h5>
                        <p class="text-xs text-admin-muted uppercase tracking-widest mt-1">{{ count($dropUploads) }} files pending upload</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" wire:click="clearStagedUploads" class="admin-ghost-btn !h-10 px-5 text-xs font-bold uppercase tracking-widest">Discard All</button>
                        <button type="button" wire:click="uploadStagedUploads" class="admin-primary-btn !h-10 px-8 text-xs font-bold uppercase tracking-widest">
                            <span wire:loading.remove wire:target="uploadStagedUploads">Confirm Upload</span>
                            <x-admin.icon wire:loading wire:target="uploadStagedUploads" name="refresh" class="h-4 w-4 animate-spin" />
                        </button>
                    </div>
                </div>
                
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($dropUploads as $index => $upload)
                        <div wire:key="gallery-staged-{{ $index }}-{{ md5($upload->getFilename()) }}" class="glass-card p-4 flex items-center gap-4 group/staged relative overflow-hidden" x-data="{ loaded: false }">
                            <div class="h-16 w-16 rounded-xl overflow-hidden border border-admin-stroke bg-admin-bg relative flex-shrink-0">
                                <img src="{{ $upload->temporaryUrl() }}" class="h-full w-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="text" wire:model.defer="dropUploadDetails.{{ $index }}.title" class="admin-input !h-9 !text-sm !bg-white/5 !border-admin-stroke px-3 focus:!border-primary-500/50" placeholder="Give it a title...">
                                <p class="text-[10px] text-admin-muted truncate mt-1.5 uppercase tracking-tighter">{{ $upload->getClientOriginalName() }} • {{ number_format($upload->getSize() / 1024, 0) }} KB</p>
                            </div>
                            <button type="button" wire:click="removeStagedUpload({{ $index }})" class="absolute top-2 right-2 text-admin-muted hover:text-red-400 transition-colors bg-admin-bg/50 p-1 rounded-lg">
                                <x-admin.icon name="close" class="h-3 w-3" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- FULL WIDTH FLOATING ACTION STRIP --}}
    <div 
        x-show="selectedIds.length > 0" 
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 left-0 right-0 z-[100] pb-8 pt-4 px-8 pointer-events-none"
        x-cloak
    >
        <div class="max-w-7xl mx-auto w-full pointer-events-auto">
            <div class="glass-card flex flex-wrap items-center justify-between gap-3 sm:gap-6 p-4 sm:p-5 shadow-[0_-20px_50px_rgba(0,0,0,0.5)] border-primary-500/40 backdrop-blur-3xl bg-primary-950/20">
                <div class="flex items-center gap-4 sm:gap-6">
                    <label class="admin-check-wrap !mb-0 cursor-pointer">
                        <input type="checkbox" :checked="isAllSelected()" @change="toggleSelectAll()" class="peer sr-only">
                        <span class="admin-check-box !h-5 !w-5 sm:!h-6 sm:!w-6 !bg-white/5 !border-white/20 peer-checked:!bg-primary-500 peer-checked:!border-primary-500 shadow-inner" aria-hidden="true">
                            <x-admin.icon name="check" class="h-3 w-3 sm:h-4 sm:w-4 text-white" />
                        </span>
                        <span class="text-xs sm:text-sm font-bold ml-2">Select All</span>
                    </label>
                    
                    <div class="h-8 sm:h-10 w-px bg-white/10 hidden xs:block"></div>
                    
                    <div class="flex flex-col">
                        <span class="text-lg sm:text-xl font-black text-primary-400 flex items-center gap-2">
                            <span x-text="selectedIds.length"></span>
                            <span class="text-[9px] sm:text-xs font-bold uppercase tracking-widest text-white/50">Selected</span>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button type="button" 
                        wire:click="bulkUpdate('published', selectedIds)" 
                        wire:loading.attr="disabled"
                        @click="setTimeout(() => { if(!$wire.errors) selectedIds = [] }, 1000)"
                        class="admin-primary-btn !h-10 sm:!h-12 !px-4 sm:!px-8 text-[10px] sm:text-xs font-black uppercase tracking-widest shadow-lg shadow-primary-500/30"
                    >
                        <span wire:loading.remove wire:target="bulkUpdate('published', selectedIds)">
                            <span class="hidden sm:inline">Publish All</span>
                            <span class="sm:hidden">Publish</span>
                        </span>
                        <x-admin.icon wire:loading wire:target="bulkUpdate('published', selectedIds)" name="refresh" class="h-4 w-4 animate-spin" />
                    </button>
                    
                    <button type="button" 
                        wire:click="bulkUpdate('draft', selectedIds)" 
                        wire:loading.attr="disabled"
                        @click="setTimeout(() => { if(!$wire.errors) selectedIds = [] }, 1000)"
                        class="admin-ghost-btn !h-10 sm:!h-12 !px-4 sm:!px-8 text-[10px] sm:text-xs font-black uppercase tracking-widest !bg-white/5 !border-white/20 hover:!bg-white/10"
                    >
                        <span wire:loading.remove wire:target="bulkUpdate('draft', selectedIds)">
                            <span class="hidden sm:inline">Move to Draft</span>
                            <span class="sm:hidden">Draft</span>
                        </span>
                        <x-admin.icon wire:loading wire:target="bulkUpdate('draft', selectedIds)" name="refresh" class="h-4 w-4 animate-spin" />
                    </button>
                    
                    <button type="button" 
                        @click="bulkDeleteModalOpen = true" 
                        class="admin-ghost-btn !h-10 sm:!h-12 !px-4 sm:!px-8 text-[10px] sm:text-xs font-black uppercase tracking-widest !border-red-500/30 text-red-400 hover:!bg-red-500/10"
                    >
                        <span class="hidden sm:inline">Delete Selection</span>
                        <x-admin.icon name="trash" class="h-4 w-4 sm:hidden" />
                    </button>
                    
                    <button type="button" @click="selectedIds = []" class="admin-icon-btn !h-10 !w-10 sm:!h-12 sm:!w-12 !bg-black/20 hover:!bg-black/40 !border-white/10">
                        <x-admin.icon name="close" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="itemModalOpen" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4" wire:key="gallery-item-modal-wrap">
        <div class="admin-modal-backdrop" @click="itemModalOpen = false"></div>
        <section class="admin-modal-panel w-full max-w-5xl max-h-[90vh] flex flex-col shadow-2xl border-white/10" role="dialog" aria-modal="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <header class="flex items-center justify-between border-b border-admin-stroke p-4 flex-shrink-0 bg-white/[0.02]">
                <div>
                    <p class="text-[9px] uppercase tracking-[0.3em] text-primary-400 font-bold mb-0.5">Asset Studio</p>
                    <h3 class="text-xl font-black tracking-tight">{{ $editingItemId ? 'Update Asset' : 'New Asset' }}</h3>
                </div>
                <button type="button" @click="itemModalOpen = false" class="admin-icon-btn h-10 w-10 hover:rotate-90 transition-transform bg-white/5 border-white/10">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <div class="overflow-y-auto p-5 custom-scrollbar">
                {{-- Skeleton Shimmer Loading --}}
                <div x-show="isLoadingItem" class="space-y-6 animate-pulse" wire:key="modal-skeleton">
                    <div class="grid gap-8 lg:grid-cols-12">
                        <div class="lg:col-span-5">
                            <div class="bg-white/5 aspect-square rounded-3xl border border-white/5"></div>
                        </div>
                        <div class="lg:col-span-7 space-y-5">
                            <div class="h-10 bg-white/5 rounded-xl w-3/4"></div>
                            <div class="grid grid-cols-2 gap-5">
                                <div class="h-10 bg-white/5 rounded-xl"></div>
                                <div class="h-10 bg-white/5 rounded-xl"></div>
                            </div>
                            <div class="h-10 bg-white/5 rounded-xl w-full"></div>
                            <div class="h-10 bg-white/5 rounded-xl w-full"></div>
                            <div class="h-32 bg-white/5 rounded-xl w-full"></div>
                        </div>
                    </div>
                </div>
                
                <div x-show="!isLoadingItem" class="grid gap-5 lg:gap-8 lg:grid-cols-12" wire:key="modal-content-{{ $editingItemId ?? 'new' }}">
                    {{-- Preview Side --}}
                    <div class="lg:col-span-5 space-y-4 lg:space-y-5">
                        <div class="relative overflow-hidden rounded-3xl border-2 border-admin-stroke bg-admin-bg flex items-center justify-center aspect-video sm:aspect-square shadow-2xl">
                            @if($itemUpload)
                                <img src="{{ $itemUpload->temporaryUrl() }}" class="h-full w-full object-cover">
                            @elseif($editingItemMediaUrl)
                                <img src="{{ $editingItemMediaUrl }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex flex-col items-center gap-4 text-admin-muted p-12 text-center">
                                    <div class="h-16 w-16 rounded-full bg-white/5 flex items-center justify-center">
                                        <x-admin.icon name="image" class="h-8 w-8 opacity-20" />
                                    </div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-40">No media attached</p>
                                </div>
                            @endif
                            
                            <div wire:loading wire:target="itemUpload" class="absolute inset-0 bg-admin-bg/80 backdrop-blur-md flex items-center justify-center">
                                <x-admin.icon name="refresh" class="h-8 w-8 animate-spin text-primary-500" />
                            </div>
                        </div>
                        
                        <label class="block group cursor-pointer">
                            <div class="flex items-center justify-center gap-3 p-4 rounded-2xl border border-dashed border-admin-stroke group-hover:border-primary-500/50 transition-all bg-white/[0.02] group-hover:bg-primary-500/[0.02]">
                                <x-admin.icon name="upload" class="h-4 w-4 text-admin-muted group-hover:text-primary-400 transition-transform" />
                                <span class="text-[10px] font-black uppercase tracking-widest text-admin-muted group-hover:text-white">{{ $editingItemId ? 'Replace Asset' : 'Select Source' }}</span>
                            </div>
                            <input type="file" class="hidden" wire:model="itemUpload" accept="image/*">
                        </label>
                    </div>

                    {{-- Details Side --}}
                    <div class="lg:col-span-7 space-y-5">
                        <div class="grid gap-5">
                            <div>
                                <label class="admin-label !text-[9px] uppercase tracking-widest !mb-1.5 opacity-60">Asset Title</label>
                                <input type="text" wire:model="title" wire:key="f-title-{{ $editingItemId ?? 'new' }}" class="admin-input !h-11 font-bold !bg-white/[0.03] !border-white/10 focus:!border-primary-500/50" placeholder="Internal name...">
                                @error('title') <p class="admin-input-feedback is-error text-[9px] uppercase font-bold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="admin-label !text-[9px] uppercase tracking-widest !mb-1.5 opacity-60">Status</label>
                                    <x-admin.select wire:model="itemStatus" wire:key="f-status-{{ $editingItemId ?? 'new' }}" class="!h-11 !bg-white/[0.03] !border-white/10">
                                        <x-admin.option value="draft">Draft</x-admin.option>
                                        <x-admin.option value="published">Published</x-admin.option>
                                        <x-admin.option value="archived">Archived</x-admin.option>
                                    </x-admin.select>
                                </div>
                                <div>
                                    <label class="admin-label !text-[9px] uppercase tracking-widest !mb-1.5 opacity-60">Sequence</label>
                                    <input type="number" wire:model="sortOrder" wire:key="f-sort-{{ $editingItemId ?? 'new' }}" class="admin-input !h-11 !bg-white/[0.03] !border-white/10">
                                </div>
                            </div>

                            <div>
                                <label class="admin-label !text-[9px] uppercase tracking-widest !mb-1.5 opacity-60">Category</label>
                                <div class="flex gap-2.5">
                                    <x-admin.select class="flex-1 !h-11 !bg-white/[0.03] !border-white/10" wire:model="galleryCategoryId" wire:key="f-cat-{{ $editingItemId ?? 'new' }}">
                                        <x-admin.option value="">Uncategorized</x-admin.option>
                                        @foreach($categories as $category)
                                            <x-admin.option value="{{ $category->id }}">{{ $category->name }}</x-admin.option>
                                        @endforeach
                                    </x-admin.select>
                                    <button type="button" class="admin-ghost-btn !h-11 !w-11 !p-0 justify-center !bg-white/5 !border-white/10" wire:click="$toggle('showCreateCategory')">
                                        <x-admin.icon name="plus" class="h-4 w-4" />
                                    </button>
                                </div>
                                
                                @if($showCreateCategory)
                                    <div class="mt-3 p-4 bg-primary-500/[0.03] rounded-2xl border border-primary-500/20 space-y-3 animate-in fade-in slide-in-from-top-2">
                                        <input type="text" wire:model="newCategoryName" class="admin-input !h-9 text-xs !bg-admin-bg !border-white/10" placeholder="New category name...">
                                        <button type="button" wire:click="createCategory" class="admin-primary-btn !h-8 text-[9px] w-full font-black uppercase tracking-widest">Store Category</button>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="admin-label !text-[9px] uppercase tracking-widest !mb-1.5 opacity-60">Alt Text (Accessibility)</label>
                                <input type="text" wire:model="altText" wire:key="f-alt-{{ $editingItemId ?? 'new' }}" class="admin-input !h-11 !bg-white/[0.03] !border-white/10" placeholder="Describe image content...">
                            </div>

                            <div>
                                <label class="admin-label !text-[9px] uppercase tracking-widest !mb-1.5 opacity-60">Caption (Public)</label>
                                <textarea wire:model="caption" wire:key="f-cap-{{ $editingItemId ?? 'new' }}" class="admin-input min-h-[80px] py-3 !bg-white/[0.03] !border-white/10" placeholder="Visible to visitors..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="border-t border-admin-stroke p-4 flex justify-end gap-3 bg-white/[0.04] flex-shrink-0">
                <button type="button" @click="itemModalOpen = false" class="admin-ghost-btn !h-11 px-6 text-xs font-bold uppercase tracking-widest !border-white/10">Cancel</button>
                <button type="button" wire:click="saveItem" class="admin-primary-btn !h-11 px-10 text-xs font-black uppercase tracking-widest shadow-xl shadow-primary-500/20">
                    <span wire:loading.remove wire:target="saveItem">{{ $editingItemId ? 'Update Asset' : 'Publish Asset' }}</span>
                    <div wire:loading wire:target="saveItem" class="flex items-center gap-2">
                        <x-admin.icon name="refresh" class="h-4 w-4 animate-spin" />
                        <span>Syncing...</span>
                    </div>
                </button>
            </footer>
        </section>
    </div>

    {{-- Individual Delete Modal --}}
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4">
        <div class="admin-modal-backdrop" @click="deleteModalOpen = false"></div>
        <section class="admin-modal-panel max-w-md w-full p-8 text-center relative overflow-hidden" role="dialog" aria-modal="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="scale-90 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="absolute top-0 left-0 right-0 h-1 bg-red-500/50"></div>
            <div class="h-20 w-20 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-6 border border-red-500/20">
                <x-admin.icon name="trash" class="h-8 w-8 text-red-500" />
            </div>
            <h3 class="text-2xl font-black tracking-tighter">Destroy Asset?</h3>
            <p class="mt-3 text-admin-muted text-sm leading-relaxed">
                Purge <span class="text-white font-black italic" x-text="deleteItemTitle"></span> from library?
            </p>
            <div class="mt-8 flex flex-col gap-2">
                <button type="button" wire:click="confirmDelete" @click="setTimeout(() => { if(!$wire.errors) deleteModalOpen = false }, 1000)" class="admin-primary-btn !h-12 !bg-red-500 hover:!bg-red-600 border-none font-black uppercase tracking-widest">
                    <span wire:loading.remove wire:target="confirmDelete">Confirm</span>
                    <x-admin.icon wire:loading wire:target="confirmDelete" name="refresh" class="h-4 w-4 animate-spin text-white" />
                </button>
                <button type="button" @click="deleteModalOpen = false" class="admin-ghost-btn !h-12 font-bold uppercase tracking-widest !border-white/10">Abort</button>
            </div>
        </section>
    </div>

    {{-- Bulk Delete Modal --}}
    <div x-show="bulkDeleteModalOpen" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4">
        <div class="admin-modal-backdrop" @click="bulkDeleteModalOpen = false"></div>
        <section class="admin-modal-panel max-w-md w-full p-8 text-center relative overflow-hidden" role="dialog" aria-modal="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="scale-90 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="absolute top-0 left-0 right-0 h-1 bg-red-600"></div>
            <div class="h-20 w-20 rounded-full bg-red-600/10 flex items-center justify-center mx-auto mb-6 border border-red-600/20">
                <x-admin.icon name="alert" class="h-8 w-8 text-red-600" />
            </div>
            <h3 class="text-2xl font-black tracking-tighter uppercase">Bulk Purge</h3>
            <p class="mt-3 text-admin-muted text-sm leading-relaxed">
                Purge <span class="text-white font-black" x-text="selectedIds.length"></span> assets?
            </p>
            <div class="mt-8 flex flex-col gap-2">
                <button type="button" wire:click="confirmBulkDelete(selectedIds)" @click="setTimeout(() => { if(!$wire.errors) { bulkDeleteModalOpen = false; selectedIds = []; } }, 1000)" class="admin-primary-btn !h-12 !bg-red-600 hover:!bg-red-700 border-none font-black uppercase tracking-widest">
                    <span wire:loading.remove wire:target="confirmBulkDelete(selectedIds)">Execute Purge</span>
                    <x-admin.icon wire:loading wire:target="confirmBulkDelete(selectedIds)" name="refresh" class="h-4 w-4 animate-spin text-white" />
                </button>
                <button type="button" @click="bulkDeleteModalOpen = false" class="admin-ghost-btn !h-12 font-bold uppercase tracking-widest !border-white/10">Cancel</button>
            </div>
        </section>
    </div>
</div>
