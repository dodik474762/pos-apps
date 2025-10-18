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
        @if (isset($error))
            <div class="alert alert-danger" role="alert">
                {{ $error }}
            </div>
        @endif

        @if (isset($success))
            <div class="alert alert-success" role="alert">
                {{ $success }}
            </div>
        @endif
        <div class="card">
            <div class="card-body">

                {{-- <form onsubmit="Company.submit(this, event)"> --}}
                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Nama Perusahaan</label>
                            <div>
                                <input type="text" id="nama" class="form-control required" error="Nama"
                                    placeholder="Nama"
                                    value="{{ isset($data->nama_company) ? $data->nama_company : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Email</label>
                            <div>
                                <input type="email" id="email" class="form-control required" error="Email"
                                    placeholder="Email" value="{{ isset($data->email) ? $data->email : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Alamat</label>
                            <div>
                                <textarea name="" id="alamat" class="form-control texteditor" error="Alamat">{{ isset($data->alamat) ? $data->alamat : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Alamat Pengiriman</label>
                            <div>
                                <textarea name="" id="alamat_pengiriman" class="form-control texteditor" error="Alamat Pengiriman">{{ isset($data->alamat_pengiriman) ? $data->alamat_pengiriman : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Akun Bank</label>
                            <div>
                                <select name="akun_bank" class="form-control" id="akun_bank">
                                    <option value="MANDIRI"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'MANDIRI' ? 'selected' : '') : '' }}>
                                        MANDIRI</option>
                                    <option value="BRI"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'BRI' ? 'selected' : '') : '' }}>
                                        BRI</option>
                                    <option value="BNI"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'BNI' ? 'selected' : '') : '' }}>
                                        BNI</option>
                                    <option value="BCA"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'BCA' ? 'selected' : '') : '' }}>
                                        BCA</option>
                                    <option value="CIMB NIAGA"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'CIMB NIAGA' ? 'selected' : '') : '' }}>
                                        CIMB NIAGA</option>
                                    <option value="BSI"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'BSI' ? 'selected' : '') : '' }}>
                                        BSI</option>
                                    <option value="BCA SYARIAH"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'BCA SYARIAH' ? 'selected' : '') : '' }}>
                                        BCA SYARIAH</option>
                                    <option value="DBS BANK"
                                        {{ isset($data->akun_bank) ? ($data->akun_bank == 'DBS BANK' ? 'selected' : '') : '' }}>
                                        DBS BANK</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Nama Akun Bank</label>
                            <div>
                                <input type="text" id="akun_bank_name" class="form-control required"
                                    error="Nama Akun Bank" placeholder="Nama Akun Bank"
                                    value="{{ isset($data->akun_bank_name) ? $data->akun_bank_name : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>No. Rekening</label>
                            <div>
                                <input type="text" id="akun_bank_number" class="form-control required"
                                    error="No. Rekening" placeholder="No. Rekening"
                                    value="{{ isset($data->akun_bank_number) ? $data->akun_bank_number : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>No. HP / No. WA</label>
                            <div>
                                <input type="number" id="no_hp" class="form-control required" error="No. HP"
                                    placeholder="No. HP" value="{{ isset($data->no_hp) ? $data->no_hp : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>Cabang Bank</label>
                            <div>
                                <input type="text" id="branch_bank" class="form-control required"
                                    error="Nama Akun Bank" placeholder="Cabang Bank"
                                    value="{{ isset($data->branch_bank) ? $data->branch_bank : '' }}">
                            </div>
                        </div>
                    </div>


                    @if (isset($id))
                        <form method="POST" action="{{ route('company-upload-logo') }}" enctype="multipart/form-data">
                            <input type="hidden" id="id_company" name="id_company" value="{{ $id }}">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label>Upload Logo</label>
                                    <div>                                        
                                        <input type="file" onchange="Company.uploadLogo(this, event)"
                                            id="logo-files" name="logo-files" class="form-control" value="">
                                    </div>
                                    <div>
                                        @if ($data->files != '')
                                            <img src="{{ url('/') . $data->path_files . '/' . $data->files }}" alt="logo"
                                                style="width: 100px; height: 120px;">
                                            <br />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
                {{-- </form> --}}

            </div>
        </div>
        <!-- end select2 -->

        <div class="text-end">
            <div>
                <button type="submit" onclick="Company.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Company.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>

    </div>


</div>
<!-- end row -->
