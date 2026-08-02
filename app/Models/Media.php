<?php

namespace App\Models;

use App\Models\Concerns\UsesContentConnection;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use UsesContentConnection;
}
