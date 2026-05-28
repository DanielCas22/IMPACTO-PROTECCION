<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isAdministrator()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT email FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $u = $stmt->fetch();

    $stmt = $pdo->prepare('DELETE FROM admins WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($u) {
        try { logActivity($_SESSION['admin_id'] ?? null, 'user_delete', "Usuario eliminado: {$u['email']} (ID {$id})"); } catch (Exception $e) {}
    }
}

header('Location: users.php');
exit;
