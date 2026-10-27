<?php

return [
    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 14),
    'grace_days' => (int) env('BILLING_GRACE_DAYS', 7),
    'invoice_lead_days' => (int) env('BILLING_INVOICE_LEAD_DAYS', 7),
    'timezone' => env('BILLING_TIMEZONE', 'Asia/Jakarta'),
    'currency' => env('BILLING_CURRENCY', 'IDR'),
    'tax_percent' => (float) env('BILLING_TAX_PERCENT', 0),

    'default_plan' => env('BILLING_DEFAULT_PLAN', 'starter'),

    'enforcement' => [
        'enabled' => filter_var(env('BILLING_ENFORCE', false), FILTER_VALIDATE_BOOLEAN),
        'log_only' => filter_var(env('BILLING_ENFORCE_LOG_ONLY', true), FILTER_VALIDATE_BOOLEAN),
        'quota' => filter_var(env('BILLING_ENFORCE_QUOTA', false), FILTER_VALIDATE_BOOLEAN),
        'features' => filter_var(env('BILLING_ENFORCE_FEATURES', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'cache' => [
        'subscription_ttl' => (int) env('BILLING_CACHE_TTL', 60),
    ],

    'impersonate' => [
        'session_minutes' => (int) env('BILLING_IMPERSONATE_MINUTES', 30),
    ],

    'gateway' => [
        'default' => env('BILLING_GATEWAY', 'manual'),
        'webhook_secret' => env('BILLING_WEBHOOK_SECRET', ''),
        'signature_header' => env('BILLING_SIGNATURE_HEADER', 'X-Billing-Signature'),
        'event_header' => env('BILLING_EVENT_HEADER', 'X-Event-Id'),
        'manual_payment_url' => env('BILLING_MANUAL_PAYMENT_URL', '/billing/invoices'),
        'allow_manual_confirm' => filter_var(env('BILLING_ALLOW_MANUAL_CONFIRM', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)), FILTER_VALIDATE_BOOLEAN),
    ],

    'notifications' => [
        'channels' => array_filter(explode(',', env('BILLING_NOTIFICATION_CHANNELS', 'mail,log'))),
        'reminder_days' => [7, 3, 0],
        'trial_reminder_days' => [3, 0],
        'whatsapp' => [
            'provider' => env('BILLING_WA_PROVIDER', 'log'),
            'endpoint' => env('BILLING_WA_ENDPOINT'),
            'token' => env('BILLING_WA_TOKEN'),
            'sender' => env('BILLING_WA_SENDER'),
        ],
    ],

    'quota' => [
        'users' => [
            'table' => 'users',
            'label' => 'Users',
            'scope' => 'company',
        ],
        'warehouses' => [
            'table' => 'warehouse',
            'label' => 'Gudang',
            'scope' => 'company',
        ],
        'invoices_per_month' => [
            'table' => 'sales_invoice_header',
            'label' => 'Invoice per bulan',
            'scope' => 'period',
            'date_column' => 'invoice_date',
        ],
    ],
];
