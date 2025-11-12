@foreach ($disc as $item)
    <tr product_id="{{ $item->product }}"
        class="diskon-{{ $item->product }}"
        data_id="{{ $item->id }}"
        unit="{{ $item->unit }}"
        discount_type="{{ $item->discount_type }}"
        discount_value="{{ $item->discount_value }}"
        customer_category="{{ $item->customer_category }}"
        min_qty="{{ $item->min_qty }}"
        max_qty="{{ $item->max_qty }}"
        berlaku_from="{{ $item->date_start }}"
        customer="{{ $item->customer }}">
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
