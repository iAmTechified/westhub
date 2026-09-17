<?php
set_time_limit(60);

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine the base path for Laravel (supports standard structure and cPanel public_html split)
$basePath = file_exists(__DIR__.'/../vendor/autoload.php')
    ? __DIR__.'/..'
    : (file_exists(__DIR__.'/../westhub/vendor/autoload.php')
        ? __DIR__.'/../westhub'
        : __DIR__.'/..');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel...
$app = require_once $basePath.'/bootstrap/app.php';

// On cPanel this file is served from public_html, but Laravel assumes the public
// folder is <basePath>/public, which the deploy never creates. Without this,
// public_path() points at a missing folder, so @vite cannot find
// build/manifest.json and bundled images under public/assets are not found.
// In a standard layout the two paths are identical and nothing changes.
if (realpath($basePath.'/public') !== realpath(__DIR__)) {
    $app->usePublicPath(__DIR__);
}

// Handle the request...
$app->handleRequest(Request::capture());
