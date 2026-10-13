<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class StockCustomer extends Model
{
    protected $fillable = [
        'customer',
        'product_id',
        'qty',
        'unit',
        'unit_price',
        'discount_type',
        'is_free_good',
        'status',
        'created_by',
        'foto_path',
        'toko_tutup',
        'company_id',
    ];
    protected $table = 'stock_customer';
}
