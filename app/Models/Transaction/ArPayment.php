<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ArPayment extends Model
{
    protected $table = 'ar_payments';

    protected $fillable = [
        'customer_payment_id',
        'customer_id',
        'payment_date',
        'amount',
        'allocated_amount',
        'status',
    ];

    protected $casts = [
        'customer_payment_id' => 'integer',
        'customer_id' => 'integer',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];
}
