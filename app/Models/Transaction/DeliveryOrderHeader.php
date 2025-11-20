<?php

namespace App\Models\Transaction;

use App\Models\Master\Customer;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderHeader extends Model
{
    protected $table = 'delivery_order_header';

    public function so(){
        return $this->hasOne(SalesOrderHeader::class, 'id', 'so_id');
    }

    public function customers(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function warehouses(){
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function items(){
        return $this->hasMany(DeliveryOrderDtl::class, 'do_id', 'id')->whereNull('deleted');
    }
}
