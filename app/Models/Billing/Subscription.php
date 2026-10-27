<?php

namespace App\Models\Billing;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use BelongsToCompany;

    public const STATUS_TRIAL = 'TRIAL';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_PAST_DUE = 'PAST_DUE';
    public const STATUS_SUSPENDED = 'SUSPENDED';
    public const STATUS_CANCELED = 'CANCELED';

    public const CYCLE_MONTHLY = 'MONTHLY';
    public const CYCLE_YEARLY = 'YEARLY';

    protected $table = 'subscriptions';

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'billing_cycle',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'grace_ends_at',
        'canceled_at',
        'cancel_at_period_end',
        'pending_plan_id',
        'pending_billing_cycle',
        'pending_effective_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'grace_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'pending_effective_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function pendingPlan()
    {
        return $this->belongsTo(Plan::class, 'pending_plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function events()
    {
        return $this->hasMany(SubscriptionEvent::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_TRIAL => 'Trial',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_PAST_DUE => 'Jatuh Tempo',
            self::STATUS_SUSPENDED => 'Ditangguhkan',
            self::STATUS_CANCELED => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, [self::STATUS_SUSPENDED, self::STATUS_CANCELED], true);
    }

    public function hasFullAccess(): bool
    {
        return in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE, self::STATUS_PAST_DUE], true);
    }
}
