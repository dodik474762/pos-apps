<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">
<button type="button" id="btn-show-modal" class="" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-karyawan"></button>
<div id="content-modal-form"></div>

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


@if (isset($data->id))
    @if ($data->acc_remarks != '' && $data->status == 'REJECTED')
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <h5 class="card-header">Informasi</h5>
                    <div class="card-body">
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-error align-top me-2"></i></h6>
                            <span>Rejected By {{ $data->acc_by_name }} {{ $data->acc_remarks }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <h5 class="card-header">Informasi</h5>
                    <div class="card-body">
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-check align-top me-2"></i></h6>
                            <span>{{ $data->status == 'COMPLETED' ? 'Full ' : '' }} Approved By {{ $data->acc_by_name }}</span>
                        </div>
                        <div class="text-end">
                            @if ($data->status != 'COMPLETED')
                                <p><i>PIC Next Approval {{ $data->jabatan_acc }}</i></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form
                        {{-- onsubmit="Presensi.submit(this, event)" --}}
                        action="{{ url('/api/transaksi/presensi/submit') }}"
                        method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Pilih Karyawan</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Presensi.showDataKaryawan(this)">Pilih</button>
                                    <input readonly id="nik" name="nik" type="text" class="form-control required"
                                        error="Karyawan" placeholder="Pilih Data Karyawan"
                                        aria-label="Pilih Data Karyawan" aria-describedby="button-addon1"
                                        value="{{ isset($users_default) ? $users_default : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Remarks</label>
                                <div>
                                    <input type="text" id="remarks" name="remarks" class="form-control" error="Remarks"
                                        placeholder="Remarks" value="{{ isset($data->remarks) ? $data->remarks : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Latitude</label>
                                <div>
                                    <input readonly type="text" id="latitude" name="latitude" class="form-control required" error="Latitude"
                                        placeholder="Latitude" value="{{ isset($data->latitude) ? $data->latitude : '0.0' }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Presence Date</label>
                                <div>
                                    <input type="date" id="presence_date" name="presence_date" class="form-control required"
                                        error="Leave Date" placeholder="Leave Date"
                                        readonly
                                        value="{{ isset($data->presence_date) ? $data->presence_date : date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="">File</label>
                                <div class="input-group">
                                    @if (isset($data->id))
                                        <a class="btn btn-outline-secondary" target="_blank" id="btn-lihat"
                                            href="{{ url('/') . $data->path_files . '/' . $data->files }}">Lihat
                                            File</a>
                                    @endif
                                    <input id="file" type="file" name="file" class="form-control"
                                        placeholder="Pilih Data File" aria-label="Pilih Data File" src=""
                                        error="Data File" aria-describedby="button-addon1"
                                        value="{{ isset($data->id) ? $data->files : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Longitude</label>
                                <div>
                                    <input readonly type="text" id="longitude" name="longitude" class="form-control required" error="Longitude"
                                        placeholder="Longitude" value="{{ isset($data->longitude) ? $data->longitude : '0.0' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end" style="margin-bottom: 10px;">
                        <div>
                            @if (isset($data->id))
                                @if ($data->status == 'LOCKED' || $data->status == 'APPROVED' || $data->status == 'COMPLETED')
                                @else
                                    <button type="submit"
                                        {{-- onclick="Presensi.submit(this, event)" --}}
                                        class="btn btn-success waves-effect waves-light me-1">
                                        Submit
                                    </button>
                                @endif
                            @else
                                <button type="submit"
                                    {{-- onclick="Presensi.submit(this, event)" --}}
                                    class="btn btn-success waves-effect waves-light me-1">
                                    Submit
                                </button>
                            @endif
                            <button type="reset" onclick="Presensi.cancel(this, event)" class="btn waves-effect">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
