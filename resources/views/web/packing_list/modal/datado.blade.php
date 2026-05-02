 <!-- First modal dialog -->
 <div class="modal bs-example-modal-lg fade" id="data-modal-product" aria-hidden="true" aria-labelledby="..."
     tabindex="-1">
     <div class="modal-dialog modal-xl">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Data</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                <button type="button" class="btn btn-success" onclick="PackingList.generateCheckedDO()">
                    <i class="bx bx-check-double"></i> Generate Selected
                </button>
                <br/>
                <br/>
                 <div class="table-responsive">
                     <table id="table-data-modal" class="table table-striped table-bordered dt-responsive nowrap"
                         style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                         <thead>
                            <tr>
                              <th>No</th>
                              <th>DO Number</th>
                              <th>Tanggal DO</th>
                              <th>No. Faktur</th>
                              <th>Tanggal Faktur</th>
                              <th>Kode Customer</th>
                              <th>Nama Customer</th>
                              <th>Alamat</th>
                              <th>No. SO</th>
                              <th>Tanggal SO</th>
                              <th>Status</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                                <td class="text-center" colspan="12">Tidak ada data ditemukan</td>
                            </tr>
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
