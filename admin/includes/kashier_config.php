<?php
// Kashier payment gateway config (card checkout is currently disabled - see api/kashier_*.php,
// which return HTTP 410, and nothing includes this file).
// Secrets must NOT live in the repository. When card payments are re-enabled, provide the
// values through environment variables (cPanel > Setup Python/PHP App, or SetEnv in a
// private file outside the web root) and rotate any key that was ever committed.
return [
    'merchant_id'     => (string)(getenv('VG_KASHIER_MERCHANT_ID') ?: ''),
    'payment_api_key' => (string)(getenv('VG_KASHIER_API_KEY') ?: ''),
    'secret_key'      => (string)(getenv('VG_KASHIER_SECRET_KEY') ?: ''),
    'mode'            => (string)(getenv('VG_KASHIER_MODE') ?: 'test'),
    'currency'        => 'EGP',
];
