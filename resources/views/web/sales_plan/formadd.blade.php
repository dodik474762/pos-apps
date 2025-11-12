<!-- Page Title -->
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
                                    value="{{ isset($sales_plan->plan_code) ? $sales_plan->plan_code : 'AUTO' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Period Year</label>
                                <select id="period_year" class="form-control required" error="Period Year">
                                    @php
                                        $currentYear = date('Y');
                                        $startYear = $currentYear - 1;
                                        $endYear = $currentYear + 5;
                                    @endphp
                                    @for($y = $startYear; $y <= $endYear; $y++)
                                        <option value="{{ $y }}" {{ isset($sales_plan->period_year) && $sales_plan->period_year == $y ? 'selected' : ($y == $currentYear ? 'selected' : '') }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Period Month</label>
                                <select class="form-control required" id="period_month" error="Period Month">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ isset($sales_plan->period_month) && $sales_plan->period_month == $m ? 'selected' : '' }}>
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
                                        <option value="{{ $s->id }}" {{ isset($sales_plan->salesman) && $sales_plan->salesman == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-control"
                                    placeholder="Optional">{{ isset($sales_plan->description) ? $sales_plan->description : '' }}</textarea>
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
                                <table class="table table-bordered" id="table-sales-plan-detail">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:15%;">Customer</th>
                                            <th style="width:15%;">Product</th>
                                            <th style="width:8%;">Week Number</th>
                                            <th style="width:8%;">Week Type</th>
                                            <th style="width:10%;">Day</th>
                                            <th style="width:10%;">Target Qty</th>
                                            <th style="width:10%;">Target Value</th>
                                            <th style="width:20%;">Note</th>
                                            <th style="width:4%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-body">
                                        @if(!empty($sales_plan_details))
                                            @foreach($sales_plan_details as $item)
                                                <tr class="input" data_id="{{ $item->id }}">
                                                    <td>
                                                        <select class="form-control select2 required" id="customer_id"
                                                            error="Customer">
                                                            @foreach ($customers as $c)
                                                                <option value="{{ $c->id }}" {{ $item->customer_id == $c->id ? 'selected' : '' }}>{{ $c->nama_customer }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="form-control select2 required" id="product_id"
                                                            error="Product">
                                                            @foreach ($products as $p)
                                                                <option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->product_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" id="week_number"
                                                            value="{{ $item->week_number }}">
                                                    </td>
                                                    <td>
                                                        <select class="form-control" id="week_type">
                                                            <option value="ODD" {{ $item->week_type == 'ODD' ? 'selected' : '' }}>ODD
                                                            </option>
                                                            <option value="EVEN" {{ $item->week_type == 'EVEN' ? 'selected' : '' }}>
                                                                EVEN</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="form-control" id="day_of_week">
                                                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                                <option value="{{ $day }}" {{ $item->day_of_week == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="number" class="form-control" id="target_qty"
                                                            value="{{ $item->target_qty }}"></td>
                                                    <td><input type="number" class="form-control" id="target_value"
                                                            value="{{ $item->target_value }}"></td>
                                                    <td><input type="text" class="form-control" id="note"
                                                            value="{{ $item->note }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="SalesPlan.removeRow(this)">Delete</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="input">
                                                <td>
                                                    <select class="form-control select2 required" id="customer_id"
                                                        error="Customer">
                                                        <option value=""></option>
                                                        @foreach ($customers as $c)
                                                            <option value="{{ $c->id }}">{{ $c->nama_customer }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control select2 required" id="product_id"
                                                        error="Product">
                                                        <option value=""></option>
                                                        @foreach ($products as $p)
                                                            <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control" id="week_number" value="1">
                                                </td>
                                                <td>
                                                    <select class="form-control" id="week_type">
                                                        <option value="ODD">ODD</option>
                                                        <option value="EVEN">EVEN</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="day_of_week">
                                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                            <option value="{{ $day }}">{{ $day }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control" id="target_qty" value="0">
                                                </td>
                                                <td><input type="number" class="form-control" id="target_value" value="0">
                                                </td>
                                                <td><input type="text" class="form-control" id="note" value=""></td>
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
