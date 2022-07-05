-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 05, 2022 at 06:35 PM
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
(1, 'Unknown', 'The category for products that aren\'t classified !', 0, 0, 'unknown'),
(2, 'ASUS', 'ASUS from Taiwan', 1, 0, 'asus'),
(3, 'Lenovo', 'Lenovo from China', 2, 0, 'lenovo');

-- --------------------------------------------------------

--
-- Table structure for table `ec_configuration`
--

CREATE TABLE `ec_configuration` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ec_configuration`
--

INSERT INTO `ec_configuration` (`id`, `name`, `data`) VALUES
(1, 'smtp', '{\r\n  \"host\": \"smtp.gmail.com\",\r\n  \"port\": \"465\",\r\n  \"encryption\": \"ssl\",\r\n  \"auth\": true,\r\n  \"username\": \"phongkaster@gmail.com\",\r\n  \"password\": \"def502007f265790521f109cc8d371201ce1d3ea6b50a36aa964cc12ca24d71e7664e379ab7a24b5efa3dd21b1227915b8db9e02c54be13af67ac67c7a38002713c6088f6db45b3070e5dee82504ae1ada01343f156044d87301678335f3eaf4eaaf\",\r\n  \"from\": \"phongkaster@gmail.com\"\r\n}'),
(2, 'settings', '{\r\n  \"site_name\": \"PhongKaster Shop\",\r\n  \"site_description\": \"A shop provides PC/Laptop\",\r\n  \"site_keywords\": \"pc, laptop, personal laptop\",\r\n  \"currency\": \"đ\",\r\n  \"logomark\": \"https://timeswriter.xyz/assets/uploads/images/logo.png\",\r\n  \"logotype\": \"https://timeswriter.xyz/assets/uploads/images/logo.png\",\r\n  \"site_slogan\": \"Together for the future\",\r\n  \"language\": \"vi-VN\"\r\n}');

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
  `screen_size` float DEFAULT NULL,
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
(1, 'DELL Vostro 3500 i5-1135G7/8GB/ SSD 256GB/ 15.6FHD/WIN 10', 0, 'Dell Technology', 2000000, 14, 'Intel Core I7-1165G7', '8', 'Intel Iris XE, NVIDIA GEFORCE MX 330', '512GB', 'design', 'Laptop DELL Vostro 3500 i5-1135G7/8GB/ SSD 256GB/ 15.6FHD/WIN 10 từ nhà sản xuất Dell Technology là một laptop hiện đại, đa dụng & rất thời trang !', NULL, '2022-07-04 15:37:22'),
(2, 'Laptop Lenovo IdeaPad Slim 5 15ITL05 i5 1135G7/8GB', 10, 'Lenovo', 14999000, 15.6, 'Intel Core i5 1', '8GB', 'Intel Iris Xe', '512GB', 'Văn phòng', 'Dòng Lenovo IdeaPad Slim 5 15ITL05 giờ đây đã được nâng cấp lên bộ vi xử lý Intel thế hệ thứ 11 vô cùng mạnh mẽ, trở thành chiếc laptop 15,6 inch nhỏ gọn, hiệu suất chuyên nghiệp, lý tưởng cho công việc.', '2022-06-30 22:13:16', '2022-06-30 22:13:16'),
(3, 'Laptop Dell Vostro 3500', 10, 'Lenovo', 17999000, 15.6, 'Intel Core i7 1165G7', '8GB', 'NVIDIA GeForce MX330', '512GB', 'Van-phong', 'Dòng Dell vostro 3500 giờ đây đã được nâng cấp lên bộ vi xử lý Intel thế hệ thứ 11 vô cùng mạnh mẽ, trở thành chiếc laptop 15,6 inch nhỏ gọn, hiệu suất chuyên nghiệp, lý tưởng cho công việc.', '2022-06-30 22:13:34', '2022-06-30 22:13:34'),
(4, 'Laptop Dell Vostroll 34', 0, 'Dell Techonology', 2000000, 14, 'Intel Core I7-1165G7', '8', 'Intel Irisxs, MX 330', '512GB', 'office', '0', '2022-07-03 09:36:30', '2022-07-03 09:36:30'),
(13, 'Laptop Dell Vostroll 18', 0, 'Dell Techonology', 2000000, 14, 'Intel Core I7-1165G7', '8', 'Intel Iris XE, NVIDIA GEFORCE MX 330', '512GB', 'office', 'Laptop Laptop Dell Vostroll 18 từ nhà sản xuất Dell Techonology là một laptop hiện đại, đa dụng & rất thời trang !', '2022-07-03 09:47:39', '2022-07-03 09:47:39');

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

--
-- Dumping data for table `ec_productsphoto`
--

INSERT INTO `ec_productsphoto` (`id`, `product_id`, `path`, `is_avatar`) VALUES
(12, 1, 'product_1_1657030442.png', 1),
(13, 1, 'product_1_1657037632.png', 0),
(17, 1, 'product_1_1657037730.png', 0);

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
(7, 'n18dccn147@student.ptithcm.edu.vn', '$2y$10$/mCY8QdXm3Huk1lidiorg.OmbiIwb0FfYIjdSUEUAifv7DhynATPy', 'JON', 'SNOW', 'member', 0, '2022-07-03 08:24:08', '2022-07-03 08:24:08'),
(8, 'n18dccn148@student.ptithcm.edu.vn', '$2y$10$d4Ic6vmJ8zBvZNNLZmY.1u9iTyO1Ux52TRlwvUO8s8SEr/e/IA4XC', 'phong', 'nguyen', 'member', 0, '2022-07-02 12:27:08', '2022-07-02 12:27:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ec_categories`
--
ALTER TABLE `ec_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ec_configuration`
--
ALTER TABLE `ec_configuration`
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
-- AUTO_INCREMENT for table `ec_configuration`
--
ALTER TABLE `ec_configuration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ec_orderscontent`
--
ALTER TABLE `ec_orderscontent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ec_products`
--
ALTER TABLE `ec_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ec_productsphoto`
--
ALTER TABLE `ec_productsphoto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
