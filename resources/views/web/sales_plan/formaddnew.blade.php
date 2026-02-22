<!-- Page Title -->
<button type="button" id="btn-show-modal" style="display: none;" data-bs-toggle="modal"
    data-bs-target="#data-modal-product"></button>
<div id="content-modal-form"></div>
<input type="hidden" id="id" value="{{ isset($id) ? $id : '' }}">

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create Sales Plan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Sales</a></li>
                    <li class="breadcrumb-item active">Create Sales Plan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="SalesPlan.submit(this, event)">
                    <div class="row">
                        <!-- Header -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Plan Code</label>
                                <input type="text" id="plan_code" class="form-control required" error="Plan Code"
                                    placeholder="Auto Generate" readonly
                                    value="{{ isset($data->plan_code) ? $data->plan_code : 'AUTO' }}">
                            </div>

                            <div class="mb-3 d-none">
                                <label class="form-label">Period Year</label>
                                <select id="period_year" class="form-control required" error="Period Year">
                                    @php
                                        $currentYear = date('Y');
                                        $startYear = $currentYear - 1;
                                        $endYear = $currentYear + 5;
                                    @endphp
                                    @for ($y = $startYear; $y <= $endYear; $y++)
                                        <option value="{{ $y }}"
                                            {{ isset($data->period_year) && $data->period_year == $y ? 'selected' : ($y == $currentYear ? 'selected' : '') }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3 d-none">
                                <label class="form-label">Period Month</label>
                                <select class="form-control required" id="period_month" error="Period Month">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}"
                                            {{ isset($data->period_month) && $data->period_month == $m ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Salesman</label>
                                <select class="form-control select2 required" id="salesman" error="Salesman">
                                    <option value=""></option>
                                    @foreach ($salesmen as $s)
                                        <option value="{{ $s->id }}"
                                            {{ isset($data->salesman) && $data->salesman == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-control" placeholder="Optional">{{ isset($data->description) ? $data->description : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detail Items -->
                    <ul class="nav nav-tabs" id="itemTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-barang" data-bs-toggle="tab"
                                data-bs-target="#tab-pane-barang" type="button" role="tab"
                                aria-controls="tab-pane-barang" aria-selected="true">
                                Plan Details
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="itemTabContent">
                        <div class="tab-pane fade show active" id="tab-pane-barang" role="tabpanel"
                            aria-labelledby="tab-barang">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="table-items">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Type</th>
                                            <th colspan="7" class="text-center">Visit Day</th>
                                            {{-- <th>Target Qty</th>
                                            <th>Target Value</th> --}}
                                            <th>Note</th>
                                            <th>Kategori</th>
                                            <th>Action</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2"></th>
                                            <th>Mon</th>
                                            <th>Tue</th>
                                            <th>Wed</th>
                                            <th>Thu</th>
                                            <th>Fri</th>
                                            <th>Sat</th>
                                            <th>Sun</th>
                                            <th colspan="4"></th>
                                        </tr>
                                    </thead>

                                    <tbody id="detail-body">
                                        @if (!empty($sales_plan_details))
                                            @foreach ($sales_plan_details as $item)
                                                <tr class="input" data_id="{{ $item->id ?? '' }}">

                                                    <!-- Customer -->
                                                    <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            onclick="SalesPlan.showDataCustomer(this)">Pilih</button>
                                                        <input disabled type="text" class="form-control required" id="customer_id" error="Customer" value="{{ $item->customer_id }}//{{ $item->nama_customer }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="visit_type">
                                                        @foreach ($visit_types as $v)
                                                            <option value="{{ $v['id'] }}" {{ $item->visit_circle == $v['id'] ? 'selected' : '' }}>{{ $v['term_id'].' - '.$v['keterangan'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                  <!-- Visit Days -->
                                                    @foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $d)
                                                        <td class="text-center">
                                                            @php
                                                                $selected = '';
                                                                if($item->visit_mon == 1 && $d == 'mon'){
                                                                    $selected = 'checked';
                                                                }
                                                                if($item->visit_tue == 1 && $d == 'tue'){
                                                                    $selected = 'checked';
                                                                }
                                                                if($item->visit_wed == 1 && $d == 'wed'){
                                                                    $selected = 'checked';
                                                                }
                                                                if($item->visit_thu == 1 && $d == 'thu'){
                                                                    $selected = 'checked';
                                                                }
                                                                if($item->visit_fri == 1 && $d == 'fri'){
                                                                    $selected = 'checked';
                                                                }
                                                                if($item->visit_sat == 1 && $d == 'sat'){
                                                                    $selected = 'checked';
                                                                }
                                                                if($item->visit_sun == 1 && $d == 'sun'){
                                                                    $selected = 'checked';
                                                                }

                                                            @endphp
                                                            <input type="checkbox" id="visit_{{ $d }}"
                                                                {{ $selected }}>
                                                        </td>
                                                    @endforeach
                                                <td><input type="text" class="form-control" id="note"
                                                        value="{{ $item->note  }}"></td>
                                                <td>
                                                    <select name="type" id="type"
                                                        class="form-control required" error="Tipe">
                                                        <option value="PERMANEN" {{ $item->pjp_status == 'PERMANEN' ? 'selected' : '' }}>PERMANEN</option>
                                                        <option value="EXTRA CALL" {{ $item->pjp_status == 'EXTRA CALL' ? 'selected' : '' }}>EXTRA CALL</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="SalesPlan.removeRow(this)">Delete</button>
                                                </td>

                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="input">
                                                <td>
                                                    <div class="input-group">
                                                        <button class="btn btn-outline-primary" type="button"
                                                            onclick="SalesPlan.showDataCustomer(this)">Pilih</button>
                                                        <input disabled type="text" class="form-control required"
                                                            id="customer_id" error="Customer" value="">
                                                    </div>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="visit_type">
                                                        @foreach ($visit_types as $v)
                                                            <option value="{{ $v['id'] }}">{{ $v['term_id'].' - '.$v['keterangan'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                  <!-- Visit Days -->
                                                    @foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $d)
                                                        <td class="text-center">
                                                            <input type="checkbox" id="visit_{{ $d }}"
                                                                {{ isset($item) && $item->{'visit_' . $d} ? 'checked' : '' }}>
                                                        </td>
                                                    @endforeach
                                                {{-- <td><input type="number" class="form-control" id="target_qty"
                                                        value="0">
                                                </td>
                                                <td><input type="number" class="form-control" id="target_value"
                                                        value="0">
                                                </td> --}}
                                                <td><input type="text" class="form-control" id="note"
                                                        value=""></td>
                                                <td>
                                                    <select name="type" id="type"
                                                        class="form-control required" error="Tipe">
                                                        <option value="PERMANEN">PERMANEN</option>
                                                        <option value="EXTRA CALL">EXTRA CALL</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="SalesPlan.removeRow(this)">Delete</button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="SalesPlan.addRow()">+ Add
                            Detail</button>
                    </div>

                    <hr>

                    <!-- Submit / Cancel -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-success waves-effect waves-light me-1">Submit</button>
                        <button type="reset" onclick="SalesPlan.back(this, event)"
                            class="btn btn-secondary waves-effect">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
