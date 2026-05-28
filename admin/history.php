<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$users = $pdo->query('SELECT id, email FROM admins ORDER BY email')->fetchAll();

$where = '1=1';
$params = [];
if (!empty($_GET['user'])) {
    $where = 'user_id = :uid';
    $params['uid'] = (int) $_GET['user'];
}

$stmt = $pdo->prepare("SELECT a.*, ad.email FROM activity_log a LEFT JOIN admins ad ON ad.id = a.user_id WHERE {$where} ORDER BY a.created_at DESC, a.id DESC LIMIT 200");
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>
<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Historial</p>
            <h1 class="h3 fw-bold mb-0">Historial de actividad</h1>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
            <form class="d-flex" method="get">
            <select name="user" class="form-select me-2">
                <option value="">Todos</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo (int) $u['id']; ?>" <?php echo (!empty($_GET['user']) && (int) $_GET['user'] === (int) $u['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['email']); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-secondary">Filtrar</button>
        </form>
    </div>

    <div class="card history-card p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
            <div>
                <p class="text-secondary mb-2">Registros ordenados por fecha y hora, con los más recientes arriba.</p>
                <p class="history-note mb-0">El historial refleja acciones internas del área administrativa: inicios de sesión, cambios de producto y gestión de usuarios.</p>
            </div>
            <div class="text-end text-muted small">
                Total de eventos: <?php echo count($logs); ?>
            </div>
        </div>
        <div class="table-responsive" style="max-height:620px; overflow:auto;">
            <table class="table table-hover history-table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No hay registros para mostrar.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($l['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($l['email'] ?? 'Sistema'); ?></td>
                            <td><strong><?php echo htmlspecialchars($l['action']); ?></strong></td>
                            <td><pre class="history-details"><?php echo htmlspecialchars($l['details']); ?></pre></td>
                            <td><?php echo htmlspecialchars($l['ip_address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
