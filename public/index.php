<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Resolve app root. Default: 1 level up (standard Laravel deploy).
// Override via LARAVEL_APP_ROOT env if public/ is copied ke subfolder lain (mis. cPanel public_html/<sub>/).
$appRoot = getenv('LARAVEL_APP_ROOT') ?: dirname(__DIR__);
if (! is_dir($appRoot . '/vendor')) {
    // cPanel subfolder layout — fallback. Sesuaikan dgn path app kamu atau pakai LARAVEL_APP_ROOT.
    $appRoot = '/home/' . get_current_user() . '/absen-reminder-maganghub';
}

if (file_exists($maintenance = $appRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appRoot . '/vendor/autoload.php';

(require_once $appRoot . '/bootstrap/app.php')
    ->handleRequest(Request::capture());