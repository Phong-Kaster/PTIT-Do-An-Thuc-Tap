-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2022 at 10:19 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `ec_categories`
--

CREATE TABLE `ec_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `slug` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ec_categories`
--

INSERT INTO `ec_categories` (`id`, `name`, `description`, `position`, `parent_id`, `slug`) VALUES
(1, 'Dell', 'Máy tính Dell đến từ nước Mỹ', 0, 0, 'may-tinh-dell'),
(2, 'ASUS', 'Máy tính ASUS đền từ Đài Loan', 1, 0, 'may-tinh-asus'),
(3, 'Lenovo', 'Máy tính lenovo đến từ Trung Quốc', 2, 0, 'may-tinh-lenovo');

-- --------------------------------------------------------

--
-- Table structure for table `ec_orders`
--

CREATE TABLE `ec_orders` (
  `id` binary(16) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `receiver_phone` varchar(15) DEFAULT NULL,
  `receiver_address` varchar(255) DEFAULT NULL,
  `receiver_name` varchar(30) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `create_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ec_orderscontent`
--

CREATE TABLE `ec_orderscontent` (
  `id` int(11) NOT NULL,
  `order_id` binary(16) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `price` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ec_products`
--

CREATE TABLE `ec_products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '0',
  `remaining` int(11) DEFAULT 0,
  `manufacturer` varchar(30) DEFAULT NULL,
  `price` int(11) DEFAULT 0,
  `screen_size` varchar(10) DEFAULT NULL,
  `cpu` varchar(50) DEFAULT NULL,
  `ram` varchar(10) DEFAULT NULL,
  `graphic_card` varchar(50) DEFAULT NULL,
  `rom` varchar(10) DEFAULT NULL,
  `demand` varchar(20) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `create_at` datetime DEFAULT current_timestamp(),
  `update_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ec_products`
--

INSERT INTO `ec_products` (`id`, `name`, `remaining`, `manufacturer`, `price`, `screen_size`, `cpu`, `ram`, `graphic_card`, `rom`, `demand`, `content`, `create_at`, `update_at`) VALUES
(1, 'Asus TUF Gaming FX506LHB-HN188W i5 10300H', 15, 'ASUS', 24990000, '15.6', 'Intel, Core i5, 10300H', '8GB', 'NVIDIA GeForce GTX 1', '512GB', 'Gaming', 'ASUS TUF Gaming FX506LHB HN188W là chiếc laptop gaming giá rẻ với thiết kế tuyệt đẹp, phong cách chuẩn game thủ và cấu hình mạnh mẽ cho cả học tập, công việc cũng như chơi game. Bên cạnh đó là độ bền chuẩn quân đội đã làm nên tên tuổi của dòng TUF.', NULL, NULL),
(2, 'Laptop Lenovo IdeaPad Slim 5 15ITL05 i5 1135G7/8GB', 10, 'Lenovo', 14999000, '15.6', 'Intel Core i5 1', '8GB', 'Intel Iris Xe', '512GB', 'Văn phòng', 'Dòng Lenovo IdeaPad Slim 5 15ITL05 giờ đây đã được nâng cấp lên bộ vi xử lý Intel thế hệ thứ 11 vô cùng mạnh mẽ, trở thành chiếc laptop 15,6 inch nhỏ gọn, hiệu suất chuyên nghiệp, lý tưởng cho công việc.', '2022-06-30 22:13:16', '2022-06-30 22:13:16'),
(3, 'Laptop Dell Vostro 3500', 10, 'Lenovo', 17999000, '15.6', 'Intel Core i7 1165G7', '8GB', 'NVIDIA GeForce MX330', '512GB', 'Van-phong', 'Dòng Dell vostro 3500 giờ đây đã được nâng cấp lên bộ vi xử lý Intel thế hệ thứ 11 vô cùng mạnh mẽ, trở thành chiếc laptop 15,6 inch nhỏ gọn, hiệu suất chuyên nghiệp, lý tưởng cho công việc.', '2022-06-30 22:13:34', '2022-06-30 22:13:34');

-- --------------------------------------------------------

--
-- Table structure for table `ec_productsphoto`
--

CREATE TABLE `ec_productsphoto` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `is_avatar` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ec_productsreview`
--

CREATE TABLE `ec_productsreview` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `author_name` varchar(30) DEFAULT NULL,
  `author_email` varchar(30) DEFAULT NULL,
  `create_at` datetime DEFAULT NULL,
  `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ec_product_category`
--

CREATE TABLE `ec_product_category` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ec_product_category`
--

INSERT INTO `ec_product_category` (`id`, `category_id`, `product_id`) VALUES
(1, 2, 1),
(2, 3, 2),
(3, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ec_users`
--

CREATE TABLE `ec_users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `update_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ec_users`
--

INSERT INTO `ec_users` (`id`, `email`, `password`, `first_name`, `last_name`, `role`, `active`, `create_at`, `update_at`) VALUES
(1, 'phongkaster@gmail.com', '$2y$10$DbMcjgmEvTO/OMcYl.HhuOKyhPkA8F4YcTQbpBJderkog.Izn0YjW', 'phong', 'kaster', 'admin', 1, '2022-06-29 20:54:20', '2022-06-29 20:54:53'),
(4, 'phong1@gmail.com', '$2y$10$hNuPpJANZ5AuXZzdu7CwmumEcSsllzMSxuXqxubqHVFprwe7usuzm', 'Phong', 'Nguyen', 'customer', 1, '2022-07-02 07:41:35', '2022-07-02 07:41:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ec_categories`
--
ALTER TABLE `ec_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ec_orders`
--
ALTER TABLE `ec_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ec_orderscontent`
--
ALTER TABLE `ec_orderscontent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ec_products`
--
ALTER TABLE `ec_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ec_productsphoto`
--
ALTER TABLE `ec_productsphoto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ec_productsreview`
--
ALTER TABLE `ec_productsreview`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ec_product_category`
--
ALTER TABLE `ec_product_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ec_users`
--
ALTER TABLE `ec_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ec_categories`
--
ALTER TABLE `ec_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ec_orderscontent`
--
ALTER TABLE `ec_orderscontent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ec_products`
--
ALTER TABLE `ec_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ec_productsphoto`
--
ALTER TABLE `ec_productsphoto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ec_productsreview`
--
ALTER TABLE `ec_productsreview`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ec_product_category`
--
ALTER TABLE `ec_product_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ec_users`
--
ALTER TABLE `ec_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ec_orders`
--
ALTER TABLE `ec_orders`
  ADD CONSTRAINT `ec_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ec_users` (`id`);

--
-- Constraints for table `ec_orderscontent`
--
ALTER TABLE `ec_orderscontent`
  ADD CONSTRAINT `ec_orderscontent_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ec_orders` (`id`),
  ADD CONSTRAINT `ec_orderscontent_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `ec_products` (`id`);

--
-- Constraints for table `ec_productsphoto`
--
ALTER TABLE `ec_productsphoto`
  ADD CONSTRAINT `ec_productsphoto_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `ec_products` (`id`);

--
-- Constraints for table `ec_productsreview`
--
ALTER TABLE `ec_productsreview`
  ADD CONSTRAINT `ec_productsreview_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `ec_products` (`id`);

--
-- Constraints for table `ec_product_category`
--
ALTER TABLE `ec_product_category`
  ADD CONSTRAINT `ec_product_category_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `ec_categories` (`id`),
  ADD CONSTRAINT `ec_product_category_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `ec_products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
