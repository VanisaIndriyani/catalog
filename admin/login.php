<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Redirect if already logged in
if (isAdmin()) {
    header('Location: /catalog/admin/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: /catalog/admin/dashboard.php');
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
        $conn->close();
    }
}

$pageTitle = "Admin Login";
include '../includes/header.php';
?>

<div class="login-page-wrapper">
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-lg-5 col-md-7 col-sm-9">
                    <div class="login-card-wrapper">
                        <!-- Logo Section -->
                        <div class="login-header text-center mb-5">
                            <div class="login-logo">
                                <i class="fas fa-couch"></i>
                            </div>
                            <h2 class="login-title mb-2">Admin Login</h2>
                            <p class="login-subtitle">Masuk ke panel administrasi toko</p>
                        </div>

                        <!-- Error Alert -->
                        <?php if ($error): ?>
                        <div class="alert alert-danger login-alert" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <div class="login-card">
                            <form method="POST" action="" class="login-form">
                                <div class="form-group mb-4">
                                    <label for="username" class="form-label fw-semibold mb-2">
                                        <i class="fas fa-user me-2 text-primary-custom"></i>Username
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="input-icon fas fa-user"></i>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="username" 
                                               name="username" 
                                               placeholder="Masukkan username"
                                               required 
                                               autofocus>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="password" class="form-label fw-semibold mb-2">
                                        <i class="fas fa-lock me-2 text-primary-custom"></i>Password
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="input-icon fas fa-lock"></i>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Masukkan password"
                                               required>
                                        <button type="button" class="password-toggle" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom w-100 btn-lg login-btn">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Dashboard
                                </button>
                            </form>

                            <div class="login-footer mt-4 pt-4 border-top">
                                <div class="text-center">
                                    <a href="/catalog/" class="back-link">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="/catalog/assets/js/main.js"></script>
</body>
</html>

