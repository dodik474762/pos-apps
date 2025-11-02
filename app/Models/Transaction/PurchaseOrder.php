<?php

namespace App\Models\Transaction;

use App\Models\Master\Vendor;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';

    public function vendors(){
        return $this->hasOne(Vendor::class, 'id', 'vendor');
    }

    public function warehouses(){
        return $this->hasOne(Warehouse::class, 'id', 'warehouse');
    }

    public function items(){
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order', 'id');
    }
}
