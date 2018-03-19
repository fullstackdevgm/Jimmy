-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 25, 2013 at 05:12 PM
-- Server version: 5.5.31
-- PHP Version: 5.3.25

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ppc`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE IF NOT EXISTS `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admin_user`
--

INSERT INTO `admin_user` (`id`, `user_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE IF NOT EXISTS `client` (
  `user_id` int(11) NOT NULL,
  `adwords_client_id` varchar(25) NOT NULL,
  `parent` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`user_id`, `adwords_client_id`, `parent`) VALUES
(27, '437-446-6327', 31),
(28, '453-776-4239', 0),
(29, '340-007-0423', 52),
(34, '123456', 11),
(35, '1223232323', 31),
(36, '12344', NULL),
(37, '857-960-3029', 31),
(38, '12345', 31),
(54, '123451111', 53),
(55, '', 11);

-- --------------------------------------------------------

--
-- Table structure for table `client_report_subscription`
--

CREATE TABLE IF NOT EXISTS `client_report_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `type` enum('Weekly','Monthly') NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `client_report_subscription`
--


-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE IF NOT EXISTS `report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `campaigns` text NOT NULL,
  `report_type` int(11) NOT NULL,
  `date_range` int(11) NOT NULL,
  `metrics_type` int(11) NOT NULL,
  `metrics` int(11) NOT NULL,
  `format` int(11) NOT NULL,
  `compare` int(11) NOT NULL,
  `metrics_type_compare` int(11) NOT NULL,
  `metrics_compare` int(11) NOT NULL,
  `segment` int(11) NOT NULL,
  `notes` text NOT NULL,
  `raw_data` varchar(255) DEFAULT NULL,
  `kpi` varchar(255) NOT NULL,
  `client_id` int(11) NOT NULL,
  `status` enum('0','1') NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`id`, `title`, `campaigns`, `report_type`, `date_range`, `metrics_type`, `metrics`, `format`, `compare`, `metrics_type_compare`, `metrics_compare`, `segment`, `notes`, `raw_data`, `kpi`, `client_id`, `status`, `date_added`, `date_updated`) VALUES
(5, 'Test Report 1', '', 1, 5, 1, 2, 2, 1, 3, 3, 2, '0', '', '', 7, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'Home page', '', 0, 1, 1, 2, 1, 0, 1, 2, 0, '0', '', '', 15, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'Home page', '', 1, 1, 1, 4, 1, 0, 0, 0, 1, '0', '', '', 7, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 'Mitronics Custom Report', '58506305,62416265', 1, 9, 1, 1, 1, 0, 0, 0, 1, '0', '1', '1,2', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 'Mitronics Report', '58506305,62416265,58507145,58506065,58506545,62416025', 4, 9, 1, 1, 1, 0, 0, 0, 1, '0', '1', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 'Sentia', '145778852', 1, 1, 1, 2, 1, 0, 0, 0, 1, '0', '1,2', '', 28, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(21, 'New Report With Compare', '58506305,62416265,58507145,58506065,58506545,62416025', 1, 5, 1, 1, 1, 1, 1, 2, 1, '0', '1,2,3,4,5,6', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(22, 'Ad Report For Hp Printers Gen', '58506065', 4, 5, 1, 1, 1, 0, 0, 0, 1, '0', '1,2,3,4,5,6', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(23, 'New Report for This Month', '58506305,62416265,58507145,58506065,116497145,58506545,62416025', 1, 12, 1, 1, 1, 1, 1, 2, 1, '0', '1,2,3,4,5,6', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(24, 'New Report', '77090839,77091319,101035639,105026719,77091439,71506159,114809839,86756719,115282759', 1, 5, 1, 1, 1, 0, 0, 0, 1, '0', '1,2,3,4,5,8,10', '1,2,3,5', 29, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(25, 'Mitronics Custom Report123', '58506305,62416265', 4, 9, 1, 1, 27, 0, 0, 0, 27, '0', '1', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(26, 'New Report With Conversion', '58506305,62416265,58507145,58506065,116497145,58506545,62416025', 1, 5, 1, 1, 1, 0, 0, 0, 1, '0', '1,2,3,4,5,6,8,9,10', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(27, 'New Report for Avg Pos', '58506305,62416265,58507145,58506065,116497145,58506545,62416025', 1, 5, 1, 1, 1, 0, 0, 0, 1, '0', '6,8,9,10', '', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(28, 'KPI Test', '58506305,62416265,58507145,58506065,116497145,58506545,62416025', 1, 5, 1, 1, 1, 0, 0, 0, 1, '0', '1,4,6', '1,2,3,4,5', 27, '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('0','1') DEFAULT '1' COMMENT '0 = Disabled, 1 = Enabled',
  `notes` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `title`, `status`, `notes`, `created`, `updated`) VALUES
(1, 29, 'test', '1', NULL, '2013-06-03 06:27:53', '2013-06-03 06:27:53'),
(2, 27, 'Test 124', '1', NULL, '2013-06-03 06:28:23', '2013-06-03 06:28:23'),
(3, 27, 'asasas', '1', NULL, '2013-06-03 06:35:20', '2013-06-03 06:35:20'),
(4, 27, '', '1', NULL, '2013-06-04 11:50:59', '2013-06-04 11:50:59'),
(5, 27, '', '1', NULL, '2013-06-04 11:52:38', '2013-06-04 11:52:38'),
(6, 27, '', '1', NULL, '2013-06-04 11:59:51', '2013-06-04 11:59:51'),
(7, 27, '', '1', NULL, '2013-06-04 12:00:16', '2013-06-04 12:00:16'),
(8, 27, '', '1', NULL, '2013-06-04 12:00:48', '2013-06-04 12:00:48'),
(9, 29, '', '1', NULL, '2013-06-04 12:01:01', '2013-06-04 12:01:01'),
(10, 29, '', '1', NULL, '2013-06-04 12:03:07', '2013-06-04 12:03:07'),
(11, 29, 'asasas', '1', NULL, '2013-06-04 12:03:45', '2013-06-04 12:03:45'),
(12, 27, '', '1', NULL, '2013-06-04 01:23:46', '2013-06-04 01:23:46'),
(13, 27, '', '1', NULL, '2013-06-04 01:24:39', '2013-06-04 01:24:39'),
(14, 27, '', '1', NULL, '2013-06-05 03:53:11', '2013-06-05 03:53:11'),
(15, 27, 'test 123', '1', NULL, '2013-06-05 03:54:13', '2013-06-05 03:54:13'),
(16, 27, '', '1', NULL, '2013-06-05 03:54:56', '2013-06-05 03:54:56'),
(17, 27, 'test 123', '1', NULL, '2013-06-05 03:55:22', '2013-06-05 03:55:22'),
(18, 27, 'New Report', '1', NULL, '2013-06-05 03:56:06', '2013-06-05 03:56:06'),
(19, 27, 'New Report 123', '1', NULL, '2013-06-05 03:57:35', '2013-06-05 03:57:35'),
(20, 27, '', '1', NULL, '2013-06-05 03:58:07', '2013-06-05 03:58:07'),
(21, 27, '', '1', NULL, '2013-06-05 03:58:50', '2013-06-05 03:58:50'),
(22, 27, '', '1', NULL, '2013-06-05 03:59:18', '2013-06-05 03:59:18'),
(23, 27, '', '1', NULL, '2013-06-05 04:00:04', '2013-06-05 04:00:04'),
(24, 27, 'Test1222', '1', NULL, '2013-06-05 04:01:18', '2013-06-05 04:01:18'),
(25, 27, 'New Report 12345', '1', NULL, '2013-06-05 04:01:34', '2013-06-05 04:01:34'),
(26, 29, 'Blank Report', '1', NULL, '2013-06-05 04:02:01', '2013-06-05 04:02:01'),
(27, 29, 'New Adwords Report', '1', NULL, '2013-06-05 04:08:45', '2013-06-05 04:08:45'),
(28, 27, '', '1', NULL, '2013-06-06 11:05:08', '2013-06-06 11:05:08'),
(29, 27, 'New One', '1', NULL, '2013-06-06 11:11:59', '2013-06-06 11:11:59'),
(30, 27, '', '1', NULL, '2013-06-06 11:12:45', '2013-06-06 11:12:45'),
(31, 29, 'asasasa', '1', NULL, '2013-06-06 03:51:11', '2013-06-06 03:51:11');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `display_name` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(128) NOT NULL,
  `type` enum('admin','client','agency') DEFAULT 'client',
  `state` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=56 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `display_name`, `name`, `password`, `type`, `state`) VALUES
(11, NULL, 'webgig.adwords@gmail.com', NULL, 'Sagar Bhandari', '$2y$14$jSiFNgFbqqS8M/5hJIUAKesrLdlwKXy4SbfyZkacX9BN.wWJ5dcai', 'admin', 1),
(27, NULL, 'admin@mitronics.com.au', NULL, 'Mitronics Pty. Ltd', '$2y$14$jSiFNgFbqqS8M/5hJIUAKesrLdlwKXy4SbfyZkacX9BN.wWJ5dcai', 'client', 1),
(28, NULL, 'sentia@gmail.com', NULL, 'Sentia', '$2y$14$2dFipjD/jUXHDPqHkUEFfu7fMTmQs1Pkw9IUBaDHwsncgWQiWXDZ.', 'client', 1),
(29, NULL, 'admin@dentallounge.com', NULL, 'DentalLounge', '$2y$14$Rz4zWgSOo3uJFyru6SasdejUwt9qvHl/Ev10E1mWKQy98gUcWZ85u', 'client', 1),
(30, '', 'webgig.sagar@gmail.com', NULL, 'Sagar Bhandari', '', 'agency', 1),
(31, NULL, 'jimmy@globalrevgen.com.au', NULL, 'Global RevGen 123', '$2y$14$/AtS//JN77DzjxY54CJKO.JNwDRwl1eg225OvxODtnglKYavgEWx2', 'agency', 1),
(32, NULL, 'admin@agencyone.com.au', NULL, 'Agency One', '$2y$14$KAORfr8C2zWflpEJCSLWbuUmG4XqLdL8LuwKeOOYzSVDFWdSJpdd2', 'agency', 1),
(33, NULL, 'admin@testagency.com.au', NULL, 'TestAgency', '$2y$14$9radXZznCQf0br1vHzfXa.Ln5GN5d5s8CqT9aByjZDvL2DzGjZc0.', 'agency', 1),
(34, NULL, 'test@test.com.au', NULL, 'Test Client', '$2y$14$eNsUfQDTgjsZQx3hN59NSO2U5hl5hUX0W8WzFTxGR2Ns8bJSBSuIm', 'client', 1),
(35, NULL, 'new@client.com.au', NULL, 'New Client For Global Revgen', '$2y$14$/aujfVUP.r10XsEjpVmMs.Sm7IDhXOPXMQyjQ4t2lNSegt5iIpK8q', 'client', 1),
(36, NULL, 'test@wmc.com.au', NULL, 'test', '$2y$14$RvYOOna0Y0OzmteL9JlvZur7ev5d3snbScxkYgWXLASuBO77.wN4y', 'client', 1),
(37, NULL, 'willwrontier2013v2@gmail.com', NULL, 'William', '$2y$14$h/VuoNjn67Iy/oY4V1ZuxOUvEiujUI6ZqxE/c8WJfkw9Z6njKU6jG', 'client', 1),
(38, NULL, 'testin123@client.com', NULL, 'Testing 123', '$2y$14$jSiFNgFbqqS8M/5hJIUAKesrLdlwKXy4SbfyZkacX9BN.wWJ5dcai', 'client', 1),
(52, NULL, 'sagar@webmarketers.com.au', 'Sagar Bhandari', 'Sagar Bhandari', 'googleToLocalUser', 'agency', 1),
(53, NULL, 'abc@sasas.com', NULL, 'Testing', '$2y$14$We7JOkXjhXFxSeKu0rONuuvKWskrRPXesI9TcWy0VSaLObO4wz2w.', 'agency', 1),
(54, NULL, 'abc@sasas11.com', NULL, 'Test Client1', '$2y$14$cxSItx27NEizBTo5569t1uhUg6bYdrVT8U.zjXvlCX/.eQpkxxlV.', 'client', 1),
(55, NULL, '', NULL, '', '$2y$14$wOcLSRjvtSwjCt3nqtQF8e92m6O3DVSrtWl9pMoG.jE3O7lnwhmFK', 'client', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_meta`
--

CREATE TABLE IF NOT EXISTS `user_meta` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`,`user_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `user_meta`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_provider`
--

CREATE TABLE IF NOT EXISTS `user_provider` (
  `user_id` int(11) NOT NULL,
  `provider_id` varchar(50) NOT NULL,
  `provider` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`provider_id`),
  UNIQUE KEY `provider_id` (`provider_id`,`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_provider`
--

INSERT INTO `user_provider` (`user_id`, `provider_id`, `provider`) VALUES
(52, '105143728409084940937', 'google'),
(30, '107786249318764355887', 'google');

-- --------------------------------------------------------

--
-- Table structure for table `widget`
--

CREATE TABLE IF NOT EXISTS `widget` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('kpi','graph','table','notes') NOT NULL,
  `sub_type` varchar(100) DEFAULT NULL,
  `fields` text NOT NULL,
  `comments` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `widget`
--

INSERT INTO `widget` (`id`, `report_id`, `title`, `type`, `sub_type`, `fields`, `comments`, `created`, `updated`, `order`, `status`) VALUES
(3, 10, 'Test', '', NULL, 'Array', NULL, '2013-06-05 11:14:25', '2013-06-05 03:48:28', 4, '1'),
(4, 10, 'Test', '', NULL, 'Array', NULL, '2013-06-05 11:14:57', '2013-06-05 03:48:28', 3, '1'),
(5, 10, 'Test', '', NULL, 'Array', NULL, '2013-06-05 11:16:04', '2013-06-05 03:48:28', 2, '1'),
(6, 10, 'asasas', '', NULL, 'Array', NULL, '2013-06-05 11:17:01', '2013-06-05 03:48:28', 1, '1'),
(7, 10, 'test', 'kpi', NULL, 'Array', NULL, '2013-06-05 11:20:05', '2013-06-05 03:48:28', 0, '1'),
(8, 10, 'Test Widget', 'kpi', NULL, 'a:2:{s:9:"campaigns";a:9:{i:0;s:8:"77090839";i:1;s:8:"77091319";i:2;s:9:"101035639";i:3;s:9:"105026719";i:4;s:8:"77091439";i:5;s:8:"71506159";i:6;s:9:"114809839";i:7;s:8:"86756719";i:8;s:9:"115282759";}s:3:"kpi";a:4:{i:0;s:1:"3";i:1;s:1:"4";i:2;s:1:"5";i:3;s:1:"6";}}', NULL, '2013-06-05 11:25:04', '2013-06-05 03:48:28', 5, '1'),
(14, 10, 'Test', 'kpi', NULL, 'a:2:{s:9:"campaigns";a:2:{i:0;s:8:"77090839";i:1;s:8:"77091319";}s:3:"kpi";a:3:{i:0;s:1:"3";i:1;s:1:"4";i:2;s:1:"5";}}', NULL, '2013-06-05 12:49:51', '2013-06-05 03:48:28', 6, '1'),
(15, 26, 'Widget 123', 'kpi', NULL, 'a:2:{s:9:"campaigns";a:9:{i:0;s:8:"77090839";i:1;s:8:"77091319";i:2;s:9:"101035639";i:3;s:9:"105026719";i:4;s:8:"77091439";i:5;s:8:"71506159";i:6;s:9:"114809839";i:7;s:8:"86756719";i:8;s:9:"115282759";}s:3:"kpi";a:6:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";}}', NULL, '2013-06-05 04:07:30', '2013-06-05 04:07:30', 1, '1'),
(16, 26, 'Widget 123', 'kpi', NULL, 'a:2:{s:9:"campaigns";a:9:{i:0;s:8:"77090839";i:1;s:8:"77091319";i:2;s:9:"101035639";i:3;s:9:"105026719";i:4;s:8:"77091439";i:5;s:8:"71506159";i:6;s:9:"114809839";i:7;s:8:"86756719";i:8;s:9:"115282759";}s:3:"kpi";a:6:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";}}', NULL, '2013-06-05 04:08:20', '2013-06-05 04:08:20', 1, '1'),
(17, 27, 'W1', 'kpi', NULL, 'a:2:{s:9:"campaigns";a:4:{i:0;s:8:"77090839";i:1;s:8:"77091319";i:2;s:9:"101035639";i:3;s:9:"105026719";}s:3:"kpi";a:3:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";}}', NULL, '2013-06-05 04:09:01', '2013-06-05 04:15:33', 2, '1'),
(18, 27, 'W2', 'kpi', NULL, 'a:2:{s:9:"campaigns";a:9:{i:0;s:8:"77090839";i:1;s:8:"77091319";i:2;s:9:"101035639";i:3;s:9:"105026719";i:4;s:8:"77091439";i:5;s:8:"71506159";i:6;s:9:"114809839";i:7;s:8:"86756719";i:8;s:9:"115282759";}s:3:"kpi";a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}}', NULL, '2013-06-05 04:09:23', '2013-06-05 04:15:33', 1, '1'),
(19, 27, 'Notes 1', 'notes', NULL, 'a:1:{s:5:"notes";s:6:"Notes ";}', NULL, '2013-06-05 04:09:38', '2013-06-05 04:15:33', 3, '1'),
(20, 27, 'Table Widget 1', 'table', NULL, 'a:2:{s:9:"campaigns";a:9:{i:0;s:8:"77090839";i:1;s:8:"77091319";i:2;s:9:"101035639";i:3;s:9:"105026719";i:4;s:8:"77091439";i:5;s:8:"71506159";i:6;s:9:"114809839";i:7;s:8:"86756719";i:8;s:9:"115282759";}s:8:"raw_data";a:4:{i:0;s:1:"6";i:1;s:1:"8";i:2;s:1:"9";i:3;s:2:"10";}}', 'sdsddsdd', '2013-06-05 04:14:49', '2013-06-05 04:15:33', 0, '1'),
(21, 27, 'Testing Table', 'table', NULL, 'a:2:{s:9:"campaigns";a:2:{i:0;s:8:"77090839";i:1;s:8:"77091319";}s:8:"raw_data";a:2:{i:0;s:1:"1";i:1;s:1:"2";}}', NULL, '2013-06-06 01:41:44', '2013-06-06 01:41:44', 1, '1'),
(22, 27, '12345', 'table', NULL, 'N;', NULL, '2013-06-06 02:06:37', '2013-06-06 02:06:37', 1, '1');

-- --------------------------------------------------------

--
-- Table structure for table `widget_fields`
--

CREATE TABLE IF NOT EXISTS `widget_fields` (
  `id` bigint(20) DEFAULT NULL,
  `widget_id` bigint(20) DEFAULT NULL,
  `fields` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `widget_fields`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_provider`
--
ALTER TABLE `user_provider`
  ADD CONSTRAINT `user_provider_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
