 <!-- First modal dialog -->
 <div class="modal bs-example-modal-lg fade" id="data-modal-product" aria-hidden="true" aria-labelledby="..."
     tabindex="-1">
     <div class="modal-dialog modal-xl">
         <div class="modal-content">
             <div class="modal-header">
                    <p class="text-danger">{{ $message }}</p>
                 <h5 class="modal-title">Data Program Disc Product {{ $produk_name }}</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <div class="table-responsive">
                     <table id="table-data-modal" class="table table-striped table-bordered dt-responsive nowrap"
                         style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                         <thead>
                            <tr>
                              <th>No</th>
                              <th>Satuan</th>
                              <th>Kategori Customer</th>
                              <th>Min Qty</th>
                              <th>Max Qty</th>
                              <th>Disc Tipe</th>
                              <th>Disc Nilai</th>
                              <th>Tanggal Berlaku</th>
                              <th>Customer</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            @php
                                $no = 1;
                            @endphp
                            @if (!empty($disc))
                                @foreach ($disc as $item)
                                    <tr>
                                        <td>{{ $no++ }}</td>
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
                            @else
                                <tr>
                                    <td class="text-center" colspan="10">Tidak ada data program discount ditemukan</td>
                                </tr>
                            @endif
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
