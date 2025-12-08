@foreach ($data as $d)
    <tr class="do_detail_{{ $d->do_id }}" data_id="{{ $d->id }}">
        <td id="product_id" data_id="{{ $d->product_id }}">{{ $d->product_code }} - {{ $d->product_name }}</td>
        <td id="product_qty">{{ $d->qty }}</td>
        <td>
            <input id="qty_pack" type="number" step="0.01" class="form-control" min="{{ $d->qty }}" max="{{$d->qty  }}" value="{{ $d->qty }}">
        </td>
        <td>
            <input type="text" disabled class="form-control" value="{{ $d->note }}">
        </td>
    </tr>
@endforeach
