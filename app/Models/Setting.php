<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_encrypted',
        'updated_by',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];
}
