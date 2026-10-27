<?php

namespace App\Http\Controllers\web\billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Billing\SubscriptionInvoice;
use App\Services\Billing\AuditLogger;
use App\Services\Billing\BillingInvoiceService;
use App\Services\Billing\FeatureService;
use App\Services\Billing\QuotaService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptions,
        private FeatureService $features,
        private QuotaService $quota,
        private BillingInvoiceService $invoices,
        private AuditLogger $audit
    ) {
        date_default_timezone_set('Asia/Jakarta');
    }

    protected function header(): array
    {
        return [
            'js-1' => asset('assets/js/controllers/notification.js'),
        ];
    }

    protected function render(string $view, array $data, string $title, string $parent = 'Langganan')
    {
        $put = [
            'title_content' => $title,
            'title_top' => $title,
            'title_parent' => $parent,
            'view_file' => view($view, $data),
            'header_data' => $this->header(),
        ];

        return view('web.template.main', $put);
    }

    public function index()
    {
        $companyId = (int) session('id_company');
        $subscription = $this->subscriptions->forCompany($companyId);
        $subscription->load(['plan', 'pendingPlan']);

        $data = [
            'title' => 'Langganan Saya',
            'title_parent' => 'Langganan',
            'subscription' => $subscription,
            'quota' => $this->quota->all($companyId),
            'invoices' => SubscriptionInvoice::withoutCompanyScope()
                ->where('company_id', $companyId)
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
            'events' => $subscription->events()->orderByDesc('id')->limit(15)->get(),
        ];

        return $this->render('web.billing.subscription.index', $data, 'Langganan Saya');
    }

    public function plans()
    {
        $companyId = (int) session('id_company');
        $subscription = $this->subscriptions->forCompany($companyId);
        $subscription->load(['plan', 'pendingPlan']);

        $data = [
            'title' => 'Pilih Paket',
            'title_parent' => 'Langganan',
            'subscription' => $subscription,
            'plans' => Plan::where('is_active', true)->orderBy('price_monthly')->get(),
        ];

        return $this->render('web.billing.subscription.plans', $data, 'Pilih Paket');
    }

    public function choosePlan(Request $request)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:MONTHLY,YEARLY'],
        ]);

        $companyId = (int) session('id_company');
        $subscription = $this->subscriptions->forCompany($companyId);
        $plan = Plan::findOrFail($data['plan_id']);

        try {
            $result = $this->subscriptions->choosePlan(
                $subscription,
                $plan,
                $data['billing_cycle'],
                (int) session('user_id')
            );
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['is_valid' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($result['type'] === 'blocked') {
            $message = 'Tidak dapat downgrade. Pemakaian saat ini melebihi batas paket tujuan: '
                .collect($result['conflicts'])->map(fn ($row) => $row['label'].' '.$row['used'].'/'.$row['limit'])->implode(', ');

            return $request->expectsJson()
                ? response()->json(['is_valid' => false, 'message' => $message, 'conflicts' => $result['conflicts']], 422)
                : back()->with('error', $message);
        }

        if ($result['type'] === 'noop') {
            return $request->expectsJson()
                ? response()->json(['is_valid' => true, 'message' => 'Paket tidak berubah.'])
                : back()->with('success', 'Paket tidak berubah.');
        }

        if ($result['type'] === 'downgrade') {
            $message = 'Downgrade dijadwalkan berlaku pada '.$result['effective_at']->format('d/m/Y H:i').'.';

            return $request->expectsJson()
                ? response()->json(['is_valid' => true, 'message' => $message])
                : redirect()->route('billing.subscription.index')->with('success', $message);
        }

        $message = 'Tagihan dibuat. Silakan lakukan pembayaran untuk mengaktifkan paket.';

        return $request->expectsJson()
            ? response()->json([
                'is_valid' => true,
                'message' => $message,
                'invoice_id' => $result['invoice']->id,
                'payment_url' => $result['invoice']->payment_url,
            ])
            : redirect()->route('billing.invoices.show', $result['invoice']->id)->with('success', $message);
    }

    public function cancel(Request $request)
    {
        $companyId = (int) session('id_company');
        $subscription = $this->subscriptions->forCompany($companyId);

        try {
            $this->subscriptions->cancel($subscription, (int) session('user_id'));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('billing.subscription.index')
            ->with('success', 'Langganan dibatalkan. Data tetap dapat dibaca dan di-export.');
    }

    public function invoices()
    {
        $companyId = (int) session('id_company');
        $data = [
            'title' => 'Tagihan Saya',
            'title_parent' => 'Langganan',
            'invoices' => SubscriptionInvoice::withoutCompanyScope()
                ->where('company_id', $companyId)
                ->orderByDesc('id')
                ->paginate(20),
        ];

        return $this->render('web.billing.invoices.index', $data, 'Tagihan Saya');
    }

    public function invoiceShow(int $id)
    {
        $companyId = (int) session('id_company');
        $invoice = SubscriptionInvoice::withoutCompanyScope()
            ->with('subscription.plan')
            ->where('company_id', $companyId)
            ->findOrFail($id);

        $data = [
            'title' => 'Detail Tagihan',
            'title_parent' => 'Langganan',
            'invoice' => $invoice,
        ];

        return $this->render('web.billing.invoices.show', $data, 'Detail Tagihan');
    }

    public function invoicePay(int $id)
    {
        $companyId = (int) session('id_company');
        $invoice = SubscriptionInvoice::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->findOrFail($id);

        if (! config('billing.gateway.allow_manual_confirm', true) || $invoice->gateway !== 'manual') {
            return back()->with('error', 'Konfirmasi pembayaran manual tidak tersedia untuk gateway ini.');
        }

        if ($invoice->status === SubscriptionInvoice::STATUS_PAID) {
            return back()->with('success', 'Tagihan sudah lunas.');
        }

        $this->invoices->markPaid($invoice, null, ['source' => 'manual_confirm']);

        $this->audit->log(
            'subscription_invoice.manual_payment',
            $companyId,
            SubscriptionInvoice::class,
            $invoice->id,
            'Konfirmasi pembayaran manual'
        );

        return redirect()->route('billing.subscription.index')->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }
}
