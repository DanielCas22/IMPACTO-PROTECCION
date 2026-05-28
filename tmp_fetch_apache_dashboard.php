<?php
$url = 'http://localhost/IMPACTO-PROTECCION/admin/dashboard.php';
$opts = ['http' => ['timeout' => 10, 'ignore_errors' => true]];
$context = stream_context_create($opts);
$html = @file_get_contents($url, false, $context);
if ($html === false) { echo 'FAILED'; exit(1); }
echo substr($html, 0, 800);
