<?php
/**
 * صفحة إدارة المقررات الدراسية في نظام UniverBoard
 * تتيح لمسؤول الكلية إدارة المقررات الدراسية
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول مسؤول الكلية
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'college_admin') {
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    header('Location: ../login.php');
    exit;
}

// الحصول على معلومات مسؤول الكلية
$admin_id = $_SESSION['user_id'];
$db = get_db_connection();
$admin = get_college_admin_info($db, $admin_id);
$college_id = $admin['college_id'];
$college = get_college_info($db, $college_id);

// تعيين اللغة الافتراضية
$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : SITE_LANG;

// تعيين المظهر الافتراضي
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : SITE_THEME;

// تحميل ملفات اللغة
$translations = [];
if ($lang === 'ar') {
    include '../includes/lang/ar.php';
} else {
    include '../includes/lang/en.php';
}

// دالة ترجمة النصوص
function t($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}

// تحديد البرنامج المحدد (إذا تم تمرير معرف البرنامج في الرابط)
$selected_program_id = isset($_GET['program_id']) ? filter_input(INPUT_GET, 'program_id', FILTER_SANITIZE_NUMBER_INT) : null;

// تحديد القسم المحدد (إذا تم تمرير معرف القسم في الرابط)
$selected_department_id = isset($_GET['department_id']) ? filter_input(INPUT_GET, 'department_id', FILTER_SANITIZE_NUMBER_INT) : null;

// معالجة إضافة مقرر جديد
if (isset($_POST['add_course'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_SANITIZE_NUMBER_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $credit_hours = filter_input(INPUT_POST, 'credit_hours', FILTER_SANITIZE_NUMBER_INT);
    $level = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_NUMBER_INT);
    $semester = filter_input(INPUT_POST, 'semester', FILTER_SANITIZE_STRING);
    $teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_SANITIZE_NUMBER_INT);
    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];
    
    if (!empty($name) && !empty($code) && !empty($program_id)) {
        $result = add_course($db, $college_id, $program_id, $name, $code, $description, $credit_hours, $level, $semester, $teacher_id, $prerequisites);
        
        if ($result) {
            $success_message = t('course_added_successfully');
        } else {
            $error_message = t('failed_to_add_course');
        }
    } else {
        $error_message = t('name_code_and_program_required');
    }
}

// معالجة تحديث مقرر
if (isset($_POST['update_course'])) {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_SANITIZE_NUMBER_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $credit_hours = filter_input(INPUT_POST, 'credit_hours', FILTER_SANITIZE_NUMBER_INT);
    $level = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_NUMBER_INT);
    $semester = filter_input(INPUT_POST, 'semester', FILTER_SANITIZE_STRING);
    $teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];
    
    if (!empty($course_id) && !empty($name) && !empty($code) && !empty($program_id)) {
        $result = update_course($db, $course_id, $program_id, $name, $code, $description, $credit_hours, $level, $semester, $teacher_id, $status, $prerequisites);
        
        if ($result) {
            $success_message = t('course_updated_successfully');
        } else {
            $error_message = t('failed_to_update_course');
        }
    } else {
        $error_message = t('name_code_and_program_required');
    }
}

// معالجة حذف مقرر
if (isset($_POST['delete_course'])) {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!empty($course_id)) {
        $result = delete_course($db, $course_id);
        
        if ($result) {
            $success_message = t('course_deleted_successfully');
        } else {
            $error_message = t('failed_to_delete_course');
        }
    } else {
        $error_message = t('course_id_required');
    }
}

// الحصول على قائمة الأقسام في الكلية
$departments = get_college_departments($db, $college_id);

// الحصول على قائمة البرامج في الكلية
if ($selected_department_id) {
    $programs = get_department_programs($db, $selected_department_id);
} else {
    $programs = get_college_programs($db, $college_id);
}

// الحصول على قائمة المقررات في الكلية
if ($selected_program_id) {
    $courses = get_program_courses($db, $selected_program_id);
} elseif ($selected_department_id) {
    $courses = get_department_courses($db, $selected_department_id);
} else {
    $courses = get_college_courses($db, $college_id);
}

// الحصول على قائمة المعلمين في الكلية
$teachers = get_college_teachers($db, $college_id);

// إغلاق اتصال قاعدة البيانات
$dsn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('courses'); ?></title>
    
    <!-- Bootstrap RTL إذا كانت اللغة العربية -->
    <?php if ($lang === 'ar'): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- خط Cairo -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    
    <!-- ملف CSS الرئيسي -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- ملف CSS للمظهر -->
    <link rel="stylesheet" href="assets/css/theme-<?php echo $theme; ?>.css">
    
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            width: 250px;
        }
        
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
        }
        
        .sidebar-sticky {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .sidebar .nav-link i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .sidebar .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(0, 48, 73, 0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .sidebar .nav-link.active {
            border-left: none;
            border-right: 4px solid var(--primary-color);
        }
        
        .sidebar-heading {
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 1rem 1.5rem 0.5rem;
            color: var(--gray-color);
        }
        
        .sidebar-logo {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-logo img {
            height: 40px;
        }
        
        .sidebar-logo span {
            font-size: 1.2rem;
            font-weight: 700;
            margin-left: 0.5rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .sidebar-logo span {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        
        .content {
            margin-left: 250px;
            padding: 2rem;
            transition: all 0.3s;
        }
        
        [dir="rtl"] .content {
            margin-left: 0;
            margin-right: 250px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                margin-left: 0;
                margin-right: 0;
            }
            
            [dir="rtl"] .content {
                margin-left: 0;
                margin-right: 0;
            }
            
            .sidebar-sticky {
                height: auto;
            }
        }
        
        .navbar-top {
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        
        .theme-dark .navbar-top {
            background-color: var(--dark-bg);
        }
        
        .navbar-top .navbar-nav {
            display: flex;
            align-items: center;
        }
        
        .navbar-top .nav-item {
            margin-left: 1rem;
        }
        
        [dir="rtl"] .navbar-top .nav-item {
            margin-left: 0;
            margin-right: 1rem;
        }
        
        .navbar-top .nav-link {
            color: var(--text-color);
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            position: relative;
        }
        
        .navbar-top .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .navbar-top .nav-link .badge {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 0.6rem;
        }
        
        [dir="rtl"] .navbar-top .nav-link .badge {
            right: auto;
            left: 0;
        }
        
        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-color);
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .toggle-sidebar {
                display: block;
            }
        }
        
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-dropdown .dropdown-toggle img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-dropdown .dropdown-menu {
            min-width: 200px;
            padding: 0;
        }
        
        .user-dropdown .dropdown-header {
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
        }
        
        .user-dropdown .dropdown-item {
            padding: 0.75rem 1rem;
        }
        
        .user-dropdown .dropdown-item i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .user-dropdown .dropdown-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: var(--gray-color);
        }
        
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border: none;
        }
        
        .theme-dark .card {
            background-color: var(--dark-bg);
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .theme-dark .card-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #002135;
            border-color: #002135;
        }
        
        .btn-secondary {
            background-color: #669bbc;
            border-color: #669bbc;
        }
        
        .btn-secondary:hover {
            background-color: #5589a7;
            border-color: #5589a7;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            border-top: none;
        }
        
        .table td, .table th {
            padding: 0.75rem;
            vertical-align: middle;
        }
        
        .table-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .table-avatar {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .badge-department {
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-department {
            background-color: rgba(0, 48, 73, 0.3);
        }
        
        .badge-program {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-program {
            background-color: rgba(102, 155, 188, 0.3);
        }
        
        .badge-course {
            background-color: rgba(255, 159, 28, 0.1);
            color: #ff9f1c;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-course {
            background-color: rgba(255, 159, 28, 0.3);
        }
        
        .badge-status-active {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-status-active {
            background-color: rgba(40, 167, 69, 0.3);
        }
        
        .badge-status-inactive {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-status-inactive {
            background-color: rgba(220, 53, 69, 0.3);
        }
        
        .badge-semester {
            background-color: rgba(111, 66, 193, 0.1);
            color: #6f42c1;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-semester {
            background-color: rgba(111, 66, 193, 0.3);
        }
        
        .badge-level {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-level {
            background-color: rgba(23, 162, 184, 0.3);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-button {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
        }
        
        .action-button-view {
            background-color: #17a2b8;
        }
        
        .action-button-edit {
            background-color: #ffc107;
        }
        
        .action-button-delete {
            background-color: #dc3545;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .form-control {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 48, 73, 0.25);
            border-color: var(--primary-color);
        }
        
        .form-text {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .modal-content {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .modal-content {
            background-color: var(--dark-bg);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
        }
        
        .theme-dark .modal-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
        }
        
        .theme-dark .modal-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .course-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .course-stat {
            flex: 1;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
            padding: 1.25rem;
            text-align: center;
        }
        
        .theme-dark .course-stat {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .course-stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }
        
        .course-stat-label {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .course-info {
            margin-bottom: 1.5rem;
        }
        
        .course-info-item {
            display: flex;
            margin-bottom: 0.75rem;
        }
        
        .course-info-label {
            font-weight: 500;
            width: 150px;
            flex-shrink: 0;
        }
        
        .course-info-value {
            color: var(--gray-color);
        }
        
        .course-teacher {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }
        
        .theme-dark .course-teacher {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .course-teacher-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .course-teacher-avatar {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .course-teacher-info {
            flex-grow: 1;
        }
        
        .course-teacher-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .course-teacher-title {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .course-teacher-contact {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .course-teacher-contact a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        
        .course-teacher-contact a i {
            margin-right: 0.25rem;
        }
        
        [dir="rtl"] .course-teacher-contact a i {
            margin-right: 0;
            margin-left: 0.25rem;
        }
        
        .filter-container {
            margin-bottom: 1.5rem;
        }
        
        .prerequisites-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .prerequisites-list li {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.25rem;
        }
        
        .theme-dark .prerequisites-list li {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .prerequisites-list li:last-child {
            margin-bottom: 0;
        }
        
        .prerequisites-list .badge-course {
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .prerequisites-list .badge-course {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .select2-container--bootstrap-5 .select2-selection {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .theme-dark .select2-dropdown {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-results__option {
            color: white;
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: rgba(0, 48, 73, 0.5);
        }
    </style>
</head>
<body class="theme-<?php echo $theme; ?>">
    <!-- القائمة الجانبية -->
    <nav class="sidebar bg-white">
        <div class="sidebar-sticky">
            <div class="sidebar-logo">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link" href="college_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_departments.php">
                        <i class="fas fa-building"></i> <?php echo t('departments'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_programs.php">
                        <i class="fas fa-graduation-cap"></i> <?php echo t('academic_programs'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="college_courses.php">
                        <i class="fas fa-book"></i> <?php echo t('courses'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_teachers.php">
                        <i class="fas fa-chalkboard-teacher"></i> <?php echo t('teachers'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_students.php">
                        <i class="fas fa-user-graduate"></i> <?php echo t('students'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_schedule.php">
                        <i class="fas fa-calendar-alt"></i> <?php echo t('schedule'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('reports'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="college_reports_academic.php">
                        <i class="fas fa-chart-line"></i> <?php echo t('academic_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_reports_attendance.php">
                        <i class="fas fa-clipboard-check"></i> <?php echo t('attendance_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_reports_performance.php">
                        <i class="fas fa-chart-bar"></i> <?php echo t('performance_reports'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('communication'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="college_announcements.php">
                        <i class="fas fa-bullhorn"></i> <?php echo t('announcements'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_messages.php">
                        <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('settings'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="college_profile.php">
                        <i class="fas fa-university"></i> <?php echo t('college_profile'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_settings.php">
                        <i class="fas fa-cog"></i> <?php echo t('settings'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- المحتوى الرئيسي -->
    <div class="content">
        <!-- شريط التنقل العلوي -->
        <nav class="navbar-top">
            <button class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger">5</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                        <div class="dropdown-header"><?php echo t('notifications'); ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">تم تسجيل 15 طالب جديد في قسم علوم الحاسب</p>
                                    <small class="text-muted">منذ 30 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-sm bg-warning text-white rounded-circle">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">تم تحديث جدول الامتحانات النهائية</p>
                                    <small class="text-muted">منذ ساعة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-sm bg-info text-white rounded-circle">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">تم تعيين د. محمد أحمد كرئيس لقسم الهندسة المدنية</p>
                                    <small class="text-muted">منذ 3 ساعات</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="college_notifications.php"><?php echo t('view_all_notifications'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesDropdown">
                        <div class="dropdown-header"><?php echo t('messages'); ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/dean.jpg" alt="Dean" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">د. عبدالله العمري</p>
                                    <small class="text-muted">نرجو مراجعة الميزانية المقترحة للعام القادم</small>
                                    <small class="text-muted d-block">منذ 20 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/department_head.jpg" alt="Department Head" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">د. سارة الأحمد</p>
                                    <small class="text-muted">هل يمكننا مناقشة توزيع المقررات للفصل القادم؟</small>
                                    <small class="text-muted d-block">منذ ساعتين</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="college_messages.php"><?php echo t('view_all_messages'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $admin['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $admin['name']; ?>">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?php echo $admin['name']; ?></h6>
                            <small><?php echo t('college_admin'); ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="college_profile.php">
                            <i class="fas fa-university"></i> <?php echo t('college_profile'); ?>
                        </a>
                        <a class="dropdown-item" href="college_settings.php">
                            <i class="fas fa-cog"></i> <?php echo t('settings'); ?>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                        </a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <div class="dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                            <li><a class="dropdown-item" href="?lang=en">English</a></li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <button class="btn btn-link nav-link" id="themeToggle">
                        <i class="fas <?php echo $theme === 'light' ? 'fa-moon' : 'fa-sun'; ?>"></i>
                    </button>
                </li>
            </ul>
        </nav>
        
        <!-- عنوان الصفحة -->
        <div class="page-header mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title"><?php echo t('courses'); ?></h1>
                    <p class="page-subtitle"><?php echo t('manage_academic_courses'); ?></p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                    <i class="fas fa-plus me-1"></i> <?php echo t('add_course'); ?>
                </button>
            </div>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- فلتر البرامج والأقسام -->
        <div class="filter-container">
            <div class="card">
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <div class="col-md-5">
                            <label for="department_filter" class="form-label"><?php echo t('filter_by_department'); ?></label>
                            <select class="form-select" id="department_filter" name="department_id" onchange="updateProgramsFilter()">
                                <option value=""><?php echo t('all_departments'); ?></option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo $department['id']; ?>" <?php echo $selected_department_id == $department['id'] ? 'selected' : ''; ?>>
                                        <?php echo $department['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="program_filter" class="form-label"><?php echo t('filter_by_program'); ?></label>
                            <select class="form-select" id="program_filter" name="program_id">
                                <option value=""><?php echo t('all_programs'); ?></option>
                                <?php foreach ($programs as $program): ?>
                                    <option value="<?php echo $program['id']; ?>" <?php echo $selected_program_id == $program['id'] ? 'selected' : ''; ?>>
                                        <?php echo $program['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> <?php echo t('filter'); ?>
                            </button>
                            <a href="college_courses.php" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> <?php echo t('reset'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- جدول المقررات -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo t('courses_list'); ?></h3>
                <div class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="courseSearch" class="form-control" placeholder="<?php echo t('search_courses'); ?>">
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="refreshTable">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="coursesTable">
                        <thead>
                            <tr>
                                <th><?php echo t('name'); ?></th>
                                <th><?php echo t('code'); ?></th>
                                <th><?php echo t('program'); ?></th>
                                <th><?php echo t('credit_hours'); ?></th>
                                <th><?php echo t('level'); ?></th>
                                <th><?php echo t('semester'); ?></th>
                                <th><?php echo t('teacher'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>
                                        <span class="badge-course"><?php echo $course['name']; ?></span>
                                    </td>
                                    <td><?php echo $course['code']; ?></td>
                                    <td>
                                        <span class="badge-program"><?php echo $course['program_name']; ?></span>
                                    </td>
                                    <td><?php echo $course['credit_hours']; ?></td>
                                    <td>
                                        <span class="badge-level"><?php echo t('level') . ' ' . $course['level']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-semester"><?php echo $course['semester']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($course['teacher_name']): ?>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $course['teacher_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $course['teacher_name']; ?>" class="table-avatar">
                                                <span><?php echo $course['teacher_name']; ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo t('not_assigned'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($course['status'] == 1): ?>
                                            <span class="badge-status-active"><?php echo t('active'); ?></span>
                                        <?php else: ?>
                                            <span class="badge-status-inactive"><?php echo t('inactive'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="#" class="action-button action-button-view" data-bs-toggle="modal" data-bs-target="#viewCourseModal" data-course-id="<?php echo $course['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="action-button action-button-edit" data-bs-toggle="modal" data-bs-target="#editCourseModal" data-course-id="<?php echo $course['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="action-button action-button-delete" data-bs-toggle="modal" data-bs-target="#deleteCourseModal" data-course-id="<?php echo $course['id']; ?>" data-course-name="<?php echo $course['name']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال إضافة مقرر جديد -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel"><?php echo t('add_new_course'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label"><?php echo t('course_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code" class="form-label"><?php echo t('course_code'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" required>
                                    <div class="form-text"><?php echo t('course_code_help'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id_add" class="form-label"><?php echo t('department'); ?></label>
                                    <select class="form-select" id="department_id_add" onchange="updateProgramsDropdown('add')">
                                        <option value=""><?php echo t('select_department'); ?></option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?php echo $department['id']; ?>" <?php echo $selected_department_id == $department['id'] ? 'selected' : ''; ?>>
                                                <?php echo $department['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="program_id" class="form-label"><?php echo t('program'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="program_id" name="program_id" required>
                                        <option value=""><?php echo t('select_program'); ?></option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?php echo $program['id']; ?>" <?php echo $selected_program_id == $program['id'] ? 'selected' : ''; ?>>
                                                <?php echo $program['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="credit_hours" class="form-label"><?php echo t('credit_hours'); ?></label>
                                    <input type="number" class="form-control" id="credit_hours" name="credit_hours" min="1" max="6" value="3">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="level" class="form-label"><?php echo t('level'); ?></label>
                                    <select class="form-select" id="level" name="level">
                                        <option value="1"><?php echo t('level') . ' 1'; ?></option>
                                        <option value="2"><?php echo t('level') . ' 2'; ?></option>
                                        <option value="3"><?php echo t('level') . ' 3'; ?></option>
                                        <option value="4"><?php echo t('level') . ' 4'; ?></option>
                                        <option value="5"><?php echo t('level') . ' 5'; ?></option>
                                        <option value="6"><?php echo t('level') . ' 6'; ?></option>
                                        <option value="7"><?php echo t('level') . ' 7'; ?></option>
                                        <option value="8"><?php echo t('level') . ' 8'; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="semester" class="form-label"><?php echo t('semester'); ?></label>
                                    <select class="form-select" id="semester" name="semester">
                                        <option value="الفصل الأول"><?php echo t('first_semester'); ?></option>
                                        <option value="الفصل الثاني"><?php echo t('second_semester'); ?></option>
                                        <option value="الفصل الصيفي"><?php echo t('summer_semester'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="teacher_id" class="form-label"><?php echo t('course_teacher'); ?></label>
                            <select class="form-select" id="teacher_id" name="teacher_id">
                                <option value=""><?php echo t('select_teacher'); ?></option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="prerequisites" class="form-label"><?php echo t('prerequisites'); ?></label>
                            <select class="form-select" id="prerequisites" name="prerequisites[]" multiple>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name'] . ' (' . $course['code'] . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?php echo t('prerequisites_help'); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" name="add_course" class="btn btn-primary"><?php echo t('add_course'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض تفاصيل المقرر -->
    <div class="modal fade" id="viewCourseModal" tabindex="-1" aria-labelledby="viewCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCourseModalLabel"><?php echo t('course_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="course-stats">
                        <div class="course-stat">
                            <div class="course-stat-value" id="viewStudentsCount">0</div>
                            <div class="course-stat-label"><?php echo t('students'); ?></div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value" id="viewCreditHours">0</div>
                            <div class="course-stat-label"><?php echo t('credit_hours'); ?></div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value" id="viewLevel">0</div>
                            <div class="course-stat-label"><?php echo t('level'); ?></div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value" id="viewPrerequisitesCount">0</div>
                            <div class="course-stat-label"><?php echo t('prerequisites'); ?></div>
                        </div>
                    </div>
                    
                    <div class="course-teacher" id="courseTeacherContainer">
                        <img src="assets/images/default-user.png" alt="Course Teacher" class="course-teacher-avatar" id="viewTeacherImage">
                        <div class="course-teacher-info">
                            <div class="course-teacher-name" id="viewTeacherName">-</div>
                            <div class="course-teacher-title" id="viewTeacherTitle">-</div>
                            <div class="course-teacher-contact">
                                <a href="#" id="viewTeacherEmail"><i class="fas fa-envelope"></i> <span>-</span></a>
                                <a href="#" id="viewTeacherPhone"><i class="fas fa-phone"></i> <span>-</span></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-info">
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('course_name'); ?>:</div>
                            <div class="course-info-value" id="viewName">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('course_code'); ?>:</div>
                            <div class="course-info-value" id="viewCode">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('program'); ?>:</div>
                            <div class="course-info-value" id="viewProgram">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('department'); ?>:</div>
                            <div class="course-info-value" id="viewDepartment">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('semester'); ?>:</div>
                            <div class="course-info-value" id="viewSemester">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('status'); ?>:</div>
                            <div class="course-info-value" id="viewStatus">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('description'); ?>:</div>
                            <div class="course-info-value" id="viewDescription">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('prerequisites'); ?>:</div>
                            <div class="course-info-value">
                                <ul class="prerequisites-list" id="viewPrerequisites"></ul>
                            </div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('created_at'); ?>:</div>
                            <div class="course-info-value" id="viewCreatedAt">-</div>
                        </div>
                        <div class="course-info-item">
                            <div class="course-info-label"><?php echo t('last_updated'); ?>:</div>
                            <div class="course-info-value" id="viewUpdatedAt">-</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <a href="#" class="btn btn-primary" id="viewStudentsBtn"><?php echo t('view_enrolled_students'); ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل المقرر -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCourseModalLabel"><?php echo t('edit_course'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editCourseId" name="course_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editName" class="form-label"><?php echo t('course_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editCode" class="form-label"><?php echo t('course_code'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editCode" name="code" required>
                                    <div class="form-text"><?php echo t('course_code_help'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id_edit" class="form-label"><?php echo t('department'); ?></label>
                                    <select class="form-select" id="department_id_edit" onchange="updateProgramsDropdown('edit')">
                                        <option value=""><?php echo t('select_department'); ?></option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?php echo $department['id']; ?>">
                                                <?php echo $department['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editProgramId" class="form-label"><?php echo t('program'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editProgramId" name="program_id" required>
                                        <option value=""><?php echo t('select_program'); ?></option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?php echo $program['id']; ?>">
                                                <?php echo $program['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editDescription" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="editCreditHours" class="form-label"><?php echo t('credit_hours'); ?></label>
                                    <input type="number" class="form-control" id="editCreditHours" name="credit_hours" min="1" max="6">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="editLevel" class="form-label"><?php echo t('level'); ?></label>
                                    <select class="form-select" id="editLevel" name="level">
                                        <option value="1"><?php echo t('level') . ' 1'; ?></option>
                                        <option value="2"><?php echo t('level') . ' 2'; ?></option>
                                        <option value="3"><?php echo t('level') . ' 3'; ?></option>
                                        <option value="4"><?php echo t('level') . ' 4'; ?></option>
                                        <option value="5"><?php echo t('level') . ' 5'; ?></option>
                                        <option value="6"><?php echo t('level') . ' 6'; ?></option>
                                        <option value="7"><?php echo t('level') . ' 7'; ?></option>
                                        <option value="8"><?php echo t('level') . ' 8'; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="editSemester" class="form-label"><?php echo t('semester'); ?></label>
                                    <select class="form-select" id="editSemester" name="semester">
                                        <option value="الفصل الأول"><?php echo t('first_semester'); ?></option>
                                        <option value="الفصل الثاني"><?php echo t('second_semester'); ?></option>
                                        <option value="الفصل الصيفي"><?php echo t('summer_semester'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="editStatus" class="form-label"><?php echo t('status'); ?></label>
                                    <select class="form-select" id="editStatus" name="status">
                                        <option value="1"><?php echo t('active'); ?></option>
                                        <option value="0"><?php echo t('inactive'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editTeacherId" class="form-label"><?php echo t('course_teacher'); ?></label>
                            <select class="form-select" id="editTeacherId" name="teacher_id">
                                <option value=""><?php echo t('select_teacher'); ?></option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="editPrerequisites" class="form-label"><?php echo t('prerequisites'); ?></label>
                            <select class="form-select" id="editPrerequisites" name="prerequisites[]" multiple>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name'] . ' (' . $course['code'] . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?php echo t('prerequisites_help'); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" name="update_course" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف المقرر -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCourseModalLabel"><?php echo t('delete_course'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo t('delete_course_confirmation'); ?> <strong id="deleteCourseName"></strong>؟</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_course_warning'); ?>
                    </div>
                </div>
                <form action="" method="post">
                    <input type="hidden" id="deleteCourseId" name="course_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" name="delete_course" class="btn btn-danger"><?php echo t('delete'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- ملف JavaScript الرئيسي -->
    <script src="assets/js/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تبديل القائمة الجانبية في الشاشات الصغيرة
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('d-none');
            });
            
            // زر تبديل المظهر
            document.getElementById('themeToggle').addEventListener('click', function() {
                const currentTheme = document.body.className.includes('theme-dark') ? 'dark' : 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.cookie = `theme=${newTheme}; path=/; max-age=31536000`;
                document.body.className = `theme-${newTheme}`;
                
                this.innerHTML = `<i class="fas fa-${newTheme === 'dark' ? 'sun' : 'moon'}"></i>`;
            });
            
            // تهيئة جدول البيانات
            const coursesTable = $('#coursesTable').DataTable({
                language: {
                    url: '<?php echo $lang === 'ar' ? 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json' : 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/en-GB.json'; ?>'
                },
                responsive: true,
                dom: 'lrtip',
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[0, 'asc']]
            });
            
            // البحث في الجدول
            $('#courseSearch').on('keyup', function() {
                coursesTable.search(this.value).draw();
            });
            
            // تحديث الجدول
            $('#refreshTable').on('click', function() {
                location.reload();
            });
            
            // تهيئة Select2 للقوائم المنسدلة المتعددة
            $('#prerequisites, #editPrerequisites').select2({
                theme: 'bootstrap-5',
                placeholder: '<?php echo t('select_prerequisites'); ?>',
                allowClear: true,
                width: '100%'
            });
            
            // مودال عرض تفاصيل المقرر
            $('#viewCourseModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const courseId = button.data('course-id');
                
                // هنا يمكن إضافة طلب AJAX للحصول على بيانات المقرر
                // لأغراض العرض، سنستخدم بيانات وهمية
                
                const courses = <?php echo json_encode($courses); ?>;
                const course = courses.find(c => c.id == courseId);
                
                if (course) {
                    $('#viewName').text(course.name);
                    $('#viewCode').text(course.code);
                    $('#viewProgram').text(course.program_name);
                    $('#viewDepartment').text(course.department_name);
                    $('#viewCreditHours').text(course.credit_hours);
                    $('#viewLevel').text(course.level);
                    $('#viewSemester').text(course.semester);
                    $('#viewDescription').text(course.description || '-');
                    $('#viewStatus').html(course.status == 1 ? 
                        '<span class="badge-status-active"><?php echo t('active'); ?></span>' : 
                        '<span class="badge-status-inactive"><?php echo t('inactive'); ?></span>');
                    $('#viewCreatedAt').text(course.created_at || '-');
                    $('#viewUpdatedAt').text(course.updated_at || '-');
                    
                    $('#viewStudentsCount').text(course.students_count || 0);
                    $('#viewPrerequisitesCount').text(course.prerequisites ? course.prerequisites.length : 0);
                    
                    // عرض المتطلبات السابقة
                    const prerequisitesList = $('#viewPrerequisites');
                    prerequisitesList.empty();
                    
                    if (course.prerequisites && course.prerequisites.length > 0) {
                        course.prerequisites.forEach(prereq => {
                            prerequisitesList.append(`
                                <li>
                                    <span class="badge-course">${prereq.name}</span>
                                    <span class="text-muted">(${prereq.code})</span>
                                </li>
                            `);
                        });
                    } else {
                        prerequisitesList.append(`<li class="text-muted"><?php echo t('no_prerequisites'); ?></li>`);
                    }
                    
                    if (course.teacher_name) {
                        $('#courseTeacherContainer').show();
                        $('#viewTeacherName').text(course.teacher_name);
                        $('#viewTeacherTitle').text(course.teacher_title || '<?php echo t('course_teacher'); ?>');
                        $('#viewTeacherImage').attr('src', course.teacher_image || 'assets/images/default-user.png');
                        $('#viewTeacherEmail span').text(course.teacher_email || '-');
                        $('#viewTeacherPhone span').text(course.teacher_phone || '-');
                    } else {
                        $('#courseTeacherContainer').hide();
                    }
                    
                    $('#viewStudentsBtn').attr('href', 'college_course_students.php?course_id=' + courseId);
                }
            });
            
            // مودال تعديل المقرر
            $('#editCourseModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const courseId = button.data('course-id');
                
                // هنا يمكن إضافة طلب AJAX للحصول على بيانات المقرر
                // لأغراض العرض، سنستخدم بيانات وهمية
                
                const courses = <?php echo json_encode($courses); ?>;
                const course = courses.find(c => c.id == courseId);
                
                if (course) {
                    $('#editCourseId').val(course.id);
                    $('#editName').val(course.name);
                    $('#editCode').val(course.code);
                    $('#department_id_edit').val(course.department_id);
                    $('#editProgramId').val(course.program_id);
                    $('#editDescription').val(course.description);
                    $('#editCreditHours').val(course.credit_hours);
                    $('#editLevel').val(course.level);
                    $('#editSemester').val(course.semester);
                    $('#editTeacherId').val(course.teacher_id || '');
                    $('#editStatus').val(course.status);
                    
                    // تحديد المتطلبات السابقة
                    if (course.prerequisites && course.prerequisites.length > 0) {
                        const prerequisiteIds = course.prerequisites.map(prereq => prereq.id);
                        $('#editPrerequisites').val(prerequisiteIds).trigger('change');
                    } else {
                        $('#editPrerequisites').val([]).trigger('change');
                    }
                }
            });
            
            // مودال حذف المقرر
            $('#deleteCourseModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const courseId = button.data('course-id');
                const courseName = button.data('course-name');
                
                $('#deleteCourseId').val(courseId);
                $('#deleteCourseName').text(courseName);
            });
        });
        
        // تحديث قائمة البرامج بناءً على القسم المحدد (للفلتر)
        function updateProgramsFilter() {
            const departmentId = document.getElementById('department_filter').value;
            const programSelect = document.getElementById('program_filter');
            
            // إعادة تعيين قائمة البرامج
            programSelect.innerHTML = `<option value=""><?php echo t('all_programs'); ?></option>`;
            
            if (departmentId) {
                // هنا يمكن إضافة طلب AJAX للحصول على البرامج حسب القسم
                // لأغراض العرض، سنستخدم بيانات وهمية
                
                const programs = <?php echo json_encode($programs); ?>;
                const filteredPrograms = programs.filter(p => p.department_id == departmentId);
                
                filteredPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            } else {
                // إذا لم يتم تحديد قسم، عرض جميع البرامج
                const programs = <?php echo json_encode($programs); ?>;
                
                programs.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            }
        }
        
        // تحديث قائمة البرامج بناءً على القسم المحدد (للنماذج)
        function updateProgramsDropdown(mode) {
            const departmentId = document.getElementById(`department_id_${mode}`).value;
            const programSelect = document.getElementById(mode === 'add' ? 'program_id' : 'editProgramId');
            
            // إعادة تعيين قائمة البرامج
            programSelect.innerHTML = `<option value=""><?php echo t('select_program'); ?></option>`;
            
            if (departmentId) {
                // هنا يمكن إضافة طلب AJAX للحصول على البرامج حسب القسم
                // لأغراض العرض، سنستخدم بيانات وهمية
                
                const programs = <?php echo json_encode($programs); ?>;
                const filteredPrograms = programs.filter(p => p.department_id == departmentId);
                
                filteredPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            } else {
                // إذا لم يتم تحديد قسم، عرض جميع البرامج
                const programs = <?php echo json_encode($programs); ?>;
                
                programs.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            }
        }
    </script>
</body>
</html>
