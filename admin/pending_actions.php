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

$pdo = getPDO();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if ($id > 0 && in_array($action, ['approve', 'reject'], true)) {
        $stmt = $pdo->prepare('SELECT pa.*, a.email AS requester FROM pending_actions pa LEFT JOIN admins a ON a.id = pa.user_id WHERE pa.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $pending = $stmt->fetch();

        if ($pending && $pending['status'] === 'pending') {
            $payload = json_decode($pending['payload'], true);
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $reviewerNote = $note !== '' ? $note : ($status === 'approved' ? 'Aprobado.' : 'Rechazado.');

            if ($action === 'approve') {
                try {
                    if ($pending['action_type'] === 'product_create') {
                        $stmt = $pdo->prepare('INSERT INTO products (name, description, price, image_url, category_id) VALUES (:name, :description, :price, :image_url, :category_id)');
                        $stmt->execute([
                            'name' => $payload['name'] ?? '',
                            'description' => $payload['description'] ?? '',
                            'price' => $payload['price'] ?? 0,
                            'image_url' => $payload['image_url'] ?? '',
                            'category_id' => $payload['category_id'] ?? 0,
                        ]);
                        $entityId = (int) $pdo->lastInsertId();
                        logActivity($_SESSION['admin_id'] ?? null, 'product_create_approved', "Solicitud aprobada: Producto creado {$payload['name']} (ID {$entityId})");
                        logActivity($pending['user_id'], 'request_approved', "Solicitud de creación de producto aprobada: {$payload['name']} (ID {$entityId})");
                    } elseif ($pending['action_type'] === 'product_update') {
                        $stmt = $pdo->prepare('UPDATE products SET name = :name, description = :description, price = :price, image_url = :image_url, category_id = :category_id WHERE id = :id');
                        $stmt->execute([
                            'name' => $payload['name'] ?? '',
                            'description' => $payload['description'] ?? '',
                            'price' => $payload['price'] ?? 0,
                            'image_url' => $payload['image_url'] ?? '',
                            'category_id' => $payload['category_id'] ?? 0,
                            'id' => (int) $pending['entity_id'],
                        ]);
                        $entityId = (int) $pending['entity_id'];
                        logActivity($_SESSION['admin_id'] ?? null, 'product_update_approved', "Solicitud aprobada: Producto actualizado {$payload['name']} (ID {$entityId})");
                        logActivity($pending['user_id'], 'request_approved', "Solicitud de actualización de producto aprobada: {$payload['name']} (ID {$entityId})");
                    } else {
                        $entityId = $pending['entity_id'];
                        logActivity($_SESSION['admin_id'] ?? null, 'request_approved', "Solicitud aprobada: {$pending['action_type']} (ID {$entityId})");
                        logActivity($pending['user_id'], 'request_approved', "Solicitud aprobada: {$pending['action_type']} (ID {$entityId})");
                    }
                } catch (Exception $e) {
                    $message = 'No se pudo aplicar la acción aprobada. Revisa los datos y vuelve a intentar.';
                }
            }

            if ($message === '') {
                $stmt = $pdo->prepare('UPDATE pending_actions SET status = :status, reviewer_id = :reviewer_id, reviewer_note = :reviewer_note, reviewed_at = NOW(), entity_id = :entity_id WHERE id = :id');
                $stmt->execute([
                    'status' => $status,
                    'reviewer_id' => $_SESSION['admin_id'],
                    'reviewer_note' => $reviewerNote,
                    'entity_id' => $entityId ?? $pending['entity_id'],
                    'id' => $id,
                ]);

                if ($message === '') {
                    $message = $action === 'approve' ? 'Solicitud aprobada correctamente.' : 'Solicitud rechazada correctamente.';
                    if ($action === 'reject') {
                        logActivity($_SESSION['admin_id'] ?? null, 'request_rejected', "Solicitud rechazada: {$pending['action_type']} (ID {$pending['id']})");
                        logActivity($pending['user_id'], 'request_rejected', "Solicitud rechazada: {$pending['action_type']} (ID {$pending['id']})");
                    }
                }
            }
        }
    }
}

$stmt = $pdo->prepare('SELECT pa.*, a.email AS requester FROM pending_actions pa LEFT JOIN admins a ON a.id = pa.user_id WHERE pa.status = :status ORDER BY pa.created_at DESC');
$stmt->execute(['status' => 'pending']);
$pendingActions = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitudes pendientes - Impacto Protección</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?php echo assetUrl('assets/css/style.css'); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Impacto Protección</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="pending_actions.php">Pendientes</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="history.php">Historial</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Salir</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Aprobaciones</p>
            <h1 class="h3 fw-bold mb-0">Solicitudes pendientes</h1>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (empty($pendingActions)): ?>
        <div class="alert alert-secondary">No hay solicitudes pendientes en este momento.</div>
    <?php else: ?>
        <div class="card p-3 admin-panel-card">
            <div class="table-responsive">
                <table class="table table-striped table-hover admin-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitante</th>
                            <th>Tipo</th>
                            <th>Entidad</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingActions as $pending): ?>
                            <tr>
                                <td><?php echo (int) $pending['id']; ?></td>
                                <td><?php echo htmlspecialchars($pending['requester'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($pending['action_type']); ?></td>
                                <td><?php echo htmlspecialchars($pending['entity_type'] . ' ' . ($pending['entity_id'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($pending['created_at']); ?></td>
                                <td class="text-end">
                                    <button class="btn btn-outline-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#approveModal-<?php echo (int) $pending['id']; ?>">Aprobar</button>
                                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal-<?php echo (int) $pending['id']; ?>">Rechazar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php foreach ($pendingActions as $pending): ?>
    <div class="modal fade" id="approveModal-<?php echo (int) $pending['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Aprobar solicitud #<?php echo (int) $pending['id']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($pending['requester'] ?? ''); ?></p>
                        <p><strong>Tipo de acción:</strong> <?php echo htmlspecialchars($pending['action_type']); ?></p>
                        <pre class="history-details"><?php echo htmlspecialchars(json_encode(json_decode($pending['payload'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        <div class="mb-3">
                            <label class="form-label">Nota de aprobación</label>
                            <textarea class="form-control" name="note" rows="3" placeholder="Opcional."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" value="<?php echo (int) $pending['id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Aprobar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rejectModal-<?php echo (int) $pending['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Rechazar solicitud #<?php echo (int) $pending['id']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($pending['requester'] ?? ''); ?></p>
                        <p><strong>Tipo de acción:</strong> <?php echo htmlspecialchars($pending['action_type']); ?></p>
                        <div class="mb-3">
                            <label class="form-label">Motivo del rechazo</label>
                            <textarea class="form-control" name="note" rows="3" placeholder="Opcional."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" value="<?php echo (int) $pending['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
