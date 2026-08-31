<input type="hidden" id="id" value="{{ isset($data->id) ? $data->id : '' }}">

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Detail {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Detail {{ $title }}</li>
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

                <div class="row">
                    <div class="col-lg-6">
                        <input type="hidden" id="current_credit_limit"
                            value="{{ isset($data->current_credit_limit) ? $data->current_credit_limit : '' }}">
                        <input type="hidden" id="current_payment_terms"
                            value="{{ isset($data->current_payment_terms) ? $data->current_payment_terms : '' }}">
                        <div class="mb-3">
                            <label class="form-label">Code Pengajuan</label>
                            <div>
                                <input type="text" class="form-control"
                                    value="{{ isset($data->code) ? $data->code : '' }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <div>
                                <input type="text" class="form-control"
                                    value="{{ isset($data->customer_code) ? $data->customer_code : '' }} - {{ isset($data->customer_name) ? $data->customer_name : '' }}"
                                    readonly>
                            </div>
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
                                <textarea id="reason" class="form-control" placeholder="Alasan Pengajuan" readonly>{{ isset($data->reason) ? $data->reason : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label">Tipe Pengajuan</label>
                            <select class="form-control select2" id="type_pengajuan" disabled
                                onchange="CustomerLimitTop.changeTypePengajuan(this)">
                                <option value=""></option>
                                <option value="CREDIT_LIMIT"
                                    {{ isset($data->type_pengajuan) ? ($data->type_pengajuan == 'CREDIT_LIMIT' ? 'selected' : '') : '' }}>
                                    Pengajuan Credit Limit</option>
                                <option value="TERM_OF_PAYMENT"
                                    {{ isset($data->type_pengajuan) ? ($data->type_pengajuan == 'TERM_OF_PAYMENT' ? 'selected' : '') : '' }}>
                                    Pengajuan Term Of Payment</option>
                                <option value="CREDIT_LIMIT_DAN_TOP"
                                    {{ isset($data->type_pengajuan) ? ($data->type_pengajuan == 'CREDIT_LIMIT_DAN_TOP' ? 'selected' : '') : '' }}>
                                    Pengajuan Credit Limit & Term Of Payment</option>
                            </select>
                        </div>
                        <div class="mb-3 field-credit-limit">
                            <label>Pengajuan Credit Limit</label>
                            <div>
                                <input tabindex="10" type="number" id="new_credit_limit" class="form-control"
                                    placeholder="Pengajuan Credit Limit"
                                    value="{{ isset($data->new_credit_limit) ? $data->new_credit_limit : '' }}"
                                    readonly>
                            </div>
                        </div>
                        <div class="mb-3 field-top">
                            <label>Pengajuan Term Of Payment</label>
                            <div>
                                @php
                                    $topName = '';
                                    foreach ($tops as $item) {
                                        if (isset($data->new_payment_terms) && $data->new_payment_terms == $item->id) {
                                            $topName = $item->code;
                                        }
                                    }
                                @endphp
                                <input type="text" id="new_top_name" class="form-control"
                                    value="{{ $topName }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Status Pengajuan</label>
                            <div>
                                <input type="text" id="status_pengajuan" class="form-control"
                                    value="{{ isset($data->status) ? $data->status : '' }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Spv Sales Acc</label>
                            <div>
                                <input type="text" class="form-control"
                                    value="{{ isset($data->spv_sales_date) ? $data->spv_sales_date : '-' }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Admin Spv Acc</label>
                            <div>
                                <input type="text" class="form-control"
                                    value="{{ isset($data->admin_sales_date) ? $data->admin_sales_date : '-' }}"
                                    readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Operational Manager Acc</label>
                            <div>
                                <input type="text" class="form-control"
                                    value="{{ isset($data->om_date) ? $data->om_date : '-' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <div>
                        @if (strtolower(session('akses')) == 'supervisor sales' ||
                                strtolower(session('akses')) == 'admin supervisor' ||
                                strtolower(session('akses')) == 'superadmin' ||
                                strtolower(session('akses')) == 'operational manager' ||
                                strtolower(session('akses')) == 'bod')
                            @if (!isset($data->status) || ($data->status != 'APPROVED' && $data->status != 'REJECTED'))
                                <button type="submit" onclick="CustomerLimitTop.approve(this, event, 'acc')"
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
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
<!-- end row -->
