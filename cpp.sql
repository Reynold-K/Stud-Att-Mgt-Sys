-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 11:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cpp`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(5) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `faculty_id` int(5) DEFAULT NULL,
  `date` date NOT NULL,
  `attendance_time` time NOT NULL,
  `status` enum('present','absent') NOT NULL DEFAULT 'present',
  `auth_method` enum('fingerprint','facial','pin') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `subject_id`, `faculty_id`, `date`, `attendance_time`, `status`, `auth_method`, `timestamp`) VALUES
(1, 9, 1, NULL, '2025-04-18', '00:00:04', 'present', 'fingerprint', '2025-04-22 05:17:55'),
(2, 1, 3, 1003, '2025-04-27', '10:00:52', 'present', '', '2025-04-27 08:00:52'),
(3, 1, 8, 1004, '2025-04-27', '10:04:01', 'present', '', '2025-04-27 08:04:01'),
(4, 1, 3, 1003, '2025-04-27', '10:07:00', 'present', '', '2025-04-27 08:07:00'),
(5, 1, 4, 1001, '2025-04-27', '10:12:22', 'present', '', '2025-04-27 08:12:22'),
(6, 1, 2, 1002, '2025-04-27', '10:18:34', 'present', '', '2025-04-27 08:18:34'),
(7, 3, 7, 1003, '2025-04-27', '11:04:04', 'present', '', '2025-04-27 09:04:04'),
(8, 7, 5, 1004, '2025-04-27', '11:10:47', 'present', '', '2025-04-27 09:10:47'),
(9, 7, 1, 1001, '2025-04-27', '11:11:22', 'present', '', '2025-04-27 09:11:22');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `f_id` int(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`f_id`, `name`, `email`, `password`, `department`) VALUES
(1001, 'James Mwangi', 'james.mwangi@mmu.ac.ke', 'lec1', 'Computer Science'),
(1002, 'Prof Sarah Odhiambo', 'sarahodhiambo@gmail.com', 'lec2', 'Computer Science'),
(1003, 'Michael Kamau', 'michaelkamau1965@gmail.com', 'lec3', 'Computer Science'),
(1004, 'Nancy Wangari', 'nancywangari@mmu.ac.ke', 'lec4', 'Information Systems');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `Username` varchar(20) NOT NULL,
  `Password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`Username`, `Password`) VALUES
('admin', 'ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `present_table`
--

CREATE TABLE `present_table` (
  `record_id` int(11) NOT NULL,
  `semester` int(5) NOT NULL,
  `id` varchar(20) NOT NULL,
  `subject` varchar(25) NOT NULL,
  `date` varchar(20) NOT NULL,
  `day` varchar(20) NOT NULL,
  `time` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `present_table`
--

INSERT INTO `present_table` (`record_id`, `semester`, `id`, `subject`, `date`, `day`, `time`) VALUES
(1, 0, '1', 'Systems Project', '04-04-2025', 'Friday', '4'),
(2, 0, '2', 'Systems Project', '04-04-2025', 'Friday', '4'),
(3, 0, '3', 'Systems Project', '25-04-2025', 'Friday', '4'),
(4, 0, '4', 'Systems Project', '25-04-2025', 'Friday', '4'),
(5, 2, 'BCS 2418', 'Systems Project', '05-04-2025', 'Friday', '3'),
(6, 2, 'BCS 2421', 'Computer Security & Crypt', '01-04-2025', 'Monday', '4'),
(7, 2, 'BCS 2424', 'Auditing of Information S', '04-04-2025', 'Thursday', '2'),
(8, 2, 'BCS 2425', 'Data Mining & Business In', '02-04-2025', 'Tuesday', '4'),
(9, 2, 'BHR 2427', 'Human Resource Management', '04-04-2025', 'Friday', '1'),
(10, 2, 'BIT 2422', 'Advanced Network Concepts', '03-04-2025', 'Wednesday', '2'),
(11, 2, 'BIT 2426', 'Information Resource Mana', '01-04-2025', 'Monday', '1'),
(12, 2, 'UCU 2403', 'Entrepreneurship & Innova', '02-04-2025', 'Tuesday', '1'),
(13, 0, '6', 'Systems Project', '25-04-2025', 'Friday', '4'),
(14, 0, '8', 'Systems Project', '25-04-2025', 'Friday', '4'),
(15, 0, '10', 'Systems Project', '25-04-2025', 'Friday', '1'),
(16, 0, '82921', 'Systems Project', '30-04-2025', 'Monday', '1'),
(17, 0, '302910', 'ETE', '24-04-2025', 'Monday', '1');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `s_id` int(5) NOT NULL,
  `roll_no` int(10) NOT NULL,
  `enroll_no` bigint(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `contact` int(12) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `semester` int(11) DEFAULT 0,
  `biometric_template` text DEFAULT NULL,
  `facial_template` text DEFAULT NULL,
  `pin_hash` varchar(255) DEFAULT NULL,
  `has_fingerprint_scanner` enum('yes','no') NOT NULL DEFAULT 'no',
  `webauthn_credential` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`s_id`, `roll_no`, `enroll_no`, `name`, `contact`, `password`, `semester`, `biometric_template`, `facial_template`, `pin_hash`, `has_fingerprint_scanner`, `webauthn_credential`) VALUES
(1, 3701, 200010001, 'Reynold Kibet', 717204747, 'std1', 2, NULL, NULL, NULL, 'no', NULL),
(2, 3702, 200010002, 'Rodrick Rotich ', 712345678, 'std2', 2, NULL, NULL, NULL, 'no', NULL),
(3, 3703, 200010003, 'Rose Ogot', 722345678, 'std3', 2, NULL, NULL, NULL, 'no', NULL),
(4, 3704, 200010004, 'Richard Rotich', 732345678, 'std4', 2, NULL, NULL, NULL, 'no', NULL),
(5, 3705, 200010005, 'Matthew Mark', 717204705, 'std5', 2, NULL, NULL, NULL, 'no', NULL),
(6, 3706, 200010006, 'Luke John', 717204706, 'std6', 2, NULL, NULL, NULL, 'no', NULL),
(7, 3707, 200010007, 'Arnold Odhiambo', 717204707, 'std7', 2, NULL, NULL, NULL, 'no', '{\"id\":\"JCAdD6QOuUMczv9habIIxNDmUXNzKqarc1lIT2h3nxY\",\"rawId\":\"JCAdD6QOuUMczv9habIIxNDmUXNzKqarc1lIT2h3nxY\",\"type\":\"public-key\",\"response\":{\"clientDataJSON\":\"eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiMjM3MmE5ZmVjZTZjMWNlNTAwNjZkZTI0MmE1M2VjOGQ3NmE0ZGY1N2Y5YmZkM2I1MWRkZGNlMThhMzlkNGYxZiIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ\",\"attestationObject\":\"o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVkBZ0mWDeWIDoxodDQXD2R2YFuP5K65ooYyx5lc87qDHZdjRQAAAAAAAAAAAAAAAAAAAAAAAAAAACAkIB0PpA65QxzO_2FpsgjE0OZRc3MqpqtzWUhPaHefFqQBAwM5AQAgWQEA49qDDqvhqA7grTTuwe5z-FPSGFQTI6KTfw5jGyDr-dWrN_-hykh8qAsMZTVzVyS7KMNGUHG0Dc_HKfgZ9XbcszVCbdwuEWqu3KXJ9C8VQIhxwQ2yM38eQ-5jRCMkxRq4tB-_rsWkZzpn4P_w0Sw9j-YASJ8vEhIKTJWWgbZKp_mAM7f1uMHdiI-1oVwxYBtmEgHGepPICwy-bW7E1lcADfDZU6u_osGRkbJ2IdSjWYUVl2MWEUYNWazK14grLfdbRtKjzb5Vp8gB_ti1cjldYNCImGSNM7r2CTpdcDn799EE2d2Pjn7kOFPBISwCNXjSJpgDTQSL1PuvMqNyxwDppSFDAQAB\"}}'),
(8, 3708, 200010008, 'Mary Mutua', 717204708, 'std8', 2, NULL, NULL, NULL, 'no', NULL),
(9, 3709, 200010009, 'Jeff Hardy', 717204709, 'std9', 2, NULL, NULL, NULL, 'no', NULL),
(10, 3710, 200010010, 'Elizabeth Njeri', 717204710, 'std10', 2, NULL, NULL, NULL, 'no', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(10) NOT NULL,
  `subject_name` varchar(50) NOT NULL,
  `semester` int(5) NOT NULL,
  `faculty_id` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `semester`, `faculty_id`) VALUES
(1, 'BCS2418', 'Systems Project', 2, 1001),
(2, 'BCS2421', 'Computer Security & Cryptography', 2, 1002),
(3, 'BCS2424', 'Auditing of Information Systems', 2, 1003),
(4, 'BCS2425', 'Data Mining & Business Intelligence', 2, 1001),
(5, 'BHR2427', 'Human Resource Management', 2, 1004),
(6, 'BIT2422', 'Advanced Network Concepts', 2, 1002),
(7, 'BIT2426', 'Information Resource Management', 2, 1003),
(8, 'UCU2403', 'Entrepreneurship & Innovation', 2, 1004);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`subject_id`,`date`,`attendance_time`),
  ADD KEY `fk_subject` (`subject_id`),
  ADD KEY `fk_faculty` (`faculty_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`f_id`);

--
-- Indexes for table `present_table`
--
ALTER TABLE `present_table`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`s_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `f_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `present_table`
--
ALTER TABLE `present_table`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `s_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`f_id`),
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`s_id`),
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
