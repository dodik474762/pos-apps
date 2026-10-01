<style>
    .dash-kpi {
        height: 100%;
    }

    .dash-kpi .card-body {
        padding: 18px 20px;
    }

    .dash-kpi .kpi-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .dash-kpi .kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .dash-kpi .kpi-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--vz-secondary-color, #878a99);
        margin: 0;
    }

    .dash-kpi .kpi-value {
        font-size: 22px;
        font-weight: 600;
        letter-spacing: -0.01em;
        line-height: 1.2;
        margin: 0 0 6px;
        font-variant-numeric: tabular-nums;
    }

    .dash-kpi .kpi-value small {
        font-size: 13px;
        font-weight: 500;
        color: var(--vz-secondary-color, #878a99);
        margin-right: 4px;
    }

    .dash-kpi .kpi-meta {
        font-size: 12.5px;
        color: var(--vz-secondary-color, #878a99);
        margin: 0;
    }

    .dash-kpi .kpi-meta b {
        color: var(--vz-body-color);
        font-weight: 600;
    }
</style>

<div class="row g-3 mb-3">
    {{-- Total Pesanan --}}
    <div class="col-xl-3 col-md-6 {{ strtolower($akses) != 'superadmin' ? 'd-none' : '' }}">
        <div class="card dash-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-success-subtle text-success"><i class="ri-shopping-cart-2-line"></i></span>
                    <p class="kpi-label">Total Pesanan</p>
                </div>
                <h4 class="kpi-value"><small>IDR</small>{{ number_format($summary_so['summary'], 0, ',', '.') }}</h4>
                <p class="kpi-meta"><b>{{ $summary_so['jumlah'] }}</b> pesanan</p>
            </div>
        </div>
    </div>

    {{-- Total Penjualan --}}
    <div class="col-xl-3 col-md-6 {{ strtolower($akses) != 'superadmin' ? 'd-none' : '' }}">
        <div class="card dash-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-info-subtle text-info"><i class="ri-line-chart-line"></i></span>
                    <p class="kpi-label">Total Penjualan</p>
                </div>
                <h4 class="kpi-value">
                    <small>IDR</small>{{ number_format($summary_invoice['summary_gross'], 0, ',', '.') }}</h4>
                <p class="kpi-meta">
                    Netto <b>{{ number_format($summary_invoice['summary_netto'], 0, ',', '.') }}</b>
                    &middot; <b>{{ $summary_invoice['jumlah'] }}</b> invoice
                </p>
            </div>
        </div>
    </div>

    {{-- Total Laba Bruto --}}
    <div class="col-xl-3 col-md-6 {{ strtolower($akses) != 'superadmin' ? 'd-none' : '' }}">
        <div class="card dash-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-primary-subtle text-primary"><i
                            class="ri-money-dollar-circle-line"></i></span>
                    <p class="kpi-label">Total Laba Bruto</p>
                </div>
                <h4 class="kpi-value"><small>IDR</small>{{ number_format($gross_profit, 0, ',', '.') }}</h4>
                <p class="kpi-meta">Selisih penjualan dan harga pokok</p>
            </div>
        </div>
    </div>

    {{-- Total Tagihan Outstanding --}}
    <div class="col-xl-3 col-md-6 {{ strtolower($akses) != 'superadmin' ? 'd-none' : '' }}">
        <div class="card dash-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-warning-subtle text-warning"><i class="ri-hourglass-line"></i></span>
                    <p class="kpi-label">Tagihan Outstanding</p>
                </div>
                <h4 class="kpi-value"><small>IDR</small>{{ number_format($summary_invoice['summary'], 0, ',', '.') }}
                </h4>
                <p class="kpi-meta"><b>{{ $summary_invoice['jumlah_outstanding'] }}</b> invoice belum lunas</p>
            </div>
        </div>
    </div>
</div>
