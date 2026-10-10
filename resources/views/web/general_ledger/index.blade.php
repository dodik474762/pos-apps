@if (isset($akses->general_ledger))
    @if ($akses->general_ledger->view == 1)
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                            <li class="breadcrumb-item active">{{ $title }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-3">
                        <h5 class="card-title mb-0 fw-semibold">{{ $title }} Filter</h5>
                    </div>
                    <div class="card-body pt-0">
                        <form class="row g-3">
                            <div class="col-xxl-4 col-sm-6">
                                <label class="form-label fw-medium text-muted small">Periode</label>
                                <select id="accounting_period_id" class="form-control select2">
                                    <option value="">Pilih Periode</option>
                                    @foreach ($periods as $p)
                                        <option value="{{ $p->id }}" data-status="{{ $p->status }}">
                                            {{ $p->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xxl-6 col-sm-6">
                                <label class="form-label fw-medium text-muted small">Akun</label>
                                <select id="account_id" class="form-control select2">
                                    <option value="">Pilih Akun</option>
                                    @foreach ($accounts as $a)
                                        <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xxl-2 col-sm-6 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" id="search-btn">
                                    <i class="ri-search-line align-bottom me-1"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div
                        class="card-header bg-transparent border-0 pb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title mb-0 fw-semibold">Mutasi Akun</h5>
                        <span id="gl-source-tag" class="badge rounded-pill d-none">
                            <i class="ri-circle-fill align-bottom me-1" style="font-size:8px;"></i><span
                                id="gl-source-text"></span>
                        </span>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <div class="text-muted small">Akun</div>
                                    <div class="fw-semibold" id="gl-account">-</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <div class="text-muted small d-flex align-items-center justify-content-between">
                                        Periode
                                        <span id="gl-period-badge" class="badge rounded-pill d-none"></span>
                                    </div>
                                    <div class="fw-semibold" id="gl-period">-</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2">
                                    <div class="text-muted small">Saldo Awal</div>
                                    <div class="fw-semibold text-end" id="gl-opening">0.00</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 bg-light">
                                    <div class="text-muted small">Saldo Akhir</div>
                                    <div class="fw-semibold text-end" id="gl-closing-card">0.00</div>
                                </div>
                            </div>
                        </div>

                        <div id="gl-draft-blocker" class="alert alert-danger d-none mb-3">
                            <div class="fw-semibold small mb-1">
                                <i class="ri-error-warning-line align-bottom me-1"></i>
                                Periode ini masih OPEN, ada jurnal DRAFT yang belum diposting
                            </div>
                            <div class="small text-muted">Data mutasi di bawah bisa berubah sampai jurnal-jurnal tsb
                                diposting atau dibatalkan.</div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle" id="table-gl">
                                <thead class="text-muted">
                                    <tr class="text-uppercase small">
                                        <th>Tanggal</th>
                                        <th>No Jurnal</th>
                                        <th>Uraian</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Kredit</th>
                                        <th class="text-end">Saldo Berjalan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Saldo Akhir</th>
                                        <th></th>
                                        <th></th>
                                        <th class="text-end" id="gl-closing">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('web.alert.message')
    @endif
@else
    @include('web.alert.message')
@endif

@push('scripts')
    <script src="{{ asset('assets/js/controllers/report/general_ledger.js') }}"></script>
@endpush
