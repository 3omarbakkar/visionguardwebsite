<?php
/**
 * Product photo helpers.
 * The storefront (index.php) loads the primary photo for product id N from
 * "images/pN.jpg" and silently falls back to a drawn icon if that file
 * is missing (see the pimg() function in index.php). Products can also have
 * extra photos from other angles, saved as "images/pN_2.jpg" .. "images/pN_10.jpg".
 * Slot 1 is always the plain "images/pN.jpg" file, kept for backward compatibility.
 */

const PRODUCT_IMAGE_MAX_SLOTS = 10;

function product_image_path(int $id, int $slot = 1): string {
    $suffix = $slot > 1 ? '_' . $slot : '';
    return rtrim(IMAGES_DIR, '/') . '/p' . $id . $suffix . '.jpg';
}

function product_image_exists(int $id, int $slot = 1): bool {
    return is_file(product_image_path($id, $slot));
}

/** Returns the slot numbers (1..10) that currently have a saved photo, in order. */
function product_image_slots(int $id): array {
    $slots = [];
    for ($i = 1; $i <= PRODUCT_IMAGE_MAX_SLOTS; $i++) {
        if (product_image_exists($id, $i)) $slots[] = $i;
    }
    return $slots;
}

/** Converts/saves an uploaded image to images/p{id}[_{slot}].jpg (max 1200px, JPEG q=85). */
function save_product_image(string $tmpPath, int $id, int $slot = 1): bool {
    $info = @getimagesize($tmpPath);
    if (!$info) return false;
    [$w, $h] = $info;

    $src = match ($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($tmpPath),
        'image/png'  => @imagecreatefrompng($tmpPath),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : null,
        default      => null,
    };
    if (!$src) return false;

    $max = 1200;
    $scale = min(1, $max / max($w, $h));
    $nw = max(1, (int)round($w * $scale));
    $nh = max(1, (int)round($h * $scale));

    $dst = imagecreatetruecolor($nw, $nh);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white); // flatten transparency (PNG) onto white
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

    if (!is_dir(IMAGES_DIR)) @mkdir(IMAGES_DIR, 0755, true);
    $ok = imagejpeg($dst, product_image_path($id, $slot), 85);

    imagedestroy($src);
    imagedestroy($dst);
    return $ok;
}

function delete_product_image(int $id, int $slot = 1): void {
    $path = product_image_path($id, $slot);
    if (is_file($path)) @unlink($path);
}

/**
 * Shifts remaining photos down so slots stay contiguous from 1 upward after
 * a delete (slot 1 keeps the lowest surviving photo, which is what the
 * homepage card displays).
 */
function product_images_repack(int $id): void {
    $existing = product_image_slots($id);
    $target = 1;
    foreach ($existing as $slot) {
        if ($slot !== $target) {
            $from = product_image_path($id, $slot);
            $to = product_image_path($id, $target);
            @rename($from, $to);
        }
        $target++;
    }
}

/** Adds a version query (file mtime) so browsers fetch a new photo as soon as it changes. */
if (!function_exists('vg_img')) {
    function vg_img(string $rel): string {
        $f = rtrim(IMAGES_DIR, '/') . '/' . basename(strtok($rel, '?'));
        $v = @filemtime($f);
        return $rel . ($v ? '?v=' . $v : '');
    }
}
