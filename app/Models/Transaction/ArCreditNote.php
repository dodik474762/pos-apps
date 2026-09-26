<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ArCreditNote extends Model
{
    protected $table = 'ar_credit_notes';

    protected $fillable = [
        'sales_return_id',
        'customer_id',
        'credit_note_date',
        'amount',
        'allocated_amount',
        'status',
    ];

    protected $casts = [
        'sales_return_id' => 'integer',
        'customer_id' => 'integer',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'credit_note_date' => 'date',
    ];
}
