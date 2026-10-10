@if (isset($akses->trial_balance))
    @if ($akses->trial_balance->view == 1)

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            .tb-page {
                --tb-bg: #ffffff;
                --tb-fg: #09090b;
                --tb-muted: #f4f4f5;
                --tb-muted-fg: #71717a;
                --tb-border: #e4e4e7;
                --tb-primary: #18181b;
                --tb-primary-fg: #fafafa;
                --tb-ring: #a1a1aa;
                --tb-radius: .625rem;

                --tb-success-fg: #047857;
                --tb-success-bg: #ecfdf5;
                --tb-success-border: #a7f3d0;
                --tb-warn-fg: #b45309;
                --tb-warn-bg: #fffbeb;
                --tb-warn-border: #fde68a;
                --tb-destructive-fg: #b91c1c;
                --tb-destructive-bg: #fef2f2;
                --tb-destructive-border: #fecaca;

                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
                color: var(--tb-fg);
                -webkit-font-smoothing: antialiased;
            }

            .tb-page h4,
            .tb-page .card-title {
                letter-spacing: -.01em;
            }

            /* ---------- Card (shadcn: border bukan shadow tebal) ---------- */
            .tb-page .card {
                background: var(--tb-bg);
                border: 1px solid var(--tb-border) !important;
                border-radius: var(--tb-radius);
                box-shadow: 0 1px 2px rgba(0, 0, 0, .03) !important;
            }

            .tb-page .card-header {
                border-bottom: 1px solid var(--tb-border) !important;
                padding: 1.1rem 1.25rem .9rem;
            }

            .tb-page .card-body {
                padding: 1.1rem 1.25rem 1.25rem;
            }

            .tb-page .card-title {
                font-size: .95rem;
                font-weight: 600;
                color: var(--tb-fg);
            }

            /* ---------- Form controls (shadcn input style) ---------- */
            .tb-page .form-label {
                font-size: .8rem;
                font-weight: 500;
                color: var(--tb-muted-fg);
                margin-bottom: .4rem;
            }

            .tb-page .select2-container--default .select2-selection--single,
            .tb-page .form-control {
                border: 1px solid var(--tb-border);
                border-radius: calc(var(--tb-radius) - 2px);
                height: 38px;
                font-size: .875rem;
            }

            .tb-page .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
                color: var(--tb-fg);
            }

            .tb-page .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }

            /* ---------- Button (shadcn "default" & disabled) ---------- */
            .tb-page .btn-primary,
            .tb-page #close-period-btn {
                background: var(--tb-primary);
                border-color: var(--tb-primary);
                color: var(--tb-primary-fg);
                border-radius: calc(var(--tb-radius) - 2px);
                font-size: .825rem;
                font-weight: 500;
                padding: .5rem 1rem;
                height: 38px;
                display: inline-flex;
                align-items: center;
                transition: background .12s ease;
            }

            .tb-page .btn-primary:hover,
            .tb-page #close-period-btn:not(:disabled):hover {
                background: #27272a;
                border-color: #27272a;
            }

            .tb-page #close-period-btn:disabled {
                background: var(--tb-muted);
                border-color: var(--tb-border);
                color: var(--tb-muted-fg);
                opacity: 1;
            }

            /* ---------- Kartu status periode ---------- */
            .tb-period-card .card-body {
                padding: 1.15rem 1.35rem;
            }

            #tb-period-name {
                font-size: .95rem;
                font-weight: 600;
            }

            #tb-period-meta {
                font-size: .8rem;
                color: var(--tb-muted-fg);
                margin-top: .2rem;
            }

            /* ---------- Badge (shadcn badge: rounded-full, border, text kecil tebal) ---------- */
            .tb-page .badge.rounded-pill {
                font-family: inherit;
                font-size: .7rem;
                font-weight: 600;
                padding: .3em .75em;
                border-radius: 999px;
                border: 1px solid transparent;
                letter-spacing: .1px;
            }

            .bg-success-subtle {
                background: var(--tb-success-bg) !important;
                border-color: var(--tb-success-border) !important;
            }

            .bg-success-subtle.text-success,
            .text-success.bg-success-subtle {
                color: var(--tb-success-fg) !important;
            }

            .bg-warning-subtle {
                background: var(--tb-warn-bg) !important;
                border-color: var(--tb-warn-border) !important;
            }

            .text-warning.bg-warning-subtle {
                color: var(--tb-warn-fg) !important;
            }

            .bg-secondary-subtle {
                background: var(--tb-muted) !important;
                border-color: var(--tb-border) !important;
            }

            .text-secondary.bg-secondary-subtle {
                color: var(--tb-muted-fg) !important;
            }

            /* ---------- Alert / blocker DRAFT (shadcn alert destructive) ---------- */
            .tb-page .alert-danger {
                background: var(--tb-bg);
                border: 1px solid var(--tb-destructive-border);
                border-radius: var(--tb-radius);
                padding: 1rem 1.1rem;
                color: var(--tb-fg);
            }

            .tb-page .alert-danger .fw-semibold {
                color: var(--tb-destructive-fg);
                font-size: .85rem;
            }

            #tb-blocker-list {
                list-style: none;
                padding-left: 0;
                margin: .6rem 0 0;
            }

            #tb-blocker-list li {
                padding: .5rem 0;
                border-bottom: 1px solid var(--tb-border);
                font-size: .8rem;
                color: var(--tb-muted-fg);
            }

            #tb-blocker-list li:last-child {
                border-bottom: none;
            }

            #tb-source-sub {
                color: var(--tb-muted-fg);
                font-size: .78rem;
                margin-top: .15rem;
            }

            /* ---------- Tabel (shadcn table: border tipis, header muted) ---------- */
            .tb-page #table-tb thead th {
                font-size: .72rem;
                font-weight: 500;
                letter-spacing: .3px;
                color: var(--tb-muted-fg);
                border-bottom: 1px solid var(--tb-border) !important;
                padding: 0 .75rem .65rem;
                text-transform: uppercase;
            }

            .tb-page #table-tb tbody td {
                padding: .75rem;
                border-color: var(--tb-border) !important;
                font-size: .875rem;
                color: var(--tb-fg);
            }

            .tb-page #table-tb tbody tr:hover {
                background: var(--tb-muted);
            }

            .tb-page #table-tb tbody td:nth-child(3),
            .tb-page #table-tb tbody td:nth-child(4),
            .tb-page #table-tb tfoot th:nth-child(3),
            .tb-page #table-tb tfoot th:nth-child(4) {
                font-variant-numeric: tabular-nums;
                font-feature-settings: "tnum";
            }

            .tb-page #table-tb tfoot tr:first-child th {
                border-top: 1px solid var(--tb-fg) !important;
                border-bottom: none !important;
                padding: .85rem .75rem .3rem;
                font-size: .875rem;
                font-weight: 600;
            }

            .tb-page #table-tb tfoot tr:last-child th {
                border: none !important;
                padding: .4rem .75rem 0;
            }

            /* Pill BALANCED / NOT BALANCED \u2014 murni CSS, JS tidak diubah */
            #tb-balanced {
                display: inline-block;
                margin-top: .5rem;
                padding: .3rem 1rem;
                border-radius: 999px;
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .3px;
                border: 1px solid transparent;
            }

            #tb-balanced.text-success {
                background: var(--tb-success-bg);
                border-color: var(--tb-success-border);
                color: var(--tb-success-fg) !important;
            }

            #tb-balanced.text-danger {
                background: var(--tb-destructive-bg);
                border-color: var(--tb-destructive-border);
                color: var(--tb-destructive-fg) !important;
            }

            .tb-page .dataTables_processing {
                border-radius: var(--tb-radius);
                border: 1px solid var(--tb-border) !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, .06) !important;
                font-size: .85rem;
            }
        </style>

        <div class="tb-page">

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
                    <div class="card border-0">
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

            {{-- Kartu status periode + aksi Tutup Periode --}}
            <div class="row" id="tb-period-card-wrapper" style="display:none;">
                <div class="col-lg-12">
                    <div class="card border-0 tb-period-card">
                        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="tb-period-name">-</span>
                                    <span id="tb-period-badge" class="badge rounded-pill"></span>
                                </div>
                                <div id="tb-period-meta">-</div>
                            </div>
                            <button type="button" class="btn btn-primary" id="close-period-btn" disabled>
                                <i class="ri-lock-line align-bottom me-1"></i> Tutup Periode
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Blocker: daftar jurnal DRAFT yang menghalangi closing --}}
            <div class="row" id="tb-blocker-wrapper" style="display:none;">
                <div class="col-lg-12">
                    <div class="alert alert-danger mb-3">
                        <div class="fw-semibold mb-1">
                            <i class="ri-error-warning-line align-bottom me-1"></i>
                            Tidak bisa ditutup &mdash; masih ada <span id="tb-blocker-count">0</span> jurnal berstatus
                            DRAFT
                        </div>
                        <ul id="tb-blocker-list"></ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0">
                        <div
                            class="card-header bg-transparent border-0 pb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-0 fw-semibold">Trial Balance</h5>
                                <div id="tb-source-sub">-</div>
                            </div>
                            <span id="tb-source-tag" class="badge rounded-pill">
                                <i class="ri-circle-fill align-bottom me-1" style="font-size:7px;"></i><span
                                    id="tb-source-text">-</span>
                            </span>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle" id="table-tb">
                                    <thead class="text-muted">
                                        <tr>
                                            <th>Kode Akun</th>
                                            <th>Nama Akun</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-end">Total</th>
                                            <th class="text-end" id="tb-total-debit">0.00</th>
                                            <th class="text-end" id="tb-total-credit">0.00</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-center" id="tb-balanced"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
    <script src="{{ asset('assets/js/controllers/report/trial_balance.js') }}"></script>
@endpush
