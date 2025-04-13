-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2025 at 10:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workly_db`
--
CREATE DATABASE IF NOT EXISTS `workly_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `workly_db`;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` int(11) NOT NULL,
  `job_post_id` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('not_approved','approved','rejected') DEFAULT 'not_approved',
  PRIMARY KEY (`id`),
  KEY `candidate_id` (`candidate_id`),
  KEY `job_post_id` (`job_post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `candidate_id`, `job_post_id`, `applied_at`, `status`) VALUES
(2, 3, 3, '2025-04-11 16:55:15', 'not_approved'),
(3, 3, 12, '2025-04-11 17:09:47', 'not_approved'),
(4, 4, 23, '2025-04-12 19:35:11', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `cv_url` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT 'assets/images/default-avatar.png',
  `industry` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `user_id`, `full_name`, `phone`, `address`, `cv_url`, `avatar`, `industry`, `bio`) VALUES
(3, 12, 'Châu Thuyên Long', '09449222339', 'Cô Giang, Quận 1, TP.HCM', NULL, 'uploads/avatars/1744392903_DSC_0009.JPG', '', 'Hello'),
(4, 14, 'Nguyễn Hoàng Quân', '', '', NULL, 'uploads/avatars/1744485404_default-avatar.jpg', 'Information Technology', 'Hi, i\'m an intern IT');

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

DROP TABLE IF EXISTS `employers`;
CREATE TABLE IF NOT EXISTS `employers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `company_logo` varchar(255) NOT NULL DEFAULT 'assets/images/default-null.png',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`id`, `user_id`, `company_name`, `website`, `description`, `company_logo`) VALUES
(1, 3, 'Công ty ABC', 'https://abc.com', 'Công ty công nghệ chuyên tuyển dụng nhân sự ngành IT', 'assets/images/default-null.png'),
(2, 6, 'testregemployer002', 'testregemployer002', 'testregemployer002', 'assets/images/default-null.png'),
(3, 7, 'testregemployer003', 'testregemployer003', 'testregemployer003', 'assets/images/default-null.png'),
(5, 9, 'testregemployer005', 'testregemployer005', 'testregemployer005', 'assets/images/default-null.png'),
(6, 10, 'testregemployer006', 'testregemployer006', 'testregemployer006', 'assets/images/default-null.png'),
(7, 11, 'Tập đoàn FPT', 'https://fpt.com/', 'Tập đoàn FPT', 'assets/images/Logo-FPT.png'),
(8, 13, 'Công ty Cổ phần Viễn thông FPT', 'https://fpt.com.vn', 'FPT Telecom là một trong những nhà cung cấp dịch vụ viễn thông và công nghệ thông tin hàng đầu tại Việt Nam. Chúng tôi chuyên cung cấp các giải pháp công nghệ tiên tiến, dịch vụ internet tốc độ cao, và các sản phẩm phần mềm cho doanh nghiệp. Đội ngũ của chúng tôi luôn tìm kiếm những ứng viên tài năng, đam mê công nghệ để cùng phát triển.', '../uploads/companies/logo_67faa0a402dbb.png'),
(9, 20, 'VinAI - Công ty Nghiên cứu Trí tuệ Nhân tạo', 'https://vinai.io', 'VinAI là đơn vị tiên phong trong nghiên cứu và ứng dụng trí tuệ nhân tạo tại Việt Nam, thuộc tập đoàn Vingroup. Chúng tôi tập trung vào các dự án AI tiên tiến như nhận diện hình ảnh, xử lý ngôn ngữ tự nhiên, và tự động hóa. VinAI luôn chào đón các kỹ sư và nhà nghiên cứu đam mê AI để cùng tạo ra những sản phẩm đột phá.', 'assets/images/vinai_logo.jpg'),
(10, 21, 'Ngân hàng TMCP Kỹ Thương Việt Nam (Techcombank)', 'https://techcombank.com.vn', 'Techcombank là một trong những ngân hàng hàng đầu tại Việt Nam, cung cấp các dịch vụ tài chính đa dạng như ngân hàng số, quản lý tài sản, và đầu tư. Chúng tôi cam kết xây dựng một môi trường làm việc chuyên nghiệp, sáng tạo, và luôn tìm kiếm những nhân sự xuất sắc để gia nhập đội ngũ của mình.', 'assets/images/techcombank_logo.png'),
(11, 22, 'Shopee Việt Nam', 'https://shopee.vn', 'Shopee là nền tảng thương mại điện tử hàng đầu tại Đông Nam Á, mang đến trải nghiệm mua sắm trực tuyến tiện lợi và an toàn. Chúng tôi đang tìm kiếm các ứng viên năng động, sáng tạo để gia nhập đội ngũ vận hành, công nghệ, và marketing của Shopee tại Việt Nam.', 'assets/images/shopee_logo.png'),
(12, 23, 'Công ty Cổ phần Tiki', 'https://tiki.vn', 'Tiki là một trong những nền tảng thương mại điện tử lớn nhất tại Việt Nam, chuyên cung cấp sách, đồ điện tử, và hàng tiêu dùng. Chúng tôi tự hào với dịch vụ giao hàng nhanh và đội ngũ nhân viên tận tâm. Tiki luôn chào đón các ứng viên nhiệt huyết để cùng phát triển.', 'assets/images/tiki_logo.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

DROP TABLE IF EXISTS `job_posts`;
CREATE TABLE IF NOT EXISTS `job_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `salary_min` int(11) DEFAULT NULL,
  `salary_max` int(11) DEFAULT NULL,
  `job_type` enum('full-time','part-time') NOT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `requirements` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employer_id` (`employer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_posts`
--

INSERT INTO `job_posts` (`id`, `employer_id`, `title`, `description`, `location`, `salary_min`, `salary_max`, `job_type`, `industry`, `keywords`, `status`, `expires_at`, `created_at`, `requirements`, `benefits`, `updated_at`) VALUES
(1, 1, 'Lập trình viên PHP', 'Cần tuyển lập trình viên PHP có kinh nghiệm.', 'Hồ Chí Minh', 10000000, 20000000, 'full-time', 'IT', 'php, backend, developer', 'active', '2025-04-18', '2025-04-11 07:36:02', NULL, NULL, NULL),
(2, 1, 'Thiết kế UI/UX', 'Tuyển designer UI/UX cho mobile app.', 'Đà Nẵng', 8000000, 15000000, 'part-time', 'Design', 'design, ui, ux', 'active', '2025-04-25', '2025-04-11 07:36:02', NULL, NULL, NULL),
(3, 1, 'Lập trình viên PHP 1', 'Mô tả công việc PHP 1', 'Hà Nội', 8000000, 11999999, 'full-time', 'IT', 'php, backend', 'inactive', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, '2025-04-12 13:36:31'),
(4, 1, 'Lập trình viên PHP 2', 'Mô tả công việc PHP 2', 'Hà Nội', 8000000, 12000000, 'full-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(5, 1, 'Lập trình viên PHP 3', 'Mô tả công việc PHP 3', 'Hà Nội', 8000000, 12000000, 'full-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(6, 1, 'Lập trình viên PHP 4', 'Mô tả công việc PHP 4', 'Hà Nội', 8000000, 12000000, 'full-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(7, 1, 'Lập trình viên PHP 5', 'Mô tả công việc PHP 5', 'Hà Nội', 8000000, 12000000, 'full-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(8, 1, 'Lập trình viên PHP 6', 'Mô tả công việc PHP 6', 'TP.HCM', 9000000, 14000000, 'part-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(9, 1, 'Lập trình viên PHP 7', 'Mô tả công việc PHP 7', 'TP.HCM', 9000000, 14000000, 'part-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(10, 1, 'Lập trình viên PHP 8', 'Mô tả công việc PHP 8', 'Đà Nẵng', 7000000, 11000000, 'full-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(11, 1, 'Lập trình viên PHP 9', 'Mô tả công việc PHP 9', 'Đà Nẵng', 7000000, 11000000, 'full-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(12, 1, 'Lập trình viên PHP 10', 'Mô tả công việc PHP 10', 'Hải Phòng', 7500000, 11500000, 'part-time', 'IT', 'php, backend', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(13, 1, 'Front-end Developer 1', 'Mô tả Front-end 1', 'Hà Nội', 8000000, 13000000, 'full-time', 'IT', 'html, css, js', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(14, 1, 'Front-end Developer 2', 'Mô tả Front-end 2', 'Hà Nội', 8000000, 13000000, 'full-time', 'IT', 'html, css, js', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(15, 1, 'Front-end Developer 3', 'Mô tả Front-end 3', 'TP.HCM', 8500000, 13500000, 'part-time', 'IT', 'html, css, js', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(17, 1, 'Graphic Designer 1', 'Mô tả Designer 1', 'Đà Nẵng', 7000000, 10000000, 'full-time', 'Design', 'photoshop, ai', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(18, 1, 'Graphic Designer 2', 'Mô tả Designer 2', 'Đà Nẵng', 7000000, 10000000, 'full-time', 'Design', 'photoshop, ai', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(19, 1, 'Graphic Designer 3', 'Mô tả Designer 3', 'Hải Phòng', 6500000, 9500000, 'part-time', 'Design', 'photoshop, ai', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(20, 1, 'Graphic Designer 4', 'Mô tả Designer 4', 'Hải Phòng', 6500000, 9500000, 'part-time', 'Design', 'photoshop, ai', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(21, 1, 'QA Engineer', 'Mô tả QA Engineer', 'Hà Nội', 9000000, 14000000, 'full-time', 'IT', 'qa, testing', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(22, 1, 'DevOps Engineer', 'Mô tả DevOps', 'TP.HCM', 10000000, 16000000, 'full-time', 'IT', 'devops, aws', 'active', '2025-12-31', '2025-04-11 08:49:31', NULL, NULL, NULL),
(23, 8, 'NHÂN VIÊN KINH DOANH – FPT TELECOM', 'Tư vấn, giải thích cho khách hàng về dịch vụ do FPT Telecom đang cung cấp\r\n\r\nMặt hàng: viễn thông (Internet, Truyền hình, Camera,..)\r\n\r\nĐàm phán, thương lượng, xúc tiến ký kết hợp đồng với khách hàng\r\n\r\nTìm kiếm khách hàng tiềm năng, chăm sóc khách hàng sau bán hàng và phát triển thị trường\r\n\r\nVà các công việc khác theo yêu cầu quản lý', 'tại tất cả quận huyện tại TP.HCM (Công ty sẽ sắp xếp ứng viên làm việc gần nhà)', 10000000, 20000000, 'full-time', 'Kinh doanh', 'Kinh doanh, fpt, nhân viên kinh doanh', 'active', '2025-04-30', '2025-04-12 18:00:36', 'Nam/ Nữ, tốt nghiệp Trung cấp trở lên, tuổi từ 20 - 35\r\n\r\nCó phương tiện di chuyển và smartphone\r\n\r\nĐam mê kinh doanh, đam mê kiếm tiền, yêu thích công việc tiếp xúc với khách hàng\r\n\r\nGiao tiếp tốt, có kỹ năng thuyết phục\r\n\r\nNhanh nhẹn, linh hoạt, giải quyết tình huống tốt\r\n\r\nNgoại hình dễ nhìn, không nói ngọng, nói lắp\r\n\r\nChịu được áp lực cao trong công việc', 'Lương cơ bản + hoa hồng + doanh thu. Thu nhập hấp dẫn theo năng lực (thưởng theo doanh số, thưởng quý, thưởng Tết không giới hạn)\r\n\r\nLương trung bình/tháng: 10 – 20 triệu\r\n\r\nĐược tham gia BHXH, BHYT, BHTN đầy đủ theo quy định của pháp luật\r\n\r\nĐược tham gia du lịch hè, Team building, tiệc New-year party và các sự kiện có liên quan do công ty tổ chức\r\n\r\nĐược tham gia Khóa đào tạo Nhân viên kinh doanh chuyên nghiệp, đầy thử thách ngay sau khi đậu phỏng vấn\r\n\r\nĐược làm việc trong môi trường trẻ trung, năng động và linh hoạt\r\n\r\nĐược đào tạo, học hỏi và phát triển bản thân, môi trường làm việc có nhiều cơ hội thăng tiến\r\n\r\nMua sản phẩm công ty với giá ưu đãi', '2025-04-12 18:00:36'),
(24, 8, 'CHUYÊN VIÊN QUẢN LÝ CHẤT LƯỢNG', 'Tổ chức Xây dựng, áp dụng, duy trì, đánh giá và cải tiến Hệ thống chất lượng của công ty.\r\nXây dựng chương trình, kế hoạch và tổ chức thực hiện công tác thanh tra, kiểm tra, kiểm soát, đánh giá về việc thực hiện các qui định, qui trình, tiêu chuẩn kỹ thuật của công ty.\r\nĐảm bảo chất lượng.\r\nTìm hiểu, áp dụng tiêu chuẩn mới', 'Lô L29B-31B-33B, Đường Tân Thuận, KCX Tân Thuận, Quận 7, Tp.HCM', 15000000, 25000000, 'full-time', NULL, 'CHUYÊN VIÊN QUẢN LÝ CHẤT LƯỢNG,', 'active', '2025-04-30', '2025-04-12 18:04:38', 'Tốt nghiệp Đại học, Ưu tiên các khối: Quản trị chất lương, Kinh tế, Tài chính, Công nghệ thông tin.\r\nCó kiến thức chuyên môn về các nghiệp vụ về một hoặc nhiều lĩnh vực sau: Viễn thông, Chăm sóc khách hàng, Hỗ trợ kỹ thuật, Kinh doanh và Marketing, Kế toán Tài chính, Mua hàng, Logistic, Quản trị chất lượng…Ưu tiên các ứng viên có kỹ năng cho 4.0 về Data mining, Kiểm soát quy trình số, thiết lập quy trình chuyển đổi số.\r\nCó khả năng tự nghiên cứu, học hỏi kiến thức từ các nguồn (Nội bộ, Internet, các tổ chức đơn vị khác trong và ngoài công ty, các tài liệu tiếng Việt hoặc tiếng Anh).\r\nVề kiến thức và kỹ năng mềm: Teamwork (trong nội bộ và với các đơn vị liên quan), tiếng Anh, tin học văn phòng, thuyết trình và đào tạo.\r\nƯu tiên các ứng viên có kiến thức và kinh nghiệm vận hành về: Các tiêu chuẩn quản trị: ISO 9001, ISO 27001, ISO 20000, CMMI, PCI-DSS,…Công cụ cải tiến: Lean, 6Sigma, Quality cost,…\r\nChịu được áp lực công việc, sẵn sàng đi công tác.', 'Gói thu nhập cạnh tranh (thưởng lương tháng 13, thưởng nghỉ mát....)\r\nĐầy đủ các chế độ theo luật lao động hiện hành.\r\nChính sách phúc lợi theo quy định của Công ty đa dạng: Chăm sóc sức khỏe định kì hàng năm; Gói bảo hiểm sức khỏe chuyên biệt (FPT Care – Khám chữa bệnh miễn phí tại tất cả các bệnh viện); Các hoạt động tri ân, chăm lo đời sống tinh thần CBNV và Thân nhân ...\r\nMôi trường làm việc thân thiện, cởi mở.\r\nCơ sở vật chất và công cụ làm việc hiện đại, tiện nghi.\r\nNhiều cơ hội phát triển và thăng tiến.\r\nVăn hóa Doanh nghiệp đặc sắc, sinh động bậc nhất với nhiều các hoạt động hấp dẫn: tân binh, teambuilding, thi trạng, hội làng, hội diễn Sao Chổi, sinh nhật FPT, ngày 08/03, ngày 11/11...\r\nHưởng các gói ưu đãi cước khi sử dụng dịch vụ của FPT Telecom.\r\n*Ghi chú: FPT Telecom không thu bất kỳ chi phí nào của Ứng viên, Sinh viên trong quá trình tuyển dụng, thực tập.*', '2025-04-12 18:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','candidate','employer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin123', '$2y$10$bBAiN1eZS/5T2z8aeHH3lOGMAzVlYJpuaL0ZjrEXw4eJaZ1t8sZqi', 'admin@example.com', 'admin', '2025-04-11 07:36:02'),
(2, 'candidate123', '$2y$10$4DhBZtFmeAkH241TAIAQWOrnS8SiArgpCVW4xfEPmJYUdl3JfXxde', 'candidate@example.com', 'candidate', '2025-04-11 07:36:02'),
(3, 'employer123', '$2y$10$Hsxiwy7STETKsnG9szPfX.h/7iwIe1K3GpifP0.btF3rFdMaVp4Pm', 'employer@example.com', 'employer', '2025-04-11 07:36:02'),
(4, 'testregcandidate001', '$2y$10$uK.JMzjrn/QEaHRbQahfru4Rxz9E3DsB9SJ8d65C.w6vfenvpWa/C', 'testregcandidate001@gmail.com', 'candidate', '2025-04-11 13:38:25'),
(5, 'testregemployer001', '$2y$10$CTl3TbW0bJgL3mU/5UMwn.ZV3ZSvkILp3EhhMERaoLyZdqgURYMJm', 'testregemployer001@gmail.com', 'employer', '2025-04-11 15:15:54'),
(6, 'testregemployer002', '$2y$10$MZXIlWGaZ78x8mvaFL5bCOROR0tTAX6nHueuaSL9KwuCjO2n6BBeW', 'testregemployer002@gmail.com', 'employer', '2025-04-11 15:18:52'),
(7, 'testregemployer003', '$2y$10$q.FFGxyTffv/PGSglNk/WulUwIyArPfGJRq2pnM51sahecBHggdHm', 'testregemployer003@gmail.com', 'employer', '2025-04-11 15:20:04'),
(8, 'testregemployer004', '$2y$10$YGUwO6ulw5VFP6iQxolCyOv85QQo/iYhTHoykbPan0CApwGbaVeJu', 'testregemployer004@gmail.com', 'employer', '2025-04-11 15:20:38'),
(9, 'testregemployer005', '$2y$10$scczdt468DRu7O.zrn2PDeRqTa10vWpduAa8LNaUkPyDg9K2ZK6zC', 'testregemployer005@gmail.com', 'employer', '2025-04-11 15:24:01'),
(10, 'testregemployer006', '$2y$10$bYftD0p26S69MVBlBuH4/.8IKkB/O5t7lPgIjIOpvS0zzgE.Dtst2', 'testregemployer006@gmail.com', 'employer', '2025-04-11 15:24:23'),
(11, 'fpt_employer', '$2y$10$YTb2RU.AF4sEr0mJ5VUXA.txyZlHuNv7C6DGykHfrrY81SW6J23Jq', 'fpt_employer@gmail.com', 'employer', '2025-04-11 15:39:03'),
(12, 'Chaulong5823', '$2y$10$1rMekpmUMHZXJcTY3t1HvuIaVE3d9U9hhRBbq2bybFD1xg.duIib6', 'longchau5823@gmail.com', 'candidate', '2025-04-11 16:10:15'),
(13, 'nguyenvananh_fpt', '$2y$10$hkUuyctVdqW4jqMgaqlcXuYdJggFMeMYAUwNRR.odUphTA4nsMDqO', 'nguyenvananh_fpt123@fpt.com.vn', 'employer', '2025-04-12 17:19:32'),
(14, 'nguyenhoangquan', '$2y$10$U8qjck7PVGJfBJiZxel.kOg5OVcrfpwjzOY1MyO2EEGWOXNPAZfDu', 'nguyenhoangquan@gmail.com', 'candidate', '2025-04-12 19:15:36'),
(20, 'tranminhthu_vinai', '$2y$10$5YfZ8oJq5z9Xb3rL4tK7vOaPqW8mN9xYcZ2vU3wE4iT5jU6kL7mN8', 'recruitment@vinai.io', 'employer', '2025-04-12 20:31:24'),
(21, 'lethithao_techcom', '$2y$10$8pG2hK3mL4nQ5rT6uV7wXObPyR9sT0vWxY1zA2bC3dE4fF5gH6iJ8', 'hr@techcombank.com.vn', 'employer', '2025-04-12 20:31:24'),
(22, 'phamquanghuy_shopee', '$2y$10$2qW4eR5tY6uI7oP8aS9dFObPyT0vU1wWxZ2xC3bE4dF5gH6iJ7kL8', 'careers@shopee.vn', 'employer', '2025-04-12 20:31:24'),
(23, 'nguyenthithanh_tiki', '$2y$10$5tY7uI8oP9aS0dF1gH2jKObPyV3wWxZ4xC5bE6dF7gH8iJ9kL0mN2', 'jobs@tiki.vn', 'employer', '2025-04-12 20:31:24');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_post_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD CONSTRAINT `job_posts_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;
COMMIT;
