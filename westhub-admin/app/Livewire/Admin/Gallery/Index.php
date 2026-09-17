<?php

namespace App\Livewire\Admin\Gallery;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\AdminPreference;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use InteractsWithAdminToast;

    private const STATUSES = [
        GalleryItem::STATUS_DRAFT,
        GalleryItem::STATUS_PUBLISHED,
        GalleryItem::STATUS_ARCHIVED,
    ];

    public bool $readyToLoad = true;
    public bool $loadError = false;
    public ?string $loadErrorMessage = null;
    public string $viewMode = 'masonry';
    public string $search = '';
    public string $status = 'all';
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';
    public ?int $categoryId = null;
    public array $pageItemIds = [];
    public int $matchingItemsCount = 0;
    public array $statusTotals = [
        GalleryItem::STATUS_DRAFT => 0,
        GalleryItem::STATUS_PUBLISHED => 0,
        GalleryItem::STATUS_ARCHIVED => 0,
    ];
    public array $dropUploads = [];
    public array $dropUploadDetails = [];
    public array $categoryTotals = [];

    public bool $showItemModal = false;
    public ?int $editingItemId = null;
    public string $title = '';
    public ?string $altText = null;
    public ?string $caption = null;
    public ?int $galleryCategoryId = null;
    public string $itemStatus = GalleryItem::STATUS_DRAFT;
    public int $sortOrder = 0;
    public $itemUpload = null;
    public ?string $editingItemMediaUrl = null;

    public bool $showCreateCategory = false;
    public string $newCategoryName = '';
    public ?string $newCategoryDescription = null;

    public bool $showDeleteModal = false;
    public ?int $pendingDeleteItemId = null;
    public ?string $pendingDeleteItemTitle = null;
    public bool $showBulkDeleteModal = false;

    public function mount(): void
    {
        Gate::authorize('gallery.view');

        $preferences = AdminPreference::query()
            ->where('user_id', Auth::id())
            ->where('module', 'gallery')
            ->first();

        $preferredViewMode = data_get($preferences?->preferences, 'view_mode');

        if (is_string($preferredViewMode) && in_array($preferredViewMode, ['masonry', 'grid', 'list'], true)) {
            $this->viewMode = $preferredViewMode;
        }
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
        $this->loadError = false;
        $this->loadErrorMessage = null;
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['masonry', 'grid', 'list'], true)) {
            $this->setFeedback('Unsupported view mode.', 'error');
            return;
        }

        $this->viewMode = $mode;
        $this->persistViewModePreference();
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, ['all', ...self::STATUSES], true)) {
            return;
        }

        $this->status = $status;
        $this->resetPage();
    }

    public function setCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->resetPage();
    }

    public function bulkUpdate(string $status, array $ids): void
    {
        Gate::authorize('gallery.publish');

        if (empty($ids)) {
            $this->setFeedback('Select at least one item first.', 'error');
            return;
        }

        if (! in_array($status, self::STATUSES, true)) {
            $this->setFeedback('Unsupported status action.', 'error');
            return;
        }

        $now = now();

        DB::transaction(function () use ($ids, $status, $now): void {
            GalleryItem::query()->whereIn('id', $ids)->update([
                'status' => $status,
                'updated_at' => $now,
            ]);

            // Keep the original publish date on items that were already published;
            // only stamp items that have never been published. Never clear it.
            if ($status === GalleryItem::STATUS_PUBLISHED) {
                GalleryItem::query()
                    ->whereIn('id', $ids)
                    ->whereNull('published_at')
                    ->update(['published_at' => $now]);
            }
        });

        $this->setFeedback('Bulk update completed.', 'success');
        $this->resetPage();
        $this->dispatch('gallery-selection-reset');
    }

    public function updateItemStatus(int $id, string $status): void
    {
        Gate::authorize('gallery.publish');

        if (! in_array($status, self::STATUSES, true)) {
            $this->setFeedback('Unsupported status.', 'error');
            return;
        }

        $item = GalleryItem::query()->findOrFail($id);
        $item->update([
            'status' => $status,
            'published_at' => $status === GalleryItem::STATUS_PUBLISHED ? ($item->published_at ?? now()) : $item->published_at,
        ]);

        $this->setFeedback('Status updated to '.ucfirst($status).'.', 'success');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, ['all', ...self::STATUSES], true)) {
            $this->status = 'all';
        }
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function updatedDropUploads(): void
    {
        Gate::authorize('gallery.edit');

        if ($this->dropUploads === []) {
            $this->dropUploadDetails = [];
            return;
        }

        $this->validate([
            'dropUploads.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ]);

        $details = [];
        foreach ($this->dropUploads as $index => $upload) {
            $existing = $this->dropUploadDetails[$index] ?? [];
            $baseName = pathinfo((string) $upload->getClientOriginalName(), PATHINFO_FILENAME);
            $fallbackTitle = Str::title(str_replace(['-', '_'], ' ', $baseName)) ?: 'Untitled Media';

            $details[$index] = [
                'title' => (string) ($existing['title'] ?? $fallbackTitle),
                'alt_text' => $existing['alt_text'] ?? null,
                'caption' => $existing['caption'] ?? null,
                'gallery_category_id' => $existing['gallery_category_id'] ?? null,
                'status' => (string) ($existing['status'] ?? GalleryItem::STATUS_DRAFT),
                'sort_order' => (int) ($existing['sort_order'] ?? 0),
            ];
        }

        $this->dropUploadDetails = array_values($details);
    }

    public function removeStagedUpload(int $index): void
    {
        if (! array_key_exists($index, $this->dropUploads)) {
            return;
        }

        unset($this->dropUploads[$index], $this->dropUploadDetails[$index]);
        $this->dropUploads = array_values($this->dropUploads);
        $this->dropUploadDetails = array_values($this->dropUploadDetails);
    }

    public function clearStagedUploads(): void
    {
        $this->dropUploads = [];
        $this->dropUploadDetails = [];
    }

    public function uploadStagedUploads(): void
    {
        Gate::authorize('gallery.edit');

        if ($this->dropUploads === []) {
            $this->setFeedback('Select at least one file to upload.', 'error');
            return;
        }

        $this->validate([
            'dropUploads.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'dropUploadDetails.*.title' => ['required', 'string', 'max:255'],
            'dropUploadDetails.*.status' => ['required', 'in:'.implode(',', self::STATUSES)],
            'dropUploadDetails.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // Uploading straight to "published" is a publish action.
        if (collect($this->dropUploadDetails)->contains(fn ($details) => ($details['status'] ?? null) === GalleryItem::STATUS_PUBLISHED)) {
            Gate::authorize('gallery.publish');
        }

        $created = 0;

        foreach ($this->dropUploads as $index => $upload) {
            $details = $this->dropUploadDetails[$index] ?? null;

            if (! is_array($details)) {
                continue;
            }

            $baseName = pathinfo((string) $upload->getClientOriginalName(), PATHINFO_FILENAME);

            DB::transaction(function () use ($details, $upload, $baseName): void {
                $status = (string) ($details['status'] ?? GalleryItem::STATUS_DRAFT);
                $title = (string) ($details['title'] ?? 'Untitled Media');

                $item = GalleryItem::query()->create([
                    'gallery_category_id' => $details['gallery_category_id'] ?: null,
                    'title' => $title,
                    'alt_text' => $details['alt_text'] ?: null,
                    'caption' => $details['caption'] ?: null,
                    'status' => $status,
                    'sort_order' => (int) ($details['sort_order'] ?? 0),
                    'published_at' => $status === GalleryItem::STATUS_PUBLISHED ? now() : null,
                ]);

                $extension = $this->trustedImageExtension($upload);
                $sanitizedFileName = Str::slug($baseName ?: 'gallery-item').'-'.time().'-'.$item->id.'.'.$extension;

                $item->addMedia($upload->getRealPath())
                    ->usingName($item->title)
                    ->usingFileName($sanitizedFileName)
                    ->toMediaCollection('gallery');
            });

            $created++;
        }

        $this->dropUploads = [];
        $this->dropUploadDetails = [];
        $this->setFeedback($created.' file'.($created === 1 ? '' : 's').' uploaded to gallery.', 'success');
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetItemForm();
        $this->showItemModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetItemForm();
        
        $item = GalleryItem::query()->findOrFail($id);

        $this->editingItemId = $item->id;
        $this->title = (string) ($item->title ?? '');
        $this->altText = (string) ($item->alt_text ?? '');
        $this->caption = (string) ($item->caption ?? '');
        $this->galleryCategoryId = $item->gallery_category_id ? (int) $item->gallery_category_id : null;
        $this->itemStatus = (string) ($item->status ?? GalleryItem::STATUS_DRAFT);
        $this->sortOrder = (int) ($item->sort_order ?? 0);
        $this->editingItemMediaUrl = $item->getFirstMediaUrl('gallery') ?: null;
        $this->showItemModal = true;
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    public function saveItem(): void
    {
        Gate::authorize('gallery.edit');

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'altText' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'galleryCategoryId' => ['nullable', 'exists:gallery_categories,id'],
            'itemStatus' => ['required', 'in:'.implode(',', self::STATUSES)],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'itemUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ];

        if ($this->editingItemId === null) {
            $rules['itemUpload'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'];
        }

        $this->validate($rules);

        $item = $this->editingItemId
            ? GalleryItem::query()->findOrFail($this->editingItemId)
            : new GalleryItem();

        if ($this->itemStatus === GalleryItem::STATUS_PUBLISHED && $item->status !== GalleryItem::STATUS_PUBLISHED) {
            Gate::authorize('gallery.publish');
        }

        $item->fill([
            'gallery_category_id' => $this->galleryCategoryId,
            'title' => $this->title,
            'alt_text' => $this->altText,
            'caption' => $this->caption,
            'status' => $this->itemStatus,
            'sort_order' => $this->sortOrder,
            'published_at' => $this->itemStatus === GalleryItem::STATUS_PUBLISHED ? ($item->published_at ?? now()) : $item->published_at,
        ]);
        $item->save();

        if ($this->itemUpload) {
            $baseName = pathinfo((string) $this->itemUpload->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $this->trustedImageExtension($this->itemUpload);
            $sanitizedFileName = Str::slug($baseName ?: $item->title).'-'.time().'-'.$item->id.'.'.$extension;

            $item->addMedia($this->itemUpload->getRealPath())
                ->usingName($item->title)
                ->usingFileName($sanitizedFileName)
                ->toMediaCollection('gallery');
        }

        $this->setFeedback($this->editingItemId ? 'Gallery item updated.' : 'Gallery item created.', 'success');
        $this->closeItemModal();
    }

    public function createCategory(): void
    {
        Gate::authorize('gallery.edit');

        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = GalleryCategory::query()->create([
            'name' => $this->newCategoryName,
            'description' => $this->newCategoryDescription,
            'is_active' => true,
        ]);

        $this->galleryCategoryId = $category->id;
        $this->showCreateCategory = false;
        $this->newCategoryName = '';
        $this->newCategoryDescription = null;
        $this->setFeedback('Category created.', 'success');
    }

    public function promptDelete(int $id): void
    {
        $item = GalleryItem::query()->findOrFail($id);
        $this->pendingDeleteItemId = $item->id;
        $this->pendingDeleteItemTitle = $item->title;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        Gate::authorize('gallery.edit');

        if ($this->pendingDeleteItemId === null) return;

        $item = GalleryItem::query()->findOrFail($this->pendingDeleteItemId);
        $item->clearMediaCollection('gallery');
        $item->delete();

        $this->showDeleteModal = false;
        $this->pendingDeleteItemId = null;
        $this->pendingDeleteItemTitle = null;
        $this->toastSuccess('Gallery item deleted.', 'Gallery');
        $this->resetPage();
    }

    public function confirmBulkDelete(array $ids): void
    {
        Gate::authorize('gallery.edit');

        if (empty($ids)) return;

        $items = GalleryItem::query()->whereIn('id', $ids)->get();

        foreach ($items as $item) {
            $item->clearMediaCollection('gallery');
            $item->delete();
        }

        $this->showBulkDeleteModal = false;
        $this->setFeedback(count($items).' items deleted.', 'success');
        $this->resetPage();
        $this->dispatch('gallery-selection-reset');
    }

    public function render()
    {
        $items = GalleryItem::query()->whereRaw('1 = 0')->paginate(18);
        $categories = GalleryCategory::query()->where('is_active', true)->orderBy('name')->get();

        try {
            if ($this->readyToLoad) {
                $query = $this->galleryQuery();
                $this->matchingItemsCount = (clone $query)->count();
                $items = $query->paginate(18);
                $this->pageItemIds = $items->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
            }

            $this->refreshStatusTotals();
            $this->refreshCategoryTotals();
        } catch (\Throwable $exception) {
            report($exception);
            $this->loadError = true;
            $this->loadErrorMessage = 'Unable to load gallery data right now.';
        }

        return view('livewire.admin.gallery.index', compact('items', 'categories'))
            ->layout('layouts.admin');
    }

    protected function refreshStatusTotals(): void
    {
        $totals = $this->baseGalleryQuery()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereIn('status', self::STATUSES)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $this->statusTotals = [
            GalleryItem::STATUS_DRAFT => (int) ($totals[GalleryItem::STATUS_DRAFT] ?? 0),
            GalleryItem::STATUS_PUBLISHED => (int) ($totals[GalleryItem::STATUS_PUBLISHED] ?? 0),
            GalleryItem::STATUS_ARCHIVED => (int) ($totals[GalleryItem::STATUS_ARCHIVED] ?? 0),
        ];
    }

    protected function refreshCategoryTotals(): void
    {
        $query = $this->baseGalleryQuery();
        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        $this->categoryTotals = $query
            ->selectRaw('gallery_category_id, COUNT(*) as aggregate')
            ->groupBy('gallery_category_id')
            ->pluck('aggregate', 'gallery_category_id')
            ->map(fn ($value) => (int) $value)
            ->toArray();
    }

    protected function persistViewModePreference(): void
    {
        if (! Auth::check()) return;

        AdminPreference::query()->updateOrCreate(
            ['user_id' => Auth::id(), 'module' => 'gallery'],
            ['preferences' => ['view_mode' => $this->viewMode]]
        );
    }

    protected function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->title = '';
        $this->altText = '';
        $this->caption = '';
        $this->galleryCategoryId = null;
        $this->itemStatus = GalleryItem::STATUS_DRAFT;
        $this->sortOrder = 0;
        $this->itemUpload = null;
        $this->editingItemMediaUrl = null;
        $this->showCreateCategory = false;
        $this->newCategoryName = '';
        $this->newCategoryDescription = null;
        $this->resetValidation();
    }

    protected function galleryQuery(): Builder
    {
        $query = $this->baseGalleryQuery();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->orderByDesc('id');
    }

    protected function baseGalleryQuery(): Builder
    {
        $query = GalleryItem::query()->with(['category', 'media']);

        if ($this->search !== '') {
            $query->where(function (Builder $builder): void {
                $builder->where('title', 'like', "%{$this->search}%")
                    ->orWhere('caption', 'like', "%{$this->search}%")
                    ->orWhere('alt_text', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryId !== null) {
            $query->where('gallery_category_id', $this->categoryId);
        }

        return $query;
    }

    protected function trustedImageExtension(TemporaryUploadedFile $upload): string
    {
        $extension = strtolower((string) $upload->guessExtension());
        return in_array($extension, ['png', 'webp', 'gif'], true) ? $extension : 'jpg';
    }

    protected function setFeedback(string $message, string $tone = 'success'): void
    {
        match (strtolower($tone)) {
            'success' => $this->toastSuccess($message, 'Gallery'),
            'error' => $this->toastError($message, 'Gallery'),
            default => $this->toastInfo($message, 'Gallery'),
        };
    }
}
