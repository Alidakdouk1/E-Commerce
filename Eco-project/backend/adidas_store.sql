-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 21, 2025 at 16:05
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adidas_store`
--
CREATE DATABASE IF NOT EXISTS `adidas_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `adidas_store`;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_zip` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `subcategory`, `image`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'Superstar II Shoes', 'The classic Adidas Superstar II shoes with iconic shell toe design.', '100.00', 'Men', 'Shoes', 'images/products/superstar.jpg', 50, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(2, 'Ultra Boost 5.0', 'Experience ultimate comfort with Ultra Boost 5.0 running shoes.', '180.00', 'Men', 'Shoes', 'images/products/ultraboost.jpg', 35, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(3, 'Stan Smith Classic', 'The timeless Stan Smith tennis shoes with clean, minimalist design.', '90.00', 'Men', 'Shoes', 'images/products/stansmith.jpg', 45, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(4, 'NMD R1 Primeknit', 'Modern NMD R1 with breathable Primeknit upper for urban style.', '150.00', 'Men', 'Shoes', 'images/products/nmd.jpg', 25, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(5, 'Tiro 21 Training Pants', 'Sleek Tiro 21 training pants with iconic 3-stripes design.', '45.00', 'Men', 'Clothing', 'images/products/tiro.jpg', 60, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(6, 'Essentials 3-Stripes Hoodie', 'Comfortable cotton blend hoodie with classic 3-stripes branding.', '65.00', 'Men', 'Clothing', 'images/products/hoodie.jpg', 40, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(7, 'Trefoil T-Shirt', 'Classic cotton t-shirt featuring the iconic Adidas Trefoil logo.', '30.00', 'Men', 'Clothing', 'images/products/tshirt.jpg', 75, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(8, 'Ultraboost 22 Women\'s', 'Women\'s Ultraboost 22 with adaptive support and responsive cushioning.', '190.00', 'Women', 'Shoes', 'images/products/ultraboost_women.jpg', 30, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(9, 'Cloudfoam Pure Shoes', 'Lightweight Cloudfoam Pure shoes for everyday comfort.', '75.00', 'Women', 'Shoes', 'images/products/cloudfoam.jpg', 40, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(10, 'Believe This 2.0 Tights', 'High-rise training tights with supportive fit and moisture management.', '50.00', 'Women', 'Clothing', 'images/products/tights.jpg', 55, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(11, 'Cropped Hoodie', 'Stylish cropped hoodie with drawcord-adjustable hood.', '60.00', 'Women', 'Clothing', 'images/products/crop_hoodie.jpg', 35, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(12, 'Kids Duramo SL Shoes', 'Durable and comfortable running shoes for active kids.', '45.00', 'Kids', 'Shoes', 'images/products/kids_shoes.jpg', 50, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(13, 'Kids Essentials 3-Stripes Joggers', 'Comfortable joggers with elastic waist and iconic 3-stripes.', '35.00', 'Kids', 'Clothing', 'images/products/kids_joggers.jpg', 45, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(14, 'Classic Backpack', 'Spacious backpack with padded laptop sleeve and multiple compartments.', '40.00', 'Accessories', 'Bags', 'images/products/backpack.jpg', 60, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(15, 'Training Gym Sack', 'Lightweight drawstring gym sack for your essentials.', '20.00', 'Accessories', 'Bags', 'images/products/gym_sack.jpg', 70, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(16, 'Trefoil Baseball Cap', 'Classic cotton twill cap with embroidered Trefoil logo.', '25.00', 'Accessories', 'Headwear', 'images/products/cap.jpg', 80, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(17, 'Inter Miami CF Messi Home Jersey', 'Official Inter Miami CF home jersey featuring Messi\'s name and number.', '150.00', 'Sports', 'Football', 'images/products/messi_jersey.jpg', 25, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(18, 'Predator Match Goalkeeper Gloves', 'Professional goalkeeper gloves with enhanced grip and protection.', '65.00', 'Sports', 'Football', 'images/products/goalkeeper_gloves.jpg', 30, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(19, 'Tiro Competition Soccer Ball', 'FIFA Quality Pro certified match ball with seamless surface.', '40.00', 'Sports', 'Football', 'images/products/soccer_ball.jpg', 40, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(20, 'Ultraboost Light Running Shoes', 'The lightest Ultraboost ever, designed for serious runners.', '200.00', 'Sports', 'Running', 'images/products/ultraboost_light.jpg', 20, '2025-05-21 16:00:00', '2025-05-21 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@example.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-05-21 16:00:00', '2025-05-21 16:00:00'),
(2, 'John', 'Doe', 'john@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-05-21 16:00:00', '2025-05-21 16:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `subcategory` (`subcategory`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
