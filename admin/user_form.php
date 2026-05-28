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
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = ['id' => 0, 'email' => '', 'role' => 'asistente'];
$message = '';

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT id, email, role FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'asistente';

    if ($email === '') {
        $message = 'Correo es requerido.';
    } else {
        if ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE admins SET email = :email, password_hash = :hash, role = :role WHERE id = :id');
                $stmt->execute(['email' => $email, 'hash' => $hash, 'role' => $role, 'id' => $id]);
                try { logActivity($_SESSION['admin_id'] ?? null, 'user_update', "Usuario actualizado: {$email} (ID {$id})"); } catch (Exception $e) {}
            } else {
                $stmt = $pdo->prepare('UPDATE admins SET email = :email, role = :role WHERE id = :id');
                $stmt->execute(['email' => $email, 'role' => $role, 'id' => $id]);
                try { logActivity($_SESSION['admin_id'] ?? null, 'user_update', "Usuario actualizado (email): {$email} (ID {$id})"); } catch (Exception $e) {}
            }
        } else {
            if ($password === '') {
                $message = 'La contraseña es requerida al crear un usuario.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO admins (email, password_hash, role) VALUES (:email, :hash, :role)');
                $stmt->execute(['email' => $email, 'hash' => $hash, 'role' => $role]);
                $newId = (int) $pdo->lastInsertId();
                try { logActivity($_SESSION['admin_id'] ?? null, 'user_create', "Usuario creado: {$email} (ID {$newId})"); } catch (Exception $e) {}
                header('Location: users.php');
                exit;
            }
        }

        if ($message === '') {
            header('Location: users.php');
            exit;
        }
    }
}

?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Usuarios</p>
            <h1 class="h3 fw-bold mb-0"><?php echo $id > 0 ? 'Editar usuario' : 'Nuevo usuario'; ?></h1>
        </div>
        <a href="users.php" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card p-4">
                <?php if ($message !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role" required>
                            <option value="administrador" <?php echo ($user['role'] ?? '') === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                            <option value="vendedor" <?php echo ($user['role'] ?? '') === 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                            <option value="asistente" <?php echo ($user['role'] ?? '') === 'asistente' ? 'selected' : ''; ?>>Asistente de punto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" <?php echo $id === 0 ? 'required' : ''; ?> >
                        <?php if ($id > 0): ?>
                            <div class="form-text">Dejar en blanco para mantener la contraseña actual.</div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-warning" type="submit">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
