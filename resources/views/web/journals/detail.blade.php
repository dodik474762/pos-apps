@php
    $journalStatus = isset($data->status) ? $data->status : 'DRAFT';
    $accountMap = [];
    foreach ($account_list as $item) {
        $accountMap[$item['id']] = $item;
    }
    $totalDebit = 0;
    $totalCredit = 0;
@endphp

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Detail {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $title_parent }}</a></li>
                    <li class="breadcrumb-item active">Detail {{ $title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ isset($data->journal_no) ? $data->journal_no : '' }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <span
                            class="badge bg-{{ $journalStatus == 'POSTED' ? 'success' : ($journalStatus == 'REVERSED' ? 'danger' : 'warning') }}">
                            {{ $journalStatus }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 40%">Tanggal Jurnal</td>
                                    <td>{{ isset($data->journal_date) ? date('d-m-Y', strtotime($data->journal_date)) : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Periode</td>
                                    <td>{{ isset($data->period_month) ? $data->period_month . ' / ' . $data->period_year : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Keterangan</td>
                                    <td>{{ isset($data->description) ? $data->description : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 40%">Reference</td>
                                    <td>
                                        {{ isset($data->reference_type) && $data->reference_type ? $data->reference_type : '-' }}
                                        @if (isset($data->reference_id) && $data->reference_id)
                                            #{{ $data->reference_id }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Posted At</td>
                                    <td>{{ isset($data->posted_at) && $data->posted_at ? date('d-m-Y H:i', strtotime($data->posted_at)) : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Reversal Of</td>
                                    <td>{{ isset($data->reversal_of_no) && $data->reversal_of_no ? $data->reversal_of_no : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="table-detail-view">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 30%">Akun</th>
                                <th>Keterangan</th>
                                <th style="width: 18%" class="text-end">Debit</th>
                                <th style="width: 18%" class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $row)
                                @php
                                    $account = isset($accountMap[$row->account_id])
                                        ? $accountMap[$row->account_id]
                                        : null;
                                    $totalDebit += (float) $row->debit;
                                    $totalCredit += (float) $row->credit;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $row->line_no }}</td>
                                    <td>{{ empty($account) ? $row->account_id : $account['code'] . ' - ' . $account['name'] }}
                                    </td>
                                    <td>{{ $row->description }}</td>
                                    <td class="text-end">{{ number_format((float) $row->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $row->credit, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total Debit</th>
                                <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                <th class="text-end"></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total Credit</th>
                                <th class="text-end"></th>
                                <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-end">
            <div>
                @if ($journalStatus == 'POSTED')
                    <button type="button" data_id="{{ $id }}" onclick="Journals.reversal(this, event)"
                        class="btn btn-warning waves-effect waves-light me-1">
                        Reversal
                    </button>
                @endif
                <button type="button" onclick="Journals.cancel(this, event)" class="btn btn-secondary waves-effect">
                    Back
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end row -->
