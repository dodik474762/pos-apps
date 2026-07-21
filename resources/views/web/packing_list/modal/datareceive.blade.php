 <!-- First modal dialog -->
 <div class="modal bs-example-modal-lg fade" id="data-modal-product" aria-hidden="true" aria-labelledby="..." tabindex="-1">
     <div class="modal-dialog modal-xl">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">{{ $do_number }} / {{ $customer_name }}</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 {{-- @if ($receive_wh_date == null) --}}
                 <button type="button" class="btn btn-success" data_id="{{ $do_id }}"
                     onclick="PackingList.confirmReceive(this)">
                     <i class="bx bx-check-double"></i> Confirm Receive
                 </button>
                 {{-- @else --}}
                 <span class="text-success">Last Received on {{ $receive_wh_date }}</span>
                 {{-- @endif --}}
                 <br />
                 <br />
                 <div class="table-responsive">
                     <table id="table-data-item-receive" class="table table-striped table-bordered dt-responsive nowrap"
                         style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                         <thead>
                             <tr>
                                 <th>No</th>
                                 <th>Product</th>
                                 <th>Product Code</th>
                                 <th>Qty</th>
                                 <th>Qty Receive</th>
                                 <th>Unit</th>
                                 <th>Remarks</th>
                                 {{-- <th>Action</th> --}}
                             </tr>
                         </thead>
                         <tbody>
                             @foreach ($datadtl as $item)
                                 <tr data_id="{{ $item->id }}" product_id="{{ $item->product_id }}"
                                     unit_id="{{ $item->unit_id }}">
                                     <td>{{ $loop->iteration }}</td>
                                     <td>{{ $item->product_name }}</td>
                                     <td>{{ $item->product_code }}</td>
                                     <td>{{ $item->qty_do }}</td>
                                     <td>
                                         <input style="width: 80px;" type="number" name="qty_receive" id="qty_receive"
                                             value="{{ $item->qty_received ?? 0 }}">
                                     </td>
                                     <td>{{ $item->unit_name }}</td>
                                     <td>{{ $item->remarks }}</td>
                                     {{-- <td> --}}
                                     {{-- <button type="button" class="btn btn-primary"
                                             onclick="PackingList.confirmReceiveProduct('{{ $item->product_id }}')">
                                             <i class="bx bx-check-double"></i> Confirm Receive
                                         </button> --}}
                                     {{-- </td> --}}
                                 </tr>
                             @endforeach
                         </tbody>
                     </table>
                 </div>
             </div>
             <div class="modal-footer">
                 <!-- Toogle to second dialog -->
             </div>
         </div>
     </div>
 </div>
