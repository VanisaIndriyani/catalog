<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireAdmin();

$conn = getConnection();
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

// Handle messages from actions
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'success';
}

// Handle delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get product image to delete
    $product = $conn->query("SELECT image FROM products WHERE id = $id")->fetch_assoc();
    if ($product && $product['image'] && file_exists('../' . $product['image'])) {
        unlink('../' . $product['image']);
    }
    
    $conn->query("DELETE FROM products WHERE id = $id");
    header('Location: /catalog/admin/products.php?msg=Produk berhasil dihapus&type=success');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = isset($_POST['category_id']) && $_POST['category_id'] > 0 ? (int)$_POST['category_id'] : null;
    
    $hasError = false;
    
    if (empty($name) || $price <= 0) {
        $message = 'Nama produk dan harga harus diisi!';
        $messageType = 'danger';
        $hasError = true;
    } else {
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = '../' . UPLOAD_DIR;
            
            // Ensure upload directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            // Check file type
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($imageFileType, $allowedTypes)) {
                // Check file size (5MB max)
                if ($_FILES['image']['size'] > 5000000) {
                    $message = 'Ukuran file terlalu besar! Maksimal 5MB.';
                    $messageType = 'danger';
                    $hasError = true;
                } else {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $imagePath = UPLOAD_DIR . $fileName;
                        
                        // Delete old image if updating
                        if ($id > 0) {
                            $oldProduct = $conn->query("SELECT image FROM products WHERE id = $id")->fetch_assoc();
                            if ($oldProduct && $oldProduct['image'] && file_exists('../' . $oldProduct['image'])) {
                                unlink('../' . $oldProduct['image']);
                            }
                        }
                    } else {
                        $message = 'Gagal mengupload gambar. Pastikan folder uploads/products memiliki permission write.';
                        $messageType = 'danger';
                        $hasError = true;
                    }
                }
            } else {
                $message = 'Format file tidak didukung! Gunakan JPG, PNG, GIF, atau WEBP.';
                $messageType = 'danger';
                $hasError = true;
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != 0 && $_FILES['image']['error'] != 4) {
            // Error code 4 means no file uploaded (which is OK)
            $uploadErrors = [
                1 => 'File terlalu besar (melebihi upload_max_filesize)',
                2 => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
                3 => 'File hanya ter-upload sebagian',
                6 => 'Folder temporary tidak ditemukan',
                7 => 'Gagal menulis file ke disk',
                8 => 'Upload dihentikan oleh extension'
            ];
            $errorMsg = $uploadErrors[$_FILES['image']['error']] ?? 'Error upload tidak diketahui';
            $message = 'Error upload: ' . $errorMsg;
            $messageType = 'danger';
            $hasError = true;
        }
        
        // Only proceed with insert/update if no error occurred
        if (!$hasError) {
            if ($id > 0) {
                // Update - preserve old image if no new image uploaded
                if ($imagePath) {
                    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, image=? WHERE id=?");
                    $stmt->bind_param('ssdiss', $name, $description, $price, $category_id, $imagePath, $id);
                } else {
                    // Keep existing image when updating without new upload
                    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=? WHERE id=?");
                    $stmt->bind_param('ssdii', $name, $description, $price, $category_id, $id);
                }
                $stmt->execute();
                $message = 'Produk berhasil diperbarui!';
        } else {
            // Insert
            if (!$imagePath) {
                $imagePath = null;
            }
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdis', $name, $description, $price, $category_id, $imagePath);
            $stmt->execute();
            $message = 'Produk berhasil ditambahkan!';
            }
            $messageType = 'success';
            header('Location: /catalog/admin/products.php?msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }
        // If there's an error, stay on the form page to show the error message
    }
}

// Get product for edit
$product = null;
if (($action == 'edit' || $action == 'add') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $action = 'edit';
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get all products for list view
$products = $conn->query("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY p.created_at DESC");

$pageTitle = $action == 'add' ? 'Tambah Produk' : ($action == 'edit' ? 'Edit Produk' : 'Kelola Produk');
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
                            <?php echo $action == 'add' ? 'Tambah Produk Baru' : 'Edit Produk'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($action == 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="0">Pilih Kategori</option>
                                    <?php 
                                    $categories->data_seek(0);
                                    while($category = $categories->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($product['category_id']) && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="price" 
                                           name="price" 
                                           step="0.01" 
                                           min="0" 
                                           value="<?php echo $product['price'] ?? ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Gambar Produk</label>
                                <?php if ($action == 'edit' && $product['image']): ?>
                                <div class="mb-2">
                                    <img src="/catalog/<?php echo htmlspecialchars($product['image']); ?>" 
                                         id="imagePreview" 
                                         alt="Current Image" 
                                         style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                </div>
                                <?php else: ?>
                                <img id="imagePreview" 
                                     src="" 
                                     alt="Preview" 
                                     style="max-width: 200px; max-height: 200px; border-radius: 5px; display: none;">
                                <?php endif; ?>
                                <input type="file" 
                                       class="form-control" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*"
                                       onchange="previewImage(this, 'imagePreview')">
                                <small class="form-text text-muted">Format: JPG, PNG, GIF, WEBP. Maksimal 5MB</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="fas fa-save me-1"></i>Simpan
                                </button>
                                <a href="/catalog/admin/products.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Products List -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-box me-2"></i>Kelola Produk</h2>
                    <a href="/catalog/admin/products.php?action=add" class="btn btn-primary-custom">
                        <i class="fas fa-plus me-1"></i>Tambah Produk
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if ($products->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($prod = $products->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if($prod['image']): ?>
                                            <img src="/catalog/<?php echo htmlspecialchars($prod['image']); ?>" 
                                                 alt="Product" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prod['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-custom">
                                                <?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?>
                                            </span>
                                        </td>
                                        <td>Rp <?php echo number_format($prod['price'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($prod['created_at'])); ?></td>
                                        <td>
                                            <a href="/catalog/product-detail.php?id=<?php echo $prod['id']; ?>" 
                                               class="btn btn-sm btn-outline-info btn-action" 
                                               target="_blank"
                                               title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/catalog/admin/products.php?action=edit&id=<?php echo $prod['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary btn-action"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/catalog/admin/products.php?action=delete&id=<?php echo $prod['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger btn-action"
                                               onclick="return confirmDelete('Apakah Anda yakin ingin menghapus produk ini?')"
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
                            <i class="fas fa-box-open"></i>
                            <h4>Belum ada produk</h4>
                            <p>Mulai dengan menambahkan produk pertama Anda</p>
                            <a href="/catalog/admin/products.php?action=add" class="btn btn-primary-custom">
                                <i class="fas fa-plus me-1"></i>Tambah Produk
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

