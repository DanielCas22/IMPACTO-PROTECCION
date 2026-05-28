<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isAdministrator()) {
    header('Location: products.php?error=delete_permission');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $pdo = getPDO();
    // fetch for logging
    $stmt = $pdo->prepare('SELECT name FROM products WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $prod = $stmt->fetch();

    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($prod) {
        try { logActivity($_SESSION['admin_id'] ?? null, 'product_delete', "Producto eliminado: {$prod['name']} (ID {$id})"); } catch (Exception $e) {}
    }
}

header('Location: products.php');
exit;
