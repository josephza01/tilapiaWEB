-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 12, 2025 at 07:16 PM
-- Server version: 5.5.62-MariaDB
-- PHP Version: 8.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ponglert_tilapia_farm`
--

-- --------------------------------------------------------

--
-- Table structure for table `nodelog_farm`
--

CREATE TABLE `nodelog_farm` (
  `id_nodelog` int(11) NOT NULL COMMENT 'id ของ Node Log',
  `id_node` int(11) NOT NULL COMMENT 'id ของ Node',
  `timeon_log` datetime NOT NULL COMMENT 'เวลาบันทึก log',
  `temp_nodelog` float NOT NULL COMMENT 'อุณหภูมิในอากาศ',
  `hum_nodelog` float NOT NULL COMMENT 'ความชื้นในอากาศ',
  `do_nodelog` float NOT NULL COMMENT 'ค่าออกซิเจนในน้ำ',
  `ph_nodelog` float NOT NULL COMMENT 'ค่า PH ในน้ำ',
  `tempw_nodelog` float NOT NULL COMMENT 'ค่าอุณหภูมิในน้ำ',
  `pump_nodelog` tinyint(1) NOT NULL COMMENT 'สถานะการ เปิด/ปิด ปั๊ม 0 = ปิด, 1 = เปิด',
  `alert_nodelog` tinyint(4) NOT NULL COMMENT '	แจ้งเตือนระดับคุณภาพน้ำ 1 = ปกติ 2 = เฝ้าระวัง 3 = ผิดปกติ',
  `rssi_log` smallint(6) NOT NULL COMMENT 'ระดับความแรงสัญญาณ Rssi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `nodelog_farm`
--

INSERT INTO `nodelog_farm` (`id_nodelog`, `id_node`, `timeon_log`, `temp_nodelog`, `hum_nodelog`, `do_nodelog`, `ph_nodelog`, `tempw_nodelog`, `pump_nodelog`, `alert_nodelog`, `rssi_log`) VALUES
(25107, 1, '2025-03-04 09:02:31', -1, -1, -1, -1, -1, 0, 3, -65),
(25108, 1, '2025-03-04 09:03:23', -1, -1, -1, -1, -1, 0, 3, -65),
(25109, 1, '2025-03-04 09:07:55', -1, -1, -1, -1, -1, 0, 3, -35),
(25110, 1, '2025-03-04 09:09:17', -1, -1, -1, -1, -1, 0, 3, -53),
(25111, 1, '2025-03-04 09:09:35', -1, -1, -1, -1, -1, 0, 3, -40),
(25112, 1, '2025-03-04 09:10:47', -1, -1, -1, -1, -1, 0, 3, -47),
(25113, 1, '2025-03-04 09:11:35', -1, -1, -1, -1, -1, 0, 3, -53),
(25114, 1, '2025-03-04 09:12:04', -1, -1, -1, -1, -1, 0, 3, -43),
(25115, 1, '2025-03-04 09:12:48', -1, -1, -1, -1, -1, 0, 3, -39),
(25116, 1, '2025-03-04 09:13:47', -1, -1, -1, -1, -1, 0, 3, -39),
(25117, 1, '2025-03-04 09:14:10', -1, -1, -1, -1, -1, 0, 3, -38),
(25118, 1, '2025-03-04 09:14:33', -1, -1, -1, -1, -1, 0, 3, -46),
(25119, 1, '2025-03-04 09:15:02', -1, -1, -1, -1, -1, 0, 3, -43),
(25120, 1, '2025-03-04 09:15:55', -1, -1, -1, -1, -1, 0, 3, -42),
(25121, 1, '2025-03-04 09:16:08', -1, -1, -1, -1, -1, 0, 3, -42),
(25122, 1, '2025-03-04 09:17:00', -1, -1, -1, -1, -1, 0, 3, -36),
(25123, 1, '2025-03-04 09:17:48', -1, -1, -1, -1, -1, 0, 3, -37),
(25124, 1, '2025-03-04 09:18:23', -1, -1, -1, -1, -1, 0, 3, -37),
(25125, 1, '2025-03-04 09:18:37', -1, -1, -1, -1, -1, 0, 3, -38),
(25126, 1, '2025-03-04 09:19:10', -1, -1, -1, -1, -1, 0, 3, -37),
(25127, 1, '2025-03-04 09:20:47', -1, -1, -1, -1, -1, 0, 3, -39),
(25128, 1, '2025-03-04 09:21:21', -1, -1, -1, -1, -1, 0, 3, -37),
(25129, 1, '2025-03-04 09:21:43', -1, -1, -1, -1, -1, 0, 3, -37),
(25130, 1, '2025-03-04 09:22:36', -1, -1, -1, -1, -1, 0, 3, -36),
(25131, 1, '2025-03-04 09:22:59', -1, -1, -1, -1, -1, 0, 3, -34),
(25132, 1, '2025-03-04 09:23:33', -1, -1, -1, -1, -1, 0, 3, -34),
(25133, 1, '2025-03-04 09:24:24', -1, -1, -1, -1, -1, 0, 3, -34),
(25134, 1, '2025-03-04 09:25:35', -1, -1, -1, -1, -1, 0, 3, -39),
(25135, 1, '2025-03-04 09:26:13', -1, -1, -1, -1, -1, 1, 3, -41),
(25136, 1, '2025-03-04 09:27:07', -1, -1, -1, -1, -1, 1, 3, -36),
(25137, 1, '2025-03-04 09:27:40', -1, -1, -1, -1, -1, 1, 3, -40),
(25138, 1, '2025-04-14 06:58:10', 30, 40, 7.3, 6.2, 32, 1, 2, 123),
(25139, 1, '2025-04-22 10:33:20', 30, 40, 7.3, 6.2, 32, 0, 2, 123),
(25140, 1, '2025-04-22 14:03:12', 23, 51, -1, 6.6, -1, 0, 3, -41),
(25141, 1, '2025-04-22 14:04:24', 24, 50, -1, 6.5, -1, 0, 3, -43),
(25142, 1, '2025-04-22 14:06:17', 24, 50, -1, 6.7, -1, 0, 3, -42),
(25143, 1, '2025-04-22 14:08:17', 24, 50, -1, 6.9, -1, 0, 3, -42),
(25144, 1, '2025-04-22 14:11:02', 24, 50, -1, 6.9, -1, 1, 3, -44),
(25145, 1, '2025-04-22 14:11:31', 24, 49, -1, 6.9, -1, 1, 3, -51),
(25146, 1, '2025-04-22 14:14:32', 24, 49, -1, 6.9, -1, 1, 3, -52),
(25147, 1, '2025-04-22 14:15:18', 24, 49, -1, 6.9, -1, 1, 3, -43),
(25148, 1, '2025-04-22 14:17:03', 24, 49, -1, 6.9, -1, 0, 3, -47),
(25149, 1, '2025-04-24 14:59:39', 26, 56, -1, 5.8, -1, 0, 3, -51),
(25150, 1, '2025-05-30 09:23:35', 30, 40, 7.3, 6.2, 32, 0, 2, 123),
(25151, 1, '2025-05-30 09:25:14', 30, 40, 7.3, 6.2, 32, 0, 2, 123),
(25152, 1, '2025-05-30 09:25:19', 30, 40, 7.3, 6.2, 32, 0, 2, 0),
(25153, 1, '2025-05-30 09:25:35', 30, 40, 7.3, 6.2, 32, 0, 2, 123);

-- --------------------------------------------------------

--
-- Table structure for table `node_farm`
--

CREATE TABLE `node_farm` (
  `id_node` int(11) NOT NULL COMMENT 'id ของ Node',
  `code_node` varchar(10) NOT NULL COMMENT 'รหัส Node',
  `name_node` varchar(50) NOT NULL COMMENT 'ชื่อโหนด',
  `temp_node` float NOT NULL COMMENT 'อุณหภูมิในอากาศ',
  `hum_node` float NOT NULL COMMENT 'ความชื้นในอากาศ',
  `do_node` float NOT NULL COMMENT 'ค่าออกซิเจนในน้ำ',
  `ph_node` float NOT NULL COMMENT 'ค่า PH ในน้ำ',
  `tempw_node` float NOT NULL COMMENT 'ค่าอุณหภูมิในน้ำ',
  `pump_node` tinyint(1) NOT NULL COMMENT 'สถานะการ เปิด/ปิด ปั๊ม 0 = ปิด, 1 = เปิด',
  `alert_node` tinyint(4) NOT NULL COMMENT 'แจ้งเตือนระดับคุณภาพน้ำ\r\n1 = ปกติ\r\n2 = เฝ้าระวัง\r\n3 = ผิดปกติ 4 = อุปกรณ์ผิดปกติ',
  `laston_node` datetime NOT NULL COMMENT 'เวลาอัพเดทข้อมูลล่าสุด',
  `id_user` int(11) NOT NULL COMMENT 'รหัสผู้ใช้'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `node_farm`
--

INSERT INTO `node_farm` (`id_node`, `code_node`, `name_node`, `temp_node`, `hum_node`, `do_node`, `ph_node`, `tempw_node`, `pump_node`, `alert_node`, `laston_node`, `id_user`) VALUES
(1, 'T3dDhS', 'จุดตรวจน้ำที่ 1', 30, 40, 7.3, 6.2, 32, 0, 2, '2025-05-30 09:25:35', 1),
(2, 'TiFhVw', 'จุดตรวจน้ำที่ 2', 29, 66, 7.8, 6.5, 33, 0, 1, '2025-05-15 08:59:39', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_tb`
--

CREATE TABLE `user_tb` (
  `id_user` int(11) NOT NULL COMMENT 'id ของ User',
  `name_user` varchar(60) NOT NULL COMMENT 'ชื่อของผู้ใช้',
  `email_user` varchar(100) NOT NULL COMMENT 'อีเมล์ของผู้ใช้',
  `pass_user` varchar(30) NOT NULL COMMENT 'รหัสผ่านของผู้ใช้',
  `tel_user` varchar(12) NOT NULL COMMENT 'เบอร์ของผู้ใช้',
  `name_farm` varchar(50) NOT NULL COMMENT 'ชื่อฟาร์ม',
  `address_farm` text NOT NULL COMMENT 'ที่อยู่ฟาร์ม',
  `zipcode_farm` varchar(6) NOT NULL COMMENT 'รหัสไปรษณีย์ฟาร์ม',
  `county_farm` varchar(50) NOT NULL COMMENT 'ประเทศ',
  `latlon_farm` varchar(50) NOT NULL COMMENT 'ตำแหน่งพิกัดฟาร์ม Ex. 15.236199430122442, 104.8632899561982',
  `alert_auto` tinyint(1) NOT NULL COMMENT 'เปิดการแจ้งเตือนอัตโนมัติ\r\n0 = ไม่เปิดแจ้งเตือน\r\n1 = เปิดแจ้งเตือน',
  `time_alert` tinyint(4) NOT NULL COMMENT 'จำนวนความถี่การแจ้งเตือนทุกกี่นาที',
  `pump_auto` tinyint(1) NOT NULL COMMENT 'เปิดปั๊มอัตโนมัติ 0 = ปิดอัตโนมัติ 1 = เปิดอัตโนมัติ',
  `Token_user` varchar(10) NOT NULL COMMENT 'โทเคนใช้ยืนยันตัวตนเข้าถึงข้อมูลในระบบ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_tb`
--

INSERT INTO `user_tb` (`id_user`, `name_user`, `email_user`, `pass_user`, `tel_user`, `name_farm`, `address_farm`, `zipcode_farm`, `county_farm`, `latlon_farm`, `alert_auto`, `time_alert`, `pump_auto`, `Token_user`) VALUES
(1, 'user02', 'user01@gmail.com', '123456', '0872544401', 'CSuser01', 'addresrcs1', '34001', 'Thai1', '15.236199430122441, 104.8632899561981', 0, 1, 0, 'UiyAwV'),
(3, 'นายวรรณภา นาคูณ', 'wannapa3867@gmail.com', '0898933867', '0898933867', 'นายวรรณภา นาคูณ', '101 บ้านแหลมสวรรค์ ม.1 ตำบลนิคมสร้างตนเองลำโดมน้อย อำเภอสิรินธร จังหวัดอุบลราชธานี', '34350', 'ไทย', '15.230335096601152, 104.85731399454345', 0, 5, 0, 'Ut3Sri');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nodelog_farm`
--
ALTER TABLE `nodelog_farm`
  ADD PRIMARY KEY (`id_nodelog`),
  ADD KEY `id_node` (`id_node`);

--
-- Indexes for table `node_farm`
--
ALTER TABLE `node_farm`
  ADD PRIMARY KEY (`id_node`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `user_tb`
--
ALTER TABLE `user_tb`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nodelog_farm`
--
ALTER TABLE `nodelog_farm`
  MODIFY `id_nodelog` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id ของ Node Log', AUTO_INCREMENT=25154;

--
-- AUTO_INCREMENT for table `node_farm`
--
ALTER TABLE `node_farm`
  MODIFY `id_node` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id ของ Node', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_tb`
--
ALTER TABLE `user_tb`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id ของ User', AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nodelog_farm`
--
ALTER TABLE `nodelog_farm`
  ADD CONSTRAINT `nodelog_farm_ibfk_1` FOREIGN KEY (`id_node`) REFERENCES `node_farm` (`id_node`);

--
-- Constraints for table `node_farm`
--
ALTER TABLE `node_farm`
  ADD CONSTRAINT `node_farm_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user_tb` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
