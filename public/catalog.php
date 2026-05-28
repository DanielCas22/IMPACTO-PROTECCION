<?php
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

$categorySlug = trim($_GET['category'] ?? '');
$category = null;
$products = [];

if ($categorySlug !== '') {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $categorySlug]);
    $category = $stmt->fetch();

    if ($category) {
        $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE c.id = :cid ORDER BY p.id DESC');
        $stmt->execute(['cid' => $category['id']]);
        $products = $stmt->fetchAll();
    }
} else {
    // mostrar todos si no hay categoría
    $products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
}

?>

<main>
    <section class="container py-5">
        <div class="mb-4">
            <?php if ($category): ?>
                <p class="text-uppercase small text-warning mb-1">Catálogo</p>
                <h1 class="h3 fw-bold">Productos en: <?php echo htmlspecialchars($category['name']); ?></h1>
            <?php else: ?>
                <p class="text-uppercase small text-warning mb-1">Catálogo</p>
                <h1 class="h3 fw-bold">Todos los productos</h1>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php if (count($products) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">No se encontraron productos para esta categoría.</div>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
                <article class="col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <img src="<?php echo htmlspecialchars(assetUrl($product['image_url'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 220px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-warning text-dark align-self-start mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            <h3 class="h5 fw-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-secondary small flex-grow-1"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <strong class="text-success">$<?php echo number_format($product['price'], 2); ?></strong>
                                <a href="https://wa.me/5215512345678?text=Hola%20quiero%20consultar%20el%20producto%20<?php echo rawurlencode($product['name']); ?>" class="btn btn-outline-warning btn-sm" target="_blank" rel="noopener">Consultar</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
