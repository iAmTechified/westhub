<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_TRASHED = 'trashed';

    protected $fillable = [
        'article_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'headline_image_path',
        'headline_image_alt',
        'headline_image_title',
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
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (self $article): void {
            if ($article->headline_image_path) {
                Storage::disk('public')->delete($article->headline_image_path);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class)->latest('version');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function seoMetrics()
    {
        return $this->morphMany(SeoMetric::class, 'entity');
    }

    public function headlineImageUrl(): ?string
    {
        if (! $this->headline_image_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->headline_image_path);
    }

}
