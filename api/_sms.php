<?php
// Minimal SMS sender for the password-reset one-time code.
// Configuration order: environment variables, then api/sms-config.php.
// api/sms-config.php is blocked from web access by the root .htaccess.
//
// Supported drivers:
//   twilio : sid, token, from (or messaging_service_sid)
//   http   : generic provider, configurable URL/method/body/headers

function vg_sms_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $cfg = ['driver' => '', 'timeout' => 10, 'success_contains' => ''];
    $path = (string)(getenv('VG_SMS_CONFIG') ?: (__DIR__ . '/sms-config.php'));
    if ($path !== '' && is_file($path)) {
        $loaded = include $path;
        if (is_array($loaded)) $cfg = array_merge($cfg, $loaded);
    }
    $map = [
        'driver' => 'DRIVER', 'sid' => 'SID', 'token' => 'TOKEN', 'from' => 'FROM',
        'messaging_service_sid' => 'MESSAGING_SERVICE_SID', 'url' => 'URL',
        'method' => 'METHOD', 'body' => 'BODY', 'headers' => 'HEADERS',
        'key' => 'KEY', 'success_contains' => 'SUCCESS_CONTAINS',
    ];
    foreach ($map as $key => $env) {
        $v = getenv('VG_SMS_' . $env);
        if ($v !== false && $v !== '') $cfg[$key] = $v;
    }
    return $cfg;
}

function vg_sms_enabled(): bool {
    $c = vg_sms_config();
    $driver = strtolower(trim((string)($c['driver'] ?? '')));
    if ($driver === 'twilio') {
        return trim((string)($c['sid'] ?? '')) !== '' && trim((string)($c['token'] ?? '')) !== ''
            && (trim((string)($c['from'] ?? '')) !== '' || trim((string)($c['messaging_service_sid'] ?? '')) !== '');
    }
    if ($driver === 'http') {
        return trim((string)($c['url'] ?? '')) !== '';
    }
    return false;
}

/** Egyptian local number (01xxxxxxxxx) to E.164 (+201xxxxxxxxx). */
function vg_sms_intl(string $phone): string {
    $p = preg_replace('/\D+/', '', $phone);
    if (strpos($p, '0020') === 0) $p = substr($p, 4);
    elseif (strpos($p, '20') === 0 && strlen($p) === 12) $p = substr($p, 2);
    if (strlen($p) === 10 && $p[0] === '1') $p = '0' . $p;
    return '+20' . ltrim($p, '0');
}

function vg_sms_send(string $phone, string $text): bool {
    if (!vg_sms_enabled()) return false;
    $c = vg_sms_config();
    $driver = strtolower(trim((string)$c['driver']));
    $intl = vg_sms_intl($phone);
    $timeout = max(3, min(20, (int)($c['timeout'] ?? 10)));

    $ch = curl_init();
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => min(6, $timeout),
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => 'VisionGuard/1.0',
    ];

    if ($driver === 'twilio') {
        $sid = (string)$c['sid'];
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($sid) . '/Messages.json';
        $fields = ['To' => $intl, 'Body' => $text];
        if (trim((string)($c['messaging_service_sid'] ?? '')) !== '') {
            $fields['MessagingServiceSid'] = (string)$c['messaging_service_sid'];
        } else {
            $fields['From'] = (string)$c['from'];
        }
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($fields, '', '&');
        $opts[CURLOPT_USERPWD] = $sid . ':' . (string)$c['token'];
    } else {
        $replace = [
            '{phone}' => $phone,
            '{phone_intl}' => $intl,
            '{phone_digits}' => preg_replace('/\D+/', '', $intl),
            '{text}' => $text,
            '{text_url}' => rawurlencode($text),
            '{key}' => (string)($c['key'] ?? ''),
        ];
        $url = strtr((string)$c['url'], $replace);
        $method = strtoupper(trim((string)($c['method'] ?? 'POST')));
        $body = strtr((string)($c['body'] ?? ''), $replace);
        $headers = [];
        $rawHeaders = $c['headers'] ?? '';
        if (is_array($rawHeaders)) {
            foreach ($rawHeaders as $h) $headers[] = strtr((string)$h, $replace);
        } elseif (is_string($rawHeaders) && trim($rawHeaders) !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $rawHeaders) as $h) {
                $h = trim($h);
                if ($h !== '') $headers[] = strtr($h, $replace);
            }
        }
        if ($body !== '' && !preg_grep('/^content-type:/i', $headers)) {
            $headers[] = (substr(ltrim($body), 0, 1) === '{') ? 'Content-Type: application/json' : 'Content-Type: application/x-www-form-urlencoded';
        }
        if ($headers) $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_URL] = $url;
        if ($method === 'GET') {
            $opts[CURLOPT_HTTPGET] = true;
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            $opts[CURLOPT_CUSTOMREQUEST] = $method;
            $opts[CURLOPT_POSTFIELDS] = $body;
        } else {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $body;
        }
    }

    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    $ok = $resp !== false && $status >= 200 && $status < 300;
    $needle = trim((string)($c['success_contains'] ?? ''));
    if ($ok && $needle !== '' && strpos((string)$resp, $needle) === false) $ok = false;
    if (!$ok) {
        error_log('Vision Guard SMS failed: driver=' . $driver . ' http=' . $status . ' curl=' . $err);
    }
    return $ok;
}

function vg_sms_reset_text(string $code): string {
    return 'Vision Guard: كود استعادة كلمة المرور ' . $code . '. صالح 10 دقائق. متشاركوش مع حد.';
}
