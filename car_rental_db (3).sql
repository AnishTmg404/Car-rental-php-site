-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2026 at 02:29 AM
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
-- Database: `car_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `pickup_location` varchar(100) NOT NULL,
  `return_location` varchar(100) NOT NULL,
  `total_days` int(11) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','active','completed','cancelled') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `user_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cancel_reason` text DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `car_id`, `pickup_date`, `return_date`, `pickup_location`, `return_location`, `total_days`, `daily_rate`, `total_amount`, `status`, `admin_notes`, `user_notes`, `created_at`, `updated_at`, `cancel_reason`, `canceled_at`) VALUES
(1, 3, 2, '2025-11-22', '2025-12-21', 'airport', 'downtown', 29, 299.00, 8671.00, 'rejected', NULL, 'asdjkalsjd', '2025-11-22 11:37:45', '2026-03-13 03:34:34', NULL, NULL),
(2, 3, 1, '2025-11-27', '2025-12-10', 'airport', 'hotel', 13, 89.00, 1157.00, 'rejected', NULL, 'help', '2025-11-22 12:17:30', '2025-12-13 19:30:31', NULL, NULL),
(3, 3, 6, '2025-12-18', '2025-12-22', 'airport', 'hotel', 4, 159.00, 636.00, 'cancelled', NULL, 'trip to weed', '2025-12-10 02:35:46', '2025-12-13 12:47:12', NULL, NULL),
(4, 7, 1, '2025-12-12', '2025-12-14', 'hotel', 'shopping_mall', 2, 89.00, 178.00, 'approved', NULL, 'Need for kidnapping.', '2025-12-12 03:22:01', '2025-12-13 13:57:52', NULL, NULL),
(5, 7, 8, '2025-12-18', '2025-12-25', 'downtown', 'downtown', 7, 10000000.00, 70000000.00, 'cancelled', NULL, 'help me', '2025-12-12 03:47:16', '2025-12-13 12:41:48', NULL, NULL),
(6, 7, 18, '2025-12-14', '2025-12-16', 'downtown', 'airport', 2, 50.00, 100.00, 'cancelled', NULL, 'Test rental', '2025-12-14 05:49:16', '2026-03-15 18:42:30', NULL, NULL),
(7, 7, 37, '2026-03-14', '2026-03-16', 'downtown', 'airport', 2, 79.00, 158.00, 'approved', NULL, '', '2026-03-13 03:36:52', '2026-03-13 03:49:03', NULL, NULL),
(9, 7, 37, '2026-03-20', '2026-03-23', 'downtown', 'shopping_mall', 3, 79.00, 237.00, 'cancelled', NULL, '', '2026-03-13 04:17:29', '2026-03-13 04:25:55', NULL, NULL),
(10, 7, 3, '2026-03-15', '2026-03-16', 'downtown', 'airport', 1, 149.00, 149.00, 'cancelled', NULL, 'Test', '2026-03-15 18:28:27', '2026-03-15 18:38:57', NULL, NULL),
(11, 10, 37, '2026-03-17', '2026-03-18', 'shopping_mall', 'shopping_mall', 1, 79.00, 79.00, 'cancelled', NULL, 'Test cancel', '2026-03-15 18:52:59', '2026-03-15 18:54:29', NULL, NULL),
(12, 10, 2, '2026-03-15', '2026-03-16', 'downtown', 'airport', 1, 299.00, 299.00, 'active', NULL, 'Test', '2026-03-15 18:56:02', '2026-03-15 19:37:45', NULL, NULL),
(13, 11, 37, '2026-03-17', '2026-03-18', 'downtown', 'shopping_mall', 1, 79.00, 79.00, 'pending', NULL, 'Pickup at 5am', '2026-03-16 09:00:01', '2026-03-16 09:00:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_cancellations`
--

CREATE TABLE `booking_cancellations` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cancel_reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `decided_at` datetime DEFAULT NULL,
  `admin_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_cancellations`
--

INSERT INTO `booking_cancellations` (`id`, `booking_id`, `user_id`, `cancel_reason`, `status`, `requested_at`, `decided_at`, `admin_note`) VALUES
(1, 5, 7, 'Test cancel', 'approved', '2025-12-13 13:41:48', '2025-12-13 13:41:48', NULL),
(2, 3, 3, 'Test cancel1', 'approved', '2025-12-13 13:47:12', '2025-12-13 13:47:12', NULL),
(3, 9, 7, 'test cancellation', 'approved', '2026-03-13 05:25:55', '2026-03-13 05:25:55', NULL),
(4, 10, 7, 'Test cancellation of car', 'approved', '2026-03-15 19:38:57', '2026-03-15 19:38:57', NULL),
(5, 6, 7, 'test', 'approved', '2026-03-15 19:42:30', '2026-03-15 19:42:30', NULL),
(6, 11, 10, 'Test cancel pending request', 'approved', '2026-03-15 19:53:45', '2026-03-15 19:54:29', 'Approve cancel request'),
(7, 12, 10, 'Test', 'rejected', '2026-03-15 19:56:27', '2026-03-15 19:56:38', 'no'),
(8, 13, 11, 'Cancellation demo', 'pending', '2026-03-16 10:19:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `license_plate` varchar(20) NOT NULL,
  `chassis_number` varchar(17) DEFAULT NULL,
  `mileage` int(11) DEFAULT 0,
  `fuel_type` enum('petrol','diesel','hybrid','electric') NOT NULL,
  `transmission` enum('manual','automatic') NOT NULL,
  `seats` int(11) NOT NULL,
  `doors` int(11) NOT NULL,
  `category` enum('economy','compact','mid-size','full-size','luxury','suv','sports') NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `status` enum('available','rented','maintenance','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `brand`, `model`, `year`, `color`, `license_plate`, `chassis_number`, `mileage`, `fuel_type`, `transmission`, `seats`, `doors`, `category`, `daily_rate`, `description`, `features`, `images`, `status`, `created_at`, `updated_at`) VALUES
(1, 'BMW', '3 Series', 2023, 'White', 'BMW-001', 'WBA3A5C59EF123456', 0, 'petrol', 'automatic', 5, 4, 'luxury', 89.00, 'Luxury sedan with premium features', '[\"GPS,Bluetooth,Leather Seats,Sunroof\"]', '[\"https:\\/\\/images.unsplash.com\\/photo-1555215695-3004980ad54e?w=500\",\"assets\\/uploads\\/cars\\/car_693d78ba52899.jpg\",\"assets\\/uploads\\/cars\\/car_693d79e2f3cc8.jpg\"]', 'available', '2025-10-26 10:11:57', '2025-12-13 09:51:18'),
(2, 'Ferrari', '488', 2023, 'Red', 'FER-001', 'ZFF68AHA0F0123456', 0, 'petrol', 'automatic', 2, 2, 'sports', 299.00, 'High-performance sports car', '[\"GPS\", \"Sport Mode\", \"Premium Sound\", \"Carbon Fiber\"]', '[\"https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=500\"]', 'rented', '2025-10-26 10:11:57', '2026-03-15 19:37:45'),
(3, 'Range Rover', 'Evoque', 2023, 'Black', 'RR-001', 'SALVA2BG8EH123456', 0, 'diesel', 'automatic', 5, 5, 'suv', 149.00, 'Luxury SUV with off-road capabilities', '[\"4WD\", \"GPS\", \"Premium Interior\", \"Panoramic Roof\"]', '[\"https://images.unsplash.com/photo-1549317336-206569e8475c?w=500\"]', 'available', '2025-10-26 10:11:57', '2025-12-12 03:34:40'),
(4, 'Mercedes', 'C-Class', 2023, 'Silver', 'MB-001', 'WDD2050461A123456', 0, 'hybrid', 'automatic', 5, 4, 'luxury', 95.00, 'Elegant luxury sedan', '[\"GPS\", \"Adaptive Cruise\", \"Premium Audio\", \"LED Lights\"]', '[\"https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=500\"]', 'available', '2025-10-26 10:11:57', '2025-10-26 10:11:57'),
(5, 'Toyota', 'Corolla', 2023, 'Red', 'TOY-001', '1HGBH41JXMN123456', 0, 'petrol', 'manual', 5, 4, 'economy', 45.00, 'Reliable economy car', '[\"GPS\", \"Bluetooth\", \"Air Conditioning\", \"USB Ports\"]', '[\"https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=500\"]', 'available', '2025-10-26 10:11:57', '2025-10-26 10:11:57'),
(6, 'Audi', 'Q7', 2023, 'White', 'AUD-001', 'WAUZZZ4G5FN123456', 0, 'diesel', 'automatic', 7, 5, 'suv', 159.00, 'Premium 7-seater SUV', '[\"Quattro AWD\", \"Virtual Cockpit\", \"Premium Sound\", \"Third Row Seats\"]', '[\"https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=500\"]', 'available', '2025-10-26 10:11:57', '2025-10-26 10:11:57'),
(8, 'ajay moktan', '2004', 2004, 'Brown', '98111111000', '', 1000, 'hybrid', 'manual', 1, 4, 'mid-size', 10000000.00, 'Good Mileage and fast runner', '[\"Bluetooth,Sunroof,USB Ports,Heated Seats,Keyless Entry,Remote Start\"]', '[\"assets\\/uploads\\/cars\\/car_693dc080914f8.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0942a8ed.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0a77e2ec.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0b79434e.jpg\"]', 'unavailable', '2025-12-12 03:39:00', '2026-03-15 19:20:47'),
(17, 'Testcar', 'car', 2025, 'Royal Blue', 'TESTCAR123', '12testcar12', 2025, 'electric', 'manual', 2, 4, 'economy', 78.00, 'This is test car', '[\"GPS Navigation,Bluetooth,Air Conditioning,Leather Seats,Sunroof,Premium Sound,USB Ports,Cruise Control,Backup Camera,Heated Seats,Keyless Entry,Remote Start\"]', '[\"assets\\/uploads\\/cars\\/693d83ad6066f_0.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d17f686.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d17f7ca.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d17f87c.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d17f986.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d17faef.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d17ff38.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d18005e.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d18012a.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1801fc.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1802bc.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1803db.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1965d0.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1966dc.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d196832.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1969a0.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d196abf.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d196b80.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d196cdd.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d196e34.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d196fc2.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1971cc.jpg\",\"assets\\/uploads\\/cars\\/car_693dc0d1972f7.jpg\"]', 'available', '2025-12-13 10:33:05', '2025-12-13 14:53:57'),
(18, 'ICMS car', 'Vii', 2025, 'red', '2010', 'ICMS122', 20, 'hybrid', 'manual', 4, 4, 'mid-size', 50.00, 'ICMS car', '[\"GPS Navigation\",\"Bluetooth\",\"Leather Seats\",\"USB Ports\",\"Heated Seats\"]', '[\"assets\\/uploads\\/cars\\/693e4f5f155e4_0.jpg\"]', 'available', '2025-12-14 01:02:11', '2026-03-13 03:34:06'),
(37, 'toyota', 'S', 2022, 'Red', 'BA-1237', '', 2000, 'hybrid', 'automatic', 4, 4, 'mid-size', 79.00, 'Toyato car add', '[\"GPS Navigation\",\"Bluetooth\",\"Leather Seats\",\"USB Ports\"]', '[\"assets\\/uploads\\/cars\\/car_69b3851d6e2a5.webp\"]', 'available', '2026-03-12 22:46:41', '2026-03-13 03:31:41'),
(39, 'Toyota', 'Corolla', 2023, 'White', 'BAG 3 CHA 4512', '', 2000, 'hybrid', 'automatic', 5, 4, 'economy', 4500.00, '', '[\"Bluetooth\",\"USB Ports\",\"Cruise Control\",\"Backup Camera\"]', '[\"assets\\/uploads\\/cars\\/car_69b7cd3fa4209.webp\"]', 'available', '2026-03-16 04:43:31', '2026-03-16 09:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','cash','bank_transfer') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `license_number` varchar(50) DEFAULT NULL,
  `license_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `profile_image`, `phone`, `address`, `role`, `status`, `created_at`, `updated_at`, `license_number`, `license_image`) VALUES
(1, 'admin', 'admin@carrental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', NULL, NULL, NULL, 'admin', 'active', '2025-10-26 10:11:57', '2025-10-26 10:11:57', NULL, NULL),
(3, 'User123', 'user@carrental.com', '$2y$10$i5Gs9sPyG2itByYVY65V0uFB5K1yH41Q9Z9mSZ36mpUXPgDXgSd92', 'User', 'User', NULL, '1234567890', NULL, 'user', 'active', '2025-11-22 11:34:11', '2025-11-22 11:34:11', NULL, NULL),
(4, 'Ajay', 'ajayamoktan2@gmail.com', '$2y$10$.0qB6kLFkKYKbLU01j5AmeDWD9ZE/DMCF1fOktvSpovGY4S8csPsu', 'Ajay', 'Moktan', NULL, '123456789', 'budhalinkantha', 'user', 'inactive', '2025-12-10 01:14:45', '2026-03-15 16:07:13', NULL, NULL),
(6, 'Ashishpakhrin', 'ashishpakhrin@gmail.com', '$2y$10$PKICTqRe3dUT9JtQhxZrgO.pfJ1fSM56rq/Z8zZD22ewyv.phtOwW', 'Ashish', 'Pakhrin', NULL, '123123123', 'jorpati', 'user', 'active', '2025-12-11 15:07:09', '2025-12-11 19:52:09', NULL, NULL),
(7, 'ajaymoktan', 'ajaymoktan@gmail.com', '$2y$10$7jjT6EloSFxxVdDPHWr0tuR3f8863HTcCv.jTC2jOIJjrVYa7tiwK', 'Ajay', 'Moktan', 'assets/uploads/profiles/user_7_1765628079.jpg', '123123123', 'Budhalinkhanta, kathmandu', 'user', 'active', '2025-12-12 02:55:00', '2026-03-16 04:30:44', '', ''),
(8, 'anishtamang', 'anishtmg@carrental.com', '$2y$10$2NXAiuuVfhuHkwPmjzLdzu6IwQi/i3Zur.w30tdplhODVwrAjQPZC', 'Anish', 'Tamang', NULL, '9800000000', 'Baluwatar-4, Kathmandu', 'admin', 'inactive', '2025-12-13 09:20:40', '2026-03-15 16:06:49', NULL, NULL),
(10, 'ninjahattori', 'ninjahattori@gmail.com', '$2y$10$7YZBxzMlkz9ds7zclGbdrOJFs.mZXEnjnKhWCP3UsbgjeEP.TIybW', 'Ninja', 'Hattori', NULL, '1231231233', NULL, 'user', 'active', '2026-03-15 18:52:05', '2026-03-15 18:52:05', NULL, NULL),
(11, 'anishtamang12', 'anishtamang@gmail.com', '$2y$10$yOP8p/eqBo7TgWte/8tU1uSMJe/0Ssfj/U2knCghhvMSY2TMQ4qfu', 'Anish', 'Tamang', NULL, '1234567890', NULL, 'user', 'active', '2026-03-16 08:51:43', '2026-03-16 08:51:43', NULL, NULL),
(12, 'Sudhrith', 'sudhrithdev@gmail.com', '$2y$10$2Z8Yl1r6Ww6HFx/hXSSq7OwwswE6/NI4hogYc48kUkt.PMyptZBQW', 'Sudhrith dev', 'Dangi', NULL, '1234567890', 'Pespsi cola', 'user', 'active', '2026-03-16 04:37:47', '2026-03-16 09:22:47', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_user_id` (`user_id`),
  ADD KEY `idx_bookings_car_id` (`car_id`),
  ADD KEY `idx_bookings_status` (`status`),
  ADD KEY `idx_bookings_dates` (`pickup_date`,`return_date`);

--
-- Indexes for table `booking_cancellations`
--
ALTER TABLE `booking_cancellations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `idx_cars_status` (`status`),
  ADD KEY `idx_cars_category` (`category`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_review` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `booking_cancellations`
--
ALTER TABLE `booking_cancellations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_cancellations`
--
ALTER TABLE `booking_cancellations`
  ADD CONSTRAINT `booking_cancellations_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `booking_cancellations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
