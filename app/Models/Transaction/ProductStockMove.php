<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Model;

class ProductStockMove extends Model
{
    protected $table = 'product_stock_move';

    public function products()
    {
        return $this->hasOne(Product::class, 'id', 'product');
    }
}
