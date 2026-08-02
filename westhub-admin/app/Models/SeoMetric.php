<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'metric_date',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'page_url',
        'top_queries',
        'source',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'top_queries' => 'array',
    ];

    public function entity()
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }
}
