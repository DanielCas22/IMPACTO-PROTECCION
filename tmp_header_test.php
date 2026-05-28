<?php
$_SERVER['SCRIPT_NAME'] = '/IMPACTO-PROTECCION/admin/dashboard.php';
require __DIR__ . '/includes/header.php';
echo 'ROOT=' . projectRoot() . PHP_EOL;
echo 'ASSET=' . assetUrl('assets/css/style.css') . PHP_EOL;
