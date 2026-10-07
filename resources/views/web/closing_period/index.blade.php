<style>
    .closing-period .cursor-pointer {
        cursor: pointer;
    }

    .closing-period .kpi-card {
        height: 100%;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .closing-period .kpi-card:hover {
        border-color: var(--vz-primary, #405189);
        box-shadow: 0 4px 14px rgba(64, 81, 137, 0.12);
    }

    .closing-period .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .closing-period .kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .closing-period .kpi-label {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--vz-secondary-color, #878a99);
        margin: 0;
    }

    .closing-period .kpi-value {
        font-size: 24px;
        font-weight: 600;
        line-height: 1.2;
        margin: 0;
        font-variant-numeric: tabular-nums;
    }

    .closing-period .nav-tabs .nav-link i {
        margin-right: 4px;
    }
</style>

<div class="closing-period">
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

    <div class="row g-3 mb-3">
        @foreach ($tabs as $key => $tab)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card kpi-card cursor-pointer" onclick="ClosingPeriod.showTab('{{ $key }}')">
                    <div class="card-body">
                        <div class="kpi-top">
                            <span
                                class="kpi-icon {{ $summary[$key] > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }}">
                                <i class="{{ $tab['icon'] }}"></i>
                            </span>
                            <span class="badge {{ $summary[$key] > 0 ? 'bg-danger' : 'bg-success' }}">
                                {{ $summary[$key] > 0 ? 'Pending' : 'Clear' }}
                            </span>
                        </div>
                        <p class="kpi-label">{{ $tab['label'] }}</p>
                        <h4 class="kpi-value">{{ $summary[$key] }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="closingPeriodList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Outstanding Documents {{ $title }}</h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-sm-end">
                                <select class="form-select form-select-sm w-auto"
                                    onchange="ClosingPeriod.changePeriod(this)">
                                    <option value="">Bulan Berjalan ({{ date('F Y') }})</option>
                                    @foreach ($periods as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ $period['id'] === $item['id'] ? 'selected' : '' }}>
                                            {{ $item['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    Period : {{ $period_start }} s/d {{ $period_end }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    @if (!empty($stock_closing))
                        <div class="alert alert-success" role="alert">
                            <strong>Success!</strong> Inventory Closing {{ $period_label }} sudah dilakukan
                            ({{ $stock_closing->closing_date }})
                        </div>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <strong>Attention!</strong> Inventory Closing {{ $period_label }} belum dilakukan
                        </div>
                    @endif

                    <div>
                        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                            @foreach ($tabs as $key => $tab)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }} py-3"
                                        data-bs-toggle="tab" id="tab-{{ $key }}" href="#list-data-{{ $key }}"
                                        role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <i class="{{ $tab['icon'] }} me-1 align-bottom"></i> {{ $tab['label'] }}
                                        <span
                                            class="badge {{ $summary[$key] > 0 ? 'bg-danger' : 'bg-success' }} ms-1">{{ $summary[$key] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach ($tabs as $key => $tab)
                                <div class="tab-pane {{ $loop->first ? 'active' : '' }}"
                                    id="list-data-{{ $key }}" role="tabpanel">
                                    @if (!empty($tab['post']))
                                        <div
                                            class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                            <span class="text-muted fs-14" id="selected-{{ $key }}">0 dokumen
                                                dipilih</span>
                                            <button type="button" class="btn btn-primary btn-sm"
                                                onclick="ClosingPeriod.post('{{ $key }}', event)">
                                                <i class="ri-fire-line me-1 align-bottom"></i>Posting
                                            </button>
                                        </div>
                                    @elseif (!empty($tab['info']))
                                        <div class="alert alert-info py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2"
                                            role="alert">
                                            <span class="fs-14">{{ $tab['info']['message'] }}</span>
                                            <a href="{{ url($tab['info']['url']) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                {{ $tab['info']['label'] }}<i class="ri-external-link-line ms-1"></i>
                                            </a>
                                        </div>
                                    @endif
                                    <div class="table-responsive table-card mb-1" style="height: 420px">
                                        <table class="table table-nowrap align-middle" id="table-{{ $key }}">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    @foreach ($tab['columns'] as $column)
                                                        @if (($column['type'] ?? '') === 'check')
                                                            <th style="width: 40px;">
                                                                <input type="checkbox" id="check-all-{{ $key }}"
                                                                    onchange="ClosingPeriod.checkAll(this, '{{ $key }}')">
                                                            </th>
                                                        @else
                                                            <th>{{ $column['label'] }}</th>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                            </tbody>
                                        </table>
                                        <div class="noresult" style="display: none">
                                            <div class="text-center">
                                                <lord-icon
                                                    src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#405189,secondary:#0ab39c"
                                                    style="width:75px;height:75px"></lord-icon>
                                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.CLOSING_PERIOD_TABS = @json($tabs);
</script>
