<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ApInvoice extends Model
{
    protected $table = 'ap_invoices';

    protected $fillable = [
        'purchase_invoice_id',
        'supplier_id',
        'invoice_no',
        'invoice_date',
        'due_date',
        'invoice_amount',
        'paid_amount',
        'outstanding_amount',
        'status',
    ];

    protected $casts = [
        'purchase_invoice_id' => 'integer',
        'supplier_id' => 'integer',
        'invoice_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];
}