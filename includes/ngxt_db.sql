-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2025 at 01:06 PM
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
-- Database: `ngxt_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `company_contacts_tbl`
--

CREATE TABLE `company_contacts_tbl` (
  `cc_id` int(11) NOT NULL,
  `cc_email` varchar(255) NOT NULL,
  `cc_mo_number` int(11) NOT NULL,
  `cc_fb_link` varchar(255) NOT NULL,
  `cc_ig_link` varchar(255) NOT NULL,
  `cc_twt_link` varchar(255) NOT NULL,
  `cc_yt_link` varchar(255) NOT NULL,
  `cd_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_details_tbl`
--

CREATE TABLE `company_details_tbl` (
  `cd_id` int(11) NOT NULL,
  `cd_title` varchar(255) NOT NULL,
  `cd_subtitle` varchar(255) NOT NULL,
  `cd_subtitle1` varchar(255) NOT NULL,
  `cd_description` varchar(1000) NOT NULL,
  `cd_logo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_details_tbl`
--

INSERT INTO `company_details_tbl` (`cd_id`, `cd_title`, `cd_subtitle`, `cd_subtitle1`, `cd_description`, `cd_logo`) VALUES
(1, 'NGXT', 'Empowering', 'Your Brand\'s Story', 'Next-level creative solutions tailored for your brand. Igniting innovations through strategic and bold design.', 'logo_ngxt.png');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_tbl`
--

CREATE TABLE `gallery_tbl` (
  `id` int(11) NOT NULL,
  `svs_id` int(11) NOT NULL,
  `glr_title` varchar(100) NOT NULL,
  `glr_description` varchar(255) NOT NULL,
  `gallery_image` varchar(255) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_tbl`
--

INSERT INTO `gallery_tbl` (`id`, `svs_id`, `glr_title`, `glr_description`, `gallery_image`, `file_type`, `created_at`) VALUES
(13, 3, 'valorantasgasg', 'sgsagas', '1752402037_6873887582467.mp4', 'video', '2025-07-13 10:20:37'),
(14, 3, 'valorant', 'asgsagsasggas', '1752402731_68738b2b7babd.mp4', 'video', '2025-07-13 10:32:11');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(4, 'renzrodriguez23@gmail.com', 'f788d5a95e7f8fc2058f4a20c2b0e2712de16b42bc03efec46579f9820b22c50', '2025-07-01 14:39:45', '2025-07-01 05:39:45');

-- --------------------------------------------------------

--
-- Table structure for table `profile_images_tbl`
--

CREATE TABLE `profile_images_tbl` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `p_images` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_images_tbl`
--

INSERT INTO `profile_images_tbl` (`id`, `user_id`, `p_images`, `category`, `created_at`) VALUES
(1, 2, '687340b4a8b80_516890076_1109136917754339_2986362504777430536_n.jpg', NULL, '2025-07-13 05:14:28'),
(2, 2, '687341418710d_516890076_1109136917754339_2986362504777430536_n.jpg', NULL, '2025-07-13 05:16:49'),
(3, 2, '2_20250713.jpg', NULL, '2025-07-13 05:18:53'),
(4, 2, '2_20250713_687341c3ca85a.jpg', NULL, '2025-07-13 05:18:59'),
(5, 2, '2_20250713_6873424973ae5.jpg', NULL, '2025-07-13 05:21:13'),
(6, 2, '2_20250713_68734260901a5.jpg', NULL, '2025-07-13 05:21:36');

-- --------------------------------------------------------

--
-- Table structure for table `project_cover_photo_tbl`
--

CREATE TABLE `project_cover_photo_tbl` (
  `pcp_id` int(11) NOT NULL,
  `pcp_title` varchar(255) NOT NULL,
  `pcp_image` varchar(255) NOT NULL,
  `cd_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_cover_photo_tbl`
--

INSERT INTO `project_cover_photo_tbl` (`pcp_id`, `pcp_title`, `pcp_image`, `cd_id`) VALUES
(10, 'default background', 'ngxt_3_1139.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `registration_tokens`
--

CREATE TABLE `registration_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` char(64) NOT NULL,
  `act` enum('accept','reject') DEFAULT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services_tbl`
--

CREATE TABLE `services_tbl` (
  `svs_id` int(11) NOT NULL,
  `svs_title` varchar(255) DEFAULT NULL,
  `svs_description` text DEFAULT NULL,
  `svs_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_tbl`
--

INSERT INTO `services_tbl` (`svs_id`, `svs_title`, `svs_description`, `svs_logo`) VALUES
(3, 'Bawat Pyesa', 'Munimuni', 'Screenshot 2025-07-13 124042.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `role_status` varchar(50) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `facebook` varchar(255) NOT NULL,
  `instagram` varchar(255) NOT NULL,
  `twitter` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `fname`, `lname`, `role_status`, `profile_pic`, `facebook`, `instagram`, `twitter`) VALUES
(1, 'biokey.lockersystem@gmail.com', '$2y$10$zKCQb/J7OOlMZSZTHlnWx.Szyjo4QnkdXUCoAfdRsL23Z9Aa79qwq', 'Renzel', 'Rodriguez', 'partners', '', '', '', ''),
(2, 'renzrodriguez23@gmail.com', '$2y$10$9ql6DOu98qez/Kdt32GVQO9CHrzVItXfcuHVSCGR7iQ5Zr4ZDEG3e', 'Renzel', 'Rodriguez', 'partners', 'profile_1752383205_R.jpg', 'm.me/renz', 'ig.me/renz', 'x.me/renz'),
(3, 'marklouis0santiago@gmail.com', '$2y$10$0Kl/oqXsTcA7k3829br6auuqjhb5p2xxBCiEhaFaSHVCRk.Q34JOy', 'Louis', 'Santiago', 'active', '', '', '', ''),
(4, 'santiagomarklouis.bsit@gmail.com', '$2y$10$Ep7lUCYeLiw8AM52Dt67.euZ9QCjHZ0IiGz6hf3cSUZMHmPfGFf0C', 'mark', 'louis', 'partners', '', '', '', ''),
(11, 'zoro@gmail.com', '$2y$10$iTx7Mc1tzFb.8F9hd8ft2OjJCch/Hmigq0jW186foGAWour2cHg4G', 'zoro', 'roronoa', 'partners', '', '', '', ''),
(12, 'sanji@gmail.com', '$2y$10$bvSj5apL6uMMN6VFmlO3pO0unTJ7kTDWROCnulAxxsfs91qX250ue', 'sanji', 'vinsmoke', 'rejected', '', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gallery_tbl`
--
ALTER TABLE `gallery_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `svs_id` (`svs_id`);

--
-- Indexes for table `profile_images_tbl`
--
ALTER TABLE `profile_images_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services_tbl`
--
ALTER TABLE `services_tbl`
  ADD PRIMARY KEY (`svs_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gallery_tbl`
--
ALTER TABLE `gallery_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `profile_images_tbl`
--
ALTER TABLE `profile_images_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services_tbl`
--
ALTER TABLE `services_tbl`
  MODIFY `svs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gallery_tbl`
--
ALTER TABLE `gallery_tbl`
  ADD CONSTRAINT `svs_id` FOREIGN KEY (`svs_id`) REFERENCES `services_tbl` (`svs_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `profile_images_tbl`
--
ALTER TABLE `profile_images_tbl`
  ADD CONSTRAINT `profile_images_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
