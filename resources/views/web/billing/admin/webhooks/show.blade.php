@php
    $badge = ['RECEIVED' => 'info', 'PROCESSED' => 'success', 'IGNORED' => 'secondary', 'FAILED' => 'danger'];
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }} #{{ $log->id }}</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.billing.webhooks.index') }}">Webhook Log</a></li>
                <li class="breadcrumb-item active">#{{ $log->id }}</li>
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
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Payload</h5>
                <span class="badge bg-{{ $badge[$log->status] ?? 'secondary' }}">{{ $log->status }}</span>
            </div>
            <div class="card-body">
                <pre class="mb-0" style="max-height: 520px; overflow:auto;">{{ $payload }}</pre>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Informasi</h5></div>
            <div class="card-body">
                <table class="table table-sm mb-3">
                    <tr><th>Gateway</th><td>{{ $log->gateway }}</td></tr>
                    <tr><th>Event ID</th><td>{{ $log->event_id ?: '-' }}</td></tr>
                    <tr><th>Signature valid</th><td>{{ $log->signature_valid ? 'Ya' : 'Tidak' }}</td></tr>
                    <tr><th>Diterima</th><td>{{ $log->received_at->format('d/m/Y H:i:s') }}</td></tr>
                    <tr><th>Diproses</th><td>{{ $log->processed_at ? $log->processed_at->format('d/m/Y H:i:s') : '-' }}</td></tr>
                    <tr><th>Error</th><td class="text-danger">{{ $log->error ?: '-' }}</td></tr>
                </table>

                @if ($log->signature_valid && $log->status !== 'PROCESSED')
                    <form method="POST" action="{{ route('admin.billing.webhooks.replay', $log->id) }}" onsubmit="return confirm('Proses ulang webhook ini?');">
                        @csrf
                        <button class="btn btn-primary w-100" type="submit">Proses Ulang</button>
                    </form>
                @endif

                <a href="{{ route('admin.billing.webhooks.index') }}" class="btn btn-soft-secondary w-100 mt-2">Kembali</a>
            </div>
        </div>
    </div>
</div>
