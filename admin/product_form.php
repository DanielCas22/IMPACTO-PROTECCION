<?php
require_once __DIR__ . '/../includes/header.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = [
    'id' => 0,
    'name' => '',
    'description' => '',
    'price' => '',
    'image_url' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80',
    'category_id' => $categories[0]['id'] ?? 1,
];

$uploadDir = __DIR__ . '/../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $imageFile = $_FILES['image_file'] ?? null;

    if ($name === '' || $description === '' || $price === '' || !is_numeric($price) || (float) $price < 0 || $categoryId <= 0) {
        $message = 'Completa todos los campos correctamente.';
    } else {
        if (is_array($imageFile) && $imageFile['error'] === UPLOAD_ERR_OK && is_uploaded_file($imageFile['tmp_name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowed, true)) {
                $message = 'Solo puedes subir imágenes JPG, PNG, GIF o WEBP.';
            } else {
                $fileName = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                    $imageUrl = 'uploads/products/' . $fileName;
                } else {
                    $message = 'No se pudo guardar la imagen subida.';
                }
            }
        } elseif ($imageUrl === '') {
            $imageUrl = $product['image_url'] ?? 'https://via.placeholder.com/600x400?text=Protecci%C3%B3n';
        }

        if ($message === '') {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE products SET name = :name, description = :description, price = :price, image_url = :image_url, category_id = :category_id WHERE id = :id');
                $stmt->execute(['name' => $name, 'description' => $description, 'price' => (float) $price, 'image_url' => $imageUrl, 'category_id' => $categoryId, 'id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO products (name, description, price, image_url, category_id) VALUES (:name, :description, :price, :image_url, :category_id)');
                $stmt->execute(['name' => $name, 'description' => $description, 'price' => (float) $price, 'image_url' => $imageUrl, 'category_id' => $categoryId]);
            }

            header('Location: products.php');
            exit;
        }
    }

    $product['name'] = $name;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['image_url'] = $imageUrl;
    $product['category_id'] = $categoryId;
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase small text-warning mb-1">Producto</p>
            <h1 class="h3 fw-bold mb-0"><?php echo $id > 0 ? 'Editar producto' : 'Nuevo producto'; ?></h1>
        </div>
        <a href="products.php" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <?php if ($message !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Precio</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" value="<?php echo htmlspecialchars((string) $product['price']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo ((int) $product['category_id'] === (int) $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subir imagen local</label>
                        <input type="file" class="form-control" name="image_file" accept="image/png, image/jpeg, image/gif, image/webp">
                        <div class="form-text">Si eliges una imagen local, se guardará en la carpeta uploads/products/. Si dejas esto vacío, se usará la URL de abajo.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL de imagen</label>
                        <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>" placeholder="https://...">
                        <div class="form-text">Puedes mantener esta URL o usar una imagen local. Si subes archivo, esta URL se reemplaza automáticamente.</div>
                    </div>
                    <button type="submit" class="btn btn-warning fw-semibold">Guardar producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
