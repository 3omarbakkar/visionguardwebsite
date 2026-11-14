<?php
// Legacy card checkout is disabled. The storefront currently supports COD, InstaPay and wallet transfers only.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
http_response_code(410);
echo json_encode(['ok' => false, 'error' => 'card_payments_disabled'], JSON_UNESCAPED_UNICODE);
