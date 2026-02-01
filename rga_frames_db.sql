-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 01, 2026 at 02:53 PM
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
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_custom_frame_product`
--

CREATE TABLE `tbl_custom_frame_product` (
  `c_product_id` int(11) NOT NULL,
  `frame_type_id` int(11) DEFAULT NULL,
  `frame_design_id` int(11) DEFAULT NULL,
  `frame_color_id` int(11) DEFAULT NULL,
  `frame_size_id` int(11) DEFAULT NULL,
  `product_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_colors`
--

CREATE TABLE `tbl_frame_colors` (
  `frame_color_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_designs`
--

CREATE TABLE `tbl_frame_designs` (
  `frame_design_id` int(11) NOT NULL,
  `design_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_order_items`
--

CREATE TABLE `tbl_frame_order_items` (
  `order_item_id` int(11) NOT NULL,
  `frame_category` enum('READY_MADE','CUSTOM') NOT NULL,
  `product_id` int(11) NOT NULL,
  `source_type` enum('CART','ORDER') NOT NULL,
  `source_id` int(11) NOT NULL,
  `primary_matboard_id` int(11) NOT NULL,
  `secondary_matboard_id` int(11) DEFAULT NULL,
  `mount_type_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `extra_price` decimal(10,2) DEFAULT 0.00,
  `sub_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_sizes`
--

CREATE TABLE `tbl_frame_sizes` (
  `frame_size_id` int(11) NOT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `width_inch` decimal(5,2) DEFAULT NULL,
  `height_inch` decimal(5,2) DEFAULT NULL,
  `total_inch` decimal(5,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_types`
--

CREATE TABLE `tbl_frame_types` (
  `frame_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `type_price` decimal(10,2) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_matboard_colors`
--

CREATE TABLE `tbl_matboard_colors` (
  `matboard_color_id` int(11) NOT NULL,
  `matboard_color_name` varchar(50) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mount_type`
--

CREATE TABLE `tbl_mount_type` (
  `mount_type_id` int(11) NOT NULL,
  `mount_name` enum('WALL_HANGING','WITH_STAND') NOT NULL,
  `additional_fee` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notifications`
--

CREATE TABLE `tbl_notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` enum('CASH','G-CASH') DEFAULT NULL,
  `delivery_status` tinyint(4) DEFAULT 0 COMMENT '\r\n        0 - Pending (Customer) / New Order (Admin)\r\n        1 - Processing\r\n        2 - For Pickup\r\n        3 - For Delivery\r\n        4 - Completed\r\n        5 - Rejected\r\n        6 - Cancelled\r\n    ',
  `delivery_option` enum('FOR PICK-UP','FOR DELIVERY') DEFAULT 'FOR PICK-UP',
  `delivery_address` text DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paper_type`
--

CREATE TABLE `tbl_paper_type` (
  `paper_type_id` int(11) NOT NULL,
  `paper_name` varchar(100) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `width_inch` decimal(5,2) DEFAULT NULL,
  `height_inch` decimal(5,2) DEFAULT NULL,
  `total_inch` decimal(5,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('PARTIAL','FULL') DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_printing_order_items`
--

CREATE TABLE `tbl_printing_order_items` (
  `printing_order_item_id` int(11) NOT NULL,
  `source_type` enum('Cart','Order') NOT NULL,
  `source_id` int(11) NOT NULL,
  `paper_type_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ready_made_product`
--

CREATE TABLE `tbl_ready_made_product` (
  `r_product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `frame_type_id` int(11) DEFAULT NULL,
  `frame_design_id` int(11) DEFAULT NULL,
  `frame_color_id` int(11) DEFAULT NULL,
  `frame_size_id` int(11) DEFAULT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ready_made_product_stocks`
--

CREATE TABLE `tbl_ready_made_product_stocks` (
  `stock_id` int(11) NOT NULL,
  `r_product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('CUSTOMER','ADMIN') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  ADD PRIMARY KEY (`c_product_id`),
  ADD KEY `frame_type_id` (`frame_type_id`),
  ADD KEY `frame_design_id` (`frame_design_id`),
  ADD KEY `frame_color_id` (`frame_color_id`),
  ADD KEY `frame_size_id` (`frame_size_id`);

--
-- Indexes for table `tbl_frame_colors`
--
ALTER TABLE `tbl_frame_colors`
  ADD PRIMARY KEY (`frame_color_id`);

--
-- Indexes for table `tbl_frame_designs`
--
ALTER TABLE `tbl_frame_designs`
  ADD PRIMARY KEY (`frame_design_id`);

--
-- Indexes for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `mount_type_id` (`mount_type_id`),
  ADD KEY `primary_matboard_id` (`primary_matboard_id`),
  ADD KEY `secondary_matboard_id` (`secondary_matboard_id`);

--
-- Indexes for table `tbl_frame_sizes`
--
ALTER TABLE `tbl_frame_sizes`
  ADD PRIMARY KEY (`frame_size_id`);

--
-- Indexes for table `tbl_frame_types`
--
ALTER TABLE `tbl_frame_types`
  ADD PRIMARY KEY (`frame_type_id`);

--
-- Indexes for table `tbl_matboard_colors`
--
ALTER TABLE `tbl_matboard_colors`
  ADD PRIMARY KEY (`matboard_color_id`);

--
-- Indexes for table `tbl_mount_type`
--
ALTER TABLE `tbl_mount_type`
  ADD PRIMARY KEY (`mount_type_id`);

--
-- Indexes for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_paper_type`
--
ALTER TABLE `tbl_paper_type`
  ADD PRIMARY KEY (`paper_type_id`);

--
-- Indexes for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  ADD PRIMARY KEY (`printing_order_item_id`),
  ADD KEY `paper_type_id` (`paper_type_id`);

--
-- Indexes for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  ADD PRIMARY KEY (`r_product_id`),
  ADD KEY `frame_type_id` (`frame_type_id`),
  ADD KEY `frame_design_id` (`frame_design_id`),
  ADD KEY `frame_color_id` (`frame_color_id`),
  ADD KEY `frame_size_id` (`frame_size_id`);

--
-- Indexes for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `r_product_id` (`r_product_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  MODIFY `c_product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_colors`
--
ALTER TABLE `tbl_frame_colors`
  MODIFY `frame_color_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_designs`
--
ALTER TABLE `tbl_frame_designs`
  MODIFY `frame_design_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_sizes`
--
ALTER TABLE `tbl_frame_sizes`
  MODIFY `frame_size_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_types`
--
ALTER TABLE `tbl_frame_types`
  MODIFY `frame_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_matboard_colors`
--
ALTER TABLE `tbl_matboard_colors`
  MODIFY `matboard_color_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_mount_type`
--
ALTER TABLE `tbl_mount_type`
  MODIFY `mount_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_paper_type`
--
ALTER TABLE `tbl_paper_type`
  MODIFY `paper_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  MODIFY `printing_order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  MODIFY `r_product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD CONSTRAINT `tbl_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_1` FOREIGN KEY (`frame_type_id`) REFERENCES `tbl_frame_types` (`frame_type_id`),
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_2` FOREIGN KEY (`frame_design_id`) REFERENCES `tbl_frame_designs` (`frame_design_id`),
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_3` FOREIGN KEY (`frame_color_id`) REFERENCES `tbl_frame_colors` (`frame_color_id`),
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_4` FOREIGN KEY (`frame_size_id`) REFERENCES `tbl_frame_sizes` (`frame_size_id`);

--
-- Constraints for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_1` FOREIGN KEY (`mount_type_id`) REFERENCES `tbl_mount_type` (`mount_type_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_2` FOREIGN KEY (`primary_matboard_id`) REFERENCES `tbl_matboard_colors` (`matboard_color_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_3` FOREIGN KEY (`secondary_matboard_id`) REFERENCES `tbl_matboard_colors` (`matboard_color_id`);

--
-- Constraints for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD CONSTRAINT `tbl_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD CONSTRAINT `tbl_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD CONSTRAINT `tbl_payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`);

--
-- Constraints for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  ADD CONSTRAINT `tbl_printing_order_items_ibfk_1` FOREIGN KEY (`paper_type_id`) REFERENCES `tbl_paper_type` (`paper_type_id`);

--
-- Constraints for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_1` FOREIGN KEY (`frame_type_id`) REFERENCES `tbl_frame_types` (`frame_type_id`),
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_2` FOREIGN KEY (`frame_design_id`) REFERENCES `tbl_frame_designs` (`frame_design_id`),
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_3` FOREIGN KEY (`frame_color_id`) REFERENCES `tbl_frame_colors` (`frame_color_id`),
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_4` FOREIGN KEY (`frame_size_id`) REFERENCES `tbl_frame_sizes` (`frame_size_id`);

--
-- Constraints for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  ADD CONSTRAINT `tbl_ready_made_product_stocks_ibfk_1` FOREIGN KEY (`r_product_id`) REFERENCES `tbl_ready_made_product` (`r_product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
