<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Model;

class PackingListReturnDtl extends Model
{
    protected $table = 'packing_list_sales_return_detail';

     public function product(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function returnDetail(){
        return $this->hasOne(SalesReturnDtl::class, 'id', 'sales_return_detail_id');
    }
}
