<input type="hidden" id="id" value="{{ isset($data->id) ? $data->id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Create {{ $title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="AccountingPeriods.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Year -->
                            <div class="mb-3">
                                <label class="form-label">Tahun</label>
                                <input type="text" id="year" class="form-control required"
                                    error="Tahun" placeholder="Masukkan tahun (misal: 2026)"
                                    value="{{ isset($data->year) ? $data->year : date('Y') }}">
                            </div>

                            <!-- Month -->
                            <div class="mb-3">
                                <label class="form-label">Bulan</label>
                                <select class="form-control select2 required" id="month" error="Bulan">
                                    <option value=""></option>
                                    @foreach ($month_list as $number => $name)
                                        <option value="{{ $number }}"
                                            {{ isset($data->month) && $data->month == $number ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea id="description" class="form-control" rows="3"
                                    placeholder="Keterangan tambahan">{{ isset($data->description) ? $data->description : '' }}</textarea>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Start Date -->
                            <div class="mb-3">
                                <label class="form-label">Tanggal Awal</label>
                                <input type="date" id="start_date" class="form-control required" error="Tanggal Awal"
                                    value="{{ isset($data->start_date) ? date('Y-m-d', strtotime($data->start_date)) : '' }}">
                            </div>

                            <!-- End Date -->
                            <div class="mb-3">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" id="end_date" class="form-control required" error="Tanggal Akhir"
                                    value="{{ isset($data->end_date) ? date('Y-m-d', strtotime($data->end_date)) : '' }}">
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label">Status Periode</label>
                                <select class="form-control select2" id="status">
                                    @foreach ($status_list as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ isset($data->status) && $data->status == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hanya periode berstatus <b>OPEN</b> yang dapat
                                    menerima jurnal.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-end">
            <div>
                <button type="submit" onclick="AccountingPeriods.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="AccountingPeriods.cancel(this, event)"
                    class="btn btn-secondary waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
