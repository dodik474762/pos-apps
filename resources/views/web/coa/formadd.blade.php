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
                <form onsubmit="Coa.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Account Code -->
                            <div class="mb-3">
                                <label class="form-label">Kode Akun</label>
                                <input type="text" id="account_code" class="form-control required"
                                    error="Kode Akun" placeholder="Masukkan kode akun (misal: 1110)"
                                    value="{{ isset($data->account_code) ? $data->account_code : '' }}">
                            </div>

                            <!-- Account Name -->
                            <div class="mb-3">
                                <label class="form-label">Nama Akun</label>
                                <input type="text" id="account_name" class="form-control required"
                                    error="Nama Akun" placeholder="Masukkan nama akun (misal: Kas Kecil)"
                                    value="{{ isset($data->account_name) ? $data->account_name : '' }}">
                            </div>

                            <!-- Parent Account -->
                            <div class="mb-3">
                                <label class="form-label">Akun Induk (Parent)</label>
                                <select class="form-control select2" id="parent_code">
                                    <option value=""></option>
                                    @foreach ($coa_parent as $item)
                                        <option value="{{ $item['account_code'] }}"
                                            {{ isset($data->parent_code) && $data->parent_code == $item['account_code'] ? 'selected' : '' }}>
                                            {{ $item['account_code'] }} - {{ $item['account_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Account Type -->
                            <div class="mb-3">
                                <label class="form-label">Tipe Akun</label>
                                <select class="form-control select2" id="account_type">
                                    <option value="Header" {{ isset($data->account_type) && $data->account_type == 'Header' ? 'selected' : '' }}>Header</option>
                                    <option value="Detail" {{ isset($data->account_type) && $data->account_type == 'Detail' ? 'selected' : '' }}>Detail</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Category -->
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-control select2" id="category">
                                    <option value=""></option>
                                    <option value="Asset" {{ isset($data->category) && $data->category == 'Asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="Liability" {{ isset($data->category) && $data->category == 'Liability' ? 'selected' : '' }}>Liability</option>
                                    <option value="Equity" {{ isset($data->category) && $data->category == 'Equity' ? 'selected' : '' }}>Equity</option>
                                    <option value="Revenue" {{ isset($data->category) && $data->category == 'Revenue' ? 'selected' : '' }}>Revenue</option>
                                    <option value="COGS" {{ isset($data->category) && $data->category == 'COGS' ? 'selected' : '' }}>COGS</option>
                                    <option value="Expense" {{ isset($data->category) && $data->category == 'Expense' ? 'selected' : '' }}>Expense</option>
                                    <option value="Other" {{ isset($data->category) && $data->category == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Normal Balance -->
                            <div class="mb-3">
                                <label class="form-label">Saldo Normal</label>
                                <select class="form-control select2" id="normal_balance">
                                    <option value="Debit" {{ isset($data->normal_balance) && $data->normal_balance == 'Debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="Credit" {{ isset($data->normal_balance) && $data->normal_balance == 'Credit' ? 'selected' : '' }}>Credit</option>
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
                <button type="submit" onclick="Coa.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Coa.cancel(this, event)" class="btn btn-secondary waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
