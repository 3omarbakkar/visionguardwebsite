<?php
// Minimal Web Push (RFC 8291 aes128gcm + RFC 8292 VAPID), no external libraries. Needs PHP 7.3+ with openssl and curl.

function wp_b64u($s) { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function wp_b64d($s) { return base64_decode(strtr($s, '-_', '+/') . str_repeat('=', (4 - strlen($s) % 4) % 4)); }

function wp_raw_public($pkey) {
    $d = openssl_pkey_get_details($pkey);
    return "\x04" . str_pad($d['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['ec']['y'], 32, "\0", STR_PAD_LEFT);
}

function wp_new_keypair() {
    $k = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
    openssl_pkey_export($k, $pem);
    return ['pem' => $pem, 'public' => wp_b64u(wp_raw_public($k))];
}

function wp_pub_to_pem($raw) {
    $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $raw;
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

function wp_der_to_raw($der) {
    // ECDSA DER -> r||s (64 bytes)
    $o = 2; // 0x30 len
    if (ord($der[1]) & 0x80) { $o = 2 + (ord($der[1]) & 0x7f); }
    $rl = ord($der[$o + 1]); $r = substr($der, $o + 2, $rl); $o += 2 + $rl;
    $sl = ord($der[$o + 1]); $s = substr($der, $o + 2, $sl);
    $r = str_pad(ltrim($r, "\0"), 32, "\0", STR_PAD_LEFT);
    $s = str_pad(ltrim($s, "\0"), 32, "\0", STR_PAD_LEFT);
    return $r . $s;
}

function wp_vapid_header($endpoint, $vapidPem, $vapidPublic, $subject) {
    $p = parse_url($endpoint);
    $aud = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
    $h = wp_b64u(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
    $c = wp_b64u(json_encode(['aud' => $aud, 'exp' => time() + 12 * 3600, 'sub' => $subject]));
    openssl_sign("$h.$c", $der, $vapidPem, OPENSSL_ALGO_SHA256);
    return 'vapid t=' . "$h.$c." . wp_b64u(wp_der_to_raw($der)) . ', k=' . $vapidPublic;
}

function wp_encrypt($payload, $uaPublicB64, $authB64) {
    $uaPub = wp_b64d($uaPublicB64); $auth = wp_b64d($authB64);
    if (strlen($uaPub) !== 65 || strlen($auth) < 8) return null;
    $as = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
    $asPub = wp_raw_public($as);
    $peer = openssl_pkey_get_public(wp_pub_to_pem($uaPub));
    if (!$peer) return null;
    $ecdh = openssl_pkey_derive($peer, $as, 32);
    if ($ecdh === false) return null;
    $prk = hash_hkdf('sha256', $ecdh, 32, "WebPush: info\0" . $uaPub . $asPub, $auth);
    $salt = random_bytes(16);
    $cek = hash_hkdf('sha256', $prk, 16, "Content-Encoding: aes128gcm\0", $salt);
    $nonce = hash_hkdf('sha256', $prk, 12, "Content-Encoding: nonce\0", $salt);
    $tag = '';
    $ct = openssl_encrypt($payload . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
    if ($ct === false) return null;
    return $salt . pack('N', 4096) . chr(65) . $asPub . $ct . $tag;
}

// Returns HTTP status (201 = accepted, 404/410 = subscription gone, 0 = local failure)
function wp_send($sub, $payloadJson, $vapidPem, $vapidPublic, $subject, $ttl = 86400) {
    $ep = $sub['endpoint'] ?? '';
    $u = parse_url($ep);
    $host = strtolower((string)($u['host'] ?? ''));
    $allowed = ['fcm.googleapis.com','updates.push.services.mozilla.com','push.services.mozilla.com','web.push.apple.com'];
    $hostOk = in_array($host, $allowed, true) || (bool)preg_match('/^[a-z0-9.-]+\\.notify\\.windows\\.com$/', $host);
    if (!$u || strtolower((string)($u['scheme'] ?? '')) !== 'https' || !$hostOk || isset($u['user']) || isset($u['pass']) || empty($sub['keys']['p256dh']) || empty($sub['keys']['auth'])) return 0;
    $body = wp_encrypt($payloadJson, $sub['keys']['p256dh'], $sub['keys']['auth']);
    if ($body === null) return 0;
    $ch = curl_init($ep);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/octet-stream', 'Content-Encoding: aes128gcm',
            'Content-Length: ' . strlen($body), 'TTL: ' . $ttl, 'Urgency: normal',
            'Authorization: ' . wp_vapid_header($ep, $vapidPem, $vapidPublic, $subject),
        ],
    ]);
    curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code;
}
