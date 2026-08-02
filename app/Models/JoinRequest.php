<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    use HasFactory;
    use UsesContentConnection;

    public const STATUS_NEW = 'new';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const ALL_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_ACCEPTED,
        self::STATUS_DECLINED,
    ];
    public const PENDING_STATUSES = [
        self::STATUS_NEW,
    ];
    public const DECISION_STATUSES = [
        self::STATUS_ACCEPTED,
        self::STATUS_DECLINED,
    ];
    public const ROLE_SKILLED = 'skilled';
    public const ROLE_NON_SKILLED = 'non-skilled';
    public const ROLE_TYPES = [
        self::ROLE_SKILLED,
        self::ROLE_NON_SKILLED,
    ];

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'middle_initial',
        'full_name',
        'email',
        'phone',
        'professional_type',
        'home_address',
        'position_applied_for',
        'date_available',
        'desired_salary',
        'is_citizen',
        'has_felony',
        'felony_explanation',
        'is_authorized',
        'worked_here_before',
        'worked_here_before_when',
        'ssn_tin',
        'dob',
        'education',
        'military_service',
        'work_experience',
        'references',
        'profession',
        'about',
        'qualifications',
        'resume_path',
        'document_paths',
        'signature_path',
        'disclaimer_accepted',
        'internal_notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'decided_at',
    ];

    protected $casts = [
        'qualifications' => 'array',
        'education' => 'array',
        'military_service' => 'array',
        'work_experience' => 'array',
        'references' => 'array',
        'document_paths' => 'array',
        'is_citizen' => 'boolean',
        'has_felony' => 'boolean',
        'is_authorized' => 'boolean',
        'worked_here_before' => 'boolean',
        'disclaimer_accepted' => 'boolean',
        'date_available' => 'date',
        'dob' => 'date',
        'reviewed_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function getFullNameAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->first_name || $this->last_name) {
            return trim(($this->title ? $this->title . ' ' : '') . $this->first_name . ' ' . $this->last_name);
        }

        return 'N/A';
    }

    public function events()
    {
        return $this->hasMany(JoinRequestEvent::class)->latest('event_at');
    }

    public function outboundMessages()
    {
        return $this->hasMany(OutboundMessage::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function isPending(string $status): bool
    {
        return in_array(self::normalizeStatus($status), self::PENDING_STATUSES, true);
    }

    public static function canAccept(string $status): bool
    {
        return self::isPending($status);
    }

    public static function canDecline(string $status): bool
    {
        return self::isPending($status);
    }

    public static function normalizeStatus(?string $status): string
    {
        if (! is_string($status) || trim($status) === '') {
            return self::STATUS_NEW;
        }

        $normalized = str($status)
            ->lower()
            ->replace(['-', ' '], '_')
            ->squish()
            ->replace(' ', '_')
            ->value();

        return in_array($normalized, self::ALL_STATUSES, true) ? $normalized : self::STATUS_NEW;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus((string) $value);
    }

    public function getStatusAttribute($value): string
    {
        return self::normalizeStatus((string) $value);
    }

    public static function normalizeProfessionalType(?string $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return self::ROLE_NON_SKILLED;
        }

        $normalized = str($value)
            ->lower()
            ->replace(['_', ' '], '-')
            ->squish()
            ->replace(' ', '-')
            ->value();

        if ($normalized === 'nonskilled') {
            $normalized = self::ROLE_NON_SKILLED;
        }

        return in_array($normalized, self::ROLE_TYPES, true) ? $normalized : self::ROLE_NON_SKILLED;
    }

    public function setProfessionalTypeAttribute($value): void
    {
        $this->attributes['professional_type'] = self::normalizeProfessionalType(is_scalar($value) ? (string) $value : null);
    }

    public function getProfessionalTypeAttribute($value): string
    {
        return self::normalizeProfessionalType((string) $value);
    }
}
