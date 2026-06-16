-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 08:10 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `check_in` timestamp NULL DEFAULT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'present',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
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
(1, 'Headquarters', 'Main City', '2026-06-13 01:58:40', '2026-06-13 01:58:40'),
(2, 'Mumbai Warehouse', 'Mumbai', '2026-06-13 03:03:59', '2026-06-13 03:03:59'),
(3, 'Pune Warehouse', 'Pune', '2026-06-13 03:04:00', '2026-06-13 03:04:00'),
(4, 'Delhi Hub', 'Delhi', '2026-06-13 03:04:00', '2026-06-13 03:04:00');

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
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:17:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:9:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;i:7;i:8;i:8;i:9;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"manage employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:7;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"view attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"view inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:7;i:4;i:8;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:13:\"view products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:7;i:4;i:8;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"view categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"view warehouses\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"manage suppliers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"view invoices\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:9;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:16:\"manage inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:7;i:3;i:8;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:14:\"manage payroll\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"manage sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:16:\"manage purchases\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"create sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:9;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:16:\"manage transfers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:8;}}}s:5:\"roles\";a:9:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"Super Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"Admin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"Manager\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:2:\"HR\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:8:\"Employee\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:10:\"Accountant\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"Warehouse Manager\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"Inventory Staff\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:15:\"Sales Executive\";s:1:\"c\";s:3:\"web\";}}}', 1781525273);

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

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `gst_number`, `address`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'Prasad Kalvikatti', 'pkalvikatti@gmail.com', NULL, 'ABC123456PQR151', 'abc gjh', '2026-06-14 23:58:04', '2026-06-14 23:58:04', 1);

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
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
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
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `shift` varchar(255) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `reporting_manager_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `department`, `salary`, `shift`, `joining_date`, `status`, `created_at`, `updated_at`, `branch_id`, `designation`, `reporting_manager_id`) VALUES
(1, 104, 'Operations', 80000.00, NULL, '2026-06-13', 'active', '2026-06-13 03:04:00', '2026-06-13 03:04:00', 2, 'Warehouse Manager', NULL),
(2, 105, 'HR', 60000.00, NULL, '2026-06-13', 'active', '2026-06-13 03:04:00', '2026-06-13 03:04:00', 2, 'HR Specialist', 104),
(3, 106, 'Operations', 80000.00, NULL, '2026-06-13', 'active', '2026-06-13 03:04:00', '2026-06-13 03:04:00', 3, 'Warehouse Manager', NULL),
(4, 107, 'HR', 60000.00, NULL, '2026-06-13', 'active', '2026-06-13 03:04:00', '2026-06-13 03:04:00', 3, 'HR Specialist', 106),
(5, 108, 'Operations', 80000.00, NULL, '2026-06-13', 'active', '2026-06-13 03:04:01', '2026-06-13 03:04:01', 4, 'Warehouse Manager', NULL),
(6, 109, 'HR', 60000.00, NULL, '2026-06-13', 'active', '2026-06-13 03:04:01', '2026-06-13 03:04:01', 4, 'HR Specialist', 108);

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
(12, 3, 4, 40, '2026-06-13 03:04:53', '2026-06-13 03:04:53');

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
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
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
(56, '2026_06_15_055456_create_category_product_table', 7);

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
(7, 'App\\Models\\User', 108);

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
  `month_year` varchar(255) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view dashboard', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(2, 'manage employees', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(3, 'view attendance', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(4, 'view inventory', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(5, 'view products', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(6, 'view categories', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(7, 'view warehouses', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(8, 'manage suppliers', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(9, 'view invoices', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(10, 'view reports', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(11, 'manage settings', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(12, 'manage inventory', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(13, 'manage payroll', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(14, 'manage sales', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(15, 'manage purchases', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(16, 'create sales', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(17, 'manage transfers', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16');

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
(1, 'App\\Models\\User', 101, 'auth_token', 'c9a66cef1578d08b5b688a76e91b0ce64f842507682fbc71f8a3f1ea9eff67a1', '[\"*\"]', '2026-06-10 08:00:23', NULL, '2026-06-10 07:59:30', '2026-06-10 08:00:23'),
(2, 'App\\Models\\User', 101, 'auth_token', '3c36e00ae315d8b443dc45548e0dff7ce6f84458a221ff9be3143de5e7c30b67', '[\"*\"]', '2026-06-13 01:28:00', NULL, '2026-06-10 08:00:54', '2026-06-13 01:28:00'),
(3, 'App\\Models\\User', 101, 'auth_token', '93e85456265c8574ca8c468cef57c7379978f3c963b75ccac6b31020a2c984f8', '[\"*\"]', '2026-06-10 13:53:24', NULL, '2026-06-10 13:20:23', '2026-06-10 13:53:24'),
(4, 'App\\Models\\User', 101, 'auth_token', 'fa05b9fbc90df105d1c0fa153ca3861d5d0160a639a82718e90059684a0c2b02', '[\"*\"]', '2026-06-10 14:28:12', NULL, '2026-06-10 13:28:04', '2026-06-10 14:28:12'),
(5, 'App\\Models\\User', 102, 'auth_token', '2466ac511f96ccc0e24ddb386753cc3cb269484f214c14b37b514858bacc0e4a', '[\"*\"]', '2026-06-10 14:30:27', NULL, '2026-06-10 14:30:15', '2026-06-10 14:30:27'),
(7, 'App\\Models\\User', 102, 'auth_token', '00f00c61fa6a36dbeb2456af7d523fcac30a4979ffcad22ddd93d2c64644fae0', '[\"*\"]', '2026-06-11 04:17:00', NULL, '2026-06-11 04:07:19', '2026-06-11 04:17:00'),
(8, 'App\\Models\\User', 102, 'auth_token', '005974f10f0622cd235b17c90ac6a0ab84afb473701583d12f5adab20fa9cd90', '[\"*\"]', '2026-06-11 04:22:47', NULL, '2026-06-11 04:22:46', '2026-06-11 04:22:47'),
(9, 'App\\Models\\User', 102, 'auth_token', 'b0c337ba1c61146637ed81ceb187aec73c570bda24d337586897c14ee2f27d55', '[\"*\"]', '2026-06-11 04:25:40', NULL, '2026-06-11 04:24:22', '2026-06-11 04:25:40'),
(10, 'App\\Models\\User', 102, 'auth_token', '1448d70e4fe1984ac725117362fd069054af74c410352fca2fe980cab4541ab7', '[\"*\"]', '2026-06-11 04:30:19', NULL, '2026-06-11 04:25:52', '2026-06-11 04:30:19'),
(11, 'App\\Models\\User', 102, 'auth_token', 'ba9da8db6af5b385b6189e56b6a27a31b704a01091d67918e29e51dae8130203', '[\"*\"]', '2026-06-13 03:59:05', NULL, '2026-06-11 04:31:23', '2026-06-13 03:59:05'),
(12, 'App\\Models\\User', 102, 'auth_token', '171731bc8f08601ee155769c2de4393b077f1411f708b78be93e43d305f98e9e', '[\"*\"]', '2026-06-13 08:30:24', NULL, '2026-06-13 04:03:15', '2026-06-13 08:30:24'),
(13, 'App\\Models\\User', 104, 'auth_token', '442096510baf56776c380ddd940ad13c497e5b52a69722663bcce05f647536f4', '[\"*\"]', '2026-06-13 08:37:18', NULL, '2026-06-13 08:37:06', '2026-06-13 08:37:18'),
(14, 'App\\Models\\User', 101, 'auth_token', 'bd93e32e8017ef05fbd9375c5d243366f17c802406a01b175761e5cb3b6d9523', '[\"*\"]', '2026-06-14 23:57:28', NULL, '2026-06-13 08:37:45', '2026-06-14 23:57:28'),
(15, 'App\\Models\\User', 103, 'test', '5f8977cf28f2d287ddbd1a1ee67438e986a1452cd3631819a267241d2d3a4956', '[\"*\"]', '2026-06-13 09:07:24', NULL, '2026-06-13 09:07:23', '2026-06-13 09:07:24'),
(16, 'App\\Models\\User', 101, 'auth_token', 'ca044fae8728633f5e8f8014b50a721b47511d917919501b8616aca53c10a0ef', '[\"*\"]', '2026-06-15 00:41:58', NULL, '2026-06-14 23:57:42', '2026-06-15 00:41:58'),
(17, 'App\\Models\\User', 104, 'auth_token', '39310ae4a1d4c8ce86700225da67f2a015e1f870fd6d3a485cc562c8fd675a81', '[\"*\"]', '2026-06-15 00:43:05', NULL, '2026-06-15 00:42:09', '2026-06-15 00:43:05'),
(18, 'App\\Models\\User', 101, 'auth_token', '3fdef9603b522039c8396521dd96b4cef1389941106edd61a3cbe3cea6244547', '[\"*\"]', '2026-06-15 00:43:20', NULL, '2026-06-15 00:43:09', '2026-06-15 00:43:20'),
(19, 'App\\Models\\User', 105, 'auth_token', '3868b1c1fbdaffd2acc86ed2c9276b83a2509aab3ca6f52dea8b8326cee5d262', '[\"*\"]', '2026-06-15 00:43:58', NULL, '2026-06-15 00:43:49', '2026-06-15 00:43:58'),
(20, 'App\\Models\\User', 101, 'auth_token', '943a8ffa4b88e975282ab7c9b813b428834bdc5d4107a59e4cd22aec72b4c985', '[\"*\"]', '2026-06-15 02:31:03', NULL, '2026-06-15 00:45:06', '2026-06-15 02:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
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

INSERT INTO `products` (`id`, `sku`, `name`, `category_id`, `purchase_price`, `selling_price`, `gst_percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LAP-X1', 'Laptop X1', 1, 1000.00, 1200.00, 0.00, 'active', '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(2, 'DESK-PRO', 'Desktop Pro', 1, 600.00, 800.00, 0.00, 'active', '2026-06-13 03:04:53', '2026-06-13 03:04:53'),
(3, 'CHAIR-01', 'Office Chair', 2, 100.00, 150.00, 0.00, 'active', '2026-06-13 03:04:53', '2026-06-13 03:04:53');

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
(1, 'PO-1781456724', 1, 1, 1000.00, 0.00, 'Approved', NULL, '2026-06-14 11:35:24', '2026-06-14 23:15:54', 101, 101),
(2, 'PO-1781458366', 1, 1, 2000.00, 0.00, 'Pending Approval', NULL, '2026-06-14 12:02:46', '2026-06-14 12:02:46', 101, NULL);

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
(1, 1, 1, 1, 0, 1000.00, 0.00, 1000.00, '2026-06-14 11:35:24', '2026-06-14 11:35:24'),
(2, 2, 1, 2, 0, 1000.00, 0.00, 2000.00, '2026-06-14 12:02:46', '2026-06-14 12:02:46');

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2026-06-11 09:54:50', '2026-06-13 01:57:54'),
(2, 'Admin', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(3, 'Manager', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(4, 'HR', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(5, 'Employee', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(6, 'Accountant', 'web', '2026-06-13 01:56:24', '2026-06-13 01:56:24'),
(7, 'Warehouse Manager', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(8, 'Inventory Staff', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16'),
(9, 'Sales Executive', 'web', '2026-06-13 02:29:16', '2026-06-13 02:29:16');

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
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 7),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 1),
(4, 2),
(4, 3),
(4, 7),
(4, 8),
(5, 1),
(5, 2),
(5, 3),
(5, 7),
(5, 8),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(9, 6),
(9, 9),
(10, 1),
(10, 2),
(10, 3),
(10, 4),
(10, 5),
(10, 6),
(10, 7),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(12, 7),
(12, 8),
(13, 1),
(13, 2),
(13, 4),
(14, 1),
(14, 2),
(14, 6),
(15, 1),
(15, 2),
(15, 6),
(16, 1),
(16, 2),
(16, 9),
(17, 1),
(17, 2),
(17, 8);

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
(1, 'SO-1781503671', 1, 1, 1200.00, 0.00, 0.00, 'Pending Approval', NULL, '2026-06-15 00:37:51', '2026-06-15 00:37:51', 101, NULL),
(2, 'SO-1781503698', 1, 1, 3600.00, 0.00, 0.00, 'Draft', NULL, '2026-06-15 00:38:18', '2026-06-15 00:38:18', 101, NULL),
(3, 'SO-1781503711', 1, 1, 3600.00, 0.00, 0.00, 'Pending Approval', NULL, '2026-06-15 00:38:31', '2026-06-15 00:38:31', 101, NULL);

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
('h197uzalop3jRiIylFnqkXQGL74hYCoQFJqtJH6D', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWxDcXg3VERhRWNQU1EwclJTTkZvU0pyR3I1VnM1UWZ5S0ozeldXTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781117879),
('XbAdbC7sp3mlLcZ3iye8yDGnn9pBjn8h1TV2D3Vy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ3NJN3ZYY01BZk1Wak9iUmFmZ25nS0JNbERVV09BMVpXTWZpZFdHWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYW5jdHVtL2NzcmYtY29va2llIjtzOjU6InJvdXRlIjtzOjE5OiJzYW5jdHVtLmNzcmYtY29va2llIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1780995463),
('xhoHQfFIkQbodl5y9gUVCHWYhwDZRJwIIe6ZG4nX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.123.2 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnJFMVVKWUh4c0xJQk5nMUpRdlJCUTM4Qm9kcld5bXphNUVGWFBHWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781098193);

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
(1, 'ABC Solutions', 'abcSolution@gmail.com', '7894561112', NULL, 'abc xyz pqr', '2026-06-14 17:03:02', '2026-06-14 23:52:36', 1);

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `branch_id`) VALUES
(101, 'Admin', 'admin@example.com', '2026-06-10 13:28:26', '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, NULL, '2026-06-13 09:09:45', 1),
(102, 'Admin', 'superadmin@example.com', '2026-06-10 13:28:26', '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, NULL, NULL, NULL),
(103, 'System Admin', 'admin@erp.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 01:58:41', '2026-06-13 01:58:41', 1),
(104, 'Mumbai Manager', 'mumbaimanager@example.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 03:04:00', '2026-06-13 03:04:00', 2),
(105, 'Mumbai HR', 'mumbaihr@example.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 03:04:00', '2026-06-13 03:04:00', 2),
(106, 'Pune Manager', 'punemanager@example.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 03:04:00', '2026-06-13 03:04:00', 3),
(107, 'Pune HR', 'punehr@example.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 03:04:00', '2026-06-13 03:04:00', 3),
(108, 'Delhi Manager', 'delhimanager@example.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 4),
(109, 'Delhi HR', 'delhihr@example.com', NULL, '$2y$10$ABJ76ApqPXk2crfBCMEpWOgmzNKDIJRsRVu1CmjYpWYAcukSBPuq.', NULL, '2026-06-13 03:04:01', '2026-06-13 03:04:01', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_employee_id_date_unique` (`employee_id`,`date`),
  ADD KEY `attendances_branch_id_foreign` (`branch_id`);

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_branch_id_foreign` (`branch_id`),
  ADD KEY `categories_created_by_foreign` (`created_by`);

--
-- Indexes for table `category_product`
--
ALTER TABLE `category_product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_product_category_id_product_id_unique` (`category_id`,`product_id`),
  ADD KEY `category_product_product_id_foreign` (`product_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `customer_quotations`
--
ALTER TABLE `customer_quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_quotations_quotation_number_unique` (`quotation_number`),
  ADD KEY `customer_quotations_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_quotations_branch_id_foreign` (`branch_id`),
  ADD KEY `customer_quotations_created_by_foreign` (`created_by`),
  ADD KEY `customer_quotations_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `customer_quotation_items`
--
ALTER TABLE `customer_quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_quotation_items_customer_quotation_id_foreign` (`customer_quotation_id`),
  ADD KEY `customer_quotation_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_notes_delivery_number_unique` (`delivery_number`),
  ADD KEY `delivery_notes_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `delivery_notes_branch_id_foreign` (`branch_id`),
  ADD KEY `delivery_notes_delivered_by_foreign` (`delivered_by`);

--
-- Indexes for table `delivery_note_items`
--
ALTER TABLE `delivery_note_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_note_items_delivery_note_id_foreign` (`delivery_note_id`),
  ADD KEY `delivery_note_items_sales_order_item_id_foreign` (`sales_order_item_id`),
  ADD KEY `delivery_note_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_documentable_type_documentable_id_index` (`documentable_type`,`documentable_id`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employees_user_id_foreign` (`user_id`),
  ADD KEY `employees_branch_id_foreign` (`branch_id`),
  ADD KEY `employees_reporting_manager_id_foreign` (`reporting_manager_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `goods_receipt_notes`
--
ALTER TABLE `goods_receipt_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goods_receipt_notes_grn_number_unique` (`grn_number`),
  ADD KEY `goods_receipt_notes_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `goods_receipt_notes_branch_id_foreign` (`branch_id`),
  ADD KEY `goods_receipt_notes_received_by_foreign` (`received_by`);

--
-- Indexes for table `grn_items`
--
ALTER TABLE `grn_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grn_items_goods_receipt_note_id_foreign` (`goods_receipt_note_id`),
  ADD KEY `grn_items_purchase_order_item_id_foreign` (`purchase_order_item_id`),
  ADD KEY `grn_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventories_product_id_branch_id_unique` (`product_id`,`branch_id`),
  ADD KEY `inventories_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_transactions_inventory_id_foreign` (`inventory_id`),
  ADD KEY `inventory_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_transfers_source_branch_id_foreign` (`source_branch_id`),
  ADD KEY `inventory_transfers_destination_branch_id_foreign` (`destination_branch_id`),
  ADD KEY `inventory_transfers_product_id_foreign` (`product_id`),
  ADD KEY `inventory_transfers_requested_by_foreign` (`requested_by`),
  ADD KEY `inventory_transfers_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `invoices_branch_id_foreign` (`branch_id`),
  ADD KEY `invoices_customer_id_foreign` (`customer_id`),
  ADD KEY `invoices_created_by_foreign` (`created_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_items_product_id_foreign` (`product_id`);

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
  ADD KEY `leaves_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notes_notable_type_notable_id_index` (`notable_type`,`notable_id`),
  ADD KEY `notes_created_by_foreign` (`created_by`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_invoice_id_foreign` (`invoice_id`),
  ADD KEY `payments_branch_id_foreign` (`branch_id`),
  ADD KEY `payments_supplier_id_foreign` (`supplier_id`),
  ADD KEY `payments_customer_id_foreign` (`customer_id`),
  ADD KEY `payments_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `payments_sales_order_id_foreign` (`sales_order_id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`),
  ADD KEY `payrolls_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

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
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  ADD KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_orders_branch_id_foreign` (`branch_id`),
  ADD KEY `purchase_orders_requested_by_foreign` (`requested_by`),
  ADD KEY `purchase_orders_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `purchase_order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_returns_return_number_unique` (`return_number`),
  ADD KEY `purchase_returns_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_returns_branch_id_foreign` (`branch_id`),
  ADD KEY `purchase_returns_goods_receipt_note_id_foreign` (`goods_receipt_note_id`),
  ADD KEY `purchase_returns_processed_by_foreign` (`processed_by`);

--
-- Indexes for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_return_items_purchase_return_id_foreign` (`purchase_return_id`),
  ADD KEY `purchase_return_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_orders_so_number_unique` (`so_number`),
  ADD KEY `sales_orders_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_orders_branch_id_foreign` (`branch_id`),
  ADD KEY `sales_orders_created_by_foreign` (`created_by`),
  ADD KEY `sales_orders_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_items_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_returns_return_number_unique` (`return_number`),
  ADD KEY `sales_returns_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_returns_branch_id_foreign` (`branch_id`),
  ADD KEY `sales_returns_invoice_id_foreign` (`invoice_id`),
  ADD KEY `sales_returns_processed_by_foreign` (`processed_by`);

--
-- Indexes for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_return_items_sales_return_id_foreign` (`sales_return_id`),
  ADD KEY `sales_return_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suppliers_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_quotations_quotation_number_unique` (`quotation_number`),
  ADD KEY `supplier_quotations_supplier_id_foreign` (`supplier_id`),
  ADD KEY `supplier_quotations_branch_id_foreign` (`branch_id`),
  ADD KEY `supplier_quotations_created_by_foreign` (`created_by`),
  ADD KEY `supplier_quotations_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `supplier_quotation_items`
--
ALTER TABLE `supplier_quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_quotation_items_supplier_quotation_id_foreign` (`supplier_quotation_id`),
  ADD KEY `supplier_quotation_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_employee_id_foreign` (`employee_id`),
  ADD KEY `tasks_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category_product`
--
ALTER TABLE `category_product`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_quotations`
--
ALTER TABLE `customer_quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_quotation_items`
--
ALTER TABLE `customer_quotation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_notes`
--
ALTER TABLE `delivery_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_note_items`
--
ALTER TABLE `delivery_note_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goods_receipt_notes`
--
ALTER TABLE `goods_receipt_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grn_items`
--
ALTER TABLE `grn_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
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
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_returns`
--
ALTER TABLE `sales_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_quotation_items`
--
ALTER TABLE `supplier_quotation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `category_product`
--
ALTER TABLE `category_product`
  ADD CONSTRAINT `category_product_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_quotations`
--
ALTER TABLE `customer_quotations`
  ADD CONSTRAINT `customer_quotations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_quotations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_quotations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_quotation_items`
--
ALTER TABLE `customer_quotation_items`
  ADD CONSTRAINT `customer_quotation_items_customer_quotation_id_foreign` FOREIGN KEY (`customer_quotation_id`) REFERENCES `customer_quotations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD CONSTRAINT `delivery_notes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_notes_delivered_by_foreign` FOREIGN KEY (`delivered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delivery_notes_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_note_items`
--
ALTER TABLE `delivery_note_items`
  ADD CONSTRAINT `delivery_note_items_delivery_note_id_foreign` FOREIGN KEY (`delivery_note_id`) REFERENCES `delivery_notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_note_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_note_items_sales_order_item_id_foreign` FOREIGN KEY (`sales_order_item_id`) REFERENCES `sales_order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_reporting_manager_id_foreign` FOREIGN KEY (`reporting_manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `goods_receipt_notes`
--
ALTER TABLE `goods_receipt_notes`
  ADD CONSTRAINT `goods_receipt_notes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goods_receipt_notes_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goods_receipt_notes_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `grn_items`
--
ALTER TABLE `grn_items`
  ADD CONSTRAINT `grn_items_goods_receipt_note_id_foreign` FOREIGN KEY (`goods_receipt_note_id`) REFERENCES `goods_receipt_notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_items_purchase_order_item_id_foreign` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  ADD CONSTRAINT `inventory_transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_transfers_destination_branch_id_foreign` FOREIGN KEY (`destination_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfers_source_branch_id_foreign` FOREIGN KEY (`source_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_orders_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_returns_goods_receipt_note_id_foreign` FOREIGN KEY (`goods_receipt_note_id`) REFERENCES `goods_receipt_notes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_returns_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD CONSTRAINT `purchase_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_return_items_purchase_return_id_foreign` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD CONSTRAINT `sales_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_order_items_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_returns_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD CONSTRAINT `sales_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_return_items_sales_return_id_foreign` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  ADD CONSTRAINT `supplier_quotations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_quotations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_quotations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_quotations_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_quotation_items`
--
ALTER TABLE `supplier_quotation_items`
  ADD CONSTRAINT `supplier_quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_quotation_items_supplier_quotation_id_foreign` FOREIGN KEY (`supplier_quotation_id`) REFERENCES `supplier_quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
