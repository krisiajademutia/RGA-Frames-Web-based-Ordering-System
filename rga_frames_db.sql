-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 25, 2026 at 01:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rga_frames_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` enum('Ready-Made','Custom-Frame-Only','Custom-Frame-Print','Print-Only') NOT NULL,
  `product_variant_id` int(11) DEFAULT NULL,
  `custom_size_id` int(11) DEFAULT NULL,
  `custom_design_id` int(11) DEFAULT NULL,
  `custom_color_id` int(11) DEFAULT NULL,
  `custom_matboard_id` int(11) DEFAULT NULL,
  `custom_mount_type` enum('Wall Hanging','With Stand','Both') DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_print_jobs`
--

CREATE TABLE `cart_print_jobs` (
  `cart_print_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `image_file_path` varchar(255) NOT NULL,
  `paper_type` enum('Photo Paper','Canvas') NOT NULL,
  `quantity_copies` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_options`
--

CREATE TABLE `custom_options` (
  `option_id` int(11) NOT NULL,
  `category` enum('Frame Design','Frame Color','Matboard') NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `price_addition` decimal(10,2) DEFAULT 0.00,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_sizes`
--

CREATE TABLE `custom_sizes` (
  `size_id` int(11) NOT NULL,
  `size_label` varchar(50) NOT NULL,
  `width_inches` decimal(5,2) DEFAULT NULL,
  `height_inches` decimal(5,2) DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `downpayment_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Partial','Paid') DEFAULT 'Partial',
  `payment_method` enum('Cash','GCash') NOT NULL,
  `payment_proof_image` varchar(255) DEFAULT NULL,
  `initial_receipt_image` varchar(255) DEFAULT NULL,
  `final_receipt_image` varchar(255) DEFAULT NULL,
  `delivery_option` enum('Pickup','Delivery') DEFAULT 'Pickup',
  `delivery_address` text DEFAULT NULL,
  `status` enum('Pending','Preparing','Ready for Pickup','To be Delivered','Completed','Cancelled','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `subtotal`, `discount_amount`, `grand_total`, `downpayment_amount`, `payment_status`, `payment_method`, `payment_proof_image`, `initial_receipt_image`, `final_receipt_image`, `delivery_option`, `delivery_address`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 500.00, 0.00, 500.00, 250.00, 'Partial', 'GCash', 'placeholder.jpg', NULL, NULL, 'Pickup', NULL, 'Pending', '2026-01-25 02:42:41', '2026-01-25 02:42:41'),
(6, 1, 2500.00, 0.00, 2500.00, 1250.00, 'Paid', 'GCash', 'uploads/test_proof.jpg', 'uploads/receipts/1769306701_617878593_1625893428895997_169389575488762968_n.jpg', 'uploads/receipts/1769306738_617878593_1625893428895997_169389575488762968_n.jpg', 'Pickup', NULL, 'Completed', '2026-01-25 02:02:01', '2026-01-25 02:05:38'),
(7, 1, 2500.00, 0.00, 2500.00, 1250.00, 'Paid', 'GCash', 'uploads/test_proof.jpg', 'uploads/receipts/1769307552_617878593_1625893428895997_169389575488762968_n.jpg', 'uploads/receipts/1769307569_617878593_1625893428895997_169389575488762968_n.jpg', 'Pickup', NULL, 'Completed', '2026-01-25 02:04:07', '2026-01-25 02:19:29'),
(8, 1, 800.00, 0.00, 800.00, 400.00, 'Partial', 'Cash', NULL, 'uploads/receipts/1769311465_617878593_1625893428895997_169389575488762968_n.jpg', NULL, 'Pickup', NULL, 'Preparing', '2026-01-25 02:04:07', '2026-01-25 03:24:25'),
(9, 1, 5000.00, 0.00, 5000.00, 2500.00, 'Partial', 'GCash', NULL, NULL, NULL, 'Delivery', NULL, 'Preparing', '2026-01-25 02:04:07', '2026-01-25 02:04:07'),
(10, 1, 1500.00, 0.00, 1500.00, 750.00, 'Partial', 'Cash', NULL, NULL, NULL, 'Pickup', NULL, 'Ready for Pickup', '2026-01-25 02:04:07', '2026-01-25 02:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `service_type` enum('Ready-Made-Frame-Only','Ready-Made-Frame-Print','Custom-Frame-Only','Custom-Frame-Print','Print-Only') NOT NULL,
  `product_variant_id` int(11) DEFAULT NULL,
  `custom_size_id` int(11) DEFAULT NULL,
  `custom_design_id` int(11) DEFAULT NULL,
  `custom_color_id` int(11) DEFAULT NULL,
  `custom_matboard_id` int(11) DEFAULT NULL,
  `custom_mount_type` enum('Wall Hanging','With Stand','Both') DEFAULT NULL,
  `frame_quantity` int(11) DEFAULT 1,
  `item_subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `service_type`, `product_variant_id`, `custom_size_id`, `custom_design_id`, `custom_color_id`, `custom_matboard_id`, `custom_mount_type`, `frame_quantity`, `item_subtotal`) VALUES
(1, 1, '', 1, NULL, NULL, NULL, NULL, NULL, 1, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_print_jobs`
--

CREATE TABLE `order_print_jobs` (
  `print_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `image_file_path` varchar(255) NOT NULL,
  `paper_type` enum('Photo Paper','Canvas') NOT NULL,
  `quantity_copies` int(11) DEFAULT 1,
  `print_cost_per_copy` decimal(10,2) DEFAULT NULL,
  `total_print_cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `frame_name` varchar(150) NOT NULL,
  `frame_design` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `base_image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `frame_name`, `frame_design`, `description`, `base_image_url`, `is_active`, `created_at`) VALUES
(1, 'Classic Wood Frame', 'Wood', 'Elegant wooden frame', NULL, 1, '2026-01-25 02:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `matboard` varchar(50) DEFAULT 'None',
  `mount_type` varchar(50) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price_frame_only` decimal(10,2) NOT NULL,
  `price_with_print` decimal(10,2) NOT NULL,
  `variant_image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `size`, `color`, `matboard`, `mount_type`, `stock_quantity`, `price_frame_only`, `price_with_print`, `variant_image_url`) VALUES
(1, 1, 'A4', 'Brown', 'None', '', 50, 500.00, 700.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `phone_number`, `password`, `role`, `address`, `created_at`) VALUES
(1, 'Krisia Jade', 'Mutia', '09306282413', '$2y$10$0nmdP1V6OB.lQ/5zpq6GtegPgps/c/rLMvX6hUvq6ljapMy0PqORu', 'admin', 'Lupon, Davao Oriental', '2026-01-24 13:40:24'),
(2, 'Daday', 'Ett', '09234344478', '$2y$10$7aB0PdH65PNPu8G.Kf0tYuIQ/zqDGoaZm3/EBW9VjTs3o1ZSnaq4a', 'customer', 'Tagum, Davao Del Norte', '2026-01-25 06:41:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_variant_id` (`product_variant_id`),
  ADD KEY `custom_design_id` (`custom_design_id`),
  ADD KEY `custom_color_id` (`custom_color_id`),
  ADD KEY `custom_matboard_id` (`custom_matboard_id`),
  ADD KEY `custom_size_id` (`custom_size_id`);

--
-- Indexes for table `cart_print_jobs`
--
ALTER TABLE `cart_print_jobs`
  ADD PRIMARY KEY (`cart_print_id`),
  ADD KEY `cart_id` (`cart_id`);

--
-- Indexes for table `custom_options`
--
ALTER TABLE `custom_options`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `custom_sizes`
--
ALTER TABLE `custom_sizes`
  ADD PRIMARY KEY (`size_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_variant_id` (`product_variant_id`),
  ADD KEY `custom_size_id` (`custom_size_id`),
  ADD KEY `custom_design_id` (`custom_design_id`),
  ADD KEY `custom_color_id` (`custom_color_id`),
  ADD KEY `custom_matboard_id` (`custom_matboard_id`);

--
-- Indexes for table `order_print_jobs`
--
ALTER TABLE `order_print_jobs`
  ADD PRIMARY KEY (`print_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_print_jobs`
--
ALTER TABLE `cart_print_jobs`
  MODIFY `cart_print_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_options`
--
ALTER TABLE `custom_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_sizes`
--
ALTER TABLE `custom_sizes`
  MODIFY `size_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_print_jobs`
--
ALTER TABLE `order_print_jobs`
  MODIFY `print_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`variant_id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`custom_design_id`) REFERENCES `custom_options` (`option_id`),
  ADD CONSTRAINT `cart_ibfk_4` FOREIGN KEY (`custom_color_id`) REFERENCES `custom_options` (`option_id`),
  ADD CONSTRAINT `cart_ibfk_5` FOREIGN KEY (`custom_matboard_id`) REFERENCES `custom_options` (`option_id`),
  ADD CONSTRAINT `cart_ibfk_6` FOREIGN KEY (`custom_size_id`) REFERENCES `custom_sizes` (`size_id`);

--
-- Constraints for table `cart_print_jobs`
--
ALTER TABLE `cart_print_jobs`
  ADD CONSTRAINT `cart_print_jobs_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`variant_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`custom_size_id`) REFERENCES `custom_sizes` (`size_id`),
  ADD CONSTRAINT `order_items_ibfk_4` FOREIGN KEY (`custom_design_id`) REFERENCES `custom_options` (`option_id`),
  ADD CONSTRAINT `order_items_ibfk_5` FOREIGN KEY (`custom_color_id`) REFERENCES `custom_options` (`option_id`),
  ADD CONSTRAINT `order_items_ibfk_6` FOREIGN KEY (`custom_matboard_id`) REFERENCES `custom_options` (`option_id`);

--
-- Constraints for table `order_print_jobs`
--
ALTER TABLE `order_print_jobs`
  ADD CONSTRAINT `order_print_jobs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `order_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
