<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class CustomerLimitTop extends Model
{
    //
    protected $table = 'customer_limit_top';

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer');
    }
}
