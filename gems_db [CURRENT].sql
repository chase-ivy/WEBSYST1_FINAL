-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 05:13 PM
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
(1, 1, 'Blind', 1, '2026-05-20 09:23:02'),
(2, 1, 'Low Vision', 1, '2026-05-20 09:23:02');

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
(9, 'Chronic Illness', 1, '2026-05-20 09:23:02'),
(10, 'Others', 1, '2026-05-20 09:23:02');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`section_subject_id`),
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
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_disabilities`
--
ALTER TABLE `enrollment_disabilities`
  MODIFY `enrollment_disability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_family_medical_history`
--
ALTER TABLE `enrollment_family_medical_history`
  MODIFY `family_history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_medical_allergies`
--
ALTER TABLE `enrollment_medical_allergies`
  MODIFY `allergy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_medical_conditions`
--
ALTER TABLE `enrollment_medical_conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_medical_information`
--
ALTER TABLE `enrollment_medical_information`
  MODIFY `medical_information_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_medical_surgeries`
--
ALTER TABLE `enrollment_medical_surgeries`
  MODIFY `surgery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_medical_treatments`
--
ALTER TABLE `enrollment_medical_treatments`
  MODIFY `treatment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_returning_learners`
--
ALTER TABLE `enrollment_returning_learners`
  MODIFY `returning_learner_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `section_subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_addresses`
--
ALTER TABLE `student_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_medical_records`
--
ALTER TABLE `student_medical_records`
  MODIFY `student_medical_record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_parent_guardians`
--
ALTER TABLE `student_parent_guardians`
  MODIFY `parent_guardian_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_school_records`
--
ALTER TABLE `student_school_records`
  MODIFY `school_record_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

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
