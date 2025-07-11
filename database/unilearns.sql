-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 10 يوليو 2025 الساعة 20:51
-- إصدار الخادم: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `univerboard`
--

-- --------------------------------------------------------

--
-- بنية الجدول `academic_programs`
--

CREATE TABLE `academic_programs` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `degree` enum('bachelor','master','phd') NOT NULL,
  `credit_hours` int(11) NOT NULL,
  `duration_years` int(11) NOT NULL,
  `coordinator_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `academic_programs`
--

INSERT INTO `academic_programs` (`id`, `department_id`, `name`, `name_en`, `code`, `description`, `description_en`, `degree`, `credit_hours`, `duration_years`, `coordinator_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'بكالوريوس الهندسة المدنية', 'Bachelor of Civil Engineering', 'BCE', 'برنامج بكالوريوس الهندسة المدنية يهدف إلى تأهيل الطلاب في مجال بكالوريوس الهندسة المدنية وإعدادهم لسوق العمل', 'The Bachelor of Civil Engineering program aims to qualify students in the field of Bachelor of Civil Engineering and prepare them for the job market', 'bachelor', 150, 5, 7, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 2, 'بكالوريوس الهندسة الكهربائية', 'Bachelor of Electrical Engineering', 'BEE', 'برنامج بكالوريوس الهندسة الكهربائية يهدف إلى تأهيل الطلاب في مجال بكالوريوس الهندسة الكهربائية وإعدادهم لسوق العمل', 'The Bachelor of Electrical Engineering program aims to qualify students in the field of Bachelor of Electrical Engineering and prepare them for the job market', 'bachelor', 150, 5, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 2, 'ماجستير الهندسة الكهربائية', 'Master of Electrical Engineering', 'MEE', 'برنامج ماجستير الهندسة الكهربائية يهدف إلى تأهيل الطلاب في مجال ماجستير الهندسة الكهربائية وإعدادهم لسوق العمل', 'The Master of Electrical Engineering program aims to qualify students in the field of Master of Electrical Engineering and prepare them for the job market', 'master', 36, 2, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 3, 'بكالوريوس الهندسة الميكانيكية', 'Bachelor of Mechanical Engineering', 'BME', 'برنامج بكالوريوس الهندسة الميكانيكية يهدف إلى تأهيل الطلاب في مجال بكالوريوس الهندسة الميكانيكية وإعدادهم لسوق العمل', 'The Bachelor of Mechanical Engineering program aims to qualify students in the field of Bachelor of Mechanical Engineering and prepare them for the job market', 'bachelor', 150, 5, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 4, 'بكالوريوس علوم الحاسب', 'Bachelor of Computer Science', 'BCS', 'برنامج بكالوريوس علوم الحاسب يهدف إلى تأهيل الطلاب في مجال بكالوريوس علوم الحاسب وإعدادهم لسوق العمل', 'The Bachelor of Computer Science program aims to qualify students in the field of Bachelor of Computer Science and prepare them for the job market', 'bachelor', 132, 4, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 4, 'ماجستير علوم الحاسب', 'Master of Computer Science', 'MCS', 'برنامج ماجستير علوم الحاسب يهدف إلى تأهيل الطلاب في مجال ماجستير علوم الحاسب وإعدادهم لسوق العمل', 'The Master of Computer Science program aims to qualify students in the field of Master of Computer Science and prepare them for the job market', 'master', 36, 2, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 4, 'دكتوراه علوم الحاسب', 'PhD in Computer Science', 'PCS', 'برنامج دكتوراه علوم الحاسب يهدف إلى تأهيل الطلاب في مجال دكتوراه علوم الحاسب وإعدادهم لسوق العمل', 'The PhD in Computer Science program aims to qualify students in the field of PhD in Computer Science and prepare them for the job market', 'phd', 60, 4, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 5, 'بكالوريوس هندسة البرمجيات', 'Bachelor of Software Engineering', 'BSE', 'برنامج بكالوريوس هندسة البرمجيات يهدف إلى تأهيل الطلاب في مجال بكالوريوس هندسة البرمجيات وإعدادهم لسوق العمل', 'The Bachelor of Software Engineering program aims to qualify students in the field of Bachelor of Software Engineering and prepare them for the job market', 'bachelor', 132, 4, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 6, 'بكالوريوس نظم المعلومات', 'Bachelor of Information Systems', 'BIS', 'برنامج بكالوريوس نظم المعلومات يهدف إلى تأهيل الطلاب في مجال بكالوريوس نظم المعلومات وإعدادهم لسوق العمل', 'The Bachelor of Information Systems program aims to qualify students in the field of Bachelor of Information Systems and prepare them for the job market', 'bachelor', 132, 4, 6, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 7, 'بكالوريوس الرياضيات', 'Bachelor of Mathematics', 'BMATH', 'برنامج بكالوريوس الرياضيات يهدف إلى تأهيل الطلاب في مجال بكالوريوس الرياضيات وإعدادهم لسوق العمل', 'The Bachelor of Mathematics program aims to qualify students in the field of Bachelor of Mathematics and prepare them for the job market', 'bachelor', 128, 4, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 8, 'بكالوريوس الفيزياء', 'Bachelor of Physics', 'BPHYS', 'برنامج بكالوريوس الفيزياء يهدف إلى تأهيل الطلاب في مجال بكالوريوس الفيزياء وإعدادهم لسوق العمل', 'The Bachelor of Physics program aims to qualify students in the field of Bachelor of Physics and prepare them for the job market', 'bachelor', 128, 4, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(12, 9, 'بكالوريوس الكيمياء', 'Bachelor of Chemistry', 'BCHEM', 'برنامج بكالوريوس الكيمياء يهدف إلى تأهيل الطلاب في مجال بكالوريوس الكيمياء وإعدادهم لسوق العمل', 'The Bachelor of Chemistry program aims to qualify students in the field of Bachelor of Chemistry and prepare them for the job market', 'bachelor', 128, 4, 9, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(13, 10, 'بكالوريوس المحاسبة', 'Bachelor of Accounting', 'BACC', 'برنامج بكالوريوس المحاسبة يهدف إلى تأهيل الطلاب في مجال بكالوريوس المحاسبة وإعدادهم لسوق العمل', 'The Bachelor of Accounting program aims to qualify students in the field of Bachelor of Accounting and prepare them for the job market', 'bachelor', 128, 4, NULL, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(14, 11, 'بكالوريوس إدارة الأعمال', 'Bachelor of Business Administration', 'BBA', 'برنامج بكالوريوس إدارة الأعمال يهدف إلى تأهيل الطلاب في مجال بكالوريوس إدارة الأعمال وإعدادهم لسوق العمل', 'The Bachelor of Business Administration program aims to qualify students in the field of Bachelor of Business Administration and prepare them for the job market', 'bachelor', 128, 4, 8, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(15, 11, 'ماجستير إدارة الأعمال', 'Master of Business Administration', 'MBA', 'برنامج ماجستير إدارة الأعمال يهدف إلى تأهيل الطلاب في مجال ماجستير إدارة الأعمال وإعدادهم لسوق العمل', 'The Master of Business Administration program aims to qualify students in the field of Master of Business Administration and prepare them for the job market', 'master', 42, 2, 8, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(16, 12, 'بكالوريوس التسويق', 'Bachelor of Marketing', 'BMKT', 'برنامج بكالوريوس التسويق يهدف إلى تأهيل الطلاب في مجال بكالوريوس التسويق وإعدادهم لسوق العمل', 'The Bachelor of Marketing program aims to qualify students in the field of Bachelor of Marketing and prepare them for the job market', 'bachelor', 128, 4, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(17, 13, 'بكالوريوس الطب والجراحة', 'Bachelor of Medicine and Surgery', 'MBBS', 'برنامج بكالوريوس الطب والجراحة يهدف إلى تأهيل الطلاب في مجال بكالوريوس الطب والجراحة وإعدادهم لسوق العمل', 'The Bachelor of Medicine and Surgery program aims to qualify students in the field of Bachelor of Medicine and Surgery and prepare them for the job market', 'bachelor', 220, 6, 11, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `academic_terms`
--

CREATE TABLE `academic_terms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `term_type` enum('first','second','summer') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `registration_start_date` date NOT NULL,
  `registration_end_date` date NOT NULL,
  `drop_deadline` date NOT NULL,
  `withdraw_deadline` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `academic_terms`
--

INSERT INTO `academic_terms` (`id`, `name`, `name_en`, `academic_year`, `term_type`, `start_date`, `end_date`, `registration_start_date`, `registration_end_date`, `drop_deadline`, `withdraw_deadline`, `is_current`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'الفصل الأول 2024-2025', 'First Semester 2024-2025', '2024-2025', 'first', '2024-09-01', '2024-12-20', '2024-08-15', '2024-08-25', '2024-09-15', '2024-11-01', 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 'الفصل الثاني 2024-2025', 'Second Semester 2024-2025', '2024-2025', 'second', '2025-01-15', '2025-05-10', '2024-12-25', '2025-01-05', '2025-01-30', '2025-03-15', 0, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 'الفصل الصيفي 2024-2025', 'Summer Semester 2024-2025', '2024-2025', 'summer', '2025-06-01', '2025-07-30', '2025-05-15', '2025-05-25', '2025-06-10', '2025-07-01', 0, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `content_en` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `target_type` enum('all','college','department','program','course_section') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `publish_date` datetime NOT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `is_important` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `announcement_attachments`
--

CREATE TABLE `announcement_attachments` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `description_en` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `instructions_en` text DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `total_marks` decimal(5,2) NOT NULL,
  `weight_percentage` decimal(5,2) NOT NULL,
  `allow_late_submission` tinyint(1) NOT NULL DEFAULT 0,
  `late_submission_deadline` datetime DEFAULT NULL,
  `late_submission_penalty` decimal(5,2) DEFAULT NULL,
  `submission_type` enum('file','text','both') NOT NULL,
  `file_types_allowed` varchar(255) DEFAULT NULL,
  `max_file_size` int(11) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_date` datetime NOT NULL,
  `text_submission` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `is_late` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('submitted','graded','returned') NOT NULL DEFAULT 'submitted',
  `marks` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `attendance_details`
--

CREATE TABLE `attendance_details` (
  `id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `backup_type` enum('automatic','manual') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `colleges`
--

CREATE TABLE `colleges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `dean_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `colleges`
--

INSERT INTO `colleges` (`id`, `name`, `name_en`, `code`, `description`, `description_en`, `dean_id`, `location`, `contact_email`, `contact_phone`, `website`, `logo`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'كلية الهندسة', 'College of Engineering', 'ENG', 'وصف كلية الهندسة - كلية متميزة تقدم برامج أكاديمية متنوعة في مجالات كلية الهندسة', 'Description of College of Engineering - A distinguished college offering diverse academic programs in College of Engineering fields', 1, 'مبنى 1، الحرم الجامعي الرئيسي', 'eng@univerboard.edu', '0564015369', 'https://www.univerboard.edu/eng', '/assets/images/colleges/eng_logo.png', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 'كلية علوم الحاسب والمعلومات', 'College of Computer and Information Sciences', 'CIS', 'وصف كلية علوم الحاسب والمعلومات - كلية متميزة تقدم برامج أكاديمية متنوعة في مجالات كلية علوم الحاسب والمعلومات', 'Description of College of Computer and Information Sciences - A distinguished college offering diverse academic programs in College of Computer and Information Sciences fields', 10, 'مبنى 2، الحرم الجامعي الرئيسي', 'cis@univerboard.edu', '0564743849', 'https://www.univerboard.edu/cis', '/assets/images/colleges/cis_logo.png', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 'كلية العلوم', 'College of Science', 'SCI', 'وصف كلية العلوم - كلية متميزة تقدم برامج أكاديمية متنوعة في مجالات كلية العلوم', 'Description of College of Science - A distinguished college offering diverse academic programs in College of Science fields', 9, 'مبنى 3، الحرم الجامعي الرئيسي', 'sci@univerboard.edu', '0588175592', 'https://www.univerboard.edu/sci', '/assets/images/colleges/sci_logo.png', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 'كلية إدارة الأعمال', 'College of Business Administration', 'BUS', 'وصف كلية إدارة الأعمال - كلية متميزة تقدم برامج أكاديمية متنوعة في مجالات كلية إدارة الأعمال', 'Description of College of Business Administration - A distinguished college offering diverse academic programs in College of Business Administration fields', 3, 'مبنى 4، الحرم الجامعي الرئيسي', 'bus@univerboard.edu', '0599460263', 'https://www.univerboard.edu/bus', '/assets/images/colleges/bus_logo.png', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 'كلية الطب', 'College of Medicine', 'MED', 'وصف كلية الطب - كلية متميزة تقدم برامج أكاديمية متنوعة في مجالات كلية الطب', 'Description of College of Medicine - A distinguished college offering diverse academic programs in College of Medicine fields', 11, 'مبنى 5، الحرم الجامعي الرئيسي', 'med@univerboard.edu', '0532557650', 'https://www.univerboard.edu/med', '/assets/images/colleges/med_logo.png', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `college_admins`
--

CREATE TABLE `college_admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `college_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `permissions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `college_admins`
--

INSERT INTO `college_admins` (`id`, `user_id`, `admin_id`, `college_id`, `position`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 31, 'CA300001', 1, 'عميد الكلية', '{\"college_management\": true, \"department_management\": true, \"program_management\": true, \"teacher_management\": true, \"student_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 32, 'CA300002', 2, 'وكيل الكلية للشؤون الأكاديمية', '{\"college_management\": true, \"department_management\": true, \"program_management\": true, \"teacher_management\": true, \"student_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 33, 'CA300003', 3, 'وكيل الكلية للشؤون الإدارية', '{\"college_management\": true, \"department_management\": true, \"program_management\": true, \"teacher_management\": true, \"student_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 34, 'CA300004', 4, 'مدير الشؤون الطلابية', '{\"college_management\": true, \"department_management\": true, \"program_management\": true, \"teacher_management\": true, \"student_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 35, 'CA300005', 5, 'عميد الكلية', '{\"college_management\": true, \"department_management\": true, \"program_management\": true, \"teacher_management\": true, \"student_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 40, 'CA300006', 1, 'وكيل الكلية للشؤون الأكاديمية', '{\"college_management\": true, \"department_management\": true, \"program_management\": true, \"teacher_management\": true, \"student_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('private','group') NOT NULL DEFAULT 'private',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `left_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `credit_hours` int(11) NOT NULL,
  `lecture_hours` int(11) NOT NULL,
  `lab_hours` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `courses`
--

INSERT INTO `courses` (`id`, `department_id`, `code`, `name`, `name_en`, `description`, `description_en`, `credit_hours`, `lecture_hours`, `lab_hours`, `level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'CE101', 'مقدمة في الهندسة المدنية', 'Introduction to Civil Engineering', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مقدمة في الهندسة المدنية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Introduction to Civil Engineering and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 1, 'CE201', 'ميكانيكا المواد', 'Mechanics of Materials', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات ميكانيكا المواد وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Mechanics of Materials and its practical applications', 3, 2, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 1, 'CE301', 'تصميم الخرسانة المسلحة', 'Reinforced Concrete Design', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات تصميم الخرسانة المسلحة وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Reinforced Concrete Design and its practical applications', 4, 3, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 1, 'CE401', 'هندسة الأساسات', 'Foundation Engineering', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات هندسة الأساسات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Foundation Engineering and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 2, 'EE101', 'مقدمة في الهندسة الكهربائية', 'Introduction to Electrical Engineering', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مقدمة في الهندسة الكهربائية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Introduction to Electrical Engineering and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 2, 'EE201', 'الدوائر الكهربائية', 'Electrical Circuits', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الدوائر الكهربائية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Electrical Circuits and its practical applications', 4, 3, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 2, 'EE301', 'الإلكترونيات', 'Electronics', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الإلكترونيات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Electronics and its practical applications', 4, 3, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 2, 'EE401', 'أنظمة الطاقة الكهربائية', 'Electrical Power Systems', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات أنظمة الطاقة الكهربائية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Electrical Power Systems and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 3, 'ME101', 'مقدمة في الهندسة الميكانيكية', 'Introduction to Mechanical Engineering', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مقدمة في الهندسة الميكانيكية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Introduction to Mechanical Engineering and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 3, 'ME201', 'ديناميكا حرارية', 'Thermodynamics', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات ديناميكا حرارية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Thermodynamics and its practical applications', 3, 3, 0, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 3, 'ME301', 'ميكانيكا الموائع', 'Fluid Mechanics', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات ميكانيكا الموائع وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Fluid Mechanics and its practical applications', 4, 3, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(12, 3, 'ME401', 'تصميم الآلات', 'Machine Design', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات تصميم الآلات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Machine Design and its practical applications', 3, 2, 2, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(13, 4, 'CS101', 'مقدمة في البرمجة', 'Introduction to Programming', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مقدمة في البرمجة وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Introduction to Programming and its practical applications', 4, 3, 2, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(14, 4, 'CS201', 'هياكل البيانات', 'Data Structures', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات هياكل البيانات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Data Structures and its practical applications', 4, 3, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(15, 4, 'CS301', 'قواعد البيانات', 'Databases', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات قواعد البيانات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Databases and its practical applications', 3, 2, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(16, 4, 'CS401', 'الذكاء الاصطناعي', 'Artificial Intelligence', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الذكاء الاصطناعي وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Artificial Intelligence and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(17, 5, 'SE101', 'مقدمة في هندسة البرمجيات', 'Introduction to Software Engineering', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مقدمة في هندسة البرمجيات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Introduction to Software Engineering and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(18, 5, 'SE201', 'تحليل وتصميم النظم', 'Systems Analysis and Design', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات تحليل وتصميم النظم وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Systems Analysis and Design and its practical applications', 3, 2, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(19, 5, 'SE301', 'اختبار البرمجيات', 'Software Testing', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات اختبار البرمجيات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Software Testing and its practical applications', 3, 2, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(20, 5, 'SE401', 'إدارة مشاريع البرمجيات', 'Software Project Management', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات إدارة مشاريع البرمجيات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Software Project Management and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(21, 6, 'IS101', 'مقدمة في نظم المعلومات', 'Introduction to Information Systems', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مقدمة في نظم المعلومات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Introduction to Information Systems and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(22, 6, 'IS201', 'تطوير تطبيقات الويب', 'Web Application Development', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات تطوير تطبيقات الويب وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Web Application Development and its practical applications', 4, 3, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(23, 6, 'IS301', 'أمن المعلومات', 'Information Security', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات أمن المعلومات وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Information Security and its practical applications', 3, 3, 0, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(24, 6, 'IS401', 'ذكاء الأعمال', 'Business Intelligence', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات ذكاء الأعمال وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Business Intelligence and its practical applications', 3, 2, 2, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(25, 7, 'MATH101', 'حساب التفاضل والتكامل 1', 'Calculus I', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات حساب التفاضل والتكامل 1 وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Calculus I and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(26, 7, 'MATH201', 'الجبر الخطي', 'Linear Algebra', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الجبر الخطي وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Linear Algebra and its practical applications', 3, 3, 0, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(27, 7, 'MATH301', 'المعادلات التفاضلية', 'Differential Equations', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات المعادلات التفاضلية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Differential Equations and its practical applications', 3, 3, 0, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(28, 7, 'MATH401', 'التحليل العددي', 'Numerical Analysis', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات التحليل العددي وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Numerical Analysis and its practical applications', 3, 2, 2, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(29, 8, 'PHYS101', 'فيزياء عامة 1', 'General Physics I', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات فيزياء عامة 1 وتطبيقاتها العملية', 'This course aims to introduce students to the basics of General Physics I and its practical applications', 4, 3, 2, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(30, 8, 'PHYS201', 'الميكانيكا الكلاسيكية', 'Classical Mechanics', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الميكانيكا الكلاسيكية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Classical Mechanics and its practical applications', 3, 3, 0, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(31, 8, 'PHYS301', 'الكهرومغناطيسية', 'Electromagnetism', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الكهرومغناطيسية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Electromagnetism and its practical applications', 4, 3, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(32, 8, 'PHYS401', 'فيزياء الكم', 'Quantum Physics', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات فيزياء الكم وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Quantum Physics and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(33, 9, 'CHEM101', 'كيمياء عامة', 'General Chemistry', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات كيمياء عامة وتطبيقاتها العملية', 'This course aims to introduce students to the basics of General Chemistry and its practical applications', 4, 3, 2, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(34, 9, 'CHEM201', 'الكيمياء العضوية', 'Organic Chemistry', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الكيمياء العضوية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Organic Chemistry and its practical applications', 4, 3, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(35, 9, 'CHEM301', 'الكيمياء التحليلية', 'Analytical Chemistry', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الكيمياء التحليلية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Analytical Chemistry and its practical applications', 4, 3, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(36, 9, 'CHEM401', 'الكيمياء الفيزيائية', 'Physical Chemistry', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الكيمياء الفيزيائية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Physical Chemistry and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(37, 10, 'ACC101', 'مبادئ المحاسبة 1', 'Principles of Accounting I', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مبادئ المحاسبة 1 وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Principles of Accounting I and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(38, 10, 'ACC201', 'محاسبة متوسطة', 'Intermediate Accounting', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات محاسبة متوسطة وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Intermediate Accounting and its practical applications', 3, 3, 0, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(39, 10, 'ACC301', 'محاسبة التكاليف', 'Cost Accounting', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات محاسبة التكاليف وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Cost Accounting and its practical applications', 3, 3, 0, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(40, 10, 'ACC401', 'المراجعة', 'Auditing', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات المراجعة وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Auditing and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(41, 11, 'BA101', 'مبادئ الإدارة', 'Principles of Management', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مبادئ الإدارة وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Principles of Management and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(42, 11, 'BA201', 'السلوك التنظيمي', 'Organizational Behavior', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات السلوك التنظيمي وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Organizational Behavior and its practical applications', 3, 3, 0, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(43, 11, 'BA301', 'إدارة الموارد البشرية', 'Human Resource Management', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات إدارة الموارد البشرية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Human Resource Management and its practical applications', 3, 3, 0, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(44, 11, 'BA401', 'الإدارة الاستراتيجية', 'Strategic Management', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الإدارة الاستراتيجية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Strategic Management and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(45, 12, 'MKT101', 'مبادئ التسويق', 'Principles of Marketing', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات مبادئ التسويق وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Principles of Marketing and its practical applications', 3, 3, 0, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(46, 12, 'MKT201', 'سلوك المستهلك', 'Consumer Behavior', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات سلوك المستهلك وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Consumer Behavior and its practical applications', 3, 3, 0, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(47, 12, 'MKT301', 'التسويق الرقمي', 'Digital Marketing', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات التسويق الرقمي وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Digital Marketing and its practical applications', 3, 2, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(48, 12, 'MKT401', 'إدارة العلامات التجارية', 'Brand Management', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات إدارة العلامات التجارية وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Brand Management and its practical applications', 3, 3, 0, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(49, 13, 'MED101', 'تشريح الجسم البشري', 'Human Anatomy', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات تشريح الجسم البشري وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Human Anatomy and its practical applications', 4, 3, 2, 1, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(50, 13, 'MED201', 'علم وظائف الأعضاء', 'Physiology', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات علم وظائف الأعضاء وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Physiology and its practical applications', 4, 3, 2, 2, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(51, 13, 'MED301', 'علم الأمراض', 'Pathology', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات علم الأمراض وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Pathology and its practical applications', 4, 3, 2, 3, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(52, 13, 'MED401', 'الطب الباطني', 'Internal Medicine', 'يهدف هذا المقرر إلى تعريف الطلاب بأساسيات الطب الباطني وتطبيقاتها العملية', 'This course aims to introduce students to the basics of Internal Medicine and its practical applications', 6, 4, 4, 4, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `course_contents`
--

CREATE TABLE `course_contents` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `content_type` enum('file','video','link','text') NOT NULL,
  `content_path` varchar(255) DEFAULT NULL,
  `external_url` varchar(255) DEFAULT NULL,
  `text_content` text DEFAULT NULL,
  `order` int(11) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `type` enum('document','video','link','other') NOT NULL DEFAULT 'document',
  `external_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `course_prerequisites`
--

CREATE TABLE `course_prerequisites` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `prerequisite_course_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `course_prerequisites`
--

INSERT INTO `course_prerequisites` (`id`, `course_id`, `prerequisite_course_id`, `created_at`) VALUES
(1, 2, 1, '2025-05-23 19:46:56'),
(2, 3, 2, '2025-05-23 19:46:56'),
(3, 4, 3, '2025-05-23 19:46:56'),
(4, 6, 5, '2025-05-23 19:46:56'),
(5, 7, 5, '2025-05-23 19:46:56'),
(6, 7, 6, '2025-05-23 19:46:56'),
(7, 8, 5, '2025-05-23 19:46:56'),
(8, 8, 6, '2025-05-23 19:46:56'),
(9, 10, 9, '2025-05-23 19:46:56'),
(10, 11, 10, '2025-05-23 19:46:56'),
(11, 11, 9, '2025-05-23 19:46:56'),
(12, 12, 11, '2025-05-23 19:46:56'),
(13, 12, 10, '2025-05-23 19:46:56'),
(14, 14, 13, '2025-05-23 19:46:56'),
(15, 15, 14, '2025-05-23 19:46:56'),
(16, 16, 13, '2025-05-23 19:46:56'),
(17, 16, 15, '2025-05-23 19:46:56'),
(18, 18, 17, '2025-05-23 19:46:56'),
(19, 19, 17, '2025-05-23 19:46:56'),
(20, 20, 17, '2025-05-23 19:46:56'),
(21, 22, 21, '2025-05-23 19:46:56'),
(22, 23, 21, '2025-05-23 19:46:56'),
(23, 24, 23, '2025-05-23 19:46:56'),
(24, 24, 21, '2025-05-23 19:46:56'),
(25, 26, 25, '2025-05-23 19:46:56'),
(26, 27, 26, '2025-05-23 19:46:56'),
(27, 28, 27, '2025-05-23 19:46:56'),
(28, 30, 29, '2025-05-23 19:46:56'),
(29, 31, 30, '2025-05-23 19:46:56'),
(30, 31, 29, '2025-05-23 19:46:56'),
(31, 32, 30, '2025-05-23 19:46:56'),
(32, 32, 31, '2025-05-23 19:46:56'),
(33, 34, 33, '2025-05-23 19:46:56'),
(34, 35, 34, '2025-05-23 19:46:56'),
(35, 36, 35, '2025-05-23 19:46:56'),
(36, 38, 37, '2025-05-23 19:46:56'),
(37, 39, 37, '2025-05-23 19:46:56'),
(38, 40, 39, '2025-05-23 19:46:56'),
(39, 42, 41, '2025-05-23 19:46:56'),
(40, 43, 42, '2025-05-23 19:46:56'),
(41, 43, 41, '2025-05-23 19:46:56'),
(42, 44, 42, '2025-05-23 19:46:56'),
(43, 46, 45, '2025-05-23 19:46:56'),
(44, 47, 46, '2025-05-23 19:46:56'),
(45, 47, 45, '2025-05-23 19:46:56'),
(46, 48, 47, '2025-05-23 19:46:56'),
(47, 48, 46, '2025-05-23 19:46:56'),
(48, 50, 49, '2025-05-23 19:46:56'),
(49, 51, 50, '2025-05-23 19:46:56'),
(50, 52, 49, '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `registration_date` datetime NOT NULL,
  `status` enum('registered','dropped','withdrawn') NOT NULL DEFAULT 'registered',
  `grade` varchar(2) DEFAULT NULL,
  `grade_points` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `course_sections`
--

CREATE TABLE `course_sections` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `section_number` varchar(10) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `enrolled_count` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `days` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('open','closed','cancelled') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `course_units`
--

CREATE TABLE `course_units` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `order` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `course_waitlists`
--

CREATE TABLE `course_waitlists` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `status` enum('waiting','enrolled','cancelled','expired') NOT NULL DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `departments`
--

INSERT INTO `departments` (`id`, `college_id`, `name`, `name_en`, `code`, `description`, `description_en`, `head_id`, `location`, `contact_email`, `contact_phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'الهندسة المدنية', 'Civil Engineering', 'CE', 'قسم الهندسة المدنية يقدم برامج متميزة في مجال الهندسة المدنية', 'The Department of Civil Engineering offers distinguished programs in the field of Civil Engineering', 7, 'مبنى 1، الطابق 3', 'ce@univerboard.edu', '0581826930', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 1, 'الهندسة الكهربائية', 'Electrical Engineering', 'EE', 'قسم الهندسة الكهربائية يقدم برامج متميزة في مجال الهندسة الكهربائية', 'The Department of Electrical Engineering offers distinguished programs in the field of Electrical Engineering', NULL, 'مبنى 1، الطابق 3', 'ee@univerboard.edu', '0523020562', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 1, 'الهندسة الميكانيكية', 'Mechanical Engineering', 'ME', 'قسم الهندسة الميكانيكية يقدم برامج متميزة في مجال الهندسة الميكانيكية', 'The Department of Mechanical Engineering offers distinguished programs in the field of Mechanical Engineering', NULL, 'مبنى 1، الطابق 2', 'me@univerboard.edu', '0547693512', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 2, 'علوم الحاسب', 'Computer Science', 'CS', 'قسم علوم الحاسب يقدم برامج متميزة في مجال علوم الحاسب', 'The Department of Computer Science offers distinguished programs in the field of Computer Science', NULL, 'مبنى 2، الطابق 1', 'cs@univerboard.edu', '0555707361', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 2, 'هندسة البرمجيات', 'Software Engineering', 'SE', 'قسم هندسة البرمجيات يقدم برامج متميزة في مجال هندسة البرمجيات', 'The Department of Software Engineering offers distinguished programs in the field of Software Engineering', NULL, 'مبنى 2، الطابق 1', 'se@univerboard.edu', '0557722901', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 2, 'نظم المعلومات', 'Information Systems', 'IS', 'قسم نظم المعلومات يقدم برامج متميزة في مجال نظم المعلومات', 'The Department of Information Systems offers distinguished programs in the field of Information Systems', 6, 'مبنى 2، الطابق 3', 'is@univerboard.edu', '0535825577', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 3, 'الرياضيات', 'Mathematics', 'MATH', 'قسم الرياضيات يقدم برامج متميزة في مجال الرياضيات', 'The Department of Mathematics offers distinguished programs in the field of Mathematics', NULL, 'مبنى 3، الطابق 1', 'math@univerboard.edu', '0570988484', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 3, 'الفيزياء', 'Physics', 'PHYS', 'قسم الفيزياء يقدم برامج متميزة في مجال الفيزياء', 'The Department of Physics offers distinguished programs in the field of Physics', NULL, 'مبنى 3، الطابق 2', 'phys@univerboard.edu', '0548348855', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 3, 'الكيمياء', 'Chemistry', 'CHEM', 'قسم الكيمياء يقدم برامج متميزة في مجال الكيمياء', 'The Department of Chemistry offers distinguished programs in the field of Chemistry', 9, 'مبنى 3، الطابق 3', 'chem@univerboard.edu', '0528670848', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 4, 'المحاسبة', 'Accounting', 'ACC', 'قسم المحاسبة يقدم برامج متميزة في مجال المحاسبة', 'The Department of Accounting offers distinguished programs in the field of Accounting', NULL, 'مبنى 4، الطابق 2', 'acc@univerboard.edu', '0549621551', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 4, 'إدارة الأعمال', 'Business Administration', 'BA', 'قسم إدارة الأعمال يقدم برامج متميزة في مجال إدارة الأعمال', 'The Department of Business Administration offers distinguished programs in the field of Business Administration', 8, 'مبنى 4، الطابق 2', 'ba@univerboard.edu', '0533441741', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(12, 4, 'التسويق', 'Marketing', 'MKT', 'قسم التسويق يقدم برامج متميزة في مجال التسويق', 'The Department of Marketing offers distinguished programs in the field of Marketing', 3, 'مبنى 4، الطابق 2', 'mkt@univerboard.edu', '0562008503', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(13, 5, 'الطب البشري', 'Medicine', 'MED', 'قسم الطب البشري يقدم برامج متميزة في مجال الطب البشري', 'The Department of Medicine offers distinguished programs in the field of Medicine', 11, 'مبنى 5، الطابق 2', 'med@univerboard.edu', '0590036222', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(14, 5, 'العلوم الطبية الأساسية', 'Basic Medical Sciences', 'BMS', 'قسم العلوم الطبية الأساسية يقدم برامج متميزة في مجال العلوم الطبية الأساسية', 'The Department of Basic Medical Sciences offers distinguished programs in the field of Basic Medical Sciences', 2, 'مبنى 5، الطابق 3', 'bms@univerboard.edu', '0599823031', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(15, 5, 'طب الأسرة والمجتمع', 'Family and Community Medicine', 'FCM', 'قسم طب الأسرة والمجتمع يقدم برامج متميزة في مجال طب الأسرة والمجتمع', 'The Department of Family and Community Medicine offers distinguished programs in the field of Family and Community Medicine', NULL, 'مبنى 5، الطابق 3', 'fcm@univerboard.edu', '0531516606', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `discussion_forums`
--

CREATE TABLE `discussion_forums` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `discussion_replies`
--

CREATE TABLE `discussion_replies` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `discussion_topics`
--

CREATE TABLE `discussion_topics` (
  `id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `max_grade` decimal(5,2) NOT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `fcm_tokens`
--

CREATE TABLE `fcm_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `device_type` enum('web','android','ios') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `forums`
--

CREATE TABLE `forums` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_solution` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `forum_topics`
--

CREATE TABLE `forum_topics` (
  `id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `last_reply_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `content_en` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `title_en` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `instructions_en` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `total_marks` decimal(5,2) NOT NULL,
  `weight_percentage` decimal(5,2) NOT NULL,
  `attempts_allowed` int(11) NOT NULL DEFAULT 1,
  `shuffle_questions` tinyint(1) NOT NULL DEFAULT 0,
  `show_results` enum('after_submission','after_deadline','never') NOT NULL DEFAULT 'after_deadline',
  `passing_score` decimal(5,2) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `text_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `total_score` decimal(5,2) DEFAULT NULL,
  `status` enum('in_progress','completed','timed_out') NOT NULL DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_text_en` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','essay') NOT NULL,
  `marks` decimal(5,2) NOT NULL,
  `order` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `quiz_question_options`
--

CREATE TABLE `quiz_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `option_text_en` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'مشرف النظام', 'صلاحيات كاملة للنظام', '{\"all\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 'مدير كلية', 'إدارة الكلية والأقسام والبرامج', '{\"college_management\": true, \"department_management\": true, \"program_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 'رئيس قسم', 'إدارة القسم والبرامج والمقررات', '{\"department_management\": true, \"program_management\": true, \"course_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 'منسق برنامج', 'إدارة البرنامج والخطط الدراسية', '{\"program_management\": true, \"study_plan_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 'معلم', 'إدارة المقررات والشعب والتقييمات', '{\"course_teaching\": true, \"grade_management\": true, \"assignment_management\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 'طالب', 'تسجيل المقررات وعرض الدرجات', '{\"course_registration\": true, \"grade_view\": true, \"assignment_submission\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `college_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `academic_level` int(11) NOT NULL,
  `admission_date` date NOT NULL,
  `expected_graduation_date` date DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `status` enum('active','graduated','suspended','withdrawn') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `college_id`, `department_id`, `program_id`, `academic_level`, `admission_date`, `expected_graduation_date`, `gpa`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'S200001', 3, 8, 11, 3, '2023-11-23', '2027-11-22', '2.29', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 2, 'S200002', 1, 3, 4, 3, '2020-07-23', '2025-07-22', '2.61', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 3, 'S200003', 5, 13, 17, 4, '2021-07-24', '2027-07-23', '3.87', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 4, 'S200004', 2, 6, 9, 2, '2023-06-10', '2027-06-09', '3.56', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 5, 'S200005', 3, 8, 11, 4, '2022-05-27', '2026-05-26', '2.47', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 6, 'S200006', 4, 11, 15, 1, '2020-11-14', '2022-11-14', '3.36', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 7, 'S200007', 1, 2, 3, 2, '2022-06-14', '2024-06-13', '3.01', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 8, 'S200008', 2, 4, 7, 4, '2022-02-22', '2026-02-21', '3.86', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 9, 'S200009', 3, 7, 10, 2, '2021-07-14', '2025-07-13', '2.53', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 10, 'S200010', 3, 9, 12, 4, '2023-04-06', '2027-04-05', '3.05', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 11, 'S200011', 2, 5, 8, 2, '2021-08-09', '2025-08-08', '3.53', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(12, 12, 'S200012', 5, 13, 17, 4, '2022-08-21', '2028-08-19', '3.83', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(13, 13, 'S200013', 2, 5, 8, 1, '2023-07-13', '2027-07-12', '3.73', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(14, 14, 'S200014', 5, 13, 17, 3, '2021-03-08', '2027-03-07', '2.54', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(15, 15, 'S200015', 2, 4, 5, 4, '2020-07-31', '2024-07-30', '3.40', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(16, 16, 'S200016', 1, 2, 2, 2, '2023-10-18', '2028-10-16', '2.67', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(17, 17, 'S200017', 2, 4, 7, 4, '2020-07-23', '2024-07-22', '3.90', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(18, 18, 'S200018', 1, 3, 4, 1, '2022-10-25', '2027-10-24', '3.90', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(19, 19, 'S200019', 4, 10, 13, 3, '2023-12-18', '2027-12-17', '3.77', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(20, 20, 'S200020', 3, 9, 12, 1, '2023-03-17', '2027-03-16', '2.55', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(21, 38, 'S200021', 2, 5, 8, 2, '2020-12-08', '2024-12-07', '2.59', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `study_plans`
--

CREATE TABLE `study_plans` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `total_credit_hours` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `study_plans`
--

INSERT INTO `study_plans` (`id`, `program_id`, `name`, `name_en`, `description`, `description_en`, `effective_from`, `effective_to`, `total_credit_hours`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'خطة بكالوريوس الهندسة المدنية 2025', 'Bachelor of Civil Engineering Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الهندسة المدنية المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Civil Engineering program for the academic year 2025', '2025-09-01', NULL, 150, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 2, 'خطة بكالوريوس الهندسة الكهربائية 2025', 'Bachelor of Electrical Engineering Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الهندسة الكهربائية المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Electrical Engineering program for the academic year 2025', '2025-09-01', NULL, 150, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 3, 'خطة ماجستير الهندسة الكهربائية 2025', 'Master of Electrical Engineering Plan 2025', 'الخطة الدراسية لبرنامج ماجستير الهندسة الكهربائية المعتمدة للعام الدراسي 2025', 'The approved study plan for Master of Electrical Engineering program for the academic year 2025', '2025-09-01', NULL, 36, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 4, 'خطة بكالوريوس الهندسة الميكانيكية 2025', 'Bachelor of Mechanical Engineering Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الهندسة الميكانيكية المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Mechanical Engineering program for the academic year 2025', '2025-09-01', NULL, 150, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 5, 'خطة بكالوريوس علوم الحاسب 2025', 'Bachelor of Computer Science Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس علوم الحاسب المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Computer Science program for the academic year 2025', '2025-09-01', NULL, 132, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 6, 'خطة ماجستير علوم الحاسب 2025', 'Master of Computer Science Plan 2025', 'الخطة الدراسية لبرنامج ماجستير علوم الحاسب المعتمدة للعام الدراسي 2025', 'The approved study plan for Master of Computer Science program for the academic year 2025', '2025-09-01', NULL, 36, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 7, 'خطة دكتوراه علوم الحاسب 2025', 'PhD in Computer Science Plan 2025', 'الخطة الدراسية لبرنامج دكتوراه علوم الحاسب المعتمدة للعام الدراسي 2025', 'The approved study plan for PhD in Computer Science program for the academic year 2025', '2025-09-01', NULL, 60, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 8, 'خطة بكالوريوس هندسة البرمجيات 2025', 'Bachelor of Software Engineering Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس هندسة البرمجيات المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Software Engineering program for the academic year 2025', '2025-09-01', NULL, 132, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 9, 'خطة بكالوريوس نظم المعلومات 2025', 'Bachelor of Information Systems Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس نظم المعلومات المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Information Systems program for the academic year 2025', '2025-09-01', NULL, 132, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 10, 'خطة بكالوريوس الرياضيات 2025', 'Bachelor of Mathematics Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الرياضيات المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Mathematics program for the academic year 2025', '2025-09-01', NULL, 128, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 11, 'خطة بكالوريوس الفيزياء 2025', 'Bachelor of Physics Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الفيزياء المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Physics program for the academic year 2025', '2025-09-01', NULL, 128, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(12, 12, 'خطة بكالوريوس الكيمياء 2025', 'Bachelor of Chemistry Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الكيمياء المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Chemistry program for the academic year 2025', '2025-09-01', NULL, 128, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(13, 13, 'خطة بكالوريوس المحاسبة 2025', 'Bachelor of Accounting Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس المحاسبة المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Accounting program for the academic year 2025', '2025-09-01', NULL, 128, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(14, 14, 'خطة بكالوريوس إدارة الأعمال 2025', 'Bachelor of Business Administration Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس إدارة الأعمال المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Business Administration program for the academic year 2025', '2025-09-01', NULL, 128, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(15, 15, 'خطة ماجستير إدارة الأعمال 2025', 'Master of Business Administration Plan 2025', 'الخطة الدراسية لبرنامج ماجستير إدارة الأعمال المعتمدة للعام الدراسي 2025', 'The approved study plan for Master of Business Administration program for the academic year 2025', '2025-09-01', NULL, 42, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(16, 16, 'خطة بكالوريوس التسويق 2025', 'Bachelor of Marketing Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس التسويق المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Marketing program for the academic year 2025', '2025-09-01', NULL, 128, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(17, 17, 'خطة بكالوريوس الطب والجراحة 2025', 'Bachelor of Medicine and Surgery Plan 2025', 'الخطة الدراسية لبرنامج بكالوريوس الطب والجراحة المعتمدة للعام الدراسي 2025', 'The approved study plan for Bachelor of Medicine and Surgery program for the academic year 2025', '2025-09-01', NULL, 220, 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `study_plan_courses`
--

CREATE TABLE `study_plan_courses` (
  `id` int(11) NOT NULL,
  `study_plan_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `study_plan_courses`
--

INSERT INTO `study_plan_courses` (`id`, `study_plan_id`, `course_id`, `semester`, `is_required`, `created_at`) VALUES
(1, 1, 26, 1, 1, '2025-05-23 19:46:56'),
(2, 1, 25, 1, 1, '2025-05-23 19:46:56'),
(3, 1, 27, 1, 1, '2025-05-23 19:46:56'),
(4, 1, 4, 1, 1, '2025-05-23 19:46:56'),
(5, 1, 32, 1, 1, '2025-05-23 19:46:56'),
(6, 1, 29, 1, 1, '2025-05-23 19:46:56'),
(7, 1, 33, 2, 1, '2025-05-23 19:46:56'),
(8, 1, 3, 2, 1, '2025-05-23 19:46:56'),
(9, 1, 35, 2, 1, '2025-05-23 19:46:56'),
(10, 1, 31, 2, 1, '2025-05-23 19:46:56'),
(11, 1, 1, 2, 1, '2025-05-23 19:46:56'),
(12, 1, 36, 2, 1, '2025-05-23 19:46:56'),
(13, 1, 34, 3, 1, '2025-05-23 19:46:56'),
(14, 1, 30, 3, 1, '2025-05-23 19:46:57'),
(15, 1, 2, 3, 1, '2025-05-23 19:46:57'),
(16, 1, 28, 3, 1, '2025-05-23 19:46:57'),
(17, 2, 27, 1, 1, '2025-05-23 19:46:57'),
(18, 2, 29, 1, 1, '2025-05-23 19:46:57'),
(19, 2, 30, 1, 1, '2025-05-23 19:46:57'),
(20, 2, 5, 1, 1, '2025-05-23 19:46:57'),
(21, 2, 34, 1, 1, '2025-05-23 19:46:57'),
(22, 2, 26, 2, 1, '2025-05-23 19:46:57'),
(23, 2, 7, 2, 1, '2025-05-23 19:46:57'),
(24, 2, 31, 2, 1, '2025-05-23 19:46:57'),
(25, 2, 33, 2, 1, '2025-05-23 19:46:57'),
(26, 2, 35, 3, 1, '2025-05-23 19:46:57'),
(27, 2, 25, 3, 1, '2025-05-23 19:46:57'),
(28, 2, 28, 3, 1, '2025-05-23 19:46:57'),
(29, 2, 36, 3, 1, '2025-05-23 19:46:57'),
(30, 2, 6, 3, 1, '2025-05-23 19:46:57'),
(31, 2, 32, 3, 1, '2025-05-23 19:46:57'),
(32, 2, 8, 4, 1, '2025-05-23 19:46:57'),
(33, 3, 32, 1, 1, '2025-05-23 19:46:57'),
(34, 3, 27, 1, 1, '2025-05-23 19:46:57'),
(35, 3, 34, 1, 1, '2025-05-23 19:46:57'),
(36, 3, 7, 1, 1, '2025-05-23 19:46:57'),
(37, 3, 6, 2, 1, '2025-05-23 19:46:57'),
(38, 3, 35, 2, 1, '2025-05-23 19:46:57'),
(39, 3, 31, 2, 1, '2025-05-23 19:46:57'),
(40, 3, 33, 2, 1, '2025-05-23 19:46:57'),
(41, 3, 26, 2, 1, '2025-05-23 19:46:57'),
(42, 3, 25, 2, 1, '2025-05-23 19:46:57'),
(43, 3, 5, 3, 1, '2025-05-23 19:46:57'),
(44, 3, 8, 3, 1, '2025-05-23 19:46:57'),
(45, 3, 36, 3, 1, '2025-05-23 19:46:57'),
(46, 3, 30, 3, 1, '2025-05-23 19:46:57'),
(47, 3, 29, 3, 1, '2025-05-23 19:46:57'),
(48, 3, 28, 4, 1, '2025-05-23 19:46:57'),
(49, 4, 28, 1, 1, '2025-05-23 19:46:57'),
(50, 4, 32, 1, 1, '2025-05-23 19:46:57'),
(51, 4, 29, 1, 1, '2025-05-23 19:46:57'),
(52, 4, 11, 1, 1, '2025-05-23 19:46:57'),
(53, 4, 25, 1, 1, '2025-05-23 19:46:57'),
(54, 4, 27, 2, 1, '2025-05-23 19:46:57'),
(55, 4, 33, 2, 1, '2025-05-23 19:46:57'),
(56, 4, 31, 2, 1, '2025-05-23 19:46:57'),
(57, 4, 26, 2, 1, '2025-05-23 19:46:57'),
(58, 4, 12, 3, 1, '2025-05-23 19:46:57'),
(59, 4, 30, 3, 1, '2025-05-23 19:46:57'),
(60, 4, 9, 3, 1, '2025-05-23 19:46:57'),
(61, 4, 10, 3, 1, '2025-05-23 19:46:57'),
(62, 4, 34, 3, 1, '2025-05-23 19:46:57'),
(63, 4, 36, 3, 1, '2025-05-23 19:46:57'),
(64, 4, 35, 4, 1, '2025-05-23 19:46:57'),
(65, 5, 32, 1, 1, '2025-05-23 19:46:57'),
(66, 5, 28, 1, 1, '2025-05-23 19:46:57'),
(67, 5, 33, 1, 1, '2025-05-23 19:46:57'),
(68, 5, 14, 1, 1, '2025-05-23 19:46:57'),
(69, 5, 13, 1, 1, '2025-05-23 19:46:57'),
(70, 5, 27, 2, 1, '2025-05-23 19:46:57'),
(71, 5, 15, 2, 1, '2025-05-23 19:46:57'),
(72, 5, 26, 2, 1, '2025-05-23 19:46:57'),
(73, 5, 35, 2, 1, '2025-05-23 19:46:57'),
(74, 5, 36, 2, 1, '2025-05-23 19:46:57'),
(75, 5, 30, 2, 1, '2025-05-23 19:46:57'),
(76, 5, 29, 3, 1, '2025-05-23 19:46:57'),
(77, 5, 34, 3, 1, '2025-05-23 19:46:57'),
(78, 5, 16, 3, 1, '2025-05-23 19:46:57'),
(79, 5, 31, 3, 1, '2025-05-23 19:46:57'),
(80, 5, 25, 3, 1, '2025-05-23 19:46:57'),
(81, 6, 28, 1, 1, '2025-05-23 19:46:57'),
(82, 6, 29, 1, 1, '2025-05-23 19:46:57'),
(83, 6, 16, 1, 1, '2025-05-23 19:46:57'),
(84, 6, 15, 1, 1, '2025-05-23 19:46:57'),
(85, 6, 31, 1, 1, '2025-05-23 19:46:57'),
(86, 6, 14, 2, 1, '2025-05-23 19:46:57'),
(87, 6, 34, 2, 1, '2025-05-23 19:46:57'),
(88, 6, 30, 2, 1, '2025-05-23 19:46:57'),
(89, 6, 13, 2, 1, '2025-05-23 19:46:57'),
(90, 6, 26, 2, 1, '2025-05-23 19:46:57'),
(91, 6, 25, 2, 1, '2025-05-23 19:46:57'),
(92, 6, 32, 3, 1, '2025-05-23 19:46:57'),
(93, 6, 36, 3, 1, '2025-05-23 19:46:57'),
(94, 6, 27, 3, 1, '2025-05-23 19:46:57'),
(95, 6, 35, 3, 1, '2025-05-23 19:46:57'),
(96, 6, 33, 3, 1, '2025-05-23 19:46:57'),
(97, 7, 33, 1, 1, '2025-05-23 19:46:57'),
(98, 7, 31, 1, 1, '2025-05-23 19:46:57'),
(99, 7, 25, 1, 1, '2025-05-23 19:46:57'),
(100, 7, 28, 1, 1, '2025-05-23 19:46:57'),
(101, 7, 32, 1, 1, '2025-05-23 19:46:57'),
(102, 7, 16, 1, 1, '2025-05-23 19:46:57'),
(103, 7, 14, 2, 1, '2025-05-23 19:46:57'),
(104, 7, 29, 2, 1, '2025-05-23 19:46:57'),
(105, 7, 36, 2, 1, '2025-05-23 19:46:57'),
(106, 7, 15, 2, 1, '2025-05-23 19:46:57'),
(107, 7, 30, 2, 1, '2025-05-23 19:46:57'),
(108, 7, 13, 3, 1, '2025-05-23 19:46:57'),
(109, 7, 27, 3, 1, '2025-05-23 19:46:57'),
(110, 7, 26, 3, 1, '2025-05-23 19:46:57'),
(111, 7, 35, 3, 1, '2025-05-23 19:46:57'),
(112, 7, 34, 3, 1, '2025-05-23 19:46:57'),
(113, 8, 17, 1, 1, '2025-05-23 19:46:57'),
(114, 8, 19, 1, 1, '2025-05-23 19:46:57'),
(115, 8, 26, 1, 1, '2025-05-23 19:46:57'),
(116, 8, 29, 1, 1, '2025-05-23 19:46:57'),
(117, 8, 31, 1, 1, '2025-05-23 19:46:57'),
(118, 8, 20, 2, 1, '2025-05-23 19:46:57'),
(119, 8, 34, 2, 1, '2025-05-23 19:46:57'),
(120, 8, 35, 2, 1, '2025-05-23 19:46:57'),
(121, 8, 32, 2, 1, '2025-05-23 19:46:57'),
(122, 8, 18, 2, 1, '2025-05-23 19:46:57'),
(123, 8, 27, 2, 1, '2025-05-23 19:46:57'),
(124, 8, 28, 3, 1, '2025-05-23 19:46:57'),
(125, 8, 25, 3, 1, '2025-05-23 19:46:57'),
(126, 8, 30, 3, 1, '2025-05-23 19:46:57'),
(127, 8, 36, 3, 1, '2025-05-23 19:46:57'),
(128, 8, 33, 4, 1, '2025-05-23 19:46:57'),
(129, 9, 24, 1, 1, '2025-05-23 19:46:57'),
(130, 9, 21, 1, 1, '2025-05-23 19:46:57'),
(131, 9, 34, 1, 1, '2025-05-23 19:46:57'),
(132, 9, 33, 1, 1, '2025-05-23 19:46:57'),
(133, 9, 26, 2, 1, '2025-05-23 19:46:57'),
(134, 9, 35, 2, 1, '2025-05-23 19:46:57'),
(135, 9, 22, 2, 1, '2025-05-23 19:46:57'),
(136, 9, 23, 2, 1, '2025-05-23 19:46:57'),
(137, 9, 28, 3, 1, '2025-05-23 19:46:57'),
(138, 9, 27, 3, 1, '2025-05-23 19:46:57'),
(139, 9, 25, 3, 1, '2025-05-23 19:46:57'),
(140, 9, 32, 3, 1, '2025-05-23 19:46:57'),
(141, 9, 30, 4, 1, '2025-05-23 19:46:57'),
(142, 9, 29, 4, 1, '2025-05-23 19:46:57'),
(143, 9, 31, 4, 1, '2025-05-23 19:46:57'),
(144, 9, 36, 4, 1, '2025-05-23 19:46:57'),
(145, 10, 27, 1, 1, '2025-05-23 19:46:57'),
(146, 10, 34, 1, 1, '2025-05-23 19:46:57'),
(147, 10, 26, 1, 1, '2025-05-23 19:46:57'),
(148, 10, 28, 1, 1, '2025-05-23 19:46:57'),
(149, 10, 25, 2, 1, '2025-05-23 19:46:57'),
(150, 10, 32, 2, 1, '2025-05-23 19:46:57'),
(151, 10, 35, 2, 1, '2025-05-23 19:46:57'),
(152, 10, 33, 2, 1, '2025-05-23 19:46:57'),
(154, 10, 31, 3, 1, '2025-05-23 19:46:57');

-- --------------------------------------------------------

--
-- بنية الجدول `system_admins`
--

CREATE TABLE `system_admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `role` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `system_admins`
--

INSERT INTO `system_admins` (`id`, `user_id`, `admin_id`, `role`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 36, 'SA400001', 'مدير النظام', '{\"all\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 37, 'SA400002', 'مسؤول الدعم الفني', '{\"all\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 41, 'SA400003', 'مسؤول قواعد البيانات', '{\"all\": true}', '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `teacher_id` varchar(20) NOT NULL,
  `college_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `qualification` varchar(100) NOT NULL,
  `hire_date` date NOT NULL,
  `office_location` varchar(100) DEFAULT NULL,
  `office_hours` text DEFAULT NULL,
  `status` enum('active','on_leave','retired','terminated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_id`, `college_id`, `department_id`, `position`, `specialization`, `qualification`, `hire_date`, `office_location`, `office_hours`, `status`, `created_at`, `updated_at`) VALUES
(1, 21, 'T100001', 1, 1, 'أستاذ مساعد', 'الهندسة المدنية', 'بكالوريوس', '2023-04-16', 'مبنى 1، مكتب 357', 'الأحد والثلاثاء: 10:00 - 15:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(2, 22, 'T100002', 5, 14, 'محاضر', 'العلوم الطبية الأساسية', 'ماجستير', '2021-05-05', 'مبنى 5، مكتب 278', 'الأحد والثلاثاء: 10:00 - 13:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 23, 'T100003', 4, 12, 'أستاذ مساعد', 'التسويق', 'دكتوراه', '2013-07-25', 'مبنى 4، مكتب 443', 'الأحد والثلاثاء: 11:00 - 13:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 24, 'T100004', 1, 1, 'أستاذ', 'الهندسة المدنية', 'ماجستير', '2018-02-18', 'مبنى 1، مكتب 350', 'الأحد والثلاثاء: 11:00 - 14:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 25, 'T100005', 1, 1, 'محاضر', 'الهندسة المدنية', 'بكالوريوس', '2014-03-25', 'مبنى 1، مكتب 128', 'الأحد والثلاثاء: 11:00 - 15:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 26, 'T100006', 2, 6, 'معيد', 'نظم المعلومات', 'بكالوريوس', '2023-07-14', 'مبنى 2، مكتب 435', 'الأحد والثلاثاء: 10:00 - 15:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 27, 'T100007', 1, 1, 'معيد', 'الهندسة المدنية', 'دكتوراه', '2023-06-01', 'مبنى 1، مكتب 337', 'الأحد والثلاثاء: 10:00 - 15:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 28, 'T100008', 4, 11, 'أستاذ مشارك', 'إدارة الأعمال', 'بكالوريوس', '2021-06-01', 'مبنى 4، مكتب 500', 'الأحد والثلاثاء: 11:00 - 14:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 29, 'T100009', 3, 9, 'أستاذ مساعد', 'الكيمياء', 'دكتوراه', '2014-01-12', 'مبنى 3، مكتب 166', 'الأحد والثلاثاء: 11:00 - 15:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 30, 'T100010', 2, 6, 'معيد', 'نظم المعلومات', 'ماجستير', '2020-11-19', 'مبنى 2، مكتب 116', 'الأحد والثلاثاء: 9:00 - 15:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 39, 'T100011', 5, 13, 'أستاذ مشارك', 'الطب البشري', 'دكتوراه', '2023-04-05', 'مبنى 5، مكتب 273', 'الأحد والثلاثاء: 11:00 - 12:00', 'active', '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('student','teacher','college_admin','system_admin') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `language` enum('ar','en') NOT NULL DEFAULT 'ar',
  `theme` enum('light','dark') NOT NULL DEFAULT 'light',
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `user_type`, `first_name`, `last_name`, `profile_picture`, `phone`, `address`, `date_of_birth`, `gender`, `language`, `theme`, `last_login`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'student1', 'student1@univerboard.edu', 'student123', 'student', 'محمد', 'مشتاق', 'uploads/profile_pictures/default-user.png', '0579022553', 'طريق الملك خالد، المدينة المنورة', '1997-05-30', 'male', 'ar', 'light', '2025-07-09 19:35:11', 1, '2025-05-23 19:46:56', '2025-07-09 16:35:11'),
(2, 'student2', 'student2@univerboard.edu', '213ee683360d88249109c2f92789dbc3', 'student', 'رامي', 'الراشد', NULL, '0557009187', 'شارع الملك فهد، الخبر', '2000-05-29', 'male', 'en', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(3, 'student3', 'student3@univerboard.edu', '8e4947690532bc44a8e41e9fb365b76a', 'student', 'لطيفة', 'الخالد', NULL, '0574098309', 'شارع الملك عبدالله، تبوك', '1999-05-30', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(4, 'student4', 'student4@univerboard.edu', '166a50c910e390d922db4696e4c7747b', 'student', 'محمد', 'السالم', NULL, '0555763815', 'طريق الملك خالد، الظهران', '2001-05-29', 'male', 'en', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(5, 'student5', 'student5@univerboard.edu', '9fd9280a7aa3578c8e853745a5fcc18a', 'student', 'هاني', 'المنصور', NULL, '0563700994', 'شارع الملك عبدالله، الخبر', '2007-05-28', 'male', 'ar', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(6, 'student6', 'student6@univerboard.edu', '27e062bf3df59edebb5db9f89952c8b3', 'student', 'لينا', 'الحمد', NULL, '0548373110', 'شارع الأمير محمد، الرياض', '2005-05-28', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(7, 'student7', 'student7@univerboard.edu', '72e8744fc2faa17a83dec9bed06b8b65', 'student', 'علي', 'الحسن', NULL, '0598151746', 'شارع الأمير محمد، الظهران', '1998-05-30', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(8, 'student8', 'student8@univerboard.edu', '8aa7fb36a4efbbf019332b4677b528cf', 'student', 'زياد', 'المنصور', NULL, '0544657553', 'شارع الأمير سلطان، الظهران', '2001-05-29', 'male', 'ar', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(9, 'student9', 'student9@univerboard.edu', '7c8cd5da17441ff04bf445736964dd16', 'student', 'سهام', 'الحسن', NULL, '0586823378', 'شارع الملك عبدالله، جدة', '1996-05-30', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(10, 'student10', 'student10@univerboard.edu', '2c62e6068c765179e1aed9bc2bfd4689', 'student', 'حسين', 'الصالح', NULL, '0594980859', 'طريق الملك خالد، الظهران', '2001-05-29', 'male', 'en', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(11, 'student11', 'student11@univerboard.edu', '9cf695ac37ef238e62f6ee874b4b3968', 'student', 'ماجد', 'الحميد', NULL, '0517295156', 'شارع الملك فهد، الخبر', '1996-05-30', 'male', 'en', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(12, 'student12', 'student12@univerboard.edu', '7e941d9a3237b1770effdcb05a0aa2a5', 'student', 'سعود', 'الحسين', NULL, '0592266126', 'طريق الملك خالد، المدينة المنورة', '2006-05-28', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(13, 'student13', 'student13@univerboard.edu', 'b32d56347d79af0164e17f8b2b4ff4d0', 'student', 'هيا', 'الأحمد', NULL, '0513928107', 'طريق الملك خالد، المدينة المنورة', '2004-05-28', 'female', 'ar', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(14, 'student14', 'student14@univerboard.edu', '165f0cca0654c0f643fc6f06ae91d353', 'student', 'يوسف', 'الجاسر', NULL, '0522496649', 'شارع الملك عبدالله، المدينة المنورة', '1999-05-30', 'male', 'ar', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(15, 'student15', 'student15@univerboard.edu', '1aab63425143cdbee13e839afc02849f', 'student', 'عبير', 'القحطاني', NULL, '0562333941', 'شارع الأمير سلطان، الظهران', '2006-05-28', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(16, 'student16', 'student16@univerboard.edu', '051bef2eaac7c580b990da8f79459263', 'student', 'ندى', 'الحسن', NULL, '0522145944', 'شارع الملك عبدالله، الدمام', '2000-05-29', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(17, 'student17', 'student17@univerboard.edu', '717edde484efc01f99e58d67181717f9', 'student', 'فاطمة', 'الزيد', NULL, '0581835881', 'طريق الملك خالد، الظهران', '2003-05-29', 'female', 'ar', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(18, 'student18', 'student18@univerboard.edu', '4b8d3feaf05b8735fc91f0a3df478ccf', 'student', 'سامي', 'الحمد', NULL, '0526998065', 'شارع الملك عبدالله، تبوك', '1999-05-30', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(19, 'student19', 'student19@univerboard.edu', '6bcd069ce2644941017227ee0d8d902f', 'student', 'لطيفة', 'المالكي', NULL, '0575652325', 'شارع الملك فهد، جدة', '1998-05-30', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(20, 'student20', 'student20@univerboard.edu', 'b4569eaf92f01ae1b5da1ead071f5a0e', 'student', 'خالد', 'الفارس', NULL, '0554189021', 'شارع الملك فهد، الرياض', '1999-05-30', 'male', 'en', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(21, 'teacher1', 'teacher1@univerboard.edu', '41c8949aa55b8cb5dbec662f34b62df3', 'teacher', 'نورة', 'المطيري', NULL, '0589792697', 'شارع الأمير سلطان، مكة المكرمة', '1983-06-03', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(22, 'teacher2', 'teacher2@univerboard.edu', 'ccffb0bb993eeb79059b31e1611ec353', 'teacher', 'سارة', 'القحطاني', NULL, '0557957925', 'شارع الملك عبدالله، الدمام', '1980-06-03', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(23, 'teacher3', 'teacher3@univerboard.edu', '82470256ea4b80343b27afccbca1015b', 'teacher', 'سلمى', 'العلي', NULL, '0517673972', 'شارع الأمير سلطان، مكة المكرمة', '1968-06-06', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(24, 'teacher4', 'teacher4@univerboard.edu', '93dacda950b1dd917079440788af3321', 'teacher', 'فيصل', 'الأحمد', NULL, '0566019202', 'طريق الملك خالد، أبها', '1977-06-04', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(25, 'teacher5', 'teacher5@univerboard.edu', 'ea105f0d381e790cdadc6a41eb611c77', 'teacher', 'عبدالملك', 'البلوي', NULL, '0570585034', 'شارع الملك عبدالله، الرياض', '1969-06-06', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(26, 'teacher6', 'teacher6@univerboard.edu', 'ff1643afb67a6edb36ee3f6fea756323', 'teacher', 'ريم', 'العمر', NULL, '0527865864', 'طريق الملك خالد، أبها', '1985-06-02', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(27, 'teacher7', 'teacher7@univerboard.edu', '71e0f8d7d61b45e27b57c62eb8684583', 'teacher', 'لينا', 'الشهري', NULL, '0593646011', 'شارع الأمير محمد، الظهران', '1965-06-07', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(28, 'teacher8', 'teacher8@univerboard.edu', 'ee1079e7de417c403b87932ea235dab7', 'teacher', 'سامي', 'الشهري', NULL, '0578361113', 'طريق الملك خالد، مكة المكرمة', '1972-06-05', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(29, 'teacher9', 'teacher9@univerboard.edu', 'e2ad72550f7b4d96d84336d8814edc72', 'teacher', 'عمر', 'السلطان', NULL, '0521458220', 'شارع الملك عبدالله، أبها', '1986-06-02', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(30, 'teacher10', 'teacher10@univerboard.edu', '410a4d044151a691448419d8bf272704', 'teacher', 'إبراهيم', 'الشريف', NULL, '0529000611', 'شارع الأمير سلطان، الخبر', '1989-06-01', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(31, 'college_admin1', 'college_admin1@univerboard.edu', '916035fcd758b65f076f53dcf5e5f88d', 'college_admin', 'لينا', 'الحسين', NULL, '0569963918', 'شارع الأمير سلطان، الظهران', '1990-06-01', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(32, 'college_admin2', 'college_admin2@univerboard.edu', '27ff710e7bb32403da220e2fac1c28ea', 'college_admin', 'لينا', 'الفوزان', NULL, '0596761067', 'شارع الملك فهد، جدة', '1960-06-08', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(33, 'college_admin3', 'college_admin3@univerboard.edu', '6dea5962da3c906a07d35f7995a330dc', 'college_admin', 'أحمد', 'العمر', NULL, '0561289615', 'شارع الأمير محمد، أبها', '1961-06-08', 'male', 'ar', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(34, 'college_admin4', 'college_admin4@univerboard.edu', '9ef8ee947199fbb0b7073d56c2af5d91', 'college_admin', 'سمية', 'الزهراني', NULL, '0558972000', 'شارع الملك فهد، الخبر', '1964-06-07', 'female', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(35, 'college_admin5', 'college_admin5@univerboard.edu', '8386cdf651c8eef7489559963e1a7c6e', 'college_admin', 'ريما', 'الشمري', NULL, '0598720322', 'شارع الملك فهد، الدمام', '1968-06-06', 'female', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(36, 'admin1', 'admin1@univerboard.edu', 'e00cf25ad42683b3df678c61f42c6bda', 'system_admin', 'عبدالرحمن', 'الشايع', NULL, '0544314000', 'شارع الأمير سلطان، الدمام', '1990-06-01', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(37, 'admin2', 'admin2@univerboard.edu', 'c84258e9c39059a89ab77d846ddab909', 'system_admin', 'طارق', 'الزيد', NULL, '0542658767', 'شارع الأمير محمد، أبها', '1992-05-31', 'male', 'en', 'dark', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(38, 'student', 'student@univerboard.com', 'ad6a280417a0f533d8b670c61667e1a0', 'student', 'طالب', 'نموذجي', NULL, '0512345678', 'شارع الجامعة، الرياض', '2000-01-01', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(39, 'teacher', 'teacher@univerboard.com', 'a426dcf72ba25d046591f81a5495eab7', 'teacher', 'معلم', 'نموذجي', NULL, '0523456789', 'شارع المعرفة، الرياض', '1980-05-15', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(40, 'college', 'college@univerboard.com', '3243d92438f16c1c82458315f3975b28', 'college_admin', 'مدير', 'الكلية', NULL, '0534567890', 'شارع العلم، الرياض', '1975-08-20', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56'),
(41, 'admin', 'admin@univerboard.com', '0192023a7bbd73250516f069df18b500', 'system_admin', 'مشرف', 'النظام', NULL, '0545678901', 'شارع التقنية، الرياض', '1985-03-10', 'male', 'ar', 'light', '2025-05-23 15:37:19', 1, '2025-05-23 19:46:56', '2025-05-23 19:46:56');

-- --------------------------------------------------------

--
-- بنية الجدول `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(1, 1, 6, '2025-05-23 19:46:56'),
(2, 2, 6, '2025-05-23 19:46:56'),
(3, 3, 6, '2025-05-23 19:46:56'),
(4, 4, 6, '2025-05-23 19:46:56'),
(5, 5, 6, '2025-05-23 19:46:56'),
(6, 6, 6, '2025-05-23 19:46:56'),
(7, 7, 6, '2025-05-23 19:46:56'),
(8, 8, 6, '2025-05-23 19:46:56'),
(9, 9, 6, '2025-05-23 19:46:56'),
(10, 10, 6, '2025-05-23 19:46:56'),
(11, 11, 6, '2025-05-23 19:46:56'),
(12, 12, 6, '2025-05-23 19:46:56'),
(13, 13, 6, '2025-05-23 19:46:56'),
(14, 14, 6, '2025-05-23 19:46:56'),
(15, 15, 6, '2025-05-23 19:46:56'),
(16, 16, 6, '2025-05-23 19:46:56'),
(17, 17, 6, '2025-05-23 19:46:56'),
(18, 18, 6, '2025-05-23 19:46:56'),
(19, 19, 6, '2025-05-23 19:46:56'),
(20, 20, 6, '2025-05-23 19:46:56'),
(21, 21, 5, '2025-05-23 19:46:56'),
(22, 22, 5, '2025-05-23 19:46:56'),
(23, 23, 5, '2025-05-23 19:46:56'),
(24, 24, 5, '2025-05-23 19:46:56'),
(25, 25, 5, '2025-05-23 19:46:56'),
(26, 26, 5, '2025-05-23 19:46:56'),
(27, 27, 5, '2025-05-23 19:46:56'),
(28, 28, 5, '2025-05-23 19:46:56'),
(29, 29, 5, '2025-05-23 19:46:56'),
(30, 30, 5, '2025-05-23 19:46:56'),
(31, 31, 2, '2025-05-23 19:46:56'),
(32, 32, 2, '2025-05-23 19:46:56'),
(33, 33, 2, '2025-05-23 19:46:56'),
(34, 34, 2, '2025-05-23 19:46:56'),
(35, 35, 2, '2025-05-23 19:46:56'),
(36, 36, 1, '2025-05-23 19:46:56'),
(37, 37, 1, '2025-05-23 19:46:56'),
(38, 38, 6, '2025-05-23 19:46:56'),
(39, 39, 5, '2025-05-23 19:46:56'),
(40, 40, 2, '2025-05-23 19:46:56'),
(41, 41, 1, '2025-05-23 19:46:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_programs`
--
ALTER TABLE `academic_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_code` (`department_id`,`code`),
  ADD KEY `coordinator_id` (`coordinator_id`);

--
-- Indexes for table `academic_terms`
--
ALTER TABLE `academic_terms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academic_year_term_type` (`academic_year`,`term_type`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `announcement_attachments`
--
ALTER TABLE `announcement_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_student` (`assignment_id`,`student_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `graded_by` (`graded_by`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_date` (`section_id`,`date`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `attendance_details`
--
ALTER TABLE `attendance_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_student` (`attendance_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `dean_id` (`dean_id`);

--
-- Indexes for table `college_admins`
--
ALTER TABLE `college_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `conversation_user` (`conversation_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `course_contents`
--
ALTER TABLE `course_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_prerequisite` (`course_id`,`prerequisite_course_id`),
  ADD KEY `prerequisite_course_id` (`prerequisite_course_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_section` (`student_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_term_section` (`course_id`,`term_id`,`section_number`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `course_units`
--
ALTER TABLE `course_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `course_waitlists`
--
ALTER TABLE `course_waitlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_section` (`student_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `college_code` (`college_id`,`code`),
  ADD KEY `head_id` (`head_id`);

--
-- Indexes for table `discussion_forums`
--
ALTER TABLE `discussion_forums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `discussion_topics`
--
ALTER TABLE `discussion_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_id` (`forum_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_student` (`exam_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_device` (`user_id`,`device_token`);

--
-- Indexes for table `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_id` (`forum_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attempt_question` (`attempt_id`,`question_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `selected_option_id` (`selected_option_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `college_id` (`college_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `study_plans`
--
ALTER TABLE `study_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `study_plan_courses`
--
ALTER TABLE `study_plan_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `study_plan_course` (`study_plan_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `system_admins`
--
ALTER TABLE `system_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `college_id` (`college_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_role` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_programs`
--
ALTER TABLE `academic_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `academic_terms`
--
ALTER TABLE `academic_terms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement_attachments`
--
ALTER TABLE `announcement_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_details`
--
ALTER TABLE `attendance_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `college_admins`
--
ALTER TABLE `college_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `course_contents`
--
ALTER TABLE `course_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_sections`
--
ALTER TABLE `course_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_units`
--
ALTER TABLE `course_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_waitlists`
--
ALTER TABLE `course_waitlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `discussion_forums`
--
ALTER TABLE `discussion_forums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discussion_topics`
--
ALTER TABLE `discussion_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forums`
--
ALTER TABLE `forums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `study_plans`
--
ALTER TABLE `study_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `study_plan_courses`
--
ALTER TABLE `study_plan_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `system_admins`
--
ALTER TABLE `system_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `academic_programs`
--
ALTER TABLE `academic_programs`
  ADD CONSTRAINT `academic_programs_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `academic_programs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- القيود للجدول `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- القيود للجدول `announcement_attachments`
--
ALTER TABLE `announcement_attachments`
  ADD CONSTRAINT `announcement_attachments_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- القيود للجدول `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `attendance_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `attendance_details`
--
ALTER TABLE `attendance_details`
  ADD CONSTRAINT `attendance_details_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_details_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- القيود للجدول `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- القيود للجدول `colleges`
--
ALTER TABLE `colleges`
  ADD CONSTRAINT `colleges_dean_id_foreign` FOREIGN KEY (`dean_id`) REFERENCES `teachers` (`id`);

--
-- القيود للجدول `college_admins`
--
ALTER TABLE `college_admins`
  ADD CONSTRAINT `college_admins_college_id_foreign` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`),
  ADD CONSTRAINT `college_admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- القيود للجدول `course_contents`
--
ALTER TABLE `course_contents`
  ADD CONSTRAINT `course_contents_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  ADD CONSTRAINT `course_prerequisites_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_prerequisites_prerequisite_course_id_foreign` FOREIGN KEY (`prerequisite_course_id`) REFERENCES `courses` (`id`);

--
-- القيود للجدول `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD CONSTRAINT `course_registrations_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`),
  ADD CONSTRAINT `course_registrations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- القيود للجدول `course_sections`
--
ALTER TABLE `course_sections`
  ADD CONSTRAINT `course_sections_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `course_sections_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `course_sections_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `academic_terms` (`id`);

--
-- القيود للجدول `course_units`
--
ALTER TABLE `course_units`
  ADD CONSTRAINT `course_units_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `course_waitlists`
--
ALTER TABLE `course_waitlists`
  ADD CONSTRAINT `course_waitlists_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`),
  ADD CONSTRAINT `course_waitlists_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- القيود للجدول `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_college_id_foreign` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`),
  ADD CONSTRAINT `departments_head_id_foreign` FOREIGN KEY (`head_id`) REFERENCES `teachers` (`id`);

--
-- القيود للجدول `discussion_forums`
--
ALTER TABLE `discussion_forums`
  ADD CONSTRAINT `discussion_forums_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD CONSTRAINT `discussion_replies_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_replies_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `discussion_topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- القيود للجدول `discussion_topics`
--
ALTER TABLE `discussion_topics`
  ADD CONSTRAINT `discussion_topics_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `discussion_forums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_topics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- القيود للجدول `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  ADD CONSTRAINT `fcm_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `forums`
--
ALTER TABLE `forums`
  ADD CONSTRAINT `forums_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_topics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- القيود للجدول `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `message_attachments_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`),
  ADD CONSTRAINT `quiz_answers_selected_option_id_foreign` FOREIGN KEY (`selected_option_id`) REFERENCES `quiz_question_options` (`id`);

--
-- القيود للجدول `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- القيود للجدول `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD CONSTRAINT `quiz_question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_college_id_foreign` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`),
  ADD CONSTRAINT `students_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `students_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `academic_programs` (`id`),
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `study_plans`
--
ALTER TABLE `study_plans`
  ADD CONSTRAINT `study_plans_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `academic_programs` (`id`);

--
-- القيود للجدول `study_plan_courses`
--
ALTER TABLE `study_plan_courses`
  ADD CONSTRAINT `study_plan_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `study_plan_courses_study_plan_id_foreign` FOREIGN KEY (`study_plan_id`) REFERENCES `study_plans` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `system_admins`
--
ALTER TABLE `system_admins`
  ADD CONSTRAINT `system_admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- القيود للجدول `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
