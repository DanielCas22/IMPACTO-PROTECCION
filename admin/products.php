<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Administración</p>
            <h1 class="h3 fw-bold mb-0">Gestión de productos</h1>
            <p class="text-secondary mb-0">Crea, edita y elimina artículos del catálogo.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
            <a href="product_form.php" class="btn btn-warning fw-semibold">+ Nuevo producto</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Imagen</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <div class="small text-secondary"><?php echo htmlspecialchars(mb_substr($product['description'], 0, 70)); ?>...</div>
                            </td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars(assetUrl($product['image_url'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 72px; height: 52px; object-fit: cover; border-radius: 8px;">
                            </td>
                            <td class="text-end">
                                <a href="product_form.php?id=<?php echo (int) $product['id']; ?>" class="btn btn-outline-warning btn-sm me-2">Editar</a>
                                <form action="product_delete.php" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar este producto?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
