@if (isset($akses->ap_subledger))
    @if ($akses->ap_subledger->view == 1)
        <input type="hidden" id="update" value="{{ $akses->ap_subledger->update }}">
        <input type="hidden" id="delete" value="{{ $akses->ap_subledger->delete }}">
        <button type="button" id="confirm-delete-btn" class="" style="display: none;" data-bs-toggle="modal"
            data-bs-target="#konfirmasi-delete"></button>
        <div id="content-confirm-delete"></div>

        <!-- Start Page Title -->
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
        <!-- End Page Title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" id="apSubledgerList">
                    <div class="card-header bg-transparent border-0 pb-3">
                        <div class="row align-items-center gy-3">
                            <div class="col-sm">
                                <h5 class="card-title mb-0 fw-semibold">{{ $title }} List</h5>
                                <p class="text-muted mb-0 small">Daftar hutang supplier</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <form class="">
                            <div class="row g-3 mb-4">
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label fw-medium text-muted small">Start Date</label>
                                    <input type="date" class="form-control" data-provider="flatpickr"
                                        data-date-format="Y-m-d" data-range-date="true" id="start_date"
                                        placeholder="Select start date">
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label fw-medium text-muted small">End Date</label>
                                    <input type="date" class="form-control" data-provider="flatpickr"
                                        data-date-format="Y-m-d" data-range-date="true" id="end_date"
                                        placeholder="Select end date">
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label fw-medium text-muted small">Supplier</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control select2">
                                        <option value="">Semua Supplier</option>
                                        @foreach ($suppliers as $s)
                                            <option value="{{ $s['id'] }}">{{ $s['code'] }} -
                                                {{ $s['nama_vendor'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <label class="form-label fw-medium text-muted small">Status</label>
                                    <select name="status" id="status" class="form-control select2">
                                        <option value="">Semua Status</option>
                                        <option value="OPEN">OPEN</option>
                                        <option value="PARTIALLY_PAID">PARTIALLY_PAID</option>
                                        <option value="PAID">PAID</option>
                                        <option value="REVERSED">REVERSED</option>
                                    </select>
                                </div>
                                <div class="col-xxl-12 col-sm-12 text-end pt-2">
                                    <button type="button" class="btn btn-primary" id="search-btn">
                                        <i class="ri-search-line align-bottom me-1"></i> Cari
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="reset-btn">
                                        <i class="ri-refresh-line align-bottom me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-lg-3">
                                <div class="card border border-primary/20 hover-shadow transition-all">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <p class="text-muted small mb-1 fw-medium">Total Invoice</p>
                                                <h3 class="mb-0 fw-bold text-primary" id="sum-invoice">0</h3>
                                            </div>
                                            <div class="bg-primary/10 p-3 rounded-xl">
                                                <i class="ri-file-text-line text-primary fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card border border-success/20 hover-shadow transition-all">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <p class="text-muted small mb-1 fw-medium">Total Payment</p>
                                                <h3 class="mb-0 fw-bold text-success" id="sum-payment">0</h3>
                                            </div>
                                            <div class="bg-success/10 p-3 rounded-xl">
                                                <i class="ri-bank-card-line text-success fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card border border-info/20 hover-shadow transition-all">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <p class="text-muted small mb-1 fw-medium">Total Credit Note</p>
                                                <h3 class="mb-0 fw-bold text-info" id="sum-credit-note">0</h3>
                                            </div>
                                            <div class="bg-info/10 p-3 rounded-xl">
                                                <i class="ri-refund-line text-info fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card border border-warning/20 hover-shadow transition-all">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <p class="text-muted small mb-1 fw-medium">Outstanding</p>
                                                <h3 class="mb-0 fw-bold text-warning" id="sum-outstanding">0</h3>
                                            </div>
                                            <div class="bg-warning/10 p-3 rounded-xl">
                                                <i class="ri-alert-line text-warning fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle" id="table-ap">
                                <thead class="text-muted">
                                    <tr class="text-uppercase small">
                                        <th>No</th>
                                        <th>Invoice No</th>
                                        <th>Supplier</th>
                                        <th>Invoice Date</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Invoice Amount</th>
                                        <th class="text-end">Paid Amount</th>
                                        <th class="text-end">Outstanding</th>
                                        <th>Status</th>
                                        <th style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                </tbody>
                            </table>

                            <div class="noresult" style="display: none">
                                <div class="text-center py-5">
                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                        colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px">
                                    </lord-icon>
                                    <h5 class="mt-3 text-muted">Sorry! No Result Found</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->
    @else
        @include('web.alert.message')
    @endif
@else
    @include('web.alert.message')
@endif

@push('scripts')
    <script src="{{ asset('assets/js/controllers/transaction/ap_subledger.js') }}"></script>
@endpush