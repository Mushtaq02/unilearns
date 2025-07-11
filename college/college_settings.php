<?php
/**
 * صفحة إعدادات الكلية في نظام UniverBoard
 * تتيح لمسؤول الكلية تعديل إعدادات الكلية المختلفة
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

// الحصول على إعدادات الكلية
$settings = get_college_settings($db, $college_id);

// معالجة تحديث الإعدادات العامة
$update_success = false;
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_general_settings') {
    $academic_year = filter_input(INPUT_POST, 'academic_year', FILTER_SANITIZE_STRING);
    $current_semester = filter_input(INPUT_POST, 'current_semester', FILTER_SANITIZE_STRING);
    $registration_open = isset($_POST['registration_open']) ? 1 : 0;
    $allow_course_registration = isset($_POST['allow_course_registration']) ? 1 : 0;
    $allow_grade_viewing = isset($_POST['allow_grade_viewing']) ? 1 : 0;
    $allow_schedule_viewing = isset($_POST['allow_schedule_viewing']) ? 1 : 0;
    
    // تحديث الإعدادات العامة
    $result = update_college_general_settings($db, $college_id, $academic_year, $current_semester, $registration_open, $allow_course_registration, $allow_grade_viewing, $allow_schedule_viewing);
    
    if ($result) {
        $update_success = true;
        // تحديث الإعدادات المحلية
        $settings['academic_year'] = $academic_year;
        $settings['current_semester'] = $current_semester;
        $settings['registration_open'] = $registration_open;
        $settings['allow_course_registration'] = $allow_course_registration;
        $settings['allow_grade_viewing'] = $allow_grade_viewing;
        $settings['allow_schedule_viewing'] = $allow_schedule_viewing;
    } else {
        $update_error = t('update_failed');
    }
}

// معالجة تحديث إعدادات الإشعارات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_notification_settings') {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    $notify_on_grade_change = isset($_POST['notify_on_grade_change']) ? 1 : 0;
    $notify_on_assignment = isset($_POST['notify_on_assignment']) ? 1 : 0;
    $notify_on_announcement = isset($_POST['notify_on_announcement']) ? 1 : 0;
    $notify_on_schedule_change = isset($_POST['notify_on_schedule_change']) ? 1 : 0;
    
    // تحديث إعدادات الإشعارات
    $result = update_college_notification_settings($db, $college_id, $email_notifications, $sms_notifications, $push_notifications, $notify_on_grade_change, $notify_on_assignment, $notify_on_announcement, $notify_on_schedule_change);
    
    if ($result) {
        $update_success = true;
        // تحديث الإعدادات المحلية
        $settings['email_notifications'] = $email_notifications;
        $settings['sms_notifications'] = $sms_notifications;
        $settings['push_notifications'] = $push_notifications;
        $settings['notify_on_grade_change'] = $notify_on_grade_change;
        $settings['notify_on_assignment'] = $notify_on_assignment;
        $settings['notify_on_announcement'] = $notify_on_announcement;
        $settings['notify_on_schedule_change'] = $notify_on_schedule_change;
    } else {
        $update_error = t('update_failed');
    }
}

// معالجة تحديث إعدادات الأمان
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_security_settings') {
    $two_factor_auth = isset($_POST['two_factor_auth']) ? 1 : 0;
    $password_expiry_days = filter_input(INPUT_POST, 'password_expiry_days', FILTER_VALIDATE_INT);
    $max_login_attempts = filter_input(INPUT_POST, 'max_login_attempts', FILTER_VALIDATE_INT);
    $session_timeout_minutes = filter_input(INPUT_POST, 'session_timeout_minutes', FILTER_VALIDATE_INT);
    $require_strong_password = isset($_POST['require_strong_password']) ? 1 : 0;
    
    // تحديث إعدادات الأمان
    $result = update_college_security_settings($db, $college_id, $two_factor_auth, $password_expiry_days, $max_login_attempts, $session_timeout_minutes, $require_strong_password);
    
    if ($result) {
        $update_success = true;
        // تحديث الإعدادات المحلية
        $settings['two_factor_auth'] = $two_factor_auth;
        $settings['password_expiry_days'] = $password_expiry_days;
        $settings['max_login_attempts'] = $max_login_attempts;
        $settings['session_timeout_minutes'] = $session_timeout_minutes;
        $settings['require_strong_password'] = $require_strong_password;
    } else {
        $update_error = t('update_failed');
    }
}

// معالجة تحديث إعدادات المظهر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_appearance_settings') {
    $default_theme = filter_input(INPUT_POST, 'default_theme', FILTER_SANITIZE_STRING);
    $default_language = filter_input(INPUT_POST, 'default_language', FILTER_SANITIZE_STRING);
    $custom_css = filter_input(INPUT_POST, 'custom_css', FILTER_SANITIZE_STRING);
    $show_announcements_banner = isset($_POST['show_announcements_banner']) ? 1 : 0;
    $show_events_calendar = isset($_POST['show_events_calendar']) ? 1 : 0;
    
    // تحديث إعدادات المظهر
    $result = update_college_appearance_settings($db, $college_id, $default_theme, $default_language, $custom_css, $show_announcements_banner, $show_events_calendar);
    
    if ($result) {
        $update_success = true;
        // تحديث الإعدادات المحلية
        $settings['default_theme'] = $default_theme;
        $settings['default_language'] = $default_language;
        $settings['custom_css'] = $custom_css;
        $settings['show_announcements_banner'] = $show_announcements_banner;
        $settings['show_events_calendar'] = $show_events_calendar;
    } else {
        $update_error = t('update_failed');
    }
}

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_college_admin_info($db, $admin_id) {
    return [
        'id' => $admin_id,
        'name' => 'د. عبدالله العمري',
        'email' => 'admin@example.com',
        'college_id' => 1,
        'profile_image' => 'assets/images/admin.jpg'
    ];
}

function get_college_info($db, $college_id) {
    return [
        'id' => $college_id,
        'name' => 'كلية علوم الحاسب والمعلومات',
        'code' => 'CS',
        'description' => 'كلية متخصصة في علوم الحاسب وتقنية المعلومات',
        'address' => 'المدينة الجامعية، طريق الملك عبدالله، الرياض',
        'phone' => '+966 11 4567890',
        'email' => 'cs-college@example.edu.sa',
        'website' => 'https://cs.example.edu.sa',
        'logo' => 'assets/images/college_logo.png',
        'established_date' => '2005-09-01',
        'dean_name' => 'د. عبدالله العمري'
    ];
}

function get_college_settings($db, $college_id) {
    return [
        // الإعدادات العامة
        'academic_year' => '2024-2025',
        'current_semester' => 'الفصل الثاني',
        'registration_open' => 1,
        'allow_course_registration' => 1,
        'allow_grade_viewing' => 1,
        'allow_schedule_viewing' => 1,
        
        // إعدادات الإشعارات
        'email_notifications' => 1,
        'sms_notifications' => 0,
        'push_notifications' => 1,
        'notify_on_grade_change' => 1,
        'notify_on_assignment' => 1,
        'notify_on_announcement' => 1,
        'notify_on_schedule_change' => 1,
        
        // إعدادات الأمان
        'two_factor_auth' => 0,
        'password_expiry_days' => 90,
        'max_login_attempts' => 5,
        'session_timeout_minutes' => 30,
        'require_strong_password' => 1,
        
        // إعدادات المظهر
        'default_theme' => 'light',
        'default_language' => 'ar',
        'custom_css' => '',
        'show_announcements_banner' => 1,
        'show_events_calendar' => 1
    ];
}

function update_college_general_settings($db, $college_id, $academic_year, $current_semester, $registration_open, $allow_course_registration, $allow_grade_viewing, $allow_schedule_viewing) {
    // في الواقع، يجب تحديث الإعدادات العامة في قاعدة البيانات
    return true;
}

function update_college_notification_settings($db, $college_id, $email_notifications, $sms_notifications, $push_notifications, $notify_on_grade_change, $notify_on_assignment, $notify_on_announcement, $notify_on_schedule_change) {
    // في الواقع، يجب تحديث إعدادات الإشعارات في قاعدة البيانات
    return true;
}

function update_college_security_settings($db, $college_id, $two_factor_auth, $password_expiry_days, $max_login_attempts, $session_timeout_minutes, $require_strong_password) {
    // في الواقع، يجب تحديث إعدادات الأمان في قاعدة البيانات
    return true;
}

function update_college_appearance_settings($db, $college_id, $default_theme, $default_language, $custom_css, $show_announcements_banner, $show_events_calendar) {
    // في الواقع، يجب تحديث إعدادات المظهر في قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('college_settings'); ?></title>
    
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
    
    <!-- ملف CSS الرئيسي -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- ملف CSS للمظهر -->
    <link rel="stylesheet" href="assets/css/theme-<?php echo $theme; ?>.css">
    
    <style>
        /* نفس تنسيقات القائمة الجانبية وشريط التنقل العلوي من الصفحات السابقة */
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
        
        /* تنسيقات خاصة بصفحة الإعدادات */
        .form-label {
            font-weight: 500;
        }
        
        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        
        .theme-dark .form-control, .theme-dark .form-select {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.25rem;
        }
        
        .form-check-label {
            padding-left: 0.5rem;
        }
        
        [dir="rtl"] .form-check-label {
            padding-left: 0;
            padding-right: 0.5rem;
        }
        
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .settings-section-title {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .settings-description {
            color: var(--gray-color);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .form-text {
            color: var(--gray-color);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .theme-preview {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .theme-option {
            border: 2px solid transparent;
            border-radius: 0.5rem;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .theme-option.active {
            border-color: var(--primary-color);
        }
        
        .theme-option img {
            width: 100%;
            height: auto;
            border-radius: 0.25rem;
        }
        
        .theme-option-label {
            text-align: center;
            padding: 0.5rem;
            font-weight: 500;
        }
        
        .custom-css-editor {
            font-family: monospace;
            min-height: 150px;
        }
        
        .nav-tabs {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .theme-dark .nav-tabs {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .nav-tabs .nav-link:hover {
            border-color: rgba(0, 48, 73, 0.3);
        }
        
        .nav-tabs .nav-link.active {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
        }
        
        .tab-content {
            padding-top: 1rem;
        }
        
        .settings-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .settings-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .settings-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .settings-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .settings-header p {
            margin: 0;
            color: var(--gray-color);
        }
    </style>
</head>
<body class="theme-<?php echo $theme; ?>">
    <!-- القائمة الجانبية -->
    <nav class="sidebar bg-white">
        <div class="sidebar-sticky">
            <div class="sidebar-logo">
                <img src="assets/images/logo<?php echo $theme === 'dark' ? '-white' : ''; ?>.png" alt="<?php echo SITE_NAME; ?>">
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
                    <a class="nav-link active" href="college_settings.php">
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
                <!-- نفس عناصر شريط التنقل العلوي من الصفحات السابقة -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger">5</span>
                    </a>
                    <!-- قائمة الإشعارات -->
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">3</span>
                    </a>
                    <!-- قائمة الرسائل -->
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
            <h1 class="page-title"><?php echo t('college_settings'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_college_settings_and_preferences'); ?></p>
        </div>
        
        <!-- رسالة نجاح التحديث -->
        <?php if ($update_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('update_success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- رسالة خطأ التحديث -->
        <?php if (!empty($update_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $update_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- علامات التبويب -->
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                    <i class="fas fa-sliders-h me-2"></i> <?php echo t('general_settings'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                    <i class="fas fa-bell me-2"></i> <?php echo t('notification_settings'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                    <i class="fas fa-shield-alt me-2"></i> <?php echo t('security_settings'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab" aria-controls="appearance" aria-selected="false">
                    <i class="fas fa-palette me-2"></i> <?php echo t('appearance_settings'); ?>
                </button>
            </li>
        </ul>
        
        <!-- محتوى علامات التبويب -->
        <div class="tab-content" id="settingsTabsContent">
            <!-- الإعدادات العامة -->
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-sliders-h me-2"></i> <?php echo t('general_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-header">
                            <div class="settings-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <div>
                                <h3><?php echo t('academic_settings'); ?></h3>
                                <p><?php echo t('manage_academic_year_and_semester_settings'); ?></p>
                            </div>
                        </div>
                        
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_general_settings">
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="academic_year" class="form-label"><?php echo t('academic_year'); ?></label>
                                    <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo $settings['academic_year']; ?>">
                                    <div class="form-text"><?php echo t('academic_year_format'); ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="current_semester" class="form-label"><?php echo t('current_semester'); ?></label>
                                    <select class="form-select" id="current_semester" name="current_semester">
                                        <option value="الفصل الأول" <?php echo $settings['current_semester'] === 'الفصل الأول' ? 'selected' : ''; ?>><?php echo t('first_semester'); ?></option>
                                        <option value="الفصل الثاني" <?php echo $settings['current_semester'] === 'الفصل الثاني' ? 'selected' : ''; ?>><?php echo t('second_semester'); ?></option>
                                        <option value="الفصل الصيفي" <?php echo $settings['current_semester'] === 'الفصل الصيفي' ? 'selected' : ''; ?>><?php echo t('summer_semester'); ?></option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('registration_settings'); ?></div>
                                <div class="settings-description"><?php echo t('control_registration_and_viewing_permissions'); ?></div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="registration_open" name="registration_open" <?php echo $settings['registration_open'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="registration_open"><?php echo t('open_registration_for_new_students'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('allow_new_students_to_register'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allow_course_registration" name="allow_course_registration" <?php echo $settings['allow_course_registration'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_course_registration"><?php echo t('allow_course_registration'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('allow_students_to_register_for_courses'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allow_grade_viewing" name="allow_grade_viewing" <?php echo $settings['allow_grade_viewing'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_grade_viewing"><?php echo t('allow_grade_viewing'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('allow_students_to_view_their_grades'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allow_schedule_viewing" name="allow_schedule_viewing" <?php echo $settings['allow_schedule_viewing'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_schedule_viewing"><?php echo t('allow_schedule_viewing'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('allow_students_to_view_their_schedules'); ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات الإشعارات -->
            <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-bell me-2"></i> <?php echo t('notification_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-header">
                            <div class="settings-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h3><?php echo t('notification_channels'); ?></h3>
                                <p><?php echo t('configure_how_notifications_are_sent'); ?></p>
                            </div>
                        </div>
                        
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_notification_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('notification_methods'); ?></div>
                                <div class="settings-description"><?php echo t('select_notification_delivery_methods'); ?></div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_notifications"><?php echo t('email_notifications'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('send_notifications_via_email'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications" <?php echo $settings['sms_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="sms_notifications"><?php echo t('sms_notifications'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('send_notifications_via_sms'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="push_notifications" name="push_notifications" <?php echo $settings['push_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="push_notifications"><?php echo t('push_notifications'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('send_notifications_via_push_notifications'); ?></div>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('notification_events'); ?></div>
                                <div class="settings-description"><?php echo t('select_events_that_trigger_notifications'); ?></div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notify_on_grade_change" name="notify_on_grade_change" <?php echo $settings['notify_on_grade_change'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notify_on_grade_change"><?php echo t('grade_changes'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('notify_when_grades_are_updated'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notify_on_assignment" name="notify_on_assignment" <?php echo $settings['notify_on_assignment'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notify_on_assignment"><?php echo t('new_assignments'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('notify_when_new_assignments_are_posted'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notify_on_announcement" name="notify_on_announcement" <?php echo $settings['notify_on_announcement'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notify_on_announcement"><?php echo t('announcements'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('notify_when_new_announcements_are_posted'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notify_on_schedule_change" name="notify_on_schedule_change" <?php echo $settings['notify_on_schedule_change'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notify_on_schedule_change"><?php echo t('schedule_changes'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('notify_when_schedule_changes_occur'); ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات الأمان -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-shield-alt me-2"></i> <?php echo t('security_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-header">
                            <div class="settings-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div>
                                <h3><?php echo t('account_security'); ?></h3>
                                <p><?php echo t('configure_security_settings_for_all_users'); ?></p>
                            </div>
                        </div>
                        
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_security_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('authentication_settings'); ?></div>
                                <div class="settings-description"><?php echo t('configure_authentication_methods_and_requirements'); ?></div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="two_factor_auth" name="two_factor_auth" <?php echo $settings['two_factor_auth'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="two_factor_auth"><?php echo t('two_factor_authentication'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('require_two_factor_authentication_for_all_users'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_strong_password" name="require_strong_password" <?php echo $settings['require_strong_password'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="require_strong_password"><?php echo t('require_strong_passwords'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('enforce_strong_password_requirements'); ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="password_expiry_days" class="form-label"><?php echo t('password_expiry_days'); ?></label>
                                        <input type="number" class="form-control" id="password_expiry_days" name="password_expiry_days" value="<?php echo $settings['password_expiry_days']; ?>" min="0" max="365">
                                        <div class="form-text"><?php echo t('days_until_password_expires'); ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="max_login_attempts" class="form-label"><?php echo t('max_login_attempts'); ?></label>
                                        <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" value="<?php echo $settings['max_login_attempts']; ?>" min="1" max="10">
                                        <div class="form-text"><?php echo t('maximum_failed_login_attempts_before_lockout'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('session_settings'); ?></div>
                                <div class="settings-description"><?php echo t('configure_user_session_behavior'); ?></div>
                                
                                <div class="mb-3">
                                    <label for="session_timeout_minutes" class="form-label"><?php echo t('session_timeout_minutes'); ?></label>
                                    <input type="number" class="form-control" id="session_timeout_minutes" name="session_timeout_minutes" value="<?php echo $settings['session_timeout_minutes']; ?>" min="5" max="240">
                                    <div class="form-text"><?php echo t('minutes_of_inactivity_before_session_expires'); ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات المظهر -->
            <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-palette me-2"></i> <?php echo t('appearance_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-header">
                            <div class="settings-icon">
                                <i class="fas fa-paint-brush"></i>
                            </div>
                            <div>
                                <h3><?php echo t('theme_and_display'); ?></h3>
                                <p><?php echo t('customize_the_appearance_of_the_platform'); ?></p>
                            </div>
                        </div>
                        
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_appearance_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('theme_settings'); ?></div>
                                <div class="settings-description"><?php echo t('select_default_theme_for_all_users'); ?></div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><?php echo t('default_theme'); ?></label>
                                    <div class="theme-preview row">
                                        <div class="col-md-6">
                                            <div class="theme-option <?php echo $settings['default_theme'] === 'light' ? 'active' : ''; ?>" data-theme="light">
                                                <img src="assets/images/theme-light-preview.png" alt="<?php echo t('light_theme'); ?>">
                                                <div class="theme-option-label">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="default_theme" id="theme_light" value="light" <?php echo $settings['default_theme'] === 'light' ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="theme_light">
                                                            <?php echo t('light_theme'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="theme-option <?php echo $settings['default_theme'] === 'dark' ? 'active' : ''; ?>" data-theme="dark">
                                                <img src="assets/images/theme-dark-preview.png" alt="<?php echo t('dark_theme'); ?>">
                                                <div class="theme-option-label">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="default_theme" id="theme_dark" value="dark" <?php echo $settings['default_theme'] === 'dark' ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="theme_dark">
                                                            <?php echo t('dark_theme'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label"><?php echo t('default_language'); ?></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="default_language" id="lang_ar" value="ar" <?php echo $settings['default_language'] === 'ar' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="lang_ar">
                                                    العربية
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="default_language" id="lang_en" value="en" <?php echo $settings['default_language'] === 'en' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="lang_en">
                                                    English
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('custom_styling'); ?></div>
                                <div class="settings-description"><?php echo t('add_custom_css_to_customize_appearance'); ?></div>
                                
                                <div class="mb-3">
                                    <label for="custom_css" class="form-label"><?php echo t('custom_css'); ?></label>
                                    <textarea class="form-control custom-css-editor" id="custom_css" name="custom_css" rows="6"><?php echo $settings['custom_css']; ?></textarea>
                                    <div class="form-text"><?php echo t('custom_css_description'); ?></div>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title"><?php echo t('display_options'); ?></div>
                                <div class="settings-description"><?php echo t('configure_what_elements_are_displayed'); ?></div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="show_announcements_banner" name="show_announcements_banner" <?php echo $settings['show_announcements_banner'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="show_announcements_banner"><?php echo t('show_announcements_banner'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('display_announcements_banner_on_dashboard'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="show_events_calendar" name="show_events_calendar" <?php echo $settings['show_events_calendar'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="show_events_calendar"><?php echo t('show_events_calendar'); ?></label>
                                    </div>
                                    <div class="form-text"><?php echo t('display_events_calendar_on_dashboard'); ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
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
            
            // تحديد خيار المظهر عند النقر على الصورة
            document.querySelectorAll('.theme-option').forEach(function(option) {
                option.addEventListener('click', function() {
                    const theme = this.dataset.theme;
                    document.getElementById(`theme_${theme}`).checked = true;
                    
                    document.querySelectorAll('.theme-option').forEach(function(opt) {
                        opt.classList.remove('active');
                    });
                    
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
