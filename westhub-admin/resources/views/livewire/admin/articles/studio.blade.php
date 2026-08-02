<div class="space-y-4" x-data="westhubStudioAutosave($wire)">
    <div class="glass-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="admin-chip is-active">
                <span wire:loading.remove wire:target="autosaveFromInteraction,saveDraft,publish,createCategory,removeHeadlineImageNow">{{ $saveState }}</span>
                <span wire:loading wire:target="autosaveFromInteraction,saveDraft,publish,createCategory,removeHeadlineImageNow" class="inline-flex items-center gap-2">
                    <x-admin.icon name="spinner" class="h-3 w-3 animate-spin" />
                    Saving...
                </span>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="admin-ghost-btn" @click="triggerSaveDraft()" :disabled="!isFormValid || isSubmitting" wire:loading.attr="disabled" wire:target="saveDraft,publish,autosaveFromInteraction,createCategory">
                Save Draft
            </button>
            <button type="button" class="admin-primary-btn" @click="triggerPublish()" :disabled="!isFormValid || isSubmitting" wire:loading.attr="disabled" wire:target="saveDraft,publish,autosaveFromInteraction,createCategory">
                Publish
            </button>
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="space-y-5">
            <section class="space-y-4">
                <div>
                    <label class="admin-label">Headline Image & SEO</label>
                    <div class="grid md:grid-cols-[1fr,320px] gap-4">
                        <div class="relative group">
                            @if($headlineImageUpload)
                                <div class="relative aspect-[2/1] md:aspect-[3/1] rounded-xl overflow-hidden border border-admin-stroke">
                                    <img src="{{ $headlineImageUpload->temporaryUrl() }}" class="w-full h-full object-cover">
                                    <button type="button" wire:click="removeHeadlineImageNow" class="absolute top-2 right-2 admin-icon-btn !bg-rose-500/80 !border-rose-400 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <x-admin.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            @elseif($article?->headline_image_path && !$removeHeadlineImage)
                                <div class="relative aspect-[2/1] md:aspect-[3/1] rounded-xl overflow-hidden border border-admin-stroke">
                                    <img src="{{ $this->headlineImageUrl() }}" class="w-full h-full object-cover">
                                    <button type="button" wire:click="removeHeadlineImageNow" class="absolute top-2 right-2 admin-icon-btn !bg-rose-500/80 !border-rose-400 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <x-admin.icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            @else
                                <label class="flex flex-col items-center justify-center aspect-[2/1] md:aspect-[3/1] rounded-xl border-2 border-dashed border-admin-stroke bg-white/5 cursor-pointer hover:bg-white/10 transition-colors">
                                    <x-admin.icon name="image" class="h-8 w-8 text-admin-muted mb-2" />
                                    <span class="text-xs text-admin-muted">Choose headline image</span>
                                    <input type="file" wire:model="headlineImageUpload" class="hidden">
                                </label>
                            @endif

                            <div wire:loading wire:target="headlineImageUpload" class="absolute inset-0 bg-admin-surface/60 backdrop-blur-sm flex flex-col items-center justify-center rounded-xl">
                                <x-admin.icon name="spinner" class="h-6 w-6 animate-spin text-admin-ink" />
                                <span class="text-[10px] uppercase tracking-widest mt-2">Preparing...</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] uppercase tracking-widest text-admin-muted mb-1 block">Image Alt Text (SEO)</label>
                                <input type="text" wire:model.live.debounce.300ms="headline_image_alt" class="admin-input !text-xs !h-9" placeholder="Describe the image for SEO...">
                            </div>
                            <div>
                                <label class="text-[10px] uppercase tracking-widest text-admin-muted mb-1 block">Image Title / Caption</label>
                                <input type="text" wire:model.live.debounce.300ms="headline_image_title" class="admin-input !text-xs !h-9" placeholder="Image title or caption...">
                            </div>
                            <p class="text-[10px] text-admin-muted leading-relaxed">
                                Optimized alt text helps search engines understand your content and improves accessibility.
                            </p>
                        </div>
                    </div>
                    @error('headlineImageUpload') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <input
                        wire:model.live.debounce.300ms="title"
                        @input="onInput"
                        @blur="onBlur"
                        class="admin-input text-xl font-semibold"
                        placeholder="Headline"
                    >
                    <p class="mt-1 text-xs text-admin-muted">Slug: /{{ $this->slugPreview ?: '-' }}</p>
                    @error('title') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <textarea
                        wire:model.live.debounce.300ms="excerpt"
                        @input="onInput"
                        @blur="onBlur"
                        class="admin-input min-h-24"
                        placeholder="Short description"
                    ></textarea>
                    @error('excerpt') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                </div>

                <div wire:ignore x-data="westhubEditor(@entangle('body'))" class="space-y-2" @input="onInput" @focusout="onBlur">
                    <div class="flex items-center justify-between">
                        <label class="admin-label !mb-0">Body Editor</label>
                        <p class="text-[11px] text-admin-muted uppercase tracking-wider">Rich text</p>
                    </div>
                    <div class="editor-toolbar flex flex-wrap items-center gap-2 rounded-xl border border-admin-stroke bg-white/5 p-2">
                        <select class="admin-input !h-8 w-full sm:!w-[170px] !py-1 !text-xs" :value="currentBlockType()" @change="setBlockType($event.target.value)">
                            <option value="paragraph">Paragraph</option>
                            <option value="h1">Heading 1</option>
                            <option value="h2">Heading 2</option>
                            <option value="h3">Heading 3</option>
                        </select>
                        <span class="h-6 w-px bg-admin-surface/40"></span>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('bold') }" @click="toggleBold()" title="Bold">
                            <x-admin.icon name="bold" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('italic') }" @click="toggleItalic()" title="Italic">
                            <x-admin.icon name="italic" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('underline') }" @click="toggleUnderline()" title="Underline">
                            <span class="text-xs font-semibold">U</span>
                        </button>
                        <span class="h-6 w-px bg-admin-surface/40"></span>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('bulletList') }" @click="toggleBulletList()" title="Bullets">
                            <x-admin.icon name="list" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('orderedList') }" @click="toggleOrderedList()" title="Numbered">
                            <x-admin.icon name="list-ordered" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('blockquote') }" @click="toggleBlockquote()" title="Quote">
                            <span class="text-sm font-semibold">"</span>
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('codeBlock') }" @click="toggleCodeBlock()" title="Code Block">
                            <span class="text-[11px] font-semibold">{ }</span>
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" @click="insertHorizontalRule()" title="Divider">
                            <span class="text-xs font-semibold">-</span>
                        </button>
                        <span class="h-6 w-px bg-admin-surface/40"></span>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive({ textAlign: 'left' }) }" @click="setTextAlign('left')" title="Align Left">
                            <x-admin.icon name="align-left" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive({ textAlign: 'center' }) }" @click="setTextAlign('center')" title="Align Center">
                            <x-admin.icon name="align-center" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive({ textAlign: 'right' }) }" @click="setTextAlign('right')" title="Align Right">
                            <x-admin.icon name="align-right" class="h-4 w-4" />
                        </button>
                        <span class="h-6 w-px bg-admin-surface/40"></span>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" :class="{ 'is-active': isActive('link') }" @click="setLink()" title="Add Link">
                            <x-admin.icon name="link" class="h-4 w-4" />
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" @click="unsetLink()" title="Remove Link">
                            <x-admin.icon name="close" class="h-4 w-4" />
                        </button>
                        <span class="h-6 w-px bg-admin-surface/40"></span>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" @click="undo()" title="Undo">
                            <span class="text-xs font-semibold">&lt;</span>
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !w-8 !px-0" @click="redo()" title="Redo">
                            <span class="text-xs font-semibold">&gt;</span>
                        </button>
                        <button type="button" class="admin-ghost-btn !h-8 !px-2 text-[11px]" @click="clearFormatting()" title="Clear Formatting">
                            Clear
                        </button>
                    </div>
                    <div x-ref="editor" class="tiptap-surface min-h-72 admin-scrollbar overflow-auto"></div>
                </div>
                @error('body') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror

                <div class="grid gap-4">
                    <div x-data="{ showCreateCategory: @entangle('showCreateCategory').live }">
                        <label class="admin-label">Category</label>
                        <div class="flex items-start gap-2">
                            <x-admin.select class="flex-1" wire:model.live="article_category_id" placeholder="Choose category" @change="onBlur" wire:loading.attr="disabled" wire:target="createCategory">
                                <x-admin.option value="" :selected="empty($article_category_id)">Choose category</x-admin.option>
                                @foreach($categories as $category)
                                    <x-admin.option value="{{ $category->id }}" :selected="$category->id == $article_category_id">{{ $category->name }}</x-admin.option>
                                @endforeach
                            </x-admin.select>
                            <button type="button" class="admin-ghost-btn" @click="showCreateCategory = !showCreateCategory">Add</button>
                        </div>
                        <div class="mt-2 min-h-5">
                            @if($categoryFeedback)
                                <p class="text-xs text-admin-muted">{{ $categoryFeedback }}</p>
                            @endif
                            @error('article_category_id') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="mt-1 p-4 rounded-xl border border-admin-stroke bg-white/5 space-y-3" x-show="showCreateCategory" x-cloak>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-admin-muted">New Category</span>
                                <button type="button" @click="showCreateCategory = false" class="text-admin-muted hover:text-admin-ink transition-colors">
                                    <x-admin.icon name="close" class="h-4 w-4" />
                                </button>
                            </div>
                            <input type="text" wire:model.defer="newCategoryName" class="admin-input" placeholder="Enter category name...">
                            @error('newCategoryName') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
                            <textarea wire:model.defer="newCategoryDescription" class="admin-input min-h-16" placeholder="Category description (optional)"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showCreateCategory = false" class="admin-ghost-btn !h-9">Done</button>
                                <button type="button" wire:click="createCategory" wire:loading.attr="disabled" wire:target="createCategory" class="admin-primary-btn !h-9">
                                    <span wire:loading.remove wire:target="createCategory">Save Category</span>
                                    <span wire:loading wire:target="createCategory">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
