<input type="hidden" id="id" value="{{ isset($data->id) ? $data->id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Form {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Form {{ $title }}</li>
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

                <form onsubmit="CustomerLimitTop.submit(this, event)">
                    <input type="hidden" id="current_credit_limit" value="{{ isset($data->current_credit_limit) ? $data->current_credit_limit : '' }}">
                    <input type="hidden" id="current_payment_terms" value="{{ isset($data->current_payment_terms) ? $data->current_payment_terms : '' }}">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <select class="form-control select2 required" error="Customer" id="customer"
                                    {{ isset($view_detail) ? 'disabled' : '' }}
                                    onchange="CustomerLimitTop.getDetailCustomer(this)">
                                    <option value=""></option>
                                    @foreach ($customers as $item)
                                        @php
                                            $isEdit = isset($data->customer) && $data->customer == $item->id;
                                        @endphp
                                        <option value="{{ $item->id }}" {{ $isEdit ? 'selected' : '' }}>
                                            {{ $item->code }} - {{ $item->nama_customer }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Credit Limit Saat Ini</label>
                                <div>
                                    <input type="number" id="info_credit_limit" class="form-control"
                                        value="{{ isset($data->current_credit_limit) ? $data->current_credit_limit : '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Term Of Payment Saat Ini</label>
                                <div>
                                    <input type="text" id="info_top_name" class="form-control"
                                        value="{{ isset($data->current_top_name) ? $data->current_top_name : '' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Alasan Pengajuan</label>
                                <div>
                                    <textarea id="reason" class="form-control required" error="Alasan Pengajuan"
                                        placeholder="Alasan Pengajuan"
                                        {{ isset($view_detail) ? 'readonly' : '' }}>{{ isset($data->reason) ? $data->reason : '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tipe Pengajuan</label>
                                <select class="form-control select2 required" error="Tipe Pengajuan"
                                    id="type_pengajuan" {{ isset($view_detail) ? 'disabled' : '' }}
                                    onchange="CustomerLimitTop.changeTypePengajuan(this)">
                                    <option value=""></option>
                                    <option value="CREDIT_LIMIT" {{ isset($data->type_pengajuan) ? ($data->type_pengajuan == 'CREDIT_LIMIT' ? 'selected' : '') : '' }}>
                                        Pengajuan Credit Limit</option>
                                    <option value="TERM_OF_PAYMENT" {{ isset($data->type_pengajuan) ? ($data->type_pengajuan == 'TERM_OF_PAYMENT' ? 'selected' : '') : '' }}>
                                        Pengajuan Term Of Payment</option>
                                    <option value="CREDIT_LIMIT_DAN_TOP" {{ isset($data->type_pengajuan) ? ($data->type_pengajuan == 'CREDIT_LIMIT_DAN_TOP' ? 'selected' : '') : '' }}>
                                        Pengajuan Credit Limit & Term Of Payment</option>
                                </select>
                            </div>
                            <div class="mb-3 field-credit-limit">
                                <label>Pengajuan Credit Limit</label>
                                <div>
                                    <input tabindex="10" type="number" id="new_credit_limit"
                                        class="form-control required" error="Pengajuan Credit Limit"
                                        placeholder="Pengajuan Credit Limit"
                                        value="{{ isset($data->new_credit_limit) ? $data->new_credit_limit : '' }}"
                                        {{ isset($view_detail) ? 'readonly' : '' }}>
                                </div>
                            </div>
                            <div class="mb-3 field-top">
                                <label>Pengajuan Term Of Payment</label>
                                <div>
                                    <select id="new_payment_terms" class="form-control required"
                                        error="Pengajuan Term Of Payment" {{ isset($view_detail) ? 'disabled' : '' }}>
                                        <option value=""></option>
                                        @foreach ($tops as $item)
                                            @php
                                                $isEdit = isset($data->new_payment_terms) && $data->new_payment_terms == $item->id;
                                            @endphp
                                            <option value="{{ $item->id }}" {{ $isEdit ? 'selected' : '' }}>
                                                {{ $item->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if (isset($view_detail))
                                <div class="mb-3">
                                    <label>Status Pengajuan</label>
                                    <div>
                                        <input type="text" id="status_pengajuan" class="form-control"
                                            value="{{ isset($data->status) ? $data->status : '' }}" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Keterangan Approval</label>
                                    <div>
                                        <textarea id="remarks" class="form-control" placeholder="Keterangan"
                                            readonly>{{ isset($data->remarks) ? $data->remarks : '' }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="text-end">
                        <div>
                            @if (!isset($view_detail))
                                <button type="submit" onclick="CustomerLimitTop.submit(this, event)"
                                    class="btn btn-success waves-effect waves-light me-1">
                                    Submit
                                </button>
                                <button type="reset" onclick="CustomerLimitTop.back(this)"
                                    class="btn  waves-effect">
                                    Cancel
                                </button>
                            @else
                                @if (strtolower(session('akses')) == 'supervisor sales' || strtolower(session('akses')) == 'admin supervisor' || strtolower(session('akses')) == 'superadmin' || strtolower(session('akses')) == 'operational manager')
                                    @if (!isset($data->status) || ($data->status != 'APPROVED' && $data->status != 'REJECTED'))
                                        <button type="submit" akses="{{ strtolower(session('akses')) }}"
                                            onclick="CustomerLimitTop.approve(this, event, 'acc')"
                                            class="btn btn-success waves-effect waves-light me-1">
                                            Approve
                                        </button>
                                        <button type="submit" onclick="CustomerLimitTop.reject(this, event)"
                                            class="btn btn-danger waves-effect waves-light me-1">
                                            Reject
                                        </button>
                                    @endif
                                @endif
                                <button type="reset" onclick="CustomerLimitTop.cancelAcc(this, event)"
                                    class="btn  waves-effect">
                                    Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- end select2 -->

    </div>
</div>
<!-- end row -->
