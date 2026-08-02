<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentEvent extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'appointment_id',
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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
