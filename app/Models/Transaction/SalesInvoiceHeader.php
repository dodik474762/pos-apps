<?php

namespace App\Models\Transaction;

use App\Models\Master\Customer;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceHeader extends Model
{
    protected $table = 'sales_invoice_header';


    public function do(){
        return $this->hasOne(DeliveryOrderHeader::class, 'id', 'do_id');
    }

    public function customers(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function warehouses(){
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function items(){
        return $this->hasMany(SalesInvoiceDtl::class, 'invoice_id', 'id')->whereNull('deleted');
    }
}
