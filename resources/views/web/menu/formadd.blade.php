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
                <form onsubmit="Menu.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Parent Menu</label>
                                <select class="form-control select2" id="parent_menu">
                                    <option value="">Daftar Menu</option>
                                    @foreach ($data_menu as $item)
                                        <option value="{{ $item['id'] }}" {{ isset($data->parent) ? $data->parent == $item['id'] ? 'selected' : ''  : ''}}>{{ $item['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Url Link</label>
                                <div>
                                    <input type="text" id="url" class="form-control required" error="Url Link"
                                        placeholder="Url Link" value="{{ isset($data->url) ? $data->url : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>Nama Menu</label>
                                <div>
                                    <input type="text" id="nama" class="form-control required" error="Nama Menu"
                                        placeholder="Nama Menu" value="{{ isset($data->nama) ? $data->nama : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <br>
                                <label>Routing</label>
                                <input style="margin-top:3px;" type="checkbox" name="routing" id="routing" {{ isset($data->routing) ? $data->routing == 1 ? 'checked' : ''  : '' }}>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end">
            <div>
                <button type="submit" onclick="Menu.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Menu.cancel(this, event)"
                    class="btn btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>

    </div>


</div>
<!-- end row -->
