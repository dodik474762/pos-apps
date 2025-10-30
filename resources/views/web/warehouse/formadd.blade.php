<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form onsubmit="Warehouse.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Kode</label>
                                <div>
                                    <input type="text" id="code" class="form-control required" error="Kode"
                                        placeholder="Kode" value="{{ isset($data->code) ? $data->code : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Nama</label>
                                <div>
                                    <input type="text" id="name" class="form-control required" error="Nama"
                                        placeholder="Nama" value="{{ isset($data->name) ? $data->name : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Lokasi</label>
                                <div>
                                    <input type="text" id="location" class="form-control required" error="Lokasi"
                                        placeholder="Lokasi" value="{{ isset($data->location) ? $data->location : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-end">
            <div>
                <button type="submit" onclick="Warehouse.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Warehouse.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
        <!-- end select2 -->

    </div>


</div>
<!-- end row -->
