-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2025 at 03:50 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catalog_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$n9z7YcYHFdLxClcBgTIJxeFCBpRUvdAcEBqQdFij9kxu3zloT/3xK', '2025-12-17 00:17:56');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(11, 'Dekorasi', '2025-12-17 06:03:38'),
(12, 'Kursi & Sofa', '2025-12-17 06:03:38'),
(13, 'Lemari & Rak', '2025-12-17 06:03:38'),
(14, 'Meja', '2025-12-17 06:03:38'),
(15, 'Tempat Tidur', '2025-12-17 06:03:38');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category_id`, `created_at`, `updated_at`) VALUES
(12, 'Sofa Minimalis 3 Seater', 'Sofa modern dengan desain minimalis yang nyaman untuk ruang tamu. Terbuat dari bahan berkualitas tinggi dengan bantalan empuk. Tersedia dalam berbagai warna.', 3500000.00, 15, 'uploads/products/1765988024_Sofa Minimalis 3 Seater.jpg', 12, '2025-12-17 06:03:38', '2025-12-17 16:13:44'),
(13, 'Meja Makan Kayu Jati 6 Kursi', 'Meja makan klasik terbuat dari kayu jati asli dengan finishing natural. Ukuran 180x90 cm, cocok untuk 6-8 orang. Tahan lama dan mudah dirawat. Dilengkapi dengan 6 kursi.', 8500000.00, 20, 'uploads/products/1765988037_Meja Makan Kayu Jati.jpg', 14, '2025-12-17 06:03:38', '2025-12-17 16:13:57'),
(14, 'Lemari Pakaian 3 Pintu Modern', 'Lemari pakaian modern dengan 3 pintu sliding. Dilengkapi rak, gantungan, dan laci. Bahan MDF dengan finishing high gloss. Ukuran 180x60x200 cm.', 4500000.00, 18, 'uploads/products/1765988047_Kursi Ergonomis Kantor.jpg', 13, '2025-12-17 06:03:38', '2025-12-17 16:14:07'),
(15, 'Tempat Tidur King Size Minimalis', 'Tempat tidur king size dengan desain minimalis modern. Frame terbuat dari kayu solid yang kuat dan tahan lama. Dilengkapi dengan headboard yang nyaman.', 5500000.00, 12, 'uploads/products/1765988057_Rak Buku Minimalis.jpg', 15, '2025-12-17 06:03:38', '2025-12-17 16:14:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
