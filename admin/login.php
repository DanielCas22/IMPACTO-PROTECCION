<?php
require_once __DIR__ . '/../includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Completa correo y contraseña.';
    } else {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare('SELECT id, email, password_hash FROM admins WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                header('Location: dashboard.php');
                exit;
            }

            $message = 'Credenciales inválidas.';
        } catch (PDOException $e) {
            $message = 'No se pudo conectar a la base de datos.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow border-0 rounded-4 p-4">
                <h1 class="h3 fw-bold mb-2">Acceso administrador</h1>
                <p class="text-secondary mb-4">Ingresa tus credenciales para administrar el catálogo.</p>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button class="btn btn-warning w-100 fw-semibold" type="submit">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
