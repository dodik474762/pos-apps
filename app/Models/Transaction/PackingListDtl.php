<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Model;

class PackingListDtl extends Model
{
    protected $table = 'packing_list_detail';

    public function product(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function deliveryDetail(){
        return $this->hasOne(DeliveryOrderDtl::class, 'id', 'delivery_detail_id');
    }
}
