<?php
$url = 'http://localhost/IMPACTO-PROTECCION/assets/css/style.css';
$opts = ['http' => ['timeout' => 10]];
$context = stream_context_create($opts);
$content = @file_get_contents($url, false, $context);
if ($content === false) {
    echo 'FAILED';
    exit(1);
}
echo $content;
