@php
    $limits = $plan->limits ?? [];
    $value = fn ($key, $default = '') => old($key, $limits[$key] ?? $default);
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.billing.plans.index') }}">Paket Langganan</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </div>
    </div>
</div>

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.billing.plans.submit') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $plan->id }}">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Paket</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $plan->code) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Paket</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga Bulanan</label>
                    <input type="number" name="price_monthly" min="0" step="0.01" class="form-control" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga Tahunan</label>
                    <input type="number" name="price_yearly" min="0" step="0.01" class="form-control" value="{{ old('price_yearly', $plan->price_yearly ?? 0) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Batas Users <small class="text-muted">(kosong = tak terbatas)</small></label>
                    <input type="number" name="users" min="0" class="form-control" value="{{ $value('users') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Batas Gudang</label>
                    <input type="number" name="warehouses" min="0" class="form-control" value="{{ $value('warehouses') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Batas Invoice / Bulan</label>
                    <input type="number" name="invoices_per_month" min="0" class="form-control" value="{{ $value('invoices_per_month') }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Fitur <small class="text-muted">(pisahkan dengan koma atau baris baru)</small></label>
                    <textarea name="features" class="form-control" rows="3">{{ old('features', implode(', ', $plan->features ?? [])) }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Paket aktif dan bisa dipilih</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success" type="submit">Simpan</button>
                <a href="{{ route('admin.billing.plans.index') }}" class="btn btn-soft-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
