
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
                {{-- <form onsubmit="Product.submit(this, event)" enctype="multipart/form-data"> --}}
                <form action="{{ url('/api/master/product/submit') }}" method="POST" enctype="multipart/form-data" id="form-product">
                     @csrf
                    <input type="hidden" id="id" name="id" value="{{ isset($id) ? $id : '' }}">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-control select2 required" error='Type' id="product_type" name="product_type">
                                    @foreach ($product_type as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->product_type) ? ($data->product_type == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['type'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Product Name</label>
                                <div>
                                    <input type="text" id="name" name="name" class="form-control required"
                                        error="Product Name" placeholder="Product Name"
                                        value="{{ isset($data->name) ? $data->name : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Kode Princpal</label>
                                <div>
                                    <input type="text" id="model_number" name="model_number" class="form-control required"
                                        error="Kode Princpal" placeholder="Kode Princpal"
                                        value="{{ isset($data->model_number) ? $data->model_number : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Remarks</label>
                                <div>
                                    <input type="text" id="remarks" name="remarks" class="form-control required" error="Remarks"
                                        placeholder="Remarks"
                                        value="{{ isset($data->remarks) ? $data->remarks : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Tax Jual</label>
                                <div>
                                    <select class="form-control select2 required" error='Tax Jual' id="tax_id" name="tax_id">
                                        <option value="">-- Pilih Tax Jual --</option>
                                        @foreach ($taxs as $item)
                                            <option value="{{ $item['id'] }}"
                                                {{ isset($data->tax_sale) ? ($data->tax_sale == $item['id'] ? 'selected' : '') : '' }}>
                                                {{ $item['tax_name'] }} - {{ $item['rate'] }}%</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Principal</label>
                                <div>
                                    <select class="form-control select2 required" error='Principal' id="principal" name="principal">
                                        <option value="">-- Principal --</option>
                                        @foreach ($vendors as $item)
                                            <option value="{{ $item->id }}"
                                                {{ isset($data->principal) ? ($data->principal == $item->id ? 'selected' : '') : '' }}>
                                                {{ $item->nama_vendor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                             <div class="mb-3">
                                <label>Vendor</label>
                                <div>
                                    <select class="form-control select2 required" error='Principal' id="vendor" name="vendor">
                                        <option value="">-- Vendor --</option>
                                        @foreach ($vendors as $item)
                                            <option value="{{ $item->id }}"
                                                {{ isset($data->vendor) ? ($data->vendor == $item->id ? 'selected' : '') : '' }}>
                                                {{ $item->nama_vendor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Tax Tipe</label>
                                <div>
                                    <select class="form-control select2 required" error='Tax Tipe' id="type_tax" name="type_tax">
                                        <option value="">-- Pilih Tax Tipe --</option>
                                        @foreach ($tax_type as $item)
                                            <option value="{{ $item }}"
                                                {{ isset($data->type_tax) ? ($data->type_tax == $item ? 'selected' : '') : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Retur Type</label>
                                <div>
                                    <select class="form-control select2 required" error='Retur Type' id="type_retur" name="type_retur">
                                        @foreach ($retur_type as $item)
                                            <option value="{{ $item }}"
                                                {{ isset($data->type_retur) ? ($data->type_retur == $item ? 'selected' : '') : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="">File Produk</label>
                                <div class="input-group">
                                    {{-- <button class="btn btn-outline-secondary" type="button" id="button-addon1"
                                        onclick="Product.addFile(this)">Choose
                                        File</button> --}}
                                    @if (isset($data->id))
                                        @if ($data->files != '')
                                            <a class="btn btn-outline-secondary" target="_blank" id="btn-lihat"
                                                href="{{ url('/') . $data->path_files . '/' . $data->files }}">Lihat
                                                File {{ $data->files }}</a>
                                        @endif
                                    @endif
                                    <input id="file" name="file" type="file" readonly class="form-control"
                                        placeholder="Pilih Data File" aria-label="Pilih Data File" src=""
                                        error="Data File" aria-describedby="button-addon1"
                                        value="{{ isset($data->id) ? $data->files : '' }}">
                                    <input type="hidden" name="files" id="files"
                                        value="{{ isset($data->id) ? $data->id : '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Nama SKU</label>
                                <div>
                                    <input type="text" id="sku_name" name="sku_name" class="form-control required" error="Nama SKU"
                                        placeholder="Nama SKU"
                                        value="{{ isset($data->sku_name) ? $data->sku_name : '' }}">
                                </div>
                            </div>

                             <div class="mb-3">
                                <label>Kategori SKU</label>
                                <div>
                                    <input type="text" id="category" name="category" class="form-control required" error="Kategori SKU"
                                        placeholder="Kategori SKU"
                                        value="{{ isset($data->category) ? $data->category : '' }}">
                                </div>
                            </div>

                             <div class="mb-3">
                                <label>Sub Brand</label>
                                <div>
                                    <input type="text" id="sub_brand" name="sub_brand" class="form-control" error="Sub Brand"
                                        placeholder="Sub Brand"
                                        value="{{ isset($data->sub_brand) ? $data->sub_brand : '' }}">
                                </div>
                            </div>

                             @if(count($product_uoms) > 0)
                                <div class="mb-3">
                                    <label>Harga Jual Retail Satuan Besar</label>
                                    <div>
                                        <input type="text" id="harga_satuan_besar" name="harga_satuan_besar" class="form-control required" error="Harga"
                                            placeholder="Harga"
                                            value="{{  count($product_prices) > 0 ? $prices_retails[count($prices_retails)-1]->price : ''  }}">
                                            <div style="margin-top: 5px;">
                                                <button class="btn btn-primary" onclick="Product.updateHargaRetail(this, event)">Update Harga Retail</button>
                                            </div>
                                    </div>
                                </div>
                             @endif

                        </div>

                        @if (isset($id))
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="">Stok Produk</label>
                                    <div class="table-responsive">
                                        <table class="table table-nowrap align-middle table-sm" id="table-stock">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 10%;">Satuan Kecil</th>
                                                    <th style="width: 10%;">Stok</th>
                                                    <th style="width: 10%;">Satuan Besar</th>
                                                    <th style="width: 10%;">Stok</th>
                                                    <th style="width: 30%;">Gudang</th>
                                                    <th style="width: 30%;">Last Update</th>
                                                </tr>
                                            </thead>
                                            @foreach ($product_stocks as $item)
                                                <tr>
                                                    <td>{{ $item->unit_name }}</td>
                                                    <td>{{ $item->qty }}</td>
                                                    <td>{{ $item->unit_large }}</td>
                                                    <td>{{ $item->qty_large }}</td>
                                                    <td>{{ $item->warehouse_name }}</td>
                                                    <td>{{ $item->updated_at }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    @if (strtolower($akses) == 'superadmin')
                                        <label for="">Harga Beli Produk</label>
                                        <div class="table-responsive">
                                            <table class="table table-nowrap align-middle table-sm" id="table-stock">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 20%;">Satuan</th>
                                                        <th style="width: 20%;">Harga</th>
                                                        <th style="width: 30%;">Vendor</th>
                                                        <th style="width: 15%;">Tanggal Mulai Berlaku</th>
                                                        <th style="width: 15%;">Status</th>
                                                    </tr>
                                                </thead>
                                                @foreach ($product_costs as $item)
                                                    <tr>
                                                        <td>{{ $item->unit_name }}</td>
                                                        <td>{{ number_format($item->cost, 2, ',', '.') }}</td>
                                                        <td>{{ $item->nama_vendor }}</td>
                                                        <td>{{ $item->date_start }}</td>
                                                        <td>{{ $item->is_active == 1 ? 'Aktif' : 'Tidak Aktif' }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    @if (isset($id))
                        @include('web.product.product-list-item-level')
                        @include('web.product.product-list-item-price')
                        @include('web.product.product-list-disc-strata')
                        @include('web.product.product-list-disc-free')
                    @endif

                    <div class="text-end">
                        <div>
                            {{-- <button type="submit" onclick="Product.submit(this, event)"
                                class="btn btn-success waves-effect waves-light me-1">
                                Submit
                            </button> --}}
                            <button type="submit"
                                onclick="Product.submit(this, event)"
                                class="btn btn-success waves-effect waves-light me-1">
                                Submit
                            </button>
                            <button type="reset" onclick="Product.cancel(this, event)" class="btn btn waves-effect">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->


    </div>


</div>
<!-- end row -->
