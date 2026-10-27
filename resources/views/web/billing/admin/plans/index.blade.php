@php
    $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Paket</h5>
        <a href="{{ route('admin.billing.plans.add') }}" class="btn btn-success btn-sm"><i class="ri-add-line me-1"></i> Tambah Paket</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Harga Bulanan</th>
                        <th>Harga Tahunan</th>
                        <th>Batas</th>
                        <th>Fitur</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td>{{ $plan->code }}</td>
                            <td class="fw-semibold">{{ $plan->name }}</td>
                            <td>{{ $rp($plan->price_monthly) }}</td>
                            <td>{{ $rp($plan->price_yearly) }}</td>
                            <td class="fs-12">
                                @foreach (($plan->limits ?? []) as $key => $limit)
                                    <div>{{ $key }}: {{ $limit === null ? 'Tak terbatas' : $limit }}</div>
                                @endforeach
                            </td>
                            <td class="fs-12">
                                @foreach (($plan->features ?? []) as $feature)
                                    <span class="badge bg-light text-dark">{{ $feature }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if ($plan->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.billing.plans.edit', $plan->id) }}" class="btn btn-sm btn-soft-primary">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada paket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
