<?php

namespace App\Http\Controllers\web\billing\admin;

use App\Http\Controllers\Controller;
use App\Models\Billing\SubscriptionInvoice;
use App\Services\Billing\BillingInvoiceService;
use App\Services\Billing\AuditLogger;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private BillingInvoiceService $invoices,
        private AuditLogger $audit
    ) {
        date_default_timezone_set('Asia/Jakarta');
    }

    protected function render(string $view, array $data, string $title)
    {
        $put = [
            'title_content' => $title,
            'title_top' => $title,
            'title_parent' => 'Super Admin Billing',
            'view_file' => view($view, $data),
            'header_data' => ['js-1' => asset('assets/js/controllers/notification.js')],
        ];

        return view('web.template.main', $put);
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $keyword = $request->get('q');

        $rows = SubscriptionInvoice::withoutCompanyScope()
            ->with(['subscription.plan'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, fn ($q) => $q->where(function ($sub) use ($keyword) {
                $sub->where('number', 'like', '%'.$keyword.'%')
                    ->orWhere('gateway_reference', 'like', '%'.$keyword.'%');
            }))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $companies = \App\Models\Billing\Subscription::withoutCompanyScope()
            ->pluck('company_id')
            ->all();
        $companyNames = \Illuminate\Support\Facades\DB::table('company')
            ->whereIn('id', $companies)
            ->pluck('nama_company', 'id');

        $data = [
            'title' => 'Tagihan Langganan',
            'title_parent' => 'Super Admin Billing',
            'rows' => $rows,
            'companyNames' => $companyNames,
            'status' => $status,
            'keyword' => $keyword,
            'statuses' => ['PENDING', 'PAID', 'EXPIRED', 'FAILED', 'VOID'],
        ];

        return $this->render('web.billing.admin.invoices.index', $data, 'Tagihan Langganan');
    }

    public function markPaid(int $id)
    {
        $invoice = SubscriptionInvoice::withoutCompanyScope()->findOrFail($id);

        if ($invoice->status !== SubscriptionInvoice::STATUS_PAID) {
            $this->invoices->markPaid($invoice, null, ['source' => 'admin']);
            $this->audit->log(
                'subscription_invoice.marked_paid',
                $invoice->company_id,
                SubscriptionInvoice::class,
                $invoice->id,
                'Tandai lunas oleh super admin'
            );
        }

        return back()->with('success', 'Tagihan ditandai lunas.');
    }
}
