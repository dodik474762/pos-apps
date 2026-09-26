@if (isset($akses->ar_subledger))
    @if ($akses->ar_subledger->view == 1)
        <input type="hidden" id="update" value="{{ $akses->ar_subledger->update }}">
        <input type="hidden" id="delete" value="{{ $akses->ar_subledger->delete }}">
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
                            <li class="breadcrumb-item"><a href="{{ url('transaksi/ar_subledger') }}">{{ $title_parent }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('transaksi/ar_subledger') }}">AR Subledger</a></li>
                            <li class="breadcrumb-item active">{{ $customer->code }} - {{ $customer->nama_customer }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-3">
                        <h5 class="card-title mb-0 fw-semibold">Filter Periode</h5>
                        <p class="text-muted mb-0 small">Filter data ledger customer</p>
                    </div>
                    <div class="card-body pt-0">
                        <form id="filter-form" class="row g-3">
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-medium text-muted small">Start Date</label>
                                <input type="date" class="form-control" data-provider="flatpickr"
                                    data-date-format="Y-m-d" id="start_date" name="start_date"
                                    value="{{ $start_date ?? '' }}" placeholder="Select start date">
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-medium text-muted small">End Date</label>
                                <input type="date" class="form-control" data-provider="flatpickr"
                                    data-date-format="Y-m-d" id="end_date" name="end_date"
                                    value="{{ $end_date ?? '' }}" placeholder="Select end date">
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <label class="form-label fw-medium text-muted small">Customer</label>
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                <input type="text" class="form-control" readonly
                                    value="{{ $customer->code }} - {{ $customer->nama_customer }}">
                            </div>
                            <div class="col-xxl-12 col-sm-12 text-end pt-2">
                                <a href="{{ url('transaksi/ar_subledger') }}" class="btn btn-outline-secondary">
                                    <i class="ri-arrow-left-line align-bottom me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line align-bottom me-1"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-3">
                <div class="card border border-primary/20 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-medium">Total Invoice</p>
                                <h3 class="mb-0 fw-bold text-primary">{{ number_format($totalInvoices, 2) }}</h3>
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
                                <h3 class="mb-0 fw-bold text-success">{{ number_format($totalPayments, 2) }}</h3>
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
                                <h3 class="mb-0 fw-bold text-info">{{ number_format($totalCreditNotes, 2) }}</h3>
                            </div>
                            <div class="bg-info/10 p-3 rounded-xl">
                                <i class="ri-refund-line text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card border {{ $outstanding > 0 ? 'border-warning/20' : 'border-success/20' }} hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1 fw-medium">Outstanding</p>
                                <h3 class="mb-0 fw-bold {{ $outstanding > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($outstanding, 2) }}</h3>
                            </div>
                            <div class="{{ $outstanding > 0 ? 'bg-warning/10' : 'bg-success/10' }} p-3 rounded-xl">
                                <i class="{{ $outstanding > 0 ? 'ri-alert-line text-warning' : 'ri-check-line text-success' }} fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-3">
                        <h5 class="card-title mb-0 fw-semibold">Customer Ledger</h5>
                        <p class="text-muted mb-0 small">Riwayat transaksi {{ $customer->code }} - {{ $customer->nama_customer }}</p>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table id="table-ledger" class="table table-nowrap align-middle">
                                <thead class="text-muted">
                                    <tr class="text-uppercase small">
                                        <th>No</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Ref No</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                </tbody>
                            </table>

                            <div class="noresult" style="display: none">
                                <div class="text-center py-5">
                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                        trigger="loop"
                                        colors="primary:#405189,secondary:#0ab39c"
                                        style="width:75px;height:75px">
                                    </lord-icon>
                                    <h5 class="mt-3 text-muted">Sorry! No Result Found</h5>
                                </div>
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

@push('scripts')
<script src="{{ asset('assets/js/controllers/transaction/ar_subledger.js') }}"></script>
@endpush