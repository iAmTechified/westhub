<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'event_name',
        'event_group',
        'entity_type',
        'entity_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];
}
