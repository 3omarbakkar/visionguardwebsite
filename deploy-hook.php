<?php
// GitHub webhook -> pulls the cPanel git clone and deploys it to public_html.
// Config lives OUTSIDE the web root: ~/.vg-deploy.json  {"secret":"...","repo":"/home/USER/repositories/visionguardwebsite"}
header('Content-Type: text/plain');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit("POST only\n"); }

$home = getenv('HOME') ?: (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['dir'] : '');
$cfg  = @json_decode((string)@file_get_contents($home . '/.vg-deploy.json'), true);
if (!$cfg || empty($cfg['secret']) || empty($cfg['repo'])) { http_response_code(500); exit("not configured\n"); }

$body = file_get_contents('php://input');
$sig  = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!hash_equals('sha256=' . hash_hmac('sha256', $body, $cfg['secret']), $sig)) { http_response_code(403); exit("bad signature\n"); }

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event === 'ping') exit("pong\n");
$p = json_decode($body, true);
if ($event !== 'push' || ($p['ref'] ?? '') !== 'refs/heads/main') exit("ignored\n");

$repo = escapeshellarg($cfg['repo']);
$dest = escapeshellarg(rtrim($home, '/') . '/public_html/');
$cmd  = "cd $repo && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1"
      . " && /usr/bin/rsync -rlt --chmod=D755,F644 --exclude='.git' --exclude='.cpanel.yml' --exclude='.gitignore' --exclude='dev'"
      . " --exclude='README.md' --exclude='package.json' --exclude='*.sql' --exclude='push-data' ./ $dest 2>&1";
exec($cmd, $out, $rc);
http_response_code($rc === 0 ? 200 : 500);
echo implode("\n", $out), "\nexit=$rc\n";
