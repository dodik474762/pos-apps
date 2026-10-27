@php
    $badge = [
        'TRIAL' => 'warning',
        'ACTIVE' => 'success',
        'PAST_DUE' => 'danger',
        'SUSPENDED' => 'dark',
        'CANCELED' => 'secondary',
    ];
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }} - {{ $company->nama_company }}</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.billing.subscriptions.index') }}">Langganan Company</a></li>
                <li class="breadcrumb-item active">{{ $company->nama_company }}</li>
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
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                @if ($subscription)
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">{{ $subscription->plan->name ?? '-' }}</h5>
                            <p class="text-muted mb-0">{{ $subscription->billing_cycle === 'YEARLY' ? 'Tahunan' : 'Bulanan' }}</p>
                        </div>
                        <span class="badge bg-{{ $badge[$subscription->status] ?? 'secondary' }} fs-12">{{ $subscription->statusLabel() }}</span>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Periode</small>
                            <strong>{{ $subscription->current_period_start->format('d/m/Y') }} - {{ $subscription->current_period_end->format('d/m/Y') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Trial berakhir</small>
                            <strong>{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('d/m/Y H:i') : '-' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Grace berakhir</small>
                            <strong>{{ $subscription->grace_ends_at ? $subscription->grace_ends_at->format('d/m/Y H:i') : '-' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Pending plan</small>
                            <strong>{{ $subscription->pendingPlan->name ?? '-' }}</strong>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">Company ini belum memiliki langganan. Aksi di bawah akan membuat langganan trial otomatis.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Pemakaian vs Batas</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Komponen</th><th>Terpakai</th><th>Batas</th></tr></thead>
                        <tbody>
                            @foreach ($quota as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['used'] }}</td>
                                    <td>{{ $row['unlimited'] ? 'Tak terbatas' : $row['limit'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Riwayat Status</h5></div>
            <div class="card-body table-responsive">
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

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Tagihan</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Nomor</th><th>Periode</th><th>Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->number }}</td>
                                <td>{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</td>
                                <td>Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</td>
                                <td>{{ $invoice->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada tagihan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Audit Log</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Waktu</th><th>Aksi</th><th>Alasan</th><th>Aktor</th></tr></thead>
                    <tbody>
                        @forelse ($auditLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->reason ?: '-' }}</td>
                                <td>{{ $log->actor_name ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada audit log.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Perpanjang Manual</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.billing.subscriptions.extend', $company->id) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Tambah hari</label>
                        <input type="number" name="days" min="1" max="3650" class="form-control" value="30" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alasan (wajib)</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                    <button class="btn btn-success w-100" type="submit">Perpanjang</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Suspend / Aktifkan</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.billing.subscriptions.suspend', $company->id) }}" class="mb-3">
                    @csrf
                    <label class="form-label">Alasan suspend (wajib)</label>
                    <textarea name="reason" class="form-control mb-2" rows="2" required></textarea>
                    <button class="btn btn-danger w-100" type="submit">Suspend</button>
                </form>
                <form method="POST" action="{{ route('admin.billing.subscriptions.activate', $company->id) }}">
                    @csrf
                    <label class="form-label">Alasan aktifkan (wajib)</label>
                    <textarea name="reason" class="form-control mb-2" rows="2" required></textarea>
                    <button class="btn btn-success w-100" type="submit">Aktifkan</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Ganti Paket</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.billing.subscriptions.change-plan', $company->id) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Paket</label>
                        <select name="plan_id" class="form-select" required>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} (Rp {{ number_format((float) $plan->price_monthly, 0, ',', '.') }}/bln)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Siklus</label>
                        <select name="billing_cycle" class="form-select" required>
                            <option value="MONTHLY">Bulanan</option>
                            <option value="YEARLY">Tahunan</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alasan (wajib)</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Ganti Paket</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Impersonate Company</h5></div>
            <div class="card-body">
                <p class="text-muted fs-12">Semua aksi selama impersonate ditandai di audit log. Sesi dibatasi waktu.</p>
                <form method="POST" action="{{ route('admin.billing.subscriptions.impersonate', $company->id) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Alasan (wajib)</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                    <button class="btn btn-warning w-100" type="submit">Mulai Impersonate</button>
                </form>
            </div>
        </div>
    </div>
</div>
