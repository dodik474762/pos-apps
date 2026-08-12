@if (isset($akses->sales_invoice))
    @if ($akses->sales_invoice->view == 1)
        <input type="hidden" id="update" value="{{ $akses->sales_invoice->update }}">
        <input type="hidden" id="delete" value="{{ $akses->sales_invoice->delete }}">
        <button type="button" id="confirm-delete-btn" class="" style="display: none;" data-bs-toggle="modal"
            data-bs-target="#konfirmasi-delete"></button>
        <div id="content-confirm-delete"></div>
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
                    <div class="card-header border-0">
                        <div class="row align-items-center gy-3">
                            <div class="col-sm">
                                <h5 class="card-title mb-0">{{ $title }} </h5>
                            </div>
                            <div class="col-sm-auto">
                                @if ($akses->sales_invoice->print == 1)
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                            id="create-btn" href="javascript:void(0);"
                                            url="{{ route('sales-invoice-print-multiple') }}"
                                            onclick="SalesInvoice.cetakAll(this, event)"><i
                                                class="ri-printer-line align-bottom me-1"></i> Cetak Semua</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="card-body pt-0">
                        <div>
                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active All py-3" data-bs-toggle="tab" id="All"
                                        href="#list-data" role="tab" aria-selected="true">
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> All {{ $title }}
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane active" id="list-data">
                                    <div class="table-responsive table-card mb-1">
                                        <table class="table table-nowrap align-middle" id="table-data-cetak">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>Invoice Number</th>
                                                    <th>Tanggal Invoice</th>
                                                    <th>DO Number</th>
                                                    <th>Tanggal DO</th>
                                                    <th>Customer</th>
                                                    <th>Warehouse</th>
                                                    <th>Dibuat Oleh</th>
                                                    <th>Tanggal Jatuh Tempo</th>
                                                    <th>Waktu Print</th>
                                                    <th>Status Invoice</th>
                                                    <th style="width: 40px;">
                                                        <input type="checkbox" id="check-all"
                                                            onchange="SalesInvoice.checkAll(this)">
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($invoices as $item)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $item->invoice_number }}</td>
                                                        <td>{{ $item->invoice_date }}</td>
                                                        <td>{{ $item->do_number }}</td>
                                                        <td>{{ $item->do_date }}</td>
                                                        <td>{{ $item->nama_customer }}</td>
                                                        <td>{{ $item->warehouse_name }}</td>
                                                        <td>{{ $item->created_by_name }}</td>
                                                        <td>{{ $item->due_date }}</td>
                                                        <td>{{ $item->print_date }}</td>
                                                        <td>{{ $item->status }}</td>
                                                        <td>
                                                            <input type="checkbox" class="check-item"
                                                                value="{{ $item->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
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
    @else
        @include('web.alert.message')
    @endif
@else
    @include('web.alert.message')
@endif
