<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinRequestEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'join_request_id',
        'actor_id',
        'event_type',
        'old_status',
        'new_status',
        'note',
        'meta',
        'event_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'event_at' => 'datetime',
    ];

    public function joinRequest()
    {
        return $this->belongsTo(JoinRequest::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
