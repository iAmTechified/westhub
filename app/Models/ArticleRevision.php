<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleRevision extends Model
{
    use HasFactory;
    use UsesContentConnection;

    protected $fillable = [
        'article_id',
        'saved_by',
        'version',
        'payload_json',
        'saved_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'saved_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
