-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 07:09 AM
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
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `admin_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`admin_id`, `first_name`, `last_name`, `username`, `email`, `password`, `created_at`, `last_login`) VALUES
(1, 'Admin', 'User', 'admin', 'mutiakrisiaj@gmail.com', '$2y$10$s4WDlv178NFxurKY2nSHnOjqM70Afhlk37Lx.T68KYTpMofJ1SnqO', '2026-02-18 12:57:20', '2026-02-19 21:55:25');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_customer`
--

INSERT INTO `tbl_customer` (`customer_id`, `first_name`, `last_name`, `username`, `email`, `password`, `phone_number`, `created_at`) VALUES
(1, 'Krisia Jade', 'Mutia', 'krisia_jade', 'mutiakrisiajade@gmail.com', '$2y$10$eRG5oCTiS7hVF8bhLiT11OiNc5pbJh6P6OP42W7VJSXWO6gwIRM.q', '09306282413', '2026-02-18 01:32:41');

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
  `custom_width` decimal(5,2) NOT NULL,
  `custom_height` decimal(5,2) NOT NULL,
  `calculated_price` decimal(10,2) NOT NULL
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

--
-- Dumping data for table `tbl_frame_colors`
--

INSERT INTO `tbl_frame_colors` (`frame_color_id`, `color_name`, `is_active`) VALUES
(1, 'Red', 1),
(2, 'Gold', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_designs`
--

CREATE TABLE `tbl_frame_designs` (
  `frame_design_id` int(11) NOT NULL,
  `design_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_frame_designs`
--

INSERT INTO `tbl_frame_designs` (`frame_design_id`, `design_name`, `price`, `image_name`, `is_active`) VALUES
(1, 'DESIGN1', 450.00, '1771421550_3in-Frame-4-b-1900x1900.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_order_items`
--

CREATE TABLE `tbl_frame_order_items` (
  `item_id` int(11) NOT NULL,
  `frame_category` enum('READY_MADE','CUSTOM') NOT NULL,
  `r_product_id` int(11) DEFAULT NULL,
  `c_product_id` int(11) DEFAULT NULL,
  `source_type` enum('CART','ORDER') NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `service_type` enum('FRAME_ONLY','FRAME&PRINT') NOT NULL,
  `printing_order_item_id` int(11) DEFAULT NULL,
  `primary_matboard_id` int(11) DEFAULT NULL,
  `secondary_matboard_id` int(11) DEFAULT NULL,
  `mount_type_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `extra_price` decimal(10,2) NOT NULL,
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

--
-- Dumping data for table `tbl_frame_sizes`
--

INSERT INTO `tbl_frame_sizes` (`frame_size_id`, `dimension`, `width_inch`, `height_inch`, `total_inch`, `price`, `is_active`) VALUES
(1, '10x20', 10.00, 20.00, 200.00, 180.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_types`
--

CREATE TABLE `tbl_frame_types` (
  `frame_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `type_price` decimal(10,2) NOT NULL DEFAULT 0.00,
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
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_matboard_colors`
--

INSERT INTO `tbl_matboard_colors` (`matboard_color_id`, `matboard_color_name`, `base_price`, `image_name`, `is_active`) VALUES
(1, 'White', 0.00, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mount_type`
--

CREATE TABLE `tbl_mount_type` (
  `mount_type_id` int(11) NOT NULL,
  `mount_name` varchar(50) NOT NULL,
  `additional_fee` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_mount_type`
--

INSERT INTO `tbl_mount_type` (`mount_type_id`, `mount_name`, `additional_fee`, `is_active`) VALUES
(1, 'With Stand', 50.00, 1),
(2, 'Hanging', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notifications`
--

CREATE TABLE `tbl_notifications` (
  `notification_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_reference_no` varchar(50) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` enum('CASH','GCASH') NOT NULL,
  `order_status` enum('PENDING','PROCESSING','READY_FOR_PICKUP','FOR_DELIVERY','COMPLETED','REJECTED','CANCELLED') DEFAULT 'PENDING',
  `delivery_option` enum('PICKUP','DELIVERY') DEFAULT 'PICKUP',
  `delivery_address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_otp`
--

CREATE TABLE `tbl_otp` (
  `otp_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expired_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_otp`
--

INSERT INTO `tbl_otp` (`otp_id`, `customer_id`, `admin_id`, `otp_code`, `expired_at`, `is_used`) VALUES
(8, NULL, 1, '113669', '2026-02-18 16:54:56', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paper_type`
--

CREATE TABLE `tbl_paper_type` (
  `paper_type_id` int(11) NOT NULL,
  `paper_name` varchar(100) NOT NULL,
  `pricing_logic` enum('FIXED','CALCULATED') NOT NULL DEFAULT 'FIXED',
  `dimension` varchar(50) DEFAULT NULL,
  `width_inch` decimal(5,2) DEFAULT NULL,
  `height_inch` decimal(5,2) DEFAULT NULL,
  `total_inch` decimal(5,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('PENDING','PARTIAL','FULL') DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `date_paid` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_printing_order_items`
--

CREATE TABLE `tbl_printing_order_items` (
  `printing_order_item_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `paper_type_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `width_inch` decimal(5,2) NOT NULL,
  `height_inch` decimal(5,2) NOT NULL,
  `total_inch` decimal(5,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ready_made_product`
--

CREATE TABLE `tbl_ready_made_product` (
  `r_product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `frame_type_id` int(11) DEFAULT NULL,
  `frame_design_id` int(11) DEFAULT NULL,
  `frame_color_id` int(11) DEFAULT NULL,
  `width` decimal(5,2) NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ready_made_product_stocks`
--

CREATE TABLE `tbl_ready_made_product_stocks` (
  `stock_id` int(11) NOT NULL,
  `r_product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_updated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  ADD PRIMARY KEY (`c_product_id`),
  ADD KEY `frame_type_id` (`frame_type_id`),
  ADD KEY `frame_design_id` (`frame_design_id`),
  ADD KEY `frame_color_id` (`frame_color_id`);

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
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `r_product_id` (`r_product_id`),
  ADD KEY `c_product_id` (`c_product_id`),
  ADD KEY `printing_order_item_id` (`printing_order_item_id`),
  ADD KEY `primary_matboard_id` (`primary_matboard_id`),
  ADD KEY `secondary_matboard_id` (`secondary_matboard_id`),
  ADD KEY `mount_type_id` (`mount_type_id`);

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
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_reference_no` (`order_reference_no`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `tbl_otp`
--
ALTER TABLE `tbl_otp`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `admin_id` (`admin_id`);

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
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `paper_type_id` (`paper_type_id`);

--
-- Indexes for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  ADD PRIMARY KEY (`r_product_id`),
  ADD KEY `frame_type_id` (`frame_type_id`),
  ADD KEY `frame_design_id` (`frame_design_id`),
  ADD KEY `frame_color_id` (`frame_color_id`);

--
-- Indexes for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `r_product_id` (`r_product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  MODIFY `c_product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_colors`
--
ALTER TABLE `tbl_frame_colors`
  MODIFY `frame_color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_frame_designs`
--
ALTER TABLE `tbl_frame_designs`
  MODIFY `frame_design_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_sizes`
--
ALTER TABLE `tbl_frame_sizes`
  MODIFY `frame_size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_frame_types`
--
ALTER TABLE `tbl_frame_types`
  MODIFY `frame_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_matboard_colors`
--
ALTER TABLE `tbl_matboard_colors`
  MODIFY `matboard_color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_mount_type`
--
ALTER TABLE `tbl_mount_type`
  MODIFY `mount_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_otp`
--
ALTER TABLE `tbl_otp`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD CONSTRAINT `tbl_cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_1` FOREIGN KEY (`frame_type_id`) REFERENCES `tbl_frame_types` (`frame_type_id`),
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_2` FOREIGN KEY (`frame_design_id`) REFERENCES `tbl_frame_designs` (`frame_design_id`),
  ADD CONSTRAINT `tbl_custom_frame_product_ibfk_3` FOREIGN KEY (`frame_color_id`) REFERENCES `tbl_frame_colors` (`frame_color_id`);

--
-- Constraints for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `tbl_cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_3` FOREIGN KEY (`r_product_id`) REFERENCES `tbl_ready_made_product` (`r_product_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_4` FOREIGN KEY (`c_product_id`) REFERENCES `tbl_custom_frame_product` (`c_product_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_5` FOREIGN KEY (`printing_order_item_id`) REFERENCES `tbl_printing_order_items` (`printing_order_item_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_6` FOREIGN KEY (`primary_matboard_id`) REFERENCES `tbl_matboard_colors` (`matboard_color_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_7` FOREIGN KEY (`secondary_matboard_id`) REFERENCES `tbl_matboard_colors` (`matboard_color_id`),
  ADD CONSTRAINT `tbl_frame_order_items_ibfk_8` FOREIGN KEY (`mount_type_id`) REFERENCES `tbl_mount_type` (`mount_type_id`);

--
-- Constraints for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD CONSTRAINT `tbl_notifications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_notifications_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin` (`admin_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_notifications_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD CONSTRAINT `tbl_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`);

--
-- Constraints for table `tbl_otp`
--
ALTER TABLE `tbl_otp`
  ADD CONSTRAINT `tbl_otp_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_otp_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `tbl_admin` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD CONSTRAINT `tbl_payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  ADD CONSTRAINT `tbl_printing_order_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `tbl_cart` (`cart_id`),
  ADD CONSTRAINT `tbl_printing_order_items_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`),
  ADD CONSTRAINT `tbl_printing_order_items_ibfk_3` FOREIGN KEY (`paper_type_id`) REFERENCES `tbl_paper_type` (`paper_type_id`);

--
-- Constraints for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_1` FOREIGN KEY (`frame_type_id`) REFERENCES `tbl_frame_types` (`frame_type_id`),
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_2` FOREIGN KEY (`frame_design_id`) REFERENCES `tbl_frame_designs` (`frame_design_id`),
  ADD CONSTRAINT `tbl_ready_made_product_ibfk_3` FOREIGN KEY (`frame_color_id`) REFERENCES `tbl_frame_colors` (`frame_color_id`);

--
-- Constraints for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  ADD CONSTRAINT `tbl_ready_made_product_stocks_ibfk_1` FOREIGN KEY (`r_product_id`) REFERENCES `tbl_ready_made_product` (`r_product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
