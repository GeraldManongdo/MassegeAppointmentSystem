-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 03:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `appointment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no-show') DEFAULT 'confirmed',
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `service_id`, `appointment_date`, `start_time`, `end_time`, `status`, `cancellation_reason`, `cancelled_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 2, '2026-03-27', '14:00:00', '15:30:00', 'cancelled', '', '2026-03-08 08:07:11', '', '2026-03-08 08:00:19', '2026-03-08 08:07:11'),
(2, 3, 2, '2026-03-09', '09:00:00', '10:30:00', 'confirmed', NULL, NULL, '', '2026-03-08 08:07:33', '2026-03-08 08:07:33'),
(3, 3, 5, '2026-03-09', '14:00:00', '15:15:00', 'confirmed', NULL, NULL, '', '2026-03-08 08:08:14', '2026-03-08 08:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `business_hours`
--

CREATE TABLE `business_hours` (
  `hour_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '0=Sunday, 6=Saturday',
  `opening_time` time NOT NULL,
  `closing_time` time NOT NULL,
  `is_closed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_hours`
--

INSERT INTO `business_hours` (`hour_id`, `day_of_week`, `opening_time`, `closing_time`, `is_closed`) VALUES
(1, 0, '00:00:00', '00:00:00', 1),
(2, 1, '09:00:00', '17:00:00', 0),
(3, 2, '09:00:00', '17:00:00', 0),
(4, 3, '09:00:00', '17:00:00', 0),
(5, 4, '09:00:00', '17:00:00', 0),
(6, 5, '09:00:00', '17:00:00', 0),
(7, 6, '00:00:00', '00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `type` enum('confirmation','reminder','cancellation','reschedule') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL COMMENT 'Service image URL or path',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `duration`, `price`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Massage Therapy', 'Relaxing full-body massage therapy session', 60, 75.00, 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800', 'active', '2026-03-08 07:50:52', '2026-03-08 07:50:52'),
(2, 'Deep Tissue Massage', 'Intensive massage targeting deep muscle tension', 90, 110.00, 'https://images.unsplash.com/photo-1519824145371-296894a0daa9?w=800', 'active', '2026-03-08 07:50:52', '2026-03-08 07:50:52'),
(3, 'Swedish Massage', 'Gentle, relaxing Swedish massage', 60, 70.00, 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800', 'active', '2026-03-08 07:50:52', '2026-03-08 07:50:52'),
(4, 'Sports Massage', 'Therapeutic massage for athletes and active individuals', 60, 85.00, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800', 'active', '2026-03-08 07:50:52', '2026-03-08 07:50:52'),
(5, 'Hot Stone Therapy', 'Massage using heated stones for deep relaxation', 75, 95.00, 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=800', 'active', '2026-03-08 07:50:52', '2026-03-08 07:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `description`) VALUES
(1, 'cancellation_hours', '24', 'Hours before appointment that cancellation is allowed'),
(2, 'slot_lock_duration', '5', 'Minutes to hold a time slot during checkout'),
(3, 'buffer_time', '0', 'Buffer time in minutes between appointments'),
(4, 'system_name', 'Appointment Booking System', 'Name of the system'),
(5, 'system_email', 'noreply@appointmentsystem.com', 'System email for notifications');

-- --------------------------------------------------------

--
-- Table structure for table `time_slot_locks`
--

CREATE TABLE `time_slot_locks` (
  `lock_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `locked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password`, `role`, `status`, `email_verified`, `verification_token`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin@appointmentsystem.com', '1234567890', '$2y$10$VYKUBMhk/9K1V0u8VlLuVO8gHWs1rkDutELUaFubZv.zQgvTjisQG', 'admin', 'active', 1, NULL, '2026-03-08 07:50:52', '2026-03-08 13:57:34'),
(2, 'Just Admin', 'admin@gmail.com', '09562184010', '$2y$10$I/R.tYVHU7Ty4haqv8X73eZjuIWvv3i9t.73iHT/qxTkIpoLLEwNC', 'admin', 'active', 0, NULL, '2026-03-08 07:55:57', '2026-03-08 07:57:46'),
(3, 'Just User', 'user@gmail.com', '09562184010', '$2y$10$Pem.iB1gSjy4SeyGgbSeyeiFI3wgtDNXXhKLSrzeTTzqHvSFUbzd2', 'user', 'active', 0, NULL, '2026-03-08 07:59:56', '2026-03-08 07:59:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_datetime` (`appointment_date`,`start_time`);

--
-- Indexes for table `business_hours`
--
ALTER TABLE `business_hours`
  ADD PRIMARY KEY (`hour_id`),
  ADD UNIQUE KEY `day_of_week` (`day_of_week`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `time_slot_locks`
--
ALTER TABLE `time_slot_locks`
  ADD PRIMARY KEY (`lock_id`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_slot` (`service_id`,`appointment_date`,`start_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `business_hours`
--
ALTER TABLE `business_hours`
  MODIFY `hour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `time_slot_locks`
--
ALTER TABLE `time_slot_locks`
  MODIFY `lock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointment_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
