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

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <form onsubmit="Customer.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-control select2 required" error="Kategori" id="customer_category">
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
                                    <input tabindex="2" type="text" id="pic" class="form-control required"
                                        error="PIC" placeholder="PIC"
                                        value="{{ isset($data->pic) ? $data->pic : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Office Contact</label>
                                <div>
                                    <input tabindex="4" type="text" id="office_contact"
                                        class="form-control required" error="Office Contact"
                                        placeholder="Office Contact"
                                        value="{{ isset($data->office_contact) ? $data->office_contact : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <div>
                                    <input tabindex="6" type="text" id="address" class="form-control required"
                                        error="Address" placeholder="Address"
                                        value="{{ isset($data->address) ? $data->address : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Provinsi</label>
                                <select class="form-control select2 required" error="Province" id="provinsi"
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
                                        <option value="IDR"
                                            {{ isset($data->currency) ? ($data->currency == 'IDR' ? 'selected' : '') : '' }}>
                                            IDR</option>
                                        <option value="USD"
                                            {{ isset($data->currency) ? ($data->currency == 'USD' ? 'selected' : '') : '' }}>
                                            USD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Payment Terms / TOP</label>
                                <div>
                                    <select name="" id="payment_terms"
                                        {{ strtolower($akses) != 'superadmin' ? 'disabled' : '' }}
                                        class="form-control required" error="TOP"
                                        onchange="Customer.changeCreditLimit(this)">
                                        <option value="">PILIH</option>
                                        @foreach ($tops as $item)
                                            <option value="{{ $item->id }}"
                                                {{ isset($data->payment_terms) ? ($data->payment_terms == $item->id ? 'selected' : '') : '' }}>
                                                {{ $item->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Credit Limit</label>
                                <div>
                                    <input disabled tabindex="10" type="number" id="credit_limit" class="form-control"
                                        error="Credit Limit" placeholder="Credit Limit"
                                        value="{{ isset($data->credit_limit) ? $data->credit_limit : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Price List</label>
                                <div>
                                    <select disabled name="" id="price_list" class="form-control">
                                        <option value="">PILIH</option>
                                        @foreach ($data_price_list as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Max Retur</label>
                                <div>
                                    <input disabled tabindex="10" type="number" id="max_retur" class="form-control"
                                        error="Max Retur" placeholder="Max Retur"
                                        value="{{ isset($data->max_retur) ? $data->max_retur : '9999999999999' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Longitude</label>
                                <div>
                                    <input tabindex="12" type="text" id="longitude" class="form-control"
                                        error="Longitude" placeholder="Longitude Ex : 106.816666"
                                        value="{{ isset($data->longitude) ? $data->longitude : '' }}">
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
                            <div class="mb-3">
                                <label class="form-label">Foto NPWP</label>
                                <input type="file" id="foto_npwp_path" class="form-control" error="Foto Customer"
                                    accept="image/*">
                                @if (isset($data->foto_npwp_path))
                                    @if ($data->foto_npwp_path != '')
                                        <br />
                                        <a href="{{ $data->foto_npwp_path }}">Check File Uploaded</a>
                                    @endif
                                @endif
                            </div>
                            <div class="mb-3">
                                <label>Channel Outlet</label>
                                <div>
                                    <select name="" id="channel_outlet" class="form-control required"
                                        error="Channel Outlet" onchange="Customer.changeCreditLimit(this)">
                                        <option value="">PILIH</option>
                                        @foreach ($channels as $item)
                                            <option value="{{ $item->term_id }}"
                                                {{ isset($data->channel_outlet) ? ($data->channel_outlet == $item->term_id ? 'selected' : '') : '' }}>
                                                {{ $item->keterangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Customer</label>
                                <div>
                                    <input tabindex="1" type="text" id="nama_customer"
                                        class="form-control required" error="Nama Customer"
                                        placeholder="Nama Customer"
                                        value="{{ isset($data->nama_customer) ? $data->nama_customer : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Phone</label>
                                <div>
                                    <input tabindex="3" type="text" id="phone"
                                        class="form-control required" error="Phone" placeholder="Phone"
                                        value="{{ isset($data->phone) ? $data->phone : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <div>
                                    <input tabindex="5" type="text" id="email"
                                        class="form-control required" error="Email" placeholder="Email"
                                        value="{{ isset($data->email) ? $data->email : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kota</label>
                                <select class="form-control select2" error="Kota" id="kota"
                                    onchange="Customer.getKecamatan(this)">
                                    @if (isset($data->kota))
                                        <option value="{{ $data->kota }}" selected>{{ $data->city_name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select class="form-control select2" error="Kecamatan" id="kecamatan"
                                    onchange="Customer.getKelurahan(this)">
                                    @if (isset($data->kecamatan))
                                        <option value="{{ $data->kecamatan }}" selected>{{ $data->kecamatan_name }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kelurahan</label>
                                <select class="form-control select2" error="Kelurahan" id="kelurahan">
                                    @if (isset($data->kelurahan))
                                        <option value="{{ $data->kelurahan }}" selected>{{ $data->kelurahan_name }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>NPWP</label>
                                <div>
                                    <input tabindex="9" type="text" id="npwp"
                                        class="form-control required" error="NPWP" placeholder="NPWP"
                                        value="{{ isset($data->npwp) ? $data->npwp : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>No. KTP</label>
                                <div>
                                    <input tabindex="9" type="text" id="no_ktp" class="form-control"
                                        error="No. KTP" placeholder="No. KTP"
                                        value="{{ isset($data->no_ktp) ? $data->no_ktp : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Reference Number</label>
                                <div>
                                    <input tabindex="10" type="text" id="reference_number" class="form-control"
                                        error="Reference Number" placeholder="Reference Number"
                                        value="{{ isset($data->reference_number) ? $data->reference_number : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Latitude</label>
                                <div>
                                    <input tabindex="11" type="text" id="latitude" class="form-control"
                                        error="Latitude" placeholder="Latitude Ex : -6.200000"
                                        value="{{ isset($data->latitude) ? $data->latitude : '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto Customer</label>
                                <input type="file" id="photo_path"
                                    class="form-control {{ isset($id) ? '' : 'required' }}" error="Foto Customer"
                                    accept="image/*">
                                @if (isset($data->photo_path))
                                    @if ($data->photo_path != '')
                                        <br />
                                        <a href="{{ $data->photo_path }}">Check File Uploaded</a>
                                    @endif
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto KTP</label>
                                <input type="file" id="foto_ktp_path" class="form-control" error="Foto Customer"
                                    accept="image/*">
                                @if (isset($data->foto_ktp_path))
                                    @if ($data->foto_ktp_path != '')
                                        <br />
                                        <a href="{{ $data->foto_ktp_path }}">Check File Uploaded</a>
                                    @endif
                                @endif
                            </div>
                            <div class="mb-3">
                                <label>Sub Channel Outlet</label>
                                <div>
                                    <select name="" id="sub_channel_outlet" class="form-control required"
                                        error="Sub Channel Outlet" onchange="Customer.changeCreditLimit(this)">
                                        <option value="">PILIH</option>
                                        @foreach ($sub_channels as $item)
                                            <option value="{{ $item->term_id }}"
                                                {{ isset($data->sub_channel_outlet) ? ($data->sub_channel_outlet == $item->term_id ? 'selected' : '') : '' }}>
                                                {{ $item->keterangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Min Faktur</label>
                                <div>
                                    <input disabled tabindex="12" type="number" id="min_invoice"
                                        class="form-control" error="Min Faktur "
                                        {{ strtolower($akses) != 'superadmin' ? 'readonly' : '' }}
                                        placeholder="Min Faktur"
                                        value="{{ isset($data->min_invoice) ? $data->min_invoice : '1' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (isset($id))
                        @include('web.customer.product-list-item-price')
                        @include('web.customer.list_stock_customer')
                    @endif
                </form>

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end">
            <div>
                @if (isset($view_detail))
                    @if ($akses == 'supervisor sales' || $akses == 'admin supervisor')
                        <button type="submit" onclick="Customer.submit(this, event, 'update-sales')"
                            class="btn btn-success waves-effect waves-light me-1">
                            Update
                        </button>
                    @endif
                @else
                    <button type="submit" onclick="Customer.submit(this, event)"
                        class="btn btn-success waves-effect waves-light me-1">
                        Submit
                    </button>
                @endif
                <button type="reset" onclick="Customer.cancel(this, event)" class="btn  waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
