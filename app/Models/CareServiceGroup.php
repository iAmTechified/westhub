<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CareServiceGroup extends Model
{
    use HasFactory;
    use HasSlug;
    use UsesContentConnection;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'is_active',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(CareServiceItem::class, 'care_service_group_id')->orderBy('sort_order');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }
}
