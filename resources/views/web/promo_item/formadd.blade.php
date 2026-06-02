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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form onsubmit="PromoItem.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Promo</label>
                                <div>
                                    <input type="text" id="promo_name" class="form-control required"
                                        error="Nama Promo" placeholder="Nama Promo"
                                        value="{{ isset($data->promo_name) ? $data->promo_name : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Min Qty</label>
                                <div>
                                    <input type="number" id="min_qty" name="min_qty" class="form-control required"
                                        error="Min Qty" min="1"
                                        value="{{ isset($data->min_qty) ? $data->min_qty : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Berlaku Mulai</label>
                                <div>
                                    <input type="date" id="date_start" name="date_start"
                                        class="form-control required" error="Tanggal Berlaku Mulai"
                                        value="{{ isset($data->date_start) ? $data->date_start : date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Min Mix</label>
                                <div>
                                    <input type="number" id="min_mix" name="min_mix" class="form-control required"
                                        error="Min Mix" min="1"
                                        value="{{ isset($data->min_mix) ? $data->min_mix : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount Nilai</label>
                                <div>
                                    <input type="number" id="disc_value" name="disc_value"
                                        class="form-control required" error="Disc Nilai" min="1"
                                        value="{{ isset($data->discount_value) ? $data->discount_value : '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Sub Channel Outlet</label>
                                <div>
                                    <select name="" id="sub_channel_outlet" class="form-control"
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
                                <label class="form-label">Tambahan Discount Nilai</label>
                                <div>
                                    <input type="number" id="additional_disc" name="additional_disc"
                                        class="form-control" error="Tambahan Disc Nilai" min="1"
                                        value="{{ isset($data->additional_disc) ? $data->additional_disc : '0' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tipe Pengambilan</label>
                                <select id="kategori" name="kategori" class="form-control required" error="Disc Tipe">
                                    @foreach ($kategori as $item)
                                        <option value="{{ $item }}"
                                            {{ isset($data->kategori) ? ($data->kategori == $item ? 'selected' : '') : '' }}>
                                            {{ strtoupper($item) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Max Qty</label>
                                <div>
                                    <input type="number" id="max_qty" name="max_qty" class="form-control required"
                                        error="Max Qty" min="1"
                                        value="{{ isset($data->max_qty) ? $data->max_qty : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount Type</label>
                                <select id="disc_type" name="disc_type" class="form-control required"
                                    error="Disc Tipe">
                                    @foreach ($data_disc_tipe as $item)
                                        <option value="{{ $item }}"
                                            {{ isset($data->discount_type) ? ($data->discount_type == $item ? 'selected' : '') : '' }}>
                                            {{ strtoupper($item) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Max Mix</label>
                                <div>
                                    <input type="number" id="max_mix" name="max_mix"
                                        class="form-control required" error="Max" min="1"
                                        value="{{ isset($data->max_mix) ? $data->max_mix : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Channel Outlet</label>
                                <div>
                                    <select name="" id="channel_outlet" class="form-control"
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
                            <div class="mb-3">
                                <label>Kategori Disc</label>
                                <div>
                                    <select name="" id="kategori_disc" class="form-control required"
                                        error="Kategori Disc" onchange="Customer.changeCreditLimit(this)">
                                        <option value="">PILIH</option>
                                        @foreach ($list_kategori as $item)
                                            <option value="{{ $item }}"
                                                {{ isset($data->kategori_disc) ? ($data->kategori_disc == $item ? 'selected' : '') : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tambahan Discount Type</label>
                                <select id="additional_disc_type" name="additional_disc_type" class="form-control"
                                    error="Tambahan Discount Type">
                                    <option value=""></option>
                                    @foreach ($data_disc_tipe as $item)
                                        <option value="{{ $item }}"
                                            {{ isset($data->additional_disc_type) ? ($data->additional_disc_type == $item ? 'selected' : '') : '' }}>
                                            {{ strtoupper($item) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dibebankan untuk</label>
                                <select id="beban" name="beban" class="form-control required"
                                    error="Disc Tipe">
                                    @foreach ($list_beban as $item)
                                        <option value="{{ $item }}"
                                            {{ isset($data->beban) ? ($data->beban == $item ? 'selected' : '') : '' }}>
                                            {{ strtoupper($item) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <br />
                                <br />
                                <input type="checkbox" id="kelipatan"
                                    {{ isset($data->kelipatan) ? ($data->kelipatan == '1' ? 'checked' : '') : '' }}>
                                Kelipatan
                                <input type="checkbox" id="potong_grand_total"
                                    {{ isset($data->potong_grand_total) ? ($data->potong_grand_total == '1' ? 'checked' : '') : '' }}>
                                Potong Grand Total
                                <input type="checkbox" id="potong_per_qty"
                                    {{ isset($data->potong_per_qty) ? ($data->potong_per_qty == '1' ? 'checked' : '') : '' }}>
                                Potong Per Qty
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h5>Daftar Product Diskon</h5>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap" id="table-routing">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($promo_item as $v)
                                                <tr class="input" data_id="{{ $v->id }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                            <input id="product" name="product" type="text"
                                                                class="form-control required" error="Product"
                                                                placeholder="Pilih Data Product"
                                                                aria-label="Pilih Data Product"
                                                                aria-describedby="button-addon1"
                                                                value="{{ $v->product_uom . '//' . $v->product . '//' . $v->product_name . '//' . $v->unit_name }}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        <button type="button"
                                                            onclick="PromoItem.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @if (!isset($data->id))
                                            <tr class="input" data_id="">
                                                <td>&nbsp;</td>
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            id="button-addon1"
                                                            onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                        <input id="product" name="product" type="text"
                                                            class="form-control required" error="Product"
                                                            placeholder="Pilih Data Product"
                                                            aria-label="Pilih Data Product"
                                                            aria-describedby="button-addon1" value="">
                                                    </div>
                                                </td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="PromoItem.addItem(this, event)">Add
                                                    Item</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Daftar Free Product</h5>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap"
                                    id="table-routing-reminder">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($product_free as $v)
                                                <tr class="input" data_id="{{ $v->id }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                            <input id="product" name="product" type="text"
                                                                class="form-control" error="Product"
                                                                placeholder="Pilih Data Product"
                                                                aria-label="Pilih Data Product"
                                                                aria-describedby="button-addon1"
                                                                value="{{ $v->product_uom . '//' . $v->free_product . '//' . $v->product_name . '//' . $v->unit_name }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" id="qty" name="qty"
                                                            class="form-control" error="Qty" min="1"
                                                            value="{{ $v->free_qty }}">
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        <button type="button"
                                                            onclick="PromoItem.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            @if (empty($product_free))
                                                <tr class="input" data_id="">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                            <input id="product" name="product" type="text"
                                                                class="form-control" error="Product"
                                                                placeholder="Pilih Data Product"
                                                                aria-label="Pilih Data Product"
                                                                aria-describedby="button-addon1" value="">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" id="qty" name="qty"
                                                            class="form-control" error="Qty" min="1"
                                                            value="">
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                        @if (!isset($data->id))
                                            <tr class="input" data_id="">
                                                <td>&nbsp;</td>
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            id="button-addon1"
                                                            onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                        <input id="product" name="product" type="text"
                                                            class="form-control" error="Product"
                                                            placeholder="Pilih Data Product"
                                                            aria-label="Pilih Data Product"
                                                            aria-describedby="button-addon1" value="">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" id="qty" name="qty"
                                                        class="form-control" error="Qty" min="1"
                                                        value="">
                                                </td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="PromoItem.addReminderItem(this, event)">Add
                                                    Item</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <br />
                                <h5>Daftar Product Syarat</h5>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 table-sm table-nowrap"
                                        id="table-routing-syarat">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Nominal</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($data->id))
                                                @foreach ($product_syarat as $v)
                                                    <tr class="input" data_id="{{ $v->id }}">
                                                        <td>&nbsp;</td>
                                                        <td>
                                                            <div class="input-group">
                                                                <button class="btn btn-outline-primary" type="button"
                                                                    id="button-addon1"
                                                                    onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                                <input id="product" name="product" type="text"
                                                                    class="form-control" error="Product"
                                                                    placeholder="Pilih Data Product"
                                                                    aria-label="Pilih Data Product"
                                                                    aria-describedby="button-addon1"
                                                                    value="{{ $v->product_uom . '//' . $v->product . '//' . $v->product_name . '//' . $v->unit_name }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="number" id="qty" name="qty"
                                                                class="form-control" error="Qty" min="0"
                                                                value="{{ $v->qty }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" id="nominal" name="nominal"
                                                                class="form-control" error="Nominal" min="0"
                                                                value="{{ $v->nominal }}">
                                                        </td>
                                                        <td class="text-center" id="action">
                                                            <button type="button"
                                                                onclick="PromoItem.deleteItem(this, event)"
                                                                class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                    class="bx bx-trash-alt"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                @if (empty($product_syarat))
                                                    <tr class="input" data_id="">
                                                        <td>&nbsp;</td>
                                                        <td>
                                                            <div class="input-group">
                                                                <button class="btn btn-outline-primary" type="button"
                                                                    id="button-addon1"
                                                                    onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                                <input id="product" name="product" type="text"
                                                                    class="form-control" error="Product"
                                                                    placeholder="Pilih Data Product"
                                                                    aria-label="Pilih Data Product"
                                                                    aria-describedby="button-addon1" value="">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="number" id="qty" name="qty"
                                                                class="form-control" error="Qty" min="0"
                                                                value="">
                                                        </td>
                                                        <td>
                                                            <input type="number" id="nominal" name="nominal"
                                                                class="form-control" error="Nominal" min="0"
                                                                value="">
                                                        </td>
                                                        <td class="text-center" id="action">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                            @if (!isset($data->id))
                                                <tr class="input" data_id="">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                id="button-addon1"
                                                                onclick="PromoItem.showDataProduct(this)">Pilih</button>
                                                            <input id="product" name="product" type="text"
                                                                class="form-control" error="Product"
                                                                placeholder="Pilih Data Product"
                                                                aria-label="Pilih Data Product"
                                                                aria-describedby="button-addon1" value="">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" id="qty" name="qty"
                                                            class="form-control" error="Qty" min="0"
                                                            value="">
                                                    </td>
                                                    <td>
                                                        <input type="number" id="nominal" name="nominal"
                                                            class="form-control" error="Nominal" min="0"
                                                            value="">
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr class="" data_id="">
                                                <td colspan="4">
                                                    <a href="javascript:;" class="btn btn-primary btn-sm"
                                                        onclick="PromoItem.addSyaratItem(this, event)">Add
                                                        Item</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
        <div class="text-end">
            <div>
                <button type="submit" onclick="PromoItem.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="PromoItem.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
        <!-- end select2 -->

    </div>


</div>
<!-- end row -->
