@foreach ($data as $item)
    <tr data_id="">
        <td id="do_number" data_id="{{ $item->id }}">{{ $item->do_number }}</td>
        <td id="do_date">{{ $item->do_date }}</td>
        <td id="do_customer" data_id="{{ $item->customer_id }}">{{ $item->customer_code }} - {{ $item->nama_customer }}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="PackingList.removeRow(this)">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    </tr>
@endforeach
