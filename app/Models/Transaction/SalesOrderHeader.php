<?php

namespace App\Models\Transaction;

use App\Models\Master\Customer;
use Illuminate\Database\Eloquent\Model;

class SalesOrderHeader extends Model
{
    protected $table = 'sales_order_headers';

    public function customers(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function items(){
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_id', 'id')->whereNull('deleted');
    }
}
