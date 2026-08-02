<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'author_name',
        'author_role',
        'quote',
        'rating',
        'avatar_path',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];
}
