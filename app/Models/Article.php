<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use UsesContentConnection;
    
    /** @var array<string, bool> Per-request cache for Schema::hasColumn checks. */
    protected static array $columnExists = [];

    protected $fillable = [
        'article_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'headline_image_path',
        'status',
        'seo_title',
        'seo_description',
        'canonical_url',
        'focus_keyword',
        'og_image',
        'read_time',
        'last_saved_at',
        'last_edited_at',
        'published_at',
        'scheduled_for',
    ];

    protected $casts = [
        'last_saved_at' => 'datetime',
        'last_edited_at' => 'datetime',
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'meta_tags' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function scopePubliclyVisible($query)
    {
        if ($this->hasContentColumn('status')) {
            $query->where('status', 'published');
        }

        if ($this->hasContentColumn('deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function categoryModel()
    {
        return $this->category();
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class)->latest('version');
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->hasContentColumn('status') && $this->status !== 'published') {
            return false;
        }

        if ($this->hasContentColumn('deleted_at') && $this->deleted_at !== null) {
            return false;
        }

        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function headlineImageUrl(): ?string
    {
        if (! $this->headline_image_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->headline_image_path);
    }

    public function getCategoryNameAttribute(): string
    {
        if ($this->relationLoaded('category') && $this->category) {
            return $this->category->name;
        }

        if ($this->article_category_id && $this->category) {
            return $this->category->name;
        }

        return (string) ($this->getRawOriginal('category') ?: 'Healthcare');
    }

    public function imageUrl(): string
    {
        return $this->assetUrl($this->headline_image_path ?: $this->image_path)
            ?: asset('assets/images/Scaled down - side-view-smiley-nurse-talking-patient 1.png');
    }

    public function ogImageUrl(): string
    {
        return $this->assetUrl($this->og_image) ?: $this->imageUrl();
    }

    public function authorImageUrl(): string
    {
        return $this->assetUrl($this->author_image)
            ?: asset('assets/icons/Logo=Default.svg');
    }

    public function authorDisplayName(): string
    {
        return (string) ($this->author_name ?: 'Westhub');
    }

    public function authorDisplayRole(): string
    {
        return (string) ($this->author_role ?: 'Healthcare Excellence');
    }

    public function authorDisplayBio(): string
    {
        return (string) ($this->author_bio ?: 'Westhub Healthcare is dedicated to providing compassionate, high-quality home healthcare services, ensuring patients receive the best care in the comfort of their homes.');
    }

    public function renderedBody(): string
    {
        $body = (string) $this->body;

        if (trim($body) === '') {
            return '';
        }

        if (Str::contains($body, ['<p', '<h', '<ul', '<ol', '<li', '<blockquote', '<br'])) {
            return $body;
        }

        return Str::markdown($body);
    }

    protected function assetUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        // Bundled public assets are served from the app itself.
        if (Str::startsWith($path, ['/assets/', 'assets/'])) {
            return asset(ltrim($path, '/'));
        }

        // Everything else is storage-relative; resolve it through the public disk so a
        // configured PUBLIC_STORAGE_URL (e.g. a separate storage host) is honoured.
        $relativePath = ltrim($path, '/');
        if (Str::startsWith($relativePath, 'storage/')) {
            $relativePath = substr($relativePath, strlen('storage/'));
        }

        $url = Storage::disk('public')->url($relativePath);

        // A root-relative disk URL (the default "/storage") stays absolute, as before, for OG tags etc.
        return Str::startsWith($url, ['http://', 'https://', '//']) ? $url : asset(ltrim($url, '/'));
    }

    protected function hasContentColumn(string $column): bool
    {
        $connection = $this->getConnectionName();
        $table = $this->getTable();
        $key = "{$connection}.{$table}.{$column}";

        if (! array_key_exists($key, self::$columnExists)) {
            self::$columnExists[$key] = Schema::connection($connection)->hasColumn($table, $column);
        }

        return self::$columnExists[$key];
    }
}
