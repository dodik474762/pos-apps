<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'code',
        'name',
        'price_monthly',
        'price_yearly',
        'limits',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'limits' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function priceFor(string $cycle): float
    {
        return (float) ($cycle === Subscription::CYCLE_YEARLY ? $this->price_yearly : $this->price_monthly);
    }

    public function limit(string $key)
    {
        $limits = $this->limits ?? [];

        return array_key_exists($key, $limits) ? $limits[$key] : null;
    }

    public function hasFeature(string $feature): bool
    {
        if (in_array('*', $this->features ?? [], true)) {
            return true;
        }

        return in_array($feature, $this->features ?? [], true);
    }
}
