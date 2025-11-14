<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use App\Models\Master\Unit;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderDtl extends Model
{
    protected $table = 'delivery_order_detail';

    public function products(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function units(){
        return $this->hasOne(Unit::class, 'id', 'uom');
    }
}
