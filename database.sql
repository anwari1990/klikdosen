-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 25, 2019 at 09:08 PM
-- Server version: 10.3.12-MariaDB-2
-- PHP Version: 7.3.3-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `follower_user_id` int(11) NOT NULL,
  `following_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `followers`
--

INSERT INTO `followers` (`id`, `follower_user_id`, `following_user_id`) VALUES
(3, 36, 38),
(112, 36, 37);

-- --------------------------------------------------------

--
-- Table structure for table `papers`
--

CREATE TABLE `papers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file` varchar(255) NOT NULL,
  `views` bigint(255) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `papers`
--

INSERT INTO `papers` (`id`, `user_id`, `title`, `description`, `file`, `views`, `created_at`, `updated_at`) VALUES
(16, 36, 'Makalah Akuntabilitas Publik', 'Ini Makalah Akuntabilitas Publik Contoh ', '5cc18c791c9cc.pdf', 130, '2019-04-26 01:01:59', '2019-04-25 21:31:21'),
(17, 36, 'ini hanya test paper', 'ini hanya test paper ini hanya test paper ini hanya test paper', '5cc18e44b0b66.pdf', 247, '2019-04-26 01:02:16', '2019-04-25 21:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `paper_research`
--

CREATE TABLE `paper_research` (
  `id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `research_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `researches`
--

CREATE TABLE `researches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user-no-photo.png',
  `bio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hello There',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `forgot_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `picture`, `bio`, `password`, `forgot_token`, `created_at`, `updated_at`) VALUES
(36, 'Mochammad Riyad', 'ilhampasya920@gmail.com', 'GtOPJ6Wc34a694J7.jpg', '', 'eyJpdiI6InZ5Sjk0eE1qcFprNWRqcnRBM1g1Y2c9PSIsInZhbHVlIjoiWlhXR1wvbENcL1FIakVocE9mOExEUlJRPT0ifQ==', NULL, '2019-04-21 11:57:22', '2019-04-25 23:30:48'),
(37, 'test', 'test@test.com', '5cc08d8a88a10.jpg', 'Hello There', 'eyJpdiI6ImtQcnRyTmNlcVFvZEV1Mjdhc2NVVUE9PSIsInZhbHVlIjoiWmxhc2ZaVnNxM1pUazlLVm56UFBNZz09In0=', NULL, '2019-04-22 11:33:20', '2019-04-25 03:24:20'),
(38, 'ilam', 'ilam@ilam.com', 'user-no-photo.png', 'Hello There', 'eyJpdiI6IkRCOFliUW9OR21BVWxPOGVKZ0dBWnc9PSIsInZhbHVlIjoiWWtJRW90SGlyUUFpS3ZHUTFZN1dQVTZpRkFWWmYyOEFLY2JHSkpvNFhVYz0ifQ==', NULL, '2019-04-23 13:55:00', '2019-04-23 13:55:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `papers`
--
ALTER TABLE `papers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paper_research`
--
ALTER TABLE `paper_research`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `researches`
--
ALTER TABLE `researches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `papers`
--
ALTER TABLE `papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `paper_research`
--
ALTER TABLE `paper_research`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `researches`
--
ALTER TABLE `researches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
