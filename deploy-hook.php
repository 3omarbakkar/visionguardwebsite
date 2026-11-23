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
$ref = $p['ref'] ?? '';
// production: main -> public_html.  staging (optional): "staging": {"repo": "...", "dest": "/home/USER/staging.example"} in ~/.vg-deploy.json
if ($event === 'push' && $ref === 'refs/heads/main') {
    $branch = 'main'; $repoPath = $cfg['repo']; $destPath = rtrim($home, '/') . '/public_html/';
} elseif ($event === 'push' && $ref === 'refs/heads/staging' && !empty($cfg['staging']['repo']) && !empty($cfg['staging']['dest'])) {
    $branch = 'staging'; $repoPath = $cfg['staging']['repo']; $destPath = rtrim($cfg['staging']['dest'], '/') . '/';
} else {
    exit('ignored' . PHP_EOL);
}

$repo = escapeshellarg($repoPath);
$br   = escapeshellarg($branch);
$dest = escapeshellarg($destPath);
$cmd  = "cd $repo && git fetch origin $br 2>&1 && git reset --hard FETCH_HEAD 2>&1"
      . " && /usr/bin/rsync -rlt --chmod=D755,F644 --exclude='.git' --exclude='.cpanel.yml' --exclude='.gitignore' --exclude='dev'"
      . " --exclude='README.md' --exclude='package.json' --exclude='*.sql' --exclude='push-data' ./ $dest 2>&1";
exec($cmd, $out, $rc);
http_response_code($rc === 0 ? 200 : 500);
echo implode(PHP_EOL, $out), PHP_EOL, 'exit=', $rc, PHP_EOL;
