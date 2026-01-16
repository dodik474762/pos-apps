<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class SalesReturnDtl extends Model
{
    protected $table = 'sales_return_detail';

    public function invoice(){
        return $this->hasOne(SalesInvoiceDtl::class, 'id', 'invoice_detail_id');
    }
}
