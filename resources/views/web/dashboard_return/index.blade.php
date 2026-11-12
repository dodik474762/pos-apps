<style>
    /* ===== Dashboard Return: style terscope di .dash-return ===== */
    .dash-return {
        --dash-radius: 12px;
        --dash-border: var(--vz-border-color, #e9ebec);
        --dash-muted: var(--vz-secondary-color, #878a99);
    }

    .dash-return .card {
        border: 1px solid var(--dash-border);
        border-radius: var(--dash-radius);
        box-shadow: none;
    }

    .dash-return .dash-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 20px;
        border-bottom: 1px solid var(--dash-border);
    }

    .dash-return .dash-card-head h4 {
        font-size: 15px;
        font-weight: 600;
        margin: 0;
    }

    .dash-return .dash-card-head p {
        font-size: 12.5px;
        color: var(--dash-muted);
        margin: 2px 0 0;
    }

    .dash-return .dash-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .dash-return .dash-hello h4 {
        font-size: 20px;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .dash-return .dash-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--dash-muted);
    }

    .dash-return table.table thead th {
        font-size: 12px;
        font-weight: 600;
        color: var(--dash-muted);
        white-space: nowrap;
    }

    .dash-return table.table tbody td {
        font-size: 13.5px;
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .dash-return table.table tbody tr {
        border-bottom: 1px solid var(--dash-border);
    }

    .dash-return table.table tbody tr:last-child {
        border-bottom: 0;
    }

    .dash-return .dataTables_wrapper .dataTables_info {
        font-size: 12.5px;
        padding-top: 14px;
    }

    .dash-return .dataTables_wrapper .dataTables_paginate {
        padding-top: 8px;
    }

    .dash-return .dataTables_wrapper .dataTables_filter {
        margin-bottom: 12px;
        font-size: 13px;
        color: var(--dash-muted);
    }

    .dash-return .dataTables_wrapper .dataTables_filter input {
        margin-left: 8px;
        width: 240px;
        max-width: 100%;
        padding: 6px 12px;
        font-size: 13px;
        border: 1px solid var(--dash-border);
        border-radius: 8px;
        background: var(--vz-input-bg, #fff);
        color: var(--vz-body-color);
        outline: none;
    }

    .dash-return .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--vz-primary, #405189);
    }

    .dash-return .dataTables_wrapper>.row {
        margin-left: 0;
        margin-right: 0;
    }

    .dash-return .dataTables_wrapper>.row>[class*="col-"] {
        padding-left: 0;
        padding-right: 0;
    }

    .dash-return table.table thead th:first-child,
    .dash-return table.table tbody td:first-child {
        padding-left: 12px;
    }

    .dash-return table.table thead th:last-child,
    .dash-return table.table tbody td:last-child {
        padding-right: 12px;
    }

    .dash-return .detail-label {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: var(--dash-muted);
        margin-bottom: 2px;
    }

    .dash-return .detail-value {
        font-size: 13.5px;
        font-weight: 600;
        margin-bottom: 12px;
        word-break: break-word;
    }

    .dash-return .section-title {
        font-size: 13px;
        font-weight: 600;
        margin: 18px 0 10px;
    }

    @media (max-width: 575.98px) {
        .dash-return #return_chart {
            min-width: 640px;
        }
    }
</style>

<div class="row dash-return">
    <div class="col">
        <input type="hidden" id="start_date" value="{{ $start_date }}">
        <input type="hidden" id="end_date" value="{{ $end_date }}">

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-lg-center flex-lg-row flex-column gap-3">
                    <div class="flex-grow-1 dash-hello">
                        <h4 class="mb-1">Dashboard Return</h4>
                        <span class="dash-date">
                            <i class="ri-calendar-line"></i>
                            {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                            <span class="text-muted">· Ringkasan retur penjualan barang kondisi baik</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ url('dashboard') }}" class="btn btn-soft-secondary btn-sm"
                            style="height:40px;display:inline-flex;align-items:center;gap:6px;">
                            <i class="ri-arrow-left-line"></i> Dashboard
                        </a>
                        <form action="" method="GET" class="d-flex align-items-end gap-2 m-0 flex-wrap">
                            <div>
                                <label for="filter_start_date" class="form-label mb-1 small text-muted">Dari Tanggal</label>
                                <input type="date" id="filter_start_date" name="start_date"
                                    class="form-control form-control-sm" value="{{ $start_date }}" required>
                            </div>
                            <div>
                                <label for="filter_end_date" class="form-label mb-1 small text-muted">Sampai Tanggal</label>
                                <input type="date" id="filter_end_date" name="end_date"
                                    class="form-control form-control-sm" value="{{ $end_date }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" style="height:31px;">
                                <i class="ri-filter-3-line me-1"></i> Filter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('web.dashboard_return.card_summary')

        {{-- Grafik retur --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="card mb-3">
                    <div class="dash-card-head">
                        <div class="d-flex align-items-center gap-3">
                            <span class="dash-title-icon bg-danger-subtle text-danger">
                                <i class="ri-bar-chart-2-line"></i>
                            </span>
                            <div>
                                <h4>Retur Penjualan</h4>
                                <p>Jumlah transaksi retur per bulan</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pb-2">
                        <div class="table-responsive">
                            <div id="return_chart" data-colors='["--vz-danger"]' class="apex-charts"
                                dir="ltr"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar retur --}}
        <div class="card mb-3">
            <div class="dash-card-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="dash-title-icon bg-primary-subtle text-primary">
                        <i class="ri-file-list-2-line"></i>
                    </span>
                    <div>
                        <h4>Daftar Sales Return</h4>
                        <p>Retur barang kondisi baik beserta invoice dan item yang dikembalikan</p>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-data-return"
                        class="table table-borderless table-centered align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>No. Retur</th>
                                <th>Tanggal Retur</th>
                                <th>No. Invoice</th>
                                <th>Tanggal Invoice</th>
                                <th>No. DO</th>
                                <th>Tanggal DO</th>
                                <th>No. PL</th>
                                <th>Tanggal PL</th>
                                <th>Customer</th>
                                <th class="text-end">Qty Retur</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th class="text-center">Item Batal</th>
                                <th>Diterima Gudang</th>
                                <th>PIC Gudang</th>
                                <th>Dibuat Oleh</th>
                                <th>Sumber Retur</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="list"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal detail retur --}}
<div class="modal fade" id="return-detail-modal" tabindex="-1" aria-labelledby="return-detail-modal-label"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="return-detail-modal-label">Detail Sales Return</h5>
                    <div class="text-muted small mt-1" id="return-detail-subtitle"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="return-detail-header"></div>

                <div class="section-title">
                    <i class="ri-archive-2-line me-1"></i> Barang yang diretur
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th class="text-end">Qty Retur</th>
                                <th>Satuan</th>
                                <th>Invoice Detail</th>
                            </tr>
                        </thead>
                        <tbody id="return-detail-items"></tbody>
                    </table>
                </div>

                <div class="section-title">
                    <i class="ri-close-circle-line me-1"></i> Item invoice yang dibatalkan
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th class="text-end">Qty Invoice</th>
                                <th>Satuan</th>
                                <th class="text-end">Qty Retur</th>
                            </tr>
                        </thead>
                        <tbody id="return-canceled-items"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success me-auto" id="btn-received-return"
                    onclick="DashboardReturn.confirmReceived()">
                    <i class="ri-checkbox-circle-line align-bottom me-1"></i> Received
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
