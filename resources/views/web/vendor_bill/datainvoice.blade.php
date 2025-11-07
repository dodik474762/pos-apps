@if(isset($invoices) && count($invoices) > 0)
    @foreach($invoices as $inv)
        <tr class="input" data_id="{{ $inv->id }}" id_detail="">
            <td><input type="text" class="form-control" id="invoice_number" value="{{ $inv->invoice_number }}" disabled></td>
            <td><input type="date" class="form-control" id="invoice_date" value="{{ $inv->invoice_date }}" disabled></td>
            <td><input type="number" class="form-control" id="total_amount" value="{{ $inv->total_amount }}" disabled></td>
            <td><input type="number" class="form-control" id="outstanding" value="{{ $inv->outstanding }}" disabled></td>
            <td><input type="number" class="form-control" id="amount_paid" max="{{ $inv->outstanding }}" value="0" onkeyup="VendorBill.calcRow(this)"></td>
            <td><input type="number" class="form-control" id="remaining" value="{{ $inv->total_amount }}" disabled></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="VendorBill.removeRow(this)">
                    <i class="bx bx-trash-alt"></i>
                </button>
            </td>
        </tr>
    @endforeach
@endif
