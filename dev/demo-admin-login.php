<?php
// DEMO ONLY (copied into the temporary site by dev/server.js; never part of the real site).
require __DIR__ . '/includes/auth.php';
$_SESSION['admin'] = ['id' => 1, 'username' => 'boss', 'role' => 'admin', 'full_name' => 'Omar'];
header('Location: ' . (preg_match('/^[a-z_]+\\.php$/', $_GET['to'] ?? '') ? $_GET['to'] : 'index.php'));
