<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class ProductDisc extends Model
{
    protected $table = 'product_discount';

    public function scopeValid($query, $productId, $unitId, $customerId, $customerCategory, $qty)
    {
        return $query->where('product', $productId)
            ->where('unit', $unitId)
            ->where(function ($q) use ($customerId) {
                $q->where('customer', $customerId)->orWhereNull('customer');
            })
            ->where(function ($q) use ($customerCategory) {
                $q->where('customer_category', $customerCategory)->orWhereNull('customer_category');
            })
            ->where(function ($q) use ($qty) {
                $q->where('min_qty', '<=', $qty)
                    ->where(function ($q2) use ($qty) {
                        $q2->where('max_qty', '>=', $qty)->orWhereNull('max_qty');
                    });
            })
            ->where('status_aktif', 1)
            ->whereDate('date_start', '<=', now())
            ->where(function ($q) {
                $q->whereDate('date_end', '>=', now())->orWhereNull('date_end');
            });
    }
}
