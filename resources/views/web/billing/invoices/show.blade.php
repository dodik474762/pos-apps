@php
    $badge = [
        'PENDING' => 'warning',
        'PAID' => 'success',
        'EXPIRED' => 'secondary',
        'FAILED' => 'danger',
        'VOID' => 'dark',
    ];
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('billing.invoices.index') }}">Tagihan</a></li>
                <li class="breadcrumb-item active">{{ $invoice->number }}</li>
            </ol>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title mb-1">{{ $invoice->number }}</h5>
                        <p class="text-muted mb-0">Paket {{ $invoice->subscription->plan->name ?? '-' }}</p>
                    </div>
                    <span class="badge bg-{{ $badge[$invoice->status] ?? 'secondary' }} fs-12">{{ $invoice->statusLabel() }}</span>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Periode tagihan</small>
                        <strong>{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Jatuh tempo</small>
                        <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Jumlah</small>
                        <strong class="fs-18">Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Gateway / Referensi</small>
                        <strong>{{ $invoice->gateway ?: '-' }} / {{ $invoice->gateway_reference ?: '-' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Dibayar pada</small>
                        <strong>{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '-' }}</strong>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <a href="{{ route('billing.invoices.index') }}" class="btn btn-soft-secondary">Kembali</a>
                    @if ($invoice->status === 'PENDING')
                        @if ($invoice->payment_url)
                            <a href="{{ $invoice->payment_url }}" class="btn btn-primary" target="_blank">Buka Halaman Pembayaran</a>
                        @endif
                        @if ($invoice->gateway === 'manual' && config('billing.gateway.allow_manual_confirm', true))
                            <form action="{{ route('billing.invoices.pay', $invoice->id) }}" method="POST" onsubmit="return confirm('Konfirmasi pembayaran manual untuk tagihan ini?');">
                                @csrf
                                <button type="submit" class="btn btn-success">Konfirmasi Pembayaran Manual</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Informasi</h5></div>
            <div class="card-body">
                <p class="mb-2">Pembayaran yang berhasil akan otomatis memperpanjang periode langganan.</p>
                <p class="mb-0 text-muted fs-12">Status pembayaran diperbarui melalui webhook gateway secara idempoten.</p>
            </div>
        </div>
    </div>
</div>
