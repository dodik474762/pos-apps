<div class="table-responsive">
    <label for="">Promo</label>
    <table id="table-data-promo-header" class="table table-striped table-bordered dt-responsive nowrap">
        <thead class="table-light">
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
                @foreach ($promoIds as $item)
                    @php
                        $promo = collect($promo_item)->where('product_promo_item', $item)->first();
                    @endphp
                    <tr data_id="{{ $item }}" kelipatan="{{ $promo->kelipatan }}">
                        <td id="promo-name">{{ $promo->promo_name }}</td>
                        <td id="promo-min-qty">{{ $promo->min_qty }}</td>
                        <td id="promo-max-qty">{{ $promo->max_qty }}</td>
                        <td id="promo-unit" unit_id="{{ $promo->unit }}">{{ $promo->unit_name }}</td>
                        <td id="promo-min-mix">{{ $promo->min_mix }}</td>
                        <td id="promo-discount-type">{{ $promo->discount_type }}</td>
                        <td id="promo-discount-value">{{ $promo->discount_value }}</td>
                        <td id="promo-date-start">{{ $promo->date_start }}</td>
                    </tr>
                @endforeach
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
                <th>Ikut Promo</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($promo_item))
                @foreach ($promo_item as $item)
                    <tr parent_id="{{ $item->product_promo_item }}" class="promo-item-{{ $item->product_promo_item }}">
                        <td id="promo-product-code" product_id="{{ $item->product }}">{{ $item->product_code }}</td>
                        <td id="promo-product-name">{{ $item->product_name }}</td>
                        <td id="promo-unit-name">{{ $item->unit_name }}</td>
                        <td id="">{{ $item->promo_name }}</td>
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
                <th>Ikut Promo</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($product_free))
                @foreach ($product_free as $item)
                    <tr parent_id="{{ $item->product_promo_item }}" class="promo-free-{{ $item->product_promo_item }}">
                        <td id="promo-free-product-code" product_id="{{ $item->free_product }}">{{ $item->product_code }}</td>
                        <td id="promo-free-product-name">{{ $item->product_name }}</td>
                        <td id="promo-free-unit-name" unit_id="{{ $item->free_unit }}">{{ $item->unit_name }}</td>
                        <td id="promo-free-qty">{{ $item->free_qty }}</td>
                        <td id="">{{ $item->promo_name }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
