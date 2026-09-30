<style>
    /* Inventory Valuation - scoped di .iv-page, mengikuti variabel tema Velzon (light/dark otomatis) */
    .iv-page {
        --iv-in: #0f7a4d;
        --iv-out: #c0392b;
        --iv-line: var(--vz-border-color, #000);
        --iv-head: #1f3a52;
        --iv-zebra: var(--vz-light, #000);
    }

    [data-bs-theme="dark"] .iv-page {
        --iv-in: #4cc790;
        --iv-out: #ff8a72;
        --iv-head: #0c1319;
        --iv-zebra: rgba(255, 255, 255, 0.03);
    }

    /* Kartu ringkasan */
    .iv-page .iv-sum {
        border: 1px solid var(--iv-line);
        border-left: 4px solid var(--iv-head);
        border-radius: 6px;
        padding: 10px 14px;
        height: 100%;
        background: var(--vz-card-bg, #000);
    }

    .iv-page .iv-sum.in {
        border-left-color: var(--iv-in);
    }

    .iv-page .iv-sum.out {
        border-left-color: var(--iv-out);
    }

    .iv-page .iv-sum small {
        display: block;
        color: var(--vz-secondary-color, #878a99);
    }

    .iv-page .iv-sum b {
        font-size: 1.15rem;
        font-variant-numeric: tabular-nums;
    }

    /* Tabel */
    .iv-page .iv-scroll {
        max-height: 62vh;
        overflow: auto;
        border: 1px solid var(--iv-line);
        border-radius: 6px;
    }

    .iv-page #table-data {
        font-variant-numeric: tabular-nums;
        margin: 0;
    }

    .iv-page #table-data thead th {
        position: sticky;
        background: var(--iv-head);
        color: #000;
        font-weight: 600;
        text-align: center;
        border: 0;
        white-space: nowrap;
        z-index: 2;
    }

    .iv-page #table-data thead tr.iv-group th {
        top: 0;
        border-left: 1px solid rgba(255, 255, 255, 0.25);
    }

    .iv-page #table-data thead tr.iv-cols th {
        top: 33px;
        font-weight: 500;
        font-size: 12px;
    }

    .iv-page #table-data thead tr.iv-cols th.g {
        border-left: 1px solid rgba(255, 255, 255, 0.25);
    }

    .iv-page #table-data tbody td {
        border-bottom: 1px solid var(--iv-line);
        white-space: nowrap;
    }

    .iv-page #table-data tbody tr:nth-child(even) {
        background: var(--iv-zebra);
    }

    .iv-page #table-data tbody td.g {
        border-left: 1px solid var(--iv-line);
    }

    .iv-page #table-data tfoot th {
        background: var(--iv-zebra);
        border-top: 2px solid var(--iv-head);
    }

    .iv-page .iv-in {
        color: var(--iv-in);
    }

    .iv-page .iv-out {
        color: var(--iv-out);
    }

    .iv-page .iv-zero {
        color: var(--vz-secondary-color, #878a99);
        opacity: 0.55;
    }

    .iv-page .iv-tag {
        display: inline-block;
        font-size: 11px;
        padding: 1px 8px;
        border-radius: 10px;
        border: 1px solid var(--iv-line);
        color: var(--vz-secondary-color, #878a99);
    }

    .iv-page .iv-tag.avg {
        color: var(--iv-in);
        border-color: var(--iv-in);
    }

    .iv-page .iv-tag.master {
        color: #b7791f;
        border-color: #b7791f;
    }
</style>
@if (isset($akses->inventory_valuation))
    @if ($akses->inventory_valuation->view == 1)
        {{-- Tambahkan sekali di layout / section css: <link rel="stylesheet" href="{{ asset('assets/css/inventory-valuation.css') }}"> --}}
        <input type="hidden" id="update" value="{{ $akses->inventory_valuation->update }}">
        <input type="hidden" id="delete" value="{{ $akses->inventory_valuation->delete }}">

        <div class="iv-page">
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
                    <div class="card" id="orderList">
                        {{-- Filter --}}
                        <div class="card-header border-0">
                            <div class="row align-items-end gy-3">
                                <div class="col-sm">
                                    <h5 class="card-title mb-0">{{ $title }}</h5>
                                    <p class="text-muted mb-0 fs-13">Kartu stok dengan valuasi rata-rata bergerak</p>
                                </div>
                                <div class="col-sm-auto">
                                    <form class="row g-2 align-items-end">
                                        <div class="col-auto">
                                            <label class="form-label fs-12 text-muted mb-1">Dari tanggal</label>
                                            <input type="date" class="form-control" id="filter-tanggal-awal"
                                                value="{{ $date_start }}">
                                        </div>
                                        <div class="col-auto">
                                            <label class="form-label fs-12 text-muted mb-1">Sampai tanggal</label>
                                            <input type="date" class="form-control" id="filter-tanggal"
                                                value="{{ $tanggal }}">
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" route="{{ route('inventory-valuation') }}"
                                                class="btn btn-primary" onclick="InventoryValuation.filter(this);">
                                                <i class="ri-filter-3-line align-bottom me-1"></i> Tampilkan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            @if (isset($error))
                                <div class="alert alert-danger" role="alert">{{ $error }}</div>
                            @endif
                            @if (isset($success))
                                <div class="alert alert-success" role="alert">{{ $success }}</div>
                            @endif

                            {{-- Ringkasan (diisi getSummary) --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-4 col-xl-2">
                                    <div class="iv-sum in"><small>Qty masuk</small><b id="sum-qty-in">0</b></div>
                                </div>
                                <div class="col-6 col-md-4 col-xl-2">
                                    <div class="iv-sum out"><small>Qty keluar</small><b id="sum-qty-out">0</b></div>
                                </div>
                                <div class="col-6 col-md-4 col-xl-2">
                                    <div class="iv-sum in"><small>Nilai masuk (Rp)</small><b id="sum-value-in">0</b>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-xl-2">
                                    <div class="iv-sum out"><small>Nilai keluar (Rp)</small><b id="sum-value-out">0</b>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-xl-2">
                                    <div class="iv-sum"><small>Selisih nominal (Rp)</small><b id="sum-nominal">0</b>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-xl-2">
                                    <div class="iv-sum"><small>Nilai akhir (Rp)</small><b id="sum-closing">0</b></div>
                                </div>
                            </div>

                            {{-- Tabel --}}
                            <div class="iv-scroll mb-1">
                                <table class="table table-sm align-middle" id="table-data">
                                    <thead>
                                        <tr class="iv-group">
                                            <th colspan="5">Item</th>
                                            <th colspan="5">Kuantitas</th>
                                            <th colspan="7">Nilai (Rp)</th>
                                            <th colspan="3">Keterangan</th>
                                        </tr>
                                        <tr class="iv-cols">
                                            <th>No</th>
                                            <th>Kode</th>
                                            <th>Produk</th>
                                            <th>Gudang</th>
                                            <th>Tanggal</th>
                                            <th class="g">Saldo awal</th>
                                            <th>Masuk</th>
                                            <th>Keluar</th>
                                            <th>Adjust</th>
                                            <th>Saldo akhir</th>
                                            <th class="g">Nilai awal</th>
                                            <th>Harga satuan</th>
                                            <th>Nilai masuk</th>
                                            <th>Nilai keluar</th>
                                            <th>Nominal</th>
                                            <th>Nilai akhir</th>
                                            <th>Rata-rata biaya</th>
                                            <th class="g">Sumber biaya</th>
                                            <th>Catatan</th>
                                            <th>Tipe referensi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list"></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">Total periode</th>
                                            <th></th>
                                            <th class="text-end iv-in" id="total-qty-in">0</th>
                                            <th class="text-end iv-out" id="total-qty-out">0</th>
                                            <th class="text-end" id="total-qty-adjust">0</th>
                                            <th></th>
                                            <th class="text-end" id="total-opening-value">0</th>
                                            <th></th>
                                            <th class="text-end iv-in" id="total-value-in">0</th>
                                            <th class="text-end iv-out" id="total-value-out">0</th>
                                            <th class="text-end" id="total-nominal-value">0</th>
                                            <th class="text-end" id="total-closing-value">0</th>
                                            <th colspan="4"></th>
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
