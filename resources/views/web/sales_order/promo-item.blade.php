<div class="table-responsive">
    <label for="">Promo</label>
    <table id="table-data-promo-header" class="table table-striped table-bordered dt-responsive nowrap">
        <thead>
            <tr>
                <th>Nama Promo</th>
                <th>Min Qty</th>
                <th>Max Qty</th>
                <th>Unit</th>
                <th>Min Mix</th>
                <th>Discount Type</th>
                <th>Discount Value</th>
                <th>Tanggal Berlaku</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($promo_item))
                <tr>
                    <td id="promo-name">{{ $promo_item[0]->promo_name }}</td>
                    <td id="promo-min-qty">{{ $promo_item[0]->min_qty }}</td>
                    <td id="promo-max-qty">{{ $promo_item[0]->max_qty }}</td>
                    <td id="promo-unit">{{ $promo_item[0]->unit_name }}</td>
                    <td id="promo-min-mix">{{ $promo_item[0]->min_mix }}</td>
                    <td id="promo-discount-type">{{ $promo_item[0]->discount_type }}</td>
                    <td id="promo-discount-value">{{ $promo_item[0]->discount_value }}</td>
                    <td id="promo-date-start">{{ $promo_item[0]->date_start }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="table-responsive">
    <label for="">Produk Promo</label>
    <table id="table-data-promo-product" class="table table-striped table-bordered dt-responsive nowrap"
        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
        <thead class="table-light">
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($promo_item))
                @foreach ($promo_item as $item)
                    <tr>
                        <td id="promo-product-code">{{ $item->product_code }}</td>
                        <td id="promo-product-name">{{ $item->product_name }}</td>
                        <td id="promo-unit-name">{{ $item->unit_name }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
<div class="table-responsive">
    <label for="">Bonus Produk</label>
    <table id="table-data-promo-product-free" class="table table-striped table-bordered dt-responsive nowrap"
        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
        <thead class="table-light">
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th>Satuan</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($product_free))
                @foreach ($product_free as $item)
                    <tr>
                        <td id="promo-free-product-code">{{ $item->product_code }}</td>
                        <td id="promo-free-product-name">{{ $item->product_name }}</td>
                        <td id="promo-free-unit-name">{{ $item->unit_name }}</td>
                        <td id="promo-free-qty">{{ $item->free_qty }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
