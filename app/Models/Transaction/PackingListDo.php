<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class PackingListDo extends Model
{
    protected $table = 'packing_list_do';

    public function detail(){
        return $this->hasMany(PackingListDtl::class, 'packing_list_id', 'packing_list_id');
    }
}
