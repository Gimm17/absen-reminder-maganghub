<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Resolve app root.
// Default: 1 level up (standard Laravel deploy).
// Override via LARAVEL_APP_ROOT env jika public/ di-copy ke folder lain.
$appRoot = getenv('LARAVEL_APP_ROOT') ?: dirname(__DIR__);
if (! is_dir($appRoot . '/vendor')) {
    // cPanel subdomain layout: subdomain root berisi copy dari public/, app source di subfolder 'absen-reminder'.
    // dirname(__DIR__) dari /home/<user>/<subdomain>/index.php = /home/<user> (atau /home/<user>/<subdomain> kalau di subfolder).
    // Kita hardcode ke path eksplisit berdasarkan user login.
    $user = get_current_user();
    $candidates = [
        __DIR__ . '/absen-reminder',                              // subdomain root + /absen-reminder
        dirname(__DIR__, 2) . '/absendong.pinnhost.my.id/absen-reminder',  // parent.parent pattern
        '/home/' . $user . '/absendong.pinnhost.my.id/absen-reminder',
        '/home/' . $user . '/absen-reminder',
    ];
    foreach ($candidates as $cand) {
        if (is_dir($cand . '/vendor')) {
            $appRoot = $cand;
            break;
        }
    }
}

if (file_exists($maintenance = $appRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appRoot . '/vendor/autoload.php';

(require_once $appRoot . '/bootstrap/app.php')
    ->handleRequest(Request::capture());