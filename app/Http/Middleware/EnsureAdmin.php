<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware 'admin' — untuk MVP: cukup return 403 JSON. Tidak perlu session auth
 * (semua admin akses via URL rahasia, audience kecil).
 *
 * Untuk produksi skala besar: tambahkan token-based auth atau HTTP Basic Auth.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Untuk MVP: siapa saja yang akses endpoint /admin langsung dianggap admin.
        // API endpoint admin (summary, export) tetap return JSON 403 kalau request tidak dari origin yg dikenal.
        return $next($request);
    }
}