<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireAdmin();

$conn = getConnection();
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

// Handle messages
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'success';
}

// Handle delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if category is used
    $check = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = $id")->fetch_assoc();
    if ($check['count'] > 0) {
        header('Location: /catalog/admin/categories.php?msg=Kategori tidak dapat dihapus karena masih digunakan oleh produk&type=danger');
        exit();
    }
    
    $conn->query("DELETE FROM categories WHERE id = $id");
    header('Location: /catalog/admin/categories.php?msg=Kategori berhasil dihapus&type=success');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $message = 'Nama kategori harus diisi!';
        $messageType = 'danger';
    } else {
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            $message = 'Kategori berhasil diperbarui!';
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $message = 'Kategori berhasil ditambahkan!';
        }
        $messageType = 'success';
        header('Location: /catalog/admin/categories.php?msg=' . urlencode($message) . '&type=' . $messageType);
        exit();
    }
}

// Get category for edit
$category = null;
if (($action == 'edit' || $action == 'add') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM categories WHERE id = $id");
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $action = 'edit';
    }
}

// Get all categories with product count
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           GROUP BY c.id 
                           ORDER BY c.name");

$pageTitle = $action == 'add' ? 'Tambah Kategori' : ($action == 'edit' ? 'Edit Kategori' : 'Kelola Kategori');
include '../includes/header-admin.php';
?>

<div class="container-fluid p-0">
    <?php include '../includes/sidebar-admin.php'; ?>

    <!-- Main Content -->
    <div class="admin-content-wrapper p-4">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-custom alert-dismissible fade show">
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($action == 'add' || $action == 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="card">
                    <div class="card-header bg-primary-custom text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $action == 'add' ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo $action == 'add' ? 'Tambah Kategori Baru' : 'Edit Kategori'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($action == 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" 
                                       required 
                                       autofocus>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="fas fa-save me-1"></i>Simpan
                                </button>
                                <a href="/catalog/admin/categories.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Categories List -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tags me-2"></i>Kelola Kategori</h2>
                    <a href="/catalog/admin/categories.php?action=add" class="btn btn-primary-custom">
                        <i class="fas fa-plus me-1"></i>Tambah Kategori
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if ($categories->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>NO</th>
                                        <th>Nama Kategori</th>
                                        <th>Jumlah Produk</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while($cat = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-custom">
                                                <?php echo $cat['product_count']; ?> Produk
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($cat['created_at'])); ?></td>
                                        <td>
                                            <a href="/catalog/products.php?category=<?php echo $cat['id']; ?>" 
                                               class="btn btn-sm btn-outline-info btn-action" 
                                               target="_blank"
                                               title="Lihat Produk">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/catalog/admin/categories.php?action=edit&id=<?php echo $cat['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary btn-action"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/catalog/admin/categories.php?action=delete&id=<?php echo $cat['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger btn-action"
                                               onclick="return confirmDelete('Apakah Anda yakin ingin menghapus kategori ini?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <h4>Belum ada kategori</h4>
                            <p>Mulai dengan menambahkan kategori pertama Anda</p>
                            <a href="/catalog/admin/categories.php?action=add" class="btn btn-primary-custom">
                                <i class="fas fa-plus me-1"></i>Tambah Kategori
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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

