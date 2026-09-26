<!-- Modal konfirmasi pembuatan jurnal pembayaran supplier -->
<div class="modal fade" id="konfirmasi-post-jurnal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Posting Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan membuat dan mempost jurnal pembayaran untuk Vendor Payment
                    <b>{{ ! empty($payment_number) ? $payment_number : '' }}</b>
                    ke <b>{{ ! empty($vendor_name) ? $vendor_name : '' }}</b> sebesar
                    <b>Rp {{ ! empty($payment_total) ? number_format($payment_total, 0, ',', '.') : '0' }}</b> ?
                </p>
                <p class="mb-1">Jurnal yang dibuat:</p>
                <ul class="mb-2">
                    <li>Debit <b>AP</b> sebesar total pembayaran</li>
                    <li>Credit <b>Bank/Cash</b> sebesar total pembayaran</li>
                    <li>Total alokasi detail: <b>{{ ! empty($invoice_count) ? $invoice_count : 0 }}</b> invoice</li>
                </ul>

                <div class="mb-2">
                    <label class="form-label">Akun Bank / Cash (Jurnal)</label>
                    <select id="vendor-bill-bank-account-id" class="form-control select2 required"
                        @if (empty($bank_cash_accounts) || $bank_cash_accounts->count() === 0) disabled @endif>
                        <option value="">-- Pilih Akun Bank / Cash --</option>
                        @foreach ($bank_cash_accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        Akun ini dipilih user, bukan dari Account Mapping Rules. Bila dikosongkan, akun diambil
                        dari rule <b>SUPPLIER_PAYMENT / BANK</b>.
                    </small>
                </div>

                @if (empty($bank_cash_accounts) || $bank_cash_accounts->count() === 0)
                    <div class="alert alert-warning mb-0">
                        Tidak ada akun kas/bank pada master accounts Journal Engine. Isi dropdown di atas
                        akan kosong sehingga posting hanya bisa berjalan bila rule
                        <b>SUPPLIER_PAYMENT / BANK</b> tersedia.
                    </div>
                @endif

                <p class="text-muted mb-0 mt-2">
                    Akun AP diambil dari menu Master &gt; Account Mapping Rules dengan Transaction Type
                    <b>SUPPLIER_PAYMENT</b>. Bila mapping AP belum tersedia, proses dibatalkan.
                    Akun ini memakai tabel <b>accounts</b> Journal Engine, bukan kolom Account Kas/Bank di
                    form pembayaran yang menunjuk ke tabel <b>coa</b> legacy. Setelah diposting, jurnal
                    bersifat immutable.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="VendorBill.confirmPostJurnal(this)"
                    data_id="{{ ! empty($id) ? $id : '' }}">Ya</button>
                <button class="btn btn-default" data-bs-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
</div>
