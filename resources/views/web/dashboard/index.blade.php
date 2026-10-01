<style>
    /* ===== Dashboard: style terscope di .dash agar tidak mengganggu halaman lain ===== */
    .dash {
        --dash-radius: 12px;
        --dash-border: var(--vz-border-color, #e9ebec);
        --dash-muted: var(--vz-secondary-color, #878a99);
    }

    .dash .card {
        border: 1px solid var(--dash-border);
        border-radius: var(--dash-radius);
        box-shadow: none;
    }

    .dash .dash-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 20px;
        border-bottom: 1px solid var(--dash-border);
    }

    .dash .dash-card-head h4 {
        font-size: 15px;
        font-weight: 600;
        margin: 0;
    }

    .dash .dash-card-head p {
        font-size: 12.5px;
        color: var(--dash-muted);
        margin: 2px 0 0;
    }

    .dash .dash-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    /* Header halaman */
    .dash .dash-hello h4 {
        font-size: 20px;
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .dash .dash-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--dash-muted);
    }

    .dash .dash-group-btn {
        height: 40px;
        padding: 0 16px;
        border-radius: 10px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    /* Toolbar filter */
    .dash .dash-filter {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .dash .dash-filter .dash-field {
        min-width: 220px;
    }

    .dash .dash-filter label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: var(--dash-muted);
        margin-bottom: 4px;
    }

    .dash #map {
        min-height: 560px;
        border-radius: 10px;
        border: 1px solid var(--dash-border);
        z-index: 0;
    }

    /* Popup peta */
    .dash-popup img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f3f4f6;
    }

    .dash-popup dl {
        margin: 0;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 3px 10px;
        font-size: 12.5px;
    }

    .dash-popup dt {
        color: #878a99;
        font-weight: 400;
    }

    .dash-popup dd {
        margin: 0;
        font-weight: 600;
    }

    /* Tabel (DataTables) */
    .dash table.table thead th {
        font-size: 12px;
        font-weight: 600;
        color: var(--dash-muted);
        white-space: nowrap;
    }

    .dash table.table tbody td {
        font-size: 13.5px;
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .dash table.table tbody tr {
        border-bottom: 1px solid var(--dash-border);
    }

    .dash table.table tbody tr:last-child {
        border-bottom: 0;
    }

    .dash .dataTables_wrapper .dataTables_info {
        font-size: 12.5px;
        padding-top: 14px;
    }

    .dash .dataTables_wrapper .dataTables_paginate {
        padding-top: 8px;
    }

    .dash .dataTables_wrapper .dataTables_filter {
        margin-bottom: 12px;
        font-size: 13px;
        color: var(--dash-muted);
    }

    .dash .dataTables_wrapper .dataTables_filter input {
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

    .dash .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--vz-primary, #405189);
    }

    .dash .dataTables_wrapper>.row {
        margin-left: 0;
        margin-right: 0;
    }

    .dash .dataTables_wrapper>.row>[class*="col-"] {
        padding-left: 0;
        padding-right: 0;
    }

    .dash table.table thead th:first-child,
    .dash table.table tbody td:first-child {
        padding-left: 12px;
    }

    .dash table.table thead th:last-child,
    .dash table.table tbody td:last-child {
        padding-right: 12px;
    }

    @media (max-width: 575.98px) {
        .dash .dash-filter .dash-field {
            min-width: 100%;
        }

        .dash #map {
            min-height: 380px;
        }
    }
</style>

<div class="row dash">
    <div class="col">
        <input type="hidden" id="year" value="{{ $year }}">

        <div class="h-100">
            {{-- Header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column gap-3">
                        <div class="flex-grow-1 dash-hello">
                            <h4 class="mb-1">Halo, {{ $username }}</h4>
                            <span class="dash-date">
                                <i class="ri-calendar-line"></i>
                                {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                                <span class="text-muted">· Ringkasan aktivitas perusahaan</span>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <form action="" method="GET" class="m-0">
                                <select name="year" class="form-select form-select-sm" style="width:110px;"
                                    onchange="this.form.submit()">
                                    <option value="{{ date('Y') }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                        {{ date('Y') }}</option>
                                    @for ($y = date('Y') - 1; $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            {{ $y }}</option>
                                    @endfor
                                </select>
                            </form>
                            <form action="javascript:void(0);">
                                <button type="button"
                                    class="btn btn-soft-info btn-icon waves-effect waves-light layout-rightside-btn"
                                    style="width: 140px; height: 40px;"><i class="ri-pulse-line"></i>
                                    {{ session('group_karyawan_name') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @include('web.dashboard.card_summary')
            @include('web.dashboard.list_sales_order')

            {{-- Grafik penjualan --}}
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="dash-card-head">
                            <div class="d-flex align-items-center gap-3">
                                <span class="dash-title-icon bg-success-subtle text-success">
                                    <i class="ri-bar-chart-2-line"></i>
                                </span>
                                <div>
                                    <h4>Penjualan</h4>
                                    <p>Perbandingan penjualan valid dan batal per bulan</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pb-2">
                            <div class="w-100">
                                <div id="penjualan_chart" data-colors='["--vz-success", "--vz-danger"]'
                                    class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Peta kunjungan salesman --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="dash-card-head">
                            <div class="d-flex align-items-center gap-3">
                                <span class="dash-title-icon bg-info-subtle text-info">
                                    <i class="ri-map-pin-user-line"></i>
                                </span>
                                <div>
                                    <h4>Kunjungan Salesman</h4>
                                    <p>Lokasi check-in berdasarkan salesman dan tanggal</p>
                                </div>
                            </div>

                            <div class="dash-filter">
                                <div class="dash-field">
                                    <label for="salesman">Salesman</label>
                                    <select class="form-select select2" onchange="Dashboard.getMapVisit(this)"
                                        id="salesman" style="width:100%;">
                                        <option value="">.:: Pilih Salesman ::.</option>
                                        @foreach ($data_salesman as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="dash-field">
                                    <label for="date-visit">Tanggal kunjungan</label>
                                    <input type="date" class="form-control" id="date-visit"
                                        onchange="Dashboard.getMapVisit()" value="}">
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="content-map-visit">
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoice outstanding (superadmin) --}}
            <div class="row {{ strtolower($akses) != 'superadmin' ? 'd-none' : '' }}">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="dash-card-head">
                            <div class="d-flex align-items-center gap-3">
                                <span class="dash-title-icon bg-warning-subtle text-warning">
                                    <i class="ri-file-list-3-line"></i>
                                </span>
                                <div>
                                    <h4>Invoice Outstanding</h4>
                                    <p>Tagihan yang belum lunas. Tanggal jatuh tempo yang lewat ditandai merah</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table-data-invoice"
                                    class="table table-borderless table-centered align-middle table-nowrap table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>Kode Customer</th>
                                            <th>Nama Customer</th>
                                            <th>No. Invoice</th>
                                            <th>Tgl. Invoice</th>
                                            <th>Jatuh Tempo</th>
                                            <th class="text-end">Outstanding</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end .h-100-->
    </div> <!-- end col -->
</div>
