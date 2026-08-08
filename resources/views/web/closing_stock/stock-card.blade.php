@if (isset($akses->stock_card))
    @if ($akses->stock_card->view == 1)
        <input type="hidden" id="update" value="{{ $akses->stock_card->update }}">
        <input type="hidden" id="delete" value="{{ $akses->stock_card->delete }}">
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
                                @if ($akses->stock_card->insert == 1)
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body border border-dashed border-end-0 border-start-0">
                        <form class="">
                            <div class="row g-3">
                                <!--end col-->
                                <div class="col-xxl-2 col-sm-4">
                                    <div>
                                        <input type="date" class="form-control" id="filter-tanggal-awal"
                                            value="{{ $date_start }}" placeholder="Select date">
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-sm-4">
                                    <div>
                                        <input type="date" class="form-control" id="filter-tanggal"
                                            value="{{ $tanggal }}" placeholder="Select date">
                                    </div>
                                </div>
                                <div class="col-xxl-1 col-sm-4">
                                    <div>
                                        <button type="button" route="{{ route('stock-card') }}"
                                            class="btn btn-primary w-100" onclick="ClosingStock.filter(this);">
                                            Filters
                                        </button>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </form>
                    </div>
                    <div class="card-body pt-0">
                        <br />
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
                                    <div class="table-responsive table-card mb-1" style="height: 350px">
                                        <table class="table table-nowrap align-middle" id="table-data">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th>No</th>
                                                    <th>Product Code</th>
                                                    <th>Product</th>
                                                    <th>Warehouse</th>
                                                    <th>Trans Date</th>
                                                    <th>Opening Balance</th>
                                                    <th>In</th>
                                                    <th>Out</th>
                                                    <th>Adjust</th>
                                                    <th>Ending Balance</th>
                                                    <th>Note</th>
                                                    <th>Reference Type</th>
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
