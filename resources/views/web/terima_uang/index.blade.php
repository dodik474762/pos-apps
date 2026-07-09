@if (isset($akses->terima_uang))
    @if ($akses->terima_uang->view == 1)
        <input type="hidden" id="update" value="{{ $akses->terima_uang->update }}">
        <input type="hidden" id="delete" value="{{ $akses->terima_uang->delete }}">
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
                                <h5 class="card-title mb-0">{{ $title }} History</h5>
                            </div>
                            <div class="col-sm-auto">
                            </div>
                        </div>
                    </div>
                    <div class="card-body border border-dashed border-end-0 border-start-0">
                        <form class="">
                            <div class="row g-3">
                                <!--end col-->
                                <div class="col-md-4">
                                    <input type="date" class="form-control" data-provider="flatpickr"
                                        data-date-format="d M, Y" data-range-date="true" id="filterDate"
                                        placeholder="Select date" value="{{ isset($tanggal) ? $tanggal : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="salesman" id="salesman" class="form-control select2">
                                        <option value=""></option>
                                        @foreach ($salesmans as $item)
                                            <option
                                                {{ isset($salesman) ? ($salesman == $item['id'] ? 'selected' : '') : '' }}
                                                value="{{ $item['id'] }}">{{ $item['nik'] }} - {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100"
                                        url="{{ route('terima-uang-print-all') }}" onclick="TerimaUang.search(this);">
                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                        Print All Invoice
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger w-100"
                                        url="{{ route('terima-uang-print') }}" onclick="TerimaUang.cetak(this);"> <i
                                            class="ri-equalizer-fill me-1 align-bottom"></i>
                                        Print
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success w-100"
                                        onclick="TerimaUang.submit(this, event, 'pending');"> <i
                                            class="ri-equalizer-fill me-1 align-bottom"></i>
                                        Submit
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    @if (strtolower($akses_user) == 'superadmin')
                                        <button type="button" class="btn btn-info w-100"
                                            onclick="TerimaUang.submit(this, event, 'posted');"> <i
                                                class="ri-equalizer-fill me-1 align-bottom"></i>
                                            Posting
                                        </button>
                                    @endif
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </form>
                    </div>
                    <div class="card-body pt-0">
                        <div>
                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active All py-3" data-bs-toggle="tab" id="All"
                                        href="#list-data" role="tab" aria-selected="true">
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> All {{ $title }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-3" data-bs-toggle="tab" id="all-submit"
                                        href="#list-data-submit" role="tab" aria-selected="false">
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> All Sudah Submit
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-3" data-bs-toggle="tab" id="all-posted"
                                        href="#list-data-posted" role="tab" aria-selected="false">
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> All Sudah Posted
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane active" id="list-data">
                                    <div class="table-responsive table-card mb-1">
                                        <table class="table table-nowrap align-middle" id="table-data">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>Tanggal Rute</th>
                                                    <th>Invoice Number</th>
                                                    <th>Tanggal Invoice</th>
                                                    <th>DO Number</th>
                                                    <th>Tanggal DO</th>
                                                    <th>Customer</th>
                                                    <th>Tipe Pembayaran</th>
                                                    <th>Tanggal Jatuh Tempo</th>
                                                    <th>Status</th>
                                                    <th>Tagihan (IDR)</th>
                                                    <th>Koresi Terima Uang</th>
                                                    <th style="width: 40px;">
                                                        <input type="checkbox" id="check-all"
                                                            onchange="TerimaUang.checkAll(this)">
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($invoices as $item)
                                                    @php
                                                        $do_number =
                                                            $item->do_number == ''
                                                                ? $item->dohs_number
                                                                : $item->do_number;
                                                        $do_date =
                                                            $item->do_date == '' ? $item->dohs_date : $item->do_date;
                                                        $total_bayar =
                                                            $item->total_terbayar_rph == ''
                                                                ? $item->total_terbayar
                                                                : $item->total_terbayar_rph;
                                                    @endphp
                                                    <tr class="input" invoice_id="{{ $item->invoice_id }}">
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $tanggal }}</td>
                                                        <td>{{ $item->invoice_number }}</td>
                                                        <td>{{ $item->invoice_date }}</td>
                                                        <td>{{ $do_date }}</td>
                                                        <td>{{ $do_number }}</td>
                                                        <td>{{ $item->customer_code }} - {{ $item->nama_customer }}
                                                        </td>
                                                        <td>{{ $item->top_customer }}</td>
                                                        <td>{{ $item->due_date }}</td>
                                                        <td>{{ $item->status }}</td>
                                                        <td>{{ number_format($item->total_amount, 0, ',', '.') }}
                                                        </td>
                                                        <td value="{{ $total_bayar }}">
                                                            @if ($item->status_received == '' || $item->status_received == 'PENDING')
                                                                <input type="text" value="{{ $total_bayar }}"
                                                                    id="amount_paid">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" data_id="{{ $item->invoice_id }}"
                                                                class="check-item" value="{{ $item->invoice_id }}">
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

                                <div class="tab-pane" id="list-data-submit">
                                    <div class="table-responsive table-card mb-1">
                                        <table class="table table-nowrap align-middle" id="table-data-submit">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>Tanggal Rute</th>
                                                    <th>Invoice Number</th>
                                                    <th>Tanggal Invoice</th>
                                                    <th>DO Number</th>
                                                    <th>Tanggal DO</th>
                                                    <th>Customer</th>
                                                    <th>Tipe Pembayaran</th>
                                                    <th>Tanggal Jatuh Tempo</th>
                                                    <th>Status</th>
                                                    <th>Tagihan (IDR)</th>
                                                    <th>Koresi Terima Uang</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($invoices as $item)
                                                    @php
                                                        $do_number =
                                                            $item->do_number == ''
                                                                ? $item->dohs_number
                                                                : $item->do_number;
                                                        $do_date =
                                                            $item->do_date == '' ? $item->dohs_date : $item->do_date;
                                                        $total_bayar =
                                                            $item->total_terbayar_rph == ''
                                                                ? $item->total_terbayar
                                                                : $item->total_terbayar_rph;
                                                    @endphp
                                                    @if ($item->status_received == 'PENDING')
                                                        <tr class="input" invoice_id="{{ $item->invoice_id }}">
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ $tanggal }}</td>
                                                            <td>{{ $item->invoice_number }}</td>
                                                            <td>{{ $item->invoice_date }}</td>
                                                            <td>{{ $do_date }}</td>
                                                            <td>{{ $do_number }}</td>
                                                            <td>{{ $item->customer_code }} -
                                                                {{ $item->nama_customer }}
                                                            </td>
                                                            <td>{{ $item->top_customer }}</td>
                                                            <td>{{ $item->due_date }}</td>
                                                            <td>{{ $item->status }}</td>
                                                            <td>{{ number_format($item->total_amount, 0, ',', '.') }}
                                                            </td>
                                                            <td value="{{ $total_bayar }}">
                                                                {{ number_format($total_bayar, 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endif
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

                                <div class="tab-pane" id="list-data-posted">
                                    <div class="table-responsive table-card mb-1">
                                        <table class="table table-nowrap align-middle" id="table-data-posted">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>Tanggal Rute</th>
                                                    <th>Invoice Number</th>
                                                    <th>Tanggal Invoice</th>
                                                    <th>DO Number</th>
                                                    <th>Tanggal DO</th>
                                                    <th>Customer</th>
                                                    <th>Tipe Pembayaran</th>
                                                    <th>Tanggal Jatuh Tempo</th>
                                                    <th>Status</th>
                                                    <th>Tagihan (IDR)</th>
                                                    <th>Koresi Terima Uang</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($invoices as $item)
                                                    @php
                                                        $do_number =
                                                            $item->do_number == ''
                                                                ? $item->dohs_number
                                                                : $item->do_number;
                                                        $do_date =
                                                            $item->do_date == '' ? $item->dohs_date : $item->do_date;
                                                        $total_bayar =
                                                            $item->total_terbayar_rph == ''
                                                                ? $item->total_terbayar
                                                                : $item->total_terbayar_rph;
                                                    @endphp
                                                    @if ($item->status_received == 'POSTED')
                                                        <tr class="input" invoice_id="{{ $item->invoice_id }}">
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ $tanggal }}</td>
                                                            <td>{{ $item->invoice_number }}</td>
                                                            <td>{{ $item->invoice_date }}</td>
                                                            <td>{{ $do_date }}</td>
                                                            <td>{{ $do_number }}</td>
                                                            <td>{{ $item->customer_code }} -
                                                                {{ $item->nama_customer }}
                                                            </td>
                                                            <td>{{ $item->top_customer }}</td>
                                                            <td>{{ $item->due_date }}</td>
                                                            <td>{{ $item->status }}</td>
                                                            <td>{{ number_format($item->total_amount, 0, ',', '.') }}
                                                            </td>
                                                            <td value="{{ $total_bayar }}">
                                                                {{ number_format($total_bayar, 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endif
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
