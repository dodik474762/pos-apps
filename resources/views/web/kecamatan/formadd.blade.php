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
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="Kecamatan.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Province</label>
                                <select class="form-control select2" id="parent" onchange="Kecamatan.getCity(this)">
                                    <option value=""></option>
                                    @foreach ($data_province as $item)
                                        <option value="{{ $item['id'] }}" {{ isset($data->province) ? $data->province == $item['id'] ? 'selected' : '' : ''}}>{{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kota</label>
                                <select class="form-control select2" error="Kota" id="kota">
                                    @if (isset($data->kota))
                                        <option value="{{ $data->kota }}" selected>{{ $data->city_name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nama Kecamatan</label>
                                <div>
                                    <input type="text" id="kecamatan" class="form-control required" error="Nama Kecamatan"
                                        placeholder="Nama Kecamatan" value="{{ isset($data->name) ? $data->name : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end">
            <div>
                <button type="submit" onclick="Kecamatan.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Kecamatan.cancel(this, event)" class="btn btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>

    </div>


</div>
<!-- end row -->
