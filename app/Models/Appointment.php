<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    use UsesContentConnection;

    public const STATUS_NEW = 'new';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RESCHEDULED = 'rescheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const PENDING_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONFIRMED,
        self::STATUS_RESCHEDULED,
    ];

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'county_id',
        'township_id',
        'service_id',
        'event_type_name',
        'preferred_date',
        'preferred_time',
        'message',
        'source',
        'status',
        'scheduled_at',
        'end_time',
        'assigned_to',
        'resolved_at',
        'meta',
        'calendly_event_id',
        'calendly_invitee_id',
        'cancel_url',
        'reschedule_url',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'scheduled_at' => 'datetime',
        'end_time' => 'datetime',
        'resolved_at' => 'datetime',
        'meta' => 'array',
    ];

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function township()
    {
        return $this->belongsTo(Township::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function events()
    {
        return $this->hasMany(AppointmentEvent::class)->latest('event_at');
    }

    public static function isPending(string $status): bool
    {
        return in_array($status, self::PENDING_STATUSES, true);
    }
}
