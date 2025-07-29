-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2025 at 07:04 PM
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
-- Database: `aquabill`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`id`, `username`, `password`, `email`, `full_name`, `profile_photo`) VALUES
(3, 'admin', '$2y$10$UN.Z7r4wASvWdIqChWiSAuaNc1xoouGKvC/OsEogW3RKxnn9W4alm', 'admin@gmail.com', 'Rojer Completo', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `posted_by` varchar(100) NOT NULL,
  `date_posted` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `posted_by`, `date_posted`) VALUES
(54, 'Water Billing Meeting', 'magsipunta kayo dito sa bahay at  10am', 'admin', '2025-07-16'),
(55, 'asas', 'as', 'admin', '2025-07-16'),
(56, 'Roger To', 'may meeting tayo sa patubig', 'admin', '2025-07-18'),
(57, 'asd', 'asd', 'admin', '2025-07-18');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `coverage_from` date NOT NULL,
  `coverage_to` date NOT NULL,
  `reading_date` date NOT NULL,
  `due_date` date NOT NULL,
  `consumption` double NOT NULL,
  `price_per_cubic` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `resident_id`, `coverage_from`, `coverage_to`, `reading_date`, `due_date`, `consumption`, `price_per_cubic`, `total`, `created_at`) VALUES
(1, 36, '2025-06-30', '2025-07-30', '2025-07-30', '2025-07-28', 15, 15, 225, '2025-07-16 16:22:44'),
(2, 36, '2025-06-30', '2025-07-29', '2025-07-29', '2025-07-30', 15, 15, 225, '2025-07-16 16:49:54'),
(3, 44, '2025-07-31', '2025-07-13', '2025-07-13', '2025-07-29', 15, 15, 225, '2025-07-17 11:06:30'),
(4, 44, '2025-06-30', '2025-07-30', '2025-07-30', '2025-08-12', 200, 15, 3000, '2025-07-18 16:12:17'),
(5, 41, '2025-07-01', '2025-07-23', '2025-07-23', '2025-07-31', 15, 15, 225, '2025-07-22 04:35:23'),
(6, 45, '2025-07-20', '2025-07-23', '2025-07-23', '2025-07-31', 15, 15, 225, '2025-07-23 15:59:12'),
(7, 45, '2025-07-21', '2025-07-31', '2025-07-31', '2025-07-31', 15, 15, 225, '2025-07-23 15:59:59'),
(8, 43, '2025-07-05', '2025-07-30', '2025-07-30', '2025-07-27', 15, 15, 225, '2025-07-23 16:15:28');

-- --------------------------------------------------------

--
-- Table structure for table `collectors`
--

CREATE TABLE `collectors` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `age` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collectors`
--

INSERT INTO `collectors` (`id`, `fullname`, `email`, `contact_number`, `age`, `status`, `password`, `profile_photo`) VALUES
(1, 'Renz Rusca', 'renz@example.com', '0928812343', 23, 'active', '$2y$10$wEyYv8QrG1uK2XvoEdXFyulIE2tjeR2p2jUTWyIg/wZm2Qrl9vCQi', '');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_type` enum('resident','system') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_type` enum('collector','resident') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `sender_type`, `sender_id`, `receiver_type`, `receiver_id`, `message`, `image_url`, `is_read`, `created_at`) VALUES
(16, 'resident', 29, 'collector', 1, 'New payment proof uploaded.', 'http://192.168.250.130/aquabill-api/uploads/proof_687705bef024a.jpg', 0, '2025-07-16 01:51:58'),
(17, 'resident', 29, 'collector', 1, 'New payment proof uploaded.', 'http://192.168.250.130/aquabill-api/uploads/proof_687705f1ee5ba.jpg', 0, '2025-07-16 01:52:49'),
(18, 'system', NULL, 'resident', 34, 'Collector has received your payment.', NULL, 0, '2025-07-16 04:32:51'),
(19, 'resident', 29, 'collector', 1, 'New payment proof uploaded.', 'http://192.168.0.160/aquabill-api/uploads/proof_687760f78f62f.jpg', 0, '2025-07-16 08:21:11'),
(20, 'resident', 29, 'collector', 1, 'New payment proof uploaded.', 'http://192.168.0.160/aquabill-api/uploads/proof_68776117a5fe0.jpg', 0, '2025-07-16 08:21:43'),
(21, 'system', NULL, 'resident', 36, 'Collector has received your payment.', NULL, 0, '2025-07-16 08:22:35'),
(22, 'resident', 29, 'collector', 1, 'New payment proof uploaded.', 'http://192.168.1.16/aquabill-api/uploads/proof_6878d9d75ffa0.jpg', 0, '2025-07-17 11:09:11'),
(23, 'system', NULL, 'resident', 44, 'Collector has received your payment.', NULL, 0, '2025-07-18 16:13:09'),
(24, 'resident', 29, 'collector', 1, 'New payment proof uploaded.', 'http://192.168.1.16/aquabill-api/uploads/proof_687a73c4d4ab1.jpg', 0, '2025-07-18 16:18:12'),
(25, 'system', NULL, 'resident', 45, 'Your payment of ₱225.00 on 2025-07-31 has been received.', NULL, 0, '2025-07-23 16:00:09'),
(26, 'system', NULL, 'resident', 43, 'Your payment of ₱225.00 on 2025-07-27 has been received.', NULL, 0, '2025-07-23 16:15:32'),
(27, 'system', NULL, 'resident', 45, 'Your payment of ₱225.00 on 2025-07-31 has been received.', NULL, 0, '2025-07-23 16:17:26'),
(28, 'system', NULL, 'resident', 43, 'Your payment of ₱225.00 on 2025-07-27 has been received.', NULL, 0, '2025-07-23 16:24:40'),
(29, 'system', NULL, 'resident', 45, 'Your payment of ₱225.00 on 2025-07-31 has been received.', NULL, 0, '2025-07-23 16:24:51'),
(30, 'system', NULL, 'resident', 45, 'Your payment of ₱225.00 on 2025-07-31 has been received.', NULL, 0, '2025-07-23 16:28:46'),
(31, 'system', NULL, 'resident', 44, 'Your payment of ₱3,000.00 on 2025-08-12 has been received.', NULL, 0, '2025-07-23 16:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_paid` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `resident_id`, `amount`, `date_paid`) VALUES
(8, 1, 200.00, '2025-06-01'),
(9, 2, 250.00, '2025-06-05'),
(10, 3, 180.00, '2025-07-01');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(20) NOT NULL,
  `paid_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `resident_id`, `bill_id`, `amount`, `payment_mode`, `paid_at`) VALUES
(4, 43, 8, 225.00, 'Cash', '2025-07-23 18:24:40'),
(5, 45, 6, 225.00, 'G-Cash', '2025-07-23 18:24:50'),
(6, 45, 6, 225.00, 'G-Cash', '2025-07-23 18:28:46'),
(7, 44, 4, 3000.00, 'G-Cash', '2025-07-23 18:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `proof_uploads`
--

CREATE TABLE `proof_uploads` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proof_uploads`
--

INSERT INTO `proof_uploads` (`id`, `resident_id`, `image_url`, `uploaded_at`) VALUES
(0, 29, 'http://192.168.0.160/aquabill-api/uploads/proof_687760f78f62f.jpg', '2025-07-16 08:21:11'),
(0, 29, 'http://192.168.0.160/aquabill-api/uploads/proof_68776117a5fe0.jpg', '2025-07-16 08:21:43'),
(0, 29, 'http://192.168.1.16/aquabill-api/uploads/proof_6878d9d75ffa0.jpg', '2025-07-17 11:09:11'),
(0, 29, 'http://192.168.1.16/aquabill-api/uploads/proof_687a73c4d4ab1.jpg', '2025-07-18 16:18:12');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `meter_no` varchar(50) NOT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL DEFAULT '',
  `qr_path` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `name`, `email`, `contact`, `gender`, `age`, `meter_no`, `payment_mode`, `is_verified`, `password`, `qr_path`, `profile_pic`, `is_archived`) VALUES
(36, 'El Jean G. Villalobos', 'villaloboseljean820@gmail.com', '09268821120', 'Female', 20, '111-222', NULL, 0, '$2y$10$aYqKvh.fcc8AGCZdEV.5L.JSjF5Y1l2IhLJOBlSnSbIxghMlvr2aK', NULL, NULL, 0),
(41, 'Renz Rusca', 'renz.rusca132@gmail.com', '09268831140', 'Male', 12, '23', NULL, 0, '$2y$10$utHAm6rPvfsYkBq6sJVZgOAewqjRzEMADRzhvRWax6MTEGenvQFVG', NULL, NULL, 0),
(43, 'Ryan Reynlods', 'renzru@gmail.com', '09268821120', 'Male', 23, '111-223', 'Cash', 1, '$2y$10$KuLoe13B0/9JMo664V4oIO0NyXe/L1nGTjEb5TFS06wwHEpkrVw0e', NULL, NULL, 0),
(44, 'Juan Dela Cruz', 'hihi@gmail.com', '09171234567', 'Male', 30, 'MTR-0001', 'G-Cash', 1, '$2y$10$nETjLN1APRoq8NYx9yC4OuVBYysnMPViYzEbGMWat3PI5r/TJZ1LC', NULL, NULL, 0),
(45, 'Renz Rus', 'renz.rusca13@gmail.com', '09268831140', 'Male', 23, '112-323', 'G-Cash', 1, '$2y$10$.D3UtJQJW5lBj3XpGQsA3uXSVN8riHyD0U80ctQrf8WIhRxD4Vqz6', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collectors`
--
ALTER TABLE `collectors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `collectors`
--
ALTER TABLE `collectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`),
  ADD CONSTRAINT `payment_history_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
