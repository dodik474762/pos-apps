@if (isset($akses->report_stock_customer))
    @if ($akses->report_stock_customer->view == 1)
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
                                <h5 class="card-title mb-0">{{ $title }} List</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border border-dashed border-end-0 border-start-0">
                        <form class="">
                            <div class="row g-3">
                                <div class="col-xxl-3 col-sm-6">
                                    <div>
                                        <select class="form-control select2" id="filter-customer">
                                            <option value="">Semua Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ isset($data->customer_id) && $data->customer_id == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->code }} - {{ $customer->nama_customer }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <div>
                                        <button type="button" class="btn btn-primary w-100" onclick="ReportStockCustomer.filter();">
                                            <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                            Filters
                                        </button>
                                    </div>
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
                                        <i class="ri-store-2-fill me-1 align-bottom"></i> All {{ $title }}
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane active" id="list-data">
                                    @if (isset($error))
                                        <div class="alert alert-danger" role="alert">
                                            {{ $error }}
                                        </div>
                                    @endif

                                    @if (isset($success))
                                        <div class="alert alert-success" role="alert">
                                            {{ $success }}
                                        </div>
                                    @endif
                                    <div class="table-responsive table-card mb-1">
                                        <table class="table table-nowrap align-middle" id="table-data">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>Customer Code</th>
                                                    <th>Customer Name</th>
                                                    <th>Product Code</th>
                                                    <th>Product Name</th>
                                                    <th>Unit</th>
                                                    <th>Qty</th>
                                                    <th>Unit Price</th>
                                                    <th>Status</th>
                                                    <th>Is Free Good</th>
                                                    <th>Principal</th>
                                                    <th>Foto</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                            </tbody>
                                        </table>
                                        <div class="noresult" style="display: none">
                                            <div class="text-center">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px">
                                                </lord-icon>
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
