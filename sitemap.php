<?php
require_once __DIR__ . '/admin/includes/db.php';
header('Content-Type: application/xml; charset=utf-8');
$base = 'https://visionguardeg.com';
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
echo "<url><loc>$base/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>\n";
foreach(['shop','planner','about','faq','contact'] as $__pg){ echo "<url><loc>$base/$__pg</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>\n"; }
$__cats = @include __DIR__ . '/seo_categories.php';
if (is_array($__cats)) { foreach (array_keys($__cats) as $__s) { echo "<url><loc>$base/c/$__s</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>\n"; } }
$rows = db()->query('SELECT id, slug FROM products ORDER BY id ASC')->fetchAll();
foreach ($rows as $r) {
    $path = (isset($r['slug']) && $r['slug'] !== '') ? '/p/' . rawurlencode($r['slug']) : '/product.php?id=' . (int)$r['id'];
    echo '<url><loc>' . htmlspecialchars($base . $path, ENT_XML1) . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
}
echo '</urlset>';
