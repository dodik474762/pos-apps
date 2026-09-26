<?php

namespace App\Http\Controllers\api\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArSubledgerController extends Controller
{
    public function getTableName()
    {
        return "ar_invoices";
    }

    public function getData(Request $request)
    {
        $data['data'] = [];
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;

        $datadb = DB::table('ar_invoices as ai')
            ->select([
                'ai.*',
                'c.code as customer_code',
                'c.nama_customer',
            ])
            ->join('customer as c', 'c.id', 'ai.customer_id')
            ->whereNull('ai.deleted_at')
            ->whereNull('c.deleted')
            ->orderBy('ai.invoice_date', 'desc')
            ->orderBy('ai.id', 'desc');

        if ($request->filled('customer_id')) {
            $datadb->where('ai.customer_id', $request->customer_id);
        }

        if ($request->filled('start_date')) {
            $datadb->where('ai.invoice_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $datadb->where('ai.invoice_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $datadb->where('ai.status', $request->status);
        }

        $data['recordsTotal'] = DB::table('ar_invoices')->whereNull('deleted_at')->count();

        if ($request->filled('search.value')) {
            $keyword = $request->input('search.value');
            $datadb->where(function ($query) use ($keyword) {
                $query->where('ai.invoice_no', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('c.code', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('c.nama_customer', 'LIKE', '%' . $keyword . '%');
                $query->orWhere('ai.status', 'LIKE', '%' . $keyword . '%');
            });
        }

        $data['recordsFiltered'] = (clone $datadb)->count();

        if ($request->filled('order.0.column')) {
            $columns = ['ai.id', 'ai.invoice_no', 'c.nama_customer', 'ai.invoice_date', 'ai.due_date', 'ai.invoice_amount', 'ai.outstanding_amount', 'ai.status'];
            $colIndex = (int) $request->input('order.0.column');
            $dir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
            if (isset($columns[$colIndex])) {
                $datadb->orderBy($columns[$colIndex], $dir);
            }
        }

        if ($request->filled('length')) {
            $datadb->limit((int) $request->length);
        }
        if ($request->filled('start')) {
            $datadb->offset((int) $request->start);
        }

        $data['data'] = $datadb->get()->toArray();
        $data['draw'] = (int) $request->input('draw', 1);

        // Tambah detail_url ke setiap row untuk frontend
        foreach ($data['data'] as &$row) {
            $row->detail_url = url('transaksi/ar_subledger/customer-ledger') . '?customer_id=' . $row->customer_id;
        }

        return response()->json($data);
    }

    public function getCustomerList(Request $request)
    {
        $customers = DB::table('customer')
            ->whereNull('deleted')
            ->orderBy('nama_customer')
            ->get(['id', 'code', 'nama_customer']);

        return response()->json(['data' => $customers]);
    }

    public function getLedgerData(Request $request)
    {
        $customerId = $request->input('customer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (empty($customerId)) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'draw' => (int) $request->input('draw', 1),
            ]);
        }

        $invoices = DB::table('ar_invoices')
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('invoice_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('invoice_date', '<=', $endDate))
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $payments = DB::table('ar_payments')
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('payment_date', '<=', $endDate))
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $creditNotes = DB::table('ar_credit_notes')
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('credit_note_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('credit_note_date', '<=', $endDate))
            ->orderBy('credit_note_date')
            ->orderBy('id')
            ->get();

        $ledger = collect();

        foreach ($invoices as $inv) {
            $ledger->push([
                'date' => $inv->invoice_date,
                'type' => 'INVOICE',
                'ref_no' => $inv->invoice_no,
                'debit' => (float) $inv->invoice_amount,
                'credit' => 0,
                'balance' => 0,
                'status' => $inv->status,
                'outstanding' => (float) $inv->outstanding_amount,
                'id' => $inv->id,
            ]);
        }

        foreach ($payments as $pay) {
            $ledger->push([
                'date' => $pay->payment_date,
                'type' => 'PAYMENT',
                'ref_no' => $pay->id,
                'debit' => 0,
                'credit' => (float) $pay->amount,
                'balance' => 0,
                'status' => $pay->status,
                'allocated' => (float) $pay->allocated_amount,
                'id' => $pay->id,
            ]);
        }

        foreach ($creditNotes as $cn) {
            $ledger->push([
                'date' => $cn->credit_note_date,
                'type' => 'CREDIT_NOTE',
                'ref_no' => $cn->id,
                'debit' => 0,
                'credit' => (float) $cn->amount,
                'balance' => 0,
                'status' => $cn->status,
                'allocated' => (float) $cn->allocated_amount,
                'id' => $cn->id,
            ]);
        }

        $ledger = $ledger->sortBy(function ($row) {
            return $row['date'] . '_' . ($row['type'] === 'INVOICE' ? '1' : '2') . '_' . $row['id'];
        })->values()->toArray();

        $balance = 0;
        foreach ($ledger as $i => $row) {
            $balance += $row['debit'] - $row['credit'];
            $ledger[$i]['balance'] = $balance;
        }

        $data = $ledger;
        $total = count($data);

        return response()->json([
            'data' => $data,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'draw' => (int) $request->input('draw', 1),
        ]);
    }

    public function getDetailData($id)
    {
        $datadb = DB::table('ar_invoices as ai')
            ->select([
                'ai.*',
                'c.code as customer_code',
                'c.nama_customer',
            ])
            ->join('customer as c', 'c.id', 'ai.customer_id')
            ->where('ai.id', $id)
            ->whereNull('ai.deleted_at')
            ->whereNull('c.deleted')
            ->first();

        return response()->json($datadb);
    }
}