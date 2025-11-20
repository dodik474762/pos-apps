<?php

namespace App\Models\Transaction;

use App\Models\Master\Customer;
use Illuminate\Database\Eloquent\Model;

class SalesPaymentHeader extends Model
{
    protected $table = 'sales_payment_header';

    public function customers(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function items(){
        return $this->hasMany(SalesPaymentDtl::class, 'payment_id', 'id')->whereNull('deleted');
    }
}
