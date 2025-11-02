<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use App\Models\Master\Unit;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $table = 'purchase_order_detail';

    public function products(){
        return $this->hasOne(Product::class, 'id', 'product');
    }

    public function units(){
        return $this->hasOne(Unit::class, 'id', 'unit');
    }
}
