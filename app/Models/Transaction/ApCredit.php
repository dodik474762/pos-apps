<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ApCredit extends Model
{
    protected $table = 'ap_credit_notes';

    protected $fillable = [
        'purchase_return_id',
        'supplier_id',
        'credit_note_date',
        'amount',
        'allocated_amount',
        'status',
    ];

    protected $casts = [
        'purchase_return_id' => 'integer',
        'supplier_id' => 'integer',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'credit_note_date' => 'date',
    ];
}