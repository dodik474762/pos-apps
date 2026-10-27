<?php

namespace App\Http\Controllers\web\billing\admin;

use App\Http\Controllers\Controller;
use App\Models\Billing\AuditLog;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\Billing\SubscriptionInvoice;
use App\Services\Billing\AuditLogger;
use App\Services\Billing\QuotaService;
use App\Services\Billing\SubscriptionService;
use App\Services\Billing\SubscriptionStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptions,
        private SubscriptionStateService $state,
        private QuotaService $quota,
        private AuditLogger $audit
    ) {
        date_default_timezone_set('Asia/Jakarta');
    }

    protected function header(): array
    {
        return ['js-1' => asset('assets/js/controllers/notification.js')];
    }

    protected function render(string $view, array $data, string $title, string $parent = 'Super Admin Billing')
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

    public function index(Request $request)
    {
        $status = $request->get('status');
        $keyword = $request->get('q');

        $query = DB::table('company as c')
            ->leftJoin('subscriptions as s', 's.company_id', '=', 'c.id')
            ->leftJoin('plans as p', 'p.id', '=', 's.plan_id')
            ->whereNull('c.deleted')
            ->select([
                'c.id as company_id',
                'c.nama_company',
                'c.email',
                's.id as subscription_id',
                's.status',
                's.billing_cycle',
                's.current_period_start',
                's.current_period_end',
                's.trial_ends_at',
                's.grace_ends_at',
                'p.name as plan_name',
                'p.code as plan_code',
                DB::raw('(select count(*) from users u where u.company_id = c.id and u.deleted is null) as users_used'),
                DB::raw('(select count(*) from warehouse w where w.company_id = c.id and w.deleted is null) as warehouses_used'),
                DB::raw('(select count(*) from sales_invoice_header si where si.company_id = c.id and si.deleted is null and si.invoice_date between date(s.current_period_start) and date(s.current_period_end)) as invoices_used'),
            ])
            ->when($status, fn ($q) => $q->where('s.status', $status))
            ->when($keyword, fn ($q) => $q->where('c.nama_company', 'like', '%'.$keyword.'%'))
            ->orderBy('c.nama_company');

        $data = [
            'title' => 'Langganan Company',
            'title_parent' => 'Super Admin Billing',
            'rows' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'keyword' => $keyword,
            'statuses' => [
                Subscription::STATUS_TRIAL,
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_PAST_DUE,
                Subscription::STATUS_SUSPENDED,
                Subscription::STATUS_CANCELED,
            ],
        ];

        return $this->render('web.billing.admin.subscriptions.index', $data, 'Langganan Company');
    }

    public function show(int $companyId)
    {
        $company = DB::table('company')->where('id', $companyId)->whereNull('deleted')->firstOrFail();
        $subscription = Subscription::withoutCompanyScope()
            ->with(['plan', 'pendingPlan'])
            ->where('company_id', $companyId)
            ->first();

        $data = [
            'title' => 'Detail Langganan',
            'title_parent' => 'Super Admin Billing',
            'company' => $company,
            'subscription' => $subscription,
            'quota' => $this->quota->all($companyId),
            'plans' => Plan::where('is_active', true)->orderBy('price_monthly')->get(),
            'invoices' => SubscriptionInvoice::withoutCompanyScope()
                ->where('company_id', $companyId)
                ->orderByDesc('id')
                ->limit(20)
                ->get(),
            'events' => $subscription
                ? $subscription->events()->orderByDesc('id')->limit(20)->get()
                : collect(),
            'auditLogs' => AuditLog::where('company_id', $companyId)->orderByDesc('id')->limit(20)->get(),
        ];

        return $this->render('web.billing.admin.subscriptions.show', $data, 'Detail Langganan');
    }

    public function extend(Request $request, int $companyId)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $subscription = $this->subscriptions->forCompany($companyId);
        $this->subscriptions->extend($subscription, (int) $data['days'], (int) session('user_id'), $data['reason']);

        return back()->with('success', 'Langganan diperpanjang '.$data['days'].' hari.');
    }

    public function suspend(Request $request, int $companyId)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $subscription = $this->subscriptions->forCompany($companyId);
        $before = ['status' => $subscription->status];

        $this->state->suspend($subscription, $data['reason'], 'superadmin', (int) session('user_id'));

        $this->audit->log(
            'subscription.suspended',
            $companyId,
            Subscription::class,
            $subscription->id,
            $data['reason'],
            $before,
            ['status' => Subscription::STATUS_SUSPENDED]
        );

        return back()->with('success', 'Langganan disuspend.');
    }

    public function activate(Request $request, int $companyId)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $subscription = $this->subscriptions->forCompany($companyId);
        $before = ['status' => $subscription->status];

        $this->state->activate($subscription, $data['reason'], 'superadmin', (int) session('user_id'));

        $this->audit->log(
            'subscription.activated',
            $companyId,
            Subscription::class,
            $subscription->id,
            $data['reason'],
            $before,
            ['status' => Subscription::STATUS_ACTIVE]
        );

        return back()->with('success', 'Langganan diaktifkan.');
    }

    public function changePlan(Request $request, int $companyId)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:MONTHLY,YEARLY'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $subscription = $this->subscriptions->forCompany($companyId);
        $plan = Plan::findOrFail($data['plan_id']);

        $this->subscriptions->forceChangePlan(
            $subscription,
            $plan,
            $data['billing_cycle'],
            $data['reason'],
            (int) session('user_id')
        );

        return back()->with('success', 'Paket langganan diganti.');
    }

    public function impersonate(Request $request, int $companyId)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $company = DB::table('company')->where('id', $companyId)->whereNull('deleted')->firstOrFail();

        if (! Session::has('impersonate_original_company')) {
            Session::put('impersonate_original_company', session('id_company'));
        }

        Session::put('impersonate_company_id', $companyId);
        Session::put('impersonate_company_name', $company->nama_company);
        Session::put('impersonate_started_at', time());
        Session::put('impersonated_by', session('user_id'));
        Session::put('id_company', $companyId);

        $this->audit->log(
            'company.impersonated',
            $companyId,
            'company',
            $companyId,
            $data['reason'],
            null,
            ['impersonated_by' => session('user_id')]
        );

        return redirect('/dashboard')->with('success', 'Impersonate '.$company->nama_company.' dimulai.');
    }

    public function stopImpersonate()
    {
        $companyId = session('impersonate_company_id');

        Session::forget(['impersonate_company_id', 'impersonate_company_name', 'impersonate_started_at', 'impersonated_by']);

        if (session('impersonate_original_company')) {
            Session::put('id_company', session('impersonate_original_company'));
            Session::forget('impersonate_original_company');
        }

        if ($companyId) {
            $this->audit->log('company.impersonation_stopped', $companyId, 'company', $companyId, 'Stop impersonate');
        }

        return redirect('admin/billing/subscriptions')->with('success', 'Impersonate dihentikan.');
    }
}
