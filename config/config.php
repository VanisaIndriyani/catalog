<?php
session_start();

// Site configuration
define('SITE_NAME', 'DIVA MITRA COMPUTER');
define('SITE_URL', 'http://localhost/catalog');
define('UPLOAD_DIR', 'uploads/products/');

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Check if user is logged in as admin
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /catalog/admin/login.php');
        exit();
    }
}

// Cart functions (session-based, no database)
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function getCartCount() {
    initCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function addToCart($productId, $quantity = 1) {
    initCart();
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = ['quantity' => $quantity];
    }
}

function updateCart($productId, $quantity) {
    initCart();
    if ($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }
}

function removeFromCart($productId) {
    initCart();
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}
?>

