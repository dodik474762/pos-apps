<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = session('impersonate_company_id');

        if ($companyId) {
            $startedAt = (int) session('impersonate_started_at', 0);
            $limit = (int) config('billing.impersonate.session_minutes', 30) * 60;

            if ($startedAt > 0 && (time() - $startedAt) > $limit) {
                Session::forget(['impersonate_company_id', 'impersonate_company_name', 'impersonate_started_at', 'impersonated_by']);

                if (session('impersonate_original_company')) {
                    Session::put('id_company', session('impersonate_original_company'));
                }

                Session::forget('impersonate_original_company');

                return redirect('/dashboard')->with('error', 'Sesi impersonate telah berakhir.');
            }

            view()->share('impersonation', [
                'company_id' => $companyId,
                'company_name' => session('impersonate_company_name'),
                'started_at' => $startedAt,
                'expires_in' => max(0, $limit - (time() - $startedAt)),
            ]);
        }

        return $next($request);
    }
}
