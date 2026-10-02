<?php

return [
    'batch_size' => (int) env('IMPORT_BATCH_SIZE', 1000),
    'preview_rows' => (int) env('IMPORT_PREVIEW_ROWS', 5),
    'headers' => [
        'external_id', 'created_at', 'first_name', 'last_name', 'phone',
        'email', 'city', 'source', 'utm_campaign', 'product', 'budget_uah',
        'status', 'manager', 'comment', 'next_contact_at',
    ],
];
