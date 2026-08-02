<?php

namespace App\Models\Concerns;

trait UsesContentConnection
{
    public function getConnectionName()
    {
        return config('database.content_connection', 'content');
    }
}
