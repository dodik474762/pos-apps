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

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $keyword }}" class="form-control" placeholder="Cari nama company...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $item)
                        <option value="{{ $item }}" {{ $status === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a href="{{ route('admin.billing.subscriptions.index') }}" class="btn btn-soft-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th>Company</th>
                        <th>Paket</th>
                        <th>Status</th>
                        <th>Periode Berakhir</th>
                        <th>Pemakaian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $row->nama_company }}</div>
                                <div class="text-muted fs-12">{{ $row->email }}</div>
                            </td>
                            <td>
                                @if ($row->plan_name)
                                    {{ $row->plan_name }}
                                    <div class="text-muted fs-12">{{ $row->billing_cycle === 'YEARLY' ? 'Tahunan' : 'Bulanan' }}</div>
                                @else
                                    <span class="text-muted">Belum ada</span>
                                @endif
                            </td>
                            <td>
                                @if ($row->status)
                                    <span class="badge bg-{{ $badge[$row->status] ?? 'secondary' }}">{{ $row->status }}</span>
                                @else
                                    <span class="badge bg-light text-dark">NO SUBSCRIPTION</span>
                                @endif
                            </td>
                            <td>
                                {{ $row->current_period_end ? \Illuminate\Support\Carbon::parse($row->current_period_end)->format('d/m/Y') : '-' }}
                                @if ($row->grace_ends_at)
                                    <div class="text-danger fs-12">Grace: {{ \Illuminate\Support\Carbon::parse($row->grace_ends_at)->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="fs-12">
                                Users {{ $row->users_used }}<br>
                                Gudang {{ $row->warehouses_used }}<br>
                                Invoice {{ $row->invoices_used }}
                            </td>
                            <td>
                                <a href="{{ route('admin.billing.subscriptions.show', $row->company_id) }}" class="btn btn-sm btn-soft-primary">Kelola</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $rows->links() }}</div>
    </div>
</div>
