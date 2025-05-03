-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 05, 2025 at 06:26 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nishan_bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `delivery_location` varchar(255) DEFAULT NULL,
  `customer_coordinates` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','processing','delivered','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category`) VALUES
(1, 'Test cake', 'Rich chocolate cake with ganache frosting', 28.99, 'images\\cakes.png', 'Cakes'),
(2, 'Vanilla Cupcake', 'Classic vanilla cupcake with buttercream frosting', 3.49, 'vanilla_cupcake.jpg', 'Cupcakes'),
(3, 'Cinnamon Roll', 'Freshly baked cinnamon roll with cream cheese glaze', 4.99, 'cinnamon_roll.jpg', 'Pastries'),
(4, 'Sourdough Bread', 'Artisan sourdough bread baked daily', 6.99, 'sourdough.jpg', 'Breads'),
(5, 'Croissant', 'Buttery, flaky French croissant', 2.99, 'croissant.jpg', 'Pastries'),
(6, 'Strawberry Tart', 'Sweet pastry crust filled with custard and fresh strawberries', 5.99, 'strawberry_tart.jpg', 'Pastries'),
(7, 'Baguette', 'Traditional French baguette with crispy crust', 3.99, 'baguette.jpg', 'Breads'),
(8, 'Chocolate Chip Cookie', 'Classic chocolate chip cookie with chunks of chocolate', 1.99, 'chocolate_chip_cookie.jpg', 'Cookies'),
(9, 'Red Velvet Cake', 'Moist red velvet cake with cream cheese frosting', 32.99, 'red_velvet_cake.jpg', 'Cakes'),
(10, 'Oatmeal Raisin Cookie', 'Chewy oatmeal cookie with raisins', 1.99, 'oatmeal_cookie.jpg', 'Cookies'),
(11, 'Blueberry Muffin', 'Fresh blueberry muffin with streusel topping', 3.49, 'blueberry_muffin.jpg', 'Muffins'),
(12, 'French Macaron', 'Delicate French macaron with ganache filling', 2.49, 'macaron.jpg', 'Cookies');

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

DROP TABLE IF EXISTS `user_accounts`;
CREATE TABLE IF NOT EXISTS `user_accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text,
  `phone` int DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('customer','admin','staff') DEFAULT 'customer',
  `profile_image` varchar(255) DEFAULT NULL,
  `job_role` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_accounts`
--

INSERT INTO `user_accounts` (`id`, `name`, `email`, `address`, `phone`, `password`, `user_type`, `profile_image`, `job_role`, `created_at`, `updated_at`) VALUES
(1, 'Thisara Silva', 'thisara@gmail.com', '177/2,ragama', 778779453, '$2y$10$b4dQ5iHEueaCnovtOHmdcue3/NokppsVGHy05gUkah/Oxyf84E9tW', 'customer', 'uploads/67c6d3db81340.jpg', NULL, '2025-02-25 11:20:20', '2025-03-04 10:20:11'),
(26, 'admin', 'admin@gmail.com', NULL, 0, '$2y$10$Gunz04BBm8oX4B5y81SziOjx..XWJphD.0J/P.aKgtrLhkrLdD6oi', 'admin', '', NULL, '2025-02-25 11:53:41', '2025-02-26 14:54:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
