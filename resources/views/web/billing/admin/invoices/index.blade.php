@php
    $badge = ['PENDING' => 'warning', 'PAID' => 'success', 'EXPIRED' => 'secondary', 'FAILED' => 'danger', 'VOID' => 'dark'];
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
                <input type="text" name="q" value="{{ $keyword }}" class="form-control" placeholder="Cari nomor / referensi...">
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
                <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-soft-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th>Nomor</th>
                        <th>Company</th>
                        <th>Periode</th>
                        <th>Jatuh Tempo</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $companyNames[$invoice->company_id] ?? '#'.$invoice->company_id }}</td>
                            <td>{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</td>
                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            <td>Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $badge[$invoice->status] ?? 'secondary' }}">{{ $invoice->status }}</span></td>
                            <td>
                                @if ($invoice->status === 'PENDING')
                                    <form method="POST" action="{{ route('admin.billing.invoices.mark-paid', $invoice->id) }}" onsubmit="return confirm('Tandai tagihan ini lunas?');">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit">Tandai Lunas</button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $rows->links() }}</div>
    </div>
</div>
