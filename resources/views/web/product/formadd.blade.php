
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
                                <label>Model Number</label>
                                <div>
                                    <input type="text" id="model_number" name="model_number" class="form-control required"
                                        error="Model Number" placeholder="Model Number"
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
                        </div>
                    </div>

                    @if (isset($id))
                        @include('web.product.product-list-item-level')
                        @include('web.product.product-list-item-price')
                        @include('web.product.product-list-disc-strata')
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
