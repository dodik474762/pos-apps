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
                <li class="breadcrumb-item"><a href="{{ route('billing.subscription.index') }}">Langganan</a></li>
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
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th>Nomor</th>
                        <th>Periode</th>
                        <th>Jatuh Tempo</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</td>
                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            <td>Rp {{ number_format((float) $invoice->amount, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $badge[$invoice->status] ?? 'secondary' }}">{{ $invoice->status }}</span></td>
                            <td>
                                <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="btn btn-sm btn-soft-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada tagihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
