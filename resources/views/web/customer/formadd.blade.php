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
                                    <input tabindex="2" type="text" id="pic" class="form-control required" error="PIC"
                                        placeholder="PIC" value="{{ isset($data->pic) ? $data->pic : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Office Contact</label>
                                <div>
                                    <input tabindex="4" type="text" id="office_contact" class="form-control required" error="Office Contact"
                                        placeholder="Office Contact" value="{{ isset($data->office_contact) ? $data->office_contact : '' }}">
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
                                    <select name="" id="currency" class="form-control">
                                        <option value="">PILIH</option>
                                        <option value="IDR" {{ isset($data->currency) ? ($data->currency == 'IDR' ? 'selected' : '') : '' }}>IDR</option>
                                        <option value="USD" {{ isset($data->currency) ? ($data->currency == 'USD' ? 'selected' : '') : '' }}>USD</option>
                                    </select>
                                </div>
                            </div>
                             <div class="mb-3">
                                <label>Credit Limit</label>
                                <div>
                                    <input tabindex="10" type="number" id="credit_limit" class="form-control required" error="Credit Limit"
                                        placeholder="Credit Limit" value="{{ isset($data->credit_limit) ? $data->credit_limit : '' }}">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label>No. KTP</label>
                                <div>
                                    <input tabindex="9" type="text" id="no_ktp" class="form-control" error="No. KTP"
                                        placeholder="No. KTP" value="{{ isset($data->no_ktp) ? $data->no_ktp : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Customer</label>
                                <div>
                                    <input tabindex="1" type="text" id="nama_customer" class="form-control required" error="Nama Customer"
                                        placeholder="Nama Customer" value="{{ isset($data->nama_customer) ? $data->nama_customer : '' }}">
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
                                <select class="form-control select2" error="Kota" id="kota">
                                    @if (isset($data->kota))
                                        <option value="{{ $data->kota }}" selected>{{ $data->city_name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>NPWP</label>
                                <div>
                                    <input tabindex="9" type="text" id="npwp" class="form-control required" error="NPWP"
                                        placeholder="NPWP" value="{{ isset($data->npwp) ? $data->npwp : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Payment Terms / TOP</label>
                                <div>
                                    <input tabindex="10" type="number" id="payment_terms" class="form-control required" error="Payment Terms"
                                        placeholder="Payment Terms" value="{{ isset($data->payment_terms) ? $data->payment_terms : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Price List</label>
                                <div>
                                    <select name="" id="price_list" class="form-control">
                                        <option value="">PILIH</option>
                                        @foreach ($data_price_list as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
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
                <button type="submit" onclick="Customer.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Customer.cancel(this, event)"
                    class="btn  waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
