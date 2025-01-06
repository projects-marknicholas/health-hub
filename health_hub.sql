-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2025 at 04:51 AM
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
-- Database: `health_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `appointment_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `doctor_id` varchar(255) NOT NULL,
  `appointment_time` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `appointment_id`, `user_id`, `doctor_id`, `appointment_time`, `created_at`) VALUES
(4, '64c5231367c078afe6eb981626ba76b8', 'f1189a121a1e04a73a26e94cde8e02fc', '6fe9a3d416552fd34f2c7a1c02bee22a', '2025-01-01 03:17:00', '2025-01-06 02:17:47'),
(5, '2ccd7c1add92ebd2e2f9c41c0928e3b9', 'dbf3a73695f70f02da0f59a6ce171504', '6fe9a3d416552fd34f2c7a1c02bee22a', '2025-01-17 01:34:00', '2025-01-06 11:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `availability_id` varchar(255) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `user_id`, `availability_id`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(5, '6fe9a3d416552fd34f2c7a1c02bee22a', 'c79346d3d418fd57de377a78c5e1e200', '2025-01-01 02:16:00', '2025-01-10 02:16:00', 'available', '2025-01-06 02:16:49'),
(6, '6fe9a3d416552fd34f2c7a1c02bee22a', 'f853d981118e7dd1a029e4b013d3e193', '2025-01-11 00:33:00', '2025-01-20 00:33:00', 'available', '2025-01-06 11:33:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `role` varchar(16) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `name`, `email`, `contact_number`, `role`, `password`, `created_at`) VALUES
(1, 'f1189a121a1e04a73a26e94cde8e02fc', 'user', 'user@gmail.com', '09123456789', 'user', '$2y$10$jmIDg5pmadHPiYtk9tThhOzVGhh.gy0XQTuieA60tXTYXF6OFEcC6', '2025-01-05 11:41:11'),
(2, '6fe9a3d416552fd34f2c7a1c02bee22a', 'doctor', 'doctor@gmail.com', '09123456789', 'doctor', '$2y$10$GngzyJpbOdul.wphcnJfse8vy.zAJZQxywBmnEX5OdROpjrM4oClC', '2025-01-05 11:51:33'),
(3, 'dbf3a73695f70f02da0f59a6ce171504', 'lex', 'lex@gmail.com', '09123456789', 'user', '$2y$10$UNQDJsxQOiYoN.wjqAalWOA13XQSEix5BEI5e7M6YMCK1QFW4mC5C', '2025-01-06 11:31:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
