@php
    $journalStatus = isset($data->status) ? $data->status : 'DRAFT';
    $isEditable = $journalStatus == 'DRAFT';
    $detailJson = [];
    foreach ($details as $row) {
        $detailJson[] = [
            'account_id' => $row->account_id,
            'description' => $row->description,
            'debit' => number_format((float) $row->debit, 2, '.', ''),
            'credit' => number_format((float) $row->credit, 2, '.', ''),
        ];
    }
@endphp
<input type="hidden" id="id" value="{{ isset($data->id) ? $data->id : '' }}">
<input type="hidden" id="status" value="{{ $journalStatus }}">
<input type="hidden" id="detail-data" value='{{ json_encode($detailJson) }}'>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Create {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Create {{ $title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

@if (! $isEditable)
    <div class="alert alert-warning" role="alert">
        Jurnal berstatus <b>{{ $journalStatus }}</b> bersifat immutable dan tidak dapat diedit. Gunakan jurnal
        reversal untuk membatalkan jurnal berstatus POSTED.
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form onsubmit="Journals.submit(this, event)">
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Journal Date -->
                            <div class="mb-3">
                                <label class="form-label">Tanggal Jurnal</label>
                                <input type="date" id="journal_date" class="form-control required" error="Tanggal Jurnal"
                                    @disabled(! $isEditable)
                                    value="{{ isset($data->journal_date) ? date('Y-m-d', strtotime($data->journal_date)) : date('Y-m-d') }}">
                                <small class="text-muted">Tanggal jurnal harus berada pada periode akuntansi OPEN.</small>
                            </div>

                            <!-- Reference Type -->
                            <div class="mb-3">
                                <label class="form-label">Reference Type</label>
                                <input type="text" id="reference_type" class="form-control" @disabled(! $isEditable)
                                    placeholder="Contoh: SALES_INVOICE"
                                    value="{{ isset($data->reference_type) ? $data->reference_type : '' }}">
                            </div>

                            <!-- Reference Id -->
                            <div class="mb-3">
                                <label class="form-label">Reference Id</label>
                                <input type="text" id="reference_id" class="form-control" @disabled(! $isEditable)
                                    placeholder="Id dokumen asal transaksi"
                                    value="{{ isset($data->reference_id) ? $data->reference_id : '' }}">
                                <small class="text-muted">Jurnal otomatis wajib memiliki reference_type dan reference_id.</small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Journal No -->
                            <div class="mb-3">
                                <label class="form-label">Journal No</label>
                                <input type="text" id="journal_no" class="form-control" readonly
                                    value="{{ isset($data->journal_no) ? $data->journal_no : 'Otomatis saat disimpan' }}">
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" id="journal_status" class="form-control" readonly
                                    value="{{ $journalStatus }}">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea id="description" class="form-control" rows="3" @disabled(! $isEditable)
                                    placeholder="Keterangan jurnal">{{ isset($data->description) ? $data->description : '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Jurnal -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Detail Jurnal</h5>
                                @if ($isEditable)
                                    <button type="button" class="btn btn-sm btn-success" id="add-detail-btn"
                                        onclick="Journals.addDetail()">
                                        <i class="ri-add-line align-bottom me-1"></i> Tambah Baris
                                    </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="table-detail">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 30%">Akun</th>
                                            <th>Keterangan</th>
                                            <th style="width: 18%">Debit</th>
                                            <th style="width: 18%">Credit</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-detail">
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">Total</th>
                                            <th class="text-end text-primary" id="total-debit">0.00</th>
                                            <th class="text-end text-primary" id="total-credit">0.00</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="alert alert-danger d-none" id="balance-alert" role="alert">
                                Jurnal tidak balance, total debit harus sama dengan total credit.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-end">
            <div>
                @if ($isEditable)
                    <button type="submit" onclick="Journals.submit(this, event)"
                        class="btn btn-success waves-effect waves-light me-1">
                        Save as Draft
                    </button>
                    <button type="button" onclick="Journals.saveAndPost(this, event)"
                        class="btn btn-primary waves-effect waves-light me-1">
                        Save &amp; Post
                    </button>
                @else
                    @if ($journalStatus == 'POSTED')
                        <button type="button" onclick="Journals.reversal(this, event)"
                            class="btn btn-warning waves-effect waves-light me-1">
                            Reversal
                        </button>
                    @endif
                @endif
                <button type="reset" onclick="Journals.cancel(this, event)" class="btn btn-secondary waves-effect">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- Template baris detail -->
<script type="text/html" id="template-detail">
    <tr>
        <td class="text-center td-line"></td>
        <td>
            <select class="form-control select2-detail" name="account_id">
                <option value=""></option>
                @foreach ($account_list as $item)
                    <option value="{{ $item['id'] }}">{{ $item['code'] }} - {{ $item['name'] }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="description" placeholder="Keterangan baris">
        </td>
        <td>
            <input type="number" class="form-control text-end" name="debit" step="0.01" min="0" value="0.00">
        </td>
        <td>
            <input type="number" class="form-control text-end" name="credit" step="0.01" min="0" value="0.00">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="Journals.removeDetail(this)">
                <i class="bx bx-trash-alt"></i>
            </button>
        </td>
    </tr>
</script>
