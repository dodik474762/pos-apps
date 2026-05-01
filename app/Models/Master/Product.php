<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    public function uom()
    {
        return $this->hasMany(ProductUom::class, 'product', 'id')->whereNull('deleted')->orderBy('level');
    }

    public function uomFromLarge()
    {
        return $this->hasMany(ProductUom::class, 'product', 'id')->whereNull('deleted')->orderBy('level', 'desc');
    }
}
