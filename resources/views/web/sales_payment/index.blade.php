@if (isset($akses->sales_payment))
    @if ($akses->sales_payment->view == 1)
        <input type="hidden" id="update" value="{{ $akses->sales_payment->update }}">
        <input type="hidden" id="delete" value="{{ $akses->sales_payment->delete }}">
        <input type="hidden" id="url-print" value="{{ route('sales-payment-print-rekap') }}">
        <input type="hidden" id="url-confirm" value="{{ route('sales-payment-confirm') }}">
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
                                @if ($akses->sales_payment->insert == 1)
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a type="button" class="btn btn-primary add-btn" data-bs-toggle="modal"
                                            id="create-btn" href="javascript:void(0);"
                                            onclick="SalesPayment.addAll(this, event)"><i
                                                class="ri-add-line align-bottom me-1"></i> Create Bulk Payment</a>
                                        <a type="button" class="btn btn-success add-btn d-none" data-bs-toggle="modal"
                                            id="create-btn" href="javascript:void(0);"
                                            onclick="SalesPayment.add(this, event)"><i
                                                class="ri-add-line align-bottom me-1"></i> Create New</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body border border-dashed border-end-0 border-start-0">
                        <form class="">
                            <div class="row g-3">
                                <!--end col-->
                                <div class="col-md-3">
                                    <div>
                                        <input type="date" class="form-control" id="filter-date" value=""
                                            placeholder="Print Tanggal">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="salesman" id="salesman" class="form-control select2">
                                        <option value="">ALL</option>
                                        @foreach ($salesmans as $item)
                                            <option
                                                {{ isset($salesman) ? ($salesman == $item['id'] ? 'selected' : '') : '' }}
                                                value="{{ $item['id'] }}">{{ $item['nik'] }} - {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div>
                                        <button type="button" class="btn btn-danger w-100"
                                            onclick="SalesPayment.printRekapPembayaran();"> <i
                                                class="ri-printer-line me-1 align-bottom"></i>
                                            Print Rekap Pembayaran
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    @if (strtolower($akses_roles) == 'superadmin' || strtolower($akses_roles) == 'admin')
                                        <div>
                                            <button type="button" class="btn btn-success w-100"
                                                onclick="SalesPayment.confirmPayment();"> <i
                                                    class="ri-checkbox-line me-1 align-bottom"></i>
                                                Confirm Pembayaran
                                            </button>
                                        </div>
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
                                    <a class="nav-link py-3" data-bs-toggle="tab" id="pending"
                                        href="#list-data-pending" role="tab" aria-selected="true">
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> Pending Verification
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-3" data-bs-toggle="tab" id="posted"
                                        href="#list-data-posted" role="tab" aria-selected="true">
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> Posted Verification
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
                                                    <th>No. Pembayaran</th>
                                                    <th>Tanggal Pembayaran</th>
                                                    <th>Customer</th>
                                                    <th>Metode Pembayaran</th>
                                                    <th>Total </th>
                                                    <th>Dibuat Oleh</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
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

                                <div class="tab-pane" id="list-data-pending">
                                    <div class="table-responsive table-card mb-1">
                                        <table class="table table-nowrap align-middle" id="table-data-pending">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>No. Pembayaran</th>
                                                    <th>Tanggal Pembayaran</th>
                                                    <th>Customer</th>
                                                    <th>Metode Pembayaran</th>
                                                    <th>Total </th>
                                                    <th>Dibuat Oleh</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
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
                                                    <th>No. Pembayaran</th>
                                                    <th>Tanggal Pembayaran</th>
                                                    <th>Customer</th>
                                                    <th>Metode Pembayaran</th>
                                                    <th>Total </th>
                                                    <th>Dibuat Oleh</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
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
