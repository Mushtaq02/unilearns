<?php
/**
 * صفحة إدارة الطلاب في نظام UniverBoard
 * تتيح لمسؤول الكلية إدارة الطلاب
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

// تحديد القسم المحدد (إذا تم تمرير معرف القسم في الرابط)
$selected_department_id = isset($_GET['department_id']) ? filter_input(INPUT_GET, 'department_id', FILTER_SANITIZE_NUMBER_INT) : null;

// تحديد البرنامج المحدد (إذا تم تمرير معرف البرنامج في الرابط)
$selected_program_id = isset($_GET['program_id']) ? filter_input(INPUT_GET, 'program_id', FILTER_SANITIZE_NUMBER_INT) : null;

// تحديد المستوى المحدد
$selected_level = isset($_GET['level']) ? filter_input(INPUT_GET, 'level', FILTER_SANITIZE_NUMBER_INT) : null;

// تحديد الحالة المحددة
$selected_status = isset($_GET['status']) ? filter_input(INPUT_GET, 'status', FILTER_SANITIZE_NUMBER_INT) : null;

// معالجة إضافة طالب جديد
if (isset($_POST['add_student'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $student_id_number = filter_input(INPUT_POST, 'student_id_number', FILTER_SANITIZE_STRING);
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_SANITIZE_NUMBER_INT);
    $level = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_NUMBER_INT);
    $enrollment_date = filter_input(INPUT_POST, 'enrollment_date', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = t('invalid_email_format');
    } else {
        // التحقق من عدم وجود بريد إلكتروني مكرر
        $email_exists = check_email_exists($db, $email);
        
        if ($email_exists) {
            $error_message = t('email_already_exists');
        } else {
            // التحقق من عدم وجود رقم جامعي مكرر
            $student_id_exists = check_student_id_exists($db, $student_id_number);
            
            if ($student_id_exists) {
                $error_message = t('student_id_already_exists');
            } else {
                // إضافة الطالب الجديد
                $result = add_student($db, $college_id, $program_id, $name, $email, $phone, $student_id_number, $level, $enrollment_date, $address, $date_of_birth, $gender, $password);
                
                if ($result) {
                    $success_message = t('student_added_successfully');
                    
                    // معالجة تحميل الصورة الشخصية
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $student_id = $result;
                        $upload_result = upload_profile_image($student_id, 'student', $_FILES['profile_image']);
                        
                        if (!$upload_result['success']) {
                            $warning_message = $upload_result['message'];
                        }
                    }
                } else {
                    $error_message = t('failed_to_add_student');
                }
            }
        }
    }
}

// معالجة تحديث طالب
if (isset($_POST['update_student'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $student_id_number = filter_input(INPUT_POST, 'student_id_number', FILTER_SANITIZE_STRING);
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_SANITIZE_NUMBER_INT);
    $level = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_NUMBER_INT);
    $enrollment_date = filter_input(INPUT_POST, 'enrollment_date', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = t('invalid_email_format');
    } else {
        // التحقق من عدم وجود بريد إلكتروني مكرر (باستثناء البريد الإلكتروني الحالي للطالب)
        $email_exists = check_email_exists($db, $email, $student_id);
        
        if ($email_exists) {
            $error_message = t('email_already_exists');
        } else {
            // التحقق من عدم وجود رقم جامعي مكرر (باستثناء الرقم الجامعي الحالي للطالب)
            $student_id_exists = check_student_id_exists($db, $student_id_number, $student_id);
            
            if ($student_id_exists) {
                $error_message = t('student_id_already_exists');
            } else {
                // تحديث بيانات الطالب
                $result = update_student($db, $student_id, $program_id, $name, $email, $phone, $student_id_number, $level, $enrollment_date, $address, $date_of_birth, $gender, $status, $password);
                
                if ($result) {
                    $success_message = t('student_updated_successfully');
                    
                    // معالجة تحميل الصورة الشخصية
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $upload_result = upload_profile_image($student_id, 'student', $_FILES['profile_image']);
                        
                        if (!$upload_result['success']) {
                            $warning_message = $upload_result['message'];
                        }
                    }
                } else {
                    $error_message = t('failed_to_update_student');
                }
            }
        }
    }
}

// معالجة حذف طالب
if (isset($_POST['delete_student'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
    
    // التحقق من عدم وجود سجلات مرتبطة بالطالب (مثل التسجيل في المقررات، الدرجات، إلخ)
    $related_records_exist = check_student_related_records($db, $student_id);
    
    if ($related_records_exist) {
        $error_message = t('cannot_delete_student_with_records');
    } else {
        $result = delete_student($db, $student_id);
        
        if ($result) {
            $success_message = t('student_deleted_successfully');
        } else {
            $error_message = t('failed_to_delete_student');
        }
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

// الحصول على قائمة الطلاب في الكلية بناءً على الفلاتر
$filters = [
    'college_id' => $college_id,
    'department_id' => $selected_department_id,
    'program_id' => $selected_program_id,
    'level' => $selected_level,
    'status' => $selected_status
];
$students = get_college_students($db, $filters);

// إغلاق اتصال قاعدة البيانات
$dsn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('students'); ?></title>
    
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
        
        .badge-status-graduated {
            background-color: rgba(111, 66, 193, 0.1);
            color: #6f42c1;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-status-graduated {
            background-color: rgba(111, 66, 193, 0.3);
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
        
        .student-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .student-stat {
            flex: 1;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
            padding: 1.25rem;
            text-align: center;
        }
        
        .theme-dark .student-stat {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .student-stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }
        
        .student-stat-label {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .student-info {
            margin-bottom: 1.5rem;
        }
        
        .student-info-item {
            display: flex;
            margin-bottom: 0.75rem;
        }
        
        .student-info-label {
            font-weight: 500;
            width: 150px;
            flex-shrink: 0;
        }
        
        .student-info-value {
            color: var(--gray-color);
        }
        
        .student-profile {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }
        
        .theme-dark .student-profile {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .student-profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1.5rem;
        }
        
        [dir="rtl"] .student-profile-avatar {
            margin-right: 0;
            margin-left: 1.5rem;
        }
        
        .student-profile-info {
            flex-grow: 1;
        }
        
        .student-profile-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .student-profile-id {
            font-size: 1rem;
            color: var(--gray-color);
            margin-bottom: 0.5rem;
        }
        
        .student-profile-contact {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .student-profile-contact a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        
        .student-profile-contact a i {
            margin-right: 0.25rem;
        }
        
        [dir="rtl"] .student-profile-contact a i {
            margin-right: 0;
            margin-left: 0.25rem;
        }
        
        .filter-container {
            margin-bottom: 1.5rem;
        }
        
        .student-courses {
            margin-top: 1.5rem;
        }
        
        .student-courses-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .student-courses-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .student-courses-list li {
            background-color: rgba(0, 0, 0, 0.02);
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.9rem;
        }
        
        .theme-dark .student-courses-list li {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .profile-image-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 2px solid var(--primary-color);
        }
        
        .custom-file-upload {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            cursor: pointer;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        
        .theme-dark .custom-file-upload {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .custom-file-upload i {
            margin-right: 0.25rem;
        }
        
        [dir="rtl"] .custom-file-upload i {
            margin-right: 0;
            margin-left: 0.25rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray-color);
        }
        
        [dir="rtl"] .password-toggle {
            right: auto;
            left: 10px;
        }
        
        .password-container {
            position: relative;
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
                    <a class="nav-link" href="college_courses.php">
                        <i class="fas fa-book"></i> <?php echo t('courses'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_teachers.php">
                        <i class="fas fa-chalkboard-teacher"></i> <?php echo t('teachers'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="college_students.php">
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
                    <h1 class="page-title"><?php echo t('students'); ?></h1>
                    <p class="page-subtitle"><?php echo t('manage_college_students'); ?></p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="fas fa-plus me-1"></i> <?php echo t('add_student'); ?>
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
        
        <?php if (isset($warning_message)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo $warning_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- فلتر الطلاب -->
        <div class="filter-container">
            <div class="card">
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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
                        <div class="col-md-2">
                            <label for="level_filter" class="form-label"><?php echo t('filter_by_level'); ?></label>
                            <select class="form-select" id="level_filter" name="level">
                                <option value=""><?php echo t('all_levels'); ?></option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $selected_level == $i ? 'selected' : ''; ?>><?php echo t('level') . ' ' . $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status_filter" class="form-label"><?php echo t('filter_by_status'); ?></label>
                            <select class="form-select" id="status_filter" name="status">
                                <option value=""><?php echo t('all_statuses'); ?></option>
                                <option value="1" <?php echo $selected_status === 1 ? 'selected' : ''; ?>><?php echo t('active'); ?></option>
                                <option value="0" <?php echo $selected_status === 0 ? 'selected' : ''; ?>><?php echo t('inactive'); ?></option>
                                <option value="2" <?php echo $selected_status === 2 ? 'selected' : ''; ?>><?php echo t('graduated'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> <?php echo t('filter'); ?>
                            </button>
                            <a href="college_students.php" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> <?php echo t('reset'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- جدول الطلاب -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo t('students_list'); ?></h3>
                <div class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="studentSearch" class="form-control" placeholder="<?php echo t('search_students'); ?>">
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="refreshTable">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="studentsTable">
                        <thead>
                            <tr>
                                <th><?php echo t('name'); ?></th>
                                <th><?php echo t('student_id_number'); ?></th>
                                <th><?php echo t('program'); ?></th>
                                <th><?php echo t('level'); ?></th>
                                <th><?php echo t('email'); ?></th>
                                <th><?php echo t('phone'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $student['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $student['name']; ?>" class="table-avatar">
                                            <span><?php echo $student['name']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $student['student_id_number']; ?></td>
                                    <td>
                                        <span class="badge-program"><?php echo $student['program_name']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-level"><?php echo t('level') . ' ' . $student['level']; ?></span>
                                    </td>
                                    <td><?php echo $student['email']; ?></td>
                                    <td><?php echo $student['phone'] ?: '-'; ?></td>
                                    <td>
                                        <?php 
                                        if ($student['status'] == 1) {
                                            echo '<span class="badge-status-active">' . t('active') . '</span>';
                                        } elseif ($student['status'] == 0) {
                                            echo '<span class="badge-status-inactive">' . t('inactive') . '</span>';
                                        } elseif ($student['status'] == 2) {
                                            echo '<span class="badge-status-graduated">' . t('graduated') . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="#" class="action-button action-button-view" data-bs-toggle="modal" data-bs-target="#viewStudentModal" data-student-id="<?php echo $student['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="action-button action-button-edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" data-student-id="<?php echo $student['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="action-button action-button-delete" data-bs-toggle="modal" data-bs-target="#deleteStudentModal" data-student-id="<?php echo $student['id']; ?>" data-student-name="<?php echo $student['name']; ?>">
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
    
    <!-- مودال إضافة طالب جديد -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel"><?php echo t('add_new_student'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="assets/images/default-user.png" alt="Profile Image" class="profile-image-preview" id="addProfileImagePreview">
                                <div class="mb-3">
                                    <label for="add_profile_image" class="custom-file-upload">
                                        <i class="fas fa-upload"></i> <?php echo t('upload_image'); ?>
                                    </label>
                                    <input type="file" id="add_profile_image" name="profile_image" class="d-none" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="addName" class="form-label"><?php echo t('full_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addName" name="name" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="addEmail" class="form-label"><?php echo t('email'); ?> <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="addEmail" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="addPhone" class="form-label"><?php echo t('phone'); ?></label>
                                            <input type="text" class="form-control" id="addPhone" name="phone">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="addStudentIdNumber" class="form-label"><?php echo t('student_id_number'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addStudentIdNumber" name="student_id_number" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addDepartmentId" class="form-label"><?php echo t('department'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addDepartmentId" onchange="updateProgramsDropdown('add')" required>
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
                                    <label for="addProgramId" class="form-label"><?php echo t('program'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addProgramId" name="program_id" required>
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addLevel" class="form-label"><?php echo t('level'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addLevel" name="level" required>
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo t('level') . ' ' . $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addEnrollmentDate" class="form-label"><?php echo t('enrollment_date'); ?> <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="addEnrollmentDate" name="enrollment_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="addAddress" class="form-label"><?php echo t('address'); ?></label>
                            <input type="text" class="form-control" id="addAddress" name="address">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addDateOfBirth" class="form-label"><?php echo t('date_of_birth'); ?></label>
                                    <input type="date" class="form-control" id="addDateOfBirth" name="date_of_birth">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addGender" class="form-label"><?php echo t('gender'); ?></label>
                                    <select class="form-select" id="addGender" name="gender">
                                        <option value="ذكر"><?php echo t('male'); ?></option>
                                        <option value="أنثى"><?php echo t('female'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="addPassword" class="form-label"><?php echo t('password'); ?> <span class="text-danger">*</span></label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="addPassword" name="password" required>
                                <span class="password-toggle" onclick="togglePasswordVisibility('addPassword')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="form-text"><?php echo t('password_requirements'); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" name="add_student" class="btn btn-primary"><?php echo t('add_student'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض تفاصيل الطالب -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStudentModalLabel"><?php echo t('student_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="student-stats">
                        <div class="student-stat">
                            <div class="student-stat-value" id="viewGpa">0.00</div>
                            <div class="student-stat-label"><?php echo t('gpa'); ?></div>
                        </div>
                        <div class="student-stat">
                            <div class="student-stat-value" id="viewCompletedHours">0</div>
                            <div class="student-stat-label"><?php echo t('completed_hours'); ?></div>
                        </div>
                        <div class="student-stat">
                            <div class="student-stat-value" id="viewCurrentCourses">0</div>
                            <div class="student-stat-label"><?php echo t('current_courses'); ?></div>
                        </div>
                        <div class="student-stat">
                            <div class="student-stat-value" id="viewAttendanceRate">0%</div>
                            <div class="student-stat-label"><?php echo t('attendance_rate'); ?></div>
                        </div>
                    </div>
                    
                    <div class="student-profile">
                        <img src="assets/images/default-user.png" alt="Student Profile" class="student-profile-avatar" id="viewProfileImage">
                        <div class="student-profile-info">
                            <div class="student-profile-name" id="viewName">-</div>
                            <div class="student-profile-id" id="viewStudentIdNumber">-</div>
                            <div class="student-profile-contact">
                                <a href="#" id="viewEmailLink"><i class="fas fa-envelope"></i> <span id="viewEmail">-</span></a>
                                <a href="#" id="viewPhoneLink"><i class="fas fa-phone"></i> <span id="viewPhone">-</span></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="student-info">
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('program'); ?>:</div>
                            <div class="student-info-value" id="viewProgram">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('department'); ?>:</div>
                            <div class="student-info-value" id="viewDepartment">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('level'); ?>:</div>
                            <div class="student-info-value" id="viewLevel">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('enrollment_date'); ?>:</div>
                            <div class="student-info-value" id="viewEnrollmentDate">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('date_of_birth'); ?>:</div>
                            <div class="student-info-value" id="viewDateOfBirth">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('gender'); ?>:</div>
                            <div class="student-info-value" id="viewGender">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('address'); ?>:</div>
                            <div class="student-info-value" id="viewAddress">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('status'); ?>:</div>
                            <div class="student-info-value" id="viewStatus">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('join_date'); ?>:</div>
                            <div class="student-info-value" id="viewJoinDate">-</div>
                        </div>
                        <div class="student-info-item">
                            <div class="student-info-label"><?php echo t('last_login'); ?>:</div>
                            <div class="student-info-value" id="viewLastLogin">-</div>
                        </div>
                    </div>
                    
                    <div class="student-courses">
                        <h4 class="student-courses-title"><?php echo t('registered_courses'); ?></h4>
                        <ul class="student-courses-list" id="viewCoursesList"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <a href="#" class="btn btn-primary" id="viewTranscriptBtn"><?php echo t('view_transcript'); ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل الطالب -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel"><?php echo t('edit_student'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editStudentId" name="student_id">
                        
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="assets/images/default-user.png" alt="Profile Image" class="profile-image-preview" id="editProfileImagePreview">
                                <div class="mb-3">
                                    <label for="edit_profile_image" class="custom-file-upload">
                                        <i class="fas fa-upload"></i> <?php echo t('change_image'); ?>
                                    </label>
                                    <input type="file" id="edit_profile_image" name="profile_image" class="d-none" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="editName" class="form-label"><?php echo t('full_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editName" name="name" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editEmail" class="form-label"><?php echo t('email'); ?> <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="editEmail" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editPhone" class="form-label"><?php echo t('phone'); ?></label>
                                            <input type="text" class="form-control" id="editPhone" name="phone">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="editStudentIdNumber" class="form-label"><?php echo t('student_id_number'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editStudentIdNumber" name="student_id_number" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editDepartmentId" class="form-label"><?php echo t('department'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editDepartmentId" onchange="updateProgramsDropdown('edit')" required>
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editLevel" class="form-label"><?php echo t('level'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editLevel" name="level" required>
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo t('level') . ' ' . $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editEnrollmentDate" class="form-label"><?php echo t('enrollment_date'); ?> <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editEnrollmentDate" name="enrollment_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editAddress" class="form-label"><?php echo t('address'); ?></label>
                            <input type="text" class="form-control" id="editAddress" name="address">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editDateOfBirth" class="form-label"><?php echo t('date_of_birth'); ?></label>
                                    <input type="date" class="form-control" id="editDateOfBirth" name="date_of_birth">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editGender" class="form-label"><?php echo t('gender'); ?></label>
                                    <select class="form-select" id="editGender" name="gender">
                                        <option value="ذكر"><?php echo t('male'); ?></option>
                                        <option value="أنثى"><?php echo t('female'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editStatus" class="form-label"><?php echo t('status'); ?></label>
                                    <select class="form-select" id="editStatus" name="status">
                                        <option value="1"><?php echo t('active'); ?></option>
                                        <option value="0"><?php echo t('inactive'); ?></option>
                                        <option value="2"><?php echo t('graduated'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editPassword" class="form-label"><?php echo t('password'); ?></label>
                            <div class="password-container">
                                <input type="password" class="form-control" id="editPassword" name="password">
                                <span class="password-toggle" onclick="togglePasswordVisibility('editPassword')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="form-text"><?php echo t('leave_empty_to_keep_current_password'); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" name="update_student" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف الطالب -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStudentModalLabel"><?php echo t('delete_student'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo t('delete_student_confirmation'); ?> <strong id="deleteStudentName"></strong>؟</p>
                    <div class="alert alert-warning" id="deleteStudentWarning">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_student_warning'); ?>
                    </div>
                </div>
                <form action="" method="post">
                    <input type="hidden" id="deleteStudentId" name="student_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" name="delete_student" class="btn btn-danger" id="deleteStudentBtn"><?php echo t('delete'); ?></button>
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
            const studentsTable = $('#studentsTable').DataTable({
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
            $('#studentSearch').on('keyup', function() {
                studentsTable.search(this.value).draw();
            });
            
            // تحديث الجدول
            $('#refreshTable').on('click', function() {
                location.reload();
            });
            
            // معاينة الصورة الشخصية عند التحميل (إضافة طالب جديد)
            document.getElementById('add_profile_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('addProfileImagePreview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // معاينة الصورة الشخصية عند التحميل (تعديل طالب)
            document.getElementById('edit_profile_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('editProfileImagePreview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // مودال عرض تفاصيل الطالب
            $('#viewStudentModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const studentId = button.data('student-id');
                
                // هنا يمكن إضافة طلب AJAX للحصول على بيانات الطالب الكاملة
                // لأغراض العرض، سنستخدم بيانات وهمية من الجدول
                
                const students = <?php echo json_encode($students); ?>;
                const student = students.find(s => s.id == studentId);
                
                if (student) {
                    $('#viewName').text(student.name);
                    $('#viewStudentIdNumber').text(student.student_id_number);
                    $('#viewEmail').text(student.email);
                    $('#viewEmailLink').attr('href', 'mailto:' + student.email);
                    $('#viewPhone').text(student.phone || '-');
                    $('#viewPhoneLink').attr('href', 'tel:' + (student.phone || '#'));
                    $('#viewProgram').text(student.program_name);
                    $('#viewDepartment').text(student.department_name);
                    $('#viewLevel').text(student.level);
                    $('#viewEnrollmentDate').text(student.enrollment_date || '-');
                    $('#viewDateOfBirth').text(student.date_of_birth || '-');
                    $('#viewGender').text(student.gender || '-');
                    $('#viewAddress').text(student.address || '-');
                    $('#viewProfileImage').attr('src', student.profile_image || 'assets/images/default-user.png');
                    
                    let statusText = '';
                    if (student.status == 1) {
                        statusText = '<span class="badge-status-active"><?php echo t('active'); ?></span>';
                    } else if (student.status == 0) {
                        statusText = '<span class="badge-status-inactive"><?php echo t('inactive'); ?></span>';
                    } else if (student.status == 2) {
                        statusText = '<span class="badge-status-graduated"><?php echo t('graduated'); ?></span>';
                    }
                    $('#viewStatus').html(statusText);
                    
                    $('#viewJoinDate').text(student.created_at || '-');
                    $('#viewLastLogin').text(student.last_login || '-');
                    
                    // بيانات وهمية للإحصائيات
                    $('#viewGpa').text(student.gpa || '3.75');
                    $('#viewCompletedHours').text(student.completed_hours || 90);
                    $('#viewCurrentCourses').text(student.current_courses_count || 5);
                    $('#viewAttendanceRate').text((student.attendance_rate || 95) + '%');
                    
                    // عرض المقررات المسجلة (بيانات وهمية)
                    const coursesList = $('#viewCoursesList');
                    coursesList.empty();
                    const sampleCourses = ['مقدمة في البرمجة (CS101)', 'هياكل البيانات (CS201)', 'قواعد البيانات (CS305)', 'شبكات الحاسب (CS340)', 'نظم التشغيل (CS350)'];
                    if (sampleCourses.length > 0) {
                        sampleCourses.forEach(course => {
                            coursesList.append(`<li>${course}</li>`);
                        });
                    } else {
                        coursesList.append(`<li class="text-muted"><?php echo t('no_registered_courses'); ?></li>`);
                    }
                    
                    $('#viewTranscriptBtn').attr('href', 'college_student_transcript.php?student_id=' + studentId);
                }
            });
            
            // مودال تعديل الطالب
            $('#editStudentModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const studentId = button.data('student-id');
                
                // هنا يمكن إضافة طلب AJAX للحصول على بيانات الطالب
                // لأغراض العرض، سنستخدم بيانات وهمية من الجدول
                
                const students = <?php echo json_encode($students); ?>;
                const student = students.find(s => s.id == studentId);
                
                if (student) {
                    $('#editStudentId').val(student.id);
                    $('#editName').val(student.name);
                    $('#editEmail').val(student.email);
                    $('#editPhone').val(student.phone || '');
                    $('#editStudentIdNumber').val(student.student_id_number);
                    $('#editDepartmentId').val(student.department_id);
                    updateProgramsDropdown('edit', student.department_id, student.program_id); // تحديث البرامج وتحديد البرنامج الحالي
                    $('#editLevel').val(student.level);
                    $('#editEnrollmentDate').val(student.enrollment_date || '');
                    $('#editAddress').val(student.address || '');
                    $('#editDateOfBirth').val(student.date_of_birth || '');
                    $('#editGender').val(student.gender || 'ذكر');
                    $('#editStatus').val(student.status);
                    $('#editPassword').val('');
                    $('#editProfileImagePreview').attr('src', student.profile_image || 'assets/images/default-user.png');
                }
            });
            
            // مودال حذف الطالب
            $('#deleteStudentModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const studentId = button.data('student-id');
                const studentName = button.data('student-name');
                
                $('#deleteStudentId').val(studentId);
                $('#deleteStudentName').text(studentName);
                
                // هنا يمكن إضافة طلب AJAX للتحقق من وجود سجلات مرتبطة بالطالب
                // لأغراض العرض، سنفترض عدم وجود سجلات مرتبطة
                const relatedRecordsExist = false; // يجب استبدالها بنتيجة التحقق الفعلي
                
                if (relatedRecordsExist) {
                    $('#deleteStudentWarning').html(`
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo t('cannot_delete_student_with_records_warning'); ?>
                    `);
                    $('#deleteStudentBtn').prop('disabled', true);
                } else {
                    $('#deleteStudentWarning').html(`
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo t('delete_student_warning'); ?>
                    `);
                    $('#deleteStudentBtn').prop('disabled', false);
                }
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
                // إذا لم يتم تحديد قسم، عرض جميع البرامج المتاحة للكلية
                const allPrograms = <?php echo json_encode(get_college_programs(get_db_connection(), $college_id)); ?>;
                allPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            }
        }
        
        // تحديث قائمة البرامج بناءً على القسم المحدد (للنماذج)
        function updateProgramsDropdown(mode, selectedDepartmentId = null, selectedProgramId = null) {
            const departmentId = selectedDepartmentId || document.getElementById(`addDepartmentId`).value;
            const programSelect = document.getElementById(mode === 'add' ? 'addProgramId' : 'editProgramId');
            
            // إعادة تعيين قائمة البرامج
            programSelect.innerHTML = `<option value=""><?php echo t('select_program'); ?></option>`;
            
            if (departmentId) {
                // هنا يمكن إضافة طلب AJAX للحصول على البرامج حسب القسم
                // لأغراض العرض، سنستخدم بيانات وهمية
                
                const allPrograms = <?php echo json_encode(get_college_programs(get_db_connection(), $college_id)); ?>;
                const filteredPrograms = allPrograms.filter(p => p.department_id == departmentId);
                
                filteredPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    if (selectedProgramId && program.id == selectedProgramId) {
                        option.selected = true;
                    }
                    programSelect.appendChild(option);
                });
            } else {
                 // إذا لم يتم تحديد قسم، عرض جميع البرامج المتاحة للكلية
                const allPrograms = <?php echo json_encode(get_college_programs(get_db_connection(), $college_id)); ?>;
                allPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                     if (selectedProgramId && program.id == selectedProgramId) {
                        option.selected = true;
                    }
                    programSelect.appendChild(option);
                });
            }
        }
        
        // دالة لإظهار/إخفاء كلمة المرور
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const passwordToggle = passwordInput.nextElementSibling.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
