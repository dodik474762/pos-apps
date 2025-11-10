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

                <form onsubmit="Vendor.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                             <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-control select2 required" error="Kategori" id="category">
                                    @foreach ($categorys as $item)
                                        <option value="{{ $item->id }}"
                                            {{ isset($data->category) ? ($data->category == $item->id ? 'selected' : '') : '' }}>
                                            {{ $item->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>PIC</label>
                                <div>
                                    <input tabindex="2" type="text" id="pic" class="form-control required" error="PIC"
                                        placeholder="PIC" value="{{ isset($data->pic) ? $data->pic : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Mobile Phone</label>
                                <div>
                                    <input tabindex="4" type="text" id="mobile_phone" class="form-control required" error="Mobile Phone"
                                        placeholder="Mobile Phone" value="{{ isset($data->mobile_phone) ? $data->mobile_phone : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <div>
                                    <input tabindex="6" type="text" id="address" class="form-control required" error="Address"
                                        placeholder="Address" value="{{ isset($data->address) ? $data->address : '' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Provinsi</label>
                                <select class="form-control select2 required" error="Province" id="region"
                                    onchange="Vendor.getCity(this)">
                                    <option value=""></option>
                                    @foreach ($data_province as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->region) ? ($data->region == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Remarks</label>
                                <div>
                                    <input tabindex="9" type="text" id="remarks" class="form-control required" error="Remarks"
                                        placeholder="Remarks" value="{{ isset($data->remarks) ? $data->remarks : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Vendor</label>
                                <div>
                                    <input tabindex="1" type="text" id="nama_vendor" class="form-control required" error="Nama Vendor"
                                        placeholder="Nama Vendor" value="{{ isset($data->nama_vendor) ? $data->nama_vendor : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Phone</label>
                                <div>
                                    <input tabindex="3" type="text" id="phone" class="form-control required" error="Phone"
                                        placeholder="Phone" value="{{ isset($data->phone) ? $data->phone : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <div>
                                    <input tabindex="5" type="text" id="email" class="form-control required" error="Email"
                                        placeholder="Email" value="{{ isset($data->email) ? $data->email : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kota</label>
                                <select class="form-control select2" error="Kota" id="city">
                                    @if (isset($data->city))
                                        <option value="{{ $data->city }}" selected>{{ $data->city_name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end" style="margin-bottom: 10px;">
            <div>
                <button type="submit" onclick="Vendor.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Vendor.cancel(this, event)"
                    class="btn  waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
