<?php

namespace App\Models\Accounting;

use App\Models\Master\Accounts;
use App\Models\Transaction\AccountingPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountPeriodBalance extends Model
{
    use HasFactory;

    protected $table = 'account_period_balances';

    protected $fillable = [
        'account_id',
        'accounting_period_id',
        'opening_balance',
        'total_debit',
        'total_credit',
        'closing_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id');
    }
}
