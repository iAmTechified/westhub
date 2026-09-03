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

// Bootstrap Laravel and handle the request...
(require_once $basePath.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
