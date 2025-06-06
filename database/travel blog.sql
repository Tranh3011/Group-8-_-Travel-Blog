-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 02, 2025 lúc 12:18 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `category`
CREATE TABLE `category` (
  `CategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`CategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `category` (`CategoryID`, `Name`, `Description`, `Created_at`, `Updated_at`, `Image`) VALUES
(1, 'Cities', 'Popular urban destinations around the world.', '2024-12-05 14:43:13', '2025-01-02 09:27:36', '../uploads/sydney.jpg'),
(2, 'Beaches', 'Beautiful beaches for relaxation and recreation.', '2024-12-05 14:43:13', '2024-12-29 03:05:29', '../uploads/beach.jpg'),
(3, 'Cultural Sites', 'Historical and cultural landmarks to explore.', '2024-12-05 14:43:13', '2024-12-29 03:06:24', '../uploads/cultural.jpg'),
(4, 'Adventure Spots', 'Locations for thrilling outdoor activities.', '2024-12-05 14:43:13', '2025-01-02 09:29:24', '../uploads/Adventure Spots.jpg'),
(5, 'Natural Wonders', 'Breathtaking natural landscapes and formations.', '2024-12-05 14:43:13', '2025-01-02 09:28:30', '../uploads/Natural Wonders.jpg'),
(6, 'Luxury Destinations', 'High-end locations for a lavish experience.', '2024-12-05 14:43:13', '2025-01-02 09:28:46', '../uploads/singapore.jpg'),
(7, 'Family-Friendly Locations', 'Places suitable for family vacations and activities.', '2024-12-05 14:43:13', '2025-01-02 09:32:21', '../uploads/family and friends_disneyland.jpg'),
(8, 'Romantic Getaways', 'Perfect spots for couples and romantic trips.', '2024-12-05 14:43:13', '2025-01-02 09:33:12', '../uploads/romantic place.jpg'),
(9, 'Wildlife Experiences', 'Destinations for observing and interacting with wildlife.', '2024-12-05 14:43:13', '2025-01-02 09:30:47', '../uploads/wildlife.jpg'),
(10, 'Culinary Destinations', 'Locations famous for their unique and delicious food.', '2024-12-05 14:43:13', '2025-01-02 09:34:03', '../uploads/culinary places.jpeg');

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `comment`
CREATE TABLE `comment` (
  `CommentID` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(255) DEFAULT NULL,
  `PostID` int(11) DEFAULT NULL,
  `Content` text DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`CommentID`),
  KEY `PostID` (`PostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comment` (`CommentID`, `FullName`, `PostID`, `Content`, `Created_at`) VALUES
(1, 'Thuy Anh', 1, 'Amazing insights about Paris! Love Paris so much', '2024-12-05 14:43:14'),
(2, 'Phuong Vu', 2, 'Can’t wait to visit New York!', '2024-12-05 14:43:14'),
(3, 'Phuong Vu', 3, 'Tokyo sounds incredible!', '2024-12-05 14:43:14'),
(4, 'Jane Smith', 4, 'Rome is on my bucket list.', '2024-12-05 14:43:14'),
(5, 'Thuy Anh', 5, 'London has so much history!', '2024-12-05 14:43:14'),
(6, 'Jane Smith', 6, 'Sydney looks beautiful!', '2024-12-05 14:43:14'),
(7, 'Tra Huong', 7, 'Singapore is fascinating!', '2024-12-05 14:43:14'),
(8, NULL, NULL, 'Love Japanese foods so much. Tokyo vibe is sth amazing', '2024-12-27 15:04:00');

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `destination`
CREATE TABLE `destination` (
  `DestinationID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(200) DEFAULT NULL,
  `post_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DestinationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `destination` (`DestinationID`, `Name`, `Description`, `Location`, `Created_at`, `Updated_at`, `image`, `post_link`) VALUES
(1, 'Paris', 'The capital city of France, known for its art and culture.', 'France', '2024-12-05 14:43:13', '2025-01-02 10:59:26', '../uploads/paris.jpg', 'post_Paris.php'),
(2, 'New York', 'A bustling city known for its skyline and cultural diversity.', 'USA', '2024-12-05 14:43:13', '2025-01-02 11:00:03', '../uploads/Statue of Liberty.jpg', 'post_NewYork.php'),
(3, 'Tokyo', 'The capital of Japan, known for its modernity.', 'Japan', '2024-12-05 14:43:13', '2025-01-02 11:12:12', '../uploads/tokyo.jpeg', 'post_Tokyo.php'),
(4, 'Rome', 'Ancient ruins that showcase the history of Rome.', 'Italy', '2024-12-05 14:43:13', '2025-01-02 10:42:26', '../uploads/rome2.jpeg', NULL),
(5, 'London', 'The capital city of England, famous for its landmarks.', 'UK', '2024-12-05 14:43:13', '2025-01-02 10:41:25', '../uploads/london2.jpg', NULL),
(6, 'Sydney', 'Known for its Sydney Opera House and beautiful harbor.', 'Australia', '2024-12-05 14:43:13', '2025-01-02 10:27:10', '../uploads/sydney.jpg', NULL),
(7, 'Singapore', 'A city known for luxury shopping and modern architecture.', 'Singapore', '2024-12-05 14:43:13', '2025-01-02 10:38:54', '../uploads/singapore.jpg', NULL),
(8, 'Bangkok', 'The capital of Thailand, known for its vibrant street life.', 'Thailand', '2024-12-05 14:43:13', '2025-01-02 10:28:16', '../uploads/bangkok.jpg', NULL),
(9, 'Barcelona', 'A city known for its art and architecture.', 'Spain', '2024-12-05 14:43:13', '2025-01-02 10:30:10', '../uploads/barcelona.jpeg', NULL),
(10, 'Istanbul', 'A city that straddles Europe and Asia across the Bosphorus Strait.', 'Turkey', '2024-12-05 14:43:13', '2025-01-02 10:29:15', '../uploads/istanbul.jpeg', NULL),
(19, 'Hue', 'Hue, the former imperial capital of Vietnam, is renowned for its rich history and stunning architecture. Nestled along the banks of the Perfume River, it boasts the UNESCO-listed Imperial City, ancient pagodas, and vibrant markets. Visitors can explore the royal tombs of Nguyen emperors and savor delicious local cuisine. With its blend of cultural heritage and natural beauty, Hue offers a unique glimpse into Vietnam\'s past.\r\nTravelers can try delicious foods in Hue like: bún bò Huế, bánh canh, etc', 'Vietnam', '2025-01-02 10:53:03', '2025-01-02 10:53:03', '../uploads/hue.jpg', NULL);

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `posts`
CREATE TABLE `posts` (
  `PostID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `DestinationID` int(11) DEFAULT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`PostID`),
  KEY `UserID` (`UserID`),
  KEY `DestinationID` (`DestinationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `posts` (`PostID`, `UserID`, `DestinationID`, `Title`, `Content`, `Created_at`, `Updated_at`, `image`) VALUES
(1, 1, 1, 'Exploring Paris', 'A deep dive into the culture and lifestyle of Paris.', '2024-12-05 14:43:14', '2025-01-02 09:06:54', '../uploads/paris.jpg'),
(2, 2, 2, 'New York Adventures', 'Experience the vibrancy of New York City.', '2024-12-05 14:43:14', '2025-01-02 09:20:04', '../uploads/Times Square.jpg'),
(3, 3, 3, 'Tokyo: A Blend of Tradition and Modernity', 'Discover the beauty of Tokyo.', '2024-12-05 14:43:14', '2025-01-02 09:20:55', '../uploads/tokyo.jpeg'),
(4, 4, 4, 'Rome: The Eternal City', 'A journey through the history of Rome.', '2024-12-05 14:43:14', '2024-12-30 15:45:21', '../uploads/rome.jpeg'),
(5, 5, 5, 'London: A City of History', 'Exploring the landmarks of London.', '2024-12-05 14:43:14', '2025-01-02 09:21:45', '../uploads/london.jpeg'),
(6, 6, 6, 'Sydney: Sun and Surf', 'Enjoying the beaches of Sydney.', '2024-12-05 14:43:14', '2025-01-02 09:22:29', '../uploads/sydney.jpg'),
(7, 7, 7, 'Singapore: A City of Luxury', 'Discover the extravagance of Singapore.', '2024-12-05 14:43:14', '2025-01-02 09:23:41', '../uploads/singapore.jpg'),
(21, NULL, NULL, 'Bali', 'Bali is a famous island in Indonesia known for its stunning beaches, rich culture, and breathtaking scenery. It is an ideal destination for those looking to relax and explore.', '2024-12-24 08:21:02', '2025-01-02 09:24:40', '../uploads/bali.jpeg'),
(25, NULL, NULL, 'Sapa - A perfect sightseeing and cultural trip', 'Sapa is a place of natural beauty, with cascading rice terraces, misty valleys, and majestic peaks. It is also a cultural hub, home to several ethnic minority groups with unique customs and traditions.', '2024-12-30 15:40:22', '2025-01-02 09:26:44', '../uploads/sapa.jpg');

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `travel_tips`
CREATE TABLE `travel_tips` (
  `TipID` int(11) NOT NULL AUTO_INCREMENT,
  `TipTitle` varchar(255) NOT NULL,
  `TipContent` text NOT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`TipID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `travel_tips` (`TipID`, `TipTitle`, `TipContent`, `Created_at`, `Updated_at`) VALUES
(1, 'How to Pack Efficiently for Your Trip', 'Packing light can make your trip easier and more enjoyable. Focus on essentials and use packing cubes to save space.', '2024-12-31 00:01:41', '2024-12-31 00:01:41'),
(2, 'Top 5 Safety Tips for Travelers', 'Always keep a copy of your passport and important documents. Stay aware of your surroundings, and keep your belongings secure.', '2024-12-31 00:01:41', '2024-12-31 00:01:41'),
(3, 'How to Find Cheap Flights', 'Look for flights in advance, use fare comparison websites, and set up price alerts to get the best deals on flights.', '2024-12-31 00:01:41', '2024-12-31 00:01:41'),
(4, 'Essential Travel Apps You Need', 'Download apps for navigation, language translation, currency conversion, and accommodation booking for a smoother travel experience.', '2024-12-31 00:01:41', '2024-12-31 00:01:41'),
(5, 'How to Stay Healthy While Traveling', 'Stay hydrated, eat balanced meals, and take breaks to avoid burnout during your trip.', '2024-12-31 00:01:41', '2024-12-31 00:01:41');

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `user`
CREATE TABLE `user` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `FullName` varchar(255) GENERATED ALWAYS AS (CONCAT(`FirstName`, ' ', `LastName`)) STORED,
  `Email` varchar(200) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `City` varchar(255) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`UserID`, `FirstName`, `LastName`, `FullName`, `Email`, `PhoneNumber`, `Password`, `City`, `Country`, `Avatar`, `user_type`) VALUES
(1, 'Phuong', 'Vu', 'Phuong Vu', 'phuongvu123@gmail.com', '0988873211', 'phuong1122@', 'Hanoi', 'Vietnam', '../uploadsavartar_nam.jpg', 'admin'),
(2, 'Jane', 'Smith', 'Jane Smith', 'jane2324@gmail.com', '0987654322', 'password2', 'London', 'UK', '../uploads/avartar.jpg', 'user'),
(3, 'Alice', 'Johnson', 'Alice Johnson', 'alice1144@gmail.com', '0963829192', 'password3', 'Sydney', 'Australia', '../uploads/avartar_nu1.png', 'user'),
(4, 'Thuy', 'Anh', 'Thuy Anh', '21070294@vnu.edu.vn', '0988724453', 'password7', 'Hanoi', 'Vietnam', '../uploads/avartar_nu.png', 'user'),
(5, 'Tra', 'Huong', 'Tra Huong', '21070705@vnu.edu.vn', '6677889900', '123456@', 'Hanoi', 'Vietnam', '../uploads/avartar_nu1.png', 'admin'),
(6, 'Tuan', 'Kiet', 'Tuan Kiet', '20070789@vnu.edu.vn', '0977383842', 'password9', 'Barcelona', 'Spain', '../uploads/avartar.jpg', 'user'),
(7, 'Hien', 'Tran', 'Hien Tran', 'somintran421@gmail.com', '0988728972', 'hientran@', 'Haiduong', 'Vietnam', '../uploads/avartar_nu.png', 'user'),
(8, 'Kevin', 'Tran', 'Kevin Tran', 'kevintran123@gmail.com', '0981824432', 'kevintran@11', 'Haiduong', 'Vietnam', '../uploads/avartar.jpg', 'user'),
(9, 'Nguyen', 'Tra', 'Nguyen Tra', 'nguyentra3011@vnu.edu.vn', '0933425561', 'Tranh3011', 'Ha Noi', 'Vietnam', '../uploads/avartar_nam.jpg', 'user');

-- --------------------------------------------------------
-- Các ràng buộc cho các bảng đã đổ
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`PostID`) REFERENCES `posts` (`PostID`);

ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`DestinationID`) REFERENCES `destination` (`DestinationID`);

COMMIT;
