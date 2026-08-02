<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CareServiceItem extends Model
{
    use HasFactory;
    use HasSlug;
    use UsesContentConnection;

    protected $fillable = [
        'care_service_group_id',
        'service_id',
        'title',
        'slug',
        'subtitle',
        'description',
        'icon',
        'status',
        'is_active',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(CareServiceGroup::class, 'care_service_group_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }
}
