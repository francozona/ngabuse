-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 03:11 PM
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
-- Database: `nira_abuse`
--

-- --------------------------------------------------------

--
-- Table structure for table `abuse_reports`
--

CREATE TABLE `abuse_reports` (
  `id` int(11) UNSIGNED NOT NULL,
  `ticket_id` varchar(40) NOT NULL,
  `registrar_email` varchar(400) DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `domain_name` varchar(253) NOT NULL,
  `tld` varchar(20) NOT NULL COMMENT '.ng | .com.ng | .org.ng …',
  `full_domain` varchar(273) NOT NULL,
  `abusive_url` text NOT NULL,
  `date_first_observed` date NOT NULL,
  `abuse_category` text DEFAULT NULL,
  `description` text NOT NULL,
  `registrar_notified` varchar(200) DEFAULT NULL,
  `registrar_notification_date` date DEFAULT NULL,
  `evidence_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evidence_files`)),
  `status` enum('pending','assigned_to_registrar','resolved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `abuse_reports`
--

INSERT INTO `abuse_reports` (`id`, `ticket_id`, `registrar_email`, `user_id`, `domain_name`, `tld`, `full_domain`, `abusive_url`, `date_first_observed`, `abuse_category`, `description`, `registrar_notified`, `registrar_notification_date`, `evidence_files`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'NiRA-ABUSE-20260603-0001', NULL, 1, 'nira', 'org.ng', 'nira.org.ng', 'https://nira.org.ng/abuse', '2026-06-03', 'dfbdfb', 'dfdf dbff fdfbfb', NULL, NULL, '[\"\\/uploads\\/evidence\\/1780492034_5ae49eec1883c2d33cd6.png\"]', 'pending', '2026-06-03 13:07:25', '2026-06-03 13:10:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `abuse_report_responses`
--

CREATE TABLE `abuse_report_responses` (
  `id` int(11) UNSIGNED NOT NULL,
  `report_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(15, '2026-05-13-195210', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1780492020, 1),
(16, '2026-05-13-195229', 'App\\Database\\Migrations\\CreateAbuseReportsTable', 'default', 'App', 1780492020, 1),
(17, '2026-05-16-154451', 'App\\Database\\Migrations\\CreateAbuseReportResponsesTable', 'default', 'App', 1780492020, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `image` varchar(150) NOT NULL,
  `raw_password` varchar(1000) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `role` varchar(200) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `image`, `raw_password`, `full_name`, `email`, `password`, `role`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '', '7deaca36d5', 'dfbf fbfd', 'solo@Hfjf.fkff', '$2y$10$Z6PgqDNFFjy43opFeevQr.aiOAuungsqmlTyunQyWOtrGxwIk90c6', 'visitor', '2026-06-03 13:07:14', '2026-06-03 13:10:08', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abuse_reports`
--
ALTER TABLE `abuse_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `abuse_category` (`abuse_category`(768)),
  ADD KEY `status` (`status`);

--
-- Indexes for table `abuse_report_responses`
--
ALTER TABLE `abuse_report_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abuse_reports`
--
ALTER TABLE `abuse_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `abuse_report_responses`
--
ALTER TABLE `abuse_report_responses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abuse_reports`
--
ALTER TABLE `abuse_reports`
  ADD CONSTRAINT `abuse_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `abuse_report_responses`
--
ALTER TABLE `abuse_report_responses`
  ADD CONSTRAINT `abuse_report_responses_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `abuse_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `abuse_report_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
