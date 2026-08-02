<?php

namespace App\Livewire\Admin\Articles;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\AdminPreference;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use InteractsWithAdminToast;

    private const ALLOWED_STATUSES = ['draft', 'published', 'archived', 'trashed'];

    public bool $readyToLoad = true;
    public bool $loadError = false;
    public ?string $loadErrorMessage = null;

    public string $search = '';
    public string $status = 'draft';
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';
    public string $viewMode = 'table';
    public ?int $categoryId = null;
    public ?int $previewArticleId = null;
    public ?int $editingArticleId = null;
    public int $createArticleModalKey = 0;

    public ?int $editingCategoryId = null;
    public ?int $deletingCategoryId = null;
    public ?int $deletingArticleId = null;
    public string $categoryName = '';
    public ?string $categoryDescription = null;
    protected $paginationView = 'livewire.admin-pagination';

    public function loadData(): void
    {
        $this->readyToLoad = true;
        $this->loadError = false;
        $this->loadErrorMessage = null;
    }

    public function mount(): void
    {
        $pref = AdminPreference::query()
            ->where('user_id', Auth::id())
            ->where('module', 'articles.index')
            ->first();

        if ($pref) {
            $data = $pref->preferences;
            $this->viewMode = $data['view_mode'] ?? $this->viewMode;
            $this->sortBy = $data['sort_by'] ?? $this->sortBy;
            $this->sortDirection = $data['sort_direction'] ?? $this->sortDirection;
            $this->status = in_array(($data['status'] ?? 'draft'), self::ALLOWED_STATUSES, true)
                ? $data['status']
                : 'draft';
            $this->categoryId = isset($data['category_id']) && is_numeric($data['category_id'])
                ? (int) $data['category_id']
                : null;
        }
    }

    public function setViewMode(string $viewMode): void
    {
        if (! in_array($viewMode, ['table', 'cards', 'list', 'grid'], true)) {
            return;
        }

        $this->viewMode = $viewMode;
    }

    public function saveViewModePreference(string $viewMode): void
    {
        if (! in_array($viewMode, ['table', 'cards', 'list', 'grid'], true)) {
            return;
        }

        $this->viewMode = $viewMode;
        $this->storePreference();
        $this->skipRender();
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            return;
        }

        $this->status = $status;
        $this->resetPage();
        $this->storePreference();
    }

    public function setCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->resetPage();
        $this->storePreference();
    }

    public function updatedStatus(string $status): void
    {
        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            $this->status = 'draft';
        }

        $this->resetPage();
        $this->storePreference();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(string $sortBy): void
    {
        $allowedSorts = ['title', 'published_at', 'updated_at', 'created_at', 'status'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $this->sortBy = 'updated_at';
        }

        $this->resetPage();
        $this->storePreference();
    }

    public function updatedSortDirection(string $sortDirection): void
    {
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
        $this->storePreference();
    }

    public function setSort(string $field): void
    {
        $allowedSorts = ['title', 'published_at', 'updated_at', 'created_at', 'status'];
        if (! in_array($field, $allowedSorts, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = in_array($field, ['title', 'status'], true) ? 'asc' : 'desc';
        }

        $this->resetPage();
        $this->storePreference();
    }

    public function toggleSort(string $sortBy): void
    {
        if ($this->sortBy === $sortBy) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $sortBy;
            $this->sortDirection = 'desc';
        }

        $this->storePreference();
    }

    public function quickUpdate(int $articleId, string $field, string $value, int $sequence = 0): void
    {
        if (! in_array($field, ['title', 'status'], true)) {
            return;
        }

        if ($field === 'status' && ! in_array($value, self::ALLOWED_STATUSES, true)) {
            return;
        }

        if ($field === 'title') {
            $value = trim($value);
            if ($value === '') {
                return;
            }
        }

        $sequenceKey = $this->inlineSequenceKey($articleId, $field);
        $latestSequence = (int) Cache::get($sequenceKey, 0);
        if ($sequence > 0 && $sequence < $latestSequence) {
            return;
        }

        if ($sequence > 0) {
            Cache::put($sequenceKey, $sequence, now()->addHours(6));
        }

        try {
            Cache::lock($this->inlineLockKey($articleId, $field), 5)->block(2, function () use ($articleId, $field, $value, $sequence, $sequenceKey): void {
                if ($sequence > 0) {
                    $latestSequence = (int) Cache::get($sequenceKey, 0);
                    if ($sequence < $latestSequence) {
                        return;
                    }
                }

                Article::query()->whereKey($articleId)->update([
                    $field => $value,
                    'last_edited_at' => now(),
                    'last_saved_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (LockTimeoutException $exception) {
            report($exception);
        }

        $this->toastSuccess('Article updated.', 'Article');
    }

    public function openCreateCategoryModal(): void
    {
        $this->resetCategoryForm();
    }

    public function openEditCategoryModal(int $categoryId): void
    {
        $category = ArticleCategory::find($categoryId);
        if ($category) {
            $this->editingCategoryId = $categoryId;
            $this->categoryName = $category->name;
            $this->categoryDescription = $category->description;
        }
    }

    public function openDeleteCategoryModal(int $categoryId): void
    {
        $this->deletingCategoryId = $categoryId;
    }

    public function closeCategoryModals(): void
    {
        $this->resetCategoryForm();
    }

    public function createCategory(): void
    {
        $data = $this->validateCategory();

        ArticleCategory::create([
            'name' => $data['categoryName'],
            'description' => $data['categoryDescription'],
            'is_active' => true,
        ]);

        $this->toastSuccess('Category created.', 'Category');
        $this->closeCategoryModals();
        $this->dispatch('category-created');
    }

    public function updateCategory(): void
    {
        if (! $this->editingCategoryId) {
            return;
        }

        $data = $this->validateCategory();
        $category = ArticleCategory::find($this->editingCategoryId);
        if (! $category) {
            return;
        }

        $category->update([
            'name' => $data['categoryName'],
            'description' => $data['categoryDescription'],
        ]);

        $this->toastSuccess('Category updated.', 'Category');
        $this->closeCategoryModals();
        $this->dispatch('category-updated');
    }

    public function deleteCategory(): void
    {
        if (! $this->deletingCategoryId) {
            return;
        }

        $category = ArticleCategory::find($this->deletingCategoryId);
        if ($category) {
            $category->delete();
        }

        if ($this->categoryId === $this->deletingCategoryId) {
            $this->categoryId = null;
        }

        $this->toastSuccess('Category deleted.', 'Category');
        $this->closeCategoryModals();
        $this->dispatch('category-deleted');
    }

    public function retryLoading(): void
    {
        $this->loadData();
    }

    public function openPreview(int $articleId): void
    {
        $this->previewArticleId = $articleId;
    }

    public function openCreateArticleModal(): void
    {
        $this->editingArticleId = null;
        $this->createArticleModalKey++;
    }

    public function openEditArticleModal(int $articleId): void
    {
        $this->editingArticleId = $articleId;
        $this->createArticleModalKey++;
    }

    public function closeCreateArticleModal(): void
    {
        $this->editingArticleId = null;
    }

    public function closePreview(): void
    {
        $this->previewArticleId = null;
    }

    public function confirmDeleteArticle(int $articleId): void
    {
        $this->deletingArticleId = $articleId;
    }

    public function deleteArticle(): void
    {
        if (! $this->deletingArticleId) {
            return;
        }

        $article = Article::query()->find($this->deletingArticleId);
        if ($article) {
            $article->status = Article::STATUS_TRASHED;
            $article->last_edited_at = now();
            $article->last_saved_at = now();
            $article->save();
            $article->delete();
            $this->toastSuccess('Article moved to trash.', 'Article');
            $this->dispatch('article-deleted');
        }

        $this->deletingArticleId = null;
    }

    public function restoreArticle(int $articleId): void
    {
        $article = Article::query()->onlyTrashed()->find($articleId);
        if (! $article) {
            return;
        }

        $article->restore();
        $article->update([
            'status' => Article::STATUS_DRAFT,
            'last_edited_at' => now(),
            'last_saved_at' => now(),
        ]);

        $this->toastSuccess('Article restored from trash.', 'Article');
    }

    public function forceDeleteArticle(int $articleId): void
    {
        $article = Article::query()->onlyTrashed()->find($articleId);
        if (! $article) {
            return;
        }

        $article->forceDelete();
        $this->toastSuccess('Article permanently deleted.', 'Article');
    }

    protected function storePreference(): void
    {
        AdminPreference::updateOrCreate(
            ['user_id' => Auth::id(), 'module' => 'articles.index'],
            [
                'preferences' => [
                    'view_mode' => $this->viewMode,
                    'sort_by' => $this->sortBy,
                    'sort_direction' => $this->sortDirection,
                    'status' => $this->status,
                    'category_id' => $this->categoryId,
                ],
            ]
        );
    }

    protected function validateCategory(): array
    {
        return $this->validate([
            'categoryName' => ['required', 'string', 'min:2', 'max:255'],
            'categoryDescription' => ['nullable', 'string', 'max:500'],
        ], [], [
            'categoryName' => 'name',
            'categoryDescription' => 'description',
        ]);
    }

    protected function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->deletingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = null;
    }

    protected function inlineSequenceKey(int $articleId, string $field): string
    {
        return 'articles:inline-seq:'.Auth::id().':'.$articleId.':'.$field;
    }

    protected function inlineLockKey(int $articleId, string $field): string
    {
        return 'articles:inline-lock:'.$articleId.':'.$field;
    }

    public function render()
    {
        $articles = Article::query()->whereRaw('1 = 0')->paginate(12);
        $categories = collect();
        $previewArticle = null;
        $modalArticle = null;

        try {
            if ($this->readyToLoad) {
                $query = Article::query()->with('category');

                if ($this->status === 'trashed') {
                    $query->onlyTrashed()->where('status', Article::STATUS_TRASHED);
                }

                if ($this->search !== '') {
                    $query->where(function ($builder) {
                        $builder->where('title', 'like', "%{$this->search}%")
                            ->orWhere('excerpt', 'like', "%{$this->search}%");
                    });
                }

                if ($this->status !== 'trashed') {
                    if ($this->status === 'draft') {
                        $query->whereIn('status', [Article::STATUS_DRAFT, Article::STATUS_IN_REVIEW, Article::STATUS_SCHEDULED]);
                    } else {
                        $query->where('status', $this->status);
                    }
                }

                if ($this->categoryId) {
                    $query->where('article_category_id', $this->categoryId);
                }

                $allowedSorts = ['title', 'published_at', 'updated_at', 'created_at', 'status'];
                $sortBy = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'updated_at';
                $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

                $articles = $query->orderBy($sortBy, $sortDirection)->paginate(12);
                $categories = ArticleCategory::query()->where('is_active', true)->orderBy('name')->get();
                $previewArticle = $this->previewArticleId ? Article::find($this->previewArticleId) : null;
                $modalArticle = ($this->editingArticleId !== null) ? Article::query()->find($this->editingArticleId) : null;
            }

            $this->loadError = false;
            $this->loadErrorMessage = null;
        } catch (\Throwable $exception) {
            report($exception);
            $this->loadError = true;
            $this->loadErrorMessage = 'Unable to load article data right now.';
        }

        return view('livewire.admin.articles.index', [
            'articles' => $articles,
            'categories' => $categories,
            'previewArticle' => $previewArticle,
            'modalArticle' => $modalArticle,
        ])
            ->layout('layouts.admin');
    }
}
