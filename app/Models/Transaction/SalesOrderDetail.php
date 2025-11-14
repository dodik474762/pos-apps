<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use App\Models\Master\Unit;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    protected $table = 'sales_order_details';

     public function products(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function units(){
        return $this->hasOne(Unit::class, 'id', 'unit');
    }
}
