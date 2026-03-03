<div class="card">
    <div class="card-body">
        <div class="card-title">
            <label>Stok Kunjungan Customer</label>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle table-sm" id="table-price">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">Product</th>
                        <th style="width: 15%;">Foto Outlet</th>
                        <th style="width: 30%;">Satuan</th>
                        <th style="width: 15%;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($stock_customer as $v)
                        <tr class="=" data_id="{{ $v->id }}">
                            <td class="text-center">
                                {{ $no++ }}
                            </td>
                            <td>
                               @if($v->foto_path != '')
                                <a href="{{ $v->foto_path }}" target="_blank" rel="foto outlet">{{ $v->foto_path }}</a>
                               @endif 
                            </td>  
                            <td>{{$v->product_code}} - {{ $v->product_name }}</td>
                            <td >{{ $v->unit_name }}</td>  
                            <td >{{ $v->qty }}</td>  
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
