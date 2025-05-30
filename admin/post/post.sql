-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 03:19 PM
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
-- Database: `post`
--

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `PostID` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Content` text NOT NULL,
  `ImageURL` varchar(100) DEFAULT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Location` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`PostID`, `Title`, `Content`, `ImageURL`, `Created_at`, `Updated_at`, `Location`) VALUES
(1, 'Khám Phá Hà Nội', 'Hà Nội, thủ đô ngàn năm văn hiến, nổi tiếng với các di tích lịch sử như Văn Miếu, Hoàng Thành Thăng Long.', NULL, '2022-12-28 20:08:53', '2023-12-13 20:08:53', 'Hà Nội\r\n'),
(2, 'Ẩm Thực Hồ Chí Minh', 'Thành phố Hồ Chí Minh nổi tiếng với những món ăn đường phố hấp dẫn như phở, bánh mì và gỏi cuốn.', NULL, '2022-08-16 20:08:53', '2022-10-18 20:08:53', 'Hồ Chí Minh '),
(3, 'Biển Đà Nẵng ', 'Đà Nẵng nổi tiếng với bãi biển Mỹ Khê và các điểm du lịch như Ngũ Hành Sơn và Bà Nà Hills.', NULL, '2024-09-03 20:14:13', '2024-12-03 14:14:13', 'Đà Nẵng '),
(4, 'Lễ Hội Hội An ', 'Hội An nổi bật với các lễ hội đèn lồng và văn hóa truyền thống, thu hút du khách khắp nơi', NULL, '2021-05-17 20:14:13', '2021-05-27 20:14:13', 'Hội An '),
(5, 'Thăm Nha Trang ', 'Nha Trang nổi tiếng với những bãi biển tuyệt đẹp và các hoạt động thể thao nước hấp dẫn	', NULL, '2023-10-17 20:19:57', '2023-10-19 20:19:57', 'Nha Trang '),
(6, 'Khám phá ', 'Sapa nổi tiếng với những cánh đồng bậc thang và khí hậu mát mẻ, là điểm đến lý tưởng cho những ai yêu thiên nhiên', NULL, '2019-08-19 20:19:57', '2022-10-28 20:19:57', 'Sapa');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`PostID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `PostID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
