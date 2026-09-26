<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ApAllocation extends Model
{
    protected $table = 'ap_allocations';

    protected $fillable = [
        'source_type',
        'source_id',
        'ap_invoice_id',
        'allocation_date',
        'allocated_amount',
        'status',
        'reversed_at',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'ap_invoice_id' => 'integer',
        'allocated_amount' => 'decimal:2',
        'allocation_date' => 'date',
        'reversed_at' => 'datetime',
    ];
}