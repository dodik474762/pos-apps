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
                <form onsubmit="AccountMappingRules.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Transaction Type -->
                            <div class="mb-3">
                                <label class="form-label">Transaction</label>
                                <select class="form-control select2 required" id="transaction_type"
                                    error="Transaction">
                                    <option value=""></option>
                                    <option value="SALES" {{ isset($data->transaction_type) && $data->transaction_type == 'SALES' ? 'selected' : '' }}>SALES</option>
                                    <option value="DELIVERY" {{ isset($data->transaction_type) && $data->transaction_type == 'DELIVERY' ? 'selected' : '' }}>DELIVERY</option>
                                    <option value="DELIVERY_ORDER" {{ isset($data->transaction_type) && $data->transaction_type == 'DELIVERY_ORDER' ? 'selected' : '' }}>DELIVERY_ORDER</option>
                                    <option value="PURCHASE" {{ isset($data->transaction_type) && $data->transaction_type == 'PURCHASE' ? 'selected' : '' }}>PURCHASE</option>
                                    <option value="PURCHASE_INVOICE" {{ isset($data->transaction_type) && $data->transaction_type == 'PURCHASE_INVOICE' ? 'selected' : '' }}>PURCHASE_INVOICE</option>
                                    <option value="GOODS_RECEIPT" {{ isset($data->transaction_type) && $data->transaction_type == 'GOODS_RECEIPT' ? 'selected' : '' }}>GOODS_RECEIPT</option>
                                    <option value="CUSTOMER_PAYMENT" {{ isset($data->transaction_type) && $data->transaction_type == 'CUSTOMER_PAYMENT' ? 'selected' : '' }}>CUSTOMER_PAYMENT</option>
                                    <option value="SUPPLIER_PAYMENT" {{ isset($data->transaction_type) && $data->transaction_type == 'SUPPLIER_PAYMENT' ? 'selected' : '' }}>SUPPLIER_PAYMENT</option>
                                    <option value="PURCHASE_RETURN" {{ isset($data->transaction_type) && $data->transaction_type == 'PURCHASE_RETURN' ? 'selected' : '' }}>PURCHASE_RETURN</option>
                                    <option value="SALES_RETURN" {{ isset($data->transaction_type) && $data->transaction_type == 'SALES_RETURN' ? 'selected' : '' }}>SALES_RETURN</option>
                                </select>
                            </div>

                            <!-- Account Role -->
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-control select2 required" id="account_role"
                                    error="Role">
                                    <option value=""></option>
                                    <option value="AR" {{ isset($data->account_role) && $data->account_role == 'AR' ? 'selected' : '' }}>AR</option>
                                    <option value="AP" {{ isset($data->account_role) && $data->account_role == 'AP' ? 'selected' : '' }}>AP</option>
                                    <option value="REVENUE" {{ isset($data->account_role) && $data->account_role == 'REVENUE' ? 'selected' : '' }}>REVENUE</option>
                                    <option value="COGS" {{ isset($data->account_role) && $data->account_role == 'COGS' ? 'selected' : '' }}>COGS</option>
                                    <option value="INVENTORY" {{ isset($data->account_role) && $data->account_role == 'INVENTORY' ? 'selected' : '' }}>INVENTORY</option>
                                    <option value="EXPENSE" {{ isset($data->account_role) && $data->account_role == 'EXPENSE' ? 'selected' : '' }}>EXPENSE</option>
                                    <option value="GRNI" {{ isset($data->account_role) && $data->account_role == 'GRNI' ? 'selected' : '' }}>GRNI</option>
                                    <option value="BANK" {{ isset($data->account_role) && $data->account_role == 'BANK' ? 'selected' : '' }}>BANK</option>
                                    <option value="TAX_PAYABLE" {{ isset($data->account_role) && $data->account_role == 'TAX_PAYABLE' ? 'selected' : '' }}>TAX_PAYABLE</option>
                                    <option value="TAX_RECEIVABLE" {{ isset($data->account_role) && $data->account_role == 'TAX_RECEIVABLE' ? 'selected' : '' }}>TAX_RECEIVABLE</option>
                                    <option value="SALES_RETURN" {{ isset($data->account_role) && $data->account_role == 'SALES_RETURN' ? 'selected' : '' }}>SALES_RETURN</option>
                                    <option value="LOSS" {{ isset($data->account_role) && $data->account_role == 'LOSS' ? 'selected' : '' }}>LOSS</option>
                                </select>
                            </div>

                            <!-- COA (Account) -->
                            <div class="mb-3">
                                <label class="form-label">COA</label>
                                <select class="form-control select2 required" id="account_id"
                                    error="COA">
                                    <option value=""></option>
                                    @foreach ($account_list as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->account_id) && $data->account_id == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['code'] }} - {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Product Category -->
                            <div class="mb-3">
                                <label class="form-label">Product Category</label>
                                <select class="form-control select2" id="product_category_id">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Kosongkan jika berlaku untuk semua kategori produk.</small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Warehouse -->
                            <div class="mb-3">
                                <label class="form-label">Warehouse</label>
                                <select class="form-control select2" id="warehouse_id">
                                    <option value=""></option>
                                    @foreach ($warehouse_list as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->warehouse_id) && $data->warehouse_id == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Kosongkan jika berlaku untuk semua warehouse.</small>
                            </div>

                            <!-- Company -->
                            <div class="mb-3">
                                <label class="form-label">Company</label>
                                <select class="form-control select2" id="company_id">
                                    <option value=""></option>
                                    @foreach ($company_list as $item)
                                        <option value="{{ $item['id'] }}"
                                            {{ isset($data->company_id) && $data->company_id == $item['id'] ? 'selected' : '' }}>
                                            {{ $item['nama_company'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Kosongkan jika berlaku untuk semua company.</small>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
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
                <button type="submit" onclick="AccountMappingRules.submit(this, event)" class="btn btn-success waves-effect waves-light me-1">
                    Submit
                </button>
                <button type="reset" onclick="AccountMappingRules.cancel(this, event)" class="btn btn-secondary waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->