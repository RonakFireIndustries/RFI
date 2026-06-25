-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2026 at 02:57 PM
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
-- Database: `erp_db`
--
CREATE DATABASE IF NOT EXISTS `erp_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `erp_db`;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `checkin_latitude` decimal(10,8) DEFAULT NULL,
  `checkin_longitude` decimal(11,8) DEFAULT NULL,
  `checkout_latitude` decimal(10,8) DEFAULT NULL,
  `checkout_longitude` decimal(11,8) DEFAULT NULL,
  `checkin_distance` decimal(8,2) DEFAULT NULL,
  `checkout_distance` decimal(8,2) DEFAULT NULL,
  `location_verified` tinyint(1) NOT NULL DEFAULT 0,
  `accuracy` decimal(8,2) DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `browser_info` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `working_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overtime` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Present',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shift_id` bigint(20) UNSIGNED DEFAULT NULL,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `employee_id`, `site_id`, `date`, `check_in`, `check_out`, `checkin_latitude`, `checkin_longitude`, `checkout_latitude`, `checkout_longitude`, `checkin_distance`, `checkout_distance`, `location_verified`, `accuracy`, `device_info`, `browser_info`, `ip_address`, `working_hours`, `overtime`, `status`, `created_at`, `updated_at`, `shift_id`, `overtime_hours`, `remarks`) VALUES
(33, 9, 4, '2026-06-20', '2026-06-20 12:06:38', '2026-06-20 19:36:46', 19.29946630, 72.85804723, 19.29946630, 72.85804723, 135.08, 135.08, 1, 141.00, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '127.0.0.1', 7.50, 0.00, 'Present', '2026-06-20 01:06:38', '2026-06-20 07:05:36', NULL, 0.00, NULL),
(34, 10, 4, '2026-06-23', '2026-06-23 11:58:22', '2026-06-23 12:47:49', 19.29942676, 72.85814550, 19.29942676, 72.85814550, 146.00, 146.00, 1, 141.00, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '127.0.0.1', 0.82, 0.00, 'Present', '2026-06-23 06:28:22', '2026-06-23 07:17:49', NULL, 0.00, NULL),
(35, 9, 4, '2026-06-23', '2026-06-23 14:46:50', NULL, 19.29942676, 72.85814550, NULL, NULL, 146.00, NULL, 1, 141.00, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '127.0.0.1', 0.00, 0.00, 'Present', '2026-06-23 09:16:50', '2026-06-23 09:16:50', NULL, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_settings`
--

CREATE TABLE `audit_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `configurable_type` varchar(255) NOT NULL,
  `configurable_id` bigint(20) UNSIGNED NOT NULL,
  `logging_verbosity` varchar(255) NOT NULL DEFAULT 'medium',
  `retention_days` int(11) NOT NULL DEFAULT 90,
  `reason_required` tinyint(1) NOT NULL DEFAULT 0,
  `change_tracking_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `snapshot_creation_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_settings`
--

INSERT INTO `audit_settings` (`id`, `configurable_type`, `configurable_id`, `logging_verbosity`, `retention_days`, `reason_required`, `change_tracking_enabled`, `snapshot_creation_enabled`, `created_at`, `updated_at`) VALUES
(1, 'role', 1, 'medium', 90, 0, 1, 0, '2026-06-22 12:05:34', '2026-06-22 12:05:34'),
(2, 'role', 2, 'medium', 90, 0, 1, 0, '2026-06-22 12:21:17', '2026-06-22 12:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('rfi-management-suite-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:220:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"create sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"manage employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"manage inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"manage payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"manage purchases\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"manage sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"manage suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"manage transfers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:15:\"view attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"view categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:8:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:21;i:5;i:6;i:6;i:4;i:7;i:5;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:14:\"view inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:13:\"view invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:13:\"view products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:6;i:5;i:4;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:15:\"view warehouses\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:6;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:16:\"update_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"view_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:21;i:5;i:6;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:15:\"create_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:15:\"update_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:15:\"delete_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:14:\"view_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:16:\"create_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:16:\"update_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:16:\"delete_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:14:\"view_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:16:\"create_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:16:\"update_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:16:\"delete_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:10:\"view_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:4;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:12:\"create_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:12:\"update_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:12:\"delete_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:15:\"department.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:17:\"department.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:15:\"department.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"department.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:16:\"designation.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:18:\"designation.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:16:\"designation.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:18:\"designation.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"employee.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:8:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:3;i:5;i:21;i:6;i:6;i:7;i:4;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:15:\"employee.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:13:\"employee.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:15:\"employee.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:23:\"employee.assign-manager\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:20:\"employee.assign-role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:15:\"view_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:21;i:5;i:6;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:17:\"create_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:17:\"update_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:17:\"delete_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"view_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:4;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"create_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:4;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:17:\"update_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:4;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:14:\"view_inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:21;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:16:\"manage_inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:13:\"view_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:15:\"create_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:15:\"update_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:15:\"delete_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:13:\"view_payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:15:\"create_payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:12:\"view_payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;i:4;i:4;i:5;i:5;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:14:\"manage_payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:6;i:4;i:4;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:11:\"view_leaves\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:13:\"create_leaves\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:17:\"view_sales_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;i:4;i:6;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:19:\"create_sales_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;i:4;i:6;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:20:\"view_purchase_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;i:4;i:6;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:22:\"create_purchase_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:21;i:4;i:6;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:12:\"manage_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:18:\"manage_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:13:\"document.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:15:\"document.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:13:\"document.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:15:\"document.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:17:\"document.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:16:\"document.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:22:\"document.manage-expiry\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:9:\"site.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:2;i:4;i:3;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:11:\"site.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:9:\"site.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:11:\"site.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:11:\"site.assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:13:\"site.transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:17:\"site.history.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:17:\"delete_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:4;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:15:\"attendance.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:15:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:13;i:4;i:14;i:5;i:15;i:6;i:16;i:7;i:17;i:8;i:19;i:9;i:20;i:10;i:22;i:11;i:2;i:12;i:3;i:13;i:4;i:14;i:5;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:17:\"attendance.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:8:{i:0;i:10;i:1;i:11;i:2;i:14;i:3;i:15;i:4;i:16;i:5;i:17;i:6;i:22;i:7;i:4;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:15:\"attendance.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:4;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:17:\"attendance.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:4;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:18:\"attendance.checkin\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:4;i:3;i:5;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:19:\"attendance.checkout\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:4;i:3;i:5;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:10:\"shift.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:13;i:4;i:22;i:5;i:2;i:6;i:4;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:12:\"shift.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:10:\"shift.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:12:\"shift.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:17:\"daily-report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:13:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:13;i:4;i:14;i:5;i:15;i:6;i:16;i:7;i:17;i:8;i:22;i:9;i:2;i:10;i:3;i:11;i:4;i:12;i:5;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:19:\"daily-report.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:22;i:4;i:2;i:5;i:4;i:6;i:5;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:17:\"daily-report.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:19:\"daily-report.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:19:\"daily-report.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;i:4;i:5;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:20:\"daily-report.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:19:\"daily-report.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:19:\"daily-report.rework\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:24:\"daily-report.report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:3;i:4;i:4;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:10:\"leave.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:3;i:5;i:4;i:6;i:5;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:12:\"leave.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:4;i:5;i:5;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:10:\"leave.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:12:\"leave.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:13:\"leave.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:12:\"leave.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:12:\"leave.cancel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:5;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:18:\"leave.balance.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;i:4;i:5;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:15:\"leave-type.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:17:\"leave-type.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:15:\"leave-type.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:2;i:3;i:4;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:17:\"leave-type.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:9:\"user.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:11:\"user.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:9:\"user.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:11:\"user.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:23;i:2;i:2;}}i:127;a:4:{s:1:\"a\";i:128;s:1:\"b\";s:9:\"role.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:11:\"role.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:129;a:4:{s:1:\"a\";i:130;s:1:\"b\";s:9:\"role.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:11:\"role.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:131;a:4:{s:1:\"a\";i:132;s:1:\"b\";s:15:\"permission.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:132;a:4:{s:1:\"a\";i:133;s:1:\"b\";s:17:\"permission.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:133;a:4:{s:1:\"a\";i:134;s:1:\"b\";s:15:\"permission.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:134;a:4:{s:1:\"a\";i:135;s:1:\"b\";s:17:\"permission.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:23;i:3;i:2;}}i:135;a:4:{s:1:\"a\";i:136;s:1:\"b\";s:14:\"inventory.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:2;}}i:136;a:4:{s:1:\"a\";i:137;s:1:\"b\";s:16:\"inventory.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:137;a:4:{s:1:\"a\";i:138;s:1:\"b\";s:14:\"inventory.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:138;a:4:{s:1:\"a\";i:139;s:1:\"b\";s:16:\"inventory.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:139;a:4:{s:1:\"a\";i:140;s:1:\"b\";s:14:\"warehouse.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:140;a:4:{s:1:\"a\";i:141;s:1:\"b\";s:16:\"warehouse.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:141;a:4:{s:1:\"a\";i:142;s:1:\"b\";s:14:\"warehouse.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:142;a:4:{s:1:\"a\";i:143;s:1:\"b\";s:16:\"warehouse.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:143;a:4:{s:1:\"a\";i:144;s:1:\"b\";s:12:\"product.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:19;i:4;i:20;i:5;i:2;}}i:144;a:4:{s:1:\"a\";i:145;s:1:\"b\";s:14:\"product.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:145;a:4:{s:1:\"a\";i:146;s:1:\"b\";s:12:\"product.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:146;a:4:{s:1:\"a\";i:147;s:1:\"b\";s:14:\"product.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:147;a:4:{s:1:\"a\";i:148;s:1:\"b\";s:13:\"customer.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:148;a:4:{s:1:\"a\";i:149;s:1:\"b\";s:15:\"customer.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:149;a:4:{s:1:\"a\";i:150;s:1:\"b\";s:13:\"customer.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:150;a:4:{s:1:\"a\";i:151;s:1:\"b\";s:15:\"customer.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:151;a:4:{s:1:\"a\";i:152;s:1:\"b\";s:16:\"sales-order.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:152;a:4:{s:1:\"a\";i:153;s:1:\"b\";s:18:\"sales-order.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:153;a:4:{s:1:\"a\";i:154;s:1:\"b\";s:16:\"sales-order.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:154;a:4:{s:1:\"a\";i:155;s:1:\"b\";s:18:\"sales-order.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:155;a:4:{s:1:\"a\";i:156;s:1:\"b\";s:12:\"invoice.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:156;a:4:{s:1:\"a\";i:157;s:1:\"b\";s:14:\"invoice.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:157;a:4:{s:1:\"a\";i:158;s:1:\"b\";s:12:\"invoice.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:158;a:4:{s:1:\"a\";i:159;s:1:\"b\";s:14:\"invoice.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:159;a:4:{s:1:\"a\";i:160;s:1:\"b\";s:19:\"purchase-order.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:21:\"purchase-order.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:161;a:4:{s:1:\"a\";i:162;s:1:\"b\";s:19:\"purchase-order.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:21:\"purchase-order.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:163;a:4:{s:1:\"a\";i:164;s:1:\"b\";s:11:\"report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:22;i:4;i:2;}}i:164;a:4:{s:1:\"a\";i:165;s:1:\"b\";s:13:\"report.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:165;a:4:{s:1:\"a\";i:166;s:1:\"b\";s:14:\"dashboard.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:14:{i:0;i:10;i:1;i:11;i:2;i:12;i:3;i:13;i:4;i:14;i:5;i:15;i:6;i:16;i:7;i:17;i:8;i:18;i:9;i:19;i:10;i:20;i:11;i:22;i:12;i:23;i:13;i:2;}}i:166;a:4:{s:1:\"a\";i:167;s:1:\"b\";s:12:\"payroll.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:22;i:4;i:2;}}i:167;a:4:{s:1:\"a\";i:168;s:1:\"b\";s:14:\"payroll.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:168;a:4:{s:1:\"a\";i:169;s:1:\"b\";s:16:\"payroll.generate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:169;a:4:{s:1:\"a\";i:170;s:1:\"b\";s:15:\"payroll.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:170;a:4:{s:1:\"a\";i:171;s:1:\"b\";s:12:\"payroll.lock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:171;a:4:{s:1:\"a\";i:172;s:1:\"b\";s:11:\"payroll.pay\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:172;a:4:{s:1:\"a\";i:173;s:1:\"b\";s:14:\"payroll.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:173;a:4:{s:1:\"a\";i:174;s:1:\"b\";s:12:\"payslip.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:174;a:4:{s:1:\"a\";i:175;s:1:\"b\";s:16:\"payslip.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:175;a:4:{s:1:\"a\";i:176;s:1:\"b\";s:21:\"salary-structure.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;i:4;i:6;}}i:176;a:4:{s:1:\"a\";i:177;s:1:\"b\";s:23:\"salary-structure.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:177;a:4:{s:1:\"a\";i:178;s:1:\"b\";s:21:\"salary-structure.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:178;a:4:{s:1:\"a\";i:179;s:1:\"b\";s:23:\"salary-structure.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:22;i:3;i:2;}}i:179;a:4:{s:1:\"a\";i:180;s:1:\"b\";s:13:\"category.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:180;a:4:{s:1:\"a\";i:181;s:1:\"b\";s:15:\"category.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:181;a:4:{s:1:\"a\";i:182;s:1:\"b\";s:13:\"category.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:182;a:4:{s:1:\"a\";i:183;s:1:\"b\";s:15:\"category.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:183;a:4:{s:1:\"a\";i:184;s:1:\"b\";s:13:\"supplier.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:10;i:1;i:11;i:2;i:18;i:3;i:2;}}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:15:\"supplier.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:185;a:4:{s:1:\"a\";i:186;s:1:\"b\";s:13:\"supplier.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:186;a:4:{s:1:\"a\";i:187;s:1:\"b\";s:15:\"supplier.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:10;i:1;i:11;i:2;i:2;}}i:187;a:3:{s:1:\"a\";i:188;s:1:\"b\";s:22:\"attendance.geo.checkin\";s:1:\"c\";s:3:\"web\";}i:188;a:3:{s:1:\"a\";i:189;s:1:\"b\";s:23:\"attendance.geo.checkout\";s:1:\"c\";s:3:\"web\";}i:189;a:4:{s:1:\"a\";i:190;s:1:\"b\";s:24:\"attendance.location.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:190;a:4:{s:1:\"a\";i:191;s:1:\"b\";s:25:\"attendance.location.audit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:191;a:4:{s:1:\"a\";i:192;s:1:\"b\";s:12:\"view_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:192;a:4:{s:1:\"a\";i:193;s:1:\"b\";s:14:\"create_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:193;a:4:{s:1:\"a\";i:194;s:1:\"b\";s:12:\"edit_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:194;a:4:{s:1:\"a\";i:195;s:1:\"b\";s:14:\"delete_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:195;a:4:{s:1:\"a\";i:196;s:1:\"b\";s:14:\"export_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:196;a:4:{s:1:\"a\";i:197;s:1:\"b\";s:16:\"schedule_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:197;a:4:{s:1:\"a\";i:198;s:1:\"b\";s:24:\"inventory.locations.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}i:198;a:4:{s:1:\"a\";i:199;s:1:\"b\";s:26:\"inventory.locations.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:199;a:4:{s:1:\"a\";i:200;s:1:\"b\";s:24:\"inventory.locations.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:200;a:4:{s:1:\"a\";i:201;s:1:\"b\";s:26:\"inventory.locations.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:201;a:4:{s:1:\"a\";i:202;s:1:\"b\";s:20:\"inventory.units.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}i:202;a:4:{s:1:\"a\";i:203;s:1:\"b\";s:22:\"inventory.units.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:203;a:4:{s:1:\"a\";i:204;s:1:\"b\";s:20:\"inventory.units.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:204;a:4:{s:1:\"a\";i:205;s:1:\"b\";s:22:\"inventory.units.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:205;a:4:{s:1:\"a\";i:206;s:1:\"b\";s:26:\"inventory.conversions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:206;a:4:{s:1:\"a\";i:207;s:1:\"b\";s:28:\"inventory.conversions.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:207;a:4:{s:1:\"a\";i:208;s:1:\"b\";s:26:\"inventory.conversions.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:208;a:4:{s:1:\"a\";i:209;s:1:\"b\";s:28:\"inventory.conversions.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:209;a:4:{s:1:\"a\";i:210;s:1:\"b\";s:20:\"inventory.stock.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}i:210;a:4:{s:1:\"a\";i:211;s:1:\"b\";s:22:\"inventory.stock.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:211;a:4:{s:1:\"a\";i:212;s:1:\"b\";s:27:\"inventory.transactions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}i:212;a:4:{s:1:\"a\";i:213;s:1:\"b\";s:29:\"inventory.transactions.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:213;a:4:{s:1:\"a\";i:214;s:1:\"b\";s:23:\"inventory.requests.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}i:214;a:4:{s:1:\"a\";i:215;s:1:\"b\";s:25:\"inventory.requests.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:215;a:4:{s:1:\"a\";i:216;s:1:\"b\";s:26:\"inventory.requests.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:216;a:4:{s:1:\"a\";i:217;s:1:\"b\";s:24:\"inventory.transfers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}i:217;a:4:{s:1:\"a\";i:218;s:1:\"b\";s:26:\"inventory.transfers.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:218;a:4:{s:1:\"a\";i:219;s:1:\"b\";s:27:\"inventory.transfers.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:2;i:1;i:21;}}i:219;a:4:{s:1:\"a\";i:220;s:1:\"b\";s:24:\"inventory.dashboard.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:2;i:1;i:3;i:2;i:21;}}}s:5:\"roles\";a:19:{i:0;a:3:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"System Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"General Manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"Admin\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:2:\"HR\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:21;s:1:\"b\";s:13:\"Store Manager\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"Manager\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:10:\"Accountant\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:8:\"Employee\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:22;s:1:\"b\";s:10:\"HR Manager\";s:1:\"c\";s:3:\"web\";}i:9;a:3:{s:1:\"a\";i:12;s:1:\"b\";s:18:\"Production Manager\";s:1:\"c\";s:3:\"web\";}i:10;a:3:{s:1:\"a\";i:13;s:1:\"b\";s:19:\"Workshop Supervisor\";s:1:\"c\";s:3:\"web\";}i:11;a:3:{s:1:\"a\";i:14;s:1:\"b\";s:6:\"Fitter\";s:1:\"c\";s:3:\"web\";}i:12;a:3:{s:1:\"a\";i:15;s:1:\"b\";s:6:\"Welder\";s:1:\"c\";s:3:\"web\";}i:13;a:3:{s:1:\"a\";i:16;s:1:\"b\";s:11:\"Electrician\";s:1:\"c\";s:3:\"web\";}i:14;a:3:{s:1:\"a\";i:17;s:1:\"b\";s:6:\"Helper\";s:1:\"c\";s:3:\"web\";}i:15;a:3:{s:1:\"a\";i:19;s:1:\"b\";s:14:\"Design Manager\";s:1:\"c\";s:3:\"web\";}i:16;a:3:{s:1:\"a\";i:20;s:1:\"b\";s:8:\"Designer\";s:1:\"c\";s:3:\"web\";}i:17;a:3:{s:1:\"a\";i:23;s:1:\"b\";s:10:\"IT Manager\";s:1:\"c\";s:3:\"web\";}i:18;a:3:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"Finance Manager\";s:1:\"c\";s:3:\"web\";}}}', 1782477496);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `status`, `parent_id`, `created_by`) VALUES
(1, 'Electronics', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL),
(2, 'Furniture', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL),
(3, 'Machinery', NULL, '2026-06-15 01:12:18', '2026-06-15 01:12:18', 'Active', NULL, NULL),
(1, 'Electronics', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL),
(2, 'Furniture', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL),
(3, 'Machinery', NULL, '2026-06-15 01:12:18', '2026-06-15 01:12:18', 'Active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category_product`
--

CREATE TABLE `category_product` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL DEFAULT 'RFI Global ERP',
  `address` text DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `vat_number` varchar(255) DEFAULT NULL,
  `tax_registration_number` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `bank_ifsc_code` varchar(255) DEFAULT NULL,
  `bank_swift_code` varchar(255) DEFAULT NULL,
  `bank_branch` varchar(255) DEFAULT NULL,
  `signatory_name` varchar(255) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `company_seal` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `address`, `gst_number`, `vat_number`, `tax_registration_number`, `contact_email`, `contact_phone`, `website`, `bank_name`, `bank_account_name`, `bank_account_number`, `bank_ifsc_code`, `bank_swift_code`, `bank_branch`, `signatory_name`, `signature_image`, `company_seal`, `created_at`, `updated_at`) VALUES
(1, 'Ronak Fire Industries', 'Kasturi Vandana Complex, Bhayandar, Sonam Sagar, Indira Nagar, Bhayandar East., Bhayandar, Maharashtra - 401105', '27ABUPR0079D1ZB', 'VAT-12345678', 'PAN-AAAAA1234A', 'account@ronakfire.com', '+91 22 4123 4567', 'www.rfiglobal.com', 'HDFC Bank - CA', 'RFI Global ERP Solutions Pvt. Ltd.', '50200034070881', 'HDFC0001024', 'SBININBB123', 'Bhayander West', 'Authorised Signatory', NULL, NULL, '2026-06-22 07:48:42', '2026-06-22 07:48:42');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `gst_number`, `address`, `created_at`, `updated_at`) VALUES
(2, 'Abc', 'abc@gmail.com', '1234567980', '12345678912340', 'aaabbbccc', '2026-06-22 06:30:14', '2026-06-22 06:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `customer_quotations`
--

CREATE TABLE `customer_quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_quotation_items`
--

CREATE TABLE `customer_quotation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_quotation_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_reports`
--

CREATE TABLE `daily_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `work_description` text DEFAULT NULL,
  `materials_used` text DEFAULT NULL,
  `supervisor_remarks` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Submitted',
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tasks_completed` text DEFAULT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `issues_faced` text DEFAULT NULL,
  `equipment_used` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_reports`
--

INSERT INTO `daily_reports` (`id`, `employee_id`, `site_id`, `date`, `work_description`, `materials_used`, `supervisor_remarks`, `status`, `photo_path`, `created_at`, `updated_at`, `tasks_completed`, `hours_worked`, `issues_faced`, `equipment_used`, `submitted_at`, `approved_at`, `approved_by`) VALUES
(3, 9, 4, '2026-06-20', 'aaa', 'asdqwe', NULL, 'Approved', NULL, '2026-06-20 07:13:31', '2026-06-20 07:17:25', 'abc', 8.00, 'qwe', 'zxcasdqew', '2026-06-20 07:13:31', '2026-06-20 07:17:25', 9);

-- --------------------------------------------------------

--
-- Table structure for table `daily_report_histories`
--

CREATE TABLE `daily_report_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `daily_report_id` bigint(20) UNSIGNED NOT NULL,
  `action_by` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_report_histories`
--

INSERT INTO `daily_report_histories` (`id`, `daily_report_id`, `action_by`, `action`, `comments`, `created_at`, `updated_at`) VALUES
(1, 3, 9, 'Approved', 'Good', '2026-06-20 07:17:25', '2026-06-20 07:17:25');

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_widgets`
--

CREATE TABLE `dashboard_widgets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `widget_key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `chart_type` varchar(255) DEFAULT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `permission` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dashboard_widgets`
--

INSERT INTO `dashboard_widgets` (`id`, `widget_key`, `name`, `type`, `icon`, `chart_type`, `config`, `permission`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'total_employees', 'Total Employees', 'card', 'Users', NULL, NULL, 'employee.view', 1, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(2, 'present_today', 'Present Today', 'card', 'UserCheck', NULL, NULL, 'attendance.view', 2, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(3, 'absent_today', 'Absent Today', 'card', 'UserX', NULL, NULL, 'attendance.view', 3, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(4, 'employees_on_leave', 'On Leave', 'card', 'UserMinus', NULL, NULL, 'attendance.view', 4, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(5, 'active_sites', 'Active Sites', 'card', 'Building2', NULL, NULL, 'site.view', 5, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(6, 'inventory_value', 'Inventory Value', 'card', 'Package', NULL, NULL, 'inventory.view', 6, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(7, 'low_stock', 'Low Stock Items', 'card', 'AlertTriangle', NULL, NULL, 'inventory.view', 7, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(8, 'out_of_stock', 'Out of Stock', 'card', 'XCircle', NULL, NULL, 'inventory.view', 8, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(9, 'revenue', 'Revenue', 'card', 'TrendingUp', NULL, NULL, 'invoice.view', 9, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(10, 'expenses', 'Expenses', 'card', 'TrendingDown', NULL, NULL, 'purchase-order.view', 10, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(11, 'payroll_cost', 'Payroll Cost', 'card', 'DollarSign', NULL, NULL, 'payroll.view', 11, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(12, 'pending_approvals', 'Pending Approvals', 'card', 'ClipboardList', NULL, NULL, NULL, 12, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(13, 'new_joiners', 'New Joiners', 'card', 'UserPlus', NULL, NULL, 'employee.view', 13, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(14, 'pending_leave_requests', 'Pending Leave Requests', 'card', 'CalendarClock', NULL, NULL, 'leave.view', 14, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(15, 'pending_dpr_approvals', 'Pending DPR Approvals', 'card', 'FileText', NULL, NULL, 'daily-report.approve', 15, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(16, 'document_expiry', 'Document Expiry', 'card', 'FileWarning', NULL, NULL, NULL, 16, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(17, 'salary_processed', 'Salary Processed', 'card', 'Percent', NULL, NULL, 'payroll.view', 17, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(18, 'pending_payroll', 'Draft Payrolls', 'card', 'Clock', NULL, NULL, 'payroll.view', 18, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(19, 'approved_payroll', 'Approved Payroll', 'card', 'CheckCircle', NULL, NULL, 'payroll.approve', 19, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(20, 'paid_payroll', 'Paid Payroll', 'card', 'CreditCard', NULL, NULL, 'payroll.view', 20, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(21, 'payroll_records', 'Payroll Records', 'card', 'Database', NULL, NULL, 'payroll.view', 21, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(22, 'payslips', 'Payslips', 'card', 'FileText', NULL, NULL, 'payslip.view', 22, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(23, 'pending_payments', 'Pending Payments', 'card', 'Wallet', NULL, NULL, 'invoice.view', 23, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(24, 'production_workforce', 'Production Workforce', 'card', 'HardHat', NULL, NULL, NULL, 24, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(25, 'site_productivity', 'Site Productivity', 'card', 'TrendingUp', NULL, NULL, NULL, 25, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(26, 'assigned_employees', 'Assigned Employees', 'card', 'Users', NULL, NULL, NULL, 26, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(27, 'attendance_today', 'Attendance Today', 'card', 'ClipboardCheck', NULL, NULL, NULL, 27, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(28, 'completed_work_reports', 'Completed Reports', 'card', 'CheckSquare', NULL, NULL, NULL, 28, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(29, 'pending_work_reports', 'Pending Reports', 'card', 'Square', NULL, NULL, NULL, 29, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(30, 'design_projects', 'Design Projects', 'card', 'Palette', NULL, NULL, NULL, 30, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(31, 'assigned_designers', 'Design Team', 'card', 'Users', NULL, NULL, NULL, 31, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(32, 'project_progress', 'Project Progress', 'card', 'BarChart3', NULL, NULL, NULL, 32, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(33, 'my_tasks', 'Today\'s Tasks', 'card', 'ListChecks', NULL, NULL, NULL, 33, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(34, 'my_projects', 'My Sites', 'card', 'Building2', NULL, NULL, NULL, 34, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(35, 'my_attendance', 'My Attendance', 'card', 'Calendar', NULL, NULL, NULL, 35, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(36, 'my_dpr', 'My DPRs', 'card', 'FileText', NULL, NULL, NULL, 36, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(37, 'leave_balance', 'Leave Balance', 'card', 'CalendarDays', NULL, NULL, NULL, 37, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(38, 'today_attendance', 'Today\'s Attendance', 'card', 'Fingerprint', NULL, NULL, NULL, 38, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(39, 'current_site', 'Current Site', 'card', 'MapPin', NULL, NULL, NULL, 39, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(40, 'pending_leave_requests_mine', 'My Leave Requests', 'card', 'Clock', NULL, NULL, NULL, 40, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(41, 'active_users', 'Active Users', 'card', 'Users', NULL, NULL, NULL, 41, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(42, 'system_health', 'System Health', 'card', 'Activity', NULL, NULL, NULL, 42, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(43, 'login_statistics', 'Login Statistics', 'card', 'LogIn', NULL, NULL, NULL, 43, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(44, 'audit_logs', 'Audit Logs', 'card', 'ScrollText', NULL, NULL, NULL, 44, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(45, 'failed_logins', 'Failed Logins', 'card', 'ShieldAlert', NULL, NULL, NULL, 45, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(46, 'permission_changes', 'Permission Changes', 'card', 'Shield', NULL, NULL, NULL, 46, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(47, 'server_status', 'Server Status', 'card', 'Server', NULL, NULL, NULL, 47, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(48, 'recent_activity', 'Recent Activity', 'card', 'RefreshCw', NULL, NULL, NULL, 48, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(49, 'employee_summary', 'Employee Summary', 'card', 'Users', NULL, NULL, 'employee.view', 49, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(50, 'attendance_summary', 'Attendance Summary', 'card', 'ClipboardCheck', NULL, NULL, 'attendance.view', 50, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(51, 'leave_summary', 'Leave Summary', 'card', 'CalendarClock', NULL, NULL, 'leave.view', 51, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(52, 'dpr_summary', 'DPR Summary', 'card', 'FileText', NULL, NULL, NULL, 52, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(53, 'payroll_summary', 'Payroll Summary', 'card', 'DollarSign', NULL, NULL, 'payroll.view', 53, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(54, 'company_overview', 'Company Overview', 'card', 'Briefcase', NULL, NULL, NULL, 54, 1, '2026-06-18 06:14:11', '2026-06-18 06:14:11'),
(55, 'site_performance', 'Site Performance', 'card', 'BarChart3', NULL, NULL, NULL, 55, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(56, 'department_performance', 'Department Performance', 'card', 'Building', NULL, NULL, NULL, 56, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(57, 'pending_transfers', 'Pending Transfers', 'card', 'ArrowLeftRight', NULL, NULL, NULL, 57, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(58, 'incoming_stock', 'Incoming Stock', 'card', 'Truck', NULL, NULL, NULL, 58, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(59, 'user_activity', 'User Activity', 'card', 'Activity', NULL, NULL, NULL, 59, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(60, 'attendance_trend', 'Attendance Trend', 'chart', 'BarChart3', 'area', NULL, 'attendance.view', 1, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(61, 'payroll_trend', 'Payroll Trend', 'chart', 'TrendingUp', 'area', NULL, 'payroll.view', 2, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(62, 'inventory_trend', 'Inventory Trend', 'chart', 'Package', 'area', NULL, 'inventory.view', 3, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(63, 'sales_trend', 'Sales Trend', 'chart', 'TrendingUp', 'area', NULL, 'sales-order.view', 4, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(64, 'revenue_vs_expenses', 'Revenue vs Expenses', 'chart', 'BarChart3', 'bar', NULL, NULL, 5, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(65, 'leave_trend', 'Leave Trend', 'chart', 'CalendarClock', 'line', NULL, 'leave.view', 6, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(66, 'employee_growth', 'Employee Growth', 'chart', 'UserPlus', 'area', NULL, 'employee.view', 7, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(67, 'department_headcount', 'Department Headcount', 'chart', 'Building2', 'bar', NULL, NULL, 8, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(68, 'department_salary_cost', 'Salary Cost by Dept', 'chart', 'DollarSign', 'bar', NULL, 'payroll.view', 9, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(69, 'expense_trend', 'Expense Trend', 'chart', 'TrendingDown', 'area', NULL, NULL, 10, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(70, 'attendance_by_site', 'Attendance by Site', 'chart', 'Building2', 'bar', NULL, NULL, 11, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(71, 'dpr_trend', 'DPR Trend', 'chart', 'FileText', 'line', NULL, NULL, 12, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(72, 'workforce_utilization', 'Workforce Utilization', 'chart', 'PieChart', 'pie', NULL, NULL, 13, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(73, 'project_completion', 'Project Completion', 'chart', 'PieChart', 'pie', NULL, NULL, 14, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(74, 'designer_productivity', 'Designer Productivity', 'chart', 'BarChart3', 'bar', NULL, NULL, 15, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(75, 'inventory_movement', 'Inventory Movement', 'chart', 'ArrowLeftRight', 'bar', NULL, NULL, 16, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(76, 'warehouse_utilization', 'Warehouse Utilization', 'chart', 'PieChart', 'pie', NULL, NULL, 17, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(77, 'category_distribution', 'Category Distribution', 'chart', 'PieChart', 'pie', NULL, NULL, 18, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(78, 'add_employee', 'Add Employee', 'quick_action', 'UserPlus', NULL, NULL, 'employee.create', 1, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(79, 'approve_leave', 'Approve Leave', 'quick_action', 'CheckCircle', NULL, NULL, 'leave.approve', 2, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(80, 'generate_payroll', 'Generate Payroll', 'quick_action', 'DollarSign', NULL, NULL, 'payroll.generate', 3, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(81, 'create_purchase_order', 'Create Purchase Order', 'quick_action', 'ShoppingCart', NULL, NULL, 'purchase-order.create', 4, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(82, 'create_sales_order', 'Create Sales Order', 'quick_action', 'Receipt', NULL, NULL, 'sales-order.create', 5, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(83, 'manage_users', 'Manage Users', 'quick_action', 'Users', NULL, NULL, 'user.view', 6, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(84, 'manage_roles', 'Manage Roles', 'quick_action', 'Shield', NULL, NULL, 'role.view', 7, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(85, 'manage_permissions', 'Manage Permissions', 'quick_action', 'Key', NULL, NULL, 'permission.view', 8, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(86, 'system_settings', 'System Settings', 'quick_action', 'Settings', NULL, NULL, NULL, 9, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(87, 'assign_site', 'Assign Site', 'quick_action', 'MapPin', NULL, NULL, 'site.assign', 10, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(88, 'upload_documents', 'Upload Documents', 'quick_action', 'Upload', NULL, NULL, NULL, 11, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(89, 'lock_payroll', 'Lock Payroll', 'quick_action', 'Lock', NULL, NULL, 'payroll.lock', 12, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(90, 'export_payslips', 'Export Payslips', 'quick_action', 'Download', NULL, NULL, NULL, 13, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(91, 'generate_payslip', 'Generate Payslip', 'quick_action', 'FileText', NULL, NULL, 'payslip.view', 14, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(92, 'export_payroll', 'Export Payroll', 'quick_action', 'DownloadCloud', NULL, NULL, NULL, 15, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(93, 'approve_dpr', 'Approve DPR', 'quick_action', 'ClipboardCheck', NULL, NULL, 'daily-report.approve', 16, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(94, 'view_team_attendance', 'View Team Attendance', 'quick_action', 'Users', NULL, NULL, NULL, 17, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(95, 'transfer_employee', 'Transfer Employee', 'quick_action', 'ArrowLeftRight', NULL, NULL, 'site.transfer', 18, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(96, 'review_attendance', 'Review Attendance', 'quick_action', 'ClipboardList', NULL, NULL, NULL, 19, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(97, 'submit_dpr', 'Submit DPR', 'quick_action', 'FilePlus', NULL, NULL, NULL, 20, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(98, 'apply_leave', 'Apply Leave', 'quick_action', 'CalendarPlus', NULL, NULL, NULL, 21, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(99, 'check_attendance', 'Check Attendance', 'quick_action', 'Fingerprint', NULL, NULL, NULL, 22, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(100, 'add_product', 'Add Product', 'quick_action', 'PackagePlus', NULL, NULL, 'product.create', 23, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(101, 'transfer_stock', 'Transfer Stock', 'quick_action', 'ArrowLeftRight', NULL, NULL, NULL, 24, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(102, 'stock_adjustment', 'Stock Adjustment', 'quick_action', 'ClipboardList', NULL, NULL, NULL, 25, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(103, 'manage_access', 'Manage Access', 'quick_action', 'Shield', NULL, NULL, NULL, 26, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(104, 'view_logs', 'View Logs', 'quick_action', 'ScrollText', NULL, NULL, NULL, 27, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(105, 'check_in', 'Check In', 'quick_action', 'LogIn', NULL, NULL, NULL, 28, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(106, 'check_out', 'Check Out', 'quick_action', 'LogOut', NULL, NULL, NULL, 29, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(107, 'download_payslip', 'Download Payslip', 'quick_action', 'Download', NULL, NULL, NULL, 30, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(108, 'low_stock_alert', 'Low Stock Alert', 'alert', 'AlertTriangle', NULL, '{\"severity\":\"warning\"}', 'inventory.view', 1, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(109, 'document_expiry_alert', 'Document Expiry Alert', 'alert', 'FileWarning', NULL, '{\"severity\":\"warning\"}', NULL, 2, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(110, 'pending_payroll_alert', 'Pending Payroll Alert', 'alert', 'Clock', NULL, '{\"severity\":\"info\"}', 'payroll.view', 3, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(111, 'absenteeism_alert', 'High Absenteeism Alert', 'alert', 'UserX', NULL, '{\"severity\":\"warning\"}', 'attendance.view', 4, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(112, 'attendance_reminder', 'Attendance Reminder', 'alert', 'Bell', NULL, '{\"severity\":\"info\"}', NULL, 5, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(113, 'dpr_reminder', 'DPR Submission Reminder', 'alert', 'Bell', NULL, '{\"severity\":\"info\"}', NULL, 6, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(114, 'leave_expiry_alert', 'Leave Balance Expiry', 'alert', 'CalendarClock', NULL, '{\"severity\":\"info\"}', NULL, 7, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(115, 'site_delays_alert', 'Site Delays', 'alert', 'Clock', NULL, '{\"severity\":\"warning\"}', NULL, 8, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(116, 'server_alert', 'Server Alert', 'alert', 'AlertCircle', NULL, '{\"severity\":\"critical\"}', NULL, 9, 1, '2026-06-18 06:14:12', '2026-06-18 06:14:12'),
(119, 'my_documents', 'My Documents', 'widget', 'FileText', NULL, NULL, NULL, 1, 1, '2026-06-25 12:17:14', '2026-06-25 12:17:14'),
(120, 'my_payslips', 'My Payslips', 'widget', 'CreditCard', NULL, NULL, NULL, 2, 1, '2026-06-25 12:17:14', '2026-06-25 12:17:14');

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_widget_designation`
--

CREATE TABLE `dashboard_widget_designation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dashboard_widget_id` bigint(20) UNSIGNED NOT NULL,
  `designation_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dashboard_widget_designation`
--

INSERT INTO `dashboard_widget_designation` (`id`, `dashboard_widget_id`, `designation_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL),
(6, 2, 1, NULL, NULL),
(7, 2, 2, NULL, NULL),
(12, 3, 1, NULL, NULL),
(13, 3, 2, NULL, NULL),
(18, 4, 1, NULL, NULL),
(19, 4, 2, NULL, NULL),
(23, 5, 1, NULL, NULL),
(24, 5, 2, NULL, NULL),
(30, 6, 1, NULL, NULL),
(37, 7, 1, NULL, NULL),
(40, 9, 1, NULL, NULL),
(41, 9, 2, NULL, NULL),
(45, 10, 1, NULL, NULL),
(46, 10, 2, NULL, NULL),
(48, 10, 4, NULL, NULL),
(51, 11, 1, NULL, NULL),
(52, 11, 2, NULL, NULL),
(56, 12, 1, NULL, NULL),
(64, 13, 1, NULL, NULL),
(67, 14, 1, NULL, NULL),
(73, 15, 1, NULL, NULL),
(78, 16, 1, NULL, NULL),
(82, 17, 1, NULL, NULL),
(86, 18, 1, NULL, NULL),
(89, 19, 1, NULL, NULL),
(92, 20, 1, NULL, NULL),
(108, 27, 7, NULL, NULL),
(109, 27, 8, NULL, NULL),
(110, 27, 9, NULL, NULL),
(112, 27, 6, NULL, NULL),
(114, 28, 7, NULL, NULL),
(115, 28, 8, NULL, NULL),
(116, 28, 9, NULL, NULL),
(118, 28, 6, NULL, NULL),
(120, 29, 7, NULL, NULL),
(121, 29, 8, NULL, NULL),
(122, 29, 9, NULL, NULL),
(124, 29, 6, NULL, NULL),
(131, 33, 7, NULL, NULL),
(132, 33, 8, NULL, NULL),
(133, 33, 9, NULL, NULL),
(135, 33, 6, NULL, NULL),
(137, 34, 7, NULL, NULL),
(138, 34, 8, NULL, NULL),
(139, 34, 9, NULL, NULL),
(141, 34, 6, NULL, NULL),
(142, 35, 7, NULL, NULL),
(143, 35, 8, NULL, NULL),
(144, 35, 9, NULL, NULL),
(146, 35, 6, NULL, NULL),
(148, 36, 7, NULL, NULL),
(149, 36, 8, NULL, NULL),
(150, 36, 9, NULL, NULL),
(152, 36, 6, NULL, NULL),
(154, 37, 7, NULL, NULL),
(155, 37, 8, NULL, NULL),
(156, 37, 9, NULL, NULL),
(158, 37, 6, NULL, NULL),
(160, 38, 7, NULL, NULL),
(161, 38, 8, NULL, NULL),
(162, 38, 9, NULL, NULL),
(164, 38, 6, NULL, NULL),
(166, 39, 7, NULL, NULL),
(167, 39, 8, NULL, NULL),
(168, 39, 9, NULL, NULL),
(170, 39, 6, NULL, NULL),
(171, 40, 7, NULL, NULL),
(172, 40, 8, NULL, NULL),
(173, 40, 9, NULL, NULL),
(175, 40, 6, NULL, NULL),
(179, 41, 1, NULL, NULL),
(182, 42, 1, NULL, NULL),
(185, 43, 1, NULL, NULL),
(188, 44, 1, NULL, NULL),
(191, 45, 1, NULL, NULL),
(194, 46, 1, NULL, NULL),
(197, 47, 1, NULL, NULL),
(200, 48, 1, NULL, NULL),
(203, 49, 1, NULL, NULL),
(216, 53, 1, NULL, NULL),
(217, 54, 1, NULL, NULL),
(223, 56, 1, NULL, NULL),
(234, 60, 1, NULL, NULL),
(238, 61, 1, NULL, NULL),
(241, 62, 1, NULL, NULL),
(244, 63, 1, NULL, NULL),
(248, 64, 1, NULL, NULL),
(252, 65, 1, NULL, NULL),
(255, 66, 1, NULL, NULL),
(259, 67, 1, NULL, NULL),
(263, 68, 1, NULL, NULL),
(267, 69, 1, NULL, NULL),
(277, 72, 1, NULL, NULL),
(280, 73, 3, NULL, NULL),
(283, 74, 3, NULL, NULL),
(290, 78, 1, NULL, NULL),
(291, 78, 2, NULL, NULL),
(294, 79, 1, NULL, NULL),
(302, 80, 1, NULL, NULL),
(310, 83, 1, NULL, NULL),
(313, 84, 1, NULL, NULL),
(316, 85, 1, NULL, NULL),
(319, 86, 1, NULL, NULL),
(323, 87, 1, NULL, NULL),
(324, 88, 7, NULL, NULL),
(325, 88, 8, NULL, NULL),
(326, 88, 9, NULL, NULL),
(328, 88, 6, NULL, NULL),
(332, 89, 1, NULL, NULL),
(350, 95, 1, NULL, NULL),
(354, 97, 7, NULL, NULL),
(355, 97, 8, NULL, NULL),
(356, 97, 9, NULL, NULL),
(358, 97, 6, NULL, NULL),
(360, 98, 7, NULL, NULL),
(361, 98, 8, NULL, NULL),
(362, 98, 9, NULL, NULL),
(364, 98, 6, NULL, NULL),
(366, 99, 7, NULL, NULL),
(367, 99, 8, NULL, NULL),
(368, 99, 9, NULL, NULL),
(370, 99, 6, NULL, NULL),
(380, 103, 1, NULL, NULL),
(383, 104, 1, NULL, NULL),
(384, 105, 7, NULL, NULL),
(385, 105, 8, NULL, NULL),
(386, 105, 9, NULL, NULL),
(388, 105, 6, NULL, NULL),
(390, 106, 7, NULL, NULL),
(391, 106, 8, NULL, NULL),
(392, 106, 9, NULL, NULL),
(394, 106, 6, NULL, NULL),
(396, 107, 7, NULL, NULL),
(397, 107, 8, NULL, NULL),
(398, 107, 9, NULL, NULL),
(400, 107, 6, NULL, NULL),
(405, 108, 1, NULL, NULL),
(408, 109, 1, NULL, NULL),
(412, 110, 1, NULL, NULL),
(417, 112, 7, NULL, NULL),
(418, 112, 8, NULL, NULL),
(419, 112, 9, NULL, NULL),
(421, 112, 6, NULL, NULL),
(423, 113, 7, NULL, NULL),
(424, 113, 8, NULL, NULL),
(425, 113, 9, NULL, NULL),
(427, 113, 6, NULL, NULL),
(429, 114, 7, NULL, NULL),
(430, 114, 8, NULL, NULL),
(431, 114, 9, NULL, NULL),
(433, 114, 6, NULL, NULL),
(440, 116, 1, NULL, NULL),
(441, 6, 10, NULL, NULL),
(442, 7, 10, NULL, NULL),
(443, 7, 2, NULL, NULL),
(444, 8, 10, NULL, NULL),
(445, 8, 2, NULL, NULL),
(446, 12, 2, NULL, NULL),
(447, 13, 2, NULL, NULL),
(448, 14, 2, NULL, NULL),
(449, 15, 2, NULL, NULL),
(450, 16, 2, NULL, NULL),
(451, 17, 4, NULL, NULL),
(452, 18, 4, NULL, NULL),
(453, 19, 2, NULL, NULL),
(454, 20, 2, NULL, NULL),
(455, 21, 2, NULL, NULL),
(456, 21, 4, NULL, NULL),
(457, 22, 4, NULL, NULL),
(458, 23, 4, NULL, NULL),
(459, 24, 2, NULL, NULL),
(460, 25, 2, NULL, NULL),
(461, 26, 2, NULL, NULL),
(462, 27, 3, NULL, NULL),
(463, 28, 3, NULL, NULL),
(464, 29, 3, NULL, NULL),
(465, 30, 2, NULL, NULL),
(466, 30, 3, NULL, NULL),
(467, 31, 2, NULL, NULL),
(468, 32, 2, NULL, NULL),
(469, 32, 3, NULL, NULL),
(470, 33, 3, NULL, NULL),
(471, 35, 3, NULL, NULL),
(472, 36, 3, NULL, NULL),
(473, 37, 3, NULL, NULL),
(474, 38, 3, NULL, NULL),
(475, 40, 3, NULL, NULL),
(476, 41, 5, NULL, NULL),
(477, 42, 5, NULL, NULL),
(478, 43, 5, NULL, NULL),
(479, 44, 5, NULL, NULL),
(480, 45, 5, NULL, NULL),
(481, 46, 5, NULL, NULL),
(482, 47, 5, NULL, NULL),
(483, 48, 5, NULL, NULL),
(484, 49, 2, NULL, NULL),
(485, 50, 2, NULL, NULL),
(486, 50, 1, NULL, NULL),
(487, 51, 2, NULL, NULL),
(488, 51, 1, NULL, NULL),
(489, 52, 2, NULL, NULL),
(490, 52, 1, NULL, NULL),
(491, 53, 2, NULL, NULL),
(492, 54, 2, NULL, NULL),
(493, 55, 2, NULL, NULL),
(494, 56, 2, NULL, NULL),
(495, 57, 10, NULL, NULL),
(496, 57, 1, NULL, NULL),
(497, 58, 10, NULL, NULL),
(498, 58, 2, NULL, NULL),
(499, 59, 5, NULL, NULL),
(500, 59, 1, NULL, NULL),
(501, 60, 2, NULL, NULL),
(502, 61, 2, NULL, NULL),
(503, 62, 10, NULL, NULL),
(504, 63, 2, NULL, NULL),
(505, 64, 2, NULL, NULL),
(506, 65, 2, NULL, NULL),
(507, 66, 2, NULL, NULL),
(508, 67, 2, NULL, NULL),
(509, 68, 2, NULL, NULL),
(510, 69, 4, NULL, NULL),
(511, 70, 2, NULL, NULL),
(512, 70, 1, NULL, NULL),
(513, 71, 2, NULL, NULL),
(514, 71, 1, NULL, NULL),
(515, 72, 2, NULL, NULL),
(516, 73, 2, NULL, NULL),
(517, 73, 1, NULL, NULL),
(518, 74, 2, NULL, NULL),
(519, 74, 1, NULL, NULL),
(520, 75, 10, NULL, NULL),
(521, 75, 1, NULL, NULL),
(522, 76, 10, NULL, NULL),
(523, 76, 1, NULL, NULL),
(524, 77, 10, NULL, NULL),
(525, 77, 1, NULL, NULL),
(526, 79, 2, NULL, NULL),
(527, 80, 2, NULL, NULL),
(528, 80, 4, NULL, NULL),
(529, 81, 10, NULL, NULL),
(530, 81, 1, NULL, NULL),
(531, 81, 2, NULL, NULL),
(532, 82, 1, NULL, NULL),
(533, 82, 2, NULL, NULL),
(534, 83, 5, NULL, NULL),
(535, 84, 5, NULL, NULL),
(536, 85, 5, NULL, NULL),
(537, 86, 5, NULL, NULL),
(538, 87, 2, NULL, NULL),
(539, 88, 3, NULL, NULL),
(540, 89, 2, NULL, NULL),
(541, 90, 4, NULL, NULL),
(542, 91, 4, NULL, NULL),
(543, 92, 2, NULL, NULL),
(544, 92, 4, NULL, NULL),
(545, 93, 2, NULL, NULL),
(546, 93, 1, NULL, NULL),
(547, 94, 2, NULL, NULL),
(548, 95, 2, NULL, NULL),
(549, 96, 2, NULL, NULL),
(550, 96, 1, NULL, NULL),
(551, 97, 3, NULL, NULL),
(552, 98, 3, NULL, NULL),
(553, 99, 3, NULL, NULL),
(554, 100, 1, NULL, NULL),
(555, 100, 10, NULL, NULL),
(556, 101, 10, NULL, NULL),
(557, 101, 1, NULL, NULL),
(558, 102, 10, NULL, NULL),
(559, 102, 1, NULL, NULL),
(560, 103, 5, NULL, NULL),
(561, 104, 5, NULL, NULL),
(562, 105, 3, NULL, NULL),
(563, 106, 3, NULL, NULL),
(564, 107, 3, NULL, NULL),
(565, 108, 10, NULL, NULL),
(566, 108, 2, NULL, NULL),
(567, 109, 2, NULL, NULL),
(568, 110, 4, NULL, NULL),
(569, 111, 2, NULL, NULL),
(570, 111, 1, NULL, NULL),
(571, 112, 3, NULL, NULL),
(572, 113, 3, NULL, NULL),
(573, 114, 3, NULL, NULL),
(574, 115, 2, NULL, NULL),
(575, 116, 5, NULL, NULL),
(576, 119, 6, NULL, NULL),
(577, 119, 7, NULL, NULL),
(578, 119, 8, NULL, NULL),
(579, 119, 9, NULL, NULL),
(580, 119, 3, NULL, NULL),
(581, 120, 6, NULL, NULL),
(582, 120, 7, NULL, NULL),
(583, 120, 8, NULL, NULL),
(584, 120, 9, NULL, NULL),
(585, 120, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_notes`
--

CREATE TABLE `delivery_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_number` varchar(255) NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `delivered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_note_items`
--

CREATE TABLE `delivery_note_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_note_id` bigint(20) UNSIGNED NOT NULL,
  `sales_order_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `delivered_quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Administration', 'Core management and administrative operations', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(2, 'Production', 'Manufacturing, fabrication, and assembly operations', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(3, 'Accounts', 'Financial management, bookkeeping, and accounting', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(4, 'IT', 'Information technology and system administration', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(5, 'Designer', 'Design, drafting, and creative development', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(6, 'Inventory & Warehouse', 'Stock management and material tracking', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(7, 'Sales', 'Sales, client acquisition, and business development', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(8, 'Other', 'For employees whose departments are not fixed', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `name`, `department_id`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 1, 'General administrative management and office operations', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(2, 'Manager', 1, 'Departmental oversight, team management, and operational supervision', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(3, 'Designer', 5, 'Design, drafting, and creative development', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(4, 'Accountant', 3, 'Financial bookkeeping, invoicing, and ledger management', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(5, 'Developer', 4, 'Software development, IT support, and system administration', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(6, 'Fitter', 2, 'Mechanical fitting, assembly, and structural alignment', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(7, 'Welder', 2, 'Welding, fabrication, and metal joining operations', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(8, 'Electrician', 2, 'Electrical installation, maintenance, and wiring', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(9, 'Helper', 2, 'General workshop assistance and manual labor support', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL),
(10, 'Store Manager', 6, 'Inventory control, stock management, and logistics', '2026-06-25 06:23:18', '2026-06-25 06:23:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'in bytes',
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `document_type` varchar(255) DEFAULT NULL COMMENT 'e.g. Resume, Offer Letter',
  `expiry_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `documentable_type` varchar(255) NOT NULL,
  `documentable_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `emp_id` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `interview_date` date DEFAULT NULL,
  `joining_date` date NOT NULL,
  `probation_end_date` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `designation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employment_type` varchar(255) NOT NULL DEFAULT 'Full-Time',
  `reporting_manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `marital_status` varchar(255) DEFAULT NULL,
  `government_id_type` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `personal_number` varchar(255) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `employment_bond_status` tinyint(1) DEFAULT NULL,
  `previous_termination_status` tinyint(1) DEFAULT NULL,
  `legal_proceedings_status` tinyint(1) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `aadhaar_path` varchar(255) DEFAULT NULL,
  `pan_path` varchar(255) DEFAULT NULL,
  `offer_letter_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `emp_id`, `full_name`, `photo_path`, `interview_date`, `joining_date`, `probation_end_date`, `dob`, `department_id`, `designation_id`, `employment_type`, `reporting_manager_id`, `gender`, `marital_status`, `government_id_type`, `address`, `contact_number`, `personal_number`, `emergency_contact`, `qualification`, `employment_bond_status`, `previous_termination_status`, `legal_proceedings_status`, `resume_path`, `aadhaar_path`, `pan_path`, `offer_letter_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 9, '000', 'Ronak Ray', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2023-06-19', 1, 1, 'Full-Time', NULL, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12th', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:18:52', '2026-06-19 23:50:48', NULL),
(10, 10, '101', 'Darshna Lade', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2023-10-10', 3, 4, 'Full-Time', 9, 'Female', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12th', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:20:33', '2026-06-22 05:04:21', NULL),
(11, 11, '102', 'shwetali bavdane', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2020-10-10', 3, 4, 'Full-Time', 9, 'Female', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:21:32', '2026-06-17 03:21:32', NULL),
(12, 12, '103', 'Rohit Ambedkar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2020-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:22:54', '2026-06-17 03:22:54', NULL),
(13, 13, '104', 'Dhurmil Darji', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 6, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:24:06', '2026-06-17 03:45:06', NULL),
(14, 14, '105', 'Sanjeevani Manjerekar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 1, 3, 'Full-Time', 9, 'Female', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:25:03', '2026-06-17 03:25:04', NULL),
(15, 15, '106', 'Mithun ghosh', 'employees/ykRCToTlDZ95KQ10octsC1Ph4wmDvHuj33LXWKJQ.jpg', '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc zy', '123546798', '123546798', '123546798', '12', 0, 0, 0, 'employees/W79OYzhqvcY8YBeRutj9KMLfqa3XkstOXgrtADi6.pdf', 'employees/fKkAwhiS0ntju9VSDk32strQoG7f8dO4USnHvk5j.pdf', 'employees/2I70YqGovURVtX9iwwe1Q5TdrE5kKdjPVnehSvPt.pdf', 'employees/9ooxbV8Vc4VSuz7PASFrB1SxtpNkyErs563rEPfg.pdf', '2026-06-17 03:58:21', '2026-06-17 03:58:21', NULL),
(16, 16, '107', 'Dileep Madhukar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:11:48', '2026-06-17 04:11:48', NULL),
(17, 17, '108', 'Rampreet Vishwakarma', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:12:51', '2026-06-17 04:12:51', NULL),
(18, 18, '109', 'ugrasen', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:13:50', '2026-06-17 04:13:50', NULL),
(19, 19, '110', 'Kishor Tare', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:15:08', '2026-06-17 04:15:08', NULL),
(20, 20, '111', 'Sarvesh Pal', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:16:15', '2026-06-17 04:16:15', NULL),
(21, 21, '112', 'Rama Shankar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:45:26', '2026-06-17 04:45:26', NULL),
(22, 22, '113', 'Ajay Saki', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 1, NULL, NULL, NULL, NULL, '2026-06-17 04:46:46', '2026-06-17 04:46:46', NULL),
(23, 23, '114', 'Bhavesh Jawale', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 1, 2, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:48:43', '2026-06-17 04:48:43', NULL),
(24, 24, '115', 'Priyotosh mondal', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:49:57', '2026-06-17 04:49:57', NULL),
(27, 25, '116', 'Santosh Mondak', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 1, NULL, NULL, NULL, NULL, '2026-06-17 05:25:07', '2026-06-17 05:25:07', NULL),
(28, 26, '117', 'Sukumar Konai', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:46:57', '2026-06-17 05:50:48', NULL),
(29, 27, '118', 'Sahadeb konai', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:51:48', '2026-06-17 05:51:48', NULL),
(30, 28, '119', 'Sujan Hansda', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2020-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'ab c xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:52:34', '2026-06-17 05:52:34', NULL),
(31, 29, '120', 'Rintu Ghosh', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:53:26', '2026-06-17 05:53:26', NULL),
(32, 30, '121', 'Nayan Kahar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-02-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xtyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:54:23', '2026-06-17 05:54:24', NULL),
(33, 31, '122', 'Nilkumar Aditya', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:55:25', '2026-06-17 05:55:25', NULL),
(34, 32, '123', 'Jayanta Kahar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'PAN', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 1, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:56:35', '2026-06-17 05:56:36', NULL),
(35, 33, '124', 'Raj Kumar Aditya', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:57:33', '2026-06-17 05:57:33', NULL),
(36, 34, '125', 'Raj Aditya', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', NULL, '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:58:28', '2026-06-17 05:58:28', NULL),
(37, 35, '126', 'Subodh Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:00:05', '2026-06-17 06:00:05', NULL),
(38, 36, '127', 'Hemant Kahar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:01:33', '2026-06-17 06:01:33', NULL),
(39, 37, '128', 'Jeet Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:07:05', '2026-06-17 06:07:05', NULL),
(40, 38, '129', 'Saheb Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 1, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:40:09', '2026-06-17 06:40:09', NULL),
(41, 39, '130', 'Ramkrishna Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:41:04', '2026-06-17 06:41:04', NULL),
(42, 40, '131', 'Biswanath Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 1, 1, NULL, NULL, NULL, NULL, '2026-06-17 06:41:58', '2026-06-17 06:41:58', NULL),
(43, 41, '132', 'Bappa Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 1, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:42:54', '2026-06-17 06:42:54', NULL),
(44, 42, '133', 'Jiban Pal', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 6, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:43:44', '2026-06-17 06:43:44', NULL),
(45, 43, '134', 'Bikash Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 1, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:44:51', '2026-06-17 06:44:51', NULL),
(46, 44, '135', 'Rahul Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:46:09', '2026-06-17 06:46:09', NULL),
(47, 45, '136', 'Suvendu Fouzder', NULL, '2010-10-10', '2010-10-21', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:47:31', '2026-06-17 06:47:31', NULL),
(48, 46, '137', 'Sanjeet Mondal', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 6, 'Full-Time', 9, 'Male', 'Single', 'PAN', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:51:28', '2026-06-17 06:51:28', NULL),
(49, 47, '138', 'Jiban Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:52:23', '2026-06-17 06:52:24', NULL),
(50, 48, '139', 'Bishwajit Bholla', NULL, '2010-10-10', '2010-10-10', '2010-02-10', '2010-10-10', 7, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:53:30', '2026-06-17 06:53:31', NULL),
(51, 49, '140', 'Shib Shankar Roy', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:54:20', '2026-06-17 06:54:20', NULL),
(52, 50, '141', 'Aakash Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:55:18', '2026-06-17 06:55:18', NULL),
(53, 51, '142', 'Suvendu Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:56:24', '2026-06-17 06:56:24', NULL),
(54, 52, '143', 'Palash Majhi', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:57:27', '2026-06-17 06:57:27', NULL),
(55, 53, '144', 'Gopeshwar Mondal', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:58:16', '2026-06-17 06:58:17', NULL),
(56, 54, '145', 'Prasad Kalvikatti', NULL, '2026-05-27', '2026-06-15', '2026-09-15', '2003-10-02', 4, 1, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '9022428111', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:59:45', '2026-06-19 06:11:22', NULL),
(57, 55, '1010', 'test user', 'employees/YnaClsxS9FovTWmmMPFXK4X7j1QOlWAymDeuh8qa.png', '2026-01-01', '2026-01-01', '2026-01-01', '2005-02-10', 1, 1, 'Full-Time', 9, 'Male', 'Married', 'Aadhaar', 'abc xyz', '1478523690', '4567891230', '1324567890', 'yes', 0, 0, 0, 'employees/m2DSBk8caomLo2YzMo617vE20x4bUp3vRzFaBmuC.pdf', 'employees/FDxfTihz1wgXmAsTskmKbk1PzGpRsogA1ZmXLXm0.pdf', 'employees/yXpSRCjN2OERpE13z24sb7jn2QrmZgWszibenfhE.pdf', 'employees/CiN60JsqvqsFihsGdhDMokqh0QXEkpr58ABVLRbk.pdf', '2026-06-19 06:35:05', '2026-06-25 06:37:20', '2026-06-25 06:37:20'),
(58, 1, 'EMP-001', 'Admin', NULL, NULL, '2026-06-20', NULL, NULL, NULL, NULL, 'Full-Time', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-19 23:35:26', '2026-06-19 23:35:26', NULL),
(59, 2, 'EMP-002', 'Super Admin', NULL, NULL, '2026-06-20', NULL, NULL, NULL, NULL, 'Full-Time', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-19 23:35:26', '2026-06-19 23:35:26', NULL),
(60, 3, 'EMP-003', 'System Admin', NULL, NULL, '2026-06-20', NULL, NULL, NULL, NULL, 'Full-Time', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-19 23:35:26', '2026-06-19 23:35:26', NULL),
(61, 56, '146', 'Chandradeb Mondal', NULL, '2010-10-10', '2010-02-10', '2020-02-10', '2010-10-10', 1, 2, 'Full-Time', 9, 'Male', 'Married', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-25 05:45:52', '2026-06-25 05:45:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_leave_balances`
--

CREATE TABLE `employee_leave_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `allocated` decimal(5,2) NOT NULL DEFAULT 0.00,
  `used` decimal(5,2) NOT NULL DEFAULT 0.00,
  `remaining` decimal(5,2) NOT NULL DEFAULT 0.00,
  `carry_forward` decimal(5,2) NOT NULL DEFAULT 0.00,
  `expired` decimal(5,2) NOT NULL DEFAULT 0.00,
  `year` year(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_leave_balances`
--

INSERT INTO `employee_leave_balances` (`id`, `employee_id`, `leave_type_id`, `allocated`, `used`, `remaining`, `carry_forward`, `expired`, `year`, `created_at`, `updated_at`) VALUES
(1, 9, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(2, 9, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(3, 9, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(4, 9, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(5, 10, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(6, 10, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(7, 10, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(8, 10, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(9, 11, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(10, 11, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(11, 11, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(12, 11, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(13, 12, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(14, 12, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(15, 12, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(16, 12, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(17, 13, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(18, 13, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(19, 13, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(20, 13, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(21, 14, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(22, 14, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(23, 14, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(24, 14, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(25, 15, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(26, 15, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(27, 15, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(28, 15, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(29, 16, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(30, 16, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(31, 16, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(32, 16, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(33, 17, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(34, 17, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(35, 17, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(36, 17, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(37, 18, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(38, 18, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(39, 18, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(40, 18, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(41, 19, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(42, 19, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(43, 19, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(44, 19, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(45, 20, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(46, 20, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(47, 20, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(48, 20, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(49, 21, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(50, 21, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(51, 21, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(52, 21, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(53, 22, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(54, 22, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(55, 22, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(56, 22, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(57, 23, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(58, 23, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(59, 23, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(60, 23, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(61, 24, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(62, 24, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(63, 24, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(64, 24, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(65, 27, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(66, 27, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(67, 27, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(68, 27, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(69, 28, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(70, 28, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(71, 28, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(72, 28, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(73, 29, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(74, 29, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(75, 29, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(76, 29, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(77, 30, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(78, 30, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(79, 30, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(80, 30, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(81, 31, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(82, 31, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(83, 31, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(84, 31, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(85, 32, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(86, 32, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(87, 32, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(88, 32, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(89, 33, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(90, 33, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(91, 33, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(92, 33, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(93, 34, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(94, 34, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(95, 34, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(96, 34, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(97, 35, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(98, 35, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(99, 35, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(100, 35, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(101, 36, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(102, 36, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(103, 36, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(104, 36, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(105, 37, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(106, 37, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(107, 37, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(108, 37, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(109, 38, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(110, 38, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(111, 38, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(112, 38, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(113, 39, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(114, 39, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(115, 39, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(116, 39, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(117, 40, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(118, 40, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(119, 40, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(120, 40, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(121, 41, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(122, 41, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(123, 41, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(124, 41, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(125, 42, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(126, 42, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(127, 42, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(128, 42, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(129, 43, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(130, 43, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(131, 43, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(132, 43, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(133, 44, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(134, 44, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(135, 44, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(136, 44, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(137, 45, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(138, 45, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(139, 45, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(140, 45, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(141, 46, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(142, 46, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(143, 46, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(144, 46, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(145, 47, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(146, 47, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(147, 47, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(148, 47, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(149, 48, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(150, 48, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(151, 48, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(152, 48, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:34', '2026-06-22 07:01:34'),
(153, 49, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(154, 49, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(155, 49, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(156, 49, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(157, 50, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(158, 50, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(159, 50, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(160, 50, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(161, 51, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(162, 51, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(163, 51, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(164, 51, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(165, 52, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(166, 52, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(167, 52, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(168, 52, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(169, 53, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(170, 53, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(171, 53, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(172, 53, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(173, 54, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(174, 54, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(175, 54, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(176, 54, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(177, 55, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(178, 55, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(179, 55, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(180, 55, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(181, 56, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(182, 56, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(183, 56, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(184, 56, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(185, 57, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(186, 57, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(187, 57, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(188, 57, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(189, 58, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(190, 58, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(191, 58, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(192, 58, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(193, 59, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(194, 59, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(195, 59, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(196, 59, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(197, 60, 1, 21.00, 0.00, 21.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(198, 60, 2, 10.00, 0.00, 10.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(199, 60, 3, 7.00, 0.00, 7.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35'),
(200, 60, 4, 90.00, 0.00, 90.00, 0.00, 0.00, '2026', '2026-06-22 07:01:35', '2026-06-22 07:01:35');

-- --------------------------------------------------------

--
-- Table structure for table `employee_sites`
--

CREATE TABLE `employee_sites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `site_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` date NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_sites`
--

INSERT INTO `employee_sites` (`id`, `employee_id`, `site_id`, `assigned_at`, `role`, `created_at`, `updated_at`) VALUES
(10, 9, 4, '2026-06-20', NULL, '2026-06-20 00:14:22', '2026-06-20 00:14:22'),
(12, 10, 4, '2026-06-23', NULL, '2026-06-23 05:41:44', '2026-06-23 05:41:44');

-- --------------------------------------------------------

--
-- Table structure for table `employee_site_histories`
--

CREATE TABLE `employee_site_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `previous_site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `new_site_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `transfer_date` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_site_histories`
--

INSERT INTO `employee_site_histories` (`id`, `employee_id`, `assigned_by_id`, `previous_site_id`, `new_site_id`, `assigned_at`, `transfer_date`, `remarks`, `created_at`, `updated_at`) VALUES
(3, 14, 3, NULL, NULL, '2026-06-17 03:59:43', NULL, 'Assigned to site', '2026-06-17 03:59:43', '2026-06-17 03:59:43'),
(4, 14, 3, NULL, NULL, '2026-06-17 03:59:58', NULL, 'abc', '2026-06-17 03:59:58', '2026-06-17 03:59:58'),
(5, 14, 3, NULL, NULL, '2026-06-17 04:00:17', NULL, 'Assigned to site', '2026-06-17 04:00:17', '2026-06-17 04:00:17'),
(6, 9, 3, NULL, NULL, '2026-06-17 07:37:01', NULL, 'Assigned to site', '2026-06-17 07:37:01', '2026-06-17 07:37:01'),
(7, 9, 3, NULL, NULL, '2026-06-17 07:37:51', NULL, 'Assigned to site', '2026-06-17 07:37:51', '2026-06-17 07:37:51'),
(8, 9, 3, NULL, NULL, '2026-06-17 07:38:24', NULL, 'aqbc sa as das', '2026-06-17 07:38:24', '2026-06-17 07:38:24'),
(9, 56, 3, NULL, NULL, '2026-06-18 00:25:31', NULL, 'Assigned to site', '2026-06-18 00:25:31', '2026-06-18 00:25:31'),
(10, 10, 3, NULL, NULL, '2026-06-18 00:26:15', NULL, 'Assigned to site', '2026-06-18 00:26:15', '2026-06-18 00:26:15'),
(11, 57, 3, NULL, NULL, '2026-06-19 06:35:17', NULL, 'Assigned to site', '2026-06-19 06:35:17', '2026-06-19 06:35:17'),
(12, 57, 3, NULL, NULL, '2026-06-19 06:35:25', '2026-06-19 06:35:25', 'pqr', '2026-06-19 06:35:25', '2026-06-19 06:35:25'),
(13, 57, 3, NULL, NULL, '2026-06-19 06:39:11', NULL, 'qwqe', '2026-06-19 06:39:11', '2026-06-19 06:39:11'),
(14, 9, 9, NULL, 4, '2026-06-20 00:14:22', NULL, 'Assigned to site', '2026-06-20 00:14:22', '2026-06-20 00:14:22'),
(15, 10, 9, NULL, 5, '2026-06-22 05:04:28', NULL, 'Assigned to site', '2026-06-22 05:04:28', '2026-06-22 05:04:28'),
(16, 10, 3, 5, 4, '2026-06-23 05:41:44', '2026-06-23 05:41:44', 'abc', '2026-06-23 05:41:44', '2026-06-23 05:41:44');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipt_notes`
--

CREATE TABLE `goods_receipt_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `grn_number` varchar(255) NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `receipt_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grn_items`
--

CREATE TABLE `grn_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `goods_receipt_note_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `received_quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Public Holiday',
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(2, 1, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(3, 1, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(4, 1, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(5, 2, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(6, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(7, 2, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(8, 2, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(9, 3, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(10, 3, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(11, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(12, 3, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(1, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(2, 1, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(3, 1, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(4, 1, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(5, 2, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(6, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(7, 2, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(8, 2, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(9, 3, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(10, 3, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(11, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(12, 3, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(1, 1, 10, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(2, 1, 20, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(3, 1, 30, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(4, 1, 40, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(5, 2, 10, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(6, 2, 20, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(7, 2, 30, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(8, 2, 40, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(9, 3, 10, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(10, 3, 20, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(11, 3, 30, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(12, 3, 40, '2026-06-12 21:34:53', '2026-06-12 21:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transfers`
--

CREATE TABLE `inventory_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `pdf_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst_type` varchar(255) DEFAULT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `payment_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `sales_order_id`, `invoice_number`, `issue_date`, `due_date`, `total_amount`, `pdf_url`, `created_at`, `updated_at`, `customer_id`, `subtotal`, `cgst_total`, `sgst_total`, `igst_total`, `gst_type`, `gst_rate`, `grand_total`, `status`, `payment_date`, `paid_amount`, `notes`, `terms`, `created_by`) VALUES
(4, NULL, 'RFI-AB-0001', '2026-06-22', '2026-06-22', 5900.00, NULL, '2026-06-22 10:53:57', '2026-06-22 10:53:57', 2, 5000.00, 450.00, 450.00, 0.00, 'cgst', 18.00, 5900.00, 'Paid', NULL, 0.00, NULL, '1. Payment is due within 30 days.\n2. Goods once sold will not be taken back.', 9);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_description` varchar(255) NOT NULL,
  `hsn_code` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `item_description`, `hsn_code`, `quantity`, `unit_price`, `discount`, `total`, `created_at`, `updated_at`) VALUES
(2, 3, 3, 'sprinkler', 'abc123', 12, 200.00, 0.00, 2400.00, '2026-06-22 09:42:49', '2026-06-22 09:42:49'),
(3, 4, 3, 'sprinkler', 'abc123', 25, 200.00, 0.00, 5000.00, '2026-06-22 10:53:57', '2026-06-22 10:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` decimal(5,2) NOT NULL DEFAULT 1.00,
  `is_half_day` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `is_half_day`, `status`, `approved_by`, `reason`, `attachment_path`, `comments`, `applied_at`, `created_at`, `updated_at`) VALUES
(1, 10, 3, '2026-06-18', '2026-06-18', 1.00, 0, 'Submitted', NULL, 'abc xyz', NULL, NULL, '2026-06-18 04:04:39', '2026-06-18 04:04:39', '2026-06-18 04:04:39'),
(2, 10, 3, '2026-06-18', '2026-06-18', 1.00, 0, 'Cancelled', NULL, 'abc xyz', NULL, '111', '2026-06-18 04:07:18', '2026-06-18 04:07:18', '2026-06-23 05:41:10'),
(3, 9, 1, '2026-06-20', '2026-06-20', 1.00, 0, 'Submitted', NULL, 'HEHE', NULL, NULL, '2026-06-20 07:24:59', '2026-06-20 07:24:59', '2026-06-20 07:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `leave_histories`
--

CREATE TABLE `leave_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `leave_id` bigint(20) UNSIGNED NOT NULL,
  `action_by` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_histories`
--

INSERT INTO `leave_histories` (`id`, `leave_id`, `action_by`, `action`, `comments`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Submitted', NULL, '2026-06-18 04:04:39', '2026-06-18 04:04:39'),
(2, 2, 3, 'Submitted', NULL, '2026-06-18 04:07:18', '2026-06-18 04:07:18'),
(3, 3, 9, 'Submitted', NULL, '2026-06-20 07:24:59', '2026-06-20 07:24:59'),
(4, 2, 10, 'Cancelled', '111', '2026-06-23 05:41:10', '2026-06-23 05:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `annual_allocation` decimal(5,2) NOT NULL DEFAULT 0.00,
  `carry_forward` tinyint(1) NOT NULL DEFAULT 0,
  `max_consecutive_days` int(11) DEFAULT NULL,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `code`, `annual_allocation`, `carry_forward`, `max_consecutive_days`, `requires_approval`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'AL', 21.00, 1, 14, 1, 'active', '2026-06-18 09:32:36', '2026-06-18 09:32:36'),
(2, 'Sick Leave', 'SL', 10.00, 0, 3, 0, 'active', '2026-06-18 09:32:36', '2026-06-18 09:32:36'),
(3, 'Casual Leave', 'CL', 7.00, 0, 2, 1, 'active', '2026-06-18 09:32:36', '2026-06-18 09:32:36'),
(4, 'Maternity Leave', 'ML', 90.00, 0, 90, 1, 'active', '2026-06-18 09:32:36', '2026-06-18 09:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_15_102110_create_personal_access_tokens_table', 1),
(5, '2026_06_15_105602_create_departments_table', 1),
(6, '2026_06_15_105603_create_designations_table', 1),
(7, '2026_06_15_105603_create_employees_table', 1),
(8, '2026_06_15_105604_create_sites_table', 1),
(9, '2026_06_15_105605_create_attendances_table', 1),
(10, '2026_06_15_105606_create_leaves_table', 1),
(11, '2026_06_15_105606_create_payrolls_table', 1),
(12, '2026_06_15_105607_create_daily_reports_table', 1),
(13, '2026_06_15_105608_create_employee_sites_table', 1),
(24, '0001_01_01_000000_create_users_table', 1),
(25, '0001_01_01_000001_create_cache_table', 1),
(26, '0001_01_01_000002_create_jobs_table', 1),
(27, '2026_05_30_083426_create_personal_access_tokens_table', 1),
(28, '2026_05_30_083453_create_permission_tables', 1),
(29, '2026_05_30_083454_create_branches_table', 1),
(30, '2026_05_30_083454_create_employees_table', 1),
(31, '2026_05_30_083504_add_branch_id_to_users_table', 1),
(32, '2026_05_30_090647_create_attendances_table', 1),
(33, '2026_05_30_093033_create_categories_table', 1),
(34, '2026_05_30_093033_create_products_table', 1),
(35, '2026_05_30_093034_create_inventories_table', 1),
(36, '2026_05_30_093034_create_inventory_transactions_table', 1),
(37, '2026_06_06_085506_create_suppliers_table', 1),
(38, '2026_06_06_085507_create_customers_table', 1),
(39, '2026_06_06_085508_create_purchase_orders_table', 1),
(40, '2026_06_06_085509_create_sales_orders_table', 1),
(41, '2026_06_06_085509z_create_sales_order_items_table', 1),
(42, '2026_06_06_085510_create_invoices_table', 1),
(43, '2026_06_06_085511_create_payments_table', 1),
(44, '2026_06_06_085512_create_leaves_table', 1),
(45, '2026_06_06_085512_create_tasks_table', 1),
(46, '2026_06_06_085513_create_payrolls_table', 1),
(47, '2026_06_13_075512_add_branch_id_to_operational_tables', 2),
(48, '2026_06_13_075522_create_inventory_transfers_table', 2),
(49, '2026_06_14_075512_create_purchase_and_sales_tables', 3),
(50, '2026_06_14_174246_add_supplier_and_po_to_payments_table', 4),
(51, '2026_06_15_051017_create_notes_table', 5),
(52, '2026_06_15_051022_create_documents_table', 5),
(53, '2026_06_15_055437_alter_categories_table', 6),
(54, '2026_06_15_055444_alter_invoices_table', 7),
(55, '2026_06_15_055450_create_invoice_items_table', 7),
(56, '2026_06_15_055456_create_category_product_table', 7),
(57, '2026_06_16_000001_create_sites_table', 8),
(58, '2026_06_16_000002_create_employee_sites_table', 8),
(59, '2026_06_16_000003_create_daily_reports_table', 8),
(60, '2026_06_16_000004_create_departments_table', 8),
(61, '2026_06_16_000005_create_designations_table', 8),
(62, '2026_06_16_000006_add_business_relationship_columns', 9),
(63, '2026_06_17_044104_add_metadata_to_documents_table', 10),
(64, '2026_06_17_065624_add_details_to_sites_table', 11),
(65, '2026_06_17_065624_create_employee_site_histories_table', 11),
(66, '2026_06_17_071821_rename_columns_in_employee_sites_table', 12),
(67, '2026_06_17_072006_make_role_column_nullable_in_employee_sites_table', 13),
(68, '2026_06_17_074528_create_shifts_table', 14),
(69, '2026_06_17_074532_add_details_to_attendances_table', 15),
(70, '2026_06_17_074641_add_branch_id_to_attendances_if_missing', 16),
(71, '2026_06_17_074713_add_branch_id_to_shifts_table', 17),
(72, '2026_06_17_104109_make_employee_status_fields_nullable_in_employees_table', 18),
(73, '2026_06_17_124110_alter_daily_reports_table', 19),
(74, '2026_06_17_124110_create_daily_report_histories_table', 19),
(75, '2026_06_17_125209_create_leave_types_table', 20),
(76, '2026_06_17_125210_create_employee_leave_balances_table', 21),
(77, '2026_06_17_125211_alter_leaves_table_for_leave_management', 21),
(78, '2026_06_17_125212_create_leave_histories_table', 21),
(79, '2026_06_17_125213_create_holidays_table', 21),
(80, '2026_06_17_170337_create_salary_structures_table', 22),
(81, '2026_06_17_170537_create_payroll_periods_table', 22),
(82, '2026_06_17_170710_add_details_to_payrolls_table', 22),
(83, '2026_06_17_170915_create_payslips_table', 22),
(84, '2026_06_18_000001_add_allowed_radius_to_sites_table', 23),
(85, '2026_06_18_000002_add_gps_fields_to_attendances_table', 23),
(86, '2026_06_18_070751_add_geo_fields_to_tables', 24),
(87, '2026_06_18_080000_create_dashboard_widgets_table', 25),
(88, '2026_06_19_000001_create_inventory_locations_table', 26),
(89, '2026_06_19_000002_create_units_table', 26),
(90, '2026_06_19_000003_create_unit_conversions_table', 26),
(91, '2026_06_19_000004_alter_products_table', 26),
(92, '2026_06_19_000005_create_product_stock_table', 27),
(93, '2026_06_19_000006_create_transaction_ledger_table', 27),
(94, '2026_06_19_000007_create_stock_requests_table', 27),
(95, '2026_06_19_000008_alter_stock_tables_for_polymorphic_locations', 28),
(96, '2026_06_19_000009_fix_product_stock_unique_key', 29),
(97, '2026_06_19_000010_remove_soft_deletes_from_documents_table', 30),
(98, '2026_06_19_000011_add_opening_stock_and_in_storage_supplier', 31),
(99, '2026_06_19_000012_fix_suppliers_table_primary_key', 31),
(100, '2026_06_19_000013_insert_in_storage_supplier', 31),
(101, '2026_06_19_000014_move_gst_to_header_level', 32),
(102, '2026_06_20_170513_add_branch_id_to_payrolls_table', 32),
(103, '2026_06_20_174233_remove_branches', 33),
(104, '2026_06_20_180957_fix_products_id_auto_increment', 34),
(105, '2026_06_22_114701_add_hsn_gst_and_shipping_to_purchase_tables', 35),
(106, '2026_06_22_115806_fix_purchase_order_items_id_auto_increment', 36),
(107, '2026_06_22_121500_add_hsn_gst_and_shipping_to_sales_tables', 37),
(108, '2026_06_22_130000_create_report_categories_table', 38),
(109, '2026_06_22_130001_create_reports_table', 38),
(110, '2026_06_22_130002_create_report_schedules_table', 38),
(111, '2026_06_22_130003_create_report_generations_table', 38),
(112, '2026_06_22_140000_create_company_settings_table', 39),
(113, '2026_06_22_150000_fix_sales_tables_id_auto_increment', 40),
(114, '2026_06_22_170822_create_permission_scopes_table', 41),
(115, '2026_06_22_170827_create_role_templates_table', 41),
(116, '2026_06_22_170832_create_system_exceptions_table', 41),
(117, '2026_06_22_170836_create_audit_settings_table', 41),
(118, '2026_06_22_170841_create_permission_dependencies_table', 41),
(119, '2026_06_22_180558_create_push_subscriptions_table', 42),
(120, '2026_06_25_000001_update_departments_and_designations_data', 43),
(121, '2026_06_25_000002_fix_employee_references_and_descriptions', 44),
(122, '2026_06_25_000003_add_start_end_date_to_sites_table', 45),
(124, '2026_06_25_000001_add_receipt_delivery_fields_to_orders', 46),
(125, '2026_06_25_000004_allow_custom_products_in_order_items', 46);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 104),
(2, 'App\\Models\\User', 104),
(4, 'App\\Models\\User', 104),
(5, 'App\\Models\\User', 104),
(8, 'App\\Models\\User', 104),
(10, 'App\\Models\\User', 104),
(12, 'App\\Models\\User', 104),
(14, 'App\\Models\\User', 104),
(15, 'App\\Models\\User', 104),
(1, 'App\\Models\\User', 104),
(2, 'App\\Models\\User', 104),
(4, 'App\\Models\\User', 104),
(5, 'App\\Models\\User', 104),
(8, 'App\\Models\\User', 104),
(10, 'App\\Models\\User', 104),
(12, 'App\\Models\\User', 104),
(14, 'App\\Models\\User', 104),
(15, 'App\\Models\\User', 104),
(1, 'App\\Models\\User', 104),
(2, 'App\\Models\\User', 104),
(4, 'App\\Models\\User', 104),
(5, 'App\\Models\\User', 104),
(8, 'App\\Models\\User', 104),
(10, 'App\\Models\\User', 104),
(12, 'App\\Models\\User', 104),
(14, 'App\\Models\\User', 104),
(15, 'App\\Models\\User', 104);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 103),
(4, 'App\\Models\\User', 105),
(4, 'App\\Models\\User', 107),
(4, 'App\\Models\\User', 109),
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 103),
(4, 'App\\Models\\User', 105),
(4, 'App\\Models\\User', 107),
(4, 'App\\Models\\User', 109),
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 103),
(4, 'App\\Models\\User', 105),
(4, 'App\\Models\\User', 107),
(4, 'App\\Models\\User', 109),
(2, 'App\\Models\\User', 3),
(5, 'App\\Models\\User', 4),
(5, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 7),
(5, 'App\\Models\\User', 8),
(18, 'App\\Models\\User', 10),
(6, 'App\\Models\\User', 11),
(21, 'App\\Models\\User', 12),
(14, 'App\\Models\\User', 13),
(19, 'App\\Models\\User', 14),
(21, 'App\\Models\\User', 15),
(15, 'App\\Models\\User', 16),
(17, 'App\\Models\\User', 17),
(15, 'App\\Models\\User', 18),
(16, 'App\\Models\\User', 19),
(16, 'App\\Models\\User', 20),
(16, 'App\\Models\\User', 21),
(15, 'App\\Models\\User', 22),
(13, 'App\\Models\\User', 23),
(21, 'App\\Models\\User', 24),
(21, 'App\\Models\\User', 25),
(21, 'App\\Models\\User', 26),
(21, 'App\\Models\\User', 27),
(21, 'App\\Models\\User', 28),
(17, 'App\\Models\\User', 29),
(15, 'App\\Models\\User', 30),
(21, 'App\\Models\\User', 31),
(15, 'App\\Models\\User', 32),
(17, 'App\\Models\\User', 33),
(17, 'App\\Models\\User', 34),
(21, 'App\\Models\\User', 35),
(15, 'App\\Models\\User', 36),
(21, 'App\\Models\\User', 37),
(15, 'App\\Models\\User', 38),
(17, 'App\\Models\\User', 39),
(21, 'App\\Models\\User', 40),
(15, 'App\\Models\\User', 41),
(14, 'App\\Models\\User', 42),
(21, 'App\\Models\\User', 43),
(21, 'App\\Models\\User', 44),
(15, 'App\\Models\\User', 45),
(14, 'App\\Models\\User', 46),
(15, 'App\\Models\\User', 47),
(21, 'App\\Models\\User', 48),
(17, 'App\\Models\\User', 49),
(17, 'App\\Models\\User', 50),
(17, 'App\\Models\\User', 51),
(17, 'App\\Models\\User', 52),
(17, 'App\\Models\\User', 53),
(11, 'App\\Models\\User', 56),
(2, 'App\\Models\\User', 9);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `notable_type` varchar(255) NOT NULL,
  `notable_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Payable',
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sales_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overtime_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pf` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pt` decimal(10,2) NOT NULL DEFAULT 0.00,
  `salary_advance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loss_of_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payroll_period_id` bigint(20) UNSIGNED DEFAULT NULL,
  `present_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `absent_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `paid_leaves` decimal(5,2) NOT NULL DEFAULT 0.00,
  `unpaid_leaves` decimal(5,2) NOT NULL DEFAULT 0.00,
  `working_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `conveyance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `medical_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `special_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `site_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `travel_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `food_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `esic` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tds` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `late_penalty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonuses` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `employee_id`, `month`, `year`, `basic_salary`, `hra`, `other_allowance`, `overtime_pay`, `pf`, `pt`, `salary_advance`, `loss_of_pay`, `net_salary`, `status`, `created_at`, `updated_at`, `payroll_period_id`, `present_days`, `absent_days`, `paid_leaves`, `unpaid_leaves`, `working_days`, `conveyance`, `medical_allowance`, `special_allowance`, `site_allowance`, `travel_allowance`, `food_allowance`, `esic`, `tds`, `other_deductions`, `late_penalty`, `bonuses`) VALUES
(3, 9, 6, 2026, 25000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 25000.00, 'Approved', '2026-06-20 12:05:43', '2026-06-20 12:06:00', 2, 0.00, 0.00, 0.00, 0.00, 1.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_periods`
--

INSERT INTO `payroll_periods` (`id`, `month`, `year`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 2026, '2026-06-20', '2026-06-30', 'Approved', '2026-06-17 23:29:15', '2026-06-20 10:57:18', '2026-06-20 10:57:18'),
(2, 6, 2026, '2026-06-16', '2026-06-16', 'Processing', '2026-06-20 10:57:48', '2026-06-22 05:59:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`id`, `payroll_id`, `file_path`, `generated_at`, `created_at`, `updated_at`) VALUES
(2, 3, NULL, '2026-06-20 12:08:52', '2026-06-20 12:08:52', '2026-06-20 12:08:52');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'create sales', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(2, 'manage employees', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(3, 'manage inventory', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(4, 'manage payroll', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(5, 'manage purchases', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(6, 'manage sales', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(7, 'manage settings', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(8, 'manage suppliers', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(9, 'manage transfers', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(10, 'view attendance', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(11, 'view categories', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(12, 'view dashboard', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(13, 'view inventory', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(14, 'view invoices', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(15, 'view products', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(16, 'view reports', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(17, 'view warehouses', 'web', '2026-06-16 11:31:52', '2026-06-16 11:31:52', NULL),
(18, 'view_employees', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(19, 'create_employees', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(20, 'update_employees', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(21, 'delete_employees', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(22, 'view_products', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(23, 'create_products', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(24, 'update_products', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(25, 'delete_products', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(26, 'view_customers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(27, 'create_customers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(28, 'update_customers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(29, 'delete_customers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(30, 'view_suppliers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(31, 'create_suppliers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(32, 'update_suppliers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(33, 'delete_suppliers', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(34, 'view_sites', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(35, 'create_sites', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(36, 'update_sites', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(37, 'delete_sites', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(38, 'department.view', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(39, 'department.create', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(40, 'department.edit', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(41, 'department.delete', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(42, 'designation.view', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(43, 'designation.create', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(44, 'designation.edit', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(45, 'designation.delete', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(46, 'employee.view', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(47, 'employee.create', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(48, 'employee.edit', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(49, 'employee.delete', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(50, 'employee.assign-manager', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(51, 'employee.assign-role', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(52, 'view_categories', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(53, 'create_categories', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(54, 'update_categories', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(55, 'delete_categories', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(56, 'view_attendance', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(57, 'create_attendance', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(58, 'update_attendance', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(59, 'view_inventory', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(60, 'manage_inventory', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(61, 'view_invoices', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(62, 'create_invoices', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(63, 'update_invoices', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(64, 'delete_invoices', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(65, 'view_payments', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(66, 'create_payments', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(67, 'view_payroll', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(68, 'manage_payroll', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(69, 'view_leaves', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(70, 'create_leaves', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(71, 'view_sales_orders', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(72, 'create_sales_orders', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(73, 'view_purchase_orders', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(74, 'create_purchase_orders', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(75, 'manage_roles', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(76, 'manage_permissions', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(77, 'manage_users', 'web', '2026-06-16 23:01:47', '2026-06-16 23:01:47', NULL),
(78, 'document.view', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(79, 'document.create', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(80, 'document.edit', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(81, 'document.delete', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(82, 'document.download', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(83, 'document.preview', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(84, 'document.manage-expiry', 'web', '2026-06-16 23:14:15', '2026-06-16 23:14:15', NULL),
(85, 'site.view', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(86, 'site.create', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(87, 'site.edit', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(88, 'site.delete', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(89, 'site.assign', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(90, 'site.transfer', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(91, 'site.history.view', 'web', '2026-06-17 01:33:53', '2026-06-17 01:33:53', NULL),
(92, 'delete_attendance', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(93, 'attendance.view', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(94, 'attendance.create', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(95, 'attendance.edit', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(96, 'attendance.delete', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(97, 'attendance.checkin', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(98, 'attendance.checkout', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(99, 'shift.view', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(100, 'shift.create', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(101, 'shift.edit', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(102, 'shift.delete', 'web', '2026-06-17 05:35:00', '2026-06-17 05:35:00', NULL),
(103, 'daily-report.view', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(104, 'daily-report.create', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(105, 'daily-report.edit', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(106, 'daily-report.delete', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(107, 'daily-report.submit', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(108, 'daily-report.approve', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(109, 'daily-report.reject', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(110, 'daily-report.rework', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(111, 'daily-report.report.view', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL),
(112, 'leave.view', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(113, 'leave.create', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(114, 'leave.edit', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(115, 'leave.delete', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(116, 'leave.approve', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(117, 'leave.reject', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(118, 'leave.cancel', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(119, 'leave.balance.view', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(120, 'leave-type.view', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(121, 'leave-type.create', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(122, 'leave-type.edit', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(123, 'leave-type.delete', 'web', '2026-06-17 07:26:17', '2026-06-17 07:26:17', NULL),
(124, 'user.view', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(125, 'user.create', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(126, 'user.edit', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(127, 'user.delete', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(128, 'role.view', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(129, 'role.create', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(130, 'role.edit', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(131, 'role.delete', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(132, 'permission.view', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(133, 'permission.create', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(134, 'permission.edit', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(135, 'permission.delete', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(136, 'inventory.view', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(137, 'inventory.create', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(138, 'inventory.edit', 'web', '2026-06-18 00:47:32', '2026-06-18 00:47:32', NULL),
(139, 'inventory.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(140, 'warehouse.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(141, 'warehouse.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(142, 'warehouse.edit', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(143, 'warehouse.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(144, 'product.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(145, 'product.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(146, 'product.edit', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(147, 'product.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(148, 'customer.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(149, 'customer.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(150, 'customer.edit', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(151, 'customer.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(152, 'sales-order.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(153, 'sales-order.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(154, 'sales-order.edit', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(155, 'sales-order.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(156, 'invoice.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(157, 'invoice.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(158, 'invoice.edit', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(159, 'invoice.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(160, 'purchase-order.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(161, 'purchase-order.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(162, 'purchase-order.edit', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(163, 'purchase-order.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(164, 'report.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(165, 'report.export', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(166, 'dashboard.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(167, 'payroll.view', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(168, 'payroll.create', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(169, 'payroll.generate', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(170, 'payroll.approve', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(171, 'payroll.lock', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(172, 'payroll.pay', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(173, 'payroll.delete', 'web', '2026-06-18 00:47:33', '2026-06-18 00:47:33', NULL),
(174, 'payslip.view', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(175, 'payslip.download', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(176, 'salary-structure.view', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(177, 'salary-structure.create', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(178, 'salary-structure.edit', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(179, 'salary-structure.delete', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(180, 'category.view', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(181, 'category.create', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(182, 'category.edit', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(183, 'category.delete', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(184, 'supplier.view', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(185, 'supplier.create', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(186, 'supplier.edit', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(187, 'supplier.delete', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(188, 'attendance.geo.checkin', 'web', '2026-06-18 02:15:37', '2026-06-18 02:15:37', NULL),
(189, 'attendance.geo.checkout', 'web', '2026-06-18 02:15:37', '2026-06-18 02:15:37', NULL),
(190, 'attendance.location.view', 'web', '2026-06-18 02:15:37', '2026-06-18 02:15:37', NULL),
(191, 'attendance.location.audit', 'web', '2026-06-18 02:15:37', '2026-06-18 02:15:37', NULL),
(192, 'view_reports', 'web', '2026-06-22 06:52:55', '2026-06-22 06:52:55', NULL),
(193, 'create_reports', 'web', '2026-06-22 06:52:55', '2026-06-22 06:52:55', NULL),
(194, 'edit_reports', 'web', '2026-06-22 06:52:55', '2026-06-22 06:52:55', NULL),
(195, 'delete_reports', 'web', '2026-06-22 06:52:55', '2026-06-22 06:52:55', NULL),
(196, 'export_reports', 'web', '2026-06-22 06:52:55', '2026-06-22 06:52:55', NULL),
(197, 'schedule_reports', 'web', '2026-06-22 06:52:55', '2026-06-22 06:52:55', NULL),
(198, 'inventory.locations.view', 'web', '2026-06-25 10:25:56', '2026-06-25 10:25:56', NULL),
(199, 'inventory.locations.create', 'web', '2026-06-25 10:25:56', '2026-06-25 10:25:56', NULL),
(200, 'inventory.locations.edit', 'web', '2026-06-25 10:25:56', '2026-06-25 10:25:56', NULL),
(201, 'inventory.locations.delete', 'web', '2026-06-25 10:25:56', '2026-06-25 10:25:56', NULL),
(202, 'inventory.units.view', 'web', '2026-06-25 10:25:56', '2026-06-25 10:25:56', NULL),
(203, 'inventory.units.create', 'web', '2026-06-25 10:25:56', '2026-06-25 10:25:56', NULL),
(204, 'inventory.units.edit', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(205, 'inventory.units.delete', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(206, 'inventory.conversions.view', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(207, 'inventory.conversions.create', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(208, 'inventory.conversions.edit', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(209, 'inventory.conversions.delete', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(210, 'inventory.stock.view', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(211, 'inventory.stock.create', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(212, 'inventory.transactions.view', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(213, 'inventory.transactions.create', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(214, 'inventory.requests.view', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(215, 'inventory.requests.create', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(216, 'inventory.requests.approve', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(217, 'inventory.transfers.view', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(218, 'inventory.transfers.create', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(219, 'inventory.transfers.approve', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL),
(220, 'inventory.dashboard.view', 'web', '2026-06-25 10:25:57', '2026-06-25 10:25:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permission_dependencies`
--

CREATE TABLE `permission_dependencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `depends_on_permission_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'requires',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_scopes`
--

CREATE TABLE `permission_scopes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_scopes`
--

INSERT INTO `permission_scopes` (`id`, `name`, `label`, `description`, `level`, `created_at`, `updated_at`) VALUES
(1, 'self', 'Self', 'Own records only', 0, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(2, 'team', 'Team', 'Team members', 1, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(3, 'department', 'Department', 'Entire department', 2, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(4, 'branch', 'Branch', 'Branch-wide', 3, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(5, 'region', 'Region', 'Regional access', 4, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(6, 'organization', 'Organization', 'Entire organization', 5, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(7, 'global', 'Global', 'All access', 6, '2026-06-22 11:40:23', '2026-06-22 11:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '3d6296120e366af4b956edb93407781718f7ace10c33b69e000381adc6632f91', '[\"*\"]', NULL, NULL, '2026-06-15 23:52:38', '2026-06-15 23:52:38'),
(2, 'App\\Models\\User', 1, 'auth_token', '46d122ecab5c3892225aad3af5f1b5c28fc919e3cc4c2a1f87d24cbe9d621ec5', '[\"*\"]', '2026-06-16 00:33:58', NULL, '2026-06-15 23:52:56', '2026-06-16 00:33:58'),
(3, 'App\\Models\\User', 1, 'auth_token', 'ea60aa8c18699a03fba3becd9f6574828ba3727a882ff0b3fef4f2640ea08672', '[\"*\"]', NULL, NULL, '2026-06-16 00:35:15', '2026-06-16 00:35:15'),
(4, 'App\\Models\\User', 1, 'auth_token', 'abd51f67f5df639cf5fb89667f13008b463d48bfea7d83da1f4cd61045938601', '[\"*\"]', NULL, NULL, '2026-06-16 02:02:20', '2026-06-16 02:02:20'),
(5, 'App\\Models\\User', 1, 'auth_token', '16fada6d473a49ef771db61eef4c7ebf04886e225066661262a4f22e172af6ad', '[\"*\"]', '2026-06-16 04:31:44', NULL, '2026-06-16 02:06:52', '2026-06-16 04:31:44'),
(6, 'App\\Models\\User', 1, 'auth_token', '51f78074ab4a8e916bdcc48b39961b4de537a381ecbb3394ccf64c84c0914fc6', '[\"*\"]', '2026-06-16 04:33:54', NULL, '2026-06-16 04:32:03', '2026-06-16 04:33:54'),
(7, 'App\\Models\\User', 1, 'auth_token', '281d6fee7da50ea484a6f160decabed29a5e53f17c248730f868cd4b7697a6dc', '[\"*\"]', '2026-06-16 05:33:41', NULL, '2026-06-16 04:42:23', '2026-06-16 05:33:41'),
(8, 'App\\Models\\User', 1, 'auth_token', '1cf4e120acd9c9bd822dbb8555b95ab47a5a018b15997c6783de895cf1a6d460', '[\"*\"]', '2026-06-16 06:47:04', NULL, '2026-06-16 05:34:47', '2026-06-16 06:47:04'),
(10, 'App\\Models\\User', 1, 'auth_token', 'e8cc7187ad676ffc9a7b14bc18df54baee3cf5f56469cf9396549c28357a0032', '[\"*\"]', '2026-06-16 07:28:47', NULL, '2026-06-16 07:22:09', '2026-06-16 07:28:47'),
(13, 'App\\Models\\User', 3, 'test', 'cccdbfccddc3a915092fbb0089b86fa1c6481633ee8dfc270b0b7e4bd3ed80c1', '[\"*\"]', NULL, NULL, '2026-06-16 23:40:28', '2026-06-16 23:40:28'),
(14, 'App\\Models\\User', 3, 'test', '3ff218a8b6ab7e73bcccd9fdc1d9448163145d9c21b2f61485849e123355c4a0', '[\"*\"]', '2026-06-16 23:40:49', NULL, '2026-06-16 23:40:49', '2026-06-16 23:40:49'),
(15, 'App\\Models\\User', 2, 'auth_token', '5eeebcdae980d7c4ec6d8254764ea2d572a33d13d14849edcc645dd754f69f2a', '[\"*\"]', NULL, NULL, '2026-06-16 23:41:42', '2026-06-16 23:41:42'),
(16, 'App\\Models\\User', 1, 'auth_token', '3c914e9af32b16ced56e8e4ee85cc8605102b2c365f94196b1d3930386771d5c', '[\"*\"]', '2026-06-16 23:42:47', NULL, '2026-06-16 23:42:04', '2026-06-16 23:42:47'),
(20, 'App\\Models\\User', 3, 'test', 'f354592d9e0f3829ffd79d99e0420bc176daf10566e976a6def6099eccc0af99', '[\"*\"]', '2026-06-17 00:08:46', NULL, '2026-06-17 00:08:46', '2026-06-17 00:08:46'),
(21, 'App\\Models\\User', 3, 'test', '1496821f07425458d44880dce4aeb4d5e123d2f8185a2266a1431ef4869bbfb0', '[\"*\"]', '2026-06-17 00:09:00', NULL, '2026-06-17 00:09:00', '2026-06-17 00:09:00'),
(24, 'App\\Models\\User', 3, 'test', '258518818e6ec9f616291e1bafbf9364ba3463ebe17822daa8d1ac0da1e528b8', '[\"*\"]', '2026-06-17 05:19:50', NULL, '2026-06-17 05:18:34', '2026-06-17 05:19:50'),
(35, 'App\\Models\\User', 10, 'auth_token', '9b49549166bdc038e049b9427807491e0cb18b60cabaeed8c4b2120c19501beb', '[\"*\"]', '2026-06-18 03:00:37', NULL, '2026-06-18 03:00:33', '2026-06-18 03:00:37'),
(36, 'App\\Models\\User', 10, 'auth_token', '581abc7e1068865502ccb03e3ddf6ee8f2b05c1eca62a88580650c5bcffec760', '[\"*\"]', '2026-06-18 06:24:14', NULL, '2026-06-18 05:53:43', '2026-06-18 06:24:14'),
(41, 'App\\Models\\User', 10, 'auth_token', '281976c390cc5d2f7253f62db6df4e85fd5352612cee0c317eda7e5e8c88eb81', '[\"*\"]', NULL, NULL, '2026-06-19 05:58:48', '2026-06-19 05:58:48'),
(53, 'App\\Models\\User', 10, 'auth_token', '6174ce468b38d198c6d8f343650b17e440ee7de28a781760a63a3a2a305cf2bf', '[\"*\"]', '2026-06-19 07:17:56', NULL, '2026-06-19 07:17:38', '2026-06-19 07:17:56'),
(58, 'App\\Models\\User', 9, 'auth_token', '5ddbc487d6c45e9847c36de7ead8cc3a1a48aa07ddfab37c280b733c25d95a04', '[\"*\"]', '2026-06-20 00:22:49', NULL, '2026-06-20 00:22:15', '2026-06-20 00:22:49'),
(60, 'App\\Models\\User', 9, 'auth_token', 'd9e7874f28711c895886ebe47ed31e20e4e55b7c12b6a6a313acdca8b997d373', '[\"*\"]', '2026-06-20 00:32:39', NULL, '2026-06-20 00:32:39', '2026-06-20 00:32:39'),
(61, 'App\\Models\\User', 9, 'auth_token', '20e6295bd4d0a9e5c038cdda1650766a14582171f826b6ab3ff4602318514a35', '[\"*\"]', '2026-06-20 00:40:21', NULL, '2026-06-20 00:33:52', '2026-06-20 00:40:21'),
(62, 'App\\Models\\User', 9, 'test', '0f4bc1da6f823e98ebb51f2749d03ca90045419df92a5bc80114565058fd5c65', '[\"*\"]', '2026-06-20 00:41:43', NULL, '2026-06-20 00:41:42', '2026-06-20 00:41:43'),
(66, 'App\\Models\\User', 9, 'test', '81e8071c7b6e0b9ef204f40f474dfa3fe66f97211f97fe01cea152d987735dcf', '[\"*\"]', '2026-06-20 07:09:48', NULL, '2026-06-20 07:09:47', '2026-06-20 07:09:48'),
(67, 'App\\Models\\User', 9, 'test', '6a042459527e8dd8075ded4afb6144c2b9f2729deb82deef391378640472dbdf', '[\"*\"]', '2026-06-20 07:10:17', NULL, '2026-06-20 07:10:17', '2026-06-20 07:10:17'),
(68, 'App\\Models\\User', 9, 'auth_token', '0a8420134033beeccccf715eb30403edeed92523245b604f0f43edfd2305f647', '[\"*\"]', '2026-06-20 08:18:18', NULL, '2026-06-20 08:18:15', '2026-06-20 08:18:18'),
(72, 'App\\Models\\User', 1, 'test', '469c957f901c17c093f9b5fc7c2aeb0d72d8d8ed9827dc1f6d5d78e918552970', '[\"*\"]', '2026-06-22 07:21:19', NULL, '2026-06-22 07:21:19', '2026-06-22 07:21:19'),
(75, 'App\\Models\\User', 3, 'auth_token', '7c1782c325e2edc1b461cd4b28652b334fcae5d8aaabb7a3bfbb0c3ae536f82a', '[\"*\"]', '2026-06-23 07:43:03', NULL, '2026-06-23 05:41:29', '2026-06-23 07:43:03'),
(77, 'App\\Models\\User', 9, 'auth_token', '9674ec1e2171a2f8a46b11fa01919eee8e5ecbb1a46b09d9f6f22d4408bf413c', '[\"*\"]', '2026-06-23 09:28:19', NULL, '2026-06-23 08:51:54', '2026-06-23 09:28:19'),
(78, 'App\\Models\\User', 1, 'test-token', '5c62a36db7dd04efe554492f69a016af0b7892595f7cea258d3469e4959290ee', '[\"*\"]', '2026-06-23 09:17:56', NULL, '2026-06-23 08:57:17', '2026-06-23 09:17:56'),
(80, 'App\\Models\\User', 3, 'auth_token', '1eb7ae7e27ffb10ea7a3d603982108370c014e81250ecc17bc58dfcaf180759b', '[\"*\"]', '2026-06-25 12:27:22', NULL, '2026-06-25 06:14:46', '2026-06-25 12:27:22'),
(81, 'App\\Models\\User', 12, 'auth_token', '4ece2bf442548f13663b8d5f11f2df6c71d0d450940aa26d93b60b1eb8552dc7', '[\"*\"]', '2026-06-25 12:13:54', NULL, '2026-06-25 11:42:40', '2026-06-25 12:13:54'),
(82, 'App\\Models\\User', 12, 'auth_token', '463254ac377bc3152e928c3afc5c6e92039a9443bc5ddc57c534596d48a62efe', '[\"*\"]', '2026-06-25 12:56:26', NULL, '2026-06-25 12:28:00', '2026-06-25 12:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `hsn_code` varchar(255) DEFAULT NULL,
  `product_code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `opening_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `hsn_code`, `product_code`, `name`, `category_id`, `supplier_id`, `purchase_price`, `selling_price`, `cost_price`, `reorder_level`, `min_stock`, `max_stock`, `description`, `status`, `opening_stock`, `created_at`, `updated_at`, `unit_id`) VALUES
(3, 'ELEC-SPRI-001', NULL, NULL, 'sprinkler', 1, 1, 150.00, 200.00, 0.00, 0.00, 0.00, 0.00, NULL, 'active', 0.00, '2026-06-22 04:47:17', '2026-06-22 04:47:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_stock`
--

CREATE TABLE `product_stock` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reserved_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `available_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location_type` varchar(255) NOT NULL DEFAULT 'AppModelsBranch'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`id`, `product_id`, `location_id`, `quantity`, `reserved_quantity`, `available_quantity`, `created_at`, `updated_at`, `location_type`) VALUES
(3, 3, 4, 85.00, 0.00, 85.00, '2026-06-22 04:47:17', '2026-06-22 06:02:20', 'App\\Models\\Site'),
(4, 3, 5, 415.00, 0.00, 415.00, '2026-06-22 05:23:55', '2026-06-22 06:02:20', 'App\\Models\\Site');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_type` varchar(255) DEFAULT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier_id`, `total_amount`, `tax_amount`, `shipping_cost`, `gst_type`, `gst_rate`, `status`, `notes`, `created_at`, `updated_at`, `requested_by`, `approved_by`, `received_by`, `received_at`) VALUES
(4, 'PO-1782118783', 1, 1500.00, 75.00, 100.00, 'cgst', 0.00, 'Approved', NULL, '2026-06-22 08:59:43', '2026-06-22 08:59:55', 9, 9, NULL, NULL),
(6, 'PO-1782387213', 1, 2175.00, 391.50, 150.00, 'cgst', 0.00, 'Approved', NULL, '2026-06-25 11:33:33', '2026-06-25 12:52:16', 3, 12, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `received_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(10,2) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `hsn_code` varchar(255) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `product_id`, `custom_product_name`, `quantity`, `received_quantity`, `unit_cost`, `gst_rate`, `hsn_code`, `total`, `created_at`, `updated_at`) VALUES
(4, 4, 3, NULL, 10, 0, 150.00, 5.00, 'abc123', 1500.00, '2026-06-22 08:59:43', '2026-06-22 08:59:43'),
(5, 6, NULL, 'ELEC-SPRI-001 - sprinkler', 10, 0, 150.00, 18.00, NULL, 1500.00, '2026-06-25 11:33:33', '2026-06-25 11:33:33'),
(6, 6, NULL, 'Elbow', 15, 0, 45.00, 18.00, NULL, 675.00, '2026-06-25 11:33:33', '2026-06-25 11:33:33');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `return_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `goods_receipt_note_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_reference` varchar(255) DEFAULT NULL,
  `return_date` date NOT NULL,
  `return_reason` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return_items`
--

CREATE TABLE `purchase_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_return_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `return_quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `endpoint` varchar(500) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `content_encoding` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `category_id`, `name`, `slug`, `description`, `icon`, `route`, `api_endpoint`, `status`, `created_by`, `last_generated_at`, `parameters`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sales Overview', 'sales-overview', 'Summary of sales orders, revenue, and trends', NULL, '/dashboard/reports/sales', '/api/v1/reports/sales', 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(2, 1, 'Sales by Customer', 'sales-by-customer', 'Sales breakdown by customer account', NULL, NULL, NULL, 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(3, 1, 'Revenue Report', 'revenue-report', 'Revenue analysis with period comparisons', NULL, NULL, NULL, 'active', 1, NULL, NULL, 3, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(4, 2, 'Stock Summary', 'stock-summary', 'Current stock levels across all locations', NULL, '/dashboard/inventory/stock', NULL, 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(5, 2, 'Stock Movement', 'stock-movement', 'Transaction history and stock transfers', NULL, '/dashboard/inventory/transactions', NULL, 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(6, 2, 'Inventory Valuation', 'inventory-valuation', 'Stock valuation by product and location', NULL, NULL, NULL, 'active', 1, NULL, NULL, 3, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(7, 3, 'Payment Report', 'payment-report', 'Payment collections and outstanding amounts', NULL, NULL, '/api/v1/reports/payments', 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(8, 3, 'Invoice Summary', 'invoice-summary', 'Invoice generation and payment status', NULL, '/dashboard/invoices', NULL, 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(9, 4, 'Employee Directory', 'employee-directory', 'Complete employee list with department and designation', NULL, '/dashboard/employees', '/api/v1/reports/employees', 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(10, 4, 'Department Overview', 'department-overview', 'Employees grouped by department', NULL, '/dashboard/departments', NULL, 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(11, 4, 'Employee Count Report', 'employee-count-report', 'Employee count and distribution analysis', NULL, NULL, '/api/v1/reports/employees', 'active', 1, NULL, NULL, 3, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(12, 5, 'Attendance Report', 'attendance-report', 'Monthly attendance records by employee', NULL, '/dashboard/attendance', '/api/v1/reports/attendance', 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(13, 5, 'Attendance Summary', 'attendance-summary', 'Present, absent, late, and half-day counts', NULL, NULL, '/api/v1/reports/attendance', 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(14, 6, 'Payroll Summary', 'payroll-summary', 'Payroll processing summary and totals', NULL, '/dashboard/payroll', NULL, 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(15, 6, 'Salary Structure Report', 'salary-structure', 'Employee salary components breakdown', NULL, '/dashboard/salary-structures', NULL, 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(16, 7, 'Purchase Orders', 'purchase-orders', 'Purchase order status and amounts', NULL, '/dashboard/purchases', NULL, 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(17, 7, 'Supplier Report', 'supplier-report', 'Supplier performance and procurement metrics', NULL, NULL, NULL, 'active', 1, NULL, NULL, 2, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(18, 8, 'Leave Report', 'leave-report', 'Leave requests by status and period', NULL, '/dashboard/leave-management', '/api/v1/reports/leaves', 'active', 1, NULL, NULL, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(19, 8, 'Leave Balance', 'leave-balance', 'Employee leave balance overview', NULL, '/dashboard/leave-management/balances', NULL, 'active', 1, '2026-06-22 07:29:44', NULL, 2, '2026-06-22 06:53:55', '2026-06-22 07:29:44');

-- --------------------------------------------------------

--
-- Table structure for table `report_categories`
--

CREATE TABLE `report_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_categories`
--

INSERT INTO `report_categories` (`id`, `name`, `slug`, `description`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sales', 'sales', 'Sales performance and revenue reports', 'TrendingUp', 1, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(2, 'Inventory', 'inventory', 'Stock levels, movements, and valuation reports', 'Package', 2, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(3, 'Financial', 'financial', 'Payments, invoices, and accounting reports', 'DollarSign', 3, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(4, 'Employees', 'employees', 'Employee information and HR metrics', 'Users', 4, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(5, 'Attendance', 'attendance', 'Employee attendance and timesheet reports', 'Clock', 5, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(6, 'Payroll', 'payroll', 'Salary, deductions, and payroll summary reports', 'Wallet', 6, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(7, 'Purchases', 'purchases', 'Procurement and supplier reports', 'ShoppingBag', 7, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55'),
(8, 'Leaves', 'leaves', 'Leave balances, requests, and utilization reports', 'Calendar', 8, 1, '2026-06-22 06:53:55', '2026-06-22 06:53:55');

-- --------------------------------------------------------

--
-- Table structure for table `report_generations`
--

CREATE TABLE `report_generations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL DEFAULT 'pdf',
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT 0,
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_generations`
--

INSERT INTO `report_generations` (`id`, `report_id`, `schedule_id`, `file_name`, `file_type`, `file_path`, `file_size`, `generated_by`, `generated_at`, `parameters`, `status`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 19, NULL, 'leave-balance_20260622_124709.pdf', 'pdf', 'reports/leave-balance_20260622_124709.pdf', 158034, 9, '2026-06-22 07:17:09', '[]', 'completed', NULL, '2026-06-22 07:17:09', '2026-06-22 07:17:09'),
(2, 19, NULL, 'leave-balance_20260622_125831.pdf', 'pdf', 'reports/leave-balance_20260622_125831.pdf', 905374, 1, '2026-06-22 07:28:32', '[]', 'completed', NULL, '2026-06-22 07:28:32', '2026-06-22 07:28:32'),
(3, 19, NULL, 'leave-balance_20260622_125943.pdf', 'pdf', 'reports/leave-balance_20260622_125943.pdf', 905367, 9, '2026-06-22 07:29:44', '[]', 'completed', NULL, '2026-06-22 07:29:44', '2026-06-22 07:29:44');

-- --------------------------------------------------------

--
-- Table structure for table `report_schedules`
--

CREATE TABLE `report_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `frequency` varchar(255) NOT NULL DEFAULT 'daily',
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `next_run_at` timestamp NULL DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recipients` text DEFAULT NULL,
  `format` varchar(255) NOT NULL DEFAULT 'pdf',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Admin', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(3, 'Manager', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(4, 'HR', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(5, 'Employee', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(6, 'Accountant', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(10, 'System Admin', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(11, 'General Manager', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(12, 'Production Manager', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(13, 'Workshop Supervisor', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(14, 'Fitter', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(15, 'Welder', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(16, 'Electrician', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(17, 'Helper', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(18, 'Finance Manager', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(19, 'Design Manager', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(20, 'Designer', 'web', '2026-06-18 00:47:34', '2026-06-18 00:47:34', NULL),
(21, 'Store Manager', 'web', '2026-06-18 00:47:35', '2026-06-18 00:47:35', NULL),
(22, 'HR Manager', 'web', '2026-06-18 00:47:35', '2026-06-18 00:47:35', NULL),
(23, 'IT Manager', 'web', '2026-06-18 00:47:35', '2026-06-18 00:47:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 10),
(2, 10),
(3, 10),
(4, 10),
(5, 10),
(6, 10),
(7, 10),
(8, 10),
(9, 10),
(10, 10),
(11, 10),
(12, 10),
(13, 10),
(14, 10),
(15, 10),
(16, 10),
(17, 10),
(18, 10),
(19, 10),
(20, 10),
(21, 10),
(22, 10),
(23, 10),
(24, 10),
(25, 10),
(26, 10),
(27, 10),
(28, 10),
(29, 10),
(30, 10),
(31, 10),
(32, 10),
(33, 10),
(34, 10),
(35, 10),
(36, 10),
(37, 10),
(38, 10),
(39, 10),
(40, 10),
(41, 10),
(42, 10),
(43, 10),
(44, 10),
(45, 10),
(46, 10),
(47, 10),
(48, 10),
(49, 10),
(50, 10),
(51, 10),
(52, 10),
(53, 10),
(54, 10),
(55, 10),
(56, 10),
(57, 10),
(58, 10),
(59, 10),
(60, 10),
(61, 10),
(62, 10),
(63, 10),
(64, 10),
(65, 10),
(66, 10),
(67, 10),
(68, 10),
(69, 10),
(70, 10),
(71, 10),
(72, 10),
(73, 10),
(74, 10),
(75, 10),
(76, 10),
(77, 10),
(78, 10),
(79, 10),
(80, 10),
(81, 10),
(82, 10),
(83, 10),
(84, 10),
(85, 10),
(86, 10),
(87, 10),
(88, 10),
(89, 10),
(90, 10),
(91, 10),
(92, 10),
(93, 10),
(94, 10),
(95, 10),
(96, 10),
(97, 10),
(98, 10),
(99, 10),
(100, 10),
(101, 10),
(102, 10),
(103, 10),
(104, 10),
(105, 10),
(106, 10),
(107, 10),
(108, 10),
(109, 10),
(110, 10),
(111, 10),
(112, 10),
(113, 10),
(114, 10),
(115, 10),
(116, 10),
(117, 10),
(118, 10),
(119, 10),
(120, 10),
(121, 10),
(122, 10),
(123, 10),
(124, 10),
(125, 10),
(126, 10),
(127, 10),
(128, 10),
(129, 10),
(130, 10),
(131, 10),
(132, 10),
(133, 10),
(134, 10),
(135, 10),
(136, 10),
(137, 10),
(138, 10),
(139, 10),
(140, 10),
(141, 10),
(142, 10),
(143, 10),
(144, 10),
(145, 10),
(146, 10),
(147, 10),
(148, 10),
(149, 10),
(150, 10),
(151, 10),
(152, 10),
(153, 10),
(154, 10),
(155, 10),
(156, 10),
(157, 10),
(158, 10),
(159, 10),
(160, 10),
(161, 10),
(162, 10),
(163, 10),
(164, 10),
(165, 10),
(166, 10),
(167, 10),
(168, 10),
(169, 10),
(170, 10),
(171, 10),
(172, 10),
(173, 10),
(174, 10),
(175, 10),
(176, 10),
(177, 10),
(178, 10),
(179, 10),
(180, 10),
(181, 10),
(182, 10),
(183, 10),
(184, 10),
(185, 10),
(186, 10),
(187, 10),
(1, 11),
(2, 11),
(3, 11),
(4, 11),
(5, 11),
(6, 11),
(7, 11),
(8, 11),
(9, 11),
(10, 11),
(11, 11),
(12, 11),
(13, 11),
(14, 11),
(15, 11),
(16, 11),
(17, 11),
(18, 11),
(19, 11),
(20, 11),
(21, 11),
(22, 11),
(23, 11),
(24, 11),
(25, 11),
(26, 11),
(27, 11),
(28, 11),
(29, 11),
(30, 11),
(31, 11),
(32, 11),
(33, 11),
(34, 11),
(35, 11),
(36, 11),
(37, 11),
(38, 11),
(39, 11),
(40, 11),
(41, 11),
(42, 11),
(43, 11),
(44, 11),
(45, 11),
(46, 11),
(47, 11),
(48, 11),
(49, 11),
(50, 11),
(51, 11),
(52, 11),
(53, 11),
(54, 11),
(55, 11),
(56, 11),
(57, 11),
(58, 11),
(59, 11),
(60, 11),
(61, 11),
(62, 11),
(63, 11),
(64, 11),
(65, 11),
(66, 11),
(67, 11),
(68, 11),
(69, 11),
(70, 11),
(71, 11),
(72, 11),
(73, 11),
(74, 11),
(75, 11),
(76, 11),
(77, 11),
(78, 11),
(79, 11),
(80, 11),
(81, 11),
(82, 11),
(83, 11),
(84, 11),
(85, 11),
(86, 11),
(87, 11),
(88, 11),
(89, 11),
(90, 11),
(91, 11),
(92, 11),
(93, 11),
(94, 11),
(95, 11),
(96, 11),
(97, 11),
(98, 11),
(99, 11),
(100, 11),
(101, 11),
(102, 11),
(103, 11),
(104, 11),
(105, 11),
(106, 11),
(107, 11),
(108, 11),
(109, 11),
(110, 11),
(111, 11),
(112, 11),
(113, 11),
(114, 11),
(115, 11),
(116, 11),
(117, 11),
(118, 11),
(119, 11),
(120, 11),
(121, 11),
(122, 11),
(123, 11),
(124, 11),
(125, 11),
(126, 11),
(128, 11),
(129, 11),
(130, 11),
(131, 11),
(132, 11),
(133, 11),
(134, 11),
(135, 11),
(136, 11),
(137, 11),
(138, 11),
(139, 11),
(140, 11),
(141, 11),
(142, 11),
(143, 11),
(144, 11),
(145, 11),
(146, 11),
(147, 11),
(148, 11),
(149, 11),
(150, 11),
(151, 11),
(152, 11),
(153, 11),
(154, 11),
(155, 11),
(156, 11),
(157, 11),
(158, 11),
(159, 11),
(160, 11),
(161, 11),
(162, 11),
(163, 11),
(164, 11),
(165, 11),
(166, 11),
(167, 11),
(168, 11),
(169, 11),
(170, 11),
(171, 11),
(172, 11),
(173, 11),
(174, 11),
(175, 11),
(176, 11),
(177, 11),
(178, 11),
(179, 11),
(180, 11),
(181, 11),
(182, 11),
(183, 11),
(184, 11),
(185, 11),
(186, 11),
(187, 11),
(166, 12),
(93, 12),
(136, 12),
(144, 12),
(103, 12),
(104, 12),
(99, 12),
(85, 12),
(166, 13),
(93, 13),
(103, 13),
(99, 13),
(166, 14),
(93, 14),
(94, 14),
(103, 14),
(166, 15),
(93, 15),
(94, 15),
(103, 15),
(166, 16),
(93, 16),
(94, 16),
(103, 16),
(166, 17),
(93, 17),
(94, 17),
(103, 17),
(166, 18),
(156, 18),
(157, 18),
(158, 18),
(159, 18),
(164, 18),
(165, 18),
(152, 18),
(160, 18),
(167, 18),
(170, 18),
(171, 18),
(172, 18),
(184, 18),
(148, 18),
(166, 19),
(93, 19),
(144, 19),
(166, 20),
(93, 20),
(144, 20),
(166, 22),
(46, 22),
(47, 22),
(48, 22),
(49, 22),
(93, 22),
(94, 22),
(95, 22),
(96, 22),
(164, 22),
(167, 22),
(168, 22),
(169, 22),
(173, 22),
(174, 22),
(175, 22),
(176, 22),
(177, 22),
(178, 22),
(179, 22),
(99, 22),
(100, 22),
(101, 22),
(102, 22),
(103, 22),
(104, 22),
(105, 22),
(106, 22),
(112, 22),
(113, 22),
(114, 22),
(115, 22),
(38, 22),
(39, 22),
(40, 22),
(41, 22),
(42, 22),
(43, 22),
(44, 22),
(45, 22),
(166, 23),
(128, 23),
(129, 23),
(130, 23),
(131, 23),
(132, 23),
(133, 23),
(134, 23),
(135, 23),
(124, 23),
(125, 23),
(126, 23),
(127, 23),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(59, 2),
(60, 2),
(61, 2),
(62, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(76, 2),
(77, 2),
(78, 2),
(79, 2),
(80, 2),
(81, 2),
(82, 2),
(83, 2),
(84, 2),
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(89, 2),
(90, 2),
(91, 2),
(93, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
(103, 2),
(104, 2),
(105, 2),
(106, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(112, 2),
(113, 2),
(114, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(121, 2),
(122, 2),
(123, 2),
(124, 2),
(125, 2),
(126, 2),
(127, 2),
(128, 2),
(129, 2),
(130, 2),
(131, 2),
(132, 2),
(133, 2),
(134, 2),
(135, 2),
(136, 2),
(137, 2),
(138, 2),
(139, 2),
(140, 2),
(141, 2),
(142, 2),
(143, 2),
(144, 2),
(145, 2),
(146, 2),
(147, 2),
(148, 2),
(149, 2),
(150, 2),
(151, 2),
(152, 2),
(153, 2),
(154, 2),
(155, 2),
(156, 2),
(157, 2),
(158, 2),
(159, 2),
(160, 2),
(161, 2),
(162, 2),
(163, 2),
(164, 2),
(165, 2),
(166, 2),
(167, 2),
(168, 2),
(169, 2),
(170, 2),
(171, 2),
(172, 2),
(173, 2),
(174, 2),
(175, 2),
(176, 2),
(177, 2),
(178, 2),
(179, 2),
(180, 2),
(181, 2),
(182, 2),
(183, 2),
(184, 2),
(185, 2),
(186, 2),
(187, 2),
(190, 2),
(191, 2),
(192, 2),
(193, 2),
(194, 2),
(195, 2),
(196, 2),
(197, 2),
(198, 2),
(199, 2),
(200, 2),
(201, 2),
(202, 2),
(203, 2),
(204, 2),
(205, 2),
(206, 2),
(207, 2),
(208, 2),
(209, 2),
(210, 2),
(211, 2),
(212, 2),
(213, 2),
(214, 2),
(215, 2),
(216, 2),
(217, 2),
(218, 2),
(219, 2),
(220, 2),
(12, 3),
(34, 3),
(85, 3),
(18, 3),
(46, 3),
(22, 3),
(52, 3),
(59, 3),
(210, 3),
(220, 3),
(198, 3),
(212, 3),
(217, 3),
(202, 3),
(214, 3),
(103, 3),
(111, 3),
(93, 3),
(56, 3),
(112, 3),
(16, 3),
(12, 21),
(9, 21),
(59, 21),
(60, 21),
(22, 21),
(23, 21),
(24, 21),
(52, 21),
(53, 21),
(54, 21),
(198, 21),
(199, 21),
(200, 21),
(202, 21),
(203, 21),
(204, 21),
(206, 21),
(207, 21),
(208, 21),
(210, 21),
(211, 21),
(212, 21),
(213, 21),
(214, 21),
(215, 21),
(216, 21),
(217, 21),
(218, 21),
(219, 21),
(220, 21),
(46, 21),
(73, 21),
(74, 21),
(71, 21),
(72, 21),
(12, 6),
(16, 6),
(67, 6),
(68, 6),
(73, 6),
(74, 6),
(71, 6),
(72, 6),
(61, 6),
(62, 6),
(63, 6),
(65, 6),
(66, 6),
(26, 6),
(30, 6),
(46, 6),
(18, 6),
(22, 6),
(52, 6),
(176, 6),
(12, 4),
(2, 4),
(10, 4),
(4, 4),
(16, 4),
(46, 4),
(47, 4),
(48, 4),
(56, 4),
(57, 4),
(58, 4),
(92, 4),
(93, 4),
(94, 4),
(95, 4),
(96, 4),
(97, 4),
(98, 4),
(99, 4),
(100, 4),
(101, 4),
(102, 4),
(67, 4),
(68, 4),
(38, 4),
(39, 4),
(40, 4),
(42, 4),
(43, 4),
(44, 4),
(69, 4),
(70, 4),
(34, 4),
(35, 4),
(36, 4),
(78, 4),
(79, 4),
(80, 4),
(81, 4),
(82, 4),
(83, 4),
(84, 4),
(103, 4),
(104, 4),
(105, 4),
(106, 4),
(107, 4),
(108, 4),
(109, 4),
(110, 4),
(111, 4),
(112, 4),
(113, 4),
(116, 4),
(117, 4),
(119, 4),
(120, 4),
(121, 4),
(122, 4),
(12, 5),
(97, 5),
(98, 5),
(93, 5),
(104, 5),
(103, 5),
(107, 5),
(112, 5),
(113, 5),
(118, 5),
(119, 5),
(67, 5);

-- --------------------------------------------------------

--
-- Table structure for table `role_templates`
--

CREATE TABLE `role_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_templates`
--

INSERT INTO `role_templates` (`id`, `name`, `description`, `category`, `permissions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Manager Template', 'Comprehensive access for department managers with read/write on most modules', 'management', '[\"view_dashboard\",\"view_employees\",\"view_products\",\"view_customers\",\"view_suppliers\",\"view_sites\",\"view_inventory\",\"view_invoices\",\"view_reports\",\"view_attendance\",\"view_leaves\"]', 1, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(2, 'Executive Template', 'Read-only access across all modules for executive review', 'executive', '[\"view_dashboard\",\"view_employees\",\"view_products\",\"view_customers\",\"view_suppliers\",\"view_sites\",\"view_inventory\",\"view_invoices\",\"view_reports\",\"view_attendance\",\"view_payroll\",\"view_purchase_orders\",\"view_sales_orders\"]', 1, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(3, 'Finance Template', 'Financial module access including invoices, payments, and payroll', 'finance', '[\"view_dashboard\",\"view_invoices\",\"create_invoices\",\"update_invoices\",\"view_payments\",\"create_payments\",\"view_payroll\",\"manage_payroll\",\"view_customers\",\"view_suppliers\",\"view_reports\"]', 1, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(4, 'Operations Template', 'Operational access for inventory, purchasing, and site management', 'operations', '[\"view_dashboard\",\"view_inventory\",\"manage_inventory\",\"view_products\",\"view_categories\",\"view_purchase_orders\",\"create_purchase_orders\",\"view_sites\",\"view_suppliers\",\"inventory.stock.view\",\"inventory.transactions.view\",\"inventory.transactions.create\",\"inventory.transfers.view\",\"inventory.transfers.create\"]', 1, '2026-06-22 11:40:23', '2026-06-22 11:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `salary_structures`
--

CREATE TABLE `salary_structures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `conveyance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `medical_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `special_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `site_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `travel_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `food_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_earnings` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pf_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `esic_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `professional_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tds` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_structures`
--

INSERT INTO `salary_structures` (`id`, `employee_id`, `basic_salary`, `hra`, `conveyance`, `medical_allowance`, `special_allowance`, `site_allowance`, `travel_allowance`, `food_allowance`, `other_earnings`, `pf_deduction`, `esic_deduction`, `professional_tax`, `tds`, `other_deductions`, `effective_from`, `effective_to`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 56, 22000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-06-01', NULL, 'active', '2026-06-17 23:46:10', '2026-06-20 10:33:20', '2026-06-20 10:33:20'),
(2, 9, 25000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-06-01', NULL, 'active', '2026-06-20 10:39:49', '2026-06-20 11:15:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `so_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_type` varchar(255) DEFAULT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `delivered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_orders`
--

INSERT INTO `sales_orders` (`id`, `so_number`, `customer_id`, `total_amount`, `tax_amount`, `shipping_cost`, `gst_type`, `gst_rate`, `discount_amount`, `status`, `notes`, `created_at`, `updated_at`, `created_by`, `approved_by`, `delivered_by`, `delivered_at`) VALUES
(1, 'SO-1782119309', 2, 3000.00, 360.00, 150.00, 'cgst', 0.00, 0.00, 'Approved', NULL, '2026-06-22 09:08:29', '2026-06-22 09:08:55', 9, 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `delivered_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `hsn_code` varchar(255) DEFAULT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_order_items`
--

INSERT INTO `sales_order_items` (`id`, `sales_order_id`, `product_id`, `custom_product_name`, `quantity`, `delivered_quantity`, `unit_price`, `gst_rate`, `hsn_code`, `discount`, `total`, `created_at`, `updated_at`) VALUES
(1, 1, 3, NULL, 15, 0, 200.00, 12.00, NULL, 0.00, 3000.00, '2026-06-22 09:08:29', '2026-06-22 09:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `return_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_reference` varchar(255) DEFAULT NULL,
  `return_date` date NOT NULL,
  `return_reason` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_return_items`
--

CREATE TABLE `sales_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_return_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `return_quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('eEGRWsR3nxhbAt10XyWhxX1Mm954JFpXcYxYOVBr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTjJvOXh2YVUyUGtXU0RiWlpBazdtMXFnMVA4RDlWZUpjRkdaWjdkdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781098253),
('F8YR3XECaINNRDgRERke5kmDmCxajmxdA2GCSXE8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0VmSTVzU1NIR3d2U291a2Zxd3kwMU9zczF4TlRWOFNKaW1HQWJwRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781587133),
('gIb2GbWiIFPYiHUhbWO21srwSM3q57ESbiWR5rUP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVZqZzM5cE52THNDV3BqNkxNUUZqSVpWalNEWEpiNFVjdGQ4SWFvVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586044),
('GkDgYOV0Km0DRuef81IyTSba0a6W1MHOl36f9sDo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieDk4VWFjWHJXdWFzRDQyekR2Z0VCdE44ejJYUFdnSFphSFlGNDhkaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586356),
('h197uzalop3jRiIylFnqkXQGL74hYCoQFJqtJH6D', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWxDcXg3VERhRWNQU1EwclJTTkZvU0pyR3I1VnM1UWZ5S0ozeldXTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781117879),
('I5HnKbNbaHeXkReFid1M9tMvZBuQQ1sCIYUktrBp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTDRUVDM2M2tKVzhNd0NSR0hmWHpjVUs3c3VZcEM3UHN0MFBLaG92biI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781587375),
('j4Lz5HuXnSp9rvRvwLgKp9lzan8KFGINjRjdXQTC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYTczTU1DTmpnY0xndXZDeHV1NmRaQlpWQjlkSjBmU3pDOFpJUlRHcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781587190),
('kXT8XFgvawApnfCRNEKHKp8o5JVUWvRBKA2McRPL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY3RHZ296TjcxSmJ3cktHZWRNc0FTaGpuaDhSNUNoTWRRaUliVGl2USI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586561),
('Ohgr37hd8NASfoAixf9PyliPaaJGy3D5Ydl7DfsI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWhjbzJhVEJoa3dNenc3bWd3RHF6Qm9VdVBieEdlRUhLRFpYTkhiNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586054),
('OySpemPmuQ3lTQKqK4gKisCKfK1WR9oqd7BjlXW7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiemREcHJ1MnJZUm1QeVE3UXVRU2NNcGhXS2hocHI3RGJ3WGticTNaUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586035),
('XbAdbC7sp3mlLcZ3iye8yDGnn9pBjn8h1TV2D3Vy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ3NJN3ZYY01BZk1Wak9iUmFmZ25nS0JNbERVV09BMVpXTWZpZFdHWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1780995463),
('xhoHQfFIkQbodl5y9gUVCHWYhwDZRJwIIe6ZG4nX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.123.2 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnJFMVVKWUh4c0xJQk5nMUpRdlJCUTM4Qm9kcld5bXphNUVGWFBHWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781098193),
('znj2he9AJLfcOMffBkEWk6ZEDrle9YjuBR7PDhLC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU1RNTmRRam1BWWVKSUpuWmxJb1VwUTNUWnFNTHd3bEF5WFIyZ2J2RiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586438),
('zYhd3BZKom0vcuoPElAvZ4Wgb5fmcyfWm4iNXlKS', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoielloSFVnUHNsUUR4SHhmNTRQSGkzb2hVRWtiR296RkYwSzAwYjNrciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781586047);

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_period` int(11) NOT NULL DEFAULT 0,
  `late_threshold` int(11) NOT NULL DEFAULT 0,
  `half_day_threshold` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `grace_period`, `late_threshold`, `half_day_threshold`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Regular', '09:00:00', '18:00:00', 10, 10, 30, 'Active', '2026-06-17 07:10:02', '2026-06-17 07:10:02');

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `client_details` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `allowed_radius` int(11) NOT NULL DEFAULT 100,
  `geo_fencing_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `site_manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sites`
--

INSERT INTO `sites` (`id`, `name`, `code`, `client_details`, `address`, `city`, `state`, `country`, `pincode`, `contact_person`, `phone`, `email`, `status`, `latitude`, `longitude`, `allowed_radius`, `geo_fencing_enabled`, `site_manager_id`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(4, 'Headquaters', '111', NULL, 'Kasturi Vandana Complex, Bhayandar, Sonam Sagar, Indira Nagar, Bhayandar East.', 'Bhayandar', 'Maharashtra', 'India', '401105', 'Ronak Ray', '1234567890', 'ronak.ray@company.com', 'Active', 19.29966670, 72.85677770, 200, 1, NULL, '2026-06-25', '2026-08-31', '2026-06-20 00:14:00', '2026-06-25 08:43:28'),
(5, 'Remi', '102', '123456', 'abc xyz', NULL, NULL, 'India', NULL, 'Ronak Ray', '1234567890', 'ronak.ray@company.com', 'Active', 20.30764677, 72.84440918, 100, 1, NULL, NULL, NULL, '2026-06-22 04:51:40', '2026-06-22 05:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `stock_requests`
--

CREATE TABLE `stock_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_number` varchar(255) NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `from_location_id` bigint(20) UNSIGNED NOT NULL,
  `to_location_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `approved_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `issued_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `received_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('requested','approved','issued','received') NOT NULL DEFAULT 'requested',
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `from_location_type` varchar(255) NOT NULL DEFAULT 'AppModelsBranch',
  `to_location_type` varchar(255) NOT NULL DEFAULT 'AppModelsBranch'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_requests`
--

INSERT INTO `stock_requests` (`id`, `request_number`, `product_id`, `from_location_id`, `to_location_id`, `quantity`, `approved_quantity`, `issued_quantity`, `received_quantity`, `status`, `requested_by`, `approved_by`, `issued_by`, `received_by`, `notes`, `created_at`, `updated_at`, `from_location_type`, `to_location_type`) VALUES
(1, 'SRQ-20260622-0001', 3, 4, 5, 150.00, 150.00, 150.00, 150.00, 'received', 9, 9, 9, 9, NULL, '2026-06-22 05:46:11', '2026-06-22 05:46:33', 'App\\Models\\Site', 'App\\Models\\Site'),
(2, 'SRQ-20260622-0002', 3, 4, 5, 150.00, 150.00, 150.00, 150.00, 'received', 9, 9, 9, 9, NULL, '2026-06-22 06:02:06', '2026-06-22 06:02:22', 'App\\Models\\Site', 'App\\Models\\Site');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `gst_number`, `address`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'InStorage', 'instorage@system.local', '1234567890', '123456000', 'System - Internal Storage', 1, '2026-06-19 07:25:39', '2026-06-22 06:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_quotations`
--

CREATE TABLE `supplier_quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_quotation_items`
--

CREATE TABLE `supplier_quotation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_quotation_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_exceptions`
--

CREATE TABLE `system_exceptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `allowed_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_roles`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_exceptions`
--

INSERT INTO `system_exceptions` (`id`, `name`, `key`, `description`, `category`, `is_enabled`, `requires_approval`, `allowed_roles`, `created_at`, `updated_at`) VALUES
(1, 'Override Credit Limits', 'override_credit_limits', 'Allow role to override customer credit limits during order processing', 'financial', 0, 1, NULL, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(2, 'Approve Beyond Threshold', 'approve_beyond_threshold', 'Allow approval of transactions exceeding standard approval thresholds', 'financial', 0, 1, NULL, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(3, 'Emergency Inventory Release', 'emergency_inventory_release', 'Authorize emergency release of inventory without standard approvals', 'inventory', 0, 1, NULL, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(4, 'Bulk Data Export', 'bulk_data_export', 'Allow export of large data sets from the system', 'system', 0, 1, NULL, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(5, 'Payroll Override', 'payroll_override', 'Override payroll calculations for adjustments', 'financial', 0, 1, NULL, '2026-06-22 11:40:23', '2026-06-22 11:40:23'),
(6, 'Financial Adjustments', 'financial_adjustments', 'Make manual adjustments to financial records', 'financial', 0, 1, NULL, '2026-06-22 11:40:23', '2026-06-22 11:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Assigned',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_ledger`
--

CREATE TABLE `transaction_ledger` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_number` varchar(255) NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `to_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_type` enum('opening_stock','purchase','purchase_return','issue','consumption','transfer_in','transfer_out','adjustment','damage','sales','sales_return') NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `quantity_before` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity_after` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location_type` varchar(255) NOT NULL DEFAULT 'AppModelsBranch',
  `to_location_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_ledger`
--

INSERT INTO `transaction_ledger` (`id`, `transaction_number`, `product_id`, `location_id`, `to_location_id`, `transaction_type`, `quantity`, `unit_price`, `total_price`, `quantity_before`, `quantity_after`, `reference_type`, `reference_id`, `notes`, `created_by`, `created_at`, `updated_at`, `location_type`, `to_location_type`) VALUES
(1, 'TXN-20260622-0001', 3, 4, NULL, 'purchase', 500.00, 0.00, 0.00, 0.00, 500.00, 'opening_stock', 3, 'Opening stock on product creation', 9, '2026-06-22 04:47:17', '2026-06-22 04:47:17', 'App\\Models\\Site', NULL),
(2, 'TXN-20260622-0002', 3, 5, NULL, 'transfer_in', 15.00, 0.00, 0.00, 0.00, 15.00, NULL, NULL, NULL, 9, '2026-06-22 05:23:55', '2026-06-22 05:23:55', 'App\\Models\\Site', NULL),
(3, 'TXN-20260622-0003', 3, 4, 5, 'transfer_out', -15.00, 0.00, 0.00, 500.00, 485.00, NULL, NULL, NULL, 9, '2026-06-22 05:23:55', '2026-06-22 05:23:55', 'App\\Models\\Site', 'App\\Models\\Site'),
(4, 'TXN-20260622-0004', 3, 5, NULL, 'transfer_in', 150.00, 0.00, 0.00, 15.00, 165.00, 'stock_request', 1, 'Stock request #SRQ-20260622-0001', 9, '2026-06-22 05:46:26', '2026-06-22 05:46:26', 'App\\Models\\Site', NULL),
(5, 'TXN-20260622-0005', 3, 4, 5, 'transfer_out', -150.00, 0.00, 0.00, 485.00, 335.00, 'stock_request', 1, 'Stock request #SRQ-20260622-0001', 9, '2026-06-22 05:46:26', '2026-06-22 05:46:26', 'App\\Models\\Site', 'App\\Models\\Site'),
(6, 'TXN-20260622-0006', 3, 5, NULL, 'transfer_in', 100.00, 0.00, 0.00, 165.00, 265.00, NULL, NULL, NULL, 9, '2026-06-22 06:01:45', '2026-06-22 06:01:45', 'App\\Models\\Site', NULL),
(7, 'TXN-20260622-0007', 3, 4, 5, 'transfer_out', -100.00, 0.00, 0.00, 335.00, 235.00, NULL, NULL, NULL, 9, '2026-06-22 06:01:45', '2026-06-22 06:01:45', 'App\\Models\\Site', 'App\\Models\\Site'),
(8, 'TXN-20260622-0008', 3, 5, NULL, 'transfer_in', 150.00, 0.00, 0.00, 265.00, 415.00, 'stock_request', 2, 'Stock request #SRQ-20260622-0002', 9, '2026-06-22 06:02:20', '2026-06-22 06:02:20', 'App\\Models\\Site', NULL),
(9, 'TXN-20260622-0009', 3, 4, 5, 'transfer_out', -150.00, 0.00, 0.00, 235.00, 85.00, 'stock_request', 2, 'Stock request #SRQ-20260622-0002', 9, '2026-06-22 06:02:20', '2026-06-22 06:02:20', 'App\\Models\\Site', 'App\\Models\\Site');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'quantity',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `code`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Piece', 'PCS', 'quantity', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(2, 'Box', 'BOX', 'quantity', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(3, 'Kilogram', 'KG', 'weight', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(4, 'Gram', 'G', 'weight', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(5, 'Ton', 'TON', 'weight', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(6, 'Liter', 'L', 'volume', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(7, 'Milliliter', 'ML', 'volume', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(8, 'Meter', 'M', 'length', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(9, 'Centimeter', 'CM', 'length', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(10, 'Square Feet', 'SQFT', 'area', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(11, 'Packet', 'PKT', 'quantity', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(12, 'Dozen', 'DOZ', 'quantity', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(13, 'Pair', 'PR', 'quantity', 'active', '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(14, 'Milimeters', '111', 'length', 'active', '2026-06-22 05:16:50', '2026-06-22 05:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `unit_conversions`
--

CREATE TABLE `unit_conversions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_unit_id` bigint(20) UNSIGNED NOT NULL,
  `to_unit_id` bigint(20) UNSIGNED NOT NULL,
  `conversion_factor` decimal(15,6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_conversions`
--

INSERT INTO `unit_conversions` (`id`, `from_unit_id`, `to_unit_id`, `conversion_factor`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 1000.000000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(2, 4, 3, 0.001000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(3, 5, 3, 1000.000000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(4, 3, 5, 0.001000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(5, 2, 1, 12.000000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(6, 1, 2, 0.083333, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(7, 6, 7, 1000.000000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(8, 7, 6, 0.001000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(9, 8, 9, 100.000000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(10, 9, 8, 0.010000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(11, 12, 1, 12.000000, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(12, 1, 12, 0.083333, '2026-06-19 03:11:50', '2026-06-19 03:11:50'),
(13, 14, 9, 10.000000, '2026-06-22 05:18:57', '2026-06-22 05:18:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '2026-06-16 05:04:26', '$2y$12$cT8ilC.BsvFJqxOjsEMLuuwaI1RCdtoUOthnf12LIs24TbkBqxVQC', NULL, '2026-06-16 05:09:01', '2026-06-16 07:13:02'),
(2, 'Super Admin', 'superadmin@example.com', NULL, '$2y$12$cT8ilC.BsvFJqxOjsEMLuuwaI1RCdtoUOthnf12LIs24TbkBqxVQC', NULL, '2026-06-16 23:03:32', '2026-06-16 23:03:32'),
(3, 'System Admin', 'admin@erp.com', NULL, '$2y$12$cT8ilC.BsvFJqxOjsEMLuuwaI1RCdtoUOthnf12LIs24TbkBqxVQC', NULL, '2026-06-16 23:03:32', '2026-06-16 23:03:32'),
(9, 'Ronak Ray', 'ronak.ray@ronakfire.com', NULL, '$2y$12$xiKz9w043OfLzvROw6OYIeEIDEhwlYF/j1Ta7o6.iN8WIp6dSgiDO', NULL, '2026-06-17 03:18:52', '2026-06-17 03:18:52'),
(10, 'Darshna Lade', 'darshna.lade@ronakfire.com', NULL, '$2y$12$OkYYT6e8nc9XF9jEsiSTp.re4RLC.gXjths5nUteUyp1UxPWPUI5i', NULL, '2026-06-17 03:20:33', '2026-06-17 03:20:33'),
(11, 'Shwetali bavdane', 'shwetali.bavdane@ronakfire.com', NULL, '$2y$12$peD5VQ0y1lfCOM05BXrw0erOuW7JoRyVzhi1edYLlMDfoBzFnAsjG', NULL, '2026-06-17 03:21:32', '2026-06-17 03:21:32'),
(12, 'Rohit Ambedkar', 'rohit.ambedkar@ronakfire.com', NULL, '$2y$12$Uf9i9OUfyx048SjAhGRYC.UL57mQdDl6k3080ZCc0kwn8C/Yxk4lO', NULL, '2026-06-17 03:22:54', '2026-06-17 03:22:54'),
(13, 'Dhurmil Darji', 'dhurmil.darji@ronakfire.com', NULL, '$2y$12$R4FeKLC2Ga.hKYazw6.bhu9zapRM5/tfhW5GJQnwfU6KpY/QMdDdi', NULL, '2026-06-17 03:24:06', '2026-06-17 03:24:06'),
(14, 'Sanjeevani Manjerekar', 'sanjeevani.manjerekar@ronakfire.com', NULL, '$2y$12$ZYy7X2JVNym16WJ7wlbR1O/WQIAyZm3liNs33xnlR/JbEd0eaIRO6', NULL, '2026-06-17 03:25:04', '2026-06-17 03:25:04'),
(15, 'Mithun ghosh', 'mithun.ghosh@ronakfire.com', NULL, '$2y$12$uS25Ia0YUv.cdx.M8QK0I.fgSLAlXW1b44zrIOjxSQre.xJc6fx5u', NULL, '2026-06-17 03:58:21', '2026-06-17 03:58:21'),
(16, 'Dileep Madhukar', 'dileep.madhukar@ronakfire.com', NULL, '$2y$12$VQRg.LeOwNd/GqXgezcc8uipch2HnM0wy7KA6LeDcEsYF5ADx84E2', NULL, '2026-06-17 04:11:48', '2026-06-17 04:11:48'),
(17, 'Rampreet Vishwakarma', 'rampreet.vishwakarma@ronakfire.com', NULL, '$2y$12$oEyxGu7AF7Q6KXKaxPxp9.iuKTKoZh95bp/FsYxYQyJxaqfLrQ4Oi', NULL, '2026-06-17 04:12:51', '2026-06-17 04:12:51'),
(18, 'ugrasen', 'ugrasen@ronakfire.com', NULL, '$2y$12$fbm/rtqEgYkp2F1vKTTa6epb3cEIRcKa0KvgfVPY/5LM1KvPN4vvm', NULL, '2026-06-17 04:13:50', '2026-06-17 04:13:50'),
(19, 'Kishor Tare', 'kishor.tare@ronakfire.com', NULL, '$2y$12$6yubkX2wV47o/BVxM1WE7OTvxN..QvF8LivA2EM8jy95Wyh1hZVJO', NULL, '2026-06-17 04:15:08', '2026-06-17 04:15:08'),
(20, 'Sarvesh Pal', 'sarvesh.pal@ronakfire.com', NULL, '$2y$12$llxFF4C0Wf/Yg9hpzlFC8.DINapKdXT7xt7daF2EHKiND2C3aHNQ2', NULL, '2026-06-17 04:16:15', '2026-06-17 04:16:15'),
(21, 'Rama Shankar', 'rama.shankar@ronakfire.com', NULL, '$2y$12$0KZGIPXqnljA4Hooisx8LeNZ/1dM.D62Rw.Lk2vmGyDh/JPodo3Ga', NULL, '2026-06-17 04:45:26', '2026-06-17 04:45:26'),
(22, 'Ajay Saki', 'ajay.saki@ronakfire.com', NULL, '$2y$12$IbWNzSZLVjBVKkfKphlbcOQJLvH7OgXgT2MOBWk3pkwGafQvBp3zm', NULL, '2026-06-17 04:46:46', '2026-06-17 04:46:46'),
(23, 'Bhavesh Jawale', 'bhavesh.jawale@ronakfire.com', NULL, '$2y$12$oMkSQSoOekMYQi4L.C.HMOU04CIHpIoSlrLPtLXdY7gUaI7pO6p8u', NULL, '2026-06-17 04:48:43', '2026-06-17 04:48:43'),
(24, 'Priyotosh mondal', 'priyotosh.mondal@ronakfire.com', NULL, '$2y$12$rJcd.z1tVhJMxQc2coCqie117Yo2OxmtRYtTme5k0MpbXkH8YKmLC', NULL, '2026-06-17 04:49:57', '2026-06-17 04:49:57'),
(25, 'Santosh Mondak', 'santosh.mondak@ronakfire.com', NULL, '$2y$12$B2xnvB4L2XZdsyLC6I9.X.Oh2gjCgq6zjmExIAAmZlTXNCcsc7STW', NULL, '2026-06-17 05:25:07', '2026-06-17 05:25:07'),
(26, 'Sukumar Mondal', 'sukumar.mondal@ronakfire.com', NULL, '$2y$12$zSi61bx44lfzWE2alvjAY.MHbcfNTCt2LtC8CEgo63p9FSIXMOVVa', NULL, '2026-06-17 05:46:57', '2026-06-17 05:46:57'),
(27, 'Sahadeb konai', 'sahadeb.konai@ronakfire.com', NULL, '$2y$12$NYMu20DqFM0jIaQ9.3LSXuc1s00pTxmY.hGOtmB6/zDr88MjriEae', NULL, '2026-06-17 05:51:48', '2026-06-17 05:51:48'),
(28, 'Sujan Hansda', 'sujan.hansda@ronakfire.com', NULL, '$2y$12$HrwXJSZhtANzY3QxpkbrFeEudzIbrehwwMdevmdsMvRqe/nVMC0bi', NULL, '2026-06-17 05:52:34', '2026-06-17 05:52:34'),
(29, 'Rintu Ghosh', 'rintu.ghosh@ronakfire.com', NULL, '$2y$12$m1bkpcrwt95KO4ySF8M.debEE34Z80tyKdVfqLSlSDaG89lDEde.W', NULL, '2026-06-17 05:53:26', '2026-06-17 05:53:26'),
(30, 'Nayan Kahar', 'nayan.kahar@ronakfire.com', NULL, '$2y$12$nPjWJ6i335jB/ZdRS1ULoevnB9sC7oU2EZwvmPLkhyjAxnGxqVslG', NULL, '2026-06-17 05:54:24', '2026-06-17 05:54:24'),
(31, 'Nilkumar Aditya', 'nilkumar.aditya@ronakfire.com', NULL, '$2y$12$.MLR9o8MaAcUYwrx3Ith.uk9NnImW2CoSoYKPJ10sn44mfDWdsIIS', NULL, '2026-06-17 05:55:25', '2026-06-17 05:55:25'),
(32, 'Jayanta Kahar', 'jayanta.kahar@ronakfire.com', NULL, '$2y$12$WX9ZsK9KI9I8pr/SBdYWbOSnTGvIOAZCPwIsEANH.ez3pqtmRo5mK', NULL, '2026-06-17 05:56:36', '2026-06-17 05:56:36'),
(33, 'Raj Kumar Aditya', 'raj.kumar.aditya@ronakfire.com', NULL, '$2y$12$ZePto9Q6.AtbGEpFzlPRDOy5oYU61Pdr.eZSY3j9a.avugocYR1/y', NULL, '2026-06-17 05:57:33', '2026-06-17 05:57:33'),
(34, 'Raj Aditya', 'raj.aditya@ronakfire.com', NULL, '$2y$12$NFDRECdEmKNYOZPIfe9aTef3M/oBTW2637SK6.r.OBwosNYtKdctq', NULL, '2026-06-17 05:58:28', '2026-06-17 05:58:28'),
(35, 'Subodh Bholla', 'subodh.bholla@ronakfire.com', NULL, '$2y$12$BeUiT9mDcSAYzqQ9XiPQjOxLnRLZ7m92k.lbwUsiVYMiSehW7Uaey', NULL, '2026-06-17 06:00:05', '2026-06-17 06:00:05'),
(36, 'Hemant Kahar', 'hemant.kahar@ronakfire.com', NULL, '$2y$12$ebDSIS8wcOHha7AlnvXDeeR/pI.X2M3l.awvHi8TdiZR4d47xmVkG', NULL, '2026-06-17 06:01:33', '2026-06-17 06:01:33'),
(37, 'Jeet Dhibar', 'jeet.dhibar@ronakfire.com', NULL, '$2y$12$6iZZsWCOkRySDskFJRAwIu3bAQW1xs.D5dOBW4dUxXQC8J8Pca6qS', NULL, '2026-06-17 06:07:05', '2026-06-17 06:07:05'),
(38, 'Saheb Bholla', 'saheb.bholla@ronakfire.com', NULL, '$2y$12$b.xjyHedYGis2rbKTdi.z.lTkjTq2N2mcF7yi5nPLkrkceIp1mAdS', NULL, '2026-06-17 06:40:09', '2026-06-17 06:40:09'),
(39, 'Ramkrishna Bholla', 'ramkrishna.bholla@ronakfire.com', NULL, '$2y$12$iOIfFVbDhymeCF7.ZUDTw.p1k2t00Ets6p8hlQUnvnYjWsKkU1SB6', NULL, '2026-06-17 06:41:04', '2026-06-17 06:41:04'),
(40, 'Biswanath Bholla', 'biswanath.bholla@ronakfire.com', NULL, '$2y$12$dZKw8GzT4Uwly3yy2euXU.wz3mPTaX013WrQN5zYuDkDHfi.GY/j6', NULL, '2026-06-17 06:41:58', '2026-06-17 06:41:58'),
(41, 'Bappa Bholla', 'bappa.bholla@ronakfire.com', NULL, '$2y$12$max7LLq9YXt0fXFimo1SSey8LkijrdHjlyvYS/FmTImxGWbygC.AC', NULL, '2026-06-17 06:42:54', '2026-06-17 06:42:54'),
(42, 'Jiban Pal', 'jiban.pal@ronakfire.com', NULL, '$2y$12$AYpHhnylJHTJwVGvQClHaes6nTd3K9clSZB/NuUxvwRmp947u7FHW', NULL, '2026-06-17 06:43:44', '2026-06-17 06:43:44'),
(43, 'Bikash Bholla', 'bikash.bholla@ronakfire.com', NULL, '$2y$12$8WhM41gnGu0AzM7PQNio9uP2RTj9KMf2nVLS5xYtEfepMJwLoJGoG', NULL, '2026-06-17 06:44:51', '2026-06-17 06:44:51'),
(44, 'Rahul Dhibar', 'rahul.dhibar@ronakfire.com', NULL, '$2y$12$vMI3mqZ9TIaTMCrn0trifO1CdmQClcHlB9HYvp0/jFkivcXRNBr9K', NULL, '2026-06-17 06:46:09', '2026-06-17 06:46:09'),
(45, 'Suvendu Fouzder', 'suvendu.fouzder@ronakfire.com', NULL, '$2y$12$MrFDqoX3XSawoh/p7UhLRe5C6DIKoFFxDxbLN8XCMAUl3bfUn/aUC', NULL, '2026-06-17 06:47:31', '2026-06-17 06:47:31'),
(46, 'Sanjeet Mondal', 'sanjeet.mondal@ronakfire.com', NULL, '$2y$12$KOAouXZUCLCHOX3ocknHk.f7W/dTRLwG6qiIq7TdSd8FrwwCj54FW', NULL, '2026-06-17 06:51:28', '2026-06-17 06:51:28'),
(47, 'Jiban Dhibar', 'jiban.dhibar@ronakfire.com', NULL, '$2y$12$ksnE1Y9lC0k03Ut7wchSDuODNppiqsdiTSxLeQq.TBNEE0jqwZ8Ha', NULL, '2026-06-17 06:52:24', '2026-06-17 06:52:24'),
(48, 'Bishwajit Bholla', 'bishwajit.bholla@ronakfire.com', NULL, '$2y$12$z0e/QcYt9ryTf0YOUIT7J.esmiVzF34Ov7bN/M6iA/C.kYbLRxRjG', NULL, '2026-06-17 06:53:31', '2026-06-17 06:53:31'),
(49, 'Shib Shankar Roy', 'shib.shankar.roy@ronakfire.com', NULL, '$2y$12$07ZeCiq2hUnZgN/o6jsqAeVhhYEt44/J.LRafOkk2B8zai09ZsjEa', NULL, '2026-06-17 06:54:20', '2026-06-17 06:54:20'),
(50, 'Aakash Bholla', 'aakash.bholla@ronakfire.com', NULL, '$2y$12$LSXO6cGfmNyXNvGWnwipSuyeJhjbN2w0PdfE6.QesSQ2nH8lQaMqy', NULL, '2026-06-17 06:55:18', '2026-06-17 06:55:18'),
(51, 'Suvendu Dhibar', 'suvendu.dhibar@ronakfire.com', NULL, '$2y$12$fxl0qwBuXPM1YRPkTfXwMexiMgIRKEmxGtuaD3WTsm97g4Sbyz6Dm', NULL, '2026-06-17 06:56:24', '2026-06-17 06:56:24'),
(52, 'Palash Majhi', 'palash.majhi@ronakfire.com', NULL, '$2y$12$5ujnUINvzhgUxF8DXOQfE.l.ARfxUQxt7eNcEBRi89rHvRh5qAo0C', NULL, '2026-06-17 06:57:27', '2026-06-17 06:57:27'),
(53, 'Gopeshwar Mondal', 'gopeshwar.mondal@ronakfire.com', NULL, '$2y$12$2VkXQ67ep2cW53Vsbp21retq/BLiM6zzq7iDD9faC4LuKWUr0b9za', NULL, '2026-06-17 06:58:17', '2026-06-17 06:58:17'),
(54, 'Prasad Kalvikatti', 'prasad.kalvikatti@ronakfire.com', NULL, '$2y$12$Hb.xR1z3ZInjczfPmX65IetX0XqemsNO4TPEqMTK0eqki1N6nNsFi', NULL, '2026-06-17 06:59:45', '2026-06-17 06:59:45'),
(55, 'test user', 'test.user@ronakfire.com', NULL, '$2y$12$IMiQ3pzqEAR1Unm65A7lhOtKrJwe96slxtGMbZSEJqeNJ44ZtCfby', NULL, '2026-06-19 06:35:05', '2026-06-19 06:35:05'),
(56, 'Chandradeb Mondal', 'chandradeb.mondal@ronakfire.com', NULL, '$2y$12$gxENmjtWMGegURm9DwnqqeF8ebM8P/tcTF8VLIzkF.oFbaiV5g8MG', NULL, '2026-06-25 05:45:52', '2026-06-25 05:45:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_employee_id_foreign` (`employee_id`),
  ADD KEY `attendances_site_id_foreign` (`site_id`),
  ADD KEY `attendances_shift_id_foreign` (`shift_id`);

--
-- Indexes for table `audit_settings`
--
ALTER TABLE `audit_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_settings_configurable_type_configurable_id_index` (`configurable_type`,`configurable_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_reports_employee_id_foreign` (`employee_id`),
  ADD KEY `daily_reports_site_id_foreign` (`site_id`),
  ADD KEY `daily_reports_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `daily_report_histories`
--
ALTER TABLE `daily_report_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_report_histories_daily_report_id_foreign` (`daily_report_id`),
  ADD KEY `daily_report_histories_action_by_foreign` (`action_by`);

--
-- Indexes for table `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dashboard_widgets_widget_key_unique` (`widget_key`);

--
-- Indexes for table `dashboard_widget_designation`
--
ALTER TABLE `dashboard_widget_designation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dashboard_widget_designation_dashboard_widget_id_foreign` (`dashboard_widget_id`),
  ADD KEY `dashboard_widget_designation_designation_id_foreign` (`designation_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_name_unique` (`name`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `designations_name_unique` (`name`),
  ADD KEY `designations_department_id_foreign` (`department_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_emp_id_unique` (`emp_id`),
  ADD KEY `employees_user_id_foreign` (`user_id`),
  ADD KEY `employees_department_id_foreign` (`department_id`),
  ADD KEY `employees_designation_id_foreign` (`designation_id`),
  ADD KEY `employees_reporting_manager_id_foreign` (`reporting_manager_id`);

--
-- Indexes for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_leave_balances_employee_id_leave_type_id_year_unique` (`employee_id`,`leave_type_id`,`year`),
  ADD KEY `employee_leave_balances_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `employee_sites`
--
ALTER TABLE `employee_sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_sites_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_sites_site_id_foreign` (`site_id`);

--
-- Indexes for table `employee_site_histories`
--
ALTER TABLE `employee_site_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_site_histories_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_site_histories_assigned_by_id_foreign` (`assigned_by_id`),
  ADD KEY `employee_site_histories_previous_site_id_foreign` (`previous_site_id`),
  ADD KEY `employee_site_histories_new_site_id_foreign` (`new_site_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `holidays_date_unique` (`date`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leaves_employee_id_foreign` (`employee_id`),
  ADD KEY `leaves_approved_by_foreign` (`approved_by`),
  ADD KEY `leaves_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `leave_histories`
--
ALTER TABLE `leave_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_histories_leave_id_foreign` (`leave_id`),
  ADD KEY `leave_histories_action_by_foreign` (`action_by`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_types_code_unique` (`code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`),
  ADD KEY `payrolls_payroll_period_id_foreign` (`payroll_period_id`);

--
-- Indexes for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslips_payroll_id_foreign` (`payroll_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permission_dependencies`
--
ALTER TABLE `permission_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pd_unique` (`permission_id`,`depends_on_permission_id`),
  ADD KEY `permission_dependencies_depends_on_permission_id_foreign` (`depends_on_permission_id`);

--
-- Indexes for table `permission_scopes`
--
ALTER TABLE `permission_scopes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_stock_product_id_location_type_location_id_unique` (`product_id`,`location_type`,`location_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_orders_received_by_foreign` (`received_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  ADD KEY `push_subscriptions_user_id_foreign` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reports_slug_unique` (`slug`),
  ADD KEY `reports_category_id_foreign` (`category_id`),
  ADD KEY `reports_created_by_foreign` (`created_by`);

--
-- Indexes for table `report_categories`
--
ALTER TABLE `report_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_categories_slug_unique` (`slug`);

--
-- Indexes for table `report_generations`
--
ALTER TABLE `report_generations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_generations_report_id_foreign` (`report_id`),
  ADD KEY `report_generations_schedule_id_foreign` (`schedule_id`),
  ADD KEY `report_generations_generated_by_foreign` (`generated_by`);

--
-- Indexes for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_schedules_report_id_foreign` (`report_id`),
  ADD KEY `report_schedules_created_by_foreign` (`created_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_templates`
--
ALTER TABLE `role_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_structures`
--
ALTER TABLE `salary_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_structures_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_orders_delivered_by_foreign` (`delivered_by`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shifts_name_unique` (`name`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sites_name_unique` (`name`),
  ADD KEY `sites_site_manager_id_foreign` (`site_manager_id`);

--
-- Indexes for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_requests_request_number_unique` (`request_number`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_exceptions`
--
ALTER TABLE `system_exceptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_exceptions_key_unique` (`key`);

--
-- Indexes for table `transaction_ledger`
--
ALTER TABLE `transaction_ledger`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_ledger_transaction_number_unique` (`transaction_number`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `units_code_unique` (`code`);

--
-- Indexes for table `unit_conversions`
--
ALTER TABLE `unit_conversions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_conversions_from_unit_id_to_unit_id_unique` (`from_unit_id`,`to_unit_id`),
  ADD KEY `unit_conversions_to_unit_id_foreign` (`to_unit_id`);

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
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `audit_settings`
--
ALTER TABLE `audit_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `daily_report_histories`
--
ALTER TABLE `daily_report_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `dashboard_widget_designation`
--
ALTER TABLE `dashboard_widget_designation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=586;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `employee_sites`
--
ALTER TABLE `employee_sites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employee_site_histories`
--
ALTER TABLE `employee_site_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_histories`
--
ALTER TABLE `leave_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `permission_dependencies`
--
ALTER TABLE `permission_dependencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_scopes`
--
ALTER TABLE `permission_scopes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `report_categories`
--
ALTER TABLE `report_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `report_generations`
--
ALTER TABLE `report_generations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report_schedules`
--
ALTER TABLE `report_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `role_templates`
--
ALTER TABLE `role_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salary_structures`
--
ALTER TABLE `salary_structures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_exceptions`
--
ALTER TABLE `system_exceptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transaction_ledger`
--
ALTER TABLE `transaction_ledger`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `unit_conversions`
--
ALTER TABLE `unit_conversions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD CONSTRAINT `daily_reports_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_reports_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_reports_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_report_histories`
--
ALTER TABLE `daily_report_histories`
  ADD CONSTRAINT `daily_report_histories_action_by_foreign` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_report_histories_daily_report_id_foreign` FOREIGN KEY (`daily_report_id`) REFERENCES `daily_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dashboard_widget_designation`
--
ALTER TABLE `dashboard_widget_designation`
  ADD CONSTRAINT `dashboard_widget_designation_dashboard_widget_id_foreign` FOREIGN KEY (`dashboard_widget_id`) REFERENCES `dashboard_widgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dashboard_widget_designation_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_reporting_manager_id_foreign` FOREIGN KEY (`reporting_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD CONSTRAINT `employee_leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_sites`
--
ALTER TABLE `employee_sites`
  ADD CONSTRAINT `employee_sites_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_sites_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_site_histories`
--
ALTER TABLE `employee_site_histories`
  ADD CONSTRAINT `employee_site_histories_assigned_by_id_foreign` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_site_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_site_histories_new_site_id_foreign` FOREIGN KEY (`new_site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_site_histories_previous_site_id_foreign` FOREIGN KEY (`previous_site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leaves_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaves_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_histories`
--
ALTER TABLE `leave_histories`
  ADD CONSTRAINT `leave_histories_action_by_foreign` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_histories_leave_id_foreign` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payrolls_payroll_period_id_foreign` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_dependencies`
--
ALTER TABLE `permission_dependencies`
  ADD CONSTRAINT `permission_dependencies_depends_on_permission_id_foreign` FOREIGN KEY (`depends_on_permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_dependencies_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD CONSTRAINT `push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `report_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_generations`
--
ALTER TABLE `report_generations`
  ADD CONSTRAINT `report_generations_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `report_generations_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_generations_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `report_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD CONSTRAINT `report_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `report_schedules_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_structures`
--
ALTER TABLE `salary_structures`
  ADD CONSTRAINT `salary_structures_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_delivered_by_foreign` FOREIGN KEY (`delivered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD CONSTRAINT `sales_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sites`
--
ALTER TABLE `sites`
  ADD CONSTRAINT `sites_site_manager_id_foreign` FOREIGN KEY (`site_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `unit_conversions`
--
ALTER TABLE `unit_conversions`
  ADD CONSTRAINT `unit_conversions_from_unit_id_foreign` FOREIGN KEY (`from_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_conversions_to_unit_id_foreign` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
