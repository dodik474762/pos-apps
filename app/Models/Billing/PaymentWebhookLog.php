<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    protected $table = 'payment_webhook_logs';

    protected $fillable = [
        'gateway',
        'event_id',
        'payload',
        'signature_valid',
        'status',
        'error',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
