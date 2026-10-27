<?php

namespace App\Http\Controllers\web\billing\admin;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentWebhookLog;
use App\Services\Billing\AuditLogger;
use App\Services\Billing\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhooks,
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
        $gateway = $request->get('gateway');

        $rows = PaymentWebhookLog::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($gateway, fn ($q) => $q->where('gateway', $gateway))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $data = [
            'title' => 'Webhook Log',
            'title_parent' => 'Super Admin Billing',
            'rows' => $rows,
            'status' => $status,
            'gateway' => $gateway,
            'statuses' => ['RECEIVED', 'PROCESSED', 'IGNORED', 'FAILED'],
        ];

        return $this->render('web.billing.admin.webhooks.index', $data, 'Webhook Log');
    }

    public function show(int $id)
    {
        $log = PaymentWebhookLog::findOrFail($id);

        $data = [
            'title' => 'Detail Webhook',
            'log' => $log,
            'payload' => json_encode(json_decode($log->payload, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];

        return $this->render('web.billing.admin.webhooks.show', $data, 'Detail Webhook');
    }

    public function replay(int $id)
    {
        $log = PaymentWebhookLog::findOrFail($id);

        try {
            $result = $this->webhooks->replay($log);
            $this->audit->log(
                'payment_webhook.replayed',
                null,
                PaymentWebhookLog::class,
                $log->id,
                'Replay webhook oleh super admin'
            );

            return back()->with('success', 'Webhook diproses ulang: '.$result['body']['status']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
