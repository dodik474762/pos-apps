<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master\AccountMapping;
use App\Models\Master\Currency;
use App\Models\Transaction\ReceivePaymentDetail;
use App\Models\Transaction\ReceivePaymentHeader;
use App\Models\Transaction\SalesOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerimaUangController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function submit(Request $request)
    {
        $data = $request->all();
        $userId = session('user_id');
        $result = ['is_valid' => false];


        DB::beginTransaction();
        try {

            $kasBesar = AccountMapping::where('module', 'RECEIVED_CASH')
                ->where('account_type', 'kas besar')
                ->where('remarks', $data['type'])
                ->with('account') // kalau kamu pakai relasi
                ->first();

            $terimaUangAcc = AccountMapping::where('module', 'RECEIVED_CASH')
                ->where('account_type', $data['type'] == 'pending' ? 'penerimaan uang' : 'bank')
                ->where('remarks', $data['type'])
                ->with('account')
                ->first();


            if (!$kasBesar || !$terimaUangAcc) {
                DB::rollBack();

                return response()->json([
                    'is_valid' => false,
                    'message' => 'Konfigurasi akun untuk Terima Uang belum lengkap.',
                ]);
            }

            $exisReceived = ReceivePaymentHeader::where('salesman', $data['salesman'])->where('visit_date', $data['visit_date'])->first();
            $header = empty($exisReceived) ? new ReceivePaymentHeader() : $exisReceived;

            if (empty($exisReceived)) {
                $header->code = generateNoReceivedCashier();
                $header->created_by = $userId;
                $header->status = 'PENDING';
            } else {
                if ($header->status == 'POSTED') {
                    DB::rollBack();
                    return response()->json([
                        'is_valid' => false,
                        'message' => 'Data sudah di posting',
                    ]);
                }
                $header->status = strtoupper($data['type']);
            }


            $header->receive_date = date('Y-m-d');
            $header->salesman = $data['salesman'];
            $header->visit_date = $data['visit_date'];
            $header->total_amount = 0;
            $header->type_trans = 'salesman';
            $header->save();

            $hdrId = $header->id;


            $total_amount = 0;

            ReceivePaymentDetail::where('receive_id', $hdrId)->delete();

            $line = 1;
            foreach ($data['items'] as $key => $value) {
                $value['amount_paid'] = $value['amount_paid'] == '' ? 0 : $value['amount_paid'];
                $detail = new ReceivePaymentDetail();
                $detail->receive_id = $hdrId;
                $detail->invoice_id = $value['invoice_id'];
                $detail->amount_paid = $value['amount_paid'];
                $detail->line_no = $line++;
                $detail->save();

                $total_amount += $value['amount_paid'];
            }

            $header->total_amount = $total_amount;
            $header->save();

            $reference = $header->code;
            if (!empty($exisReceived)) {
                if ($data['type'] == 'pending') {
                    cancelAllGL($reference);
                }
            }

            $currency = Currency::where('code', 'IDR')->first();
            $currencyId = $currency->id;
            postingGL($reference, $kasBesar->account_id, $kasBesar->account->account_name, $kasBesar->cd, ($total_amount), $currencyId);
            postingGL($reference, $terimaUangAcc->account_id, $terimaUangAcc->account->account_name, $terimaUangAcc->cd, ($total_amount), $currencyId);

            DB::commit();
            $result['is_valid'] = true;
            $result['message'] = 'Terima Uang berhasil disimpan';
            $result['so_id'] = $hdrId;
        } catch (\Throwable $th) {
            DB::rollBack();
            $result['is_valid'] = false;
            $result['message'] = $th->getMessage();
        }

        return response()->json($result);
    }
}
