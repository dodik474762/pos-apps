<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'company_id',
        'actor_id',
        'actor_name',
        'action',
        'subject_type',
        'subject_id',
        'reason',
        'before',
        'after',
        'ip_address',
        'is_impersonated',
        'impersonated_by',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'is_impersonated' => 'boolean',
    ];
}
