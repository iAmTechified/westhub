<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleRevision extends Model
{
    use HasFactory;

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

    public function author()
    {
        return $this->belongsTo(User::class, 'saved_by');
    }
}
