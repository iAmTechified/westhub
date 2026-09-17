<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoClaim extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_REDEEMED = 'redeemed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_REDEEMED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'campaign',
        'full_name',
        'email',
        'phone',
        'service_id',
        'voucher_code',
        'status',
        'consent_at',
        'source_page',
        'expires_at',
        'emailed_at',
        'contacted_at',
        'redeemed_at',
        'synced_at',
        'appointment_id',
        'assigned_to',
        'meta',
    ];

    protected $casts = [
        'consent_at' => 'datetime',
        'expires_at' => 'datetime',
        'emailed_at' => 'datetime',
        'contacted_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'synced_at' => 'datetime',
        'meta' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isRedeemable(): bool
    {
        if (in_array($this->status, [self::STATUS_REDEEMED, self::STATUS_EXPIRED, self::STATUS_CANCELLED], true)) {
            return false;
        }

        return ! $this->expires_at || $this->expires_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_CONTACTED]);
    }
}
