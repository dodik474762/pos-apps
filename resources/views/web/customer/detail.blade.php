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

                <form onsubmit="Customer.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select disabled class="form-control select2 required" error="Kategori" id="customer_category">
                                    @foreach ($data_category as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->customer_category) ? ($data->customer_category == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['category'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>PIC</label>
                                <div>
                                    <input disabled tabindex="2" type="text" id="pic" class="form-control required" error="PIC"
                                        placeholder="PIC" value="{{ isset($data->pic) ? $data->pic : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Office Contact</label>
                                <div>
                                    <input tabindex="4" type="text" id="office_contact" class="form-control" error="Office Contact"
                                        placeholder="Office Contact" value="{{ isset($data->office_contact) ? $data->office_contact : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <div>
                                    <input disabled tabindex="6" type="text" id="address" class="form-control required" error="Address"
                                        placeholder="Address" value="{{ isset($data->address) ? $data->address : '' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Provinsi</label>
                                <select disabled class="form-control select2 required" error="Province" id="provinsi"
                                    onchange="Customer.getCity(this)">
                                    <option value=""></option>
                                    @foreach ($data_province as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->provinsi) ? ($data->provinsi == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Currency</label>
                                <div>
                                    <select disabled name="" id="currency" class="form-control">
                                        <option value="">PILIH</option>
                                        <option value="IDR" {{ isset($data->currency) ? ($data->currency == 'IDR' ? 'selected' : '') : '' }}>IDR</option>
                                        <option value="USD" {{ isset($data->currency) ? ($data->currency == 'USD' ? 'selected' : '') : '' }}>USD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Max Retur</label>
                                <div>
                                    <input disabled tabindex="10" type="number" id="max_retur" class="form-control required" error="Max Retur"
                                        placeholder="Max Retur" value="{{ isset($data->max_retur) ? $data->max_retur : '9999999999999' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label>Longitude</label>
                                <div>
                                    <input disabled tabindex="12" type="text" id="longitude" class="form-control" error="Longitude"
                                        placeholder="Longitude Ex : 106.816666" value="{{ isset($data->longitude) ? $data->longitude : '' }}">
                                </div>
                            </div>
                              <div class="mb-3">
                                <label class="form-label">Pasar</label>
                                <select class="form-control select2" error="Pasar" id="pasar">
                                    <option value=""></option>
                                    @foreach ($pasars as $item)
                                        <option value="{{ $item->id }}"
                                            {{ isset($data->pasar) ? ($data->pasar == $item->id ? 'selected' : '') : '' }}>
                                            {{ $item->nama_pasar }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Customer</label>
                                <div>
                                    <input disabled tabindex="1" type="text" id="nama_customer" class="form-control required" error="Nama Customer"
                                        placeholder="Nama Customer" value="{{ isset($data->nama_customer) ? $data->nama_customer : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Phone</label>
                                <div>
                                    <input disabled tabindex="3" type="text" id="phone" class="form-control required" error="Phone"
                                        placeholder="Phone" value="{{ isset($data->phone) ? $data->phone : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <div>
                                    <input disabled tabindex="5" type="text" id="email" class="form-control required" error="Email"
                                        placeholder="Email" value="{{ isset($data->email) ? $data->email : '' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Kota</label>
                                <select disabled class="form-control select2" error="Kota" id="kota" onchange="Customer.getKecamatan(this)">
                                    @if (isset($data->kota))
                                        <option value="{{ $data->kota }}" selected>{{ $data->city_name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>NPWP</label>
                                <div>
                                    <input disabled tabindex="9" type="text" id="npwp" class="form-control required" error="NPWP"
                                        placeholder="NPWP" value="{{ isset($data->npwp) ? $data->npwp : '' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label>No. KTP</label>
                                <div>
                                    <input disabled tabindex="9" type="text" id="no_ktp" class="form-control" error="No. KTP"
                                        placeholder="No. KTP" value="{{ isset($data->no_ktp) ? $data->no_ktp : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Reference Number</label>
                                <div>
                                    <input tabindex="10" type="text" id="reference_number" class="form-control" error="Reference Number"
                                        placeholder="Reference Number" value="{{ isset($data->reference_number) ? $data->reference_number : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Latitude</label>
                                <div>
                                    <input  disabled tabindex="11" type="text" id="latitude" class="form-control" error="Latitude"
                                        placeholder="Latitude Ex : -6.200000" value="{{ isset($data->latitude) ? $data->latitude : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h4>Foto Customer</h4>
                            <img src="{{ asset($data->photo_path) }}" alt="Foto">
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end">
            <div>
                @if ($data->status == '')
                    <button type="submit" onclick="Customer.approve(this, event)"
                        class="btn btn-success waves-effect waves-light me-1">
                        Approve
                    </button>
                    <button type="submit" onclick="Customer.reject(this, event)"
                        class="btn btn-danger waves-effect waves-light me-1">
                        Reject
                    </button>
                @endif
                <button type="reset" onclick="Customer.cancelAcc(this, event)"
                    class="btn  waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
