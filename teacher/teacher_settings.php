<?php
/**
 * صفحة إعدادات المعلم في نظام UniverBoard
 * تتيح للمعلم تخصيص إعدادات حسابه وتفضيلاته
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول المعلم
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    header('Location: ../login.php');
    exit;
}

// الحصول على معلومات المعلم
$teacher_id = $_SESSION['user_id'];
$db = get_db_connection();
$teacher = get_teacher_info($db, $teacher_id);

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

// معالجة تغيير اللغة
if (isset($_POST['change_language'])) {
    $new_lang = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING);
    
    if ($new_lang === 'ar' || $new_lang === 'en') {
        // تعيين ملف تعريف الارتباط للغة
        setcookie('lang', $new_lang, time() + (86400 * 365), '/'); // صالح لمدة سنة
        
        // تحديث اللغة في قاعدة البيانات
        $result = update_user_language($db, $teacher_id, $new_lang);
        
        if ($result) {
            $success_message = t('language_updated_successfully');
            // تحديث اللغة الحالية
            $lang = $new_lang;
            // إعادة تحميل ملفات اللغة
            if ($lang === 'ar') {
                include '../includes/lang/ar.php';
            } else {
                include '../includes/lang/en.php';
            }
        } else {
            $error_message = t('failed_to_update_language');
        }
    }
}

// معالجة تغيير المظهر
if (isset($_POST['change_theme'])) {
    $new_theme = filter_input(INPUT_POST, 'theme', FILTER_SANITIZE_STRING);
    
    if ($new_theme === 'light' || $new_theme === 'dark') {
        // تعيين ملف تعريف الارتباط للمظهر
        setcookie('theme', $new_theme, time() + (86400 * 365), '/'); // صالح لمدة سنة
        
        // تحديث المظهر في قاعدة البيانات
        $result = update_user_theme($db, $teacher_id, $new_theme);
        
        if ($result) {
            $success_message = t('theme_updated_successfully');
            // تحديث المظهر الحالي
            $theme = $new_theme;
        } else {
            $error_message = t('failed_to_update_theme');
        }
    }
}

// معالجة تغيير إعدادات الإشعارات
if (isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $browser_notifications = isset($_POST['browser_notifications']) ? 1 : 0;
    
    // تحديث إعدادات الإشعارات في قاعدة البيانات
    $result = update_notification_settings($db, $enable_email_notifications, $enable_sms_notifications, $enable_push_notifications, $notification_frequency, $sms_provider, $sms_api_key, $sms_sender_id, $push_api_key);

    if ($result) {
        $success_message = t('notification_settings_updated_successfully');
        // تحديث معلومات المعلم
        $teacher = get_teacher_info($db, $teacher_id);
    } else {
        $error_message = t('failed_to_update_notification_settings');
    }
}

// معالجة تغيير إعدادات الخصوصية
if (isset($_POST['update_privacy'])) {
    $show_email = isset($_POST['show_email']) ? 1 : 0;
    $show_phone = isset($_POST['show_phone']) ? 1 : 0;
    $show_profile = isset($_POST['show_profile']) ? 1 : 0;
    
    // تحديث إعدادات الخصوصية في قاعدة البيانات
    $result = update_privacy_settings($db, $teacher_id, $show_email, $show_phone, $show_profile);
    
    if ($result) {
        $success_message = t('privacy_settings_updated_successfully');
        // تحديث معلومات المعلم
        $teacher = get_teacher_info($db, $teacher_id);
    } else {
        $error_message = t('failed_to_update_privacy_settings');
    }
}

// معالجة تغيير إعدادات الأمان
if (isset($_POST['update_security'])) {
    $two_factor_auth = isset($_POST['two_factor_auth']) ? 1 : 0;
    $login_alerts = isset($_POST['login_alerts']) ? 1 : 0;
    
    // تحديث إعدادات الأمان في قاعدة البيانات
    $result = update_security_settings($db, $password_min_length, $password_require_uppercase, $password_require_lowercase, $password_require_number, $password_require_special, $password_expiry_days, $max_login_attempts, $lockout_time, $session_timeout, $enable_2fa);

    if ($result) {
        $success_message = t('security_settings_updated_successfully');
        // تحديث معلومات المعلم
        $teacher = get_teacher_info($db, $teacher_id);
    } else {
        $error_message = t('failed_to_update_security_settings');
    }
}

// إغلاق اتصال قاعدة البيانات
$dsn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('settings'); ?></title>
    
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
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
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
        
        .settings-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .theme-dark .settings-container {
            background-color: var(--dark-bg);
        }
        
        .settings-sidebar {
            padding: 1.5rem;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        [dir="rtl"] .settings-sidebar {
            border-right: none;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .settings-sidebar {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .settings-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .settings-nav-item {
            margin-bottom: 0.5rem;
        }
        
        .settings-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .settings-nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .settings-nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .settings-nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .settings-nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .settings-nav-link i {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        .settings-content {
            padding: 1.5rem;
        }
        
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-section:last-child {
            margin-bottom: 0;
        }
        
        .settings-section-title {
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .settings-section-title {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .settings-form-group {
            margin-bottom: 1.5rem;
        }
        
        .settings-form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .settings-form-text {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-top: 0.25rem;
        }
        
        .settings-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .theme-preview {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .theme-option {
            width: 120px;
            height: 80px;
            border-radius: 0.5rem;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .theme-option.active {
            border-color: var(--primary-color);
        }
        
        .theme-option-header {
            height: 30%;
            background-color: #003049;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .theme-option-body {
            height: 70%;
            background-color: white;
            padding: 0.5rem;
        }
        
        .theme-option-dark .theme-option-header {
            background-color: #003049;
        }
        
        .theme-option-dark .theme-option-body {
            background-color: #121212;
        }
        
        .theme-option-text {
            width: 100%;
            height: 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            margin-bottom: 4px;
        }
        
        .theme-option-dark .theme-option-text {
            background-color: #333;
        }
        
        .theme-option-text:last-child {
            width: 70%;
        }
        
        .language-option {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .theme-dark .language-option {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .language-option:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .language-option:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .language-option.active {
            border-color: var(--primary-color);
            background-color: rgba(0, 48, 73, 0.05);
        }
        
        .theme-dark .language-option.active {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .language-option-flag {
            width: 30px;
            height: 20px;
            margin-right: 0.75rem;
            border-radius: 2px;
            object-fit: cover;
        }
        
        [dir="rtl"] .language-option-flag {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        .language-option-info {
            flex-grow: 1;
        }
        
        .language-option-name {
            font-weight: 500;
        }
        
        .language-option-native {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .language-option-check {
            color: var(--primary-color);
            opacity: 0;
            transition: all 0.2s ease;
        }
        
        .language-option.active .language-option-check {
            opacity: 1;
        }
        
        .form-switch {
            padding-left: 2.5em;
        }
        
        [dir="rtl"] .form-switch {
            padding-left: 0;
            padding-right: 2.5em;
        }
        
        .form-switch .form-check-input {
            height: 1.5em;
            width: 2.75em;
            margin-left: -2.5em;
        }
        
        [dir="rtl"] .form-switch .form-check-input {
            margin-left: 0;
            margin-right: -2.5em;
        }
        
        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                    <a class="nav-link" href="teacher_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_courses.php">
                        <i class="fas fa-book"></i> <?php echo t('my_courses'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_assignments.php">
                        <i class="fas fa-tasks"></i> <?php echo t('assignments'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_exams.php">
                        <i class="fas fa-file-alt"></i> <?php echo t('exams'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_grades.php">
                        <i class="fas fa-chart-line"></i> <?php echo t('grades'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_schedule.php">
                        <i class="fas fa-calendar-alt"></i> <?php echo t('schedule'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_students.php">
                        <i class="fas fa-user-graduate"></i> <?php echo t('students'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('communication'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="teacher_messages.php">
                        <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_notifications.php">
                        <i class="fas fa-bell"></i> <?php echo t('notifications'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_forums.php">
                        <i class="fas fa-comments"></i> <?php echo t('forums'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('account'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="teacher_profile.php">
                        <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="teacher_settings.php">
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
                        <span class="badge bg-danger">3</span>
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
                                    <p class="mb-0">قام الطالب أحمد محمد بتسليم واجب جديد</p>
                                    <small class="text-muted">منذ 10 دقائق</small>
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
                                    <p class="mb-0">تذكير: موعد محاضرة برمجة الويب غداً</p>
                                    <small class="text-muted">منذ 30 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-sm bg-info text-white rounded-circle">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">رسالة جديدة من رئيس القسم</p>
                                    <small class="text-muted">منذ ساعة</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="teacher_notifications.php"><?php echo t('view_all_notifications'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">2</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesDropdown">
                        <div class="dropdown-header"><?php echo t('messages'); ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/student1.jpg" alt="Student" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">سارة أحمد</p>
                                    <small class="text-muted">هل يمكنني الحصول على مساعدة في المشروع النهائي؟</small>
                                    <small class="text-muted d-block">منذ 15 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/student2.jpg" alt="Student" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">محمد علي</p>
                                    <small class="text-muted">أستاذ، هل يمكنني تأجيل موعد تسليم الواجب؟</small>
                                    <small class="text-muted d-block">منذ ساعة</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="teacher_messages.php"><?php echo t('view_all_messages'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $teacher['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $teacher['name']; ?>">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?php echo $teacher['name']; ?></h6>
                            <small><?php echo $teacher['title']; ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="teacher_profile.php">
                            <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                        </a>
                        <a class="dropdown-item" href="teacher_settings.php">
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
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <div>
                <h1 class="h3"><?php echo t('settings'); ?></h1>
                <p class="text-muted"><?php echo t('manage_your_account_settings_and_preferences'); ?></p>
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
        
        <!-- محتوى الإعدادات -->
        <div class="settings-container">
            <div class="row g-0">
                <div class="col-md-3">
                    <div class="settings-sidebar">
                        <ul class="settings-nav" id="settingsNav">
                            <li class="settings-nav-item">
                                <a href="#appearance" class="settings-nav-link active" data-bs-toggle="tab">
                                    <i class="fas fa-palette"></i> <?php echo t('appearance'); ?>
                                </a>
                            </li>
                            <li class="settings-nav-item">
                                <a href="#language" class="settings-nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-language"></i> <?php echo t('language'); ?>
                                </a>
                            </li>
                            <li class="settings-nav-item">
                                <a href="#notifications" class="settings-nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-bell"></i> <?php echo t('notifications'); ?>
                                </a>
                            </li>
                            <li class="settings-nav-item">
                                <a href="#privacy" class="settings-nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-user-shield"></i> <?php echo t('privacy'); ?>
                                </a>
                            </li>
                            <li class="settings-nav-item">
                                <a href="#security" class="settings-nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-lock"></i> <?php echo t('security'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="settings-content">
                        <div class="tab-content">
                            <!-- علامة التبويب: المظهر -->
                            <div class="tab-pane fade show active" id="appearance">
                                <div class="settings-section">
                                    <h3 class="settings-section-title"><?php echo t('theme'); ?></h3>
                                    <p class="mb-3"><?php echo t('choose_theme_description'); ?></p>
                                    
                                    <form action="" method="post">
                                        <div class="theme-preview">
                                            <div class="theme-option theme-option-light <?php echo $theme === 'light' ? 'active' : ''; ?>" data-theme="light">
                                                <div class="theme-option-header"></div>
                                                <div class="theme-option-body">
                                                    <div class="theme-option-text"></div>
                                                    <div class="theme-option-text"></div>
                                                </div>
                                            </div>
                                            <div class="theme-option theme-option-dark <?php echo $theme === 'dark' ? 'active' : ''; ?>" data-theme="dark">
                                                <div class="theme-option-header"></div>
                                                <div class="theme-option-body">
                                                    <div class="theme-option-text"></div>
                                                    <div class="theme-option-text"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="theme" id="themeInput" value="<?php echo $theme; ?>">
                                        
                                        <div class="settings-form-actions">
                                            <button type="submit" name="change_theme" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- علامة التبويب: اللغة -->
                            <div class="tab-pane fade" id="language">
                                <div class="settings-section">
                                    <h3 class="settings-section-title"><?php echo t('language'); ?></h3>
                                    <p class="mb-3"><?php echo t('choose_language_description'); ?></p>
                                    
                                    <form action="" method="post">
                                        <div class="language-option <?php echo $lang === 'ar' ? 'active' : ''; ?>" data-lang="ar">
                                            <img src="assets/images/flags/ar.png" alt="Arabic" class="language-option-flag">
                                            <div class="language-option-info">
                                                <div class="language-option-name">العربية</div>
                                                <div class="language-option-native">Arabic</div>
                                            </div>
                                            <div class="language-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="language-option <?php echo $lang === 'en' ? 'active' : ''; ?>" data-lang="en">
                                            <img src="assets/images/flags/en.png" alt="English" class="language-option-flag">
                                            <div class="language-option-info">
                                                <div class="language-option-name">English</div>
                                                <div class="language-option-native">الإنجليزية</div>
                                            </div>
                                            <div class="language-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="language" id="languageInput" value="<?php echo $lang; ?>">
                                        
                                        <div class="settings-form-actions">
                                            <button type="submit" name="change_language" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- علامة التبويب: الإشعارات -->
                            <div class="tab-pane fade" id="notifications">
                                <div class="settings-section">
                                    <h3 class="settings-section-title"><?php echo t('notification_preferences'); ?></h3>
                                    <p class="mb-3"><?php echo t('notification_preferences_description'); ?></p>
                                    
                                    <form action="" method="post">
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailNotifications" name="email_notifications" <?php echo $teacher['email_notifications'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailNotifications"><?php echo t('email_notifications'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('email_notifications_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="smsNotifications" name="sms_notifications" <?php echo $teacher['sms_notifications'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="smsNotifications"><?php echo t('sms_notifications'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('sms_notifications_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="browserNotifications" name="browser_notifications" <?php echo $teacher['browser_notifications'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="browserNotifications"><?php echo t('browser_notifications'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('browser_notifications_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-actions">
                                            <button type="submit" name="update_notifications" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- علامة التبويب: الخصوصية -->
                            <div class="tab-pane fade" id="privacy">
                                <div class="settings-section">
                                    <h3 class="settings-section-title"><?php echo t('privacy_settings'); ?></h3>
                                    <p class="mb-3"><?php echo t('privacy_settings_description'); ?></p>
                                    
                                    <form action="" method="post">
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="showEmail" name="show_email" <?php echo $teacher['show_email'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="showEmail"><?php echo t('show_email_to_students'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('show_email_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="showPhone" name="show_phone" <?php echo $teacher['show_phone'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="showPhone"><?php echo t('show_phone_to_students'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('show_phone_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="showProfile" name="show_profile" <?php echo $teacher['show_profile'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="showProfile"><?php echo t('show_profile_to_public'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('show_profile_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-actions">
                                            <button type="submit" name="update_privacy" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- علامة التبويب: الأمان -->
                            <div class="tab-pane fade" id="security">
                                <div class="settings-section">
                                    <h3 class="settings-section-title"><?php echo t('security_settings'); ?></h3>
                                    <p class="mb-3"><?php echo t('security_settings_description'); ?></p>
                                    
                                    <form action="" method="post">
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="twoFactorAuth" name="two_factor_auth" <?php echo $teacher['two_factor_auth'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="twoFactorAuth"><?php echo t('two_factor_authentication'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('two_factor_authentication_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-group">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="loginAlerts" name="login_alerts" <?php echo $teacher['login_alerts'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="loginAlerts"><?php echo t('login_alerts'); ?></label>
                                            </div>
                                            <div class="settings-form-text"><?php echo t('login_alerts_description'); ?></div>
                                        </div>
                                        
                                        <div class="settings-form-actions">
                                            <button type="submit" name="update_security" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="settings-section">
                                    <h3 class="settings-section-title"><?php echo t('sessions'); ?></h3>
                                    <p class="mb-3"><?php echo t('sessions_description'); ?></p>
                                    
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-info-circle fa-2x"></i>
                                            </div>
                                            <div>
                                                <h5 class="alert-heading"><?php echo t('current_session'); ?></h5>
                                                <p class="mb-0"><?php echo t('current_session_description'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-danger" id="logoutAllDevices">
                                            <i class="fas fa-sign-out-alt me-1"></i> <?php echo t('logout_from_all_devices'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
                
                // تحديث خيار المظهر المحدد
                document.querySelectorAll('.theme-option').forEach(option => {
                    option.classList.remove('active');
                });
                document.querySelector(`.theme-option-${newTheme}`).classList.add('active');
                document.getElementById('themeInput').value = newTheme;
            });
            
            // اختيار المظهر
            document.querySelectorAll('.theme-option').forEach(option => {
                option.addEventListener('click', function() {
                    const theme = this.getAttribute('data-theme');
                    
                    document.querySelectorAll('.theme-option').forEach(opt => {
                        opt.classList.remove('active');
                    });
                    
                    this.classList.add('active');
                    document.getElementById('themeInput').value = theme;
                });
            });
            
            // اختيار اللغة
            document.querySelectorAll('.language-option').forEach(option => {
                option.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    
                    document.querySelectorAll('.language-option').forEach(opt => {
                        opt.classList.remove('active');
                    });
                    
                    this.classList.add('active');
                    document.getElementById('languageInput').value = lang;
                });
            });
            
            // تسجيل الخروج من جميع الأجهزة
            document.getElementById('logoutAllDevices').addEventListener('click', function() {
                if (confirm('<?php echo t("logout_from_all_devices_confirmation"); ?>')) {
                    // يمكن إضافة منطق لتسجيل الخروج من جميع الأجهزة هنا
                    alert('<?php echo t("logout_from_all_devices_success"); ?>');
                }
            });
        });
    </script>
</body>
</html>
