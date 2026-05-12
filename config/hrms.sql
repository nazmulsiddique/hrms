-- Adminer 5.4.1 MySQL 8.0.46 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `in_time` datetime DEFAULT NULL,
  `in_latitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `in_longitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `in_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `out_time` datetime DEFAULT NULL,
  `out_latitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `out_longitude` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `out_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `attendance` (`id`, `employee_id`, `in_time`, `in_latitude`, `in_longitude`, `in_image`, `out_time`, `out_latitude`, `out_longitude`, `out_image`, `attendance_date`, `created_at`) VALUES
(1,	'1234',	'2026-04-27 05:47:59',	'23.814236806646043',	'90.43811130424312',	'uploads/attendance/1234_in_1777261679.png',	'2026-04-27 05:48:58',	'23.814256103488017',	'90.43813136580826',	'uploads/attendance/1234_out_1777261738.png',	'2026-04-27',	'2026-04-27 03:47:59'),
(2,	'12345',	'2026-04-27 09:58:39',	'23.814289712115574',	'90.4381501665669',	'uploads/attendance/12345_in_1777262319.png',	'2026-04-27 09:58:58',	'23.814289712115574',	'90.4381501665669',	'uploads/attendance/12345_out_1777262338.png',	'2026-04-27',	'2026-04-27 03:58:39'),
(6,	'12345',	'2026-05-07 08:16:12',	'23.81411604074008',	'90.4378026845757',	'uploads/attendance/12345_in_1778148972.png',	'2026-05-07 18:02:15',	'23.81411604074008',	'90.4378026845757',	'uploads/attendance/12345_out_1778148975.png',	'2026-05-07',	'2026-05-07 10:16:12'),
(7,	'1234',	'2026-05-07 16:25:42',	'23.81410261288805',	'90.43776047977423',	'uploads/attendance/1234_in_1778149542.png',	'2026-05-07 16:26:22',	'23.814106295958677',	'90.43781318140384',	'uploads/attendance/1234_out_1778149582.png',	'2026-05-07',	'2026-05-07 10:25:42');

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `departments` (`id`, `department_name`, `status`, `created_at`, `updated_at`) VALUES
(1,	'ICT',	'active',	'2026-04-25 05:59:32',	'2026-04-25 06:06:19'),
(2,	'Administration',	'active',	'2026-04-25 06:01:58',	'2026-04-25 06:01:58'),
(4,	'HRM',	'inactive',	'2026-04-25 06:05:43',	'2026-04-25 06:06:58');

DROP TABLE IF EXISTS `designations`;
CREATE TABLE `designations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `designation_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `designations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `designations` (`id`, `designation_name`, `department_id`, `status`, `created_at`, `updated_at`) VALUES
(1,	'Junior Officer',	1,	'active',	'2026-04-25 06:08:59',	'2026-04-25 06:08:59'),
(2,	'Director',	2,	'active',	'2026-04-25 06:09:11',	'2026-04-25 06:09:11'),
(3,	'General Manager',	2,	'inactive',	'2026-04-25 06:09:23',	'2026-04-25 06:09:35');

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_father_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_mother_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_personal_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_nid` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `designation_id` int DEFAULT NULL,
  `shift_id` int DEFAULT NULL,
  `employee_joining_date` date DEFAULT NULL,
  `employee_date_of_birth` date DEFAULT NULL,
  `employee_salary` decimal(10,2) DEFAULT NULL,
  `employee_address` text COLLATE utf8mb4_general_ci,
  `employee_education` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_reference_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_reference_mobile` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_role` enum('admin','employee') COLLATE utf8mb4_general_ci DEFAULT 'employee',
  `employee_status` enum('active','inactive','resign') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `fk_emp_department` (`department_id`),
  KEY `fk_emp_designation` (`designation_id`),
  CONSTRAINT `fk_emp_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_emp_designation` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `employees` (`id`, `employee_id`, `employee_name`, `employee_father_name`, `employee_mother_name`, `employee_email`, `employee_phone`, `employee_personal_phone`, `employee_nid`, `department_id`, `designation_id`, `shift_id`, `employee_joining_date`, `employee_date_of_birth`, `employee_salary`, `employee_address`, `employee_education`, `employee_reference_name`, `employee_reference_mobile`, `employee_image`, `employee_password`, `employee_role`, `employee_status`, `created_at`, `updated_at`) VALUES
(1,	'EMP3988',	'Nazmul Siddique',	'Mottaleb',	'Nazma',	'nazmulit92@gmail.com',	'01686690765',	'01729890659',	'423432423',	1,	2,	1,	'2026-04-08',	'1992-01-02',	30000.00,	'Kuripara, Ghatail, Tangail',	'BSC in CSE',	'Own',	'243423',	'EMP_1777108118.png',	NULL,	'',	'active',	'2026-04-25 08:55:03',	'2026-04-26 05:29:48'),
(3,	'EMP8467',	'Sourov Siddique',	'Sohidul Islam',	'Mrs Sultana',	'sourov@gmail.com',	'01929202922999',	'0192828229',	'02383838388888888',	1,	1,	1,	'2026-04-15',	'2023-01-26',	5434.00,	'Kuripara, Ghatail, Tangail',	'BSC in CSE',	'0',	'02832830',	'EMP_1777178419.jpeg',	NULL,	'',	'active',	'2026-04-26 04:40:19',	'2026-04-26 04:40:43'),
(4,	'EMP9425',	'Rohim Mia',	'dfasdf ',	'afa sdfadf',	'nazmulit92@gmail.com',	'01686690765',	'01729890659',	'02383838388888888',	1,	1,	1,	'2026-04-07',	'2025-12-03',	432423.00,	'Kuripara, Ghatail, Tangail',	'BSC in CSE',	'nalfas',	'02832830',	'EMP_1777178749.png',	NULL,	'',	'active',	'2026-04-26 04:45:49',	'2026-04-26 04:57:14'),
(5,	'EMP9716',	'dfasdfasd ',	'afdsfafd',	'af asdfsadf',	'nazmulit92@gmail.com',	'afasdf',	'adsfasdf',	'0',	1,	1,	1,	'2026-04-02',	'2025-12-19',	2432.00,	'Kuripara, Ghatail, Tangail',	'dasfsdf',	'sdfasdf',	'adfadsfads',	'EMP_1777179686.jpg',	NULL,	'',	'active',	'2026-04-26 05:01:26',	'2026-04-26 05:01:26'),
(6,	'123',	'Sakibul',	'Sohidul Islam',	'Mrs Sultana',	'nazmulit92@gmail.com',	'01686690765',	'01729890659',	'2383838388888888',	2,	2,	1,	'2026-04-09',	'2026-04-01',	434234.00,	'Kuripara, Ghatail, Tangail',	'BSC in CSE',	'Nazmul',	'02832830',	'EMP_1777179748.jpg',	'$2y$10$7qdqgZuaHKDrAROZHV8f9eydlQ0J.tOiIkWJtMlrqY0V9vu5nxTRi',	'employee',	'active',	'2026-04-26 05:02:28',	'2026-05-12 05:25:16'),
(7,	'12345',	'fadfasd',	'fadsfasdf',	' asdfadsf',	'nazmulit92@gmail.com',	'01686690765',	'42342342',	'2383838388888888',	2,	2,	1,	'2026-04-01',	'2026-04-02',	42342.00,	'Kuripara, Ghatail, Tangail',	'BSC in CSE',	'dfasdfasd ',	'4324234',	'EMP_1777184434.png',	'$2y$10$9jK9JeAWZZEuYidp.gUc6e.93bPathH7cGbmdgUcDNUMNVB4eH5Be',	'admin',	'active',	'2026-04-26 06:20:34',	'2026-04-27 03:55:05'),
(8,	'1234',	'AAAAAAAA',	' adsfadsf ',	'asdfasdfasd',	'nazmulit92@gmail.com',	'01686690765',	'09292282828',	'2383838388888888',	1,	2,	1,	'2026-04-15',	'2025-09-03',	43343.00,	'Kuripara, Ghatail, Tangail',	'BSC in CSE',	'dfadsfadsdfasdf',	'dasfasdfsf',	'EMP_1777191818.png',	'$2y$10$DYgM7ndqpSaA6t18f6l4FOGuzsQPgjMXul9mzhpHeCkBc2eSPmQIq',	'employee',	'active',	'2026-04-26 08:23:38',	'2026-04-27 03:27:09');

DROP TABLE IF EXISTS `leave_balance`;
CREATE TABLE `leave_balance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year` year DEFAULT NULL,
  `cl_balance` int DEFAULT '0',
  `ml_balance` int DEFAULT '0',
  `bl_balance` int DEFAULT '0',
  `with_pay_balance` int DEFAULT '0',
  `without_pay_balance` int DEFAULT '0',
  `others_balance` int DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `leave_balance` (`id`, `employee_id`, `year`, `cl_balance`, `ml_balance`, `bl_balance`, `with_pay_balance`, `without_pay_balance`, `others_balance`, `status`, `created_at`, `updated_at`) VALUES
(1,	'1234',	'2026',	10,	14,	1,	0,	0,	2,	'active',	'2026-05-07 11:54:27',	'2026-05-07 11:54:45'),
(2,	'123',	'2026',	7,	11,	2,	0,	20,	0,	'active',	'2026-05-12 05:25:46',	NULL);

DROP TABLE IF EXISTS `leaves`;
CREATE TABLE `leaves` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `leave_type` enum('CL','ML','AL','BL','OTHERS') COLLATE utf8mb4_general_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int DEFAULT '0',
  `address_during_leave` text COLLATE utf8mb4_general_ci,
  `reason` text COLLATE utf8mb4_general_ci,
  `leave_status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `leaves` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `address_during_leave`, `reason`, `leave_status`, `created_at`, `updated_at`) VALUES
(1,	'1234',	'CL',	'2026-05-06',	'2026-05-07',	2,	'                    Home                               ',	'                    Personal                                                    ',	'approved',	'2026-05-07 11:39:49',	'2026-05-12 05:07:41'),
(2,	'1234',	'BL',	'2026-05-12',	'2026-05-12',	1,	'',	'',	'pending',	'2026-05-12 05:06:57',	'2026-05-12 05:07:30');

DROP TABLE IF EXISTS `shifts`;
CREATE TABLE `shifts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_after` time DEFAULT NULL,
  `early_leave_before` time DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `shifts` (`id`, `shift_name`, `start_time`, `end_time`, `late_after`, `early_leave_before`, `status`, `created_at`, `updated_at`) VALUES
(1,	'General',	'09:00:00',	'18:00:00',	'09:10:00',	'18:00:00',	'active',	'2026-04-26 04:07:06',	'2026-04-26 04:07:29');

-- 2026-05-12 06:16:24 UTC
