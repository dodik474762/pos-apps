
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
                <form action="{{ url('/api/master/product/submit_import') }}" method="POST" enctype="multipart/form-data" id="form-product">
                     @csrf
                    <input type="hidden" id="id" name="id" value="{{ isset($id) ? $id : '' }}">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="">File Excel Import</label>
                                <div class="input-group">
                                    <input id="file" name="file" type="file" readonly class="form-control"
                                        placeholder="Pilih Data File" aria-label="Pilih Data File" src=""
                                        error="Data File" aria-describedby="button-addon1"
                                        value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div>
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
