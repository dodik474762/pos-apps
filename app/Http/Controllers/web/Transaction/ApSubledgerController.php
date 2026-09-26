<?php

namespace App\Http\Controllers\web\Transaction;

use App\Http\Controllers\api\Transaction\ApSubledgerController as ApiApSubledgerController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Master\Vendor;

class ApSubledgerController extends Controller
{
    public $akses_menu = [];

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->akses_menu = json_decode(session('akses_menu'));
    }

    public function getHeaderCss()
    {
        return array(
            'js-1' => asset('assets/js/controllers/transaction/ap_subledger.js'),
            'js-2' => asset('assets/js/controllers/notification.js'),
        );
    }

    public function getTitleParent()
    {
        return "Transaksi";
    }

    public function getTableName()
    {
        return "ap_invoices";
    }

    public function getTitle()
    {
        return "AP Subledger";
    }

    public function index()
    {
        $data['data'] = [];
        $data['title'] = $this->getTitle();
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['akses_session'] = session('akses_session');
        $data['suppliers'] = Vendor::whereNull('deleted')
            ->orderBy('nama_vendor')
            ->get(['id', 'code', 'nama_vendor'])
            ->toArray();

        $view = view('web.ap_subledger.index', $data);

        $put['title_content'] = $this->getTitle();
        $put['title_top'] = $this->getTitle();
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }

    public function supplierLedger(Request $request)
    {
        $data['title'] = 'AP Subledger - Supplier Ledger';
        $data['title_parent'] = $this->getTitleParent();
        $data['akses'] = $this->akses_menu;
        $data['akses_session'] = session('akses_session');
        $data['supplier'] = \App\Models\Master\Vendor::where('id', $request->supplier_id)
            ->whereNull('deleted')
            ->first();
        $data['start_date'] = $request->start_date;
        $data['end_date'] = $request->end_date;

        if (empty($data['supplier'])) {
            return redirect()->route('ap-subledger-index')
                ->with('error', 'Supplier tidak ditemukan.');
        }

        $supplierId = $data['supplier']->id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $invoices = DB::table('ap_invoices')
            ->where('supplier_id', $supplierId)
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('invoice_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('invoice_date', '<=', $endDate))
            ->get();

        $payments = DB::table('ap_payments')
            ->where('supplier_id', $supplierId)
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('payment_date', '<=', $endDate))
            ->get();

        $creditNotes = DB::table('ap_credit_notes')
            ->where('supplier_id', $supplierId)
            ->whereNull('deleted_at')
            ->when($startDate, fn($q) => $q->where('credit_note_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('credit_note_date', '<=', $endDate))
            ->get();

        $data['totalInvoices'] = $invoices->sum('invoice_amount');
        $data['totalPayments'] = $payments->sum('amount');
        $data['totalCreditNotes'] = $creditNotes->sum('amount');
        $data['outstanding'] = $data['totalInvoices'] - $data['totalPayments'] - $data['totalCreditNotes'];

        $view = view('web.ap_subledger.ledger', $data);

        $put['title_content'] = $this->getTitle();
        $put['title_top'] = 'Supplier Ledger';
        $put['title_parent'] = $this->getTitleParent();
        $put['view_file'] = $view;
        $put['header_data'] = $this->getHeaderCss();

        return view('web.template.main', $put);
    }
}