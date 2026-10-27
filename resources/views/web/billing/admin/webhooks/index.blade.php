@php
    $badge = ['RECEIVED' => 'info', 'PROCESSED' => 'success', 'IGNORED' => 'secondary', 'FAILED' => 'danger'];
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
            <div class="col-md-3">
                <input type="text" name="gateway" value="{{ $gateway }}" class="form-control" placeholder="Gateway...">
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
                <a href="{{ route('admin.billing.webhooks.index') }}" class="btn btn-soft-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th>Waktu</th>
                        <th>Gateway</th>
                        <th>Event ID</th>
                        <th>Signature</th>
                        <th>Status</th>
                        <th>Error</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $log)
                        <tr>
                            <td>{{ $log->received_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->gateway }}</td>
                            <td>{{ $log->event_id ?: '-' }}</td>
                            <td>
                                @if ($log->signature_valid)
                                    <span class="badge bg-success">VALID</span>
                                @else
                                    <span class="badge bg-danger">INVALID</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $badge[$log->status] ?? 'secondary' }}">{{ $log->status }}</span></td>
                            <td class="fs-12 text-danger">{{ \Illuminate\Support\Str::limit($log->error, 60) }}</td>
                            <td>
                                <a href="{{ route('admin.billing.webhooks.show', $log->id) }}" class="btn btn-sm btn-soft-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada webhook log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $rows->links() }}</div>
    </div>
</div>
