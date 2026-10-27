<?php

namespace App\Models\Billing;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    use BelongsToCompany;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_VOID = 'VOID';

    protected $table = 'subscription_invoices';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'number',
        'amount',
        'period_start',
        'period_end',
        'due_date',
        'status',
        'gateway',
        'gateway_reference',
        'payment_url',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_VOID => 'Dibatalkan',
            default => $this->status,
        };
    }
}
