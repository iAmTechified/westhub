<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPreference extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'user_id',
        'module',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];
}
