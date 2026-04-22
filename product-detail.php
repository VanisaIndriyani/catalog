<?php
require_once 'config/config.php';
require_once 'config/database.php';

$conn = getConnection();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: /catalog/index.php');
    exit();
}

$product = $result->fetch_assoc();

// Get related products (same category)
$relatedSql = "SELECT p.*, c.name as category_name 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE p.category_id = ? AND p.id != ? 
               ORDER BY RAND() 
               LIMIT 3";
$relatedStmt = $conn->prepare($relatedSql);
$relatedStmt->bind_param('ii', $product['category_id'], $productId);
$relatedStmt->execute();
$relatedProducts = $relatedStmt->get_result();

$pageTitle = $product['name'];
include 'includes/header.php';

// Show cart message if exists
if (isset($_SESSION['cart_message'])) {
    echo '<div class="container mt-3">';
    echo '<div class="alert alert-' . ($_SESSION['cart_message_type'] ?? 'success') . ' alert-custom alert-dismissible fade show">';
    echo '<i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['cart_message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div></div>';
    unset($_SESSION['cart_message']);
    unset($_SESSION['cart_message_type']);
}
?>

<div class="container" style="padding-top: 100px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php" class="text-decoration-none">Produk</a></li>
            <?php if ($product['category_id']): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>/index.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="product-image-container">
                <?php if($product['image']): ?>
                <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($product['image']); ?>" 
                     class="product-detail-img" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     id="mainProductImage">
                <?php else: ?>
                <div class="product-placeholder-large">
                    <i class="fas fa-image fa-5x text-muted mb-3"></i>
                    <p class="text-muted">No Image Available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info-card">
                <div class="product-badge mb-3">
                    <span class="badge bg-primary-custom rounded-pill px-3 py-2">
                        <i class="fas fa-tag me-2"></i>
                        <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                    </span>
                </div>
                
                <h1 class="product-title mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-price-section mb-4">
                    <div class="price-large">
                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-check-circle text-success me-1"></i>Stok Tersedia
                    </small>
                </div>

                <hr class="my-4">

                <div class="product-description mb-4">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-info-circle me-2 text-primary-custom"></i>Deskripsi Produk
                    </h5>
                    <p class="description-text">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>

                <hr class="my-4">

                <div class="product-actions">
                    <a href="https://wa.me/62882007926483?text=Halo,%20saya%20tertarik%20dengan%20produk:%20<?php echo urlencode($product['name']); ?>%20-%20Rp%20<?php echo number_format($product['price'], 0, ',', '.'); ?>" 
                       target="_blank"
                       class="btn btn-success btn-lg w-100 mb-3">
                        <i class="fab fa-whatsapp me-2"></i>Pesan via WhatsApp
                    </a>
                    </form>
                    <a href="/catalog/products.php<?php echo $product['category_id'] ? '?category=' . $product['category_id'] : ''; ?>" 
                       class="btn btn-outline-primary-custom w-100">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Produk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if ($relatedProducts->num_rows > 0): ?>
    <div class="related-products-section mt-5 pt-5">
        <div class="section-header mb-4">
            <h3 class="section-title-large">
                <i class="fas fa-th me-2 text-primary-custom"></i>
                Produk Terkait
            </h3>
            <p class="text-muted">Produk serupa yang mungkin Anda sukai</p>
        </div>
        <div class="row g-4">
            <?php while($related = $relatedProducts->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card product-card h-100 shadow-sm">
                    <div class="product-image-wrapper">
                        <?php if($related['image']): ?>
                        <img src="/catalog/<?php echo htmlspecialchars($related['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <?php else: ?>
                        <div class="product-placeholder">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p class="text-muted mb-0">No Image</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-primary-custom rounded-pill mb-2 px-3 py-1">
                            <?php echo htmlspecialchars($related['category_name'] ?? 'Uncategorized'); ?>
                        </span>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($related['name']); ?></h5>
                        <p class="card-text flex-grow-1 text-muted">
                            <?php echo substr(htmlspecialchars($related['description']), 0, 100); ?>...
                        </p>
                        <div class="mt-auto">
                            <div class="price mb-3">
                                Rp <?php echo number_format($related['price'], 0, ',', '.'); ?>
                            </div>
                            <a href="/catalog/product-detail.php?id=<?php echo $related['id']; ?>" 
                               class="btn btn-primary-custom w-100">
                                <i class="fas fa-eye me-1"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="/catalog/assets/js/main.js"></script>
</body>
</html>

