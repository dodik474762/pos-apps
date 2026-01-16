<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class PackingListReturn extends Model
{
    protected $table = 'packing_list_sales_return';

    public function detail(){
        return $this->hasMany(PackingListReturnDtl::class , 'packing_list_id', 'packing_list_id');
    }
}
