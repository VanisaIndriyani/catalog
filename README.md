# Furniture Catalog - Sistem Katalog Furniture

Aplikasi web katalog furniture responsif dengan Bootstrap 5 dan Font Awesome, menggunakan skema warna biru tua. Dilengkapi dengan fitur keranjang belanja berbasis session.

## Fitur

### Admin Panel
- ✅ Login ke sistem
- ✅ Menambah data produk
- ✅ Mengubah data produk
- ✅ Menghapus data produk
- ✅ Mengelola kategori produk
- ✅ Mengunggah gambar produk
- ✅ Mengatur harga dan deskripsi produk

### User Interface
- ✅ Mengakses website tanpa login
- ✅ Melihat daftar furniture
- ✅ Melihat detail furniture
- ✅ Memfilter furniture berdasarkan kategori
- ✅ Pencarian furniture
- ✅ **Keranjang belanja (session-based, tanpa database)**
- ✅ **Counter keranjang di navbar dengan badge angka**
- ✅ Tambah/update/hapus item di keranjang

## Teknologi

- **PHP** - Backend
- **MySQL** - Database
- **Bootstrap 5** - Framework CSS Responsif
- **Font Awesome 6** - Icons
- **Custom CSS** - Skema warna biru tua

## Instalasi

1. Pastikan Anda menggunakan Laragon atau XAMPP dengan PHP dan MySQL aktif

2. Clone atau copy folder ini ke direktori web server Anda:
   ```
   D:\APLIKASI\laragon\www\catalog
   ```

3. Database akan dibuat otomatis saat pertama kali diakses. Atau buat database manual:
   - Nama database: `catalog_db`
   - Host: `localhost`
   - User: `root`
   - Password: `` (kosong)

4. Jalankan seeder untuk menambahkan 5 data furniture:
   ```
   http://localhost/catalog/seeder.php
   ```
   Atau jalankan via command line:
   ```bash
   php seeder.php
   ```

5. Akses aplikasi melalui browser:
   ```
   http://localhost/catalog
   ```

6. Login Admin:
   - Username: `admin`
   - Password: `admin123`

## Struktur Folder

```
catalog/
├── admin/              # Halaman admin panel
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── products.php
│   └── categories.php
├── assets/             # CSS, JS, dan file statis
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/             # Konfigurasi
│   ├── config.php
│   └── database.php
├── includes/           # Komponen yang digunakan ulang
│   ├── header.php
│   └── footer.php
├── uploads/            # Folder untuk upload gambar produk
│   └── products/
├── index.php           # Halaman beranda
├── products.php        # Halaman daftar furniture
├── product-detail.php  # Halaman detail furniture
├── cart.php            # Halaman keranjang belanja
├── cart-action.php     # Handler aksi keranjang
├── seeder.php          # Seeder 5 data furniture
└── README.md
```

## Penggunaan

### Admin

1. **Login**: Akses `/catalog/admin/login.php` dan login dengan kredensial default
2. **Dashboard**: Lihat statistik dan produk terbaru
3. **Kelola Produk**: Tambah, edit, atau hapus produk
4. **Kelola Kategori**: Buat dan kelola kategori produk

### User

1. **Beranda**: Lihat furniture terbaru dan filter kategori
2. **Daftar Furniture**: Lihat semua furniture dengan filter dan pencarian
3. **Detail Furniture**: Lihat informasi lengkap furniture dan tambah ke keranjang
4. **Keranjang**: Kelola item di keranjang (tambah, update jumlah, hapus)
   - Keranjang menggunakan session (tidak disimpan di database)
   - Counter keranjang muncul di navbar dengan badge angka

## Catatan

- Pastikan folder `uploads/products/` memiliki permission write (777)
- Ukuran maksimal upload gambar: 5MB
- Format gambar yang didukung: JPG, JPEG, PNG, GIF, WEBP
- Ganti password admin default setelah instalasi pertama
- **Keranjang belanja menggunakan session PHP (tidak disimpan di database)**
- Data keranjang akan hilang jika session berakhir atau browser ditutup
- Jalankan `seeder.php` untuk mendapatkan 5 data furniture contoh

## Screenshot

Aplikasi menggunakan desain modern dengan:
- Skema warna biru tua (#1e3a8a)
- Layout responsif untuk mobile dan desktop
- Icons Font Awesome
- Animasi hover yang smooth
- Card-based design

## Lisensi

Free to use untuk keperluan pribadi dan komersial.

