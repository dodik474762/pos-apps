<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>

<div id="content-modal-form"></div>

<input type="hidden" id="id" value="{{ $data->id ?? '' }}">
<input type="hidden" id="url" value="{{ isset($data) ? route('return-cs-edit') : route('return-cs-add') }}">

<!-- Start Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{ isset($data) ? 'Edit Credit Note' : 'Create Credit Note' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Credit</a></li>
                    <li class="breadcrumb-item active">Note</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- End Page Title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <form onsubmit="ReturnConsigment.submit(this, event)">

                    <input type="hidden" id="id" value="{{ $data->id ?? '' }}">
                    <input type="hidden" id="url"
                        value="{{ isset($data) ? route('return-cs-edit') : route('return-cs-add') }}">

                    <div class="row">

                        {{-- LEFT --}}
                        <div class="col-lg-6">

                            <div class="mb-3">
                                <label class="form-label">Return Number</label>
                                <input type="text" id="return_number" class="form-control"
                                    value="{{ $data->return_number ?? 'AUTO' }}" readonly>
                            </div>

                            {{-- Return Date --}}
                            <div class="mb-3">
                                <label class="form-label">Return Date</label>
                                <input type="date" id="return_date" class="form-control required"
                                    error="Return Date"
                                    value="{{ $data->return_date ?? date('Y-m-d') }}">
                            </div>

                            {{-- Customer --}}
                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick="ReturnConsigment.showModalVendor(this)">Pilih</button>

                                    <input disabled type="text" id="vendor_id" class="form-control required"
                                        value="{{ isset($data->vendor_id) ? $data->vendor_id . ' // ' . $data->nama_vendor : '' }}"
                                        data_id="{{ $data->vendor_id ?? '' }}">
                                </div>
                            </div>

                            {{-- Invoice (optional) --}}
                            <div class="mb-3">
                                <label class="form-label">Product</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-primary"
                                        onclick="ReturnConsigment.showModalProduct(this)">
                                        Pilih
                                    </button>
                                    <input disabled type="text" id="product_id" class="form-control required"
                                        error="Product" value="{{ $data->product_code ?? '' }}"
                                        data_id="{{ $data->product_id ?? '' }}">
                                </div>
                            </div>
                        </div>


                        {{-- RIGHT --}}
                        <div class="col-lg-6">
                             <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input data_id="{{ $data->price_id ?? '' }}" disabled type="number" id="price" class="form-control"
                                    value="{{ $data->price ?? '0' }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Qty Return</label>
                                <input type="number" id="qty" class="form-control required" error="Qty"
                                    value="{{ $data->qty ?? '0' }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Note Retur</label>
                                <select id="status_supply" class="form-control select2 required">
                                    <option value=""></option>
                                    <option value="good" {{ isset($data->status_supply) && $data->status_supply == 'good' ? 'selected' : '' }}>GOOD</option>
                                    <option value="bad" {{ isset($data->status_supply) && $data->status_supply == 'bad' ? 'selected' : '' }}>BAD</option>
                                </select>
                            </div>

                            {{-- Reason --}}
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <textarea id="remarks" class="form-control">{{ $data->remarks ?? '' }}</textarea>
                            </div>

                        </div>
                    </div>


                    <hr>

                </form>


                @include('web.general_ledger.list_general_ledger')

            </div>
        </div>

        <div class="text-end mt-3">
            @php
                $disabled = '';
            @endphp
            @if (isset($id))
                @if ($data->status == 'DRAFT')
                    <button type="button" onclick="ReturnConsigment.posted(this, event)"
                        class="btn btn-primary waves-effect waves-light me-1">
                        Confirm
                    </button>
                @else
                    @php
                        $disabled = 'disabled'
                    @endphp
                @endif
            @endif
            <button {{ $disabled }} type="submit" onclick="ReturnConsigment.submit(this, event)"
                class="btn btn-success waves-effect waves-light me-1">
                Submit
            </button>

            <button type="reset" onclick="ReturnConsigment.back(this, event)" class="btn btn-secondary waves-effect">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
    .freegood {
        background-color: #f5f7ff
    }
</style>
