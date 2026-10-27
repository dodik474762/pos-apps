<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class SubscriptionEvent extends Model
{
    protected $table = 'subscription_events';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'from_status',
        'to_status',
        'reason',
        'actor',
        'actor_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
