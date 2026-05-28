<?php
require_once __DIR__ . '/includes/header.php';

// Sesión temporal para captura de pantalla del dashboard.
$_SESSION['admin_id'] = 1;
$_SESSION['admin_email'] = 'admin@impacto.com';
header('Location: admin/dashboard.php');
exit;
