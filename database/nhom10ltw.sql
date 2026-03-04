-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 05:13 PM
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
-- Database: `nhom11ltw`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `admin_name` varchar(255) NOT NULL COMMENT 'Tên admin/nhân viên',
  `action` varchar(50) NOT NULL COMMENT 'Loại hành động: CREATE, UPDATE, DELETE, VIEW',
  `table_name` varchar(100) NOT NULL COMMENT 'Tên bảng được thao tác',
  `record_id` int(11) DEFAULT NULL COMMENT 'ID của record được thao tác',
  `old_values` text DEFAULT NULL COMMENT 'Giá trị cũ (cho UPDATE/DELETE)',
  `new_values` text DEFAULT NULL COMMENT 'Giá trị mới (cho CREATE/UPDATE)',
  `description` text DEFAULT NULL COMMENT 'Mô tả chi tiết về hành động',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Địa chỉ IP của người thực hiện',
  `user_agent` text DEFAULT NULL COMMENT 'Thông tin trình duyệt',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu trữ log hoạt động của nhân viên';

-- --------------------------------------------------------

--
-- Table structure for table `audit_log_relations`
--

CREATE TABLE `audit_log_relations` (
  `id` int(11) NOT NULL,
  `audit_log_id` int(11) NOT NULL,
  `related_table` varchar(100) NOT NULL,
  `related_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'ID của admin tạo danh mục',
  `updated_by` int(11) DEFAULT NULL COMMENT 'ID của admin cập nhật danh mục cuối cùng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`, `image`, `created_at`, `created_by`, `updated_by`) VALUES
(10, 'Áo ', 'Áo-34', '', 0, '1762876198.jpg', '2025-11-11 15:49:58', NULL, NULL),
(11, 'Giày', 'giày-78', '', 0, '1762882715.jpg', '2025-11-11 17:38:35', NULL, NULL),
(12, 'Áo Sơ Mi', 'ao-so-mi-12', '', 0, '1762883683.jpeg', '2025-11-11 17:54:43', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `voucher_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 2,
  `total_amount` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_note` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `processed_by` int(11) DEFAULT NULL COMMENT 'ID của admin xử lý đơn hàng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `voucher_id`, `status`, `total_amount`, `created_at`, `customer_name`, `customer_phone`, `customer_address`, `customer_email`, `customer_note`, `payment_method`, `processed_by`) VALUES
(13, NULL, NULL, 4, 300000, '2025-11-10 08:02:31', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL),
(14, NULL, NULL, 5, 20000, '2025-11-10 08:14:31', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD) | Ghi chú: giao thứu 2', 'COD', NULL),
(15, NULL, NULL, 4, 300000, '2025-11-10 08:42:57', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL),
(16, NULL, NULL, 4, 6000000, '2025-11-10 09:13:57', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL),
(17, NULL, NULL, 4, 1000000, '2025-11-10 09:16:11', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL),
(18, NULL, NULL, 4, 10000, '2025-11-10 10:03:42', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Chuyển khoản ngân hàng', 'COD', NULL),
(19, NULL, NULL, 4, 1010000, '2025-11-10 10:20:15', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL),
(20, NULL, NULL, 4, 3000000, '2025-11-11 15:09:48', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Chuyển khoản ngân hàng (MB Bank - 0000955063080 - Nguyen Tran Tuan Phat)', 'COD', NULL),
(21, NULL, NULL, 4, 10000, '2025-11-11 15:16:16', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Chuyển khoản ngân hàng (MB Bank - 0000955063080 - Nguyen Tran Tuan Phat)', 'COD', NULL),
(22, NULL, NULL, 6, 4300000, '2025-11-11 18:13:50', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL),
(23, NULL, NULL, 2, 6600000, '2025-11-11 18:53:13', 'nguyễn trần Tuấn Phát', '1234563241', '439 lê văn quới, bình trị đông a, quận bình tân, Thành phố hồ chí minh', 'tuanphat.htht@gmail.com', 'Phương thức thanh toán: Thanh toán khi nhận hàng (COD)', 'COD', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_detail`
--

CREATE TABLE `order_detail` (
  `id` bigint(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `product_size` varchar(50) DEFAULT NULL,
  `rate` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `comment` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_detail`
--

INSERT INTO `order_detail` (`id`, `product_id`, `order_id`, `user_id`, `quantity`, `price`, `product_size`, `rate`, `status`, `comment`, `created_at`) VALUES
(5, 8, 13, NULL, 3, 100000, NULL, NULL, 4, NULL, '2025-11-10 08:02:31'),
(6, 3, 14, NULL, 2, 10000, NULL, NULL, 5, NULL, '2025-11-10 08:14:31'),
(7, 8, 15, NULL, 3, 100000, NULL, NULL, 4, NULL, '2025-11-10 08:42:57'),
(8, 10, 16, NULL, 6, 1000000, '', NULL, 4, NULL, '2025-11-10 09:13:57'),
(9, 11, 17, NULL, 1, 1000000, '', NULL, 4, NULL, '2025-11-10 09:16:11'),
(10, 17, 18, NULL, 1, 10000, 'L', NULL, 4, NULL, '2025-11-10 10:03:42'),
(11, 17, 19, NULL, 1, 10000, 'M', NULL, 4, NULL, '2025-11-10 10:20:15'),
(12, 18, 19, NULL, 1, 1000000, '42', NULL, 4, NULL, '2025-11-10 10:20:15'),
(13, 18, 20, NULL, 2, 1000000, '40', NULL, 4, NULL, '2025-11-11 15:09:48'),
(14, 18, 20, NULL, 1, 1000000, '42', NULL, 4, NULL, '2025-11-11 15:09:48'),
(15, 17, 21, NULL, 1, 10000, 'S', NULL, 4, NULL, '2025-11-11 15:16:16'),
(16, 29, 22, NULL, 2, 2000000, '41', NULL, 6, NULL, '2025-11-11 18:13:50'),
(17, 24, 22, NULL, 1, 300000, 'M', NULL, 6, NULL, '2025-11-11 18:13:50'),
(18, 24, 23, NULL, 1, 300000, 'M', NULL, 2, NULL, '2025-11-11 18:53:13'),
(19, 24, 23, NULL, 1, 300000, 'S', NULL, 2, NULL, '2025-11-11 18:53:13'),
(20, 25, 23, NULL, 2, 2000000, '41', NULL, 2, NULL, '2025-11-11 18:53:13'),
(21, 25, 23, NULL, 1, 2000000, '42', NULL, 2, NULL, '2025-11-11 18:53:13');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL COMMENT 'Module: products, categories, orders, users, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `module`, `created_at`) VALUES
(1, 'view_dashboard', 'Xem Dashboard', 'Xem trang tổng quan', 'dashboard', '2025-10-22 18:05:10'),
(2, 'view_products', 'Xem sản phẩm', 'Xem danh sách sản phẩm', 'products', '2025-10-22 18:05:10'),
(3, 'create_products', 'Tạo sản phẩm', 'Thêm sản phẩm mới', 'products', '2025-10-22 18:05:10'),
(4, 'edit_products', 'Sửa sản phẩm', 'Chỉnh sửa sản phẩm', 'products', '2025-10-22 18:05:10'),
(5, 'delete_products', 'Xóa sản phẩm', 'Xóa sản phẩm', 'products', '2025-10-22 18:05:10'),
(6, 'view_categories', 'Xem danh mục', 'Xem danh sách danh mục', 'categories', '2025-10-22 18:05:10'),
(7, 'create_categories', 'Tạo danh mục', 'Thêm danh mục mới', 'categories', '2025-10-22 18:05:10'),
(8, 'edit_categories', 'Sửa danh mục', 'Chỉnh sửa danh mục', 'categories', '2025-10-22 18:05:10'),
(9, 'delete_categories', 'Xóa danh mục', 'Xóa danh mục', 'categories', '2025-10-22 18:05:10'),
(10, 'view_orders', 'Xem đơn hàng', 'Xem danh sách đơn hàng', 'orders', '2025-10-22 18:05:10'),
(11, 'edit_orders', 'Sửa đơn hàng', 'Cập nhật trạng thái đơn hàng', 'orders', '2025-10-22 18:05:10'),
(12, 'view_users', 'Xem người dùng', 'Xem danh sách người dùng', 'users', '2025-10-22 18:05:10'),
(13, 'create_users', 'Tạo người dùng', 'Thêm người dùng mới', 'users', '2025-10-22 18:05:10'),
(14, 'edit_users', 'Sửa người dùng', 'Chỉnh sửa thông tin người dùng', 'users', '2025-10-22 18:05:10'),
(15, 'delete_users', 'Xóa người dùng', 'Xóa người dùng', 'users', '2025-10-22 18:05:10'),
(16, 'view_vouchers', 'Xem voucher', 'Xem danh sách voucher', 'vouchers', '2025-10-22 18:05:10'),
(17, 'create_vouchers', 'Tạo voucher', 'Thêm voucher mới', 'vouchers', '2025-10-22 18:05:10'),
(18, 'edit_vouchers', 'Sửa voucher', 'Chỉnh sửa voucher', 'vouchers', '2025-10-22 18:05:10'),
(19, 'delete_vouchers', 'Xóa voucher', 'Xóa voucher', 'vouchers', '2025-10-22 18:05:10'),
(20, 'view_audit_log', 'Xem lịch sử', 'Xem lịch sử hoạt động', 'audit', '2025-10-22 18:05:10'),
(21, 'view_reports', 'Xem báo cáo', 'Xem các báo cáo thống kê', 'reports', '2025-10-22 18:05:10'),
(22, 'manage_settings', 'Quản lý cài đặt', 'Cài đặt hệ thống', 'settings', '2025-10-22 18:05:10'),
(23, 'manage_roles', 'Quản lý vai trò', 'Quản lý vai trò và quyền', 'roles', '2025-10-22 18:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `small_description` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `original_price` int(11) NOT NULL,
  `selling_price` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'ID của admin tạo sản phẩm',
  `updated_by` int(11) DEFAULT NULL COMMENT 'ID của admin cập nhật sản phẩm cuối cùng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `small_description`, `description`, `original_price`, `selling_price`, `image`, `qty`, `size`, `color`, `status`, `deleted_at`, `created_at`, `created_by`, `updated_by`) VALUES
(20, 10, 'Áo Dream Maker-Trắng', 'ao-dream-maker-trang-83-22-52-91-13', 'Áo form oversize màu trắng', 'Form semi-oversized được cải tiến và kết hợp từ form oversized và boxy. Với độ dài dưới thắt lưng và chiều rộng vừa phải, semi-oversized là sự lựa chọn hoàn hảo dành cho những khách hàng cần sự thoải mái nhưng cũng quan tâm nhiều đến form dáng.', 450000, 300000, '1762882024.jpeg', 0, '', NULL, 0, NULL, '2025-11-11 17:27:04', NULL, NULL),
(21, 10, 'Áo Dream Maker-Đen chữ trắng', 'ao-dream-maker-den-chu-trang-35', 'Áo form oversize', 'Form semi-oversized được cải tiến và kết hợp từ form oversized và boxy. Với độ dài dưới thắt lưng và chiều rộng vừa phải, semi-oversized là sự lựa chọn hoàn hảo dành cho những khách hàng cần sự thoải mái nhưng cũng quan tâm nhiều đến form dáng.', 450000, 300000, '1762882338.jpeg', 0, '', NULL, 0, NULL, '2025-11-11 17:32:18', NULL, NULL),
(22, 10, 'Áo Dream Maker-Đen Hồng', 'ao-dream-maker-den-hong-21', 'áo form oversize đen hồng', 'Form semi-oversized được cải tiến và kết hợp từ form oversized và boxy. Với độ dài dưới thắt lưng và chiều rộng vừa phải, semi-oversized là sự lựa chọn hoàn hảo dành cho những khách hàng cần sự thoải mái nhưng cũng quan tâm nhiều đến form dáng.', 450000, 300000, '1762882483.jpeg', 0, '', NULL, 0, NULL, '2025-11-11 17:34:43', NULL, NULL),
(23, 10, 'Áo Dream Maker-Trắng Đỏ', 'ao-dream-maker-trang-do-56', 'áo form oversize', 'Form semi-oversized được cải tiến và kết hợp từ form oversized và boxy. Với độ dài dưới thắt lưng và chiều rộng vừa phải, semi-oversized là sự lựa chọn hoàn hảo dành cho những khách hàng cần sự thoải mái nhưng cũng quan tâm nhiều đến form dáng.', 450000, 300000, '1762882583.jpeg', 0, '', NULL, 0, NULL, '2025-11-11 17:36:23', NULL, NULL),
(24, 10, 'Áo Dream Maker-Xám Đỏ', 'ao-dream-maker-xam-do-13', 'áo form oversize', 'Form semi-oversized được cải tiến và kết hợp từ form oversized và boxy. Với độ dài dưới thắt lưng và chiều rộng vừa phải, semi-oversized là sự lựa chọn hoàn hảo dành cho những khách hàng cần sự thoải mái nhưng cũng quan tâm nhiều đến form dáng.', 450000, 300000, '1762882661.jpeg', 0, '', NULL, 0, NULL, '2025-11-11 17:37:41', NULL, NULL),
(25, 11, 'AIR JORDAN 1 LOW CARDINAL RED', 'air-jordan-1-low-cardinal-red-83-66', 'Jordan 1 low', 'Air Jordan 1 là đôi giày mang tính biểu tượng trong lịch sử sneaker, được Nike ra mắt lần đầu vào năm 1985 cho huyền thoại bóng rổ Michael Jordan. Thiết kế của Jordan 1 kết hợp giữa phong cách cổ điển và hiện đại, thể hiện tinh thần năng động, cá tính và tự do.', 3000000, 2000000, '1762882947.webp', 0, '', NULL, 0, NULL, '2025-11-11 17:42:27', NULL, NULL),
(26, 11, 'Air Jordan 1 Low ‘Pink Foam’', 'air-jordan-1-low-pink-foam-29', 'Jordan 1 low', 'Air Jordan 1 là đôi giày mang tính biểu tượng trong lịch sử sneaker, được Nike ra mắt lần đầu vào năm 1985 cho huyền thoại bóng rổ Michael Jordan. Thiết kế của Jordan 1 kết hợp giữa phong cách cổ điển và hiện đại, thể hiện tinh thần năng động, cá tính và tự do.', 3000000, 1000000, '1762883185.webp', 0, '', NULL, 0, NULL, '2025-11-11 17:46:25', NULL, NULL),
(27, 11, 'Samba OG ‘Black’ ', 'samba-og-black--53', 'Samba OG', 'Adidas Samba là mẫu giày biểu tượng ra đời từ những năm 1950, ban đầu được thiết kế cho các cầu thủ bóng đá tập luyện trên mặt sân lạnh và trơn trượt. Với thiết kế cổ điển, thanh lịch và dễ phối đồ, Samba nhanh chóng trở thành một biểu tượng thời trang đường phố.', 3000000, 1000000, '1762883292.webp', 0, '', NULL, 0, NULL, '2025-11-11 17:48:12', NULL, NULL),
(28, 11, 'Samba OG ‘White’ ', 'samba-og-white--39', 'Samba OG', 'Adidas Samba là mẫu giày biểu tượng ra đời từ những năm 1950, ban đầu được thiết kế cho các cầu thủ bóng đá tập luyện trên mặt sân lạnh và trơn trượt. Với thiết kế cổ điển, thanh lịch và dễ phối đồ, Samba nhanh chóng trở thành một biểu tượng thời trang đường phố.', 3000000, 2000000, '1762883382.webp', 0, '', NULL, 0, NULL, '2025-11-11 17:49:42', NULL, NULL),
(29, 11, ' adiFOM Superstar \'Core White\'', '-adifom-superstar-core-white-85', ' adiFOM Superstar \'Core White\'', ' adiFOM Superstar \'Core White\'', 3000000, 2000000, '1762883444.webp', 0, '', NULL, 0, NULL, '2025-11-11 17:50:44', NULL, NULL),
(30, 12, 'Striped Boxy Long Sleeve Shirt', 'striped-boxy-long-sleeve-shirt-57', 'Striped Boxy Long Sleeve Shirt', 'Striped Boxy Long Sleeve Shirt', 300000, 200000, '1762883741.jpeg', 0, '', NULL, 0, NULL, '2025-11-11 17:55:41', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `alt_text` varchar(255) NOT NULL,
  `is_main` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `alt_text`, `is_main`, `created_at`) VALUES
(32, 20, '1762882024_0.jpeg', 'Áo Dream Maker-Trắng', 1, '2025-11-11 17:27:04'),
(33, 20, '1762882024_1.jpeg', 'Áo Dream Maker-Trắng', 0, '2025-11-11 17:27:04'),
(34, 20, '1762882024_2.jpeg', 'Áo Dream Maker-Trắng', 0, '2025-11-11 17:27:04'),
(35, 21, '1762882338_0.jpeg', 'Áo Dream Maker-Đen chữ trắng', 1, '2025-11-11 17:32:18'),
(36, 21, '1762882338_1.jpeg', 'Áo Dream Maker-Đen chữ trắng', 0, '2025-11-11 17:32:18'),
(37, 21, '1762882338_2.jpeg', 'Áo Dream Maker-Đen chữ trắng', 0, '2025-11-11 17:32:18'),
(38, 22, '1762882483_0.jpeg', 'Áo Dream Maker-Đen Hồng', 1, '2025-11-11 17:34:43'),
(39, 22, '1762882483_1.jpeg', 'Áo Dream Maker-Đen Hồng', 0, '2025-11-11 17:34:43'),
(40, 22, '1762882483_2.jpeg', 'Áo Dream Maker-Đen Hồng', 0, '2025-11-11 17:34:43'),
(41, 22, '1762882483_3.jpeg', 'Áo Dream Maker-Đen Hồng', 0, '2025-11-11 17:34:43'),
(42, 22, '1762882483_4.jpeg', 'Áo Dream Maker-Đen Hồng', 0, '2025-11-11 17:34:43'),
(43, 23, '1762882583_0.jpeg', 'Áo Dream Maker-Trắng Đỏ', 1, '2025-11-11 17:36:23'),
(44, 23, '1762882583_1.jpeg', 'Áo Dream Maker-Trắng Đỏ', 0, '2025-11-11 17:36:23'),
(45, 24, '1762882661_0.jpeg', 'Áo Dream Maker-Xám Đỏ', 1, '2025-11-11 17:37:41'),
(46, 24, '1762882661_1.jpeg', 'Áo Dream Maker-Xám Đỏ', 0, '2025-11-11 17:37:41'),
(47, 24, '1762882661_2.jpeg', 'Áo Dream Maker-Xám Đỏ', 0, '2025-11-11 17:37:41'),
(48, 24, '1762882661_3.jpeg', 'Áo Dream Maker-Xám Đỏ', 0, '2025-11-11 17:37:41'),
(49, 25, '1762882947_0.webp', 'AIR JORDAN 1 LOW CARDINAL RED', 1, '2025-11-11 17:42:27'),
(50, 25, '1762882947_1.webp', 'AIR JORDAN 1 LOW CARDINAL RED', 0, '2025-11-11 17:42:27'),
(51, 25, '1762882947_2.webp', 'AIR JORDAN 1 LOW CARDINAL RED', 0, '2025-11-11 17:42:27'),
(52, 26, '1762883185_0.webp', 'Air Jordan 1 Low ‘Pink Foam’', 1, '2025-11-11 17:46:25'),
(53, 26, '1762883185_1.webp', 'Air Jordan 1 Low ‘Pink Foam’', 0, '2025-11-11 17:46:25'),
(54, 26, '1762883185_2.webp', 'Air Jordan 1 Low ‘Pink Foam’', 0, '2025-11-11 17:46:25'),
(55, 26, '1762883185_3.webp', 'Air Jordan 1 Low ‘Pink Foam’', 0, '2025-11-11 17:46:25'),
(56, 26, '1762883185_4.webp', 'Air Jordan 1 Low ‘Pink Foam’', 0, '2025-11-11 17:46:25'),
(57, 27, '1762883292_0.webp', 'Samba OG ‘Black’ ', 1, '2025-11-11 17:48:12'),
(58, 27, '1762883292_1.webp', 'Samba OG ‘Black’ ', 0, '2025-11-11 17:48:12'),
(59, 27, '1762883292_2.webp', 'Samba OG ‘Black’ ', 0, '2025-11-11 17:48:12'),
(60, 28, '1762883382_0.webp', 'Samba OG ‘White’ ', 1, '2025-11-11 17:49:42'),
(61, 28, '1762883382_1.webp', 'Samba OG ‘White’ ', 0, '2025-11-11 17:49:42'),
(62, 28, '1762883382_2.webp', 'Samba OG ‘White’ ', 0, '2025-11-11 17:49:42'),
(63, 28, '1762883382_3.webp', 'Samba OG ‘White’ ', 0, '2025-11-11 17:49:42'),
(64, 28, '1762883382_4.webp', 'Samba OG ‘White’ ', 0, '2025-11-11 17:49:42'),
(65, 29, '1762883444_0.webp', ' adiFOM Superstar \'Core White\'', 1, '2025-11-11 17:50:44'),
(66, 29, '1762883444_1.webp', ' adiFOM Superstar \'Core White\'', 0, '2025-11-11 17:50:44'),
(67, 29, '1762883444_2.webp', ' adiFOM Superstar \'Core White\'', 0, '2025-11-11 17:50:44'),
(68, 29, '1762883444_3.webp', ' adiFOM Superstar \'Core White\'', 0, '2025-11-11 17:50:44'),
(69, 30, '1762883741_0.jpeg', 'Striped Boxy Long Sleeve Shirt', 1, '2025-11-11 17:55:41'),
(70, 30, '1762883741_1.jpeg', 'Striped Boxy Long Sleeve Shirt', 0, '2025-11-11 17:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`, `quantity`, `created_at`) VALUES
(21, 20, 'S', 2, '2025-11-11 17:30:59'),
(22, 20, 'M', 3, '2025-11-11 17:30:59'),
(23, 20, 'L', 6, '2025-11-11 17:30:59'),
(24, 20, 'XL', 9, '2025-11-11 17:30:59'),
(25, 21, 'S', 2, '2025-11-11 17:32:18'),
(26, 21, 'M', 4, '2025-11-11 17:32:18'),
(27, 21, 'L', 6, '2025-11-11 17:32:18'),
(28, 21, 'XL', 7, '2025-11-11 17:32:18'),
(29, 22, 'S', 0, '2025-11-11 17:34:43'),
(30, 22, 'M', 0, '2025-11-11 17:34:43'),
(31, 22, 'L', 0, '2025-11-11 17:34:43'),
(32, 22, 'XL', 0, '2025-11-11 17:34:43'),
(33, 23, 'S', 25, '2025-11-11 17:36:23'),
(34, 23, 'M', 32, '2025-11-11 17:36:23'),
(35, 23, 'L', 5, '2025-11-11 17:36:23'),
(36, 23, 'XL', 8, '2025-11-11 17:36:23'),
(37, 24, 'S', 23, '2025-11-11 17:37:41'),
(38, 24, 'M', 29, '2025-11-11 17:37:41'),
(39, 24, 'L', 40, '2025-11-11 17:37:41'),
(40, 24, 'XL', 60, '2025-11-11 17:37:41'),
(41, 25, '36', 0, '2025-11-11 17:43:22'),
(42, 25, '37', 0, '2025-11-11 17:43:22'),
(43, 25, '38', 0, '2025-11-11 17:43:22'),
(44, 25, '39', 0, '2025-11-11 17:43:22'),
(45, 25, '40', 0, '2025-11-11 17:43:22'),
(46, 25, '41', 0, '2025-11-11 17:43:22'),
(47, 25, '42', 3, '2025-11-11 17:43:22'),
(48, 25, '43', 0, '2025-11-11 17:43:22'),
(49, 25, '44', 3, '2025-11-11 17:43:22'),
(50, 26, '36', 0, '2025-11-11 17:46:25'),
(51, 26, '37', 0, '2025-11-11 17:46:25'),
(52, 26, '38', 0, '2025-11-11 17:46:25'),
(53, 26, '39', 0, '2025-11-11 17:46:25'),
(54, 26, '40', 2, '2025-11-11 17:46:25'),
(55, 26, '41', 4, '2025-11-11 17:46:25'),
(56, 26, '42', 5, '2025-11-11 17:46:25'),
(57, 26, '43', 2, '2025-11-11 17:46:25'),
(58, 26, '44', 2, '2025-11-11 17:46:25'),
(59, 27, '36', 0, '2025-11-11 17:48:12'),
(60, 27, '37', 0, '2025-11-11 17:48:12'),
(61, 27, '38', 0, '2025-11-11 17:48:12'),
(62, 27, '39', 0, '2025-11-11 17:48:12'),
(63, 27, '40', 0, '2025-11-11 17:48:12'),
(64, 27, '41', 5, '2025-11-11 17:48:12'),
(65, 27, '42', 0, '2025-11-11 17:48:12'),
(66, 27, '43', 0, '2025-11-11 17:48:12'),
(67, 27, '44', 0, '2025-11-11 17:48:12'),
(68, 28, '36', 0, '2025-11-11 17:49:42'),
(69, 28, '37', 0, '2025-11-11 17:49:42'),
(70, 28, '38', 0, '2025-11-11 17:49:42'),
(71, 28, '39', 0, '2025-11-11 17:49:42'),
(72, 28, '40', 0, '2025-11-11 17:49:42'),
(73, 28, '41', 0, '2025-11-11 17:49:42'),
(74, 28, '42', 0, '2025-11-11 17:49:42'),
(75, 28, '43', 0, '2025-11-11 17:49:42'),
(76, 28, '44', 0, '2025-11-11 17:49:42'),
(77, 29, '36', 0, '2025-11-11 17:50:44'),
(78, 29, '37', 0, '2025-11-11 17:50:44'),
(79, 29, '38', 0, '2025-11-11 17:50:44'),
(80, 29, '39', 0, '2025-11-11 17:50:44'),
(81, 29, '40', 0, '2025-11-11 17:50:44'),
(82, 29, '41', 5, '2025-11-11 17:50:44'),
(83, 29, '42', 0, '2025-11-11 17:50:44'),
(84, 29, '43', 0, '2025-11-11 17:50:44'),
(85, 29, '44', 0, '2025-11-11 17:50:44'),
(86, 30, 'S', 0, '2025-11-11 17:55:41'),
(87, 30, 'M', 0, '2025-11-11 17:55:41'),
(88, 30, 'L', 0, '2025-11-11 17:55:41'),
(89, 30, 'XL', 0, '2025-11-11 17:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON chứa các quyền',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Quản trị viên', 'Có toàn quyền trong hệ thống', '[\"all\"]', '2025-10-22 18:05:10', '2025-10-22 18:05:10'),
(2, 'employee', 'Nhân viên', 'Nhân viên với quyền hạn chế', '[\"view_dashboard\", \"view_products\", \"create_products\", \"edit_products\", \"view_categories\", \"create_categories\", \"edit_categories\", \"view_orders\", \"edit_orders\", \"view_vouchers\", \"create_vouchers\", \"edit_vouchers\"]', '2025-10-22 18:05:10', '2025-10-22 18:05:10'),
(3, 'sales_employee', 'Nhân viên bán hàng', 'Chỉ quản lý đơn hàng và khách hàng', '[\"view_dashboard\", \"view_products\", \"view_categories\", \"view_orders\", \"edit_orders\", \"view_users\"]', '2025-10-22 18:05:10', '2025-10-22 18:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 7, '2025-10-22 18:05:10'),
(2, 1, 3, '2025-10-22 18:05:10'),
(3, 1, 13, '2025-10-22 18:05:10'),
(4, 1, 17, '2025-10-22 18:05:10'),
(5, 1, 9, '2025-10-22 18:05:10'),
(6, 1, 5, '2025-10-22 18:05:10'),
(7, 1, 15, '2025-10-22 18:05:10'),
(8, 1, 19, '2025-10-22 18:05:10'),
(9, 1, 8, '2025-10-22 18:05:10'),
(10, 1, 11, '2025-10-22 18:05:10'),
(11, 1, 4, '2025-10-22 18:05:10'),
(12, 1, 14, '2025-10-22 18:05:10'),
(13, 1, 18, '2025-10-22 18:05:10'),
(14, 1, 23, '2025-10-22 18:05:10'),
(15, 1, 22, '2025-10-22 18:05:10'),
(16, 1, 20, '2025-10-22 18:05:10'),
(17, 1, 6, '2025-10-22 18:05:10'),
(18, 1, 1, '2025-10-22 18:05:10'),
(19, 1, 10, '2025-10-22 18:05:10'),
(20, 1, 2, '2025-10-22 18:05:10'),
(21, 1, 21, '2025-10-22 18:05:10'),
(22, 1, 12, '2025-10-22 18:05:10'),
(23, 1, 16, '2025-10-22 18:05:10'),
(32, 2, 7, '2025-10-22 18:05:10'),
(33, 2, 3, '2025-10-22 18:05:10'),
(34, 2, 17, '2025-10-22 18:05:10'),
(35, 2, 8, '2025-10-22 18:05:10'),
(36, 2, 11, '2025-10-22 18:05:10'),
(37, 2, 4, '2025-10-22 18:05:10'),
(38, 2, 18, '2025-10-22 18:05:10'),
(39, 2, 6, '2025-10-22 18:05:10'),
(40, 2, 1, '2025-10-22 18:05:10'),
(41, 2, 10, '2025-10-22 18:05:10'),
(42, 2, 2, '2025-10-22 18:05:10'),
(43, 2, 16, '2025-10-22 18:05:10'),
(47, 3, 11, '2025-10-22 18:05:10'),
(48, 3, 6, '2025-10-22 18:05:10'),
(49, 3, 1, '2025-10-22 18:05:10'),
(50, 3, 10, '2025-10-22 18:05:10'),
(51, 3, 2, '2025-10-22 18:05:10'),
(52, 3, 12, '2025-10-22 18:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_as` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','employee') DEFAULT 'employee' COMMENT 'Vai trò: admin hoặc nhân viên',
  `permissions` text DEFAULT NULL COMMENT 'JSON chứa các quyền cụ thể',
  `role_id` int(11) DEFAULT NULL COMMENT 'ID của role',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Lần đăng nhập cuối',
  `login_count` int(11) DEFAULT 0 COMMENT 'Số lần đăng nhập',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1: hoạt động, 0: bị khóa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `password`, `role_as`, `created_at`, `role`, `permissions`, `role_id`, `last_login`, `login_count`, `is_active`) VALUES
(5, 'Admin', 'admin@fashion.com', '0123456789', 'Hà Nội', 'admin123', 1, '2025-10-22 18:45:43', 'employee', NULL, NULL, NULL, 0, 1),
(6, 'Admin', 'tuanphat.htht@gmail.com', '0123456789', 'Hà Nội', '123456', 1, '2025-11-06 01:37:33', 'employee', NULL, NULL, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) DEFAULT 1 COMMENT '1: được phép, 0: bị từ chối',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) NOT NULL,
  `max_order` decimal(10,2) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'ID của admin tạo voucher',
  `updated_by` int(11) DEFAULT NULL COMMENT 'ID của admin cập nhật voucher cuối cùng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id`, `code`, `type`, `value`, `min_order`, `max_order`, `start_date`, `end_date`, `status`, `created_at`, `created_by`, `updated_by`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 100.00, 1000.00, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 1, '2025-10-13 04:14:28', NULL, NULL),
(2, 'SAVE20', 'fixed', 20.00, 200.00, 500.00, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 1, '2025-10-13 04:14:28', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_table_name` (`table_name`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_audit_log_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_audit_log_admin_created` (`admin_id`,`created_at`),
  ADD KEY `idx_audit_log_action_created` (`action`,`created_at`);

--
-- Indexes for table `audit_log_relations`
--
ALTER TABLE `audit_log_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_log_id` (`audit_log_id`),
  ADD KEY `idx_related_table` (`related_table`),
  ADD KEY `idx_related_id` (`related_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_product_size` (`product_id`,`size`),
  ADD KEY `id_product_size` (`product_id`,`size`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_permission` (`user_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log_relations`
--
ALTER TABLE `audit_log_relations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_log_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `audit_log_relations`
--
ALTER TABLE `audit_log_relations`
  ADD CONSTRAINT `fk_audit_log_relations_audit_log_id` FOREIGN KEY (`audit_log_id`) REFERENCES `audit_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
