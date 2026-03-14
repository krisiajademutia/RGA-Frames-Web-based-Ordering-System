-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 11:18 AM
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
(1, 'Admin', 'User', 'admin', 'mutiakrisiaj@gmail.com', '$2y$10$kd9FoZ0japdSk3mzS96QmeYSUH1Pbqm/0SdIRHO57r9NoMUuMQZia', '2026-02-18 12:57:20', '2026-03-14 14:17:11'),
(2, 'Calise', 'Sav', 'calise', 'savvcalise@gmail.com', '$2y$10$LyY134gbnwtwvLUchKeOHesA.5lmBNfBSFQt1kF8cLnyzcDd2xiLu', '2026-03-06 01:25:46', '2026-03-06 01:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cart`
--

INSERT INTO `tbl_cart` (`cart_id`, `customer_id`, `created_at`) VALUES
(1, 1, '2026-03-07 13:42:47');

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
  `customer_type` enum('REGULAR','PHOTOGRAPHER') NOT NULL DEFAULT 'REGULAR',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_customer`
--

INSERT INTO `tbl_customer` (`customer_id`, `first_name`, `last_name`, `username`, `email`, `password`, `phone_number`, `customer_type`, `created_at`) VALUES
(1, 'Krisia Jade', 'Mutia', 'krisia_jade', 'mutiakrisiajade@gmail.com', '$2y$10$/8VWQYDM/meDf6GFWWkbEe.OaQj6N.LtOneEVBHysvpnWPYh8Ohla', '09306282413', 'REGULAR', '2026-02-18 01:32:41'),
(2, 'Customer', 'Test', 'customer_101', 'customer@gmail.com', '$2y$10$aqvIYWozM8Rk0pK/7Ra6J.PeUelKWfcW8zna4vuGQN.xoH1YWy.fy', '09890987154', 'REGULAR', '2026-02-21 03:22:36'),
(9, 'Trisha', 'Lleno', 'trisha', 'kreiafey@gmail.com', '$2y$10$A3keQnqhfj8iAGI.untxiu0Uz3RMx1U8x86SwH7mm4VQkrnVftqHC', '09364650128', 'REGULAR', '2026-03-06 15:37:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_custom_frame_product`
--

CREATE TABLE `tbl_custom_frame_product` (
  `c_product_id` int(11) NOT NULL,
  `frame_type_id` int(11) DEFAULT NULL,
  `frame_design_id` int(11) DEFAULT NULL,
  `frame_color_id` int(11) DEFAULT NULL,
  `custom_width` decimal(5,2) NOT NULL,
  `custom_height` decimal(5,2) NOT NULL,
  `calculated_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_custom_frame_product`
--

INSERT INTO `tbl_custom_frame_product` (`c_product_id`, `frame_type_id`, `frame_design_id`, `frame_color_id`, `custom_width`, `custom_height`, `calculated_price`) VALUES
(1, 1, 1, 1, 8.00, 10.00, 650.00),
(2, 2, 2, 2, 12.00, 16.00, 1200.00),
(3, 1, 3, 1, 5.00, 7.00, 450.00),
(4, 3, 1, 2, 10.00, 12.00, 1850.00),
(5, 2, 4, 1, 8.00, 8.00, 900.00),
(6, 1, 5, 2, 16.00, 20.00, 800.00),
(7, 1, 6, 1, 6.00, 8.00, 600.00),
(8, 3, 4, 2, 8.00, 10.00, 1800.00),
(9, 3, 4, 2, 8.00, 10.00, 1800.00),
(10, 3, 4, 2, 8.00, 10.00, 1800.00),
(11, 3, 4, 2, 8.00, 10.00, 1800.00),
(12, 3, 4, 2, 8.00, 10.00, 1800.00),
(13, 3, 4, 2, 8.00, 10.00, 1800.00),
(14, 3, 4, 2, 8.00, 10.00, 1800.00),
(15, 3, 4, 2, 8.00, 10.00, 1800.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fixed_print_prices`
--

CREATE TABLE `tbl_fixed_print_prices` (
  `fixed_price_id` int(11) NOT NULL,
  `paper_type_id` int(11) NOT NULL,
  `dimension` varchar(50) NOT NULL,
  `width_inch` decimal(5,2) NOT NULL,
  `height_inch` decimal(5,2) NOT NULL,
  `fixed_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_colors`
--

CREATE TABLE `tbl_frame_colors` (
  `frame_color_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `color_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_frame_colors`
--

INSERT INTO `tbl_frame_colors` (`frame_color_id`, `color_name`, `is_active`, `color_image`) VALUES
(1, 'Red', 1, NULL),
(2, 'Gold', 1, NULL),
(3, 'pink', 1, 'WALL.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_designs`
--

CREATE TABLE `tbl_frame_designs` (
  `frame_design_id` int(11) NOT NULL,
  `design_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_frame_designs`
--

INSERT INTO `tbl_frame_designs` (`frame_design_id`, `design_name`, `price`, `is_active`) VALUES
(1, 'DESIGN1', 450.00, 1),
(2, 'Design 456', 1000.00, 1),
(3, 'desin 777', 200.00, 1),
(4, 'D111', 200.00, 1),
(5, 'D112', 300.00, 1),
(6, 'D113', 400.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_design_images`
--

CREATE TABLE `tbl_frame_design_images` (
  `image_id` int(11) NOT NULL,
  `frame_design_id` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `tbl_frame_order_items`
--

INSERT INTO `tbl_frame_order_items` (`item_id`, `frame_category`, `r_product_id`, `c_product_id`, `source_type`, `cart_id`, `order_id`, `service_type`, `printing_order_item_id`, `primary_matboard_id`, `secondary_matboard_id`, `mount_type_id`, `quantity`, `base_price`, `extra_price`, `sub_total`) VALUES
(1, 'READY_MADE', 1, NULL, 'ORDER', NULL, 1, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 350.00, 0.00, 350.00),
(2, 'READY_MADE', 2, NULL, 'ORDER', NULL, 2, 'FRAME_ONLY', NULL, 2, NULL, 2, 1, 550.00, 0.00, 550.00),
(3, 'CUSTOM', NULL, 1, 'ORDER', NULL, 3, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 650.00, 0.00, 650.00),
(4, 'CUSTOM', NULL, 2, 'ORDER', NULL, 4, 'FRAME_ONLY', NULL, 2, NULL, 2, 1, 1200.00, 0.00, 1200.00),
(5, 'READY_MADE', 1, NULL, 'ORDER', NULL, 5, 'FRAME&PRINT', 1, 1, NULL, 1, 1, 350.00, 320.00, 670.00),
(6, 'CUSTOM', NULL, 3, 'ORDER', NULL, 6, 'FRAME&PRINT', 2, 2, NULL, 2, 1, 450.00, 180.00, 1520.00),
(7, 'READY_MADE', 3, NULL, 'ORDER', NULL, 8, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 450.00, 0.00, 450.00),
(8, 'CUSTOM', NULL, 5, 'ORDER', NULL, 9, 'FRAME_ONLY', NULL, 1, NULL, 2, 1, 900.00, 0.00, 900.00),
(9, 'READY_MADE', 5, NULL, 'ORDER', NULL, 10, 'FRAME_ONLY', NULL, 2, NULL, 1, 1, 200.00, 0.00, 200.00),
(10, 'CUSTOM', NULL, 4, 'ORDER', NULL, 11, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 1850.00, 0.00, 1850.00),
(11, 'CUSTOM', NULL, 6, 'ORDER', NULL, 12, 'FRAME&PRINT', 3, 1, 2, 1, 1, 800.00, 560.00, 1560.00),
(12, 'READY_MADE', 4, NULL, 'ORDER', NULL, 14, 'FRAME_ONLY', NULL, 2, NULL, 2, 1, 1200.00, 0.00, 1200.00),
(13, 'READY_MADE', 2, NULL, 'ORDER', NULL, 15, 'FRAME&PRINT', 5, 1, NULL, 1, 1, 550.00, 320.00, 870.00),
(14, 'CUSTOM', NULL, 6, 'ORDER', NULL, 16, 'FRAME_ONLY', NULL, 2, NULL, 2, 1, 800.00, 0.00, 800.00),
(15, 'READY_MADE', 1, NULL, 'ORDER', NULL, 18, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 350.00, 0.00, 350.00),
(16, 'CUSTOM', NULL, 7, 'ORDER', NULL, 19, 'FRAME_ONLY', NULL, 2, NULL, 1, 1, 600.00, 0.00, 600.00),
(17, 'READY_MADE', 5, NULL, 'ORDER', NULL, 20, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 200.00, 0.00, 200.00),
(18, 'READY_MADE', 1, NULL, 'ORDER', NULL, 21, 'FRAME&PRINT', 4, 1, NULL, 2, 1, 350.00, 150.00, 520.00),
(19, 'CUSTOM', NULL, 2, 'ORDER', NULL, 22, 'FRAME_ONLY', NULL, 2, NULL, 1, 1, 1200.00, 0.00, 1200.00),
(20, 'CUSTOM', NULL, 1, 'ORDER', NULL, 24, 'FRAME&PRINT', 6, 1, 2, 2, 1, 650.00, 380.00, 1220.00),
(21, 'READY_MADE', 2, NULL, 'ORDER', NULL, 25, 'FRAME_ONLY', NULL, 2, NULL, 1, 1, 550.00, 0.00, 550.00),
(22, 'CUSTOM', NULL, 1, 'ORDER', NULL, 26, 'FRAME_ONLY', NULL, 1, NULL, 2, 1, 650.00, 0.00, 650.00),
(23, 'READY_MADE', 6, NULL, 'ORDER', NULL, 27, 'FRAME_ONLY', NULL, 1, NULL, 1, 1, 240.00, 0.00, 240.00),
(24, 'CUSTOM', NULL, 8, '', 1, NULL, '', NULL, 1, 2, 2, 1, 1800.00, 0.00, 1800.00),
(25, 'CUSTOM', NULL, 9, '', 1, NULL, '', NULL, 2, 1, 2, 1, 1800.00, 0.00, 1800.00),
(26, 'CUSTOM', NULL, 10, '', 1, NULL, '', NULL, 2, 1, 2, 1, 1800.00, 0.00, 1800.00),
(27, 'CUSTOM', NULL, 11, '', NULL, 28, '', NULL, 2, 1, 2, 1, 1800.00, 0.00, 1800.00),
(28, 'CUSTOM', NULL, 12, '', 1, NULL, '', NULL, NULL, NULL, 2, 1, 1800.00, 0.00, 1800.00),
(29, 'CUSTOM', NULL, 13, '', 1, NULL, '', NULL, 2, 2, 2, 1, 1800.00, 0.00, 1800.00),
(30, 'CUSTOM', NULL, 14, '', NULL, 29, '', NULL, 2, 1, 2, 1, 1800.00, 0.00, 1800.00),
(31, 'CUSTOM', NULL, 15, '', NULL, 30, '', NULL, 2, 1, 2, 1, 1800.00, 0.00, 1800.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_frame_sizes`
--

CREATE TABLE `tbl_frame_sizes` (
  `frame_size_id` int(11) NOT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `width_inch` decimal(5,2) DEFAULT NULL,
  `height_inch` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_frame_sizes`
--

INSERT INTO `tbl_frame_sizes` (`frame_size_id`, `dimension`, `width_inch`, `height_inch`, `is_active`) VALUES
(3, '3.5x5', 3.50, 5.00, 0),
(5, '4x6', 4.00, 6.00, 0),
(6, '5x7', 5.00, 7.00, 0),
(7, '5x8', 5.00, 8.00, 0),
(8, '6x8', 6.00, 8.00, 0),
(10, '5x10', 5.00, 10.00, 0),
(11, '5x12', 5.00, 12.00, 0),
(12, '8x10', 8.00, 10.00, 0),
(13, '8x11', 8.00, 11.00, 0),
(14, '8x12', 8.00, 12.00, 0),
(16, '8x10', 8.00, 10.00, 1),
(17, '10x16', 10.00, 16.00, 1);

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

--
-- Dumping data for table `tbl_frame_types`
--

INSERT INTO `tbl_frame_types` (`frame_type_id`, `type_name`, `type_price`, `image_name`, `is_active`) VALUES
(1, 'Wooden', 0.00, NULL, 1),
(2, 'Metal', 0.00, NULL, 1),
(3, 'Glass', 1200.00, 'type_1772771003_69aa56bbd3e31.jpg', 1),
(4, 'type 1', 100.00, 'type_1773106474_69af752ae6652.jpg', 0);

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
(1, 'White', 0.00, '', 1),
(2, 'Maroon', 0.00, '6d08e54c-71e6-4ea7-bbb8-c36984f185d3.jpg', 1),
(3, 'Green', 0.00, 'Screenshot 2026-03-10 154018.png', 0),
(4, 'Blue', 0.00, 'Screenshot 2025-10-15 124225.png', 0);

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
  `sub_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` enum('CASH','GCASH') NOT NULL,
  `order_status` enum('PENDING','PROCESSING','READY_FOR_PICKUP','FOR_DELIVERY','COMPLETED','REJECTED','CANCELLED') DEFAULT 'PENDING',
  `delivery_option` enum('PICKUP','DELIVERY') DEFAULT 'PICKUP',
  `delivery_address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_orders`
--

INSERT INTO `tbl_orders` (`order_id`, `customer_id`, `order_reference_no`, `sub_total`, `discount_amount`, `total_price`, `payment_method`, `order_status`, `delivery_option`, `delivery_address`, `created_at`) VALUES
(1, 1, 'RGA-2026-0001', 0.00, 0.00, 350.00, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-03-01 08:00:00'),
(2, 1, 'RGA-2026-0002', 0.00, 0.00, 550.00, 'GCASH', 'PENDING', 'DELIVERY', '123 Rizal St, Davao City', '2026-03-01 09:00:00'),
(3, 1, 'RGA-2026-0003', 0.00, 0.00, 650.00, 'CASH', 'PROCESSING', 'PICKUP', NULL, '2026-03-02 09:00:00'),
(4, 1, 'RGA-2026-0004', 0.00, 0.00, 1200.00, 'GCASH', 'PROCESSING', 'DELIVERY', '456 Quimpo Blvd, Davao City', '2026-03-02 10:00:00'),
(5, 1, 'RGA-2026-0005', 0.00, 0.00, 670.00, 'CASH', 'READY_FOR_PICKUP', 'PICKUP', NULL, '2026-03-03 10:00:00'),
(6, 1, 'RGA-2026-0006', 0.00, 0.00, 1520.00, 'GCASH', 'FOR_DELIVERY', 'DELIVERY', '789 Magsaysay Ave, Davao City', '2026-03-03 11:00:00'),
(7, 1, 'RGA-2026-0007', 0.00, 0.00, 320.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-04 11:00:00'),
(8, 1, 'RGA-2026-0008', 0.00, 0.00, 450.00, 'GCASH', 'CANCELLED', 'PICKUP', NULL, '2026-03-04 12:00:00'),
(9, 1, 'RGA-2026-0009', 0.00, 0.00, 900.00, 'CASH', 'REJECTED', 'DELIVERY', '321 Ilustre St, Davao City', '2026-03-04 13:00:00'),
(10, 2, 'RGA-2026-0010', 0.00, 0.00, 200.00, 'GCASH', 'PENDING', 'PICKUP', NULL, '2026-03-05 08:00:00'),
(11, 2, 'RGA-2026-0011', 0.00, 0.00, 1850.00, 'CASH', 'PENDING', 'DELIVERY', '55 Tulip St, Davao City', '2026-03-05 09:00:00'),
(12, 2, 'RGA-2026-0012', 0.00, 0.00, 1560.00, 'GCASH', 'PROCESSING', 'PICKUP', NULL, '2026-03-06 09:00:00'),
(13, 2, 'RGA-2026-0013', 0.00, 0.00, 180.00, 'CASH', 'PROCESSING', 'DELIVERY', '77 Lakewood Rd, Davao City', '2026-03-06 10:00:00'),
(14, 2, 'RGA-2026-0014', 0.00, 0.00, 1200.00, 'GCASH', 'READY_FOR_PICKUP', 'PICKUP', NULL, '2026-03-07 10:00:00'),
(15, 2, 'RGA-2026-0015', 0.00, 0.00, 870.00, 'CASH', 'FOR_DELIVERY', 'DELIVERY', '99 Claveria St, Davao City', '2026-03-07 11:00:00'),
(16, 2, 'RGA-2026-0016', 0.00, 0.00, 800.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-08 08:00:00'),
(17, 2, 'RGA-2026-0017', 0.00, 0.00, 560.00, 'CASH', 'COMPLETED', 'DELIVERY', '44 Ponciano St, Davao City', '2026-03-08 09:00:00'),
(18, 2, 'RGA-2026-0018', 0.00, 0.00, 350.00, 'CASH', 'CANCELLED', 'PICKUP', NULL, '2026-03-09 08:00:00'),
(19, 2, 'RGA-2026-0019', 0.00, 0.00, 600.00, 'GCASH', 'REJECTED', 'DELIVERY', '11 Anda St, Davao City', '2026-03-09 09:00:00'),
(20, 9, 'RGA-2026-0020', 0.00, 0.00, 200.00, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-03-10 08:00:00'),
(21, 9, 'RGA-2026-0021', 0.00, 0.00, 520.00, 'GCASH', 'PROCESSING', 'DELIVERY', '88 Bolton St, Davao City', '2026-03-10 09:00:00'),
(22, 9, 'RGA-2026-0022', 0.00, 0.00, 1200.00, 'CASH', 'PROCESSING', 'PICKUP', NULL, '2026-03-11 09:00:00'),
(23, 9, 'RGA-2026-0023', 0.00, 0.00, 420.00, 'GCASH', 'READY_FOR_PICKUP', 'PICKUP', NULL, '2026-03-11 10:00:00'),
(24, 9, 'RGA-2026-0024', 0.00, 0.00, 1220.00, 'CASH', 'FOR_DELIVERY', 'DELIVERY', '33 Km4 Diversion Rd, Davao', '2026-03-12 10:00:00'),
(25, 9, 'RGA-2026-0025', 0.00, 0.00, 550.00, 'GCASH', 'COMPLETED', 'DELIVERY', '22 Buhangin Rd, Davao City', '2026-03-12 11:00:00'),
(26, 9, 'RGA-2026-0026', 0.00, 0.00, 650.00, 'CASH', 'CANCELLED', 'PICKUP', NULL, '2026-03-13 08:00:00'),
(27, 9, 'RGA-2026-0027', 0.00, 0.00, 240.00, 'GCASH', 'REJECTED', 'DELIVERY', '15 Mac Arthur Hwy, Davao', '2026-03-13 09:00:00'),
(28, 1, 'RGA-20260314-E70CD7', 0.00, 0.00, 1800.00, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-03-14 16:09:34'),
(29, 1, 'RGA-20260314-D326D0', 0.00, 0.00, 1800.00, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-03-14 16:46:21'),
(30, 1, 'RGA-20260314-D6F429', 0.00, 0.00, 1800.00, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-03-14 16:58:37');

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
(20, 1, NULL, '945221', '2026-03-04 07:36:49', 0),
(24, NULL, 1, '113007', '2026-03-04 08:54:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paper_type`
--

CREATE TABLE `tbl_paper_type` (
  `paper_type_id` int(11) NOT NULL,
  `paper_name` varchar(100) NOT NULL,
  `multiplier` decimal(4,2) DEFAULT NULL,
  `min_width_inch` decimal(5,2) DEFAULT 0.00,
  `min_height_inch` decimal(5,2) DEFAULT 0.00,
  `max_width_inch` decimal(5,2) DEFAULT 50.00,
  `max_height_inch` decimal(5,2) DEFAULT 96.00,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_paper_type`
--

INSERT INTO `tbl_paper_type` (`paper_type_id`, `paper_name`, `multiplier`, `min_width_inch`, `min_height_inch`, `max_width_inch`, `max_height_inch`, `is_active`) VALUES
(1, 'Glossy', NULL, 0.00, 0.00, 50.00, 96.00, 1),
(2, 'Matte', NULL, 0.00, 0.00, 50.00, 96.00, 1),
(3, 'Canvas', NULL, 0.00, 0.00, 50.00, 96.00, 1),
(4, 'canvas', NULL, 0.00, 0.00, 50.00, 96.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('PENDING','PARTIAL','FULL') DEFAULT NULL,
  `date_paid` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_payment`
--

INSERT INTO `tbl_payment` (`payment_id`, `order_id`, `total_amount`, `payment_status`, `date_paid`) VALUES
(1, 1, 350.00, 'PENDING', NULL),
(2, 2, 550.00, 'PENDING', NULL),
(3, 3, 650.00, 'PARTIAL', '2026-03-02 00:00:00'),
(4, 4, 1200.00, 'PENDING', NULL),
(5, 5, 670.00, 'FULL', '2026-03-03 00:00:00'),
(6, 6, 1520.00, 'PARTIAL', '2026-03-03 00:00:00'),
(7, 7, 320.00, 'FULL', '2026-03-04 00:00:00'),
(8, 8, 450.00, 'PENDING', NULL),
(9, 9, 900.00, 'PENDING', NULL),
(10, 10, 200.00, 'PENDING', NULL),
(11, 11, 1850.00, 'PENDING', NULL),
(12, 12, 1560.00, 'PARTIAL', '2026-03-06 00:00:00'),
(13, 13, 180.00, 'FULL', '2026-03-06 00:00:00'),
(14, 14, 1200.00, 'FULL', '2026-03-07 00:00:00'),
(15, 15, 870.00, 'FULL', '2026-03-07 00:00:00'),
(16, 16, 800.00, 'FULL', '2026-03-08 00:00:00'),
(17, 17, 560.00, 'FULL', '2026-03-08 00:00:00'),
(18, 18, 350.00, 'PENDING', NULL),
(19, 19, 600.00, 'PENDING', NULL),
(20, 20, 200.00, 'PENDING', NULL),
(21, 21, 520.00, 'PENDING', NULL),
(22, 22, 1200.00, 'PARTIAL', '2026-03-11 00:00:00'),
(23, 23, 420.00, 'FULL', '2026-03-11 00:00:00'),
(24, 24, 1220.00, 'PARTIAL', '2026-03-12 00:00:00'),
(25, 25, 550.00, 'FULL', '2026-03-12 00:00:00'),
(26, 26, 650.00, 'PENDING', NULL),
(27, 27, 240.00, 'PENDING', NULL),
(28, 29, 1800.00, 'PENDING', '2026-03-14 16:46:21'),
(29, 30, 1800.00, 'PENDING', '2026-03-14 16:58:37');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment_proof_uploads`
--

CREATE TABLE `tbl_payment_proof_uploads` (
  `upload_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `uploaded_amount` decimal(10,2) NOT NULL,
  `payment_proof` varchar(255) NOT NULL,
  `verification_status` varchar(50) DEFAULT 'Pending',
  `upload_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_payment_proof_uploads`
--

INSERT INTO `tbl_payment_proof_uploads` (`upload_id`, `payment_id`, `uploaded_amount`, `payment_proof`, `verification_status`, `upload_date`) VALUES
(1, 2, 300.00, 'uploads/uploaded_receipts/gcash_receipt_2.jpg', 'Pending Verification', '2026-03-01 10:00:00'),
(2, 4, 600.00, 'uploads/uploaded_receipts/gcash_receipt_4.jpg', 'Pending Verification', '2026-03-02 11:00:00'),
(3, 6, 760.00, 'uploads/uploaded_receipts/gcash_receipt_6a.jpg', 'Verified', '2026-03-03 12:00:00'),
(4, 6, 760.00, 'uploads/uploaded_receipts/gcash_receipt_6b.jpg', 'Verified', '2026-03-03 14:00:00'),
(5, 8, 200.00, 'uploads/uploaded_receipts/gcash_receipt_8.jpg', 'Pending Verification', '2026-03-04 13:00:00'),
(6, 12, 780.00, 'uploads/uploaded_receipts/gcash_receipt_12.jpg', 'Verified', '2026-03-06 10:00:00'),
(7, 14, 600.00, 'uploads/uploaded_receipts/gcash_receipt_14a.jpg', 'Verified', '2026-03-07 09:00:00'),
(8, 14, 600.00, 'uploads/uploaded_receipts/gcash_receipt_14b.jpg', 'Verified', '2026-03-07 10:00:00'),
(9, 16, 800.00, 'uploads/uploaded_receipts/gcash_receipt_16.jpg', 'Verified', '2026-03-08 08:30:00'),
(10, 19, 300.00, 'uploads/uploaded_receipts/gcash_receipt_19.jpg', 'Rejected', '2026-03-09 10:00:00'),
(11, 21, 260.00, 'uploads/uploaded_receipts/gcash_receipt_21.jpg', 'Pending Verification', '2026-03-10 10:00:00'),
(12, 23, 420.00, 'uploads/uploaded_receipts/gcash_receipt_23.jpg', 'Verified', '2026-03-11 11:00:00'),
(13, 25, 550.00, 'uploads/uploaded_receipts/gcash_receipt_25.jpg', 'Verified', '2026-03-12 12:00:00'),
(14, 27, 120.00, 'uploads/uploaded_receipts/gcash_receipt_27.jpg', 'Rejected', '2026-03-13 10:00:00');

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
  `width_inch` decimal(5,2) NOT NULL,
  `height_inch` decimal(5,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_printing_order_items`
--

INSERT INTO `tbl_printing_order_items` (`printing_order_item_id`, `cart_id`, `order_id`, `paper_type_id`, `image_path`, `width_inch`, `height_inch`, `quantity`, `sub_total`) VALUES
(1, NULL, 7, 1, 'uploads/customer_images/sample1.jpg', 8.00, 10.00, 1, 320.00),
(2, NULL, 13, 2, 'uploads/customer_images/sample2.jpg', 5.00, 7.00, 1, 180.00),
(3, NULL, 17, 3, 'uploads/customer_images/sample3.jpg', 12.00, 16.00, 2, 560.00),
(4, NULL, NULL, 1, 'uploads/customer_images/sample4.jpg', 4.00, 6.00, 1, 150.00),
(5, NULL, NULL, 2, 'uploads/customer_images/sample5.jpg', 8.00, 10.00, 1, 320.00),
(6, NULL, NULL, 1, 'uploads/customer_images/sample6.jpg', 10.00, 12.00, 1, 380.00),
(7, NULL, NULL, 3, 'uploads/customer_images/sample7.jpg', 5.00, 7.00, 2, 360.00),
(8, NULL, NULL, 2, 'uploads/customer_images/sample8.jpg', 8.00, 10.00, 1, 320.00),
(9, NULL, 23, 1, 'uploads/customer_images/sample9.jpg', 12.00, 16.00, 1, 420.00),
(10, NULL, 27, 2, 'uploads/customer_images/sample10.jpg', 6.00, 8.00, 1, 240.00);

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
  `product_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_ready_made_product`
--

INSERT INTO `tbl_ready_made_product` (`r_product_id`, `product_name`, `frame_type_id`, `frame_design_id`, `frame_color_id`, `width`, `height`, `product_price`) VALUES
(1, '5x7 Wooden Red Frame', 1, 1, 1, 5.00, 7.00, 350.00),
(2, '8x10 Metal Gold Frame', 2, 1, 2, 8.00, 10.00, 550.00),
(3, 'Product 123', 1, 1, 2, 12.00, 12.00, 450.00),
(4, 'Product456', 2, 1, 1, 12.00, 20.00, 1200.00),
(5, 'Frame 111', 1, 3, 2, 12.00, 27.00, 200.00),
(6, 'Product 111012221', 1, 3, 1, 12.00, 18.00, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ready_made_product_images`
--

CREATE TABLE `tbl_ready_made_product_images` (
  `image_id` int(11) NOT NULL,
  `r_product_id` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_ready_made_product_images`
--

INSERT INTO `tbl_ready_made_product_images` (`image_id`, `r_product_id`, `image_name`, `is_primary`, `uploaded_at`) VALUES
(1, 3, '1772770544_1343d63d-4e26-4448-a83c-02a0efced7df.jpg', 1, '2026-03-06 12:15:44'),
(2, 4, '1772770899_05d6cfc1-f04c-47d8-ae79-78f340dfc68c.jpg', 1, '2026-03-06 12:21:39'),
(3, 5, '1772771477_6f0ae6a3-d4a7-402c-af67-df88e5c59db9.jpg', 1, '2026-03-06 12:31:17'),
(4, 6, '1773316895_a5c8a621-b5fa-4998-ac6c-73daca782905.jpg', 1, '2026-03-12 20:01:35');

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
-- Dumping data for table `tbl_ready_made_product_stocks`
--

INSERT INTO `tbl_ready_made_product_stocks` (`stock_id`, `r_product_id`, `quantity`, `date_updated`) VALUES
(1, 3, 12, '2026-03-06 12:15:44'),
(2, 4, 2, '2026-03-06 12:21:39'),
(3, 5, 2, '2026-03-06 12:31:17'),
(4, 6, 1, '2026-03-12 20:01:35');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reviews`
--

CREATE TABLE `tbl_reviews` (
  `review_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review_text` text NOT NULL,
  `review_date_posted` datetime DEFAULT current_timestamp()
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
-- Indexes for table `tbl_fixed_print_prices`
--
ALTER TABLE `tbl_fixed_print_prices`
  ADD PRIMARY KEY (`fixed_price_id`),
  ADD KEY `paper_type_id` (`paper_type_id`);

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
-- Indexes for table `tbl_frame_design_images`
--
ALTER TABLE `tbl_frame_design_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `frame_design_id` (`frame_design_id`);

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
-- Indexes for table `tbl_payment_proof_uploads`
--
ALTER TABLE `tbl_payment_proof_uploads`
  ADD PRIMARY KEY (`upload_id`),
  ADD KEY `payment_id` (`payment_id`);

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
-- Indexes for table `tbl_ready_made_product_images`
--
ALTER TABLE `tbl_ready_made_product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `r_product_id` (`r_product_id`);

--
-- Indexes for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `fk_r_product_stock` (`r_product_id`);

--
-- Indexes for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  MODIFY `c_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_fixed_print_prices`
--
ALTER TABLE `tbl_fixed_print_prices`
  MODIFY `fixed_price_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_colors`
--
ALTER TABLE `tbl_frame_colors`
  MODIFY `frame_color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_frame_designs`
--
ALTER TABLE `tbl_frame_designs`
  MODIFY `frame_design_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_frame_design_images`
--
ALTER TABLE `tbl_frame_design_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tbl_frame_sizes`
--
ALTER TABLE `tbl_frame_sizes`
  MODIFY `frame_size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tbl_frame_types`
--
ALTER TABLE `tbl_frame_types`
  MODIFY `frame_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_matboard_colors`
--
ALTER TABLE `tbl_matboard_colors`
  MODIFY `matboard_color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tbl_otp`
--
ALTER TABLE `tbl_otp`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tbl_paper_type`
--
ALTER TABLE `tbl_paper_type`
  MODIFY `paper_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tbl_payment_proof_uploads`
--
ALTER TABLE `tbl_payment_proof_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  MODIFY `printing_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  MODIFY `r_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product_images`
--
ALTER TABLE `tbl_ready_made_product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `tbl_fixed_print_prices`
--
ALTER TABLE `tbl_fixed_print_prices`
  ADD CONSTRAINT `tbl_fixed_print_prices_ibfk_1` FOREIGN KEY (`paper_type_id`) REFERENCES `tbl_paper_type` (`paper_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_frame_design_images`
--
ALTER TABLE `tbl_frame_design_images`
  ADD CONSTRAINT `tbl_frame_design_images_ibfk_1` FOREIGN KEY (`frame_design_id`) REFERENCES `tbl_frame_designs` (`frame_design_id`) ON DELETE CASCADE;

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
-- Constraints for table `tbl_payment_proof_uploads`
--
ALTER TABLE `tbl_payment_proof_uploads`
  ADD CONSTRAINT `tbl_payment_proof_uploads_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `tbl_payment` (`payment_id`) ON DELETE CASCADE;

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
-- Constraints for table `tbl_ready_made_product_images`
--
ALTER TABLE `tbl_ready_made_product_images`
  ADD CONSTRAINT `tbl_ready_made_product_images_ibfk_1` FOREIGN KEY (`r_product_id`) REFERENCES `tbl_ready_made_product` (`r_product_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  ADD CONSTRAINT `fk_r_product_stock` FOREIGN KEY (`r_product_id`) REFERENCES `tbl_ready_made_product` (`r_product_id`);

--
-- Constraints for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  ADD CONSTRAINT `tbl_reviews_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
