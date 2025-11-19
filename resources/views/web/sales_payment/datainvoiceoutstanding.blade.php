@foreach ($data as $item)
    <tr data_id="">
        <td id="invoice_id" data_id="{{ $item->id }}" subtotal="{{ $item->subtotal }}" discount_amount="{{ $item->discount_amount }}">{{ $item->invoice_number }}</td>
        <td id="date_invoice">{{ $item->invoice_date }}</td>
        <td>
            <input type="number" step="0.01" class="form-control" id="outstanding_amount" disabled value="{{ $item->outstanding_amount }}">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" id="allocated_amount" value="{{ $item->outstanding_amount }}" min="0" max="{{ $item->outstanding_amount }}" onkeyup="SalesPayment.changeAllocate(this)">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="SalesPayment.removeRow(this)">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    </tr>
@endforeach
