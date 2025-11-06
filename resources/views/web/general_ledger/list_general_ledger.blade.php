<hr>

<!-- Informasi General Ledger -->
<h5 class="mb-3 mt-4">Informasi General Ledger</h5>
<div class="table-responsive">
    <table class="table table-bordered" id="table-gl">
        <thead class="table-light">
            <tr>
                <th style="width: 20%">Account Code</th>
                <th style="width: 30%">Account Name</th>
                <th style="width: 10%" class="text-center">D/C</th>
                <th style="width: 20%" class="text-end">Amount</th>
                <th style="width: 20%">Description</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($general_ledgers))
                @foreach ($general_ledgers as $gl)
                    <tr>
                        <td>{{ $gl->account_code }}</td>
                        <td>{{ $gl->account_name_coa }}</td>
                        <td class="text-center">{{ $gl->dc }}</td>
                        <td class="text-end">{{ number_format($gl->amount, 2) }}</td>
                        <td>{{ $gl->description }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center text-muted">Belum ada jurnal untuk transaksi ini.</td>
                </tr>
            @endif
        </tbody>
        @if (!empty($general_ledgers))
            <tfoot class="table-light">
                <tr>
                    <th colspan="3" class="text-end">Total Debit:</th>
                    <th class="text-end">
                        {{ number_format($general_ledgers->where('dc', 'D')->sum('amount'), 2) }}
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Total Credit:</th>
                    <th class="text-end">
                        {{ number_format($general_ledgers->where('dc', 'C')->sum('amount'), 2) }}
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
