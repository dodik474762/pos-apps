<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $akses = strtolower((string) session('akses'));
        $username = strtolower((string) session('username'));

        if ($akses === 'superadmin' || $username === 'administrator') {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['is_valid' => false, 'message' => 'Tidak punya akses super admin.'], 403);
        }

        abort(403, 'Tidak punya akses super admin.');
    }
}
