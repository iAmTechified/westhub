<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMetric extends Model
{
    use HasFactory;
    use UsesContentConnection;

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
}
