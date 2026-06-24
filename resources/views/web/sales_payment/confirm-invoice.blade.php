<button type="button" id="confirm-delete-btn" class="" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#konfirmasi-delete"></button>
<div id="content-confirm-delete"></div>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Confirm {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Confirm {{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="orderList">
            <div class="card-body border border-dashed border-end-0 border-start-0">
                <form class="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="">Tanggal Bayar</label>
                            <div>
                                <input type="date" disabled class="form-control" id="filter-date"
                                    value="{{ $date }}" placeholder="Print Tanggal Bayar">
                            </div>
                        </div>
                        <div class="col-md-2">
                            @if (strtolower($akses_roles) == 'superadmin' || strtolower($akses_roles) == 'admin')
                                <label for="">Confirm</label>
                                <div>
                                    <button type="button" class="btn btn-success w-100"
                                        onclick="SalesPayment.confirmAll();"> <i
                                            class="ri-checkbox-line me-1 align-bottom"></i>
                                        Confirm
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body pt-0">
                <div>
                    <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active All py-3" data-bs-toggle="tab" id="All" href="#list-data"
                                role="tab" aria-selected="true">
                                <i class="ri-store-2-fill me-1 align-bottom"></i> Confirm All {{ $title }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="list-data">
                            <div class="table-responsive table-card mb-1">
                                <table class="table table-nowrap align-middle" id="table-data-confirm">
                                    <thead class="text-muted table-light">
                                        <tr class="text-uppercase">
                                            <th>No</th>
                                            <th>Salesman</th>
                                            <th>No. Invoice</th>
                                            <th>Pelanggan</th>
                                            <th>Kecamatan</th>
                                            <th>Metode Bayar</th>
                                            <th>Tanggal Invoice</th>
                                            <th>Tanggal Jatuh Tempo</th>
                                            <th>Status Invoice</th>
                                            <th>Jumlah Belum Dibayar</th>
                                            <th>Jumlah Dibayar</th>
                                            <th>Tanggal Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @php
                                            $total = 0;
                                            $no = 1;
                                        @endphp
                                        @if (!empty($data_payment))
                                            @foreach ($data_payment as $item_payment)
                                                @foreach ($item_payment->items as $i => $item)
                                                    @php
                                                        $salesman =
                                                            $item->invoice->do?->so?->salesmans?->name ??
                                                            ($item->invoice->so?->salesmans?->name ?? '-');
                                                        $show = true;
                                                    @endphp

                                                    @if ($salesmans != '')
                                                        @if ($salesman == $salesmans->name)
                                                            @php
                                                                $show = true;
                                                            @endphp
                                                        @else
                                                            @php
                                                                $show = false;
                                                            @endphp
                                                        @endif
                                                    @endif
                                                    @if ($show)
                                                        <tr>
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ $salesman }}</td>
                                                            <td>{{ $item->invoice->invoice_number }}</td>
                                                            <td>{{ $item_payment->customer_code }} -
                                                                {{ $item_payment->nama_customer }}</td>
                                                            <td>{{ $item_payment->customers->kecamatans->name ?? '-' }}
                                                            </td>
                                                            <td>{{ $item_payment->payment_method }}</td>
                                                            <td>{{ $item->invoice->invoice_date }}</td>
                                                            <td>{{ $item->invoice->due_date }}</td>
                                                            <td>{{ $item->invoice->status }}</td>
                                                            <td>{{ number_format($item->outstanding_amount, 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ number_format($item->allocated_amount, 0, ',', '.') }}
                                                            </td>
                                                            <td>{{ $item_payment->payment_date }}</td>
                                                        </tr>
                                                        @php
                                                            $total += $item->allocated_amount ?? 0;
                                                        @endphp
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                <div class="noresult" style="display: none">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#405189,secondary:#0ab39c"
                                            style="width:75px;height:75px"></lord-icon>
                                        <h5 class="mt-2">Sorry! No Result Found</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--end col-->
</div>
