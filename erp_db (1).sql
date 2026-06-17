-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 02:55 PM
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
  `working_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overtime` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Present',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shift_id` bigint(20) UNSIGNED DEFAULT NULL,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Godown 1', 'Bhyander', '2026-06-13 01:58:40', '2026-06-13 01:58:40'),
(2, 'Headquarters', 'Bhyander Office', '2026-06-16 23:03:12', '2026-06-16 23:03:12');

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
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:111:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"create sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:3;i:1;i:5;i:2;i:1;i:3;i:2;i:4;i:9;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"manage employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:3;i:1;i:1;i:2;i:2;i:3;i:7;i:4;i:4;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"manage inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:3;i:1;i:5;i:2;i:1;i:3;i:2;i:4;i:7;i:5;i:8;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"manage payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:3;i:1;i:1;i:2;i:2;i:3;i:4;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"manage purchases\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:3;i:1;i:1;i:2;i:2;i:3;i:6;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"manage sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"manage suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"manage transfers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:8;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:15:\"view attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:3;i:1;i:5;i:2;i:1;i:3;i:2;i:4;i:4;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"view categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:4;i:4;i:6;i:5;i:8;i:6;i:9;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:14:\"view inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:8;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:13:\"view invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:9;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:13:\"view products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:8;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:4;i:4;i:6;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:15:\"view warehouses\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:16:\"update_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"view_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:8;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:15:\"create_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:7;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:15:\"update_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:7;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:15:\"delete_products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:14:\"view_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:9;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:16:\"create_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:16:\"update_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:16:\"delete_customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:14:\"view_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:16:\"create_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:16:\"update_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:16:\"delete_suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:10:\"view_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:12:\"create_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:12:\"update_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:12:\"delete_sites\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:15:\"department.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:17:\"department.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:15:\"department.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"department.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:16:\"designation.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:18:\"designation.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:16:\"designation.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:18:\"designation.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"employee.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:4;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:15:\"employee.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:13:\"employee.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:15:\"employee.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:23:\"employee.assign-manager\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:20:\"employee.assign-role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:15:\"view_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:8;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:17:\"create_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:17:\"update_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:17:\"delete_categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"view_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"create_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:17:\"update_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:14:\"view_inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:8;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:16:\"manage_inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:8;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:13:\"view_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:9;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:15:\"create_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:9;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:15:\"update_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:15:\"delete_invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:13:\"view_payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:15:\"create_payments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:12:\"view_payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:14:\"manage_payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:11:\"view_leaves\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:13:\"create_leaves\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:17:\"view_sales_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:9;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:19:\"create_sales_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:20:\"view_purchase_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:22:\"create_purchase_orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:12:\"manage_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:18:\"manage_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:13:\"document.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:15:\"document.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:13:\"document.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:15:\"document.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:17:\"document.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:16:\"document.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:22:\"document.manage-expiry\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:9:\"site.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:11:\"site.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:9:\"site.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:11:\"site.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:11:\"site.assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:13:\"site.transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:17:\"site.history.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:17:\"delete_attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:15:\"attendance.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:17:\"attendance.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:15:\"attendance.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:17:\"attendance.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:18:\"attendance.checkin\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:19:\"attendance.checkout\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:10:\"shift.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:12:\"shift.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:10:\"shift.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:12:\"shift.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:17:\"daily-report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:19:\"daily-report.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:17:\"daily-report.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:19:\"daily-report.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:19:\"daily-report.submit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:20:\"daily-report.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:19:\"daily-report.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:19:\"daily-report.rework\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:24:\"daily-report.report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}}s:5:\"roles\";a:9:{i:0;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"Manager\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:8:\"Employee\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"Super Admin\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"Admin\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:15:\"Sales Executive\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"Warehouse Manager\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:2:\"HR\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"Inventory Staff\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:10:\"Accountant\";s:1:\"c\";s:3:\"web\";}}}', 1781787039);

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
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `status`, `parent_id`, `branch_id`, `created_by`) VALUES
(1, 'Electronics', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL, NULL),
(2, 'Furniture', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL, NULL),
(3, 'Machinery', NULL, '2026-06-15 01:12:18', '2026-06-15 01:12:18', 'Active', NULL, NULL, NULL),
(1, 'Electronics', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL, NULL),
(2, 'Furniture', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 'Active', NULL, NULL, NULL),
(3, 'Machinery', NULL, '2026-06-15 01:12:18', '2026-06-15 01:12:18', 'Active', NULL, NULL, NULL);

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
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

-- --------------------------------------------------------

--
-- Table structure for table `delivery_notes`
--

CREATE TABLE `delivery_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_number` varchar(255) NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
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
(1, 'Administration', 'Core management, system administration, and executive oversight', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(2, 'Production & Workshop', 'Manufacturing, fabrication, welding, and assembly operations', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(3, 'Finance & Accounts', 'Financial management, bookkeeping, billing, and tax compliance', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(4, 'Engineering & Design', 'Product development, drafting, and CAD structural design', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(5, 'Inventory & Warehouse', 'Stock management, store logistics, and material tracking', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(6, 'Human Resources', 'Recruitment, employee relations, payroll, and compliance', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(7, 'Information Technology', 'IT support, network infrastructure, and hardware maintenance', '2026-06-17 08:44:21', '2026-06-17 08:44:21', NULL),
(8, 'Sales & Marketing', 'Client acquisition, business development, marketing campaigns, and account management', '2026-06-17 09:19:35', '2026-06-17 09:19:35', NULL);

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
(1, 'Super Admin', 1, 'Full system access and overarching corporate control', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(2, 'System Admin', 1, 'Technical system configuration and network administration', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(3, 'Admin', 1, 'General administrative management and office operations', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(4, 'General Manager', 1, 'Overall operational management and executive oversight', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(5, 'Production Manager', 2, 'Oversight of workshop operations and manufacturing schedules', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(6, 'Workshop Supervisor', 2, 'Direct supervision of technicians and workshop floor activities', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(7, 'Fitter', 2, 'Mechanical fitting, assembly, and structural alignment tasks', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(8, 'Welder', 2, 'Welding, fabrication, and metal joining operations', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(9, 'Electrician', 2, 'Electrical installation, maintenance, and wiring troubleshooting', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(10, 'Helper', 2, 'General workshop assistance, cleanup, and manual labor support', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(11, 'Finance Manager', 3, 'Oversight of financial strategies, budgets, and reporting', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(12, 'Accountant', 3, 'Day-to-day bookkeeping, invoicing, and ledger maintenance', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(13, 'Design Manager', 4, 'Oversight of the design team and project blueprints', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(14, 'Designer', 4, 'Product development, drafting, and CAD structural design', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(15, 'Store Manager', 5, 'Inventory control, warehouse stock management, and logistics', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(16, 'HR Manager', 6, 'Oversight of recruitment, payroll, and employee relations', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL),
(17, 'IT Manager', 7, 'Oversight of company technology infrastructure and security', '2026-06-17 08:45:44', '2026-06-17 08:45:44', NULL);

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
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
(9, 9, '000', 'Ronak Ray', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2023-06-19', 1, 2, 'Full-Time', NULL, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12th', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:18:52', '2026-06-17 03:18:52', NULL),
(10, 10, '101', 'Darshna Lade', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2023-10-10', 3, 12, 'Full-Time', 9, 'Female', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12th', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:20:33', '2026-06-17 03:20:33', NULL),
(11, 11, '102', 'shwetali bavdane', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2020-10-10', 3, 12, 'Full-Time', 9, 'Female', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:21:32', '2026-06-17 03:21:32', NULL),
(12, 12, '103', 'Rohit Ambedkar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2020-10-10', 2, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:22:54', '2026-06-17 03:22:54', NULL),
(13, 13, '104', 'Dhurmil Darji', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:24:06', '2026-06-17 03:45:06', NULL),
(14, 14, '105', 'Sanjeevani Manjerekar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 4, 13, 'Full-Time', 9, 'Female', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 03:25:03', '2026-06-17 03:25:04', NULL),
(15, 15, '106', 'Mithun ghosh', 'employees/ykRCToTlDZ95KQ10octsC1Ph4wmDvHuj33LXWKJQ.jpg', '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc zy', '123546798', '123546798', '123546798', '12', 0, 0, 0, 'employees/W79OYzhqvcY8YBeRutj9KMLfqa3XkstOXgrtADi6.pdf', 'employees/fKkAwhiS0ntju9VSDk32strQoG7f8dO4USnHvk5j.pdf', 'employees/2I70YqGovURVtX9iwwe1Q5TdrE5kKdjPVnehSvPt.pdf', 'employees/9ooxbV8Vc4VSuz7PASFrB1SxtpNkyErs563rEPfg.pdf', '2026-06-17 03:58:21', '2026-06-17 03:58:21', NULL),
(16, 16, '107', 'Dileep Madhukar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:11:48', '2026-06-17 04:11:48', NULL),
(17, 17, '108', 'Rampreet Vishwakarma', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:12:51', '2026-06-17 04:12:51', NULL),
(18, 18, '109', 'ugrasen', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:13:50', '2026-06-17 04:13:50', NULL),
(19, 19, '110', 'Kishor Tare', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:15:08', '2026-06-17 04:15:08', NULL),
(20, 20, '111', 'Sarvesh Pal', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:16:15', '2026-06-17 04:16:15', NULL),
(21, 21, '112', 'Rama Shankar', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 9, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:45:26', '2026-06-17 04:45:26', NULL),
(22, 22, '113', 'Ajay Saki', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 1, NULL, NULL, NULL, NULL, '2026-06-17 04:46:46', '2026-06-17 04:46:46', NULL),
(23, 23, '114', 'Bhavesh Jawale', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 5, 6, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:48:43', '2026-06-17 04:48:43', NULL),
(24, 24, '115', 'Priyotosh mondal', NULL, '2026-06-17', '2026-06-17', '2026-06-17', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '123546798', '123546798', '123546798', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 04:49:57', '2026-06-17 04:49:57', NULL),
(27, 25, '116', 'Santosh Mondak', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 1, NULL, NULL, NULL, NULL, '2026-06-17 05:25:07', '2026-06-17 05:25:07', NULL),
(28, 26, '117', 'Sukumar Konai', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:46:57', '2026-06-17 05:50:48', NULL),
(29, 27, '118', 'Sahadeb konai', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:51:48', '2026-06-17 05:51:48', NULL),
(30, 28, '119', 'Sujan Hansda', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2020-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'ab c xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:52:34', '2026-06-17 05:52:34', NULL),
(31, 29, '120', 'Rintu Ghosh', NULL, '2020-10-10', '2020-10-10', '2020-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:53:26', '2026-06-17 05:53:26', NULL),
(32, 30, '121', 'Nayan Kahar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-02-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xtyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:54:23', '2026-06-17 05:54:24', NULL),
(33, 31, '122', 'Nilkumar Aditya', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:55:25', '2026-06-17 05:55:25', NULL),
(34, 32, '123', 'Jayanta Kahar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'PAN', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 1, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:56:35', '2026-06-17 05:56:36', NULL),
(35, 33, '124', 'Raj Kumar Aditya', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:57:33', '2026-06-17 05:57:33', NULL),
(36, 34, '125', 'Raj Aditya', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', NULL, '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 05:58:28', '2026-06-17 05:58:28', NULL),
(37, 35, '126', 'Subodh Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:00:05', '2026-06-17 06:00:05', NULL),
(38, 36, '127', 'Hemant Kahar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:01:33', '2026-06-17 06:01:33', NULL),
(39, 37, '128', 'Jeet Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:07:05', '2026-06-17 06:07:05', NULL),
(40, 38, '129', 'Saheb Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 1, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:40:09', '2026-06-17 06:40:09', NULL),
(41, 39, '130', 'Ramkrishna Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:41:04', '2026-06-17 06:41:04', NULL),
(42, 40, '131', 'Biswanath Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 1, 1, NULL, NULL, NULL, NULL, '2026-06-17 06:41:58', '2026-06-17 06:41:58', NULL),
(43, 41, '132', 'Bappa Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 1, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:42:54', '2026-06-17 06:42:54', NULL),
(44, 42, '133', 'Jiban Pal', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:43:44', '2026-06-17 06:43:44', NULL),
(45, 43, '134', 'Bikash Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 1, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:44:51', '2026-06-17 06:44:51', NULL),
(46, 44, '135', 'Rahul Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:46:09', '2026-06-17 06:46:09', NULL),
(47, 45, '136', 'Suvendu Fouzder', NULL, '2010-10-10', '2010-10-21', '2010-10-10', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:47:31', '2026-06-17 06:47:31', NULL),
(48, 46, '137', 'Sanjeet Mondal', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 7, 'Full-Time', 9, 'Male', 'Single', 'PAN', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:51:28', '2026-06-17 06:51:28', NULL),
(49, 47, '138', 'Jiban Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 8, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:52:23', '2026-06-17 06:52:24', NULL),
(50, 48, '139', 'Bishwajit Bholla', NULL, '2010-10-10', '2010-10-10', '2010-02-10', '2010-10-10', 8, 15, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:53:30', '2026-06-17 06:53:31', NULL),
(51, 49, '140', 'Shib Shankar Roy', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:54:20', '2026-06-17 06:54:20', NULL),
(52, 50, '141', 'Aakash Bholla', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:55:18', '2026-06-17 06:55:18', NULL),
(53, 51, '142', 'Suvendu Dhibar', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:56:24', '2026-06-17 06:56:24', NULL),
(54, 52, '143', 'Palash Majhi', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:57:27', '2026-06-17 06:57:27', NULL),
(55, 53, '144', 'Gopeshwar Mondal', NULL, '2010-10-10', '2010-10-10', '2010-10-10', '2010-10-10', 2, 10, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '1234567890', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:58:16', '2026-06-17 06:58:17', NULL),
(56, 54, '145', 'Prasad Kalvikatti', NULL, '2026-05-27', '2026-06-15', '2026-09-15', '2003-10-02', 7, 2, 'Full-Time', 9, 'Male', 'Single', 'Aadhaar', 'abc xyz', '9022428111', '1234567890', '1234567890', '12', 0, 0, 0, NULL, NULL, NULL, NULL, '2026-06-17 06:59:45', '2026-06-17 06:59:45', NULL);

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
(3, 14, 2, '2026-06-17', NULL, '2026-06-17 04:00:17', '2026-06-17 04:00:17');

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
(3, 14, 3, NULL, 2, '2026-06-17 03:59:43', NULL, 'Assigned to site', '2026-06-17 03:59:43', '2026-06-17 03:59:43'),
(4, 14, 3, 2, NULL, '2026-06-17 03:59:58', NULL, 'abc', '2026-06-17 03:59:58', '2026-06-17 03:59:58'),
(5, 14, 3, NULL, 2, '2026-06-17 04:00:17', NULL, 'Assigned to site', '2026-06-17 04:00:17', '2026-06-17 04:00:17');

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
  `branch_id` bigint(20) UNSIGNED NOT NULL,
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
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `product_id`, `branch_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(2, 1, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(3, 1, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(4, 1, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(5, 2, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(6, 2, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(7, 2, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(8, 2, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(9, 3, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(10, 3, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(11, 3, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(12, 3, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(1, 1, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(2, 1, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(3, 1, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(4, 1, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(5, 2, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(6, 2, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(7, 2, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(8, 2, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(9, 3, 1, 10, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(10, 3, 2, 20, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(11, 3, 3, 30, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(12, 3, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(1, 1, 1, 10, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(2, 1, 2, 20, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(3, 1, 3, 30, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(4, 1, 4, 40, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(5, 2, 1, 10, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(6, 2, 2, 20, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(7, 2, 3, 30, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(8, 2, 4, 40, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(9, 3, 1, 10, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(10, 3, 2, 20, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(11, 3, 3, 30, '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(12, 3, 4, 40, '2026-06-12 21:34:53', '2026-06-12 21:34:53');

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
  `source_branch_id` bigint(20) UNSIGNED NOT NULL,
  `destination_branch_id` bigint(20) UNSIGNED NOT NULL,
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
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `pdf_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `payment_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(79, '2026_06_17_125213_create_holidays_table', 21);

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
(1, 'App\\Models\\User', 102),
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 103),
(4, 'App\\Models\\User', 105),
(4, 'App\\Models\\User', 107),
(4, 'App\\Models\\User', 109),
(7, 'App\\Models\\User', 104),
(7, 'App\\Models\\User', 106),
(7, 'App\\Models\\User', 108),
(1, 'App\\Models\\User', 102),
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 103),
(4, 'App\\Models\\User', 105),
(4, 'App\\Models\\User', 107),
(4, 'App\\Models\\User', 109),
(7, 'App\\Models\\User', 104),
(7, 'App\\Models\\User', 106),
(7, 'App\\Models\\User', 108),
(1, 'App\\Models\\User', 102),
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 103),
(4, 'App\\Models\\User', 105),
(4, 'App\\Models\\User', 107),
(4, 'App\\Models\\User', 109),
(7, 'App\\Models\\User', 104),
(7, 'App\\Models\\User', 106),
(7, 'App\\Models\\User', 108),
(1, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(5, 'App\\Models\\User', 4),
(5, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 7),
(5, 'App\\Models\\User', 8),
(5, 'App\\Models\\User', 9),
(5, 'App\\Models\\User', 10),
(5, 'App\\Models\\User', 11),
(5, 'App\\Models\\User', 12),
(5, 'App\\Models\\User', 13),
(5, 'App\\Models\\User', 14),
(5, 'App\\Models\\User', 15),
(5, 'App\\Models\\User', 16),
(5, 'App\\Models\\User', 17),
(5, 'App\\Models\\User', 18),
(5, 'App\\Models\\User', 19),
(5, 'App\\Models\\User', 20),
(5, 'App\\Models\\User', 21),
(5, 'App\\Models\\User', 22),
(5, 'App\\Models\\User', 23),
(5, 'App\\Models\\User', 24),
(5, 'App\\Models\\User', 25),
(5, 'App\\Models\\User', 26),
(5, 'App\\Models\\User', 27),
(5, 'App\\Models\\User', 28),
(5, 'App\\Models\\User', 29),
(5, 'App\\Models\\User', 30),
(5, 'App\\Models\\User', 31),
(5, 'App\\Models\\User', 32),
(5, 'App\\Models\\User', 33),
(5, 'App\\Models\\User', 34),
(5, 'App\\Models\\User', 35),
(5, 'App\\Models\\User', 36),
(5, 'App\\Models\\User', 37),
(5, 'App\\Models\\User', 38),
(5, 'App\\Models\\User', 39),
(5, 'App\\Models\\User', 40),
(5, 'App\\Models\\User', 41),
(5, 'App\\Models\\User', 42),
(5, 'App\\Models\\User', 43),
(5, 'App\\Models\\User', 44),
(5, 'App\\Models\\User', 45),
(5, 'App\\Models\\User', 46),
(5, 'App\\Models\\User', 47),
(5, 'App\\Models\\User', 48),
(5, 'App\\Models\\User', 49),
(5, 'App\\Models\\User', 50),
(5, 'App\\Models\\User', 51),
(5, 'App\\Models\\User', 52),
(5, 'App\\Models\\User', 53),
(5, 'App\\Models\\User', 54);

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
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(111, 'daily-report.report.view', 'web', '2026-06-17 07:15:10', '2026-06-17 07:15:10', NULL);

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
(23, 'App\\Models\\User', 3, 'auth_token', '4e6b0d2f1b1790c57a41fd38927d1dd0d165ae1abf08bb6d1b01c3b2dc4a4bba', '[\"*\"]', '2026-06-17 07:24:51', NULL, '2026-06-17 05:16:05', '2026-06-17 07:24:51'),
(24, 'App\\Models\\User', 3, 'test', '258518818e6ec9f616291e1bafbf9364ba3463ebe17822daa8d1ac0da1e528b8', '[\"*\"]', '2026-06-17 05:19:50', NULL, '2026-06-17 05:18:34', '2026-06-17 05:19:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `gst_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `category_id`, `supplier_id`, `purchase_price`, `selling_price`, `gst_percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LAP-X1', 'Laptop X1', 1, NULL, 1000.00, 1200.00, 0.00, 'active', '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(2, 'DESK-PRO', 'Desktop Pro', 1, NULL, 600.00, 800.00, 0.00, 'active', '2026-06-12 21:34:53', '2026-06-12 21:34:53'),
(3, 'CHAIR-01', 'Office Chair', 2, NULL, 100.00, 150.00, 0.00, 'active', '2026-06-12 21:34:53', '2026-06-12 21:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier_id`, `branch_id`, `total_amount`, `tax_amount`, `status`, `notes`, `created_at`, `updated_at`, `requested_by`, `approved_by`) VALUES
(1, 'PO-1781456724', 1, 1, 1000.00, 0.00, 'Approved', NULL, '2026-06-14 06:05:24', '2026-06-14 17:45:54', 101, 101),
(2, 'PO-1781458366', 1, 1, 2000.00, 0.00, 'Pending Approval', NULL, '2026-06-14 06:32:46', '2026-06-14 06:32:46', 101, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `received_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `product_id`, `quantity`, `received_quantity`, `unit_cost`, `tax`, `total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 0, 1000.00, 0.00, 1000.00, '2026-06-14 06:05:24', '2026-06-14 06:05:24'),
(2, 2, 1, 2, 0, 1000.00, 0.00, 2000.00, '2026-06-14 06:32:46', '2026-06-14 06:32:46');

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
(1, 'Super Admin', 'web', '2026-06-11 04:24:50', '2026-06-12 20:27:54', NULL),
(2, 'Admin', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(3, 'Manager', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(4, 'HR', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(5, 'Employee', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(6, 'Accountant', 'web', '2026-06-12 20:26:24', '2026-06-12 20:26:24', NULL),
(7, 'Warehouse Manager', 'web', '2026-06-12 20:59:16', '2026-06-12 20:59:16', NULL),
(8, 'Inventory Staff', 'web', '2026-06-12 20:59:16', '2026-06-12 20:59:16', NULL),
(9, 'Sales Executive', 'web', '2026-06-12 20:59:16', '2026-06-12 20:59:16', NULL);

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
(1, 3),
(1, 5),
(2, 3),
(3, 3),
(3, 5),
(4, 3),
(5, 3),
(10, 3),
(10, 5),
(12, 1),
(2, 1),
(10, 1),
(13, 1),
(3, 1),
(15, 1),
(11, 1),
(17, 1),
(8, 1),
(14, 1),
(16, 1),
(7, 1),
(4, 1),
(6, 1),
(5, 1),
(1, 1),
(9, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(12, 2),
(2, 2),
(10, 2),
(13, 2),
(3, 2),
(15, 2),
(11, 2),
(17, 2),
(8, 2),
(14, 2),
(16, 2),
(7, 2),
(4, 2),
(6, 2),
(5, 2),
(1, 2),
(9, 2),
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
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(89, 2),
(90, 2),
(91, 2),
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
(57, 2),
(58, 2),
(92, 2),
(93, 2),
(94, 2),
(95, 2),
(96, 2),
(97, 2),
(98, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
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
(103, 2),
(104, 2),
(105, 2),
(106, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(12, 7),
(2, 7),
(13, 7),
(3, 7),
(15, 7),
(16, 7),
(46, 7),
(59, 7),
(60, 7),
(22, 7),
(23, 7),
(24, 7),
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
(12, 6),
(14, 6),
(6, 6),
(5, 6),
(16, 6),
(61, 6),
(62, 6),
(63, 6),
(65, 6),
(66, 6),
(26, 6),
(30, 6),
(71, 6),
(73, 6),
(12, 8),
(13, 8),
(3, 8),
(15, 8),
(9, 8),
(59, 8),
(60, 8),
(22, 8),
(52, 8),
(12, 9),
(1, 9),
(14, 9),
(26, 9),
(27, 9),
(28, 9),
(71, 9),
(72, 9),
(61, 9),
(62, 9);

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `so_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_orders`
--

INSERT INTO `sales_orders` (`id`, `so_number`, `customer_id`, `branch_id`, `total_amount`, `tax_amount`, `discount_amount`, `status`, `notes`, `created_at`, `updated_at`, `created_by`, `approved_by`) VALUES
(1, 'SO-1781503671', 1, 1, 1200.00, 0.00, 0.00, 'Pending Approval', NULL, '2026-06-14 19:07:51', '2026-06-14 19:07:51', 101, NULL),
(2, 'SO-1781503698', 1, 1, 3600.00, 0.00, 0.00, 'Draft', NULL, '2026-06-14 19:08:18', '2026-06-14 19:08:18', 101, NULL),
(3, 'SO-1781503711', 1, 1, 3600.00, 0.00, 0.00, 'Pending Approval', NULL, '2026-06-14 19:08:31', '2026-06-14 19:08:31', 101, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `delivered_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
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

INSERT INTO `shifts` (`id`, `branch_id`, `name`, `start_time`, `end_time`, `grace_period`, `late_threshold`, `half_day_threshold`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Regular', '09:00:00', '18:00:00', 10, 10, 30, 'Active', '2026-06-17 07:10:02', '2026-06-17 07:10:02');

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
  `site_manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sites`
--

INSERT INTO `sites` (`id`, `name`, `code`, `client_details`, `address`, `city`, `state`, `country`, `pincode`, `contact_person`, `phone`, `email`, `status`, `latitude`, `longitude`, `site_manager_id`, `created_at`, `updated_at`) VALUES
(2, 'Site A', '101', NULL, NULL, NULL, NULL, 'India', NULL, NULL, NULL, NULL, 'Active', NULL, NULL, NULL, '2026-06-17 03:59:08', '2026-06-17 03:59:08');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `gst_number`, `address`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'ABC Solutions', 'abcSolution@gmail.com', '7894561112', NULL, 'abc xyz pqr', '2026-06-14 11:33:02', '2026-06-14 18:22:36', 1);

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
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(9, 'Ronak Ray', 'ronak.ray@company.com', NULL, '$2y$12$xiKz9w043OfLzvROw6OYIeEIDEhwlYF/j1Ta7o6.iN8WIp6dSgiDO', NULL, '2026-06-17 03:18:52', '2026-06-17 03:18:52'),
(10, 'Darshna Lade', 'darshna.lade@company.com', NULL, '$2y$12$OkYYT6e8nc9XF9jEsiSTp.re4RLC.gXjths5nUteUyp1UxPWPUI5i', NULL, '2026-06-17 03:20:33', '2026-06-17 03:20:33'),
(11, 'Shwetali bavdane', 'shwetali.bavdane@company.com', NULL, '$2y$12$peD5VQ0y1lfCOM05BXrw0erOuW7JoRyVzhi1edYLlMDfoBzFnAsjG', NULL, '2026-06-17 03:21:32', '2026-06-17 03:21:32'),
(12, 'Rohit Ambedkar', 'rohit.ambedkar@company.com', NULL, '$2y$12$Uf9i9OUfyx048SjAhGRYC.UL57mQdDl6k3080ZCc0kwn8C/Yxk4lO', NULL, '2026-06-17 03:22:54', '2026-06-17 03:22:54'),
(13, 'Dhurmil Darji', 'dhurmil.darji@company.com', NULL, '$2y$12$R4FeKLC2Ga.hKYazw6.bhu9zapRM5/tfhW5GJQnwfU6KpY/QMdDdi', NULL, '2026-06-17 03:24:06', '2026-06-17 03:24:06'),
(14, 'Sanjeevani Manjerekar', 'sanjeevani.manjerekar@company.com', NULL, '$2y$12$ZYy7X2JVNym16WJ7wlbR1O/WQIAyZm3liNs33xnlR/JbEd0eaIRO6', NULL, '2026-06-17 03:25:04', '2026-06-17 03:25:04'),
(15, 'Mithun ghosh', 'mithun.ghosh@company.com', NULL, '$2y$12$uS25Ia0YUv.cdx.M8QK0I.fgSLAlXW1b44zrIOjxSQre.xJc6fx5u', NULL, '2026-06-17 03:58:21', '2026-06-17 03:58:21'),
(16, 'Dileep Madhukar', 'dileep.madhukar@company.com', NULL, '$2y$12$VQRg.LeOwNd/GqXgezcc8uipch2HnM0wy7KA6LeDcEsYF5ADx84E2', NULL, '2026-06-17 04:11:48', '2026-06-17 04:11:48'),
(17, 'Rampreet Vishwakarma', 'rampreet.vishwakarma@company.com', NULL, '$2y$12$oEyxGu7AF7Q6KXKaxPxp9.iuKTKoZh95bp/FsYxYQyJxaqfLrQ4Oi', NULL, '2026-06-17 04:12:51', '2026-06-17 04:12:51'),
(18, 'ugrasen', 'ugrasen@company.com', NULL, '$2y$12$fbm/rtqEgYkp2F1vKTTa6epb3cEIRcKa0KvgfVPY/5LM1KvPN4vvm', NULL, '2026-06-17 04:13:50', '2026-06-17 04:13:50'),
(19, 'Kishor Tare', 'kishor.tare@company.com', NULL, '$2y$12$6yubkX2wV47o/BVxM1WE7OTvxN..QvF8LivA2EM8jy95Wyh1hZVJO', NULL, '2026-06-17 04:15:08', '2026-06-17 04:15:08'),
(20, 'Sarvesh Pal', 'sarvesh.pal@company.com', NULL, '$2y$12$llxFF4C0Wf/Yg9hpzlFC8.DINapKdXT7xt7daF2EHKiND2C3aHNQ2', NULL, '2026-06-17 04:16:15', '2026-06-17 04:16:15'),
(21, 'Rama Shankar', 'rama.shankar@company.com', NULL, '$2y$12$0KZGIPXqnljA4Hooisx8LeNZ/1dM.D62Rw.Lk2vmGyDh/JPodo3Ga', NULL, '2026-06-17 04:45:26', '2026-06-17 04:45:26'),
(22, 'Ajay Saki', 'ajay.saki@company.com', NULL, '$2y$12$IbWNzSZLVjBVKkfKphlbcOQJLvH7OgXgT2MOBWk3pkwGafQvBp3zm', NULL, '2026-06-17 04:46:46', '2026-06-17 04:46:46'),
(23, 'Bhavesh Jawale', 'bhavesh.jawale@company.com', NULL, '$2y$12$oMkSQSoOekMYQi4L.C.HMOU04CIHpIoSlrLPtLXdY7gUaI7pO6p8u', NULL, '2026-06-17 04:48:43', '2026-06-17 04:48:43'),
(24, 'Priyotosh mondal', 'priyotosh.mondal@company.com', NULL, '$2y$12$rJcd.z1tVhJMxQc2coCqie117Yo2OxmtRYtTme5k0MpbXkH8YKmLC', NULL, '2026-06-17 04:49:57', '2026-06-17 04:49:57'),
(25, 'Santosh Mondak', 'santosh.mondak@company.com', NULL, '$2y$12$B2xnvB4L2XZdsyLC6I9.X.Oh2gjCgq6zjmExIAAmZlTXNCcsc7STW', NULL, '2026-06-17 05:25:07', '2026-06-17 05:25:07'),
(26, 'Sukumar Mondal', 'sukumar.mondal@company.com', NULL, '$2y$12$zSi61bx44lfzWE2alvjAY.MHbcfNTCt2LtC8CEgo63p9FSIXMOVVa', NULL, '2026-06-17 05:46:57', '2026-06-17 05:46:57'),
(27, 'Sahadeb konai', 'sahadeb.konai@company.com', NULL, '$2y$12$NYMu20DqFM0jIaQ9.3LSXuc1s00pTxmY.hGOtmB6/zDr88MjriEae', NULL, '2026-06-17 05:51:48', '2026-06-17 05:51:48'),
(28, 'Sujan Hansda', 'sujan.hansda@company.com', NULL, '$2y$12$HrwXJSZhtANzY3QxpkbrFeEudzIbrehwwMdevmdsMvRqe/nVMC0bi', NULL, '2026-06-17 05:52:34', '2026-06-17 05:52:34'),
(29, 'Rintu Ghosh', 'rintu.ghosh@company.com', NULL, '$2y$12$m1bkpcrwt95KO4ySF8M.debEE34Z80tyKdVfqLSlSDaG89lDEde.W', NULL, '2026-06-17 05:53:26', '2026-06-17 05:53:26'),
(30, 'Nayan Kahar', 'nayan.kahar@company.com', NULL, '$2y$12$nPjWJ6i335jB/ZdRS1ULoevnB9sC7oU2EZwvmPLkhyjAxnGxqVslG', NULL, '2026-06-17 05:54:24', '2026-06-17 05:54:24'),
(31, 'Nilkumar Aditya', 'nilkumar.aditya@company.com', NULL, '$2y$12$.MLR9o8MaAcUYwrx3Ith.uk9NnImW2CoSoYKPJ10sn44mfDWdsIIS', NULL, '2026-06-17 05:55:25', '2026-06-17 05:55:25'),
(32, 'Jayanta Kahar', 'jayanta.kahar@company.com', NULL, '$2y$12$WX9ZsK9KI9I8pr/SBdYWbOSnTGvIOAZCPwIsEANH.ez3pqtmRo5mK', NULL, '2026-06-17 05:56:36', '2026-06-17 05:56:36'),
(33, 'Raj Kumar Aditya', 'raj.kumar.aditya@company.com', NULL, '$2y$12$ZePto9Q6.AtbGEpFzlPRDOy5oYU61Pdr.eZSY3j9a.avugocYR1/y', NULL, '2026-06-17 05:57:33', '2026-06-17 05:57:33'),
(34, 'Raj Aditya', 'raj.aditya@company.com', NULL, '$2y$12$NFDRECdEmKNYOZPIfe9aTef3M/oBTW2637SK6.r.OBwosNYtKdctq', NULL, '2026-06-17 05:58:28', '2026-06-17 05:58:28'),
(35, 'Subodh Bholla', 'subodh.bholla@company.com', NULL, '$2y$12$BeUiT9mDcSAYzqQ9XiPQjOxLnRLZ7m92k.lbwUsiVYMiSehW7Uaey', NULL, '2026-06-17 06:00:05', '2026-06-17 06:00:05'),
(36, 'Hemant Kahar', 'hemant.kahar@company.com', NULL, '$2y$12$ebDSIS8wcOHha7AlnvXDeeR/pI.X2M3l.awvHi8TdiZR4d47xmVkG', NULL, '2026-06-17 06:01:33', '2026-06-17 06:01:33'),
(37, 'Jeet Dhibar', 'jeet.dhibar@company.com', NULL, '$2y$12$6iZZsWCOkRySDskFJRAwIu3bAQW1xs.D5dOBW4dUxXQC8J8Pca6qS', NULL, '2026-06-17 06:07:05', '2026-06-17 06:07:05'),
(38, 'Saheb Bholla', 'saheb.bholla@company.com', NULL, '$2y$12$b.xjyHedYGis2rbKTdi.z.lTkjTq2N2mcF7yi5nPLkrkceIp1mAdS', NULL, '2026-06-17 06:40:09', '2026-06-17 06:40:09'),
(39, 'Ramkrishna Bholla', 'ramkrishna.bholla@company.com', NULL, '$2y$12$iOIfFVbDhymeCF7.ZUDTw.p1k2t00Ets6p8hlQUnvnYjWsKkU1SB6', NULL, '2026-06-17 06:41:04', '2026-06-17 06:41:04'),
(40, 'Biswanath Bholla', 'biswanath.bholla@company.com', NULL, '$2y$12$dZKw8GzT4Uwly3yy2euXU.wz3mPTaX013WrQN5zYuDkDHfi.GY/j6', NULL, '2026-06-17 06:41:58', '2026-06-17 06:41:58'),
(41, 'Bappa Bholla', 'bappa.bholla@company.com', NULL, '$2y$12$max7LLq9YXt0fXFimo1SSey8LkijrdHjlyvYS/FmTImxGWbygC.AC', NULL, '2026-06-17 06:42:54', '2026-06-17 06:42:54'),
(42, 'Jiban Pal', 'jiban.pal@company.com', NULL, '$2y$12$AYpHhnylJHTJwVGvQClHaes6nTd3K9clSZB/NuUxvwRmp947u7FHW', NULL, '2026-06-17 06:43:44', '2026-06-17 06:43:44'),
(43, 'Bikash Bholla', 'bikash.bholla@company.com', NULL, '$2y$12$8WhM41gnGu0AzM7PQNio9uP2RTj9KMf2nVLS5xYtEfepMJwLoJGoG', NULL, '2026-06-17 06:44:51', '2026-06-17 06:44:51'),
(44, 'Rahul Dhibar', 'rahul.dhibar@company.com', NULL, '$2y$12$vMI3mqZ9TIaTMCrn0trifO1CdmQClcHlB9HYvp0/jFkivcXRNBr9K', NULL, '2026-06-17 06:46:09', '2026-06-17 06:46:09'),
(45, 'Suvendu Fouzder', 'suvendu.fouzder@company.com', NULL, '$2y$12$MrFDqoX3XSawoh/p7UhLRe5C6DIKoFFxDxbLN8XCMAUl3bfUn/aUC', NULL, '2026-06-17 06:47:31', '2026-06-17 06:47:31'),
(46, 'Sanjeet Mondal', 'sanjeet.mondal@company.com', NULL, '$2y$12$KOAouXZUCLCHOX3ocknHk.f7W/dTRLwG6qiIq7TdSd8FrwwCj54FW', NULL, '2026-06-17 06:51:28', '2026-06-17 06:51:28'),
(47, 'Jiban Dhibar', 'jiban.dhibar@company.com', NULL, '$2y$12$ksnE1Y9lC0k03Ut7wchSDuODNppiqsdiTSxLeQq.TBNEE0jqwZ8Ha', NULL, '2026-06-17 06:52:24', '2026-06-17 06:52:24'),
(48, 'Bishwajit Bholla', 'bishwajit.bholla@company.com', NULL, '$2y$12$z0e/QcYt9ryTf0YOUIT7J.esmiVzF34Ov7bN/M6iA/C.kYbLRxRjG', NULL, '2026-06-17 06:53:31', '2026-06-17 06:53:31'),
(49, 'Shib Shankar Roy', 'shib.shankar.roy@company.com', NULL, '$2y$12$07ZeCiq2hUnZgN/o6jsqAeVhhYEt44/J.LRafOkk2B8zai09ZsjEa', NULL, '2026-06-17 06:54:20', '2026-06-17 06:54:20'),
(50, 'Aakash Bholla', 'aakash.bholla@company.com', NULL, '$2y$12$LSXO6cGfmNyXNvGWnwipSuyeJhjbN2w0PdfE6.QesSQ2nH8lQaMqy', NULL, '2026-06-17 06:55:18', '2026-06-17 06:55:18'),
(51, 'Suvendu Dhibar', 'suvendu.dhibar@company.com', NULL, '$2y$12$fxl0qwBuXPM1YRPkTfXwMexiMgIRKEmxGtuaD3WTsm97g4Sbyz6Dm', NULL, '2026-06-17 06:56:24', '2026-06-17 06:56:24'),
(52, 'Palash Majhi', 'palash.majhi@company.com', NULL, '$2y$12$5ujnUINvzhgUxF8DXOQfE.l.ARfxUQxt7eNcEBRi89rHvRh5qAo0C', NULL, '2026-06-17 06:57:27', '2026-06-17 06:57:27'),
(53, 'Gopeshwar Mondal', 'gopeshwar.mondal@company.com', NULL, '$2y$12$2VkXQ67ep2cW53Vsbp21retq/BLiM6zzq7iDD9faC4LuKWUr0b9za', NULL, '2026-06-17 06:58:17', '2026-06-17 06:58:17'),
(54, 'Prasad Kalvikatti', 'prasad.kalvikatti@company.com', NULL, '$2y$12$Hb.xR1z3ZInjczfPmX65IetX0XqemsNO4TPEqMTK0eqki1N6nNsFi', NULL, '2026-06-17 06:59:45', '2026-06-17 06:59:45');

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
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_report_histories`
--
ALTER TABLE `daily_report_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_sites`
--
ALTER TABLE `employee_sites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_site_histories`
--
ALTER TABLE `employee_site_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_histories`
--
ALTER TABLE `leave_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

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
  ADD CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sites`
--
ALTER TABLE `sites`
  ADD CONSTRAINT `sites_site_manager_id_foreign` FOREIGN KEY (`site_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
