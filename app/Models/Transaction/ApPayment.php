<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ApPayment extends Model
{
    protected $table = 'ap_payments';

    protected $fillable = [
        'supplier_payment_id',
        'supplier_id',
        'payment_date',
        'amount',
        'allocated_amount',
        'status',
    ];

    protected $casts = [
        'supplier_payment_id' => 'integer',
        'supplier_id' => 'integer',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];
}