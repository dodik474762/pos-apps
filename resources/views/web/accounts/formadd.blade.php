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
                <form onsubmit="Accounts.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Account Code -->
                            <div class="mb-3">
                                <label class="form-label">Kode</label>
                                <input type="text" id="code" class="form-control required"
                                    error="Kode" placeholder="Masukkan kode akun (misal: 1101)"
                                    value="{{ isset($data->code) ? $data->code : '' }}">
                            </div>

                            <!-- Account Name -->
                            <div class="mb-3">
                                <label class="form-label">Nama Akun</label>
                                <input type="text" id="name" class="form-control required"
                                    error="Nama Akun" placeholder="Masukkan nama akun (misal: CASH)"
                                    value="{{ isset($data->name) ? $data->name : '' }}">
                            </div>

                            <!-- Account Type -->
                            <div class="mb-3">
                                <label class="form-label">Tipe Akun</label>
                                <select class="form-control select2 required" id="account_type_id"
                                    error="Tipe Akun">
                                    <option value=""></option>
                                    @foreach ($account_type_list as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->account_type_id) && $data->account_type_id == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['code'] }} - {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Parent Account -->
                            <div class="mb-3">
                                <label class="form-label">Akun Induk (Parent)</label>
                                <select class="form-control select2" id="parent_id">
                                    <option value=""></option>
                                    @foreach ($parent_list as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->parent_id) && $data->parent_id == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['code'] }} - {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Kosongkan jika akun ini adalah akun induk (level 1).</small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Normal Balance -->
                            <div class="mb-3">
                                <label class="form-label">Saldo Normal</label>
                                <select class="form-control select2" id="normal_balance">
                                    <option value="Debit" {{ isset($data->normal_balance) && $data->normal_balance == 'Debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="Credit" {{ isset($data->normal_balance) && $data->normal_balance == 'Credit' ? 'selected' : '' }}>Credit</option>
                                </select>
                            </div>

                            <!-- Header / Detail -->
                            <div class="mb-3">
                                <label class="form-label">Jenis</label>
                                <select class="form-control select2" id="is_header">
                                    <option value="1" {{ isset($data->is_header) && $data->is_header == 1 ? 'selected' : '' }}>Header</option>
                                    <option value="0" {{ isset($data->is_header) && $data->is_header == 0 ? 'selected' : '' }}>Detail</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea id="description" class="form-control" rows="3"
                                    placeholder="Keterangan tambahan">{{ isset($data->description) ? $data->description : '' }}</textarea>
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control select2" id="is_active">
                                    <option value="1" {{ isset($data->is_active) && $data->is_active == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ isset($data->is_active) && $data->is_active == 0 ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-end">
            <div>
                <button type="submit" onclick="Accounts.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Accounts.cancel(this, event)" class="btn btn-secondary waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
