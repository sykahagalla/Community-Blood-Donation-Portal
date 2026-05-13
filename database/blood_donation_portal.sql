-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 02:26 AM
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
-- Database: `blood_donation_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `admin_id`, `action`, `target_user_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 4, 'User Approved', 8, 'User account activated', NULL, '2026-04-01 07:08:08'),
(2, 4, 'User Approved', 7, 'User account activated', NULL, '2026-04-01 07:08:15'),
(3, 4, 'User Approved', 9, 'User account activated', NULL, '2026-04-03 10:49:18'),
(4, 4, 'User Approved', 10, 'User account activated', NULL, '2026-04-03 11:20:34'),
(5, 4, 'User Approved', 11, 'User account activated', NULL, '2026-04-03 11:20:39'),
(6, 4, 'User Approved', 12, 'User account activated', NULL, '2026-04-03 11:20:42'),
(7, 4, 'User Approved', 13, 'User account activated', NULL, '2026-04-03 11:28:22'),
(8, 4, 'User Approved', 17, 'User account activated', NULL, '2026-04-29 20:44:49'),
(9, 4, 'User Approved', 16, 'User account activated', NULL, '2026-04-29 20:45:08'),
(10, 4, 'User Approved', 15, 'User account activated', NULL, '2026-04-29 20:45:16'),
(11, 4, 'User Approved', 14, 'User account activated', NULL, '2026-04-29 20:45:20'),
(12, 4, 'User Approved', 18, 'User account activated', NULL, '2026-04-29 22:11:46'),
(13, 4, 'User Approved', 20, 'User account activated', NULL, '2026-04-29 22:20:58'),
(14, 4, 'User Approved', 22, 'User account activated', NULL, '2026-04-29 23:59:39'),
(15, 4, 'User Approved', 23, 'User account activated', NULL, '2026-04-30 00:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `blood_drives`
--

CREATE TABLE `blood_drives` (
  `drive_id` int(11) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `drive_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `organizer_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `request_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_needed` int(11) NOT NULL,
  `urgency_level` enum('critical','urgent','normal') NOT NULL,
  `patient_name` varchar(255) DEFAULT NULL,
  `reason` text NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `status` enum('active','fulfilled','cancelled') DEFAULT 'active',
  `needed_by` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fulfilled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`request_id`, `hospital_id`, `blood_type`, `units_needed`, `urgency_level`, `patient_name`, `reason`, `contact_number`, `status`, `needed_by`, `created_at`, `updated_at`, `fulfilled_at`) VALUES
(1, 2, 'O+', 3, 'urgent', 'Sample Patient', 'Emergency surgery required', '+94 11 269 1111', 'active', '2026-04-02 10:19:46', '2026-03-31 04:49:46', '2026-03-31 04:49:46', NULL),
(2, 2, 'A-', 45, 'critical', 'hghfgfj', 'gsdgssh', '+94 11 269 1111', 'active', '2026-04-15 16:22:00', '2026-04-03 10:52:34', '2026-04-03 10:52:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `units_donated` decimal(4,2) NOT NULL,
  `hemoglobin_level` decimal(4,2) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `donation_type` enum('whole_blood','platelets','plasma') DEFAULT 'whole_blood',
  `status` enum('scheduled','completed','cancelled') DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`donation_id`, `donor_id`, `hospital_id`, `request_id`, `donation_date`, `units_donated`, `hemoglobin_level`, `blood_pressure`, `donation_type`, `status`, `notes`, `created_at`) VALUES
(1, 2, 2, NULL, '2025-12-15', 1.00, NULL, NULL, 'whole_blood', 'completed', NULL, '2026-03-31 04:49:46');

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `donor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `last_donation_date` date DEFAULT NULL,
  `total_donations` int(11) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `medical_conditions` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`donor_id`, `user_id`, `full_name`, `blood_type`, `date_of_birth`, `gender`, `phone`, `address`, `city`, `latitude`, `longitude`, `last_donation_date`, `total_donations`, `is_available`, `medical_conditions`, `profile_image`, `created_at`, `updated_at`) VALUES
(2, 5, 'John Doe (Demo Donor)', 'O+', '1990-01-15', 'male', '+94 77 123 4567', '123 Main Street, Colombo', 'Colombo', NULL, NULL, '2025-12-15', 5, 1, NULL, NULL, '2026-03-31 04:49:46', '2026-03-31 04:49:46'),
(3, 7, 'don silva', 'AB+', '1999-10-19', 'male', '0712654372', 'Nugegoda', 'colombo', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-01 07:04:23', '2026-04-01 07:04:23'),
(4, 9, 'dddd', 'B+', '2004-10-26', 'male', '46653745', 'dhdh', 'ttrfgfg', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-03 10:44:00', '2026-04-03 10:44:00'),
(5, 11, 'dmaa', 'A-', '2001-10-31', 'female', '2543665', 'colombo Rd,', 'Kurunagala', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-03 11:07:20', '2026-04-03 11:07:20'),
(6, 12, 'ggdgdfh', 'O-', '2005-09-26', 'female', '454543534', 'fdfdsfhds', 'vbvcbcvb', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-03 11:13:19', '2026-04-03 11:13:19'),
(7, 14, 'rrey', 'B-', '2005-07-05', 'female', '645747', 'ghghfg', 'fgjjfjhj', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-03 11:30:53', '2026-04-03 11:30:53'),
(8, 15, 'fhdfh', 'AB-', '2004-06-22', 'male', '545745754', 'fdfhdfhd', 'vgjsgjsgj', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-03 12:09:03', '2026-04-03 12:09:03'),
(9, 16, 'yy', 'A+', '2008-04-17', 'male', '533636636', 'gtytytttrtreurrtruaf', 'rytryrtut', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-29 19:05:10', '2026-04-29 19:05:10'),
(10, 18, 'ccc', 'AB-', '2008-04-02', 'male', '5655447', 'hfgjfj', 'gfjfgj', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-29 22:08:23', '2026-04-29 22:08:23'),
(11, 19, 'iii', 'AB+', '2008-04-11', 'male', '6656777', 'hghfgfgjfg', 'colombo', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-29 22:14:41', '2026-04-29 22:14:41'),
(12, 20, 'llll', 'A-', '2008-04-02', 'male', '5645747', 'gfdgdfh', 'Homagama', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-29 22:19:17', '2026-04-29 22:19:17'),
(13, 21, 'ttt', 'B-', '2008-04-09', 'female', '463463', 'sdgdfgh', 'colombo', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-29 23:52:56', '2026-04-29 23:52:56'),
(14, 23, 'fpy', 'AB+', '2008-04-01', 'male', '454654', 'sdgdfshdf', 'colombo', NULL, NULL, NULL, 0, 1, NULL, NULL, '2026-04-30 00:19:50', '2026-04-30 00:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `hospital_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hospital_name` varchar(255) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`hospital_id`, `user_id`, `hospital_name`, `registration_number`, `phone`, `address`, `city`, `latitude`, `longitude`, `contact_person`, `contact_email`, `created_at`, `updated_at`) VALUES
(2, 6, 'General Hospital Colombo (Demo)', 'REG-2024-001', '+94 11 269 1111', '456 Hospital Road, Colombo 08', 'Colombo', NULL, NULL, 'Dr. Perera', 'contact@generalhospital.lk', '2026-03-31 04:49:46', '2026-03-31 04:49:46'),
(3, 8, 'Homagama', '1423555', '5434636', 'Pitipana', 'Homagama', NULL, NULL, 'silva', 'silva@gmail.com', '2026-04-01 07:06:22', '2026-04-01 07:06:22'),
(4, 10, 'Anuradhapura', '646347', '7547474', 'Kandy Rd,', 'Anuradhapura', NULL, NULL, 'hdhdh', 'gh@gmail.com', '2026-04-03 10:54:27', '2026-04-03 10:54:27'),
(5, 13, 'Dambulla', '234567', '565457457', 'hgjfgjfgj', 'jgjfgjfgj', NULL, NULL, 'fdhh', 'd@gmail.com', '2026-04-03 11:22:35', '2026-04-03 11:22:35'),
(6, 17, 'rr', '453454', '554', 'fsdgdsfh', 'sfdghf', NULL, NULL, 'dfhd', 'd@gmail.com', '2026-04-29 20:37:18', '2026-04-29 20:37:18'),
(7, 22, 'hsh', '4534', '463246', 'fgdfhfdh', 'sdhsh', NULL, NULL, 'fhdhh', 'rrre@gmail.com', '2026-04-29 23:55:36', '2026-04-29 23:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('blood_request','donation_confirmation','system','alert') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('donor','hospital','admin') NOT NULL,
  `status` enum('pending','active','suspended') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `user_type`, `status`, `created_at`, `last_login`, `reset_token`, `reset_token_expiry`) VALUES
(4, 'admin@bloodportal.com', '$2y$10$eQWOh9CqVSGoxXYn/egMg.KvhOIqqiDOIVgpI6sydA0uCDXxl2wcW', 'admin', 'active', '2026-03-31 04:49:46', '2026-04-30 00:20:17', NULL, NULL),
(5, 'donors@bloodportal.com', '$2y$10$92hojhggPUhPOIvAhMA7WuKJ1quGcSHaLFxQKFYSjdycgSIV5cexC', 'donor', 'active', '2026-03-31 04:49:46', '2026-04-30 00:22:15', NULL, NULL),
(6, 'hospitals@bloodportal.com', '$2y$10$DdnCzdH5AXTpwcRGkQ2KD.VtmvLqiNnyTphdoASdHlksFeBmdRm1y', 'hospital', 'active', '2026-03-31 04:49:46', '2026-04-30 00:22:48', NULL, NULL),
(7, 'don@gmail.com', '$2y$10$qydBOH9tTlm6L9VXI8BWduVmKlZP4l2FpLxYIs6HhLlctDykNhVXW', 'donor', 'active', '2026-04-01 07:04:23', '2026-04-01 07:09:41', NULL, NULL),
(8, 'hos@gmail.com', '$2y$10$lXk.2lx8V/Zv.0iEgJGZjeqlJ8GP1Yx2tZcVMjwcv4ETcUvF98zIK', 'hospital', 'active', '2026-04-01 07:06:22', '2026-04-01 07:11:36', NULL, NULL),
(9, 'd@gmail.com', '$2y$10$xAkiTgtUuJ34SRy.wV7l8eztjiX43nRp8ItPkhpt14PLISpNup9je', 'donor', 'active', '2026-04-03 10:44:00', '2026-04-03 10:50:03', NULL, NULL),
(10, 'h@gmail.com', '$2y$10$7/fXKsTG6TZKX1GRWvLNiuyxzd98bm/Qjgi5pc5yj6Azb46WW0Pw6', 'hospital', 'active', '2026-04-03 10:54:27', NULL, NULL, NULL),
(11, 'dm@gmail.com', '$2y$10$kIHhjbs2tW94SWwlkjF/pu4bs5ZFniUULUviKyeAdJyRwMMT5eRmC', 'donor', 'active', '2026-04-03 11:07:20', NULL, NULL, NULL),
(12, 'Adns@gmail.com', '$2y$10$FceB8UHklRXPTygAvertre2T5Ohwz3T/lUXuHHF2ew4oz7Tx7oxZe', 'donor', 'active', '2026-04-03 11:13:19', NULL, NULL, NULL),
(13, 'hg@gmail.com', '$2y$10$VagLE6fX0rsIRQm/corjM.5ro5vgj3I6GAzOdphLqcP83hXXn.fna', 'hospital', 'active', '2026-04-03 11:22:35', NULL, NULL, NULL),
(14, 'b@gmail.com', '$2y$10$cjsoK0B/ws0z7eIGIa2omOump1u/584jHp.Um5Pqzxxub0EikNHWO', 'donor', 'active', '2026-04-03 11:30:53', NULL, NULL, NULL),
(15, 'dn@gmail.com', '$2y$10$qDKp9lLqKMjWFa5luPMCzOG9ppooRJnP4a445YnD8eN/hW7rp6ng6', 'donor', 'active', '2026-04-03 12:09:03', NULL, NULL, NULL),
(16, 'yy@gmail.com', '$2y$10$oI9rRJlc6jsa01cvml.gCe2etWgRi/m6gb7N6FVDkia8KG7bkqzPO', 'donor', 'active', '2026-04-29 19:05:10', NULL, NULL, NULL),
(17, 'ppr@gmail.com', '$2y$10$arfVs9oGh6UqEIjSEH1GtObz145.EiDQ/0UrWOSDn98HjWgjYqCOm', 'hospital', 'active', '2026-04-29 20:37:18', '2026-04-29 20:45:52', NULL, NULL),
(18, 'ccc@gmail.com', '$2y$10$PCsai9fGeJiaY.UBEFjW7OOBxGQ6b33wmW36S95Hx7DXjW7/NWx6.', 'donor', 'active', '2026-04-29 22:08:23', '2026-04-29 23:52:08', NULL, NULL),
(19, 'iii@gmail.com', '$2y$10$mK6aNiBJm6/yyimxRE7vle7RQquHETSCJKMSJCnRefTUGd5.yyB2W', 'donor', 'pending', '2026-04-29 22:14:41', NULL, NULL, NULL),
(20, 'lahiruprasangika2018@gmail.com', '$2y$10$bE5w0GmIXahaeyL/MdxNY.wX.TUDzhNh/tPvHt3fpIkgKSpEAQ7kC', 'donor', 'active', '2026-04-29 22:19:17', '2026-04-29 23:51:19', NULL, NULL),
(21, 'ttt@gmail.com', '$2y$10$aNG0Py8i0ZEpPL870gw9TemDnOH5zDL5Kz51dkhEJFlLk2KCulc1m', 'donor', 'pending', '2026-04-29 23:52:56', NULL, NULL, NULL),
(22, 'rrw@gmail.com', '$2y$10$sWaEFdig.a.8XTn0Ppg0AuwEkfoH1UWZvtXtmG5M1Cu90EkOKSGJu', 'hospital', 'active', '2026-04-29 23:55:36', '2026-04-30 00:00:57', NULL, NULL),
(23, 'fpy@gmail.com', '$2y$10$p0oRarfsuz/IovJDPEiqnO9j4Oi1X9TTVL/kNOOhZOzt6VrDdeAeS', 'donor', 'active', '2026-04-30 00:19:50', '2026-04-30 00:21:44', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `blood_drives`
--
ALTER TABLE `blood_drives`
  ADD PRIMARY KEY (`drive_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `idx_drive_date` (`drive_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_city` (`city`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `idx_blood_type` (`blood_type`),
  ADD KEY `idx_urgency` (`urgency_level`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `idx_donor_id` (`donor_id`),
  ADD KEY `idx_hospital_id` (`hospital_id`),
  ADD KEY `idx_donation_date` (`donation_date`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`donor_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_blood_type` (`blood_type`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_availability` (`is_available`),
  ADD KEY `idx_location` (`latitude`,`longitude`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`hospital_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_location` (`latitude`,`longitude`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `blood_drives`
--
ALTER TABLE `blood_drives`
  MODIFY `drive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `donor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `hospital_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_drives`
--
ALTER TABLE `blood_drives`
  ADD CONSTRAINT `blood_drives_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`) ON DELETE SET NULL;

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `blood_requests` (`request_id`) ON DELETE SET NULL;

--
-- Constraints for table `donors`
--
ALTER TABLE `donors`
  ADD CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD CONSTRAINT `hospitals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `blood_requests` (`request_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
