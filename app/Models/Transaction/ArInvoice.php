<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ArInvoice extends Model
{
    protected $table = 'ar_invoices';

    protected $fillable = [
        'sales_invoice_id',
        'customer_id',
        'invoice_no',
        'invoice_date',
        'due_date',
        'invoice_amount',
        'paid_amount',
        'outstanding_amount',
        'status',
    ];

    protected $casts = [
        'sales_invoice_id' => 'integer',
        'customer_id' => 'integer',
        'invoice_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];
}
