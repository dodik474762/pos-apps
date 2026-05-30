<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ProductPromoItem extends Model
{
    protected $table = 'product_promo_item';

    public function promoProducts(){
        return $this->hasMany(ProductPromoItemDetail::class, 'product_promo_item', 'id');
    }
    
    public function promoFree(){
        return $this->hasMany(ProductPromoItemFree::class, 'product_promo_item', 'id');
    }

    public function promoSyarat(){
        return $this->hasMany(ProductPromoItemSyarat::class, 'product_promo_item', 'id');
    }
}
