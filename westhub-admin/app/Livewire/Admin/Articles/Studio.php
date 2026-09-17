<?php

namespace App\Livewire\Admin\Articles;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleRevision;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Studio extends Component
{
    use WithFileUploads;

    public ?Article $article = null;
    public bool $embedded = false;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public ?string $excerpt = null;

    #[Validate('nullable|string')]
    public ?string $body = null;

    public ?int $article_category_id = null;
    public ?string $status = null;
    public string $draftNonce = '';

    public bool $showCreateCategory = false;
    public string $newCategoryName = '';
    public ?string $newCategoryDescription = null;

    public $headlineImageUpload = null;
    public ?string $headline_image_alt = null;
    public ?string $headline_image_title = null;
    public bool $removeHeadlineImage = false;

    public string $saveState = 'Saved';
    public bool $hasUnsavedChanges = false;
    public ?string $latestSaveRequestToken = null;
    public ?string $categoryFeedback = null;

    public function mount(?Article $article = null): void
    {
        Gate::authorize('articles.edit');

        if ($article?->exists) {
            $this->article = $article;
            $this->title = $article->title;
            $this->excerpt = $article->excerpt;
            $this->body = $article->body;
            $this->article_category_id = $article->article_category_id;
            $this->status = $article->status;
            $this->headline_image_alt = $article->headline_image_alt;
            $this->headline_image_title = $article->headline_image_title;
            $this->draftNonce = 'article-'.$article->id;
        } else {
            $this->status = Article::STATUS_DRAFT;
            $this->draftNonce = (string) Str::uuid();
        }
    }

    public function updated($name): void
    {
        if (in_array($name, ['title', 'excerpt', 'body', 'article_category_id', 'headlineImageUpload'], true)) {
            $this->saveState = 'Unsaved changes';
            $this->hasUnsavedChanges = true;
        }
    }

    public function updatedHeadlineImageUpload(): void
    {
        $this->validate([
            'headlineImageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $this->removeHeadlineImage = false;
        $this->saveState = 'Unsaved changes';
        $this->hasUnsavedChanges = true;
    }

    public function autosaveFromInteraction(?string $requestToken = null): void
    {
        Gate::authorize('articles.edit');

        if (! $this->hasUnsavedChanges || ! $this->canAutosave()) {
            return;
        }

        $targetStatus = $this->article?->status ?: Article::STATUS_DRAFT;
        $this->persist($targetStatus, false, false, true, 'autosave', $requestToken);
    }

    public function autosave(): void
    {
        Gate::authorize('articles.edit');

        $this->autosaveFromInteraction();
    }

    public function saveDraft(?string $requestToken = null): void
    {
        Gate::authorize('articles.edit');

        $targetStatus = $this->article?->status === Article::STATUS_PUBLISHED
            ? Article::STATUS_PUBLISHED
            : Article::STATUS_DRAFT;

        $this->persist($targetStatus, true, true, false, 'draft', $requestToken);
    }

    public function publish(?string $requestToken = null): void
    {
        Gate::authorize('articles.publish');

        $this->persist(Article::STATUS_PUBLISHED, true, true, false, 'publish', $requestToken);
    }

    public function restore(): void
    {
        Gate::authorize('articles.delete');

        if (! $this->article) {
            return;
        }

        // deleted_at is not fillable, so it must be cleared through SoftDeletes::restore().
        if ($this->article->trashed()) {
            $this->article->restore();
        }

        $this->article->update([
            'status' => Article::STATUS_DRAFT,
            'last_edited_at' => now(),
            'last_saved_at' => now(),
        ]);

        $this->status = Article::STATUS_DRAFT;
        $this->saveState = 'Restored';
        $this->dispatch('admin-toast', message: 'Article restored to draft.', type: 'info');
    }

    public function createCategory(): void
    {
        Gate::authorize('articles.edit');

        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $category = ArticleCategory::create([
            'name' => $this->newCategoryName,
            'description' => $this->newCategoryDescription,
            'is_active' => true,
        ]);

        $this->article_category_id = $category->id;
        $this->newCategoryName = '';
        $this->newCategoryDescription = null;
        $this->showCreateCategory = false;
        $this->categoryFeedback = 'Saved';
        $this->hasUnsavedChanges = true;
    }

    public function removeHeadlineImageNow(): void
    {
        Gate::authorize('articles.edit');

        $this->removeHeadlineImage = true;
        $this->headlineImageUpload = null;
        $this->saveState = 'Unsaved changes';
        $this->hasUnsavedChanges = true;
    }

    protected function canAutosave(): bool
    {
        return trim($this->title) !== ''
            || trim((string) $this->excerpt) !== ''
            || trim(strip_tags((string) $this->body)) !== ''
            || $this->article_category_id !== null
            || $this->headlineImageUpload !== null
            || $this->removeHeadlineImage;
    }

    protected function persist(
        string $targetStatus,
        bool $redirectAfter,
        bool $withFeedback,
        bool $isAutosave,
        string $action,
        ?string $requestToken = null
    ): void
    {
        // Every save path funnels through here; a publish-level status must never be
        // written (e.g. via autosave or saveDraft) without publish rights.
        if (in_array($targetStatus, [Article::STATUS_PUBLISHED, Article::STATUS_SCHEDULED], true)) {
            Gate::authorize('articles.publish');
        }

        $this->registerSaveRequest($requestToken);

        if (! $this->isCurrentSaveRequest($requestToken)) {
            return;
        }

        if (! $isAutosave || trim($this->title) !== '') {
            $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'excerpt' => ['nullable', 'string', 'max:500'],
                'body' => ['nullable', 'string'],
                'article_category_id' => ['nullable', 'exists:article_categories,id'],
                'headlineImageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'headline_image_alt' => ['nullable', 'string', 'max:255'],
                'headline_image_title' => ['nullable', 'string', 'max:255'],
            ]);
        }

        if ($isAutosave && trim($this->title) === '') {
            return;
        }

        if ($action === 'publish') {
            $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string'],
                'article_category_id' => ['required', 'exists:article_categories,id'],
            ]);
        }

        $this->saveState = 'Saving...';

        $didPersist = false;

        try {
            Cache::lock($this->draftLockKey(), 10)->block(5, function () use ($targetStatus, $requestToken, &$didPersist): void {
                if (! $this->isCurrentSaveRequest($requestToken)) {
                    return;
                }

                $article = $this->resolveWorkingArticle();
                $publishedAt = $targetStatus === Article::STATUS_PUBLISHED
                    ? ($article->published_at ?: now())
                    : $article->published_at;

                $headlineImagePath = $article->headline_image_path;
                $oldHeadlineImagePath = $article->headline_image_path;
                $stagedHeadlineImagePath = null;
                $appliedHeadlineImageRemoval = false;

                if ($this->headlineImageUpload) {
                    $trustedExtension = $this->trustedImageExtension($this->headlineImageUpload);
                    $fileName = Str::uuid().'.'.$trustedExtension;
                    $stagedHeadlineImagePath = $this->headlineImageUpload->storeAs('articles/headlines', $fileName, 'public');
                    $headlineImagePath = $stagedHeadlineImagePath;
                }

                if ($this->removeHeadlineImage && $headlineImagePath) {
                    $headlineImagePath = null;
                    $appliedHeadlineImageRemoval = true;
                }

                if (! $this->isCurrentSaveRequest($requestToken)) {
                    if ($stagedHeadlineImagePath) {
                        Storage::disk('public')->delete($stagedHeadlineImagePath);
                    }

                    return;
                }

                try {
                    $article->fill([
                        'title' => $this->title,
                        'excerpt' => $this->excerpt,
                        'body' => $this->body,
                        'article_category_id' => $this->article_category_id,
                        'headline_image_path' => $headlineImagePath,
                        'headline_image_alt' => $this->headline_image_alt,
                        'headline_image_title' => $this->headline_image_title,
                        'status' => $targetStatus,
                        'published_at' => $publishedAt,
                        'scheduled_for' => null,
                        'last_saved_at' => now(),
                        'last_edited_at' => now(),
                    ]);

                    $article->save();
                } catch (\Throwable $exception) {
                    if ($stagedHeadlineImagePath) {
                        Storage::disk('public')->delete($stagedHeadlineImagePath);
                    }

                    throw $exception;
                }

                $this->article = $article;
                Cache::put($this->draftMapKey(), $article->id, now()->addHours(6));
                $didPersist = true;

                // Only drop the pending upload / removal flag once the article row is persisted.
                if ($stagedHeadlineImagePath) {
                    $this->headlineImageUpload = null;
                }

                if ($appliedHeadlineImageRemoval) {
                    $this->removeHeadlineImage = false;
                }

                if ($oldHeadlineImagePath && $oldHeadlineImagePath !== $headlineImagePath) {
                    Storage::disk('public')->delete($oldHeadlineImagePath);
                }

                $this->recordRevision($article);
            });
        } catch (LockTimeoutException $exception) {
            report($exception);
            $this->dispatch('admin-toast', message: 'Save request is still in progress. Please try again.', type: 'warning');

            return;
        }

        if (! $didPersist || ! $this->isCurrentSaveRequest($requestToken)) {
            return;
        }

        $this->status = $targetStatus;
        if ($isAutosave) {
            $this->saveState = 'Saved';
        } else {
            $this->saveState = 'Saved';
        }

        if ($withFeedback) {
            $msg = 'Draft saved.';
            if ($action === 'publish') {
                $msg = 'Article published.';
            } elseif ($targetStatus === Article::STATUS_PUBLISHED) {
                $msg = 'Published article updated.';
            }
            $this->dispatch('admin-toast', message: $msg, type: 'success');
        }

        $this->hasUnsavedChanges = false;

        if (! $this->embedded && $redirectAfter && request()->routeIs('admin.articles.create') && $this->article?->exists) {
            $this->redirectRoute('admin.articles.edit', ['article' => $this->article->id], navigate: true);
        }
    }

    protected function recordRevision(Article $article): void
    {
        $payload = [
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'body' => $article->body,
            'status' => $article->status,
            'article_category_id' => $article->article_category_id,
            'headline_image_alt' => $article->headline_image_alt,
            'headline_image_title' => $article->headline_image_title,
        ];

        // Serialize revision numbering per article so concurrent editors of the same
        // article cannot compute the same max(version) + 1.
        Cache::lock($this->revisionLockKey($article), 10)->block(5, function () use ($article, $payload): void {
            $latest = ArticleRevision::query()
                ->where('article_id', $article->id)
                ->orderByDesc('version')
                ->first();

            // Skip no-op revisions (e.g. repeated autosaves with identical content).
            if ($latest && $this->normalizeRevisionPayload((array) $latest->payload_json) === $this->normalizeRevisionPayload($payload)) {
                return;
            }

            $attempts = 0;

            while (true) {
                $nextVersion = (int) ArticleRevision::query()->where('article_id', $article->id)->max('version') + 1;

                try {
                    ArticleRevision::create([
                        'article_id' => $article->id,
                        'saved_by' => Auth::id(),
                        'version' => $nextVersion,
                        'payload_json' => $payload,
                        'saved_at' => now(),
                    ]);

                    return;
                } catch (UniqueConstraintViolationException $exception) {
                    // Another writer took this version number; recompute and retry once.
                    if (++$attempts > 1) {
                        throw $exception;
                    }
                }
            }
        });
    }

    protected function normalizeRevisionPayload(array $payload): array
    {
        $normalized = [];

        foreach (['title', 'excerpt', 'body', 'status', 'article_category_id', 'headline_image_alt', 'headline_image_title'] as $key) {
            $value = $payload[$key] ?? null;
            $normalized[$key] = $value === null ? null : (string) $value;
        }

        return $normalized;
    }

    protected function revisionLockKey(Article $article): string
    {
        if ($article->getKey()) {
            return 'article-studio:revision-lock:article-'.$article->getKey();
        }

        return 'article-studio:revision-lock:'.Auth::id().':'.$this->draftNonce;
    }

    protected function registerSaveRequest(?string $requestToken): void
    {
        if (! $requestToken) {
            return;
        }

        $this->latestSaveRequestToken = $requestToken;
        Cache::put($this->draftRequestTokenKey(), $requestToken, now()->addMinutes(20));
    }

    protected function isCurrentSaveRequest(?string $requestToken): bool
    {
        if (! $requestToken) {
            return true;
        }

        $currentToken = Cache::get($this->draftRequestTokenKey());
        return $currentToken === $requestToken;
    }

    protected function draftMapKey(): string
    {
        return 'article-studio:draft-map:'.Auth::id().':'.$this->draftNonce;
    }

    protected function draftLockKey(): string
    {
        return 'article-studio:draft-lock:'.Auth::id().':'.$this->draftNonce;
    }

    protected function draftRequestTokenKey(): string
    {
        return 'article-studio:save-token:'.Auth::id().':'.$this->draftNonce;
    }

    protected function resolveWorkingArticle(): Article
    {
        if ($this->article?->exists) {
            return $this->article;
        }

        $mappedId = Cache::get($this->draftMapKey());
        if ($mappedId) {
            $existing = Article::query()->find($mappedId);
            if ($existing) {
                return $existing;
            }
        }

        return new Article();
    }

    public function render()
    {
        $categories = ArticleCategory::query()->where('is_active', true)->orderBy('name')->get();

        $view = view('livewire.admin.articles.studio', compact('categories'));

        if ($this->embedded) {
            return $view;
        }

        return $view->layout('layouts.admin');
    }

    public function headlineImageUrl(?string $path = null): ?string
    {
        $sourcePath = $path ?: $this->article?->headline_image_path;

        if (! $sourcePath) {
            return null;
        }

        if (Str::startsWith($sourcePath, ['http://', 'https://', '//'])) {
            return $sourcePath;
        }

        $relativePath = ltrim($sourcePath, '/');
        if (Str::startsWith($relativePath, 'storage/')) {
            $relativePath = substr($relativePath, strlen('storage/'));
        }

        return Storage::disk('public')->url($relativePath);
    }

    protected function trustedImageExtension(TemporaryUploadedFile $upload): string
    {
        $extension = strtolower((string) $upload->guessExtension());

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            return 'jpg';
        }

        if (in_array($extension, ['png', 'webp', 'gif'], true)) {
            return $extension;
        }

        return 'jpg';
    }

    public function getSlugPreviewProperty(): string
    {
        if ($this->article?->slug) {
            return $this->article->slug;
        }

        return Str::slug((string) $this->title);
    }
}
