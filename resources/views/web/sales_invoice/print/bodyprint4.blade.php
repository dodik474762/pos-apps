 @php
     $taxRate = (float) ($ppn_val ?? 11);
     $subtotalInclude = $data->subtotal;
     $discountInclude = $data->discount_amount;
     $subtotalDpp = $subtotalInclude / (1 + $taxRate / 100);
     $discountDpp = $discountInclude / (1 + $taxRate / 100);
     $dppAfterDiscount = $subtotalDpp - $discountDpp;
     $taxAmount = $dppAfterDiscount * ($taxRate / 100);
     $grandTotal = $dppAfterDiscount + $taxAmount;

     $so_number = isset($data->do->so->so_number) ? $data->do->so->so_number : $data->so->so_number;
     $so_date = isset($data->do->so->so_date) ? $data->do->so->so_date : $data->so->so_date;
 @endphp

 {{-- ============================================================
     FAKTUR COPY 1 (atas)
     ============================================================ --}}
 <div class="faktur-block">
     @include('web.sales_invoice.print.faktur_block')
     <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
 </div>
 {{-- end faktur-block 1 --}}
