@foreach ($disc as $item)
    <tr product_id="{{ $item->product }}" class="diskon-{{ $item->product }}">
        <td>{{ $item->code }} - {{ $item->product_name }}</td>
        <td>{{ $item->unit_name }}</td>
        <td>{{ $item->category }}</td>
        <td>{{ $item->min_qty }}</td>
        <td>{{ $item->max_qty }}</td>
        <td>{{ $item->discount_type }}</td>
        <td>{{ $item->discount_value }}</td>
        <td>{{ $item->date_start }}</td>
        <td>{{ $item->nama_customer }}</td>
        <td>&nbsp;</td>
    </tr>
@endforeach