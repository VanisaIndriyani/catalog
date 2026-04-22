<?php
require_once 'config/config.php';
require_once 'config/database.php';

$conn = getConnection();

// Get filter category
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];
$types = '';

if ($categoryFilter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
    $types .= 'i';
}

if (!empty($searchQuery)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get selected category name
$selectedCategory = null;
if ($categoryFilter > 0) {
    $catResult = $conn->query("SELECT name FROM categories WHERE id = $categoryFilter");
    if ($catResult->num_rows > 0) {
        $selectedCategory = $catResult->fetch_assoc()['name'];
    }
}

$pageTitle = "Daftar Produk";
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

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-laptop me-3"></i>Daftar Produk</h1>
        <?php if ($selectedCategory): ?>
        <p class="lead">Kategori: <?php echo htmlspecialchars($selectedCategory); ?></p>
        <?php elseif (!empty($searchQuery)): ?>
        <p class="lead">Hasil pencarian: "<?php echo htmlspecialchars($searchQuery); ?>"</p>
        <?php else: ?>
        <p class="lead">Laptop, Printer & Aksesoris</p>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <!-- Filter and Search Section -->
    <div class="filter-section">
        <form method="GET" action="/catalog/products.php" class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-search me-1"></i>Cari Produk</label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Cari nama atau deskripsi produk..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-filter me-1"></i>Filter Kategori</label>
                <select name="category" class="form-select">
                    <option value="0">Semua Kategori</option>
                    <?php 
                    $categories->data_seek(0);
                    while($category = $categories->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $category['id']; ?>" 
                            <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary-custom w-100 me-2">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
                <a href="/catalog/products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if ($products->num_rows > 0): ?>
            <?php while($product = $products->fetch_assoc()): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100">
                    <?php if($product['image']): ?>
                    <img src="/catalog/<?php echo htmlspecialchars($product['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                    <img src="https://via.placeholder.com/400x250?text=No+Image" 
                         class="card-img-top" 
                         alt="No Image">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-primary-custom mb-2">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </span>
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text flex-grow-1">
                            <?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...
                        </p>
                        <div class="mt-auto">
                            <div class="price mb-3">
                                Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="/catalog/product-detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-primary-custom">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                <a href="https://wa.me/62882007926483?text=Halo,%20saya%20tertarik%20dengan%20produk:%20<?php echo urlencode($product['name']); ?>" 
                                   target="_blank"
                                   class="btn btn-success w-100">
                                    <i class="fab fa-whatsapp me-1"></i>Pesan via WhatsApp
                                </a>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>Tidak ada produk ditemukan</h4>
                    <p>Silakan coba dengan kata kunci atau kategori yang berbeda.</p>
                    <a href="/catalog/products.php" class="btn btn-primary-custom mt-3">
                        <i class="fas fa-redo me-1"></i>Lihat Semua Produk
                    </a>
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

