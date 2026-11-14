<?php
// Dev-only router for `php -S` (mirrors the clean URLs in .htaccess). Not used in production.
$root = $_SERVER['DOCUMENT_ROOT'];
$u = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($u !== '/' && is_file($root . $u)) return false;
if (preg_match('#^/(shop|planner|about|faq|contact|cart|account)/?$#', $u, $m)) { $_GET['page'] = $m[1]; require $root . '/index.php'; return true; }
if (preg_match('#^/p/([A-Za-z0-9-]+)/?$#', $u, $m)) { $_GET['s'] = $m[1]; require $root . '/product.php'; return true; }
if (preg_match('#^/c/([a-z0-9-]+)/?$#', $u, $m)) { $_GET['c'] = $m[1]; require $root . '/category.php'; return true; }
if ($u === '/sitemap.xml') { require $root . '/sitemap.php'; return true; }
if ($u === '/') { require $root . '/index.php'; return true; }
return false;
