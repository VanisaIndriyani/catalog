<!-- Sidebar -->
<div class="admin-sidebar">
    <div class="admin-sidebar-header text-center">
        <div class="admin-sidebar-logo">
            <i class="fas fa-couch"></i>
        </div>
        <h5 class="text-white mb-1 fw-bold">Admin Panel</h5>
        <small class="text-white-50">DIVA MITRA COMPUTER</small>
    </div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" 
               href="/catalog/admin/dashboard.php">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>" 
               href="/catalog/admin/products.php">
                <i class="fas fa-box me-2"></i>Produk
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : ''; ?>" 
               href="/catalog/admin/categories.php">
                <i class="fas fa-tags me-2"></i>Kategori
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/catalog/">
                <i class="fas fa-globe me-2"></i>Lihat Website
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="/catalog/admin/logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </li>
    </ul>
</div>

