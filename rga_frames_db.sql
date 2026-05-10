-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2026 at 10:21 AM
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
(1, 'Admin', 'User', 'admin', 'mutiakrisiaj@gmail.com', '$2y$10$kd9FoZ0japdSk3mzS96QmeYSUH1Pbqm/0SdIRHO57r9NoMUuMQZia', '2026-02-18 12:57:20', '2026-05-10 15:48:53');

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
(1, 1, '2026-03-25 21:13:19'),
(2, 25, '2026-04-11 23:43:41');

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
(1, 'Krisia Jade', 'Mutia', 'krisia_jade', 'mutiakrisiajade@gmail.com', '$2y$10$ZBZNy4U93VmAdPLmpjyl.eNCYBf03heXmtsIw0SQjdm1hWFZiDrBi', '09306282413', 'REGULAR', '2026-02-18 01:32:41'),
(9, 'Trisha', 'Lleno', 'trisha', 'kreiafey@gmail.com', '$2y$10$A3keQnqhfj8iAGI.untxiu0Uz3RMx1U8x86SwH7mm4VQkrnVftqHC', '09364650128', 'REGULAR', '2026-03-06 15:37:46'),
(25, 'Daday', 'Mutia', 'urr_mutyaaa', 'savvcalise@gmail.com', '$2y$10$1B/nhyZC2OhoGlBVNb5QyegOKCUyiCp1IgWpCjeouTaAzWK0eRiyy', '09812348984', 'REGULAR', '2026-03-27 20:16:27'),
(26, 'Junngkook', 'Jeon', 'bunny_jk', 'joelmutia7@gmail.com', '$2y$10$5ccV35NuHhWht1xY1iVYq.dEEKzOD3W9qW0LSx.NpdsTk6bT881z6', '09897612341', 'PHOTOGRAPHER', '2026-03-29 13:40:00');

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
(1, 5, 15, 4, 10.00, 16.00, 1736.67),
(2, 5, 9, 4, 10.00, 16.00, 643.33),
(3, 5, 15, 4, 10.00, 16.00, 1596.67),
(4, 5, 9, 7, 24.00, 30.00, 3575.00),
(5, 5, 7, 10, 10.00, 16.00, 405.00),
(6, 5, 15, 7, 10.00, 16.00, 1596.67),
(7, 5, 17, 8, 10.00, 16.00, 2170.00),
(8, 5, 8, 5, 10.00, 16.00, 513.33),
(9, 5, 15, 7, 10.00, 16.00, 1736.67),
(10, 5, 10, 4, 8.00, 10.00, 450.00),
(11, 5, 15, 7, 18.00, 25.00, 3263.33),
(12, 5, 15, 5, 15.00, 30.00, 2705.00),
(13, 5, 17, 4, 20.00, 30.00, 4730.00),
(14, 5, 17, 8, 10.00, 16.00, 2030.00),
(15, 5, 17, 7, 10.00, 16.00, 2170.00),
(16, 5, 17, 5, 10.00, 16.00, 1950.00),
(17, 5, 9, 7, 10.00, 16.00, 783.33),
(18, 5, 15, 7, 10.00, 16.00, 1516.67),
(19, 5, 12, 7, 10.00, 16.00, 1083.33),
(20, 5, 11, 7, 10.00, 16.00, 780.00),
(21, 5, 10, 4, 10.00, 16.00, 730.00),
(22, 5, 10, 4, 10.00, 16.00, 650.00),
(23, 5, 15, 7, 10.00, 16.00, 1516.67),
(24, 5, 15, 4, 3.50, 5.00, 495.83);

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

--
-- Dumping data for table `tbl_fixed_print_prices`
--

INSERT INTO `tbl_fixed_print_prices` (`fixed_price_id`, `paper_type_id`, `dimension`, `width_inch`, `height_inch`, `fixed_price`) VALUES
(1, 5, '3R', 3.50, 5.00, 9.00),
(2, 5, '4R', 4.00, 6.00, 10.00),
(3, 5, '5R', 5.00, 7.00, 20.00),
(4, 5, '5x8', 5.00, 8.00, 22.00),
(5, 5, '6R', 6.00, 8.00, 30.00),
(6, 5, '5x10', 5.00, 10.00, 38.00),
(7, 5, '5x12', 5.00, 12.00, 55.00),
(8, 5, '8R', 8.00, 10.00, 70.00),
(9, 5, '8x11', 8.00, 11.00, 80.00),
(10, 5, '8x12', 8.00, 12.00, 90.00),
(11, 5, '10x12', 10.00, 12.00, 100.00),
(12, 5, '10x16', 10.00, 16.00, 140.00),
(13, 3, '12x18', 12.00, 18.00, 805.00),
(14, 3, '16x20', 16.00, 20.00, 1195.00),
(15, 3, '20x24', 20.00, 24.00, 1550.00),
(16, 3, '24x30', 24.00, 30.00, 2325.00);

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
(4, 'Light Brown', 1, 'color_1773487091_69b543f32b6f6.jpg'),
(5, 'Yellow', 1, 'color_1778167811_69fcb003a43fa.jpg'),
(6, 'Red', 1, 'color_1773487493_69b54585483b7.jpg'),
(7, 'Blue', 1, 'color_1773487530_69b545aa72b21.jpg'),
(8, 'Dark Brown', 1, 'color_1773487556_69b545c44a755.jpg'),
(9, 'Green', 1, 'color_1773487634_69b5461282351.jpg'),
(10, 'Black', 1, 'color_1773487695_69b5464fca16f.jpg');

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
(7, 'D1', 75.00, 1),
(8, 'D2', 100.00, 1),
(9, 'D3', 130.00, 1),
(10, 'D4', 150.00, 1),
(11, 'D5', 180.00, 1),
(12, 'D6', 250.00, 1),
(13, 'D7', 260.00, 1),
(14, 'D8', 290.00, 1),
(15, 'D10', 350.00, 1),
(16, 'D9', 320.00, 1),
(17, 'D11', 450.00, 1);

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

--
-- Dumping data for table `tbl_frame_design_images`
--

INSERT INTO `tbl_frame_design_images` (`image_id`, `frame_design_id`, `image_name`, `is_primary`, `uploaded_at`) VALUES
(1, 7, 'design_1773488795_69b54a9b00b5e.jpg', 1, '2026-03-14 19:46:35'),
(2, 7, 'design_1773488795_69b54a9b02454.jpg', 0, '2026-03-14 19:46:35'),
(3, 8, 'design_1773489564_69b54d9c8063a.jpg', 1, '2026-03-14 19:59:24'),
(4, 9, 'design_1773489607_69b54dc7328a0.jpg', 1, '2026-03-14 20:00:07'),
(5, 10, 'design_1773489651_69b54df32785b.jpg', 1, '2026-03-14 20:00:51'),
(6, 11, 'design_1773489673_69b54e094b39d.jpg', 1, '2026-03-14 20:01:13'),
(7, 12, 'design_1773489702_69b54e267a336.jpg', 1, '2026-03-14 20:01:42'),
(8, 13, 'design_1773489763_69b54e6321355.jpg', 1, '2026-03-14 20:02:43'),
(9, 14, 'design_1773489835_69b54eab5eee4.jpg', 1, '2026-03-14 20:03:55'),
(10, 15, 'design_1773489877_69b54ed598e58.jpg', 1, '2026-03-14 20:04:37'),
(11, 16, 'design_1773495695_69b5658f7c3ba.jpg', 1, '2026-03-14 21:41:35'),
(12, 16, 'design_1773495695_69b5658f7d614.jpg', 0, '2026-03-14 21:41:35'),
(13, 17, 'design_1773566299_69b6795b0bc17.jpg', 1, '2026-03-15 17:18:19');

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
(1, 'CUSTOM', NULL, 1, 'ORDER', NULL, 1, 'FRAME&PRINT', 1, 10, 15, 1, 1, 1516.67, 80.00, 1736.67),
(2, 'READY_MADE', 7, NULL, 'ORDER', NULL, 2, 'FRAME_ONLY', NULL, NULL, NULL, NULL, 1, 0.00, 0.00, 860.00),
(3, 'CUSTOM', NULL, 2, 'ORDER', NULL, 3, 'FRAME_ONLY', NULL, 6, 13, 1, 1, 0.00, 0.00, 643.33),
(4, 'CUSTOM', NULL, 3, 'ORDER', NULL, 4, 'FRAME_ONLY', NULL, 6, 7, 1, 1, 0.00, 0.00, 1596.67),
(5, 'READY_MADE', 7, NULL, 'ORDER', NULL, 5, 'FRAME_ONLY', NULL, NULL, NULL, NULL, 1, 0.00, 0.00, 860.00),
(6, 'CUSTOM', NULL, 4, 'ORDER', NULL, 6, 'FRAME&PRINT', 2, 10, 8, 1, 1, 0.00, 0.00, 3575.00),
(7, 'READY_MADE', 7, NULL, 'ORDER', NULL, 8, 'FRAME&PRINT', 3, 15, 6, 1, 1, 780.00, 80.00, 980.00),
(8, 'READY_MADE', 7, NULL, 'ORDER', NULL, 9, 'FRAME_ONLY', NULL, 10, 6, 1, 1, 780.00, 80.00, 860.00),
(9, 'CUSTOM', NULL, 5, 'ORDER', NULL, 10, 'FRAME_ONLY', NULL, 10, 6, 1, 1, 325.00, 80.00, 405.00),
(10, 'CUSTOM', NULL, 6, 'ORDER', NULL, 11, 'FRAME_ONLY', NULL, 6, 10, 1, 1, 1516.67, 80.00, 1596.67),
(11, 'CUSTOM', NULL, 7, 'ORDER', NULL, 12, 'FRAME&PRINT', 5, 7, 10, 1, 1, 1950.00, 80.00, 2170.00),
(12, 'CUSTOM', NULL, 8, 'ORDER', NULL, 17, 'FRAME_ONLY', NULL, 6, 15, 1, 1, 433.33, 80.00, 513.33),
(13, 'READY_MADE', 10, NULL, 'ORDER', NULL, 19, 'FRAME&PRINT', 11, 15, 11, 1, 1, 1063.33, 80.00, 1323.33),
(14, 'CUSTOM', NULL, 9, 'ORDER', NULL, 20, 'FRAME&PRINT', 12, 10, 6, 1, 1, 1516.67, 80.00, 1736.67),
(15, 'CUSTOM', NULL, 10, 'CART', 1, NULL, 'FRAME_ONLY', NULL, 13, 9, 1, 1, 450.00, 80.00, 530.00),
(16, 'CUSTOM', NULL, 11, 'ORDER', NULL, 22, 'FRAME&PRINT', 14, 6, 7, 1, 1, 2508.33, 80.00, 3263.33),
(17, 'CUSTOM', NULL, 12, 'ORDER', NULL, 23, 'FRAME_ONLY', NULL, 10, 6, 1, 1, 2625.00, 80.00, 2705.00),
(18, 'CUSTOM', NULL, 13, 'ORDER', NULL, 24, 'FRAME&PRINT', 15, 9, 12, 1, 1, 3750.00, 80.00, 4730.00),
(19, 'READY_MADE', 7, NULL, 'ORDER', NULL, 25, 'FRAME_ONLY', NULL, 10, 8, 1, 1, 780.00, 80.00, 860.00),
(20, 'READY_MADE', 7, NULL, 'ORDER', NULL, 26, 'FRAME_ONLY', NULL, NULL, NULL, 2, 1, 780.00, 0.00, 780.00),
(21, 'READY_MADE', 9, NULL, 'ORDER', NULL, 27, 'FRAME&PRINT', 16, 10, 8, 1, 1, 1498.33, 80.00, 1908.33),
(22, 'CUSTOM', NULL, 14, 'ORDER', NULL, 29, 'FRAME_ONLY', NULL, 8, 10, 1, 1, 1950.00, 80.00, 2030.00),
(23, 'CUSTOM', NULL, 15, 'ORDER', NULL, 30, 'FRAME&PRINT', 18, 8, 6, 1, 1, 1950.00, 80.00, 2170.00),
(24, 'READY_MADE', 7, NULL, 'ORDER', NULL, 31, 'FRAME_ONLY', NULL, NULL, NULL, 2, 1, 780.00, 0.00, 780.00),
(26, 'READY_MADE', 7, NULL, 'ORDER', NULL, 32, 'FRAME_ONLY', NULL, NULL, NULL, 2, 1, 780.00, 0.00, 780.00),
(27, 'CUSTOM', NULL, 17, 'ORDER', NULL, 33, 'FRAME&PRINT', 19, 9, 6, 1, 1, 563.33, 80.00, 783.33),
(28, 'READY_MADE', 9, NULL, 'ORDER', NULL, 34, 'FRAME&PRINT', 20, 6, 10, 1, 1, 1498.33, 80.00, 1908.33),
(34, 'READY_MADE', 10, NULL, 'ORDER', NULL, 35, 'FRAME_ONLY', NULL, NULL, NULL, 2, 1, 1063.33, 0.00, 1063.33),
(36, 'READY_MADE', 9, NULL, 'ORDER', NULL, 36, 'FRAME&PRINT', 21, 6, 10, 1, 1, 1498.33, 80.00, 1908.33),
(40, 'READY_MADE', 9, NULL, 'ORDER', NULL, 37, 'FRAME_ONLY', NULL, 10, 8, 1, 1, 1498.33, 80.00, 1578.33),
(42, 'CUSTOM', NULL, 21, 'ORDER', NULL, 38, 'FRAME_ONLY', NULL, 8, 12, 1, 1, 650.00, 80.00, 730.00),
(44, 'READY_MADE', 7, NULL, 'ORDER', NULL, 39, 'FRAME_ONLY', NULL, NULL, NULL, 2, 2, 780.00, 0.00, 1560.00),
(45, 'CUSTOM', NULL, 22, 'CART', 2, NULL, 'FRAME_ONLY', NULL, 8, 12, 1, 1, 650.00, 80.00, 730.00),
(47, 'CUSTOM', NULL, 23, 'CART', 2, NULL, 'FRAME_ONLY', NULL, 8, 10, 2, 29, 1516.67, 30.00, 44853.43),
(48, 'CUSTOM', NULL, 24, 'CART', 2, NULL, 'FRAME&PRINT', 22, 8, 10, 2, 1, 495.83, 39.00, 534.83);

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
(16, '8x10', 8.00, 10.00, 1),
(17, '10x16', 10.00, 16.00, 1),
(18, '12x16', 12.00, 16.00, 1);

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
(5, 'Polystyrene', 0.00, 'type_1778168040_69fcb0e8b62ca.jpg', 1),
(6, 'Glass to Glass', 300.00, 'type_1773499594_69b574cacab1f.jpg', 1);

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
(5, 'Light Olive', 29.99, 'matboard_1773487913_69b5472974848.jpg', 1),
(6, 'Beige', 30.00, 'matboard_1773487939_69b54743d6d4b.jpg', 1),
(7, 'Off White', 30.00, 'matboard_1773487962_69b5475a0e447.jpg', 1),
(8, 'Dark Brown', 30.00, 'matboard_1773488041_69b547a95e97e.jpg', 1),
(9, 'Dark Gray', 30.00, 'matboard_1773488065_69b547c18ca9d.jpg', 1),
(10, 'Black', 30.00, 'matboard_1773488124_69b547fc0f241.jpg', 1),
(11, 'Navy Blue', 30.00, 'matboard_1773488155_69b5481b7f49b.jpg', 1),
(12, 'Green', 30.00, 'matboard_1773488234_69b5486a3a039.jpg', 1),
(13, 'Silver', 30.00, 'matboard_1773488542_69b5499ee3738.jpg', 1),
(14, 'Pale Purple', 30.00, 'matboard_1773488565_69b549b520fe7.jpg', 1),
(15, 'Blue', 29.99, 'matboard_1773488593_69b549d13c64c.jpg', 1);

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

--
-- Dumping data for table `tbl_notifications`
--

INSERT INTO `tbl_notifications` (`notification_id`, `customer_id`, `admin_id`, `order_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, NULL, 1, 'Order Received', 'Thank you! We have received your order (RGA-20260324-A044C) and will review it shortly.', 1, '2026-03-24 23:09:06'),
(2, NULL, NULL, 1, 'New Order Alert', 'A new order (RGA-20260324-A044C) has been placed by a customer.', 1, '2026-03-24 23:09:06'),
(3, 1, NULL, 1, 'Order Accepted!', 'Your order (RGA-20260324-A044C) has been accepted and is now processing.', 1, '2026-03-25 01:43:26'),
(4, 1, NULL, 1, 'Ready for Pick-up!', 'Your order (RGA-20260324-A044C) is ready! Please visit the store to claim it.', 1, '2026-03-25 01:43:30'),
(5, 1, NULL, 1, 'Order Completed', 'Your order (RGA-20260324-A044C) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-25 01:43:34'),
(6, 1, NULL, 1, 'Payment Received ', 'We have successfully recorded your cash payment of ₱736.67 for Order #1.', 1, '2026-03-25 01:55:34'),
(7, 1, NULL, 2, 'Order Received', 'Thank you! We have received your order (RGA-20260324-9A431) and will review it shortly.', 1, '2026-03-25 01:58:13'),
(8, NULL, NULL, 2, 'New Order Alert', 'A new order (RGA-20260324-9A431) has been placed by a customer.', 1, '2026-03-25 01:58:13'),
(9, 1, NULL, 2, 'Payment Received ', 'We have successfully recorded your cash payment of ₱860.00 for Order #2.', 1, '2026-03-25 02:01:32'),
(10, 1, NULL, 2, 'Order Accepted!', 'Your order (RGA-20260324-9A431) has been accepted and is now processing.', 1, '2026-03-25 02:01:39'),
(11, 1, NULL, 2, 'Ready for Pick-up!', 'Your order (RGA-20260324-9A431) is ready! Please visit the store to claim it.', 1, '2026-03-25 02:01:44'),
(12, 1, NULL, 2, 'Order Completed', 'Your order (RGA-20260324-9A431) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-25 02:02:01'),
(13, 1, NULL, 3, 'Order Received', 'Thank you! We have received your order (RGA-20260325-6B1AB) and will review it shortly.', 1, '2026-03-25 19:47:15'),
(14, NULL, NULL, 3, 'New Order Alert', 'A new order (RGA-20260325-6B1AB) has been placed by a customer.', 1, '2026-03-25 19:47:15'),
(15, 1, NULL, 4, 'Order Received', 'Thank you! We have received your order (RGA-20260325-2F0D2) and will review it shortly.', 1, '2026-03-25 19:49:43'),
(16, NULL, NULL, 4, 'New Order Alert', 'A new order (RGA-20260325-2F0D2) has been placed by a customer.', 1, '2026-03-25 19:49:43'),
(17, 1, NULL, 4, 'Order Accepted!', 'Your order (RGA-20260325-2F0D2) has been accepted and is now processing.', 1, '2026-03-25 19:50:35'),
(18, 1, NULL, 5, 'Order Received', 'Thank you! We have received your order (RGA-20260325-DD99E) and will review it shortly.', 1, '2026-03-25 19:59:10'),
(19, NULL, NULL, 5, 'New Order Alert', 'A new order (RGA-20260325-DD99E) has been placed by a customer.', 1, '2026-03-25 19:59:10'),
(20, 1, NULL, 6, 'Order Received', 'Thank you! We have received your order (RGA-20260325-EB571) and will review it shortly.', 1, '2026-03-25 21:08:07'),
(21, NULL, NULL, 6, 'New Order Alert', 'A new order (RGA-20260325-EB571) has been placed by a customer.', 1, '2026-03-25 21:08:07'),
(22, 1, NULL, 8, 'Order Received', 'Thank you! We have received your order (RGA-20260325-6F832) and will review it shortly.', 1, '2026-03-25 21:16:55'),
(23, NULL, NULL, 8, 'New Order Alert', 'A new order (RGA-20260325-6F832) has been placed by a customer.', 1, '2026-03-25 21:16:55'),
(24, 1, NULL, 9, 'Order Received', 'Thank you! We have received your order (RGA-20260325-99F1C) and will review it shortly.', 1, '2026-03-25 21:18:10'),
(25, NULL, NULL, 9, 'New Order Alert', 'A new order (RGA-20260325-99F1C) has been placed by a customer.', 1, '2026-03-25 21:18:10'),
(26, 1, NULL, 9, 'Payment Received ', 'We have successfully recorded your cash payment of ₱860.00 for Order #9.', 1, '2026-03-27 07:25:06'),
(27, 1, NULL, 9, 'Order Accepted!', 'Your order (RGA-20260325-99F1C) has been accepted and is now processing.', 1, '2026-03-27 07:25:11'),
(28, 1, NULL, 9, 'Ready for Pick-up!', 'Your order (RGA-20260325-99F1C) is ready! Please visit the store to claim it.', 1, '2026-03-27 07:25:16'),
(29, 1, NULL, 9, 'Order Completed', 'Your order (RGA-20260325-99F1C) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-27 07:25:20'),
(30, 1, NULL, 8, 'Payment Received ', 'We have successfully recorded your cash payment of ₱380.00 for Order #8.', 1, '2026-03-27 07:50:59'),
(31, 1, NULL, 10, 'Order Received', 'Thank you! We have received your order (RGA-20260327-77E32) and will review it shortly.', 1, '2026-03-27 08:52:17'),
(32, NULL, NULL, 10, 'New Order Alert', 'A new order (RGA-20260327-77E32) has been placed by a customer.', 1, '2026-03-27 08:52:17'),
(33, 1, NULL, 10, 'Order Accepted!', 'Your order (RGA-20260327-77E32) has been accepted and is now processing.', 1, '2026-03-27 08:52:40'),
(34, 1, NULL, 10, 'Ready for Pick-up!', 'Your order (RGA-20260327-77E32) is ready! Please visit the store to claim it.', 1, '2026-03-27 08:52:55'),
(35, 1, NULL, 10, 'Payment Received ', 'We have successfully recorded your cash payment of ₱224.00 for Order #10.', 1, '2026-03-27 09:23:15'),
(36, 25, NULL, 11, 'Order Received', 'Thank you! We have received your order (RGA-20260327-63DAC) and will review it shortly.', 1, '2026-03-27 21:53:53'),
(37, NULL, NULL, 11, 'New Order Alert', 'A new order (RGA-20260327-63DAC) has been placed by a customer.', 1, '2026-03-27 21:53:53'),
(38, 25, NULL, 11, 'Payment Received ', 'We have successfully recorded your cash payment of ₱1,000.00 for Order #11.', 1, '2026-03-27 21:54:47'),
(39, 25, NULL, 11, 'Order Accepted!', 'Your order (RGA-20260327-63DAC) has been accepted and is now processing.', 1, '2026-03-27 21:54:52'),
(40, 25, NULL, 11, 'Ready for Pick-up!', 'Your order (RGA-20260327-63DAC) is ready! Please visit the store to claim it.', 1, '2026-03-27 21:55:19'),
(41, 25, NULL, 11, 'Payment Received ', 'We have successfully recorded your cash payment of ₱596.67 for Order #11.', 1, '2026-03-27 21:55:35'),
(42, 25, NULL, 12, 'Order Received', 'Thank you! We have received your order (RGA-20260327-F2089) and will review it shortly.', 1, '2026-03-27 23:32:46'),
(43, NULL, NULL, 12, 'New Order Alert', 'A new order (RGA-20260327-F2089) has been placed by a customer.', 1, '2026-03-27 23:32:46'),
(44, 25, NULL, 12, 'Order Accepted!', 'Your order (RGA-20260327-F2089) has been accepted and is now processing.', 1, '2026-03-27 23:33:29'),
(45, 25, NULL, 12, 'Ready for Pick-up!', 'Your order (RGA-20260327-F2089) is ready! Please visit the store to claim it.', 1, '2026-03-27 23:36:23'),
(46, 25, NULL, 12, 'Payment Received ', 'We have successfully recorded your cash payment of ₱1,000.00 for Order #12.', 1, '2026-03-27 23:39:29'),
(47, 25, NULL, 13, 'Order Received', 'Thank you! We have received your order (RGA-20260327-5B6A6) and will review it shortly.', 1, '2026-03-27 23:55:34'),
(48, NULL, NULL, 13, 'New Order Alert', 'A new order (RGA-20260327-5B6A6) has been placed by a customer.', 1, '2026-03-27 23:55:34'),
(49, 25, NULL, 13, 'Order Accepted!', 'Your order (RGA-20260327-5B6A6) has been accepted and is now processing.', 1, '2026-03-27 23:56:06'),
(50, 25, NULL, 13, 'Ready for Pick-up!', 'Your order (RGA-20260327-5B6A6) is ready! Please visit the store to claim it.', 1, '2026-03-27 23:56:14'),
(51, 25, NULL, 14, 'Order Received', 'Thank you! We have received your order (RGA-20260327-6ADFB) and will review it shortly.', 1, '2026-03-28 00:15:32'),
(52, NULL, NULL, 14, 'New Order Alert', 'A new order (RGA-20260327-6ADFB) has been placed by a customer.', 1, '2026-03-28 00:15:32'),
(53, 25, NULL, 14, 'Order Accepted!', 'Your order (RGA-20260327-6ADFB) has been accepted and is now processing.', 1, '2026-03-28 00:15:55'),
(54, 25, NULL, 14, 'Ready for Pick-up!', 'Your order (RGA-20260327-6ADFB) is ready! Please visit the store to claim it.', 1, '2026-03-28 00:16:00'),
(55, 25, NULL, 15, 'Order Received', 'Thank you! We have received your order (RGA-20260327-EBCF9) and will review it shortly.', 1, '2026-03-28 00:37:28'),
(56, NULL, NULL, 15, 'New Order Alert', 'A new order (RGA-20260327-EBCF9) has been placed by a customer.', 1, '2026-03-28 00:37:28'),
(57, 25, NULL, 15, 'Order Accepted!', 'Your order (RGA-20260327-EBCF9) has been accepted and is now processing.', 1, '2026-03-28 00:37:50'),
(58, 25, NULL, 15, 'Ready for Pick-up!', 'Your order (RGA-20260327-EBCF9) is ready! Please visit the store to claim it.', 1, '2026-03-28 00:37:59'),
(59, 25, NULL, 16, 'Order Received', 'Thank you! We have received your order (RGA-20260327-D1CDB) and will review it shortly.', 1, '2026-03-28 00:49:10'),
(60, NULL, NULL, 16, 'New Order Alert', 'A new order (RGA-20260327-D1CDB) has been placed by a customer.', 1, '2026-03-28 00:49:10'),
(61, 25, NULL, 16, 'Order Accepted!', 'Your order (RGA-20260327-D1CDB) has been accepted and is now processing.', 1, '2026-03-28 10:10:21'),
(62, 25, NULL, 16, 'Ready for Pick-up!', 'Your order (RGA-20260327-D1CDB) is ready! Please visit the store to claim it.', 1, '2026-03-28 10:10:27'),
(63, 25, NULL, 17, 'Order Received', 'Thank you! We have received your order (RGA-20260328-38241) and will review it shortly.', 1, '2026-03-28 10:16:33'),
(64, NULL, NULL, 17, 'New Order Alert', 'A new order (RGA-20260328-38241) has been placed by a customer.', 1, '2026-03-28 10:16:33'),
(65, 25, NULL, 17, 'Order Accepted!', 'Your order (RGA-20260328-38241) has been accepted and is now processing.', 1, '2026-03-28 10:17:09'),
(66, 25, NULL, 17, 'Ready for Pick-up!', 'Your order (RGA-20260328-38241) is ready! Please visit the store to claim it.', 1, '2026-03-28 10:17:15'),
(67, 25, NULL, 18, 'Order Received', 'Thank you! We have received your order (RGA-20260328-010F0) and will review it shortly.', 1, '2026-03-28 10:36:34'),
(68, NULL, NULL, 18, 'New Order Alert', 'A new order (RGA-20260328-010F0) has been placed by a customer.', 1, '2026-03-28 10:36:34'),
(69, 25, NULL, 18, 'Order Accepted!', 'Your order (RGA-20260328-010F0) has been accepted and is now processing.', 1, '2026-03-28 10:37:15'),
(70, 25, NULL, 18, 'Ready for Pick-up!', 'Your order (RGA-20260328-010F0) is ready! Please visit the store to claim it.', 1, '2026-03-28 10:37:20'),
(71, 25, NULL, 18, 'Order Completed', 'Your order (RGA-20260328-010F0) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-28 10:38:07'),
(72, NULL, NULL, NULL, 'New Review! ⭐⭐', 'A customer just left a 2-star review: \"yoww love this\"', 1, '2026-03-28 23:25:34'),
(73, 1, NULL, 19, 'Order Received', 'Thank you! We have received your order (RGA-20260329-E369F) and will review it shortly.', 1, '2026-03-29 16:16:27'),
(74, NULL, NULL, 19, 'New Order Alert', 'A new order (RGA-20260329-E369F) has been placed by a customer.', 1, '2026-03-29 16:16:27'),
(75, 1, NULL, 19, 'Order Accepted!', 'Your order (RGA-20260329-E369F) has been accepted and is now processing.', 1, '2026-03-29 16:23:10'),
(76, 1, NULL, 19, 'Ready for Pick-up!', 'Your order (RGA-20260329-E369F) is ready! Please visit the store to claim it.', 1, '2026-03-29 16:24:08'),
(77, 1, NULL, 19, 'Order Completed', 'Your order (RGA-20260329-E369F) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 16:25:17'),
(78, 1, NULL, 20, 'Order Received', 'Thank you! We have received your order (RGA-20260329-0779A) and will review it shortly.', 1, '2026-03-29 16:30:28'),
(79, NULL, NULL, 20, 'New Order Alert', 'A new order (RGA-20260329-0779A) has been placed by a customer.', 1, '2026-03-29 16:30:28'),
(80, 1, NULL, 20, 'Order Accepted!', 'Your order (RGA-20260329-0779A) has been accepted and is now processing.', 1, '2026-03-29 16:31:09'),
(81, 1, NULL, 20, 'Ready for Pick-up!', 'Your order (RGA-20260329-0779A) is ready! Please visit the store to claim it.', 1, '2026-03-29 16:32:17'),
(82, 1, NULL, 20, 'Payment Received ', 'We have successfully recorded your cash payment of ₱1,389.34 for Order #20.', 1, '2026-03-29 16:32:39'),
(83, 1, NULL, 20, 'Order Completed', 'Your order (RGA-20260329-0779A) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 16:32:53'),
(84, 1, NULL, 21, 'Order Received', 'Thank you! We have received your order (RGA-20260329-1C581) and will review it shortly.', 1, '2026-03-29 16:36:00'),
(85, NULL, NULL, 21, 'New Order Alert', 'A new order (RGA-20260329-1C581) has been placed by a customer.', 1, '2026-03-29 16:36:00'),
(86, 1, NULL, 21, 'Payment Received ', 'We have successfully recorded your cash payment of ₱312.00 for Order #21.', 1, '2026-03-29 16:36:45'),
(87, 1, NULL, 21, 'Order Accepted!', 'Your order (RGA-20260329-1C581) has been accepted and is now processing.', 1, '2026-03-29 16:36:50'),
(88, 1, NULL, 21, 'Ready for Pick-up!', 'Your order (RGA-20260329-1C581) is ready! Please visit the store to claim it.', 1, '2026-03-29 16:37:31'),
(89, 1, NULL, 21, 'Order Completed', 'Your order (RGA-20260329-1C581) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 16:37:36'),
(90, 1, NULL, 22, 'Order Received', 'Thank you! We have received your order (RGA-20260329-A26CE) and will review it shortly.', 1, '2026-03-29 16:44:06'),
(91, NULL, NULL, 22, 'New Order Alert', 'A new order (RGA-20260329-A26CE) has been placed by a customer.', 1, '2026-03-29 16:44:06'),
(92, 1, NULL, 22, 'Order Accepted!', 'Your order (RGA-20260329-A26CE) has been accepted and is now processing.', 1, '2026-03-29 16:45:49'),
(93, 1, NULL, 22, 'Ready for Pick-up!', 'Your order (RGA-20260329-A26CE) is ready! Please visit the store to claim it.', 1, '2026-03-29 16:46:45'),
(94, 1, NULL, 22, 'Payment Received ', 'We have successfully recorded your cash payment of ₱1,000.00 for Order #22.', 1, '2026-03-29 16:46:56'),
(95, 1, NULL, 22, 'Order Completed', 'Your order (RGA-20260329-A26CE) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 16:47:07'),
(96, 1, NULL, 8, 'Order Accepted!', 'Your order (RGA-20260325-6F832) has been accepted and is now processing.', 1, '2026-03-29 17:23:22'),
(97, 1, NULL, 8, 'Ready for Pick-up!', 'Your order (RGA-20260325-6F832) is ready! Please visit the store to claim it.', 1, '2026-03-29 17:24:26'),
(98, 1, NULL, 6, 'Order Accepted!', 'Your order (RGA-20260325-EB571) has been accepted and is now processing.', 1, '2026-03-29 17:57:40'),
(99, 1, NULL, 6, 'Payment Received ', 'We have successfully recorded your cash payment of ₱3,575.00 for Order #6.', 1, '2026-03-29 17:57:52'),
(100, 1, NULL, 6, 'Ready for Pick-up!', 'Your order (RGA-20260325-EB571) is ready! Please visit the store to claim it.', 1, '2026-03-29 17:58:12'),
(101, 1, NULL, 6, 'Order Completed', 'Your order (RGA-20260325-EB571) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 17:58:20'),
(102, 1, NULL, 5, 'Order Update', 'Unfortunately, your order (RGA-20260325-DD99E) was rejected. Please contact us for details.', 1, '2026-03-29 17:58:55'),
(103, NULL, NULL, 3, 'Order Cancelled', 'Order (RGA-20260325-6B1AB) has been cancelled by the customer.', 1, '2026-03-29 17:59:21'),
(104, 1, NULL, 4, 'Ready for Pick-up!', 'Your order (RGA-20260325-2F0D2) is ready! Please visit the store to claim it.', 1, '2026-03-29 18:00:07'),
(105, 1, NULL, 4, 'Order Completed', 'Your order (RGA-20260325-2F0D2) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 18:02:16'),
(106, 1, NULL, 10, 'Order Completed', 'Your order (RGA-20260327-77E32) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 18:18:38'),
(107, 25, NULL, 12, 'Order Completed', 'Your order (RGA-20260327-F2089) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 18:19:00'),
(108, 1, NULL, 8, 'Payment Received ', 'We have successfully recorded your cash payment of ₱600.00 for Order #8.', 1, '2026-03-29 18:19:53'),
(109, 1, NULL, 8, 'Order Completed', 'Your order (RGA-20260325-6F832) is complete. Thank you for choosing RGA Frames!', 1, '2026-03-29 18:20:19'),
(110, NULL, NULL, NULL, 'New Review! ⭐⭐⭐⭐⭐', 'A customer just left a 5-star review: \"I love the quality of frames!\"', 1, '2026-03-29 18:43:06'),
(111, NULL, NULL, NULL, 'New Review! ⭐⭐', 'A customer just left a 2-star review: \"Too expensive!\"', 1, '2026-03-30 11:41:28'),
(112, 25, NULL, 23, 'Order Received', 'Thank you! We have received your order (RGA-20260411-6F193) and will review it shortly.', 1, '2026-04-11 22:45:23'),
(113, NULL, NULL, 23, 'New Order Alert', 'A new order (RGA-20260411-6F193) has been placed by a customer.', 1, '2026-04-11 22:45:23'),
(114, 25, NULL, 23, 'Order Accepted!', 'Your order (RGA-20260411-6F193) has been accepted and is now processing.', 1, '2026-04-11 22:46:11'),
(115, 25, NULL, 23, 'Ready for Pick-up!', 'Your order (RGA-20260411-6F193) is ready! Please visit the store to claim it.', 1, '2026-04-11 22:46:27'),
(116, 25, NULL, 23, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,164.00 for Order #23.', 1, '2026-04-11 22:46:43'),
(117, 25, NULL, 23, 'Order Completed', 'Your order (RGA-20260411-6F193) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-11 22:46:54'),
(118, 25, NULL, 24, 'Order Received', 'Thank you! We have received your order (RGA-20260411-31D51) and will review it shortly.', 1, '2026-04-11 22:57:48'),
(119, NULL, NULL, 24, 'New Order Alert', 'A new order (RGA-20260411-31D51) has been placed by a customer.', 1, '2026-04-11 22:57:48'),
(120, 25, NULL, 24, 'Payment Received', 'We have successfully recorded your cash payment of ₱2,000.00 for Order #24.', 1, '2026-04-11 22:59:53'),
(121, 25, NULL, 24, 'Order Accepted!', 'Your order (RGA-20260411-31D51) has been accepted and is now processing.', 1, '2026-04-11 23:01:14'),
(122, 25, NULL, 24, 'Ready for Pick-up!', 'Your order (RGA-20260411-31D51) is ready! Please visit the store to claim it.', 1, '2026-04-11 23:26:31'),
(123, 25, NULL, 24, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,784.00 for Order #24.', 1, '2026-04-11 23:27:00'),
(124, 25, NULL, 24, 'Order Completed', 'Your order (RGA-20260411-31D51) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-11 23:27:46'),
(125, 25, NULL, 25, 'Order Received', 'Thank you! We have received your order (RGA-20260411-6E016) and will review it shortly.', 1, '2026-04-11 23:38:14'),
(126, NULL, NULL, 25, 'New Order Alert', 'A new order (RGA-20260411-6E016) has been placed by a customer.', 1, '2026-04-11 23:38:14'),
(127, NULL, NULL, 25, 'Order Cancelled', 'Order (RGA-20260411-6E016) has been cancelled by the customer.', 1, '2026-04-11 23:38:48'),
(128, 25, NULL, 26, 'Order Received', 'Thank you! We have received your order (RGA-20260411-6EB72) and will review it shortly.', 1, '2026-04-11 23:39:31'),
(129, NULL, NULL, 26, 'New Order Alert', 'A new order (RGA-20260411-6EB72) has been placed by a customer.', 1, '2026-04-11 23:39:31'),
(130, 25, NULL, 26, 'Order Accepted!', 'Your order (RGA-20260411-6EB72) has been accepted and is now processing.', 1, '2026-04-11 23:39:53'),
(131, 25, NULL, 26, 'Payment Received', 'We have successfully recorded your cash payment of ₱624.00 for Order #26.', 1, '2026-04-11 23:40:05'),
(132, 25, NULL, 26, 'Ready for Pick-up!', 'Your order (RGA-20260411-6EB72) is ready! Please visit the store to claim it.', 1, '2026-04-11 23:40:18'),
(133, 25, NULL, 26, 'Order Completed', 'Your order (RGA-20260411-6EB72) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-11 23:40:23'),
(134, 25, NULL, 27, 'Order Received', 'Thank you! We have received your order (RGA-20260411-C2AD3) and will review it shortly.', 1, '2026-04-11 23:43:44'),
(135, NULL, NULL, 27, 'New Order Alert', 'A new order (RGA-20260411-C2AD3) has been placed by a customer.', 1, '2026-04-11 23:43:44'),
(136, 25, NULL, 27, 'Order Accepted!', 'Your order (RGA-20260411-C2AD3) has been accepted and is now processing.', 1, '2026-04-11 23:44:05'),
(137, 25, NULL, 27, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,526.66 for Order #27.', 1, '2026-04-11 23:44:25'),
(138, 25, NULL, 27, 'Ready for Pick-up!', 'Your order (RGA-20260411-C2AD3) is ready! Please visit the store to claim it.', 1, '2026-04-11 23:44:30'),
(139, 25, NULL, 27, 'Order Completed', 'Your order (RGA-20260411-C2AD3) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-11 23:44:35'),
(140, 25, NULL, 28, 'Order Received', 'Thank you! We have received your order (RGA-20260411-2F057) and will review it shortly.', 1, '2026-04-11 23:48:18'),
(141, NULL, NULL, 28, 'New Order Alert', 'A new order (RGA-20260411-2F057) has been placed by a customer.', 1, '2026-04-11 23:48:18'),
(142, 25, NULL, 28, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,500.00 for Order #28.', 1, '2026-04-11 23:49:17'),
(143, 25, NULL, 28, 'Order Accepted!', 'Your order (RGA-20260411-2F057) has been accepted and is now processing.', 1, '2026-04-11 23:49:24'),
(144, 25, NULL, 28, 'Ready for Pick-up!', 'Your order (RGA-20260411-2F057) is ready! Please visit the store to claim it.', 1, '2026-04-11 23:49:30'),
(145, 25, NULL, 28, 'Order Completed', 'Your order (RGA-20260411-2F057) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-11 23:49:34'),
(146, NULL, NULL, NULL, 'New Review! ⭐⭐⭐⭐⭐', 'A customer just left a 5-star review: \"thanks i already picked up my order, and...\"', 1, '2026-04-11 23:50:32'),
(147, 25, NULL, 29, 'Order Received', 'Thank you! We have received your order (RGA-20260411-5CC45) and will review it shortly.', 1, '2026-04-12 01:17:03'),
(148, NULL, NULL, 29, 'New Order Alert', 'A new order (RGA-20260411-5CC45) has been placed by a customer.', 1, '2026-04-12 01:17:03'),
(149, NULL, NULL, NULL, 'New Review! ⭐⭐', 'A customer just left a 2-star review: \"not that bad not good either\"', 1, '2026-04-12 01:41:20'),
(150, 25, NULL, 29, 'Order Accepted!', 'Your order (RGA-20260411-5CC45) has been accepted and is now processing.', 1, '2026-04-12 01:42:33'),
(151, 25, NULL, 29, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,000.00 for Order #29.', 1, '2026-04-12 01:42:57'),
(152, 25, NULL, 29, 'Ready for Pick-up!', 'Your order (RGA-20260411-5CC45) is ready! Please visit the store to claim it.', 1, '2026-04-12 01:43:16'),
(153, 25, NULL, 29, 'Payment Received', 'We have successfully recorded your cash payment of ₱624.00 for Order #29.', 1, '2026-04-12 01:43:41'),
(154, 25, NULL, 29, 'Order Completed', 'Your order (RGA-20260411-5CC45) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-12 01:44:00'),
(155, 25, NULL, 30, 'Order Received', 'Thank you! We have received your order (RGA-20260411-6024C) and will review it shortly.', 1, '2026-04-12 01:59:35'),
(156, NULL, NULL, 30, 'New Order Alert', 'A new order (RGA-20260411-6024C) has been placed by a customer.', 1, '2026-04-12 01:59:35'),
(157, 25, NULL, 30, 'Order Accepted!', 'Your order (RGA-20260411-6024C) has been accepted and is now processing.', 1, '2026-04-12 02:02:54'),
(158, 25, NULL, 30, 'Ready for Pick-up!', 'Your order (RGA-20260411-6024C) is ready! Please visit the store to claim it.', 1, '2026-04-12 02:05:20'),
(159, 25, NULL, 30, 'Order Completed', 'Your order (RGA-20260411-6024C) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-12 02:05:26'),
(160, 25, NULL, 31, 'Order Received', 'Thank you! We have received your order (RGA-20260411-BEECC) and will review it shortly.', 1, '2026-04-12 02:10:03'),
(161, NULL, NULL, 31, 'New Order Alert', 'A new order (RGA-20260411-BEECC) has been placed by a customer.', 1, '2026-04-12 02:10:03'),
(162, 25, NULL, 32, 'Order Received', 'Thank you! We have received your order (RGA-20260418-59910) and will review it shortly.', 1, '2026-04-18 23:00:31'),
(163, NULL, NULL, 32, 'New Order Alert', 'A new order (RGA-20260418-59910) has been placed by a customer.', 1, '2026-04-18 23:00:31'),
(164, 25, NULL, 31, 'Order Accepted!', 'Your order (RGA-20260411-BEECC) has been accepted and is now processing.', 1, '2026-04-18 23:08:08'),
(165, 25, NULL, 32, 'Order Accepted!', 'Your order (RGA-20260418-59910) has been accepted and is now processing.', 1, '2026-04-18 23:10:46'),
(166, 25, NULL, 32, 'Ready for Pick-up!', 'Your order (RGA-20260418-59910) is ready! Please visit the store to claim it.', 1, '2026-04-18 23:11:10'),
(167, 25, NULL, 32, 'Payment Received', 'We have successfully recorded your cash payment of ₱324.00 for Order #32.', 1, '2026-04-18 23:11:39'),
(168, 25, NULL, 32, 'Order Completed', 'Your order (RGA-20260418-59910) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-18 23:12:27'),
(169, NULL, NULL, NULL, 'New Review! ⭐', 'A customer just left a 1-star review: \"<script>alert(\"HACKED\")</script>\"', 1, '2026-04-19 13:58:16'),
(170, 25, NULL, 33, 'Order Received', 'Thank you! We have received your order (RGA-20260419-9AA64) and will review it shortly.', 1, '2026-04-19 15:21:26'),
(171, NULL, NULL, 33, 'New Order Alert', 'A new order (RGA-20260419-9AA64) has been placed by a customer.', 1, '2026-04-19 15:21:26'),
(172, 25, NULL, 33, 'Order Accepted!', 'Your order (RGA-20260419-9AA64) has been accepted and is now processing.', 1, '2026-04-19 15:21:54'),
(173, 25, NULL, 33, 'Ready for Pick-up!', 'Your order (RGA-20260419-9AA64) is ready! Please visit the store to claim it.', 1, '2026-04-19 15:21:58'),
(174, 25, NULL, 33, 'Payment Received', 'We have successfully recorded your cash payment of ₱626.66 for Order #33.', 1, '2026-04-19 15:22:12'),
(175, 25, NULL, 33, 'Order Completed', 'Your order (RGA-20260419-9AA64) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-19 15:22:16'),
(176, 25, NULL, 34, 'Order Received', 'Thank you! We have received your order (RGA-20260419-493B5) and will review it shortly.', 1, '2026-04-19 15:25:24'),
(177, NULL, NULL, 34, 'New Order Alert', 'A new order (RGA-20260419-493B5) has been placed by a customer.', 1, '2026-04-19 15:25:24'),
(178, 25, NULL, 34, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,526.66 for Order #34.', 1, '2026-04-19 15:25:47'),
(179, 25, NULL, 34, 'Order Accepted!', 'Your order (RGA-20260419-493B5) has been accepted and is now processing.', 1, '2026-04-19 15:25:59'),
(180, 25, NULL, 34, 'Ready for Pick-up!', 'Your order (RGA-20260419-493B5) is ready! Please visit the store to claim it.', 1, '2026-04-19 15:26:03'),
(181, 25, NULL, 34, 'Order Completed', 'Your order (RGA-20260419-493B5) is complete. Thank you for choosing RGA Frames!', 1, '2026-04-19 15:26:07'),
(182, 25, NULL, 35, 'Order Received', 'Thank you! We have received your order (RGA-20260502-B2F89) and will review it shortly.', 1, '2026-05-03 02:15:27'),
(183, NULL, NULL, 35, 'New Order Alert', 'A new order (RGA-20260502-B2F89) has been placed by a customer.', 1, '2026-05-03 02:15:27'),
(184, 25, NULL, 36, 'Order Received', 'Thank you! We have received your order (RGA-20260502-3C6C1) and will review it shortly.', 1, '2026-05-03 05:41:15'),
(185, NULL, NULL, 36, 'New Order Alert', 'A new order (RGA-20260502-3C6C1) has been placed by a customer.', 1, '2026-05-03 05:41:15'),
(186, 25, NULL, 36, 'Payment Received', 'We have successfully recorded your cash payment of ₱526.66 for Order #36.', 1, '2026-05-03 05:46:28'),
(187, 25, NULL, 36, 'Order Accepted!', 'Your order (RGA-20260502-3C6C1) has been accepted and is now processing.', 1, '2026-05-03 05:46:33'),
(188, 25, NULL, 37, 'Order Received', 'Thank you! We have received your order (RGA-20260503-69CE3) and will review it shortly.', 1, '2026-05-03 16:49:13'),
(189, NULL, NULL, 37, 'New Order Alert', 'A new order (RGA-20260503-69CE3) has been placed by a customer.', 1, '2026-05-03 16:49:13'),
(190, NULL, NULL, NULL, 'New Review! ⭐⭐⭐', 'A customer just left a 3-star review: \"okeoke\"', 1, '2026-05-03 17:20:03'),
(191, 25, NULL, 37, 'Order Accepted!', 'Your order (RGA-20260503-69CE3) has been accepted and is now processing.', 1, '2026-05-03 21:27:15'),
(192, 25, NULL, 37, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,000.00 for Order #37.', 1, '2026-05-03 21:27:43'),
(193, 25, NULL, 38, 'Order Received', 'Thank you! We have received your order (RGA-20260503-89D41) and will review it shortly.', 1, '2026-05-03 21:29:14'),
(194, NULL, NULL, 38, 'New Order Alert', 'A new order (RGA-20260503-89D41) has been placed by a customer.', 1, '2026-05-03 21:29:14'),
(195, 25, NULL, 38, 'Order Accepted!', 'Your order (RGA-20260503-89D41) has been accepted and is now processing.', 1, '2026-05-03 21:31:00'),
(196, 25, NULL, 38, 'Ready for Pick-up!', 'Your order (RGA-20260503-89D41) is ready! Please visit the store to claim it.', 1, '2026-05-03 22:51:55'),
(197, 25, NULL, 38, 'Payment Received', 'We have successfully recorded your cash payment of ₱346.00 for Order #38.', 1, '2026-05-03 22:52:06'),
(198, 25, NULL, 38, 'Payment Received', 'We have successfully recorded your cash payment of ₱38.00 for Order #38.', 1, '2026-05-03 22:52:31'),
(199, 25, NULL, 38, 'Order Completed', 'Your order (RGA-20260503-89D41) is complete. Thank you for choosing RGA Frames!', 1, '2026-05-03 22:52:36'),
(200, 25, NULL, 36, 'Payment Received', 'We have successfully recorded your cash payment of ₱1,000.00 for Order #36.', 1, '2026-05-03 22:55:30'),
(201, 25, NULL, 36, 'Ready for Pick-up!', 'Your order (RGA-20260502-3C6C1) is ready! Please visit the store to claim it.', 1, '2026-05-03 22:55:36'),
(202, 25, NULL, 36, 'Order Completed', 'Your order (RGA-20260502-3C6C1) is complete. Thank you for choosing RGA Frames!', 1, '2026-05-03 22:55:41'),
(203, 25, NULL, 39, 'Order Received', 'Thank you! We have received your order (RGA-20260505-EADCA) and will review it shortly.', 1, '2026-05-05 21:36:40'),
(204, NULL, NULL, 39, 'New Order Alert', 'A new order (RGA-20260505-EADCA) has been placed by a customer.', 1, '2026-05-05 21:36:40'),
(205, 25, NULL, 40, 'Order Received', 'Thank you! We have received your order (RGA-20260507-0ADF2) and will review it shortly.', 1, '2026-05-07 22:32:28'),
(206, NULL, NULL, 40, 'New Order Alert', 'A new order (RGA-20260507-0ADF2) has been placed by a customer.', 1, '2026-05-07 22:32:28'),
(207, NULL, NULL, 40, 'Order Cancelled', 'Order (RGA-20260507-0ADF2) has been cancelled by the customer.', 1, '2026-05-07 23:21:59');

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
(1, 1, 'RGA-20260324-A044C', 1736.67, 0.00, 1736.67, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-24 23:09:06'),
(2, 1, 'RGA-20260324-9A431', 860.00, 0.00, 860.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-25 01:58:13'),
(3, 1, 'RGA-20260325-6B1AB', 643.33, 0.00, 643.33, 'CASH', 'CANCELLED', 'PICKUP', NULL, '2026-03-25 19:47:15'),
(4, 1, 'RGA-20260325-2F0D2', 1596.67, 0.00, 1596.67, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-25 19:49:43'),
(5, 1, 'RGA-20260325-DD99E', 860.00, 0.00, 860.00, 'CASH', 'REJECTED', 'PICKUP', NULL, '2026-03-25 19:59:10'),
(6, 1, 'RGA-20260325-EB571', 3575.00, 0.00, 3575.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-25 21:08:07'),
(8, 1, 'RGA-20260325-6F832', 980.00, 0.00, 980.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-25 21:16:55'),
(9, 1, 'RGA-20260325-99F1C', 860.00, 0.00, 860.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-25 21:18:10'),
(10, 1, 'RGA-20260327-77E32', 405.00, 81.00, 324.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-27 08:52:17'),
(11, 25, 'RGA-20260327-63DAC', 1596.67, 0.00, 1596.67, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-27 21:53:53'),
(12, 25, 'RGA-20260327-F2089', 2170.00, 0.00, 2170.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-27 23:32:45'),
(13, 25, 'RGA-20260327-5B6A6', 140.00, 0.00, 140.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-27 23:55:34'),
(14, 25, 'RGA-20260327-6ADFB', 1195.00, 0.00, 1195.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-28 00:15:32'),
(15, 25, 'RGA-20260327-EBCF9', 38.00, 7.60, 30.40, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-28 00:37:28'),
(16, 25, 'RGA-20260327-D1CDB', 100.00, 20.00, 80.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-28 00:49:10'),
(17, 25, 'RGA-20260328-38241', 513.33, 102.67, 410.66, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-28 10:16:33'),
(18, 25, 'RGA-20260328-010F0', 90.00, 18.00, 72.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-28 10:36:34'),
(19, 1, 'RGA-20260329-E369F', 1323.33, 264.67, 1058.66, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-29 16:16:27'),
(20, 1, 'RGA-20260329-0779A', 1736.67, 347.33, 1389.34, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-29 16:30:28'),
(21, 1, 'RGA-20260329-1C581', 390.00, 78.00, 312.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-29 16:36:00'),
(22, 1, 'RGA-20260329-A26CE', 3263.33, 652.67, 2610.66, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-03-29 16:44:06'),
(23, 25, 'RGA-20260411-6F193', 2705.00, 541.00, 2164.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-11 22:45:23'),
(24, 25, 'RGA-20260411-31D51', 4730.00, 946.00, 3784.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-11 22:57:48'),
(25, 25, 'RGA-20260411-6E016', 860.00, 172.00, 688.00, 'CASH', 'CANCELLED', 'PICKUP', NULL, '2026-04-11 23:38:14'),
(26, 25, 'RGA-20260411-6EB72', 780.00, 156.00, 624.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-11 23:39:31'),
(27, 25, 'RGA-20260411-C2AD3', 1908.33, 381.67, 1526.66, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-11 23:43:44'),
(28, 25, 'RGA-20260411-2F057', 1875.00, 375.00, 1500.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-11 23:48:18'),
(29, 25, 'RGA-20260411-5CC45', 2030.00, 406.00, 1624.00, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-12 01:17:03'),
(30, 25, 'RGA-20260411-6024C', 2170.00, 434.00, 1736.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-12 01:59:35'),
(31, 25, 'RGA-20260411-BEECC', 780.00, 156.00, 624.00, 'CASH', 'PROCESSING', 'PICKUP', NULL, '2026-04-12 02:10:03'),
(32, 25, 'RGA-20260418-59910', 780.00, 156.00, 624.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-18 23:00:31'),
(33, 25, 'RGA-20260419-9AA64', 783.33, 156.67, 626.66, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-19 15:21:26'),
(34, 25, 'RGA-20260419-493B5', 1908.33, 381.67, 1526.66, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-04-19 15:25:24'),
(35, 25, 'RGA-20260502-B2F89', 1063.33, 212.67, 850.66, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-05-03 02:15:27'),
(36, 25, 'RGA-20260502-3C6C1', 1908.33, 381.67, 1526.66, 'CASH', 'COMPLETED', 'PICKUP', NULL, '2026-05-03 05:41:15'),
(37, 25, 'RGA-20260503-69CE3', 1578.33, 315.67, 1262.66, 'CASH', 'PROCESSING', 'PICKUP', NULL, '2026-05-03 16:49:13'),
(38, 25, 'RGA-20260503-89D41', 730.00, 146.00, 584.00, 'GCASH', 'COMPLETED', 'PICKUP', NULL, '2026-05-03 21:29:14'),
(39, 25, 'RGA-20260505-EADCA', 1560.00, 312.00, 1248.00, 'CASH', 'PENDING', 'PICKUP', NULL, '2026-05-05 21:36:40'),
(40, 25, 'RGA-20260507-0ADF2', 2450.00, 490.00, 1960.00, 'CASH', 'CANCELLED', 'PICKUP', NULL, '2026-05-07 22:32:28');

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
(24, NULL, 1, '113007', '2026-03-04 08:54:44', 0),
(29, 1, NULL, '199392', '2026-03-26 16:16:41', 0),
(52, 26, NULL, '140952', '2026-03-29 14:22:21', 1),
(54, 25, NULL, '869025', '2026-05-05 00:37:31', 0);

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
(3, 'Canvas', 2.50, 12.00, 18.00, 50.00, 96.00, 1),
(5, 'Photo Paper', 1.50, 3.50, 4.98, 50.00, 96.00, 1);

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
(1, 1, 1736.67, 'FULL', '2026-03-24 23:09:06'),
(2, 2, 860.00, 'FULL', '2026-03-25 01:58:13'),
(3, 3, 643.33, 'PENDING', '2026-03-25 19:47:15'),
(4, 4, 1596.67, 'FULL', '2026-03-25 19:49:43'),
(5, 5, 860.00, 'PENDING', '2026-03-25 19:59:10'),
(6, 6, 3575.00, 'FULL', '2026-03-25 21:08:07'),
(8, 8, 980.00, 'FULL', '2026-03-25 21:16:55'),
(9, 9, 860.00, 'FULL', '2026-03-25 21:18:10'),
(10, 10, 324.00, 'FULL', '2026-03-27 08:52:17'),
(11, 11, 1596.67, 'FULL', '2026-03-27 21:53:53'),
(12, 12, 2170.00, 'FULL', '2026-03-27 23:32:46'),
(13, 13, 140.00, 'FULL', '2026-03-27 23:55:34'),
(14, 14, 1195.00, 'FULL', '2026-03-28 00:15:32'),
(15, 15, 30.40, 'FULL', '2026-03-28 00:37:28'),
(16, 16, 80.00, 'FULL', '2026-03-28 00:49:10'),
(17, 17, 410.66, 'FULL', '2026-03-28 10:16:33'),
(18, 18, 72.00, 'FULL', '2026-03-28 10:36:34'),
(19, 19, 1058.66, 'FULL', '2026-03-29 16:16:27'),
(20, 20, 1389.34, 'FULL', '2026-03-29 16:30:28'),
(21, 21, 312.00, 'FULL', '2026-03-29 16:36:00'),
(22, 22, 2610.66, 'FULL', '2026-03-29 16:44:06'),
(23, 23, 2164.00, 'FULL', '2026-04-11 22:45:23'),
(24, 24, 3784.00, 'FULL', '2026-04-11 22:57:48'),
(25, 25, 688.00, 'PENDING', '2026-04-11 23:38:14'),
(26, 26, 624.00, 'FULL', '2026-04-11 23:39:31'),
(27, 27, 1526.66, 'FULL', '2026-04-11 23:43:44'),
(28, 28, 1500.00, 'FULL', '2026-04-11 23:48:18'),
(29, 29, 1624.00, 'FULL', '2026-04-12 01:17:03'),
(30, 30, 1736.00, 'FULL', '2026-04-12 01:59:35'),
(31, 31, 624.00, 'PENDING', '2026-04-12 02:10:03'),
(32, 32, 624.00, 'FULL', '2026-04-18 23:00:31'),
(33, 33, 626.66, 'FULL', '2026-04-19 15:21:26'),
(34, 34, 1526.66, 'FULL', '2026-04-19 15:25:24'),
(35, 35, 850.66, 'PENDING', '2026-05-03 02:15:27'),
(36, 36, 1526.66, 'FULL', '2026-05-03 05:41:15'),
(37, 37, 1262.66, 'PARTIAL', '2026-05-03 16:49:13'),
(38, 38, 584.00, 'FULL', '2026-05-03 21:29:14'),
(39, 39, 1248.00, 'PENDING', '2026-05-05 21:36:40'),
(40, 40, 1960.00, 'PENDING', '2026-05-07 22:32:28');

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
(1, 1, 1000.00, 'uploads/uploaded_receipts/gcash_1_1774364946.jpg', 'Verified', '2026-03-24 23:09:06'),
(2, 1, 736.67, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-25 01:55:34'),
(3, 2, 860.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-25 02:01:32'),
(4, 4, 596.67, 'uploads/uploaded_receipts/gcash_1_1774439383.jpg', 'Verified', '2026-03-25 19:49:43'),
(5, 9, 860.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-27 07:25:06'),
(6, 8, 380.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-27 07:50:59'),
(7, 10, 100.00, 'uploads/uploaded_receipts/gcash_1_1774572737.jpg', 'Verified', '2026-03-27 08:52:17'),
(8, 10, 224.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-27 09:23:15'),
(9, 11, 1000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-27 21:54:47'),
(10, 11, 596.67, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-27 21:55:35'),
(11, 12, 1170.00, 'uploads/uploaded_receipts/gcash_25_1774625565.jpg', 'Verified', '2026-03-27 23:32:46'),
(12, 12, 1000.00, 'uploads/receipts/receipt_12_1774625804_69c6a40ce85d0.jpg', 'Verified', '2026-03-27 23:36:44'),
(13, 12, 1000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-27 23:39:29'),
(14, 13, 90.00, 'uploads/uploaded_receipts/gcash_25_1774626934.jpg', 'Verified', '2026-03-27 23:55:34'),
(15, 13, 50.00, 'uploads/receipts/receipt_13_1774627008_69c6a8c0d6638.jpg', 'Verified', '2026-03-27 23:56:48'),
(16, 14, 1000.00, 'uploads/uploaded_receipts/gcash_25_1774628132.jpg', 'Verified', '2026-03-28 00:15:32'),
(17, 14, 195.00, 'uploads/receipts/receipt_14_1774628192_69c6ad60ce514.jpg', 'Verified', '2026-03-28 00:16:32'),
(18, 15, 10.00, 'uploads/uploaded_receipts/gcash_25_1774629448.jpg', 'Verified', '2026-03-28 00:37:28'),
(19, 15, 40.00, 'uploads/receipts/receipt_15_1774629513_69c6b289ed2df.jpg', 'Verified', '2026-03-28 00:38:33'),
(20, 16, 40.00, 'uploads/uploaded_receipts/gcash_25_1774630150.jpg', 'Verified', '2026-03-28 00:49:10'),
(21, 16, 40.00, 'uploads/receipts/receipt_16_1774663795_69c738732c127.jpg', 'Verified', '2026-03-28 10:09:55'),
(22, 17, 200.00, 'uploads/uploaded_receipts/gcash_25_1774664193.jpg', 'Verified', '2026-03-28 10:16:33'),
(23, 17, 210.66, 'uploads/receipts/receipt_17_1774664272_69c73a508a7e0.jpg', 'Verified', '2026-03-28 10:17:52'),
(24, 18, 22.00, 'uploads/uploaded_receipts/gcash_25_1774665394.jpg', 'Verified', '2026-03-28 10:36:34'),
(25, 18, 50.00, 'uploads/receipts/receipt_18_1774665467_69c73efb48e0b.jpg', 'Verified', '2026-03-28 10:37:47'),
(26, 19, 858.66, 'uploads/uploaded_receipts/gcash_1_1774772187.jpg', 'Verified', '2026-03-29 16:16:27'),
(27, 19, 200.00, 'uploads/receipts/receipt_19_1774772684_69c8e1cce91fb.jpg', 'Verified', '2026-03-29 16:24:44'),
(28, 20, 1389.34, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-29 16:32:39'),
(29, 21, 312.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-29 16:36:45'),
(30, 22, 1610.66, 'uploads/uploaded_receipts/gcash_1_1774773846.jpg', 'Verified', '2026-03-29 16:44:06'),
(31, 22, 1000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-29 16:46:56'),
(32, 6, 3575.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-29 17:57:52'),
(33, 4, 1000.00, 'uploads/receipts/receipt_4_1774778514_69c8f892099dd.jpg', 'Verified', '2026-03-29 18:01:54'),
(34, 8, 600.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-03-29 18:19:53'),
(35, 23, 1000.00, 'uploads/uploaded_receipts/gcash_25_1775918723.jpg', 'Verified', '2026-04-11 22:45:23'),
(36, 23, 1164.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-11 22:46:43'),
(37, 24, 2000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-11 22:59:53'),
(38, 24, 1784.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-11 23:27:00'),
(39, 26, 624.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-11 23:40:05'),
(40, 27, 1526.66, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-11 23:44:25'),
(41, 28, 1500.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-11 23:49:17'),
(42, 29, 1000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-12 01:42:57'),
(43, 29, 624.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-12 01:43:41'),
(44, 30, 736.00, 'uploads/uploaded_receipts/gcash_25_1775930375.jpg', 'Verified', '2026-04-12 01:59:35'),
(45, 30, 1000.00, 'uploads/receipts/receipt_30_1775930702_69da8d4e462ec.jpg', 'Verified', '2026-04-12 02:05:02'),
(46, 32, 300.00, 'uploads/uploaded_receipts/gcash_25_1776524431.jpg', 'Verified', '2026-04-18 23:00:31'),
(47, 32, 324.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-18 23:11:39'),
(48, 33, 626.66, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-19 15:22:12'),
(49, 34, 1526.66, 'Admin: Walk-in Cash Payment', 'Verified', '2026-04-19 15:25:47'),
(50, 36, 526.66, 'Admin: Walk-in Cash Payment', 'Verified', '2026-05-03 05:46:28'),
(51, 37, 1000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-05-03 21:27:43'),
(52, 38, 200.00, 'uploads/uploaded_receipts/gcash_25_1777814954.png', 'Verified', '2026-05-03 21:29:14'),
(53, 38, 346.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-05-03 22:52:06'),
(54, 38, 38.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-05-03 22:52:31'),
(55, 36, 1000.00, 'Admin: Walk-in Cash Payment', 'Verified', '2026-05-03 22:55:30');

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
  `drive_link` text DEFAULT NULL,
  `width_inch` decimal(5,2) NOT NULL,
  `height_inch` decimal(5,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `sub_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_printing_order_items`
--

INSERT INTO `tbl_printing_order_items` (`printing_order_item_id`, `cart_id`, `order_id`, `paper_type_id`, `image_path`, `drive_link`, `width_inch`, `height_inch`, `quantity`, `sub_total`) VALUES
(1, NULL, 1, 5, 'uploads/customer_print/CUSTOM_PRINT_1774364918_3050.jpg', NULL, 10.00, 16.00, 1, 140.00),
(2, NULL, 6, 3, 'uploads/customer_print/CUSTOM_PRINT_1774444084_7613.jpg', NULL, 24.00, 30.00, 1, 0.00),
(3, NULL, 8, 5, 'uploads/customer_print/READY_MADE_PRINT_1774444399_7631.jpg', NULL, 8.00, 10.00, 1, 120.00),
(5, NULL, 12, 5, 'uploads/customer_print/CUSTOM_PRINT_1774625540_9226.jpg', NULL, 10.00, 16.00, 1, 140.00),
(6, NULL, 13, 5, 'uploads/customer_print/PRINT_ONLY_1774626913_1907.jpg', NULL, 10.00, 16.00, 1, 140.00),
(7, NULL, 14, 3, 'uploads/customer_print/PRINT_ONLY_1774628112_4025.jpg', NULL, 16.00, 20.00, 1, 1195.00),
(8, NULL, 15, 5, 'uploads/customer_print/PRINT_ONLY_1774629428_7317.jpg', NULL, 5.00, 10.00, 1, 38.00),
(9, NULL, 16, 5, 'uploads/customer_print/PRINT_ONLY_1774630129_9051.jpg', NULL, 10.00, 12.00, 1, 100.00),
(10, NULL, 18, 5, 'uploads/customer_print/PRINT_ONLY_1774665371_8195.jpg', NULL, 8.00, 12.00, 1, 90.00),
(11, NULL, 19, 5, 'uploads/customer_print/READY_MADE_PRINT_1774772093_6974.jpg', NULL, 10.00, 12.00, 1, 180.00),
(12, NULL, 20, 5, 'uploads/customer_print/CUSTOM_PRINT_1774773014_7549.jpg', NULL, 10.00, 16.00, 1, 140.00),
(13, NULL, 21, 5, 'uploads/customer_print/PRINT_ONLY_1774773304_1089.jpg', NULL, 13.00, 20.00, 1, 390.00),
(14, NULL, 22, 5, 'uploads/customer_print/CUSTOM_PRINT_1774773812_3159.jpg', NULL, 18.00, 25.00, 1, 675.00),
(15, NULL, 24, 5, 'uploads/customer_print/CUSTOM_PRINT_1775919419_6582.jpg', NULL, 20.00, 30.00, 1, 900.00),
(16, NULL, 27, 5, 'uploads/customer_print/READY_MADE_PRINT_1775922221_5613.jpg', NULL, 11.00, 20.00, 1, 330.00),
(17, NULL, 28, 3, 'uploads/customer_print/PRINT_ONLY_1775922466_2006.jpg', NULL, 25.00, 30.00, 1, 1875.00),
(18, NULL, 30, 5, 'uploads/customer_print/CUSTOM_PRINT_1775930342_7418.jpg', NULL, 10.00, 16.00, 1, 140.00),
(19, NULL, 33, 5, 'uploads/customer_print/CUSTOM_PRINT_1776583283_8295.jpg', NULL, 10.00, 16.00, 1, 140.00),
(20, NULL, 34, 5, 'uploads/customer_print/READY_MADE_PRINT_1776583521_8324.jpg', NULL, 11.00, 20.00, 1, 330.00),
(21, NULL, 36, 5, 'uploads/customer_print/READY_MADE_PRINT_1777758070_3979.png', NULL, 11.00, 20.00, 1, 330.00),
(22, 2, NULL, 5, 'uploads/customer_print/CUSTOM_PRINT_1778400641_8288.jpg', NULL, 3.50, 5.00, 1, 9.00);

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
(7, 'Gold Antique Frame', 5, 13, 5, 8.00, 10.00, 780.00),
(9, 'Black Frame', 5, 14, 10, 11.00, 20.00, 1498.33),
(10, 'Plain Black Frame', 5, 14, 10, 10.00, 12.00, 1063.33);

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
(5, 7, '1773508555_f44a8ec5-2ca9-4c4b-b800-51cf97b8f90e.jpg', 1, '2026-03-15 01:15:55'),
(6, 7, '1773508555_74eabcaa-c37c-402b-b8de-520e4e1b17a5.jpg', 0, '2026-03-15 01:15:55'),
(8, 9, '1774037667_ee68e124-814f-481e-ad07-465659d36701.jpg', 1, '2026-03-21 04:14:27'),
(9, 10, '1774771962_80d26ce9-8db5-4ac7-af21-a62c0050251b.jpg', 1, '2026-03-29 16:12:42');

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
(5, 7, 2, '2026-03-15 01:15:55'),
(7, 9, 1, '2026-03-21 04:14:27'),
(8, 10, 1, '2026-03-29 16:12:42');

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
-- Dumping data for table `tbl_reviews`
--

INSERT INTO `tbl_reviews` (`review_id`, `customer_id`, `rating`, `review_text`, `review_date_posted`) VALUES
(1, 1, 5, 'Amazing quality frames! Very happy with my order.', '2026-03-15 14:14:31'),
(2, 25, 2, 'yoww love this', '2026-03-28 23:25:34'),
(3, 1, 5, 'I love the quality of frames!', '2026-03-29 18:43:06'),
(4, 1, 2, 'Too expensive!', '2026-03-30 11:41:28'),
(5, 25, 5, 'thanks i already picked up my order, and i love the quality of the frames', '2026-04-11 23:50:32'),
(6, 25, 2, 'not that bad not good either', '2026-04-12 01:41:20'),
(7, 25, 1, '<script>alert(\"HACKED\")</script>', '2026-04-19 13:58:16'),
(8, 25, 3, 'okeoke', '2026-05-03 17:20:03');

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  MODIFY `c_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tbl_fixed_print_prices`
--
ALTER TABLE `tbl_fixed_print_prices`
  MODIFY `fixed_price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_frame_colors`
--
ALTER TABLE `tbl_frame_colors`
  MODIFY `frame_color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_frame_designs`
--
ALTER TABLE `tbl_frame_designs`
  MODIFY `frame_design_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_frame_design_images`
--
ALTER TABLE `tbl_frame_design_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `tbl_frame_sizes`
--
ALTER TABLE `tbl_frame_sizes`
  MODIFY `frame_size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_frame_types`
--
ALTER TABLE `tbl_frame_types`
  MODIFY `frame_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_matboard_colors`
--
ALTER TABLE `tbl_matboard_colors`
  MODIFY `matboard_color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_mount_type`
--
ALTER TABLE `tbl_mount_type`
  MODIFY `mount_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tbl_otp`
--
ALTER TABLE `tbl_otp`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `tbl_paper_type`
--
ALTER TABLE `tbl_paper_type`
  MODIFY `paper_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tbl_payment_proof_uploads`
--
ALTER TABLE `tbl_payment_proof_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  MODIFY `printing_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product`
--
ALTER TABLE `tbl_ready_made_product`
  MODIFY `r_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product_images`
--
ALTER TABLE `tbl_ready_made_product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_ready_made_product_stocks`
--
ALTER TABLE `tbl_ready_made_product_stocks`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
