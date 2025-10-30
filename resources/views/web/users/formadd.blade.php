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
                <form onsubmit="Users.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Pilih Karyawan</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" type="button" id="button-addon1"
                                        onclick="Users.showDataKaryawan(this)">Pilih</button>
                                    <input readonly id="nik" type="text" class="form-control required" error="Karyawan"
                                        placeholder="Pilih Data Karyawan" aria-label="Pilih Data Karyawan"
                                        aria-describedby="button-addon1" value="{{ isset($data->nik) ? $data->nik.'//'.$data->username : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Username</label>
                                <div>
                                    <input type="text" id="username" class="form-control required" error="Username"
                                        placeholder="Username" value="{{ isset($data->username) ? $data->username : '' }}">
                                </div>
                            </div>
                            {{-- <div class="mb-3">
                                <label>Nama User</label>
                                <div>
                                    <input type="text" id="nama_user" class="form-control required" error="Nama User"
                                        placeholder="Nama User" value="{{ isset($data->name) ? $data->name : '' }}">
                                </div>
                            </div> --}}
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Roles</label>
                                <select class="form-control select2 required" error="Roles" id="roles">
                                    @foreach ($data_roles as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->user_group) ? ($data->user_group == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['group'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                @if (isset($id))
                                    <label>Reset Password</label>
                                @else
                                    <label>Password</label>
                                @endif
                                <div>
                                    <input type="text" id="password" class="form-control required" error="Password"
                                        placeholder="Password" value="">
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
                <button type="submit" onclick="Users.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Users.cancel(this, event)"
                    class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
