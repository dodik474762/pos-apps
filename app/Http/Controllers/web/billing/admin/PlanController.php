<?php

namespace App\Http\Controllers\web\billing\admin;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;
use App\Services\Billing\AuditLogger;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(private AuditLogger $audit)
    {
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

    public function index()
    {
        $data = [
            'title' => 'Paket Langganan',
            'title_parent' => 'Super Admin Billing',
            'plans' => Plan::orderBy('price_monthly')->get(),
        ];

        return $this->render('web.billing.admin.plans.index', $data, 'Paket Langganan');
    }

    public function add()
    {
        $data = [
            'title' => 'Tambah Paket',
            'title_parent' => 'Super Admin Billing',
            'plan' => new Plan(['is_active' => true]),
        ];

        return $this->render('web.billing.admin.plans.form', $data, 'Tambah Paket');
    }

    public function edit(int $id)
    {
        $data = [
            'title' => 'Ubah Paket',
            'title_parent' => 'Super Admin Billing',
            'plan' => Plan::findOrFail($id),
        ];

        return $this->render('web.billing.admin.plans.form', $data, 'Ubah Paket');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:plans,id'],
            'code' => ['required', 'string', 'max:100', 'unique:plans,code,'.($request->input('id') ?: 'NULL').',id'],
            'name' => ['required', 'string', 'max:255'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'users' => ['nullable', 'integer', 'min:0'],
            'warehouses' => ['nullable', 'integer', 'min:0'],
            'invoices_per_month' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan = $request->filled('id') ? Plan::findOrFail($data['id']) : new Plan();
        $before = $plan->exists ? $plan->only(['code', 'name', 'price_monthly', 'price_yearly', 'limits', 'features', 'is_active']) : null;

        $features = collect(preg_split('/[\r\n,]+/', (string) ($data['features'] ?? '')))
            ->map(fn ($feature) => trim($feature))
            ->filter()
            ->values()
            ->all();

        $limits = [];
        foreach (['users', 'warehouses', 'invoices_per_month'] as $key) {
            $limits[$key] = isset($data[$key]) && $data[$key] !== '' ? (int) $data[$key] : null;
        }

        $plan->code = $data['code'];
        $plan->name = $data['name'];
        $plan->price_monthly = $data['price_monthly'];
        $plan->price_yearly = $data['price_yearly'];
        $plan->limits = $limits;
        $plan->features = $features;
        $plan->is_active = (bool) ($data['is_active'] ?? false);
        $plan->save();

        $this->audit->log(
            $before ? 'plan.updated' : 'plan.created',
            null,
            Plan::class,
            $plan->id,
            'Kelola paket dari panel super admin',
            $before,
            $plan->only(['code', 'name', 'price_monthly', 'price_yearly', 'limits', 'features', 'is_active'])
        );

        return redirect('admin/billing/plans')->with('success', 'Paket berhasil disimpan.');
    }
}
