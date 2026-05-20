-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 18, 2026 at 09:44 AM
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
-- Database: `gems_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `disability_subtypes`
--

CREATE TABLE `disability_subtypes` (
  `disability_subtype_id` int(11) NOT NULL,
  `disability_type_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disability_subtypes`
--

INSERT INTO `disability_subtypes` (`disability_subtype_id`, `disability_type_id`, `name`, `is_active`, `created_at`) VALUES
(1, 1, 'Blind', 1, '2026-05-17 21:29:07'),
(2, 1, 'Low Vision', 1, '2026-05-17 21:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `disability_types`
--

CREATE TABLE `disability_types` (
  `disability_type_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disability_types`
--

INSERT INTO `disability_types` (`disability_type_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Visual Impairment', 1, '2026-05-17 21:29:07'),
(2, 'Hearing Impairment', 1, '2026-05-17 21:29:07'),
(3, 'Learning Disability', 1, '2026-05-17 21:29:07'),
(4, 'Intellectual Disability', 1, '2026-05-17 21:29:07'),
(5, 'Autism Spectrum Disorder', 1, '2026-05-17 21:29:07'),
(6, 'Emotional / Behavioral Disorder', 1, '2026-05-17 21:29:07'),
(7, 'Orthopedic / Physical Handicap', 1, '2026-05-17 21:29:07'),
(8, 'Speech / Language Disorder', 1, '2026-05-17 21:29:07'),
(9, 'Chronic Illness', 1, '2026-05-17 21:29:07'),
(10, 'Others', 1, '2026-05-17 21:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `enrollment_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `mother_tongue_id` int(11) DEFAULT NULL,
  `is_indigenous` tinyint(1) DEFAULT 0,
  `indigenous_group_id` int(11) DEFAULT NULL,
  `is_four_ps_beneficiary` tinyint(1) DEFAULT 0,
  `four_ps_household_id` varchar(100) DEFAULT NULL,
  `is_learner_with_disability` tinyint(1) DEFAULT 0,
  `is_returning_learner` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `school_year`, `enrollment_status`, `mother_tongue_id`, `is_indigenous`, `indigenous_group_id`, `is_four_ps_beneficiary`, `four_ps_household_id`, `is_learner_with_disability`, `is_returning_learner`, `created_at`) VALUES
(3, 6, '2025-2026', 'pending', NULL, 1, NULL, 1, '1', 1, 1, '2026-05-18 01:25:08'),
(4, 7, '2026-2026', 'pending', 4, 1, 1, 1, '345', 1, 1, '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_disabilities`
--

CREATE TABLE `enrollment_disabilities` (
  `enrollment_disability_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `disability_type_id` int(11) NOT NULL,
  `disability_subtype_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_disabilities`
--

INSERT INTO `enrollment_disabilities` (`enrollment_disability_id`, `enrollment_id`, `disability_type_id`, `disability_subtype_id`, `description`, `created_at`) VALUES
(22, 3, 1, 1, NULL, '2026-05-18 01:25:08'),
(23, 3, 2, NULL, NULL, '2026-05-18 01:25:08'),
(24, 3, 3, NULL, NULL, '2026-05-18 01:25:08'),
(25, 3, 4, NULL, NULL, '2026-05-18 01:25:08'),
(26, 3, 5, NULL, NULL, '2026-05-18 01:25:08'),
(27, 3, 6, NULL, NULL, '2026-05-18 01:25:08'),
(28, 3, 7, NULL, NULL, '2026-05-18 01:25:08'),
(29, 3, 8, NULL, NULL, '2026-05-18 01:25:08'),
(30, 3, 9, NULL, NULL, '2026-05-18 01:25:08'),
(31, 3, 10, NULL, NULL, '2026-05-18 01:25:08'),
(32, 4, 1, 1, NULL, '2026-05-18 01:45:28'),
(33, 4, 2, NULL, NULL, '2026-05-18 01:45:28'),
(34, 4, 3, NULL, NULL, '2026-05-18 01:45:28'),
(35, 4, 4, NULL, NULL, '2026-05-18 01:45:28'),
(36, 4, 5, NULL, NULL, '2026-05-18 01:45:28'),
(37, 4, 6, NULL, NULL, '2026-05-18 01:45:28'),
(38, 4, 7, NULL, NULL, '2026-05-18 01:45:28'),
(39, 4, 8, NULL, NULL, '2026-05-18 01:45:28'),
(40, 4, 9, NULL, NULL, '2026-05-18 01:45:28'),
(41, 4, 10, NULL, NULL, '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_family_medical_history`
--

CREATE TABLE `enrollment_family_medical_history` (
  `family_history_id` int(11) NOT NULL,
  `medical_information_id` int(11) NOT NULL,
  `family_history_type_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_family_medical_history`
--

INSERT INTO `enrollment_family_medical_history` (`family_history_id`, `medical_information_id`, `family_history_type_id`, `description`, `created_at`) VALUES
(1, 3, 1, '2, 2', '2026-05-18 01:25:08'),
(2, 3, 2, '2, 2', '2026-05-18 01:25:08'),
(3, 3, 3, '2, 2', '2026-05-18 01:25:08'),
(4, 3, 4, '2, 2', '2026-05-18 01:25:08'),
(5, 3, 5, '2, 2', '2026-05-18 01:25:08'),
(6, 3, 6, '2, 2', '2026-05-18 01:25:08'),
(7, 3, 7, '2, 2', '2026-05-18 01:25:08'),
(8, 3, 8, '2, 2', '2026-05-18 01:25:08'),
(9, 4, 1, '9, 9', '2026-05-18 01:45:28'),
(10, 4, 2, '9, 9', '2026-05-18 01:45:28'),
(11, 4, 3, '9, 9', '2026-05-18 01:45:28'),
(12, 4, 4, '9, 9', '2026-05-18 01:45:28'),
(13, 4, 5, '9, 9', '2026-05-18 01:45:28'),
(14, 4, 6, '9, 9', '2026-05-18 01:45:28'),
(15, 4, 7, '9, 9', '2026-05-18 01:45:28'),
(16, 4, 8, '9, 9', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_medical_allergies`
--

CREATE TABLE `enrollment_medical_allergies` (
  `allergy_id` int(11) NOT NULL,
  `medical_information_id` int(11) NOT NULL,
  `allergy_type_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_medical_allergies`
--

INSERT INTO `enrollment_medical_allergies` (`allergy_id`, `medical_information_id`, `allergy_type_id`, `description`, `created_at`) VALUES
(3, 3, 1, '2', '2026-05-18 01:25:08'),
(4, 3, 2, NULL, '2026-05-18 01:25:08'),
(5, 3, 3, '2', '2026-05-18 01:25:08'),
(6, 3, 4, '2', '2026-05-18 01:25:08'),
(7, 4, 1, '9', '2026-05-18 01:45:28'),
(8, 4, 2, NULL, '2026-05-18 01:45:28'),
(9, 4, 3, '9', '2026-05-18 01:45:28'),
(10, 4, 4, '9', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_medical_conditions`
--

CREATE TABLE `enrollment_medical_conditions` (
  `condition_id` int(11) NOT NULL,
  `medical_information_id` int(11) NOT NULL,
  `condition_type_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_medical_conditions`
--

INSERT INTO `enrollment_medical_conditions` (`condition_id`, `medical_information_id`, `condition_type_id`, `description`, `created_at`) VALUES
(1, 3, 1, '2', '2026-05-18 01:25:08'),
(2, 3, 2, '2', '2026-05-18 01:25:08'),
(3, 3, 3, '2', '2026-05-18 01:25:08'),
(4, 3, 4, '2', '2026-05-18 01:25:08'),
(5, 3, 5, '2', '2026-05-18 01:25:08'),
(6, 3, 6, '2', '2026-05-18 01:25:08'),
(7, 3, 7, '2', '2026-05-18 01:25:08'),
(8, 3, 8, '2', '2026-05-18 01:25:08'),
(9, 4, 1, '9', '2026-05-18 01:45:28'),
(10, 4, 2, '9', '2026-05-18 01:45:28'),
(11, 4, 3, '9', '2026-05-18 01:45:28'),
(12, 4, 4, '9', '2026-05-18 01:45:28'),
(13, 4, 5, '9', '2026-05-18 01:45:28'),
(14, 4, 6, '9', '2026-05-18 01:45:28'),
(15, 4, 7, '9', '2026-05-18 01:45:28'),
(16, 4, 8, '9', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_medical_information`
--

CREATE TABLE `enrollment_medical_information` (
  `medical_information_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `exposed_to_cigarette_vape_smoke` tinyint(1) DEFAULT 0,
  `other_pertinent_information` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_medical_information`
--

INSERT INTO `enrollment_medical_information` (`medical_information_id`, `enrollment_id`, `exposed_to_cigarette_vape_smoke`, `other_pertinent_information`, `created_at`) VALUES
(3, 3, 1, '2', '2026-05-18 01:25:08'),
(4, 4, 1, '9', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_medical_surgeries`
--

CREATE TABLE `enrollment_medical_surgeries` (
  `surgery_id` int(11) NOT NULL,
  `medical_information_id` int(11) NOT NULL,
  `surgery_date` date DEFAULT NULL,
  `hospital_name` varchar(255) DEFAULT NULL,
  `body_part` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_medical_surgeries`
--

INSERT INTO `enrollment_medical_surgeries` (`surgery_id`, `medical_information_id`, `surgery_date`, `hospital_name`, `body_part`, `created_at`) VALUES
(2, 3, '2026-05-06', '2', '2', '2026-05-18 01:25:08'),
(3, 4, '2026-05-18', NULL, '9', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_medical_treatments`
--

CREATE TABLE `enrollment_medical_treatments` (
  `treatment_id` int(11) NOT NULL,
  `medical_information_id` int(11) NOT NULL,
  `treatment_medicine` varchar(255) DEFAULT NULL,
  `schedule_dosage` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_medical_treatments`
--

INSERT INTO `enrollment_medical_treatments` (`treatment_id`, `medical_information_id`, `treatment_medicine`, `schedule_dosage`, `created_at`) VALUES
(2, 3, '2', '2', '2026-05-18 01:25:08'),
(3, 4, '9', '9', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_returning_learners`
--

CREATE TABLE `enrollment_returning_learners` (
  `returning_learner_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `last_grade_level_completed` varchar(50) DEFAULT NULL,
  `last_school_attended` varchar(255) DEFAULT NULL,
  `last_school_year_completed` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_returning_learners`
--

INSERT INTO `enrollment_returning_learners` (`returning_learner_id`, `enrollment_id`, `last_grade_level_completed`, `last_school_attended`, `last_school_year_completed`, `created_at`) VALUES
(1, 3, 'Grade 6', '1', '1', '2026-05-18 01:25:08'),
(2, 4, 'Grade 2', '2', '2', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `family_medical_history_types`
--

CREATE TABLE `family_medical_history_types` (
  `family_history_type_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_medical_history_types`
--

INSERT INTO `family_medical_history_types` (`family_history_type_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Tuberculosis', 1, '2026-05-17 21:29:08'),
(2, 'Cancer', 1, '2026-05-17 21:29:08'),
(3, 'Diabetes Mellitus', 1, '2026-05-17 21:29:08'),
(4, 'Hypertension', 1, '2026-05-17 21:29:08'),
(5, 'Stroke / Heart attack', 1, '2026-05-17 21:29:08'),
(6, 'Depression', 1, '2026-05-17 21:29:08'),
(7, 'Kidney problems', 1, '2026-05-17 21:29:08'),
(8, 'Other', 1, '2026-05-17 21:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `indigenous_groups`
--

CREATE TABLE `indigenous_groups` (
  `indigenous_group_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `indigenous_groups`
--

INSERT INTO `indigenous_groups` (`indigenous_group_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Ibaloi', 1, '2026-05-17 21:29:07'),
(2, 'Kankanaey', 1, '2026-05-17 21:29:07'),
(3, 'Ifugao', 1, '2026-05-17 21:29:07'),
(4, 'Bontoc', 1, '2026-05-17 21:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `medical_allergy_types`
--

CREATE TABLE `medical_allergy_types` (
  `allergy_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_allergy_types`
--

INSERT INTO `medical_allergy_types` (`allergy_type_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Medicine', 1, '2026-05-17 21:29:07'),
(2, 'Pollen', 1, '2026-05-17 21:29:07'),
(3, 'Food', 1, '2026-05-17 21:29:07'),
(4, 'Other', 1, '2026-05-17 21:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `medical_condition_types`
--

CREATE TABLE `medical_condition_types` (
  `condition_type_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_condition_types`
--

INSERT INTO `medical_condition_types` (`condition_type_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Error of refraction', 1, '2026-05-17 21:29:08'),
(2, 'Asthma', 1, '2026-05-17 21:29:08'),
(3, 'Seizure', 1, '2026-05-17 21:29:08'),
(4, 'Heart Illness', 1, '2026-05-17 21:29:08'),
(5, 'Anemia', 1, '2026-05-17 21:29:08'),
(6, 'Bleeding disorder', 1, '2026-05-17 21:29:08'),
(7, 'Fracture / Dislocation', 1, '2026-05-17 21:29:08'),
(8, 'Other', 1, '2026-05-17 21:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `mother_tongues`
--

CREATE TABLE `mother_tongues` (
  `mother_tongue_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mother_tongues`
--

INSERT INTO `mother_tongues` (`mother_tongue_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Ilocano', 1, '2026-05-17 21:29:07'),
(2, 'Tagalog', 1, '2026-05-17 21:29:07'),
(3, 'Kapampangan', 1, '2026-05-17 21:29:07'),
(4, 'Pangasinan', 1, '2026-05-17 21:29:07'),
(5, 'Cebuano', 1, '2026-05-17 21:29:07'),
(6, 'English', 1, '2026-05-17 21:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `parent_guardian_types`
--

CREATE TABLE `parent_guardian_types` (
  `parent_guardian_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parent_guardian_types`
--

INSERT INTO `parent_guardian_types` (`parent_guardian_type_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Father', 1, '2026-05-17 21:29:07'),
(2, 'Mother', 1, '2026-05-17 21:29:07'),
(3, 'Guardian', 1, '2026-05-17 21:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `extension_name` varchar(50) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `lrn`, `last_name`, `first_name`, `middle_name`, `extension_name`, `birth_date`, `sex`, `place_of_birth`, `created_at`) VALUES
(6, 5, '123456789012', '1', '1', '1', '1', '2026-05-18', '', NULL, '2026-05-18 01:25:08'),
(7, 6, '908237445', '2', '2', '2', '2', '1976-02-02', '', '2', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `student_addresses`
--

CREATE TABLE `student_addresses` (
  `address_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `address_type` enum('current','permanent') NOT NULL,
  `house_no` varchar(100) DEFAULT NULL,
  `street_name` varchar(150) DEFAULT NULL,
  `barangay` varchar(150) DEFAULT NULL,
  `subdivision_house_no` varchar(150) DEFAULT NULL,
  `municipality_city` varchar(150) DEFAULT NULL,
  `province` varchar(150) DEFAULT NULL,
  `country` varchar(150) DEFAULT 'Philippines',
  `zip_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_addresses`
--

INSERT INTO `student_addresses` (`address_id`, `student_id`, `address_type`, `house_no`, `street_name`, `barangay`, `subdivision_house_no`, `municipality_city`, `province`, `country`, `zip_code`, `created_at`) VALUES
(5, 6, 'current', '1', '1', '1', NULL, '1', '1', '1', '1', '2026-05-18 01:25:08'),
(6, 6, 'permanent', '1', '1', '1', NULL, '1', '1', '1', '1', '2026-05-18 01:25:08'),
(7, 7, 'current', '2', '2', '2', NULL, '2', '2', '2', '2', '2026-05-18 01:45:28'),
(8, 7, 'permanent', '3', '3', '3', NULL, '3', '3', '3', '3', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `student_medical_records`
--

CREATE TABLE `student_medical_records` (
  `student_medical_record_id` int(11) NOT NULL,
  `school_record_id` int(11) NOT NULL,
  `exposed_to_cigarette_vape_smoke` tinyint(1) DEFAULT 0,
  `other_pertinent_information` text DEFAULT NULL,
  `allergies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allergies`)),
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `surgeries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`surgeries`)),
  `treatments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`treatments`)),
  `family_medical_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`family_medical_history`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_medical_records`
--

INSERT INTO `student_medical_records` (`student_medical_record_id`, `school_record_id`, `exposed_to_cigarette_vape_smoke`, `other_pertinent_information`, `allergies`, `conditions`, `surgeries`, `treatments`, `family_medical_history`, `created_at`) VALUES
(1, 3, 1, '2', '[{\"allergy_type_id\":1,\"description\":\"2\"},{\"allergy_type_id\":2,\"description\":null},{\"allergy_type_id\":3,\"description\":\"2\"},{\"allergy_type_id\":4,\"description\":\"2\"}]', '[{\"condition_type_id\":1,\"description\":\"2\"},{\"condition_type_id\":2,\"description\":\"2\"},{\"condition_type_id\":3,\"description\":\"2\"},{\"condition_type_id\":4,\"description\":\"2\"},{\"condition_type_id\":5,\"description\":\"2\"},{\"condition_type_id\":6,\"description\":\"2\"},{\"condition_type_id\":7,\"description\":\"2\"},{\"condition_type_id\":8,\"description\":\"2\"}]', '[{\"surgery_date\":\"2026-05-06\",\"hospital_name\":\"2\",\"body_part\":\"2\"}]', '[{\"treatment_medicine\":\"2\",\"schedule_dosage\":\"2\"}]', '[{\"family_history_type_id\":1,\"description\":\"2, 2\"},{\"family_history_type_id\":2,\"description\":\"2, 2\"},{\"family_history_type_id\":3,\"description\":\"2, 2\"},{\"family_history_type_id\":4,\"description\":\"2, 2\"},{\"family_history_type_id\":5,\"description\":\"2, 2\"},{\"family_history_type_id\":6,\"description\":\"2, 2\"},{\"family_history_type_id\":7,\"description\":\"2, 2\"},{\"family_history_type_id\":8,\"description\":\"2, 2\"}]', '2026-05-18 01:25:08'),
(2, 4, 1, '9', '[{\"allergy_type_id\":1,\"description\":\"9\"},{\"allergy_type_id\":2,\"description\":null},{\"allergy_type_id\":3,\"description\":\"9\"},{\"allergy_type_id\":4,\"description\":\"9\"}]', '[{\"condition_type_id\":1,\"description\":\"9\"},{\"condition_type_id\":2,\"description\":\"9\"},{\"condition_type_id\":3,\"description\":\"9\"},{\"condition_type_id\":4,\"description\":\"9\"},{\"condition_type_id\":5,\"description\":\"9\"},{\"condition_type_id\":6,\"description\":\"9\"},{\"condition_type_id\":7,\"description\":\"9\"},{\"condition_type_id\":8,\"description\":\"9\"}]', '[{\"surgery_date\":\"2026-05-18\",\"hospital_name\":null,\"body_part\":\"9\"}]', '[{\"treatment_medicine\":\"9\",\"schedule_dosage\":\"9\"}]', '[{\"family_history_type_id\":1,\"description\":\"9, 9\"},{\"family_history_type_id\":2,\"description\":\"9, 9\"},{\"family_history_type_id\":3,\"description\":\"9, 9\"},{\"family_history_type_id\":4,\"description\":\"9, 9\"},{\"family_history_type_id\":5,\"description\":\"9, 9\"},{\"family_history_type_id\":6,\"description\":\"9, 9\"},{\"family_history_type_id\":7,\"description\":\"9, 9\"},{\"family_history_type_id\":8,\"description\":\"9, 9\"}]', '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `student_parent_guardians`
--

CREATE TABLE `student_parent_guardians` (
  `parent_guardian_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `parent_guardian_type_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `is_emergency_contact` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_parent_guardians`
--

INSERT INTO `student_parent_guardians` (`parent_guardian_id`, `student_id`, `parent_guardian_type_id`, `last_name`, `first_name`, `middle_name`, `contact_number`, `occupation`, `is_emergency_contact`, `created_at`) VALUES
(1, 6, 1, '2', '2', '2', '2', NULL, 0, '2026-05-18 01:25:08'),
(2, 6, 2, '2', '2', '2', '2', NULL, 0, '2026-05-18 01:25:08'),
(3, 6, 3, '2', '2', '2', '2', NULL, 0, '2026-05-18 01:25:08'),
(4, 7, 1, '4', '4', '4', '4', NULL, 0, '2026-05-18 01:45:28'),
(5, 7, 2, '6', '6', '6', '6', NULL, 0, '2026-05-18 01:45:28'),
(6, 7, 3, '7', '7', '7', '7', NULL, 0, '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `student_school_records`
--

CREATE TABLE `student_school_records` (
  `school_record_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `academic_status` enum('active','completed','transferred','dropped') DEFAULT 'active',
  `lrn` varchar(50) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `extension_name` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `mother_tongue` varchar(100) DEFAULT NULL,
  `indigenous_group` varchar(150) DEFAULT NULL,
  `four_ps_household_id` varchar(100) DEFAULT NULL,
  `is_learner_with_disability` tinyint(1) DEFAULT 0,
  `is_returning_learner` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_school_records`
--

INSERT INTO `student_school_records` (`school_record_id`, `enrollment_id`, `student_id`, `school_year`, `grade_level`, `academic_status`, `lrn`, `last_name`, `first_name`, `middle_name`, `extension_name`, `birth_date`, `sex`, `place_of_birth`, `mother_tongue`, `indigenous_group`, `four_ps_household_id`, `is_learner_with_disability`, `is_returning_learner`, `created_at`) VALUES
(3, 3, 6, '2025-2026', 'Grade 5', 'active', '123456789012', '1', '1', '1', '1', '2026-05-18', '', NULL, NULL, '', '1', 1, 1, '2026-05-18 01:25:08'),
(4, 4, 7, '2026-2026', 'Grade 2', 'active', '908237445', '2', '2', '2', '2', '1976-02-02', '', '2', 'Pangasinan', 'Ibaloi', '345', 1, 1, '2026-05-18 01:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff','student') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', 'pserdeg@gmail.com', 'admin', 'admin', 1, '2026-05-18 00:06:36'),
(2, 'example', 'example@local.com', '$2y$10$Xo7I.zuoAC1t7uXg2woOFO4kLK40Vt0.IBUnQM7kDHF2kqeINxGJ2', 'staff', 1, '2026-05-18 00:34:20'),
(5, '1.1', '1.1@student.local', '$2y$10$/.4tOjAgHzururIMeQnfTuL7JIUwhGLgI5rDyON3qyDqGdsiEgiT2', 'student', 1, '2026-05-18 01:25:08'),
(6, '2.2', '2.2@student.local', '$2y$10$Xa9aZoQfeomOjvNYDAlJ4uSqbxSouQC4WLSwok0BsHk37ZIwoNOZa', 'student', 1, '2026-05-18 01:45:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `disability_subtypes`
--
ALTER TABLE `disability_subtypes`
  ADD PRIMARY KEY (`disability_subtype_id`),
  ADD KEY `disability_type_id` (`disability_type_id`);

--
-- Indexes for table `disability_types`
--
ALTER TABLE `disability_types`
  ADD PRIMARY KEY (`disability_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `mother_tongue_id` (`mother_tongue_id`),
  ADD KEY `indigenous_group_id` (`indigenous_group_id`);

--
-- Indexes for table `enrollment_disabilities`
--
ALTER TABLE `enrollment_disabilities`
  ADD PRIMARY KEY (`enrollment_disability_id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `disability_type_id` (`disability_type_id`),
  ADD KEY `disability_subtype_id` (`disability_subtype_id`);

--
-- Indexes for table `enrollment_family_medical_history`
--
ALTER TABLE `enrollment_family_medical_history`
  ADD PRIMARY KEY (`family_history_id`),
  ADD KEY `medical_information_id` (`medical_information_id`),
  ADD KEY `family_history_type_id` (`family_history_type_id`);

--
-- Indexes for table `enrollment_medical_allergies`
--
ALTER TABLE `enrollment_medical_allergies`
  ADD PRIMARY KEY (`allergy_id`),
  ADD KEY `medical_information_id` (`medical_information_id`),
  ADD KEY `allergy_type_id` (`allergy_type_id`);

--
-- Indexes for table `enrollment_medical_conditions`
--
ALTER TABLE `enrollment_medical_conditions`
  ADD PRIMARY KEY (`condition_id`),
  ADD KEY `medical_information_id` (`medical_information_id`),
  ADD KEY `condition_type_id` (`condition_type_id`);

--
-- Indexes for table `enrollment_medical_information`
--
ALTER TABLE `enrollment_medical_information`
  ADD PRIMARY KEY (`medical_information_id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `enrollment_medical_surgeries`
--
ALTER TABLE `enrollment_medical_surgeries`
  ADD PRIMARY KEY (`surgery_id`),
  ADD KEY `medical_information_id` (`medical_information_id`);

--
-- Indexes for table `enrollment_medical_treatments`
--
ALTER TABLE `enrollment_medical_treatments`
  ADD PRIMARY KEY (`treatment_id`),
  ADD KEY `medical_information_id` (`medical_information_id`);

--
-- Indexes for table `enrollment_returning_learners`
--
ALTER TABLE `enrollment_returning_learners`
  ADD PRIMARY KEY (`returning_learner_id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `family_medical_history_types`
--
ALTER TABLE `family_medical_history_types`
  ADD PRIMARY KEY (`family_history_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `indigenous_groups`
--
ALTER TABLE `indigenous_groups`
  ADD PRIMARY KEY (`indigenous_group_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `medical_allergy_types`
--
ALTER TABLE `medical_allergy_types`
  ADD PRIMARY KEY (`allergy_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `medical_condition_types`
--
ALTER TABLE `medical_condition_types`
  ADD PRIMARY KEY (`condition_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `mother_tongues`
--
ALTER TABLE `mother_tongues`
  ADD PRIMARY KEY (`mother_tongue_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `parent_guardian_types`
--
ALTER TABLE `parent_guardian_types`
  ADD PRIMARY KEY (`parent_guardian_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `lrn` (`lrn`);

--
-- Indexes for table `student_addresses`
--
ALTER TABLE `student_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_medical_records`
--
ALTER TABLE `student_medical_records`
  ADD PRIMARY KEY (`student_medical_record_id`),
  ADD UNIQUE KEY `school_record_id` (`school_record_id`);

--
-- Indexes for table `student_parent_guardians`
--
ALTER TABLE `student_parent_guardians`
  ADD PRIMARY KEY (`parent_guardian_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `parent_guardian_type_id` (`parent_guardian_type_id`);

--
-- Indexes for table `student_school_records`
--
ALTER TABLE `student_school_records`
  ADD PRIMARY KEY (`school_record_id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `disability_subtypes`
--
ALTER TABLE `disability_subtypes`
  MODIFY `disability_subtype_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `disability_types`
--
ALTER TABLE `disability_types`
  MODIFY `disability_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollment_disabilities`
--
ALTER TABLE `enrollment_disabilities`
  MODIFY `enrollment_disability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `enrollment_family_medical_history`
--
ALTER TABLE `enrollment_family_medical_history`
  MODIFY `family_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `enrollment_medical_allergies`
--
ALTER TABLE `enrollment_medical_allergies`
  MODIFY `allergy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `enrollment_medical_conditions`
--
ALTER TABLE `enrollment_medical_conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `enrollment_medical_information`
--
ALTER TABLE `enrollment_medical_information`
  MODIFY `medical_information_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollment_medical_surgeries`
--
ALTER TABLE `enrollment_medical_surgeries`
  MODIFY `surgery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollment_medical_treatments`
--
ALTER TABLE `enrollment_medical_treatments`
  MODIFY `treatment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollment_returning_learners`
--
ALTER TABLE `enrollment_returning_learners`
  MODIFY `returning_learner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `family_medical_history_types`
--
ALTER TABLE `family_medical_history_types`
  MODIFY `family_history_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `indigenous_groups`
--
ALTER TABLE `indigenous_groups`
  MODIFY `indigenous_group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medical_allergy_types`
--
ALTER TABLE `medical_allergy_types`
  MODIFY `allergy_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medical_condition_types`
--
ALTER TABLE `medical_condition_types`
  MODIFY `condition_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mother_tongues`
--
ALTER TABLE `mother_tongues`
  MODIFY `mother_tongue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parent_guardian_types`
--
ALTER TABLE `parent_guardian_types`
  MODIFY `parent_guardian_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_addresses`
--
ALTER TABLE `student_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_medical_records`
--
ALTER TABLE `student_medical_records`
  MODIFY `student_medical_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_parent_guardians`
--
ALTER TABLE `student_parent_guardians`
  MODIFY `parent_guardian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_school_records`
--
ALTER TABLE `student_school_records`
  MODIFY `school_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `disability_subtypes`
--
ALTER TABLE `disability_subtypes`
  ADD CONSTRAINT `disability_subtypes_ibfk_1` FOREIGN KEY (`disability_type_id`) REFERENCES `disability_types` (`disability_type_id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`mother_tongue_id`) REFERENCES `mother_tongues` (`mother_tongue_id`),
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`indigenous_group_id`) REFERENCES `indigenous_groups` (`indigenous_group_id`);

--
-- Constraints for table `enrollment_disabilities`
--
ALTER TABLE `enrollment_disabilities`
  ADD CONSTRAINT `enrollment_disabilities_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`),
  ADD CONSTRAINT `enrollment_disabilities_ibfk_2` FOREIGN KEY (`disability_type_id`) REFERENCES `disability_types` (`disability_type_id`),
  ADD CONSTRAINT `enrollment_disabilities_ibfk_3` FOREIGN KEY (`disability_subtype_id`) REFERENCES `disability_subtypes` (`disability_subtype_id`);

--
-- Constraints for table `enrollment_family_medical_history`
--
ALTER TABLE `enrollment_family_medical_history`
  ADD CONSTRAINT `enrollment_family_medical_history_ibfk_1` FOREIGN KEY (`medical_information_id`) REFERENCES `enrollment_medical_information` (`medical_information_id`),
  ADD CONSTRAINT `enrollment_family_medical_history_ibfk_2` FOREIGN KEY (`family_history_type_id`) REFERENCES `family_medical_history_types` (`family_history_type_id`);

--
-- Constraints for table `enrollment_medical_allergies`
--
ALTER TABLE `enrollment_medical_allergies`
  ADD CONSTRAINT `enrollment_medical_allergies_ibfk_1` FOREIGN KEY (`medical_information_id`) REFERENCES `enrollment_medical_information` (`medical_information_id`),
  ADD CONSTRAINT `enrollment_medical_allergies_ibfk_2` FOREIGN KEY (`allergy_type_id`) REFERENCES `medical_allergy_types` (`allergy_type_id`);

--
-- Constraints for table `enrollment_medical_conditions`
--
ALTER TABLE `enrollment_medical_conditions`
  ADD CONSTRAINT `enrollment_medical_conditions_ibfk_1` FOREIGN KEY (`medical_information_id`) REFERENCES `enrollment_medical_information` (`medical_information_id`),
  ADD CONSTRAINT `enrollment_medical_conditions_ibfk_2` FOREIGN KEY (`condition_type_id`) REFERENCES `medical_condition_types` (`condition_type_id`);

--
-- Constraints for table `enrollment_medical_information`
--
ALTER TABLE `enrollment_medical_information`
  ADD CONSTRAINT `enrollment_medical_information_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);

--
-- Constraints for table `enrollment_medical_surgeries`
--
ALTER TABLE `enrollment_medical_surgeries`
  ADD CONSTRAINT `enrollment_medical_surgeries_ibfk_1` FOREIGN KEY (`medical_information_id`) REFERENCES `enrollment_medical_information` (`medical_information_id`);

--
-- Constraints for table `enrollment_medical_treatments`
--
ALTER TABLE `enrollment_medical_treatments`
  ADD CONSTRAINT `enrollment_medical_treatments_ibfk_1` FOREIGN KEY (`medical_information_id`) REFERENCES `enrollment_medical_information` (`medical_information_id`);

--
-- Constraints for table `enrollment_returning_learners`
--
ALTER TABLE `enrollment_returning_learners`
  ADD CONSTRAINT `enrollment_returning_learners_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `student_addresses`
--
ALTER TABLE `student_addresses`
  ADD CONSTRAINT `student_addresses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `student_medical_records`
--
ALTER TABLE `student_medical_records`
  ADD CONSTRAINT `student_medical_records_ibfk_1` FOREIGN KEY (`school_record_id`) REFERENCES `student_school_records` (`school_record_id`);

--
-- Constraints for table `student_parent_guardians`
--
ALTER TABLE `student_parent_guardians`
  ADD CONSTRAINT `student_parent_guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_parent_guardians_ibfk_2` FOREIGN KEY (`parent_guardian_type_id`) REFERENCES `parent_guardian_types` (`parent_guardian_type_id`);

--
-- Constraints for table `student_school_records`
--
ALTER TABLE `student_school_records`
  ADD CONSTRAINT `student_school_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_school_records_ibfk_2` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
