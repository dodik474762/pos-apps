@php
    $statusColors = [
        'TRIAL' => 'warning',
        'ACTIVE' => 'success',
        'PAST_DUE' => 'danger',
        'SUSPENDED' => 'dark',
        'CANCELED' => 'secondary',
    ];
    $statusColor = $statusColors[$subscription->status] ?? 'secondary';
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">{{ $title_parent }}</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
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
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ $subscription->plan->name ?? '-' }}</h5>
                        <p class="text-muted mb-0">Siklus {{ $subscription->billing_cycle === 'YEARLY' ? 'Tahunan' : 'Bulanan' }}</p>
                    </div>
                    <span class="badge bg-{{ $statusColor }} fs-12">{{ $subscription->statusLabel() }}</span>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Periode berjalan</small>
                        <strong>{{ optional($subscription->current_period_start)->format('d/m/Y') }} - {{ optional($subscription->current_period_end)->format('d/m/Y') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Jatuh tempo</small>
                        <strong>{{ optional($subscription->current_period_end)->format('d/m/Y') }}</strong>
                    </div>
                    @if ($subscription->status === 'TRIAL')
                        <div class="col-md-6">
                            <small class="text-muted d-block">Trial berakhir</small>
                            <strong>{{ optional($subscription->trial_ends_at)->format('d/m/Y H:i') }}</strong>
                        </div>
                    @endif
                    @if ($subscription->grace_ends_at)
                        <div class="col-md-6">
                            <small class="text-muted d-block">Masa tenggang berakhir</small>
                            <strong>{{ $subscription->grace_ends_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    @endif
                    @if ($subscription->pendingPlan)
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0">
                                Menunggu perubahan ke paket <strong>{{ $subscription->pendingPlan->name }}</strong>
                                @if ($subscription->pending_effective_at)
                                    berlaku {{ $subscription->pending_effective_at->format('d/m/Y H:i') }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <a href="{{ route('billing.subscription.plans') }}" class="btn btn-primary">
                        <i class="ri-arrow-up-down-line me-1"></i> Pilih / Ganti Paket
                    </a>
                    <a href="{{ route('billing.invoices.index') }}" class="btn btn-soft-secondary">
                        <i class="ri-file-list-3-line me-1"></i> Daftar Tagihan
                    </a>
                    @if (!in_array($subscription->status, ['CANCELED']))
                        <form action="{{ route('billing.subscription.cancel') }}" method="POST" onsubmit="return confirm('Batalkan langganan? Data tetap dapat dibaca dan di-export.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Batalkan Langganan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Pemakaian vs Batas Paket</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Komponen</th><th>Terpakai</th><th>Batas</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($quota as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['used'] }}</td>
                                    <td>{{ $row['unlimited'] ? 'Tak terbatas' : $row['limit'] }}</td>
                                    <td>
                                        @if ($row['allowed'])
                                            <span class="badge bg-success-subtle text-success">Aman</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Penuh</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Riwayat Status</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Waktu</th><th>Dari</th><th>Ke</th><th>Alasan</th><th>Aktor</th></tr></thead>
                        <tbody>
                            @forelse ($events as $event)
                                <tr>
                                    <td>{{ $event->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $event->from_status ?: '-' }}</td>
                                    <td>{{ $event->to_status }}</td>
                                    <td>{{ $event->reason ?: '-' }}</td>
                                    <td>{{ $event->actor ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tagihan Terbaru</h5>
                <a href="{{ route('billing.invoices.index') }}" class="btn btn-sm btn-link">Semua</a>
            </div>
            <div class="card-body">
                @forelse ($invoices as $invoice)
                    <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                        <div>
                            <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="fw-semibold">{{ $invoice->number }}</a>
                            <div class="text-muted fs-12">{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</div>
                        </div>
                        <div class="text-end">
                            <div>Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</div>
                            <span class="badge bg-{{ $invoice->status === 'PAID' ? 'success' : ($invoice->status === 'PENDING' ? 'warning' : 'secondary') }}">{{ $invoice->status }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada tagihan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
