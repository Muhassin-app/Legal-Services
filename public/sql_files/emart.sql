-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: royoorders-2-db-staging-cluster.cvgfslznkneq.us-west-2.rds.amazonaws.com
-- Generation Time: Aug 01, 2023 at 06:45 AM
-- Server version: 8.0.28
-- PHP Version: 7.4.3-4ubuntu2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `royo_africanvillagemarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `addon_options`
--

CREATE TABLE `addon_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addon_id` bigint UNSIGNED NOT NULL,
  `position` smallint NOT NULL DEFAULT '1',
  `price` decimal(16,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `square_modifier_option_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `addon_options`
--

INSERT INTO `addon_options` (`id`, `title`, `addon_id`, `position`, `price`, `created_at`, `updated_at`, `deleted_at`, `square_modifier_option_id`) VALUES
(1, 'Small parcel', 1, 1, '100.00000000', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `addon_option_translations`
--

CREATE TABLE `addon_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addon_opt_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `addon_option_translations`
--

INSERT INTO `addon_option_translations` (`id`, `title`, `addon_opt_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Small parcel', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `addon_sets`
--

CREATE TABLE `addon_sets` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_select` tinyint NOT NULL DEFAULT '1',
  `max_select` tinyint NOT NULL DEFAULT '1',
  `position` smallint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `is_core` tinyint NOT NULL DEFAULT '1' COMMENT '0 - no, 1 - yes',
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `square_modifier_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `addon_sets`
--

INSERT INTO `addon_sets` (`id`, `title`, `min_select`, `max_select`, `position`, `status`, `is_core`, `vendor_id`, `created_at`, `updated_at`, `square_modifier_id`) VALUES
(1, 'Small Parcels', 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `addon_set_translations`
--

CREATE TABLE `addon_set_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addon_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `addon_set_translations`
--

INSERT INTO `addon_set_translations` (`id`, `title`, `addon_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Small Parcels', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

CREATE TABLE `api_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `controller` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `models` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_dynamic_tutorials`
--

CREATE TABLE `app_dynamic_tutorials` (
  `id` bigint UNSIGNED NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort` int DEFAULT '1',
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'image' COMMENT 'image/video',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_stylings`
--

CREATE TABLE `app_stylings` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint DEFAULT NULL COMMENT '1-Text, 2-Option, 3-Option Images, 4-Color',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_stylings`
--

INSERT INTO `app_stylings` (`id`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Regular Font', 2, NULL, NULL),
(2, 'Medium Font', 2, NULL, NULL),
(3, 'Bold Font', 2, NULL, NULL),
(4, 'Primary Color', 4, NULL, NULL),
(5, 'Secondary Color', 4, NULL, NULL),
(6, 'Tertiary Color', 4, NULL, NULL),
(7, 'Tab Bar Style', 3, NULL, NULL),
(8, 'Home Page Style', 3, NULL, NULL),
(9, 'Home Tag Line', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_styling_options`
--

CREATE TABLE `app_styling_options` (
  `id` bigint UNSIGNED NOT NULL,
  `app_styling_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_selected` tinyint NOT NULL DEFAULT '1' COMMENT '1-yes, 2-no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `template_id` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `app_styling_options`
--

INSERT INTO `app_styling_options` (`id`, `app_styling_id`, `name`, `image`, `is_selected`, `created_at`, `updated_at`, `template_id`) VALUES
(1, 1, 'CircularStd-Book', NULL, 1, NULL, NULL, NULL),
(2, 1, 'SFProText-Regular', NULL, 0, NULL, NULL, NULL),
(3, 1, 'Futura-Normal', NULL, 0, NULL, NULL, NULL),
(4, 1, 'Poppins-Regular', NULL, 0, NULL, NULL, NULL),
(5, 1, 'Eina02-Regular', NULL, 0, NULL, NULL, NULL),
(6, 1, 'Poppins-Regular', NULL, 0, NULL, NULL, NULL),
(7, 2, 'CircularStd-Medium', NULL, 1, NULL, NULL, NULL),
(8, 2, 'SFProText-Medium', NULL, 0, NULL, NULL, NULL),
(9, 2, 'Futura-Medium', NULL, 0, NULL, NULL, NULL),
(10, 2, 'Eina02-SemiBold', NULL, 0, NULL, NULL, NULL),
(11, 2, 'Poppins-Medium', NULL, 0, NULL, NULL, NULL),
(12, 3, 'CircularStd-Bold', NULL, 1, NULL, NULL, NULL),
(13, 3, 'SFProText-Bold', NULL, 0, NULL, NULL, NULL),
(14, 3, 'FuturaBT-Heavy', NULL, 0, NULL, NULL, NULL),
(15, 3, 'Poppins-Bold', NULL, 0, NULL, NULL, NULL),
(16, 3, 'SFProText-Bold', NULL, 0, NULL, NULL, NULL),
(17, 3, 'Poppins-Bold', NULL, 0, NULL, NULL, NULL),
(18, 4, '#41A2E6', NULL, 1, NULL, NULL, NULL),
(19, 5, '#fff', NULL, 1, NULL, NULL, NULL),
(20, 6, '#fff', NULL, 1, NULL, NULL, NULL),
(21, 7, 'Tab 1', 'bar.png', 1, NULL, NULL, 1),
(22, 7, 'Tab 2', 'bar_two.png', 0, NULL, NULL, 2),
(23, 7, 'Tab 3', 'bar_three.png', 0, NULL, NULL, 3),
(24, 7, 'Tab 4', 'bar_four.png', 0, NULL, NULL, 4),
(25, 7, 'Tab 5', 'bar_five.png', 0, NULL, NULL, 5),
(26, 8, 'Home Page 1', 'home.png', 1, NULL, NULL, 1),
(27, 8, 'Home Page 4', 'home_four.png', 0, NULL, NULL, 2),
(28, 8, 'Home Page 5', 'home_five.png', 0, NULL, NULL, 3),
(29, 8, 'Home Page 6', 'home_six.png', 0, NULL, NULL, 4),
(30, 8, 'Home Page 7', 'home_seven.png', 0, NULL, '2023-06-05 09:56:18', 6),
(31, 8, 'Home Page 8', 'home_eight.png', 0, NULL, '2023-06-05 09:56:18', 7),
(32, 8, 'Home Page 9', 'home_nine.png', 0, NULL, NULL, 8),
(33, 8, 'Home Page 10', 'home_ten.png', 0, NULL, NULL, 9),
(34, 8, 'Home Page 11', 'home_eleven.png', 0, NULL, NULL, 10),
(35, 8, 'Home Page 12', 'home_twelve.png', 0, NULL, NULL, 11),
(36, 9, 'Create a free account and join us!', NULL, 1, NULL, NULL, NULL),
(37, 7, 'Tab 6', 'bar_six.png', 0, '2023-06-05 09:56:18', '2023-06-05 09:56:18', 6);

-- --------------------------------------------------------

--
-- Table structure for table `assign_qrcodes_to_orders`
--

CREATE TABLE `assign_qrcodes_to_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `batch_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 for dropdown, 2 for color',
  `position` smallint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_categories`
--

CREATE TABLE `attribute_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `attribute_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_options`
--

CREATE TABLE `attribute_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `hexacode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` smallint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_option_translations`
--

CREATE TABLE `attribute_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_option_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_translations`
--

CREATE TABLE `attribute_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE `audits` (
  `id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint UNSIGNED NOT NULL,
  `old_values` text COLLATE utf8mb4_unicode_ci,
  `new_values` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1023) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audits`
--

INSERT INTO `audits` (`id`, `user_type`, `user_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `url`, `ip_address`, `user_agent`, `tags`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'created', 'App\\Models\\User', 18, '[]', '{\"name\":\"Testap\",\"phone_number\":\"121121212\",\"dial_code\":\"234\",\"password\":\"$2y$10$SRex.FbWrm\\/LGOZowJNZD.FxmabAwbEtcYOL4tnmeGNSTDArKmzwm\",\"type\":1,\"status\":1,\"role_id\":1,\"email\":\"Testapp@gmail.com\",\"is_email_verified\":0,\"is_phone_verified\":0,\"phone_token\":921204,\"email_token\":935157,\"country_id\":156,\"phone_token_valid_till\":\"2022-10-27 12:59:15\",\"email_token_valid_till\":\"2022-10-27 12:59:15\",\"timezone\":\"Africa\\/Abidjan\",\"updated_at\":\"2022-10-27 12:49:15\",\"created_at\":\"2022-10-27 12:49:15\",\"id\":18}', 'http://api.rostaging.com/api/v1/auth/register', '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', NULL, '2022-10-27 12:49:15', '2022-10-27 12:49:15'),
(2, NULL, NULL, 'created', 'App\\Models\\User', 19, '[]', '{\"name\":\"Testap\",\"phone_number\":\"121121212\",\"dial_code\":\"234\",\"password\":\"$2y$10$UvFbN6kPME0UP2mHLll0juAkE5kbNwnBTrKISt9GoALaQ1wzk5IFS\",\"type\":1,\"status\":1,\"role_id\":1,\"email\":\"Testapp@gmail.com\",\"is_email_verified\":0,\"is_phone_verified\":0,\"phone_token\":753901,\"email_token\":218066,\"country_id\":156,\"phone_token_valid_till\":\"2022-10-27 12:59:15\",\"email_token_valid_till\":\"2022-10-27 12:59:15\",\"timezone\":\"Africa\\/Abidjan\",\"updated_at\":\"2022-10-27 12:49:15\",\"created_at\":\"2022-10-27 12:49:15\",\"id\":19}', 'http://api.rostaging.com/api/v1/auth/register', '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', NULL, '2022-10-27 12:49:15', '2022-10-27 12:49:15'),
(3, NULL, NULL, 'updated', 'App\\Models\\User', 18, '{\"auth_token\":null}', '{\"auth_token\":\"eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjY4NzQ5NTUsImV4cCI6MTY2OTU1MzM1NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.d3bjz3_Z7Y52eOtZ49cZPwz_5cZLJg7ljfe9_wcfQdQ\"}', 'http://api.rostaging.com/api/v1/auth/register', '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', NULL, '2022-10-27 12:49:15', '2022-10-27 12:49:15'),
(4, NULL, NULL, 'updated', 'App\\Models\\User', 19, '{\"auth_token\":null}', '{\"auth_token\":\"eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjY4NzQ5NTUsImV4cCI6MTY2OTU1MzM1NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.d3bjz3_Z7Y52eOtZ49cZPwz_5cZLJg7ljfe9_wcfQdQ\"}', 'http://api.rostaging.com/api/v1/auth/register', '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', NULL, '2022-10-27 12:49:15', '2022-10-27 12:49:15');

-- --------------------------------------------------------

--
-- Table structure for table `authentication_log`
--

CREATE TABLE `authentication_log` (
  `id` bigint UNSIGNED NOT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `login_at` timestamp NULL DEFAULT NULL,
  `logout_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `authentication_log`
--

INSERT INTO `authentication_log` (`id`, `authenticatable_type`, `authenticatable_id`, `ip_address`, `user_agent`, `login_at`, `logout_at`) VALUES
(1, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:17:04', NULL),
(2, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:21:40', NULL),
(3, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:21:40', NULL),
(4, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:21:40', NULL),
(5, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:21:44', NULL),
(6, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:27:36', NULL),
(7, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:27:41', NULL),
(8, 'App\\Models\\User', NULL, '172.31.28.21', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:27:41', NULL),
(9, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:44:49', NULL),
(10, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:44:51', NULL),
(11, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:05', NULL),
(12, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:09', NULL),
(13, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:12', NULL),
(14, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:13', NULL),
(15, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:20', NULL),
(16, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:25', NULL),
(17, 'App\\Models\\User', NULL, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:47:26', NULL),
(18, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:42', NULL),
(19, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:42', NULL),
(20, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:43', NULL),
(21, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:44', NULL),
(22, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:45', NULL),
(23, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:45', NULL),
(24, 'App\\Models\\User', 18, '172.31.37.129', 'AfricanVillageMarket/1 CFNetwork/1327.0.4 Darwin/21.4.0', '2022-10-27 12:49:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `auto_reject_orders_cron`
--

CREATE TABLE `auto_reject_orders_cron` (
  `id` bigint UNSIGNED NOT NULL,
  `database_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_password` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_vendor_id` int DEFAULT NULL,
  `auto_reject_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `image` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validity_on` tinyint NOT NULL DEFAULT '1' COMMENT '1 - yes, 0 - no',
  `sorting` tinyint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 - active, 0 - pending, 2 - blocked',
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `redirect_category_id` bigint UNSIGNED DEFAULT NULL,
  `redirect_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_mobile` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `name`, `description`, `image`, `validity_on`, `sorting`, `status`, `start_date_time`, `end_date_time`, `redirect_category_id`, `redirect_vendor_id`, `link`, `link_url`, `created_at`, `updated_at`, `image_mobile`) VALUES
(2, 'Food', NULL, 'banner/LEuCpb6xIj0QRthaEytaQCAohxHGSUOQVAnSFp9v.jpg', 1, 2, 1, '2021-08-05 10:28:00', '2023-08-31 12:00:00', NULL, NULL, NULL, NULL, NULL, '2021-08-05 04:59:04', NULL),
(3, 'Pharmacy', NULL, 'default/default_image.png', 0, 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Food', NULL, 'banner/2C7DVOZw4cwazLKOVuBxoObu8292s7WA0kRnxrUL.jpg', 1, 1, 1, '2021-08-05 10:29:00', '2023-08-31 12:00:00', NULL, NULL, NULL, NULL, '2021-07-20 11:51:26', '2021-08-05 05:00:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `banner_service_areas`
--

CREATE TABLE `banner_service_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `banner_id` bigint UNSIGNED DEFAULT NULL,
  `service_area_id` bigint UNSIGNED DEFAULT NULL COMMENT 'id from service area for banners',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `id` bigint UNSIGNED NOT NULL,
  `bid_req_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint DEFAULT NULL,
  `bid_total` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 Pending 1 Accepted 2 Rejected',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bid_order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `bid_products`
--

CREATE TABLE `bid_products` (
  `id` bigint UNSIGNED NOT NULL,
  `bid_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `bid_requests`
--

CREATE TABLE `bid_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `prescription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 Pending 1 Accepted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bid_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_tokens`
--

CREATE TABLE `blocked_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `expired` tinyint NOT NULL DEFAULT '0' COMMENT '1 yes, 0 no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_banner` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` smallint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `title`, `image`, `image_banner`, `position`, `status`, `created_at`, `updated_at`) VALUES
(1, 'The Flying Squirrel', 'brand/d4liPdEV4WuMPBzGPeDHSz6mU1fyFdZXrm5DejG5.gif', NULL, 1, 1, NULL, '2021-07-13 11:06:29'),
(2, 'Lavazza', 'brand/HGun2sf0sIHOFnLBWYKwWf0iM6bH0OYXSAC6xufn.png', NULL, 2, 1, NULL, '2021-07-13 11:06:57'),
(3, 'Blue Tokai', 'brand/RFmlveOu0ENheXGDJZbJE6tszpzQDyoAUBl6sg7e.png', NULL, 3, 1, NULL, '2021-07-13 11:20:11'),
(4, 'Green Mountain', 'brand/5rkwI80pCQnXSaf2DrpcX58sso7fXNi7efKbmNmZ.jpg', NULL, 4, 1, NULL, '2021-07-13 11:20:32'),
(5, 'Folgers', 'brand/5sG7KJmPwQdnllwMa2A8xBuPXiklqMNKwj8mEK2g.jpg', NULL, 5, 1, '2021-07-13 11:08:32', '2021-07-13 11:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `brand_categories`
--

CREATE TABLE `brand_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `brand_categories`
--

INSERT INTO `brand_categories` (`id`, `brand_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 1, 3, NULL, '2021-07-13 11:06:29'),
(2, 2, 3, NULL, '2021-07-13 11:06:57'),
(3, 3, 3, NULL, '2021-07-13 11:20:11'),
(4, 4, 3, NULL, '2021-07-13 11:20:32'),
(5, 5, 2, NULL, '2021-07-13 11:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `brand_translations`
--

CREATE TABLE `brand_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `brand_translations`
--

INSERT INTO `brand_translations` (`id`, `title`, `brand_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'The Flying Squirrel', 1, 1, NULL, '2021-07-13 11:06:29'),
(2, 'Lavazza', 2, 1, NULL, '2021-07-13 11:06:57'),
(3, 'Blue Tokai', 3, 1, NULL, '2021-07-13 11:20:11'),
(4, 'Green Mountain', 4, 1, NULL, '2021-07-13 11:20:32'),
(5, 'Folgers', 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `business_types`
--

CREATE TABLE `business_types` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cab_booking_layouts`
--

CREATE TABLE `cab_booking_layouts` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_by` tinyint DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT '0-No, 1-Yes',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 = web, 2 = app',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `for_no_product_found_html` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `cab_booking_layouts`
--

INSERT INTO `cab_booking_layouts` (`id`, `title`, `slug`, `order_by`, `image`, `is_active`, `type`, `created_at`, `updated_at`, `for_no_product_found_html`) VALUES
(1, 'Featured Products', 'featured_products', 2, NULL, 1, 1, '2023-04-27 12:15:44', '2023-04-27 12:15:44', 0),
(2, 'Vendors', 'vendors', 1, NULL, 1, 1, '2023-04-27 12:15:44', '2023-04-27 12:15:44', 0),
(3, 'New Products', 'new_products', 3, NULL, 1, 1, '2023-04-27 12:15:44', '2023-04-27 12:15:44', 0),
(4, 'On Sale', 'on_sale', 4, NULL, 1, 1, '2023-04-27 12:15:44', '2023-04-27 12:15:44', 0),
(5, 'Brands', 'brands', 6, NULL, 1, 1, '2023-04-27 12:15:44', '2023-04-27 12:15:44', 0),
(6, 'Best Sellers', 'best_sellers', 5, NULL, 1, 1, '2023-04-27 12:15:44', '2023-04-27 12:15:44', 0),
(7, 'Long Term Service', 'long_term_service', 7, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(8, 'Recently Viewed', 'recently_viewed', 7, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(9, 'Spotlight Deals', 'spotlight_deals', 8, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(10, 'Top Rated', 'top_rated', 9, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(11, 'NavCategories', 'nav_categories', 10, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(12, 'Single Category Products', 'single_category_products', 11, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(13, 'Selected Products', 'selected_products', 11, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(14, 'Most Popular Products', 'most_popular_products', 12, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0),
(15, 'Ordered Products', 'ordered_products', 13, NULL, 1, 1, '2023-04-27 12:15:44', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cab_booking_layout_banners`
--

CREATE TABLE `cab_booking_layout_banners` (
  `id` bigint UNSIGNED NOT NULL,
  `cab_booking_layout_id` bigint UNSIGNED DEFAULT NULL,
  `banner_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '2' COMMENT '1-Web, 2-App'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `cab_booking_layout_categories`
--

CREATE TABLE `cab_booking_layout_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `cab_booking_layout_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `cab_booking_layout_transaltions`
--

CREATE TABLE `cab_booking_layout_transaltions` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cab_booking_layout_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `body_html` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` int NOT NULL DEFAULT '1' COMMENT '1 SMS, 2 Email, 3 Push Notification',
  `sms_text` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_body` longtext COLLATE utf8mb4_unicode_ci,
  `push_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `push_image` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `push_message_body` longtext COLLATE utf8mb4_unicode_ci,
  `push_url_option` int DEFAULT NULL COMMENT '1 URL, 2 Category, 3 Vendor',
  `push_url_option_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `send_to` int NOT NULL DEFAULT '1' COMMENT '1 All, 2 Vendors',
  `schedule_datetime` datetime DEFAULT NULL,
  `request_user_count` bigint NOT NULL DEFAULT '1',
  `request_time_difference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_request_count` bigint DEFAULT NULL,
  `status` int NOT NULL COMMENT '1 Active, 2 Pause, 3 Finish',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaign_rosters`
--

CREATE TABLE `campaign_rosters` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` bigint DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `notification_time` datetime DEFAULT NULL,
  `notofication_type` int DEFAULT NULL COMMENT '1 sms, 2 email, 3 push notification',
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caregory_kyc_docs`
--

CREATE TABLE `caregory_kyc_docs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_kyc_document_id` bigint UNSIGNED NOT NULL,
  `file_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `cart_id` bigint DEFAULT NULL,
  `ordre_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `status` enum('0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0-Active, 1-Blocked, 2-Deleted',
  `is_gift` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0-Yes, 1-No',
  `item_count` int DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `schedule_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `specific_instructions` text COLLATE utf8mb4_unicode_ci,
  `comment_for_pickup_driver` mediumtext COLLATE utf8mb4_unicode_ci,
  `comment_for_dropoff_driver` mediumtext COLLATE utf8mb4_unicode_ci,
  `comment_for_vendor` mediumtext COLLATE utf8mb4_unicode_ci,
  `schedule_pickup` datetime DEFAULT NULL,
  `schedule_dropoff` datetime DEFAULT NULL,
  `scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_delivery_type` enum('D','L','S','SR','DU','M','SH','SHF','RO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `address_id` int DEFAULT NULL,
  `dropoff_scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'dropoff slot for laundry',
  `total_other_taxes` text COLLATE utf8mb4_unicode_ci,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `gift_card_id` bigint UNSIGNED DEFAULT NULL,
  `payable_amount` decimal(64,0) NOT NULL DEFAULT '0',
  `user_gift_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_bidding_discount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `unique_identifier`, `user_id`, `created_by`, `status`, `is_gift`, `item_count`, `currency_id`, `schedule_type`, `scheduled_date_time`, `created_at`, `updated_at`, `specific_instructions`, `comment_for_pickup_driver`, `comment_for_dropoff_driver`, `comment_for_vendor`, `schedule_pickup`, `schedule_dropoff`, `scheduled_slot`, `shipping_delivery_type`, `address_id`, `dropoff_scheduled_slot`, `total_other_taxes`, `order_id`, `gift_card_id`, `payable_amount`, `user_gift_code`, `vendor_bidding_discount`) VALUES
(1, '', 1, NULL, '0', '0', 0, 147, NULL, NULL, '2021-07-13 13:02:35', '2021-07-13 13:29:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(2, '', 2, 2, '1', '', 0, 147, NULL, NULL, '2021-07-20 13:06:30', '2021-07-20 13:06:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(3, '', 3, 3, '1', '', 0, 147, NULL, NULL, '2021-07-20 13:09:26', '2021-07-21 12:57:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(4, '', 7, 7, '1', '', 0, 147, NULL, NULL, '2021-08-05 05:34:15', '2021-08-05 06:07:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(5, '', 8, 8, '1', '', 0, 147, NULL, NULL, '2021-08-05 06:04:34', '2021-08-05 06:04:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(8, '', 10, 10, '1', '', 0, 147, NULL, NULL, '2021-08-05 06:31:04', '2021-08-05 06:33:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(9, '', 11, 11, '1', '', 0, 147, NULL, NULL, '2021-08-05 11:49:43', '2021-08-05 11:56:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(10, '', 12, NULL, '1', '', 0, 147, NULL, NULL, '2021-08-05 11:50:27', '2021-08-05 11:52:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(11, '', 13, 13, '0', '', 0, 147, NULL, NULL, '2021-08-12 11:42:51', '2021-08-12 11:43:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(12, '', 14, 14, '0', '', 0, 147, NULL, NULL, '2021-08-23 07:18:34', '2021-08-23 07:18:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(13, '', 15, NULL, '0', '', 0, 147, NULL, NULL, '2021-08-23 07:24:46', '2021-08-23 07:24:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL),
(14, '', 18, NULL, '0', '', 0, 147, NULL, NULL, '2022-10-27 12:21:44', '2022-10-27 12:49:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'D', NULL, NULL, 'tax_fixed_fee:0,tax_service_charges:0,tax_delivery_charges:0,tax_markup_fee:0,product_tax_fee:0', NULL, NULL, '0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_addons`
--

CREATE TABLE `cart_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `cart_product_id` bigint UNSIGNED NOT NULL,
  `addon_id` bigint UNSIGNED NOT NULL,
  `option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `cart_coupons`
--

CREATE TABLE `cart_coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `cart_coupons`
--

INSERT INTO `cart_coupons` (`id`, `cart_id`, `coupon_id`, `created_at`, `updated_at`, `vendor_id`) VALUES
(8, 8, 1, '2021-08-05 06:33:16', '2021-08-05 06:33:16', 3);

-- --------------------------------------------------------

--
-- Table structure for table `cart_products`
--

CREATE TABLE `cart_products` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_dinein_table_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL COMMENT '0-Active, 1-Blocked, 2-Deleted',
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `is_tax_applied` tinyint NOT NULL COMMENT '0-Yes, 1-No',
  `tax_rate_id` bigint UNSIGNED DEFAULT NULL,
  `user_product_order_form` text COLLATE utf8mb4_unicode_ci,
  `schedule_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `luxury_option_id` bigint NOT NULL DEFAULT '1',
  `schedule_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dispatch_agent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'driver id',
  `dispatch_agent_price` decimal(16,4) DEFAULT '0.0000',
  `specific_instruction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `additional_increments_hrs_min` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `total_booking_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT 'in min',
  `product_delivery_fee` decimal(16,8) DEFAULT '0.00000000',
  `service_start_date` datetime DEFAULT NULL,
  `service_day` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'only day number (this is only for product is_long_term_service=1)',
  `service_date` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'only date number (this is only for product is_long_term_service=1)',
  `service_period` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'day,week,month (this is only for product is_long_term_service=1)',
  `order_quantity` int DEFAULT NULL,
  `bid_number` int DEFAULT NULL,
  `bid_discount` int DEFAULT NULL,
  `slot_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `slot_price` decimal(12,2) DEFAULT NULL,
  `product_variant_by_role_id` bigint UNSIGNED DEFAULT NULL,
  `recurring_booking_type` tinyint DEFAULT NULL COMMENT '1=daily,2=weekly,3=monthly,4=custom',
  `recurring_week_day` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurring_week_type` tinyint DEFAULT NULL COMMENT '1=daily,2=once',
  `recurring_day_data` longtext COLLATE utf8mb4_unicode_ci,
  `recurring_booking_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_cart_checked` tinyint DEFAULT '1' COMMENT '0-No, 1-Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `cart_products`
--

INSERT INTO `cart_products` (`id`, `cart_id`, `product_id`, `vendor_id`, `vendor_dinein_table_id`, `quantity`, `created_by`, `status`, `variant_id`, `is_tax_applied`, `tax_rate_id`, `user_product_order_form`, `schedule_type`, `scheduled_date_time`, `created_at`, `updated_at`, `tax_category_id`, `currency_id`, `luxury_option_id`, `schedule_slot`, `dispatch_agent_id`, `dispatch_agent_price`, `specific_instruction`, `start_date_time`, `end_date_time`, `additional_increments_hrs_min`, `total_booking_time`, `product_delivery_fee`, `service_start_date`, `service_day`, `service_date`, `service_period`, `order_quantity`, `bid_number`, `bid_discount`, `slot_id`, `delivery_date`, `slot_price`, `product_variant_by_role_id`, `recurring_booking_type`, `recurring_week_day`, `recurring_week_type`, `recurring_day_data`, `recurring_booking_time`, `is_cart_checked`) VALUES
(9, 3, 21, 4, NULL, 1, NULL, 0, 25, 1, NULL, NULL, NULL, NULL, '2021-07-21 12:56:18', '2021-07-21 12:56:18', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, '0', '0', '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(10, 3, 15, 5, NULL, 1, NULL, 0, 19, 1, NULL, NULL, NULL, NULL, '2021-07-21 12:57:04', '2021-07-21 12:57:04', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, '0', '0', '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(15, 4, 30, 3, NULL, 4, NULL, 0, 34, 1, NULL, NULL, NULL, NULL, '2021-08-05 06:07:56', '2021-08-05 06:07:56', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, '0', '0', '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(21, 8, 27, 3, NULL, 1, NULL, 0, 31, 1, NULL, NULL, NULL, NULL, '2021-08-05 06:33:05', '2021-08-05 06:33:05', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, '0', '0', '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(23, 10, 15, 5, NULL, 4, NULL, 0, 19, 1, NULL, NULL, NULL, NULL, '2021-08-05 11:50:27', '2021-08-05 11:50:27', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, '0', '0', '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(28, 14, 9, 6, NULL, 1, NULL, 0, 13, 1, NULL, NULL, NULL, NULL, '2022-10-27 12:21:44', '2022-10-27 12:21:44', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(29, 14, 39, 2, NULL, 1, NULL, 0, 43, 1, NULL, NULL, NULL, NULL, '2022-10-27 12:47:12', '2022-10-27 12:47:12', NULL, 147, 1, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, '0.00000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart_product_prescriptions`
--

CREATE TABLE `cart_product_prescriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `prescription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `cart_vendor_delivery_fee`
--

CREATE TABLE `cart_vendor_delivery_fee` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_fee` decimal(16,8) DEFAULT '0.00000000',
  `delivery_duration` tinyint DEFAULT NULL,
  `delivery_distance` decimal(16,2) DEFAULT NULL,
  `shipping_delivery_type` enum('D','L','S','SR','DU','M','SH','SHF','RO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `courier_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `car_details`
--

CREATE TABLE `car_details` (
  `id` bigint UNSIGNED NOT NULL,
  `car_id` bigint UNSIGNED NOT NULL COMMENT 'user address id is car id',
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `car_images`
--

CREATE TABLE `car_images` (
  `id` bigint UNSIGNED NOT NULL,
  `car_id` bigint UNSIGNED NOT NULL COMMENT 'user address id is car id',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `icon` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_two` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` bigint UNSIGNED DEFAULT NULL,
  `image` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_visible` tinyint DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `position` smallint NOT NULL DEFAULT '1' COMMENT 'for same position, display asc order',
  `is_core` tinyint NOT NULL DEFAULT '1' COMMENT '0 - no, 1 - yes',
  `can_add_products` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `client_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'only products name, product with description',
  `warning_page_id` bigint DEFAULT NULL,
  `template_type_id` bigint DEFAULT NULL,
  `warning_page_design` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `show_wishlist` tinyint NOT NULL DEFAULT '1' COMMENT '1 for yes, 0 for no',
  `sub_cat_banners` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_category_id` bigint DEFAULT NULL COMMENT 'Refrence of inventory panel category table'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `icon`, `icon_two`, `slug`, `type_id`, `image`, `is_visible`, `status`, `position`, `is_core`, `can_add_products`, `parent_id`, `vendor_id`, `client_code`, `display_mode`, `warning_page_id`, `template_type_id`, `warning_page_design`, `created_at`, `updated_at`, `deleted_at`, `show_wishlist`, `sub_cat_banners`, `inventory_category_id`) VALUES
(1, NULL, NULL, 'Root', 3, NULL, 0, 1, 1, 1, 0, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL),
(2, NULL, NULL, 'Delivery', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:14', NULL, 1, NULL, NULL),
(3, NULL, NULL, 'Restaurant', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:21', NULL, 1, NULL, NULL),
(4, NULL, NULL, 'Supermarket', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:28', NULL, 1, NULL, NULL),
(5, NULL, NULL, 'Pharmacy', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:35', NULL, 1, NULL, NULL),
(6, NULL, NULL, 'Send something', 1, NULL, 1, 1, 1, 1, 1, 2, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL),
(7, NULL, NULL, 'Buy something', 1, NULL, 1, 1, 1, 1, 1, 2, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL),
(8, NULL, NULL, 'Vegetables', 1, NULL, 1, 1, 1, 1, 1, 4, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL),
(9, NULL, NULL, 'Fruits', 1, NULL, 1, 1, 1, 1, 1, 4, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL),
(10, NULL, NULL, 'Dairy and Eggs', 1, NULL, 1, 1, 1, 1, 1, 4, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL),
(11, NULL, NULL, 'E-Commerce', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:41', NULL, 1, NULL, NULL),
(12, NULL, NULL, 'Cloth', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:47', NULL, 1, NULL, NULL),
(13, NULL, NULL, 'Dispatcher', 1, NULL, 0, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:37:56', NULL, 1, NULL, NULL),
(14, 'category/icon/5ysqNFjMM7QfitJpFNLczWCtKJLJxgQ2AJdRWuzF.jpg', NULL, 'italian', 1, 'category/image/GQkeHIsq0ZhPdy3ytTfFDm4PCHQWSJOZ4oaLrH7G.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:38:56', '2021-07-20 11:38:56', NULL, 1, NULL, NULL),
(15, 'category/icon/iwUL2oWefzKVKw7va2Ct2TBPYfuWQD5NqCSCfl6G.jpg', NULL, 'chinese', 1, 'category/image/7CnFSc3S1DOqxLRcyaN8fEgou4Zz5O27NkD3gLRm.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:40:05', '2021-07-20 11:40:05', NULL, 1, NULL, NULL),
(16, 'category/icon/CiA4Sf4830chxigtstvH3vSAsfROG1Je6FAUTIKk.jpg', NULL, 'dessert', 1, 'category/image/nwR7WxkeNUUHC209SxWsiZTRqHQazo695iQ1bF3b.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:40:37', '2021-07-20 11:40:37', NULL, 1, NULL, NULL),
(17, 'category/icon/LRG7a8OPtFjzHG4JoFYRqFo67TjFCz3RC1hXAQUt.jpg', NULL, 'beverages', 1, 'category/image/6wKvvlZvFGlBw0Gz7rtCCLHOeF2dpsjUBHPRk9Fb.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:41:26', '2021-07-20 12:04:41', NULL, 1, NULL, NULL),
(18, 'category/icon/xKXIJnK26ShW9cg4N76XJVCW27fMYzvyQkGPuxe6.jpg', NULL, 'salad', 1, 'category/image/EGb8DAUMf5eq29HeiBeCBv06h5vBo2hoKu6pvAsB.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:45:19', '2021-07-20 11:46:40', NULL, 1, NULL, NULL),
(19, 'category/icon/m80jrJ7hO1S2IYzSlYvDCoV8Mf1JJap68joIduqq.jpg', NULL, 'snacks', 1, 'category/image/XoTgczpD9Hm8r39KkuVikHj08vLecUSUYVk2wUIo.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:46:59', '2021-07-20 11:47:41', NULL, 1, NULL, NULL),
(20, 'category/icon/QbbDtQIgIbTuwvY6ejV8pOGcWzerIOZ3aWK3Rh6L.jpg', NULL, 'continental', 1, 'category/image/cb0kXt9Edn5SWNQZoLHGs1goYHQIgixfCH71dKuT.jpg', 1, 1, 1, 1, 1, 1, NULL, '1dfe7c', NULL, NULL, NULL, NULL, '2021-07-20 11:48:57', '2021-07-20 11:48:57', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category_histories`
--

CREATE TABLE `category_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'add' COMMENT 'add, update, delete, block, active',
  `updater_role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SuperAdmin' COMMENT 'SuperAdmin, Admin, Seller',
  `update_id` bigint UNSIGNED DEFAULT NULL,
  `client_code` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `category_histories`
--

INSERT INTO `category_histories` (`id`, `category_id`, `action`, `updater_role`, `update_id`, `client_code`, `created_at`, `updated_at`) VALUES
(1, 2, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:14', '2021-07-20 11:37:14'),
(2, 3, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:21', '2021-07-20 11:37:21'),
(3, 4, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:28', '2021-07-20 11:37:28'),
(4, 5, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:35', '2021-07-20 11:37:35'),
(5, 11, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:41', '2021-07-20 11:37:41'),
(6, 12, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:47', '2021-07-20 11:37:47'),
(7, 13, 'Update', 'Admin', 1, 1, '2021-07-20 11:37:56', '2021-07-20 11:37:56'),
(8, 18, 'Update', 'Admin', 1, 1, '2021-07-20 11:45:59', '2021-07-20 11:45:59'),
(9, 18, 'Update', 'Admin', 1, 1, '2021-07-20 11:46:40', '2021-07-20 11:46:40'),
(10, 19, 'Update', 'Admin', 1, 1, '2021-07-20 11:47:41', '2021-07-20 11:47:41'),
(11, 17, 'Update', 'Admin', 1, 1, '2021-07-20 12:04:41', '2021-07-20 12:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `category_kyc_documents`
--

CREATE TABLE `category_kyc_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_kyc_document_mappings`
--

CREATE TABLE `category_kyc_document_mappings` (
  `id` bigint UNSIGNED NOT NULL,
  `category_kyc_document_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_kyc_document_translations`
--

CREATE TABLE `category_kyc_document_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `language_id` bigint UNSIGNED NOT NULL,
  `category_kyc_document_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_roles`
--

CREATE TABLE `category_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `category_tags`
--

CREATE TABLE `category_tags` (
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `tag` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `category_translations`
--

CREATE TABLE `category_translations` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trans-slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `category_translations`
--

INSERT INTO `category_translations` (`id`, `name`, `trans-slug`, `meta_title`, `meta_description`, `meta_keywords`, `category_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'root', '', 'root', '', '', 1, 1, NULL, NULL),
(2, 'Delivery', '', 'Delivery', NULL, NULL, 2, 1, NULL, '2021-07-20 11:37:14'),
(3, 'Restaurant', '', 'Restaurant', NULL, NULL, 3, 1, NULL, '2021-07-20 11:37:21'),
(4, 'Supermarket', '', 'Supermarket', NULL, NULL, 4, 1, NULL, '2021-07-20 11:37:28'),
(5, 'Pharmacy', '', 'Pharmacy', NULL, NULL, 5, 1, NULL, '2021-07-20 11:37:35'),
(6, 'Send something', '', 'Send something', '', '', 6, 1, NULL, NULL),
(7, 'Buy something', '', 'Buy something', '', '', 7, 1, NULL, NULL),
(8, 'Vegetables', '', 'Vegetables', '', '', 8, 1, NULL, NULL),
(9, 'Fruits', '', 'Fruits', '', '', 9, 1, NULL, NULL),
(10, 'Dairy and Eggs', '', 'Dairy and Eggs', '', '', 10, 1, NULL, NULL),
(11, 'E-Commerce', '', 'E-Commerce', NULL, NULL, 11, 1, NULL, '2021-07-20 11:37:41'),
(12, 'Cloth', '', 'Cloth', NULL, NULL, 12, 1, NULL, '2021-07-20 11:37:47'),
(13, 'Dispatcher', '', 'Dispatcher', NULL, NULL, 13, 1, NULL, '2021-07-20 11:37:56'),
(14, 'Italian', NULL, 'italian', NULL, NULL, 14, 1, '2021-07-20 11:38:56', '2021-07-20 11:38:56'),
(15, 'Chinese', NULL, 'chinese', NULL, NULL, 15, 1, '2021-07-20 11:40:05', '2021-07-20 11:40:05'),
(16, 'Dessert', NULL, 'dessert', NULL, NULL, 16, 1, '2021-07-20 11:40:37', '2021-07-20 11:40:37'),
(17, 'Beverages', NULL, 'beverages', NULL, NULL, 17, 1, '2021-07-20 11:41:26', '2021-07-20 12:04:41'),
(18, 'Salad', NULL, 'salad', NULL, NULL, 18, 1, '2021-07-20 11:45:19', '2021-07-20 11:45:19'),
(19, 'Snacks', NULL, 'snacks', NULL, NULL, 19, 1, '2021-07-20 11:46:59', '2021-07-20 11:46:59'),
(20, 'Continental', NULL, 'continental', NULL, NULL, 20, 1, '2021-07-20 11:48:57', '2021-07-20 11:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `celebrities`
--

CREATE TABLE `celebrities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 - pending, 1 - active, 2 - inactive, 3 - deleted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country_id` bigint UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `celebrity_brands`
--

CREATE TABLE `celebrity_brands` (
  `celebrity_id` bigint UNSIGNED DEFAULT NULL,
  `brand_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `encpass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` bigint UNSIGNED DEFAULT NULL,
  `timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_domain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_domain` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `is_blocked` tinyint NOT NULL DEFAULT '0',
  `database_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_password` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_address` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '1 for active, 0 for pending, 2 for blocked, 3 for inactive',
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `database_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_port` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_superadmin` tinyint NOT NULL DEFAULT '1' COMMENT '1 for yes, 0 for no',
  `whatsapp_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `socket_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_chat` int NOT NULL DEFAULT '2',
  `driver_chat` int NOT NULL DEFAULT '2',
  `customer_chat` int NOT NULL DEFAULT '2',
  `dark_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `email`, `phone_number`, `password`, `encpass`, `country_id`, `timezone`, `custom_domain`, `sub_domain`, `is_deleted`, `is_blocked`, `database_path`, `database_name`, `database_username`, `database_password`, `logo`, `company_name`, `company_address`, `language_id`, `status`, `code`, `contact_email`, `contact_address`, `contact_phone_number`, `created_at`, `updated_at`, `database_host`, `database_port`, `is_superadmin`, `whatsapp_url`, `socket_url`, `admin_chat`, `driver_chat`, `customer_chat`, `dark_logo`) VALUES
(1, 'African village Market', 'admin@africanvillagemarket.com', '5896523658', '$2y$10$T7zmZDpkOqoQGXobsptW..oyNw328bjtw9dgUf9dwSsDduCTrigT2', 'admin@800', 156, 'Africa/Abidjan', 'africanvillagemarket', NULL, 0, 0, '', 'africanvillagemarket', 'cbladmin', 'aQ2hvKYLH4LKWmrA', 'Clientlogo/60f6b96ad3352.png', 'African village Market', '2493 Loop St,Milnerton', NULL, 1, '1dfe7c', NULL, NULL, NULL, NULL, '2021-09-16 15:13:10', NULL, NULL, 1, NULL, NULL, 2, 2, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_countries`
--

CREATE TABLE `client_countries` (
  `id` bigint UNSIGNED NOT NULL,
  `client_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` bigint UNSIGNED DEFAULT NULL,
  `is_primary` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_active` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `client_currencies`
--

CREATE TABLE `client_currencies` (
  `client_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `is_primary` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `doller_compare` decimal(14,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `client_currencies`
--

INSERT INTO `client_currencies` (`client_code`, `currency_id`, `is_primary`, `doller_compare`, `created_at`, `updated_at`, `id`) VALUES
('1dfe7c', 147, 1, '0.99999999', NULL, '2021-07-13 13:01:44', 1);

-- --------------------------------------------------------

--
-- Table structure for table `client_languages`
--

CREATE TABLE `client_languages` (
  `client_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `is_primary` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_active` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `client_languages`
--

INSERT INTO `client_languages` (`client_code`, `language_id`, `is_primary`, `is_active`, `created_at`, `updated_at`) VALUES
('1dfe7c', 1, 1, 1, NULL, '2021-07-13 13:01:44');

-- --------------------------------------------------------

--
-- Table structure for table `client_preferences`
--

CREATE TABLE `client_preferences` (
  `id` bigint UNSIGNED NOT NULL,
  `business_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'cab_booking',
  `rating_check` tinyint DEFAULT '0',
  `enquire_mode` tinyint DEFAULT '0',
  `hide_nav_bar` tinyint NOT NULL DEFAULT '0',
  `show_dark_mode` tinyint NOT NULL DEFAULT '2',
  `show_payment_icons` tinyint NOT NULL DEFAULT '0',
  `loyalty_check` tinyint NOT NULL DEFAULT '0' COMMENT '0-Active, 1-Inactive',
  `show_icons` tinyint NOT NULL DEFAULT '0',
  `show_wishlist` tinyint NOT NULL DEFAULT '0',
  `show_contact_us` tinyint NOT NULL DEFAULT '0',
  `age_restriction` tinyint NOT NULL DEFAULT '0',
  `age_restriction_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_mode` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `cart_enable` tinyint DEFAULT '0',
  `client_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_admin` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light' COMMENT 'Light, Dark',
  `distance_unit` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'metric' COMMENT 'metric, imperial',
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `date_format` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y-m-d',
  `time_format` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'H:i',
  `fb_login` tinyint NOT NULL DEFAULT '0' COMMENT '1 - enable, 0 - disable',
  `fb_client_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fb_client_secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fb_client_url` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_login` tinyint NOT NULL DEFAULT '0' COMMENT '1 - enable, 0 - disable',
  `twitter_client_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_client_secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_client_url` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_login` tinyint NOT NULL DEFAULT '0' COMMENT '1 - enable, 0 - disable',
  `google_client_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_client_secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_client_url` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_login` tinyint NOT NULL DEFAULT '0' COMMENT '1 - enable, 0 - disable',
  `apple_client_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_client_secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_client_url` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Default_location_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Default_latitude` decimal(15,12) NOT NULL DEFAULT '0.000000000000',
  `Default_longitude` decimal(16,12) NOT NULL DEFAULT '0.000000000000',
  `map_provider` bigint UNSIGNED DEFAULT NULL,
  `map_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `map_secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_provider` bigint UNSIGNED DEFAULT NULL,
  `sms_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_from` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_email` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `verify_phone` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `web_template_id` bigint UNSIGNED DEFAULT NULL,
  `app_template_id` bigint UNSIGNED DEFAULT NULL,
  `personal_access_token_v1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personal_access_token_v2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'smtp',
  `mail_driver` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_host` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_port` smallint DEFAULT NULL,
  `mail_username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_encryption` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_from` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_hyperlocal` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `need_delivery_service` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `need_dispacher_ride` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `delivery_service_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_top_header_color` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '#4c4c4c',
  `dispatcher_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `celebrity_check` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `delivery_service_key_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_service_key_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reffered_by_amount` decimal(16,8) DEFAULT '0.00000000',
  `reffered_to_amount` decimal(16,8) DEFAULT '0.00000000',
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `web_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pharmacy_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `dinein_check` tinyint DEFAULT '1' COMMENT '0-No, 1-Yes',
  `takeaway_check` tinyint DEFAULT '1' COMMENT '0-No, 1-Yes',
  `delivery_check` tinyint DEFAULT '1' COMMENT '0-No, 1-Yes',
  `rental_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `pick_drop_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `on_demand_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `laundry_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `appointment_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `pickup_delivery_service_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_delivery_service_key_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_delivery_service_key_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `need_dispacher_home_other_service` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dispacher_home_other_service_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dispacher_home_other_service_key_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dispacher_home_other_service_key_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_mile_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tip_before_order` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `tip_after_order` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `off_scheduling_at_cart` tinyint(1) DEFAULT '0' COMMENT '0-No, 1-Yes',
  `isolate_single_vendor_order` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `fcm_server_key` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_auth_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_project_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_storage_bucket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_messaging_sender_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_app_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_measurement_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_unit_for_time` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_to_time_multiplier` int UNSIGNED DEFAULT '0',
  `android_app_link` mediumtext COLLATE utf8mb4_unicode_ci,
  `ios_link` mediumtext COLLATE utf8mb4_unicode_ci,
  `single_vendor` tinyint NOT NULL DEFAULT '0',
  `stripe_connect` tinyint NOT NULL DEFAULT '0',
  `need_laundry_service` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `laundry_service_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laundry_service_key_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laundry_service_key_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laundry_pickup_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laundry_dropoff_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mainly used for orders and place in cc',
  `delay_order` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `gifting` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `product_order_form` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `sms_credentials` json DEFAULT NULL COMMENT 'sms credentials in json format',
  `pickup_delivery_service_area` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `customer_support` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_support_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_support_application_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_mode` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `minimum_order_batch` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_edit_order_admin` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_edit_order_vendor` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_edit_order_driver` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `static_delivey_fee` tinyint NOT NULL DEFAULT '0' COMMENT '0-No, 1-Yes',
  `header_quick_link` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `get_estimations` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `view_get_estimation_in_category` tinyint DEFAULT '0' COMMENT '0-off, 1-on',
  `tools_mode` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `max_safety_mod` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `hide_order_address` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `digit_after_decimal` tinyint NOT NULL DEFAULT '2' COMMENT '1',
  `is_cancel_order_user` tinyint DEFAULT '1' COMMENT '0-No, 1-Yes',
  `age_restriction_on_product_mode` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `show_qr_on_footer` tinyint NOT NULL DEFAULT '0' COMMENT '0-No, 1-Yes',
  `auto_implement_5_percent_tip` tinyint NOT NULL DEFAULT '0' COMMENT '0-No, 1-Yes',
  `vendor_return_request` tinyint NOT NULL DEFAULT '1' COMMENT '0-No, 1-Yes',
  `need_xero` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `category_kyc_documents` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `db_audit_logs` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `deliveryicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dineinicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `takewayicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rentalicon` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pick_dropicon` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `on_demandicon` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laundryicon` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `p2picon` text COLLATE utf8mb4_unicode_ci,
  `appointmenticon` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `third_party_accounting` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `hide_order_prepare_time` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `map_key_for_app` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_tab_taxi` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `need_inventory_service` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `inventory_service_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_service_key_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_service_key_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_inventory_service` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `scheduling_with_slots` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `same_day_delivery_for_schedule` tinyint DEFAULT '0' COMMENT '0-off, 1-on',
  `same_day_orders_for_rescheduing` tinyint DEFAULT '0' COMMENT '0-off, 1-on',
  `book_for_friend` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `order_cancellation_time` int DEFAULT NULL,
  `cancellation_percentage` int DEFAULT NULL,
  `estimation_matching_logic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_fcm_server_key` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sos` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `sos_police_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'police number for sos',
  `sos_ambulance_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ambulance number for sos',
  `concise_signup` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_static_dropoff` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_scan_qrcode_bag` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `slots_with_service_area` tinyint DEFAULT '0',
  `is_vendor_tags` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_service_area_for_banners` tinyint DEFAULT '0' COMMENT '0-Inactive, 1-Active',
  `stop_order_acceptance_for_users` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `need_appointment_service` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_service_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_service_key_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_service_key_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signup_image` text COLLATE utf8mb4_unicode_ci,
  `map_on_search_screen` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_tax_price_inclusive` tinyint NOT NULL DEFAULT '0',
  `p2p_check` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `client_preferences`
--

INSERT INTO `client_preferences` (`id`, `business_type`, `rating_check`, `enquire_mode`, `hide_nav_bar`, `show_dark_mode`, `show_payment_icons`, `loyalty_check`, `show_icons`, `show_wishlist`, `show_contact_us`, `age_restriction`, `age_restriction_title`, `subscription_mode`, `cart_enable`, `client_code`, `theme_admin`, `distance_unit`, `currency_id`, `language_id`, `date_format`, `time_format`, `fb_login`, `fb_client_id`, `fb_client_secret`, `fb_client_url`, `twitter_login`, `twitter_client_id`, `twitter_client_secret`, `twitter_client_url`, `google_login`, `google_client_id`, `google_client_secret`, `google_client_url`, `apple_login`, `apple_client_id`, `apple_client_secret`, `apple_client_url`, `Default_location_name`, `Default_latitude`, `Default_longitude`, `map_provider`, `map_key`, `map_secret`, `sms_provider`, `sms_key`, `sms_secret`, `sms_from`, `verify_email`, `verify_phone`, `web_template_id`, `app_template_id`, `personal_access_token_v1`, `personal_access_token_v2`, `mail_type`, `mail_driver`, `mail_host`, `mail_port`, `mail_username`, `mail_password`, `mail_encryption`, `mail_from`, `is_hyperlocal`, `need_delivery_service`, `need_dispacher_ride`, `delivery_service_key`, `primary_color`, `secondary_color`, `site_top_header_color`, `dispatcher_key`, `created_at`, `updated_at`, `celebrity_check`, `delivery_service_key_url`, `delivery_service_key_code`, `reffered_by_amount`, `reffered_to_amount`, `favicon`, `web_color`, `pharmacy_check`, `dinein_check`, `takeaway_check`, `delivery_check`, `rental_check`, `pick_drop_check`, `on_demand_check`, `laundry_check`, `appointment_check`, `pickup_delivery_service_key`, `pickup_delivery_service_key_url`, `pickup_delivery_service_key_code`, `need_dispacher_home_other_service`, `dispacher_home_other_service_key`, `dispacher_home_other_service_key_url`, `dispacher_home_other_service_key_code`, `last_mile_team`, `tip_before_order`, `tip_after_order`, `off_scheduling_at_cart`, `isolate_single_vendor_order`, `fcm_server_key`, `fcm_api_key`, `fcm_auth_domain`, `fcm_project_id`, `fcm_storage_bucket`, `fcm_messaging_sender_id`, `fcm_app_id`, `fcm_measurement_id`, `distance_unit_for_time`, `distance_to_time_multiplier`, `android_app_link`, `ios_link`, `single_vendor`, `stripe_connect`, `need_laundry_service`, `laundry_service_key`, `laundry_service_key_url`, `laundry_service_key_code`, `laundry_pickup_team`, `laundry_dropoff_team`, `admin_email`, `delay_order`, `gifting`, `product_order_form`, `sms_credentials`, `pickup_delivery_service_area`, `customer_support`, `customer_support_key`, `customer_support_application_id`, `shipping_mode`, `minimum_order_batch`, `is_edit_order_admin`, `is_edit_order_vendor`, `is_edit_order_driver`, `static_delivey_fee`, `header_quick_link`, `get_estimations`, `view_get_estimation_in_category`, `tools_mode`, `max_safety_mod`, `hide_order_address`, `digit_after_decimal`, `is_cancel_order_user`, `age_restriction_on_product_mode`, `show_qr_on_footer`, `auto_implement_5_percent_tip`, `vendor_return_request`, `need_xero`, `category_kyc_documents`, `db_audit_logs`, `deliveryicon`, `dineinicon`, `takewayicon`, `rentalicon`, `pick_dropicon`, `on_demandicon`, `laundryicon`, `p2picon`, `appointmenticon`, `third_party_accounting`, `hide_order_prepare_time`, `map_key_for_app`, `subscription_tab_taxi`, `need_inventory_service`, `inventory_service_key`, `inventory_service_key_url`, `inventory_service_key_code`, `enable_inventory_service`, `scheduling_with_slots`, `same_day_delivery_for_schedule`, `same_day_orders_for_rescheduing`, `book_for_friend`, `order_cancellation_time`, `cancellation_percentage`, `estimation_matching_logic`, `vendor_fcm_server_key`, `sos`, `sos_police_contact`, `sos_ambulance_contact`, `concise_signup`, `is_static_dropoff`, `is_scan_qrcode_bag`, `slots_with_service_area`, `is_vendor_tags`, `is_service_area_for_banners`, `stop_order_acceptance_for_users`, `need_appointment_service`, `appointment_service_key`, `appointment_service_key_url`, `appointment_service_key_code`, `signup_image`, `map_on_search_screen`, `is_tax_price_inclusive`, `p2p_check`) VALUES
(1, NULL, 0, 0, 0, 2, 1, 0, 0, 1, 1, 0, NULL, 0, 1, '1dfe7c', 'light', 'metric', NULL, NULL, 'YYYY-MM-DD', '24', 0, 'admin@africanvillagemarket.com', 'admin@800', NULL, 0, NULL, NULL, NULL, 1, 'admin@africanvillagemarket.com', 'admin@800', NULL, 0, NULL, NULL, NULL, 'South Africa', '-30.559482000000', '22.937506000000', 1, 'AIzaSyBppfct1EwlyUSAT9QKbiuo4e6HiMvV4Fs', NULL, 1, 'AC2d20ec147884c2bce6e926bdbe5fd963', '5f70285f1e0f4dfca4fee10806a01697', '+17206132646', 0, 0, 1, 2, NULL, NULL, 'smtp', 'smtp', 'smtp.mailgun.org', 587, 'noreply@royoorders2.com', 'bbc693b9921c114cc743a597fb53b7d1-c3d1d1eb-e107f155', 'tls', 'sales@royoorders.com', 0, 1, 0, 'UU6rZsiTBgxQsKQeyMbISpVXTLJ9pJ', '#32B5FC', '#41A2E6', '#A2A80E', NULL, NULL, '2022-02-26 06:41:24', 0, 'https://africanvillagemarket.royodispatch.com', '6c786d', NULL, NULL, 'favicon/9yYgj4G0ZT8k5OHJUk8dRHTsQcypaN9fhSz4ZCLu.png', '#535607', 0, 0, 0, 1, 0, 0, 0, 0, 0, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 1, 0, 0, 0, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `client_preference_additional`
--

CREATE TABLE `client_preference_additional` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `client_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_private` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_boolean` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_slots`
--

CREATE TABLE `client_slots` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cms`
--

CREATE TABLE `cms` (
  `id` bigint UNSIGNED NOT NULL,
  `sorting` smallint NOT NULL DEFAULT '1',
  `title` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `html_content` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` longtext COLLATE utf8mb4_unicode_ci,
  `meta_keywords` longtext COLLATE utf8mb4_unicode_ci,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 - active, 0 - pending, 2 - blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `copy_tools`
--

CREATE TABLE `copy_tools` (
  `id` bigint UNSIGNED NOT NULL,
  `copy_from` bigint UNSIGNED DEFAULT NULL,
  `copy_to` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(56) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nicename` varchar(56) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iso3` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numcode` int DEFAULT NULL,
  `phonecode` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `code`, `name`, `nicename`, `iso3`, `numcode`, `phonecode`, `created_at`, `updated_at`) VALUES
(1, 'AF', 'AFGHANISTAN', 'Afghanistan', 'AFG', 4, 93, NULL, NULL),
(2, 'AL', 'ALBANIA', 'Albania', 'ALB', 8, 355, NULL, NULL),
(3, 'DZ', 'ALGERIA', 'Algeria', 'DZA', 12, 213, NULL, NULL),
(4, 'AS', 'AMERICAN SAMOA', 'American Samoa', 'ASM', 16, 1684, NULL, NULL),
(5, 'AD', 'ANDORRA', 'Andorra', 'AND', 20, 376, NULL, NULL),
(6, 'AO', 'ANGOLA', 'Angola', 'AGO', 24, 244, NULL, NULL),
(7, 'AI', 'ANGUILLA', 'Anguilla', 'AIA', 660, 1264, NULL, NULL),
(8, 'AQ', 'ANTARCTICA', 'Antarctica', NULL, NULL, 0, NULL, NULL),
(9, 'AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', 'ATG', 28, 1268, NULL, NULL),
(10, 'AR', 'ARGENTINA', 'Argentina', 'ARG', 32, 54, NULL, NULL),
(11, 'AM', 'ARMENIA', 'Armenia', 'ARM', 51, 374, NULL, NULL),
(12, 'AW', 'ARUBA', 'Aruba', 'ABW', 533, 297, NULL, NULL),
(13, 'AU', 'AUSTRALIA', 'Australia', 'AUS', 36, 61, NULL, NULL),
(14, 'AT', 'AUSTRIA', 'Austria', 'AUT', 40, 43, NULL, NULL),
(15, 'AZ', 'AZERBAIJAN', 'Azerbaijan', 'AZE', 31, 994, NULL, NULL),
(16, 'BS', 'BAHAMAS', 'Bahamas', 'BHS', 44, 1242, NULL, NULL),
(17, 'BH', 'BAHRAIN', 'Bahrain', 'BHR', 48, 973, NULL, NULL),
(18, 'BD', 'BANGLADESH', 'Bangladesh', 'BGD', 50, 880, NULL, NULL),
(19, 'BB', 'BARBADOS', 'Barbados', 'BRB', 52, 1246, NULL, NULL),
(20, 'BY', 'BELARUS', 'Belarus', 'BLR', 112, 375, NULL, NULL),
(21, 'BE', 'BELGIUM', 'Belgium', 'BEL', 56, 32, NULL, NULL),
(22, 'BZ', 'BELIZE', 'Belize', 'BLZ', 84, 501, NULL, NULL),
(23, 'BJ', 'BENIN', 'Benin', 'BEN', 204, 229, NULL, NULL),
(24, 'BM', 'BERMUDA', 'Bermuda', 'BMU', 60, 1441, NULL, NULL),
(25, 'BT', 'BHUTAN', 'Bhutan', 'BTN', 64, 975, NULL, NULL),
(26, 'BO', 'BOLIVIA', 'Bolivia', 'BOL', 68, 591, NULL, NULL),
(27, 'BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', 'BIH', 70, 387, NULL, NULL),
(28, 'BW', 'BOTSWANA', 'Botswana', 'BWA', 72, 267, NULL, NULL),
(29, 'BV', 'BOUVET ISLAND', 'Bouvet Island', NULL, NULL, 0, NULL, NULL),
(30, 'BR', 'BRAZIL', 'Brazil', 'BRA', 76, 55, NULL, NULL),
(31, 'IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', NULL, NULL, 246, NULL, NULL),
(32, 'BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', 'BRN', 96, 673, NULL, NULL),
(33, 'BG', 'BULGARIA', 'Bulgaria', 'BGR', 100, 359, NULL, NULL),
(34, 'BF', 'BURKINA FASO', 'Burkina Faso', 'BFA', 854, 226, NULL, NULL),
(35, 'BI', 'BURUNDI', 'Burundi', 'BDI', 108, 257, NULL, NULL),
(36, 'KH', 'CAMBODIA', 'Cambodia', 'KHM', 116, 855, NULL, NULL),
(37, 'CM', 'CAMEROON', 'Cameroon', 'CMR', 120, 237, NULL, NULL),
(38, 'CA', 'CANADA', 'Canada', 'CAN', 124, 1, NULL, NULL),
(39, 'CV', 'CAPE VERDE', 'Cape Verde', 'CPV', 132, 238, NULL, NULL),
(40, 'KY', 'CAYMAN ISLANDS', 'Cayman Islands', 'CYM', 136, 1345, NULL, NULL),
(41, 'CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', 'CAF', 140, 236, NULL, NULL),
(42, 'TD', 'CHAD', 'Chad', 'TCD', 148, 235, NULL, NULL),
(43, 'CL', 'CHILE', 'Chile', 'CHL', 152, 56, NULL, NULL),
(44, 'CN', 'CHINA', 'China', 'CHN', 156, 86, NULL, NULL),
(45, 'CX', 'CHRISTMAS ISLAND', 'Christmas Island', NULL, NULL, 61, NULL, NULL),
(46, 'CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', NULL, NULL, 672, NULL, NULL),
(47, 'CO', 'COLOMBIA', 'Colombia', 'COL', 170, 57, NULL, NULL),
(48, 'KM', 'COMOROS', 'Comoros', 'COM', 174, 269, NULL, NULL),
(49, 'CG', 'CONGO', 'Congo', 'COG', 178, 242, NULL, NULL),
(50, 'CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', 'COD', 180, 242, NULL, NULL),
(51, 'CK', 'COOK ISLANDS', 'Cook Islands', 'COK', 184, 682, NULL, NULL),
(52, 'CR', 'COSTA RICA', 'Costa Rica', 'CRI', 188, 506, NULL, NULL),
(53, 'CI', 'COTE D\'IVOIRE', 'Cote D\'Ivoire', 'CIV', 384, 225, NULL, NULL),
(54, 'HR', 'CROATIA', 'Croatia', 'HRV', 191, 385, NULL, NULL),
(55, 'CU', 'CUBA', 'Cuba', 'CUB', 192, 53, NULL, NULL),
(56, 'CY', 'CYPRUS', 'Cyprus', 'CYP', 196, 357, NULL, NULL),
(57, 'CZ', 'CZECH REPUBLIC', 'Czech Republic', 'CZE', 203, 420, NULL, NULL),
(58, 'DK', 'DENMARK', 'Denmark', 'DNK', 208, 45, NULL, NULL),
(59, 'DJ', 'DJIBOUTI', 'Djibouti', 'DJI', 262, 253, NULL, NULL),
(60, 'DM', 'DOMINICA', 'Dominica', 'DMA', 212, 1767, NULL, NULL),
(61, 'DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', 'DOM', 214, 1809, NULL, NULL),
(62, 'EC', 'ECUADOR', 'Ecuador', 'ECU', 218, 593, NULL, NULL),
(63, 'EG', 'EGYPT', 'Egypt', 'EGY', 818, 20, NULL, NULL),
(64, 'SV', 'EL SALVADOR', 'El Salvador', 'SLV', 222, 503, NULL, NULL),
(65, 'GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', 'GNQ', 226, 240, NULL, NULL),
(66, 'ER', 'ERITREA', 'Eritrea', 'ERI', 232, 291, NULL, NULL),
(67, 'EE', 'ESTONIA', 'Estonia', 'EST', 233, 372, NULL, NULL),
(68, 'ET', 'ETHIOPIA', 'Ethiopia', 'ETH', 231, 251, NULL, NULL),
(69, 'FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', 'FLK', 238, 500, NULL, NULL),
(70, 'FO', 'FAROE ISLANDS', 'Faroe Islands', 'FRO', 234, 298, NULL, NULL),
(71, 'FJ', 'FIJI', 'Fiji', 'FJI', 242, 679, NULL, NULL),
(72, 'FI', 'FINLAND', 'Finland', 'FIN', 246, 358, NULL, NULL),
(73, 'FR', 'FRANCE', 'France', 'FRA', 250, 33, NULL, NULL),
(74, 'GF', 'FRENCH GUIANA', 'French Guiana', 'GUF', 254, 594, NULL, NULL),
(75, 'PF', 'FRENCH POLYNESIA', 'French Polynesia', 'PYF', 258, 689, NULL, NULL),
(76, 'TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', NULL, NULL, 0, NULL, NULL),
(77, 'GA', 'GABON', 'Gabon', 'GAB', 266, 241, NULL, NULL),
(78, 'GM', 'GAMBIA', 'Gambia', 'GMB', 270, 220, NULL, NULL),
(79, 'GE', 'GEORGIA', 'Georgia', 'GEO', 268, 995, NULL, NULL),
(80, 'DE', 'GERMANY', 'Germany', 'DEU', 276, 49, NULL, NULL),
(81, 'GH', 'GHANA', 'Ghana', 'GHA', 288, 233, NULL, NULL),
(82, 'GI', 'GIBRALTAR', 'Gibraltar', 'GIB', 292, 350, NULL, NULL),
(83, 'GR', 'GREECE', 'Greece', 'GRC', 300, 30, NULL, NULL),
(84, 'GL', 'GREENLAND', 'Greenland', 'GRL', 304, 299, NULL, NULL),
(85, 'GD', 'GRENADA', 'Grenada', 'GRD', 308, 1473, NULL, NULL),
(86, 'GP', 'GUADELOUPE', 'Guadeloupe', 'GLP', 312, 590, NULL, NULL),
(87, 'GU', 'GUAM', 'Guam', 'GUM', 316, 1671, NULL, NULL),
(88, 'GT', 'GUATEMALA', 'Guatemala', 'GTM', 320, 502, NULL, NULL),
(89, 'GN', 'GUINEA', 'Guinea', 'GIN', 324, 224, NULL, NULL),
(90, 'GW', 'GUINEA-BISSAU', 'Guinea-Bissau', 'GNB', 624, 245, NULL, NULL),
(91, 'GY', 'GUYANA', 'Guyana', 'GUY', 328, 592, NULL, NULL),
(92, 'HT', 'HAITI', 'Haiti', 'HTI', 332, 509, NULL, NULL),
(93, 'HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', NULL, NULL, 0, NULL, NULL),
(94, 'VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', 'VAT', 336, 39, NULL, NULL),
(95, 'HN', 'HONDURAS', 'Honduras', 'HND', 340, 504, NULL, NULL),
(96, 'HK', 'HONG KONG', 'Hong Kong', 'HKG', 344, 852, NULL, NULL),
(97, 'HU', 'HUNGARY', 'Hungary', 'HUN', 348, 36, NULL, NULL),
(98, 'IS', 'ICELAND', 'Iceland', 'ISL', 352, 354, NULL, NULL),
(99, 'IN', 'INDIA', 'India', 'IND', 356, 91, NULL, NULL),
(100, 'ID', 'INDONESIA', 'Indonesia', 'IDN', 360, 62, NULL, NULL),
(101, 'IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', 'IRN', 364, 98, NULL, NULL),
(102, 'IQ', 'IRAQ', 'Iraq', 'IRQ', 368, 964, NULL, NULL),
(103, 'IE', 'IRELAND', 'Ireland', 'IRL', 372, 353, NULL, NULL),
(104, 'IL', 'ISRAEL', 'Israel', 'ISR', 376, 972, NULL, NULL),
(105, 'IT', 'ITALY', 'Italy', 'ITA', 380, 39, NULL, NULL),
(106, 'JM', 'JAMAICA', 'Jamaica', 'JAM', 388, 1876, NULL, NULL),
(107, 'JP', 'JAPAN', 'Japan', 'JPN', 392, 81, NULL, NULL),
(108, 'JO', 'JORDAN', 'Jordan', 'JOR', 400, 962, NULL, NULL),
(109, 'KZ', 'KAZAKHSTAN', 'Kazakhstan', 'KAZ', 398, 7, NULL, NULL),
(110, 'KE', 'KENYA', 'Kenya', 'KEN', 404, 254, NULL, NULL),
(111, 'KI', 'KIRIBATI', 'Kiribati', 'KIR', 296, 686, NULL, NULL),
(112, 'KP', 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF', 'Korea, Democratic People\'s Republic of', 'PRK', 408, 850, NULL, NULL),
(113, 'KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', 'KOR', 410, 82, NULL, NULL),
(114, 'KW', 'KUWAIT', 'Kuwait', 'KWT', 414, 965, NULL, NULL),
(115, 'KG', 'KYRGYZSTAN', 'Kyrgyzstan', 'KGZ', 417, 996, NULL, NULL),
(116, 'LA', 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC', 'Lao People\'s Democratic Republic', 'LAO', 418, 856, NULL, NULL),
(117, 'LV', 'LATVIA', 'Latvia', 'LVA', 428, 371, NULL, NULL),
(118, 'LB', 'LEBANON', 'Lebanon', 'LBN', 422, 961, NULL, NULL),
(119, 'LS', 'LESOTHO', 'Lesotho', 'LSO', 426, 266, NULL, NULL),
(120, 'LR', 'LIBERIA', 'Liberia', 'LBR', 430, 231, NULL, NULL),
(121, 'LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', 'LBY', 434, 218, NULL, NULL),
(122, 'LI', 'LIECHTENSTEIN', 'Liechtenstein', 'LIE', 438, 423, NULL, NULL),
(123, 'LT', 'LITHUANIA', 'Lithuania', 'LTU', 440, 370, NULL, NULL),
(124, 'LU', 'LUXEMBOURG', 'Luxembourg', 'LUX', 442, 352, NULL, NULL),
(125, 'MO', 'MACAO', 'Macao', 'MAC', 446, 853, NULL, NULL),
(126, 'MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807, 389, NULL, NULL),
(127, 'MG', 'MADAGASCAR', 'Madagascar', 'MDG', 450, 261, NULL, NULL),
(128, 'MW', 'MALAWI', 'Malawi', 'MWI', 454, 265, NULL, NULL),
(129, 'MY', 'MALAYSIA', 'Malaysia', 'MYS', 458, 60, NULL, NULL),
(130, 'MV', 'MALDIVES', 'Maldives', 'MDV', 462, 960, NULL, NULL),
(131, 'ML', 'MALI', 'Mali', 'MLI', 466, 223, NULL, NULL),
(132, 'MT', 'MALTA', 'Malta', 'MLT', 470, 356, NULL, NULL),
(133, 'MH', 'MARSHALL ISLANDS', 'Marshall Islands', 'MHL', 584, 692, NULL, NULL),
(134, 'MQ', 'MARTINIQUE', 'Martinique', 'MTQ', 474, 596, NULL, NULL),
(135, 'MR', 'MAURITANIA', 'Mauritania', 'MRT', 478, 222, NULL, NULL),
(136, 'MU', 'MAURITIUS', 'Mauritius', 'MUS', 480, 230, NULL, NULL),
(137, 'YT', 'MAYOTTE', 'Mayotte', NULL, NULL, 269, NULL, NULL),
(138, 'MX', 'MEXICO', 'Mexico', 'MEX', 484, 52, NULL, NULL),
(139, 'FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', 'FSM', 583, 691, NULL, NULL),
(140, 'MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', 'MDA', 498, 373, NULL, NULL),
(141, 'MC', 'MONACO', 'Monaco', 'MCO', 492, 377, NULL, NULL),
(142, 'MN', 'MONGOLIA', 'Mongolia', 'MNG', 496, 976, NULL, NULL),
(143, 'MS', 'MONTSERRAT', 'Montserrat', 'MSR', 500, 1664, NULL, NULL),
(144, 'MA', 'MOROCCO', 'Morocco', 'MAR', 504, 212, NULL, NULL),
(145, 'MZ', 'MOZAMBIQUE', 'Mozambique', 'MOZ', 508, 258, NULL, NULL),
(146, 'MM', 'MYANMAR', 'Myanmar', 'MMR', 104, 95, NULL, NULL),
(147, 'NA', 'NAMIBIA', 'Namibia', 'NAM', 516, 264, NULL, NULL),
(148, 'NR', 'NAURU', 'Nauru', 'NRU', 520, 674, NULL, NULL),
(149, 'NP', 'NEPAL', 'Nepal', 'NPL', 524, 977, NULL, NULL),
(150, 'NL', 'NETHERLANDS', 'Netherlands', 'NLD', 528, 31, NULL, NULL),
(151, 'AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', 'ANT', 530, 599, NULL, NULL),
(152, 'NC', 'NEW CALEDONIA', 'New Caledonia', 'NCL', 540, 687, NULL, NULL),
(153, 'NZ', 'NEW ZEALAND', 'New Zealand', 'NZL', 554, 64, NULL, NULL),
(154, 'NI', 'NICARAGUA', 'Nicaragua', 'NIC', 558, 505, NULL, NULL),
(155, 'NE', 'NIGER', 'Niger', 'NER', 562, 227, NULL, NULL),
(156, 'NG', 'NIGERIA', 'Nigeria', 'NGA', 566, 234, NULL, NULL),
(157, 'NU', 'NIUE', 'Niue', 'NIU', 570, 683, NULL, NULL),
(158, 'NF', 'NORFOLK ISLAND', 'Norfolk Island', 'NFK', 574, 672, NULL, NULL),
(159, 'MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', 'MNP', 580, 1670, NULL, NULL),
(160, 'NO', 'NORWAY', 'Norway', 'NOR', 578, 47, NULL, NULL),
(161, 'OM', 'OMAN', 'Oman', 'OMN', 512, 968, NULL, NULL),
(162, 'PK', 'PAKISTAN', 'Pakistan', 'PAK', 586, 92, NULL, NULL),
(163, 'PW', 'PALAU', 'Palau', 'PLW', 585, 680, NULL, NULL),
(164, 'PS', 'PALESTINIAN TERRITORY, OCCUPIED', 'Palestinian Territory, Occupied', NULL, NULL, 970, NULL, NULL),
(165, 'PA', 'PANAMA', 'Panama', 'PAN', 591, 507, NULL, NULL),
(166, 'PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', 'PNG', 598, 675, NULL, NULL),
(167, 'PY', 'PARAGUAY', 'Paraguay', 'PRY', 600, 595, NULL, NULL),
(168, 'PE', 'PERU', 'Peru', 'PER', 604, 51, NULL, NULL),
(169, 'PH', 'PHILIPPINES', 'Philippines', 'PHL', 608, 63, NULL, NULL),
(170, 'PN', 'PITCAIRN', 'Pitcairn', 'PCN', 612, 0, NULL, NULL),
(171, 'PL', 'POLAND', 'Poland', 'POL', 616, 48, NULL, NULL),
(172, 'PT', 'PORTUGAL', 'Portugal', 'PRT', 620, 351, NULL, NULL),
(173, 'PR', 'PUERTO RICO', 'Puerto Rico', 'PRI', 630, 1787, NULL, NULL),
(174, 'QA', 'QATAR', 'Qatar', 'QAT', 634, 974, NULL, NULL),
(175, 'RE', 'REUNION', 'Reunion', 'REU', 638, 262, NULL, NULL),
(176, 'RO', 'ROMANIA', 'Romania', 'ROM', 642, 40, NULL, NULL),
(177, 'RU', 'RUSSIAN FEDERATION', 'Russian Federation', 'RUS', 643, 70, NULL, NULL),
(178, 'RW', 'RWANDA', 'Rwanda', 'RWA', 646, 250, NULL, NULL),
(179, 'SH', 'SAINT HELENA', 'Saint Helena', 'SHN', 654, 290, NULL, NULL),
(180, 'KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', 'KNA', 659, 1869, NULL, NULL),
(181, 'LC', 'SAINT LUCIA', 'Saint Lucia', 'LCA', 662, 1758, NULL, NULL),
(182, 'PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', 'SPM', 666, 508, NULL, NULL),
(183, 'VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', 'VCT', 670, 1784, NULL, NULL),
(184, 'WS', 'SAMOA', 'Samoa', 'WSM', 882, 684, NULL, NULL),
(185, 'SM', 'SAN MARINO', 'San Marino', 'SMR', 674, 378, NULL, NULL),
(186, 'ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', 'STP', 678, 239, NULL, NULL),
(187, 'SA', 'SAUDI ARABIA', 'Saudi Arabia', 'SAU', 682, 966, NULL, NULL),
(188, 'SN', 'SENEGAL', 'Senegal', 'SEN', 686, 221, NULL, NULL),
(189, 'CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', NULL, NULL, 381, NULL, NULL),
(190, 'SC', 'SEYCHELLES', 'Seychelles', 'SYC', 690, 248, NULL, NULL),
(191, 'SL', 'SIERRA LEONE', 'Sierra Leone', 'SLE', 694, 232, NULL, NULL),
(192, 'SG', 'SINGAPORE', 'Singapore', 'SGP', 702, 65, NULL, NULL),
(193, 'SK', 'SLOVAKIA', 'Slovakia', 'SVK', 703, 421, NULL, NULL),
(194, 'SI', 'SLOVENIA', 'Slovenia', 'SVN', 705, 386, NULL, NULL),
(195, 'SB', 'SOLOMON ISLANDS', 'Solomon Islands', 'SLB', 90, 677, NULL, NULL),
(196, 'SO', 'SOMALIA', 'Somalia', 'SOM', 706, 252, NULL, NULL),
(197, 'ZA', 'SOUTH AFRICA', 'South Africa', 'ZAF', 710, 27, NULL, NULL),
(198, 'GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', NULL, NULL, 0, NULL, NULL),
(199, 'ES', 'SPAIN', 'Spain', 'ESP', 724, 34, NULL, NULL),
(200, 'LK', 'SRI LANKA', 'Sri Lanka', 'LKA', 144, 94, NULL, NULL),
(201, 'SD', 'SUDAN', 'Sudan', 'SDN', 736, 249, NULL, NULL),
(202, 'SR', 'SURINAME', 'Suriname', 'SUR', 740, 597, NULL, NULL),
(203, 'SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', 'SJM', 744, 47, NULL, NULL),
(204, 'SZ', 'SWAZILAND', 'Swaziland', 'SWZ', 748, 268, NULL, NULL),
(205, 'SE', 'SWEDEN', 'Sweden', 'SWE', 752, 46, NULL, NULL),
(206, 'CH', 'SWITZERLAND', 'Switzerland', 'CHE', 756, 41, NULL, NULL),
(207, 'SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', 'SYR', 760, 963, NULL, NULL),
(208, 'TW', 'TAIWAN, PROVINCE OF CHINA', 'Taiwan, Province of China', 'TWN', 158, 886, NULL, NULL),
(209, 'TJ', 'TAJIKISTAN', 'Tajikistan', 'TJK', 762, 992, NULL, NULL),
(210, 'TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', 'TZA', 834, 255, NULL, NULL),
(211, 'TH', 'THAILAND', 'Thailand', 'THA', 764, 66, NULL, NULL),
(212, 'TL', 'TIMOR-LESTE', 'Timor-Leste', NULL, NULL, 670, NULL, NULL),
(213, 'TG', 'TOGO', 'Togo', 'TGO', 768, 228, NULL, NULL),
(214, 'TK', 'TOKELAU', 'Tokelau', 'TKL', 772, 690, NULL, NULL),
(215, 'TO', 'TONGA', 'Tonga', 'TON', 776, 676, NULL, NULL),
(216, 'TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', 'TTO', 780, 1868, NULL, NULL),
(217, 'TN', 'TUNISIA', 'Tunisia', 'TUN', 788, 216, NULL, NULL),
(218, 'TR', 'TURKEY', 'Turkey', 'TUR', 792, 90, NULL, NULL),
(219, 'TM', 'TURKMENISTAN', 'Turkmenistan', 'TKM', 795, 7370, NULL, NULL),
(220, 'TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', 'TCA', 796, 1649, NULL, NULL),
(221, 'TV', 'TUVALU', 'Tuvalu', 'TUV', 798, 688, NULL, NULL),
(222, 'UG', 'UGANDA', 'Uganda', 'UGA', 800, 256, NULL, NULL),
(223, 'UA', 'UKRAINE', 'Ukraine', 'UKR', 804, 380, NULL, NULL),
(224, 'AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', 'ARE', 784, 971, NULL, NULL),
(225, 'GB', 'UNITED KINGDOM', 'United Kingdom', 'GBR', 826, 44, NULL, NULL),
(226, 'US', 'UNITED STATES', 'United States', 'USA', 840, 1, NULL, NULL),
(227, 'UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', NULL, NULL, 1, NULL, NULL),
(228, 'UY', 'URUGUAY', 'Uruguay', 'URY', 858, 598, NULL, NULL),
(229, 'UZ', 'UZBEKISTAN', 'Uzbekistan', 'UZB', 860, 998, NULL, NULL),
(230, 'VU', 'VANUATU', 'Vanuatu', 'VUT', 548, 678, NULL, NULL),
(231, 'VE', 'VENEZUELA', 'Venezuela', 'VEN', 862, 58, NULL, NULL),
(232, 'VN', 'VIET NAM', 'Viet Nam', 'VNM', 704, 84, NULL, NULL),
(233, 'VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', 'VGB', 92, 1284, NULL, NULL),
(234, 'VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', 'VIR', 850, 1340, NULL, NULL),
(235, 'WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', 'WLF', 876, 681, NULL, NULL),
(236, 'EH', 'WESTERN SAHARA', 'Western Sahara', 'ESH', 732, 212, NULL, NULL),
(237, 'YE', 'YEMEN', 'Yemen', 'YEM', 887, 967, NULL, NULL),
(238, 'ZM', 'ZAMBIA', 'Zambia', 'ZMB', 894, 260, NULL, NULL),
(239, 'ZW', 'ZIMBABWE', 'Zimbabwe', 'ZWE', 716, 263, NULL, NULL),
(240, 'RS', 'SERBIA', 'Serbia', 'SRB', 688, 381, NULL, NULL),
(241, 'AP', 'ASIA PACIFIC REGION', 'Asia / Pacific Region', '0', 0, 0, NULL, NULL),
(242, 'ME', 'MONTENEGRO', 'Montenegro', 'MNE', 499, 382, NULL, NULL),
(243, 'AX', 'ALAND ISLANDS', 'Aland Islands', 'ALA', 248, 358, NULL, NULL),
(244, 'BQ', 'BONAIRE, SINT EUSTATIUS AND SABA', 'Bonaire, Sint Eustatius and Saba', 'BES', 535, 599, NULL, NULL),
(245, 'CW', 'CURACAO', 'Curacao', 'CUW', 531, 599, NULL, NULL),
(246, 'GG', 'GUERNSEY', 'Guernsey', 'GGY', 831, 44, NULL, NULL),
(247, 'IM', 'ISLE OF MAN', 'Isle of Man', 'IMN', 833, 44, NULL, NULL),
(248, 'JE', 'JERSEY', 'Jersey', 'JEY', 832, 44, NULL, NULL),
(249, 'XK', 'KOSOVO', 'Kosovo', '---', 0, 381, NULL, NULL),
(250, 'BL', 'SAINT BARTHELEMY', 'Saint Barthelemy', 'BLM', 652, 590, NULL, NULL),
(251, 'MF', 'SAINT MARTIN', 'Saint Martin', 'MAF', 663, 590, NULL, NULL),
(252, 'SX', 'SINT MAARTEN', 'Sint Maarten', 'SXM', 534, 1, NULL, NULL),
(253, 'SS', 'SOUTH SUDAN', 'South Sudan', 'SSD', 728, 211, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `csv_customer_imports`
--

CREATE TABLE `csv_customer_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint DEFAULT NULL COMMENT '1-Pending, 2-Success, 3-Failed, 4-In-progress',
  `error` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `csv_product_imports`
--

CREATE TABLE `csv_product_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `type` tinyint DEFAULT '0' COMMENT '0 for csv, 1 for woocommerce',
  `status` tinyint DEFAULT NULL COMMENT '1-Pending, 2-Success, 3-Failed, 4-In-progress',
  `raw_data` json DEFAULT NULL,
  `error` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `csv_product_imports`
--

INSERT INTO `csv_product_imports` (`id`, `vendor_id`, `name`, `path`, `uploaded_by`, `type`, `status`, `raw_data`, `error`, `created_at`, `updated_at`) VALUES
(1, 6, '1626175336_product csv.csv', '/storage/csv_products/1626175336_product csv.csv', NULL, 0, 3, NULL, '[\"Row 1 : This category is not activated for this vendor\",\"Row 2 : This category is not activated for this vendor\",\"Row 3 : This category is not activated for this vendor\",\"Row 4 : This category is not activated for this vendor\",\"Row 5 : This category is not activated for this vendor\",\"Row 6 : This category is not activated for this vendor\",\"Row 7 : This category is not activated for this vendor\"]', '2021-07-13 11:22:16', '2021-07-13 11:22:16'),
(2, 6, '1626175358_product csv.csv', '/storage/csv_products/1626175358_product csv.csv', NULL, 0, 2, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:22:38'),
(3, 6, '1626782276_1626411919_1626334501_continental product csv.csv', '/storage/csv_products/1626782276_1626411919_1626334501_continental product csv.csv', NULL, 0, 2, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 11:57:56'),
(4, 5, '1626782344_italian (1).csv', '/storage/csv_products/1626782344_italian (1).csv', NULL, 0, 2, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 11:59:04'),
(5, 4, '1626782419_1626412226_1626341128_chinese product csv.csv', '/storage/csv_products/1626782419_1626412226_1626341128_chinese product csv.csv', NULL, 0, 2, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:00:19'),
(6, 3, '1626782469_1626412527_1626340125_dessert product csv.csv', '/storage/csv_products/1626782469_1626412527_1626340125_dessert product csv.csv', NULL, 0, 2, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:01:09'),
(7, 3, '1626782513_snacks (2) (1).csv', '/storage/csv_products/1626782513_snacks (2) (1).csv', NULL, 0, 2, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:01:53'),
(8, 2, '1626782619_Beverages (1).csv', '/storage/csv_products/1626782619_Beverages (1).csv', NULL, 0, 3, NULL, '[\"Row 1 : Category doesn\'t exist\",\"Row 2 : Category doesn\'t exist\",\"Row 3 : Category doesn\'t exist\",\"Row 4 : Category doesn\'t exist\",\"Row 5 : Category doesn\'t exist\",\"Row 6 : Category doesn\'t exist\"]', '2021-07-20 12:03:39', '2021-07-20 12:03:39'),
(9, 2, '1626782703_Beverages (1).csv', '/storage/csv_products/1626782703_Beverages (1).csv', NULL, 0, 2, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:05:03'),
(10, 2, '1626782742_salad (2) (1).csv', '/storage/csv_products/1626782742_salad (2) (1).csv', NULL, 0, 2, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `csv_qrcode_imports`
--

CREATE TABLE `csv_qrcode_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint DEFAULT NULL COMMENT '1-Pending, 2-Success, 3-Failed, 4-In-progress',
  `error` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `csv_vendor_imports`
--

CREATE TABLE `csv_vendor_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint DEFAULT NULL COMMENT '1-Pending, 2-Success, 3-Failed, 4-In-progress',
  `error` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `csv_vendor_imports`
--

INSERT INTO `csv_vendor_imports` (`id`, `name`, `path`, `uploaded_by`, `status`, `error`, `created_at`, `updated_at`) VALUES
(1, '1626173628_Restaurant vendor csv.csv', '/storage/csv_vendors/1626173628_Restaurant vendor csv.csv', NULL, 2, NULL, '2021-07-13 10:53:48', '2021-07-13 10:53:49');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` int NOT NULL DEFAULT '0',
  `iso_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subunit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subunit_to_unit` int NOT NULL,
  `symbol_first` tinyint NOT NULL,
  `html_entity` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `decimal_mark` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thousands_separator` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_numeric` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `name`, `priority`, `iso_code`, `symbol`, `subunit`, `subunit_to_unit`, `symbol_first`, `html_entity`, `decimal_mark`, `thousands_separator`, `iso_numeric`, `created_at`, `updated_at`) VALUES
(1, 'United Arab Emirates Dirham', 100, 'AED', 'د.إ', 'Fils', 100, 1, '', '.', ',', 784, NULL, NULL),
(2, 'Afghan Afghani', 100, 'AFN', '؋', 'Pul', 100, 0, '', '.', ',', 971, NULL, NULL),
(3, 'Albanian Lek', 100, 'ALL', 'L', 'Qintar', 100, 0, '', '.', ',', 8, NULL, NULL),
(4, 'Armenian Dram', 100, 'AMD', 'դր.', 'Luma', 100, 0, '', '.', ',', 51, NULL, NULL),
(5, 'Netherlands Antillean Gulden', 100, 'ANG', 'ƒ', 'Cent', 100, 1, '&#x0192;', ',', '.', 532, NULL, NULL),
(6, 'Angolan Kwanza', 100, 'AOA', 'Kz', 'Cêntimo', 100, 0, '', '.', ',', 973, NULL, NULL),
(7, 'Argentine Peso', 100, 'ARS', '$', 'Centavo', 100, 1, '&#x20B1;', ',', '.', 32, NULL, NULL),
(8, 'Australian Dollar', 4, 'AUD', '$', 'Cent', 100, 1, '$', '.', ',', 36, NULL, NULL),
(9, 'Aruban Florin', 100, 'AWG', 'ƒ', 'Cent', 100, 0, '&#x0192;', '.', ',', 533, NULL, NULL),
(10, 'Azerbaijani Manat', 100, 'AZN', 'null', 'Qəpik', 100, 1, '', '.', ',', 944, NULL, NULL),
(11, 'Bosnia and Herzegovina Convertible Mark', 100, 'BAM', 'КМ', 'Fening', 100, 1, '', '.', ',', 977, NULL, NULL),
(12, 'Barbadian Dollar', 100, 'BBD', '$', 'Cent', 100, 0, '$', '.', ',', 52, NULL, NULL),
(13, 'Bangladeshi Taka', 100, 'BDT', '৳', 'Paisa', 100, 1, '', '.', ',', 50, NULL, NULL),
(14, 'Bulgarian Lev', 100, 'BGN', 'лв', 'Stotinka', 100, 0, '', '.', ',', 975, NULL, NULL),
(15, 'Bahraini Dinar', 100, 'BHD', 'ب.د', 'Fils', 1000, 1, '', '.', ',', 48, NULL, NULL),
(16, 'Burundian Franc', 100, 'BIF', 'Fr', 'Centime', 100, 0, '', '.', ',', 108, NULL, NULL),
(17, 'Bermudian Dollar', 100, 'BMD', '$', 'Cent', 100, 1, '$', '.', ',', 60, NULL, NULL),
(18, 'Brunei Dollar', 100, 'BND', '$', 'Sen', 100, 1, '$', '.', ',', 96, NULL, NULL),
(19, 'Bolivian Boliviano', 100, 'BOB', 'Bs.', 'Centavo', 100, 1, '', '.', ',', 68, NULL, NULL),
(20, 'Brazilian Real', 100, 'BRL', 'R$', 'Centavo', 100, 1, 'R$', ',', '.', 986, NULL, NULL),
(21, 'Bahamian Dollar', 100, 'BSD', '$', 'Cent', 100, 1, '$', '.', ',', 44, NULL, NULL),
(22, 'Bhutanese Ngultrum', 100, 'BTN', 'Nu.', 'Chertrum', 100, 0, '', '.', ',', 64, NULL, NULL),
(23, 'Botswana Pula', 100, 'BWP', 'P', 'Thebe', 100, 1, '', '.', ',', 72, NULL, NULL),
(24, 'Belarusian Ruble', 100, 'BYR', 'Br', 'Kapyeyka', 100, 0, '', '.', ',', 974, NULL, NULL),
(25, 'Belize Dollar', 100, 'BZD', '$', 'Cent', 100, 1, '$', '.', ',', 84, NULL, NULL),
(26, 'Canadian Dollar', 5, 'CAD', '$', 'Cent', 100, 1, '$', '.', ',', 124, NULL, NULL),
(27, 'Congolese Franc', 100, 'CDF', 'Fr', 'Centime', 100, 0, '', '.', ',', 976, NULL, NULL),
(28, 'Swiss Franc', 100, 'CHF', 'Fr', 'Rappen', 100, 1, '', '.', ',', 756, NULL, NULL),
(29, 'Unidad de Fomento', 100, 'CLF', 'UF', 'Peso', 1, 1, '&#x20B1;', ',', '.', 990, NULL, NULL),
(30, 'Chilean Peso', 100, 'CLP', '$', 'Peso', 1, 1, '&#36;', ',', '.', 152, NULL, NULL),
(31, 'Chinese Renminbi Yuan', 100, 'CNY', '¥', 'Fen', 100, 1, '&#20803;', '.', ',', 156, NULL, NULL),
(32, 'Colombian Peso', 100, 'COP', '$', 'Centavo', 100, 1, '&#x20B1;', ',', '.', 170, NULL, NULL),
(33, 'Costa Rican Colón', 100, 'CRC', '₡', 'Céntimo', 100, 1, '&#x20A1;', ',', '.', 188, NULL, NULL),
(34, 'Cuban Convertible Peso', 100, 'CUC', '$', 'Centavo', 100, 0, '', '.', ',', 931, NULL, NULL),
(35, 'Cuban Peso', 100, 'CUP', '$', 'Centavo', 100, 1, '&#x20B1;', '.', ',', 192, NULL, NULL),
(36, 'Cape Verdean Escudo', 100, 'CVE', '$', 'Centavo', 100, 0, '', '.', ',', 132, NULL, NULL),
(37, 'Czech Koruna', 100, 'CZK', 'Kč', 'Haléř', 100, 1, '', ',', '.', 203, NULL, NULL),
(38, 'Djiboutian Franc', 100, 'DJF', 'Fdj', 'Centime', 100, 0, '', '.', ',', 262, NULL, NULL),
(39, 'Danish Krone', 100, 'DKK', 'kr', 'Øre', 100, 0, '', ',', '.', 208, NULL, NULL),
(40, 'Dominican Peso', 100, 'DOP', '$', 'Centavo', 100, 1, '&#x20B1;', '.', ',', 214, NULL, NULL),
(41, 'Algerian Dinar', 100, 'DZD', 'د.ج', 'Centime', 100, 0, '', '.', ',', 12, NULL, NULL),
(42, 'Egyptian Pound', 100, 'EGP', 'ج.م', 'Piastre', 100, 1, '&#x00A3;', '.', ',', 818, NULL, NULL),
(43, 'Eritrean Nakfa', 100, 'ERN', 'Nfk', 'Cent', 100, 0, '', '.', ',', 232, NULL, NULL),
(44, 'Ethiopian Birr', 100, 'ETB', 'Br', 'Santim', 100, 0, '', '.', ',', 230, NULL, NULL),
(45, 'Euro', 2, 'EUR', '€', 'Cent', 100, 1, '&#x20AC;', ',', '.', 978, NULL, NULL),
(46, 'Fijian Dollar', 100, 'FJD', '$', 'Cent', 100, 0, '$', '.', ',', 242, NULL, NULL),
(47, 'Falkland Pound', 100, 'FKP', '£', 'Penny', 100, 0, '&#x00A3;', '.', ',', 238, NULL, NULL),
(48, 'British Pound', 3, 'GBP', '£', 'Penny', 100, 1, '&#x00A3;', '.', ',', 826, NULL, NULL),
(49, 'Georgian Lari', 100, 'GEL', 'ლ', 'Tetri', 100, 0, '', '.', ',', 981, NULL, NULL),
(50, 'Ghanaian Cedi', 100, 'GHS', '₵', 'Pesewa', 100, 1, '&#x20B5;', '.', ',', 936, NULL, NULL),
(51, 'Gibraltar Pound', 100, 'GIP', '£', 'Penny', 100, 1, '&#x00A3;', '.', ',', 292, NULL, NULL),
(52, 'Gambian Dalasi', 100, 'GMD', 'D', 'Butut', 100, 0, '', '.', ',', 270, NULL, NULL),
(53, 'Guinean Franc', 100, 'GNF', 'Fr', 'Centime', 100, 0, '', '.', ',', 324, NULL, NULL),
(54, 'Guatemalan Quetzal', 100, 'GTQ', 'Q', 'Centavo', 100, 1, '', '.', ',', 320, NULL, NULL),
(55, 'Guyanese Dollar', 100, 'GYD', '$', 'Cent', 100, 0, '$', '.', ',', 328, NULL, NULL),
(56, 'Hong Kong Dollar', 100, 'HKD', '$', 'Cent', 100, 1, '$', '.', ',', 344, NULL, NULL),
(57, 'Honduran Lempira', 100, 'HNL', 'L', 'Centavo', 100, 1, '', '.', ',', 340, NULL, NULL),
(58, 'Croatian Kuna', 100, 'HRK', 'kn', 'Lipa', 100, 1, '', ',', '.', 191, NULL, NULL),
(59, 'Haitian Gourde', 100, 'HTG', 'G', 'Centime', 100, 0, '', '.', ',', 332, NULL, NULL),
(60, 'Hungarian Forint', 100, 'HUF', 'Ft', 'Fillér', 100, 0, '', ',', '.', 348, NULL, NULL),
(61, 'Indonesian Rupiah', 100, 'IDR', 'Rp', 'Sen', 100, 1, '', ',', '.', 360, NULL, NULL),
(62, 'Israeli New Sheqel', 100, 'ILS', '₪', 'Agora', 100, 1, '&#x20AA;', '.', ',', 376, NULL, NULL),
(63, 'Indian Rupee', 100, 'INR', '₹', 'Paisa', 100, 1, '&#x20b9;', '.', ',', 356, NULL, NULL),
(64, 'Iraqi Dinar', 100, 'IQD', 'ع.د', 'Fils', 1000, 0, '', '.', ',', 368, NULL, NULL),
(65, 'Iranian Rial', 100, 'IRR', '﷼', 'Dinar', 100, 1, '&#xFDFC;', '.', ',', 364, NULL, NULL),
(66, 'Icelandic Króna', 100, 'ISK', 'kr', 'Eyrir', 100, 1, '', ',', '.', 352, NULL, NULL),
(67, 'Jamaican Dollar', 100, 'JMD', '$', 'Cent', 100, 1, '$', '.', ',', 388, NULL, NULL),
(68, 'Jordanian Dinar', 100, 'JOD', 'د.ا', 'Piastre', 100, 1, '', '.', ',', 400, NULL, NULL),
(69, 'Japanese Yen', 6, 'JPY', '¥', 'null', 1, 1, '&#x00A5;', '.', ',', 392, NULL, NULL),
(70, 'Kenyan Shilling', 100, 'KES', 'KSh', 'Cent', 100, 1, '', '.', ',', 404, NULL, NULL),
(71, 'Kyrgyzstani Som', 100, 'KGS', 'som', 'Tyiyn', 100, 0, '', '.', ',', 417, NULL, NULL),
(72, 'Cambodian Riel', 100, 'KHR', '៛', 'Sen', 100, 0, '&#x17\\DB;', '.', ',', 116, NULL, NULL),
(73, 'Comorian Franc', 100, 'KMF', 'Fr', 'Centime', 100, 0, '', '.', ',', 174, NULL, NULL),
(74, 'North Korean Won', 100, 'KPW', '₩', 'Chŏn', 100, 0, '&#x20A9;', '.', ',', 408, NULL, NULL),
(75, 'South Korean Won', 100, 'KRW', '₩', 'null', 1, 1, '&#x20A9;', '.', ',', 410, NULL, NULL),
(76, 'Kuwaiti Dinar', 100, 'KWD', 'د.ك', 'Fils', 1000, 1, '', '.', ',', 414, NULL, NULL),
(77, 'Cayman Islands Dollar', 100, 'KYD', '$', 'Cent', 100, 1, '$', '.', ',', 136, NULL, NULL),
(78, 'Kazakhstani Tenge', 100, 'KZT', '〒', 'Tiyn', 100, 0, '', '.', ',', 398, NULL, NULL),
(79, 'Lao Kip', 100, 'LAK', '₭', 'Att', 100, 0, '&#x20AD;', '.', ',', 418, NULL, NULL),
(80, 'Lebanese Pound', 100, 'LBP', 'ل.ل', 'Piastre', 100, 1, '&#x00A3;', '.', ',', 422, NULL, NULL),
(81, 'Sri Lankan Rupee', 100, 'LKR', '₨', 'Cent', 100, 0, '&#x0BF9;', '.', ',', 144, NULL, NULL),
(82, 'Liberian Dollar', 100, 'LRD', '$', 'Cent', 100, 0, '$', '.', ',', 430, NULL, NULL),
(83, 'Lesotho Loti', 100, 'LSL', 'L', 'Sente', 100, 0, '', '.', ',', 426, NULL, NULL),
(84, 'Lithuanian Litas', 100, 'LTL', 'Lt', 'Centas', 100, 0, '', '.', ',', 440, NULL, NULL),
(85, 'Latvian Lats', 100, 'LVL', 'Ls', 'Santīms', 100, 1, '', '.', ',', 428, NULL, NULL),
(86, 'Libyan Dinar', 100, 'LYD', 'ل.د', 'Dirham', 1000, 0, '', '.', ',', 434, NULL, NULL),
(87, 'Moroccan Dirham', 100, 'MAD', 'د.م.', 'Centime', 100, 0, '', '.', ',', 504, NULL, NULL),
(88, 'Moldovan Leu', 100, 'MDL', 'L', 'Ban', 100, 0, '', '.', ',', 498, NULL, NULL),
(89, 'Malagasy Ariary', 100, 'MGA', 'Ar', 'Iraimbilanja', 5, 1, '', '.', ',', 969, NULL, NULL),
(90, 'Macedonian Denar', 100, 'MKD', 'ден', 'Deni', 100, 0, '', '.', ',', 807, NULL, NULL),
(91, 'Myanmar Kyat', 100, 'MMK', 'K', 'Pya', 100, 0, '', '.', ',', 104, NULL, NULL),
(92, 'Mongolian Tögrög', 100, 'MNT', '₮', 'Möngö', 100, 0, '&#x20AE;', '.', ',', 496, NULL, NULL),
(93, 'Macanese Pataca', 100, 'MOP', 'P', 'Avo', 100, 0, '', '.', ',', 446, NULL, NULL),
(94, 'Mauritanian Ouguiya', 100, 'MRO', 'UM', 'Khoums', 5, 0, '', '.', ',', 478, NULL, NULL),
(95, 'Mauritian Rupee', 100, 'MUR', '₨', 'Cent', 100, 1, '&#x20A8;', '.', ',', 480, NULL, NULL),
(96, 'Maldivian Rufiyaa', 100, 'MVR', 'MVR', 'Laari', 100, 0, '', '.', ',', 462, NULL, NULL),
(97, 'Malawian Kwacha', 100, 'MWK', 'MK', 'Tambala', 100, 0, '', '.', ',', 454, NULL, NULL),
(98, 'Mexican Peso', 100, 'MXN', '$', 'Centavo', 100, 1, '$', '.', ',', 484, NULL, NULL),
(99, 'Malaysian Ringgit', 100, 'MYR', 'RM', 'Sen', 100, 1, '', '.', ',', 458, NULL, NULL),
(100, 'Mozambican Metical', 100, 'MZN', 'MTn', 'Centavo', 100, 1, '', ',', '.', 943, NULL, NULL),
(101, 'Namibian Dollar', 100, 'NAD', '$', 'Cent', 100, 0, '$', '.', ',', 516, NULL, NULL),
(102, 'Nigerian Naira', 100, 'NGN', '₦', 'Kobo', 100, 0, '&#x20A6;', '.', ',', 566, NULL, NULL),
(103, 'Nicaraguan Córdoba', 100, 'NIO', 'C$', 'Centavo', 100, 0, '', '.', ',', 558, NULL, NULL),
(104, 'Norwegian Krone', 100, 'NOK', 'kr', 'Øre', 100, 1, 'kr', ',', '.', 578, NULL, NULL),
(105, 'Nepalese Rupee', 100, 'NPR', '₨', 'Paisa', 100, 1, '&#x20A8;', '.', ',', 524, NULL, NULL),
(106, 'New Zealand Dollar', 100, 'NZD', '$', 'Cent', 100, 1, '$', '.', ',', 554, NULL, NULL),
(107, 'Omani Rial', 100, 'OMR', 'ر.ع.', 'Baisa', 1000, 1, '&#xFDFC;', '.', ',', 512, NULL, NULL),
(108, 'Panamanian Balboa', 100, 'PAB', 'B/.', 'Centésimo', 100, 0, '', '.', ',', 590, NULL, NULL),
(109, 'Peruvian Nuevo Sol', 100, 'PEN', 'S/.', 'Céntimo', 100, 1, 'S/.', '.', ',', 604, NULL, NULL),
(110, 'Papua New Guinean Kina', 100, 'PGK', 'K', 'Toea', 100, 0, '', '.', ',', 598, NULL, NULL),
(111, 'Philippine Peso', 100, 'PHP', '₱', 'Centavo', 100, 1, '&#x20B1;', '.', ',', 608, NULL, NULL),
(112, 'Pakistani Rupee', 100, 'PKR', '₨', 'Paisa', 100, 1, '&#x20A8;', '.', ',', 586, NULL, NULL),
(113, 'Polish Złoty', 100, 'PLN', 'zł', 'Grosz', 100, 0, 'z&#322;', ',', '', 985, NULL, NULL),
(114, 'Paraguayan Guaraní', 100, 'PYG', '₲', 'Céntimo', 100, 1, '&#x20B2;', '.', ',', 600, NULL, NULL),
(115, 'Qatari Riyal', 100, 'QAR', 'ر.ق', 'Dirham', 100, 0, '&#xFDFC;', '.', ',', 634, NULL, NULL),
(116, 'Romanian Leu', 100, 'RON', 'Lei', 'Bani', 100, 1, '', ',', '.', 946, NULL, NULL),
(117, 'Serbian Dinar', 100, 'RSD', 'РСД', 'Para', 100, 1, '', '.', ',', 941, NULL, NULL),
(118, 'Russian Ruble', 100, 'RUB', 'р.', 'Kopek', 100, 0, '&#x0440;&#x0443;&#x0431;', ',', '.', 643, NULL, NULL),
(119, 'Rwandan Franc', 100, 'RWF', 'FRw', 'Centime', 100, 0, '', '.', ',', 646, NULL, NULL),
(120, 'Saudi Riyal', 100, 'SAR', 'ر.س', 'Hallallah', 100, 1, '&#xFDFC;', '.', ',', 682, NULL, NULL),
(121, 'Solomon Islands Dollar', 100, 'SBD', '$', 'Cent', 100, 0, '$', '.', ',', 90, NULL, NULL),
(122, 'Seychellois Rupee', 100, 'SCR', '₨', 'Cent', 100, 0, '&#x20A8;', '.', ',', 690, NULL, NULL),
(123, 'Sudanese Pound', 100, 'SDG', '£', 'Piastre', 100, 1, '', '.', ',', 938, NULL, NULL),
(124, 'Swedish Krona', 100, 'SEK', 'kr', 'Öre', 100, 0, '', ',', '', 752, NULL, NULL),
(125, 'Singapore Dollar', 100, 'SGD', '$', 'Cent', 100, 1, '$', '.', ',', 702, NULL, NULL),
(126, 'Saint Helenian Pound', 100, 'SHP', '£', 'Penny', 100, 0, '&#x00A3;', '.', ',', 654, NULL, NULL),
(127, 'Slovak Koruna', 100, 'SKK', 'Sk', 'Halier', 100, 1, '', '.', ',', 703, NULL, NULL),
(128, 'Sierra Leonean Leone', 100, 'SLL', 'Le', 'Cent', 100, 0, '', '.', ',', 694, NULL, NULL),
(129, 'Somali Shilling', 100, 'SOS', 'Sh', 'Cent', 100, 0, '', '.', ',', 706, NULL, NULL),
(130, 'Surinamese Dollar', 100, 'SRD', '$', 'Cent', 100, 0, '', '.', ',', 968, NULL, NULL),
(131, 'South Sudanese Pound', 100, 'SSP', '£', 'piaster', 100, 0, '&#x00A3;', '.', ',', 728, NULL, NULL),
(132, 'São Tomé and Príncipe Dobra', 100, 'STD', '\\DB', 'Cêntimo', 100, 0, '', '.', ',', 678, NULL, NULL),
(133, 'Salvadoran Colón', 100, 'SVC', '₡', 'Centavo', 100, 1, '&#x20A1;', '.', ',', 222, NULL, NULL),
(134, 'Syrian Pound', 100, 'SYP', '£S', 'Piastre', 100, 0, '&#x00A3;', '.', ',', 760, NULL, NULL),
(135, 'Swazi Lilangeni', 100, 'SZL', 'L', 'Cent', 100, 1, '', '.', ',', 748, NULL, NULL),
(136, 'Thai Baht', 100, 'THB', '฿', 'Satang', 100, 1, '&#x0E3F;', '.', ',', 764, NULL, NULL),
(137, 'Tajikistani Somoni', 100, 'TJS', 'ЅМ', 'Diram', 100, 0, '', '.', ',', 972, NULL, NULL),
(138, 'Turkmenistani Manat', 100, 'TMT', 'T', 'Tenge', 100, 0, '', '.', ',', 934, NULL, NULL),
(139, 'Tunisian Dinar', 100, 'TND', 'د.ت', 'Millime', 1000, 0, '', '.', ',', 788, NULL, NULL),
(140, 'Tongan Paʻanga', 100, 'TOP', 'T$', 'Seniti', 100, 1, '', '.', ',', 776, NULL, NULL),
(141, 'Turkish Lira', 100, 'TRY', 'TL', 'kuruş', 100, 0, '', ',', '.', 949, NULL, NULL),
(142, 'Trinidad and Tobago Dollar', 100, 'TTD', '$', 'Cent', 100, 0, '$', '.', ',', 780, NULL, NULL),
(143, 'New Taiwan Dollar', 100, 'TWD', '$', 'Cent', 100, 1, '$', '.', ',', 901, NULL, NULL),
(144, 'Tanzanian Shilling', 100, 'TZS', 'Sh', 'Cent', 100, 1, '', '.', ',', 834, NULL, NULL),
(145, 'Ukrainian Hryvnia', 100, 'UAH', '₴', 'Kopiyka', 100, 0, '&#x20B4;', '.', ',', 980, NULL, NULL),
(146, 'Ugandan Shilling', 100, 'UGX', 'USh', 'Cent', 100, 0, '', '.', ',', 800, NULL, NULL),
(147, 'United States Dollar', 1, 'USD', '$', 'Cent', 100, 1, '$', '.', ',', 840, NULL, NULL),
(148, 'Uruguayan Peso', 100, 'UYU', '$', 'Centésimo', 100, 1, '&#x20B1;', ',', '.', 858, NULL, NULL),
(149, 'Uzbekistani Som', 100, 'UZS', 'null', 'Tiyin', 100, 0, '', '.', ',', 860, NULL, NULL),
(150, 'Venezuelan Bolívar', 100, 'VEF', 'Bs F', 'Céntimo', 100, 1, '', ',', '.', 937, NULL, NULL),
(151, 'Vietnamese Đồng', 100, 'VND', '₫', 'Hào', 10, 1, '&#x20AB;', ',', '.', 704, NULL, NULL),
(152, 'Vanuatu Vatu', 100, 'VUV', 'Vt', 'null', 1, 1, '', '.', ',', 548, NULL, NULL),
(153, 'Samoan Tala', 100, 'WST', 'T', 'Sene', 100, 0, '', '.', ',', 882, NULL, NULL),
(154, 'Central African Cfa Franc', 100, 'XAF', 'Fr', 'Centime', 100, 0, '', '.', ',', 950, NULL, NULL),
(155, 'Silver (Troy Ounce)', 100, 'XAG', 'oz t', 'oz', 1, 0, '', '.', ',', 961, NULL, NULL),
(156, 'Gold (Troy Ounce)', 100, 'XAU', 'oz t', 'oz', 1, 0, '', '.', ',', 959, NULL, NULL),
(157, 'East Caribbean Dollar', 100, 'XCD', '$', 'Cent', 100, 1, '$', '.', ',', 951, NULL, NULL),
(158, 'Special Drawing Rights', 100, 'XDR', 'SDR', '', 1, 0, '$', '.', ',', 960, NULL, NULL),
(159, 'West African Cfa Franc', 100, 'XOF', 'Fr', 'Centime', 100, 0, '', '.', ',', 952, NULL, NULL),
(160, 'Cfp Franc', 100, 'XPF', 'Fr', 'Centime', 100, 0, '', '.', ',', 953, NULL, NULL),
(161, 'Yemeni Rial', 100, 'YER', '﷼', 'Fils', 100, 0, '&#xFDFC;', '.', ',', 886, NULL, NULL),
(162, 'South African Rand', 100, 'ZAR', 'R', 'Cent', 100, 1, '&#x0052;', '.', ',', 710, NULL, NULL),
(163, 'Zambian Kwacha', 100, 'ZMK', 'ZK', 'Ngwee', 100, 0, '', '.', ',', 894, NULL, NULL),
(164, 'Zambian Kwacha', 100, 'ZMW', 'ZK', 'Ngwee', 100, 0, '', '.', ',', 967, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_slots`
--

CREATE TABLE `delivery_slots` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 for enabled, 1 for disabled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slot_interval` int DEFAULT NULL,
  `parent_id` int DEFAULT '0',
  `cutOff_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_slots_product`
--

CREATE TABLE `delivery_slots_product` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_slot_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `dispatcher_status_options`
--

CREATE TABLE `dispatcher_status_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '1 - for order, 2 - fordispatch',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '1 - active, 0 - inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dispatcher_status_options`
--

INSERT INTO `dispatcher_status_options` (`id`, `title`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Created', 1, 1, '2023-06-15 06:57:21', '2023-06-15 06:57:21'),
(2, 'Assigned', 1, 1, '2023-06-15 06:57:21', '2023-06-15 06:57:21'),
(3, 'Started', 1, 1, '2023-06-15 06:57:21', '2023-06-15 06:57:21'),
(4, 'Arrived', 1, 1, '2023-06-15 06:57:21', '2023-06-15 06:57:21'),
(5, 'Completed', 1, 1, '2023-06-15 06:57:21', '2023-06-15 06:57:21'),
(6, 'Rejected', 1, 1, '2023-06-15 06:57:21', '2023-06-15 06:57:21');

-- --------------------------------------------------------

--
-- Table structure for table `dispatcher_template_type_options`
--

CREATE TABLE `dispatcher_template_type_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 for inactive and 1 for active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `dispatcher_warning_pages`
--

CREATE TABLE `dispatcher_warning_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 for inactive and 1 for active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `driver_registration_documents`
--

CREATE TABLE `driver_registration_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_registration_document_translations`
--

CREATE TABLE `driver_registration_document_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `driver_registration_document_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `tags` mediumtext COLLATE utf8mb4_unicode_ci,
  `label` mediumtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `subject` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `slug`, `tags`, `label`, `content`, `subject`, `created_at`, `updated_at`) VALUES
(1, 'new-vendor-signup', '{vendor_name}, {title}, {description}, {email}, {phone_no}, {address},{website}', 'New Vendor Signup', '<tbody><tr><td><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Name</h4><p>{vendor_name}</p></div><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Title</h4><p>{title}</p></div><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Description</h4><p>{description}</p></div><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Email</h4><p>{email}</p></div><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Phone Number</h4><p>{phone_no}</p></div><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Address</h4><address style=\"font-style: normal;\"><p style=\"width: 300px;\">{address}</p></address></div><div style=\"margin-bottom: 20px;\"><h4 style=\"margin-bottom: 5px;\">Website</h4><a style=\"color: #8142ff;\" href=\"{website}\" target=\"_blank\"><b>{website}</b></a></div></td></tr></tbody>', 'New Vendor Signup', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(2, 'verify-mail', '{customer_name}, {code}', 'Verify Mail', '<tbody style=\"text-align: center;\"><tr><td style=\"padding-top: 0;\"><div style=\"background: #fff;box-shadow: 0 3px 4px #ddd;border-bottom-left-radius: 20px;border-bottom-right-radius: 20px;padding: 15px 40px 30px;\"><b style=\"margin-bottom: 10px; display: block;\">Hi {customer_name},</b><p>You can also verify manually by entering the following OTP</p><div style=\"padding:10px;border: 2px dashed #cb202d;word-break:keep-all!important;width: calc(100% - 40px);margin: 25px auto;\"><p style=\"Margin:0;Margin-bottom:16px;color:#cb202d;font-family:-apple-system,Helvetica,Arial,sans-serif;font-size:20px;font-weight:600;line-height:1.5;margin:0;margin-bottom:0;padding:0;text-align:center;word-break:keep-all!important\">{code}</p></div><p>Note: The OTP will expire in 10 minutes and can only be used once.</p></div></td></tr></tbody>', 'Verify Mail', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(3, 'forgot-password', '{reset_link}', 'Forgot Password', '<tbody><tr><td><table style=\"background-color: #f2f3f8; max-width:670px; margin:0 auto;\" width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\"><tr><td style=\"height:20px;\">&nbsp;</td></tr><tr><td style=\"padding:0 35px;\"> <h1 style=\"color:rgb(51,51,51);font-weight:500;line-height:27px;font-size:21px\">You have requested to reset your password</h1><span style=\"display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;\"></span><p style=\"color:#455056; font-size:15px;line-height:24px; margin:0;\"> We cannot simply send you your old password. A unique link to reset your password has been generated for you. To reset your password, click the following link and follow the instructions. </p> <a href=\"{reset_link}\" style=\"display: inline-block; padding: 6.7px 29px;border-radius: 4px;background:#8142ff;line-height: 20px; text-transform: uppercase;font-size: 14px;font-weight: 700;text-decoration: none;color: #fff;margin-top: 35px;\">Reset Password</a></td></tr><tr><td style=\"height:20px;\">&nbsp;</td></tr></table></td><tr><td style=\"height:20px;\">&nbsp;</td></tr></table></td></tr></tbody>', 'Reset Password Notification', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(4, 'refund', '{product_image}, {product_name}, {price}', 'Refund', '<tbody><tr><td><table style=\"width:100%;border: 1px solid rgb(221 221 221 / 41%);\"> <thead> <tr> <th style=\"border-bottom: 1px solid rgb(221 221 221 / 41%);\"><h3 style=\"color:rgb(51,51,51);font-weight:bold;line-height:27px;font-size:21px\">Refund Confirmation</h3> </th> </tr> </thead> <tbody> <tr><td><b><span style=\"font-size:16px;line-height:21px\"> Hello Share, </span> </b> <p style=\"margin:1px 0px 8px 0px;font-size:14px;line-height:18px;color:rgb(17,17,17)\"> Lorem ipsum dolor sit amet consectetur, adipisicing elit. Totam sed vitae fugiat nam, ut natus officia optio a suscipit molestiae earum magni, voluptatum debitis repellat magnam. Officiis odit qui, provident doloremque dicta modi voluptatum placeat. </p></td></tr><tr><td><p style=\"margin:1px 0px 8px 0px;font-size:14px;line-height:18px;color:rgb(17,17,17)\"> You can find the list of possible reasons why the package is being returned to us as undelivered <a href=\"#\"><span style=\"color:#0066c0\">here</span></a>. If you still want the item, please check your address and place a new order. </p> </td> </tr> <tr> <td> <a style=\"display: inline-block; padding: 6.7px 29px;border-radius: 4px;background:#8142ff;line-height: 20px; text-transform: uppercase;font-size: 14px;font-weight: 700;text-decoration: none;color: #fff;\" href=\"#\"> View return &amp; refund status </a> </td> </tr> <tr> <td> <div style=\"padding: 10px;border: 1px solid rgb(221 221 221 / 41%);margin-top: 15px;\"> <ul style=\"display: flex;align-items: center;\"> <li style=\"width: 80px;height: 80px;margin-right: 30px;\"> <img src=\"{product_image}\" alt=\"\" style=\"width: 100%;height: 100%;object-fit: cover;border-radius: 4px;\"> </li> <li> <a href=\"#\"><b>{product_name}</b></a> </li> </ul> <hr style=\"border:0; border-bottom: 1px solid rgb(221 221 221 / 41%);margin: 15px 0 20px;\"> <p align=\"right\" style=\"margin:1px 0px 8px 0px;font-size:14px;line-height:18px;font-family:&quot;Roboto&quot;,&quot;Helvetica Neue&quot;,Helvetica,Arial,sans-serif;color:rgb(17,17,17)\"><b> <span style=\"font-size:16px\"> Refund total: <span style=\"font-size:16px\">${price}* </span> </span> </b><br> <span style=\"display:inline-block;text-align:left\"> Refund of ${price} is now initiated. </span> </p></div></td> </tr> <tr> <td> <table id=\"m_-2085618623145965177legalCopy\" style=\"margin:0px 0px 0px 0px;font-weight:400;font-style:normal;font-size:13px;color:rgb(170,170,170);line-height:16px\"> <tbody> <tr> <td><p style=\"font-size:13px;color:rgb(102,102,102);line-height:16px;margin:0\"> * Learn more <a href=\"#\"><span style=\"color:#0066c0\">about refunds</span></a> </p></td> </tr> <tr> <td><p style=\"font-size:13px;color:rgb(102,102,102);line-height:16px;margin:0\"> This email was sent from a notification-only address that cannot accept incoming email. Please do not reply to this message. </p></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody>', 'Refund', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(5, 'orders', '{customer_name}, {description}, {products}, {order_id}, {address}', 'Orders', '<table style=\"width: 100%;background-color: #fff;padding: 50px 0 0;\">\n                <thead>\n                    <tr>\n                       <th colspan=\"2\" style=\"text-align: center;\">\n                         <h1 style=\"color: rgba(0,0,0,0.66);font-family: &quot;Times New Roman&quot;;font-size: 28px;font-weight: bold;letter-spacing: 0;line-height: 32px;\">Thanks for your order</h1>\n                         <p style=\"color: rgba(0,0,0,0.66);font-size: 15px;letter-spacing: 0;line-height: 25px;width: 80%;margin: 30px auto 10px;\"><span style=\"display: block;\">Hi {customer_name},</span> we have received your order and we working on it now.\n                          We will email you an update as soon as your order is processed.</p>\n                          </th>\n                       </tr>\n                 </thead>\n                <tbody>\n                    <tr>\n                        <td colspan=\"2\" style=\"padding-left: 0;padding-right: 0;\">\n                            <table style=\"width:100%; border: 1px solid rgb(221 221 221 / 41%);\">\n                                <tbody>\n                                      <tr>\n                                        <td colspan=\"2\" style=\"padding: 0;\">\n                                            <table style=\"width:100%;\">\n                                                <tbody> {products} </tbody>\n                                            </table>\n                                        </td>\n                                    </tr>\n                                </tbody>\n                            </table>\n                        </td>\n                    </tr>\n                </tbody>\n            </table>', 'Orders', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(6, 'successemail', '{name}', 'SuccessEmail', '<table style=\"width: 100%; background-color:#fff;\"> <thead> <tr> <th colspan=\"2\" style=\"text-align: center;\"> <a style=\"display: block;margin-bottom: 10px;\" href=\"#\"><img src=\"images/logo.png\" alt=\"\"> </a> <h1 style=\"margin: 0 0 10px;font-weight:400;\">Thanks for your order</h1> <p style=\"margin: 0 0 20px;font-weight:300;\">Hi {name}, <br> Payment done successfully. </p> <a style=\"display: inline-block; padding: 6.7px 29px;border-radius: 4px;background:#8142ff;line-height: 20px; text-transform: uppercase;font-size: 14px;font-weight: 700;text-decoration: none;color: #fff;\" href=\"#\">View your order</a> </th> </tr> </thead> <tbody> <tr> <td colspan=\"2\"> <table style=\"width:100%; border: 1px solid rgb(221 221 221 / 41%);\"> <thead> <tr> <th colspan=\"2\" style=\"border-bottom: 1px solid rgb(221 221 221 / 41%);\"> <h3 style=\"font-weight: 700;\">Items Ordered</h3> </th> </tr> </thead> <tbody> <tr style=\"vertical-align: top;\"> <td style=\"border-bottom: 1px solid rgb(221 221 221 / 41%);border-right: 1px solid rgb(221 221 221 / 41%);width: 50%;\"> <p style=\"margin-bottom: 5px;\"><b></b></p> <p></p> </td> </tr> <tr> <td colspan=\"2\" style=\"padding: 0;\"> <table style=\"width:100%;\"> <tbody>  </tbody> <tfoot> <tr> <td colspan=\"2\" style=\"background-color: #8142ff;color: #fff; border-top: 1px solid rgb(221 221 221 / 41%);text-align: center;\"> <b>Powered By Royo</b> </td> </tr> </tfoot> </table> </td> </tr> </tbody> </table> </td> </tr> </tbody></table>', 'Success Email Notification', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(7, 'failemail', '{name}', 'FailEmail', '<table style=\"width: 100%; background-color:#fff;\"> <thead> <tr> <th colspan=\"2\" style=\"text-align: center;\"> <a style=\"display: block;margin-bottom: 10px;\" href=\"#\"><img src=\"images/logo.png\" alt=\"\"> </a> <h1 style=\"margin: 0 0 10px;font-weight:400;\"></h1> <p style=\"margin: 0 0 20px;font-weight:300;\">Hi {name}, <br> Payment failed. </p> <a style=\"display: inline-block; padding: 6.7px 29px;border-radius: 4px;background:#8142ff;line-height: 20px; text-transform: uppercase;font-size: 14px;font-weight: 700;text-decoration: none;color: #fff;\" href=\"#\">View your order</a> </th> </tr> </thead> <tbody> <tr> <td colspan=\"2\"> <table style=\"width:100%; border: 1px solid rgb(221 221 221 / 41%);\"> <thead> <tr> <th colspan=\"2\" style=\"border-bottom: 1px solid rgb(221 221 221 / 41%);\"> <h3 style=\"font-weight: 700;\">Items Ordered</h3> </th> </tr> </thead> <tbody> <tr style=\"vertical-align: top;\"> <td style=\"border-bottom: 1px solid rgb(221 221 221 / 41%);border-right: 1px solid rgb(221 221 221 / 41%);width: 50%;\"> <p style=\"margin-bottom: 5px;\"><b></b></p> <p></p> </td> </tr> <tr> <td colspan=\"2\" style=\"padding: 0;\"> <table style=\"width:100%;\"> <tbody>  </tbody> <tfoot> <tr> <td colspan=\"2\" style=\"background-color: #8142ff;color: #fff; border-top: 1px solid rgb(221 221 221 / 41%);text-align: center;\"> <b>Powered By Royo</b> </td> </tr> </tfoot> </table> </td> </tr> </tbody> </table> </td> </tr> </tbody></table>', 'Failure Email Notification', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(8, 'sendreferralcode', '{customer_name},{customer_name}', 'SendReferralCode', '<tbody style=\"text-align: center;\"><tr><td style=\"padding-top: 0;\"><div style=\"background: #fff;box-shadow: 0 3px 4px #ddd;border-bottom-left-radius: 20px;border-bottom-right-radius: 20px;padding: 15px 40px 30px;\"><b style=\"margin-bottom: 10px; display: block;\">Hi {customer_name},</b><p>You can verify manually by entering the following referral code:</p><div style=\"padding:10px;border: 2px dashed #cb202d;word-break:keep-all!important;width: calc(100% - 40px);margin: 25px auto;\"><p style=\"Margin:0;Margin-bottom:16px;color:#cb202d;font-family:-apple-system,Helvetica,Arial,sans-serif;font-size:20px;font-weight:600;line-height:1.5;margin:0;margin-bottom:0;padding:0;text-align:center;word-break:keep-all!important\">{code}</p></div></td></tr></tbody>', 'Referral Code Email', '2022-09-01 05:55:15', '2022-09-01 05:55:15'),
(9, 'newcustomersignup', '{name},{client_name}', 'NewCustomerSignup', '<table style=\"width: 100%; background-color:#fff;\"> <thead> <tr> <th colspan=\"2\" style=\"text-align: center;\"> <a style=\"display: block;margin-bottom: 10px;\" href=\"#\"><img src=\"images/logo.png\" alt=\"\"> </a> <h3 style=\"margin: 0 0 10px;font-weight:400;\">Hi {name}</h1> <p style=\"margin: 0 0 20px;font-weight:300;\">Thanks for signing up with {client_name}. We are delighted to serve you. </p> </th> </tr> </thead>  <tr> <td colspan=\"2\" style=\"padding: 0;\"> <table style=\"width:100%;\"> <tbody>  </tbody> <tfoot> <tr> <td colspan=\"2\" style=\"background-color: #8142ff;color: #fff; border-top: 1px solid rgb(221 221 221 / 41%);text-align: center;\"> <b>Powered By Royo</b> </td> </tr> </tfoot> </table> </td> </tr> </tbody> </table> </td> </tr> </tbody></table>', 'Signup Notification', '2023-05-17 11:18:14', '2023-05-17 11:18:14'),
(10, 'giftcard', '{GiftCard},{customer_name},{sender_name},{gift_amount}', 'GiftCard', '<tbody><tr><td><table style=\"background-color: #f2f3f8; max-width:670px; margin:0 auto;\" width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);\"><tr><td style=\"height:20px;\">&nbsp;</td></tr><tr><td style=\"padding:0 35px;\"> <h1 style=\"color:rgb(51,51,51);font-weight:500;line-height:27px;font-size:21px\">Hello {customer_name}</h1><span style=\"display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;\"></span><p style=\"color:#455056; font-size:15px;line-height:24px; margin:0;\"> A gift from {sender_name}: for an amount of {gift_amount} has been presented to you.<br>\n                {GiftCard}</p></td></tr><tr><td style=\"height:20px;\">&nbsp;</td></tr></table></td><tr><td style=\"height:20px;\">&nbsp;</td></tr></table></td></tr></tbody>', 'Gift Card', '2023-05-17 11:18:14', '2023-05-17 11:18:14');

-- --------------------------------------------------------

--
-- Table structure for table `estimated_products`
--

CREATE TABLE `estimated_products` (
  `id` bigint UNSIGNED NOT NULL,
  `estimated_cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `estimated_product_addons`
--

CREATE TABLE `estimated_product_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `estimated_product_id` bigint UNSIGNED NOT NULL,
  `estimated_addon_id` bigint UNSIGNED NOT NULL,
  `estimated_addon_option_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `estimated_product_addon_new`
--

CREATE TABLE `estimated_product_addon_new` (
  `id` bigint UNSIGNED NOT NULL,
  `estimated_product_id` bigint UNSIGNED NOT NULL,
  `estimated_addon_id` bigint UNSIGNED NOT NULL,
  `estimated_addon_option_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `estimated_product_carts`
--

CREATE TABLE `estimated_product_carts` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `item_count` int DEFAULT NULL,
  `currency_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `estimated_product_cart_new`
--

CREATE TABLE `estimated_product_cart_new` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `item_count` int DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimated_product_new`
--

CREATE TABLE `estimated_product_new` (
  `id` bigint UNSIGNED NOT NULL,
  `estimated_cart_id` int NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_addon_options`
--

CREATE TABLE `estimate_addon_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimate_addon_id` bigint UNSIGNED NOT NULL,
  `position` smallint NOT NULL DEFAULT '1',
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_addon_option_translations`
--

CREATE TABLE `estimate_addon_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimate_addon_opt_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_addon_sets`
--

CREATE TABLE `estimate_addon_sets` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_select` tinyint NOT NULL DEFAULT '1',
  `max_select` tinyint NOT NULL DEFAULT '1',
  `position` smallint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `is_core` tinyint NOT NULL DEFAULT '1' COMMENT '0 - no, 1 - yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_addon_set_translations`
--

CREATE TABLE `estimate_addon_set_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimate_addon_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_products`
--

CREATE TABLE `estimate_products` (
  `id` bigint UNSIGNED NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_product_addons`
--

CREATE TABLE `estimate_product_addons` (
  `estimate_product_id` bigint UNSIGNED DEFAULT NULL,
  `estimate_addon_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_product_translations`
--

CREATE TABLE `estimate_product_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `language_id` bigint UNSIGNED NOT NULL,
  `estimate_product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price` decimal(16,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exchange_reasons`
--

CREATE TABLE `exchange_reasons` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','Block') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `order` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilties`
--

CREATE TABLE `facilties` (
  `id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilty_translations`
--

CREATE TABLE `facilty_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `facilties_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq_translations`
--

CREATE TABLE `faq_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `page_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0',
  `order_by` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gift_cards`
--

CREATE TABLE `gift_cards` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) UNSIGNED DEFAULT NULL,
  `expiry_date` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint DEFAULT '0' COMMENT '0- No, 1- yes',
  `added_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `home_page_labels`
--

CREATE TABLE `home_page_labels` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT '0-No, 1-Yes',
  `order_by` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `home_page_labels`
--

INSERT INTO `home_page_labels` (`id`, `title`, `slug`, `is_active`, `order_by`, `created_at`, `updated_at`) VALUES
(1, 'Featured Products', 'featured_products', 1, 2, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(2, 'Vendors', 'vendors', 1, 1, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(3, 'New Products', 'new_products', 1, 3, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(4, 'On Sale', 'on_sale', 1, 4, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(5, 'Brands', 'brands', 1, 6, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(6, 'Best Sellers', 'best_sellers', 1, 5, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(7, 'Pickup Delivery', 'pickup_delivery', 0, 7, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(8, 'Dynamic HTML', 'dynamic_page', 1, 8, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(9, 'Trending Vendors', 'trending_vendors', 1, 9, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(10, 'Recent Orders', 'recent_orders', 1, 10, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(11, 'Cities', 'cities', 1, 11, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(12, 'Long Term Service', 'long_term_service', 1, 12, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(13, 'Recently Viewed', 'recently_viewed', 1, 12, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(14, 'Spotlight Deals', 'spotlight_deals', 1, 13, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(15, 'Top Rated', 'top_rated', 1, 14, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(16, 'NavCategories', 'nav_categories', 1, 15, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(17, 'Single Category Products', 'single_category_products', 1, 16, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(18, 'Selected Products', 'selected_products', 1, 16, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(19, 'Most Popular Products', 'most_popular_products', 1, 17, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(20, 'Banner', 'banner', 1, 18, '2023-04-27 12:13:01', '2023-04-27 12:13:01'),
(21, 'Ordered Products', 'ordered_products', 1, 19, '2023-04-27 12:13:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `home_page_label_transaltions`
--

CREATE TABLE `home_page_label_transaltions` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_page_label_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `home_page_label_transaltions`
--

INSERT INTO `home_page_label_transaltions` (`id`, `title`, `home_page_label_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(2, NULL, 1, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(3, NULL, 2, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(4, NULL, 2, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(5, NULL, 3, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(6, NULL, 3, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(7, NULL, 4, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(8, NULL, 5, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(9, NULL, 4, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(10, NULL, 6, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(11, NULL, 6, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(12, NULL, 7, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24'),
(13, NULL, 7, 1, '2021-09-17 05:51:24', '2021-09-17 05:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `home_products`
--

CREATE TABLE `home_products` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `layout_id` int DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint DEFAULT NULL,
  `products` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '0 - Web, 1 - App'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_categories`
--

CREATE TABLE `influencer_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT '0=no active, 1=active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kyc` tinyint NOT NULL DEFAULT '0' COMMENT '1=yes, 0=No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_initial_orders_discounts`
--

CREATE TABLE `influencer_initial_orders_discounts` (
  `id` bigint UNSIGNED NOT NULL,
  `influencer_user_id` bigint UNSIGNED DEFAULT NULL,
  `order_count` int DEFAULT NULL COMMENT 'number of first orders for discount',
  `commision_type` tinyint DEFAULT NULL COMMENT '1=Percentage, 2=fixed',
  `commision` int DEFAULT NULL COMMENT 'commision percentage or amount',
  `date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_kyc`
--

CREATE TABLE `influencer_kyc` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ifsc_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adhar_front` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adhar_back` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adhar_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upi_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_social_account_details`
--

CREATE TABLE `influencer_social_account_details` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `social_media_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `followers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_reach` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_tiers`
--

CREATE TABLE `influencer_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` int DEFAULT NULL,
  `commision_type` tinyint DEFAULT NULL COMMENT '1=Percentage, 2=fixed',
  `commision` int DEFAULT NULL COMMENT 'commision percentage or amount',
  `status` tinyint DEFAULT NULL COMMENT '1=active, 0=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_users`
--

CREATE TABLE `influencer_users` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `influencer_tier_id` bigint UNSIGNED DEFAULT NULL,
  `commision_type` tinyint DEFAULT NULL COMMENT '1=Percentage, 2=fixed',
  `commision` int DEFAULT NULL COMMENT 'commision percentage or amount',
  `reffered_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influ_attributes`
--

CREATE TABLE `influ_attributes` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 for dropdown, 2 for color',
  `position` smallint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influ_attr_cat`
--

CREATE TABLE `influ_attr_cat` (
  `id` bigint UNSIGNED NOT NULL,
  `attribute_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influ_attr_opt`
--

CREATE TABLE `influ_attr_opt` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `hexacode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` smallint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influ_attr_opt_trans`
--

CREATE TABLE `influ_attr_opt_trans` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_option_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `influ_attr_trans`
--

CREATE TABLE `influ_attr_trans` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint UNSIGNED NOT NULL,
  `sort_code` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nativeName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `sort_code`, `name`, `nativeName`, `created_at`, `updated_at`) VALUES
(1, 'en', 'English', 'English', NULL, NULL),
(2, 'ab', 'Abkhaz', 'аҧсуа', NULL, NULL),
(3, 'aa', 'Afar', 'Afaraf', NULL, NULL),
(4, 'af', 'Afrikaans', 'Afrikaans', NULL, NULL),
(5, 'ak', 'Akan', 'Akan', NULL, NULL),
(6, 'sq', 'Albanian', 'Shqip', NULL, NULL),
(7, 'am', 'Amharic', 'አማርኛ', NULL, NULL),
(8, 'ar', 'Arabic', 'العربية', NULL, NULL),
(9, 'an', 'Aragonese', 'Aragonés', NULL, NULL),
(10, 'hy', 'Armenian', 'Հայերեն', NULL, NULL),
(11, 'as', 'Assamese', 'অসমীয়া', NULL, NULL),
(12, 'av', 'Avaric', 'авар мацӀ, магӀарул мацӀ', NULL, NULL),
(13, 'ae', 'Avestan', 'avesta', NULL, NULL),
(14, 'ay', 'Aymara', 'aymar aru', NULL, NULL),
(15, 'az', 'Azerbaijani', 'azərbaycan dili', NULL, NULL),
(16, 'bm', 'Bambara', 'bamanankan', NULL, NULL),
(17, 'ba', 'Bashkir', 'башҡорт теле', NULL, NULL),
(18, 'eu', 'Basque', 'euskara, euskera', NULL, NULL),
(19, 'be', 'Belarusian', 'Беларуская', NULL, NULL),
(20, 'bn', 'Bengali', 'বাংলা', NULL, NULL),
(21, 'bh', 'Bihari', 'भोजपुरी', NULL, NULL),
(22, 'bi', 'Bislama', 'Bislama', NULL, NULL),
(23, 'bs', 'Bosnian', 'bosanski jezik', NULL, NULL),
(24, 'br', 'Breton', 'brezhoneg', NULL, NULL),
(25, 'bg', 'Bulgarian', 'български език', NULL, NULL),
(26, 'my', 'Burmese', 'ဗမာစာ', NULL, NULL),
(27, 'ca', 'Catalan; Valencian', 'Català', NULL, NULL),
(28, 'ch', 'Chamorro', 'Chamoru', NULL, NULL),
(29, 'ce', 'Chechen', 'нохчийн мотт', NULL, NULL),
(30, 'ny', 'Chichewa; Chewa; Nyanja', 'chiCheŵa, chinyanja', NULL, NULL),
(31, 'zh', 'Chinese', '中文 (Zhōngwén), 汉语, 漢語', NULL, NULL),
(32, 'cv', 'Chuvash', 'чӑваш чӗлхи', NULL, NULL),
(33, 'kw', 'Cornish', 'Kernewek', NULL, NULL),
(34, 'co', 'Corsican', 'corsu, lingua corsa', NULL, NULL),
(35, 'cr', 'Cree', 'ᓀᐦᐃᔭᐍᐏᐣ', NULL, NULL),
(36, 'hr', 'Croatian', 'hrvatski', NULL, NULL),
(37, 'cs', 'Czech', 'česky, čeština', NULL, NULL),
(38, 'da', 'Danish', 'dansk', NULL, NULL),
(39, 'dv', 'Divehi; Dhivehi; Maldivian;', 'ދިވެހި', NULL, NULL),
(40, 'nl', 'Dutch', 'Nederlands, Vlaams', NULL, NULL),
(41, 'eo', 'Esperanto', 'Esperanto', NULL, NULL),
(42, 'et', 'Estonian', 'eesti, eesti keel', NULL, NULL),
(43, 'ee', 'Ewe', 'Eʋegbe', NULL, NULL),
(44, 'fo', 'Faroese', 'føroyskt', NULL, NULL),
(45, 'fj', 'Fijian', 'vosa Vakaviti', NULL, NULL),
(46, 'fi', 'Finnish', 'suomi, suomen kieli', NULL, NULL),
(47, 'fr', 'French', 'français, langue française', NULL, NULL),
(48, 'ff', 'Fula; Fulah; Pulaar; Pular', 'Fulfulde, Pulaar, Pular', NULL, NULL),
(49, 'gl', 'Galician', 'Galego', NULL, NULL),
(50, 'ka', 'Georgian', 'ქართული', NULL, NULL),
(51, 'de', 'German', 'Deutsch', NULL, NULL),
(52, 'el', 'Greek, Modern', 'Ελληνικά', NULL, NULL),
(53, 'gn', 'Guaraní', 'Avañeẽ', NULL, NULL),
(54, 'gu', 'Gujarati', 'ગુજરાતી', NULL, NULL),
(55, 'ht', 'Haitian; Haitian Creole', 'Kreyòl ayisyen', NULL, NULL),
(56, 'ha', 'Hausa', 'Hausa, هَوُسَ', NULL, NULL),
(57, 'he', 'Hebrew (modern)', 'עברית', NULL, NULL),
(58, 'hz', 'Herero', 'Otjiherero', NULL, NULL),
(59, 'hi', 'Hindi', 'हिन्दी, हिंदी', NULL, NULL),
(60, 'ho', 'Hiri Motu', 'Hiri Motu', NULL, NULL),
(61, 'hu', 'Hungarian', 'Magyar', NULL, NULL),
(62, 'ia', 'Interlingua', 'Interlingua', NULL, NULL),
(63, 'id', 'Indonesian', 'Bahasa Indonesia', NULL, NULL),
(64, 'ie', 'Interlingue', 'Originally called Occidental; then Interlingue after WWII', NULL, NULL),
(65, 'ga', 'Irish', 'Gaeilge', NULL, NULL),
(66, 'ig', 'Igbo', 'Asụsụ Igbo', NULL, NULL),
(67, 'ik', 'Inupiaq', 'Iñupiaq, Iñupiatun', NULL, NULL),
(68, 'io', 'Ido', 'Ido', NULL, NULL),
(69, 'is', 'Icelandic', 'Íslenska', NULL, NULL),
(70, 'it', 'Italian', 'Italiano', NULL, NULL),
(71, 'iu', 'Inuktitut', 'ᐃᓄᒃᑎᑐᑦ', NULL, NULL),
(72, 'ja', 'Japanese', '日本語 (にほんご／にっぽんご)', NULL, NULL),
(73, 'jv', 'Javanese', 'basa Jawa', NULL, NULL),
(74, 'kl', 'Kalaallisut, Greenlandic', 'kalaallisut, kalaallit oqaasii', NULL, NULL),
(75, 'kn', 'Kannada', 'ಕನ್ನಡ', NULL, NULL),
(76, 'kr', 'Kanuri', 'Kanuri', NULL, NULL),
(77, 'ks', 'Kashmiri', 'कश्मीरी, كشميري‎', NULL, NULL),
(78, 'kk', 'Kazakh', 'Қазақ тілі', NULL, NULL),
(79, 'km', 'Khmer', 'ភាសាខ្មែរ', NULL, NULL),
(80, 'ki', 'Kikuyu, Gikuyu', 'Gĩkũyũ', NULL, NULL),
(81, 'rw', 'Kinyarwanda', 'Ikinyarwanda', NULL, NULL),
(82, 'ky', 'Kirghiz, Kyrgyz', 'кыргыз тили', NULL, NULL),
(83, 'kv', 'Komi', 'коми кыв', NULL, NULL),
(84, 'kg', 'Kongo', 'KiKongo', NULL, NULL),
(85, 'ko', 'Korean', '한국어 (韓國語), 조선말 (朝鮮語)', NULL, NULL),
(86, 'ku', 'Kurdish', 'Kurdî, كوردی‎', NULL, NULL),
(87, 'kj', 'Kwanyama, Kuanyama', 'Kuanyama', NULL, NULL),
(88, 'la', 'Latin', 'latine, lingua latina', NULL, NULL),
(89, 'lb', 'Luxembourgish, Letzeburgesch', 'Lëtzebuergesch', NULL, NULL),
(90, 'lg', 'Luganda', 'Luganda', NULL, NULL),
(91, 'li', 'Limburgish, Limburgan, Limburger', 'Limburgs', NULL, NULL),
(92, 'ln', 'Lingala', 'Lingála', NULL, NULL),
(93, 'lo', 'Lao', 'ພາສາລາວ', NULL, NULL),
(94, 'lt', 'Lithuanian', 'lietuvių kalba', NULL, NULL),
(95, 'lu', 'Luba-Katanga', '', NULL, NULL),
(96, 'lv', 'Latvian', 'latviešu valoda', NULL, NULL),
(97, 'gv', 'Manx', 'Gaelg, Gailck', NULL, NULL),
(98, 'mk', 'Macedonian', 'македонски јазик', NULL, NULL),
(99, 'mg', 'Malagasy', 'Malagasy fiteny', NULL, NULL),
(100, 'ms', 'Malay', 'bahasa Melayu, بهاس ملايو‎', NULL, NULL),
(101, 'ml', 'Malayalam', 'മലയാളം', NULL, NULL),
(102, 'mt', 'Maltese', 'Malti', NULL, NULL),
(103, 'mi', 'Māori', 'te reo Māori', NULL, NULL),
(104, 'mr', 'Marathi (Marāṭhī)', 'मराठी', NULL, NULL),
(105, 'mh', 'Marshallese', 'Kajin M̧ajeļ', NULL, NULL),
(106, 'mn', 'Mongolian', 'монгол', NULL, NULL),
(107, 'na', 'Nauru', 'Ekakairũ Naoero', NULL, NULL),
(108, 'nv', 'Navajo, Navaho', 'Diné bizaad, Dinékʼehǰí', NULL, NULL),
(109, 'nb', 'Norwegian Bokmål', 'Norsk bokmål', NULL, NULL),
(110, 'nd', 'North Ndebele', 'isiNdebele', NULL, NULL),
(111, 'ne', 'Nepali', 'नेपाली', NULL, NULL),
(112, 'ng', 'Ndonga', 'Owambo', NULL, NULL),
(113, 'nn', 'Norwegian Nynorsk', 'Norsk nynorsk', NULL, NULL),
(114, 'no', 'Norwegian', 'Norsk', NULL, NULL),
(115, 'ii', 'Nuosu', 'ꆈꌠ꒿ Nuosuhxop', NULL, NULL),
(116, 'nr', 'South Ndebele', 'isiNdebele', NULL, NULL),
(117, 'oc', 'Occitan', 'Occitan', NULL, NULL),
(118, 'oj', 'Ojibwe, Ojibwa', 'ᐊᓂᔑᓈᐯᒧᐎᓐ', NULL, NULL),
(119, 'cu', 'Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic', 'ѩзыкъ словѣньскъ', NULL, NULL),
(120, 'om', 'Oromo', 'Afaan Oromoo', NULL, NULL),
(121, 'or', 'Oriya', 'ଓଡ଼ିଆ', NULL, NULL),
(122, 'os', 'Ossetian, Ossetic', 'ирон æвзаг', NULL, NULL),
(123, 'pa', 'Panjabi, Punjabi', 'ਪੰਜਾਬੀ, پنجابی‎', NULL, NULL),
(124, 'pi', 'Pāli', 'पाऴि', NULL, NULL),
(125, 'fa', 'Persian', 'فارسی', NULL, NULL),
(126, 'pl', 'Polish', 'polski', NULL, NULL),
(127, 'ps', 'Pashto, Pushto', 'پښتو', NULL, NULL),
(128, 'pt', 'Portuguese', 'Português', NULL, NULL),
(129, 'qu', 'Quechua', 'Runa Simi, Kichwa', NULL, NULL),
(130, 'rm', 'Romansh', 'rumantsch grischun', NULL, NULL),
(131, 'rn', 'Kirundi', 'kiRundi', NULL, NULL),
(132, 'ro', 'Romanian, Moldavian, Moldovan', 'română', NULL, NULL),
(133, 'ru', 'Russian', 'русский язык', NULL, NULL),
(134, 'sa', 'Sanskrit (Saṁskṛta)', 'संस्कृतम्', NULL, NULL),
(135, 'sc', 'Sardinian', 'sardu', NULL, NULL),
(136, 'sd', 'Sindhi', 'सिन्धी, سنڌي، سندھی‎', NULL, NULL),
(137, 'se', 'Northern Sami', 'Davvisámegiella', NULL, NULL),
(138, 'sm', 'Samoan', 'gagana faa Samoa', NULL, NULL),
(139, 'sg', 'Sango', 'yângâ tî sängö', NULL, NULL),
(140, 'sr', 'Serbian', 'српски језик', NULL, NULL),
(141, 'gd', 'Scottish Gaelic; Gaelic', 'Gàidhlig', NULL, NULL),
(142, 'sn', 'Shona', 'chiShona', NULL, NULL),
(143, 'si', 'Sinhala, Sinhalese', 'සිංහල', NULL, NULL),
(144, 'sk', 'Slovak', 'slovenčina', NULL, NULL),
(145, 'sl', 'Slovene', 'slovenščina', NULL, NULL),
(146, 'so', 'Somali', 'Soomaaliga, af Soomaali', NULL, NULL),
(147, 'st', 'Southern Sotho', 'Sesotho', NULL, NULL),
(148, 'es', 'Spanish', 'español', NULL, NULL),
(149, 'su', 'Sundanese', 'Basa Sunda', NULL, NULL),
(150, 'sw', 'Swahili', 'Kiswahili', NULL, NULL),
(151, 'ss', 'Swati', 'SiSwati', NULL, NULL),
(152, 'sv', 'Swedish', 'svenska', NULL, NULL),
(153, 'ta', 'Tamil', 'தமிழ்', NULL, NULL),
(154, 'te', 'Telugu', 'తెలుగు', NULL, NULL),
(155, 'tg', 'Tajik', 'тоҷикӣ, toğikī, تاجیکی‎', NULL, NULL),
(156, 'th', 'Thai', 'ไทย', NULL, NULL),
(157, 'ti', 'Tigrinya', 'ትግርኛ', NULL, NULL),
(158, 'bo', 'Tibetan Standard, Tibetan, Central', 'བོད་ཡིག', NULL, NULL),
(159, 'tk', 'Turkmen', 'Türkmen, Түркмен', NULL, NULL),
(160, 'tl', 'Tagalog', 'Wikang Tagalog, ᜏᜒᜃᜅ᜔ ᜆᜄᜎᜓᜄ᜔', NULL, NULL),
(161, 'tn', 'Tswana', 'Setswana', NULL, NULL),
(162, 'to', 'Tonga (Tonga Islands)', 'faka Tonga', NULL, NULL),
(163, 'tr', 'Turkish', 'Türkçe', NULL, NULL),
(164, 'ts', 'Tsonga', 'Xitsonga', NULL, NULL),
(165, 'tt', 'Tatar', 'татарча, tatarça, تاتارچا‎', NULL, NULL),
(166, 'tw', 'Twi', 'Twi', NULL, NULL),
(167, 'ty', 'Tahitian', 'Reo Tahiti', NULL, NULL),
(168, 'ug', 'Uighur, Uyghur', 'Uyƣurqə, ئۇيغۇرچە‎', NULL, NULL),
(169, 'uk', 'Ukrainian', 'українська', NULL, NULL),
(170, 'ur', 'Urdu', 'اردو', NULL, NULL),
(171, 'uz', 'Uzbek', 'zbek, Ўзбек, أۇزبېك‎', NULL, NULL),
(172, 've', 'Venda', 'Tshivenḓa', NULL, NULL),
(173, 'vi', 'Vietnamese', 'Tiếng Việt', NULL, NULL),
(174, 'vo', 'Volapük', 'Volapük', NULL, NULL),
(175, 'wa', 'Walloon', 'Walon', NULL, NULL),
(176, 'cy', 'Welsh', 'Cymraeg', NULL, NULL),
(177, 'wo', 'Wolof', 'Wollof', NULL, NULL),
(178, 'fy', 'Western Frisian', 'Frysk', NULL, NULL),
(179, 'xh', 'Xhosa', 'isiXhosa', NULL, NULL),
(180, 'yi', 'Yiddish', 'ייִדיש', NULL, NULL),
(181, 'yo', 'Yoruba', 'Yorùbá', NULL, NULL),
(182, 'za', 'Zhuang, Chuang', 'Saɯ cueŋƅ, Saw cuengh', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `long_term_services`
--

CREATE TABLE `long_term_services` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `sku` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_period` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(16,8) DEFAULT NULL,
  `compare_at_price` decimal(16,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `long_term_service_periods`
--

CREATE TABLE `long_term_service_periods` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `service_period` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'day,week,month',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `long_term_service_products`
--

CREATE TABLE `long_term_service_products` (
  `id` bigint UNSIGNED NOT NULL,
  `long_term_service_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_variant` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `long_term_service_product_addons`
--

CREATE TABLE `long_term_service_product_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `long_term_service_product_id` bigint UNSIGNED DEFAULT NULL,
  `addon_id` bigint UNSIGNED DEFAULT NULL,
  `option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `long_term_service_translations`
--

CREATE TABLE `long_term_service_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `long_term_service_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_cards`
--

CREATE TABLE `loyalty_cards` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_points` int DEFAULT NULL,
  `per_order_minimum_amount` int DEFAULT NULL,
  `per_order_points` int DEFAULT NULL,
  `per_purchase_minimum_amount` int DEFAULT NULL,
  `amount_per_loyalty_point` int DEFAULT NULL,
  `redeem_points_per_primary_currency` int DEFAULT NULL,
  `status` enum('0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0-Active, 1-Deactive, 2-Deleted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `loyalty_check` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0-Active, 1-Deactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `luxury_options`
--

CREATE TABLE `luxury_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `luxury_options`
--

INSERT INTO `luxury_options` (`id`, `title`, `created_at`, `updated_at`) VALUES
(1, 'delivery', NULL, NULL),
(2, 'dine_in', NULL, NULL),
(3, 'takeaway', NULL, NULL),
(4, 'rental', NULL, NULL),
(5, 'pick_drop', NULL, NULL),
(6, 'on_demand', NULL, NULL),
(7, 'laundry', NULL, NULL),
(8, 'appointment', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `main_permissions`
--

CREATE TABLE `main_permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `controller` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `main_permissions`
--

INSERT INTO `main_permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `controller`) VALUES
(1, 'dashboard-view', 'web', NULL, NULL, 'DashBoardController'),
(2, 'dashboard-weekRevenue', 'web', NULL, NULL, 'DashBoardController'),
(3, 'dashboard-locationRevenue', 'web', NULL, NULL, 'DashBoardController'),
(4, 'dashboard-monthRevenue', 'web', NULL, NULL, 'DashBoardController'),
(5, 'dashboard-totalRevenue', 'web', NULL, NULL, 'DashBoardController'),
(6, 'order-view', 'web', NULL, NULL, 'OrderController'),
(7, 'order-accept', 'web', NULL, NULL, 'OrderController'),
(8, 'vendor-view', 'web', NULL, NULL, 'VendorController'),
(9, 'vendor-add', 'web', NULL, NULL, 'VendorController'),
(10, 'vendor-setting', 'web', NULL, NULL, 'VendorController'),
(11, 'vendor-catalog', 'web', NULL, NULL, 'VendorController'),
(12, 'vendor-config', 'web', NULL, NULL, 'VendorController'),
(13, 'vendor-categories', 'web', NULL, NULL, 'VendorController'),
(14, 'vendor-payout', 'web', NULL, NULL, 'VendorController'),
(15, 'vendor-add-users', 'web', NULL, NULL, 'VendorController'),
(16, 'accounting-view', 'web', NULL, NULL, 'AccountController'),
(17, 'accounting-orders', 'web', NULL, NULL, 'AccountController'),
(18, 'accounting-loyalty-cards', 'web', NULL, NULL, 'AccountController'),
(19, 'accounting-promo-codes', 'web', NULL, NULL, 'AccountController'),
(20, 'accounting-taxes', 'web', NULL, NULL, 'AccountController'),
(21, 'accounting-vendors', 'web', NULL, NULL, 'AccountController'),
(22, 'accounting-payout-request', 'web', NULL, NULL, 'AccountController'),
(23, 'accounting-order-refund', 'web', NULL, NULL, 'AccountController'),
(24, 'accounting-subscription-discount', 'web', NULL, NULL, 'AccountController'),
(25, 'subscription-customer-view', 'web', NULL, NULL, 'SubscriptionPlansUserController'),
(26, 'subscription-customer-add', 'web', NULL, NULL, 'SubscriptionPlansUserController'),
(27, 'subscription-vendor-view', 'web', NULL, NULL, 'SubscriptionPlansVendorController'),
(28, 'subscription-vendor-add', 'web', NULL, NULL, 'SubscriptionPlansVendorController'),
(29, 'customers-view', 'web', NULL, NULL, 'UserController'),
(30, 'customers-add', 'web', NULL, NULL, 'UserController'),
(31, 'user-add-role-permission', 'web', NULL, NULL, 'UserController'),
(32, 'review-view', 'web', NULL, NULL, 'ReviewController'),
(33, 'review-product-performance', 'web', NULL, NULL, 'ReportController'),
(34, 'setting-profile-view', 'web', NULL, NULL, 'UserController'),
(35, 'setting-profile-add', 'web', NULL, NULL, 'UserController'),
(36, 'setting-customize-view', 'web', NULL, NULL, 'ClientPreferenceController'),
(37, 'setting-customize-add', 'web', NULL, NULL, 'ClientPreferenceController'),
(38, 'setting-webstyle-view', 'web', NULL, NULL, 'WebStylingController'),
(39, 'setting-appstyle-view', 'web', NULL, NULL, 'AppStylingController'),
(40, 'cms-pages-view', 'web', NULL, NULL, 'PageController'),
(41, 'cms-email-view', 'web', NULL, NULL, 'EmailController'),
(42, 'cms-notification-view', 'web', NULL, NULL, 'NotificationController'),
(43, 'cms-sms-view', 'web', NULL, NULL, 'SmsController'),
(44, 'cms-reason-view', 'web', NULL, NULL, 'ReasonController'),
(45, 'category-view', 'web', NULL, NULL, 'CategoryController'),
(46, 'category-add', 'web', NULL, NULL, 'CategoryController'),
(47, 'variant-view', 'web', NULL, NULL, 'CategoryController'),
(48, 'variant-add', 'web', NULL, NULL, 'CategoryController'),
(49, 'brand-view', 'web', NULL, NULL, 'CategoryController'),
(50, 'brand-add', 'web', NULL, NULL, 'CategoryController'),
(51, 'tags-view', 'web', NULL, NULL, 'CategoryController'),
(52, 'tags-add', 'web', NULL, NULL, 'CategoryController'),
(53, 'configuration-view', 'web', NULL, NULL, 'ClientPreferenceController'),
(54, 'configuration-add', 'web', NULL, NULL, 'ClientPreferenceController'),
(55, 'tax-view', 'web', NULL, NULL, 'TaxController'),
(56, 'tax-add', 'web', NULL, NULL, 'TaxController'),
(57, 'payment-option-view', 'web', NULL, NULL, 'PaymentOptionController'),
(58, 'payment-option-add', 'web', NULL, NULL, 'PaymentOptionController'),
(59, 'delivery-option-view', 'web', NULL, NULL, 'DeliveryOptionController'),
(60, 'delivery-option-add', 'web', NULL, NULL, 'DeliveryOptionController'),
(61, 'banner-option-view', 'web', NULL, NULL, 'BannerController'),
(62, 'banner-option-add', 'web', NULL, NULL, 'BannerController'),
(63, 'promo-code-view', 'web', NULL, '2023-04-07 13:33:12', 'PromoCodeController'),
(64, 'promo-code-add', 'web', NULL, '2023-04-07 13:33:12', 'PromoCodeController'),
(65, 'loyalty-code-view', 'web', NULL, NULL, 'LoyaltyController'),
(66, 'loyalty-code-add', 'web', NULL, NULL, 'LoyaltyController'),
(67, 'campaign-code-view', 'web', NULL, NULL, 'CampaignController'),
(68, 'campaign-code-add', 'web', NULL, NULL, 'CampaignController'),
(69, 'inquiry-code-view', 'web', NULL, '2023-04-07 13:33:12', 'ProductInquiryController'),
(70, 'tool-view', 'web', NULL, NULL, 'ToolsController'),
(71, 'database-log-view', 'web', NULL, NULL, 'ToolsController'),
(72, 'role-permission', 'web', NULL, NULL, 'RolePermissionController'),
(73, 'vendor-subscription', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'VendorSubscriptionController'),
(74, 'vendor_config-view', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'VendorSlotController'),
(75, 'vendor_pincode-view', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'PincodeController'),
(76, 'vendor_pincode-add', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'PincodeController'),
(77, 'permission_product_draft-published-view', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'ProductController'),
(78, 'permission_product_draft-published-add', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'ProductController'),
(79, 'chat-view', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'ChatController'),
(80, 'accounting-tax-rate', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'TaxRateController'),
(81, 'seller-module', 'web', '2023-04-07 13:33:12', '2023-04-07 13:33:12', 'SellerController');

-- --------------------------------------------------------

--
-- Table structure for table `main_roles`
--

CREATE TABLE `main_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hierarchy_no` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `main_roles`
--

INSERT INTO `main_roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `hierarchy_no`) VALUES
(1, 'Super Admin', 'web', NULL, NULL, 0),
(2, 'Admin', 'web', NULL, NULL, 0),
(3, 'Buyer', 'web', NULL, NULL, 0),
(4, 'Vendor', 'web', NULL, '2023-04-07 13:57:20', 0),
(5, 'Manager', 'web', NULL, NULL, 0),
(6, 'User', 'web', '2023-04-07 13:57:20', '2023-04-07 13:57:20', 0);

-- --------------------------------------------------------

--
-- Table structure for table `map_providers`
--

CREATE TABLE `map_providers` (
  `id` bigint UNSIGNED NOT NULL,
  `provider` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyword` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT ' 0 for no, 1 for yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `map_providers`
--

INSERT INTO `map_providers` (`id`, `provider`, `keyword`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Google Map', 'google_map', 1, NULL, NULL),
(2, 'Map Box', 'map_box', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marg_products`
--

CREATE TABLE `marg_products` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `rid` int UNSIGNED DEFAULT NULL,
  `catcode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` text COLLATE utf8mb4_unicode_ci,
  `shopcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MRP` double DEFAULT NULL,
  `Rate` double DEFAULT NULL,
  `Deal` double DEFAULT NULL,
  `Free` double DEFAULT NULL,
  `PRate` double DEFAULT NULL,
  `Is_Deleted` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `curbatch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MargCode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Conversion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Salt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ENCODE` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Gcode6` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ProductCode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_admins_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2018_11_06_222923_create_transactions_table', 1),
(4, '2018_11_07_192923_create_transfers_table', 1),
(5, '2018_11_07_202152_update_transfers_table', 1),
(6, '2018_11_15_124230_create_wallets_table', 1),
(7, '2018_11_19_164609_update_transactions_table', 1),
(8, '2018_11_20_133759_add_fee_transfers_table', 1),
(9, '2018_11_22_131953_add_status_transfers_table', 1),
(10, '2018_11_22_133438_drop_refund_transfers_table', 1),
(11, '2019_05_13_111553_update_status_transfers_table', 1),
(12, '2019_06_25_103755_add_exchange_status_transfers_table', 1),
(13, '2019_07_29_184926_decimal_places_wallets_table', 1),
(14, '2019_08_19_000000_create_failed_jobs_table', 1),
(15, '2019_09_17_051112_create_api_logs_table', 1),
(16, '2019_10_02_193759_add_discount_transfers_table', 1),
(17, '2019_10_13_000000_create_social_credentials_table', 1),
(18, '2020_01_01_000001_create_plans_table', 1),
(19, '2020_01_01_000002_create_plan_features_table', 1),
(20, '2020_01_01_000003_create_plan_subscriptions_table', 1),
(21, '2020_01_01_000004_create_plan_subscription_usage_table', 1),
(22, '2020_10_30_193412_add_meta_wallets_table', 1),
(23, '2020_12_07_100603_create_types_table', 1),
(24, '2020_12_07_103301_create_countries_table', 1),
(25, '2020_12_07_103302_create_currencies_table', 1),
(26, '2020_12_07_103380_create_languages_table', 1),
(27, '2020_12_07_103418_create_notification_types_table', 1),
(28, '2020_12_07_103419_create_blocked_tokens_table', 1),
(29, '2020_12_21_104934_create_clients_table', 1),
(30, '2020_12_21_120042_create_roles_table', 1),
(31, '2020_12_21_135144_create_users_table', 1),
(32, '2020_12_24_103343_create_map_providers_table', 1),
(33, '2020_12_24_104834_create_sms_providers_table', 1),
(34, '2020_12_25_114722_create_templates_table', 1),
(35, '2020_12_25_124722_create_client_preferences_table', 1),
(36, '2020_12_29_121021_create_client_languages_table', 1),
(37, '2020_12_30_053607_create_vendors_table', 1),
(38, '2020_12_30_060809_create_vendor_users_table', 1),
(39, '2020_12_30_061924_create_service_areas_table', 1),
(40, '2020_12_30_095943_create_cms_table', 1),
(41, '2020_12_31_142820_create_categories_table', 1),
(42, '2020_12_31_142836_create_category_translations_table', 1),
(43, '2020_12_31_165834_create_banners_table', 1),
(44, '2020_12_31_263025_create_vendor_slots_table', 1),
(45, '2020_12_31_323159_create_vendor_slot_dates_table', 1),
(46, '2020_12_31_325657_create_slot_days_table', 1),
(47, '2021_01_14_063531_create_client_currencies_table', 1),
(48, '2021_01_14_114953_create_variants_table', 1),
(49, '2021_01_14_115058_create_variant_categories_table', 1),
(50, '2021_01_14_115141_create_variant_translations_table', 1),
(51, '2021_01_14_115200_create_variant_options_table', 1),
(52, '2021_01_14_115217_create_variant_option_translations_table', 1),
(53, '2021_01_14_135222_create_brands_table', 1),
(54, '2021_01_18_141503_create_brand_categories_table', 1),
(55, '2021_01_18_141534_create_brand_translations_table', 1),
(56, '2021_01_19_103352_create_category_histories_table', 1),
(57, '2021_01_19_125204_create_tax_categories_table', 1),
(58, '2021_01_19_125318_create_tax_rates_table', 1),
(59, '2021_01_19_125451_create_tax_rate_categories_table', 1),
(60, '2021_01_20_114648_create_addon_sets_table', 1),
(61, '2021_01_20_114706_create_addon_set_translations_table', 1),
(62, '2021_01_20_114724_create_addon_options_table', 1),
(63, '2021_01_20_114734_create_addon_option_translations_table', 1),
(64, '2021_01_21_101637_create_vendor_media_table', 1),
(65, '2021_01_21_112832_create_products_table', 1),
(66, '2021_01_21_112848_create_product_translations_table', 1),
(67, '2021_01_22_101046_create_product_categories_table', 1),
(68, '2021_01_22_113717_create_product_addons_table', 1),
(69, '2021_01_22_113948_create_product_cross_sells_table', 1),
(70, '2021_01_22_114102_create_product_up_sells_table', 1),
(71, '2021_01_22_114129_create_product_related_table', 1),
(72, '2021_01_22_134800_create_product_variants_table', 1),
(73, '2021_01_22_141044_create_product_variant_sets_table', 1),
(74, '2021_02_01_101734_create_product_images_table', 1),
(75, '2021_02_03_052127_create_product_variant_images_table', 1),
(76, '2021_02_19_061327_create_user_devices_table', 1),
(77, '2021_02_20_100004_create_terminologies_table', 1),
(78, '2021_02_20_100026_create_accounts_table', 1),
(79, '2021_02_20_100053_create_reports_table', 1),
(80, '2021_02_26_094556_create_category_tags_table', 1),
(81, '2021_03_09_074202_add_port_to_clients_table', 1),
(82, '2021_03_25_064409_add_social_login_field', 1),
(83, '2021_03_25_072244_add_brands_field', 1),
(84, '2021_04_14_055025_create_user_wishlists_table', 1),
(85, '2021_04_15_125922_create_loyalty_cards_table', 1),
(86, '2021_04_16_074202_add_image_to_users_table', 1),
(87, '2021_04_20_054439_create_carts_table', 1),
(88, '2021_04_20_055234_create_promo_types_table', 1),
(89, '2021_04_20_055359_create_promocodes_table', 1),
(90, '2021_04_20_055509_create_promocode_restrictions_table', 1),
(91, '2021_04_20_055624_create_cart_coupons_table', 1),
(92, '2021_04_20_055625_create_cart_products_table', 1),
(93, '2021_04_20_092608_create_user_addresses_table', 1),
(94, '2021_04_28_041838_create_cart_addons_table', 1),
(95, '2021_05_03_092015_create_orders_table', 1),
(96, '2021_05_04_070811_create_order_vendors_table', 1),
(97, '2021_05_04_071200_create_order_products_table', 1),
(98, '2021_05_04_071929_create_order_product_addons_table', 1),
(99, '2021_05_05_080739_add_currency_field_in_cart_products', 1),
(100, '2021_05_07_145709_create_promo_usages_table', 1),
(101, '2021_05_10_034916_add_fields_to_promocodes_table', 1),
(102, '2021_05_10_052517_create_celebrities_table', 1),
(103, '2021_05_10_131738_create_promocode_details_table', 1),
(104, '2021_05_11_124314_add_fields_to_preferences_table', 1),
(105, '2021_05_12_050135_create_user_loyalty_points_table', 1),
(106, '2021_05_12_050252_create_user_loyalty_point_histories', 1),
(107, '2021_05_12_050529_alter_promocode_image_field_table', 1),
(108, '2021_05_12_100036_create_payments_table', 1),
(109, '2021_05_12_102501_alter_promocodes_short_desc_field_table', 1),
(110, '2021_05_13_053118_create_refer_and_earns_table', 1),
(111, '2021_05_13_080525_create_user_refferals_table', 1),
(112, '2021_05_13_102448_alter_cart_coupons_for_vendor_ids_field_table', 1),
(113, '2021_05_13_153007_create_celebrity_brand_table', 1),
(114, '2021_05_14_083633_add_status_to_orders', 1),
(115, '2021_05_14_084221_add_order_id_to_payments', 1),
(116, '2021_05_14_141254_add_country_id_to_celebrities', 1),
(117, '2021_05_17_092828_create_product_celebrities_table', 1),
(118, '2021_05_17_141254_add_description_to_celebrities', 1),
(119, '2021_05_19_042503_create_payment_options_table', 1),
(120, '2021_05_20_065410_alter_orders_table_order_no', 1),
(121, '2021_05_20_123811_create_jobs_table', 1),
(122, '2021_05_21_045823_add_phonecode_in_address_table', 1),
(123, '2021_05_21_045823_change_county_field_type_table', 1),
(124, '2021_05_21_063543_permission_table_for_acl', 1),
(125, '2021_05_21_074707_user_permissions_table_for_acl', 1),
(126, '2021_05_21_082602_alter_order_products_table', 1),
(127, '2021_05_21_103918_alter_order_products_for_rename_table_table', 1),
(128, '2021_05_21_113803_create_vendor_categories_table', 1),
(129, '2021_05_25_094250_user_vendors_table_for_acl', 1),
(130, '2021_05_25_100950_alter_order_vendors_table', 1),
(131, '2021_05_25_104437_alter_order_vendors_table_for_coupon_code', 1),
(132, '2021_05_27_091733_add_wishlist_field_in_categories_table', 1),
(133, '2021_05_27_112637_add_status_in_product_variants_table', 1),
(134, '2021_05_28_051503_add_timezone_in_users_table', 1),
(135, '2021_05_28_052713_add_code_in_user', 1),
(136, '2021_05_28_052713_add_superadmin_in_client', 1),
(137, '2021_05_28_052713_add_superadmin_in_user', 1),
(138, '2021_05_31_062720_add_category_switch_in_vendors_table', 1),
(139, '2021_05_31_090803_create_vendor_templetes_table', 1),
(140, '2021_05_31_091703_add_templete_id_in_vendors_table', 1),
(141, '2021_06_01_043327_create_timezones_table', 1),
(142, '2021_06_01_043453_create_order_status_table', 1),
(143, '2021_06_01_051302_add_timezone_field_users_table', 1),
(144, '2021_06_01_113407_alter_promo_codes_table', 1),
(145, '2021_06_03_061348_add_delete_at_products_table', 1),
(146, '2021_06_08_094834_create_csv_product_imports_table', 1),
(147, '2021_06_09_043732_add_loyalty_points_earned_orders_table', 1),
(148, '2021_06_09_074633_alter_cart_addons_table', 1),
(149, '2021_06_10_074037_alter_preference_dispatch_new_keys', 1),
(150, '2021_06_10_075653_add_credentials_to_payment_options', 1),
(151, '2021_06_10_094428_alter_categories_table_for_deleted_at', 1),
(152, '2021_06_11_083205_create_csv_vendor_imports_table', 1),
(153, '2021_06_14_050458_create_app_stylings_table', 1),
(154, '2021_06_14_050649_create_app_styling_options_table', 1),
(155, '2021_06_14_065037_alter_currency_id_cart_products_table', 1),
(156, '2021_06_14_105745_drop_vendor_payment_options_table', 1),
(157, '2021_06_15_102257_rename_order_status_table', 1),
(158, '2021_06_15_102631_create_dispatcher_status_options_table', 1),
(159, '2021_06_15_103435_create_order_statuses_table', 1),
(160, '2021_06_15_103450_create_dispatcher_statuses_table', 1),
(161, '2021_06_16_051848_rename_order_status2_table', 1),
(162, '2021_06_16_052121_add_vendor_id_to_order_status', 1),
(163, '2021_06_16_052903_rename_dispatcher_statuses_table', 1),
(164, '2021_06_16_052952_add_vendor_id_to_dispatcher_status', 1),
(165, '2021_06_16_064425_add_timezone_to_user_table', 1),
(166, '2021_06_16_110618_add_refer_and_earn_columns_to_client_preferences', 1),
(167, '2021_06_16_132604_drop_wallets_table', 1),
(168, '2021_06_17_044423_alter_order_vendor_web_hook_code_table', 1),
(169, '2021_06_18_072955_addtrackingurlinordervendor', 1),
(170, '2021_06_18_112628_alter_some_table_for_ref', 1),
(171, '2021_06_21_064857_add_total_delivery_fee_to_orders', 1),
(172, '2021_06_21_065449_orderproductratingstable', 1),
(173, '2021_06_21_094217_add_template_id_field_to_app_styling_options_table', 1),
(174, '2021_06_21_123621_alter_user_user_id_field_for_order_vendors_table', 1),
(175, '2021_06_21_123928_add_web_color_field_to_client_preferences_table', 1),
(176, '2021_06_22_062736_createreviewfilestable', 1),
(177, '2021_06_22_063355_alterorderproductratingsstatustable', 1),
(178, '2021_06_23_063853_add_pharmacy_check_field_to_client_preferences_table', 1),
(179, '2021_06_24_044626_add_description_to_users_table', 1),
(180, '2021_06_24_045847_add_pharmacy_check_field_to_products_table', 1),
(181, '2021_06_24_064903_create_cart_product_prescriptions_table', 1),
(182, '2021_06_24_122153_create_order_product_prescriptions_table', 1),
(183, '2021_06_25_045048_create_dispatcher_template_type_options_table', 1),
(184, '2021_06_25_052752_create_dispatcher_warning_pages_table', 1),
(185, '2021_06_25_053039_add_vendor_id_field_to_order_product_prescriptions_table', 1),
(186, '2021_06_25_054358_add_vendor_id_field_to_cart_product_prescriptions_table', 1),
(187, '2021_06_25_083707_alter_category_table_for_dipatcher_field_in_tables', 1),
(188, '2021_06_28_110323_add_category_id_field_to_order_products_table', 1),
(189, '2021_06_28_112229_alter_type_table_for_sequences_field_in_tables', 1),
(190, '2021_06_28_134738_alter_vendor_for_slug_tables', 1),
(191, '2021_06_29_045943_add_admin_commission_fields_to_order_vendors_table', 1),
(192, '2021_06_29_063720_add_actual_amount_fields_to_order_vendors_table', 1),
(193, '2021_06_29_084253_add_taxable_amount_fields_to_order_vendors_table', 1),
(194, '2021_06_29_094113_change_taxable_amount_fields_to_order_vendors_table', 1),
(195, '2021_06_29_102709_createtableorderreturnrequests', 1),
(196, '2021_06_29_121116_change_limit_fields_to_promocodes_table', 1),
(197, '2021_06_30_053518_createtablereturn_reasons', 1),
(198, '2021_06_30_095821_createtablereturnrequestfiles', 1),
(199, '2021_06_30_123018_add_image_path_field_to_dispatcher_template_type_options_table', 1),
(200, '2021_06_30_123341_add_image_path_field_to_dispatcher_warning_pages_table', 1),
(201, '2021_06_30_130644_alter_vendor_orders_add_payment_option_id_table', 1),
(202, '2021_07_01_071008_alter_orders_for_loyalty_membership_id_table', 1),
(203, '2021_07_01_072413_add_dine_in_fields_to_client_preferences_table', 1),
(204, '2021_07_01_124823_create_order_taxes_table', 1),
(205, '2021_07_02_123701_alter_order_vendor_products_for_order_vendor_id', 1),
(206, '2021_07_05_045644_add_slug_in_celebrities_table', 1),
(207, '2021_07_05_075226_alter_types_for_images_table', 1),
(208, '2021_07_05_104133_addreasonbyvendorinreturnrequests', 1),
(209, '2021_07_05_123711_add_loyalty_check_field_to_loyalty_cards_table', 1),
(210, '2021_07_06_045605_alter_vendors_table_forfew_fields', 1),
(211, '2021_07_06_054045_addpickupdeliverykeysinclientprefereance', 1),
(212, '2021_07_06_084032_create_subscription_validities_table', 1),
(213, '2021_07_06_091605_create_social_media_table', 1),
(214, '2021_07_06_091729_create_subscription_features_list_table', 1),
(215, '2021_07_06_095637_create_user_subscriptions_table', 1),
(216, '2021_07_06_095655_create_user_subscription_features_table', 1),
(217, '2021_07_07_054636_create_luxury_options_table', 1),
(218, '2021_07_07_055509_add_luxury_option_id_to_cart_products_table', 1),
(219, '2021_07_07_092630_create_vendor_subscriptions_table', 1),
(220, '2021_07_07_092704_create_vendor_subscription_features_table', 1),
(221, '2021_07_07_104413_alter_client_preferences_table_for_cart_enable', 1),
(222, '2021_07_07_114341_alter_products_table_for_enquire_mod', 1),
(223, '2021_07_07_122135_add_rating_check_to_client_preferences_table', 1),
(224, '2021_07_08_050906_addtagkeyinproducttable', 1),
(225, '2021_07_08_060154_create_product_inquiries_table', 1),
(226, '2021_07_08_064807_add_vendor_id_to_product_inquiries_table', 1),
(227, '2021_07_08_070945_create_pages_table', 1),
(228, '2021_07_08_105308_alter_pages_table', 1),
(229, '2021_07_08_132134_alter_wallets_table', 1),
(230, '2021_07_09_052529_alter_user_subscriptions_table', 1),
(231, '2021_07_09_063048_alter_vendor_subscriptions_table', 1),
(232, '2021_07_09_064149_create_page_translations_table', 1),
(233, '2021_07_09_072813_create_subscribed_users_table', 1),
(234, '2021_07_09_091843_create_subscribed_status_options_table', 1),
(235, '2021_07_12_101144_add_phone_code_field_to_users_table', 1),
(236, '2021_07_12_105145_rename_phone_code_in_users_table', 1),
(237, '2021_07_12_130335_alter_payment_options_table', 1),
(238, '2021_07_15_141847_create_subscription_status_options_table', 2),
(239, '2021_07_16_042043_create_subscription_features_list_user_table', 2),
(240, '2021_07_16_042211_create_subscription_plans_user_table', 2),
(241, '2021_07_16_042346_create_subscription_plan_features_user_table', 2),
(242, '2021_07_16_042524_create_subscription_invoices_user_table', 2),
(243, '2021_07_16_042848_create_subscription_log_user_table', 2),
(244, '2021_07_16_045752_create_subscription_invoice_features_user_table', 2),
(245, '2021_07_16_061522_drop_subscriptions_table', 2),
(246, '2021_07_16_054443_create_vendor_registration_documents_table', 3),
(247, '2021_07_16_083525_alter_subscription_plans_user_table', 3),
(248, '2021_07_16_092718_create_subscription_features_list_vendor_table', 3),
(249, '2021_07_16_092754_create_subscription_plans_vendor_table', 3),
(250, '2021_07_16_092836_create_subscription_plan_features_vendor_table', 3),
(251, '2021_07_16_100042_create_vendor_docs_table', 3),
(252, '2021_07_16_120516_add_frequency_to_subscription_plans_user_table', 4),
(253, '2021_07_16_121019_add_frequency_to_subscription_plans_vendor_table', 4),
(254, '2021_07_16_121201_add_frequency_to_subscription_invoices_user_table', 4),
(255, '2021_07_16_105955_alter_user_table_for_title', 5),
(256, '2021_07_19_052218_alter_vendor_registration_document_translations_table', 5),
(257, '2021_07_19_081612_create_nomenclatures_table', 6),
(258, '2021_07_19_083627_alter_vendors_table_for_is_show_category_details', 6),
(259, '2021_07_20_045632_alter_vendor_docs_table', 7),
(260, '2021_07_20_054826_create_user_saved_payment_methods_table', 8),
(261, '2021_07_20_085414_create_nomenclatures_translations_table', 9),
(262, '2021_07_20_111025_add_subscription_discount_to_orders', 10),
(263, '2021_07_21_045727_create_email_templates_table', 11),
(264, '2021_07_21_051114_add_subscription_invoice_id_to_payments', 11),
(265, '2021_07_21_081216_alter_subscription_invoices_user_table', 11),
(266, '2021_07_22_045616_create_subscription_invoices_vendor_table', 12),
(267, '2021_07_22_045636_create_subscription_invoice_features_vendor_table', 12),
(268, '2021_07_22_051752_add_vendor_subscription_invoice_id_to_payments', 12),
(269, '2021_07_22_053723_create_vendor_saved_payment_methods_table', 12),
(270, '2021_07_22_064142_add_added_by_in_promos_table', 13),
(271, '2021_07_22_085935_add_frequency_to_subscription_invoices_vendor', 13),
(272, '2021_07_23_065414_alter_description_in_subscription_plans_user', 14),
(273, '2021_07_23_065425_alter_description_in_subscription_plans_vendor', 14),
(274, '2021_07_23_065528_add_user_id_to_vendor_saved_payment_method', 14),
(275, '2021_07_23_085721_add_subscription_mode_to_client_preferences', 15),
(276, '2021_07_23_095519_add_user_id_to_subscription_invoices_vendor', 16),
(277, '2021_07_26_051945_add_tip_amount_to_orders', 17),
(278, '2021_07_26_054718_create_vendor_dinein_categories_table', 18),
(279, '2021_07_26_062106_add_age_restriction_field_to_client_preferences', 18),
(280, '2021_07_26_064915_add_age_restriction_title_field_to_client_preferences', 18),
(281, '2021_07_26_072854_create_vendor_dinein_tables_table', 18),
(282, '2021_07_26_092647_create_vendor_dinein_table_translations_table', 18),
(283, '2021_07_26_105532_add_vendor_id_field_to_vendor_dinein_tables', 18),
(284, '2021_07_27_094908_add_contact_us_field_to_client_preferences_table', 19),
(285, '2021_07_27_103709_addserviceskeyinconfigtable', 20),
(286, '2021_07_27_130205_addpricefromdispatcherproductstable', 20),
(287, '2021_07_28_053234_addproduct_dispatcher_taginorder_vendor_productstable', 20),
(288, '2021_07_28_085341_alter_orders_table_for_scheduled_date_time', 20),
(289, '2021_07_28_101214_create_vendor_dinein_category_translations_table', 21),
(290, '2021_07_28_115607_add_seating_number_to_vendor_dinein_tables_table', 21),
(291, '2021_07_30_064620_add_fields_wishlist_to_client_preferences_table', 21),
(292, '2021_07_30_070813_create_home_page_labels_table', 21),
(293, '2021_07_30_071648_create_home_page_label_transaltions_table', 21),
(294, '2021_07_30_090119_add_slug_field_to_home_page_labels_table', 22),
(295, '2021_07_30_095659_add_loyalty_check_field_to_client_preferences_table', 22),
(296, '2021_08_02_130659_alterdispatcherstatusinordervendorstable', 23),
(297, '2021_08_04_064805_add_order_by_field_to_home_page_labels_table', 24),
(298, '2021_08_05_050409_addlast_mile_team_in_preference', 24),
(299, '2021_08_06_082831_alter_csv_product_imports_table_for_woocomerce_import', 25),
(300, '2021_08_06_113231_create_woocommerces_table', 25),
(301, '2021_08_09_084617_create_notification_templates_table', 26),
(302, '2021_08_10_071354_add_image_to_loyalty_cards', 27),
(303, '2021_08_12_114552_add_vendor_dinein_table_id_to_order_vendors', 28),
(304, '2021_08_12_122400_add_vendor_dinein_table_id_to_cart_products', 28),
(305, '2021_08_17_095154_add_wallet_amount_used_to_orders', 29),
(306, '2021_08_17_103153_add_show_payment_icons_to_client_preferences_table', 30),
(307, '2021_08_19_063431_add_dark_mode_toggle_to_client_preferences_table', 31),
(308, '2021_08_19_090959_add_scheduled_date_time_to_carts', 31),
(309, '2021_08_24_065505_alter_timezone_in_clients', 32),
(310, '2021_08_24_091110_add_instructionsin_carts_table', 33),
(311, '2021_08_26_130550_add_site_top_header_color_to_client_preferences', 34),
(312, '2021_09_04_044855_add_columns_to_cart_products_table', 35),
(313, '2021_09_03_123843_addmodeofserviceinproductstable', 36),
(314, '2021_09_04_121447_addschedulecolumnsinorderproducts', 36),
(315, '2021_09_06_094107_addtipafterbeofreclientpreferences', 36),
(316, '2021_09_06_112912_addautoacceptorderclientpreferences', 36),
(317, '2021_09_06_134705_addautoacceptvendors', 37),
(318, '2021_09_07_120350_onoffnowschedulekeyinclientpreferenace', 38),
(319, '2021_09_06_072333_create_driver_registration_documents_table', 39),
(320, '2021_09_06_092853_alter_driver_registration_document_translations_table', 39),
(321, '2021_09_10_131003_addisolatesinglevendorordertoclientpreferences', 39),
(322, '2021_09_14_061845_create_onboard_settingstable', 40),
(323, '2021_09_14_125501_add_testmode_to_payment_options', 41),
(324, '2021_09_15_085625_createcabbookinglayoutstable', 42),
(325, '2021_09_16_064640_createcab_booking_layout_translations', 42),
(326, '2021_09_16_070957_add_firebase_credentials_columns_to_client_preferences', 42),
(327, '2021_09_16_113622_createcab_booking_layout_catrgories', 42),
(328, '2021_09_17_070649_altercab_booking_layout_transaltionstable', 43),
(329, '2021_09_21_062239_add_column_to_page_translations_table', 44),
(330, '2021_09_21_063043_add_distance_time_cal_to_client_preference', 45),
(331, '2021_09_23_124035_webstylingtable', 46),
(332, '2021_09_23_124059_webstylingoptionstable', 46),
(333, '2021_09_23_142527_alter_order_vendors_for_eta', 46),
(334, '2021_09_23_142815_hide_nav_bar_client_preferences', 46),
(335, '2021_09_23_113026_add_columns_to_brand_categories', 47),
(336, '2021_09_21_142819_add_product_type_to_client_preference', 48),
(337, '2021_09_29_053310_createbusinessmodaltable', 48),
(338, '2021_09_29_065353_alterproducttypeclientpreftable', 48),
(339, '2021_10_01_121216_add_column_to_order_vendors_table', 49),
(340, '2021_10_07_054022_add_column_to_brands_table', 50),
(341, '2021_10_06_112147_add_image_mobile_to_banners', 51),
(342, '2021_10_11_064940_add_payment_status_to_orders', 52),
(343, '2021_10_13_105952_alter_apple_playstore_link', 53),
(344, '2021_10_13_122046_add_luxury_option_id_to_orders', 54),
(345, '2021_10_14_094040_altersingle_vendorinclientpref', 55),
(346, '2021_10_14_054612_create_mobile_banners_table', 56),
(347, '2021_10_14_105550_add_column_to_permissions_table', 56),
(348, '2021_10_19_071128_alterimageinlayouttranslations', 57),
(349, '2021_10_19_065203_auto_reject_orders_cron', 58),
(350, '2021_10_22_110103_create_app_dynamic_tutorials_table', 59),
(351, '2021_10_28_131829_add_stripe_connect_to_client_preferences', 60),
(352, '2021_11_08_065839_add_column_promo_security_in_promocodes', 61),
(353, '2021_11_09_123052_addlaundrykeysinpreftable', 62),
(354, '2021_11_10_054734_add_column_is_required_to_vendor_registration', 63),
(355, '2021_11_10_130246_alter_email_null_to_users', 64),
(356, '2021_11_11_125845_altercartspickupdropoffcomment', 65),
(357, '2021_11_11_132220_alterorderspickupdropoffcomment', 65),
(358, '2021_11_12_105836_altercartschedulepickup', 66),
(359, '2021_11_12_115351_alterschedulepickupinorders', 66),
(360, '2021_11_13_103058_create_vendor_connected_accounts', 67),
(361, '2021_11_15_073414_create_payout_options_table', 67),
(362, '2021_11_16_051354_create_vendor_payouts', 68),
(363, '2021_11_17_115023_tagsandtagtranslations', 69),
(364, '2021_11_17_132120_createproducttagstable', 69),
(365, '2021_11_18_064423_alterspecific_instructionorders', 70),
(366, '2021_11_18_112035_add_column_admin_email_to_client_preferences', 70),
(367, '2021_11_22_063948_uploadiconintagstable', 71),
(368, '2021_11_23_060003_delay_orderinclient_preftable', 72),
(369, '2021_11_23_085524_alterdelayorderhrs', 73),
(370, '2021_11_24_052349_add_others_type_to_user_addresses', 74),
(371, '2021_11_24_093126_alter_users_remove_unique_email', 74),
(372, '2021_11_25_083724_add_gifting_to_client_preferences_table', 75),
(373, '2021_11_25_090306_add_is_gifit_to_orders_table', 75),
(374, '2021_11_25_091835_addproduct_variant_setsincartproductorderproducts', 75),
(375, '2021_11_27_093049_alter_table_orders_change_tip_amount', 76),
(376, '2021_11_29_113505_add_house_number_to_user_addresses_table', 77),
(377, '2021_11_30_060041_createproductfaqstable', 77),
(378, '2021_11_30_100231_alterclientpreproductorderform', 78),
(379, '2021_12_01_114017_add_service_fee_to_vendors', 79),
(380, '2021_12_01_114828_alterproductfaqs', 79),
(381, '2021_12_01_124153_alterordervendorproductsforfaqs', 79),
(382, '2021_12_03_072456_alterproductforpickupdropdelay', 80),
(383, '2021_12_06_093330_alterordervendorcacelledby', 81),
(384, '2021_12_07_120543_alter_commission_percent', 82),
(385, '2021_12_09_065303_create_shipping_options_table', 83),
(386, '2021_12_09_101244_add_need_shipment_column_to_products_table', 83),
(387, '2021_12_09_122123_add_sms_credentials_to_client_preferences_table', 83),
(388, '2021_12_14_111734_changeskustringlenghthinproducts', 84),
(389, '2021_12_15_123336_add_pickup_delivery_service_area_mod', 85),
(390, '2021_12_16_063556_add_customer_support_to_preferences', 86),
(391, '2021_12_17_091114_add_need_shipping_column_to_preferences_table', 87),
(392, '2021_12_17_114832_changer_doller_compare_inclient_curr', 87),
(393, '2021_12_21_102229_alterskuvariant', 88),
(394, '2021_12_21_131451_altercategoryslug', 89),
(395, '2021_12_22_121226_altervendor_order_dispatcher_statusestypeley', 90),
(396, '2021_12_24_090013_change_doller_compareinclient_cur', 91),
(397, '2021_12_27_091310_add_order_by_to_pages_table', 92),
(398, '2021_12_28_111429_alterclientpreftableminuimunorder', 93),
(399, '2021_12_28_112914_add_contact_details_in_to_clients_table', 94),
(400, '2021_12_29_100029_alterdefaultvalueofminimumordercount', 94),
(401, '2021_12_27_063318_create_faq_translations_table', 95),
(402, '2022_01_04_114218_altercabbookinglayoutsectionfornoproduct', 96),
(403, '2021_12_27_104701_alter_table_order_add_shipping_delivery_type', 97),
(404, '2021_12_28_071901_create_webhooks_table', 97),
(405, '2021_12_28_072159_alter_table_vendor_order_add_lalamove_tracking_url', 97),
(406, '2022_01_06_130150_alterclientpreferenceeditorder', 98),
(407, '2022_01_07_094726_alterclientprefstaticdeliveryfee', 99),
(408, '2022_01_04_141653_create_client_slots_table', 100),
(409, '2022_01_04_141745_alter_table_order_add_scheduled_slot', 100),
(410, '2022_01_05_095818_alter_table_cart_add_scheduled_slot', 100),
(411, '2022_01_05_125452_alter_table_vendor_add_slot_minutes', 100),
(412, '2022_01_10_072707_create_cart_vendor_delivery_fee', 100),
(413, '2022_01_10_122812_csv_customer_import', 100),
(414, '2022_01_10_123855_alterordertypechanges', 100),
(415, '2022_01_07_133827_alter_mail_password_client_preferences', 101),
(416, '2022_01_10_071320_create_vendor_registration_select_options_table', 101),
(417, '2022_01_10_071957_create_vendor_registration_select_option_translations_table', 101),
(418, '2022_01_11_072109_alter_deleted_at_vendor_registration_select_options', 101),
(419, '2022_01_11_121802_add_quick_link_header_to_client_preferences_table', 101),
(420, '2022_01_13_110711_alter_doller_compare_to_client_currencies', 102),
(421, '2022_01_13_102401_add_closed_store_scheduled_to_vendors_table', 103),
(422, '2022_01_14_091858_create_temp_carts_table', 103),
(423, '2022_01_18_070806_add_instructions_to_user_addresses_table', 103),
(424, '2022_01_18_122254_createestimateproductstable', 103),
(425, '2022_01_19_072558_altergetestimationproductsclientpref', 103),
(426, '2022_01_19_074135_createaddonssetforestimationstable', 103),
(427, '2022_01_19_105546_alter_id_to_client_currencies', 103),
(428, '2022_01_19_121329_add_columns_to_products_table', 103),
(429, '2022_01_20_054851_add_order_vendor_id_to_temp_carts', 103),
(430, '2022_01_20_065942_alterdecimalincartdeliveryee', 103),
(431, '2022_01_20_111239_change_type_of_doller_compare_in_client_currencies_table', 104),
(432, '2022_01_18_135755_alter_table_order_and_cart_delivery_type', 105),
(433, '2022_01_24_131516_add_tools_mode_in_client_preferences_table', 106),
(434, '2022_01_27_095406_add_shiiping_delivery_type_to_cart_table', 107),
(435, '2022_01_27_101440_alter_cart_vendor_delivery_fee', 107),
(436, '2022_01_27_102929_alter_order_delivery_fee', 107),
(437, '2022_01_27_115101_add_pincode_to_vendor_table', 107),
(438, '2022_01_27_122218_add_shipping_type_to_order_vendor_table', 107),
(439, '2022_01_27_140524_add_import_user_id_to_user_table', 107),
(440, '2022_01_31_125954_add_shiprocket_details_to_vendor_order', 108),
(441, '2022_01_31_140146_alter_pickup_address_to_vendors_table', 108),
(442, '2022_02_01_073527_alter_table_vendors', 108),
(443, '2022_02_02_135349_remove_fornkey_smsprovider', 109),
(444, '2022_02_03_084358_change_type_of_price_in_product_variants_table', 109),
(445, '2022_02_03_084415_change_type_of_compare_at_price_in_product_variants_table', 109),
(446, '2022_02_03_084430_change_type_of_cost_price_in_product_variants_table', 109),
(447, '2022_02_03_100049_add_return_request_to_vendors_table', 109),
(448, '2022_02_04_071445_alter_table_order_cart_type', 109),
(449, '2022_02_04_072335_add_address_id_in_carts_table', 109),
(450, '2022_02_11_070452_add_ahoy_location_to_vendor_table', 110),
(451, '2022_01_27_090538_create_campaigns_table', 111),
(452, '2022_01_27_092840_create_campaign_rosters_table', 111),
(453, '2022_02_14_115428_add_max_safety_to_vendors', 111),
(454, '2022_02_14_124300_add_max_safety_mod_to_client_preferences', 111),
(455, '2022_02_17_060017_add_address_is_car_to_client_preferences_table', 112),
(456, '2022_02_17_064026_create_car_details_table', 112),
(457, '2022_02_17_070848_create_car_images_table', 112),
(458, '2022_02_18_095036_add_hide_order_address_to_client_preferences_table', 113),
(459, '2022_02_22_130802_add_container_charges_to_product_variants', 114),
(460, '2022_02_24_123031_altervendorcontainercharges', 114),
(461, '2022_02_25_095328_create_order_cancel_requests_table', 115),
(462, '2022_02_24_062453_add_digit_after_decimal_column_to_client_preference_table', 116),
(463, '2022_02_24_070926_change_datatype_in_product_variant_table', 116),
(464, '2022_02_24_101542_change_datatype_in_cart_vendor_delivery_fee_table', 116),
(465, '2022_02_24_102033_change_datatype_in_addon_options_table', 116),
(466, '2022_02_25_062255_change_datatype_in_orders_table', 116),
(467, '2022_02_25_071122_change_datatypes_in_order_vendors_table', 116),
(468, '2022_02_25_071133_change_datatypes_in_order_vendor_products_table', 116),
(469, '2022_02_25_130537_change_datatype_in_subscription_invoices_user_table', 116),
(470, '2022_02_25_130609_change_datatype_in_subscription_invoices_vendor_table', 116),
(471, '2022_02_25_130626_change_datatype_in_subscription_log_user_table', 116),
(472, '2022_02_25_130638_change_datatype_in_subscription_plans_user_table', 116),
(473, '2022_02_25_130647_change_datatype_in_subscription_plans_vendor_table', 116),
(474, '2022_02_26_114321_alterorder_vendor_products_container', 116),
(475, '2022_02_28_061457_change_datatype_in_client_preferences_table', 116),
(476, '2022_02_28_064013_change_datatype_in_promocodes_table', 116),
(477, '2022_03_01_065739_create_user_registration_documents_table', 117),
(478, '2022_03_01_130319_add_last_login_at_in_users_table', 118),
(479, '2022_03_02_065411_add_accepted_by_in_order_vendors_table', 118),
(480, '2022_03_02_064723_create_user_docs_table', 119),
(481, '2022_03_03_050841_add__whatsapp_url_to_clients_table', 119),
(482, '2022_03_03_132129_add_file_name_to_user_docs_table', 119),
(483, '2022_03_07_071327_add_dial_code_to_vendors_table', 120),
(484, '2022_03_09_115836_add_icon2_in_category_table', 121),
(485, '2022_03_10_120136_add_payment_option_to_payments', 121),
(486, '2022_03_11_053432_add_driver_id_order_vendorstable', 122),
(487, '2022_03_11_072358_add_address_status_in_user_addresses', 122),
(488, '2022_03_11_110645_add_cancel_order_by_user_to_preferences', 123),
(489, '2017_09_01_000000_create_authentication_log_table', 124),
(490, '2022_03_10_092839_create_audits_table', 124),
(491, '2022_03_15_060613_create_verification_options_table', 125),
(492, '2022_03_15_061434_create_user_verification_table', 125),
(493, '2022_03_16_072836_add_nullable_column_to_authentication_log', 125),
(494, '2022_03_15_135238_change_datatype_in_user_verification_table', 126),
(495, '2022_03_16_073632_add_viva_order_id_to_orders_table', 127),
(496, '2022_03_16_092414_add_age_restriction_on_product_mode_to_client_preferences_table', 127),
(497, '2022_03_16_094519_add_age_restriction_to_products_table', 127),
(498, '2022_03_17_065342_add_show_qr_on_footer_field_to_client_preferences_table', 127),
(499, '2022_03_17_072753_create_order_driver_ratings_table', 127),
(500, '2022_03_17_094812_add_product_form_data_to_cart_products_table', 127),
(501, '2022_03_21_061632_add_viva_order_idto_payments_table', 127),
(502, '2022_03_23_131440_alter_delivery_min_and_deliver_max_exact_number_to_vendors_table', 128),
(503, '2022_03_24_095337_add_return_request_in_client_preferences', 129),
(504, '2022_03_21_115050_add_need_xero_to_client_preferences', 130),
(505, '2022_03_28_063732_add_schedule_slot_to_cart_products_table', 131),
(506, '2022_03_28_064916_create_third_party_accounting_table', 132),
(507, '2022_03_23_060708_add_category_kyc_to_client_preferences_table', 133),
(508, '2022_03_23_062102_create_category_kyc_documents_table', 133),
(509, '2022_03_23_092435_create_category_kyc_document_mappings_table', 133),
(510, '2022_03_25_073314_add_fixed_fee_to_vendors_table', 133),
(511, '2022_03_25_104241_create_caregory_kyc_docs_table', 133),
(512, '2022_03_28_093353_add_fixed_fee_filed_to_orders_table', 133),
(513, '2022_03_29_073623_add_enable_log_column_to_client_preferences', 134),
(514, '2022_03_29_131837_add_newtheme_iconto_preferneces_table', 134),
(515, '2022_04_04_071604_add_orders_per_slot_column_to_vendors', 135),
(516, '2022_03_30_064204_create_order_vendor_reports_table', 136),
(517, '2022_03_30_072410_add_price_bifurcation_to_vendors_table', 136),
(518, '2022_03_31_124517_create_order_vendor_accounting_table', 136),
(519, '2022_04_01_072010_add_third_party_accounting_to_client_preference_table', 136),
(520, '2022_04_05_062416_create_user_verification_resources_table', 137),
(521, '2022_04_07_135040_rename_column_in_categories_table', 138),
(522, '2022_04_08_105739_add_second_description_in_vendor_table', 139),
(523, '2022_04_13_093629_add_hide_order_preparation_time_in_client_preference', 139),
(524, '2022_04_14_095740_map_app_keyinclientpref', 140),
(525, '2022_04_19_063051_add_instagram_url_in_vendor', 141),
(526, '2022_04_14_064508_create_order_refunds_table', 142),
(527, '2022_04_19_091921_alert_amount_dacimal_to_payments_table', 142),
(528, '2022_04_20_071354_add_percent_value_to_sub_plan_features', 143),
(529, '2022_04_21_070646_add_percent_value_to_subscription_invoice', 143),
(530, '2022_04_29_074012_add_soft_delete_to_addon_options_table', 144),
(531, '2022_05_02_110456_add_subscriptiontabtaxi_to_client_preferences_table', 145),
(532, '2022_05_02_131454_add_easebuzz_sub_merchent_id_to_vendors_table', 146),
(533, '2022_05_02_052634_add_taxes_on_charges_to_vendor_table', 147),
(534, '2022_05_02_093842_add_taxes_on_charges_to_products_table', 147),
(535, '2022_05_03_054055_add_book_a_ride_fields_to_orders_table', 147),
(536, '2022_05_03_100850_add_taxes_of_fixed_fee_to_vendors_table', 147),
(537, '2022_05_03_101014_add_taxes_of_fixed_fee_to_products_table', 147),
(538, '2022_05_12_101300_create_shippo_delivery_options_table', 148),
(539, '2022_05_12_114316_add_other_taxes_to_orders_table', 148),
(540, '2022_05_13_050723_alter_table_delivery_options_type', 148),
(541, '2022_05_13_091707_alter_table_delivery_option_courier_id', 148),
(542, '2022_05_13_061328_alterclientpreferencesneedinventory', 149),
(543, '2022_05_16_105819_add_initial_gateway_reference_to_orders', 149),
(544, '2022_05_17_094521_add_initial_gateway_reference_to_payments', 150),
(545, '2022_05_13_103130_add_scheduling_with_slots_column_to_client_preferences', 151),
(546, '2022_05_13_104159_add_vendor_type_column_to_vendor_slots', 151),
(547, '2022_05_13_104449_add_dropoff_slot_column_to_orders', 151),
(548, '2022_05_13_104619_create_reschedule_orders_table', 151),
(549, '2022_05_13_104830_add_res_column_to_vendors', 151),
(550, '2022_05_17_115617_create_vendor_order_cancel_return_payments_table', 152),
(551, '2022_05_19_090331_add_book_for_friend_to_client_preferences_table', 152),
(552, '2022_05_23_100143_add_dropoff_slot_column_to_cart', 153),
(553, '2022_05_24_105754_create_order_locations_table', 154),
(554, '2022_05_25_062946_create_riders_table', 154),
(555, '2022_05_25_112715_add_scheduled_date_time_to_order_vendors_table', 154),
(556, '2022_05_25_113529_add_schedule_slot_to_order_vendors_table', 154),
(557, '2022_05_26_121554_change_datatype_in_riders_table', 155),
(558, '2022_05_30_130705_add_payment_from_in_payments', 155),
(559, '2022_06_02_091940_add_cancellation_to_client_preferences_table', 156),
(560, '2022_06_02_072659_aletrvendororderpretime', 157),
(561, '2022_06_05_165654_add_viewestimation_column_to_client_preferences', 158),
(562, '2022_06_05_185940_add_estimation_matching_logic_column_to_client_preferences', 158),
(563, '2022_06_10_065609_add_vendor_fcm_server_key_to_client_preferences_table', 158),
(564, '2022_06_10_072111_create_payment_methods_table', 158),
(565, '2022_06_10_114945_add_age_restriction_to_order_vendors_table', 158),
(566, '2022_06_10_130703_add_is_vendor_app_to_user_devices_table', 158),
(567, '2022_06_13_101225_add_sos_to_client_preferences_table', 158),
(568, '2022_06_15_092348_create_estimated_product_carts', 158),
(569, '2022_06_15_092401_create_estimated_products', 158),
(570, '2022_06_15_092413_create_estimated_product_addons', 158),
(571, '2022_06_15_092453_add_category_id_column_to_estimate_products', 158),
(572, '2022_06_16_130430_alter_table_vendors_add_column_weight', 158),
(573, '2022_06_16_133056_create_show_subscription_plan_on_signups_table', 159),
(574, '2022_06_29_121840_add_delete_at_to_users_table', 160),
(575, '2022_06_20_072115_alterproductimportfrominventory', 161),
(576, '2022_06_20_124801_add_global_product_id_into_products_table', 161),
(577, '2022_06_21_131642_alter_table_estimate_product_add_price', 161),
(578, '2022_06_22_074921_csv_qrcode_import', 161),
(579, '2022_06_22_094349_create_qrcode_imports_table', 161),
(580, '2022_06_22_110313_create_assign_qrcodes_to_orders_table', 161),
(581, '2022_06_23_110005_add_concise_signup_to_client_preferences_table', 161),
(582, '2022_06_30_101953_create_sms_templates_table', 161),
(583, '2022_06_30_130004_add_is_static_dropoff_to_client_preferences_table', 161),
(584, '2022_07_01_053556_create_static_dropoff_locations_table', 161),
(585, '2022_07_05_070345_add_place_id_od_to_static_dropoff_locations_table', 161),
(586, '2022_07_08_090742_add_vendor_types_to_client_preferences_table', 162),
(587, '2022_07_08_092045_add_vendor_types_to_vendors_table', 162),
(588, '2022_07_08_130702_add_vendor_types_to_vendor_slots_table', 162),
(589, '2022_07_11_093423_add_vendor_type_icon_to_client_preferences_table', 162),
(590, '2022_06_27_130430_alter_table_clients_add_column_socketUrl', 163),
(591, '2022_06_27_130430_alter_table_clients_add_column_socket_action', 163),
(592, '2022_07_01_102844_create_table_order_qrcode_links', 163),
(593, '2022_07_04_114248_add_is_scan_qrcode_bag_to_client_preferences_table', 163),
(594, '2022_07_11_131646_add_service_type_to_types_table', 163),
(595, '2022_07_12_134310_add_service_area_id_to_vendor_slots', 163),
(596, '2022_07_13_123038_add_delivery_duration_cart_delivery_fee_table', 163),
(597, '2022_07_14_113516_create_vendor_slot_service_area_table', 163),
(598, '2022_07_15_103743_add_is_active_for_vendor_slot', 163),
(599, '2022_07_19_123417_add_rentel_fields_to_products_table', 163),
(600, '2022_07_21_071619_add_column_to_product_variants_table', 163),
(601, '2022_07_21_094327_add_markup_price_to_products_table', 163),
(602, '2022_07_21_100722_add_markup_price_to_order_vendor_products_table', 163),
(603, '2022_07_21_100815_add_markup_price_to_order_vendors_table', 163),
(604, '2022_07_21_132400_add_markup_price_to_product_varients', 163),
(605, '2022_07_21_135535_add_markup_price_to_vendors', 163),
(606, '2022_07_22_061837_add_customer_location_to_orders', 163),
(607, '2022_07_22_104929_add_sub_cat_banners_to_categories_table', 163),
(608, '2022_07_22_123352_alter_table_add_vendor_id_to_qrcode_imports', 163),
(609, '2022_07_25_110312_add_markup_price_tax_id_to_vendors_table', 163),
(610, '2022_07_25_112613_create_facilties_table', 163),
(611, '2022_07_25_113021_create_facilty_translations_table', 163),
(612, '2022_07_26_063111_create_vendor_facilties_table', 163),
(613, '2022_07_26_125824_add_vendor_tags_to_client_preferences_table', 163),
(614, '2022_07_27_122448_add_cart_total_othertaxes_to_carts_table', 163),
(615, '2022_07_27_131323_add_dynamic_html_to_vendors_table', 163),
(616, '2022_07_28_114415_alter_table_drop_foreign_key_estimate_product_carts_table', 163),
(617, '2022_07_28_131211_create_estimated_product_cart_new', 163),
(618, '2022_07_28_131659_create_estimated_product_new', 163),
(619, '2022_07_28_133626_create_estimated_product_addon_new', 163),
(620, '2022_08_01_114216_create_service_area_for_banners_table', 163),
(621, '2022_08_01_115944_create_banner_service_areas_table', 163),
(622, '2022_08_01_131058_create_vendor_sections_table', 163),
(623, '2022_08_01_131519_create_vendor_section_translations_table', 163),
(624, '2022_08_02_092017_create_vendor_section_heading_translations_table', 163),
(625, '2022_08_02_093842_add_is_service_area_for_banners', 163),
(626, '2022_08_02_105627_add_type_to_serviceareaforbanners', 163),
(627, '2022_08_02_110655_create_mobile_banner_service_areas_table', 163),
(628, '2022_08_02_123417_add_rental_hrs_min_fields_to_products_table', 163),
(629, '2022_08_03_101324_add_disable_order_acceptance_for_users', 163),
(630, '2022_08_03_125858_add_dark_logo_to_clients_table', 163),
(631, '2022_08_04_112430_add_fixed_fee_amount_to_order_vendors_table', 163),
(632, '2022_08_05_112430_add_product_booking_table', 164),
(633, '2022_08_05_112430_add_variant_incremental_price_product_variant_table', 165),
(634, '2022_08_18_112430_add_start_end_time_cart_product_table', 166),
(635, '2022_08_20_112430_add_incremental_duration_cart_product_table', 167),
(636, '2022_08_20_112431_add_incremental_duration_order_vendor_product_table', 167),
(637, '2022_08_23_112431_add_incremental_duration_price_order_vendor_product_table', 168),
(638, '2022_08_23_112455_add_incremental_price_per_min_product_variant_table', 168),
(639, '2022_08_25_055904_add_addition_price_coulmn_in_order_vendors_table', 168),
(640, '2022_08_25_060955_add_addition_price_coulmn_in_orders_table', 168),
(641, '2022_08_25_113813_add_total_booking_time_to_cart_products_table', 168),
(642, '2022_08_25_113917_add_total_booking_time_to_order_vendor_products_table', 168),
(643, '2022_08_26_053350_create_vendor_cities_table', 168),
(644, '2022_08_26_053503_create_vendor_city_translations_table', 168),
(645, '2022_08_16_092731_add_appointment_to_client_preferences_table', 169),
(646, '2022_08_16_094404_add_appointment_to_vendors_table', 169),
(647, '2022_08_16_111303_add_appointment_to_vendor_slots_table', 169),
(648, '2022_08_17_063001_add_vendor_type_to_vendor_slot_dates_table', 169),
(649, '2022_08_17_094411_add_appointment_server_to_client_preferences_table', 169),
(650, '2022_08_23_095405_add_schedule_slot_to_order_vendor_products_table', 169),
(651, '2022_08_24_123846_create_order_product_dispatch_routes_table', 169),
(652, '2022_08_24_130920_create_vendor_order_product_dispatcher_statuses_table', 169),
(653, '2022_09_01_101827_add_signup_image_to_client_preferences_table', 170),
(654, '2022_09_05_073207_add_status_to_vendor_order_product_dispatcher_statuses_table', 170),
(655, '2022_09_06_111044_add_mapview_to_client_preferences_table', 171),
(656, '2022_09_19_105637_add_vendor_rating_field', 172),
(657, '2022_09_15_134502_alter_table_product_faqs_table', 173),
(658, '2022_09_15_134806_alter_table_product_faqs_select_option', 173),
(659, '2022_09_15_134818_alter_table_product_faqs_select_option_translation', 173),
(660, '2022_09_16_055039_add_driver_id_to_cart_products_table', 173),
(661, '2022_09_19_054227_add_dispatch_agent_id_to_order_vendor_products_table', 173),
(662, '2022_09_19_113004_add_is_slot_from_dispatch_to_products_table', 173),
(663, '2022_09_20_095856_create_client_preference_additional_table', 173),
(664, '2022_09_20_110637_create_vendor_multi_banners_table', 173),
(665, '2022_09_21_104226_add_is_show_dispatcher_agent_to_products_table', 173),
(666, '2022_09_23_101900_add_place_id_to_vendor_cities_table', 173),
(667, '2022_09_29_112757_drop_add_column_to_users_table', 173),
(668, '2022_09_29_113131_add_column_to_client_preferences_table', 173),
(669, '2022_09_28_095354_create_vendor_social_media_urls_table', 174),
(670, '2022_09_30_112502_create_long_term_services_table', 174),
(671, '2022_09_30_114632_create_long_term_service_products_table', 174),
(672, '2022_09_30_114836_create_long_term_service_translations_table', 174),
(673, '2022_10_04_095113_add_softdelete_to_long_term_services_table', 174),
(674, '2022_08_31_131306_add_inventory_category_id_column_to_categories_table', 175),
(675, '2022_09_06_090335_add_store_id_in_products_table', 175),
(676, '2022_09_21_091810_add_need_sync_with_order_to_vendors_table', 175),
(677, '2022_09_22_070311_add_expiry_date_to_product_variants_table', 175),
(678, '2022_09_22_095051_alter_table_client_preference_tax_price_type', 175),
(679, '2022_10_10_111645_create_product_variant_by_roles_table', 175),
(680, '2022_10_11_065809_add_enable_pricing_to_roles_table', 175),
(681, '2022_10_17_060709_create_category_roles_table', 175),
(682, '2022_10_19_093148_add_toll_price_in_order_vendor_products_table', 175),
(683, '2022_10_19_095926_create_vendor_min_amounts_table', 175),
(684, '2022_10_20_094634_add_individual_delivery_fee_to_products_table', 175),
(685, '2022_10_20_102236_alter_table_vendors_add_razorpay', 175),
(686, '2022_10_20_112846_create_product_by_roles_table', 175),
(687, '2022_10_21_052854_add_product_delivery_fee_to_cart_products_table', 175),
(688, '2022_10_21_075821_create_product_recently_viewed_table', 175),
(689, '2022_10_21_080230_add_product_delivery_fee_to_order_vendor_products_table', 175),
(690, '2022_10_30_165453_create_product_delivery_fee_by_roles_table', 175),
(691, '2022_11_08_090801_alter_table_sms_templates', 175),
(692, '2022_11_16_073349_add_fixed_service_charge_service_charge_amount_to_vendors_table', 176),
(693, '2022_11_16_104311_add_service_charge_amount_in_order_vendors_table', 176),
(694, '2022_11_16_121732_add_toll_amount_in_order_vendors_table', 176),
(695, '2022_11_16_124618_add_total_toll_amount_in_orders_table', 176),
(696, '2022_10_06_070210_create_long_term_service_product_addons_table', 177),
(697, '2022_10_06_074124_add_service_column_to_products_table', 177),
(698, '2022_10_26_120453_create_long_term_service_periods_table', 177),
(699, '2022_10_27_062023_add_long_term_timing_to_cart_products_table', 177),
(700, '2022_10_28_061432_create_order_long_term_services_table', 177),
(701, '2022_10_28_062012_create_order_long_term_services_addons_table', 177),
(702, '2022_10_28_063002_create_order_long_term_service_schedules_table', 177),
(703, '2022_10_31_095446_add_seats_seats_for_booking_to_products_table', 177),
(704, '2022_11_02_062503_add_is_long_term_to_orders_table', 177),
(705, '2022_11_02_064934_add_no_seats_for_pooling_and_is_cab_pooling_to_order_vendor_products_table', 177),
(706, '2022_11_02_122759_create_home_products_table', 177),
(707, '2022_11_03_064210_add_available_for_pooling_to_products_table', 177),
(708, '2022_11_03_102340_create_vehicle_emission_type_table', 177),
(709, '2022_11_03_102753_create_toll_pass_by_origin_table', 177),
(710, '2022_11_03_112635_create_travel_mode_table', 177),
(711, '2022_11_04_061646_add_toll_tax_related_fields_to_products_table', 177),
(712, '2022_11_08_130035_add_dispatcher_to_order_long_term_service_schedules_table', 177),
(713, '2022_11_09_065047_add_service_column_to_vendor_order_product_dispatcher_statuses_table', 177),
(714, '2022_11_10_092626_add_is_panel_auth_user_to_user_table', 177),
(715, '2022_11_14_095350_add_cancel_order_in_processing_to_vendors_table', 177),
(716, '2022_11_15_110439_add_vendor_reject_reason_to_order_cancel_requests_table', 177),
(717, '2022_11_15_123655_add_returnable_and_replaceable_to_products_table', 177),
(718, '2022_11_15_134636_add_return_auto_approve_to_vendors_table', 177),
(719, '2022_11_16_120425_create_vendor_additional_info_table', 177),
(720, '2022_11_16_132848_add_vendor_type_to_vendors_table', 177),
(721, '2022_11_17_073637_add_p2p_check_column_to_client_preferences_table', 177),
(722, '2022_11_17_074055_add_p2p_column_to_vendors_table', 177),
(723, '2022_11_17_115539_add_p2p_column_to_vendor_slots_table', 177),
(724, '2022_11_18_125426_create_exchange_reasons_table', 177),
(725, '2022_11_21_115419_create_attributes_table', 177),
(726, '2022_11_21_115505_create_attribute_categories_table', 177),
(727, '2022_11_21_115536_create_attribute_translations_table', 177),
(728, '2022_11_21_115555_create_attribute_options_table', 177),
(729, '2022_11_21_115630_create_attribute_option_translations_table', 177),
(730, '2022_11_21_131223_order_delivery_status_icon', 177),
(731, '2022_11_22_091846_add_type_to_return_reasons_table', 177),
(732, '2022_11_22_103108_create_product_attributes_table', 177),
(733, '2022_11_22_135644_add_return_reason_id_to_order_vendors_table', 177),
(734, '2022_11_23_071141_add_exchange_order_columns_to_order_vendors_table', 177),
(735, '2022_11_24_092949_add_return_reason_to_order_cancel_requests_table', 177),
(736, '2022_11_25_122601_add_type_to_order_return_requests_table', 177),
(737, '2022_11_29_104043_add_user_id_column_to_attributes_table', 177),
(738, '2022_11_30_052252_add_is_postpay_to_orders_table', 177),
(739, '2022_12_06_070742_add_return_days_to_products_table', 177),
(740, '2022_12_06_113943_add_column_delivery_response_to_order_vendors', 177),
(741, '2022_12_06_123319_altertable_add_new_delivery_options_type', 177),
(742, '2022_12_09_125420_add_tracking_order_phone_token_users_table', 177),
(743, '2022_12_12_051534_create_gift_cards_table', 177),
(744, '2022_12_12_111943_create_user_gift_cards_table', 177),
(745, '2022_12_12_113100_add_order_id_to_carts_table', 177),
(746, '2022_12_13_073001_add_reference_table_id_to_payments_table', 177),
(747, '2022_12_14_060503_add_is_edited_to_orders_table', 177),
(748, '2022_12_14_074800_add_gift_card_id_to_carts_table', 177),
(749, '2022_12_15_062254_add_gift_card_to_orders_table', 177),
(750, '2022_12_16_070536_add_type_to_cab_booking_layouts_table', 177),
(751, '2022_12_16_093647_add_pay_amount_in_carts_table', 177),
(752, '2022_12_16_095254_add_gift_card_code_to_user_gift_cards_table', 177),
(753, '2022_12_16_110837_add_user_gift_code_to_carts_table', 177),
(754, '2022_12_20_062406_add_user_gift_card_to_orders_table', 177),
(755, '2022_12_26_063021_create_cab_booking_layout_banners_table', 177),
(756, '2022_12_26_102633_create_processor_products_table', 177);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(757, '2022_12_27_063721_add_order_quantity_to_cart_products_table', 177),
(758, '2022_12_28_104109_add_subscription_discount_percent_to_vendors_table', 177),
(759, '2022_12_30_073749_add_subscription_discount_fields_to_order_vendors_table', 177),
(760, '2022_12_16_105104_create_bid_requests_table', 178),
(761, '2022_12_20_105356_create_bids_table', 178),
(762, '2022_12_21_091238_create_bid_products_table', 178),
(763, '2022_12_23_101644_add_vendor_bidding_discount_to_carts', 178),
(764, '2022_12_27_121459_add_description_to_bid_requests_table', 178),
(765, '2022_12_30_063929_add_bid_id_cart_products_and_order_table', 178),
(766, '2023_01_03_114239_add_coulmn_in_order_table', 178),
(767, '2022_12_02_101426_create_influencer_social_account_details_table', 179),
(768, '2022_12_05_091959_create_influencer_categories_table', 179),
(769, '2022_12_15_055747_create_influ_attributes_table', 179),
(770, '2022_12_15_055835_create_influ_attr_cat_table', 179),
(771, '2022_12_15_055923_create_influ_attr_trans_table', 179),
(772, '2022_12_15_060002_create_influ_attr_opt_table', 179),
(773, '2022_12_15_060026_create_influ_attr_opt_trans_table', 179),
(774, '2022_12_23_063313_create_influencer_tiers_table', 179),
(775, '2022_12_23_064050_create_influencer_users_table', 179),
(776, '2022_12_23_070817_create_influencer_initial_orders_discounts_table', 179),
(777, '2022_12_23_112555_create_refer_and_earn_details_table', 179),
(778, '2022_12_27_112246_add_promo_type_to_promocodes_table', 179),
(779, '2022_12_29_065017_add_kyc_to_influencer_categories_table', 179),
(780, '2022_12_29_074844_create_influencer_kyc_table', 179),
(781, '2023_01_05_164414_add_befor_order_admin_token_amount_for_orders', 180),
(782, '2023_01_05_165039_add_updated_price_colmn_to_order_vendor_products', 180),
(783, '2023_01_12_140639_add_p2p_to_vendor_slot_dates_table', 181),
(784, '2023_02_06_071127_update_spotlight_deals_in_product_table', 182),
(785, '2023_02_08_071904_add_product_id_field_in_home_page_table', 183),
(786, '2023_02_09_065922_add_multiple_column_to_product_attributes', 184),
(787, '2023_01_18_073358_add_url_link_to_banners_table', 185),
(788, '2023_01_18_095647_add_url_link_to_mobile_banners_table', 185),
(789, '2023_02_27_103917_add_p2picon_to_client_preferences_table', 186),
(790, '2023_02_16_133900_update__admin_vendor_rating_in_client_preference', 187),
(791, '2023_03_10_052903_tbl_user_data_vault_create_table', 188),
(792, '2023_03_10_115119_tbl_user-dat_vault_add_field_is_default', 188),
(793, '2023_03_16_055907_tbl_add_deleted_at_variant_options', 189),
(794, '2017_06_16_140051_create_nikolag_customers_table', 190),
(795, '2017_06_16_140942_create_nikolag_customer_user_table', 190),
(796, '2017_06_16_140943_create_nikolag_transactions_table', 190),
(797, '2018_02_07_140944_create_nikolag_taxes_table', 190),
(798, '2018_02_07_140945_create_nikolag_discounts_table', 190),
(799, '2018_02_07_140946_create_nikolag_deductible_table', 190),
(800, '2018_02_07_140947_create_nikolag_products_table', 190),
(801, '2018_02_07_140948_create_nikolag_orders_table', 190),
(802, '2018_02_07_140949_create_nikolag_product_order_table', 190),
(803, '2022_12_09_100718_add_multiple_column_to_vendors', 190),
(804, '2022_12_09_121244_add_cutoff_time_to_vendors_table', 190),
(805, '2022_12_09_125353_create_pincode_table', 190),
(806, '2022_12_09_131555_add_is_disabled_to_pincode_table', 190),
(807, '2022_12_12_090338_create_delivery_slots_table', 190),
(808, '2022_12_12_113000_add_multiple_column_to_products', 190),
(809, '2022_12_12_131440_create_delivery_slots_product_table', 190),
(810, '2022_12_13_080432_add_vendor_id_to_pincodes_table', 190),
(811, '2022_12_13_093506_create_pincode_delivery_options_table', 190),
(812, '2022_12_14_142348_add_multiple_column_to_cart_products', 190),
(813, '2022_12_15_115151_add_multiple_column_to_order_vendor_products', 190),
(814, '2022_12_20_103509_add_quantity_to_product_variant_by_roles', 190),
(815, '2022_12_21_125644_add_product_variant_by_role_id_to_cart_products_table', 190),
(816, '2023_01_02_112106_add_slot_interval_to_delivery_slots_table', 190),
(817, '2023_01_02_120349_add_parent_id_to_delivery_slots_table', 190),
(818, '2023_01_05_075537_add_recurring_booking_products_table', 190),
(819, '2023_01_09_075654_add_cut_off_time_to_delivery_slots_table', 190),
(820, '2023_01_09_104918_create_permission_tables', 190),
(821, '2023_01_10_063540_alter_table_role_orders', 190),
(822, '2023_01_10_064203_alter_table_users_role_permission', 190),
(823, '2023_01_12_063623_add_recurring_booking_cart_products_table', 190),
(824, '2023_01_13_090224_add_recurring_booking_orders_table', 190),
(825, '2023_01_13_103938_add_agent_price_to_cart_products_table', 190),
(826, '2023_01_16_103802_add_order_vendor_status_option_id_to_order_vendor_products_table', 190),
(827, '2023_01_17_062234_add_type_order_long_term_service_schedules_table', 190),
(828, '2023_01_17_092445_add_order_vendor_product_id_to_order_cancel_requests_table', 190),
(829, '2023_01_17_093952_create_vendor_order_product_statuses_table', 190),
(830, '2023_01_17_095603_add_order_number_order_long_term_service_schedules_table', 190),
(831, '2023_01_17_104116_add_reff_manager_id_in_table_vendors', 190),
(832, '2023_01_17_113028_add_order_vendor_product_id_to_vendor_order_product_statuses_table', 190),
(833, '2023_01_18_073449_add_security_amount_to_products_table', 190),
(834, '2023_01_18_131709_add_type_to_cab_booking_layout_banners_table', 190),
(835, '2023_01_18_142621_add_security_amount_to_order_vendor_products_table', 190),
(836, '2023_01_19_070847_add_is_vendor_instant_booking_to_vendors_table', 190),
(837, '2023_01_19_073503_add_is_product_instant_booking_to_products_table', 190),
(838, '2023_01_19_095412_add_is_one_push_booking_to_order_vendor_products_table', 190),
(839, '2023_01_20_070316_create_pick_drop_driver_bids_table', 190),
(840, '2023_01_23_093408_order_notifications_logs', 190),
(841, '2023_01_23_114625_create_order_product_dispatch_return_routes_table', 190),
(842, '2023_01_25_131937_add_is_delevery_send_to_dispatcher_to_orders_table', 190),
(843, '2023_02_01_064603_create_user_payment_cards_table', 190),
(844, '2023_02_02_075609_add_rented_product_count_to_product_variants_table', 190),
(845, '2023_02_03_063125_add_on_rent_to_product_bookings_table', 190),
(846, '2023_02_03_072325_add_product_pickup_date_to_products', 190),
(847, '2023_02_03_092131_add_order_vendor_product_id_to_product_bookings_table', 190),
(848, '2023_02_03_130135_add_order_id_to_product_bookings_table', 190),
(849, '2023_02_07_073951_create_user_bid_ride_request_table', 190),
(850, '2023_02_09_103859_add_controller_main_permission', 190),
(851, '2023_02_09_114435_add_dispatcher_status_to_vendor_order_product_statuses_table', 190),
(852, '2023_02_13_120308_add_type_coulmn_in_home_product_table', 190),
(853, '2023_02_15_114542_update_status_column', 190),
(854, '2023_02_17_042955_create_order_files_table', 190),
(855, '2023_02_17_074738_add_validate_pharmacy_check_to_products_table', 190),
(856, '2023_03_02_095036_create_user_ratings_table', 190),
(857, '2023_03_06_072542_add_is_price_buy_driver_to_order_vendor_products_table', 190),
(858, '2023_03_06_110420_add_fields_to_products_table', 190),
(859, '2023_03_06_110457_add_fields_to_product_variants_table', 190),
(860, '2023_03_06_115018_add_fields_to_tax_rates_table', 190),
(861, '2023_03_09_065427_add_specific_instruction_to_order_vendor_products_table', 190),
(862, '2023_03_09_133912_add_square_modifier_id_to_addon_sets_table', 190),
(863, '2023_03_09_134014_add_square_modifier_option_id_to_addon_options_table', 190),
(864, '2023_03_10_110439_add_fields_to_order_vendor_products_table', 190),
(865, '2023_03_14_103245_add_agent_to_temp_cart_products_table', 190),
(866, '2023_03_15_133717_add_order_amount_to_temp_carts_table', 190),
(867, '2023_03_16_061355_add_is_payment_done_to_temp_cart_products_table', 190),
(868, '2023_03_27_111216_create_square_timestamp_table', 191),
(869, '2023_03_29_130202_add_field_name_in_roles_table', 192),
(870, '2023_03_30_102040_add_compare_categories_to_vendor_info', 193),
(871, '2023_03_24_091355_add_order_count_to_subscription_plans_vendor_table', 194),
(872, '2023_03_24_100345_add_order_count_to_subscription_invoices_vendor_table', 194),
(873, '2023_03_27_045720_add_subscription_invoices_vendor_id_to_order_vendors_table', 194),
(874, '2023_04_03_070133_add_extra_time_to_order_vendors_table', 194),
(875, '2023_04_06_104441_create_user_registration_select_options_table', 194),
(876, '2023_04_06_104541_create_user_registration_select_option_translations_table', 194),
(877, '2023_05_11_075903_add_coulmn_order_tabls', 195),
(878, '2023_05_04_124622_add_sync_from_inventory_to_products_table', 196),
(879, '2023_05_15_095514_add_sync_inventory_side_cat_to_products_table', 196),
(880, '2023_05_24_094053_add_column_table_payments', 197),
(881, '2023_05_29_052738_add_is_cart_checked_to_cart_products_table', 198),
(882, '2023_06_06_100111_add_area_type_to_service_areas_table', 199),
(883, '2023_06_06_120117_alter_table_service_areas', 199),
(884, '2023_06_07_090530_add_country_code_to_service_areas_table', 199),
(885, '2023_06_09_063947_create_client_countries_table', 199),
(886, '2023_06_05_065845_create_copy_tools_table', 200),
(887, '2023_06_20_115005_tbl_vendors_change_order_per_slot_data_type', 201),
(888, '2023_06_08_093943_create_marg_products_table', 202),
(889, '2023_06_22_052446_add_marg_order_status_field_in_order_table', 202),
(890, '2023_03_31_071533_alter_shipping_delivery_type_table', 203),
(891, '2023_04_03_122801_add_roadie_tracking_url_to_order_vendors_table', 203),
(892, '2023_07_26_064222_alter_table_products', 204),
(893, '2023_07_25_094139_alter_order_add_column_marg_max_curl_attempt', 205);

-- --------------------------------------------------------

--
-- Table structure for table `mobile_banners`
--

CREATE TABLE `mobile_banners` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `image` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validity_on` tinyint NOT NULL DEFAULT '1' COMMENT '1 - yes, 0 - no',
  `sorting` tinyint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 - active, 0 - pending, 2 - blocked',
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `redirect_category_id` bigint UNSIGNED DEFAULT NULL,
  `redirect_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_banner_service_areas`
--

CREATE TABLE `mobile_banner_service_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `banner_id` bigint UNSIGNED DEFAULT NULL,
  `service_area_id` bigint UNSIGNED DEFAULT NULL COMMENT 'id from service area for banners',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(4, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 16),
(4, 'App\\Models\\User', 17);

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_customers`
--

CREATE TABLE `nikolag_customers` (
  `id` int UNSIGNED NOT NULL,
  `payment_service_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_service_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_customer_user`
--

CREATE TABLE `nikolag_customer_user` (
  `owner_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_deductibles`
--

CREATE TABLE `nikolag_deductibles` (
  `deductible_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deductible_id` bigint UNSIGNED NOT NULL,
  `featurable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `featurable_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_discounts`
--

CREATE TABLE `nikolag_discounts` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` double(8,2) DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `reference_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_orders`
--

CREATE TABLE `nikolag_orders` (
  `id` int UNSIGNED NOT NULL,
  `payment_service_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_service_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_products`
--

CREATE TABLE `nikolag_products` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variation_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` double(8,2) NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_product_order`
--

CREATE TABLE `nikolag_product_order` (
  `id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `order_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_taxes`
--

CREATE TABLE `nikolag_taxes` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` double(8,2) NOT NULL,
  `reference_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nikolag_transactions`
--

CREATE TABLE `nikolag_transactions` (
  `id` int UNSIGNED NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int UNSIGNED DEFAULT NULL,
  `payment_service_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_service_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nomenclatures`
--

CREATE TABLE `nomenclatures` (
  `id` bigint UNSIGNED NOT NULL,
  `label` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `nomenclatures_translations`
--

CREATE TABLE `nomenclatures_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `nomenclature_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `tags` mediumtext COLLATE utf8mb4_unicode_ci,
  `label` mediumtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `subject` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `slug`, `tags`, `label`, `content`, `subject`, `created_at`, `updated_at`) VALUES
(1, 'new-order', '', 'New Order', 'Thanks for your Order', 'New Vendor Signup', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(2, 'order-status-update', '', 'Order Status Update', 'Your Order status has been updated', 'Verify Mail', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(3, 'refund-status-update', '', 'Refund Status Update', 'Your Order status has been updated', 'Reset Password Notification', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(4, 'new-order-received', '', 'New Order Received (Owner)', 'You have received a new order', 'New Order Received', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(5, 'order-accepted', '{order_id}', 'Order Accepted (Customer)', 'Your order ({order_id}) has been accepted', 'Order Accepted', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(6, 'order-rejected', '{order_id}', 'Order Rejected (Customer)', 'Your order ({order_id}) has been rejected', 'Order Rejected', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(7, 'order-processing', '{order_id}', 'Order Processing (Customer)', 'Your order ({order_id}) has been processed', 'Order Processed', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(8, 'order-out-of-delivery', '{order_id}', 'Out of delivery (Customer)', 'Your order ({order_id}) has been reached to you soon', 'Out of delivery', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(9, 'order-delivered', '{order_id}', 'Order Delivered (Customer)', 'Your order ({order_id}) has delivered', 'Order Delivered', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(10, 'place-order-reminder', '', 'Place Order Reminder (Customer)', 'Place your order before it\'s too late', 'Don\'t wait too much', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(11, 'place-bid-request', '{prescription}', 'Place Bid Request (Customer)', 'Place your bid before it\'s too late {prescription}', 'You have new bid request', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(12, 'order-modified-customer', '{order_id}', 'Order Modified (Customer)', 'Your order ({order_id}) has been modified', 'Order Modified', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(13, 'order-delayed-customer', '{order_id}', 'Order Delayed (Customer)', 'Your order ({order_id}) has been Delayed', 'Order Delayed', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(14, 'pickup-delivery-customer', '{order_id}', 'Pickup Delivery Reminder', 'Your order ({order_id}) has been reached to you soon', 'Pickup Delivery', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(15, 'reached-vendor-location', '{order_id}', 'Reached Vendor Location', 'Location reached', 'Reached Vendor Location', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(16, 'order-out-for-takeaway-delivery', '{order_id}', 'Out of takeaway-delivery (Customer)', 'Your order ({order_id}) is ready for pickup', 'Out of takeaway-delivery', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(17, 'order-cancelled', '{order_id}', 'Order Cancelled', 'Your order ({order_id}) is canecelled by driver', 'Order Cancelled', '2023-06-27 11:45:27', '2023-06-27 11:45:27'),
(18, 'product-stock-vendor', '', 'Product Out Of Stock (Vendor)', 'Products are finishing! || You are running out of products!', 'Product Out Of Stock', '2023-06-27 11:45:27', '2023-06-27 11:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `onboard_settings`
--

CREATE TABLE `onboard_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_from` tinyint NOT NULL DEFAULT '1' COMMENT '1 : For GodPanel',
  `on_off` tinyint NOT NULL DEFAULT '0' COMMENT '0 : For off',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `payment_option_id` tinyint NOT NULL DEFAULT '1',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `address_id` bigint UNSIGNED DEFAULT NULL,
  `is_deleted` tinyint NOT NULL COMMENT '0-No, 1-Yes',
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `loyalty_membership_id` int UNSIGNED DEFAULT NULL,
  `luxury_option_id` int UNSIGNED DEFAULT NULL,
  `loyalty_points_used` decimal(10,2) DEFAULT NULL,
  `loyalty_amount_saved` decimal(16,8) DEFAULT NULL,
  `loyalty_points_earned` decimal(10,2) DEFAULT NULL,
  `paid_via_wallet` tinyint NOT NULL COMMENT '0-No, 1-Yes',
  `paid_via_loyalty` tinyint NOT NULL COMMENT '0-No, 1-Yes',
  `total_amount` decimal(16,8) DEFAULT NULL,
  `wallet_amount_used` decimal(16,8) NOT NULL DEFAULT '0.00000000',
  `subscription_discount` decimal(16,8) DEFAULT NULL,
  `total_discount` decimal(16,8) DEFAULT NULL,
  `total_delivery_fee` decimal(16,8) DEFAULT NULL,
  `taxable_amount` decimal(16,8) DEFAULT NULL,
  `tip_amount` decimal(16,8) DEFAULT '0.00000000',
  `payable_amount` decimal(16,8) DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_method` tinyint NOT NULL DEFAULT '1' COMMENT '1 - Credit Card, 2 - Cash On Delivery, 3 - Paypal, 4 - Wallet',
  `payment_status` tinyint DEFAULT '0' COMMENT '0=Pending, 1=Paid',
  `comment_for_pickup_driver` mediumtext COLLATE utf8mb4_unicode_ci,
  `comment_for_dropoff_driver` mediumtext COLLATE utf8mb4_unicode_ci,
  `comment_for_vendor` mediumtext COLLATE utf8mb4_unicode_ci,
  `schedule_pickup` datetime DEFAULT NULL,
  `schedule_dropoff` datetime DEFAULT NULL,
  `specific_instructions` text COLLATE utf8mb4_unicode_ci,
  `is_gift` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `total_service_fee` decimal(16,8) DEFAULT '0.00000000',
  `shipping_delivery_type` enum('D','L','S','SR','DU','M','SH','SHF','RO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_container_charges` decimal(12,4) DEFAULT '0.0000',
  `viva_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fixed_fee_amount` decimal(16,2) NOT NULL,
  `type` int DEFAULT '0' COMMENT '0=none, 1=cab book for friend',
  `friend_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `friend_phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_other_taxes` text COLLATE utf8mb4_unicode_ci COMMENT 'Other taxes like tax on fixed fee, service fee, container changes, delivery fee etc.',
  `dropoff_scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'dropoff slot for laundry',
  `user_latitude` decimal(20,16) DEFAULT NULL,
  `user_longitude` decimal(20,16) DEFAULT NULL,
  `additional_price` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `total_toll_amount` decimal(16,8) DEFAULT '0.00000000',
  `is_long_term` tinyint DEFAULT '0',
  `is_postpay` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_edited` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `gift_card_amount` decimal(16,8) DEFAULT '0.00000000',
  `gift_card_id` bigint UNSIGNED DEFAULT NULL,
  `gift_card_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bid_number` int DEFAULT NULL,
  `bid_discount` int DEFAULT NULL,
  `advance_amount` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `recurring_booking_type` tinyint DEFAULT NULL COMMENT '1=daily,2=weekly,3=monthly,4=custom',
  `recurring_week_day` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurring_week_type` tinyint DEFAULT NULL COMMENT '1=daily,2=once',
  `recurring_day_data` longtext COLLATE utf8mb4_unicode_ci,
  `recurring_booking_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_order_amount_send_to_dispatcher` tinyint NOT NULL DEFAULT '0' COMMENT '0=>no , 1=>yes',
  `old_payable_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_waiting_time` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_waiting_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `marg_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marg_max_attempt` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `created_by`, `order_number`, `scheduled_date_time`, `payment_option_id`, `user_id`, `address_id`, `is_deleted`, `currency_id`, `loyalty_membership_id`, `luxury_option_id`, `loyalty_points_used`, `loyalty_amount_saved`, `loyalty_points_earned`, `paid_via_wallet`, `paid_via_loyalty`, `total_amount`, `wallet_amount_used`, `subscription_discount`, `total_discount`, `total_delivery_fee`, `taxable_amount`, `tip_amount`, `payable_amount`, `tax_category_id`, `created_at`, `updated_at`, `payment_method`, `payment_status`, `comment_for_pickup_driver`, `comment_for_dropoff_driver`, `comment_for_vendor`, `schedule_pickup`, `schedule_dropoff`, `specific_instructions`, `is_gift`, `total_service_fee`, `shipping_delivery_type`, `scheduled_slot`, `total_container_charges`, `viva_order_id`, `fixed_fee_amount`, `type`, `friend_name`, `friend_phone_number`, `total_other_taxes`, `dropoff_scheduled_slot`, `user_latitude`, `user_longitude`, `additional_price`, `total_toll_amount`, `is_long_term`, `is_postpay`, `is_edited`, `gift_card_amount`, `gift_card_id`, `gift_card_code`, `bid_number`, `bid_discount`, `advance_amount`, `recurring_booking_type`, `recurring_week_day`, `recurring_week_type`, `recurring_day_data`, `recurring_booking_time`, `is_order_amount_send_to_dispatcher`, `old_payable_amount`, `total_waiting_time`, `total_waiting_price`, `marg_status`, `marg_max_attempt`) VALUES
(1, NULL, '49066009', NULL, 1, 1, 1, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '136.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '136.00000000', NULL, '2021-07-13 13:03:52', '2021-07-13 13:03:52', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(2, NULL, '62394271', NULL, 1, 1, 1, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '102.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '102.00000000', NULL, '2021-07-13 13:05:44', '2021-07-13 13:05:44', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(3, NULL, '07952976', NULL, 1, 1, 1, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '120.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '120.00000000', NULL, '2021-07-13 13:08:01', '2021-07-13 13:08:01', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(4, NULL, '98148365', NULL, 1, 1, 1, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '157.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '157.00000000', NULL, '2021-07-13 13:09:07', '2021-07-13 13:09:08', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(5, NULL, '32009728', NULL, 1, 1, 1, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '108.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '108.00000000', NULL, '2021-07-13 13:29:37', '2021-07-13 13:29:37', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(6, NULL, '96967493', NULL, 1, 2, 2, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '12.00000000', '0.00000000', NULL, '1.20000000', '0.00000000', '0.00000000', '0.00000000', '10.80000000', NULL, '2021-07-20 13:06:57', '2021-07-20 13:06:57', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(7, NULL, '10208058', NULL, 1, 3, 2, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '12.00000000', '0.00000000', NULL, '1.20000000', '0.00000000', '0.00000000', '0.00000000', '10.80000000', NULL, '2021-07-20 13:10:08', '2021-07-20 13:10:08', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(8, NULL, '26515346', NULL, 1, 7, 3, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '10.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '50.00000000', NULL, '2021-08-05 05:38:50', '2021-08-05 05:38:50', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(9, NULL, '46795478', NULL, 1, 7, 3, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '10.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '40.00000000', NULL, '2021-08-05 05:39:51', '2021-08-05 05:39:51', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(10, NULL, '34792506', NULL, 1, 7, 4, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '12.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '36.00000000', NULL, '2021-08-05 06:05:40', '2021-08-05 06:05:40', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(11, NULL, '35577410', NULL, 1, 7, 4, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '0.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', NULL, '2021-08-05 06:05:40', '2021-08-05 06:05:40', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(12, NULL, '65593399', NULL, 1, 8, 5, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '12.00000000', '0.00000000', NULL, '1.20000000', '0.00000000', '0.00000000', '0.00000000', '10.80000000', NULL, '2021-08-05 06:05:59', '2021-08-05 06:05:59', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(13, NULL, '25464038', NULL, 1, 9, 7, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '10.00000000', '0.00000000', NULL, '1.00000000', '0.00000000', '0.00000000', '0.00000000', '9.00000000', NULL, '2021-08-05 06:11:12', '2021-08-05 06:11:12', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(14, NULL, '51497808', NULL, 1, 9, 7, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '0.00000000', '0.00000000', NULL, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', NULL, '2021-08-05 06:11:12', '2021-08-05 06:11:12', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(15, NULL, '65890247', NULL, 1, 10, 9, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '20.00000000', '0.00000000', NULL, '1.00000000', '0.00000000', '0.00000000', '0.00000000', '19.00000000', NULL, '2021-08-05 06:32:50', '2021-08-05 06:32:50', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(16, NULL, '41962770', NULL, 1, 11, NULL, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '27.00000000', '0.00000000', NULL, '13.50000000', '0.00000000', '0.00000000', '0.00000000', '169.50000000', NULL, '2021-08-05 12:05:01', '2021-08-05 12:05:01', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(17, NULL, '55235082', NULL, 1, 13, 18, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '10.00000000', '0.00000000', '0.00000000', '5.00000000', '0.00000000', '0.00000000', '0.00000000', '45.00000000', NULL, '2021-08-12 11:46:45', '2021-08-12 11:46:45', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(18, NULL, '21433600', NULL, 1, 14, 19, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '12.00000000', '0.00000000', '0.00000000', '4.80000000', '0.00000000', '0.00000000', '0.00000000', '43.20000000', NULL, '2021-08-23 07:19:32', '2021-08-23 07:19:32', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(19, NULL, '81986788', NULL, 1, 14, 19, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', NULL, '2021-08-23 07:19:32', '2021-08-23 07:19:32', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0),
(20, NULL, '30898845', NULL, 1, 15, 20, 0, NULL, 0, NULL, '0.00', '0.00000000', '0.00', 0, 0, '12.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '12.00000000', NULL, '2021-08-23 07:25:24', '2021-08-23 07:25:24', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, '0.00000000', 'D', NULL, '0.0000', NULL, '0.00', 0, NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00000000', 0, 0, 0, '0.00000000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, 0, '0.00', '0.00', '0.00', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_cancel_requests`
--

CREATE TABLE `order_cancel_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `reject_reason` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint DEFAULT '0' COMMENT '0=Pending, 1=Approved, 2=Rejected',
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_reject_reason` text COLLATE utf8mb4_unicode_ci,
  `return_reason_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_product_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_delivery_status_icon`
--

CREATE TABLE `order_delivery_status_icon` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_delivery_status_icon`
--

INSERT INTO `order_delivery_status_icon` (`id`, `name`, `image`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Step 1', 'assets/icons/driver_1_1.png', NULL, NULL, NULL),
(2, 'Step 2', 'assets/icons/driver_2_1.png', NULL, NULL, NULL),
(3, 'Step 3', 'assets/icons/driver_4_1.png', NULL, NULL, NULL),
(4, 'Step 4', 'assets/icons/driver_3_1.png', NULL, NULL, NULL),
(5, 'Step 5', 'assets/icons/driver_4_2.png', NULL, NULL, NULL),
(6, 'Step 6', 'assets/icons/driver_5_1.png', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_driver_ratings`
--

CREATE TABLE `order_driver_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `dispatcher_driver_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `review` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_files`
--

CREATE TABLE `order_files` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `cart_id` bigint UNSIGNED DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_locations`
--

CREATE TABLE `order_locations` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `phone_number` bigint UNSIGNED DEFAULT NULL,
  `email` bigint UNSIGNED DEFAULT NULL,
  `tasks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_long_term_services`
--

CREATE TABLE `order_long_term_services` (
  `id` bigint UNSIGNED NOT NULL,
  `order_product_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `service_quentity` bigint UNSIGNED DEFAULT NULL,
  `service_day` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_date` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_period` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_start_date` datetime DEFAULT NULL,
  `service_end_date` datetime DEFAULT NULL,
  `service_product_id` bigint UNSIGNED NOT NULL,
  `service_product_variant_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0-not accept, 1-accept',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_long_term_services_addons`
--

CREATE TABLE `order_long_term_services_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `order_long_term_services_id` bigint UNSIGNED DEFAULT NULL,
  `addon_id` bigint UNSIGNED DEFAULT NULL,
  `option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_long_term_service_schedules`
--

CREATE TABLE `order_long_term_service_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `order_long_term_services_id` bigint UNSIGNED DEFAULT NULL,
  `schedule_date` datetime DEFAULT NULL,
  `status` tinyint DEFAULT '0' COMMENT '0-not completed, 1-completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `web_hook_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'dispatcher weweb_hook_codev  dispatch',
  `dispatch_traking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'product dispatch',
  `dispatcher_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'product dispatch',
  `order_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'product dispatch',
  `type` tinyint DEFAULT NULL COMMENT '1=Longterm Service,2=Recurring Service',
  `order_vendor_product_id` bigint UNSIGNED DEFAULT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_notifications_logs`
--

CREATE TABLE `order_notifications_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` int DEFAULT NULL,
  `order_vendor_id` int DEFAULT NULL,
  `order_number` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_seen` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_product_addons`
--

CREATE TABLE `order_product_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `order_product_id` bigint UNSIGNED NOT NULL,
  `addon_id` bigint UNSIGNED NOT NULL,
  `option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_product_dispatch_return_routes`
--

CREATE TABLE `order_product_dispatch_return_routes` (
  `id` bigint UNSIGNED NOT NULL COMMENT 'id based on product quantity',
  `order_id` bigint UNSIGNED NOT NULL,
  `order_vendor_id` bigint UNSIGNED NOT NULL,
  `order_vendor_product_id` bigint UNSIGNED NOT NULL,
  `order_product_route_id` bigint UNSIGNED NOT NULL,
  `web_hook_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `dispatch_traking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `dispatcher_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `order_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_product_dispatch_routes`
--

CREATE TABLE `order_product_dispatch_routes` (
  `id` bigint UNSIGNED NOT NULL COMMENT 'id based on product quantity',
  `order_id` bigint UNSIGNED NOT NULL,
  `order_vendor_id` bigint UNSIGNED NOT NULL,
  `order_vendor_product_id` bigint UNSIGNED NOT NULL,
  `order_product_route_id` bigint UNSIGNED NOT NULL,
  `web_hook_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `dispatch_traking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `dispatcher_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `order_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_product_prescriptions`
--

CREATE TABLE `order_product_prescriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `prescription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_product_ratings`
--

CREATE TABLE `order_product_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `order_vendor_product_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `rating` decimal(4,2) DEFAULT NULL,
  `review` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_product_rating_files`
--

CREATE TABLE `order_product_rating_files` (
  `id` bigint UNSIGNED NOT NULL,
  `order_product_rating_id` bigint UNSIGNED NOT NULL,
  `file` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_qrcode_links`
--

CREATE TABLE `order_qrcode_links` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` int NOT NULL,
  `qrcode_id` int NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_refunds`
--

CREATE TABLE `order_refunds` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `payment_id` bigint UNSIGNED NOT NULL,
  `payment_option_id` bigint UNSIGNED NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `webhook_payload` longtext COLLATE utf8mb4_unicode_ci,
  `paid_to_wallet` tinyint NOT NULL DEFAULT '0' COMMENT '0=no 1=yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_return_requests`
--

CREATE TABLE `order_return_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `order_vendor_product_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `return_by` bigint UNSIGNED NOT NULL,
  `reason` varchar(220) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coments` text COLLATE utf8mb4_unicode_ci,
  `reason_by_vendor` mediumtext COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','Accepted','Rejected','On-Hold','Completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 = return, 2 = exchange',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_return_request_files`
--

CREATE TABLE `order_return_request_files` (
  `id` bigint UNSIGNED NOT NULL,
  `order_return_request_id` bigint UNSIGNED NOT NULL,
  `file` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_options`
--

CREATE TABLE `order_status_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '1 - for order, 2 - fordispatch',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '1 - active, 0 - inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_status_options`
--

INSERT INTO `order_status_options` (`id`, `title`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Placed', 1, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(2, 'Accepted', 1, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(3, 'Rejected', 1, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(4, 'Processing', 1, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(5, 'Out For Delivery', 1, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(6, 'Delivered', 1, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(7, 'Accept', 2, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08'),
(8, 'Reject', 2, 1, '2021-07-13 10:41:08', '2021-07-13 10:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_taxes`
--

CREATE TABLE `order_taxes` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `tax_category_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_vendors`
--

CREATE TABLE `order_vendors` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_dinein_table_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `delivery_fee` decimal(16,8) DEFAULT NULL,
  `status` tinyint NOT NULL COMMENT '0-Created, 1-Confirmed, 2-Dispatched, 3-Delivered',
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taxable_amount` decimal(16,8) DEFAULT NULL,
  `subtotal_amount` decimal(16,8) DEFAULT NULL,
  `payable_amount` decimal(16,8) DEFAULT NULL,
  `discount_amount` decimal(16,8) DEFAULT NULL,
  `web_hook_code` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_commission_percentage_amount` decimal(16,8) DEFAULT NULL,
  `admin_commission_fixed_amount` decimal(16,8) DEFAULT NULL,
  `coupon_paid_by` tinyint DEFAULT '1' COMMENT '0-Vendor, 1-Admin',
  `payment_option_id` tinyint DEFAULT NULL,
  `dispatcher_status_option_id` tinyint UNSIGNED DEFAULT NULL,
  `order_status_option_id` tinyint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `dispatch_traking_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_pre_time` int UNSIGNED DEFAULT '0',
  `user_to_vendor_time` int UNSIGNED DEFAULT '0',
  `reject_reason` mediumtext COLLATE utf8mb4_unicode_ci,
  `service_fee_percentage_amount` decimal(16,8) DEFAULT '0.00000000',
  `cancelled_by` bigint UNSIGNED DEFAULT NULL,
  `lalamove_tracking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_delivery_type` enum('D','L','S','SR','DU','M','SH','SHF','RO') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `courier_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_shipment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_awb_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_container_charges` decimal(12,4) DEFAULT '0.0000',
  `accepted_by` bigint UNSIGNED DEFAULT NULL,
  `driver_id` int DEFAULT NULL COMMENT 'lalamove driver id',
  `scheduled_date_time` datetime DEFAULT NULL,
  `schedule_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_restricted` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `total_markup_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fixed_fee` decimal(12,4) DEFAULT NULL,
  `additional_price` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `fixed_service_charge_amount` decimal(16,8) DEFAULT '0.00000000',
  `toll_amount` decimal(16,8) DEFAULT '0.00000000',
  `return_reason_id` bigint UNSIGNED DEFAULT NULL,
  `is_exchanged_or_returned` tinyint NOT NULL DEFAULT '0' COMMENT '1 = exchanged, 2 = returned',
  `exchange_order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_response` json DEFAULT NULL,
  `subscription_discount_admin` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subscription_discount_vendor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bid_discount` int DEFAULT NULL,
  `subscription_invoices_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `extra_time` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waiting_time` decimal(10,2) NOT NULL DEFAULT '0.00',
  `waiting_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `roadie_tracking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `order_vendors`
--

INSERT INTO `order_vendors` (`id`, `order_id`, `vendor_id`, `vendor_dinein_table_id`, `user_id`, `delivery_fee`, `status`, `coupon_id`, `coupon_code`, `taxable_amount`, `subtotal_amount`, `payable_amount`, `discount_amount`, `web_hook_code`, `admin_commission_percentage_amount`, `admin_commission_fixed_amount`, `coupon_paid_by`, `payment_option_id`, `dispatcher_status_option_id`, `order_status_option_id`, `created_at`, `updated_at`, `dispatch_traking_url`, `order_pre_time`, `user_to_vendor_time`, `reject_reason`, `service_fee_percentage_amount`, `cancelled_by`, `lalamove_tracking_url`, `shipping_delivery_type`, `courier_id`, `ship_order_id`, `ship_shipment_id`, `ship_awb_id`, `total_container_charges`, `accepted_by`, `driver_id`, `scheduled_date_time`, `schedule_slot`, `is_restricted`, `total_markup_price`, `fixed_fee`, `additional_price`, `fixed_service_charge_amount`, `toll_amount`, `return_reason_id`, `is_exchanged_or_returned`, `exchange_order_vendor_id`, `delivery_response`, `subscription_discount_admin`, `subscription_discount_vendor`, `bid_discount`, `subscription_invoices_vendor_id`, `extra_time`, `waiting_time`, `waiting_price`, `roadie_tracking_url`) VALUES
(1, 1, 6, NULL, 1, NULL, 0, NULL, NULL, '0.00000000', '136.00000000', '136.00000000', '0.00000000', NULL, '13.60000000', '13.00000000', 1, 1, NULL, 1, '2021-07-13 13:03:52', '2021-07-13 13:03:52', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(2, 2, 6, NULL, 1, NULL, 0, NULL, NULL, '0.00000000', '102.00000000', '102.00000000', '0.00000000', NULL, '10.20000000', '13.00000000', 1, 1, NULL, 1, '2021-07-13 13:05:44', '2021-07-13 13:05:44', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(3, 3, 6, NULL, 1, NULL, 0, NULL, NULL, '0.00000000', '120.00000000', '120.00000000', '0.00000000', NULL, '12.00000000', '13.00000000', 1, 1, NULL, 1, '2021-07-13 13:08:01', '2021-07-13 13:08:01', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(4, 4, 6, NULL, 1, NULL, 0, NULL, NULL, '0.00000000', '157.00000000', '157.00000000', '0.00000000', NULL, '15.70000000', '13.00000000', 1, 1, NULL, 1, '2021-07-13 13:09:07', '2021-07-13 13:09:08', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(5, 5, 6, NULL, 1, NULL, 0, NULL, NULL, '0.00000000', '108.00000000', '108.00000000', '0.00000000', NULL, '10.80000000', '13.00000000', 1, 1, NULL, 1, '2021-07-13 13:29:37', '2021-07-13 13:29:37', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(6, 6, 4, NULL, 2, NULL, 0, 2, 'xqe4f3', '0.00000000', '12.00000000', '10.80000000', '1.20000000', NULL, '2.16000000', '10.00000000', 1, 1, NULL, 6, '2021-07-20 13:06:57', '2021-07-20 13:08:20', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(7, 7, 5, NULL, 3, NULL, 0, 2, 'xqe4f3', '0.00000000', '12.00000000', '10.80000000', '1.20000000', NULL, '1.62000000', '12.00000000', 1, 1, NULL, 6, '2021-07-20 13:10:08', '2021-07-20 13:12:16', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(8, 8, 6, NULL, 7, '0.00000000', 0, NULL, NULL, '0.00000000', '50.00000000', '50.00000000', '0.00000000', NULL, '5.00000000', '13.00000000', 1, 1, NULL, 1, '2021-08-05 05:38:50', '2021-08-05 05:38:50', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(9, 9, 4, NULL, 7, '0.00000000', 0, NULL, NULL, '0.00000000', '40.00000000', '40.00000000', '0.00000000', NULL, '8.00000000', '10.00000000', 1, 1, NULL, 1, '2021-08-05 05:39:51', '2021-08-05 05:39:51', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(10, 10, 3, NULL, 7, '0.00000000', 0, NULL, NULL, '0.00000000', '36.00000000', '36.00000000', '0.00000000', NULL, '5.40000000', '15.00000000', 1, 1, NULL, 1, '2021-08-05 06:05:40', '2021-08-05 06:05:40', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(11, 12, 2, NULL, 8, '0.00000000', 0, 1, '50UYDGF', '0.00000000', '12.00000000', '10.80000000', '1.20000000', NULL, '1.08000000', '10.00000000', 1, 1, NULL, 1, '2021-08-05 06:05:59', '2021-08-05 06:05:59', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(12, 13, 3, NULL, 9, '0.00000000', 0, 1, '50UYDGF', '0.00000000', '10.00000000', '9.00000000', '1.00000000', NULL, '1.35000000', '15.00000000', 1, 1, NULL, 1, '2021-08-05 06:11:12', '2021-08-05 06:11:12', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(13, 15, 3, NULL, 10, '0.00000000', 0, 2, '20DFRCV', '0.00000000', '20.00000000', '19.00000000', '1.00000000', NULL, '2.85000000', '15.00000000', 1, 1, NULL, 1, '2021-08-05 06:32:50', '2021-08-05 06:32:50', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(14, 16, 5, NULL, 11, '0.00000000', 0, 1, '50UYDGF', '0.00000000', '183.00000000', '169.50000000', '13.50000000', NULL, '25.43000000', '12.00000000', 1, 1, NULL, 1, '2021-08-05 12:05:01', '2021-08-05 12:05:01', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(15, 17, 3, NULL, 13, '0.00000000', 0, 1, '50UYDGF', '0.00000000', '50.00000000', '45.00000000', '5.00000000', NULL, '6.75000000', '15.00000000', 1, 1, NULL, 1, '2021-08-12 11:46:45', '2021-08-12 11:46:45', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(16, 18, 5, NULL, 14, '0.00000000', 0, 2, '20DFRCV', '0.00000000', '48.00000000', '43.20000000', '4.80000000', NULL, '6.48000000', '12.00000000', 1, 1, NULL, 1, '2021-08-23 07:19:32', '2021-08-23 07:19:32', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL),
(17, 20, 5, NULL, 15, '0.00000000', 0, NULL, NULL, '0.00000000', '12.00000000', '12.00000000', '0.00000000', NULL, '1.80000000', '12.00000000', 1, 1, NULL, 1, '2021-08-23 07:25:24', '2021-08-23 07:25:24', NULL, 0, 0, NULL, '0.00000000', NULL, NULL, 'D', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, 0, '0.00', NULL, '0.0000', '0.00000000', '0.00000000', NULL, 0, NULL, NULL, '0.00', '0.00', NULL, NULL, NULL, '0.00', '0.00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_vendor_accounting`
--

CREATE TABLE `order_vendor_accounting` (
  `id` bigint UNSIGNED NOT NULL,
  `order_vendor_id` bigint UNSIGNED NOT NULL,
  `third_party_accounting_id` int UNSIGNED NOT NULL,
  `invoice_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_vendor_products`
--

CREATE TABLE `order_vendor_products` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(16,8) DEFAULT NULL,
  `taxable_amount` decimal(16,8) DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `product_dispatcher_tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schedule_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `product_variant_sets` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_product_order_form` text COLLATE utf8mb4_unicode_ci,
  `container_charges` decimal(12,4) DEFAULT '0.0000',
  `markup_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `additional_increments_hrs_min` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `incremental_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `total_booking_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT 'in min',
  `schedule_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dispatch_agent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'driver id',
  `toll_price` decimal(16,8) DEFAULT '0.00000000',
  `product_delivery_fee` decimal(16,8) DEFAULT '0.00000000',
  `no_seats_for_pooling` tinyint DEFAULT '0',
  `is_cab_pooling` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `bid_number` int DEFAULT NULL,
  `bid_discount` int DEFAULT NULL,
  `old_price` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `updated_price_reason` text COLLATE utf8mb4_unicode_ci,
  `slot_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `slot_price` decimal(12,2) DEFAULT NULL,
  `order_vendor_status_option_id` bigint UNSIGNED DEFAULT NULL,
  `security_amount` decimal(10,2) DEFAULT NULL,
  `is_one_push_booking` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_price_buy_driver` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `specific_instruction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_status_option_id` tinyint DEFAULT NULL,
  `dispatcher_status_option_id` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `order_vendor_products`
--

INSERT INTO `order_vendor_products` (`id`, `order_id`, `product_id`, `order_vendor_id`, `quantity`, `product_name`, `image`, `price`, `taxable_amount`, `vendor_id`, `created_by`, `variant_id`, `tax_category_id`, `created_at`, `updated_at`, `category_id`, `product_dispatcher_tag`, `schedule_type`, `scheduled_date_time`, `product_variant_sets`, `user_product_order_form`, `container_charges`, `markup_price`, `additional_increments_hrs_min`, `start_date_time`, `end_date_time`, `incremental_price`, `total_booking_time`, `schedule_slot`, `dispatch_agent_id`, `toll_price`, `product_delivery_fee`, `no_seats_for_pooling`, `is_cab_pooling`, `bid_number`, `bid_discount`, `old_price`, `updated_price_reason`, `slot_id`, `delivery_date`, `slot_price`, `order_vendor_status_option_id`, `security_amount`, `is_one_push_booking`, `is_price_buy_driver`, `specific_instruction`, `order_status_option_id`, `dispatcher_status_option_id`) VALUES
(1, 1, 2, 1, 17, 'GS100', 'prods/a0lRm1RR4VqafeanGoUD1mjr6PhAelhLIT3KTz29.jpg', '8.00000000', '0.00000000', 6, NULL, 6, NULL, '2021-07-13 13:03:52', '2021-07-13 13:03:52', 3, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(2, 2, 3, 2, 17, 'GS101', 'prods/jtxnzniTMWs0LLsiPDIHrtITaqWW8uSebTxzvopM.jpg', '6.00000000', '0.00000000', 6, NULL, 7, NULL, '2021-07-13 13:05:44', '2021-07-13 13:05:44', 3, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(3, 3, 3, 3, 20, 'GS101', 'prods/jtxnzniTMWs0LLsiPDIHrtITaqWW8uSebTxzvopM.jpg', '6.00000000', '0.00000000', 6, NULL, 7, NULL, '2021-07-13 13:08:01', '2021-07-13 13:08:01', 3, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(4, 4, 2, 4, 18, 'GS100', 'prods/a0lRm1RR4VqafeanGoUD1mjr6PhAelhLIT3KTz29.jpg', '8.00000000', '0.00000000', 6, NULL, 6, NULL, '2021-07-13 13:09:08', '2021-07-13 13:09:08', 3, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(5, 4, 8, 4, 1, 'GS106', '', '13.00000000', '0.00000000', 6, NULL, 12, NULL, '2021-07-13 13:09:08', '2021-07-13 13:09:08', 3, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(6, 5, 3, 5, 18, 'GS101', 'prods/jtxnzniTMWs0LLsiPDIHrtITaqWW8uSebTxzvopM.jpg', '6.00000000', '0.00000000', 6, NULL, 7, NULL, '2021-07-13 13:29:37', '2021-07-13 13:29:37', 3, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(7, 6, 21, 6, 1, 'CH501', 'prods/vTkX8Dc6anvoYxHOArf967PqwPmnAK605YTvjxrH.jpg', '12.00000000', NULL, 4, NULL, 25, NULL, '2021-07-20 13:06:57', '2021-07-20 13:06:57', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(8, 7, 15, 7, 1, 'IT300', 'prods/GK2UA8sFePq6ncPA5CabVSvBR2W0Bkg3jFUmaApF.jpg', '12.00000000', NULL, 5, NULL, 19, NULL, '2021-07-20 13:10:08', '2021-07-20 13:10:08', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(9, 8, 10, 8, 5, 'Poached Pear Salad', 'prods/cPEl5Z6XzvpBMgzKXEpxmijIeTdRAPUFJNZKYGpE.jpg', '10.00000000', NULL, 6, NULL, 14, NULL, '2021-08-05 05:38:50', '2021-08-05 05:38:50', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(10, 9, 24, 9, 4, 'Spring Rolls', 'prods/M0fuSUeBRzXSOWaIlwnIBQJ0OPkmsNVAZMfVwAGe.jpg', '10.00000000', NULL, 4, NULL, 28, NULL, '2021-08-05 05:39:51', '2021-08-05 05:39:51', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(11, 10, 28, 10, 3, 'Chocolate mousse', 'prods/DDSQFDnIvZJNEcm9ZWmcf3w96RWWM6Vi6ayGG69Y.jpg', '12.00000000', NULL, 3, NULL, 32, NULL, '2021-08-05 06:05:40', '2021-08-05 06:05:40', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(12, 12, 40, 11, 1, 'Hot chocolate', 'prods/4946p3x2WDCeL7M7NyiraSgcFfO2KGjubzq73IRC.jpg', '12.00000000', NULL, 2, NULL, 44, NULL, '2021-08-05 06:05:59', '2021-08-05 06:05:59', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(13, 13, 27, 12, 1, 'Almond and date cake', 'prods/xBAo0XfIafwpSOAXVpyNbkvD4JjCFSjYdUWuQOTV.jpg', '10.00000000', NULL, 3, NULL, 31, NULL, '2021-08-05 06:11:12', '2021-08-05 06:11:12', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(14, 15, 27, 13, 1, 'Almond and date cake', 'prods/xBAo0XfIafwpSOAXVpyNbkvD4JjCFSjYdUWuQOTV.jpg', '10.00000000', NULL, 3, NULL, 31, NULL, '2021-08-05 06:32:50', '2021-08-05 06:32:50', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(15, 15, 30, 13, 1, 'Apple cinnamon custard cake', '', '10.00000000', NULL, 3, NULL, 34, NULL, '2021-08-05 06:32:50', '2021-08-05 06:32:50', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(16, 16, 15, 14, 4, 'Margherita pizza', 'prods/Y2r9DC6bZFu1Cg3v5ky0Wfq6lqKMBHkzK3VDepWk.jpg', '12.00000000', NULL, 5, NULL, 19, NULL, '2021-08-05 12:05:01', '2021-08-05 12:05:01', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(17, 16, 16, 14, 9, 'Mushroom Risotto', '', '15.00000000', NULL, 5, NULL, 20, NULL, '2021-08-05 12:05:01', '2021-08-05 12:05:01', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(18, 17, 27, 15, 5, 'Almond and date cake', 'prods/xBAo0XfIafwpSOAXVpyNbkvD4JjCFSjYdUWuQOTV.jpg', '10.00000000', NULL, 3, NULL, 31, NULL, '2021-08-12 11:46:45', '2021-08-12 11:46:45', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(19, 18, 15, 16, 4, 'Margherita pizza', 'prods/Y2r9DC6bZFu1Cg3v5ky0Wfq6lqKMBHkzK3VDepWk.jpg', '12.00000000', NULL, 5, NULL, 19, NULL, '2021-08-23 07:19:32', '2021-08-23 07:19:32', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(20, 20, 15, 17, 1, 'Margherita pizza', 'prods/Y2r9DC6bZFu1Cg3v5ky0Wfq6lqKMBHkzK3VDepWk.jpg', '12.00000000', NULL, 5, NULL, 19, NULL, '2021-08-23 07:25:24', '2021-08-23 07:25:24', NULL, NULL, NULL, NULL, NULL, NULL, '0.0000', '0.00', '0', NULL, NULL, '0', '0', NULL, NULL, '0.00000000', '0.00000000', 0, 0, NULL, NULL, '0.0000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_vendor_reports`
--

CREATE TABLE `order_vendor_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `report` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_by` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `created_at`, `updated_at`, `order_by`) VALUES
(1, 'privacy-policy', '2021-08-13 11:33:36', '2021-08-13 11:33:36', 0),
(2, 'terms-conditions', '2021-08-13 11:33:36', '2021-08-13 11:33:36', 0),
(3, 'vendor-registration', '2021-08-13 11:33:36', '2021-08-13 11:33:36', 0);

-- --------------------------------------------------------

--
-- Table structure for table `page_translations`
--

CREATE TABLE `page_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `meta_title` mediumtext COLLATE utf8mb4_unicode_ci,
  `meta_keyword` mediumtext COLLATE utf8mb4_unicode_ci,
  `meta_description` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_published` tinyint NOT NULL DEFAULT '0' COMMENT '0 draft and 1 for published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type_of_form` tinyint NOT NULL DEFAULT '0' COMMENT '0 for none; 1 for vendor registration; 2 for driver registration;'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `page_translations`
--

INSERT INTO `page_translations` (`id`, `title`, `description`, `page_id`, `language_id`, `meta_title`, `meta_keyword`, `meta_description`, `is_published`, `created_at`, `updated_at`, `type_of_form`) VALUES
(1, 'Privacy Policy', 'We provide Visitors (as defined below) with access to the Website and Registered Members (as defined below) with access to the Platform subject to the following Terms of Use. By browsing the public areas of the Website, you acknowledge that you have read, understood, and agree to be legally bound by these Terms of Use and our Privacy Policy, which is hereby incorporated by reference (collectively, this “Agreement”). If you do not agree to any of these terms, then please do not use the Website, the App, and/or the Platform. We may change the terms and conditions of these Terms of Use from time to time with or without notice to you.', 1, 1, NULL, NULL, NULL, 1, '2021-08-13 11:33:36', '2022-02-07 10:35:27', 4),
(2, 'Terms & Conditions', 'We provide Visitors (as defined below) with access to the Website and Registered Members (as defined below) with access to the Platform subject to the following Terms of Use. By browsing the public areas of the Website, you acknowledge that you have read, understood, and agree to be legally bound by these Terms of Use and our Privacy Policy, which is hereby incorporated by reference (collectively, this “Agreement”). If you do not agree to any of these terms, then please do not use the Website, the App, and/or the Platform. We may change the terms and conditions of these Terms of Use from time to time with or without notice to you.', 2, 1, NULL, NULL, NULL, 1, '2021-08-13 11:33:36', '2022-02-07 10:35:27', 5),
(3, 'Vendor Registration', 'We provide Visitors (as defined below) with access to the Website and Registered Members (as defined below) with access to the Platform subject to the following Terms of Use. By browsing the public areas of the Website, you acknowledge that you have read, understood, and agree to be legally bound by these Terms of Use and our Privacy Policy, which is hereby incorporated by reference (collectively, this “Agreement”). If you do not agree to any of these terms, then please do not use the Website, the App, and/or the Platform. We may change the terms and conditions of these Terms of Use from time to time with or without notice to you.', 3, 1, NULL, NULL, NULL, 1, '2021-08-13 11:33:36', '2022-02-07 10:35:27', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance_transaction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `cart_id` bigint UNSIGNED DEFAULT NULL,
  `user_subscription_invoice_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_subscription_invoice_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `payment_option_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `viva_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_verified` tinyint DEFAULT '0',
  `payment_from` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_table_id` bigint UNSIGNED DEFAULT NULL,
  `payment_detail` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_show` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `image`, `slug`, `is_show`, `created_at`, `updated_at`) VALUES
(1, 'Visa', 'visa.png', 'visa', 1, NULL, NULL),
(2, 'Discover', 'discover.png', 'discover', 1, NULL, NULL),
(3, 'American Express', 'american-express.png', 'american-express', 1, NULL, NULL),
(4, 'Master Cart', 'master.png', 'master', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_options`
--

CREATE TABLE `payment_options` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` json DEFAULT NULL COMMENT 'credentials in json format',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 inactive, 1 active, 2 delete',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `off_site` tinyint UNSIGNED DEFAULT '0' COMMENT '0 = on-site, 1 = off-site',
  `test_mode` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = false, 1 = true'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `payment_options`
--

INSERT INTO `payment_options` (`id`, `code`, `path`, `title`, `credentials`, `status`, `created_at`, `updated_at`, `off_site`, `test_mode`) VALUES
(1, 'cod', '', 'Cash On Delivery', NULL, 0, NULL, NULL, 0, 0),
(3, 'paypal', 'omnipay/paypal', 'PayPal', NULL, 0, NULL, NULL, 1, 0),
(4, 'stripe', 'omnipay/stripe', 'Stripe', NULL, 0, NULL, NULL, 0, 0),
(5, 'paystack', 'paystackhq/omnipay-paystack', 'Paystack', NULL, 0, NULL, NULL, 1, 0),
(6, 'payfast', 'omnipay/payfast', 'Payfast', NULL, 0, NULL, NULL, 1, 0),
(7, 'mobbex', 'mobbex/sdk', 'Mobbex', NULL, 0, NULL, NULL, 1, 0),
(8, 'yoco', 'yoco/yoco-php-laravel', 'Yoco', NULL, 0, NULL, NULL, 1, 0),
(9, 'paylink', 'paylink/paylink', 'Paylink', NULL, 0, NULL, NULL, 1, 0),
(10, 'razorpay', 'razorpay/razorpay', 'Razorpay', NULL, 0, NULL, NULL, 0, 0),
(11, 'gcash', 'adyen/php-api-library', 'GCash', NULL, 0, NULL, NULL, 1, 0),
(12, 'simplify', 'rak/simplify', 'Simplify', NULL, 0, NULL, NULL, 1, 0),
(13, 'square', 'square/square', 'Square', NULL, 0, NULL, NULL, 1, 0),
(14, 'ozow', 'tradesafe/omnipay-ozow', 'Ozow', NULL, 0, NULL, NULL, 1, 0),
(15, 'pagarme', 'pagarme/pagarme-php', 'Pagarme', NULL, 0, '2022-01-06 13:40:26', '2022-01-06 13:40:26', 1, 0),
(17, 'checkout', 'checkout/checkout-sdk-php', 'Checkout', NULL, 0, '2022-01-12 10:25:05', '2022-01-12 10:25:05', 0, 0),
(18, 'authorize_net', 'academe/omnipay-authorizenetapi', 'Authorize.net', NULL, 0, '2022-02-07 10:45:39', '2022-03-04 05:33:43', 1, 0),
(19, 'stripe_fpx', 'omnipay/stripe', 'Stripe FPX', NULL, 0, '2022-02-07 10:45:39', '2022-02-07 10:45:39', 1, 0),
(20, 'kongapay', 'kongapay/pay', 'KongaPay', NULL, 0, '2022-03-04 05:33:43', '2022-03-04 05:33:43', 1, 0),
(21, 'viva_wallet', 'vivawallet/pay', 'Viva Wallet', NULL, 0, '2022-03-04 05:33:43', '2022-03-04 05:33:43', 1, 0),
(22, 'ccavenue', 'ccavenue/pay', 'CCAvenue', NULL, 0, '2022-03-29 11:56:56', '2022-03-29 11:56:56', 1, 0),
(23, 'easypaisa', 'easypaisa/pay', 'Easypaisa', NULL, 0, '2022-03-29 11:56:56', '2022-03-29 11:56:56', 1, 0),
(24, 'cashfree', 'cashfree', 'Cashfree', NULL, 0, '2022-03-29 11:56:56', '2022-03-29 11:56:56', 1, 0),
(25, 'easebuzz', 'easebuzz', 'PAYMENT GATEWAY - EASEBUZZ', NULL, 0, '2022-04-06 06:42:12', '2022-04-25 11:40:18', 1, 0),
(26, 'toyyibpay', 'tarsoft/toyyibpay', 'Toyyibpay', NULL, 0, '2022-04-06 06:42:12', '2022-04-06 06:42:12', 1, 0),
(27, 'paytab', '', 'PayTab', NULL, 0, '2022-04-14 07:24:40', '2022-04-14 07:24:40', 1, 0),
(28, 'vnpay', 'vnpay', 'VNPay', NULL, 0, '2022-04-14 07:24:40', '2022-04-18 10:18:34', 1, 0),
(29, 'mvodafone', '', 'Mpesa Vodafone', NULL, 0, '2022-05-11 11:55:39', '2022-05-11 11:55:39', 1, 0),
(30, 'flutterwave', '', 'Flutter Wave', NULL, 0, '2022-05-11 11:55:39', '2022-05-11 11:55:39', 0, 0),
(31, 'payu', '', 'PayU', NULL, 0, '2022-05-11 11:55:39', '2022-05-11 11:55:39', 1, 0),
(32, 'payphone', '', 'PayPhone', NULL, 0, '2022-05-11 11:55:39', '2022-08-08 11:40:44', 0, 0),
(33, 'braintree', 'braintree/braintree_php', 'Braintree', NULL, 0, '2022-05-11 11:55:39', '2022-05-11 11:55:39', 1, 0),
(34, 'windcave', 'windcave', 'Windcave', NULL, 0, '2022-05-26 06:28:05', '2022-05-26 06:28:05', 1, 0),
(35, 'paytech', 'paytech', 'PayTech', NULL, 0, '2022-05-26 06:28:06', '2022-05-26 06:28:06', 1, 0),
(36, 'mycash', 'mycash', 'MyCash', NULL, 0, '2022-05-26 06:28:06', '2022-06-13 12:02:06', 1, 0),
(37, 'stripe_oxxo', '', 'Stripe OXXO', NULL, 0, '2022-05-26 06:28:06', '2022-05-26 06:28:06', 1, 0),
(38, 'offline_manual', '', 'Offline Manual Payment', NULL, 0, '2022-05-26 06:28:06', '2022-05-26 06:28:06', 0, 0),
(39, 'stripe_ideal', '', 'Stripe Ideal', NULL, 0, '2022-07-07 09:49:04', '2022-07-07 09:49:04', 1, 0),
(40, 'userede', '', 'Userede', NULL, 0, '2022-07-07 09:49:04', '2022-07-07 09:49:04', 1, 0),
(41, 'openpay', '', 'Open-pay', NULL, 0, '2022-07-07 09:49:04', '2022-07-07 09:49:04', 1, 0),
(42, 'dpo', '', 'Direct Pay Online', NULL, 0, '2022-07-07 09:49:04', '2022-07-07 09:49:04', 1, 0),
(43, 'upay', '', 'UnionBank Payments and Collections Solution', NULL, 0, '2022-07-07 09:49:04', '2022-07-07 09:49:04', 1, 0),
(44, 'conekta', 'conekta/conekta-php', 'Conekta', NULL, 0, '2022-07-07 09:49:04', '2022-07-07 09:49:04', 1, 0),
(45, 'telr', 'laravel_payment/telr', 'Telr', NULL, 0, '2022-08-08 11:40:44', '2022-08-08 11:40:44', 1, 0),
(46, 'mastercard', '', 'Mastercard', NULL, 0, '2022-08-08 11:40:44', '2022-08-08 11:40:44', 1, 0),
(47, 'khalti', 'khalti/khalti', 'Khalti', NULL, 0, '2022-08-08 11:40:44', '2022-08-08 11:40:44', 1, 0),
(48, 'mtn_momo', '', 'Mtn Momo', NULL, 0, '2023-01-23 13:29:33', '2023-01-23 13:29:33', 1, 0),
(49, 'plugnpay', '', 'plugnpay', NULL, 0, '2023-01-23 13:29:33', '2023-01-23 13:29:33', 1, 0),
(50, 'azul', '', 'Azulpay', NULL, 0, '2023-02-17 12:06:24', '2023-02-17 12:06:24', 1, 0),
(51, 'payway', '', 'Payway', NULL, 0, '2023-04-10 07:00:42', '2023-04-10 07:00:42', 1, 0),
(52, 'skip_cash', '', 'SkpCash', NULL, 0, '2023-04-10 07:00:42', '2023-04-10 07:00:42', 1, 0),
(53, 'nmi', '', 'Nmi', NULL, 0, '2023-04-10 11:42:14', '2023-04-10 11:42:14', 1, 0),
(54, 'yappy', '', 'yappy', NULL, 0, '2023-04-10 11:42:14', '2023-04-10 11:42:14', 1, 0),
(55, 'data_trans', '', 'Data Trans', NULL, 0, '2023-05-15 10:38:11', '2023-05-15 10:38:11', 0, 0),
(56, 'obo', '', 'Obo', NULL, 0, '2023-05-23 14:25:26', '2023-05-23 14:25:26', 1, 0),
(57, 'pesapal', '', 'pesapal', NULL, 0, '2023-05-22 07:42:27', '2023-05-22 07:42:27', 1, 0),
(58, 'powertrans', '', 'powertrans', NULL, 0, '2023-05-22 07:42:27', '2023-05-22 07:42:27', 1, 0),
(59, 'livee', '', 'livee', NULL, 1, '2023-07-17 09:27:40', '2023-07-17 09:27:40', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payout_options`
--

CREATE TABLE `payout_options` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` json DEFAULT NULL COMMENT 'credentials in json format',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 inactive, 1 active, 2 delete',
  `off_site` tinyint UNSIGNED DEFAULT '0' COMMENT '0 = on-site, 1 = off-site',
  `test_mode` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = false, 1 = true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payout_options`
--

INSERT INTO `payout_options` (`id`, `code`, `path`, `title`, `credentials`, `status`, `off_site`, `test_mode`, `created_at`, `updated_at`) VALUES
(1, 'cash', '', 'Off the Platform', NULL, 0, 0, 0, NULL, NULL),
(2, 'stripe', 'omnipay/stripe', 'Stripe', NULL, 0, 0, 0, NULL, NULL),
(3, 'pagarme', 'pagarme/pagarme-php', 'Pagarme', NULL, 0, 1, 0, '2022-03-11 12:24:50', '2022-03-11 12:24:50'),
(4, 'razorpay', 'razorpay/razorpay-php', 'Razorpay', NULL, 0, 1, 0, '2022-11-11 09:22:20', '2022-11-11 09:22:20');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DASHBOARD', 'dashboard', 1, NULL, NULL),
(2, 'ORDERS', 'orders', 1, NULL, NULL),
(3, 'VENDORS', 'vendors', 1, NULL, NULL),
(4, 'CUSTOMERS', 'customers', 1, NULL, NULL),
(5, 'Profile', 'profile', 1, NULL, NULL),
(6, 'CUSTOMIZE', 'customize', 1, NULL, NULL),
(7, 'CONFIGURATIONS', 'configurations', 1, NULL, NULL),
(8, 'BANNER', 'banner', 1, NULL, NULL),
(9, 'CATALOG', 'catalog', 1, NULL, NULL),
(10, 'TAX', 'tax', 1, NULL, NULL),
(11, 'PAYMENT', 'payment', 1, NULL, NULL),
(12, 'PROMOCODE', 'promocode', 1, NULL, NULL),
(13, 'LOYALTY CARDS', 'loyalty_cards', 1, NULL, NULL),
(14, 'CELEBRITY', 'celebrity', 1, NULL, NULL),
(15, 'WEB STYLING', 'web_styling', 1, NULL, NULL),
(16, 'APP STYLING', 'app_styling', 1, NULL, NULL),
(17, 'Accounting Orders', 'accounting_orders', 1, NULL, NULL),
(18, 'Accounting Loyality', 'accounting_loyality', 1, NULL, NULL),
(19, 'Accounting Promo Codes', 'accounting_promo_codes', 1, NULL, NULL),
(20, 'Accounting Taxes', 'accounting_taxes', 1, NULL, NULL),
(21, 'Accounting Vendors', 'accounting_vendors', 1, NULL, NULL),
(22, 'Subscriptions Customers', 'subscriptions_customers', 1, NULL, NULL),
(23, 'Subscriptions Vendors', 'subscriptions_vendors', 1, NULL, NULL),
(24, 'CMS Pages', 'cms_pages', 1, NULL, NULL),
(25, 'CMS Emails', 'cms_emails', 1, NULL, NULL),
(26, 'Inquiries', 'inquiries', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pick_drop_driver_bids`
--

CREATE TABLE `pick_drop_driver_bids` (
  `id` bigint UNSIGNED NOT NULL,
  `order_bid_id` bigint DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=>placed, 1=>approved, 2=>declined',
  `tasks` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_id` int DEFAULT NULL,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bid_price` decimal(15,4) DEFAULT NULL,
  `task_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pincodes`
--

CREATE TABLE `pincodes` (
  `id` bigint UNSIGNED NOT NULL,
  `pincode` mediumint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_disabled` tinyint NOT NULL DEFAULT '0' COMMENT '0 for enabled, 1 for disabled',
  `vendor_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `pincode_delivery_options`
--

CREATE TABLE `pincode_delivery_options` (
  `id` bigint UNSIGNED NOT NULL,
  `pincode_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_option_type` tinyint NOT NULL DEFAULT '1' COMMENT '1 for same_day_delivery, 2 for next_day_delivery, 3 for hyper_local_delivery',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `processor_products`
--

CREATE TABLE `processor_products` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `is_processor_enable` tinyint DEFAULT '0' COMMENT '0-vendor, 1-processor',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(15,12) DEFAULT NULL,
  `longitude` decimal(16,12) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `sku` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `body_html` longtext COLLATE utf8mb4_unicode_ci,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `type_id` bigint UNSIGNED DEFAULT NULL,
  `country_origin_id` bigint UNSIGNED DEFAULT NULL,
  `is_new` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `is_featured` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `is_live` tinyint NOT NULL DEFAULT '0' COMMENT '0 - draft, 1 - published, 2 - blocked',
  `is_physical` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `weight` decimal(10,4) DEFAULT NULL,
  `weight_unit` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_inventory` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `has_variant` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `sell_when_out_of_stock` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `requires_shipping` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `Requires_last_mile` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `averageRating` decimal(4,2) DEFAULT NULL,
  `inquiry_only` tinyint DEFAULT '0',
  `publish_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `brand_id` bigint UNSIGNED DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `pharmacy_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `need_price_from_dispatcher` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode_of_service` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delay_order_hrs` int NOT NULL DEFAULT '0',
  `delay_order_min` int NOT NULL DEFAULT '0',
  `pickup_delay_order_hrs` int NOT NULL DEFAULT '0',
  `pickup_delay_order_min` int NOT NULL DEFAULT '0',
  `dropoff_delay_order_hrs` int NOT NULL DEFAULT '0',
  `dropoff_delay_order_min` int NOT NULL DEFAULT '0',
  `need_shipment` int NOT NULL DEFAULT '0',
  `minimum_order_count` int NOT NULL DEFAULT '1',
  `batch_count` int NOT NULL DEFAULT '1',
  `delay_order_hrs_for_dine_in` int NOT NULL DEFAULT '0',
  `delay_order_min_for_dine_in` int NOT NULL DEFAULT '0',
  `delay_order_hrs_for_takeway` int NOT NULL DEFAULT '0',
  `delay_order_min_for_takeway` int NOT NULL DEFAULT '0',
  `age_restriction` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `service_charges_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `delivery_charges_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `container_charges_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `service_charges_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `delivery_charges_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `container_charges_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `fixed_fee_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `fixed_fee_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `import_from_inventory` tinyint DEFAULT NULL,
  `global_product_id` int DEFAULT NULL,
  `minimum_duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_increments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buffer_time_duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_fix_check_in_time` tinyint DEFAULT '0',
  `check_in_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `markup_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `minimum_duration_min` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_increments_min` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buffer_time_duration_min` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_slot_from_dispatch` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_show_dispatcher_agent` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `store_id` int NOT NULL COMMENT 'Store id is a refrence of inventory store id',
  `individual_delivery_fee` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_long_term_service` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `service_duration` bigint DEFAULT '0' COMMENT 'long term servier Months',
  `seats` tinyint DEFAULT '0',
  `seats_for_booking` tinyint DEFAULT '0',
  `available_for_pooling` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_toll_tax` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `travel_mode_id` tinyint DEFAULT '0',
  `toll_pass_id` tinyint DEFAULT '0',
  `emission_type_id` tinyint DEFAULT '0',
  `returnable` tinyint NOT NULL DEFAULT '0',
  `replaceable` tinyint NOT NULL DEFAULT '0',
  `return_days` int NOT NULL DEFAULT '0',
  `spotlight_deals` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `same_day_delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `next_day_delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `hyper_local_delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_recurring_booking` tinyint NOT NULL DEFAULT '0' COMMENT '1=active,0=deactive',
  `security_amount` decimal(10,2) DEFAULT NULL,
  `is_product_instant_booking` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `product_pickup_date` date DEFAULT NULL,
  `validate_pharmacy_check` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `square_item_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `square_item_version` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sync_from_inventory` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `sync_inventory_side_product_id` tinyint DEFAULT NULL COMMENT 'Inventory side product id sync',
  `height` decimal(10,4) DEFAULT NULL,
  `breadth` decimal(10,4) DEFAULT NULL,
  `length` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `title`, `url_slug`, `description`, `body_html`, `vendor_id`, `category_id`, `type_id`, `country_origin_id`, `is_new`, `is_featured`, `is_live`, `is_physical`, `weight`, `weight_unit`, `has_inventory`, `has_variant`, `sell_when_out_of_stock`, `requires_shipping`, `Requires_last_mile`, `averageRating`, `inquiry_only`, `publish_at`, `created_at`, `updated_at`, `brand_id`, `tax_category_id`, `deleted_at`, `pharmacy_check`, `tags`, `need_price_from_dispatcher`, `mode_of_service`, `delay_order_hrs`, `delay_order_min`, `pickup_delay_order_hrs`, `pickup_delay_order_min`, `dropoff_delay_order_hrs`, `dropoff_delay_order_min`, `need_shipment`, `minimum_order_count`, `batch_count`, `delay_order_hrs_for_dine_in`, `delay_order_min_for_dine_in`, `delay_order_hrs_for_takeway`, `delay_order_min_for_takeway`, `age_restriction`, `service_charges_tax`, `delivery_charges_tax`, `container_charges_tax`, `service_charges_tax_id`, `delivery_charges_tax_id`, `container_charges_tax_id`, `fixed_fee_tax`, `fixed_fee_tax_id`, `import_from_inventory`, `global_product_id`, `minimum_duration`, `additional_increments`, `buffer_time_duration`, `is_fix_check_in_time`, `check_in_time`, `markup_price`, `minimum_duration_min`, `additional_increments_min`, `buffer_time_duration_min`, `is_slot_from_dispatch`, `is_show_dispatcher_agent`, `store_id`, `individual_delivery_fee`, `is_long_term_service`, `service_duration`, `seats`, `seats_for_booking`, `available_for_pooling`, `is_toll_tax`, `travel_mode_id`, `toll_pass_id`, `emission_type_id`, `returnable`, `replaceable`, `return_days`, `spotlight_deals`, `same_day_delivery`, `next_day_delivery`, `hyper_local_delivery`, `is_recurring_booking`, `security_amount`, `is_product_instant_booking`, `product_pickup_date`, `validate_pharmacy_check`, `square_item_id`, `square_item_version`, `sync_from_inventory`, `sync_inventory_side_product_id`, `height`, `breadth`, `length`) VALUES
(1, 'sku-id', '1', 'sku-id', NULL, NULL, 1, NULL, 1, NULL, 1, 1, 1, 1, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(2, 'GS100', 'Pot Roast', 'GS100', NULL, '', 6, 3, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-13 11:23:04', NULL, '2021-07-20 11:56:25', NULL, NULL, '2021-07-20 11:56:25', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(3, 'GS101', 'Cobb salad', 'GS101', NULL, '', 6, 3, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-13 11:23:22', NULL, '2021-07-20 11:56:33', NULL, NULL, '2021-07-20 11:56:33', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(4, 'GS102', 'Tater Tots', 'GS102', NULL, '', 6, 3, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-13 11:23:43', NULL, '2021-07-20 11:57:01', NULL, NULL, '2021-07-20 11:57:01', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(5, 'GS103', 'Key lime pie', 'GS103', NULL, '', 6, 3, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-13 11:24:59', NULL, '2021-07-20 11:56:40', NULL, NULL, '2021-07-20 11:56:40', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(6, 'GS104', 'Cold Coffee', 'GS104', NULL, '', 6, 3, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-13 11:25:19', NULL, '2021-07-20 11:56:54', NULL, NULL, '2021-07-20 11:56:54', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(7, 'GS105', 'Jerky', 'GS105', NULL, '', 6, 3, 1, NULL, 1, 0, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, '2021-07-13 11:25:26', NULL, NULL, '2021-07-13 11:25:26', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(8, 'GS106', 'Fajitas', 'GS106', NULL, '', 6, 3, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-13 11:25:47', NULL, '2021-07-20 11:56:46', NULL, NULL, '2021-07-20 11:56:46', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(9, 'CN100', 'Prawn Pie', 'CN100', NULL, '', 6, 20, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:08:21', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(10, 'CN101', 'Poached Pear Salad', 'CN101', NULL, '', 6, 20, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:09:51', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(11, 'CN102', 'Crispy Calamari Rings', 'CN102', NULL, '', 6, 20, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:11:09', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(12, 'CN103', 'Sweet Potato Pie', 'CN103', NULL, '', 6, 20, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:12:40', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(13, 'CN104', 'Chicken And Cheese Salad', 'CN104', NULL, '', 6, 20, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:14:12', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(14, 'CN105', 'Yorkshire Lamb Patties', 'CN105', NULL, '', 6, 20, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:15:12', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(15, 'IT300', 'Margherita pizza', 'IT300', NULL, '', 5, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:16:35', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(16, 'IT301', 'Mushroom Risotto', 'IT301', NULL, '', 5, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:17:42', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(17, 'IT302', 'Focaccia Bread ', 'IT302', NULL, '', 5, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:18:33', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(18, 'IT303', 'Bruschetta', 'IT303', NULL, '', 5, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:19:28', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(19, 'IT304', 'Pasta Carbonara', 'IT304', NULL, '', 5, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:20:26', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(20, 'IT305', 'Caprese Salad with Pesto Sauce', 'IT305', NULL, '', 5, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:21:21', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(21, 'CH501', 'Dim Sums', 'CH501', NULL, '', 4, 15, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:22:35', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(22, 'CH502', 'Hot and Sour Soup', 'CH502', NULL, '', 4, 15, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:23:35', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(23, 'CH503', 'Chicken with Chestnuts', 'CH503', NULL, '', 4, 15, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:24:31', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(24, 'CH504', 'Spring Rolls', 'CH504', NULL, '', 4, 15, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:25:27', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(25, 'CH505', 'Stir Fried Tofu with Rice', 'CH505', NULL, '', 4, 15, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:26:21', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(26, 'CH506', 'Quick Noodles', 'CH506', NULL, '', 4, 15, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:28:14', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(27, 'D021', 'Almond and date cake', 'D021', NULL, '', 3, 16, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:29:58', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(28, 'D022', 'Chocolate mousse', 'D022', NULL, '', 3, 16, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:30:57', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(29, 'D023', 'Roasted strawberry crumble', 'D023', NULL, '', 3, 16, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:33:02', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(30, 'D024', 'Apple cinnamon custard cake', 'D024', NULL, '', 3, 16, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:34:10', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(31, 'D025', 'Tiramisu', 'D025', NULL, '', 3, 16, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:35:04', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(32, 'D026', 'White Flower Basket Bouquet', 'D026', NULL, '', 3, 16, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:36:15', NULL, '2021-08-05 05:56:00', NULL, NULL, '2021-08-05 05:56:00', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(33, 'SN400', 'Reese\'s Peanut Butter Cups', 'SN400', NULL, '', 3, 19, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:37:09', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 'SN401', 'Milky Way Bars', 'SN401', NULL, '', 3, 19, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:38:13', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(35, 'SN402', 'Butterfinger', 'SN402', NULL, '', 3, 19, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:39:18', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(36, 'SN403', '3 Musketeers', 'SN403', NULL, '', 3, 19, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:40:38', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(37, 'SN404', 'Whatchamacallit', 'SN404', NULL, '', 3, 19, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:42:11', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(38, 'SN405', 'Caprese Salad with Pesto Sauce', 'SN405', NULL, '', 3, 19, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:43:19', NULL, '2021-08-05 06:16:28', NULL, NULL, '2021-08-05 06:16:28', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(39, 'BV230', 'Lemonade', 'BV230', NULL, '', 2, 17, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:45:37', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(40, 'BV231', 'Hot chocolate', 'BV231', NULL, '', 2, 17, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:46:50', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(41, 'BV232', 'Iced tea', 'BV232', NULL, '', 2, 17, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:47:52', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(42, 'BV233', 'Smoothie', 'BV233', NULL, '', 2, 17, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:48:55', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(43, 'BV234', 'Orange juice', 'BV234', NULL, '', 2, 17, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:50:02', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(44, 'BV235', 'Milkshake', 'BV235', NULL, '', 2, 17, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:50:54', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(45, 'SL600', 'Israeli salad', 'SL600', NULL, '', 2, 18, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:52:34', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(46, 'SL601', 'Waldorf salad', 'SL601', NULL, '', 2, 18, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:53:33', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(47, 'SL602', 'Gado-gado', 'SL602', NULL, '', 2, 18, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:54:43', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(48, 'SL603', 'Nicoise salad', 'SL603', NULL, '', 2, 18, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:56:00', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(49, 'SL604', 'Dressed herring salad', 'SL604', NULL, '', 2, 18, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:57:15', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(50, 'SL605', 'Larb', 'SL605', NULL, '', 2, 18, 1, NULL, 1, 1, 1, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, '2021-07-20 12:58:12', NULL, '2021-12-29 11:48:22', NULL, NULL, NULL, 0, NULL, '0', NULL, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '0.00', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_addons`
--

CREATE TABLE `product_addons` (
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `addon_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` bigint UNSIGNED NOT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `attribute_option_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_bookings`
--

CREATE TABLE `product_bookings` (
  `id` int UNSIGNED NOT NULL,
  `product_id` int NOT NULL,
  `order_user_id` int DEFAULT NULL,
  `order_vendor_id` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `memo` longtext COLLATE utf8mb4_unicode_ci,
  `booking_type` enum('blocked','new_booking') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `booking_start_end` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `on_rent` tinyint NOT NULL DEFAULT '1' COMMENT '1=>on_rent, 0=>available',
  `order_vendor_product_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_by_roles`
--

CREATE TABLE `product_by_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `minimum_order_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 11, NULL, NULL),
(2, 3, NULL, NULL),
(2, 3, NULL, NULL),
(3, 3, NULL, NULL),
(2, 3, NULL, NULL),
(3, 3, NULL, NULL),
(4, 3, NULL, NULL),
(2, 3, NULL, NULL),
(3, 3, NULL, NULL),
(4, 3, NULL, NULL),
(5, 3, NULL, NULL),
(2, 3, NULL, NULL),
(3, 3, NULL, NULL),
(4, 3, NULL, NULL),
(5, 3, NULL, NULL),
(6, 3, NULL, NULL),
(2, 3, NULL, NULL),
(3, 3, NULL, NULL),
(4, 3, NULL, NULL),
(5, 3, NULL, NULL),
(6, 3, NULL, NULL),
(7, 3, NULL, NULL),
(2, 3, NULL, NULL),
(3, 3, NULL, NULL),
(4, 3, NULL, NULL),
(5, 3, NULL, NULL),
(6, 3, NULL, NULL),
(7, 3, NULL, NULL),
(8, 3, NULL, NULL),
(9, 20, NULL, NULL),
(9, 20, NULL, NULL),
(10, 20, NULL, NULL),
(9, 20, NULL, NULL),
(10, 20, NULL, NULL),
(11, 20, NULL, NULL),
(9, 20, NULL, NULL),
(10, 20, NULL, NULL),
(11, 20, NULL, NULL),
(12, 20, NULL, NULL),
(9, 20, NULL, NULL),
(10, 20, NULL, NULL),
(11, 20, NULL, NULL),
(12, 20, NULL, NULL),
(13, 20, NULL, NULL),
(9, 20, NULL, NULL),
(10, 20, NULL, NULL),
(11, 20, NULL, NULL),
(12, 20, NULL, NULL),
(13, 20, NULL, NULL),
(14, 20, NULL, NULL),
(15, 14, NULL, NULL),
(15, 14, NULL, NULL),
(16, 14, NULL, NULL),
(15, 14, NULL, NULL),
(16, 14, NULL, NULL),
(17, 14, NULL, NULL),
(15, 14, NULL, NULL),
(16, 14, NULL, NULL),
(17, 14, NULL, NULL),
(18, 14, NULL, NULL),
(15, 14, NULL, NULL),
(16, 14, NULL, NULL),
(17, 14, NULL, NULL),
(18, 14, NULL, NULL),
(19, 14, NULL, NULL),
(15, 14, NULL, NULL),
(16, 14, NULL, NULL),
(17, 14, NULL, NULL),
(18, 14, NULL, NULL),
(19, 14, NULL, NULL),
(20, 14, NULL, NULL),
(21, 15, NULL, NULL),
(21, 15, NULL, NULL),
(22, 15, NULL, NULL),
(21, 15, NULL, NULL),
(22, 15, NULL, NULL),
(23, 15, NULL, NULL),
(21, 15, NULL, NULL),
(22, 15, NULL, NULL),
(23, 15, NULL, NULL),
(24, 15, NULL, NULL),
(21, 15, NULL, NULL),
(22, 15, NULL, NULL),
(23, 15, NULL, NULL),
(24, 15, NULL, NULL),
(25, 15, NULL, NULL),
(21, 15, NULL, NULL),
(22, 15, NULL, NULL),
(23, 15, NULL, NULL),
(24, 15, NULL, NULL),
(25, 15, NULL, NULL),
(26, 15, NULL, NULL),
(27, 16, NULL, NULL),
(27, 16, NULL, NULL),
(28, 16, NULL, NULL),
(27, 16, NULL, NULL),
(28, 16, NULL, NULL),
(29, 16, NULL, NULL),
(27, 16, NULL, NULL),
(28, 16, NULL, NULL),
(29, 16, NULL, NULL),
(30, 16, NULL, NULL),
(27, 16, NULL, NULL),
(28, 16, NULL, NULL),
(29, 16, NULL, NULL),
(30, 16, NULL, NULL),
(31, 16, NULL, NULL),
(27, 16, NULL, NULL),
(28, 16, NULL, NULL),
(29, 16, NULL, NULL),
(30, 16, NULL, NULL),
(31, 16, NULL, NULL),
(32, 16, NULL, NULL),
(33, 19, NULL, NULL),
(33, 19, NULL, NULL),
(34, 19, NULL, NULL),
(33, 19, NULL, NULL),
(34, 19, NULL, NULL),
(35, 19, NULL, NULL),
(33, 19, NULL, NULL),
(34, 19, NULL, NULL),
(35, 19, NULL, NULL),
(36, 19, NULL, NULL),
(33, 19, NULL, NULL),
(34, 19, NULL, NULL),
(35, 19, NULL, NULL),
(36, 19, NULL, NULL),
(37, 19, NULL, NULL),
(33, 19, NULL, NULL),
(34, 19, NULL, NULL),
(35, 19, NULL, NULL),
(36, 19, NULL, NULL),
(37, 19, NULL, NULL),
(38, 19, NULL, NULL),
(39, 17, NULL, NULL),
(39, 17, NULL, NULL),
(40, 17, NULL, NULL),
(39, 17, NULL, NULL),
(40, 17, NULL, NULL),
(41, 17, NULL, NULL),
(39, 17, NULL, NULL),
(40, 17, NULL, NULL),
(41, 17, NULL, NULL),
(42, 17, NULL, NULL),
(39, 17, NULL, NULL),
(40, 17, NULL, NULL),
(41, 17, NULL, NULL),
(42, 17, NULL, NULL),
(43, 17, NULL, NULL),
(39, 17, NULL, NULL),
(40, 17, NULL, NULL),
(41, 17, NULL, NULL),
(42, 17, NULL, NULL),
(43, 17, NULL, NULL),
(44, 17, NULL, NULL),
(45, 18, NULL, NULL),
(45, 18, NULL, NULL),
(46, 18, NULL, NULL),
(45, 18, NULL, NULL),
(46, 18, NULL, NULL),
(47, 18, NULL, NULL),
(45, 18, NULL, NULL),
(46, 18, NULL, NULL),
(47, 18, NULL, NULL),
(48, 18, NULL, NULL),
(45, 18, NULL, NULL),
(46, 18, NULL, NULL),
(47, 18, NULL, NULL),
(48, 18, NULL, NULL),
(49, 18, NULL, NULL),
(45, 18, NULL, NULL),
(46, 18, NULL, NULL),
(47, 18, NULL, NULL),
(48, 18, NULL, NULL),
(49, 18, NULL, NULL),
(50, 18, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_celebrities`
--

CREATE TABLE `product_celebrities` (
  `celebrity_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_cross_sells`
--

CREATE TABLE `product_cross_sells` (
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `cross_product_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_delivery_fee_by_roles`
--

CREATE TABLE `product_delivery_fee_by_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `is_free_delivery` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_faqs`
--

CREATE TABLE `product_faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `is_required` int NOT NULL DEFAULT '1' COMMENT '0 means not required, 1 means required',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_faq_select_options`
--

CREATE TABLE `product_faq_select_options` (
  `id` bigint UNSIGNED NOT NULL,
  `product_faq_id` bigint UNSIGNED NOT NULL,
  `status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_faq_select_option_translations`
--

CREATE TABLE `product_faq_select_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_faq_select_option_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_faq_translations`
--

CREATE TABLE `product_faq_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `product_faq_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `media_id` bigint UNSIGNED DEFAULT NULL,
  `is_default` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `media_id`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, NULL, NULL),
(2, 3, 2, 1, NULL, NULL),
(3, 4, 3, 1, NULL, NULL),
(4, 5, 4, 1, NULL, NULL),
(5, 6, 5, 1, NULL, NULL),
(6, 8, 6, 1, NULL, NULL),
(17, 19, 17, 1, NULL, NULL),
(25, 27, 25, 1, NULL, NULL),
(26, 28, 26, 1, NULL, NULL),
(27, 29, 27, 1, NULL, NULL),
(28, 30, 28, 1, NULL, NULL),
(29, 31, 29, 1, NULL, NULL),
(30, 32, 30, 1, NULL, NULL),
(31, 33, 31, 1, NULL, NULL),
(32, 34, 32, 1, NULL, NULL),
(33, 35, 33, 1, NULL, NULL),
(34, 36, 34, 1, NULL, NULL),
(35, 37, 35, 1, NULL, NULL),
(36, 38, 36, 1, NULL, NULL),
(42, 44, 42, 1, NULL, NULL),
(43, 45, 43, 1, NULL, NULL),
(44, 46, 44, 1, NULL, NULL),
(45, 47, 45, 1, NULL, NULL),
(46, 48, 46, 1, NULL, NULL),
(50, 15, 49, 1, NULL, NULL),
(51, 16, 50, 1, NULL, NULL),
(53, 18, 52, 1, NULL, NULL),
(54, 17, 53, 1, NULL, NULL),
(55, 21, 54, 1, NULL, NULL),
(56, 24, 56, 1, NULL, NULL),
(57, 22, 57, 1, NULL, NULL),
(58, 23, 58, 1, NULL, NULL),
(59, 26, 59, 1, NULL, NULL),
(60, 25, 60, 1, NULL, NULL),
(61, 42, 61, 1, NULL, NULL),
(62, 39, 62, 1, NULL, NULL),
(63, 43, 63, 1, NULL, NULL),
(64, 41, 64, 1, NULL, NULL),
(65, 40, 65, 1, NULL, NULL),
(66, 50, 66, 1, NULL, NULL),
(67, 49, 67, 1, NULL, NULL),
(68, 9, 68, 1, NULL, NULL),
(69, 10, 69, 1, NULL, NULL),
(70, 11, 70, 1, NULL, NULL),
(71, 12, 71, 1, NULL, NULL),
(72, 13, 72, 1, NULL, NULL),
(73, 14, 73, 1, NULL, NULL),
(74, 20, 74, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_inquiries`
--

CREATE TABLE `product_inquiries` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_recently_viewed`
--

CREATE TABLE `product_recently_viewed` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` int DEFAULT NULL,
  `token_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_related`
--

CREATE TABLE `product_related` (
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `related_product_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_tags`
--

CREATE TABLE `product_tags` (
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `tag_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_translations`
--

CREATE TABLE `product_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_html` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keyword` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `product_translations`
--

INSERT INTO `product_translations` (`id`, `title`, `body_html`, `meta_title`, `meta_keyword`, `meta_description`, `product_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Xiaomi', NULL, 'Xiaomi', 'Xiaomi', NULL, 1, 1, NULL, NULL),
(2, 'Pot Roast', NULL, NULL, NULL, NULL, 2, 1, NULL, '2021-07-13 11:23:04'),
(3, 'Pot Roast', '', '', '', '', 2, 1, NULL, NULL),
(4, 'Cobb salad', NULL, NULL, NULL, NULL, 3, 1, NULL, '2021-07-13 11:23:22'),
(5, 'Pot Roast', '', '', '', '', 2, 1, NULL, NULL),
(6, 'Cobb salad', '', '', '', '', 3, 1, NULL, NULL),
(7, 'Tater Tots', NULL, NULL, NULL, NULL, 4, 1, NULL, '2021-07-13 11:23:43'),
(8, 'Pot Roast', '', '', '', '', 2, 1, NULL, NULL),
(9, 'Cobb salad', '', '', '', '', 3, 1, NULL, NULL),
(10, 'Tater Tots', '', '', '', '', 4, 1, NULL, NULL),
(11, 'Key lime pie', NULL, NULL, NULL, NULL, 5, 1, NULL, '2021-07-13 11:24:59'),
(12, 'Pot Roast', '', '', '', '', 2, 1, NULL, NULL),
(13, 'Cobb salad', '', '', '', '', 3, 1, NULL, NULL),
(14, 'Tater Tots', '', '', '', '', 4, 1, NULL, NULL),
(15, 'Key lime pie', '', '', '', '', 5, 1, NULL, NULL),
(16, 'Cold Coffee', NULL, NULL, NULL, NULL, 6, 1, NULL, '2021-07-13 11:25:19'),
(17, 'Pot Roast', '', '', '', '', 2, 1, NULL, NULL),
(18, 'Cobb salad', '', '', '', '', 3, 1, NULL, NULL),
(19, 'Tater Tots', '', '', '', '', 4, 1, NULL, NULL),
(20, 'Key lime pie', '', '', '', '', 5, 1, NULL, NULL),
(21, 'Cold Coffee', '', '', '', '', 6, 1, NULL, NULL),
(22, 'Jerky', '', '', '', '', 7, 1, NULL, NULL),
(23, 'Pot Roast', '', '', '', '', 2, 1, NULL, NULL),
(24, 'Cobb salad', '', '', '', '', 3, 1, NULL, NULL),
(25, 'Tater Tots', '', '', '', '', 4, 1, NULL, NULL),
(26, 'Key lime pie', '', '', '', '', 5, 1, NULL, NULL),
(27, 'Cold Coffee', '', '', '', '', 6, 1, NULL, NULL),
(28, 'Jerky', '', '', '', '', 7, 1, NULL, NULL),
(29, 'Fajitas', NULL, NULL, NULL, NULL, 8, 1, NULL, '2021-07-13 11:25:47'),
(30, 'Prawn Pie', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">Prawn pie is super rich and is best for small portions as an appetizer at dinner parties.</span><br></p>', NULL, NULL, NULL, 9, 1, NULL, '2021-07-20 12:08:22'),
(31, 'Prawn Pie', '', '', '', '', 9, 1, NULL, NULL),
(32, 'Poached Pear Salad', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">A refreshing, delicate and exquisite salad that is far different from the usual, and makes a superb summer meal.</span><br></p>', NULL, NULL, NULL, 10, 1, NULL, '2021-07-20 12:09:51'),
(33, 'Prawn Pie', '', '', '', '', 9, 1, NULL, NULL),
(34, 'Poached Pear Salad', '', '', '', '', 10, 1, NULL, NULL),
(35, 'Crispy Calamari Rings', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">A quick and easy snack recipe, calamari rings are basically squid rings deep fried in tempura batter and served hot and crispy alongside parsley sprig and thai chilli sauce.</span><br></p>', NULL, NULL, NULL, 11, 1, NULL, '2021-07-20 12:11:09'),
(36, 'Prawn Pie', '', '', '', '', 9, 1, NULL, NULL),
(37, 'Poached Pear Salad', '', '', '', '', 10, 1, NULL, NULL),
(38, 'Crispy Calamari Rings', '', '', '', '', 11, 1, NULL, NULL),
(39, 'Sweet Potato Pie', '<p><em style=\"margin-top: 0px; color: rgb(0, 0, 0); font-family: Inter, sans-serif; font-size: 16px; text-align: center;\">The perfect <span style=\"margin-top: 0px;\">Sweet Potato Pie recipe</span>! Made with a tender and flaky, buttery pie crust and a lightly spiced, perfectly sweetened, browned butter sweet potato pie filling.</em><br></p>', NULL, NULL, NULL, 12, 1, NULL, '2021-08-05 05:28:59'),
(40, 'Prawn Pie', '', '', '', '', 9, 1, NULL, NULL),
(41, 'Poached Pear Salad', '', '', '', '', 10, 1, NULL, NULL),
(42, 'Crispy Calamari Rings', '', '', '', '', 11, 1, NULL, NULL),
(43, 'Sweet Potato Pie', '', '', '', '', 12, 1, NULL, NULL),
(44, 'Chicken And Cheese Salad', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">A sparkling salad with tender chicken breasts and cheese, tossed and mixed in salt, pepper and a generous dose of mayonnaise.</span><br></p>', NULL, NULL, NULL, 13, 1, NULL, '2021-07-20 12:14:12'),
(45, 'Prawn Pie', '', '', '', '', 9, 1, NULL, NULL),
(46, 'Poached Pear Salad', '', '', '', '', 10, 1, NULL, NULL),
(47, 'Crispy Calamari Rings', '', '', '', '', 11, 1, NULL, NULL),
(48, 'Sweet Potato Pie', '', '', '', '', 12, 1, NULL, NULL),
(49, 'Chicken And Cheese Salad', '', '', '', '', 13, 1, NULL, NULL),
(50, 'Yorkshire Lamb Patties', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">Lamb patties which melt in your mouth, and are quick and easy to make. Served hot with a crisp salad.</span><br></p>', NULL, NULL, NULL, 14, 1, NULL, '2021-07-20 12:15:12'),
(51, 'Margherita pizza', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Pizza Margherita is a typical Neapolitan pizza, made with San Marzano tomatoes, mozzarella cheese, fresh basil, salt, and extra-virgin olive oil.</span><br></p>', NULL, NULL, NULL, 15, 1, NULL, '2021-07-20 12:16:35'),
(52, 'Margherita pizza', '', '', '', '', 15, 1, NULL, NULL),
(53, 'Mushroom Risotto', '<p><span style=\"color: rgba(0, 0, 0, 0.95); font-family: \"Source Sans Pro\", \"Times New Roman\", serif; font-size: 18px;\">Authentic Italian-style risotto cooked the slow and painful way, but oh so worth it. Complements grilled meats and chicken dishes very well.</span><br></p>', NULL, NULL, NULL, 16, 1, NULL, '2021-08-05 05:14:36'),
(54, 'Margherita pizza', '', '', '', '', 15, 1, NULL, NULL),
(55, 'Mushroom Risotto', '', '', '', '', 16, 1, NULL, NULL),
(56, 'Focaccia Bread', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Focaccia is a flat oven-baked Italian bread similar in style and texture to pizza; in some places, it is called \"pizza bianca\".</span><br></p>', NULL, NULL, NULL, 17, 1, NULL, '2021-07-20 12:18:33'),
(57, 'Margherita pizza', '', '', '', '', 15, 1, NULL, NULL),
(58, 'Mushroom Risotto', '', '', '', '', 16, 1, NULL, NULL),
(59, 'Focaccia Bread ', '', '', '', '', 17, 1, NULL, NULL),
(60, 'Bruschetta', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Bruschetta is an antipasto from Italy consisting of grilled bread rubbed with garlic and topped with olive oil and salt.</span><br></p>', NULL, NULL, NULL, 18, 1, NULL, '2021-07-20 12:19:28'),
(61, 'Margherita pizza', '', '', '', '', 15, 1, NULL, NULL),
(62, 'Mushroom Risotto', '', '', '', '', 16, 1, NULL, NULL),
(63, 'Focaccia Bread ', '', '', '', '', 17, 1, NULL, NULL),
(64, 'Bruschetta', '', '', '', '', 18, 1, NULL, NULL),
(65, 'Pasta Carbonara', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Carbonara is an Italian pasta dish from Rome made with egg, hard cheese, cured pork, and black pepper. The dish arrived at its modern form, with its current name, in the middle of the 20th century.</span><br></p>', NULL, NULL, NULL, 19, 1, NULL, '2021-07-20 12:20:26'),
(66, 'Margherita pizza', '', '', '', '', 15, 1, NULL, NULL),
(67, 'Mushroom Risotto', '', '', '', '', 16, 1, NULL, NULL),
(68, 'Focaccia Bread ', '', '', '', '', 17, 1, NULL, NULL),
(69, 'Bruschetta', '', '', '', '', 18, 1, NULL, NULL),
(70, 'Pasta Carbonara', '', '', '', '', 19, 1, NULL, NULL),
(71, 'Caprese Salad with Pesto Sauce', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">Juicy tomato and mozzarella cheese salad with pesto sauce. Topped with the freshness of basil.</span><br></p>', NULL, NULL, NULL, 20, 1, NULL, '2021-07-20 12:21:21'),
(72, 'Dim Sums', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Dim sum is a large range of small Chinese dishes that are traditionally enjoyed in restaurants for breakfast and lunch.</span><br></p>', NULL, NULL, NULL, 21, 1, NULL, '2021-07-20 12:22:35'),
(73, 'Dim Sums', '', '', '', '', 21, 1, NULL, NULL),
(74, 'Hot and Sour Soup', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Hot and sour soup is a soup from Asian culinary traditions. In all cases, the soup contains ingredients to make it both spicy and sour.</span><br></p>', NULL, NULL, NULL, 22, 1, NULL, '2021-07-20 12:23:35'),
(75, 'Dim Sums', '', '', '', '', 21, 1, NULL, NULL),
(76, 'Hot and Sour Soup', '', '', '', '', 22, 1, NULL, NULL),
(77, 'Chicken with Chestnuts', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">Minced chicken stir fried with mushrooms, water chestnuts and radish and served on a bed of lettuce. It is a very comforting chinese dish enjoyed by mostly everyone.</span><br></p>', NULL, NULL, NULL, 23, 1, NULL, '2021-07-20 12:24:31'),
(78, 'Dim Sums', '', '', '', '', 21, 1, NULL, NULL),
(79, 'Hot and Sour Soup', '', '', '', '', 22, 1, NULL, NULL),
(80, 'Chicken with Chestnuts', '', '', '', '', 23, 1, NULL, NULL),
(81, 'Spring Rolls', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Spring rolls are a large variety of filled, rolled appetizers or dim sum found in East Asian, South Asian, Southeast Asian and Latin American cuisine.</span><br></p>', NULL, NULL, NULL, 24, 1, NULL, '2021-07-20 12:25:27'),
(82, 'Dim Sums', '', '', '', '', 21, 1, NULL, NULL),
(83, 'Hot and Sour Soup', '', '', '', '', 22, 1, NULL, NULL),
(84, 'Chicken with Chestnuts', '', '', '', '', 23, 1, NULL, NULL),
(85, 'Spring Rolls', '', '', '', '', 24, 1, NULL, NULL),
(86, 'Stir Fried Tofu with Rice', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">An easy,vegetarian, Chinese style recipe laden with sam pal oelic and dark soya sauce which enhances its flavours. With this tofu recipe you can\'t help but finish it all.</span><br></p>', NULL, NULL, NULL, 25, 1, NULL, '2021-07-20 12:26:21'),
(87, 'Dim Sums', '', '', '', '', 21, 1, NULL, NULL),
(88, 'Hot and Sour Soup', '', '', '', '', 22, 1, NULL, NULL),
(89, 'Chicken with Chestnuts', '', '', '', '', 23, 1, NULL, NULL),
(90, 'Spring Rolls', '', '', '', '', 24, 1, NULL, NULL),
(91, 'Stir Fried Tofu with Rice', '', '', '', '', 25, 1, NULL, NULL),
(92, 'Quick Noodles', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">Noodles tossed with veggies, lemon juice, peanuts and seasoned to the perfection. Kids would love this noodles recipe. Add seasoning according to your preference.</span><br></p>', NULL, NULL, NULL, 26, 1, NULL, '2021-07-20 12:28:14'),
(93, 'Almond and date cake', '<p><span style=\"color: rgb(45, 45, 45); font-family: Bariol, &quot;Open Sans&quot;, Arial, sans-serif; font-size: 14.2181px;\">This moist loaf cake is made with yogurt, chopped dates, toasted almonds and a hint of nutmeg.</span><br></p>', NULL, NULL, NULL, 27, 1, NULL, '2021-07-20 12:29:58'),
(94, 'Almond and date cake', '', '', '', '', 27, 1, NULL, NULL),
(95, 'Chocolate mousse', '<p><span style=\"color: rgb(61, 61, 61); font-family: Raleway, sans-serif; font-size: 18px;\">Chocolate Mousse may well be the ultimate chocolate fix! Rich and creamy, yet light and fluffy, one pot is satisfying but always leaves me wanting more.</span><br></p>', NULL, NULL, NULL, 28, 1, NULL, '2021-07-20 12:30:57'),
(96, 'Almond and date cake', '', '', '', '', 27, 1, NULL, NULL),
(97, 'Chocolate mousse', '', '', '', '', 28, 1, NULL, NULL),
(98, 'Roasted strawberry crumble', '<p><span style=\"color: rgb(61, 61, 61); font-family: Raleway, sans-serif; font-size: 18px;\">A fabulous strawberry dessert recipe that’s quick and easy to prepare! This self-saucing Strawberry Crumble is made with a load of fresh strawberries and topped with a streusel-like crumbly topping.&nbsp;</span><br></p>', NULL, NULL, NULL, 29, 1, NULL, '2021-07-20 12:33:02'),
(99, 'Almond and date cake', '', '', '', '', 27, 1, NULL, NULL),
(100, 'Chocolate mousse', '', '', '', '', 28, 1, NULL, NULL),
(101, 'Roasted strawberry crumble', '', '', '', '', 29, 1, NULL, NULL),
(102, 'Apple cinnamon custard cake', '<p><span style=\"color: rgb(63, 65, 64); font-family: &quot;Source Sans&quot;, sans-serif; font-size: 16px; text-align: center;\">Curtis Stone combines apples, cinnamon and sweet custard to make a delicious cake that is best served warm with whipped cream.</span><br></p>', NULL, NULL, NULL, 30, 1, NULL, '2021-07-20 12:34:10'),
(103, 'Almond and date cake', '', '', '', '', 27, 1, NULL, NULL),
(104, 'Chocolate mousse', '', '', '', '', 28, 1, NULL, NULL),
(105, 'Roasted strawberry crumble', '', '', '', '', 29, 1, NULL, NULL),
(106, 'Apple cinnamon custard cake', '', '', '', '', 30, 1, NULL, NULL),
(107, 'Tiramisu', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Tiramisu is a coffee-flavoured Italian dessert. It is made of ladyfingers dipped in coffee, layered with a whipped mixture of eggs, sugar, and mascarpone cheese, flavoured with cocoa.&nbsp;</span><br></p>', NULL, NULL, NULL, 31, 1, NULL, '2021-07-20 12:35:04'),
(108, 'Almond and date cake', '', '', '', '', 27, 1, NULL, NULL),
(109, 'Chocolate mousse', '', '', '', '', 28, 1, NULL, NULL),
(110, 'Roasted strawberry crumble', '', '', '', '', 29, 1, NULL, NULL),
(111, 'Apple cinnamon custard cake', '', '', '', '', 30, 1, NULL, NULL),
(112, 'Tiramisu', '', '', '', '', 31, 1, NULL, NULL),
(113, 'White Flower Basket Bouquet', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Delicate&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">white</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">&nbsp;lily&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">basket</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">&nbsp;makes a touching&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">floral</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">&nbsp;tribute for funerals. The arrangement is made from Pure&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">White</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">&nbsp;Oriental Lily&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">Flowers</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">,&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">White</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">&nbsp;Avalanche Roses.</span><br></p>', NULL, NULL, NULL, 32, 1, NULL, '2021-07-20 12:36:15'),
(114, 'Reese\'s Peanut Butter Cups', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Reese\'s Peanut Butter Cups are an American candy consisting of a chocolate cup filled with peanut butter, marketed by The Hershey Company.</span><br></p>', NULL, NULL, NULL, 33, 1, NULL, '2021-07-20 12:37:09'),
(115, 'Reese\'s Peanut Butter Cups', '', '', '', '', 33, 1, NULL, NULL),
(116, 'Milky Way Bars', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Milky Way is a brand of chocolate-covered confectionery bar manufactured and marketed by the Mars confectionery company.</span><br></p>', NULL, NULL, NULL, 34, 1, NULL, '2021-07-20 12:38:13'),
(117, 'Reese\'s Peanut Butter Cups', '', '', '', '', 33, 1, NULL, NULL),
(118, 'Milky Way Bars', '', '', '', '', 34, 1, NULL, NULL),
(119, 'Butterfinger', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Butterfinger is a candy bar manufactured by the Ferrara Candy Company, a subsidiary of Ferrero. The bar consists of a layered crispy peanut butter core covered in chocolate.</span><br></p>', NULL, NULL, NULL, 35, 1, NULL, '2021-07-20 12:39:18'),
(120, 'Reese\'s Peanut Butter Cups', '', '', '', '', 33, 1, NULL, NULL),
(121, 'Milky Way Bars', '', '', '', '', 34, 1, NULL, NULL),
(122, 'Butterfinger', '', '', '', '', 35, 1, NULL, NULL),
(123, '3 Musketeers', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Each&nbsp;</span><span style=\"color: rgb(95, 99, 104); font-family: arial, sans-serif;\">3 MUSKETEERS Chocolate</span><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">&nbsp;Bar is made of a light, fluffy, whipped chocolate center enrobed in rich milk chocolate.</span><br></p>', NULL, NULL, NULL, 36, 1, NULL, '2021-07-20 12:40:38'),
(124, 'Reese\'s Peanut Butter Cups', '', '', '', '', 33, 1, NULL, NULL),
(125, 'Milky Way Bars', '', '', '', '', 34, 1, NULL, NULL),
(126, 'Butterfinger', '', '', '', '', 35, 1, NULL, NULL),
(127, '3 Musketeers', '', '', '', '', 36, 1, NULL, NULL),
(128, 'Whatchamacallit', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Whatchamacallit is a candy bar marketed in the United States by The Hershey Company.</span><br></p>', NULL, NULL, NULL, 37, 1, NULL, '2021-07-20 12:42:11'),
(129, 'Reese\'s Peanut Butter Cups', '', '', '', '', 33, 1, NULL, NULL),
(130, 'Milky Way Bars', '', '', '', '', 34, 1, NULL, NULL),
(131, 'Butterfinger', '', '', '', '', 35, 1, NULL, NULL),
(132, '3 Musketeers', '', '', '', '', 36, 1, NULL, NULL),
(133, 'Whatchamacallit', '', '', '', '', 37, 1, NULL, NULL),
(134, 'Caprese Salad with Pesto Sauce', '<p><span style=\"color: rgb(68, 68, 68); font-family: Roboto, sans-serif; font-size: 16px;\">Juicy tomato and mozzarella cheese salad with pesto sauce. Topped with the freshness of basil.</span><br></p>', NULL, NULL, NULL, 38, 1, NULL, '2021-07-20 12:43:19'),
(135, 'Lemonade', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Lemonade is a sweetened lemon-flavored beverage. There are varieties of lemonade found throughout the world.</span><br></p>', NULL, NULL, NULL, 39, 1, NULL, '2021-07-20 12:45:37'),
(136, 'Lemonade', '', '', '', '', 39, 1, NULL, NULL),
(137, 'Hot chocolate', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Hot chocolate, also known as hot cocoa or drinking chocolate, is a heated drink consisting of shaved chocolate, melted chocolate or cocoa powder, heated milk or water, and usually a sweetener.</span><br></p>', NULL, NULL, NULL, 40, 1, NULL, '2021-07-20 12:46:50'),
(138, 'Lemonade', '', '', '', '', 39, 1, NULL, NULL),
(139, 'Hot chocolate', '', '', '', '', 40, 1, NULL, NULL),
(140, 'Iced tea', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Iced tea is a form of cold tea. Though usually served in a glass with ice, it can refer to any tea that has been chilled or cooled.</span><br></p>', NULL, NULL, NULL, 41, 1, NULL, '2021-07-20 12:47:52'),
(141, 'Lemonade', '', '', '', '', 39, 1, NULL, NULL),
(142, 'Hot chocolate', '', '', '', '', 40, 1, NULL, NULL),
(143, 'Iced tea', '', '', '', '', 41, 1, NULL, NULL),
(144, 'Smoothie', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">A smoothie, smoothy, or smuthi is a drink made from pureed raw fruit and/or vegetables, using a blender.</span><br></p>', NULL, NULL, NULL, 42, 1, NULL, '2021-07-20 12:48:55'),
(145, 'Lemonade', '', '', '', '', 39, 1, NULL, NULL),
(146, 'Hot chocolate', '', '', '', '', 40, 1, NULL, NULL),
(147, 'Iced tea', '', '', '', '', 41, 1, NULL, NULL),
(148, 'Smoothie', '', '', '', '', 42, 1, NULL, NULL),
(149, 'Orange juice', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Orange juice is a liquid extract of the orange tree fruit, produced by squeezing or reaming oranges.</span><br></p>', NULL, NULL, NULL, 43, 1, NULL, '2021-07-20 12:50:02'),
(150, 'Lemonade', '', '', '', '', 39, 1, NULL, NULL),
(151, 'Hot chocolate', '', '', '', '', 40, 1, NULL, NULL),
(152, 'Iced tea', '', '', '', '', 41, 1, NULL, NULL),
(153, 'Smoothie', '', '', '', '', 42, 1, NULL, NULL),
(154, 'Orange juice', '', '', '', '', 43, 1, NULL, NULL),
(155, 'Milkshake', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">A milkshake is a sweet drink made by blending milk, ice cream, and flavorings or sweeteners such as butterscotch, caramel sauce, chocolate syrup, fruit syrup, or whole fruit into a thick, sweet, cold mixture</span><br></p>', NULL, NULL, NULL, 44, 1, NULL, '2021-07-20 12:50:55'),
(156, 'Israeli salad', '<p><span style=\"color: rgba(0, 0, 0, 0.95); font-family: &quot;Source Sans Pro&quot;, &quot;Times New Roman&quot;, serif; font-size: 18px;\">Israeli salad can typically be found at the many falafel street stands all over Israel. It is served on its own as a side dish or inside a pita sandwich wrap</span><br></p>', NULL, NULL, NULL, 45, 1, NULL, '2021-07-20 12:52:34'),
(157, 'Israeli salad', '', '', '', '', 45, 1, NULL, NULL),
(158, 'Waldorf salad', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">A Waldorf salad is a fruit and nut salad generally made of fresh apples, celery, walnuts, and grapes, dressed in mayonnaise, and traditionally served on a bed of lettuce as an appetizer or a light meal.</span><br></p>', NULL, NULL, NULL, 46, 1, NULL, '2021-07-20 12:53:33'),
(159, 'Israeli salad', '', '', '', '', 45, 1, NULL, NULL),
(160, 'Waldorf salad', '', '', '', '', 46, 1, NULL, NULL),
(161, 'Gado-gado', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Gado-gado is an Indonesian salad of slightly boiled, blanched or steamed vegetables and hard-boiled eggs, boiled potato, fried tofu and tempeh, and lontong, served with a peanut sauce dressing.</span><br></p>', NULL, NULL, NULL, 47, 1, NULL, '2021-07-20 12:54:43'),
(162, 'Israeli salad', '', '', '', '', 45, 1, NULL, NULL),
(163, 'Waldorf salad', '', '', '', '', 46, 1, NULL, NULL),
(164, 'Gado-gado', '', '', '', '', 47, 1, NULL, NULL),
(165, 'Nicoise salad', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Salade niçoise, salada nissarda in the Niçard dialect of the Occitan language, insalata nizzarda in Italian, is a salad that originated in the French city of Nice.</span><br></p>', NULL, NULL, NULL, 48, 1, NULL, '2021-07-20 12:56:00'),
(166, 'Israeli salad', '', '', '', '', 45, 1, NULL, NULL),
(167, 'Waldorf salad', '', '', '', '', 46, 1, NULL, NULL),
(168, 'Gado-gado', '', '', '', '', 47, 1, NULL, NULL),
(169, 'Nicoise salad', '', '', '', '', 48, 1, NULL, NULL),
(170, 'Dressed herring salad', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Dressed herring, colloquially known as herring under a fur coat, is a layered salad composed of diced pickled herring covered with layers of grated boiled vegetables, chopped onions, and mayonnaise.</span><br></p>', NULL, NULL, NULL, 49, 1, NULL, '2021-07-20 12:57:15'),
(171, 'Israeli salad', '', '', '', '', 45, 1, NULL, NULL),
(172, 'Waldorf salad', '', '', '', '', 46, 1, NULL, NULL),
(173, 'Gado-gado', '', '', '', '', 47, 1, NULL, NULL),
(174, 'Nicoise salad', '', '', '', '', 48, 1, NULL, NULL),
(175, 'Dressed herring salad', '', '', '', '', 49, 1, NULL, NULL),
(176, 'Larb', '<p><span style=\"color: rgb(77, 81, 86); font-family: arial, sans-serif;\">Larb is a type of Lao meat salad that is the national dish of Laos, along with green papaya salad and sticky rice. </span><br></p>', NULL, NULL, NULL, 50, 1, NULL, '2021-08-05 05:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `product_up_sells`
--

CREATE TABLE `product_up_sells` (
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `upsell_product_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint UNSIGNED NOT NULL,
  `sku` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `price` decimal(16,8) DEFAULT NULL,
  `position` tinyint NOT NULL DEFAULT '1',
  `compare_at_price` decimal(16,8) DEFAULT NULL,
  `barcode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost_price` decimal(12,4) DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `inventory_policy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfillment_service` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_management` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 for avtive, 0 for inactive',
  `container_charges` decimal(12,4) DEFAULT '0.0000',
  `minimum_duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incremental_price` decimal(12,2) DEFAULT '0.00',
  `markup_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `incremental_price_per_min` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `expiry_date` date DEFAULT NULL,
  `rented_product_count` int NOT NULL DEFAULT '0',
  `square_variant_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `square_variant_version` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `sku`, `product_id`, `title`, `quantity`, `price`, `position`, `compare_at_price`, `barcode`, `cost_price`, `currency_id`, `tax_category_id`, `inventory_policy`, `fulfillment_service`, `inventory_management`, `created_at`, `updated_at`, `status`, `container_charges`, `minimum_duration`, `incremental_price`, `markup_price`, `incremental_price_per_min`, `expiry_date`, `rented_product_count`, `square_variant_id`, `square_variant_version`) VALUES
(1, 'sku-id', 1, NULL, 100, '500.00000000', 1, '500.00000000', '7543ebf012007e', '300.0000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(2, 'sku-id-1*5', 1, 'sku-id-Black-Black', 100, '500.00000000', 1, '500.00000000', '1500cdf2d597df', '300.0000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(3, 'sku-id-1*6', 1, 'sku-id-Black-Grey', 100, '500.00000000', 1, '500.00000000', '2ea56327679387', '300.0000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(4, 'sku-id-7*5', 1, 'sku-id-Medium-Black', 100, '500.00000000', 1, '500.00000000', '8f47f11a19433f', '300.0000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(5, 'sku-id-7*6', 1, 'sku-id-Medium-Grey', 100, '500.00000000', 1, '500.00000000', '8f7318b112bbe9', '300.0000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(6, 'GS100', 2, NULL, 33, '8.00000000', 1, '8.00000000', '5d1c71ab2a8ffa', '8.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:23:04', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(7, 'GS101', 3, NULL, 55, '6.00000000', 1, '6.00000000', 'c15e117a507ac1', '6.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:23:22', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(8, 'GS102', 4, NULL, 55, '5.00000000', 1, '5.00000000', '3042a5af566169', '5.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:23:43', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(9, 'GS103', 5, NULL, 33, '10.00000000', 1, '10.00000000', 'cd48f178f9d22b', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:24:59', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(10, 'GS104', 6, NULL, 33, '7.00000000', 1, '7.00000000', 'a7afb4fa125f92', '7.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:25:19', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(11, 'GS105', 7, NULL, 0, NULL, 1, NULL, 'b0a978ba16e35b', NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:22:38', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(12, 'GS106', 8, NULL, 22, '13.00000000', 1, '13.00000000', '66b1ab82bde98b', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-13 11:22:38', '2021-07-13 11:25:47', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(13, 'CN100', 9, NULL, 333, '12.00000000', 1, '17.00000000', 'a82af9d8e342be', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 12:08:22', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(14, 'CN101', 10, NULL, 222, '10.00000000', 1, '15.00000000', 'f2368ee11ffae6', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 12:09:51', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(15, 'CN102', 11, NULL, 340, '14.00000000', 1, '18.00000000', '9d951b820f1ccd', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 12:11:09', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(16, 'CN103', 12, NULL, 222, '13.00000000', 1, '17.00000000', '42b42519b18db5', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 12:12:40', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(17, 'CN104', 13, NULL, 333, '10.00000000', 1, '15.00000000', '20f6b40e091c41', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 12:14:12', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(18, 'CN105', 14, NULL, 440, '12.00000000', 1, '18.00000000', '56f1fbc40548e0', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:57:56', '2021-07-20 12:15:12', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(19, 'IT300', 15, NULL, 222, '12.00000000', 1, '17.00000000', '216747ddc5ffb5', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 12:16:35', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(20, 'IT301', 16, NULL, 333, '15.00000000', 1, '20.00000000', '74562a6176cae7', '15.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 12:17:42', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(21, 'IT302', 17, NULL, 222, '10.00000000', 1, '15.00000000', 'a4fa710c82a780', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 12:18:33', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(22, 'IT303', 18, NULL, 340, '13.00000000', 1, '17.00000000', '3ffe55acb5c586', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 12:19:28', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(23, 'IT304', 19, NULL, 222, '14.00000000', 1, '18.00000000', '5f982ba9dbc1c7', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 12:20:26', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(24, 'IT305', 20, NULL, 333, '10.00000000', 1, '15.00000000', '8ddbc0be1c575c', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:59:04', '2021-07-20 12:21:21', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(25, 'CH501', 21, NULL, 333, '12.00000000', 1, '15.00000000', '2e348fa167016a', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:22:35', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(26, 'CH502', 22, NULL, 340, '10.00000000', 1, '15.00000000', '4f77e523b2ec59', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:23:35', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(27, 'CH503', 23, NULL, 222, '13.00000000', 1, '17.00000000', '1e3abc11f46a90', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:24:31', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(28, 'CH504', 24, NULL, 333, '10.00000000', 1, '15.00000000', '3b04782729de6e', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:25:27', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(29, 'CH505', 25, NULL, 440, '12.00000000', 1, '15.00000000', '9ea10291091662', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:26:21', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(30, 'CH506', 26, NULL, 333, '10.00000000', 1, '15.00000000', 'a7712f9295dc56', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:00:19', '2021-07-20 12:28:14', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(31, 'D021', 27, NULL, 333, '10.00000000', 1, '15.00000000', '39cf9c823fef0f', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:29:58', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(32, 'D022', 28, NULL, 222, '12.00000000', 1, '15.00000000', 'b067db3c8d1c26', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:30:57', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(33, 'D023', 29, NULL, 340, '13.00000000', 1, '18.00000000', '0a6da30b1f3415', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:33:02', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(34, 'D024', 30, NULL, 222, '10.00000000', 1, '15.00000000', '26e6f75c9a2720', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:34:10', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(35, 'D025', 31, NULL, 444, '14.00000000', 1, '18.00000000', 'e2661d677a4a99', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:35:04', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(36, 'D026', 32, NULL, 333, '12.00000000', 1, '15.00000000', '144fc0f8b9e728', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:09', '2021-07-20 12:36:15', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(37, 'SN400', 33, NULL, 222, '13.00000000', 1, '17.00000000', '396d499f46a776', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:37:09', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(38, 'SN401', 34, NULL, 440, '10.00000000', 1, '15.00000000', '933a3dce743e65', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:38:13', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(39, 'SN402', 35, NULL, 333, '14.00000000', 1, '18.00000000', 'bf008ff184499d', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:39:18', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(40, 'SN403', 36, NULL, 222, '10.00000000', 1, '15.00000000', '4cb540c821b940', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:40:38', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(41, 'SN404', 37, NULL, 340, '13.00000000', 1, '17.00000000', 'c583371b1cc9b8', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:42:11', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(42, 'SN405', 38, NULL, 240, '14.00000000', 1, '18.00000000', '0099420ff6d92e', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:01:53', '2021-07-20 12:43:19', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(43, 'BV230', 39, NULL, 333, '10.00000000', 1, '15.00000000', '63345104e7f7c1', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:45:37', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(44, 'BV231', 40, NULL, 333, '12.00000000', 1, '17.00000000', '4c318846ecc7ae', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:46:50', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(45, 'BV232', 41, NULL, 222, '14.00000000', 1, '18.00000000', 'fac4e8e87e76a9', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:47:52', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(46, 'BV233', 42, NULL, 340, '10.00000000', 1, '15.00000000', 'b39a7359c26f2d', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:48:55', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(47, 'BV234', 43, NULL, 240, '12.00000000', 1, '17.00000000', '20ee805f875288', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:50:02', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(48, 'BV235', 44, NULL, 444, '10.00000000', 1, '15.00000000', 'feb72638436281', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:03', '2021-07-20 12:50:55', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(49, 'SL600', 45, NULL, 222, '14.00000000', 1, '18.00000000', '972d3df29c2e30', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:52:34', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(50, 'SL601', 46, NULL, 333, '12.00000000', 1, '17.00000000', '250a9d95469139', '12.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:53:33', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(51, 'SL602', 47, NULL, 250, '13.00000000', 1, '18.00000000', 'b6551446b8cd65', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:54:43', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(52, 'SL603', 48, NULL, 444, '10.00000000', 1, '15.00000000', 'c14759ceb5589e', '10.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:56:00', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(53, 'SL604', 49, NULL, 222, '14.00000000', 1, '18.00000000', '818ec3fc2bb7ee', '14.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:57:15', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL),
(54, 'SL605', 50, NULL, 333, '13.00000000', 1, '17.00000000', '8e54679950b446', '13.0000', NULL, NULL, NULL, NULL, NULL, '2021-07-20 12:05:42', '2021-07-20 12:58:18', 1, '0.0000', NULL, NULL, '0.00', '0', NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_by_roles`
--

CREATE TABLE `product_variant_by_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `quantity` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_images`
--

CREATE TABLE `product_variant_images` (
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `product_image_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_sets`
--

CREATE TABLE `product_variant_sets` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `variant_type_id` bigint UNSIGNED DEFAULT NULL,
  `variant_option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `product_variant_sets`
--

INSERT INTO `product_variant_sets` (`id`, `product_id`, `product_variant_id`, `variant_type_id`, `variant_option_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 1, NULL, NULL),
(2, 1, 2, 2, 5, NULL, NULL),
(3, 1, 3, 1, 1, NULL, NULL),
(4, 1, 3, 2, 6, NULL, NULL),
(5, 1, 3, 1, 1, NULL, NULL),
(6, 1, 3, 2, 6, NULL, NULL),
(7, 1, 4, 1, 7, NULL, NULL),
(8, 1, 4, 2, 5, NULL, NULL),
(9, 1, 5, 1, 7, NULL, NULL),
(10, 1, 5, 2, 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promocodes`
--

CREATE TABLE `promocodes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` mediumtext COLLATE utf8mb4_unicode_ci,
  `short_desc` mediumtext COLLATE utf8mb4_unicode_ci,
  `amount` decimal(16,8) DEFAULT NULL,
  `expiry_date` timestamp NULL DEFAULT NULL,
  `promo_type_id` bigint UNSIGNED DEFAULT NULL,
  `allow_free_delivery` tinyint DEFAULT '0' COMMENT '0- No, 1- yes',
  `minimum_spend` decimal(16,8) DEFAULT NULL,
  `maximum_spend` decimal(16,8) DEFAULT NULL,
  `first_order_only` tinyint DEFAULT '0' COMMENT '0- No, 1- yes',
  `limit_per_user` int DEFAULT NULL,
  `limit_total` int DEFAULT NULL,
  `paid_by_vendor_admin` tinyint DEFAULT NULL,
  `is_deleted` tinyint DEFAULT '0' COMMENT '0- No, 1- yes',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `restriction_on` tinyint DEFAULT '0' COMMENT '0- product, 1-vendor',
  `restriction_type` tinyint DEFAULT '0' COMMENT '0- Include, 1-Exclude',
  `added_by` bigint UNSIGNED DEFAULT NULL,
  `promo_visibility` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'public' COMMENT 'public/private',
  `promo_type` tinyint NOT NULL DEFAULT '0' COMMENT '1=refferal, 0=promo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `promocodes`
--

INSERT INTO `promocodes` (`id`, `name`, `title`, `short_desc`, `amount`, `expiry_date`, `promo_type_id`, `allow_free_delivery`, `minimum_spend`, `maximum_spend`, `first_order_only`, `limit_per_user`, `limit_total`, `paid_by_vendor_admin`, `is_deleted`, `created_by`, `image`, `created_at`, `updated_at`, `restriction_on`, `restriction_type`, `added_by`, `promo_visibility`, `promo_type`) VALUES
(1, '50UYDGF', '50% Off', 'Hurry 50% Off', '10.00000000', '2024-08-23 11:04:00', 1, 1, '10.00000000', '2000.00000000', 0, 5, 40, 1, 0, NULL, 'promocode/d1iAJnYATDpEypbYpzy5a9G4uR5ysJ3CZDM6nsev.jpg', '2021-07-20 11:54:56', '2021-08-05 11:59:45', 1, 0, 1, 'public', 0),
(2, '20DFRCV', '20% OFF', 'Get 20% Off Discount', '10.00000000', '2021-08-31 12:00:00', 1, 1, '10.00000000', '2000.00000000', 0, 32, 40, 1, 0, NULL, 'promocode/nTscBbOeWMXrOC0kJj4WT6wnlJC8ywfWM9DdNdFw.jpg', '2021-07-20 11:56:05', '2021-08-05 12:00:06', 1, 0, 1, 'public', 0);

-- --------------------------------------------------------

--
-- Table structure for table `promocode_details`
--

CREATE TABLE `promocode_details` (
  `id` bigint UNSIGNED NOT NULL,
  `promocode_id` bigint UNSIGNED DEFAULT NULL,
  `refrence_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promocode_details`
--

INSERT INTO `promocode_details` (`id`, `promocode_id`, `refrence_id`, `created_at`, `updated_at`) VALUES
(36, 1, 2, '2021-08-05 11:59:45', '2021-08-05 11:59:45'),
(37, 1, 3, '2021-08-05 11:59:45', '2021-08-05 11:59:45'),
(38, 1, 4, '2021-08-05 11:59:45', '2021-08-05 11:59:45'),
(39, 1, 5, '2021-08-05 11:59:45', '2021-08-05 11:59:45'),
(40, 1, 6, '2021-08-05 11:59:45', '2021-08-05 11:59:45'),
(41, 2, 2, '2021-08-05 12:00:07', '2021-08-05 12:00:07'),
(42, 2, 3, '2021-08-05 12:00:07', '2021-08-05 12:00:07'),
(43, 2, 4, '2021-08-05 12:00:07', '2021-08-05 12:00:07'),
(44, 2, 5, '2021-08-05 12:00:07', '2021-08-05 12:00:07'),
(45, 2, 6, '2021-08-05 12:00:07', '2021-08-05 12:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `promocode_restrictions`
--

CREATE TABLE `promocode_restrictions` (
  `promocode_id` bigint UNSIGNED DEFAULT NULL,
  `restriction_type` tinyint DEFAULT '0' COMMENT '0- product, 1-vendor, 2-category',
  `data_id` bigint UNSIGNED DEFAULT NULL,
  `is_included` tinyint NOT NULL DEFAULT '1' COMMENT '1 for yes, 0 for no',
  `is_excluded` tinyint NOT NULL DEFAULT '1' COMMENT '1 for yes, 0 for no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `promo_types`
--

CREATE TABLE `promo_types` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 - Active, 2 - Block, 3 - delete',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_types`
--

INSERT INTO `promo_types` (`id`, `title`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Percentage Discount', 1, NULL, NULL),
(2, 'Fixed Amount', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promo_usages`
--

CREATE TABLE `promo_usages` (
  `id` bigint UNSIGNED NOT NULL,
  `promocode_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `qrcode_imports`
--

CREATE TABLE `qrcode_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refer_and_earns`
--

CREATE TABLE `refer_and_earns` (
  `id` bigint UNSIGNED NOT NULL,
  `reffered_by_amount` decimal(8,2) DEFAULT NULL,
  `reffered_to_amount` decimal(8,2) DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `refer_and_earn_details`
--

CREATE TABLE `refer_and_earn_details` (
  `id` bigint UNSIGNED NOT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `attribute_option_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `influencer_user_id` bigint UNSIGNED DEFAULT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reschedule_orders`
--

CREATE TABLE `reschedule_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `reschedule_by` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `prev_schedule_pickup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prev_schedule_dropoff` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prev_scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prev_dropoff_scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_schedule_pickup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_schedule_dropoff` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_dropoff_scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_reasons`
--

CREATE TABLE `return_reasons` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','Block') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `order` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 for Return, 2 for Exchange, 3 for Cancellation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `return_reasons`
--

INSERT INTO `return_reasons` (`id`, `title`, `status`, `order`, `created_at`, `updated_at`, `type`) VALUES
(1, 'The merchant shipped the wrong item', 'Active', 1, NULL, NULL, 1),
(2, 'Purchase arrived too late', 'Active', 2, NULL, NULL, 1),
(3, 'Customer doesn\'t need it anymore', 'Active', 3, NULL, NULL, 1),
(4, 'The product was damaged or defective', 'Active', 4, NULL, NULL, 1),
(5, 'Other', 'Active', 5, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dial_code` int DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL COMMENT '0 - pending, 1 - active',
  `is_enable_pricing` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role`, `status`, `is_enable_pricing`, `created_at`, `updated_at`, `name`) VALUES
(1, 'Buyer', 1, 0, NULL, NULL, 'Buyer'),
(2, 'Seller', 1, 0, NULL, NULL, 'Seller'),
(3, 'Corporate_user', 1, 0, NULL, NULL, 'Corporate User');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
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
(1, 4),
(2, 4),
(3, 4),
(4, 4),
(5, 4),
(6, 4),
(7, 4),
(8, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 4),
(13, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 4),
(19, 4),
(20, 4),
(21, 4),
(55, 4),
(56, 4),
(63, 4),
(64, 4),
(65, 4),
(66, 4),
(69, 4),
(73, 4),
(79, 4),
(80, 4),
(81, 4);

-- --------------------------------------------------------

--
-- Table structure for table `service_areas`
--

CREATE TABLE `service_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `geo_array` text COLLATE utf8mb4_unicode_ci,
  `zoom_level` smallint NOT NULL DEFAULT '13',
  `polygon` geometry DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active_for_vendor_slot` tinyint DEFAULT '0',
  `area_type` tinyint DEFAULT '1' COMMENT '1-vendor, 0-Admin',
  `primary_language` tinyint DEFAULT NULL,
  `primary_currency` tinyint DEFAULT NULL,
  `country_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `service_areas`
--

INSERT INTO `service_areas` (`id`, `name`, `description`, `geo_array`, `zoom_level`, `polygon`, `vendor_id`, `created_at`, `updated_at`, `is_active_for_vendor_slot`, `area_type`, `primary_language`, `primary_currency`, `country_code`) VALUES
(1, 'South Africa', 'South Africa', '(-19.085456960282578, 19.453898715979978),(-18.169316903296583, 34.043742465979975),(-29.93825420574068, 38.789836215979975),(-38.84182791726503, 28.594523715979978),(-28.248725992106984, 4.160929965979978),(-23.021581612947386, 13.653117465979978)', 4, 0x000000000103000000010000000700000016a1e181e01533c095d4ccb4327433409e51425a582b32c04a6a665a990541409dfd786d31f03dc04a6a665a19654340adc16604c16b43c095d4ccb432983c40c0c3b181ac3f3cc0545233d3caa41040d0cd615f860537c02aa99969654e2b4016a1e181e01533c095d4ccb432743340, 6, '2021-07-13 10:55:53', '2021-07-13 10:55:53', 0, 1, NULL, NULL, NULL),
(2, 'South Africa', 'South Africa', '(-19.83128617504635, 15.333725862477294),(-20.32657631238387, 43.019272737477294),(-32.93721335540002, 41.261460237477294),(-38.292073185265615, 35.724350862477294),(-39.455263031699346, 13.575913362477294),(-31.599571176827332, 5.138413362477294),(-22.45416466428993, 7.247788362477294)', 4, 0x0000000001030000000100000008000000e670b72bcfd433c0b856c21ddeaa2e40465655819a5334c0ae95708777824540a468739bf67740c0ae95708777a144408f6075a7622543c0ae957087b7dc4140fe1c1c0f46ba43c0b856c21dde262b40fe18247f7d993fc070ad843bbc8d1440c31fac22447436c070ad843bbcfd1c40e670b72bcfd433c0b856c21ddeaa2e40, 5, '2021-07-13 10:57:22', '2021-07-13 10:57:22', 0, 1, NULL, NULL, NULL),
(3, 'South Africa', 'South Africa', '(-23.23173348563368, 14.279038362477294),(-20.54071311057266, 40.030991487477294),(-35.093374993617275, 43.370835237477294),(-36.66002749162487, 26.407944612477294),(-35.88057445635988, 8.302475862477294),(-28.83551000120723, 5.489975862477294)', 4, 0x00000000010300000001000000070000004b2fbee2523b37c0b856c21dde8e2c40926da62c6c8a34c0ae957087f7034440daec37b6f38b41c0ae95708777af4540af7ee5c77b5442c05c2be10e6f683a401ce1eda9b6f041c0b856c21dde9a20407daac2fbe3d53cc070ad843bbcf515404b2fbee2523b37c0b856c21dde8e2c40, 4, '2021-07-13 10:59:23', '2021-07-13 10:59:23', 0, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_area_for_banners`
--

CREATE TABLE `service_area_for_banners` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `geo_array` text COLLATE utf8mb4_unicode_ci,
  `zoom_level` smallint NOT NULL DEFAULT '13',
  `polygon` geometry DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1-Web, 2-Mobile'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_options`
--

CREATE TABLE `shipping_options` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` json DEFAULT NULL COMMENT 'credentials in json format',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 inactive, 1 active, 2 delete',
  `test_mode` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = false, 1 = true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_options`
--

INSERT INTO `shipping_options` (`id`, `code`, `path`, `title`, `credentials`, `status`, `test_mode`, `created_at`, `updated_at`) VALUES
(1, 'shiprocket', '', 'ShipRocket', NULL, 0, 0, NULL, NULL),
(2, 'lalamove', '', 'Lalamove', NULL, 0, 0, '2022-01-17 10:39:53', '2022-01-17 10:39:53'),
(3, 'dunzo', '', 'Dunzo', NULL, 0, 0, '2022-02-16 12:44:42', '2022-02-16 12:44:42'),
(4, 'ahoy', '', 'Ahoy', NULL, 0, 0, '2022-02-16 12:44:42', '2022-02-16 12:44:42'),
(5, 'shippo', '', 'Shippo', NULL, 0, 0, '2022-05-19 12:41:21', '2022-05-19 12:41:21'),
(6, 'kwikapi', '', 'KwikApi', NULL, 0, 0, '2023-02-06 11:11:32', '2023-02-06 11:11:32'),
(7, 'roadie', '', 'Roadie', NULL, 0, 0, '2023-07-21 07:33:51', '2023-07-21 07:33:51');

-- --------------------------------------------------------

--
-- Table structure for table `shippo_delivery_options`
--

CREATE TABLE `shippo_delivery_options` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zipcode_from` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zipcode_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `json` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `show_subscription_plan_on_signups`
--

CREATE TABLE `show_subscription_plan_on_signups` (
  `id` bigint UNSIGNED NOT NULL,
  `show_plan_customer` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `every_sign_up` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `every_app_open` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slot_days`
--

CREATE TABLE `slot_days` (
  `id` bigint UNSIGNED NOT NULL,
  `slot_id` bigint UNSIGNED DEFAULT NULL,
  `day` tinyint NOT NULL DEFAULT '0' COMMENT '1 sunday, 2 monday, 3 tuesday, 4 wednesday, 5 thursday, 6 friday, 7 saturday',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `sms_providers`
--

CREATE TABLE `sms_providers` (
  `id` bigint UNSIGNED NOT NULL,
  `provider` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyword` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT ' 0 for no, 1 for yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_providers`
--

INSERT INTO `sms_providers` (`id`, `provider`, `keyword`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Twilio Service', 'twilio', 1, NULL, NULL),
(2, 'mTalkz Service', 'mTalkz', 1, NULL, NULL),
(3, 'Mazinhost Service', 'mazinhost', 1, '2021-12-24 09:10:26', '2021-12-24 09:10:26'),
(4, 'Unifonic Service', 'unifonic', 1, '2022-02-01 11:34:52', '2022-02-01 11:34:52'),
(5, 'Arkesel Service', 'arkesel', 1, NULL, NULL),
(6, 'Africa\'s Talking Ser', 'afrTalk', 1, NULL, '2023-05-23 09:52:18'),
(7, 'Vonage (nexmo)', 'vonage', 1, NULL, NULL),
(8, 'SMS Partner France', 'sms_partner', 1, NULL, NULL),
(9, 'Ethiopia', 'ethiopia', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sms_templates`
--

CREATE TABLE `sms_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `tags` mediumtext COLLATE utf8mb4_unicode_ci,
  `label` mediumtext COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `subject` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `template_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_templates`
--

INSERT INTO `sms_templates` (`id`, `slug`, `tags`, `label`, `content`, `subject`, `created_at`, `updated_at`, `template_id`) VALUES
(1, 'order-place-Successfully', '{user_name},{amount},{order_number}', 'Order Placed Successfully', 'Hi {user_name} Your order of amount {amount} for order number {order_number}', 'Order Placed Successfully', '2022-07-11 12:17:26', '2022-07-11 12:17:26', NULL),
(2, 'otp-sms-vendor-login', '{otp_code}', 'Otp Sms For Vendor Login', 'Please enter otp-{otp_code}. Keep it safe and don\'t show to other.', 'Otp Sms For Vendor Login', '2022-11-11 05:55:52', '2022-11-11 05:55:52', NULL),
(3, 'otp-sms-user-signup', '{otp_code}', 'Otp Sms For User Signup', 'Please enter otp-{otp_code}. Keep it safe and don\'t show to other.', 'Otp Sms User For Signup', '2022-11-11 05:55:52', '2022-11-11 05:55:52', NULL),
(4, 'otp-sms-user-login', '{otp_code}', 'Otp Sms For User Login', 'Please enter otp-{otp_code}. Keep it safe and don\'t show to other.', 'Otp Sms For User Login', '2022-11-11 05:55:52', '2022-11-11 05:55:52', NULL),
(5, 'user-signup-sms', '{user_name}', 'User Signup Sms', 'Dear {user_name}, Thanks for creating an account with us!', 'User Signup Sms', '2022-11-11 05:55:52', '2022-11-11 05:55:52', NULL),
(6, 'verify-account', '{user_name},{otp_code},{app_hash_key}', 'Otp to verify Account', 'Dear {user_name}, Please enter OTP {otp_code} to verify your account.{app_hash_key}', 'Otp to verify Account', '2022-11-11 05:55:52', '2022-11-11 05:55:52', NULL),
(7, 'order-tracking-url', '{user_name},{amount},{order_number},{track_url},{order_status}', 'Order Tracking', 'Hi {user_name} Your order number {order_number} has been on the way.please track your order via this link {track_url}', 'Order Tracking', '2023-01-09 06:01:38', '2023-01-09 06:01:38', NULL),
(8, 'otp-sms-tracking-url', '{otp_code}', 'Otp Sms For Tracking url', 'Please enter OTP {otp_code}. Keep it safe and don\'t show to other.', 'Otp Sms Access For Tracking Url', '2023-01-09 06:01:38', '2023-01-09 06:01:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `social_credentials`
--

CREATE TABLE `social_credentials` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `access_token` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media`
--

CREATE TABLE `social_media` (
  `id` bigint UNSIGNED NOT NULL,
  `icon` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `social_media`
--

INSERT INTO `social_media` (`id`, `icon`, `title`, `url`, `created_at`, `updated_at`) VALUES
(1, 'facebook', NULL, 'https://www.facebook.com', '2021-09-17 05:52:33', '2021-09-17 05:52:33'),
(2, 'instagram', NULL, 'https://www.instagram.com', '2021-09-17 05:52:40', '2021-09-17 05:52:40'),
(3, 'twitter', NULL, 'https://twitter.com', '2021-09-17 05:52:50', '2021-09-17 05:52:50');

-- --------------------------------------------------------

--
-- Table structure for table `square_timestamp`
--

CREATE TABLE `square_timestamp` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `static_dropoff_locations`
--

CREATE TABLE `static_dropoff_locations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(15,12) DEFAULT NULL,
  `longitude` decimal(16,12) DEFAULT NULL,
  `place_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_features_list_user`
--

CREATE TABLE `subscription_features_list_user` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Description` longtext COLLATE utf8mb4_unicode_ci,
  `status` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT '0=Inactive, 1=Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_features_list_user`
--

INSERT INTO `subscription_features_list_user` (`id`, `title`, `Description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Free Delivery', '', 1, '2021-07-20 11:54:19', '2022-05-02 12:17:43'),
(2, '% Off On Order', '', 1, '2022-05-02 12:17:43', '2022-05-02 12:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_features_list_vendor`
--

CREATE TABLE `subscription_features_list_vendor` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Description` longtext COLLATE utf8mb4_unicode_ci,
  `status` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT '0=Inactive, 1=Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_features_list_vendor`
--

INSERT INTO `subscription_features_list_vendor` (`id`, `title`, `Description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Trending', '', 1, '2021-07-20 11:54:24', '2021-07-20 11:54:24');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_invoices_user`
--

CREATE TABLE `subscription_invoices_user` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `payment_option_id` tinyint UNSIGNED DEFAULT NULL,
  `status_id` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_reference` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `subscription_amount` decimal(16,8) DEFAULT NULL,
  `discount_amount` decimal(16,8) DEFAULT NULL,
  `paid_amount` decimal(16,8) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_invoices_vendor`
--

CREATE TABLE `subscription_invoices_vendor` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `payment_option_id` tinyint UNSIGNED DEFAULT NULL,
  `status_id` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_reference` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `subscription_amount` decimal(16,8) DEFAULT NULL,
  `discount_amount` decimal(16,8) DEFAULT NULL,
  `paid_amount` decimal(16,8) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `rejected_by` bigint UNSIGNED DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_count` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_invoice_features_user`
--

CREATE TABLE `subscription_invoice_features_user` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `subscription_invoice_id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `feature_id` bigint UNSIGNED DEFAULT NULL,
  `feature_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `percent_value` decimal(5,2) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_invoice_features_vendor`
--

CREATE TABLE `subscription_invoice_features_vendor` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `subscription_invoice_id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `feature_id` bigint UNSIGNED DEFAULT NULL,
  `feature_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_log_user`
--

CREATE TABLE `subscription_log_user` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `subscription_invoice_id` bigint UNSIGNED DEFAULT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `payment_option_id` tinyint UNSIGNED DEFAULT NULL,
  `status_id` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `transaction_reference` text COLLATE utf8mb4_unicode_ci,
  `start_date` datetime DEFAULT NULL,
  `next_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `subscription_amount` decimal(16,8) DEFAULT NULL,
  `discount_amount` decimal(16,8) DEFAULT NULL,
  `paid_amount` decimal(16,8) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans_user`
--

CREATE TABLE `subscription_plans_user` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(16,8) DEFAULT '0.00000000',
  `period` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'plan validity in days',
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint NOT NULL DEFAULT '1' COMMENT 'for same position, display asc order',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0=Inactive, 1=Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans_vendor`
--

CREATE TABLE `subscription_plans_vendor` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(16,8) DEFAULT '0.00000000',
  `period` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'plan validity in days',
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint NOT NULL DEFAULT '1' COMMENT 'for same position, display asc order',
  `status` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '0=Inactive, 1=Active',
  `on_request` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `order_count` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plan_features_user`
--

CREATE TABLE `subscription_plan_features_user` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_plan_id` bigint UNSIGNED NOT NULL,
  `feature_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `percent_value` decimal(5,2) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plan_features_vendor`
--

CREATE TABLE `subscription_plan_features_vendor` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_plan_id` bigint UNSIGNED NOT NULL,
  `feature_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_status_options`
--

CREATE TABLE `subscription_status_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` smallint UNSIGNED NOT NULL DEFAULT '1' COMMENT '0-Inactive, 1-Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_status_options`
--

INSERT INTO `subscription_status_options` (`id`, `title`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Pending', 1, '2021-07-20 11:54:29', '2021-07-20 11:54:29'),
(2, 'Active', 1, '2021-07-20 11:54:29', '2021-07-20 11:54:29'),
(3, 'Inactive', 1, '2021-07-20 11:54:29', '2021-07-20 11:54:29'),
(4, 'Cancelled', 1, '2021-07-20 11:54:29', '2021-07-20 11:54:29');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tag_translations`
--

CREATE TABLE `tag_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `language_id` bigint UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax_categories`
--

CREATE TABLE `tax_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_core` tinyint NOT NULL DEFAULT '1' COMMENT '0 - no, 1 - yes',
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `tax_categories`
--

INSERT INTO `tax_categories` (`id`, `title`, `code`, `description`, `is_core`, `vendor_id`, `created_at`, `updated_at`) VALUES
(1, 'VAT', 'vat', NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax_rates`
--

CREATE TABLE `tax_rates` (
  `id` bigint UNSIGNED NOT NULL,
  `identifier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_zip` tinyint NOT NULL DEFAULT '1' COMMENT '0 - no, 1 - yes',
  `zip_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_from` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_to` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_rate` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `square_tax_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `square_tax_version` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `tax_rates`
--

INSERT INTO `tax_rates` (`id`, `identifier`, `is_zip`, `zip_code`, `zip_from`, `zip_to`, `state`, `country`, `tax_rate`, `tax_amount`, `created_at`, `updated_at`, `square_tax_id`, `square_tax_version`) VALUES
(1, 'VAT', 0, '', '', '', 'Dubai', 'United Arab Emirates', '5.00', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax_rate_categories`
--

CREATE TABLE `tax_rate_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `tax_cate_id` bigint UNSIGNED NOT NULL,
  `tax_rate_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `tax_rate_categories`
--

INSERT INTO `tax_rate_categories` (`id`, `tax_cate_id`, `tax_rate_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `for` tinyint NOT NULL DEFAULT '0' COMMENT '1 for web, 2 for app',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `name`, `image`, `for`, `created_at`, `updated_at`) VALUES
(1, 'Default', 'default/templete.jpg', 1, NULL, NULL),
(2, 'Default', 'default/templete.jpg', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `temp_carts`
--

CREATE TABLE `temp_carts` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `status` enum('0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0-Active, 1-Blocked, 2-Deleted',
  `is_gift` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0-Yes, 1-No',
  `item_count` int DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `schedule_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `specific_instructions` text COLLATE utf8mb4_unicode_ci,
  `comment_for_pickup_driver` mediumtext COLLATE utf8mb4_unicode_ci,
  `comment_for_dropoff_driver` mediumtext COLLATE utf8mb4_unicode_ci,
  `comment_for_vendor` mediumtext COLLATE utf8mb4_unicode_ci,
  `schedule_pickup` datetime DEFAULT NULL,
  `schedule_dropoff` datetime DEFAULT NULL,
  `scheduled_slot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `address_id` bigint UNSIGNED DEFAULT NULL,
  `is_submitted` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `is_approved` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `order_payable_amount` decimal(16,4) DEFAULT '0.0000',
  `vendor_wallet_amount_used` decimal(16,4) DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_cart_addons`
--

CREATE TABLE `temp_cart_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `cart_product_id` bigint UNSIGNED NOT NULL,
  `addon_id` bigint UNSIGNED NOT NULL,
  `option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_cart_coupons`
--

CREATE TABLE `temp_cart_coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_cart_products`
--

CREATE TABLE `temp_cart_products` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_dinein_table_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL COMMENT '0-Active, 1-Blocked, 2-Deleted',
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `is_tax_applied` tinyint NOT NULL COMMENT '0-Yes, 1-No',
  `tax_rate_id` bigint UNSIGNED DEFAULT NULL,
  `schedule_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date_time` datetime DEFAULT NULL,
  `tax_category_id` bigint UNSIGNED DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL,
  `luxury_option_id` bigint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `dispatch_agent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'driver id',
  `dispatch_agent_price` decimal(16,4) DEFAULT '0.0000',
  `is_payment_done` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_cart_vendor_delivery_fee`
--

CREATE TABLE `temp_cart_vendor_delivery_fee` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_fee` decimal(64,0) NOT NULL DEFAULT '0',
  `shipping_delivery_type` enum('D','L','S') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D' COMMENT 'D : Dispatcher , L : Lalamove ,S : Static',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terminologies`
--

CREATE TABLE `terminologies` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `third_party_accounting`
--

CREATE TABLE `third_party_accounting` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` json DEFAULT NULL COMMENT 'credentials in json format',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 inactive, 1 active, 2 delete',
  `test_mode` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = false, 1 = true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `third_party_accounting`
--

INSERT INTO `third_party_accounting` (`id`, `code`, `path`, `title`, `credentials`, `status`, `test_mode`, `created_at`, `updated_at`) VALUES
(1, 'xero', 'xeroapi/xero-php-oauth2', 'Xero', NULL, 0, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timezones`
--

CREATE TABLE `timezones` (
  `id` bigint UNSIGNED NOT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offset` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `diff_from_gtm` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timezones`
--

INSERT INTO `timezones` (`id`, `timezone`, `offset`, `diff_from_gtm`, `created_at`, `updated_at`) VALUES
(1, 'Africa/Abidjan', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(2, 'Africa/Accra', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(3, 'Africa/Addis_Ababa', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(4, 'Africa/Algiers', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(5, 'Africa/Asmara', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(6, 'Africa/Bamako', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(7, 'Africa/Bangui', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(8, 'Africa/Banjul', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(9, 'Africa/Bissau', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(10, 'Africa/Blantyre', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(11, 'Africa/Brazzaville', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(12, 'Africa/Bujumbura', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(13, 'Africa/Cairo', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(14, 'Africa/Casablanca', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(15, 'Africa/Ceuta', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(16, 'Africa/Conakry', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(17, 'Africa/Dakar', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(18, 'Africa/Dar_es_Salaam', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(19, 'Africa/Djibouti', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(20, 'Africa/Douala', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(21, 'Africa/El_Aaiun', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(22, 'Africa/Freetown', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(23, 'Africa/Gaborone', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(24, 'Africa/Harare', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(25, 'Africa/Johannesburg', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(26, 'Africa/Juba', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(27, 'Africa/Kampala', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(28, 'Africa/Khartoum', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(29, 'Africa/Kigali', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(30, 'Africa/Kinshasa', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(31, 'Africa/Lagos', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(32, 'Africa/Libreville', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(33, 'Africa/Lome', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(34, 'Africa/Luanda', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(35, 'Africa/Lubumbashi', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(36, 'Africa/Lusaka', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(37, 'Africa/Malabo', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(38, 'Africa/Maputo', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(39, 'Africa/Maseru', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(40, 'Africa/Mbabane', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(41, 'Africa/Mogadishu', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(42, 'Africa/Monrovia', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(43, 'Africa/Nairobi', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:05', '2021-07-13 13:41:05'),
(44, 'Africa/Ndjamena', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(45, 'Africa/Niamey', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(46, 'Africa/Nouakchott', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(47, 'Africa/Ouagadougou', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(48, 'Africa/Porto-Novo', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(49, 'Africa/Sao_Tome', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:05', '2021-07-13 10:41:05'),
(50, 'Africa/Tripoli', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(51, 'Africa/Tunis', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:05', '2021-07-13 11:41:05'),
(52, 'Africa/Windhoek', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:05', '2021-07-13 12:41:05'),
(53, 'America/Adak', '-09:00', 'UTC/GMT -09:00', '2021-07-13 01:41:05', '2021-07-13 01:41:05'),
(54, 'America/Anchorage', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:05', '2021-07-13 02:41:05'),
(55, 'America/Anguilla', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(56, 'America/Antigua', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(57, 'America/Araguaina', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(58, 'America/Argentina/Buenos_Aires', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(59, 'America/Argentina/Catamarca', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(60, 'America/Argentina/Cordoba', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(61, 'America/Argentina/Jujuy', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(62, 'America/Argentina/La_Rioja', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(63, 'America/Argentina/Mendoza', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(64, 'America/Argentina/Rio_Gallegos', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(65, 'America/Argentina/Salta', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(66, 'America/Argentina/San_Juan', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(67, 'America/Argentina/San_Luis', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(68, 'America/Argentina/Tucuman', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(69, 'America/Argentina/Ushuaia', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(70, 'America/Aruba', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(71, 'America/Asuncion', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(72, 'America/Atikokan', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:05', '2021-07-13 05:41:05'),
(73, 'America/Bahia', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(74, 'America/Bahia_Banderas', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:05', '2021-07-13 05:41:05'),
(75, 'America/Barbados', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(76, 'America/Belem', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:05', '2021-07-13 07:41:05'),
(77, 'America/Belize', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:05', '2021-07-13 04:41:05'),
(78, 'America/Blanc-Sablon', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(79, 'America/Boa_Vista', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:05', '2021-07-13 06:41:05'),
(80, 'America/Bogota', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:05', '2021-07-13 05:41:05'),
(81, 'America/Boise', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:05', '2021-07-13 04:41:05'),
(82, 'America/Cambridge_Bay', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(83, 'America/Campo_Grande', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(84, 'America/Cancun', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(85, 'America/Caracas', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(86, 'America/Cayenne', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(87, 'America/Cayman', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(88, 'America/Chicago', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(89, 'America/Chihuahua', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(90, 'America/Costa_Rica', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(91, 'America/Creston', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(92, 'America/Cuiaba', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(93, 'America/Curacao', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(94, 'America/Danmarkshavn', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:06', '2021-07-13 10:41:06'),
(95, 'America/Dawson', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(96, 'America/Dawson_Creek', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(97, 'America/Denver', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(98, 'America/Detroit', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(99, 'America/Dominica', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(100, 'America/Edmonton', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(101, 'America/Eirunepe', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(102, 'America/El_Salvador', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(103, 'America/Fort_Nelson', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(104, 'America/Fortaleza', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(105, 'America/Glace_Bay', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(106, 'America/Goose_Bay', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(107, 'America/Grand_Turk', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(108, 'America/Grenada', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(109, 'America/Guadeloupe', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(110, 'America/Guatemala', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(111, 'America/Guayaquil', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(112, 'America/Guyana', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(113, 'America/Halifax', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(114, 'America/Havana', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(115, 'America/Hermosillo', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(116, 'America/Indiana/Indianapolis', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(117, 'America/Indiana/Knox', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(118, 'America/Indiana/Marengo', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(119, 'America/Indiana/Petersburg', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(120, 'America/Indiana/Tell_City', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(121, 'America/Indiana/Vevay', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(122, 'America/Indiana/Vincennes', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(123, 'America/Indiana/Winamac', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(124, 'America/Inuvik', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(125, 'America/Iqaluit', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(126, 'America/Jamaica', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(127, 'America/Juneau', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:06', '2021-07-13 02:41:06'),
(128, 'America/Kentucky/Louisville', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(129, 'America/Kentucky/Monticello', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(130, 'America/Kralendijk', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(131, 'America/La_Paz', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(132, 'America/Lima', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(133, 'America/Los_Angeles', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(134, 'America/Lower_Princes', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(135, 'America/Maceio', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(136, 'America/Managua', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(137, 'America/Manaus', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(138, 'America/Marigot', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(139, 'America/Martinique', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(140, 'America/Matamoros', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(141, 'America/Mazatlan', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(142, 'America/Menominee', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(143, 'America/Merida', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(144, 'America/Metlakatla', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:06', '2021-07-13 02:41:06'),
(145, 'America/Mexico_City', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(146, 'America/Miquelon', '-02:00', 'UTC/GMT -02:00', '2021-07-13 08:41:06', '2021-07-13 08:41:06'),
(147, 'America/Moncton', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(148, 'America/Monterrey', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(149, 'America/Montevideo', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(150, 'America/Montserrat', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(151, 'America/Nassau', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(152, 'America/New_York', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(153, 'America/Nipigon', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(154, 'America/Nome', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:06', '2021-07-13 02:41:06'),
(155, 'America/Noronha', '-02:00', 'UTC/GMT -02:00', '2021-07-13 08:41:06', '2021-07-13 08:41:06'),
(156, 'America/North_Dakota/Beulah', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(157, 'America/North_Dakota/Center', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(158, 'America/North_Dakota/New_Salem', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(159, 'America/Nuuk', '-02:00', 'UTC/GMT -02:00', '2021-07-13 08:41:06', '2021-07-13 08:41:06'),
(160, 'America/Ojinaga', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(161, 'America/Panama', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(162, 'America/Pangnirtung', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(163, 'America/Paramaribo', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(164, 'America/Phoenix', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(165, 'America/Port-au-Prince', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(166, 'America/Port_of_Spain', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(167, 'America/Porto_Velho', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(168, 'America/Puerto_Rico', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(169, 'America/Punta_Arenas', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(170, 'America/Rainy_River', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(171, 'America/Rankin_Inlet', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(172, 'America/Recife', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(173, 'America/Regina', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(174, 'America/Resolute', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(175, 'America/Rio_Branco', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(176, 'America/Santarem', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(177, 'America/Santiago', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(178, 'America/Santo_Domingo', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(179, 'America/Sao_Paulo', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(180, 'America/Scoresbysund', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:06', '2021-07-13 10:41:06'),
(181, 'America/Sitka', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:06', '2021-07-13 02:41:06'),
(182, 'America/St_Barthelemy', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(183, 'America/St_Johns', '-02:30', 'UTC/GMT -02:30', '2021-07-13 08:11:06', '2021-07-13 08:11:06'),
(184, 'America/St_Kitts', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(185, 'America/St_Lucia', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(186, 'America/St_Thomas', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(187, 'America/St_Vincent', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(188, 'America/Swift_Current', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(189, 'America/Tegucigalpa', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(190, 'America/Thule', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(191, 'America/Thunder_Bay', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(192, 'America/Tijuana', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(193, 'America/Toronto', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(194, 'America/Tortola', '-04:00', 'UTC/GMT -04:00', '2021-07-13 06:41:06', '2021-07-13 06:41:06'),
(195, 'America/Vancouver', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(196, 'America/Whitehorse', '-07:00', 'UTC/GMT -07:00', '2021-07-13 03:41:06', '2021-07-13 03:41:06'),
(197, 'America/Winnipeg', '-05:00', 'UTC/GMT -05:00', '2021-07-13 05:41:06', '2021-07-13 05:41:06'),
(198, 'America/Yakutat', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:06', '2021-07-13 02:41:06'),
(199, 'America/Yellowknife', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:06', '2021-07-13 04:41:06'),
(200, 'Antarctica/Casey', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:06', '2021-07-13 21:41:06'),
(201, 'Antarctica/Davis', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:06', '2021-07-13 17:41:06'),
(202, 'Antarctica/DumontDUrville', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:06', '2021-07-13 20:41:06'),
(203, 'Antarctica/Macquarie', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:06', '2021-07-13 20:41:06'),
(204, 'Antarctica/Mawson', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:06', '2021-07-13 15:41:06'),
(205, 'Antarctica/McMurdo', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:06', '2021-07-13 22:41:06'),
(206, 'Antarctica/Palmer', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(207, 'Antarctica/Rothera', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:06', '2021-07-13 07:41:06'),
(208, 'Antarctica/Syowa', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(209, 'Antarctica/Troll', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:06', '2021-07-13 12:41:06'),
(210, 'Antarctica/Vostok', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:06', '2021-07-13 16:41:06'),
(211, 'Arctic/Longyearbyen', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:06', '2021-07-13 12:41:06'),
(212, 'Asia/Aden', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(213, 'Asia/Almaty', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:06', '2021-07-13 16:41:06'),
(214, 'Asia/Amman', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(215, 'Asia/Anadyr', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:06', '2021-07-13 22:41:06'),
(216, 'Asia/Aqtau', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:06', '2021-07-13 15:41:06'),
(217, 'Asia/Aqtobe', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:06', '2021-07-13 15:41:06'),
(218, 'Asia/Ashgabat', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:06', '2021-07-13 15:41:06'),
(219, 'Asia/Atyrau', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:06', '2021-07-13 15:41:06'),
(220, 'Asia/Baghdad', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(221, 'Asia/Bahrain', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(222, 'Asia/Baku', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:06', '2021-07-13 14:41:06'),
(223, 'Asia/Bangkok', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:06', '2021-07-13 17:41:06'),
(224, 'Asia/Barnaul', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:06', '2021-07-13 17:41:06'),
(225, 'Asia/Beirut', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(226, 'Asia/Bishkek', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:06', '2021-07-13 16:41:06'),
(227, 'Asia/Brunei', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:06', '2021-07-13 18:41:06'),
(228, 'Asia/Chita', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:06', '2021-07-13 19:41:06'),
(229, 'Asia/Choibalsan', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:06', '2021-07-13 18:41:06'),
(230, 'Asia/Colombo', '+05:30', 'UTC/GMT +05:30', '2021-07-13 16:11:06', '2021-07-13 16:11:06'),
(231, 'Asia/Damascus', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(232, 'Asia/Dhaka', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:06', '2021-07-13 16:41:06'),
(233, 'Asia/Dili', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:06', '2021-07-13 19:41:06'),
(234, 'Asia/Dubai', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:06', '2021-07-13 14:41:06'),
(235, 'Asia/Dushanbe', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:06', '2021-07-13 15:41:06'),
(236, 'Asia/Famagusta', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(237, 'Asia/Gaza', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(238, 'Asia/Hebron', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:06', '2021-07-13 13:41:06'),
(239, 'Asia/Ho_Chi_Minh', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:06', '2021-07-13 17:41:06'),
(240, 'Asia/Hong_Kong', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:06', '2021-07-13 18:41:06'),
(241, 'Asia/Hovd', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:06', '2021-07-13 17:41:06'),
(242, 'Asia/Irkutsk', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(243, 'Asia/Jakarta', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(244, 'Asia/Jayapura', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:07', '2021-07-13 19:41:07'),
(245, 'Asia/Jerusalem', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(246, 'Asia/Kabul', '+04:30', 'UTC/GMT +04:30', '2021-07-13 15:11:07', '2021-07-13 15:11:07'),
(247, 'Asia/Kamchatka', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:07', '2021-07-13 22:41:07'),
(248, 'Asia/Karachi', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(249, 'Asia/Kathmandu', '+05:45', 'UTC/GMT +05:45', '2021-07-13 16:26:07', '2021-07-13 16:26:07'),
(250, 'Asia/Khandyga', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:07', '2021-07-13 19:41:07'),
(251, 'Asia/Kolkata', '+05:30', 'UTC/GMT +05:30', '2021-07-13 16:11:07', '2021-07-13 16:11:07'),
(252, 'Asia/Krasnoyarsk', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(253, 'Asia/Kuala_Lumpur', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(254, 'Asia/Kuching', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(255, 'Asia/Kuwait', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(256, 'Asia/Macau', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(257, 'Asia/Magadan', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(258, 'Asia/Makassar', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(259, 'Asia/Manila', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(260, 'Asia/Muscat', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(261, 'Asia/Nicosia', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(262, 'Asia/Novokuznetsk', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(263, 'Asia/Novosibirsk', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(264, 'Asia/Omsk', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:07', '2021-07-13 16:41:07'),
(265, 'Asia/Oral', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(266, 'Asia/Phnom_Penh', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(267, 'Asia/Pontianak', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(268, 'Asia/Pyongyang', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:07', '2021-07-13 19:41:07'),
(269, 'Asia/Qatar', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(270, 'Asia/Qostanay', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:07', '2021-07-13 16:41:07'),
(271, 'Asia/Qyzylorda', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(272, 'Asia/Riyadh', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(273, 'Asia/Sakhalin', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(274, 'Asia/Samarkand', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(275, 'Asia/Seoul', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:07', '2021-07-13 19:41:07'),
(276, 'Asia/Shanghai', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(277, 'Asia/Singapore', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(278, 'Asia/Srednekolymsk', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(279, 'Asia/Taipei', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(280, 'Asia/Tashkent', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(281, 'Asia/Tbilisi', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(282, 'Asia/Tehran', '+04:30', 'UTC/GMT +04:30', '2021-07-13 15:11:07', '2021-07-13 15:11:07'),
(283, 'Asia/Thimphu', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:07', '2021-07-13 16:41:07'),
(284, 'Asia/Tokyo', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:07', '2021-07-13 19:41:07'),
(285, 'Asia/Tomsk', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(286, 'Asia/Ulaanbaatar', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(287, 'Asia/Urumqi', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:07', '2021-07-13 16:41:07'),
(288, 'Asia/Ust-Nera', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(289, 'Asia/Vientiane', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(290, 'Asia/Vladivostok', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(291, 'Asia/Yakutsk', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:07', '2021-07-13 19:41:07'),
(292, 'Asia/Yangon', '+06:30', 'UTC/GMT +06:30', '2021-07-13 17:11:07', '2021-07-13 17:11:07'),
(293, 'Asia/Yekaterinburg', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(294, 'Asia/Yerevan', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(295, 'Atlantic/Azores', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:07', '2021-07-13 10:41:07'),
(296, 'Atlantic/Bermuda', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:07', '2021-07-13 07:41:07'),
(297, 'Atlantic/Canary', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(298, 'Atlantic/Cape_Verde', '-01:00', 'UTC/GMT -01:00', '2021-07-13 09:41:07', '2021-07-13 09:41:07'),
(299, 'Atlantic/Faroe', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(300, 'Atlantic/Madeira', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(301, 'Atlantic/Reykjavik', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:07', '2021-07-13 10:41:07'),
(302, 'Atlantic/South_Georgia', '-02:00', 'UTC/GMT -02:00', '2021-07-13 08:41:07', '2021-07-13 08:41:07'),
(303, 'Atlantic/St_Helena', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:07', '2021-07-13 10:41:07'),
(304, 'Atlantic/Stanley', '-03:00', 'UTC/GMT -03:00', '2021-07-13 07:41:07', '2021-07-13 07:41:07'),
(305, 'Australia/Adelaide', '+09:30', 'UTC/GMT +09:30', '2021-07-13 20:11:07', '2021-07-13 20:11:07'),
(306, 'Australia/Brisbane', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(307, 'Australia/Broken_Hill', '+09:30', 'UTC/GMT +09:30', '2021-07-13 20:11:07', '2021-07-13 20:11:07'),
(308, 'Australia/Darwin', '+09:30', 'UTC/GMT +09:30', '2021-07-13 20:11:07', '2021-07-13 20:11:07'),
(309, 'Australia/Eucla', '+08:45', 'UTC/GMT +08:45', '2021-07-13 19:26:07', '2021-07-13 19:26:07'),
(310, 'Australia/Hobart', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(311, 'Australia/Lindeman', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(312, 'Australia/Lord_Howe', '+10:30', 'UTC/GMT +10:30', '2021-07-13 21:11:07', '2021-07-13 21:11:07'),
(313, 'Australia/Melbourne', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(314, 'Australia/Perth', '+08:00', 'UTC/GMT +08:00', '2021-07-13 18:41:07', '2021-07-13 18:41:07'),
(315, 'Australia/Sydney', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(316, 'Europe/Amsterdam', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(317, 'Europe/Andorra', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(318, 'Europe/Astrakhan', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(319, 'Europe/Athens', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(320, 'Europe/Belgrade', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(321, 'Europe/Berlin', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(322, 'Europe/Bratislava', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(323, 'Europe/Brussels', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(324, 'Europe/Bucharest', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(325, 'Europe/Budapest', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(326, 'Europe/Busingen', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(327, 'Europe/Chisinau', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(328, 'Europe/Copenhagen', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(329, 'Europe/Dublin', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(330, 'Europe/Gibraltar', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(331, 'Europe/Guernsey', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(332, 'Europe/Helsinki', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(333, 'Europe/Isle_of_Man', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(334, 'Europe/Istanbul', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(335, 'Europe/Jersey', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(336, 'Europe/Kaliningrad', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(337, 'Europe/Kiev', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(338, 'Europe/Kirov', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(339, 'Europe/Lisbon', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(340, 'Europe/Ljubljana', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(341, 'Europe/London', '+01:00', 'UTC/GMT +01:00', '2021-07-13 11:41:07', '2021-07-13 11:41:07'),
(342, 'Europe/Luxembourg', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(343, 'Europe/Madrid', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(344, 'Europe/Malta', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(345, 'Europe/Mariehamn', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(346, 'Europe/Minsk', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(347, 'Europe/Monaco', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(348, 'Europe/Moscow', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(349, 'Europe/Oslo', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(350, 'Europe/Paris', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(351, 'Europe/Podgorica', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(352, 'Europe/Prague', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(353, 'Europe/Riga', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(354, 'Europe/Rome', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(355, 'Europe/Samara', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(356, 'Europe/San_Marino', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(357, 'Europe/Sarajevo', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(358, 'Europe/Saratov', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(359, 'Europe/Simferopol', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(360, 'Europe/Skopje', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(361, 'Europe/Sofia', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(362, 'Europe/Stockholm', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(363, 'Europe/Tallinn', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(364, 'Europe/Tirane', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(365, 'Europe/Ulyanovsk', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(366, 'Europe/Uzhgorod', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(367, 'Europe/Vaduz', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(368, 'Europe/Vatican', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(369, 'Europe/Vienna', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(370, 'Europe/Vilnius', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(371, 'Europe/Volgograd', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(372, 'Europe/Warsaw', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(373, 'Europe/Zagreb', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(374, 'Europe/Zaporozhye', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(375, 'Europe/Zurich', '+02:00', 'UTC/GMT +02:00', '2021-07-13 12:41:07', '2021-07-13 12:41:07'),
(376, 'Indian/Antananarivo', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(377, 'Indian/Chagos', '+06:00', 'UTC/GMT +06:00', '2021-07-13 16:41:07', '2021-07-13 16:41:07'),
(378, 'Indian/Christmas', '+07:00', 'UTC/GMT +07:00', '2021-07-13 17:41:07', '2021-07-13 17:41:07'),
(379, 'Indian/Cocos', '+06:30', 'UTC/GMT +06:30', '2021-07-13 17:11:07', '2021-07-13 17:11:07'),
(380, 'Indian/Comoro', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(381, 'Indian/Kerguelen', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(382, 'Indian/Mahe', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(383, 'Indian/Maldives', '+05:00', 'UTC/GMT +05:00', '2021-07-13 15:41:07', '2021-07-13 15:41:07'),
(384, 'Indian/Mauritius', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(385, 'Indian/Mayotte', '+03:00', 'UTC/GMT +03:00', '2021-07-13 13:41:07', '2021-07-13 13:41:07'),
(386, 'Indian/Reunion', '+04:00', 'UTC/GMT +04:00', '2021-07-13 14:41:07', '2021-07-13 14:41:07'),
(387, 'Pacific/Apia', '+13:00', 'UTC/GMT +13:00', '2021-07-13 23:41:07', '2021-07-13 23:41:07'),
(388, 'Pacific/Auckland', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:07', '2021-07-13 22:41:07'),
(389, 'Pacific/Bougainville', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(390, 'Pacific/Chatham', '+12:45', 'UTC/GMT +12:45', '2021-07-13 23:26:07', '2021-07-13 23:26:07'),
(391, 'Pacific/Chuuk', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(392, 'Pacific/Easter', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:07', '2021-07-13 04:41:07'),
(393, 'Pacific/Efate', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(394, 'Pacific/Enderbury', '+13:00', 'UTC/GMT +13:00', '2021-07-13 23:41:07', '2021-07-13 23:41:07'),
(395, 'Pacific/Fakaofo', '+13:00', 'UTC/GMT +13:00', '2021-07-13 23:41:07', '2021-07-13 23:41:07'),
(396, 'Pacific/Fiji', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:07', '2021-07-13 22:41:07'),
(397, 'Pacific/Funafuti', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:07', '2021-07-13 22:41:07'),
(398, 'Pacific/Galapagos', '-06:00', 'UTC/GMT -06:00', '2021-07-13 04:41:07', '2021-07-13 04:41:07'),
(399, 'Pacific/Gambier', '-09:00', 'UTC/GMT -09:00', '2021-07-13 01:41:07', '2021-07-13 01:41:07'),
(400, 'Pacific/Guadalcanal', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(401, 'Pacific/Guam', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:07', '2021-07-13 20:41:07'),
(402, 'Pacific/Honolulu', '-10:00', 'UTC/GMT -10:00', '2021-07-13 00:41:07', '2021-07-13 00:41:07'),
(403, 'Pacific/Kiritimati', '+14:00', 'UTC/GMT +14:00', '2021-07-14 00:41:07', '2021-07-14 00:41:07'),
(404, 'Pacific/Kosrae', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:07', '2021-07-13 21:41:07'),
(405, 'Pacific/Kwajalein', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:07', '2021-07-13 22:41:07'),
(406, 'Pacific/Majuro', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:07', '2021-07-13 22:41:07'),
(407, 'Pacific/Marquesas', '-09:30', 'UTC/GMT -09:30', '2021-07-13 01:11:08', '2021-07-13 01:11:08'),
(408, 'Pacific/Midway', '-11:00', 'UTC/GMT -11:00', '2021-07-12 23:41:08', '2021-07-12 23:41:08'),
(409, 'Pacific/Nauru', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:08', '2021-07-13 22:41:08'),
(410, 'Pacific/Niue', '-11:00', 'UTC/GMT -11:00', '2021-07-12 23:41:08', '2021-07-12 23:41:08'),
(411, 'Pacific/Norfolk', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:08', '2021-07-13 21:41:08'),
(412, 'Pacific/Noumea', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:08', '2021-07-13 21:41:08'),
(413, 'Pacific/Pago_Pago', '-11:00', 'UTC/GMT -11:00', '2021-07-12 23:41:08', '2021-07-12 23:41:08'),
(414, 'Pacific/Palau', '+09:00', 'UTC/GMT +09:00', '2021-07-13 19:41:08', '2021-07-13 19:41:08'),
(415, 'Pacific/Pitcairn', '-08:00', 'UTC/GMT -08:00', '2021-07-13 02:41:08', '2021-07-13 02:41:08'),
(416, 'Pacific/Pohnpei', '+11:00', 'UTC/GMT +11:00', '2021-07-13 21:41:08', '2021-07-13 21:41:08'),
(417, 'Pacific/Port_Moresby', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:08', '2021-07-13 20:41:08'),
(418, 'Pacific/Rarotonga', '-10:00', 'UTC/GMT -10:00', '2021-07-13 00:41:08', '2021-07-13 00:41:08'),
(419, 'Pacific/Saipan', '+10:00', 'UTC/GMT +10:00', '2021-07-13 20:41:08', '2021-07-13 20:41:08'),
(420, 'Pacific/Tahiti', '-10:00', 'UTC/GMT -10:00', '2021-07-13 00:41:08', '2021-07-13 00:41:08'),
(421, 'Pacific/Tarawa', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:08', '2021-07-13 22:41:08'),
(422, 'Pacific/Tongatapu', '+13:00', 'UTC/GMT +13:00', '2021-07-13 23:41:08', '2021-07-13 23:41:08'),
(423, 'Pacific/Wake', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:08', '2021-07-13 22:41:08'),
(424, 'Pacific/Wallis', '+12:00', 'UTC/GMT +12:00', '2021-07-13 22:41:08', '2021-07-13 22:41:08'),
(425, 'UTC', '+00:00', 'UTC/GMT +00:00', '2021-07-13 10:41:08', '2021-07-13 10:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `toll_pass_origin`
--

CREATE TABLE `toll_pass_origin` (
  `id` bigint UNSIGNED NOT NULL,
  `toll_pass` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `payable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payable_id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('deposit','withdraw') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(64,0) NOT NULL,
  `confirmed` tinyint(1) NOT NULL,
  `meta` json DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` bigint UNSIGNED NOT NULL,
  `from_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_id` bigint UNSIGNED NOT NULL,
  `to_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_id` bigint UNSIGNED NOT NULL,
  `status` enum('exchange','transfer','paid','refund','gift') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transfer',
  `status_last` enum('exchange','transfer','paid','refund','gift') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_id` bigint UNSIGNED NOT NULL,
  `withdraw_id` bigint UNSIGNED NOT NULL,
  `discount` decimal(64,0) NOT NULL DEFAULT '0',
  `fee` decimal(64,0) NOT NULL DEFAULT '0',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `travel_mode`
--

CREATE TABLE `travel_mode` (
  `id` bigint UNSIGNED NOT NULL,
  `travelmode` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` bigint UNSIGNED NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'products_service',
  `title` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `image` mediumtext COLLATE utf8mb4_unicode_ci,
  `sequence` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `service_type`, `title`, `description`, `image`, `sequence`, `created_at`, `updated_at`) VALUES
(1, 'products_service', 'Product', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'product.png', 2, '2023-01-18 05:09:52', '2023-01-18 05:09:52'),
(2, 'pick_drop_parent_service', 'Pickup/Parent', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'pickup_delivery.png', 7, '2023-01-18 05:09:52', '2023-01-18 05:09:52'),
(3, 'products_service', 'Vendor', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'vendor.png', 3, '2023-01-18 05:09:52', '2023-01-18 05:09:52'),
(4, 'products_service', 'Brand', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'brand.png', 4, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(5, 'products_service', 'Celebrity', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'celebrity.png', 6, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(6, 'products_service', 'Subcategory', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'subcategory.png', 1, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(7, 'pick_drop_service', 'Pickup/Delivery', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'dispatcher.png', 6, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(8, 'on_demand_service', 'On Demand Service', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'ondemand.png', 7, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(9, 'laundry_service', 'Laundry', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'laundry.png', 8, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(10, 'rental_service', 'Rental Service', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'rental.png', 9, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(11, 'products_service', 'Food', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'home_five.png', 10, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(12, 'appointment_service', 'Appointment', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'appointment.png', 11, '2023-01-18 05:09:53', '2023-01-18 05:09:53'),
(13, 'p2p', 'P2P', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'P2P.png', 12, '2023-01-18 05:09:53', '2023-01-18 05:09:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `phone_number` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dial_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '1 for buyer, 2 for seller',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 - pending, 1 - active, 2 - blocked, 3 - inactive',
  `country_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `facebook_auth_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_auth_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_auth_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_auth_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_token` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_token_valid_till` timestamp NULL DEFAULT NULL,
  `phone_token` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_token_valid_till` timestamp NULL DEFAULT NULL,
  `track_order_phone_token` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `track_order_phone_token_valid_till` timestamp NULL DEFAULT NULL,
  `is_email_verified` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_phone_verified` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_superadmin` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_admin` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone_id` bigint UNSIGNED DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `import_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_panel_auth_user` tinyint DEFAULT NULL,
  `geo_ids` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `description`, `phone_number`, `dial_code`, `email_verified_at`, `password`, `type`, `status`, `country_id`, `role_id`, `auth_token`, `system_id`, `remember_token`, `created_at`, `updated_at`, `facebook_auth_id`, `twitter_auth_id`, `google_auth_id`, `apple_auth_id`, `image`, `email_token`, `email_token_valid_till`, `phone_token`, `phone_token_valid_till`, `track_order_phone_token`, `track_order_phone_token_valid_till`, `is_email_verified`, `is_phone_verified`, `code`, `is_superadmin`, `is_admin`, `title`, `timezone_id`, `timezone`, `import_user_id`, `last_login_at`, `deleted_at`, `is_panel_auth_user`, `geo_ids`) VALUES
(1, 'African village Market', 'admin@africanvillagemarket.com', NULL, '5896523658', NULL, NULL, '$2y$10$T7zmZDpkOqoQGXobsptW..oyNw328bjtw9dgUf9dwSsDduCTrigT2', 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-20 11:54:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 1, 0, NULL, NULL, 'Africa/Abidjan', NULL, NULL, NULL, NULL, '0'),
(2, 'Anil', 'anil@gmail.com', NULL, '9865473212', NULL, NULL, '$2y$10$1.qDPG4H1kcBPEVRJ2ls2.2W2wlK7CGIy4qM/vN2yLCbSvXSFZ3di', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjY3ODYzNzQsImV4cCI6MTYyOTQ2NDc3NCwiaXNzIjoicm95b29yZGVycy5jb20ifQ.8YWWLT5CeZb69vWPiIIpgq7wcWG8JfkAnjLbnNlG8X0', NULL, NULL, '2021-07-20 13:05:38', '2021-07-20 13:07:43', NULL, NULL, NULL, NULL, 'profile/2015d2418338a63b.jpg', '493864', '2021-07-20 13:15:38', '408731', '2021-07-20 13:15:38', NULL, NULL, 0, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(3, 'Mukesh', 'mukesh@gmail.com', NULL, '9865432516', NULL, NULL, '$2y$10$sFtjgGwc0AEFSfL3.QnJYOr9tiopbVv.REVjun2ctYS/idncT0Vyq', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjY3ODY2MDIsImV4cCI6MTYyOTQ2NTAwMiwiaXNzIjoicm95b29yZGVycy5jb20ifQ.831TqA9rKrFjDUWPewTW5z5xTlpBjl1WAWEMkkSuv_A', NULL, NULL, '2021-07-20 13:10:02', '2021-07-20 13:11:30', NULL, NULL, NULL, NULL, 'profile/3b776a3178eb5133.jpg', '254721', '2021-07-20 13:20:02', '715269', '2021-07-20 13:20:02', NULL, NULL, 0, 0, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(4, 'Journey', 'Logisticsjourney@gmail.com', NULL, '1234585212', '91', NULL, '$2y$10$tP0BjXXup.TOz9Nh6uM/3.FZmgQ32LaW52t3eU3MAbLd8Co77gRxa', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjc2NDA5NzYsImV4cCI6MTYzMDMxOTM3NiwiaXNzIjoicm95b29yZGVycy5jb20ifQ.bnOVNGtE9a2UPfVCXJx9ns4lsl5Z8EuO-z9GotfmmHY', NULL, NULL, '2021-07-30 10:29:36', '2021-07-30 10:29:36', NULL, NULL, NULL, NULL, NULL, '582307', '2021-07-30 10:39:36', '359011', '2021-07-30 10:39:36', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(5, 'Spartans Man', 'royoorders@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$Z6ZKcNORX2E6KIUpPDFfIunafvXNWSo6hWxO.NVEFpFaWlKBHn2hS', 1, 1, NULL, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgwNTI2MTUsImV4cCI6MTYzMDczMTAxNSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.1aC-nRyopL0gZaQoByf7StNYRhe0wQZcz8KYuTss9ls', NULL, NULL, '2021-08-04 04:50:15', '2021-08-04 04:51:00', '112043761121106', NULL, NULL, NULL, 'profile/5495862cfb85e52b.jpg', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(6, 'test', 'testing@test.com', NULL, '4578124563', '91', NULL, '$2y$10$SN0uIFbRTPDNtTnUB.U5.eKGfvDVfoG3JuoXIPusKpPE0oZbejD0G', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgwNTM2ODMsImV4cCI6MTYzMDczMjA4MywiaXNzIjoicm95b29yZGVycy5jb20ifQ.jg-8nd6cHPFD_aJWuOKYA0ctRkSS0KBq87UWbvvSf3I', NULL, NULL, '2021-08-04 05:08:03', '2021-08-04 05:08:03', NULL, NULL, NULL, NULL, NULL, '237917', '2021-08-04 05:18:03', '587221', '2021-08-04 05:18:03', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(7, 'Raman', 'raman123@support.com', NULL, '4523697852', '91', NULL, '$2y$10$S1gJYBv8gdj7aqj.2fSWd..InxkQhU1Ht4oK8caK9AIFORfR0bOc.', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgxNDE4MDgsImV4cCI6MTYzMDgyMDIwOCwiaXNzIjoicm95b29yZGVycy5jb20ifQ.EqCE5SzirbZxdJJvckGtdMymN_76OlRqnObeCvBXe8w', NULL, NULL, '2021-08-05 05:36:48', '2021-08-05 05:42:00', NULL, NULL, NULL, NULL, 'profile/74ee19507ba5a09c.jpg', '872045', '2021-08-05 05:46:48', '182693', '2021-08-05 05:46:48', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(8, 'Ramannn', 'raman@gmail.com', NULL, '8080808080', '91', NULL, '$2y$10$XCjRj8vMVAkYX7S9M0pvCulDVUqikzE/jWTK5Vpi6fux2j/sXiq/a', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgxNDM0NjMsImV4cCI6MTYzMDgyMTg2MywiaXNzIjoicm95b29yZGVycy5jb20ifQ.VymNMLfGQC5waBYtJgzKlQSiqUIp6OIxPPqsjmvliPA', NULL, NULL, '2021-08-05 06:04:23', '2021-08-05 10:57:17', NULL, NULL, NULL, NULL, 'profile/814cc42d4eb58ce4.jpg', '395244', '2021-08-05 06:14:23', '632024', '2021-08-05 06:17:36', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(9, 'testi', 'testing@suoort.com', NULL, '5632452369', '91', NULL, '$2y$10$yCnH70WwpFnoqi33rT2hDOjOVS.DfrERHdlxxZ8Oim9/5VGNY8WKa', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgxNDM4MjMsImV4cCI6MTYzMDgyMjIyMywiaXNzIjoicm95b29yZGVycy5jb20ifQ.Uo_F2WNqLOrJAKs6XQoQTFKvQ5uZq3BBxpV7zn7qmHc', NULL, NULL, '2021-08-05 06:10:23', '2021-08-05 06:18:40', NULL, NULL, NULL, NULL, NULL, '415107', '2021-08-05 06:20:23', '860654', '2021-08-05 06:20:23', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(10, 'Girraj', 'girraj.codebrew@gmail.com', NULL, '2531642812', NULL, NULL, '$2y$10$x2YuzfJYGpY0Gu.fX/OgieltXGmlg/xg509E1Ehtd4NPoHHZ9RQ.m', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgxNDUwNDMsImV4cCI6MTYzMDgyMzQ0MywiaXNzIjoicm95b29yZGVycy5jb20ifQ.rRl-SGDQSSoDWb99Rm8cvh_ldKArwhfR0FDoWeETEOY', NULL, NULL, '2021-08-05 06:30:43', '2021-08-05 06:33:38', NULL, NULL, '106318606478002588969', NULL, NULL, NULL, NULL, '701657', '2021-08-05 06:43:37', NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(11, 'testing23', 'test@gmail.com', NULL, '5632147893', '91', NULL, '$2y$10$tPEePap/yto75B8qC2V5henk4DnsJIhJ1royEtfkccHuDezbZ14wC', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgxNjE0ODMsImV4cCI6MTYzMDgzOTg4MywiaXNzIjoicm95b29yZGVycy5jb20ifQ.f6VWOBxdiWHLTIEzX6Ek8LP1_j_KjyjpbWcmOT4MN3g', NULL, NULL, '2021-08-05 11:02:37', '2021-08-05 12:08:06', NULL, NULL, NULL, NULL, 'profile/110c4fe503b38d76e.jpg', '423916', '2021-08-05 11:12:37', '659730', '2021-08-05 12:16:33', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(12, 'Testing', 'testing@support.com', NULL, '1245789655', '91', NULL, '$2y$10$F/MuKj8qJNpxdN2v6XBxo.wAV606fFTGSHd2KjCFfjeY4rESaZaLW', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2MjgxNjQzMzgsImV4cCI6MTYzMDg0MjczOCwiaXNzIjoicm95b29yZGVycy5jb20ifQ.MFbMUWigQVt_RE0GK6k5QRt4SXmyBZ3D42ZX9EXj8tk', NULL, NULL, '2021-08-05 11:52:18', '2021-08-05 11:53:20', NULL, NULL, NULL, NULL, NULL, '996591', '2021-08-05 12:02:18', '484958', '2021-08-05 12:02:54', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(13, 'Gouravkm', 'gouravk@gmail.com', NULL, '9898989895', '91', NULL, '$2y$10$ioMQzuUEhWV9lwGgKJZPKOcNIJi1GQ3bJ6YVbViQ.mAS5.OxE./Sq', 1, 1, 99, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjg3Njg1NDksImV4cCI6MTYzMTQ0Njk0OSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.sLljl6oQSDN0PMjiy17GdUX4rxdCrEVHUVw9w7AFddo', NULL, NULL, '2021-08-12 11:42:29', '2021-08-12 12:16:36', NULL, NULL, NULL, NULL, 'profile/136679d152652d333.jpg', '841564', '2021-08-12 11:53:35', '997220', '2021-08-12 11:53:30', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(14, 'Abhi jeet', 'abhi96464969@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$8K.2PxfbnwVMQ/eWhPZyCufd/vWwIQmBItixBrx5waZ2HjpKKQh/m', 1, 1, NULL, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjk3MDMwOTIsImV4cCI6MTYzMjM4MTQ5MiwiaXNzIjoicm95b29yZGVycy5jb20ifQ.GZKCUH_lq0cbhLi3DR_QoZutv8gyqezGp1tFp3pQBUI', NULL, NULL, '2021-08-23 07:18:12', '2021-08-23 07:18:12', NULL, NULL, '106469975013030012802', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(15, 'Infra', 'infra@code-brew.com', NULL, NULL, NULL, NULL, '$2y$10$Ri0yL4ZC6S2EE9nhEgLfReeUFe4PEe4gVZESMCQEoc6Dk8NQYqhM6', 1, 1, NULL, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjk3MDM0OTUsImV4cCI6MTYzMjM4MTg5NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.OgWidltdysaNJLh3dXzNlwGGwnJvCMwShtCIkZW4EKo', NULL, NULL, '2021-08-23 07:21:28', '2021-08-23 07:26:04', NULL, NULL, '111745409157628332668', NULL, 'profile/151a592e416207d0a.jpg', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(16, 'Waitrose', 'testvendor@gmail.com', NULL, '5836479573', NULL, NULL, '$2y$10$LoBuQtgAW4N7O.Lnm1iuUuqGBXyaEZWErWd4U0hTiyy5YEhVoasOu', 1, 1, 226, NULL, NULL, NULL, NULL, '2021-09-17 05:56:57', '2021-09-17 05:58:19', NULL, NULL, NULL, NULL, NULL, '417293', '2021-09-17 06:06:57', '559189', '2021-09-17 06:06:57', NULL, NULL, 0, 0, NULL, 0, 1, 'Dummy Vendor', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(17, 'SAMMYS KITCHEN', 'ebenezersam14@gmail.com', NULL, '09051340163', NULL, NULL, '$2y$10$qAvEN15R93MvXUQI26tHpuVECQVHgcAx1tc/p6jnOBxIUcDj58Fdi', 1, 1, 226, NULL, NULL, NULL, NULL, '2021-09-27 19:53:54', '2021-09-27 19:53:54', NULL, NULL, NULL, NULL, NULL, '578085', '2021-09-27 20:03:54', '246887', '2021-09-27 20:03:54', NULL, NULL, 0, 0, NULL, 0, 1, 'Mr', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(18, 'Testap', 'Testapp@gmail.com', NULL, '121121212', '234', NULL, '$2y$10$SRex.FbWrm/LGOZowJNZD.FxmabAwbEtcYOL4tnmeGNSTDArKmzwm', 1, 1, 156, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjY4NzQ5NTUsImV4cCI6MTY2OTU1MzM1NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.d3bjz3_Z7Y52eOtZ49cZPwz_5cZLJg7ljfe9_wcfQdQ', NULL, NULL, '2022-10-27 12:49:15', '2022-10-27 12:49:15', NULL, NULL, NULL, NULL, NULL, '935157', '2022-10-27 12:59:15', '921204', '2022-10-27 12:59:15', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, 'Africa/Abidjan', NULL, NULL, NULL, NULL, '0'),
(19, 'Testap', 'Testapp@gmail.com', NULL, '121121212', '234', NULL, '$2y$10$UvFbN6kPME0UP2mHLll0juAkE5kbNwnBTrKISt9GoALaQ1wzk5IFS', 1, 1, 156, NULL, 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjY4NzQ5NTUsImV4cCI6MTY2OTU1MzM1NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.d3bjz3_Z7Y52eOtZ49cZPwz_5cZLJg7ljfe9_wcfQdQ', NULL, NULL, '2022-10-27 12:49:15', '2022-10-27 12:49:15', NULL, NULL, NULL, NULL, NULL, '218066', '2022-10-27 12:59:15', '753901', '2022-10-27 12:59:15', NULL, NULL, 0, 0, NULL, 0, 0, NULL, NULL, 'Africa/Abidjan', NULL, NULL, NULL, NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `house_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(15,12) DEFAULT NULL,
  `longitude` decimal(16,12) DEFAULT NULL,
  `pincode` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `phonecode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 - home',
  `type_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_instruction` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 deleted, 1 not deleted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `house_number`, `address`, `street`, `city`, `state`, `latitude`, `longitude`, `pincode`, `is_primary`, `phonecode`, `country_code`, `country`, `type`, `type_name`, `extra_instruction`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'ffffff', NULL, 'ffffffffff', 'ffffff', NULL, NULL, 'ffffffffff', 0, NULL, 'AF', 'AFGHANISTAN', 1, NULL, NULL, 1, '2021-07-13 13:03:07', '2021-07-13 13:03:07'),
(2, 2, NULL, 'Plot No. 5, Madhya Marg, 28B, Sector 28 B, Chandigarh, 160028, India', 'Chandigarh', 'Chandigarh', 'CH', '30.718897800000', '76.810298100000', '160028', 0, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-07-20 13:06:52', '2021-07-20 13:06:52'),
(3, 7, NULL, 'Plot No. 5, Madhya Marg, 28B, Sector 28 B, Chandigarh, 160028, India', 'Chandigarh', 'Chandigarh', 'CH', '30.718897800000', '76.810298100000', '160028', 0, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 05:37:38', '2021-08-05 06:08:11'),
(4, 7, NULL, 'Sector 22, Chandigarh, India', 'Chandigarh', 'Chandigarh', 'CH', '30.732038500000', '76.772633400000', '160152', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 05:42:27', '2021-08-05 06:08:11'),
(5, 8, NULL, '35, Madhya Marg, Sector 7-C, Sector 7, Chandigarh, 160019, India', 'Chandigarh', 'Chandigarh', 'CH', '30.729804800000', '76.800727100000', '160019', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 06:05:53', '2021-08-05 10:57:02'),
(7, 9, NULL, 'Sector 17, Chandigarh, India', 'Chandigarh', 'Chandigarh', 'CH', '30.741051700000', '76.779015000000', '888888', 0, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 06:10:56', '2021-08-05 06:33:31'),
(8, 9, NULL, 'Sector 22, Chandigarh, India', 'Chandigarh', 'Chandigarh', 'CH', '30.732038500000', '76.772633400000', '999666', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 06:13:19', '2021-08-05 06:33:31'),
(9, 10, NULL, 'Plot No. 5, Madhya Marg, 28B, Sector 28 B, Chandigarh, 160028, India', 'Chandigarh', 'Chandigarh', 'CH', '30.718897800000', '76.810298100000', '160028', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 06:32:39', '2021-08-05 06:33:16'),
(12, 12, NULL, 'Plot No. 5, Madhya Marg, 28B, Sector 28 B, Chandigarh, 160028, India', 'Chandigarh', 'Chandigarh', 'CH', '30.718897800000', '76.810298100000', '160028', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 11:53:31', '2021-08-05 11:54:05'),
(16, 11, NULL, 'Plot No. 5, Madhya Marg, 28B, Sector 28 B, Chandigarh, 160028, India', 'Chandigarh', 'Chandigarh', 'CH', '30.718897800000', '76.810298100000', '160028', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-05 12:09:08', '2021-08-05 12:09:08'),
(17, 13, NULL, 'Plot No. 5, Madhya Marg, 28B, Sector 28 B, Chandigarh, 160028, India', 'Chandigarh', 'Chandigarh', 'CH', '30.718897800000', '76.810298100000', '160028', 0, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-12 11:44:43', '2021-08-12 11:49:02'),
(18, 13, NULL, '1, Phase 1, Sector 55, Shakti Market, Mohali Village, Sahibzada Ajit Singh Nagar, Chandigarh 160055, India', 'SAS Nagar', 'SAS Nagar', 'CH', '30.728408600000', '76.718349500000', '160055', 1, '+91', 'IN', 'India', 1, NULL, NULL, 1, '2021-08-12 11:45:09', '2021-08-12 11:49:02'),
(19, 14, NULL, 'South Africa', 'dub', 'dub', 'dub', '-30.559482000000', '22.937506000000', '666999', 1, '+27', 'ZA', 'South Africa', 1, NULL, NULL, 1, '2021-08-23 07:19:21', '2021-08-23 07:19:28'),
(20, 15, NULL, 'South Africa', 'ubb', 'ubb', 'ubb', '-30.559482000000', '22.937506000000', '999999', 1, '+27', 'ZA', 'South Africa', 1, NULL, NULL, 1, '2021-08-23 07:25:22', '2021-08-23 07:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_bid_ride_requests`
--

CREATE TABLE `user_bid_ride_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=>created, 1=>approved',
  `tasks` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `web_hook_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_price` decimal(15,4) DEFAULT NULL,
  `expired_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `user_data_vault`
--

CREATE TABLE `user_data_vault` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_hint` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `device_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_vendor_app` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `user_devices`
--

INSERT INTO `user_devices` (`id`, `user_id`, `device_type`, `device_token`, `access_token`, `created_at`, `updated_at`, `is_vendor_app`) VALUES
(1, 2, 'android', 'a779b5d81ef3878d', '', NULL, NULL, 0),
(2, 3, 'android', 'a779b5d81ef3878d', '', NULL, NULL, 0),
(3, 4, 'ios', '50DB1F27-A2B4-4102-96FB-805A7A9E73E6', '', NULL, NULL, 0),
(4, 5, 'ios', '50DB1F27-A2B4-4102-96FB-805A7A9E73E6', '', '2021-08-04 04:50:15', '2021-08-04 04:50:15', 0),
(5, 6, 'ios', '50DB1F27-A2B4-4102-96FB-805A7A9E73E6', '', NULL, NULL, 0),
(6, 14, 'android', '9d1195a641e1dfe3', 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjk3MDMwOTIsImV4cCI6MTYzMjM4MTQ5MiwiaXNzIjoicm95b29yZGVycy5jb20ifQ.GZKCUH_lq0cbhLi3DR_QoZutv8gyqezGp1tFp3pQBUI', NULL, '2021-08-23 07:18:12', 0),
(7, 15, 'ios', 'C19DD30E-C7EB-4317-AED7-512C0A17B40D', 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjk3MDM0OTUsImV4cCI6MTYzMjM4MTg5NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.OgWidltdysaNJLh3dXzNlwGGwnJvCMwShtCIkZW4EKo', NULL, '2021-08-23 07:24:55', 0),
(8, 9, 'android', 'f4434544a6f3c363', '', NULL, NULL, 0),
(9, 10, 'android', 'f191493eedc34deb', '', '2021-08-05 06:30:43', '2021-08-05 06:30:43', 0),
(10, 11, 'ios', 'C19DD30E-C7EB-4317-AED7-512C0A17B40D', '', NULL, '2021-08-05 11:04:43', 0),
(11, 12, 'android', '9d1195a641e1dfe3', '', NULL, NULL, 0),
(12, 13, 'ios', '8CF9108B-4193-46DC-8B55-844D16B429EE', 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2Mjg3Njg1NDksImV4cCI6MTYzMTQ0Njk0OSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.sLljl6oQSDN0PMjiy17GdUX4rxdCrEVHUVw9w7AFddo', '2021-08-12 11:42:29', '2021-08-12 11:42:29', 0),
(13, 18, 'ios', 'eCnXjABD2ULjmgAxs64by6:APA91bEvv-TA5qLK9hXbqLs-4wuBb9C_Fx7y_AuP3C1Uit7_tBzLR2-DLVcVB9VacJmjYqHkfTkiOJ08yZwBJ3pbz0b7HTlVzSYEd_e6sZa4Oxm7RowhSROvWS90N3U8r_7-KelrHVw8', 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjY4NzQ5NTUsImV4cCI6MTY2OTU1MzM1NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.d3bjz3_Z7Y52eOtZ49cZPwz_5cZLJg7ljfe9_wcfQdQ', '2022-10-27 12:49:15', '2022-10-27 12:49:15', 0),
(14, 19, 'ios', 'eCnXjABD2ULjmgAxs64by6:APA91bEvv-TA5qLK9hXbqLs-4wuBb9C_Fx7y_AuP3C1Uit7_tBzLR2-DLVcVB9VacJmjYqHkfTkiOJ08yZwBJ3pbz0b7HTlVzSYEd_e6sZa4Oxm7RowhSROvWS90N3U8r_7-KelrHVw8', 'eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjY4NzQ5NTUsImV4cCI6MTY2OTU1MzM1NSwiaXNzIjoicm95b29yZGVycy5jb20ifQ.d3bjz3_Z7Y52eOtZ49cZPwz_5cZLJg7ljfe9_wcfQdQ', '2022-10-27 12:49:15', '2022-10-27 12:49:15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_docs`
--

CREATE TABLE `user_docs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_registration_document_id` bigint UNSIGNED NOT NULL,
  `file_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `file_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_gift_cards`
--

CREATE TABLE `user_gift_cards` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `gift_card_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) UNSIGNED DEFAULT NULL,
  `expiry_date` timestamp NULL DEFAULT NULL,
  `buy_for_data` json DEFAULT NULL,
  `is_used` tinyint NOT NULL DEFAULT '0' COMMENT '1 = yes, 0 = no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `gift_card_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `user_loyalty_points`
--

CREATE TABLE `user_loyalty_points` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `points` int DEFAULT '0',
  `loyalty_card_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `user_loyalty_point_histories`
--

CREATE TABLE `user_loyalty_point_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `points` int DEFAULT NULL,
  `earn_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'on_purchase, get_as_gift, add_wallet_money',
  `earn_type_id` bigint UNSIGNED DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_cards`
--

CREATE TABLE `user_payment_cards` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `card_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_cvv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `permission_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(10, 16, 1, NULL, NULL),
(11, 16, 2, NULL, NULL),
(12, 16, 3, NULL, NULL),
(13, 16, 12, NULL, NULL),
(14, 16, 17, NULL, NULL),
(15, 16, 18, NULL, NULL),
(16, 16, 19, NULL, NULL),
(17, 16, 20, NULL, NULL),
(18, 16, 21, NULL, NULL),
(19, 17, 1, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(20, 17, 2, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(21, 17, 3, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(22, 17, 12, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(23, 17, 17, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(24, 17, 18, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(25, 17, 19, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(26, 17, 20, '2021-09-27 19:53:54', '2021-09-27 19:53:54'),
(27, 17, 21, '2021-09-27 19:53:54', '2021-09-27 19:53:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_ratings`
--

CREATE TABLE `user_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_product_id` bigint UNSIGNED DEFAULT NULL,
  `order_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'order_type 1=dispatch order on vendor base , 2=dispatch order on vendor base base.',
  `rating` decimal(4,2) DEFAULT NULL,
  `review` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'user average rating.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `user_refferals`
--

CREATE TABLE `user_refferals` (
  `id` bigint UNSIGNED NOT NULL,
  `refferal_code` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reffered_by` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `user_refferals`
--

INSERT INTO `user_refferals` (`id`, `refferal_code`, `reffered_by`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'edafed', NULL, 2, '2021-07-20 13:05:38', '2021-07-20 13:05:38'),
(2, '740e9c', NULL, 3, '2021-07-20 13:10:02', '2021-07-20 13:10:02'),
(3, '60eba7', NULL, 4, '2021-07-30 10:29:36', '2021-07-30 10:29:36'),
(4, '999cd3', NULL, 6, '2021-08-04 05:08:03', '2021-08-04 05:08:03'),
(5, '37d6a2', NULL, 7, '2021-08-05 05:36:48', '2021-08-05 05:36:48'),
(6, '3dbca1', NULL, 8, '2021-08-05 06:04:23', '2021-08-05 06:04:23'),
(7, '53801d', NULL, 9, '2021-08-05 06:10:23', '2021-08-05 06:10:23'),
(8, '9600d3', NULL, 11, '2021-08-05 11:02:37', '2021-08-05 11:02:37'),
(9, 'dc3df6', NULL, 12, '2021-08-05 11:52:18', '2021-08-05 11:52:18'),
(10, '8619e0', NULL, 13, '2021-08-12 11:42:29', '2021-08-12 11:42:29'),
(11, '163aba', NULL, 18, '2022-10-27 12:49:15', '2022-10-27 12:49:15'),
(12, '70c179', NULL, 19, '2022-10-27 12:49:15', '2022-10-27 12:49:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_registration_documents`
--

CREATE TABLE `user_registration_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_registration_document_translations`
--

CREATE TABLE `user_registration_document_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `language_id` bigint UNSIGNED NOT NULL,
  `user_registration_document_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_registration_select_options`
--

CREATE TABLE `user_registration_select_options` (
  `id` bigint UNSIGNED NOT NULL,
  `user_registration_documents_id` bigint UNSIGNED NOT NULL,
  `status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_registration_select_option_translations`
--

CREATE TABLE `user_registration_select_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_registration_select_option_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_saved_payment_methods`
--

CREATE TABLE `user_saved_payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `payment_option_id` int UNSIGNED DEFAULT NULL,
  `card_last_four_digit` int UNSIGNED DEFAULT NULL,
  `card_expiry_month` int UNSIGNED DEFAULT NULL,
  `card_expiry_year` int UNSIGNED DEFAULT NULL,
  `customerReference` text COLLATE utf8mb4_unicode_ci,
  `cardReference` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `user_vendors`
--

CREATE TABLE `user_vendors` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `user_vendors`
--

INSERT INTO `user_vendors` (`id`, `user_id`, `vendor_id`, `created_at`, `updated_at`) VALUES
(1, 2, 2, NULL, NULL),
(2, 2, 3, NULL, NULL),
(3, 2, 4, NULL, NULL),
(4, 2, 5, NULL, NULL),
(5, 2, 6, NULL, NULL),
(6, 3, 2, NULL, NULL),
(7, 3, 3, NULL, NULL),
(8, 3, 4, NULL, NULL),
(9, 3, 5, NULL, NULL),
(10, 3, 6, NULL, NULL),
(12, 16, 7, NULL, NULL),
(13, 17, 8, '2021-09-27 19:53:54', '2021-09-27 19:53:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_verification`
--

CREATE TABLE `user_verification` (
  `id` bigint UNSIGNED NOT NULL,
  `verification_option_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `response_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_verification_resources`
--

CREATE TABLE `user_verification_resources` (
  `id` bigint UNSIGNED NOT NULL,
  `user_verification_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datapoints` json DEFAULT NULL COMMENT 'datapoints in json format',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_wishlists`
--

CREATE TABLE `user_wishlists` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `added_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `user_wishlists`
--

INSERT INTO `user_wishlists` (`id`, `user_id`, `product_id`, `product_variant_id`, `added_on`, `created_at`, `updated_at`) VALUES
(1, 3, 27, NULL, '2021-07-21 12:57:28', '2021-07-21 12:57:28', '2021-07-21 12:57:28'),
(5, 8, 15, NULL, '2021-08-05 10:57:07', '2021-08-05 10:57:07', '2021-08-05 10:57:07'),
(7, 14, 15, NULL, '2021-08-23 07:18:25', '2021-08-23 07:18:25', '2021-08-23 07:18:25');

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

CREATE TABLE `variants` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1 for dropdown, 2 for color',
  `position` smallint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 - pending, 1 - active, 2 - blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `variants`
--

INSERT INTO `variants` (`id`, `title`, `type`, `position`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Size', 1, 1, 1, NULL, NULL),
(2, 'Color', 2, 2, 1, NULL, NULL),
(3, 'Phones', 1, 3, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `variant_categories`
--

CREATE TABLE `variant_categories` (
  `variant_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `variant_categories`
--

INSERT INTO `variant_categories` (`variant_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 11, NULL, NULL),
(2, 11, NULL, NULL),
(3, 12, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `variant_options`
--

CREATE TABLE `variant_options` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `hexacode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` smallint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `variant_options`
--

INSERT INTO `variant_options` (`id`, `title`, `variant_id`, `hexacode`, `position`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Small', 1, '', 1, NULL, NULL, NULL),
(2, 'White', 2, '#ffffff', 1, NULL, NULL, NULL),
(3, 'Black', 2, '#000000', 1, NULL, NULL, NULL),
(4, 'Grey', 2, '#808080', 1, NULL, NULL, NULL),
(5, 'Medium', 1, '', 1, NULL, NULL, NULL),
(6, 'Large', 1, '', 1, NULL, NULL, NULL),
(7, 'IPhone', 3, '', 1, NULL, NULL, NULL),
(8, 'Samsung', 3, '', 1, NULL, NULL, NULL),
(9, 'Xiaomi', 3, '', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `variant_option_translations`
--

CREATE TABLE `variant_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_option_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `variant_option_translations`
--

INSERT INTO `variant_option_translations` (`id`, `title`, `variant_option_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Small', 1, 1, NULL, NULL),
(2, 'White', 2, 1, NULL, NULL),
(3, 'Black', 3, 1, NULL, NULL),
(4, 'Grey', 4, 1, NULL, NULL),
(5, 'Medium', 5, 1, NULL, NULL),
(6, 'Large', 6, 1, NULL, NULL),
(7, 'IPhone', 7, 1, NULL, NULL),
(8, 'Samsung', 8, 1, NULL, NULL),
(9, 'Xiaomi', 9, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `variant_translations`
--

CREATE TABLE `variant_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `variant_translations`
--

INSERT INTO `variant_translations` (`id`, `title`, `variant_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Size', 1, 1, NULL, NULL),
(2, 'Color', 2, 1, NULL, NULL),
(3, 'Phones', 3, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_emission_type`
--

CREATE TABLE `vehicle_emission_type` (
  `id` bigint UNSIGNED NOT NULL,
  `emission_type` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `short_desc` text COLLATE utf8mb4_unicode_ci COMMENT 'for extra text below description',
  `logo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dial_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(15,12) DEFAULT NULL,
  `longitude` decimal(16,12) DEFAULT NULL,
  `order_min_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_pre_time` int UNSIGNED DEFAULT '0',
  `auto_reject_time` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_percent` decimal(10,2) DEFAULT '1.00',
  `commission_fixed_per_order` decimal(10,2) DEFAULT '0.00',
  `commission_monthly` decimal(10,2) DEFAULT '0.00',
  `dine_in` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `takeaway` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `rental` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `pick_drop` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `on_demand` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `laundry` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `appointment` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1-active, 0-pending, 2-blocked',
  `add_category` tinyint NOT NULL DEFAULT '1' COMMENT '0 for no, 1 for yes',
  `setting` tinyint NOT NULL DEFAULT '0' COMMENT '0 for no, 1 for yes',
  `is_show_vendor_details` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `show_slot` tinyint NOT NULL DEFAULT '1' COMMENT '1 for yes, 0 for no',
  `vendor_templete_id` bigint UNSIGNED DEFAULT NULL,
  `auto_accept_order` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `service_fee_percent` decimal(10,2) DEFAULT '0.00',
  `order_amount_for_delivery_fee` decimal(64,0) NOT NULL DEFAULT '0',
  `delivery_fee_minimum` decimal(64,2) NOT NULL DEFAULT '0.00',
  `delivery_fee_maximum` decimal(64,2) NOT NULL DEFAULT '0.00',
  `slot_minutes` int DEFAULT NULL,
  `orders_per_slot` int DEFAULT '0',
  `closed_store_order_scheduled` tinyint NOT NULL DEFAULT '0',
  `pincode` int DEFAULT NULL,
  `shiprocket_pickup_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_request` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `ahoy_location` json DEFAULT NULL,
  `max_safety` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `need_container_charges` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `fixed_fee` tinyint NOT NULL COMMENT '0-No, 1-Yes',
  `fixed_fee_amount` decimal(16,2) NOT NULL,
  `price_bifurcation` tinyint NOT NULL DEFAULT '0' COMMENT '0-No, 1-Yes',
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'vendor instagram page link',
  `easebuzz_sub_merchent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'easebuzz sub merchent id Payment gateway',
  `service_charges_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `delivery_charges_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `container_charges_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `service_charges_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `delivery_charges_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `container_charges_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `fixed_fee_tax` tinyint NOT NULL DEFAULT '0' COMMENT '1=active, 0=not',
  `fixed_fee_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `rescheduling_charges` decimal(64,0) NOT NULL DEFAULT '0' COMMENT 'enable for laundry',
  `pickup_cancelling_charges` decimal(64,0) DEFAULT '0',
  `set_weight_price` tinyint DEFAULT NULL,
  `estimation_base_weight` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimation_base_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimation_addition_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cron_for_service_area` tinyint DEFAULT '0',
  `add_markup_price` tinyint NOT NULL DEFAULT '0' COMMENT '0 - no, 1 - yes',
  `markup_price_tax_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `dynamic_html` longtext COLLATE utf8mb4_unicode_ci,
  `rating` decimal(4,2) DEFAULT NULL COMMENT 'vendor average rating.',
  `admin_rating` decimal(4,2) DEFAULT NULL COMMENT 'vendor rating by Admin.',
  `need_sync_with_order` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `razorpay_contact_json` longtext COLLATE utf8mb4_unicode_ci,
  `razorpay_bank_json` longtext COLLATE utf8mb4_unicode_ci,
  `fixed_service_charge` tinyint NOT NULL COMMENT '0-No, 1-Yes',
  `service_charge_amount` decimal(8,2) DEFAULT '0.00',
  `cancel_order_in_processing` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `return_auto_approve` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `is_seller` tinyint NOT NULL DEFAULT '0' COMMENT '1 for Seller, 0 for Vendor',
  `p2p` tinyint NOT NULL DEFAULT '0',
  `subscription_discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `same_day_delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `next_day_delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `hyper_local_delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `cutOff_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refference_id` int DEFAULT NULL,
  `is_vendor_instant_booking` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `slug`, `desc`, `short_desc`, `logo`, `banner`, `address`, `email`, `website`, `phone_no`, `dial_code`, `latitude`, `longitude`, `order_min_amount`, `order_pre_time`, `auto_reject_time`, `commission_percent`, `commission_fixed_per_order`, `commission_monthly`, `dine_in`, `takeaway`, `delivery`, `rental`, `pick_drop`, `on_demand`, `laundry`, `appointment`, `status`, `add_category`, `setting`, `is_show_vendor_details`, `created_at`, `updated_at`, `show_slot`, `vendor_templete_id`, `auto_accept_order`, `service_fee_percent`, `order_amount_for_delivery_fee`, `delivery_fee_minimum`, `delivery_fee_maximum`, `slot_minutes`, `orders_per_slot`, `closed_store_order_scheduled`, `pincode`, `shiprocket_pickup_name`, `city`, `state`, `country`, `return_request`, `ahoy_location`, `max_safety`, `need_container_charges`, `fixed_fee`, `fixed_fee_amount`, `price_bifurcation`, `instagram_url`, `easebuzz_sub_merchent_id`, `service_charges_tax`, `delivery_charges_tax`, `container_charges_tax`, `service_charges_tax_id`, `delivery_charges_tax_id`, `container_charges_tax_id`, `fixed_fee_tax`, `fixed_fee_tax_id`, `rescheduling_charges`, `pickup_cancelling_charges`, `set_weight_price`, `estimation_base_weight`, `estimation_base_price`, `estimation_addition_price`, `cron_for_service_area`, `add_markup_price`, `markup_price_tax_id`, `dynamic_html`, `rating`, `admin_rating`, `need_sync_with_order`, `razorpay_contact_json`, `razorpay_bank_json`, `fixed_service_charge`, `service_charge_amount`, `cancel_order_in_processing`, `return_auto_approve`, `is_seller`, `p2p`, `subscription_discount_percent`, `same_day_delivery`, `next_day_delivery`, `hyper_local_delivery`, `cutOff_time`, `refference_id`, `is_vendor_instant_booking`) VALUES
(1, 'DeliveryZone', NULL, NULL, NULL, 'default/default_logo.png', 'default/default_image.png', 'Sheikh Zayed Road - Dubai - United Arab Emirates', NULL, NULL, NULL, NULL, '25.060924600000', '55.128979500000', '0.00', NULL, NULL, '1.00', '0.00', '0.00', 0, 1, 1, 0, 0, 0, 0, 0, 2, 1, 0, 0, NULL, '2021-07-13 10:54:07', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(2, 'The Capital Grille', 'the-capital-grille', 'NULL', NULL, 'vendor/xkiOVzhuqtUzB7bEXRi3oMLLvG29q63YvxFxFdbA.jpg', 'vendor/7FVZUaAiZCfyJqARhF3eJn1WCXiMXWKV0cZtLNB5.jpg', '1095 Stanley Rd,Jozini', 'capitalgrill@support.com', NULL, '5896325896', NULL, '-27.429351500000', '32.065069700000', '200.00', 25, 'NULL', '10.00', '10.00', '0.00', 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, NULL, '2022-11-07 09:19:32', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(3, 'Daniel', 'daniel', NULL, NULL, 'vendor/E6f5XwJVODuz2dZzYGBbGgnRMF6kzntvTYA6doHw.png', 'vendor/XvjyekwqZRv1c8st1Pk9Gsmr5VN3jdQbFyFnYCpI.jpg', '2225 Glyn St,Pretoria', 'daniel@support.com', NULL, '5896325896', NULL, '-25.750007900000', '28.242499200000', '500.00', 30, '5', '15.00', '15.00', '0.00', 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, NULL, '2022-11-07 09:19:32', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(4, 'Chill & Grill', 'chill-grill', NULL, NULL, 'vendor/HoP7KStQgyYkbuo0RkaO5yBIzkzsKaqRQX8IYS2D.jpg', 'vendor/zAa63JQyk69cdb4RZcoYhRMKHDKrn8qAfKVnr1F7.jpg', '840 Schoeman St,Pretoria', 'chill&grill@support.com', NULL, '7854125896', NULL, '-25.747038300000', '28.221772600000', '200.00', 20, 'NULL', '20.00', '10.00', NULL, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, NULL, '2022-11-07 09:19:32', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(5, 'Carmine\'s Italian', 'carmines-italian', NULL, NULL, 'vendor/v95NNaYztJd3oXgwFuNAOGbRROFVvcleEn1XGIK3.jpg', 'vendor/r6d6EIqJACTwkN8KHTFYucfn9wFi4oyiLbMzAxVb.jpg', '1959 Bodenstein St,Katlehong', 'carmines@support.com', NULL, '5896325896', NULL, '-26.365125900000', '28.152619600000', '200.00', 15, '2', '15.00', '12.00', '0.00', 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, NULL, '2022-11-07 09:19:32', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(6, 'Seasons 52', 'seasons-52', NULL, NULL, 'vendor/ObJQHcqJjBzIOx9Y4kAZtXS7MOdb0tm45SXFMkP3.png', 'vendor/oRlowvrjdOdD9I3UCqOjUHhF5tpsYf4iTAXYtwPJ.jpg', '1875 Sandown Rd,Calvinia', 'season@support.com', NULL, '658965236', NULL, '51.373314700000', '-0.361966500000', '100.00', 20, 'NULL', '10.00', '13.00', NULL, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, NULL, '2022-11-07 09:19:32', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(7, 'Waitrose', 'waitrose', NULL, NULL, 'vendor/FIX6fTPdtBeZ99YayM0s1cb2yAqnMIcxNUyFPBji.jpg', 'default/default_image.png', 'South Africa', 'testvendor@gmail.com', NULL, NULL, NULL, '-30.559482000000', '22.937506000000', '0.00', NULL, NULL, '1.00', '0.00', '0.00', 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, '2021-09-17 05:56:57', '2022-11-07 09:19:32', 1, NULL, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0),
(8, 'SAMMYS KITCHEN', 'sammys-kitchen', 'sammyblazz kitchen deals in all african dishes', NULL, 'default/default_logo.png', 'vendor/9sBCOiM20IywPTe1CDhm8J1deiItwnTayhjJEh2D.png', '39c Shakiru Anjorin Street, Lagos, Nigeria', 'ebenezersam14@gmail.com', NULL, NULL, NULL, '6.451150100000', '3.470912900000', '0.00', 15, NULL, '10.00', '0.00', NULL, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, '2021-09-27 19:53:54', '2022-11-07 09:19:32', 0, 5, 0, '0.00', '0', '0.00', '0.00', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '0.00', 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '0', '0', NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, 0, '0.00', 0, 0, 0, 0, '0.00', 0, 0, 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_additional_info`
--

CREATE TABLE `vendor_additional_info` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ifsc_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `compare_categories` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_categories`
--

CREATE TABLE `vendor_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 - yes, 0 - no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `vendor_categories`
--

INSERT INTO `vendor_categories` (`id`, `vendor_id`, `category_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 3, 1, '2021-07-13 11:22:31', '2021-07-13 11:22:31'),
(2, 6, 20, 1, '2021-07-20 11:57:45', '2021-07-20 11:57:45'),
(3, 5, 14, 1, '2021-07-20 11:58:18', '2021-07-20 11:58:18'),
(4, 4, 15, 1, '2021-07-20 11:59:28', '2021-07-20 11:59:28'),
(5, 3, 16, 1, '2021-07-20 12:00:35', '2021-07-20 12:00:35'),
(6, 3, 19, 1, '2021-07-20 12:01:18', '2021-07-20 12:01:18'),
(7, 2, 17, 1, '2021-07-20 12:02:16', '2021-07-20 12:02:16'),
(8, 2, 18, 1, '2021-07-20 12:05:12', '2021-07-20 12:05:12'),
(9, 8, 2, 1, '2021-09-27 20:16:37', '2021-09-27 20:16:37'),
(10, 8, 8, 1, '2021-09-27 20:16:58', '2021-09-27 20:16:58'),
(11, 8, 9, 1, '2021-09-27 20:16:59', '2021-09-27 20:16:59'),
(12, 8, 10, 1, '2021-09-27 20:17:01', '2021-09-27 20:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_cities`
--

CREATE TABLE `vendor_cities` (
  `id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(15,12) DEFAULT NULL,
  `longitude` decimal(16,12) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `place_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_city_translations`
--

CREATE TABLE `vendor_city_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_city_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_connected_accounts`
--

CREATE TABLE `vendor_connected_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `account_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_option_id` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0-inactive, 1-active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_dinein_categories`
--

CREATE TABLE `vendor_dinein_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_dinein_category_translations`
--

CREATE TABLE `vendor_dinein_category_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_dinein_tables`
--

CREATE TABLE `vendor_dinein_tables` (
  `id` bigint UNSIGNED NOT NULL,
  `table_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `seating_number` int DEFAULT NULL,
  `vendor_dinein_category_id` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0-active, 1-inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_dinein_table_translations`
--

CREATE TABLE `vendor_dinein_table_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_dinein_table_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_docs`
--

CREATE TABLE `vendor_docs` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `vendor_registration_document_id` bigint UNSIGNED NOT NULL,
  `file_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_facilties`
--

CREATE TABLE `vendor_facilties` (
  `id` bigint UNSIGNED NOT NULL,
  `facilty_id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_media`
--

CREATE TABLE `vendor_media` (
  `id` bigint UNSIGNED NOT NULL,
  `media_type` tinyint NOT NULL DEFAULT '1' COMMENT '1 - image, 2 - video, 3 - file',
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `vendor_media`
--

INSERT INTO `vendor_media` (`id`, `media_type`, `vendor_id`, `path`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 'prods/a0lRm1RR4VqafeanGoUD1mjr6PhAelhLIT3KTz29.jpg', '2021-07-13 11:22:51', '2021-07-13 11:22:51'),
(2, 1, 6, 'prods/jtxnzniTMWs0LLsiPDIHrtITaqWW8uSebTxzvopM.jpg', '2021-07-13 11:23:19', '2021-07-13 11:23:19'),
(3, 1, 6, 'prods/LtPMAEFBu4Rfm69Nq5UGppBPMVd4YjvWadMxNtLY.jpg', '2021-07-13 11:23:31', '2021-07-13 11:23:31'),
(4, 1, 6, 'prods/SQQEPUEDyHxI0kyaZYmFimxJoAHoRLkP3V9hXNOU.jpg', '2021-07-13 11:24:47', '2021-07-13 11:24:47'),
(5, 1, 6, 'prods/gFfFN43fZfMOcNkTctsG7sGgQgbYm17Vy5qcBOlP.jpg', '2021-07-13 11:25:09', '2021-07-13 11:25:09'),
(6, 1, 6, 'prods/Nbp0kceNQEAgkxAU0jzz9H0s9El4BE7Up7FdPZQF.jpg', '2021-07-13 11:25:36', '2021-07-13 11:25:36'),
(17, 1, 5, 'prods/JXtEgwHOw5lf50Xkkb0tpMgCkeEKxJRpstjju5gw.jpg', '2021-07-20 12:20:13', '2021-07-20 12:20:13'),
(25, 1, 3, 'prods/xBAo0XfIafwpSOAXVpyNbkvD4JjCFSjYdUWuQOTV.jpg', '2021-07-20 12:29:48', '2021-07-20 12:29:48'),
(26, 1, 3, 'prods/DDSQFDnIvZJNEcm9ZWmcf3w96RWWM6Vi6ayGG69Y.jpg', '2021-07-20 12:30:44', '2021-07-20 12:30:44'),
(27, 1, 3, 'prods/RyK5pH9bbln0a7wTfhYKi161bk6esFt7VThFUUWs.jpg', '2021-07-20 12:32:48', '2021-07-20 12:32:48'),
(28, 1, 3, 'prods/ZVI8RJTOdf1OKVEyGgVP5qp5T8XKuND7n1FqOO5f.jpg', '2021-07-20 12:34:00', '2021-07-20 12:34:00'),
(29, 1, 3, 'prods/XA88b8InEHCvwNHGR3LR17n8mbX9Y4N6kvqvdTOW.jpg', '2021-07-20 12:34:50', '2021-07-20 12:34:50'),
(30, 1, 3, 'prods/0T7CAipE47pUArQk5RCYZzjvoVuNv1Bcl78hNipl.jpg', '2021-07-20 12:36:01', '2021-07-20 12:36:01'),
(31, 1, 3, 'prods/T96kZu3XwHS04G7bOQGvsX2rJFKxInqtQjhDXQZJ.jpg', '2021-07-20 12:36:57', '2021-07-20 12:36:57'),
(32, 1, 3, 'prods/eQ8YpMygOsJzWrKwXyilO1XS8q7bSxp8dirHcwwY.jpg', '2021-07-20 12:38:03', '2021-07-20 12:38:03'),
(33, 1, 3, 'prods/IolJHEEULku1EEXAQrZWsy4DVMqU6kXSZZCUmPwR.jpg', '2021-07-20 12:39:05', '2021-07-20 12:39:05'),
(34, 1, 3, 'prods/ea6blMBGeqQqboHnNpAtrlkuOzHHtyA3SWuytVF7.jpg', '2021-07-20 12:40:27', '2021-07-20 12:40:27'),
(35, 1, 3, 'prods/3LWjARvR1MV80PFo08KP823dyt4axBASSEdm8vpx.jpg', '2021-07-20 12:41:40', '2021-07-20 12:41:40'),
(36, 1, 3, 'prods/bpND1Y51DP0Pxj4gfBjn5evBPBXVDfL72taY0NY0.jpg', '2021-07-20 12:43:06', '2021-07-20 12:43:06'),
(42, 1, 2, 'prods/L7Yt32SBNn6aDQnQNL2OhueGRMappcDvrVMUetw7.jpg', '2021-07-20 12:50:44', '2021-07-20 12:50:44'),
(43, 1, 2, 'prods/zIRNsYT6gtvRPi4wD7s6OYWOgtgGscSoKOacknhf.jpg', '2021-07-20 12:52:22', '2021-07-20 12:52:22'),
(44, 1, 2, 'prods/zvouZpMv9Nk3xC8PGf0JkBgRhldhbHY0pYOb69r4.jpg', '2021-07-20 12:53:22', '2021-07-20 12:53:22'),
(45, 1, 2, 'prods/Gzt8dAVwQ1nAligj5nLY8kSTAkQZFeH5VNLoxCOr.jpg', '2021-07-20 12:54:30', '2021-07-20 12:54:30'),
(46, 1, 2, 'prods/xndnXsu5RgYLkgiZjYqVOtxGfVyMFQv8I36ys8XJ.jpg', '2021-07-20 12:55:40', '2021-07-20 12:55:40'),
(49, 1, 5, 'prods/Y2r9DC6bZFu1Cg3v5ky0Wfq6lqKMBHkzK3VDepWk.jpg', '2021-08-05 05:13:19', '2021-08-05 05:13:19'),
(50, 1, 5, 'prods/luEktxEPesBAKNOdcaqPYsiEfnYv6uOg8lMXGO7X.jpg', '2021-08-05 05:14:33', '2021-08-05 05:14:33'),
(52, 1, 5, 'prods/PSqgRXLh4wsjEm0y9sAuyeEQoHj4ijiF0JuszQvu.jpg', '2021-08-05 05:15:41', '2021-08-05 05:15:41'),
(53, 1, 5, 'prods/KL0IrS7rhTPVhHvZzu6RdfW9kdXhYa4zD426jj6Y.jpg', '2021-08-05 05:16:13', '2021-08-05 05:16:13'),
(54, 1, 4, 'prods/Pk6WmU9TU7xMJC6VpYyeTs0mbt0uCMM8NgT9mUR5.jpg', '2021-08-05 05:18:20', '2021-08-05 05:18:20'),
(55, 1, 4, 'prods/YyFnTufBbFUXZ9zH6wZsJY4RIaCMwlJj3TcNqFSL.jpg', '2021-08-05 05:19:03', '2021-08-05 05:19:03'),
(56, 1, 4, 'prods/M0fuSUeBRzXSOWaIlwnIBQJ0OPkmsNVAZMfVwAGe.jpg', '2021-08-05 05:19:31', '2021-08-05 05:19:31'),
(57, 1, 4, 'prods/IG3lYZzwXR6tY7EKtOaP3nXmBCJtuPE6HbYg6bxS.jpg', '2021-08-05 05:20:19', '2021-08-05 05:20:19'),
(58, 1, 4, 'prods/wYJSdo4APRYGbfSJO88Fim8iU1Gm3lsfyJjH1yft.jpg', '2021-08-05 05:21:05', '2021-08-05 05:21:05'),
(59, 1, 4, 'prods/E9zuMi9S3towmm9y8E3dev7UV5OzPGFUNTYZZtFj.jpg', '2021-08-05 05:21:36', '2021-08-05 05:21:36'),
(60, 1, 4, 'prods/0BnEBFe16vuxJ8YZdsoMdT0wtkC9eybPFV7C9LSz.png', '2021-08-05 05:22:01', '2021-08-05 05:22:01'),
(61, 1, 2, 'prods/n0Kvzf7T9tOWjEuSFH5jzZ7TVeeh9kRbZF5AXK4l.jpg', '2021-08-05 05:23:02', '2021-08-05 05:23:02'),
(62, 1, 2, 'prods/h6jwHlWAE9BPZssDvWfwt8HXcEGIzfvybHMbrXtY.jpg', '2021-08-05 05:23:56', '2021-08-05 05:23:56'),
(63, 1, 2, 'prods/4pesPjHHYyJEdJZ9zQ7hK992jx8RuOPizr5o4kjg.jpg', '2021-08-05 05:24:16', '2021-08-05 05:24:16'),
(64, 1, 2, 'prods/CN465ufqhxDmMe3YQVjDhuJAIk5c9SsU4NXjnLAl.jpg', '2021-08-05 05:25:26', '2021-08-05 05:25:26'),
(65, 1, 2, 'prods/4946p3x2WDCeL7M7NyiraSgcFfO2KGjubzq73IRC.jpg', '2021-08-05 05:26:04', '2021-08-05 05:26:04'),
(66, 1, 2, 'prods/WMb3ZBW7gJ0bDYvP1K9Nrxwu3z8TWnCbEaKuBtdj.jpg', '2021-08-05 05:26:30', '2021-08-05 05:26:30'),
(67, 1, 2, 'prods/FantsIuqnmHayDLrwxlBqyCeN794dzlCrphVevgI.jpg', '2021-08-05 05:27:03', '2021-08-05 05:27:03'),
(68, 1, 6, 'prods/pA1UD50Sa1WuEaaqDTeKblCfAgRIUTmXiZriRHvY.jpg', '2021-08-05 05:27:52', '2021-08-05 05:27:52'),
(69, 1, 6, 'prods/cPEl5Z6XzvpBMgzKXEpxmijIeTdRAPUFJNZKYGpE.jpg', '2021-08-05 05:28:10', '2021-08-05 05:28:10'),
(70, 1, 6, 'prods/WbpKGjK4h8d9y2QBqbyiQTnyXPn1i3rZZG4ni6jW.jpg', '2021-08-05 05:28:25', '2021-08-05 05:28:25'),
(71, 1, 6, 'prods/Om638AVAPIpgORRoqbVAynLVME3tgM7qWxUDYXPv.jpg', '2021-08-05 05:28:57', '2021-08-05 05:28:57'),
(72, 1, 6, 'prods/TYqMKMnwdLkf4zysk7ttl07qInasy9uEBKftL5bj.jpg', '2021-08-05 05:29:22', '2021-08-05 05:29:22'),
(73, 1, 6, 'prods/aqdi2opJC2819F7ZPypaXHBWSeI2NPiUU22VttJq.jpg', '2021-08-05 05:29:50', '2021-08-05 05:29:50'),
(74, 1, 5, 'prods/0DD1LmFKiaRkojEPYlUp4xTYdR0ZeNhbbqOVIwDj.jpg', '2021-08-05 05:58:39', '2021-08-05 05:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_min_amounts`
--

CREATE TABLE `vendor_min_amounts` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `order_min_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_multi_banners`
--

CREATE TABLE `vendor_multi_banners` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_order_cancel_return_payments`
--

CREATE TABLE `vendor_order_cancel_return_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `wallet_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `online_payment_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_points` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_points_earned` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_return_amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_order_dispatcher_statuses`
--

CREATE TABLE `vendor_order_dispatcher_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `dispatcher_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `dispatcher_status_option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 : pickup , 2 : drop'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_order_product_dispatcher_statuses`
--

CREATE TABLE `vendor_order_product_dispatcher_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `dispatcher_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `dispatcher_status_option_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `order_product_route_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1 : pickup , 2 : drop , 3 : Appointment',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_status_option_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'single product dispatch',
  `long_term_schedule_id` bigint UNSIGNED DEFAULT NULL COMMENT 'long_term_schedule_id from order_long_term_service_schedules'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_order_product_statuses`
--

CREATE TABLE `vendor_order_product_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `order_status_option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_vendor_product_id` bigint UNSIGNED DEFAULT NULL,
  `dispatcher_status_option_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_order_statuses`
--

CREATE TABLE `vendor_order_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `order_vendor_id` bigint UNSIGNED DEFAULT NULL,
  `order_status_option_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `vendor_order_statuses`
--

INSERT INTO `vendor_order_statuses` (`id`, `order_id`, `order_vendor_id`, `order_status_option_id`, `created_at`, `updated_at`, `vendor_id`) VALUES
(1, 1, 1, 1, '2021-07-13 13:03:52', '2021-07-13 13:03:52', 6),
(2, 2, 2, 1, '2021-07-13 13:05:44', '2021-07-13 13:05:44', 6),
(3, 3, 3, 1, '2021-07-13 13:08:01', '2021-07-13 13:08:01', 6),
(4, 4, 4, 1, '2021-07-13 13:09:08', '2021-07-13 13:09:08', 6),
(5, 5, 5, 1, '2021-07-13 13:29:37', '2021-07-13 13:29:37', 6),
(6, 6, 6, 1, '2021-07-20 13:06:57', '2021-07-20 13:06:57', 4),
(7, 6, NULL, 2, '2021-07-20 13:08:14', '2021-07-20 13:08:14', 4),
(8, 6, NULL, 4, '2021-07-20 13:08:16', '2021-07-20 13:08:16', 4),
(9, 6, NULL, 5, '2021-07-20 13:08:18', '2021-07-20 13:08:18', 4),
(10, 6, NULL, 6, '2021-07-20 13:08:20', '2021-07-20 13:08:20', 4),
(11, 7, 7, 1, '2021-07-20 13:10:08', '2021-07-20 13:10:08', 5),
(12, 7, NULL, 2, '2021-07-20 13:12:03', '2021-07-20 13:12:03', 5),
(13, 7, NULL, 4, '2021-07-20 13:12:05', '2021-07-20 13:12:05', 5),
(14, 7, NULL, 5, '2021-07-20 13:12:07', '2021-07-20 13:12:07', 5),
(15, 7, NULL, 6, '2021-07-20 13:12:16', '2021-07-20 13:12:16', 5),
(16, 8, 8, 1, '2021-08-05 05:38:50', '2021-08-05 05:38:50', 6),
(17, 9, 9, 1, '2021-08-05 05:39:51', '2021-08-05 05:39:51', 4),
(18, 10, 10, 1, '2021-08-05 06:05:40', '2021-08-05 06:05:40', 3),
(19, 12, 11, 1, '2021-08-05 06:05:59', '2021-08-05 06:05:59', 2),
(20, 13, 12, 1, '2021-08-05 06:11:12', '2021-08-05 06:11:12', 3),
(21, 15, 13, 1, '2021-08-05 06:32:50', '2021-08-05 06:32:50', 3),
(22, 16, 14, 1, '2021-08-05 12:05:01', '2021-08-05 12:05:01', 5),
(23, 17, 15, 1, '2021-08-12 11:46:45', '2021-08-12 11:46:45', 3),
(24, 18, 16, 1, '2021-08-23 07:19:32', '2021-08-23 07:19:32', 5),
(25, 20, 17, 1, '2021-08-23 07:25:24', '2021-08-23 07:25:24', 5);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_payouts`
--

CREATE TABLE `vendor_payouts` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `payout_option_id` bigint UNSIGNED DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` bigint UNSIGNED DEFAULT NULL,
  `requested_by` bigint UNSIGNED DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0-pending, 1-paid, 2-failed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_registration_documents`
--

CREATE TABLE `vendor_registration_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` int NOT NULL DEFAULT '1' COMMENT '0 means not required, 1 means required',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_registration_document_translations`
--

CREATE TABLE `vendor_registration_document_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `vendor_registration_document_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_registration_select_options`
--

CREATE TABLE `vendor_registration_select_options` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_registration_documents_id` bigint UNSIGNED NOT NULL,
  `status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_registration_select_option_translations`
--

CREATE TABLE `vendor_registration_select_option_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_registration_select_option_id` bigint UNSIGNED NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_saved_payment_methods`
--

CREATE TABLE `vendor_saved_payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `payment_option_id` int UNSIGNED DEFAULT NULL,
  `card_last_four_digit` int UNSIGNED DEFAULT NULL,
  `card_expiry_month` int UNSIGNED DEFAULT NULL,
  `card_expiry_year` int UNSIGNED DEFAULT NULL,
  `customerReference` text COLLATE utf8mb4_unicode_ci,
  `cardReference` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_sections`
--

CREATE TABLE `vendor_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `order_by` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_section_heading_translations`
--

CREATE TABLE `vendor_section_heading_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_section_id` bigint UNSIGNED NOT NULL,
  `heading` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_section_translations`
--

CREATE TABLE `vendor_section_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_section_id` bigint UNSIGNED NOT NULL,
  `title` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_slots`
--

CREATE TABLE `vendor_slots` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `geo_id` bigint UNSIGNED DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `dine_in` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `takeaway` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `rental` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `pick_drop` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `on_demand` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `laundry` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `appointment` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `slot_type` tinyint DEFAULT '0' COMMENT '0-schedule, 1-pickup, 2-dropoff',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `service_area_id` bigint UNSIGNED DEFAULT NULL,
  `p2p` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_slot_dates`
--

CREATE TABLE `vendor_slot_dates` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `specific_date` date NOT NULL,
  `working_today` tinyint NOT NULL DEFAULT '1' COMMENT '1 - yes, 0 - no',
  `dine_in` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `takeaway` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `delivery` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no',
  `rental` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `pick_drop` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `on_demand` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `laundry` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `appointment` tinyint DEFAULT '0' COMMENT '0-No, 1-Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `service_area_id` bigint UNSIGNED DEFAULT NULL,
  `p2p` tinyint NOT NULL DEFAULT '0' COMMENT '1 for yes, 0 for no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_slot_date_service_areas`
--

CREATE TABLE `vendor_slot_date_service_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_slot_date_id` bigint UNSIGNED DEFAULT NULL,
  `service_area_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_slot_service_areas`
--

CREATE TABLE `vendor_slot_service_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_slot_id` bigint UNSIGNED DEFAULT NULL,
  `service_area_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_social_media_urls`
--

CREATE TABLE `vendor_social_media_urls` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` bigint UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_templetes`
--

CREATE TABLE `vendor_templetes` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '1 - active, 0 - inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_templetes`
--

INSERT INTO `vendor_templetes` (`id`, `title`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Only Product', 'Grid', 1, NULL, NULL),
(2, 'Only Category', 'Grid', 1, NULL, NULL),
(3, 'Only Product', 'List', 0, NULL, NULL),
(4, 'Only Category', 'List', 0, NULL, NULL),
(5, 'Product with Category', 'Grid', 1, NULL, NULL),
(6, 'Product with Category Extended', 'List', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_users`
--

CREATE TABLE `vendor_users` (
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

-- --------------------------------------------------------

--
-- Table structure for table `verification_options`
--

CREATE TABLE `verification_options` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` json DEFAULT NULL COMMENT 'credentials in json format',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0 inactive, 1 active, 2 delete',
  `test_mode` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = false, 1 = true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_options`
--

INSERT INTO `verification_options` (`id`, `code`, `path`, `title`, `credentials`, `status`, `test_mode`, `created_at`, `updated_at`) VALUES
(1, 'passbase', 'passbase/passbase-php', 'Passbase', NULL, 0, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `holder_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `balance` decimal(8,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holder_id` bigint UNSIGNED NOT NULL,
  `decimal_places` tinyint NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `name`, `slug`, `holder_type`, `meta`, `balance`, `description`, `holder_id`, `decimal_places`, `created_at`, `updated_at`) VALUES
(1, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 1, 2, '2021-07-13 13:09:58', '2021-07-13 13:09:58'),
(2, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 7, 2, '2021-08-05 05:42:48', '2021-08-05 05:42:48'),
(3, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 9, 2, '2021-08-05 06:19:06', '2021-08-05 06:19:06'),
(4, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 13, 2, '2021-08-12 11:47:56', '2021-08-12 11:47:56'),
(5, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 13, 2, '2021-08-12 11:47:56', '2021-08-12 11:47:56'),
(6, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 14, 2, '2021-08-23 07:18:34', '2021-08-23 07:18:34'),
(7, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 15, 2, '2021-08-23 07:24:56', '2021-08-23 07:24:56'),
(8, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 12, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(9, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 11, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(10, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 10, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(11, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 8, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(12, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 6, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(13, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 5, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(14, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 4, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(15, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 3, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(16, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 2, 2, '2021-09-16 15:11:50', '2021-09-16 15:11:50'),
(17, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 16, 2, '2021-09-17 05:57:57', '2021-09-17 05:57:57'),
(18, 'Default Wallet', 'default', 'App\\Models\\User', '[]', '0.00', NULL, 18, 2, '2022-10-27 12:49:43', '2022-10-27 12:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` bigint UNSIGNED NOT NULL,
  `tracking_order_id` int DEFAULT NULL,
  `response` longtext COLLATE utf8mb4_unicode_ci,
  `hook_from` enum('L') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'L' COMMENT 'L : Lalamove',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `web_stylings`
--

CREATE TABLE `web_stylings` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint DEFAULT NULL COMMENT '1-Text, 2-Option, 3-Option Images, 4-Color',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `web_stylings`
--

INSERT INTO `web_stylings` (`id`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Home Page Style', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `web_styling_options`
--

CREATE TABLE `web_styling_options` (
  `id` bigint UNSIGNED NOT NULL,
  `web_styling_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_selected` tinyint NOT NULL DEFAULT '1' COMMENT '1-yes, 2-no',
  `template_id` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci TABLESPACE `innodb_system`;

--
-- Dumping data for table `web_styling_options`
--

INSERT INTO `web_styling_options` (`id`, `web_styling_id`, `name`, `image`, `is_selected`, `template_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Home Page 1', 'template-one.jpg', 1, 1, NULL, '2022-09-16 06:08:57'),
(2, 1, 'Home Page 2', 'template-two.jpg', 0, 2, NULL, '2022-09-16 06:08:57'),
(3, 1, 'Food Delivery', 'template-three.jpg', 0, 3, '2022-02-15 13:46:41', '2022-09-16 06:08:57'),
(4, 1, 'E-Commerce', 'template-four.jpg', 0, 4, '2022-05-16 11:13:24', '2022-09-16 06:08:57'),
(6, 1, 'On Demand Service', 'template-six.jpg', 0, 6, '2022-08-09 12:19:59', '2022-09-16 06:08:57'),
(7, 1, 'E-Commerce 2', 'template-eight.jpg', 0, 8, '2023-01-06 05:14:01', '2023-01-06 05:14:01'),
(8, 1, 'p2p', 'template-nine.jpg', 0, 9, '2023-01-06 05:14:01', '2023-01-06 05:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `woocommerces`
--

CREATE TABLE `woocommerces` (
  `id` bigint UNSIGNED NOT NULL,
  `url` mediumtext COLLATE utf8mb4_unicode_ci,
  `consumer_key` mediumtext COLLATE utf8mb4_unicode_ci,
  `consumer_secret` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `addon_options`
--
ALTER TABLE `addon_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_options_addon_id_foreign` (`addon_id`);

--
-- Indexes for table `addon_option_translations`
--
ALTER TABLE `addon_option_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_option_translations_addon_opt_id_foreign` (`addon_opt_id`),
  ADD KEY `addon_option_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `addon_sets`
--
ALTER TABLE `addon_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_sets_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `addon_set_translations`
--
ALTER TABLE `addon_set_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_set_translations_addon_id_foreign` (`addon_id`),
  ADD KEY `addon_set_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `api_logs`
--
ALTER TABLE `api_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_dynamic_tutorials`
--
ALTER TABLE `app_dynamic_tutorials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_stylings`
--
ALTER TABLE `app_stylings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_styling_options`
--
ALTER TABLE `app_styling_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `app_styling_options_app_styling_id_foreign` (`app_styling_id`);

--
-- Indexes for table `assign_qrcodes_to_orders`
--
ALTER TABLE `assign_qrcodes_to_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attributes_type_index` (`type`),
  ADD KEY `attributes_position_index` (`position`),
  ADD KEY `attributes_status_index` (`status`),
  ADD KEY `attributes_user_id_foreign` (`user_id`);

--
-- Indexes for table `attribute_categories`
--
ALTER TABLE `attribute_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_categories_attribute_id_foreign` (`attribute_id`),
  ADD KEY `attribute_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `attribute_options`
--
ALTER TABLE `attribute_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_options_position_index` (`position`),
  ADD KEY `attribute_options_attribute_id_foreign` (`attribute_id`);

--
-- Indexes for table `attribute_option_translations`
--
ALTER TABLE `attribute_option_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_option_translations_attribute_option_id_foreign` (`attribute_option_id`),
  ADD KEY `attribute_option_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `attribute_translations`
--
ALTER TABLE `attribute_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_translations_attribute_id_foreign` (`attribute_id`),
  ADD KEY `attribute_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audits_user_id_user_type_index` (`user_id`,`user_type`);

--
-- Indexes for table `authentication_log`
--
ALTER TABLE `authentication_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `authentication_log_authenticatable_type_authenticatable_id_index` (`authenticatable_type`,`authenticatable_id`);

--
-- Indexes for table `auto_reject_orders_cron`
--
ALTER TABLE `auto_reject_orders_cron`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banners_redirect_category_id_foreign` (`redirect_category_id`),
  ADD KEY `banners_redirect_vendor_id_foreign` (`redirect_vendor_id`),
  ADD KEY `banners_name_index` (`name`),
  ADD KEY `banners_status_index` (`status`),
  ADD KEY `banners_start_date_time_index` (`start_date_time`),
  ADD KEY `banners_end_date_time_index` (`end_date_time`);

--
-- Indexes for table `banner_service_areas`
--
ALTER TABLE `banner_service_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banner_service_areas_service_area_id_foreign` (`service_area_id`);

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bids_bid_req_id_foreign` (`bid_req_id`);

--
-- Indexes for table `bid_products`
--
ALTER TABLE `bid_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bid_products_bid_id_foreign` (`bid_id`);

--
-- Indexes for table `bid_requests`
--
ALTER TABLE `bid_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bid_requests_user_id_foreign` (`user_id`);

--
-- Indexes for table `blocked_tokens`
--
ALTER TABLE `blocked_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brands_position_index` (`position`),
  ADD KEY `brands_status_index` (`status`);

--
-- Indexes for table `brand_categories`
--
ALTER TABLE `brand_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_categories_brand_id_foreign` (`brand_id`),
  ADD KEY `brand_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `brand_translations`
--
ALTER TABLE `brand_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_translations_brand_id_foreign` (`brand_id`),
  ADD KEY `brand_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `business_types`
--
ALTER TABLE `business_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cab_booking_layouts`
--
ALTER TABLE `cab_booking_layouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cab_booking_layout_banners`
--
ALTER TABLE `cab_booking_layout_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cab_booking_layout_banners_cab_booking_layout_id_foreign` (`cab_booking_layout_id`);

--
-- Indexes for table `cab_booking_layout_categories`
--
ALTER TABLE `cab_booking_layout_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cab_booking_layout_categories_cab_booking_layout_id_foreign` (`cab_booking_layout_id`),
  ADD KEY `cab_booking_layout_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `cab_booking_layout_transaltions`
--
ALTER TABLE `cab_booking_layout_transaltions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cab_booking_layout_transaltions_cab_booking_layout_id_foreign` (`cab_booking_layout_id`),
  ADD KEY `cab_booking_layout_transaltions_language_id_foreign` (`language_id`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `campaign_rosters`
--
ALTER TABLE `campaign_rosters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `caregory_kyc_docs`
--
ALTER TABLE `caregory_kyc_docs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_id_foreign` (`user_id`),
  ADD KEY `carts_created_by_foreign` (`created_by`),
  ADD KEY `carts_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `cart_addons`
--
ALTER TABLE `cart_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_addons_cart_product_id_foreign` (`cart_product_id`),
  ADD KEY `cart_addons_addon_id_foreign` (`addon_id`),
  ADD KEY `cart_addons_option_id_foreign` (`option_id`);

--
-- Indexes for table `cart_coupons`
--
ALTER TABLE `cart_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_coupons_cart_id_foreign` (`cart_id`),
  ADD KEY `cart_coupons_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `cart_products`
--
ALTER TABLE `cart_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_products_cart_id_foreign` (`cart_id`),
  ADD KEY `cart_products_product_id_foreign` (`product_id`),
  ADD KEY `cart_products_vendor_id_foreign` (`vendor_id`),
  ADD KEY `cart_products_created_by_foreign` (`created_by`),
  ADD KEY `cart_products_variant_id_foreign` (`variant_id`),
  ADD KEY `cart_products_tax_rate_id_foreign` (`tax_rate_id`),
  ADD KEY `cart_products_status_index` (`status`),
  ADD KEY `cart_products_is_tax_applied_index` (`is_tax_applied`),
  ADD KEY `cart_products_tax_category_id_foreign` (`tax_category_id`),
  ADD KEY `cart_products_slot_id_foreign` (`slot_id`),
  ADD KEY `cart_products_product_variant_by_role_id_foreign` (`product_variant_by_role_id`);

--
-- Indexes for table `cart_product_prescriptions`
--
ALTER TABLE `cart_product_prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_product_prescriptions_cart_id_foreign` (`cart_id`),
  ADD KEY `cart_product_prescriptions_product_id_foreign` (`product_id`),
  ADD KEY `cart_product_prescriptions_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `cart_vendor_delivery_fee`
--
ALTER TABLE `cart_vendor_delivery_fee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_vendor_delivery_fee_cart_id_foreign` (`cart_id`),
  ADD KEY `cart_vendor_delivery_fee_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `car_details`
--
ALTER TABLE `car_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_details_car_id_foreign` (`car_id`);

--
-- Indexes for table `car_images`
--
ALTER TABLE `car_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_images_car_id_foreign` (`car_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_client_code_foreign` (`client_code`),
  ADD KEY `categories_vendor_id_foreign` (`vendor_id`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_type_id_foreign` (`type_id`),
  ADD KEY `categories_status_index` (`status`),
  ADD KEY `categories_is_core_index` (`is_core`),
  ADD KEY `categories_position_index` (`position`),
  ADD KEY `categories_can_add_products_index` (`can_add_products`),
  ADD KEY `categories_display_mode_index` (`display_mode`);

--
-- Indexes for table `category_histories`
--
ALTER TABLE `category_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_histories_category_id_foreign` (`category_id`);

--
-- Indexes for table `category_kyc_documents`
--
ALTER TABLE `category_kyc_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_kyc_document_mappings`
--
ALTER TABLE `category_kyc_document_mappings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_kyc_document_translations`
--
ALTER TABLE `category_kyc_document_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_roles`
--
ALTER TABLE `category_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_roles_category_id_foreign` (`category_id`),
  ADD KEY `category_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `category_tags`
--
ALTER TABLE `category_tags`
  ADD KEY `category_tags_category_id_foreign` (`category_id`);

--
-- Indexes for table `category_translations`
--
ALTER TABLE `category_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_translations_name_index` (`name`),
  ADD KEY `category_translations_category_id_foreign` (`category_id`),
  ADD KEY `category_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `celebrities`
--
ALTER TABLE `celebrities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `celebrities_email_unique` (`email`),
  ADD KEY `celebrities_country_id_foreign` (`country_id`);

--
-- Indexes for table `celebrity_brands`
--
ALTER TABLE `celebrity_brands`
  ADD KEY `celebrity_brands_celebrity_id_foreign` (`celebrity_id`),
  ADD KEY `celebrity_brands_brand_id_foreign` (`brand_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_email_unique` (`email`),
  ADD UNIQUE KEY `clients_code_unique` (`code`),
  ADD KEY `clients_country_id_foreign` (`country_id`),
  ADD KEY `clients_language_id_foreign` (`language_id`),
  ADD KEY `clients_phone_number_index` (`phone_number`),
  ADD KEY `clients_custom_domain_index` (`custom_domain`),
  ADD KEY `clients_is_deleted_index` (`is_deleted`),
  ADD KEY `clients_is_blocked_index` (`is_blocked`),
  ADD KEY `clients_database_name_index` (`database_name`),
  ADD KEY `clients_company_name_index` (`company_name`),
  ADD KEY `clients_status_index` (`status`);

--
-- Indexes for table `client_countries`
--
ALTER TABLE `client_countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_countries_client_code_foreign` (`client_code`),
  ADD KEY `client_countries_country_id_foreign` (`country_id`);

--
-- Indexes for table `client_currencies`
--
ALTER TABLE `client_currencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_currencies_client_code_foreign` (`client_code`),
  ADD KEY `client_currencies_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `client_languages`
--
ALTER TABLE `client_languages`
  ADD KEY `client_languages_client_code_foreign` (`client_code`),
  ADD KEY `client_languages_language_id_foreign` (`language_id`);

--
-- Indexes for table `client_preferences`
--
ALTER TABLE `client_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_preferences_client_code_unique` (`client_code`),
  ADD KEY `client_preferences_fb_login_index` (`fb_login`),
  ADD KEY `client_preferences_twitter_login_index` (`twitter_login`),
  ADD KEY `client_preferences_google_login_index` (`google_login`),
  ADD KEY `client_preferences_apple_login_index` (`apple_login`),
  ADD KEY `client_preferences_verify_email_index` (`verify_email`),
  ADD KEY `client_preferences_verify_phone_index` (`verify_phone`),
  ADD KEY `client_preferences_currency_id_foreign` (`currency_id`),
  ADD KEY `client_preferences_language_id_foreign` (`language_id`),
  ADD KEY `client_preferences_map_provider_foreign` (`map_provider`),
  ADD KEY `client_preferences_sms_provider_foreign` (`sms_provider`),
  ADD KEY `client_preferences_web_template_id_foreign` (`web_template_id`),
  ADD KEY `client_preferences_app_template_id_foreign` (`app_template_id`),
  ADD KEY `client_preferences_is_hyperlocal_index` (`is_hyperlocal`),
  ADD KEY `client_preferences_need_delivery_service_index` (`need_delivery_service`),
  ADD KEY `client_preferences_mail_type_index` (`mail_type`),
  ADD KEY `client_preferences_celebrity_check_index` (`celebrity_check`),
  ADD KEY `client_preferences_need_laundry_service_index` (`need_laundry_service`);

--
-- Indexes for table `client_preference_additional`
--
ALTER TABLE `client_preference_additional`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_slots`
--
ALTER TABLE `client_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms`
--
ALTER TABLE `cms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cms_title_unique` (`title`),
  ADD KEY `cms_language_id_foreign` (`language_id`),
  ADD KEY `cms_title_index` (`title`);

--
-- Indexes for table `copy_tools`
--
ALTER TABLE `copy_tools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `countries_code_index` (`code`),
  ADD KEY `countries_name_index` (`name`);

--
-- Indexes for table `csv_customer_imports`
--
ALTER TABLE `csv_customer_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `csv_customer_imports_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `csv_product_imports`
--
ALTER TABLE `csv_product_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `csv_product_imports_vendor_id_foreign` (`vendor_id`),
  ADD KEY `csv_product_imports_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `csv_qrcode_imports`
--
ALTER TABLE `csv_qrcode_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `csv_qrcode_imports_vendor_id_foreign` (`vendor_id`),
  ADD KEY `csv_qrcode_imports_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `csv_vendor_imports`
--
ALTER TABLE `csv_vendor_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `csv_vendor_imports_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `currencies_name_index` (`name`),
  ADD KEY `currencies_priority_index` (`priority`),
  ADD KEY `currencies_iso_code_index` (`iso_code`),
  ADD KEY `currencies_iso_numeric_index` (`iso_numeric`);

--
-- Indexes for table `delivery_slots`
--
ALTER TABLE `delivery_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_slots_product`
--
ALTER TABLE `delivery_slots_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_slots_product_product_id_foreign` (`product_id`),
  ADD KEY `delivery_slots_product_delivery_slot_id_foreign` (`delivery_slot_id`);

--
-- Indexes for table `dispatcher_status_options`
--
ALTER TABLE `dispatcher_status_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dispatcher_template_type_options`
--
ALTER TABLE `dispatcher_template_type_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dispatcher_warning_pages`
--
ALTER TABLE `dispatcher_warning_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_registration_documents`
--
ALTER TABLE `driver_registration_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_registration_document_translations`
--
ALTER TABLE `driver_registration_document_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estimated_products`
--
ALTER TABLE `estimated_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimated_products_estimated_cart_id_foreign` (`estimated_cart_id`),
  ADD KEY `estimated_products_product_id_foreign` (`product_id`);

--
-- Indexes for table `estimated_product_addons`
--
ALTER TABLE `estimated_product_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimated_product_addons_estimated_product_id_foreign` (`estimated_product_id`),
  ADD KEY `estimated_product_addons_estimated_addon_id_foreign` (`estimated_addon_id`),
  ADD KEY `estimated_product_addons_estimated_addon_option_id_foreign` (`estimated_addon_option_id`);

--
-- Indexes for table `estimated_product_addon_new`
--
ALTER TABLE `estimated_product_addon_new`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimated_product_addon_new_estimated_product_id_foreign` (`estimated_product_id`),
  ADD KEY `estimated_product_addon_new_estimated_addon_id_foreign` (`estimated_addon_id`),
  ADD KEY `estimated_product_addon_new_estimated_addon_option_id_foreign` (`estimated_addon_option_id`);

--
-- Indexes for table `estimated_product_carts`
--
ALTER TABLE `estimated_product_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimated_product_carts_user_id_foreign` (`user_id`),
  ADD KEY `estimated_product_carts_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `estimated_product_cart_new`
--
ALTER TABLE `estimated_product_cart_new`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estimated_product_new`
--
ALTER TABLE `estimated_product_new`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimated_product_new_product_id_foreign` (`product_id`);

--
-- Indexes for table `estimate_addon_options`
--
ALTER TABLE `estimate_addon_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimate_addon_options_estimate_addon_id_foreign` (`estimate_addon_id`);

--
-- Indexes for table `estimate_addon_option_translations`
--
ALTER TABLE `estimate_addon_option_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimate_addon_option_translations_estimate_addon_opt_id_foreign` (`estimate_addon_opt_id`),
  ADD KEY `estimate_addon_option_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `estimate_addon_sets`
--
ALTER TABLE `estimate_addon_sets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estimate_addon_set_translations`
--
ALTER TABLE `estimate_addon_set_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimate_addon_set_translations_estimate_addon_id_foreign` (`estimate_addon_id`),
  ADD KEY `estimate_addon_set_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `estimate_products`
--
ALTER TABLE `estimate_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimate_products_category_id_foreign` (`category_id`);

--
-- Indexes for table `estimate_product_addons`
--
ALTER TABLE `estimate_product_addons`
  ADD KEY `estimate_product_addons_estimate_product_id_foreign` (`estimate_product_id`),
  ADD KEY `estimate_product_addons_estimate_addon_id_foreign` (`estimate_addon_id`);

--
-- Indexes for table `estimate_product_translations`
--
ALTER TABLE `estimate_product_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estimate_product_translations_estimate_product_id_foreign` (`estimate_product_id`);

--
-- Indexes for table `exchange_reasons`
--
ALTER TABLE `exchange_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facilties`
--
ALTER TABLE `facilties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facilty_translations`
--
ALTER TABLE `facilty_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq_translations`
--
ALTER TABLE `faq_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faq_translations_page_id_foreign` (`page_id`);

--
-- Indexes for table `gift_cards`
--
ALTER TABLE `gift_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gift_cards_added_by_foreign` (`added_by`);

--
-- Indexes for table `home_page_labels`
--
ALTER TABLE `home_page_labels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_page_label_transaltions`
--
ALTER TABLE `home_page_label_transaltions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `home_page_label_transaltions_home_page_label_id_foreign` (`home_page_label_id`),
  ADD KEY `home_page_label_transaltions_language_id_foreign` (`language_id`);

--
-- Indexes for table `home_products`
--
ALTER TABLE `home_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `influencer_categories`
--
ALTER TABLE `influencer_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `influencer_initial_orders_discounts`
--
ALTER TABLE `influencer_initial_orders_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influencer_initial_orders_discounts_influencer_user_id_foreign` (`influencer_user_id`);

--
-- Indexes for table `influencer_kyc`
--
ALTER TABLE `influencer_kyc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influencer_kyc_user_id_foreign` (`user_id`);

--
-- Indexes for table `influencer_social_account_details`
--
ALTER TABLE `influencer_social_account_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influencer_social_account_details_user_id_foreign` (`user_id`);

--
-- Indexes for table `influencer_tiers`
--
ALTER TABLE `influencer_tiers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `influencer_users`
--
ALTER TABLE `influencer_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influencer_users_influencer_tier_id_foreign` (`influencer_tier_id`),
  ADD KEY `influencer_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `influ_attributes`
--
ALTER TABLE `influ_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influ_attributes_type_index` (`type`),
  ADD KEY `influ_attributes_position_index` (`position`),
  ADD KEY `influ_attributes_status_index` (`status`);

--
-- Indexes for table `influ_attr_cat`
--
ALTER TABLE `influ_attr_cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influ_attr_cat_attribute_id_foreign` (`attribute_id`),
  ADD KEY `influ_attr_cat_category_id_foreign` (`category_id`);

--
-- Indexes for table `influ_attr_opt`
--
ALTER TABLE `influ_attr_opt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influ_attr_opt_position_index` (`position`),
  ADD KEY `influ_attr_opt_attribute_id_foreign` (`attribute_id`);

--
-- Indexes for table `influ_attr_opt_trans`
--
ALTER TABLE `influ_attr_opt_trans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influ_attr_opt_trans_attribute_option_id_foreign` (`attribute_option_id`),
  ADD KEY `influ_attr_opt_trans_language_id_foreign` (`language_id`);

--
-- Indexes for table `influ_attr_trans`
--
ALTER TABLE `influ_attr_trans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `influ_attr_trans_attribute_id_foreign` (`attribute_id`),
  ADD KEY `influ_attr_trans_language_id_foreign` (`language_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `languages_sort_code_unique` (`sort_code`),
  ADD UNIQUE KEY `languages_name_unique` (`name`);

--
-- Indexes for table `long_term_services`
--
ALTER TABLE `long_term_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `long_term_services_sku_unique` (`sku`);

--
-- Indexes for table `long_term_service_periods`
--
ALTER TABLE `long_term_service_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `long_term_service_products`
--
ALTER TABLE `long_term_service_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `long_term_service_product_addons`
--
ALTER TABLE `long_term_service_product_addons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `long_term_service_translations`
--
ALTER TABLE `long_term_service_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_cards`
--
ALTER TABLE `loyalty_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `luxury_options`
--
ALTER TABLE `luxury_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `main_permissions`
--
ALTER TABLE `main_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `main_permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `main_roles`
--
ALTER TABLE `main_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `main_roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `map_providers`
--
ALTER TABLE `map_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marg_products`
--
ALTER TABLE `marg_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marg_products_product_id_foreign` (`product_id`),
  ADD KEY `marg_products_productcode_index` (`ProductCode`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_banners`
--
ALTER TABLE `mobile_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile_banners_redirect_category_id_foreign` (`redirect_category_id`),
  ADD KEY `mobile_banners_redirect_vendor_id_foreign` (`redirect_vendor_id`),
  ADD KEY `mobile_banners_name_index` (`name`),
  ADD KEY `mobile_banners_status_index` (`status`),
  ADD KEY `mobile_banners_start_date_time_index` (`start_date_time`),
  ADD KEY `mobile_banners_end_date_time_index` (`end_date_time`);

--
-- Indexes for table `mobile_banner_service_areas`
--
ALTER TABLE `mobile_banner_service_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile_banner_service_areas_service_area_id_foreign` (`service_area_id`);

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
-- Indexes for table `nikolag_customers`
--
ALTER TABLE `nikolag_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nikolag_customers_email_unique` (`email`),
  ADD UNIQUE KEY `pstype_psid` (`payment_service_type`,`payment_service_id`),
  ADD KEY `nikolag_customers_email_index` (`email`);

--
-- Indexes for table `nikolag_customer_user`
--
ALTER TABLE `nikolag_customer_user`
  ADD UNIQUE KEY `oid_cid` (`owner_id`,`customer_id`);

--
-- Indexes for table `nikolag_deductibles`
--
ALTER TABLE `nikolag_deductibles`
  ADD KEY `nikolag_deductibles_index` (`deductible_type`,`deductible_id`),
  ADD KEY `nikolag_featurables_index` (`featurable_type`,`featurable_id`);

--
-- Indexes for table `nikolag_discounts`
--
ALTER TABLE `nikolag_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nikolag_discounts_name_index` (`name`);

--
-- Indexes for table `nikolag_orders`
--
ALTER TABLE `nikolag_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nikolag_products`
--
ALTER TABLE `nikolag_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vname_name` (`name`,`variation_name`),
  ADD KEY `nikolag_products_name_index` (`name`),
  ADD KEY `nikolag_products_reference_id_index` (`reference_id`);

--
-- Indexes for table `nikolag_product_order`
--
ALTER TABLE `nikolag_product_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prodid_ordid` (`product_id`,`order_id`);

--
-- Indexes for table `nikolag_taxes`
--
ALTER TABLE `nikolag_taxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_type` (`name`,`type`),
  ADD KEY `nikolag_taxes_name_index` (`name`),
  ADD KEY `nikolag_taxes_type_index` (`type`);

--
-- Indexes for table `nikolag_transactions`
--
ALTER TABLE `nikolag_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nikolag_transactions_status_index` (`status`),
  ADD KEY `nikolag_transactions_payment_service_type_index` (`payment_service_type`),
  ADD KEY `cus_id` (`customer_id`);

--
-- Indexes for table `nomenclatures`
--
ALTER TABLE `nomenclatures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nomenclatures_translations`
--
ALTER TABLE `nomenclatures_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_types`
--
ALTER TABLE `notification_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_types_name_index` (`name`);

--
-- Indexes for table `onboard_settings`
--
ALTER TABLE `onboard_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_address_id_foreign` (`address_id`),
  ADD KEY `orders_tax_category_id_foreign` (`tax_category_id`),
  ADD KEY `orders_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `order_cancel_requests`
--
ALTER TABLE `order_cancel_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_cancel_requests_return_reason_id_foreign` (`return_reason_id`),
  ADD KEY `order_cancel_requests_order_vendor_product_id_foreign` (`order_vendor_product_id`);

--
-- Indexes for table `order_delivery_status_icon`
--
ALTER TABLE `order_delivery_status_icon`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_driver_ratings`
--
ALTER TABLE `order_driver_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_driver_ratings_order_id_foreign` (`order_id`);

--
-- Indexes for table `order_files`
--
ALTER TABLE `order_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_files_order_id_index` (`order_id`),
  ADD KEY `order_files_cart_id_index` (`cart_id`);

--
-- Indexes for table `order_locations`
--
ALTER TABLE `order_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_long_term_services`
--
ALTER TABLE `order_long_term_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_long_term_services_addons`
--
ALTER TABLE `order_long_term_services_addons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_long_term_service_schedules`
--
ALTER TABLE `order_long_term_service_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_notifications_logs`
--
ALTER TABLE `order_notifications_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_product_addons`
--
ALTER TABLE `order_product_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_product_addons_order_product_id_foreign` (`order_product_id`),
  ADD KEY `order_product_addons_addon_id_foreign` (`addon_id`),
  ADD KEY `order_product_addons_option_id_foreign` (`option_id`);

--
-- Indexes for table `order_product_dispatch_return_routes`
--
ALTER TABLE `order_product_dispatch_return_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_product_dispatch_routes`
--
ALTER TABLE `order_product_dispatch_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_product_prescriptions`
--
ALTER TABLE `order_product_prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_product_prescriptions_order_id_foreign` (`order_id`),
  ADD KEY `order_product_prescriptions_product_id_foreign` (`product_id`),
  ADD KEY `order_product_prescriptions_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `order_product_ratings`
--
ALTER TABLE `order_product_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_product_ratings_order_id_foreign` (`order_id`),
  ADD KEY `order_product_ratings_order_vendor_product_id_foreign` (`order_vendor_product_id`);

--
-- Indexes for table `order_product_rating_files`
--
ALTER TABLE `order_product_rating_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_product_rating_files_order_product_rating_id_foreign` (`order_product_rating_id`);

--
-- Indexes for table `order_qrcode_links`
--
ALTER TABLE `order_qrcode_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_refunds`
--
ALTER TABLE `order_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_refunds_user_id_foreign` (`user_id`),
  ADD KEY `order_refunds_order_id_foreign` (`order_id`),
  ADD KEY `order_refunds_payment_option_id_foreign` (`payment_option_id`);

--
-- Indexes for table `order_return_requests`
--
ALTER TABLE `order_return_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_return_requests_order_vendor_product_id_foreign` (`order_vendor_product_id`),
  ADD KEY `order_return_requests_order_id_foreign` (`order_id`),
  ADD KEY `order_return_requests_return_by_foreign` (`return_by`);

--
-- Indexes for table `order_return_request_files`
--
ALTER TABLE `order_return_request_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_return_request_files_order_return_request_id_foreign` (`order_return_request_id`);

--
-- Indexes for table `order_status_options`
--
ALTER TABLE `order_status_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_taxes`
--
ALTER TABLE `order_taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_vendors`
--
ALTER TABLE `order_vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_vendors_order_id_foreign` (`order_id`),
  ADD KEY `order_vendors_vendor_id_foreign` (`vendor_id`),
  ADD KEY `order_vendors_return_reason_id_foreign` (`return_reason_id`),
  ADD KEY `order_vendors_exchange_order_vendor_id_foreign` (`exchange_order_vendor_id`);

--
-- Indexes for table `order_vendor_accounting`
--
ALTER TABLE `order_vendor_accounting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_vendor_products`
--
ALTER TABLE `order_vendor_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_products_order_id_foreign` (`order_id`),
  ADD KEY `order_products_product_id_foreign` (`product_id`),
  ADD KEY `order_products_tax_category_id_foreign` (`tax_category_id`),
  ADD KEY `order_products_vendor_id_foreign` (`vendor_id`),
  ADD KEY `order_products_variant_id_foreign` (`variant_id`),
  ADD KEY `order_vendor_products_category_id_foreign` (`category_id`),
  ADD KEY `order_vendor_products_slot_id_foreign` (`slot_id`),
  ADD KEY `order_vendor_products_order_vendor_status_option_id_foreign` (`order_vendor_status_option_id`);

--
-- Indexes for table `order_vendor_reports`
--
ALTER TABLE `order_vendor_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_vendor_reports_order_id_foreign` (`order_id`),
  ADD KEY `order_vendor_reports_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_translations`
--
ALTER TABLE `page_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_translations_page_id_foreign` (`page_id`),
  ADD KEY `page_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_cart_id_foreign` (`cart_id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_options`
--
ALTER TABLE `payment_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payout_options`
--
ALTER TABLE `payout_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pick_drop_driver_bids`
--
ALTER TABLE `pick_drop_driver_bids`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pincodes`
--
ALTER TABLE `pincodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pincodes_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `pincode_delivery_options`
--
ALTER TABLE `pincode_delivery_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pincode_delivery_options_pincode_id_foreign` (`pincode_id`);

--
-- Indexes for table `processor_products`
--
ALTER TABLE `processor_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `processor_products_product_id_foreign` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_vendor_id_foreign` (`vendor_id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_type_id_foreign` (`type_id`),
  ADD KEY `products_country_origin_id_foreign` (`country_origin_id`),
  ADD KEY `products_is_new_index` (`is_new`),
  ADD KEY `products_is_featured_index` (`is_featured`),
  ADD KEY `products_is_live_index` (`is_live`),
  ADD KEY `products_is_physical_index` (`is_physical`),
  ADD KEY `products_has_inventory_index` (`has_inventory`),
  ADD KEY `products_sell_when_out_of_stock_index` (`sell_when_out_of_stock`),
  ADD KEY `products_requires_shipping_index` (`requires_shipping`),
  ADD KEY `products_requires_last_mile_index` (`Requires_last_mile`),
  ADD KEY `products_averagerating_index` (`averageRating`),
  ADD KEY `products_brand_id_foreign` (`brand_id`),
  ADD KEY `products_tax_category_id_foreign` (`tax_category_id`);

--
-- Indexes for table `product_addons`
--
ALTER TABLE `product_addons`
  ADD KEY `product_addons_product_id_foreign` (`product_id`),
  ADD KEY `product_addons_addon_id_foreign` (`addon_id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_attributes_product_id_foreign` (`product_id`),
  ADD KEY `product_attributes_attribute_id_foreign` (`attribute_id`),
  ADD KEY `product_attributes_attribute_option_id_foreign` (`attribute_option_id`);

--
-- Indexes for table `product_bookings`
--
ALTER TABLE `product_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_bookings_order_vendor_product_id_foreign` (`order_vendor_product_id`),
  ADD KEY `product_bookings_order_id_foreign` (`order_id`);

--
-- Indexes for table `product_by_roles`
--
ALTER TABLE `product_by_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_by_roles_product_id_foreign` (`product_id`),
  ADD KEY `product_by_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD KEY `product_categories_product_id_foreign` (`product_id`),
  ADD KEY `product_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_celebrities`
--
ALTER TABLE `product_celebrities`
  ADD KEY `product_celebrities_celebrity_id_foreign` (`celebrity_id`),
  ADD KEY `product_celebrities_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_cross_sells`
--
ALTER TABLE `product_cross_sells`
  ADD KEY `product_cross_sells_product_id_foreign` (`product_id`),
  ADD KEY `product_cross_sells_cross_product_id_foreign` (`cross_product_id`);

--
-- Indexes for table `product_delivery_fee_by_roles`
--
ALTER TABLE `product_delivery_fee_by_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_delivery_fee_by_roles_product_id_foreign` (`product_id`),
  ADD KEY `product_delivery_fee_by_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `product_faqs`
--
ALTER TABLE `product_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_faqs_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_faq_select_options`
--
ALTER TABLE `product_faq_select_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_faq_select_option_translations`
--
ALTER TABLE `product_faq_select_option_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_faq_translations`
--
ALTER TABLE `product_faq_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`),
  ADD KEY `product_images_media_id_foreign` (`media_id`);

--
-- Indexes for table `product_inquiries`
--
ALTER TABLE `product_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_inquiries_product_id_foreign` (`product_id`),
  ADD KEY `product_inquiries_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `product_recently_viewed`
--
ALTER TABLE `product_recently_viewed`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_related`
--
ALTER TABLE `product_related`
  ADD KEY `product_related_product_id_foreign` (`product_id`),
  ADD KEY `product_related_related_product_id_foreign` (`related_product_id`);

--
-- Indexes for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD KEY `product_tags_product_id_foreign` (`product_id`),
  ADD KEY `product_tags_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `product_translations`
--
ALTER TABLE `product_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_translations_product_id_foreign` (`product_id`),
  ADD KEY `product_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `product_up_sells`
--
ALTER TABLE `product_up_sells`
  ADD KEY `product_up_sells_product_id_foreign` (`product_id`),
  ADD KEY `product_up_sells_upsell_product_id_foreign` (`upsell_product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`),
  ADD KEY `product_variants_tax_category_id_foreign` (`tax_category_id`),
  ADD KEY `product_variants_sku_index` (`sku`),
  ADD KEY `product_variants_quantity_index` (`quantity`),
  ADD KEY `product_variants_price_index` (`price`),
  ADD KEY `product_variants_compare_at_price_index` (`compare_at_price`),
  ADD KEY `product_variants_cost_price_index` (`cost_price`);

--
-- Indexes for table `product_variant_by_roles`
--
ALTER TABLE `product_variant_by_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variant_by_roles_product_id_foreign` (`product_id`),
  ADD KEY `product_variant_by_roles_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `product_variant_by_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `product_variant_images`
--
ALTER TABLE `product_variant_images`
  ADD KEY `product_variant_images_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `product_variant_images_product_image_id_foreign` (`product_image_id`);

--
-- Indexes for table `product_variant_sets`
--
ALTER TABLE `product_variant_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variant_sets_product_id_foreign` (`product_id`),
  ADD KEY `product_variant_sets_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `product_variant_sets_variant_type_id_foreign` (`variant_type_id`),
  ADD KEY `product_variant_sets_variant_option_id_foreign` (`variant_option_id`);

--
-- Indexes for table `promocodes`
--
ALTER TABLE `promocodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promocodes_promo_type_id_foreign` (`promo_type_id`),
  ADD KEY `promocodes_created_by_foreign` (`created_by`),
  ADD KEY `promocodes_allow_free_delivery_index` (`allow_free_delivery`),
  ADD KEY `promocodes_minimum_spend_index` (`minimum_spend`),
  ADD KEY `promocodes_maximum_spend_index` (`maximum_spend`),
  ADD KEY `promocodes_first_order_only_index` (`first_order_only`),
  ADD KEY `promocodes_limit_per_user_index` (`limit_per_user`),
  ADD KEY `promocodes_limit_total_index` (`limit_total`),
  ADD KEY `promocodes_paid_by_vendor_admin_index` (`paid_by_vendor_admin`),
  ADD KEY `promocodes_is_deleted_index` (`is_deleted`);

--
-- Indexes for table `promocode_details`
--
ALTER TABLE `promocode_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promocode_restrictions`
--
ALTER TABLE `promocode_restrictions`
  ADD KEY `promocode_restrictions_is_included_index` (`is_included`),
  ADD KEY `promocode_restrictions_is_excluded_index` (`is_excluded`),
  ADD KEY `promocode_restrictions_promocode_id_foreign` (`promocode_id`);

--
-- Indexes for table `promo_types`
--
ALTER TABLE `promo_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promo_usages`
--
ALTER TABLE `promo_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promo_usages_promocode_id_foreign` (`promocode_id`),
  ADD KEY `promo_usages_user_id_foreign` (`user_id`);

--
-- Indexes for table `qrcode_imports`
--
ALTER TABLE `qrcode_imports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refer_and_earns`
--
ALTER TABLE `refer_and_earns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refer_and_earns_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `refer_and_earn_details`
--
ALTER TABLE `refer_and_earn_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refer_and_earn_details_user_id_foreign` (`user_id`),
  ADD KEY `refer_and_earn_details_attribute_id_foreign` (`attribute_id`),
  ADD KEY `refer_and_earn_details_attribute_option_id_foreign` (`attribute_option_id`),
  ADD KEY `refer_and_earn_details_influencer_user_id_foreign` (`influencer_user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reschedule_orders`
--
ALTER TABLE `reschedule_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reschedule_orders_reschedule_by_foreign` (`reschedule_by`),
  ADD KEY `reschedule_orders_order_id_foreign` (`order_id`),
  ADD KEY `reschedule_orders_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `return_reasons`
--
ALTER TABLE `return_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riders_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `service_areas`
--
ALTER TABLE `service_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_areas_vendor_id_foreign` (`vendor_id`),
  ADD KEY `service_areas_name_index` (`name`);

--
-- Indexes for table `service_area_for_banners`
--
ALTER TABLE `service_area_for_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_area_for_banners_name_index` (`name`);

--
-- Indexes for table `shipping_options`
--
ALTER TABLE `shipping_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shippo_delivery_options`
--
ALTER TABLE `shippo_delivery_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `show_subscription_plan_on_signups`
--
ALTER TABLE `show_subscription_plan_on_signups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `slot_days`
--
ALTER TABLE `slot_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slot_days_slot_id_foreign` (`slot_id`),
  ADD KEY `slot_days_day_index` (`day`);

--
-- Indexes for table `sms_providers`
--
ALTER TABLE `sms_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_credentials`
--
ALTER TABLE `social_credentials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_media`
--
ALTER TABLE `social_media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `square_timestamp`
--
ALTER TABLE `square_timestamp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `static_dropoff_locations`
--
ALTER TABLE `static_dropoff_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_features_list_user`
--
ALTER TABLE `subscription_features_list_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_features_list_vendor`
--
ALTER TABLE `subscription_features_list_vendor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_invoices_user`
--
ALTER TABLE `subscription_invoices_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_invoices_user_user_id_index` (`user_id`),
  ADD KEY `subscription_invoices_user_status_id_index` (`status_id`),
  ADD KEY `subscription_invoices_user_subscription_id_index` (`subscription_id`),
  ADD KEY `subscription_invoices_user_payment_option_id_index` (`payment_option_id`);

--
-- Indexes for table `subscription_invoices_vendor`
--
ALTER TABLE `subscription_invoices_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_invoices_vendor_vendor_id_index` (`vendor_id`),
  ADD KEY `subscription_invoices_vendor_status_id_index` (`status_id`),
  ADD KEY `subscription_invoices_vendor_subscription_id_index` (`subscription_id`),
  ADD KEY `subscription_invoices_vendor_payment_option_id_index` (`payment_option_id`);

--
-- Indexes for table `subscription_invoice_features_user`
--
ALTER TABLE `subscription_invoice_features_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_invoice_features_user_subscription_invoice_id_index` (`subscription_invoice_id`),
  ADD KEY `subscription_invoice_features_user_feature_id_index` (`feature_id`);

--
-- Indexes for table `subscription_invoice_features_vendor`
--
ALTER TABLE `subscription_invoice_features_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_subscription_invoice_id_index` (`subscription_invoice_id`),
  ADD KEY `subscription_invoice_features_vendor_feature_id_index` (`feature_id`);

--
-- Indexes for table `subscription_log_user`
--
ALTER TABLE `subscription_log_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plans_user`
--
ALTER TABLE `subscription_plans_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plans_vendor`
--
ALTER TABLE `subscription_plans_vendor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plan_features_user`
--
ALTER TABLE `subscription_plan_features_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_plan_features_user_subscription_plan_id_foreign` (`subscription_plan_id`),
  ADD KEY `subscription_plan_features_user_feature_id_foreign` (`feature_id`);

--
-- Indexes for table `subscription_plan_features_vendor`
--
ALTER TABLE `subscription_plan_features_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_plan_features_vendor_subscription_plan_id_foreign` (`subscription_plan_id`),
  ADD KEY `subscription_plan_features_vendor_feature_id_foreign` (`feature_id`);

--
-- Indexes for table `subscription_status_options`
--
ALTER TABLE `subscription_status_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tag_translations`
--
ALTER TABLE `tag_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tag_translations_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `tax_categories`
--
ALTER TABLE `tax_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_categories_vendor_id_foreign` (`vendor_id`),
  ADD KEY `tax_categories_code_index` (`code`),
  ADD KEY `tax_categories_is_core_index` (`is_core`);

--
-- Indexes for table `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_rates_is_zip_index` (`is_zip`);

--
-- Indexes for table `tax_rate_categories`
--
ALTER TABLE `tax_rate_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_rate_categories_tax_cate_id_foreign` (`tax_cate_id`),
  ADD KEY `tax_rate_categories_tax_rate_id_foreign` (`tax_rate_id`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_carts`
--
ALTER TABLE `temp_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temp_carts_user_id_foreign` (`user_id`),
  ADD KEY `temp_carts_created_by_foreign` (`created_by`),
  ADD KEY `temp_carts_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `temp_cart_addons`
--
ALTER TABLE `temp_cart_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temp_cart_addons_cart_product_id_foreign` (`cart_product_id`),
  ADD KEY `temp_cart_addons_addon_id_foreign` (`addon_id`),
  ADD KEY `temp_cart_addons_option_id_foreign` (`option_id`);

--
-- Indexes for table `temp_cart_coupons`
--
ALTER TABLE `temp_cart_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temp_cart_coupons_cart_id_foreign` (`cart_id`),
  ADD KEY `temp_cart_coupons_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `temp_cart_products`
--
ALTER TABLE `temp_cart_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temp_cart_products_cart_id_foreign` (`cart_id`),
  ADD KEY `temp_cart_products_product_id_foreign` (`product_id`),
  ADD KEY `temp_cart_products_vendor_id_foreign` (`vendor_id`),
  ADD KEY `temp_cart_products_created_by_foreign` (`created_by`),
  ADD KEY `temp_cart_products_variant_id_foreign` (`variant_id`),
  ADD KEY `temp_cart_products_tax_rate_id_foreign` (`tax_rate_id`),
  ADD KEY `temp_cart_products_tax_category_id_foreign` (`tax_category_id`),
  ADD KEY `temp_cart_products_status_index` (`status`),
  ADD KEY `temp_cart_products_is_tax_applied_index` (`is_tax_applied`);

--
-- Indexes for table `temp_cart_vendor_delivery_fee`
--
ALTER TABLE `temp_cart_vendor_delivery_fee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temp_cart_vendor_delivery_fee_cart_id_foreign` (`cart_id`),
  ADD KEY `temp_cart_vendor_delivery_fee_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `terminologies`
--
ALTER TABLE `terminologies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `third_party_accounting`
--
ALTER TABLE `third_party_accounting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timezones`
--
ALTER TABLE `timezones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `toll_pass_origin`
--
ALTER TABLE `toll_pass_origin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_uuid_unique` (`uuid`),
  ADD KEY `transactions_payable_type_payable_id_index` (`payable_type`,`payable_id`),
  ADD KEY `payable_type_ind` (`payable_type`,`payable_id`,`type`),
  ADD KEY `payable_confirmed_ind` (`payable_type`,`payable_id`,`confirmed`),
  ADD KEY `payable_type_confirmed_ind` (`payable_type`,`payable_id`,`type`,`confirmed`),
  ADD KEY `transactions_type_index` (`type`),
  ADD KEY `transactions_wallet_id_foreign` (`wallet_id`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transfers_uuid_unique` (`uuid`),
  ADD KEY `transfers_from_type_from_id_index` (`from_type`,`from_id`),
  ADD KEY `transfers_to_type_to_id_index` (`to_type`,`to_id`),
  ADD KEY `transfers_deposit_id_foreign` (`deposit_id`),
  ADD KEY `transfers_withdraw_id_foreign` (`withdraw_id`);

--
-- Indexes for table `travel_mode`
--
ALTER TABLE `travel_mode`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_country_id_foreign` (`country_id`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_phone_number_index` (`phone_number`),
  ADD KEY `users_type_index` (`type`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_facebook_auth_id_index` (`facebook_auth_id`),
  ADD KEY `users_twitter_auth_id_index` (`twitter_auth_id`),
  ADD KEY `users_google_auth_id_index` (`google_auth_id`),
  ADD KEY `users_apple_auth_id_index` (`apple_auth_id`),
  ADD KEY `users_timezone_id_foreign` (`timezone_id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_addresses_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_bid_ride_requests`
--
ALTER TABLE `user_bid_ride_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_bid_ride_requests_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_data_vault`
--
ALTER TABLE `user_data_vault`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_devices_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_docs`
--
ALTER TABLE `user_docs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_gift_cards`
--
ALTER TABLE `user_gift_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_gift_cards_user_id_foreign` (`user_id`),
  ADD KEY `user_gift_cards_gift_card_id_foreign` (`gift_card_id`);

--
-- Indexes for table `user_loyalty_points`
--
ALTER TABLE `user_loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_loyalty_points_points_index` (`points`),
  ADD KEY `user_loyalty_points_user_id_foreign` (`user_id`),
  ADD KEY `user_loyalty_points_loyalty_card_id_foreign` (`loyalty_card_id`);

--
-- Indexes for table `user_loyalty_point_histories`
--
ALTER TABLE `user_loyalty_point_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_payment_cards`
--
ALTER TABLE `user_payment_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_ratings_order_id_index` (`order_id`),
  ADD KEY `user_ratings_order_vendor_id_index` (`order_vendor_id`),
  ADD KEY `user_ratings_order_vendor_product_id_index` (`order_vendor_product_id`);

--
-- Indexes for table `user_refferals`
--
ALTER TABLE `user_refferals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_refferals_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_registration_documents`
--
ALTER TABLE `user_registration_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_registration_document_translations`
--
ALTER TABLE `user_registration_document_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_registration_select_options`
--
ALTER TABLE `user_registration_select_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_registration_select_option_translations`
--
ALTER TABLE `user_registration_select_option_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_saved_payment_methods`
--
ALTER TABLE `user_saved_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_saved_payment_methods_user_id_index` (`user_id`),
  ADD KEY `user_saved_payment_methods_payment_option_id_index` (`payment_option_id`);

--
-- Indexes for table `user_vendors`
--
ALTER TABLE `user_vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_vendors_vendor_id_foreign` (`vendor_id`),
  ADD KEY `user_vendors_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_verification`
--
ALTER TABLE `user_verification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_verification_resources`
--
ALTER TABLE `user_verification_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_wishlists`
--
ALTER TABLE `user_wishlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_wishlists_user_id_foreign` (`user_id`),
  ADD KEY `user_wishlists_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `variants`
--
ALTER TABLE `variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variants_type_index` (`type`),
  ADD KEY `variants_position_index` (`position`),
  ADD KEY `variants_status_index` (`status`);

--
-- Indexes for table `variant_categories`
--
ALTER TABLE `variant_categories`
  ADD KEY `variant_categories_variant_id_foreign` (`variant_id`),
  ADD KEY `variant_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `variant_options`
--
ALTER TABLE `variant_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_options_position_index` (`position`),
  ADD KEY `variant_options_variant_id_foreign` (`variant_id`);

--
-- Indexes for table `variant_option_translations`
--
ALTER TABLE `variant_option_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_option_translations_variant_option_id_foreign` (`variant_option_id`),
  ADD KEY `variant_option_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `variant_translations`
--
ALTER TABLE `variant_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_translations_variant_id_foreign` (`variant_id`),
  ADD KEY `variant_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `vehicle_emission_type`
--
ALTER TABLE `vehicle_emission_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendors_name_index` (`name`),
  ADD KEY `vendors_order_min_amount_index` (`order_min_amount`),
  ADD KEY `vendors_order_pre_time_index` (`order_pre_time`),
  ADD KEY `vendors_auto_reject_time_index` (`auto_reject_time`),
  ADD KEY `vendors_commission_percent_index` (`commission_percent`),
  ADD KEY `vendors_commission_fixed_per_order_index` (`commission_fixed_per_order`),
  ADD KEY `vendors_commission_monthly_index` (`commission_monthly`),
  ADD KEY `vendors_dine_in_index` (`dine_in`),
  ADD KEY `vendors_takeaway_index` (`takeaway`),
  ADD KEY `vendors_delivery_index` (`delivery`),
  ADD KEY `vendors_add_category_index` (`add_category`),
  ADD KEY `vendors_vendor_templete_id_foreign` (`vendor_templete_id`),
  ADD KEY `vendors_rental_pick_drop_on_demand_laundry_index` (`rental`,`pick_drop`,`on_demand`,`laundry`),
  ADD KEY `vendors_appointment_index` (`appointment`);

--
-- Indexes for table `vendor_additional_info`
--
ALTER TABLE `vendor_additional_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_additional_info_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `vendor_categories`
--
ALTER TABLE `vendor_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_categories_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `vendor_cities`
--
ALTER TABLE `vendor_cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_city_translations`
--
ALTER TABLE `vendor_city_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_connected_accounts`
--
ALTER TABLE `vendor_connected_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_dinein_categories`
--
ALTER TABLE `vendor_dinein_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_dinein_categories_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `vendor_dinein_category_translations`
--
ALTER TABLE `vendor_dinein_category_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_dinein_category_translations_category_id_foreign` (`category_id`),
  ADD KEY `vendor_dinein_category_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `vendor_dinein_tables`
--
ALTER TABLE `vendor_dinein_tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_dinein_tables_vendor_dinein_category_id_foreign` (`vendor_dinein_category_id`),
  ADD KEY `vendor_dinein_tables_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `vendor_dinein_table_translations`
--
ALTER TABLE `vendor_dinein_table_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_dinein_table_translations_vendor_dinein_table_id_foreign` (`vendor_dinein_table_id`),
  ADD KEY `vendor_dinein_table_translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `vendor_docs`
--
ALTER TABLE `vendor_docs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_facilties`
--
ALTER TABLE `vendor_facilties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_media`
--
ALTER TABLE `vendor_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_media_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_media_media_type_index` (`media_type`);

--
-- Indexes for table `vendor_min_amounts`
--
ALTER TABLE `vendor_min_amounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_min_amounts_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_min_amounts_role_id_foreign` (`role_id`);

--
-- Indexes for table `vendor_multi_banners`
--
ALTER TABLE `vendor_multi_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_order_cancel_return_payments`
--
ALTER TABLE `vendor_order_cancel_return_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_order_dispatcher_statuses`
--
ALTER TABLE `vendor_order_dispatcher_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispatcher_statuses_order_id_foreign` (`order_id`),
  ADD KEY `dispatcher_statuses_dispatcher_status_option_id_foreign` (`dispatcher_status_option_id`),
  ADD KEY `vendor_order_dispatcher_statuses_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `vendor_order_product_dispatcher_statuses`
--
ALTER TABLE `vendor_order_product_dispatcher_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_order_product_dispatcher_statuses_order_id_foreign` (`order_id`);

--
-- Indexes for table `vendor_order_product_statuses`
--
ALTER TABLE `vendor_order_product_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_order_product_statuses_product_id_foreign` (`product_id`),
  ADD KEY `vendor_order_product_statuses_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_order_product_statuses_order_id_foreign` (`order_id`),
  ADD KEY `vendor_order_product_statuses_order_status_option_id_foreign` (`order_status_option_id`),
  ADD KEY `vendor_order_product_statuses_order_vendor_product_id_foreign` (`order_vendor_product_id`);

--
-- Indexes for table `vendor_order_statuses`
--
ALTER TABLE `vendor_order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_statuses_order_id_foreign` (`order_id`),
  ADD KEY `order_statuses_order_status_option_id_foreign` (`order_status_option_id`),
  ADD KEY `vendor_order_statuses_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `vendor_payouts`
--
ALTER TABLE `vendor_payouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_registration_documents`
--
ALTER TABLE `vendor_registration_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_registration_document_translations`
--
ALTER TABLE `vendor_registration_document_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_registration_select_options`
--
ALTER TABLE `vendor_registration_select_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_registration_select_option_translations`
--
ALTER TABLE `vendor_registration_select_option_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_saved_payment_methods`
--
ALTER TABLE `vendor_saved_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_saved_payment_methods_vendor_id_index` (`vendor_id`),
  ADD KEY `vendor_saved_payment_methods_payment_option_id_index` (`payment_option_id`);

--
-- Indexes for table `vendor_sections`
--
ALTER TABLE `vendor_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_section_heading_translations`
--
ALTER TABLE `vendor_section_heading_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_section_translations`
--
ALTER TABLE `vendor_section_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_slots`
--
ALTER TABLE `vendor_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_slots_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_slots_category_id_foreign` (`category_id`),
  ADD KEY `vendor_slots_start_time_index` (`start_time`),
  ADD KEY `vendor_slots_end_time_index` (`end_time`),
  ADD KEY `vendor_slots_dine_in_index` (`dine_in`),
  ADD KEY `vendor_slots_takeaway_index` (`takeaway`),
  ADD KEY `vendor_slots_delivery_index` (`delivery`),
  ADD KEY `vendor_slots_rental_pick_drop_on_demand_laundry_index` (`rental`,`pick_drop`,`on_demand`,`laundry`),
  ADD KEY `vendor_slots_appointment_index` (`appointment`);

--
-- Indexes for table `vendor_slot_dates`
--
ALTER TABLE `vendor_slot_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_slot_dates_specific_date_index` (`specific_date`),
  ADD KEY `vendor_slot_dates_vendor_id_foreign` (`vendor_id`),
  ADD KEY `vendor_slot_dates_category_id_foreign` (`category_id`),
  ADD KEY `vendor_slot_dates_dine_in_index` (`dine_in`),
  ADD KEY `vendor_slot_dates_takeaway_index` (`takeaway`),
  ADD KEY `vendor_slot_dates_delivery_index` (`delivery`);

--
-- Indexes for table `vendor_slot_date_service_areas`
--
ALTER TABLE `vendor_slot_date_service_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_slot_date_service_areas_vendor_slot_date_id_index` (`vendor_slot_date_id`),
  ADD KEY `vendor_slot_date_service_areas_service_area_id_index` (`service_area_id`);

--
-- Indexes for table `vendor_slot_service_areas`
--
ALTER TABLE `vendor_slot_service_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_slot_service_areas_vendor_slot_id_index` (`vendor_slot_id`),
  ADD KEY `vendor_slot_service_areas_service_area_id_index` (`service_area_id`);

--
-- Indexes for table `vendor_social_media_urls`
--
ALTER TABLE `vendor_social_media_urls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_social_media_urls_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `vendor_templetes`
--
ALTER TABLE `vendor_templetes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_users`
--
ALTER TABLE `vendor_users`
  ADD KEY `vendor_users_user_id_foreign` (`user_id`),
  ADD KEY `vendor_users_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `verification_options`
--
ALTER TABLE `verification_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_stylings`
--
ALTER TABLE `web_stylings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_styling_options`
--
ALTER TABLE `web_styling_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `web_styling_options_web_styling_id_foreign` (`web_styling_id`);

--
-- Indexes for table `woocommerces`
--
ALTER TABLE `woocommerces`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `addon_options`
--
ALTER TABLE `addon_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `addon_option_translations`
--
ALTER TABLE `addon_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `addon_sets`
--
ALTER TABLE `addon_sets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `addon_set_translations`
--
ALTER TABLE `addon_set_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_logs`
--
ALTER TABLE `api_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_dynamic_tutorials`
--
ALTER TABLE `app_dynamic_tutorials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_stylings`
--
ALTER TABLE `app_stylings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `app_styling_options`
--
ALTER TABLE `app_styling_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `assign_qrcodes_to_orders`
--
ALTER TABLE `assign_qrcodes_to_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attribute_categories`
--
ALTER TABLE `attribute_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attribute_options`
--
ALTER TABLE `attribute_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attribute_option_translations`
--
ALTER TABLE `attribute_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attribute_translations`
--
ALTER TABLE `attribute_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `authentication_log`
--
ALTER TABLE `authentication_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `auto_reject_orders_cron`
--
ALTER TABLE `auto_reject_orders_cron`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `banner_service_areas`
--
ALTER TABLE `banner_service_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bid_products`
--
ALTER TABLE `bid_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bid_requests`
--
ALTER TABLE `bid_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blocked_tokens`
--
ALTER TABLE `blocked_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `brand_categories`
--
ALTER TABLE `brand_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `brand_translations`
--
ALTER TABLE `brand_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `business_types`
--
ALTER TABLE `business_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cab_booking_layouts`
--
ALTER TABLE `cab_booking_layouts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cab_booking_layout_banners`
--
ALTER TABLE `cab_booking_layout_banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cab_booking_layout_categories`
--
ALTER TABLE `cab_booking_layout_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cab_booking_layout_transaltions`
--
ALTER TABLE `cab_booking_layout_transaltions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaign_rosters`
--
ALTER TABLE `campaign_rosters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caregory_kyc_docs`
--
ALTER TABLE `caregory_kyc_docs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `cart_addons`
--
ALTER TABLE `cart_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_coupons`
--
ALTER TABLE `cart_coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cart_products`
--
ALTER TABLE `cart_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `cart_product_prescriptions`
--
ALTER TABLE `cart_product_prescriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_vendor_delivery_fee`
--
ALTER TABLE `cart_vendor_delivery_fee`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `car_details`
--
ALTER TABLE `car_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `car_images`
--
ALTER TABLE `car_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `category_histories`
--
ALTER TABLE `category_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `category_kyc_documents`
--
ALTER TABLE `category_kyc_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_kyc_document_mappings`
--
ALTER TABLE `category_kyc_document_mappings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_kyc_document_translations`
--
ALTER TABLE `category_kyc_document_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_roles`
--
ALTER TABLE `category_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_translations`
--
ALTER TABLE `category_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `celebrities`
--
ALTER TABLE `celebrities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_countries`
--
ALTER TABLE `client_countries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_currencies`
--
ALTER TABLE `client_currencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_preferences`
--
ALTER TABLE `client_preferences`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_preference_additional`
--
ALTER TABLE `client_preference_additional`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_slots`
--
ALTER TABLE `client_slots`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cms`
--
ALTER TABLE `cms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `copy_tools`
--
ALTER TABLE `copy_tools`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `csv_customer_imports`
--
ALTER TABLE `csv_customer_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `csv_product_imports`
--
ALTER TABLE `csv_product_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `csv_qrcode_imports`
--
ALTER TABLE `csv_qrcode_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `csv_vendor_imports`
--
ALTER TABLE `csv_vendor_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `delivery_slots`
--
ALTER TABLE `delivery_slots`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_slots_product`
--
ALTER TABLE `delivery_slots_product`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispatcher_status_options`
--
ALTER TABLE `dispatcher_status_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dispatcher_template_type_options`
--
ALTER TABLE `dispatcher_template_type_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispatcher_warning_pages`
--
ALTER TABLE `dispatcher_warning_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_registration_documents`
--
ALTER TABLE `driver_registration_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_registration_document_translations`
--
ALTER TABLE `driver_registration_document_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `estimated_products`
--
ALTER TABLE `estimated_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimated_product_addons`
--
ALTER TABLE `estimated_product_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimated_product_addon_new`
--
ALTER TABLE `estimated_product_addon_new`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimated_product_carts`
--
ALTER TABLE `estimated_product_carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimated_product_cart_new`
--
ALTER TABLE `estimated_product_cart_new`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimated_product_new`
--
ALTER TABLE `estimated_product_new`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_addon_options`
--
ALTER TABLE `estimate_addon_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_addon_option_translations`
--
ALTER TABLE `estimate_addon_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_addon_sets`
--
ALTER TABLE `estimate_addon_sets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_addon_set_translations`
--
ALTER TABLE `estimate_addon_set_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_products`
--
ALTER TABLE `estimate_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_product_translations`
--
ALTER TABLE `estimate_product_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exchange_reasons`
--
ALTER TABLE `exchange_reasons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilties`
--
ALTER TABLE `facilties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilty_translations`
--
ALTER TABLE `facilty_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faq_translations`
--
ALTER TABLE `faq_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gift_cards`
--
ALTER TABLE `gift_cards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `home_page_labels`
--
ALTER TABLE `home_page_labels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `home_page_label_transaltions`
--
ALTER TABLE `home_page_label_transaltions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `home_products`
--
ALTER TABLE `home_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_categories`
--
ALTER TABLE `influencer_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_initial_orders_discounts`
--
ALTER TABLE `influencer_initial_orders_discounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_kyc`
--
ALTER TABLE `influencer_kyc`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_social_account_details`
--
ALTER TABLE `influencer_social_account_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_tiers`
--
ALTER TABLE `influencer_tiers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_users`
--
ALTER TABLE `influencer_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influ_attributes`
--
ALTER TABLE `influ_attributes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influ_attr_cat`
--
ALTER TABLE `influ_attr_cat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influ_attr_opt`
--
ALTER TABLE `influ_attr_opt`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influ_attr_opt_trans`
--
ALTER TABLE `influ_attr_opt_trans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influ_attr_trans`
--
ALTER TABLE `influ_attr_trans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `long_term_services`
--
ALTER TABLE `long_term_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `long_term_service_periods`
--
ALTER TABLE `long_term_service_periods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `long_term_service_products`
--
ALTER TABLE `long_term_service_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `long_term_service_product_addons`
--
ALTER TABLE `long_term_service_product_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `long_term_service_translations`
--
ALTER TABLE `long_term_service_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_cards`
--
ALTER TABLE `loyalty_cards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `luxury_options`
--
ALTER TABLE `luxury_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `main_permissions`
--
ALTER TABLE `main_permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `main_roles`
--
ALTER TABLE `main_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `map_providers`
--
ALTER TABLE `map_providers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `marg_products`
--
ALTER TABLE `marg_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=894;

--
-- AUTO_INCREMENT for table `mobile_banners`
--
ALTER TABLE `mobile_banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_banner_service_areas`
--
ALTER TABLE `mobile_banner_service_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_customers`
--
ALTER TABLE `nikolag_customers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_discounts`
--
ALTER TABLE `nikolag_discounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_orders`
--
ALTER TABLE `nikolag_orders`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_products`
--
ALTER TABLE `nikolag_products`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_product_order`
--
ALTER TABLE `nikolag_product_order`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_taxes`
--
ALTER TABLE `nikolag_taxes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nikolag_transactions`
--
ALTER TABLE `nikolag_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nomenclatures`
--
ALTER TABLE `nomenclatures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nomenclatures_translations`
--
ALTER TABLE `nomenclatures_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notification_types`
--
ALTER TABLE `notification_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `onboard_settings`
--
ALTER TABLE `onboard_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_cancel_requests`
--
ALTER TABLE `order_cancel_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_delivery_status_icon`
--
ALTER TABLE `order_delivery_status_icon`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_driver_ratings`
--
ALTER TABLE `order_driver_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_files`
--
ALTER TABLE `order_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_locations`
--
ALTER TABLE `order_locations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_long_term_services`
--
ALTER TABLE `order_long_term_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_long_term_services_addons`
--
ALTER TABLE `order_long_term_services_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_long_term_service_schedules`
--
ALTER TABLE `order_long_term_service_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_notifications_logs`
--
ALTER TABLE `order_notifications_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_product_addons`
--
ALTER TABLE `order_product_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_product_dispatch_return_routes`
--
ALTER TABLE `order_product_dispatch_return_routes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id based on product quantity';

--
-- AUTO_INCREMENT for table `order_product_dispatch_routes`
--
ALTER TABLE `order_product_dispatch_routes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id based on product quantity';

--
-- AUTO_INCREMENT for table `order_product_prescriptions`
--
ALTER TABLE `order_product_prescriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_product_ratings`
--
ALTER TABLE `order_product_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_product_rating_files`
--
ALTER TABLE `order_product_rating_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_qrcode_links`
--
ALTER TABLE `order_qrcode_links`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_refunds`
--
ALTER TABLE `order_refunds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_return_requests`
--
ALTER TABLE `order_return_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_return_request_files`
--
ALTER TABLE `order_return_request_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_options`
--
ALTER TABLE `order_status_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_taxes`
--
ALTER TABLE `order_taxes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_vendors`
--
ALTER TABLE `order_vendors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_vendor_accounting`
--
ALTER TABLE `order_vendor_accounting`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_vendor_products`
--
ALTER TABLE `order_vendor_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_vendor_reports`
--
ALTER TABLE `order_vendor_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `page_translations`
--
ALTER TABLE `page_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_options`
--
ALTER TABLE `payment_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `payout_options`
--
ALTER TABLE `payout_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `pick_drop_driver_bids`
--
ALTER TABLE `pick_drop_driver_bids`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pincodes`
--
ALTER TABLE `pincodes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pincode_delivery_options`
--
ALTER TABLE `pincode_delivery_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `processor_products`
--
ALTER TABLE `processor_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_bookings`
--
ALTER TABLE `product_bookings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_by_roles`
--
ALTER TABLE `product_by_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_delivery_fee_by_roles`
--
ALTER TABLE `product_delivery_fee_by_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_faqs`
--
ALTER TABLE `product_faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_faq_select_options`
--
ALTER TABLE `product_faq_select_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_faq_select_option_translations`
--
ALTER TABLE `product_faq_select_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_faq_translations`
--
ALTER TABLE `product_faq_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `product_inquiries`
--
ALTER TABLE `product_inquiries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_recently_viewed`
--
ALTER TABLE `product_recently_viewed`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_translations`
--
ALTER TABLE `product_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `product_variant_by_roles`
--
ALTER TABLE `product_variant_by_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variant_sets`
--
ALTER TABLE `product_variant_sets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `promocodes`
--
ALTER TABLE `promocodes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promocode_details`
--
ALTER TABLE `promocode_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `promo_types`
--
ALTER TABLE `promo_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promo_usages`
--
ALTER TABLE `promo_usages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qrcode_imports`
--
ALTER TABLE `qrcode_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refer_and_earns`
--
ALTER TABLE `refer_and_earns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refer_and_earn_details`
--
ALTER TABLE `refer_and_earn_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reschedule_orders`
--
ALTER TABLE `reschedule_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_reasons`
--
ALTER TABLE `return_reasons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_areas`
--
ALTER TABLE `service_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_area_for_banners`
--
ALTER TABLE `service_area_for_banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_options`
--
ALTER TABLE `shipping_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shippo_delivery_options`
--
ALTER TABLE `shippo_delivery_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `show_subscription_plan_on_signups`
--
ALTER TABLE `show_subscription_plan_on_signups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slot_days`
--
ALTER TABLE `slot_days`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_providers`
--
ALTER TABLE `sms_providers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sms_templates`
--
ALTER TABLE `sms_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `social_credentials`
--
ALTER TABLE `social_credentials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media`
--
ALTER TABLE `social_media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `square_timestamp`
--
ALTER TABLE `square_timestamp`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `static_dropoff_locations`
--
ALTER TABLE `static_dropoff_locations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_features_list_user`
--
ALTER TABLE `subscription_features_list_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscription_features_list_vendor`
--
ALTER TABLE `subscription_features_list_vendor`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscription_invoices_user`
--
ALTER TABLE `subscription_invoices_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_invoices_vendor`
--
ALTER TABLE `subscription_invoices_vendor`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_invoice_features_user`
--
ALTER TABLE `subscription_invoice_features_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_invoice_features_vendor`
--
ALTER TABLE `subscription_invoice_features_vendor`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_log_user`
--
ALTER TABLE `subscription_log_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans_user`
--
ALTER TABLE `subscription_plans_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans_vendor`
--
ALTER TABLE `subscription_plans_vendor`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plan_features_user`
--
ALTER TABLE `subscription_plan_features_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plan_features_vendor`
--
ALTER TABLE `subscription_plan_features_vendor`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_status_options`
--
ALTER TABLE `subscription_status_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tag_translations`
--
ALTER TABLE `tag_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_categories`
--
ALTER TABLE `tax_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tax_rates`
--
ALTER TABLE `tax_rates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tax_rate_categories`
--
ALTER TABLE `tax_rate_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `temp_carts`
--
ALTER TABLE `temp_carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_cart_addons`
--
ALTER TABLE `temp_cart_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_cart_coupons`
--
ALTER TABLE `temp_cart_coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_cart_products`
--
ALTER TABLE `temp_cart_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_cart_vendor_delivery_fee`
--
ALTER TABLE `temp_cart_vendor_delivery_fee`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terminologies`
--
ALTER TABLE `terminologies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `third_party_accounting`
--
ALTER TABLE `third_party_accounting`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timezones`
--
ALTER TABLE `timezones`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=426;

--
-- AUTO_INCREMENT for table `toll_pass_origin`
--
ALTER TABLE `toll_pass_origin`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `travel_mode`
--
ALTER TABLE `travel_mode`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_bid_ride_requests`
--
ALTER TABLE `user_bid_ride_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_data_vault`
--
ALTER TABLE `user_data_vault`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_docs`
--
ALTER TABLE `user_docs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_gift_cards`
--
ALTER TABLE `user_gift_cards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_loyalty_points`
--
ALTER TABLE `user_loyalty_points`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_loyalty_point_histories`
--
ALTER TABLE `user_loyalty_point_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_payment_cards`
--
ALTER TABLE `user_payment_cards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_ratings`
--
ALTER TABLE `user_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_refferals`
--
ALTER TABLE `user_refferals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_registration_documents`
--
ALTER TABLE `user_registration_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_registration_document_translations`
--
ALTER TABLE `user_registration_document_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_registration_select_options`
--
ALTER TABLE `user_registration_select_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_registration_select_option_translations`
--
ALTER TABLE `user_registration_select_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_saved_payment_methods`
--
ALTER TABLE `user_saved_payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_vendors`
--
ALTER TABLE `user_vendors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_verification`
--
ALTER TABLE `user_verification`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_verification_resources`
--
ALTER TABLE `user_verification_resources`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_wishlists`
--
ALTER TABLE `user_wishlists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `variant_options`
--
ALTER TABLE `variant_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `variant_option_translations`
--
ALTER TABLE `variant_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `variant_translations`
--
ALTER TABLE `variant_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicle_emission_type`
--
ALTER TABLE `vehicle_emission_type`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vendor_additional_info`
--
ALTER TABLE `vendor_additional_info`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_categories`
--
ALTER TABLE `vendor_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vendor_cities`
--
ALTER TABLE `vendor_cities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_city_translations`
--
ALTER TABLE `vendor_city_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_connected_accounts`
--
ALTER TABLE `vendor_connected_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_dinein_categories`
--
ALTER TABLE `vendor_dinein_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_dinein_category_translations`
--
ALTER TABLE `vendor_dinein_category_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_dinein_tables`
--
ALTER TABLE `vendor_dinein_tables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_dinein_table_translations`
--
ALTER TABLE `vendor_dinein_table_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_docs`
--
ALTER TABLE `vendor_docs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_facilties`
--
ALTER TABLE `vendor_facilties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_media`
--
ALTER TABLE `vendor_media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `vendor_min_amounts`
--
ALTER TABLE `vendor_min_amounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_multi_banners`
--
ALTER TABLE `vendor_multi_banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_order_cancel_return_payments`
--
ALTER TABLE `vendor_order_cancel_return_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_order_dispatcher_statuses`
--
ALTER TABLE `vendor_order_dispatcher_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_order_product_dispatcher_statuses`
--
ALTER TABLE `vendor_order_product_dispatcher_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_order_product_statuses`
--
ALTER TABLE `vendor_order_product_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_order_statuses`
--
ALTER TABLE `vendor_order_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `vendor_payouts`
--
ALTER TABLE `vendor_payouts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_registration_documents`
--
ALTER TABLE `vendor_registration_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_registration_document_translations`
--
ALTER TABLE `vendor_registration_document_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_registration_select_options`
--
ALTER TABLE `vendor_registration_select_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_registration_select_option_translations`
--
ALTER TABLE `vendor_registration_select_option_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_saved_payment_methods`
--
ALTER TABLE `vendor_saved_payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_sections`
--
ALTER TABLE `vendor_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_section_heading_translations`
--
ALTER TABLE `vendor_section_heading_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_section_translations`
--
ALTER TABLE `vendor_section_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_slots`
--
ALTER TABLE `vendor_slots`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_slot_dates`
--
ALTER TABLE `vendor_slot_dates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_slot_date_service_areas`
--
ALTER TABLE `vendor_slot_date_service_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_slot_service_areas`
--
ALTER TABLE `vendor_slot_service_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_social_media_urls`
--
ALTER TABLE `vendor_social_media_urls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_templetes`
--
ALTER TABLE `vendor_templetes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `verification_options`
--
ALTER TABLE `verification_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `web_stylings`
--
ALTER TABLE `web_stylings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `web_styling_options`
--
ALTER TABLE `web_styling_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `woocommerces`
--
ALTER TABLE `woocommerces`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addon_options`
--
ALTER TABLE `addon_options`
  ADD CONSTRAINT `addon_options_addon_id_foreign` FOREIGN KEY (`addon_id`) REFERENCES `addon_sets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addon_option_translations`
--
ALTER TABLE `addon_option_translations`
  ADD CONSTRAINT `addon_option_translations_addon_opt_id_foreign` FOREIGN KEY (`addon_opt_id`) REFERENCES `addon_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addon_option_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `addon_sets`
--
ALTER TABLE `addon_sets`
  ADD CONSTRAINT `addon_sets_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `addon_set_translations`
--
ALTER TABLE `addon_set_translations`
  ADD CONSTRAINT `addon_set_translations_addon_id_foreign` FOREIGN KEY (`addon_id`) REFERENCES `addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addon_set_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `app_styling_options`
--
ALTER TABLE `app_styling_options`
  ADD CONSTRAINT `app_styling_options_app_styling_id_foreign` FOREIGN KEY (`app_styling_id`) REFERENCES `app_stylings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attributes`
--
ALTER TABLE `attributes`
  ADD CONSTRAINT `attributes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attribute_categories`
--
ALTER TABLE `attribute_categories`
  ADD CONSTRAINT `attribute_categories_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attribute_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attribute_options`
--
ALTER TABLE `attribute_options`
  ADD CONSTRAINT `attribute_options_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attribute_option_translations`
--
ALTER TABLE `attribute_option_translations`
  ADD CONSTRAINT `attribute_option_translations_attribute_option_id_foreign` FOREIGN KEY (`attribute_option_id`) REFERENCES `attribute_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attribute_option_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `attribute_translations`
--
ALTER TABLE `attribute_translations`
  ADD CONSTRAINT `attribute_translations_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attribute_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `banners`
--
ALTER TABLE `banners`
  ADD CONSTRAINT `banners_redirect_category_id_foreign` FOREIGN KEY (`redirect_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `banners_redirect_vendor_id_foreign` FOREIGN KEY (`redirect_vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `banner_service_areas`
--
ALTER TABLE `banner_service_areas`
  ADD CONSTRAINT `banner_service_areas_service_area_id_foreign` FOREIGN KEY (`service_area_id`) REFERENCES `service_area_for_banners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bids`
--
ALTER TABLE `bids`
  ADD CONSTRAINT `bids_bid_req_id_foreign` FOREIGN KEY (`bid_req_id`) REFERENCES `bid_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bid_products`
--
ALTER TABLE `bid_products`
  ADD CONSTRAINT `bid_products_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bid_requests`
--
ALTER TABLE `bid_requests`
  ADD CONSTRAINT `bid_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brand_categories`
--
ALTER TABLE `brand_categories`
  ADD CONSTRAINT `brand_categories_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brand_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brand_translations`
--
ALTER TABLE `brand_translations`
  ADD CONSTRAINT `brand_translations_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brand_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `cab_booking_layout_banners`
--
ALTER TABLE `cab_booking_layout_banners`
  ADD CONSTRAINT `cab_booking_layout_banners_cab_booking_layout_id_foreign` FOREIGN KEY (`cab_booking_layout_id`) REFERENCES `cab_booking_layouts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cab_booking_layout_categories`
--
ALTER TABLE `cab_booking_layout_categories`
  ADD CONSTRAINT `cab_booking_layout_categories_cab_booking_layout_id_foreign` FOREIGN KEY (`cab_booking_layout_id`) REFERENCES `cab_booking_layouts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cab_booking_layout_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cab_booking_layout_transaltions`
--
ALTER TABLE `cab_booking_layout_transaltions`
  ADD CONSTRAINT `cab_booking_layout_transaltions_cab_booking_layout_id_foreign` FOREIGN KEY (`cab_booking_layout_id`) REFERENCES `cab_booking_layouts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cab_booking_layout_transaltions_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `carts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_addons`
--
ALTER TABLE `cart_addons`
  ADD CONSTRAINT `cart_addons_addon_id_foreign` FOREIGN KEY (`addon_id`) REFERENCES `addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_addons_cart_product_id_foreign` FOREIGN KEY (`cart_product_id`) REFERENCES `cart_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_addons_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `addon_options` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_coupons`
--
ALTER TABLE `cart_coupons`
  ADD CONSTRAINT `cart_coupons_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_coupons_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `promocodes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_products`
--
ALTER TABLE `cart_products`
  ADD CONSTRAINT `cart_products_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_products_product_variant_by_role_id_foreign` FOREIGN KEY (`product_variant_by_role_id`) REFERENCES `product_variant_by_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_products_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `delivery_slots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_products_tax_category_id_foreign` FOREIGN KEY (`tax_category_id`) REFERENCES `tax_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_products_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_products_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_product_prescriptions`
--
ALTER TABLE `cart_product_prescriptions`
  ADD CONSTRAINT `cart_product_prescriptions_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_product_prescriptions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_product_prescriptions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_vendor_delivery_fee`
--
ALTER TABLE `cart_vendor_delivery_fee`
  ADD CONSTRAINT `cart_vendor_delivery_fee_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_vendor_delivery_fee_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_details`
--
ALTER TABLE `car_details`
  ADD CONSTRAINT `car_details_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `user_addresses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_images`
--
ALTER TABLE `car_images`
  ADD CONSTRAINT `car_images_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `user_addresses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_client_code_foreign` FOREIGN KEY (`client_code`) REFERENCES `clients` (`code`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `category_histories`
--
ALTER TABLE `category_histories`
  ADD CONSTRAINT `category_histories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_roles`
--
ALTER TABLE `category_roles`
  ADD CONSTRAINT `category_roles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `category_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `category_tags`
--
ALTER TABLE `category_tags`
  ADD CONSTRAINT `category_tags_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_translations`
--
ALTER TABLE `category_translations`
  ADD CONSTRAINT `category_translations_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `celebrities`
--
ALTER TABLE `celebrities`
  ADD CONSTRAINT `celebrities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `celebrity_brands`
--
ALTER TABLE `celebrity_brands`
  ADD CONSTRAINT `celebrity_brands_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `celebrity_brands_celebrity_id_foreign` FOREIGN KEY (`celebrity_id`) REFERENCES `celebrities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `clients_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `client_countries`
--
ALTER TABLE `client_countries`
  ADD CONSTRAINT `client_countries_client_code_foreign` FOREIGN KEY (`client_code`) REFERENCES `clients` (`code`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_countries_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `client_currencies`
--
ALTER TABLE `client_currencies`
  ADD CONSTRAINT `client_currencies_client_code_foreign` FOREIGN KEY (`client_code`) REFERENCES `clients` (`code`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_currencies_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `client_languages`
--
ALTER TABLE `client_languages`
  ADD CONSTRAINT `client_languages_client_code_foreign` FOREIGN KEY (`client_code`) REFERENCES `clients` (`code`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_languages_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `client_preferences`
--
ALTER TABLE `client_preferences`
  ADD CONSTRAINT `client_preferences_app_template_id_foreign` FOREIGN KEY (`app_template_id`) REFERENCES `templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_preferences_client_code_foreign` FOREIGN KEY (`client_code`) REFERENCES `clients` (`code`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_preferences_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_preferences_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_preferences_map_provider_foreign` FOREIGN KEY (`map_provider`) REFERENCES `map_providers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `client_preferences_web_template_id_foreign` FOREIGN KEY (`web_template_id`) REFERENCES `templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `cms`
--
ALTER TABLE `cms`
  ADD CONSTRAINT `cms_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `csv_customer_imports`
--
ALTER TABLE `csv_customer_imports`
  ADD CONSTRAINT `csv_customer_imports_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `csv_product_imports`
--
ALTER TABLE `csv_product_imports`
  ADD CONSTRAINT `csv_product_imports_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `csv_product_imports_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `csv_qrcode_imports`
--
ALTER TABLE `csv_qrcode_imports`
  ADD CONSTRAINT `csv_qrcode_imports_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `csv_qrcode_imports_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `csv_vendor_imports`
--
ALTER TABLE `csv_vendor_imports`
  ADD CONSTRAINT `csv_vendor_imports_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `delivery_slots_product`
--
ALTER TABLE `delivery_slots_product`
  ADD CONSTRAINT `delivery_slots_product_delivery_slot_id_foreign` FOREIGN KEY (`delivery_slot_id`) REFERENCES `delivery_slots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_slots_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimated_products`
--
ALTER TABLE `estimated_products`
  ADD CONSTRAINT `estimated_products_estimated_cart_id_foreign` FOREIGN KEY (`estimated_cart_id`) REFERENCES `estimated_product_carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimated_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `estimate_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimated_product_addons`
--
ALTER TABLE `estimated_product_addons`
  ADD CONSTRAINT `estimated_product_addons_estimated_addon_id_foreign` FOREIGN KEY (`estimated_addon_id`) REFERENCES `estimate_addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimated_product_addons_estimated_addon_option_id_foreign` FOREIGN KEY (`estimated_addon_option_id`) REFERENCES `estimate_addon_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimated_product_addons_estimated_product_id_foreign` FOREIGN KEY (`estimated_product_id`) REFERENCES `estimated_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimated_product_addon_new`
--
ALTER TABLE `estimated_product_addon_new`
  ADD CONSTRAINT `estimated_product_addon_new_estimated_addon_id_foreign` FOREIGN KEY (`estimated_addon_id`) REFERENCES `estimate_addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimated_product_addon_new_estimated_addon_option_id_foreign` FOREIGN KEY (`estimated_addon_option_id`) REFERENCES `estimate_addon_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimated_product_addon_new_estimated_product_id_foreign` FOREIGN KEY (`estimated_product_id`) REFERENCES `estimated_product_new` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimated_product_carts`
--
ALTER TABLE `estimated_product_carts`
  ADD CONSTRAINT `estimated_product_carts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimated_product_carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimated_product_new`
--
ALTER TABLE `estimated_product_new`
  ADD CONSTRAINT `estimated_product_new_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `estimate_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimate_addon_options`
--
ALTER TABLE `estimate_addon_options`
  ADD CONSTRAINT `estimate_addon_options_estimate_addon_id_foreign` FOREIGN KEY (`estimate_addon_id`) REFERENCES `estimate_addon_sets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimate_addon_option_translations`
--
ALTER TABLE `estimate_addon_option_translations`
  ADD CONSTRAINT `estimate_addon_option_translations_estimate_addon_opt_id_foreign` FOREIGN KEY (`estimate_addon_opt_id`) REFERENCES `estimate_addon_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimate_addon_option_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `estimate_addon_set_translations`
--
ALTER TABLE `estimate_addon_set_translations`
  ADD CONSTRAINT `estimate_addon_set_translations_estimate_addon_id_foreign` FOREIGN KEY (`estimate_addon_id`) REFERENCES `estimate_addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimate_addon_set_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `estimate_products`
--
ALTER TABLE `estimate_products`
  ADD CONSTRAINT `estimate_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `estimate_product_addons`
--
ALTER TABLE `estimate_product_addons`
  ADD CONSTRAINT `estimate_product_addons_estimate_addon_id_foreign` FOREIGN KEY (`estimate_addon_id`) REFERENCES `estimate_addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estimate_product_addons_estimate_product_id_foreign` FOREIGN KEY (`estimate_product_id`) REFERENCES `estimate_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `estimate_product_translations`
--
ALTER TABLE `estimate_product_translations`
  ADD CONSTRAINT `estimate_product_translations_estimate_product_id_foreign` FOREIGN KEY (`estimate_product_id`) REFERENCES `estimate_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faq_translations`
--
ALTER TABLE `faq_translations`
  ADD CONSTRAINT `faq_translations_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gift_cards`
--
ALTER TABLE `gift_cards`
  ADD CONSTRAINT `gift_cards_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `home_page_label_transaltions`
--
ALTER TABLE `home_page_label_transaltions`
  ADD CONSTRAINT `home_page_label_transaltions_home_page_label_id_foreign` FOREIGN KEY (`home_page_label_id`) REFERENCES `home_page_labels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `home_page_label_transaltions_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influencer_initial_orders_discounts`
--
ALTER TABLE `influencer_initial_orders_discounts`
  ADD CONSTRAINT `influencer_initial_orders_discounts_influencer_user_id_foreign` FOREIGN KEY (`influencer_user_id`) REFERENCES `influencer_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influencer_kyc`
--
ALTER TABLE `influencer_kyc`
  ADD CONSTRAINT `influencer_kyc_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influencer_social_account_details`
--
ALTER TABLE `influencer_social_account_details`
  ADD CONSTRAINT `influencer_social_account_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `influencer_users`
--
ALTER TABLE `influencer_users`
  ADD CONSTRAINT `influencer_users_influencer_tier_id_foreign` FOREIGN KEY (`influencer_tier_id`) REFERENCES `influencer_tiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `influencer_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influ_attr_cat`
--
ALTER TABLE `influ_attr_cat`
  ADD CONSTRAINT `influ_attr_cat_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `influ_attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `influ_attr_cat_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `influencer_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influ_attr_opt`
--
ALTER TABLE `influ_attr_opt`
  ADD CONSTRAINT `influ_attr_opt_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `influ_attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influ_attr_opt_trans`
--
ALTER TABLE `influ_attr_opt_trans`
  ADD CONSTRAINT `influ_attr_opt_trans_attribute_option_id_foreign` FOREIGN KEY (`attribute_option_id`) REFERENCES `influ_attr_opt` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `influ_attr_opt_trans_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `influ_attr_trans`
--
ALTER TABLE `influ_attr_trans`
  ADD CONSTRAINT `influ_attr_trans_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `influ_attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `influ_attr_trans_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE;

--
-- Constraints for table `marg_products`
--
ALTER TABLE `marg_products`
  ADD CONSTRAINT `marg_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mobile_banners`
--
ALTER TABLE `mobile_banners`
  ADD CONSTRAINT `mobile_banners_redirect_category_id_foreign` FOREIGN KEY (`redirect_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `mobile_banners_redirect_vendor_id_foreign` FOREIGN KEY (`redirect_vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `mobile_banner_service_areas`
--
ALTER TABLE `mobile_banner_service_areas`
  ADD CONSTRAINT `mobile_banner_service_areas_service_area_id_foreign` FOREIGN KEY (`service_area_id`) REFERENCES `service_area_for_banners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `main_permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `main_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nikolag_product_order`
--
ALTER TABLE `nikolag_product_order`
  ADD CONSTRAINT `prod_id` FOREIGN KEY (`product_id`) REFERENCES `nikolag_products` (`id`);

--
-- Constraints for table `nikolag_transactions`
--
ALTER TABLE `nikolag_transactions`
  ADD CONSTRAINT `cus_id` FOREIGN KEY (`customer_id`) REFERENCES `nikolag_customers` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `user_addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_tax_category_id_foreign` FOREIGN KEY (`tax_category_id`) REFERENCES `tax_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_cancel_requests`
--
ALTER TABLE `order_cancel_requests`
  ADD CONSTRAINT `order_cancel_requests_order_vendor_product_id_foreign` FOREIGN KEY (`order_vendor_product_id`) REFERENCES `order_vendor_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_cancel_requests_return_reason_id_foreign` FOREIGN KEY (`return_reason_id`) REFERENCES `return_reasons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_driver_ratings`
--
ALTER TABLE `order_driver_ratings`
  ADD CONSTRAINT `order_driver_ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_product_addons`
--
ALTER TABLE `order_product_addons`
  ADD CONSTRAINT `order_product_addons_addon_id_foreign` FOREIGN KEY (`addon_id`) REFERENCES `addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_product_addons_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `addon_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_product_addons_order_product_id_foreign` FOREIGN KEY (`order_product_id`) REFERENCES `order_vendor_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_product_prescriptions`
--
ALTER TABLE `order_product_prescriptions`
  ADD CONSTRAINT `order_product_prescriptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_product_prescriptions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_product_prescriptions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_product_ratings`
--
ALTER TABLE `order_product_ratings`
  ADD CONSTRAINT `order_product_ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_product_ratings_order_vendor_product_id_foreign` FOREIGN KEY (`order_vendor_product_id`) REFERENCES `order_vendor_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_product_rating_files`
--
ALTER TABLE `order_product_rating_files`
  ADD CONSTRAINT `order_product_rating_files_order_product_rating_id_foreign` FOREIGN KEY (`order_product_rating_id`) REFERENCES `order_product_ratings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_refunds`
--
ALTER TABLE `order_refunds`
  ADD CONSTRAINT `order_refunds_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_refunds_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_refunds_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_return_requests`
--
ALTER TABLE `order_return_requests`
  ADD CONSTRAINT `order_return_requests_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_return_requests_order_vendor_product_id_foreign` FOREIGN KEY (`order_vendor_product_id`) REFERENCES `order_vendor_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_return_requests_return_by_foreign` FOREIGN KEY (`return_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_return_request_files`
--
ALTER TABLE `order_return_request_files`
  ADD CONSTRAINT `order_return_request_files_order_return_request_id_foreign` FOREIGN KEY (`order_return_request_id`) REFERENCES `order_return_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_vendors`
--
ALTER TABLE `order_vendors`
  ADD CONSTRAINT `order_vendors_exchange_order_vendor_id_foreign` FOREIGN KEY (`exchange_order_vendor_id`) REFERENCES `order_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `order_vendors_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_vendors_return_reason_id_foreign` FOREIGN KEY (`return_reason_id`) REFERENCES `return_reasons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_vendors_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_vendor_products`
--
ALTER TABLE `order_vendor_products`
  ADD CONSTRAINT `order_products_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_products_tax_category_id_foreign` FOREIGN KEY (`tax_category_id`) REFERENCES `tax_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_products_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_vendor_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_vendor_products_order_vendor_status_option_id_foreign` FOREIGN KEY (`order_vendor_status_option_id`) REFERENCES `order_status_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_vendor_products_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `delivery_slots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_vendor_reports`
--
ALTER TABLE `order_vendor_reports`
  ADD CONSTRAINT `order_vendor_reports_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_vendor_reports_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `page_translations`
--
ALTER TABLE `page_translations`
  ADD CONSTRAINT `page_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  ADD CONSTRAINT `page_translations_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pincodes`
--
ALTER TABLE `pincodes`
  ADD CONSTRAINT `pincodes_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pincode_delivery_options`
--
ALTER TABLE `pincode_delivery_options`
  ADD CONSTRAINT `pincode_delivery_options_pincode_id_foreign` FOREIGN KEY (`pincode_id`) REFERENCES `pincodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `processor_products`
--
ALTER TABLE `processor_products`
  ADD CONSTRAINT `processor_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_country_origin_id_foreign` FOREIGN KEY (`country_origin_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_tax_category_id_foreign` FOREIGN KEY (`tax_category_id`) REFERENCES `tax_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_addons`
--
ALTER TABLE `product_addons`
  ADD CONSTRAINT `product_addons_addon_id_foreign` FOREIGN KEY (`addon_id`) REFERENCES `addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_addons_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_attributes_attribute_option_id_foreign` FOREIGN KEY (`attribute_option_id`) REFERENCES `attribute_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_attributes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_bookings`
--
ALTER TABLE `product_bookings`
  ADD CONSTRAINT `product_bookings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_bookings_order_vendor_product_id_foreign` FOREIGN KEY (`order_vendor_product_id`) REFERENCES `order_vendor_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_by_roles`
--
ALTER TABLE `product_by_roles`
  ADD CONSTRAINT `product_by_roles_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_by_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_celebrities`
--
ALTER TABLE `product_celebrities`
  ADD CONSTRAINT `product_celebrities_celebrity_id_foreign` FOREIGN KEY (`celebrity_id`) REFERENCES `celebrities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_celebrities_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_cross_sells`
--
ALTER TABLE `product_cross_sells`
  ADD CONSTRAINT `product_cross_sells_cross_product_id_foreign` FOREIGN KEY (`cross_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_cross_sells_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_delivery_fee_by_roles`
--
ALTER TABLE `product_delivery_fee_by_roles`
  ADD CONSTRAINT `product_delivery_fee_by_roles_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_delivery_fee_by_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_faqs`
--
ALTER TABLE `product_faqs`
  ADD CONSTRAINT `product_faqs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `vendor_media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_inquiries`
--
ALTER TABLE `product_inquiries`
  ADD CONSTRAINT `product_inquiries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_inquiries_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_related`
--
ALTER TABLE `product_related`
  ADD CONSTRAINT `product_related_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_related_related_product_id_foreign` FOREIGN KEY (`related_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD CONSTRAINT `product_tags_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_tags_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_translations`
--
ALTER TABLE `product_translations`
  ADD CONSTRAINT `product_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_translations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_up_sells`
--
ALTER TABLE `product_up_sells`
  ADD CONSTRAINT `product_up_sells_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_up_sells_upsell_product_id_foreign` FOREIGN KEY (`upsell_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variants_tax_category_id_foreign` FOREIGN KEY (`tax_category_id`) REFERENCES `tax_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variant_by_roles`
--
ALTER TABLE `product_variant_by_roles`
  ADD CONSTRAINT `product_variant_by_roles_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_variant_by_roles_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_variant_by_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variant_images`
--
ALTER TABLE `product_variant_images`
  ADD CONSTRAINT `product_variant_images_product_image_id_foreign` FOREIGN KEY (`product_image_id`) REFERENCES `product_images` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variant_images_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variant_sets`
--
ALTER TABLE `product_variant_sets`
  ADD CONSTRAINT `product_variant_sets_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variant_sets_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variant_sets_variant_option_id_foreign` FOREIGN KEY (`variant_option_id`) REFERENCES `variant_options` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_variant_sets_variant_type_id_foreign` FOREIGN KEY (`variant_type_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promocodes`
--
ALTER TABLE `promocodes`
  ADD CONSTRAINT `promocodes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promocodes_promo_type_id_foreign` FOREIGN KEY (`promo_type_id`) REFERENCES `promo_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promocode_restrictions`
--
ALTER TABLE `promocode_restrictions`
  ADD CONSTRAINT `promocode_restrictions_promocode_id_foreign` FOREIGN KEY (`promocode_id`) REFERENCES `promocodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promo_usages`
--
ALTER TABLE `promo_usages`
  ADD CONSTRAINT `promo_usages_promocode_id_foreign` FOREIGN KEY (`promocode_id`) REFERENCES `promocodes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `refer_and_earns`
--
ALTER TABLE `refer_and_earns`
  ADD CONSTRAINT `refer_and_earns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `refer_and_earn_details`
--
ALTER TABLE `refer_and_earn_details`
  ADD CONSTRAINT `refer_and_earn_details_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `influ_attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refer_and_earn_details_attribute_option_id_foreign` FOREIGN KEY (`attribute_option_id`) REFERENCES `influ_attr_opt` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refer_and_earn_details_influencer_user_id_foreign` FOREIGN KEY (`influencer_user_id`) REFERENCES `influencer_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refer_and_earn_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reschedule_orders`
--
ALTER TABLE `reschedule_orders`
  ADD CONSTRAINT `reschedule_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reschedule_orders_reschedule_by_foreign` FOREIGN KEY (`reschedule_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reschedule_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riders`
--
ALTER TABLE `riders`
  ADD CONSTRAINT `riders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `main_permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `main_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_areas`
--
ALTER TABLE `service_areas`
  ADD CONSTRAINT `service_areas_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `slot_days`
--
ALTER TABLE `slot_days`
  ADD CONSTRAINT `slot_days_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `vendor_slots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_plan_features_user`
--
ALTER TABLE `subscription_plan_features_user`
  ADD CONSTRAINT `subscription_plan_features_user_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `subscription_features_list_user` (`id`),
  ADD CONSTRAINT `subscription_plan_features_user_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans_user` (`id`);

--
-- Constraints for table `subscription_plan_features_vendor`
--
ALTER TABLE `subscription_plan_features_vendor`
  ADD CONSTRAINT `subscription_plan_features_vendor_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `subscription_features_list_vendor` (`id`),
  ADD CONSTRAINT `subscription_plan_features_vendor_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans_vendor` (`id`);

--
-- Constraints for table `tag_translations`
--
ALTER TABLE `tag_translations`
  ADD CONSTRAINT `tag_translations_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tax_categories`
--
ALTER TABLE `tax_categories`
  ADD CONSTRAINT `tax_categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tax_rate_categories`
--
ALTER TABLE `tax_rate_categories`
  ADD CONSTRAINT `tax_rate_categories_tax_cate_id_foreign` FOREIGN KEY (`tax_cate_id`) REFERENCES `tax_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tax_rate_categories_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `temp_carts`
--
ALTER TABLE `temp_carts`
  ADD CONSTRAINT `temp_carts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `temp_carts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `temp_carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `temp_cart_addons`
--
ALTER TABLE `temp_cart_addons`
  ADD CONSTRAINT `temp_cart_addons_addon_id_foreign` FOREIGN KEY (`addon_id`) REFERENCES `addon_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temp_cart_addons_cart_product_id_foreign` FOREIGN KEY (`cart_product_id`) REFERENCES `temp_cart_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temp_cart_addons_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `addon_options` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `temp_cart_coupons`
--
ALTER TABLE `temp_cart_coupons`
  ADD CONSTRAINT `temp_cart_coupons_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `temp_carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temp_cart_coupons_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `promocodes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `temp_cart_products`
--
ALTER TABLE `temp_cart_products`
  ADD CONSTRAINT `temp_cart_products_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `temp_carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temp_cart_products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `temp_cart_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temp_cart_products_tax_category_id_foreign` FOREIGN KEY (`tax_category_id`) REFERENCES `tax_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `temp_cart_products_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `temp_cart_products_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `temp_cart_products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `temp_cart_vendor_delivery_fee`
--
ALTER TABLE `temp_cart_vendor_delivery_fee`
  ADD CONSTRAINT `temp_cart_vendor_delivery_fee_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `temp_carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temp_cart_vendor_delivery_fee_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_deposit_id_foreign` FOREIGN KEY (`deposit_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_withdraw_id_foreign` FOREIGN KEY (`withdraw_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_timezone_id_foreign` FOREIGN KEY (`timezone_id`) REFERENCES `timezones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_bid_ride_requests`
--
ALTER TABLE `user_bid_ride_requests`
  ADD CONSTRAINT `user_bid_ride_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD CONSTRAINT `user_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_gift_cards`
--
ALTER TABLE `user_gift_cards`
  ADD CONSTRAINT `user_gift_cards_gift_card_id_foreign` FOREIGN KEY (`gift_card_id`) REFERENCES `gift_cards` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_gift_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_loyalty_points`
--
ALTER TABLE `user_loyalty_points`
  ADD CONSTRAINT `user_loyalty_points_loyalty_card_id_foreign` FOREIGN KEY (`loyalty_card_id`) REFERENCES `loyalty_cards` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_loyalty_points_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_refferals`
--
ALTER TABLE `user_refferals`
  ADD CONSTRAINT `user_refferals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_vendors`
--
ALTER TABLE `user_vendors`
  ADD CONSTRAINT `user_vendors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_vendors_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_wishlists`
--
ALTER TABLE `user_wishlists`
  ADD CONSTRAINT `user_wishlists_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variant_categories`
--
ALTER TABLE `variant_categories`
  ADD CONSTRAINT `variant_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `variant_categories_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variant_options`
--
ALTER TABLE `variant_options`
  ADD CONSTRAINT `variant_options_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variant_option_translations`
--
ALTER TABLE `variant_option_translations`
  ADD CONSTRAINT `variant_option_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `variant_option_translations_variant_option_id_foreign` FOREIGN KEY (`variant_option_id`) REFERENCES `variant_options` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variant_translations`
--
ALTER TABLE `variant_translations`
  ADD CONSTRAINT `variant_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `client_languages` (`language_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `variant_translations_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_vendor_templete_id_foreign` FOREIGN KEY (`vendor_templete_id`) REFERENCES `vendor_templetes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vendor_additional_info`
--
ALTER TABLE `vendor_additional_info`
  ADD CONSTRAINT `vendor_additional_info_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_categories`
--
ALTER TABLE `vendor_categories`
  ADD CONSTRAINT `vendor_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_dinein_categories`
--
ALTER TABLE `vendor_dinein_categories`
  ADD CONSTRAINT `vendor_dinein_categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_dinein_category_translations`
--
ALTER TABLE `vendor_dinein_category_translations`
  ADD CONSTRAINT `vendor_dinein_category_translations_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `vendor_dinein_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_dinein_category_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_dinein_tables`
--
ALTER TABLE `vendor_dinein_tables`
  ADD CONSTRAINT `vendor_dinein_tables_vendor_dinein_category_id_foreign` FOREIGN KEY (`vendor_dinein_category_id`) REFERENCES `vendor_dinein_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_dinein_tables_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_dinein_table_translations`
--
ALTER TABLE `vendor_dinein_table_translations`
  ADD CONSTRAINT `vendor_dinein_table_translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_dinein_table_translations_vendor_dinein_table_id_foreign` FOREIGN KEY (`vendor_dinein_table_id`) REFERENCES `vendor_dinein_tables` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_media`
--
ALTER TABLE `vendor_media`
  ADD CONSTRAINT `vendor_media_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_min_amounts`
--
ALTER TABLE `vendor_min_amounts`
  ADD CONSTRAINT `vendor_min_amounts_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vendor_min_amounts_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_order_dispatcher_statuses`
--
ALTER TABLE `vendor_order_dispatcher_statuses`
  ADD CONSTRAINT `dispatcher_statuses_dispatcher_status_option_id_foreign` FOREIGN KEY (`dispatcher_status_option_id`) REFERENCES `dispatcher_status_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispatcher_statuses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_order_dispatcher_statuses_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_order_product_dispatcher_statuses`
--
ALTER TABLE `vendor_order_product_dispatcher_statuses`
  ADD CONSTRAINT `vendor_order_product_dispatcher_statuses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_order_product_statuses`
--
ALTER TABLE `vendor_order_product_statuses`
  ADD CONSTRAINT `vendor_order_product_statuses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_order_product_statuses_order_status_option_id_foreign` FOREIGN KEY (`order_status_option_id`) REFERENCES `order_status_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_order_product_statuses_order_vendor_product_id_foreign` FOREIGN KEY (`order_vendor_product_id`) REFERENCES `order_vendor_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_order_product_statuses_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_order_product_statuses_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_order_statuses`
--
ALTER TABLE `vendor_order_statuses`
  ADD CONSTRAINT `order_statuses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_statuses_order_status_option_id_foreign` FOREIGN KEY (`order_status_option_id`) REFERENCES `order_status_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_order_statuses_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_slots`
--
ALTER TABLE `vendor_slots`
  ADD CONSTRAINT `vendor_slots_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vendor_slots_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vendor_slot_dates`
--
ALTER TABLE `vendor_slot_dates`
  ADD CONSTRAINT `vendor_slot_dates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_slot_dates_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_social_media_urls`
--
ALTER TABLE `vendor_social_media_urls`
  ADD CONSTRAINT `vendor_social_media_urls_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_users`
--
ALTER TABLE `vendor_users`
  ADD CONSTRAINT `vendor_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_users_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `web_styling_options`
--
ALTER TABLE `web_styling_options`
  ADD CONSTRAINT `web_styling_options_web_styling_id_foreign` FOREIGN KEY (`web_styling_id`) REFERENCES `web_stylings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
