<?php

namespace App\Models\Transaction;

use App\Models\Master\Vendor;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceHeader extends Model
{
    protected $table = 'purchase_invoice_header';

    public function vendors(){
        return $this->hasOne(Vendor::class, 'id', 'vendor');
    }

    public function items(){
        return $this->hasMany(PurchaseInvoiceDtl::class, 'purchase_invoice_id', 'id');
    }
}
