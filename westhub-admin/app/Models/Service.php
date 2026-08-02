<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Service extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'description',
        'icon',
        'status',
        'meta_title',
        'meta_description',
        'is_active',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function counties()
    {
        return $this->belongsToMany(County::class)->withPivot('availability_status')->withTimestamps();
    }

    public function townships()
    {
        return $this->belongsToMany(Township::class)->withPivot('availability_status')->withTimestamps();
    }

    public function careServiceItems()
    {
        return $this->hasMany(CareServiceItem::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
