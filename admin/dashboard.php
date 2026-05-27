<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$countProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$countCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
?>

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
            <article class="card shadow-sm border-0 p-4">
                <h2 class="h5 fw-semibold">Productos</h2>
                <p class="text-secondary mb-3">Cantidad total registrada en el catálogo.</p>
                <strong class="display-6 text-warning"><?php echo $countProducts; ?></strong>
            </article>
        </div>
        <div class="col-md-6">
            <article class="card shadow-sm border-0 p-4">
                <h2 class="h5 fw-semibold">Categorías</h2>
                <p class="text-secondary mb-3">Tipos de protecciones disponibles.</p>
                <strong class="display-6 text-warning"><?php echo $countCategories; ?></strong>
            </article>
        </div>
    </div>

    <div class="mt-4">
        <a href="products.php" class="btn btn-warning fw-semibold">Gestionar productos</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
