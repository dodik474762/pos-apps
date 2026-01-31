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

                <form onsubmit="Pasar.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Pasar</label>
                                <div>
                                    <input tabindex="1" type="text" id="nama_pasar" class="form-control required" error="Nama Pasar"
                                        placeholder="Nama Pasar" value="{{ isset($data->nama_pasar) ? $data->nama_pasar : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Latitude</label>
                                <div>
                                    <input tabindex="11" type="text" id="latitude" class="form-control" error="Latitude"
                                        placeholder="Latitude Ex : -6.200000" value="{{ isset($data->latitude) ? $data->latitude : '' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label>Longitude</label>
                                <div>
                                    <input tabindex="12" type="text" id="longitude" class="form-control" error="Longitude"
                                        placeholder="Longitude Ex : 106.816666" value="{{ isset($data->longitude) ? $data->longitude : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Provinsi</label>
                                <select class="form-control select2 required" error="Province" id="provinsi"
                                    onchange="Pasar.getCity(this)">
                                    <option value=""></option>
                                    @foreach ($data_province as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->provinsi) ? ($data->provinsi == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Kota</label>
                                <select class="form-control select2" error="Kota" id="kota" onchange="Pasar.getKecamatan(this)">
                                    @if (isset($data->kota))
                                        <option value="{{ $data->kota }}" selected>{{ $data->city_name }}</option>
                                    @endif
                                </select>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select class="form-control select2" error="Kecamatan" id="kecamatan">
                                    @if (isset($data->kecamatan))
                                        <option value="{{ $data->kecamatan }}" selected>{{ $data->kecamatan_name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end">
            <div>
                <button type="submit" onclick="Pasar.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Pasar.cancel(this, event)"
                    class="btn  waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
