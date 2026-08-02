<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentEvent extends Model
{
    use HasFactory;

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

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

