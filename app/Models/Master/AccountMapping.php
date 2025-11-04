<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class AccountMapping extends Model
{
    protected $table = 'account_mappings';

    public function account(){
        return $this->hasOne(Coa::class, 'id', 'account_id');
    }
}
