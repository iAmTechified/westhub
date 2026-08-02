<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_id',
        'actor_id',
        'action',
        'old_value',
        'new_value',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function setting()
    {
        return $this->belongsTo(Setting::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
