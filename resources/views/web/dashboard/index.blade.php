<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Hi, {{ $username }}!</h4>
                            <p class="text-muted mb-0">Here's what's happening with your company today.</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-auto">
                                        <button type="button"
                                            class="btn btn-soft-info btn-icon waves-effect waves-light layout-rightside-btn"
                                            style="width: 140px; height: 40px;"><i class="ri-pulse-line"></i>
                                            {{ session('group_karyawan_name') }}</button>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            @include('web.dashboard.card_summary')

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Penjualan</h4>
                        </div><!-- end card header -->

                        <div class="card-body p-0 pb-2">
                            <div class="w-100">
                                <div id="forecast_chart" data-colors='["--vz-primary", "--vz-success", "--vz-danger"]'
                                    class="apex-charts" dir="ltr"></div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
                <!-- end col -->
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Total Outlet</h4>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="input-group mb-3">
                                        <select class="form-select select2" onchange="Dashboard.getCity(this)"
                                            id="province" style="width:80%;">
                                            <option value="">.:: Pilih Provinsi ::.</option>
                                            @foreach ($data_province as $item)
                                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group mb-3">
                                        <select class="form-select select2" onchange="Dashboard.getMapProject()"
                                            id="city" style="width:80%;">
                                            <option value="">.:: Pilih Kota ::.</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group mb-3">
                                        <select class="form-select select2" onchange="Dashboard.getMapProject()"
                                            id="years" style="width:80%;">
                                            @for ($i = intval(date('Y')) - 5; $i <= date('Y'); $i++)
                                                <option value="{{ $i }}"
                                                    {{ intval(date('Y')) == $i ? 'selected' : '' }}>
                                                    {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="content-map=project">
                                        <div id="map" style="min-height: 700px;" class=""></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Penjualan</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table id="table-data-presensi"
                                    class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr class="text-uppercase">
                                            <th>No</th>
                                            <th>Code</th>
                                            <th>Tanggal</th>
                                            <th>Pelanggan</th>
                                            <th>Produk</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                    </tbody>
                                </table><!-- end table -->
                            </div>
                        </div>
                    </div> <!-- .card-->
                </div> <!-- .col-->
            </div> <!-- end row-->

        </div> <!-- end .h-100-->

    </div> <!-- end col -->
</div>
