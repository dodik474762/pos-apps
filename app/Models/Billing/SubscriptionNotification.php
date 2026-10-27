<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class SubscriptionNotification extends Model
{
    protected $table = 'subscription_notifications';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'type',
        'sent_on',
        'channel',
        'recipient',
        'status',
        'error',
    ];

    protected $casts = [
        'sent_on' => 'date',
    ];
}
