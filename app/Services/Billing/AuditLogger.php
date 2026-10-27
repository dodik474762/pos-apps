<?php

namespace App\Services\Billing;

use App\Models\Billing\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(
        string $action,
        ?int $companyId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $reason = null,
        ?array $before = null,
        ?array $after = null,
        ?Request $request = null
    ): AuditLog {
        $request = $request ?: request();
        $impersonatedBy = session('impersonated_by');

        return AuditLog::create([
            'company_id' => $companyId,
            'actor_id' => session('user_id'),
            'actor_name' => session('username') ?: session('nama_lengkap'),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'reason' => $reason,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request?->ip(),
            'is_impersonated' => $impersonatedBy !== null,
            'impersonated_by' => $impersonatedBy,
        ]);
    }
}
