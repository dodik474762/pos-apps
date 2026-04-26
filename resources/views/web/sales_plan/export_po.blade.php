
<style>
    .card-title-custom {
        font-weight: 600;
        color: #405189;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, rgba(64,81,137,0.1) 0%, rgba(64,81,137,0.5) 50%, rgba(64,81,137,0.1) 100%);
        margin: 2rem 0;
    }
    .premium-table thead th {
        background-color: #f3f6f9 !important;
        color: #333;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        border-bottom: 2px solid #e9ebec !important;
    }
    .premium-table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
    }
    .export-toolbar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 15px;
    }
    .badge-soft-success {
        background-color: rgba(10, 179, 156, 0.1);
        color: #0ab39c;
    }
    .badge-soft-info {
        background-color: rgba(41, 156, 219, 0.1);
        color: #299cdb;
    }
    .badge-soft-warning {
        background-color: rgba(247, 184, 75, 0.1);
        color: #f7b84b;
    }
    .table-card-premium {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        /* overflow: hidden; */ /* Removed to allow scrolling */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        background: #fff;
    }
    /* Ensure DataTables scroll wrapper is visible */
    .dataTables_scrollBody {
        border-radius: 0 0 8px 8px;
    }
</style>

<button type="button" id="btn-show-modal" class="" style="display: none;" data-bs-toggle="modal"
  data-bs-target="#data-modal-karyawan"></button>
<div id="content-modal-form"></div>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Export Preview</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Export PO</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom-dashed">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1 card-title-custom">
                        <i class="ri-file-download-line fs-24 text-primary"></i>
                        Purchase Order Export Panel
                    </h5>
                    <div class="flex-shrink-0">
                        <button type="button" onclick="SalesPlan.cancel(this, event)" class="btn btn-soft-secondary btn-sm waves-effect">
                            <i class="ri-arrow-go-back-line align-bottom"></i> Back to List
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ url('/api/transaksi/sales_plan/export_po') }}" method="POST" enctype="multipart/form-data" id="form-product">
                     @csrf
                    <input type="hidden" id="id" name="id" value="{{ isset($id) ? $id : '' }}">
                    
                    <!-- Header Section -->
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <h6 class="text-uppercase fw-bold text-muted mb-3 d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary fs-12">01</span>
                                Purchase Order Header
                            </h6>
                             <div class="table-card-premium table-responsive mb-4">
                                <table class="table table-nowrap align-middle premium-table" id="table-export-po" style="width: 100%;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ref.* (c.ref)</th>
                                            <th>Ref. vendor (c.ref_supplier)</th>
                                            <th>Third-party name* (c.fk_soc)</th>
                                            <th>ProjectId (c.fk_projet)</th>
                                            <th>Creation date (c.date_creation)</th>
                                            <th>DateValid (c.date_valid)</th>
                                            <th>Approving date (c.date_approve)</th>
                                            <th>DateOrder (c.date_commande)</th>
                                            <th>ModifiedById (c.fk_user_modif)</th>
                                            <th>ValidatedById (c.fk_user_valid)</th>
                                            <th>ApprovedById (c.fk_user_approve)</th>
                                            <th>Source (c.source)</th>
                                            <th>Status* (c.fk_statut)</th>
                                            <th>Billed(0/1) (c.billed)</th>
                                            <th>TotalTVA (c.total_tva)</th>
                                            <th>Total (excl. tax) (c.total_ht)</th>
                                            <th>Total (inc. tax) (c.total_ttc)</th>
                                            <th>Note (private) (c.note_private)</th>
                                            <th>Note (c.note_public)</th>
                                            <th>DeliveryDate (c.date_livraison)</th>
                                            <th>Payment Condition (c.fk_cond_reglement)</th>
                                            <th>Payment Mode (c.fk_mode_reglement)</th>
                                            <th>Doc template (c.model_pdf)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-medium text-primary">PO-BITO-2025-001</td>
                                            <td>3</td>
                                            <td>CV Maju Beton Surabaya</td>
                                            <td>3</td>
                                            <td>2025-01-03</td>
                                            <td>2025-01-04</td>
                                            <td>2025-01-04</td>
                                            <td>2025-01-05</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>0</td>
                                            <td>2</td>
                                            <td>1</td>
                                            <td>39102250</td>
                                            <td>355475000</td>
                                            <td>394577250</td>
                                            <td>Cek mutu beton sesuai mix design sebelum pengiriman</td>
                                            <td>Pengadaan beton ready mix K-300 dan K-350 untuk Gedung A</td>
                                            <td>2025-01-20</td>
                                            <td>4</td>
                                            <td>4</td>
                                            <td>template_po</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Detail Section -->
                    <div class="row">
                        <div class="col-lg-12">
                            <h6 class="text-uppercase fw-bold text-muted mb-3 d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary fs-12">02</span>
                                Purchase Order Item Details
                            </h6>
                             <div class="table-card-premium table-responsive">
                                <table class="table table-nowrap align-middle premium-table" id="table-export-po-detail" style="width: 100%;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>PurchaseOrder* (cd.fk_commande)</th>
                                            <th>Parent line ID (cd.fk_parent_line)</th>
                                            <th>IdProduct (cd.fk_product)</th>
                                            <th>SupplierRef (cd.ref)</th>
                                            <th>Description of line (cd.description)</th>
                                            <th>VAT Rate of line (cd.tva_tx)</th>
                                            <th>Quantity for line (cd.qty)</th>
                                            <th>Reduc. Percent (cd.remise_percent)</th>
                                            <th>Sub Price (cd.subprice)</th>
                                            <th>Amount excl. tax for line (cd.total_ht)</th>
                                            <th>Amount of VAT for line (cd.total_tva)</th>
                                            <th>Amount with tax for line (cd.total_ttc)</th>
                                            <th>Type of line (0=product/ 1=service) (cd.product_type)</th>
                                            <th>Start Date (cd.date_start)</th>
                                            <th>End Date (cd.date_end)</th>
                                            <th>InfoBits (cd.info_bits)</th>
                                            <th>Special Code (cd.special_code)</th>
                                            <th>LinePosition (cd.rang)</th>
                                            <th>Unit (cd.fk_unit)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>PO-BITO-2025-001</td>
                                            <td></td>
                                            <td>2</td>
                                            <td>SU2604-00002</td>
                                            <td>Beton Ready Mix K-300 untuk pengecoran pondasi Gedung A</td>
                                            <td>11</td>
                                            <td>150</td>
                                            <td>0</td>
                                            <td>950000</td>
                                            <td>142500000</td>
                                            <td>15675000</td>
                                            <td>158175000</td>
                                            <td>0</td>
                                            <td>2025-01-05</td>
                                            <td>2025-01-20</td>
                                            <td>0</td>
                                            <td></td>
                                            <td>1</td>
                                            <td>19</td>
                                        </tr>
                                        <tr>
                                            <td>PO-BITO-2025-001</td>
                                            <td></td>
                                            <td>3</td>
                                            <td>SU2604-00002</td>
                                            <td>Beton Ready Mix K-350 untuk pengecoran plat lantai Gedung A</td>
                                            <td>11</td>
                                            <td>200</td>
                                            <td>0</td>
                                            <td>1050000</td>
                                            <td>210000000</td>
                                            <td>23100000</td>
                                            <td>233100000</td>
                                            <td>0</td>
                                            <td>2025-01-05</td>
                                            <td>2025-01-20</td>
                                            <td>0</td>
                                            <td></td>
                                            <td>2</td>
                                            <td>19</td>
                                        </tr>
                                        <tr>
                                            <td>PO-BITO-2025-001</td>
                                            <td></td>
                                            <td>4</td>
                                            <td>SU2604-00002</td>
                                            <td>Admixture plasticizer untuk campuran beton</td>
                                            <td>11</td>
                                            <td>35</td>
                                            <td>0</td>
                                            <td>85000</td>
                                            <td>2975000</td>
                                            <td>327250</td>
                                            <td>3302250</td>
                                            <td>0</td>
                                            <td>2025-01-05</td>
                                            <td>2025-01-20</td>
                                            <td>0</td>
                                            <td></td>
                                            <td>3</td>
                                            <td>26</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-center text-muted">
                            <p class="fs-12"><i class="ri-information-line align-bottom text-primary"></i> Data provided above is a local preview for export generation purposes.</p>
                        </div>
                    </div>
                </form>

            </div>
            <div class="card-footer bg-light-subtle">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <i class="ri-checkbox-circle-fill text-success fs-16 me-1 align-middle"></i> Ready to Export
                    </div>
                    <div>
                        <button type="reset" onclick="SalesPlan.cancel(this, event)" class="btn btn-link link-danger fw-medium shadow-none p-0 me-3">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary d-none">
                            Process All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        if (typeof jQuery !== 'undefined') {
            $(document).ready(function() {
                const commonConfig = {
                    dom: '<"export-toolbar"B>rtip',
                    searching: true,
                    ordering: true,
                    info: true,
                    paging: true,
                    scrollX: true, // Enable horizontal scrolling
                    autoWidth: false, // Prevent DataTables from compressing columns
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>",
                        },
                    },
                    drawCallback: function () {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    }
                };

                // Initialize Header Table
                if ($.fn.DataTable.isDataTable('#table-export-po')) {
                    $('#table-export-po').DataTable().destroy();
                }
                
                $('#table-export-po').DataTable({
                    ...commonConfig,
                    buttons: [
                        {
                            extend: 'csv',
                            text: '<i class="ri-file-excel-line me-1"></i> Export Header (CSV)',
                            className: 'btn btn-success btn-sm waves-effect waves-light',
                            filename: 'PO_Header_' + new Date().getTime()
                        }
                    ]
                });

                // Initialize Detail Table
                if ($.fn.DataTable.isDataTable('#table-export-po-detail')) {
                    $('#table-export-po-detail').DataTable().destroy();
                }
                
                $('#table-export-po-detail').DataTable({
                    ...commonConfig,
                    buttons: [
                        {
                            extend: 'csv',
                            text: '<i class="ri-file-list-line me-1"></i> Export Detail (CSV)',
                            className: 'btn btn-info btn-sm waves-effect waves-light',
                            filename: 'PO_Detail_' + new Date().getTime()
                        }
                    ]
                });
            });
        }
    };
</script>

