<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function projectRoot(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $folder = str_replace('\\', '/', dirname($scriptName));
    $folder = rtrim($folder, '/');

    if (basename($folder) === 'admin' || basename($folder) === 'public') {
        $parent = str_replace('\\', '/', dirname($folder));
        return $parent === '/' ? '' : $parent;
    }

    return $folder === '/' ? '' : $folder;
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
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

function getAdminRole(): ?string
{
    return $_SESSION['admin_role'] ?? null;
}

function isAdministrator(): bool
{
    return getAdminRole() === 'administrador';
}

function createPendingAction(?int $userId, string $actionType, string $entityType, ?int $entityId, array $payload, PDO $pdo = null): int
{
    if ($pdo === null) {
        $pdo = getPDO();
    }

    $stmt = $pdo->prepare('INSERT INTO pending_actions (user_id, action_type, entity_type, entity_id, payload) VALUES (:user_id, :action_type, :entity_type, :entity_id, :payload)');
    $stmt->execute([
        'user_id' => $userId,
        'action_type' => $actionType,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Registrar una entrada en el historial de actividad.
 * @param int|null $userId
 * @param string $action
 * @param string $details
 * @param PDO|null $pdo
 */
function logActivity(?int $userId, string $action, string $details = '', PDO $pdo = null): void
{
    try {
        if ($pdo === null) {
            $pdo = getPDO();
        }

        $stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip_address)');
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Exception $e) {
        // No hacemos nada en caso de error para no romper la UX.
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impacto Protección</title>
    <meta name="description" content="Tienda online de protecciones para motociclistas con cascos, guantes, chaquetas y más.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?php echo assetUrl('assets/css/style.css'); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold me-3" href="<?php echo projectRoot(); ?>/index.php" style="min-height: 52px;">
            <img src="<?php echo assetUrl('assets/img/logo-impacto.png'); ?>" alt="Logo Impacto Protección" style="height: 54px; width: auto; max-width: 120px; object-fit: contain; background: transparent; border-radius: 0; padding: 0; box-shadow: none; display: block;">
            <span class="d-none d-md-inline text-white">Impacto Protección</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item"><a class="nav-link" href="<?php echo projectRoot(); ?>/index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo projectRoot(); ?>/index.php#catalogo">Catálogo</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo projectRoot(); ?>/index.php#categorias">Categorías</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo projectRoot(); ?>/admin/login.php">Admin</a></li>
            </ul>
        </div>
    </div>
</nav>
