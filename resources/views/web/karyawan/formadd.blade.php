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

                <h4 class="card-title">{{ $title }}</h4>
                <hr>

                <form onsubmit="Karyawan.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Perusahaan</label>
                                <select {{ strtolower($akses) != 'superadmin' ? 'readonly' : '' }}
                                    class="form-control select2 required" error="Perusahaan" id="company">
                                    @foreach ($data_company as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->company) ? ($data->company == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['nama_company'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nama Karyawan</label>
                                <div>
                                    <input type="text" id="nama" class="form-control required" error="Nama"
                                        placeholder="Nama"
                                        value="{{ isset($data->nama_lengkap) ? $data->nama_lengkap : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Contact</label>
                                <div>
                                    <input type="text" id="contact" class="form-control required" error="Contact"
                                        placeholder="Contact"
                                        value="{{ isset($data->contact) ? $data->contact : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Bank Account</label>
                                <select class="form-control select2" error="Bank Account" id="bank_name">
                                    <option value=""></option>
                                    @foreach ($list_bank as $item)
                                        <option value="{{ $item['term_id'] }}"
                                            {{ isset($data->bank_name) ? ($data->bank_name == $item['term_id'] ? 'selected' : '') : '' }}>
                                            {{ $item['keterangan'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Group</label>
                                <div>
                                    <input disabled type="text" id="group" class="form-control" error="Group"
                                        placeholder="Group"
                                        value="{{ isset($data->group_name) ? $data->group_name : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Latitude</label>
                                <div>
                                    <input tabindex="11" type="text" id="latitude" class="form-control" error="Latitude"
                                        placeholder="Latitude Ex : -6.200000" value="{{ isset($data->latitude) ? $data->latitude : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>NIK Karyawan</label>
                                <div>
                                    <input type="text" id="nik" class="form-control required" error="Nik"
                                        placeholder="Nik" value="{{ isset($data->nik) ? $data->nik : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Jabatan</label>
                                <div>
                                    <input type="text" id="jabatan" class="form-control required" error="Jabatan"
                                        placeholder="Jabatan"
                                        value="{{ isset($data->jabatan) ? $data->jabatan : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <div>
                                    <input type="text" id="email" class="form-control required" error="Email"
                                        placeholder="Email" value="{{ isset($data->email) ? $data->email : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Bank Number</label>
                                <div>
                                    <input type="number" min="0" id="bank_number" class="form-control required"
                                        error="Bank Number" placeholder="Bank Number"
                                        value="{{ isset($data->bank_number) ? $data->bank_number : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Max Retur</label>
                                <div>
                                    <input type="number" min="0" id="max_retur" class="form-control required"
                                        error="Max Retur" placeholder="Max Retur"
                                        value="{{ isset($data->max_retur) ? $data->max_retur : '9999999999999' }}">
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
                    </div>

                    <hr/>

                    <!-- Karyawan Group Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <label>Karyawan Group </label>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap" id="table-group">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Group</th>
                                            <th class="text-center">Default</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($karyawan_group as $item)
                                                <tr class="input" data_id="{{ $item['id'] }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <select class="form-control" error="Group" id="group-item">
                                                            @foreach ($groups as $items)
                                                                <option value="{{ $items['term_id'] }}"
                                                                    {{ isset($item['group']) ? ($item['group'] == $items['term_id'] ? 'selected' : '') : '' }}>
                                                                    {{ $items['keterangan'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" onchange="Karyawan.changeDefault(this, event)" name="group-default" id="group-default"
                                                            {{ $item['default'] == 1 ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        <button type="button" onclick="Karyawan.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            @if (empty($karyawan_group))
                                                <tr class="input" data_id="">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <select class="form-control" error="Group" id="group-item">
                                                            @foreach ($groups as $items)
                                                                <option value="{{ $items['term_id'] }}">
                                                                    {{ $items['keterangan'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" onchange="Karyawan.changeDefault(this, event)" name="group-default"  id="group-default">
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
                                                    <select class="form-control" error="Group" id="group-item">
                                                        @foreach ($groups as $items)
                                                            <option value="{{ $items['term_id'] }}">
                                                                {{ $items['keterangan'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="radio" onchange="Karyawan.changeDefault(this, event)" name="group-default"  id="group-default">
                                                </td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="Karyawan.addItem(this, event)">Add
                                                    Item</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <!-- List Product Table -->
                    @if(isset($id))
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="mb-0">List Product</label>
                                    <button type="button" class="btn btn-primary btn-sm waves-effect waves-light"
                                        onclick="Karyawan.openProductModal(this, event)">
                                        <i class="bx bx-plus me-1"></i> Add Product
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 table-sm table-nowrap" id="table-product">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Kode Product</th>
                                                <th>Nama Product</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($data->id) && isset($karyawan_product) && !empty($karyawan_product))
                                                @foreach ($karyawan_product as $item)
                                                    <tr class="input" data_id="{{ $item['id'] }}" data-product-id="{{ $item['product_id'] ?? '' }}">
                                                        <td>&nbsp;</td>
                                                        <td>{{ $item['kode_product'] }}</td>
                                                        <td>{{ $item['nama_product'] }}</td>
                                                        <td class="text-center">
                                                            <button type="button" onclick="Karyawan.deleteProduct(this, event)"
                                                                class="btn btn-danger btn-sm waves-effect waves-light">
                                                                <i class="bx bx-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr id="product-empty-row">
                                                    <td colspan="4" class="text-center text-muted py-3">
                                                        <i class="bx bx-package font-size-18 d-block mb-1"></i>
                                                        Belum ada product
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                </form>

            </div>
        </div>

        <div class="text-end">
            <div>
                <button type="submit" onclick="Karyawan.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Karyawan.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->


<!-- ============================================================ -->
<!-- Modal Add / Edit Product                                      -->
<!-- ============================================================ -->
<div class="modal fade" id="modalProduct" tabindex="-1" aria-labelledby="modalProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalProductLabel">
                    <i class="bx bx-package me-1"></i> Add Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Menyimpan referensi baris saat mode edit -->
                <input type="hidden" id="modal-product-row-index" value="">

                <div class="mb-3">
                    <label class="form-label">Nama Product <span class="text-danger">*</span></label>
                    <select class="form-control select2" id="modal-product-item">
                        <option value="">-- Pilih Product --</option>
                        @isset($products)
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-nama="{{ $product->name }}" data-harga-satuan-besar="{{ $product->harga_satuan_besar }}">
                                    {{ $product->code }} - {{ $product->name }} - {{ $product->sku_name }} - {{ $product->nama_vendor }} (Rp {{ number_format($product->harga_satuan_besar ?? 0, 0, ',', '.') }})
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    <div class="invalid-feedback">Nama product wajib dipilih.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Satuan Terbesar</label>
                    <input type="text" class="form-control" id="modal-product-harga-satuan-besar" placeholder="Rp 0">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light waves-effect" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary waves-effect waves-light"
                    id="btn-save-product" onclick="Karyawan.saveProduct(this, event)">
                    <i class="bx bx-save me-1"></i> Simpan
                </button>
            </div>

        </div>
    </div>
</div>
<!-- end Modal Add Product -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $('#modal-product-item').on('change', function() {
                var selected = $(this).find('option:selected');
                var harga = selected.attr('data-harga-satuan-besar');
                if (harga !== undefined && harga !== null && harga !== '') {
                    var formatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(harga);
                    $('#modal-product-harga-satuan-besar').val(formatted);
                } else {
                    $('#modal-product-harga-satuan-besar').val('');
                }
            });

            $('#modal-product-harga-satuan-besar').on('input', function() {
                var val = $(this).val().replace(/[^0-9]/g, '');
                if (val !== '') {
                    var formatted = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(val);
                    $(this).val(formatted);
                } else {
                    $(this).val('');
                }
            });
        }
    });
</script>