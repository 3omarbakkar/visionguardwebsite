<?php
// Kashier payment gateway config. Keep this file private - do not share its contents or commit it anywhere public.
// To fill the secret key: Kashier dashboard -> gear icon (Settings) is NOT it -> go to
// https://portal.kashier.io/en/dashboard/integration -> "Secret Keys" section -> click "Reveal Secret Key" -> copy it -> paste below.
return [
    'merchant_id'     => 'MID-52911-136',
    'payment_api_key' => '8fd2d5e0-aa88-4b1d-a0fc-9fb6dce08b94', // test Payment API Key (not secret)
    'secret_key'      => '7045e2deaf59dbba7d3866525e7aac29$177a4a3502df83709b9faf148c1400e69a677935d3b7dc196ea73cc907472982195733df8dcf02754ddeeb4bf9e5710a', // <-- double-click PASTE_SECRET_KEY_HERE above and paste over it
    'mode'            => 'test', // 'test' until Kashier approves the account + you swap in live keys, then 'live'
    'currency'        => 'EGP',
];
