<?php

namespace App\Models\Transaction;

use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDtl extends Model
{
    protected $table = 'sales_invoice_detail';

    public function products(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function so_detail(){
        return $this->hasOne(SalesOrderDetail::class, 'id', 'so_detail_id');
    }
}
