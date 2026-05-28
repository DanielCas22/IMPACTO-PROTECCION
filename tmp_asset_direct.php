<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function projectRoot(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $folder = rtrim(dirname($scriptName), '/');

    if (basename($folder) === 'admin' || basename($folder) === 'public') {
        $parent = dirname($folder);
        return $parent === '/' ? '' : $parent;
    }

    return $folder === '/' ? '' : $folder;
}

function assetUrl(string $path): string
{
    $path = trim($path);

    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return rtrim(projectRoot(), '/') . '/' . ltrim($path, '/');
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '(missing)';
echo var_export($scriptName, true) . "\n";
echo 'chars: ' . implode(',', array_map('ord', str_split($scriptName))) . "\n";
$normalized = str_replace('\\', '/', $scriptName);
echo var_export($normalized, true) . "\n";
echo 'norm chars: ' . implode(',', array_map('ord', str_split($normalized))) . "\n";
$folder = rtrim(dirname($normalized), '/');
echo var_export($folder, true) . "\n";
echo 'folder chars: ' . implode(',', array_map('ord', str_split($folder))) . "\n";
$root = projectRoot();
$asset = assetUrl('assets/css/style.css');
echo var_export($root, true) . "\n";
echo var_export($asset, true) . "\n";
