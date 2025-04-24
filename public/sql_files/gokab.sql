-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2021 at 02:24 PM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 7.4.19


-- --------------------------------------------------------

--
-- Dumping data for table `vendors`
--
INSERT INTO `vendors` (`id`, `name`, `slug`, `desc`, `logo`, `banner`, `address`, `email`, `website`, `phone_no`, `latitude`, `longitude`, `order_min_amount`, `order_pre_time`, `auto_reject_time`, `commission_percent`, `commission_fixed_per_order`, `commission_monthly`, `dine_in`, `takeaway`, `delivery`, `status`, `add_category`, `setting`, `is_show_vendor_details`, `created_at`, `updated_at`, `show_slot`, `vendor_templete_id`, `auto_accept_order`) VALUES
(1, 'DeliveryZone', NULL, NULL, 'default/default_logo.png', 'default/default_image.png', 'Sheikh Zayed Road - Dubai - United Arab Emirates', NULL, NULL, NULL, '25.060924600000', '55.128979500000', '0.00', NULL, NULL, 1, '0.00', '0.00', 0, 1, 1, 2, 1, 0, 0, NULL, '2021-09-29 05:55:32', 1, NULL, 0),
(2, 'Green Cab', 'green-cab', NULL, 'vendor/LkCQrTF2P1Qd8FZ825HOGDYQtkFy2dxxPIHaCDHM.jpg', 'vendor/1DDxZDGDNU1QB99cEvs1TI9ZUUNyOIPJ7A7rqvRE.jpg', 'Chandigarh, India', 'green@support.com', NULL, '7485251496', '30.733314800000', '76.779417900000', '0.00', NULL, NULL, 1, '0.00', '0.00', 0, 0, 1, 1, 1, 0, 0, '2021-09-29 05:55:23', '2021-09-29 05:55:23', 1, NULL, 0);

--
-- Dumping data for table `addon_sets`
--
INSERT INTO `addon_sets` (`id`, `title`, `min_select`, `max_select`, `position`, `status`, `is_core`, `vendor_id`, `created_at`, `updated_at`) VALUES
(1, 'Small Parcels', 1, 1, 1, 1, 1, NULL, NULL, NULL);

--
-- Dumping data for table `categories`
--
INSERT INTO `categories` (`id`, `icon`, `slug`, `type_id`, `image`, `is_visible`, `status`, `position`, `is_core`, `can_add_products`, `parent_id`, `vendor_id`, `client_code`, `display_mode`, `warning_page_id`, `template_type_id`, `warning_page_design`, `created_at`, `updated_at`, `deleted_at`, `show_wishlist`) VALUES
(1, NULL, 'Root', 3, NULL, 0, 1, 1, 1, 0, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, NULL, 'Delivery', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 06:01:06', '2021-09-29 06:01:06', 1),
(3, NULL, 'Restaurant', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:53', '2021-09-29 05:48:53', 1),
(4, NULL, 'Supermarket', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:57', '2021-09-29 05:48:57', 1),
(5, NULL, 'Pharmacy', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:49:02', '2021-09-29 05:49:02', 1),
(6, NULL, 'Send something', 1, NULL, 1, 1, 1, 1, 1, 2, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:41', '2021-09-29 05:48:41', 1),
(7, NULL, 'Buy something', 1, NULL, 1, 1, 1, 1, 1, 2, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:46', '2021-09-29 05:48:46', 1),
(8, NULL, 'Vegetables', 1, NULL, 1, 1, 1, 1, 1, 4, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:58', '2021-09-29 05:48:58', 1),
(9, NULL, 'Fruits', 1, NULL, 1, 1, 1, 1, 1, 4, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:58', '2021-09-29 05:48:58', 1),
(10, NULL, 'Dairy and Eggs', 1, NULL, 1, 1, 1, 1, 1, 4, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:48:58', '2021-09-29 05:48:58', 1),
(11, NULL, 'E-Commerce', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:49:06', '2021-09-29 05:49:06', 1),
(12, NULL, 'Cloth', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:49:11', '2021-09-29 05:49:11', 1),
(13, NULL, 'Dispatcher', 1, NULL, 1, 1, 1, 1, 1, 1, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2021-09-29 05:49:15', '2021-09-29 05:49:15', 1),
(14, 'fb78f0/category/icon/krfDcA76bhhuwi41MjflvzyrbmAMVwBljJMZKS8D.png', 'cabservice4', 7, 'category/image/Azw0EWEY0feOtxSe6joPyrvoIhVtB11TVEL1MM87.jpg', 1, 1, 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, '0', '2021-09-29 05:50:29', '2021-09-29 06:05:20', NULL, 1),
(15, 'fb78f0/category/icon/z9PW80kwEOOaB2jKNEIXZPICtewp6cUksN3jHQnP.png', 'motoservice4', 7, 'category/image/HzbCCqIM3WcdDjTNYa62qOtwMPSR2a1bRCf24ziI.png', 1, 1, 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, '0', '2021-09-29 05:51:18', '2021-09-29 06:05:40', NULL, 1),
(16, 'fb78f0/category/icon/SuaA9S9JfBZxDMxKV3uElRCI7TvTLq5UClrI6f1A.png', 'autoservice4', 7, 'category/image/at8ANQM3UQcOV4K2PEVi3pFbJJt1V0snVuWxKGL5.jpg', 1, 1, 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, '0', '2021-09-29 05:52:06', '2021-09-29 06:05:58', NULL, 1);

--
-- Dumping data for table `addon_options`
--

INSERT INTO `addon_options` (`id`, `title`, `addon_id`, `position`, `price`, `created_at`, `updated_at`) VALUES
(1, 'Small parcel', 1, 1, '100.00', NULL, NULL);


-- --------------------------------------------------------


--
-- Dumping data for table `addon_option_translations`
--

INSERT INTO `addon_option_translations` (`id`, `title`, `addon_opt_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Small parcel', 1, 1, NULL, NULL);

-- --------------------------------------------------------


-- --------------------------------------------------------


--
-- Dumping data for table `addon_set_translations`
--

INSERT INTO `addon_set_translations` (`id`, `title`, `addon_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Small Parcels', 1, 1, NULL, NULL);


-- --------------------------------------------------------


--
-- Dumping data for table `brands`
--
INSERT INTO `brands` (`id`, `title`, `image`, `image_banner`, `position`, `status`, `created_at`, `updated_at`) VALUES
(1, 'J.Crew', 'default/default_image.png', NULL, 1, 2, NULL, '2021-09-29 06:06:27'),
(2, 'Allform', 'default/default_image.png', NULL, 2, 2, NULL, '2021-09-29 06:06:31'),
(3, 'EyeBuyDirect', 'default/default_image.png', NULL, 3, 2, NULL, '2021-09-29 06:06:35'),
(4, 'In Pictures', 'default/default_image.png', NULL, 4, 2, NULL, '2021-09-29 06:06:39');

-- --------------------------------------------------------
INSERT INTO `variants` (`id`, `title`, `type`, `position`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Size', 1, 1, 2, NULL, '2021-09-29 06:06:04'),
(2, 'Color', 2, 2, 2, NULL, '2021-09-29 06:06:08'),
(3, 'Phones', 1, 3, 2, NULL, '2021-09-29 06:06:12');
--
-- Dumping data for table `variant_options`
--
INSERT INTO `variant_options` (`id`, `title`, `variant_id`, `hexacode`, `position`, `created_at`, `updated_at`) VALUES
(1, 'Small', 1, '', 1, NULL, NULL),
(2, 'White', 2, '#ffffff', 1, NULL, NULL),
(3, 'Black', 2, '#000000', 1, NULL, NULL),
(4, 'Grey', 2, '#808080', 1, NULL, NULL),
(5, 'Medium', 1, '', 1, NULL, NULL),
(6, 'Large', 1, '', 1, NULL, NULL),
(7, 'IPhone', 3, '', 1, NULL, NULL),
(8, 'Samsung', 3, '', 1, NULL, NULL),
(9, 'Xiaomi', 3, '', 1, NULL, NULL);

--
-- Dumping data for table `brand_categories`
--

INSERT INTO `brand_categories` (`id`, `brand_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 1, 11, NULL, NULL),
(2, 2, 11, NULL, NULL),
(3, 3, 11, NULL, NULL),
(4, 4, 11, NULL, NULL);



-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Dumping data for table `category_translations`
--

INSERT INTO `category_translations` (`id`, `name`, `trans-slug`, `meta_title`, `meta_description`, `meta_keywords`, `category_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'root', '', 'root', '', '', 1, 1, NULL, NULL),
(2, 'Delivery', '', 'Delivery', '', '', 2, 1, NULL, NULL),
(3, 'Restaurant', '', 'Restaurant', '', '', 3, 1, NULL, NULL),
(4, 'Supermarket', '', 'Supermarket', '', '', 4, 1, NULL, NULL),
(5, 'Pharmacy', '', 'Pharmacy', '', '', 5, 1, NULL, NULL),
(6, 'Send something', '', 'Send something', '', '', 6, 1, NULL, NULL),
(7, 'Buy something', '', 'Buy something', '', '', 7, 1, NULL, NULL),
(8, 'Vegetables', '', 'Vegetables', '', '', 8, 1, NULL, NULL),
(9, 'Fruits', '', 'Fruits', '', '', 9, 1, NULL, NULL),
(10, 'Dairy and Eggs', '', 'Dairy and Eggs', '', '', 10, 1, NULL, NULL),
(11, 'E-Commerce', '', 'E-Commerce', '', '', 11, 1, NULL, NULL),
(12, 'Cloth', '', 'Cloth', '', '', 12, 1, NULL, NULL),
(13, 'Dispatcher', '', 'Dispatcher', '', '', 13, 1, NULL, NULL),
(14, 'Cab Service', NULL, 'Cab Service', NULL, NULL, 14, 1, '2021-09-29 05:50:29', '2021-09-29 05:50:29'),
(15, 'Moto Service', NULL, 'Moto Service', NULL, NULL, 15, 1, '2021-09-29 05:51:18', '2021-09-29 05:51:18'),
(16, 'Auto Service', NULL, 'Auto Service', NULL, NULL, 16, 1, '2021-09-29 05:52:06', '2021-09-29 05:52:06');

-- --------------------------------------------------------

--
-- Dumping data for table `products`
--
INSERT INTO `products` (`id`, `sku`, `title`, `url_slug`, `description`, `body_html`, `vendor_id`, `category_id`, `type_id`, `country_origin_id`, `is_new`, `is_featured`, `is_live`, `is_physical`, `weight`, `weight_unit`, `has_inventory`, `has_variant`, `sell_when_out_of_stock`, `requires_shipping`, `Requires_last_mile`, `averageRating`, `inquiry_only`, `publish_at`, `created_at`, `updated_at`, `brand_id`, `tax_category_id`, `deleted_at`, `pharmacy_check`, `tags`, `need_price_from_dispatcher`, `mode_of_service`) VALUES
(1, 'sku-id', '1', 'sku-id', NULL, NULL, 1, NULL, 1, NULL, 1, 1, 1, 1, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(2, 'CAB5841633700288', 'RoyoXL', 'CAB5841633700288', NULL, '', 2, 14, 1, NULL, 1, 1, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, '3.00', 0, '2021-09-29 05:58:08', NULL, '2021-10-08 13:38:08', NULL, NULL, '2021-10-08 13:38:08', 0, NULL, '0', NULL),
(3, 'CAB585', 'Royo Platinum', 'CAB585', NULL, '', 2, 14, 1, NULL, 0, 0, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, '2.00', 0, '2021-09-29 05:59:06', NULL, '2021-11-16 10:39:05', NULL, NULL, NULL, 0, 'cabservice', '0', NULL),
(4, 'CAB586', 'Royo Pool', 'CAB586', NULL, '', 2, 14, 1, NULL, 0, 0, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, '5.00', 0, '2021-09-29 05:59:53', NULL, '2021-11-16 10:39:28', NULL, NULL, NULL, 0, 'poolservice', '0', NULL),
(5, 'CAB587', 'Royo Moto', 'CAB587', NULL, '', 2, 15, 1, NULL, 0, 0, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, '5.00', 0, '2021-09-29 06:00:30', NULL, '2021-11-16 12:02:30', NULL, NULL, NULL, 0, 'motoservice', '0', NULL),
(6, 'CAB588', 'Royo Auto', 'CAB588', NULL, '', 2, 16, 1, NULL, 0, 0, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, '2021-09-29 06:00:48', NULL, '2021-11-16 10:40:09', NULL, NULL, NULL, 0, 'autoservice', '0', NULL),
(7, 'com.royoorders.gokab.GreenCab.RoyoScooty', NULL, 'royo', NULL, NULL, 2, 15, 1, NULL, 0, 0, 1, 0, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, '2021-11-16 11:23:55', '2021-11-16 11:20:55', '2021-11-16 11:23:55', NULL, NULL, NULL, 0, 'scootyservice', '0', NULL);

-- --------------------------------------------------------


--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 11, NULL, NULL),
(2, 14, NULL, NULL),
(2, 14, NULL, NULL),
(3, 14, NULL, NULL),
(2, 14, NULL, NULL),
(3, 14, NULL, NULL),
(4, 14, NULL, NULL),
(2, 14, NULL, NULL),
(3, 14, NULL, NULL),
(4, 14, NULL, NULL),
(5, 15, NULL, NULL),
(2, 14, NULL, NULL),
(3, 14, NULL, NULL),
(4, 14, NULL, NULL),
(5, 15, NULL, NULL),
(6, 16, NULL, NULL),
(7, 15, '2021-11-16 11:20:55', '2021-11-16 11:20:55');

-- --------------------------------------------------------

--
-- Dumping data for table `vendor_media`
--

INSERT INTO `vendor_media` (`id`, `media_type`, `vendor_id`, `path`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'prods/iEGuba0kXWgKmuQPHoNYFzvdNIdmKfBNMeyYpZgR.jpg', '2021-09-29 05:57:38', '2021-09-29 05:57:38'),
(2, 1, 2, 'prods/SheRRCQyueAIOicWy3GFSSIeMC84szRFNz7bLTQn.jpg', '2021-09-29 05:57:52', '2021-09-29 05:57:52'),
(3, 1, 2, 'prods/FO3Ul9LyggG9NDS1r1rBA8W746iJqmsIFpqdegVo.jpg', '2021-09-29 05:57:55', '2021-09-29 05:57:55'),
(4, 1, 2, 'prods/1HcaKwnXUI3wlMwFhYquQTdNzOOy96uPemMrmcJI.jpg', '2021-09-29 05:57:59', '2021-09-29 05:57:59'),
(32, 1, 2, 'prods/SuuBPEXRlZO3LXzz7B3S4efqGeDRxpn8uWJ1VHVE.png', '2021-10-08 14:00:20', '2021-10-08 14:00:20'),
(33, 1, 2, 'prods/H1p14WtWsrDkfxznspZ24i0hVqs8xkOrdDazk5wy.png', '2021-10-08 14:00:45', '2021-10-08 14:00:45'),
(34, 1, 2, 'prods/9lHa80ZC1wOOVnx6OAz2w682IaWJVAihJAzuE7ay.png', '2021-10-08 14:01:09', '2021-10-08 14:01:09'),
(35, 1, 2, 'prods/rCcZ3p7UdLkED4bjUHxmkbMbWjJMcg94tqoh9cMD.png', '2021-10-08 14:01:30', '2021-10-08 14:01:30'),
(36, 1, 2, 'fb78f0/prods/O1UhU7zXHdDGEmytUuAUL1mVuYE7Ml7o1CUhWTLc.png', '2021-11-16 11:23:51', '2021-11-16 11:23:51');

-- --------------------------------------------------------
--
-- Dumping data for table `product_images`
--
INSERT INTO `product_images` (`id`, `product_id`, `media_id`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, NULL, NULL),
(2, 2, 2, 1, NULL, NULL),
(3, 2, 3, 1, NULL, NULL),
(32, 3, 32, 1, NULL, NULL),
(33, 4, 33, 1, NULL, NULL),
(34, 5, 34, 1, NULL, NULL),
(35, 6, 35, 1, NULL, NULL),
(36, 7, 36, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Dumping data for table `product_translations`
--

INSERT INTO `product_translations` (`id`, `title`, `body_html`, `meta_title`, `meta_keyword`, `meta_description`, `product_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Xiaomi', NULL, 'Xiaomi', 'Xiaomi', NULL, 1, 1, NULL, NULL),
(2, 'RoyoXL', NULL, NULL, NULL, NULL, 2, 1, NULL, '2021-09-29 05:58:08'),
(3, 'RoyoXL', '', '', '', '', 2, 1, NULL, NULL),
(4, 'Royo Platinum', NULL, NULL, NULL, NULL, 3, 1, NULL, '2021-09-29 05:59:06'),
(5, 'RoyoXL', '', '', '', '', 2, 1, NULL, NULL),
(6, 'Royo Platinum', '', '', '', '', 3, 1, NULL, NULL),
(7, 'Royo Pool', NULL, NULL, NULL, NULL, 4, 1, NULL, '2021-09-29 05:59:53'),
(8, 'RoyoXL', '', '', '', '', 2, 1, NULL, NULL),
(9, 'Royo Platinum', '', '', '', '', 3, 1, NULL, NULL),
(10, 'Royo Pool', '', '', '', '', 4, 1, NULL, NULL),
(11, 'Royo Moto', NULL, NULL, NULL, NULL, 5, 1, NULL, '2021-09-29 06:00:30'),
(12, 'RoyoXL', '', '', '', '', 2, 1, NULL, NULL),
(13, 'Royo Platinum', '', '', '', '', 3, 1, NULL, NULL),
(14, 'Royo Pool', '', '', '', '', 4, 1, NULL, NULL),
(15, 'Royo Moto', '', '', '', '', 5, 1, NULL, NULL),
(16, 'Royo Auto', NULL, NULL, NULL, NULL, 6, 1, NULL, '2021-09-29 06:00:48'),
(17, 'Royo Scooty', NULL, NULL, NULL, NULL, 7, 1, NULL, '2021-11-16 11:23:55');
-- --------------------------------------------------------

--
-- Dumping data for table `product_variants`
--
INSERT INTO `product_variants` (`id`, `sku`, `product_id`, `title`, `quantity`, `price`, `position`, `compare_at_price`, `barcode`, `cost_price`, `currency_id`, `tax_category_id`, `inventory_policy`, `fulfillment_service`, `inventory_management`, `created_at`, `updated_at`, `status`) VALUES
(1, 'sku-id', 1, NULL, 100, '500.00', 1, '500.00', '7543ebf012007e', '300.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 'sku-id-1*5', 1, 'sku-id-Black-Black', 100, '500.00', 1, '500.00', '1500cdf2d597df', '300.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 'sku-id-1*6', 1, 'sku-id-Black-Grey', 100, '500.00', 1, '500.00', '2ea56327679387', '300.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(4, 'sku-id-7*5', 1, 'sku-id-Medium-Black', 100, '500.00', 1, '500.00', '8f47f11a19433f', '300.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(5, 'sku-id-7*6', 1, 'sku-id-Medium-Grey', 100, '500.00', 1, '500.00', '8f7318b112bbe9', '300.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(6, 'CAB5841633700288e8bfec7', 2, NULL, 0, NULL, 1, NULL, 'f5ef8d8411347d', NULL, NULL, NULL, NULL, NULL, NULL, '2021-09-29 05:57:03', '2021-10-08 13:38:08', 1),
(7, 'CAB585', 3, NULL, 0, NULL, 1, NULL, '7efa6e0db89c45', NULL, NULL, NULL, NULL, NULL, NULL, '2021-09-29 05:57:03', '2021-11-16 10:39:05', 1),
(8, 'CAB586', 4, NULL, 0, NULL, 1, NULL, 'e54a09d0e86f42', NULL, NULL, NULL, NULL, NULL, NULL, '2021-09-29 05:57:03', '2021-11-16 10:39:28', 1),
(9, 'CAB587', 5, NULL, 0, NULL, 1, NULL, '897484ff818bea', NULL, NULL, NULL, NULL, NULL, NULL, '2021-09-29 05:57:03', '2021-11-16 10:39:48', 1),
(10, 'CAB588', 6, NULL, 0, NULL, 1, NULL, '8059d46f86c03a', NULL, NULL, NULL, NULL, NULL, NULL, '2021-09-29 05:57:03', '2021-11-16 10:40:09', 1),
(11, 'com.royoorders.gokab.GreenCab.RoyoScooty', 7, NULL, 0, NULL, 1, NULL, '764f8277978b51', NULL, NULL, NULL, NULL, NULL, NULL, '2021-11-16 11:20:55', '2021-11-16 11:23:55', 1);

-- --------------------------------------------------------

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
-- Dumping data for table `variant_translations`
--

INSERT INTO `variant_translations` (`id`, `title`, `variant_id`, `language_id`, `created_at`, `updated_at`) VALUES
(1, 'Size', 1, 1, NULL, NULL),
(2, 'Color', 2, 1, NULL, NULL),
(3, 'Phones', 3, 1, NULL, NULL);



-- --------------------------------------------------------

--
-- Dumping data for table `vendor_categories`
--
INSERT INTO `vendor_categories` (`id`, `vendor_id`, `category_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 14, 1, '2021-09-29 05:56:42', '2021-09-29 05:56:42'),
(2, 2, 15, 1, '2021-09-29 05:56:43', '2021-09-29 05:56:43'),
(3, 2, 16, 1, '2021-09-29 05:56:44', '2021-09-29 05:56:44');
-- --------------------------------------------------------

INSERT INTO `mobile_banners` (`id`, `name`, `description`, `image`, `validity_on`, `sorting`, `status`, `start_date_time`, `end_date_time`, `redirect_category_id`, `redirect_vendor_id`, `link`, `created_at`, `updated_at`) VALUES
(1, 'Taxi 1', NULL, 'banner/6MRxYyJHyo1JMLim7KDDp4aWbDkjMy9CpaPMvu4y.png', 0, 3, 1, '2021-09-29 11:47:00', '2025-09-30 12:00:00', NULL, 2, 'vendor', NULL, '2021-10-07 12:25:12'),
(2, 'Taxi 2', NULL, 'banner/v8zLyWDHUH7NuukbTFZgGKDYSsMOdORbsD6HSOqa.jpg', 0, 2, 1, '2021-09-29 11:49:00', '2025-09-30 12:00:00', NULL, 2, 'vendor', NULL, '2021-10-07 12:25:10'),
(3, 'Taxi 3', NULL, 'banner/4CR9wyiyFJoIr1RbqgGFiFa7m7hmJocnUGinbpuh.png', 1, 1, 1, '2021-09-29 11:50:00', '2025-09-30 12:00:00', NULL, 2, 'vendor', NULL, '2021-11-15 10:37:08');


INSERT INTO `cab_booking_layouts` (`id`, `title`, `slug`, `order_by`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Vendors', 'vendors', 1, 0, NULL, '2021-09-29 06:30:05'),
(2, 'Featured Products', 'featured_products', 2, 0, NULL, '2021-09-29 06:30:05'),
(3, 'New Products', 'new_products', 3, 0, NULL, '2021-09-29 06:30:05'),
(4, 'On Sale', 'on_sale', 4, 0, NULL, '2021-09-29 06:30:05'),
(5, 'Best Sellers', 'best_sellers', 5, 0, NULL, '2021-09-29 06:30:05'),
(6, 'Brands', 'brands', 6, 0, NULL, '2021-09-29 06:30:05'),
(7, 'Pickup Delivery', 'pickup_delivery', 7, 1, '2021-09-29 06:29:56', '2021-09-29 06:29:56'),
(8, 'Dynamic HTML', 'dynamic_page', 8, 1, '2021-10-14 01:05:38', '2021-10-14 01:05:38');

INSERT INTO `cab_booking_layout_categories` (`id`, `cab_booking_layout_id`, `category_id`, `created_at`, `updated_at`) VALUES
(2, 7, 14, '2021-09-29 06:30:05', '2021-09-29 06:30:05');


INSERT INTO `cab_booking_layout_transaltions` (`id`, `title`, `cab_booking_layout_id`, `language_id`, `created_at`, `updated_at`, `body_html`) VALUES
(1, NULL, 1, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(2, NULL, 2, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(3, NULL, 3, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(4, NULL, 4, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(5, NULL, 5, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(6, NULL, 6, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(7, NULL, 7, 1, '2021-09-29 06:30:05', '2021-09-29 06:30:05', NULL),
(8, NULL, 8, 1, '2021-10-14 01:06:39', '2021-10-14 01:06:39', '<div class=\"cab-content-area\">\n\n        <!-- Royo Business Start From Here -->\n        <section class=\"royo-business p-0\">\n            <div class=\"container p-64\">\n                <div class=\"row\">\n                    <div class=\"col-12\">\n                        <h2 class=\"title-36\">Royo for Business</h2>\n                        <div class=\"description-text\">\n                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Commodi, labore!</p>\n                        </div>\n                        <a class=\"btn btn-solid new-btn d-inline-block\" href=\"#\">See how</a>\n                    </div>\n                </div>\n            </div>\n        </section>\n\n        <!-- Royo Business Start From Here -->\n        <section class=\"royo-rental p-0\">\n            <div class=\"container\">                \n               \n                <div class=\"row align-items-center p-64\">\n                    <div class=\"col-sm-6\">\n                        <div class=\"cab-img-box\">\n                            <img class=\"img-fluid\" src=\"https://www.uber-assets.com/image/upload/f_auto,q_auto:eco,c_fill,w_1116,h_744/v1624484990/assets/fa/f20c42-425a-4243-866b-b480d3bd68b4/original/gettyimages-1139275491-2048x2048_With-Mask.png\" alt=\"\">\n                        </div>\n                    </div>\n                    <div class=\"offset-md-1 col-sm-6 col-md-5 pl-lg-4\">\n                        <div class=\"\">\n                            <h2 class=\"title-52\">Royo for Business</h2>\n                            <div class=\"description-text\">\n                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem nisi officiis numquam!</p>\n                            </div>\n                            <a class=\"learn-more bottom-line\" href=\"#\">Learn more</a>\n                        </div>\n                    </div>\n                </div>\n\n                <div class=\"row align-items-center p-64\">\n                    <div class=\"col-sm-6 order-md-1\">\n                        <div class=\"cab-img-box\">\n                            <img class=\"img-fluid\" src=\"https://www.uber-assets.com/image/upload/f_auto,q_auto:eco,c_fill,w_558,h_372/v1623719981/assets/4d/b05e4c-7340-40c4-a3e9-da0de41f14fc/original/rentals-iindia.jpg\" alt=\"\">\n                        </div>\n                    </div>\n                    <div class=\"col-sm-6 order-md-0\">\n                        <div class=\"pr-lg-5 mr-lg-5\">\n                            <h2 class=\"title-52\">Royo Intercity </h2>\n                            <div class=\"description-text\">\n                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem nisi officiis numquam!</p>\n                            </div>\n                            <a class=\"learn-more\" href=\"#\">Learn more</a>\n                        </div>\n                    </div>\n                </div>\n                \n            </div>\n        </section>\n\n        <!-- Focused On Safety Start From Here -->\n        <section class=\"focused-on-safety p-0\">\n            <div class=\"container p-64\">\n                <div class=\"row mb-4 pb-2\">\n                    <div class=\"col-12\">\n                        <div class=\"title-36\">Focused on safety, wherever you go</div>\n                    </div>\n                </div>\n                <div class=\"row\">\n                    <div class=\"col-md-6\">\n                        <div class=\"safety-box\">\n                            <div class=\"safety-img\">\n                                <img class=\"img-fluid\" src=\"https://www.uber-assets.com/image/upload/f_auto,q_auto:eco,c_fill,w_558,h_372/v1613520218/assets/3e/e98625-31e6-4536-8646-976a1ee3f210/original/Safety_Home_Img2x.png\" alt=\"\">\n                            </div>\n                            <div class=\"safety-content\">\n                                <h3 class=\"mt-0\">Our commitment to your safety</h3>\n                                <div class=\"safety-text\">\n                                    <p>With every safety feature and every standard in our Community Guidelines, we\'re committed to helping to create a safe environment for our users.</p>\n                                </div>\n                                <div class=\"safety-links\">\n                                    <a class=\"bottom-line\" href=\"#\">\n                                        <span>Read about our Community Guidelines</span>\n                                    </a>\n                                    <a class=\"bottom-line\" href=\"#\">\n                                        <span>See all safety features</span>\n                                    </a>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"col-md-6\">\n                        <div class=\"safety-box\">\n                            <div class=\"safety-img\">\n                                <img class=\"img-fluid\" src=\"https://www.uber-assets.com/image/upload/f_auto,q_auto:eco,c_fill,w_558,h_372/v1613520218/assets/3e/e98625-31e6-4536-8646-976a1ee3f210/original/Safety_Home_Img2x.png\" alt=\"\">\n                            </div>\n                            <div class=\"safety-content\">\n                                <h3 class=\"mt-0\">Setting 10,000+ cities in motion</h3>\n                                <div class=\"safety-text\">\n                                    <p>With every safety feature and every standard in our Community Guidelines, we\'re committed to helping to create a safe environment for our users.</p>\n                                </div>\n                                <div class=\"safety-links\">\n                                    <a class=\"bottom-line\" href=\"#\">\n                                        <span>View all cities</span>\n                                    </a>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </section>\n\n    </div>');




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



INSERT INTO `app_styling_options` (`id`, `app_styling_id`, `name`, `image`, `is_selected`, `created_at`, `updated_at`, `template_id`) VALUES
(1, 1, 'CircularStd-Book', NULL, 1, NULL, NULL, NULL),
(2, 1, 'SFProText-Regular', NULL, 0, NULL, NULL, NULL),
(3, 1, 'Futura-Normal', NULL, 0, NULL, NULL, NULL),
(4, 1, 'Eina02-Regular', NULL, 0, NULL, NULL, NULL),
(5, 2, 'CircularStd-Medium', NULL, 1, NULL, NULL, NULL),
(6, 2, 'SFProText-Medium', NULL, 0, NULL, NULL, NULL),
(7, 2, 'Futura-Medium', NULL, 0, NULL, NULL, NULL),
(8, 2, 'Eina02-SemiBold', NULL, 0, NULL, NULL, NULL),
(9, 3, 'CircularStd-Bold', NULL, 1, NULL, NULL, NULL),
(10, 3, 'SFProText-Bold', NULL, 0, NULL, NULL, NULL),
(11, 3, 'FuturaBT-Heavy', NULL, 0, NULL, NULL, NULL),
(12, 3, 'Eina02-Bold', NULL, 0, NULL, NULL, NULL),
(13, 4, '#2C59F7', NULL, 1, NULL, '2021-10-28 16:14:25', NULL),
(14, 5, '#fff', NULL, 1, NULL, NULL, NULL),
(15, 6, '#fff', NULL, 1, NULL, NULL, NULL),
(16, 7, 'Tab 1', 'bar.png', 0, NULL, '2021-10-28 16:14:03', 1),
(17, 7, 'Tab 2', 'bar_two.png', 0, NULL, '2021-10-28 16:14:03', 2),
(18, 7, 'Tab 3', 'bar_three.png', 0, NULL, '2021-10-28 16:14:03', 3),
(19, 7, 'Tab 4', 'bar_four.png', 1, NULL, '2021-10-28 16:14:03', 4),
(20, 7, 'Tab 5', 'bar_five.png', 0, NULL, '2021-10-28 16:14:03', 5),
(21, 8, 'Home Page 1', 'home.png', 0, NULL, '2021-10-13 05:58:00', 1),
(22, 8, 'Home Page 4', 'home_four.png', 0, NULL, '2021-10-13 05:58:00', 2),
(23, 8, 'Home Page 5', 'home_five.png', 0, NULL, '2021-10-13 05:58:00', 3),
(24, 9, 'Create a free account and join us!', NULL, 1, NULL, NULL, NULL),
(25, 8, 'Home Page 6', 'home_six.png', 1, '2021-10-12 14:10:13', '2021-10-13 05:58:00', 4);


INSERT INTO `loyalty_cards` (`id`, `name`, `description`, `image`, `minimum_points`, `per_order_minimum_amount`, `per_order_points`, `per_purchase_minimum_amount`, `amount_per_loyalty_point`, `redeem_points_per_primary_currency`, `status`, `created_at`, `updated_at`, `loyalty_check`) VALUES
(1, 'Gold Plan', 'Gold Loyalty Card', '2f3120/loyalty/image/im5953PjFoo5xub5X4JKes2yV2CwnoAaBiy8ACh1.png', 400, NULL, 5, NULL, 10, 10, '0', '2021-11-16 05:03:53', '2021-11-16 05:15:49', '0'),
(2, 'Silver Plan', 'Silver Loyalty Card', '2f3120/loyalty/image/EAJdZtUl3sjzDLyvZfAjadapVc1S3eAQBSAqvjbr.png', 600, NULL, 8, NULL, 14, 10, '0', '2021-11-16 05:04:29', '2021-11-16 05:15:49', '0'),
(3, 'Platinum Plan', 'Platinum Loyalty Card', '2f3120/loyalty/image/rHwJcu9Q1NWp7TXnANRWoBOhdlWBPVbrBZgS2w1g.png', 800, NULL, 10, NULL, 20, 10, '0', '2021-11-16 05:07:24', '2021-11-16 05:15:49', '0');


UPDATE `client_preferences` SET `business_type` = 'taxi' WHERE `client_preferences`.`id` = 1;
UPDATE `client_preferences` SET `is_hyperlocal` = 0 WHERE `client_preferences`.`id` = 1;


UPDATE `app_styling_options` SET `is_selected` = 0 WHERE `app_styling_options`.`app_styling_id` = 8;

UPDATE `app_styling_options` SET `is_selected` = 1 WHERE `app_styling_options`.`image` = 'home_six.png';

INSERT INTO `service_areas` (`id`, `name`, `description`, `geo_array`, `zoom_level`, `polygon`, `vendor_id`, `created_at`, `updated_at`) VALUES
(1, 'Chandigarh', 'Chandigarh', '(30.660824531495372, 76.74525653768939),(30.728430656837848, 76.63745319296282),(30.788321358708448, 76.63779651571673),(30.807490307216355, 76.71641742636126),(30.80866980994827, 76.77306568075579),(30.778293001541996, 76.83074390341204),(30.71219769922636, 76.8671361153261),(30.683857262849905, 76.87503253866595),(30.664073069800885, 76.82593738485735)', 12, 0x000000000103000000010000000a000000ca2ae7cb2ba93e4064207a48b22f5340b985786e7aba3e4064207a08cc2853401d64b66dcfc93e4064207aa8d1285340cc544dafb7ce3e4064207ac8d92d534088fd12fc04cf3e4064207ae8793153401c54cc353ec73e4064207ae82a355340b276a29652b63e4064207a287f3753408a12034511af3e4064207a8800385340faf254b100aa3e4064207a28dc345340ca2ae7cb2ba93e4064207a48b22f5340, 2, '2022-01-20 07:12:19', '2022-01-20 07:12:19');

UPDATE `clients` SET `logo` = 'Clientlogo/61e90b1a6c483.png' WHERE  1;
