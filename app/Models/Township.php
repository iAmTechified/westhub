<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Township extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'county_id',
        'name',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_township')->withPivot('availability_status')->withTimestamps();
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
