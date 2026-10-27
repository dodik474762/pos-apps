<?php

namespace App\Http\Middleware;

use App\Models\Billing\Subscription;
use App\Services\Billing\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function __construct(private FeatureService $features) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('billing.enforcement.enabled', false)) {
            return $next($request);
        }

        if ($this->isExcluded($request) || ! session('user_id') || ! session('id_company')) {
            return $next($request);
        }

        $subscription = $this->features->subscription();

        if (! $subscription) {
            return $next($request);
        }

        $this->shareBanner($subscription);

        $write = ! $request->isMethodSafe();

        if ($write && $subscription->isReadOnly()) {
            $message = 'Langganan '.$subscription->statusLabel().'. Data masih dapat dibaca, tetapi perubahan data dinonaktifkan. Silakan selesaikan pembayaran.';

            if (config('billing.enforcement.log_only', true)) {
                Log::warning('billing.enforcement_log_only_write_blocked', [
                    'company_id' => $subscription->company_id,
                    'status' => $subscription->status,
                    'path' => $request->path(),
                ]);

                return $next($request);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'is_valid' => false,
                    'message' => $message,
                    'upgrade_url' => route('billing.subscription.index'),
                ], 402);
            }

            return redirect()->route('billing.subscription.index')->with('error', $message);
        }

        return $next($request);
    }

    protected function shareBanner(Subscription $subscription): void
    {
        $banner = null;

        if ($subscription->status === Subscription::STATUS_PAST_DUE) {
            $banner = [
                'type' => 'danger',
                'message' => 'Langganan jatuh tempo. Sisa masa tenggang sampai '
                    .($subscription->grace_ends_at?->format('d/m/Y H:i') ?? '-').'.',
            ];
        } elseif ($subscription->status === Subscription::STATUS_TRIAL) {
            $banner = [
                'type' => 'warning',
                'message' => 'Masa trial berakhir pada '.($subscription->trial_ends_at?->format('d/m/Y') ?? '-').'.',
            ];
        } elseif (in_array($subscription->status, [Subscription::STATUS_SUSPENDED, Subscription::STATUS_CANCELED], true)) {
            $banner = [
                'type' => 'danger',
                'message' => 'Langganan '.$subscription->statusLabel().'. Aplikasi dalam mode baca saja.',
            ];
        }

        view()->share('billingBanner', $banner);
        view()->share('billingSubscription', $subscription);
    }

    protected function isExcluded(Request $request): bool
    {
        $path = trim($request->path(), '/');

        $patterns = [
            '',
            'user/*',
            'landing*',
            'billing',
            'billing/*',
            'admin/billing*',
            'billing/webhook/*',
            '*export*',
            '*download*',
            '*cetak*',
            '*print*',
        ];

        foreach ($patterns as $pattern) {
            if ($request->is($pattern) || $request->is(ltrim($pattern, '/'))) {
                return true;
            }
        }

        return false;
    }
}
