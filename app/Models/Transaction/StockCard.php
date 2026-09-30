<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class StockCard extends Model
{
    protected $table = 'stock_cards';

    protected $fillable = [
        'item_code',
        'opening_balance',
        'opening_value',
        'qty_in',
        'qty_out',
        'qty_adjust',
        'qty_transfer_out',
        'qty_transfer_in',
        'qty_return_in',
        'trans_date',
        'closing_balance',
        'reference_type',
        'reference_id',
        'wh_code',
        'note',
        'type_stock',
        'value_in',
        'value_out',
        'nominal_value',
        'closing_value',
        'avg_cost',
        'unit_cost',
        'cost_source'
    ];
}
