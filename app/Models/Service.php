<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    use UsesContentConnection;

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

    public function counties()
    {
        return $this->belongsToMany(County::class, 'service_county')->withPivot('availability_status')->withTimestamps();
    }

    public function townships()
    {
        return $this->belongsToMany(Township::class, 'service_township')->withPivot('availability_status')->withTimestamps();
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
