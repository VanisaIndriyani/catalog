<?php
/**
 * Seeder untuk 5 Data Furniture
 * Jalankan file ini sekali untuk mengisi database dengan data furniture
 */

require_once 'config/config.php';
require_once 'config/database.php';

$conn = getConnection();

// Clear existing data (optional - comment out if you want to keep existing data)
// $conn->query("DELETE FROM products");
// $conn->query("DELETE FROM categories");

// Clear existing categories and products first
$conn->query("DELETE FROM products");
$conn->query("DELETE FROM categories");

// Insert Categories
$categories = [
    ['name' => 'Laptop'],
    ['name' => 'Printer'],
    ['name' => 'Aksesoris'],
    ['name' => 'Meja'],
    ['name' => 'Tempat Tidur'],
    ['name' => 'Lemari & Rak']
];

echo "Menambahkan kategori...\n";
foreach ($categories as $cat) {
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param('s', $cat['name']);
    $stmt->execute();
    echo "✓ Kategori: {$cat['name']}\n";
}

// Get category IDs
$catIds = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $catIds[$row['name']] = $row['id'];
}

// Insert Furniture Products (sesuai gambar)
$produk = [
    [
        'name' => 'Laptop Dell Intel® Core™ i7-10610U | RAM 8GB',
        'description' => 'Laptop Dell ini hadir dengan prosesor Intel® Core™ i7-10610U yang tangguh, cocok untuk kebutuhan kerja, kuliah, hingga multitasking harian.',
        'price' => 4500000,
        'image' => 'uploads/products/1770403775_laptop.jpg',
        'category_id' => $catIds['Laptop']
    ],
    [
        'name' => 'Printer Canon imageCLASS LBP122dw Wireless',
        'description' => 'Canon imageCLASS LBP122dw adalah printer laser hitam-putih berkinerja tinggi yang ideal untuk kebutuhan cetak dokumen profesional.',
        'price' => 3500000,
        'image' => 'uploads/products/1770401636_printer_canon.jpg',
        'category_id' => $catIds['Printer']
    ],
    [
        'name' => 'Laptop Acer Aspire 5 Slim',
        'description' => 'Laptop Acer Aspire 5 Slim dengan desain tipis dan performa handal untuk multitasking.',
        'price' => 5200000,
        'image' => 'uploads/products/1770404799_laptop_acer.jpg',
        'category_id' => $catIds['Laptop']
    ],
    [
        'name' => 'Laptop HP Pavilion 14',
        'description' => 'Laptop HP Pavilion 14 menawarkan performa tinggi dengan desain yang elegan.',
        'price' => 6500000,
        'image' => 'uploads/products/1770405341_Laptop_HP.jpg',
        'category_id' => $catIds['Laptop']
    ],
    [
        'name' => 'Lemari Pakaian 3 Pintu Modern',
        'description' => 'Lemari pakaian modern dengan 3 pintu sliding. Bahan MDF dengan finishing high gloss.',
        'price' => 4500000,
        'image' => null,
        'category_id' => $catIds['Lemari & Rak']
    ]
];

// Add stock column if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER price");
}

echo "\nMenambahkan produk...\n";
$stockValues = [15, 20, 10, 8, 18]; // Stock untuk setiap produk
$i = 0;
foreach ($produk as $item) {
    $stock = $stockValues[$i] ?? 10;
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdiii', $item['name'], $item['description'], $item['price'], $item['image'], $stock, $item['category_id']);
    $stmt->execute();
    echo "✓ {$item['name']} - Rp " . number_format($item['price'], 0, ',', '.') . " (Stok: $stock)\n";
    $i++;
}

$conn->close();

echo "\n✅ Seeder selesai! 5 data produk berhasil ditambahkan.\n";
echo "Akses website di: http://localhost/catalog\n";
?>

