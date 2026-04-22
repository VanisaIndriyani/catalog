<?php
require_once 'config/config.php';
require_once 'config/database.php';

$conn = getConnection();

// Get filter category
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Get products with filter
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";

if ($categoryFilter > 0) {
    $sql .= " WHERE p.category_id = $categoryFilter";
}

$sql .= " ORDER BY p.created_at DESC";

$featuredProducts = $conn->query($sql);

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

$pageTitle = "Diva Mitra Computer";
include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Main Title -->
    <div class="text-center mb-5">
        <i class="fas fa-laptop fa-4x text-primary-custom mb-3"></i>
        <h1 class="display-4 fw-bold">DIVA MITRA COMPUTER</h1>
    </div>

    <!-- Filter Section -->
    <div class="filter-section mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Kategori
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>/" 
               class="btn btn-pill <?php echo !isset($_GET['category']) ? 'btn-primary-custom' : 'btn-outline-primary-custom'; ?>">
                Semua
            </a>
            <?php 
            $categories->data_seek(0);
            while($category = $categories->fetch_assoc()): 
            ?>
            <a href="<?php echo BASE_URL; ?>/index.php?category=<?php echo $category['id']; ?>" 
               class="btn btn-pill <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'btn-primary-custom' : 'btn-outline-primary-custom'; ?>">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if ($featuredProducts->num_rows > 0): ?>
            <?php while($product = $featuredProducts->fetch_assoc()): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image-wrapper">
                        <?php if($product['image']): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($product['image']); ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                        <div class="product-placeholder">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p class="text-muted mb-0">No Image</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <div class="product-category mb-3">
                            <i class="fas fa-tag me-1 text-primary-custom"></i>
                            <span class="text-muted"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                        </div>
                        <div class="price mb-3">
                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-primary-custom w-100 mt-auto">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h4>Belum ada produk</h4>
                    <p>Produk akan muncul di sini setelah ditambahkan oleh admin.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
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

