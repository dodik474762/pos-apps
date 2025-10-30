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

                <h4 class="card-title">{{ $title }}</h4>
                <hr>

                <form onsubmit="Karyawan.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Perusahaan</label>
                                <select {{ strtolower($akses) != 'superadmin' ? 'readonly' : '' }}
                                    class="form-control select2 required" error="Perusahaan" id="company">
                                    @foreach ($data_company as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->company) ? ($data->company == $item['id'] ? 'selected' : '') : '' }}>
                                            {{ $item['nama_company'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nama Karyawan</label>
                                <div>
                                    <input type="text" id="nama" class="form-control required" error="Nama"
                                        placeholder="Nama"
                                        value="{{ isset($data->nama_lengkap) ? $data->nama_lengkap : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Contact</label>
                                <div>
                                    <input type="text" id="contact" class="form-control required" error="Contact"
                                        placeholder="Contact"
                                        value="{{ isset($data->contact) ? $data->contact : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Bank Account</label>
                                <select class="form-control select2 required" error="Bank Account" id="bank_name">
                                    <option value=""></option>
                                    @foreach ($list_bank as $item)
                                        <option value="{{ $item['term_id'] }}"
                                            {{ isset($data->bank_name) ? ($data->bank_name == $item['term_id'] ? 'selected' : '') : '' }}>
                                            {{ $item['keterangan'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Group</label>
                                <div>
                                    <input disabled type="text" id="group" class="form-control" error="Group"
                                        placeholder="Group"
                                        value="{{ isset($data->group_name) ? $data->group_name : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label>NIK Karyawan</label>
                                <div>
                                    <input type="text" id="nik" class="form-control required" error="Nik"
                                        placeholder="Nik" value="{{ isset($data->nik) ? $data->nik : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Jabatan</label>
                                <div>
                                    <input type="text" id="jabatan" class="form-control required" error="Jabatan"
                                        placeholder="Jabatan"
                                        value="{{ isset($data->jabatan) ? $data->jabatan : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <div>
                                    <input type="text" id="email" class="form-control required" error="Email"
                                        placeholder="Email" value="{{ isset($data->email) ? $data->email : '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Bank Number</label>
                                <div>
                                    <input type="number" min="0" id="bank_number" class="form-control required"
                                        error="Bank Number" placeholder="Bank Number"
                                        value="{{ isset($data->bank_number) ? $data->bank_number : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <div class="row">
                        <div class="col-md-12">
                            <label>Karyawan Group </label>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-sm table-nowrap" id="table-group">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Group</th>
                                            <th class="text-center">Default</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($data->id))
                                            @foreach ($karyawan_group as $item)
                                                <tr class="input" data_id="{{ $item['id'] }}">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <select class="form-control" error="Group" id="group-item">
                                                            @foreach ($groups as $items)
                                                                <option value="{{ $items['term_id'] }}"
                                                                    {{ isset($item['group']) ? ($item['group'] == $items['term_id'] ? 'selected' : '') : '' }}>
                                                                    {{ $items['keterangan'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" onchange="Karyawan.changeDefault(this, event)" name="group-default" id="group-default"
                                                            {{ $item['default'] == 1 ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        <button type="button" onclick="Karyawan.deleteItem(this, event)"
                                                            class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i
                                                                class="bx bx-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            @if (empty($karyawan_group))
                                                <tr class="input" data_id="">
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <select class="form-control" error="Group" id="group-item">
                                                            @foreach ($groups as $items)
                                                                <option value="{{ $items['term_id'] }}">
                                                                    {{ $items['keterangan'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" onchange="Karyawan.changeDefault(this, event)" name="group-default"  id="group-default">
                                                    </td>
                                                    <td class="text-center" id="action">
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                        @if (!isset($data->id))
                                            <tr class="input" data_id="">
                                                <td>&nbsp;</td>
                                                <td>
                                                    <select class="form-control" error="Group" id="group-item">
                                                        @foreach ($groups as $items)
                                                            <option value="{{ $items['term_id'] }}">
                                                                {{ $items['keterangan'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="radio" onchange="Karyawan.changeDefault(this, event)" name="group-default"  id="group-default">
                                                </td>
                                                <td class="text-center" id="action">
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="" data_id="">
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-primary btn-sm"
                                                    onclick="Karyawan.addItem(this, event)">Add
                                                    Item</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <!-- end select2 -->

        <div class="text-end">
            <div>
                <button type="submit" onclick="Karyawan.submit(this, event)"
                    class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="Karyawan.cancel(this, event)" class="btn waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>


</div>
<!-- end row -->
