<?php

namespace App\Services\Billing;

use RuntimeException;

class QuotaExceededException extends RuntimeException
{
    public function __construct(
        public readonly string $quotaKey,
        public readonly int $used,
        public readonly int $limit,
        string $message
    ) {
        parent::__construct($message);
    }
}
