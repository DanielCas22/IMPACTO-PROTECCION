<?php
$url = 'http://localhost:8000/admin/dashboard.php';
$html = @file_get_contents($url);
if ($html === false) {
    echo "FAILED\n";
    exit(1);
}
$pos = strpos($html, 'assets/css/style.css');
$start = max(0, $pos - 40);
echo substr($html, $start, 160);
