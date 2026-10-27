<?php

namespace App\Http\Middleware;

use App\Services\Billing\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    public function __construct(private FeatureService $features) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! config('billing.enforcement.features', false)) {
            return $next($request);
        }

        if ($this->features->can($feature)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'is_valid' => false,
                'message' => 'Fitur '.$feature.' tidak tersedia pada paket langganan Anda.',
            ], 403);
        }

        return redirect()->route('billing.subscription.index')
            ->with('error', 'Fitur '.$feature.' tidak tersedia pada paket langganan Anda.');
    }
}
