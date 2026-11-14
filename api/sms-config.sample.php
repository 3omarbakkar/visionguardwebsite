<?php
// Copy this file to api/sms-config.php and fill in ONE driver.
// Never commit api/sms-config.php or send it to anyone.
// The root .htaccess blocks direct web access to api/sms-config.php, but keeping
// the file outside public_html (via VG_SMS_CONFIG) is even safer.

// ---------- Option 1: Twilio (easiest, works in Egypt) ----------
return [
    'driver' => 'twilio',
    'sid'    => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'token'  => 'your_auth_token',
    'from'   => '+12025550123',      // a Twilio number you own
    // 'messaging_service_sid' => 'MGxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'timeout' => 10,
];

// ---------- Option 2: any HTTP SMS gateway (SMS Misr, 4jawaly, local reseller) ----------
// return [
//     'driver' => 'http',
//     'url'    => 'https://example-sms.com/api/send',
//     'method' => 'POST',
//     'headers' => [
//         "Content-Type: application/json",
//         "Authorization: Bearer {key}",
//     ],
//     'body'   => '{"to":"{phone_digits}","message":"{text}"}',   // or "to={phone_intl}&text={text_url}"
//     'key'    => 'your_api_key',
//     'success_contains' => '"success"',  // optional text that must appear in the reply
//     'timeout' => 10,
// ];
