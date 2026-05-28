<?php
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC LIMIT 6')->fetchAll();
?>

<main>
    <section class="hero py-5">
        <div class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <p class="text-uppercase small text-warning fw-semibold">Protección profesional</p>
                    <h1 class="display-5 fw-bold text-white">Protege cada viaje con equipamiento de alto rendimiento.</h1>
                    <p class="lead text-light">Cascos, guantes, chaquetas y rodilleras diseñados para seguridad, confort y estilo en la ruta.</p>
                    <a href="#catalogo" class="btn btn-warning btn-lg fw-semibold">Ver catálogo</a>
                </div>
                <div class="col-lg-5">
                    <div class="card glass-card border-0 p-4 shadow-lg">
                        <h3 class="h5 fw-bold">¿Buscas algo específico?</h3>
                        <p class="text-secondary">Usa el buscador para encontrar productos por nombre o categoría.</p>
                        <form class="d-flex gap-2" method="get" action="#catalogo">
                            <input class="form-control" type="search" name="q" placeholder="Ej. casco integral" aria-label="Buscar producto">
                            <button class="btn btn-outline-warning" type="submit">Buscar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="categorias" class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <p class="text-uppercase small text-warning mb-1">Categorías</p>
                <h2 class="h3 fw-bold">Explora por tipo de protección</h2>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-xl-2">
                    <a class="text-decoration-none" href="catalog.php?category=<?php echo urlencode($category['slug']); ?>">
                        <article class="card h-100 border-0 shadow-sm text-center p-3 category-card">
                            <div class="py-2" style="background: var(--accent);">
                                <i class="fa-solid fa-shield-halved fa-2x text-white"></i>
                            </div>
                            <h3 class="h6 fw-semibold mt-3 mb-1 text-white"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="small text-secondary mb-0">Calidad y diseño para cada recorrido.</p>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="catalogo" class="container pb-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <p class="text-uppercase small text-warning mb-1">Catálogo</p>
                <h2 class="h3 fw-bold">Productos destacados</h2>
            </div>
        </div>
        <div class="row g-4">
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
