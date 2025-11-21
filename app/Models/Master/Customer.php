<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $table = 'customer';

    public function top(){
        return $this->hasOne(TermOfPayment::class, 'id', 'payment_terms');
    }
}
