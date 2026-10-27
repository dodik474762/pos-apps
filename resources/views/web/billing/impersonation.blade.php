@if (!empty($impersonation))
    <div class="alert alert-danger d-flex align-items-center justify-content-between mb-3" role="alert">
        <div>
            <i class="ri-user-shared-line me-1"></i>
            Mode impersonate: <strong>{{ $impersonation['company_name'] }}</strong>.
            Sesi berakhir otomatis dalam {{ ceil($impersonation['expires_in'] / 60) }} menit.
        </div>
        <form action="{{ route('admin.billing.impersonate.stop') }}" method="POST" class="m-0">
            @csrf
            <button class="btn btn-sm btn-light" type="submit">Stop impersonate</button>
        </form>
    </div>
@endif
