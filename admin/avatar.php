<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
csrf_check();

$id   = (int)(current_admin()['id'] ?? 0);
$dir  = dirname(__DIR__) . '/images/avatars';
$file = $dir . '/u' . $id . '.jpg';

$back = 'index.php';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $p = parse_url($_SERVER['HTTP_REFERER']);
    if (!empty($p['path']) && strpos($p['path'], '/admin/') === 0 && strpos($p['path'], '..') === false) {
        $rel = substr($p['path'], 7);
        if ($rel !== '' && preg_match('/^[A-Za-z0-9_\-\/]+\.php$/', $rel)) {
            $back = $rel . (isset($p['query']) ? '?' . $p['query'] : '');
        }
    }
}
function vg_av_done(string $msg, string $type, string $back): void { flash_set($msg, $type); header('Location: ' . $back); exit; }

if ($id <= 0) vg_av_done('تعذر تحديد المستخدم.', 'err', $back);

if (!empty($_POST['remove'])) {
    if (is_file($file)) @unlink($file);
    vg_av_done('اتشالت الصورة.', 'ok', $back);
}

$f = $_FILES['avatar'] ?? null;
if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) vg_av_done('اختار صورة الأول.', 'err', $back);
if ($f['size'] > 4 * 1024 * 1024) vg_av_done('الصورة كبيرة، الحد الأقصى 4 ميجا.', 'err', $back);
if (!function_exists('imagecreatetruecolor')) vg_av_done('السيرفر مش داعم معالجة الصور.', 'err', $back);

$info = @getimagesize($f['tmp_name']);
if (!$info) vg_av_done('الملف ده مش صورة.', 'err', $back);
switch ($info[2]) {
    case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($f['tmp_name']); break;
    case IMAGETYPE_PNG:  $src = @imagecreatefrompng($f['tmp_name']); break;
    case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($f['tmp_name']) : false; break;
    default: $src = false;
}
if (!$src) vg_av_done('الصيغة مش مدعومة، استخدم JPG أو PNG أو WebP.', 'err', $back);

if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
    $ex = @exif_read_data($f['tmp_name']);
    $o = (int)($ex['Orientation'] ?? 1);
    $deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
    if ($deg) { $r = imagerotate($src, $deg, 0); if ($r) { imagedestroy($src); $src = $r; } }
}

$w = imagesx($src); $h = imagesy($src); $s = min($w, $h);
$sx = (int)(($w - $s) / 2); $sy = (int)(($h - $s) / 2);
$out = imagecreatetruecolor(192, 192);
imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
imagecopyresampled($out, $src, 0, 0, $sx, $sy, 192, 192, $s, $s);

if (!is_dir($dir) && !@mkdir($dir, 0755, true)) vg_av_done('تعذر إنشاء مجلد الصور.', 'err', $back);
$tmp = $dir . '/.u' . $id . '.tmp';
if (!imagejpeg($out, $tmp, 86) || !@rename($tmp, $file)) vg_av_done('تعذر حفظ الصورة.', 'err', $back);
imagedestroy($src); imagedestroy($out);
vg_av_done('اتحدثت الصورة.', 'ok', $back);
