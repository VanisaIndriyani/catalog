<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireAdmin();

$conn = getConnection();

// Get admin username
$adminUsername = $_SESSION['admin_username'] ?? 'admin';

// Get statistics
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalCategories = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

// Get stock statistics
$stockResult = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if ($stockResult->num_rows > 0) {
    $totalStock = $conn->query("SELECT COALESCE(SUM(stock), 0) as total FROM products")->fetch_assoc()['total'];
    $lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 10 AND stock > 0")->fetch_assoc()['count'];
} else {
    // If stock column doesn't exist yet, add it and set default values
    $conn->query("ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER price");
    // Set some default stock values for existing products
    $conn->query("UPDATE products SET stock = FLOOR(10 + RAND() * 20) WHERE stock = 0");
    $totalStock = $conn->query("SELECT COALESCE(SUM(stock), 0) as total FROM products")->fetch_assoc()['total'];
    $lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 10 AND stock > 0")->fetch_assoc()['count'];
}

// Get latest products
$latestProducts = $conn->query("SELECT p.*, c.name as category_name 
                                FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                ORDER BY p.created_at DESC 
                                LIMIT 5");

$pageTitle = "Dashboard Admin";
include '../includes/header-admin.php';
?>

<div class="container-fluid p-0">
    <?php include '../includes/sidebar-admin.php'; ?>

    <!-- Main Content -->
    <div class="admin-content-wrapper p-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/catalog/admin/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                </ol>
            </nav>

            <!-- Welcome Banner -->
            <div class="dashboard-banner bg-primary-custom text-white mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h2 class="mb-2">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </h2>
                        <p class="mb-0 opacity-90">
                            <i class="fas fa-user-circle me-2"></i>
                            Selamat datang kembali, <strong><?php echo htmlspecialchars($adminUsername); ?></strong>!
                        </p>
                    </div>
                    <a href="/catalog/admin/products.php?action=add" class="btn btn-light mt-2 mt-md-0">
                        <i class="fas fa-plus me-2"></i>Tambah Produk Baru
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stats-card-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-primary-custom"><?php echo $totalProducts; ?></div>
                                    <div class="stats-label">Produk aktif</div>
                                </div>
                                <div class="stats-icon text-primary-custom">
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card-green h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-success"><?php echo $totalCategories; ?></div>
                                    <div class="stats-label">Kategori tersedia</div>
                                </div>
                                <div class="stats-icon text-success">
                                    <i class="fas fa-tags fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card-blue h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-info"><?php echo $totalStock; ?></div>
                                    <div class="stats-label">Unit tersedia</div>
                                </div>
                                <div class="stats-icon text-info">
                                    <i class="fas fa-warehouse fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card-yellow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-number text-warning"><?php echo $lowStock; ?></div>
                                    <div class="stats-label">Perlu restock</div>
                                </div>
                                <div class="stats-icon text-warning">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="/catalog/admin/products.php?action=add" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-plus me-2"></i>Tambah Produk
                        </a>
                        <a href="/catalog/admin/categories.php?action=add" class="btn btn-outline-primary-custom btn-lg">
                            <i class="fas fa-tags me-2"></i>Tambah Kategori
                        </a>
                        <a href="/catalog/" class="btn btn-outline-primary-custom btn-lg" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Lihat Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- Latest Products -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary-custom text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Produk Terbaru
                    </h5>
                    <a href="/catalog/admin/products.php" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-right me-1"></i>Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if ($latestProducts->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Tanggal</th>
                                    <th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = $latestProducts->fetch_assoc()): ?>
                                <tr class="align-middle">
                                    <td>
                                        <?php if($product['image']): ?>
                                        <img src="/catalog/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="Product" 
                                             class="rounded"
                                             style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #e5e7eb;">
                                        <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-custom rounded-pill px-3 py-2">
                                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary-custom">
                                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="far fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($product['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/catalog/admin/products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/catalog/admin/products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirmDelete('Apakah Anda yakin ingin menghapus produk ini?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>Belum ada produk</p>
                        <a href="/catalog/admin/products.php?action=add" class="btn btn-primary-custom">
                            <i class="fas fa-plus me-1"></i>Tambah Produk Pertama
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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

