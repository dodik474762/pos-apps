@php
    $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('billing.subscription.index') }}">Langganan</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </div>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($subscription->pendingPlan)
    <div class="alert alert-info">
        Perubahan ke paket <strong>{{ $subscription->pendingPlan->name }}</strong> sedang menunggu
        {{ $subscription->pending_effective_at ? 'berlaku '.$subscription->pending_effective_at->format('d/m/Y H:i') : 'pembayaran' }}.
    </div>
@endif

<div class="row">
    @foreach ($plans as $plan)
        @php
            $isCurrent = (int) $subscription->plan_id === (int) $plan->id;
            $limits = $plan->limits ?? [];
        @endphp
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 {{ $isCurrent ? 'border-primary' : '' }}">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">{{ $plan->name }}</h5>
                            <span class="text-muted">{{ $plan->code }}</span>
                        </div>
                        @if ($isCurrent)
                            <span class="badge bg-primary">Paket saat ini</span>
                        @endif
                    </div>

                    <div class="my-4">
                        <div class="fs-24 fw-semibold">{{ $rp($plan->price_monthly) }}<small class="fs-12 text-muted"> / bulan</small></div>
                        <div class="text-muted">{{ $rp($plan->price_yearly) }} / tahun</div>
                    </div>

                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="ri-check-line text-success me-1"></i>
                            Users: {{ array_key_exists('users', $limits) && $limits['users'] !== null ? $limits['users'] : 'Tak terbatas' }}
                        </li>
                        <li class="mb-2"><i class="ri-check-line text-success me-1"></i>
                            Gudang: {{ array_key_exists('warehouses', $limits) && $limits['warehouses'] !== null ? $limits['warehouses'] : 'Tak terbatas' }}
                        </li>
                        <li class="mb-2"><i class="ri-check-line text-success me-1"></i>
                            Invoice/bulan: {{ array_key_exists('invoices_per_month', $limits) && $limits['invoices_per_month'] !== null ? $limits['invoices_per_month'] : 'Tak terbatas' }}
                        </li>
                        @foreach (($plan->features ?? []) as $feature)
                            <li class="mb-2"><i class="ri-check-line text-success me-1"></i> {{ $feature }}</li>
                        @endforeach
                    </ul>

                    <form action="{{ route('billing.subscription.choose') }}" method="POST" class="mt-auto">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <div class="mb-3">
                            <label class="form-label">Siklus tagihan</label>
                            <select name="billing_cycle" class="form-select">
                                <option value="MONTHLY" {{ $subscription->billing_cycle === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                                <option value="YEARLY" {{ $subscription->billing_cycle === 'YEARLY' ? 'selected' : '' }}>Tahunan</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" {{ $isCurrent && $subscription->status !== 'TRIAL' ? 'disabled' : '' }}>
                            {{ $isCurrent ? 'Paket saat ini' : 'Pilih Paket' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
