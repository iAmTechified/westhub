<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$media = DB::table('media')->get(['id', 'model_type', 'model_id', 'collection_name', 'name', 'file_name', 'disk', 'size']);
echo json_encode($media, JSON_PRETTY_PRINT);
