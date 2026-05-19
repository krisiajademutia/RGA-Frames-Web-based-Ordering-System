-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 04:06 PM
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
(1, 'Admin', 'User', 'admin', 'mutiakrisiaj@gmail.com', '$2y$10$kd9FoZ0japdSk3mzS96QmeYSUH1Pbqm/0SdIRHO57r9NoMUuMQZia', '2026-02-18 12:57:20', '2026-05-19 22:03:58');

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
  `customer_type` enum('REGULAR','PHOTOGRAPHER') NOT NULL DEFAULT 'REGULAR',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_customer`
--

INSERT INTO `tbl_customer` (`customer_id`, `first_name`, `last_name`, `username`, `email`, `password`, `phone_number`, `customer_type`, `created_at`) VALUES
(25, 'Krisia', 'Mutia', 'urr_mutyaaa', 'savvcalise@gmail.com', '$2y$10$1B/nhyZC2OhoGlBVNb5QyegOKCUyiCp1IgWpCjeouTaAzWK0eRiyy', '09812348984', 'REGULAR', '2026-03-27 20:16:27');

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
(4, 'Light Brown', 1, 'color_1773487091_69b543f32b6f6.jpg'),
(5, 'Yellow', 1, 'color_1778167811_69fcb003a43fa.jpg'),
(6, 'Red', 1, 'color_1773487493_69b54585483b7.jpg'),
(7, 'Blue', 1, 'color_1773487530_69b545aa72b21.jpg'),
(8, 'Dark Brown', 1, 'color_1773487556_69b545c44a755.jpg'),
(9, 'Green', 1, 'color_1773487634_69b5461282351.jpg'),
(10, 'Black', 1, 'color_1778664262_6a0443466d7d9.jpg');

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
(7, 12, 'design_1773489702_69b54e267a336.jpg', 1, '2026-03-14 20:01:42'),
(8, 13, 'design_1773489763_69b54e6321355.jpg', 1, '2026-03-14 20:02:43'),
(9, 14, 'design_1773489835_69b54eab5eee4.jpg', 1, '2026-03-14 20:03:55'),
(10, 15, 'design_1773489877_69b54ed598e58.jpg', 1, '2026-03-14 20:04:37'),
(11, 16, 'design_1773495695_69b5658f7c3ba.jpg', 1, '2026-03-14 21:41:35'),
(12, 16, 'design_1773495695_69b5658f7d614.jpg', 0, '2026-03-14 21:41:35'),
(13, 17, 'design_1773566299_69b6795b0bc17.jpg', 1, '2026-03-15 17:18:19'),
(15, 11, 'design_1778664301_6a04436d03e5a.jpg', 1, '2026-05-13 17:25:01');

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
(5, 'Polystyrene', 0.00, 'type_1778664337_6a04439155a36.jpg', 1),
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
(15, 'Blue', 29.99, 'matboard_1778661124_6a0437046ea67.jpg', 1);

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
(9, 'Photo Paper', 1.50, 12.00, 18.00, 50.00, 96.00, 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `tbl_printing_order_items`
--

CREATE TABLE `tbl_printing_order_items` (
  `printing_order_item_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `paper_type_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `drive_link` text DEFAULT NULL,
  `width_inch` decimal(5,2) NOT NULL,
  `height_inch` decimal(5,2) NOT NULL,
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
  `product_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tbl_custom_frame_product`
--
ALTER TABLE `tbl_custom_frame_product`
  MODIFY `c_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

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
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_frame_order_items`
--
ALTER TABLE `tbl_frame_order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `tbl_frame_sizes`
--
ALTER TABLE `tbl_frame_sizes`
  MODIFY `frame_size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_frame_types`
--
ALTER TABLE `tbl_frame_types`
  MODIFY `frame_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `tbl_otp`
--
ALTER TABLE `tbl_otp`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `tbl_paper_type`
--
ALTER TABLE `tbl_paper_type`
  MODIFY `paper_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `tbl_payment_proof_uploads`
--
ALTER TABLE `tbl_payment_proof_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tbl_printing_order_items`
--
ALTER TABLE `tbl_printing_order_items`
  MODIFY `printing_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

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
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
