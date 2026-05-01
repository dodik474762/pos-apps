<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class ProductUom extends Model
{
    protected $table = 'product_uom';

    public function units()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_tujuan');
    }
}
