<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutboundMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'join_request_id',
        'appointment_id',
        'recipient_email',
        'template_key',
        'provider',
        'status',
        'subject',
        'body',
        'meta',
        'provider_response',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function joinRequest()
    {
        return $this->belongsTo(JoinRequest::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
