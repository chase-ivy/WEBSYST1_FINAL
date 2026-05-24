-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 24, 2026 at 12:33 PM
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
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `class_subject_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `max_score` decimal(6,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_scores`
--

CREATE TABLE `activity_scores` (
  `score_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `class_student_id` int(11) NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `class_student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 'Blind', 1, '2026-05-20 09:23:02'),
(2, 1, 'Low Vision', 1, '2026-05-20 09:23:02'),
(3, 9, 'Cancer', 1, '2026-05-24 01:54:01');

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
(1, 'Visual Impairment', 1, '2026-05-20 09:23:02'),
(2, 'Hearing Impairment', 1, '2026-05-20 09:23:02'),
(3, 'Learning Disability', 1, '2026-05-20 09:23:02'),
(4, 'Intellectual Disability', 1, '2026-05-20 09:23:02'),
(5, 'Autism Spectrum Disorder', 1, '2026-05-20 09:23:02'),
(6, 'Emotional / Behavioral Disorder', 1, '2026-05-20 09:23:02'),
(7, 'Orthopedic / Physical Handicap', 1, '2026-05-20 09:23:02'),
(8, 'Speech / Language Disorder', 1, '2026-05-20 09:23:02'),
(9, 'Social Health Problem/Chronic Disease', 1, '2026-05-20 09:23:02'),
(10, 'Others', 1, '2026-05-20 09:23:02'),
(11, 'Cerebral Palsy', 1, '2026-05-20 09:23:02'),
(12, 'Multiple Disorder', 1, '2026-05-20 09:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `enrollment_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `queue_number` varchar(50) DEFAULT NULL,
  `mother_tongue_id` int(11) DEFAULT NULL,
  `is_indigenous` tinyint(1) DEFAULT 0,
  `indigenous_group_id` int(11) DEFAULT NULL,
  `is_four_ps_beneficiary` tinyint(1) DEFAULT 0,
  `four_ps_household_id` varchar(100) DEFAULT NULL,
  `is_learner_with_disability` tinyint(1) DEFAULT 0,
  `is_returning_learner` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unique_guard` varchar(30) GENERATED ALWAYS AS (case when `enrollment_status` in ('pending','verified') then concat(`student_id`,'-',`school_year`) else NULL end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `school_year`, `grade_level`, `enrollment_status`, `queue_number`, `mother_tongue_id`, `is_indigenous`, `indigenous_group_id`, `is_four_ps_beneficiary`, `four_ps_household_id`, `is_learner_with_disability`, `is_returning_learner`, `verified_by`, `verified_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `created_at`) VALUES
(1, 2, '2025-2026', NULL, 'pending', NULL, NULL, 0, NULL, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-05-22 09:17:59'),
(4, 3, '2025-2026', NULL, 'pending', NULL, NULL, 0, NULL, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-05-22 09:25:48'),
(6, 4, '2025-2026', NULL, 'pending', NULL, 3, 1, 2, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-05-22 09:34:37'),
(7, 5, '2025-2026', NULL, 'pending', NULL, 2, 1, 3, 1, '324453245', 0, 1, NULL, NULL, NULL, NULL, NULL, '2026-05-22 09:37:13'),
(8, 6, '2025-2026', NULL, 'pending', NULL, 2, 1, 1, 1, '34564356', 0, 1, NULL, NULL, NULL, NULL, NULL, '2026-05-22 09:54:00'),
(10, 8, '2025-2026', 'Grade 2', 'pending', NULL, 5, 1, 2, 0, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:36:54'),
(11, 9, '2025-2026', 'Grade 2', 'pending', NULL, 1, 1, 1, 1, '4536', 1, 1, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:51:21'),
(12, 10, '2025-2026', 'Grade 1', 'pending', NULL, 1, 0, NULL, 0, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:55:53'),
(13, 11, '2025-2026', 'Grade 1', 'pending', NULL, 1, 0, NULL, 0, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-05-22 11:01:23');

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
(3, 10, 1, 1, NULL, '2026-05-22 10:36:54'),
(4, 11, 1, 1, NULL, '2026-05-22 10:51:21'),
(5, 12, 1, 1, NULL, '2026-05-22 10:55:53'),
(6, 13, 2, NULL, NULL, '2026-05-22 11:01:23'),
(7, 13, 3, NULL, NULL, '2026-05-22 11:01:23'),
(8, 13, 5, NULL, NULL, '2026-05-22 11:01:23'),
(9, 13, 6, NULL, NULL, '2026-05-22 11:01:23'),
(10, 13, 7, NULL, NULL, '2026-05-22 11:01:23'),
(11, 13, 8, NULL, NULL, '2026-05-22 11:01:23');

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
(9, 2, 1, 'erter, drgerg', '2026-05-22 10:36:54'),
(10, 2, 2, 'erter, drgerg', '2026-05-22 10:36:54'),
(11, 2, 3, 'erter, drgerg', '2026-05-22 10:36:54'),
(12, 2, 4, 'erter, drgerg', '2026-05-22 10:36:54'),
(13, 2, 5, 'erter, drgerg', '2026-05-22 10:36:54'),
(14, 2, 6, 'erter, drgerg', '2026-05-22 10:36:54'),
(15, 2, 7, 'erter, drgerg', '2026-05-22 10:36:54'),
(16, 2, 8, 'erter, drgerg', '2026-05-22 10:36:54'),
(17, 3, 1, 'tryug, retyf', '2026-05-22 10:51:21'),
(18, 3, 2, 'tryug, retyf', '2026-05-22 10:51:21'),
(19, 3, 3, 'tryug, retyf', '2026-05-22 10:51:21'),
(20, 3, 4, 'tryug, retyf', '2026-05-22 10:51:21'),
(21, 3, 5, 'tryug, retyf', '2026-05-22 10:51:21'),
(22, 3, 6, 'tryug, retyf', '2026-05-22 10:51:21'),
(23, 3, 7, 'tryug, retyf', '2026-05-22 10:51:21'),
(24, 3, 8, 'tryug, retyf', '2026-05-22 10:51:21');

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
(5, 2, 1, 'dfgh', '2026-05-22 10:36:54'),
(6, 2, 2, NULL, '2026-05-22 10:36:54'),
(7, 2, 3, 'rstgrt', '2026-05-22 10:36:54'),
(8, 2, 4, 'rtey', '2026-05-22 10:36:54'),
(9, 3, 1, 'yukhythki', '2026-05-22 10:51:21'),
(10, 3, 2, NULL, '2026-05-22 10:51:21'),
(11, 3, 3, 'ryfrey', '2026-05-22 10:51:21'),
(12, 3, 4, 'tyuhgy', '2026-05-22 10:51:21');

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
(9, 2, 1, 'fghrht', '2026-05-22 10:36:54'),
(10, 2, 2, 'fghrht', '2026-05-22 10:36:54'),
(11, 2, 3, 'fghrht', '2026-05-22 10:36:54'),
(12, 2, 4, 'fghrht', '2026-05-22 10:36:54'),
(13, 2, 5, 'fghrht', '2026-05-22 10:36:54'),
(14, 2, 6, 'fghrht', '2026-05-22 10:36:54'),
(15, 2, 7, 'fghrht', '2026-05-22 10:36:54'),
(16, 2, 8, 'fghrht', '2026-05-22 10:36:54'),
(17, 3, 1, 'ygfjvytg', '2026-05-22 10:51:21'),
(18, 3, 2, 'ygfjvytg', '2026-05-22 10:51:21'),
(19, 3, 3, 'ygfjvytg', '2026-05-22 10:51:21'),
(20, 3, 4, 'ygfjvytg', '2026-05-22 10:51:21'),
(21, 3, 5, 'ygfjvytg', '2026-05-22 10:51:21'),
(22, 3, 6, 'ygfjvytg', '2026-05-22 10:51:21'),
(23, 3, 7, 'ygfjvytg', '2026-05-22 10:51:21'),
(24, 3, 8, 'ygfjvytg', '2026-05-22 10:51:21');

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
(2, 10, 1, 'dfhdfh', '2026-05-22 10:36:54'),
(3, 11, 1, 'ytukjbyt', '2026-05-22 10:51:21'),
(4, 12, 0, 'retyferd', '2026-05-22 10:55:53'),
(5, 13, 0, 'erythfr', '2026-05-22 11:01:23');

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
(2, 2, '2026-05-22', 'fgh', 'ert', '2026-05-22 10:36:54'),
(3, 3, '2026-05-22', 'gfhj', 'rth', '2026-05-22 10:51:21');

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
(2, 2, 'dfg', 'ert', '2026-05-22 10:36:54'),
(3, 3, 'ftgyvj', 'ergxd', '2026-05-22 10:51:21');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `school_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_returning_learners`
--

INSERT INTO `enrollment_returning_learners` (`returning_learner_id`, `enrollment_id`, `last_grade_level_completed`, `last_school_attended`, `last_school_year_completed`, `created_at`, `school_id`) VALUES
(1, 10, 'Grade 2', 'rthre', '345345', '2026-05-22 10:36:54', NULL),
(2, 11, 'Grade 2', 'ewrtew', '23495', '2026-05-22 10:51:21', NULL);

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
(1, 'Tuberculosis', 1, '2026-05-20 09:23:02'),
(2, 'Cancer', 1, '2026-05-20 09:23:02'),
(3, 'Diabetes Mellitus', 1, '2026-05-20 09:23:02'),
(4, 'Hypertension', 1, '2026-05-20 09:23:02'),
(5, 'Stroke / Heart attack', 1, '2026-05-20 09:23:02'),
(6, 'Depression', 1, '2026-05-20 09:23:02'),
(7, 'Kidney problems', 1, '2026-05-20 09:23:02'),
(8, 'Other', 1, '2026-05-20 09:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `class_student_id` int(11) NOT NULL,
  `class_subject_id` int(11) NOT NULL,
  `grading_period` varchar(50) NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Ibaloi', 1, '2026-05-20 09:23:02'),
(2, 'Kankanaey', 1, '2026-05-20 09:23:02'),
(3, 'Ifugao', 1, '2026-05-20 09:23:02'),
(4, 'Bontoc', 1, '2026-05-20 09:23:02');

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
(1, 'Medicine', 1, '2026-05-20 09:23:02'),
(2, 'Pollen', 1, '2026-05-20 09:23:02'),
(3, 'Food', 1, '2026-05-20 09:23:02'),
(4, 'Other', 1, '2026-05-20 09:23:02');

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
(1, 'Error of refraction', 1, '2026-05-20 09:23:02'),
(2, 'Asthma', 1, '2026-05-20 09:23:02'),
(3, 'Seizure', 1, '2026-05-20 09:23:02'),
(4, 'Heart Illness', 1, '2026-05-20 09:23:02'),
(5, 'Anemia', 1, '2026-05-20 09:23:02'),
(6, 'Bleeding disorder', 1, '2026-05-20 09:23:02'),
(7, 'Fracture / Dislocation', 1, '2026-05-20 09:23:02'),
(8, 'Other', 1, '2026-05-20 09:23:02');

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
(1, 'Ilocano', 1, '2026-05-20 09:23:02'),
(2, 'Tagalog', 1, '2026-05-20 09:23:02'),
(3, 'Kapampangan', 1, '2026-05-20 09:23:02'),
(4, 'Pangasinan', 1, '2026-05-20 09:23:02'),
(5, 'Cebuano', 1, '2026-05-20 09:23:02'),
(6, 'English', 1, '2026-05-20 09:23:02');

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
(1, 'Father', 1, '2026-05-20 09:23:02'),
(2, 'Mother', 1, '2026-05-20 09:23:02'),
(3, 'Guardian', 1, '2026-05-20 09:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `school_year`, `grade_level`, `name`, `is_active`, `created_at`) VALUES
(1, '2026-2027', 'Grade 2', 'PEAR', 1, '2026-05-22 07:44:28'),
(2, '2026-2027', 'Grade 1', 'APPLE', 1, '2026-05-22 07:45:29'),
(3, '2026-2027', 'Grade 4', 'Egg', 1, '2026-05-23 03:47:07');

-- --------------------------------------------------------

--
-- Table structure for table `section_subjects`
--

CREATE TABLE `section_subjects` (
  `section_subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `psa_bcn` varchar(100) DEFAULT NULL,
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

INSERT INTO `students` (`student_id`, `user_id`, `lrn`, `psa_bcn`, `last_name`, `first_name`, `middle_name`, `extension_name`, `birth_date`, `sex`, `place_of_birth`, `created_at`) VALUES
(2, 3, '123490871234', NULL, 'ewrwqer', 'weqrwqr', 'trewt', '', '2000-04-05', 'Female', 'ertfyertf', '2026-05-22 09:17:59'),
(3, 4, '02934753123', NULL, 'erwdt', 'ewrdt', 'trg', '', '2000-06-04', 'Female', 'sdrx', '2026-05-22 09:20:25'),
(4, 5, '123412342134324', NULL, 'sdf', 'wer', 'wqer', 'w', '2004-05-28', 'Female', 'ertde', '2026-05-22 09:34:37'),
(5, 6, '234590873425', NULL, 'rteyferty', 'rtyhgrte', 'retdgretg', 'erdtg', '2010-05-19', 'Female', 'erted', '2026-05-22 09:37:13'),
(6, 7, '093128775312', NULL, 'rtyfrteyg', 'e', 'wrtdetd', '', '2004-05-12', 'Male', 'ewrtdwert', '2026-05-22 09:54:00'),
(7, 8, '12039847741', NULL, 'ewrt', 'ewrt', 'ytre', 'ewrt', '2023-05-19', 'Male', 'rtg', '2026-05-22 10:14:30'),
(8, 9, '234234534', NULL, 'retyr', 'ewtr', 'yrt', '', '2018-05-19', 'Male', 'gdjhdg', '2026-05-22 10:36:54'),
(9, 10, '109345887234', NULL, 'werqwre', 'erty', 'rtewrtewt', 'er', '2026-05-05', 'Male', 'sdfgcs', '2026-05-22 10:51:21'),
(10, 11, '23094856', NULL, 'sdfg', 'wqer', 'wersa', '', '2026-05-07', 'Male', 'rstgcfr', '2026-05-22 10:55:53'),
(11, 12, '1985724521123', NULL, 'erdg', 'rtyf', 'ewsr', '', '2026-05-22', 'Female', 'ertyferd', '2026-05-22 11:01:23');

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
  `enrollment_id` int(11) DEFAULT NULL,
  `ownership_type` enum('owned','rented','living_with_relatives','inherited') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_addresses`
--

INSERT INTO `student_addresses` (`address_id`, `student_id`, `address_type`, `house_no`, `street_name`, `barangay`, `subdivision_house_no`, `municipality_city`, `province`, `country`, `zip_code`, `enrollment_id`, `ownership_type`, `created_at`) VALUES
(3, 8, 'current', '345', 'rty', 'sertgserg', NULL, 'rdth', 'srey', 'rety', '32456', NULL, 'owned', '2026-05-22 10:36:54'),
(4, 8, 'permanent', '345', 'rty', 'sertgserg', NULL, 'rdth', 'srey', 'rety', '32456', NULL, 'owned', '2026-05-22 10:36:54'),
(5, 9, 'current', '3214', 'ewrew', 'wqser', NULL, 'ewtrdwet', 'qwers', 'ertf', '3453245', NULL, 'rented', '2026-05-22 10:51:21'),
(6, 9, 'permanent', '3214', 'ewrew', 'wqser', NULL, 'ewtrdwet', 'qwers', 'ertf', '3453245', NULL, 'rented', '2026-05-22 10:51:21'),
(7, 10, 'current', '3245', 'ertd', 'retf', NULL, 'tygu', 'ewsr', 'rtyg', '32456', NULL, 'rented', '2026-05-22 10:55:53'),
(8, 10, 'permanent', '3245', 'ertd', 'retf', NULL, 'tygu', 'ewsr', 'rtyg', '32456', NULL, 'rented', '2026-05-22 10:55:53'),
(9, 11, 'current', '2345', 'ergd', 'ewsr', NULL, 'rthf', 'wesr', 'ytgu', '2345', NULL, 'rented', '2026-05-22 11:01:23'),
(10, 11, 'permanent', '2345', 'ergd', 'ewsr', NULL, 'rthf', 'wesr', 'ytgu', '2345', NULL, 'rented', '2026-05-22 11:01:23');

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
  `regenerated_at` timestamp NULL DEFAULT NULL,
  `regenerated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_medical_records`
--

INSERT INTO `student_medical_records` (`student_medical_record_id`, `school_record_id`, `exposed_to_cigarette_vape_smoke`, `other_pertinent_information`, `allergies`, `conditions`, `surgeries`, `treatments`, `family_medical_history`, `regenerated_at`, `regenerated_by`, `created_at`) VALUES
(1, 2, 1, 'dfhdfh', '[{\"allergy_type_id\":1,\"description\":\"dfgh\"},{\"allergy_type_id\":2,\"description\":null},{\"allergy_type_id\":3,\"description\":\"rstgrt\"},{\"allergy_type_id\":4,\"description\":\"rtey\"}]', '[{\"condition_type_id\":1,\"description\":\"fghrht\"},{\"condition_type_id\":2,\"description\":\"fghrht\"},{\"condition_type_id\":3,\"description\":\"fghrht\"},{\"condition_type_id\":4,\"description\":\"fghrht\"},{\"condition_type_id\":5,\"description\":\"fghrht\"},{\"condition_type_id\":6,\"description\":\"fghrht\"},{\"condition_type_id\":7,\"description\":\"fghrht\"},{\"condition_type_id\":8,\"description\":\"fghrht\"}]', '[{\"surgery_date\":\"2026-05-22\",\"hospital_name\":\"fgh\",\"body_part\":\"ert\"}]', '[{\"treatment_medicine\":\"dfg\",\"schedule_dosage\":\"ert\"}]', '[{\"family_history_type_id\":1,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":2,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":3,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":4,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":5,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":6,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":7,\"description\":\"erter, drgerg\"},{\"family_history_type_id\":8,\"description\":\"erter, drgerg\"}]', NULL, NULL, '2026-05-22 10:36:54'),
(2, 3, 1, 'ytukjbyt', '[{\"allergy_type_id\":1,\"description\":\"yukhythki\"},{\"allergy_type_id\":2,\"description\":null},{\"allergy_type_id\":3,\"description\":\"ryfrey\"},{\"allergy_type_id\":4,\"description\":\"tyuhgy\"}]', '[{\"condition_type_id\":1,\"description\":\"ygfjvytg\"},{\"condition_type_id\":2,\"description\":\"ygfjvytg\"},{\"condition_type_id\":3,\"description\":\"ygfjvytg\"},{\"condition_type_id\":4,\"description\":\"ygfjvytg\"},{\"condition_type_id\":5,\"description\":\"ygfjvytg\"},{\"condition_type_id\":6,\"description\":\"ygfjvytg\"},{\"condition_type_id\":7,\"description\":\"ygfjvytg\"},{\"condition_type_id\":8,\"description\":\"ygfjvytg\"}]', '[{\"surgery_date\":\"2026-05-22\",\"hospital_name\":\"gfhj\",\"body_part\":\"rth\"}]', '[{\"treatment_medicine\":\"ftgyvj\",\"schedule_dosage\":\"ergxd\"}]', '[{\"family_history_type_id\":1,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":2,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":3,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":4,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":5,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":6,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":7,\"description\":\"tryug, retyf\"},{\"family_history_type_id\":8,\"description\":\"tryug, retyf\"}]', NULL, NULL, '2026-05-22 10:51:21'),
(3, 4, 0, 'retyferd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:55:53'),
(4, 5, 0, 'erythfr', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 11:01:23');

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
  `relationship_status` varchar(100) DEFAULT NULL,
  `facebook_messenger` varchar(150) DEFAULT NULL,
  `is_emergency_contact` tinyint(1) DEFAULT 0,
  `contact_priority` tinyint(4) DEFAULT NULL,
  `is_contact_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_parent_guardians`
--

INSERT INTO `student_parent_guardians` (`parent_guardian_id`, `student_id`, `parent_guardian_type_id`, `last_name`, `first_name`, `middle_name`, `contact_number`, `occupation`, `relationship_status`, `facebook_messenger`, `is_emergency_contact`, `contact_priority`, `is_contact_visible`, `created_at`) VALUES
(1, 8, 1, 'retyrtey', 'oijnoo', 'onoij', '874874', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:36:54'),
(2, 8, 2, 'ihuybb', 'yuigyuig', 'inmoi', '651651', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:36:54'),
(3, 8, 3, 'iuhi', 'mom', 'hbu', '44987', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:36:54'),
(4, 9, 1, 'wertd', 'retyf', 'ewrtd', '42655', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:51:21'),
(5, 9, 2, 'ewrtd', 'wqesr', 'rtuyg', '3246', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:51:21'),
(6, 9, 3, 'erdertgd', 'tygjutryug', 'tyugrrthu', '5464567', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:51:21'),
(7, 10, 1, 'rtdg', 'erws', 'tyg', '3456', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 10:55:53'),
(8, 11, 1, 'ertdg', 'tyg', 'ewsrt', '4356', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 11:01:23'),
(9, 11, 2, 'etryf', 'ytug', 'rews', '4536', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 11:01:23'),
(10, 11, 3, 'rtd', 'tuyf', 'esw', '3456', NULL, NULL, NULL, 0, NULL, 1, '2026-05-22 11:01:23');

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
  `academic_status` enum('active','completed','transferred','dropped','expelled') DEFAULT 'active',
  `lrn` varchar(50) DEFAULT NULL,
  `psa_bcn` varchar(100) DEFAULT NULL,
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
  `disabilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`disabilities`)),
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `status_changed_by` int(11) DEFAULT NULL,
  `status_changed_at` timestamp NULL DEFAULT NULL,
  `status_change_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_school_records`
--

INSERT INTO `student_school_records` (`school_record_id`, `enrollment_id`, `student_id`, `school_year`, `grade_level`, `academic_status`, `lrn`, `psa_bcn`, `last_name`, `first_name`, `middle_name`, `extension_name`, `birth_date`, `sex`, `place_of_birth`, `mother_tongue`, `indigenous_group`, `four_ps_household_id`, `is_learner_with_disability`, `is_returning_learner`, `disabilities`, `verified_by`, `verified_at`, `status_changed_by`, `status_changed_at`, `status_change_reason`, `created_at`) VALUES
(2, 10, 8, '2025-2026', 'Grade 2', 'active', '234234534', NULL, 'retyr', 'ewtr', 'yrt', NULL, '2018-05-19', 'Male', 'gdjhdg', '5', '2', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:36:54'),
(3, 11, 9, '2025-2026', 'Grade 2', 'active', '109345887234', NULL, 'werqwre', 'erty', 'rtewrtewt', 'er', '2026-05-05', 'Male', 'sdfgcs', '1', '1', '4536', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:51:21'),
(4, 12, 10, '2025-2026', 'Grade 1', 'active', '23094856', NULL, 'sdfg', 'wqer', 'wersa', NULL, '2026-05-07', 'Male', 'rstgcfr', '1', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 10:55:53'),
(5, 13, 11, '2025-2026', 'Grade 1', 'active', '1985724521123', NULL, 'erdg', 'rtyf', 'ewsr', NULL, '2026-05-22', 'Female', 'ertyferd', '1', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22 11:01:23');

-- --------------------------------------------------------

--
-- Table structure for table `student_sections`
--

CREATE TABLE `student_sections` (
  `student_section_id` int(11) NOT NULL,
  `school_record_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'admin', 'admin@gmail.com', 'admin', 'admin', 1, '2026-05-22 07:40:10'),
(2, 'teacher', 'teacher1@gmail.com', '$2y$10$GIpPMercKXyaiUjnxPlAUOCxMr/sDytyft1iUIFMi0GndoB91jcPe', 'staff', 1, '2026-05-22 07:46:20'),
(3, '123490871234', NULL, '$2y$10$ZkMUmtSR6UIJ6f4aoCuB4.l4vTjZcFin4M4GrraPFbasKk313oFoK', 'student', 1, '2026-05-22 09:17:59'),
(4, '02934753123', NULL, '$2y$10$umml/Zls9VZWnnxdbtf2y.I7yNx9gDiS5cHFIWO6JsMH8BEoE74qK', 'student', 1, '2026-05-22 09:20:25'),
(5, '123412342134324', NULL, '$2y$10$v9T5HbM4e9hUbaLMJ5aEjew/Rz8esZFocReLcSN9jLP8Z8rmQLpZ6', 'student', 1, '2026-05-22 09:34:37'),
(6, '234590873425', NULL, '$2y$10$ZmWnQcIPVwVIC4abKCcAWunSLQU7ArGlOeKarU1LdN48UBmONlGza', 'student', 1, '2026-05-22 09:37:13'),
(7, '093128775312', '093128775312@student.local', '$2y$10$/MwuSWR3xDyJeATWwaQ8beN6FRYoiXeuaRspPFTncduJa3wT6Qywm', 'student', 1, '2026-05-22 09:54:00'),
(8, '12039847741', '12039847741@student.local', '$2y$10$k9Aq55zJ1CZHUx4kuBikQOaCbuzxp/PpGNQi.uJxIdtQjwSrnpG1W', 'student', 1, '2026-05-22 10:14:30'),
(9, '234234534', '234234534@student.local', '$2y$10$CRO4yezVK9MlSZiAy2NDw.I6BpFTuNVo5cYXLV307QdifK1kwDtNS', 'student', 1, '2026-05-22 10:36:54'),
(10, '109345887234', '109345887234@student.local', '$2y$10$YXE/CPOrLfOHzuHN.Lsi/OgVCM3wAKRo8KsF7d.WW30Oq8UVOzg52', 'student', 1, '2026-05-22 10:51:21'),
(11, '23094856', '23094856@student.local', '$2y$10$BHRdA8W3mgXPXq4WsBbmsOr.xvvBebh.LQjtbJdnuYUOm44PvCxba', 'student', 1, '2026-05-22 10:55:53'),
(12, '1985724521123', '1985724521123@student.local', '$2y$10$iPINr9g5/Sj0CrnKx7Nq7.8JZuj7Q95r1/zXjQiOtNMJgf6ly35GS', 'student', 1, '2026-05-22 11:01:23'),
(13, 'linter', 'linter@gmail.com', '$2y$10$A080znJYaOeiZ9xLbZQQ3OEFJ0nN.wGEEOcDbNlQbd/JNRDxvJvtq', 'staff', 1, '2026-05-23 02:21:44'),
(14, 'lolo', 'oldman@gmail.com', '$2y$10$j1znkw.2WFGoq2ARRBm20.cVniiMBdA9qmrGFQUGmO8IM2qfsbZDu', 'staff', 1, '2026-05-23 02:47:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `class_subject_id` (`class_subject_id`);

--
-- Indexes for table `activity_scores`
--
ALTER TABLE `activity_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD UNIQUE KEY `unique_activity_student` (`activity_id`,`class_student_id`),
  ADD KEY `class_student_id` (`class_student_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`class_student_id`,`date`);

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
  ADD UNIQUE KEY `unique_student_school_year` (`student_id`,`school_year`),
  ADD UNIQUE KEY `unique_active_enrollment` (`unique_guard`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `mother_tongue_id` (`mother_tongue_id`),
  ADD KEY `indigenous_group_id` (`indigenous_group_id`),
  ADD KEY `fk_enrollments_verified_by` (`verified_by`),
  ADD KEY `fk_enrollments_rejected_by` (`rejected_by`);

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
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_grade` (`class_student_id`,`class_subject_id`,`grading_period`),
  ADD KEY `class_subject_id` (`class_subject_id`);

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
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`section_subject_id`),
  ADD UNIQUE KEY `unique_section_subject` (`section_id`,`subject_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

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
  ADD UNIQUE KEY `school_record_id` (`school_record_id`),
  ADD KEY `fk_smr_regenerated_by` (`regenerated_by`);

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
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_ssr_verified_by` (`verified_by`),
  ADD KEY `fk_ssr_status_changed_by` (`status_changed_by`);

--
-- Indexes for table `student_sections`
--
ALTER TABLE `student_sections`
  ADD PRIMARY KEY (`student_section_id`),
  ADD UNIQUE KEY `school_record_id` (`school_record_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

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
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_scores`
--
ALTER TABLE `activity_scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disability_subtypes`
--
ALTER TABLE `disability_subtypes`
  MODIFY `disability_subtype_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `disability_types`
--
ALTER TABLE `disability_types`
  MODIFY `disability_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `enrollment_disabilities`
--
ALTER TABLE `enrollment_disabilities`
  MODIFY `enrollment_disability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `enrollment_family_medical_history`
--
ALTER TABLE `enrollment_family_medical_history`
  MODIFY `family_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `enrollment_medical_allergies`
--
ALTER TABLE `enrollment_medical_allergies`
  MODIFY `allergy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `enrollment_medical_conditions`
--
ALTER TABLE `enrollment_medical_conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `enrollment_medical_information`
--
ALTER TABLE `enrollment_medical_information`
  MODIFY `medical_information_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `section_subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `student_addresses`
--
ALTER TABLE `student_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_medical_records`
--
ALTER TABLE `student_medical_records`
  MODIFY `student_medical_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_parent_guardians`
--
ALTER TABLE `student_parent_guardians`
  MODIFY `parent_guardian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_school_records`
--
ALTER TABLE `student_school_records`
  MODIFY `school_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_sections`
--
ALTER TABLE `student_sections`
  MODIFY `student_section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activities_section_subject` FOREIGN KEY (`class_subject_id`) REFERENCES `section_subjects` (`section_subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_scores`
--
ALTER TABLE `activity_scores`
  ADD CONSTRAINT `fk_activity_scores_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_activity_scores_student_section` FOREIGN KEY (`class_student_id`) REFERENCES `student_sections` (`student_section_id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_student_section` FOREIGN KEY (`class_student_id`) REFERENCES `student_sections` (`student_section_id`);

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
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`indigenous_group_id`) REFERENCES `indigenous_groups` (`indigenous_group_id`),
  ADD CONSTRAINT `fk_enrollments_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_enrollments_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

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
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_section_subject` FOREIGN KEY (`class_subject_id`) REFERENCES `section_subjects` (`section_subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grades_student_section` FOREIGN KEY (`class_student_id`) REFERENCES `student_sections` (`student_section_id`);

--
-- Constraints for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD CONSTRAINT `section_subjects_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `section_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `section_subjects_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);

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
  ADD CONSTRAINT `fk_smr_regenerated_by` FOREIGN KEY (`regenerated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
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
  ADD CONSTRAINT `fk_ssr_status_changed_by` FOREIGN KEY (`status_changed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ssr_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_school_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_school_records_ibfk_2` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);

--
-- Constraints for table `student_sections`
--
ALTER TABLE `student_sections`
  ADD CONSTRAINT `student_sections_ibfk_1` FOREIGN KEY (`school_record_id`) REFERENCES `student_school_records` (`school_record_id`),
  ADD CONSTRAINT `student_sections_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
