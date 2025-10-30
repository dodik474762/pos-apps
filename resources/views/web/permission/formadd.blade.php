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

                <form onsubmit="Permission.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Roles</label>
                                <select onchange="Permission.showMenu(this)" class="form-control select2 required"
                                    error="Roles" id="roles">
                                    <option value=""></option>
                                    @foreach ($data_roles as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->users_group) ? ($data->users_group == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['group'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            {{-- <div class="mb-3">
                                <label class="form-label">Daftar Menu</label>
                                <select class="form-control select2" id="menu">
                                    <option value="">Daftar Roles</option>
                                    @foreach ($data_roles as $item)
                                        <option value="{{ $item['id'] }}" {{ isset($data->users_group) ? $data->users_group == $item['id'] ? 'selected' : ''  : ''}}>{{ $item['group'] }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            {!! $list_menu_view !!}
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->


    </div>
    <div class="text-end" style="margin-bottom: 10px;">
        <div>
            <button type="submit" onclick="Permission.submit(this, event)"
                class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>
            <button type="reset" onclick="Permission.cancel(this, event)" class="btn waves-effect">
                Cancel
            </button>

        </div>
    </div>
</div>
<!-- end row -->
