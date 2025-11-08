<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class VendorBillHeader extends Model
{
    protected $table = 'vendor_payment_header';

    public function details(){
        return $this->hasMany(VendorBillDtl::class, 'vendor_payment_id', 'id')->whereNull('deleted');
    }
}
