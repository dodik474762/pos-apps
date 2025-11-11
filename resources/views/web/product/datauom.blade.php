
@foreach ($data_uom as $item)
    <tr product_id="{{ $item['product'] }}" class="diskon-{{ $item['product'] }}">
        <td>{{ $item['code'] }} - {{ $item['product_name'] }}</td>
        <td>{{ $item['unit_name'] }}</td>
        <td>{{ $item['qty_in_base_unit'] }}</td>
    </tr>
@endforeach