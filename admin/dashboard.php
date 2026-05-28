<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$countProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$countCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$countPending = (int) $pdo->query('SELECT COUNT(*) FROM pending_actions WHERE status = "pending"')->fetchColumn();
?>

<section class="dashboard-hero">
    <div class="hero-accent"></div>
    <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Panel</p>
            <h1 class="h3 fw-bold mb-0">Dashboard administrador</h1>
        </div>
        <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <article class="card glass-card shadow-sm border-0 p-4">
                <h2 class="h5 fw-semibold">Productos</h2>
                <p class="text-secondary mb-3">Cantidad total registrada en el catálogo.</p>
                <strong class="display-6 text-warning"><?php echo $countProducts; ?></strong>
            </article>
        </div>
        <div class="col-md-6">
            <article class="card glass-card shadow-sm border-0 p-4">
                <h2 class="h5 fw-semibold">Categorías</h2>
                <p class="text-secondary mb-3">Tipos de protecciones disponibles.</p>
                <strong class="display-6 text-warning"><?php echo $countCategories; ?></strong>
            </article>
        </div>
    </div>
    <?php if ($countPending > 0): ?>
        <div class="row g-4 mt-3">
            <div class="col-md-12">
                <article class="card glass-card shadow-sm border-0 p-4 border-warning">
                    <h2 class="h5 fw-semibold text-warning">Aprobaciones pendientes</h2>
                    <p class="text-secondary mb-3">Hay <?php echo $countPending; ?> solicitudes pendientes de revisión.</p>
                    <a href="pending_actions.php" class="btn btn-outline-warning">Ver solicitudes</a>
                </article>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="products.php" class="btn btn-warning fw-semibold">Gestionar productos</a>
        <a href="users.php" class="btn btn-outline-light ms-2">Gestionar usuarios</a>
        <a href="history.php" class="btn btn-outline-secondary ms-2">Historial</a>
    </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
