<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class GoodReceipt extends Model
{
    protected $table = 'goods_receipt_header';

    public function po(){
        return $this->hasOne(PurchaseOrder::class, 'id', 'purchase_order');
    }

    public function items(){
        return $this->hasMany(GoodReceiptDtl::class, 'goods_receipt_header', 'id');
    }
}
