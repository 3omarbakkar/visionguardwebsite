<?php
// Brute-force guard for admin login endpoints (fail-open: never blocks the site if it errors).
(function () {
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
        $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
        if ($script !== 'login.php' && $script !== 'staff_login.php') return;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0';
        $rl = __DIR__ . '/rl'; if (!is_dir($rl)) { @mkdir($rl, 0700, true); }
        $file = $rl . '/' . md5($ip) . '.json';
        $now = time(); $window = 900; $max = 10; $hits = [];
        if (is_file($file)) {
            $d = json_decode((string) @file_get_contents($file), true);
            if (is_array($d)) $hits = $d;
        }
        $hits = array_values(array_filter($hits, function ($t) use ($now, $window) { return $t > $now - $window; }));
        if (count($hits) >= $max) {
            http_response_code(429);
            header('Retry-After: ' . $window);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Too many attempts. Please try again in a few minutes.';
            exit;
        }
        $hits[] = $now;
        @file_put_contents($file, json_encode($hits), LOCK_EX);
    } catch (Throwable $e) { }
})();
