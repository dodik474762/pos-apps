<style>
    .dash-return-kpi {
        height: 100%;
    }

    .dash-return-kpi .card-body {
        padding: 18px 20px;
    }

    .dash-return-kpi .kpi-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .dash-return-kpi .kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .dash-return-kpi .kpi-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--vz-secondary-color, #878a99);
        margin: 0;
    }

    .dash-return-kpi .kpi-value {
        font-size: 22px;
        font-weight: 600;
        letter-spacing: -0.01em;
        line-height: 1.2;
        margin: 0 0 6px;
        font-variant-numeric: tabular-nums;
    }

    .dash-return-kpi .kpi-meta {
        font-size: 12.5px;
        color: var(--vz-secondary-color, #878a99);
        margin: 0;
    }

    .dash-return-kpi .kpi-meta b {
        color: var(--vz-body-color);
        font-weight: 600;
    }
</style>

<div class="row g-3 mb-3">
    {{-- Jumlah Retur --}}
    <div class="col-xl-3 col-md-6">
        <div class="card dash-return-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-danger-subtle text-danger"><i class="ri-arrow-go-back-line"></i></span>
                    <p class="kpi-label">Jumlah Retur</p>
                </div>
                <h4 class="kpi-value">{{ number_format($summary['total_return'], 0, ',', '.') }}</h4>
                <p class="kpi-meta">Transaksi retur barang kondisi baik</p>
            </div>
        </div>
    </div>

    {{-- Total Qty Retur --}}
    <div class="col-xl-3 col-md-6">
        <div class="card dash-return-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-warning-subtle text-warning"><i class="ri-archive-2-line"></i></span>
                    <p class="kpi-label">Total Qty Retur</p>
                </div>
                <h4 class="kpi-value">{{ number_format($summary['total_qty'], 2, ',', '.') }}</h4>
                <p class="kpi-meta"><b>{{ $summary['total_product'] }}</b> produk berbeda</p>
            </div>
        </div>
    </div>

    {{-- Invoice Terdampak --}}
    <div class="col-xl-3 col-md-6">
        <div class="card dash-return-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-info-subtle text-info"><i class="ri-file-list-3-line"></i></span>
                    <p class="kpi-label">Invoice Terdampak</p>
                </div>
                <h4 class="kpi-value">{{ number_format($summary['total_invoice'], 0, ',', '.') }}</h4>
                <p class="kpi-meta">Invoice yang memiliki retur</p>
            </div>
        </div>
    </div>

    {{-- Customer Terdampak --}}
    <div class="col-xl-3 col-md-6">
        <div class="card dash-return-kpi">
            <div class="card-body">
                <div class="kpi-top">
                    <span class="kpi-icon bg-success-subtle text-success"><i class="ri-user-3-line"></i></span>
                    <p class="kpi-label">Customer Terdampak</p>
                </div>
                <h4 class="kpi-value">{{ number_format($summary['total_customer'], 0, ',', '.') }}</h4>
                <p class="kpi-meta">Customer yang melakukan retur</p>
            </div>
        </div>
    </div>
</div>
