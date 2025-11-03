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
                <form onsubmit="Tax.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Tax Code -->
                            <div class="mb-3">
                                <label class="form-label">Kode Pajak</label>
                                <input type="text" id="tax_code" class="form-control required"
                                    error="Kode Pajak" placeholder="Masukkan kode pajak (misal: PPN11)"
                                    value="{{ isset($data->tax_code) ? $data->tax_code : '' }}">
                            </div>

                            <!-- Tax Name -->
                            <div class="mb-3">
                                <label class="form-label">Nama Pajak</label>
                                <input type="text" id="tax_name" class="form-control required"
                                    error="Nama Pajak" placeholder="Masukkan nama pajak (misal: PPN Keluaran)"
                                    value="{{ isset($data->tax_name) ? $data->tax_name : '' }}">
                            </div>

                            <!-- Tax Type -->
                            <div class="mb-3">
                                <label class="form-label">Tipe Pajak</label>
                                <select class="form-control select2" id="tax_type">
                                    <option value=""></option>
                                    <option value="Input" {{ isset($data->tax_type) && $data->tax_type == 'Input' ? 'selected' : '' }}>Input (PPN Masukan)</option>
                                    <option value="Output" {{ isset($data->tax_type) && $data->tax_type == 'Output' ? 'selected' : '' }}>Output (PPN Keluaran)</option>
                                    <option value="Withholding" {{ isset($data->tax_type) && $data->tax_type == 'Withholding' ? 'selected' : '' }}>Withholding (PPh 23, dsb)</option>
                                </select>
                            </div>

                            <!-- Tax Rate -->
                            <div class="mb-3">
                                <label class="form-label">Persentase (%)</label>
                                <input type="number" step="0.01" id="rate" class="form-control required"
                                    error="Persentase Pajak" placeholder="Contoh: 11.00"
                                    value="{{ isset($data->rate) ? $data->rate : '' }}">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Related COA -->
                            <div class="mb-3">
                                <label class="form-label">Akun COA Terkait</label>
                                <select class="form-control select2" id="coa_id">
                                    <option value=""></option>
                                    @foreach ($coa_list as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->coa_id) && $data->coa_id == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['account_code'] }} - {{ $item['account_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih akun COA yang digunakan untuk mencatat pajak ini.</small>
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
                <button type="submit" onclick="Tax.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Tax.cancel(this, event)" class="btn btn-secondary waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
