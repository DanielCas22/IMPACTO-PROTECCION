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
$users = $pdo->query('SELECT id, email, role, created_at FROM admins ORDER BY id DESC')->fetchAll();

?>
<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Usuarios</p>
            <h1 class="h3 fw-bold mb-0">Gestión de usuarios administrativos</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
            <a href="user_form.php" class="btn btn-warning">Nuevo usuario</a>
        </div>
    </div>

    <div class="card p-3 admin-panel-card">
        <table class="table table-striped table-hover admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Creado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo (int) $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($u['role'])); ?></td>
                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        <td class="text-end">
                            <a href="user_form.php?id=<?php echo (int) $u['id']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form method="post" action="user_delete.php" class="d-inline-block" onsubmit="return confirm('Eliminar usuario?');">
                                <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
