<?php
/**
 * صفحة إعدادات النظام للمشرف في نظام UniverBoard
 * تتيح للمشرف تعديل إعدادات النظام المختلفة
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول المشرف
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    header('Location: ../login.php');
    exit;
}

// الحصول على معلومات المشرف
$admin_id = $_SESSION['user_id'];
$db = get_db_connection();
$admin = get_admin_info($db, $admin_id);

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

// معالجة تحديث الإعدادات العامة
$general_success = false;
$general_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_general_settings') {
    $site_name = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_STRING);
    $site_description = filter_input(INPUT_POST, 'site_description', FILTER_SANITIZE_STRING);
    $site_email = filter_input(INPUT_POST, 'site_email', FILTER_VALIDATE_EMAIL);
    $default_lang = filter_input(INPUT_POST, 'default_lang', FILTER_SANITIZE_STRING);
    $default_theme = filter_input(INPUT_POST, 'default_theme', FILTER_SANITIZE_STRING);
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    
    // التحقق من البيانات
    if (empty($site_name)) {
        $general_error = t('site_name_required');
    } elseif (empty($site_email)) {
        $general_error = t('site_email_required');
    } else {
        // تحديث الإعدادات العامة
        $result = update_general_settings($db, $site_name, $site_description, $site_email, $default_lang, $default_theme, $maintenance_mode);
        
        if ($result) {
            $general_success = true;
        } else {
            $general_error = t('general_settings_update_failed');
        }
    }
}

// معالجة تحديث إعدادات البريد الإلكتروني
$email_success = false;
$email_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_email_settings') {
    $smtp_host = filter_input(INPUT_POST, 'smtp_host', FILTER_SANITIZE_STRING);
    $smtp_port = filter_input(INPUT_POST, 'smtp_port', FILTER_VALIDATE_INT);
    $smtp_username = filter_input(INPUT_POST, 'smtp_username', FILTER_SANITIZE_STRING);
    $smtp_password = filter_input(INPUT_POST, 'smtp_password', FILTER_SANITIZE_STRING);
    $smtp_encryption = filter_input(INPUT_POST, 'smtp_encryption', FILTER_SANITIZE_STRING);
    $from_email = filter_input(INPUT_POST, 'from_email', FILTER_VALIDATE_EMAIL);
    $from_name = filter_input(INPUT_POST, 'from_name', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($smtp_host)) {
        $email_error = t('smtp_host_required');
    } elseif (empty($smtp_port)) {
        $email_error = t('smtp_port_required');
    } elseif (empty($from_email)) {
        $email_error = t('from_email_required');
    } else {
        // تحديث إعدادات البريد الإلكتروني
        $result = update_email_settings($db, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption, $from_email, $from_name);
        
        if ($result) {
            $email_success = true;
        } else {
            $email_error = t('email_settings_update_failed');
        }
    }
}

// معالجة تحديث إعدادات الأمان
$security_success = false;
$security_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_security_settings') {
    $password_min_length = filter_input(INPUT_POST, 'password_min_length', FILTER_VALIDATE_INT);
    $password_require_uppercase = isset($_POST['password_require_uppercase']) ? 1 : 0;
    $password_require_lowercase = isset($_POST['password_require_lowercase']) ? 1 : 0;
    $password_require_number = isset($_POST['password_require_number']) ? 1 : 0;
    $password_require_special = isset($_POST['password_require_special']) ? 1 : 0;
    $password_expiry_days = filter_input(INPUT_POST, 'password_expiry_days', FILTER_VALIDATE_INT);
    $max_login_attempts = filter_input(INPUT_POST, 'max_login_attempts', FILTER_VALIDATE_INT);
    $lockout_time = filter_input(INPUT_POST, 'lockout_time', FILTER_VALIDATE_INT);
    $session_timeout = filter_input(INPUT_POST, 'session_timeout', FILTER_VALIDATE_INT);
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
    
    // التحقق من البيانات
    if (empty($password_min_length) || $password_min_length < 6) {
        $security_error = t('password_min_length_invalid');
    } elseif (empty($max_login_attempts) || $max_login_attempts < 1) {
        $security_error = t('max_login_attempts_invalid');
    } else {
        // تحديث إعدادات الأمان
        $result = update_security_settings($db, $password_min_length, $password_require_uppercase, $password_require_lowercase, $password_require_number, $password_require_special, $password_expiry_days, $max_login_attempts, $lockout_time, $session_timeout, $enable_2fa);
        
        if ($result) {
            $security_success = true;
        } else {
            $security_error = t('security_settings_update_failed');
        }
    }
}

// معالجة تحديث إعدادات الملفات
$file_success = false;
$file_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_file_settings') {
    $max_upload_size = filter_input(INPUT_POST, 'max_upload_size', FILTER_VALIDATE_INT);
    $allowed_file_types = filter_input(INPUT_POST, 'allowed_file_types', FILTER_SANITIZE_STRING);
    $storage_path = filter_input(INPUT_POST, 'storage_path', FILTER_SANITIZE_STRING);
    $enable_cloud_storage = isset($_POST['enable_cloud_storage']) ? 1 : 0;
    $cloud_provider = filter_input(INPUT_POST, 'cloud_provider', FILTER_SANITIZE_STRING);
    $cloud_api_key = filter_input(INPUT_POST, 'cloud_api_key', FILTER_SANITIZE_STRING);
    $cloud_secret = filter_input(INPUT_POST, 'cloud_secret', FILTER_SANITIZE_STRING);
    $cloud_bucket = filter_input(INPUT_POST, 'cloud_bucket', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($max_upload_size) || $max_upload_size < 1) {
        $file_error = t('max_upload_size_invalid');
    } elseif (empty($allowed_file_types)) {
        $file_error = t('allowed_file_types_required');
    } elseif (empty($storage_path)) {
        $file_error = t('storage_path_required');
    } else {
        // تحديث إعدادات الملفات
        $result = update_file_settings($db, $max_upload_size, $allowed_file_types, $storage_path, $enable_cloud_storage, $cloud_provider, $cloud_api_key, $cloud_secret, $cloud_bucket);
        
        if ($result) {
            $file_success = true;
        } else {
            $file_error = t('file_settings_update_failed');
        }
    }
}

// معالجة تحديث إعدادات الإشعارات
$notification_success = false;
$notification_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_notification_settings') {
    $enable_email_notifications = isset($_POST['enable_email_notifications']) ? 1 : 0;
    $enable_sms_notifications = isset($_POST['enable_sms_notifications']) ? 1 : 0;
    $enable_push_notifications = isset($_POST['enable_push_notifications']) ? 1 : 0;
    $notification_frequency = filter_input(INPUT_POST, 'notification_frequency', FILTER_SANITIZE_STRING);
    $sms_provider = filter_input(INPUT_POST, 'sms_provider', FILTER_SANITIZE_STRING);
    $sms_api_key = filter_input(INPUT_POST, 'sms_api_key', FILTER_SANITIZE_STRING);
    $sms_sender_id = filter_input(INPUT_POST, 'sms_sender_id', FILTER_SANITIZE_STRING);
    $push_api_key = filter_input(INPUT_POST, 'push_api_key', FILTER_SANITIZE_STRING);
    
    // تحديث إعدادات الإشعارات
    $result = update_notification_settings($db, $enable_email_notifications, $enable_sms_notifications, $enable_push_notifications, $notification_frequency, $sms_provider, $sms_api_key, $sms_sender_id, $push_api_key);
    
    if ($result) {
        $notification_success = true;
    } else {
        $notification_error = t('notification_settings_update_failed');
    }
}

// معالجة تحديث إعدادات Firebase
$firebase_success = false;
$firebase_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_firebase_settings') {
    $firebase_api_key = filter_input(INPUT_POST, 'firebase_api_key', FILTER_SANITIZE_STRING);
    $firebase_auth_domain = filter_input(INPUT_POST, 'firebase_auth_domain', FILTER_SANITIZE_STRING);
    $firebase_project_id = filter_input(INPUT_POST, 'firebase_project_id', FILTER_SANITIZE_STRING);
    $firebase_storage_bucket = filter_input(INPUT_POST, 'firebase_storage_bucket', FILTER_SANITIZE_STRING);
    $firebase_messaging_sender_id = filter_input(INPUT_POST, 'firebase_messaging_sender_id', FILTER_SANITIZE_STRING);
    $firebase_app_id = filter_input(INPUT_POST, 'firebase_app_id', FILTER_SANITIZE_STRING);
    $firebase_measurement_id = filter_input(INPUT_POST, 'firebase_measurement_id', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($firebase_api_key)) {
        $firebase_error = t('firebase_api_key_required');
    } elseif (empty($firebase_project_id)) {
        $firebase_error = t('firebase_project_id_required');
    } else {
        // تحديث إعدادات Firebase
        $result = update_firebase_settings($db, $firebase_api_key, $firebase_auth_domain, $firebase_project_id, $firebase_storage_bucket, $firebase_messaging_sender_id, $firebase_app_id, $firebase_measurement_id);
        
        if ($result) {
            $firebase_success = true;
        } else {
            $firebase_error = t('firebase_settings_update_failed');
        }
    }
}

// الحصول على الإعدادات الحالية
$general_settings = get_general_settings($db);
$email_settings = get_email_settings($db);
$security_settings = get_security_settings($db);
$file_settings = get_file_settings($db);
$notification_settings = get_notification_settings($db);
$firebase_settings = get_firebase_settings($db);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_admin_info($db, $admin_id) {
    return [
        'id' => $admin_id,
        'name' => 'أحمد محمد',
        'email' => 'admin@univerboard.com',
        'profile_image' => 'assets/images/admin.jpg',
        'role' => 'مشرف النظام',
        'last_login' => '2025-05-20 14:30:45'
    ];
}

function get_general_settings($db) {
    return [
        'site_name' => 'UniverBoard',
        'site_description' => 'نظام إدارة التعليم الإلكتروني للجامعات',
        'site_email' => 'info@univerboard.com',
        'default_lang' => 'ar',
        'default_theme' => 'light',
        'maintenance_mode' => 0,
        'version' => '1.0.0',
        'last_update' => '2025-05-01'
    ];
}

function get_email_settings($db) {
    return [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'info@univerboard.com',
        'smtp_password' => '********',
        'smtp_encryption' => 'tls',
        'from_email' => 'info@univerboard.com',
        'from_name' => 'UniverBoard'
    ];
}

function get_security_settings($db) {
    return [
        'password_min_length' => 8,
        'password_require_uppercase' => 1,
        'password_require_lowercase' => 1,
        'password_require_number' => 1,
        'password_require_special' => 0,
        'password_expiry_days' => 90,
        'max_login_attempts' => 5,
        'lockout_time' => 30,
        'session_timeout' => 60,
        'enable_2fa' => 0
    ];
}

function get_file_settings($db) {
    return [
        'max_upload_size' => 10,
        'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar',
        'storage_path' => '/uploads',
        'enable_cloud_storage' => 0,
        'cloud_provider' => 'aws',
        'cloud_api_key' => '',
        'cloud_secret' => '',
        'cloud_bucket' => ''
    ];
}

function get_notification_settings($db) {
    return [
        'enable_email_notifications' => 1,
        'enable_sms_notifications' => 0,
        'enable_push_notifications' => 1,
        'notification_frequency' => 'immediate',
        'sms_provider' => '',
        'sms_api_key' => '',
        'sms_sender_id' => '',
        'push_api_key' => ''
    ];
}

function get_firebase_settings($db) {
    return [
        'firebase_api_key' => 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'firebase_auth_domain' => 'univerboard.firebaseapp.com',
        'firebase_project_id' => 'univerboard',
        'firebase_storage_bucket' => 'univerboard.appspot.com',
        'firebase_messaging_sender_id' => '123456789012',
        'firebase_app_id' => '1:123456789012:web:abcdefghijklmnopqrstuv',
        'firebase_measurement_id' => 'G-ABCDEFGHIJ'
    ];
}

function update_general_settings($db, $site_name, $site_description, $site_email, $default_lang, $default_theme, $maintenance_mode) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

function update_email_settings($db, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption, $from_email, $from_name) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

function update_security_settings($db, $password_min_length, $password_require_uppercase, $password_require_lowercase, $password_require_number, $password_require_special, $password_expiry_days, $max_login_attempts, $lockout_time, $session_timeout, $enable_2fa) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

function update_file_settings($db, $max_upload_size, $allowed_file_types, $storage_path, $enable_cloud_storage, $cloud_provider, $cloud_api_key, $cloud_secret, $cloud_bucket) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

function update_notification_settings($db, $enable_email_notifications, $enable_sms_notifications, $enable_push_notifications, $notification_frequency, $sms_provider, $sms_api_key, $sms_sender_id, $push_api_key) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

function update_firebase_settings($db, $firebase_api_key, $firebase_auth_domain, $firebase_project_id, $firebase_storage_bucket, $firebase_messaging_sender_id, $firebase_app_id, $firebase_measurement_id) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('system_settings'); ?></title>
    
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
        /* (نفس تنسيقات CSS من الصفحات السابقة) */
        /* ... */
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
        
        /* تنسيقات شريط التنقل العلوي */
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
        
        /* تنسيقات الصفحة */
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
        
        /* تنسيقات النموذج */
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
        
        /* تنسيقات الأزرار */
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
        
        /* تنسيقات علامات التبويب */
        .nav-tabs {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .theme-dark .nav-tabs {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: -1px;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: transparent;
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* تنسيقات خاصة بالإعدادات */
        .settings-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .settings-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .theme-dark .settings-icon {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-section:last-child {
            margin-bottom: 0;
        }
        
        .settings-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .settings-divider {
            height: 1px;
            background-color: rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0;
        }
        
        .theme-dark .settings-divider {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .form-switch {
            padding-left: 2.5em;
        }
        
        [dir="rtl"] .form-switch {
            padding-left: 0;
            padding-right: 2.5em;
        }
        
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
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
        
        .form-check-label {
            font-weight: 500;
        }
        
        .settings-help-text {
            font-size: 0.875rem;
            color: var(--gray-color);
            margin-top: 0.25rem;
        }
        
        /* تنسيقات الإشعارات */
        .alert {
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            border-color: rgba(25, 135, 84, 0.2);
            color: #198754;
        }
        
        .theme-dark .alert-success {
            background-color: rgba(25, 135, 84, 0.2);
            border-color: rgba(25, 135, 84, 0.3);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        
        .theme-dark .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
        }
        
        .alert-info {
            background-color: rgba(13, 202, 240, 0.1);
            border-color: rgba(13, 202, 240, 0.2);
            color: #0dcaf0;
        }
        
        .theme-dark .alert-info {
            background-color: rgba(13, 202, 240, 0.2);
            border-color: rgba(13, 202, 240, 0.3);
        }
        
        /* تنسيقات الأيقونات */
        .settings-tab-icon {
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .settings-tab-icon {
            margin-right: 0;
            margin-left: 0.5rem;
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
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('user_management'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin_users.php">
                        <i class="fas fa-users"></i> <?php echo t('users'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_roles.php">
                        <i class="fas fa-user-tag"></i> <?php echo t('roles_permissions'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('academic_management'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin_colleges.php">
                        <i class="fas fa-university"></i> <?php echo t('colleges'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_departments.php">
                        <i class="fas fa-building"></i> <?php echo t('departments'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_programs.php">
                        <i class="fas fa-graduation-cap"></i> <?php echo t('academic_programs'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_courses.php">
                        <i class="fas fa-book"></i> <?php echo t('courses'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('system_management'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link active" href="admin_settings.php">
                        <i class="fas fa-cog"></i> <?php echo t('system_settings'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_logs.php">
                        <i class="fas fa-history"></i> <?php echo t('system_logs'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_backup.php">
                        <i class="fas fa-database"></i> <?php echo t('backup_restore'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('reports'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin_reports_users.php">
                        <i class="fas fa-chart-pie"></i> <?php echo t('user_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_reports_academic.php">
                        <i class="fas fa-chart-line"></i> <?php echo t('academic_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_reports_system.php">
                        <i class="fas fa-chart-bar"></i> <?php echo t('system_reports'); ?>
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
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?php echo t('notifications'); ?></h6>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-primary-light text-primary">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0"><?php echo t('new_user_registered'); ?></p>
                                    <small class="text-muted">30 <?php echo t('minutes_ago'); ?></small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-warning-light text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0"><?php echo t('system_update_available'); ?></p>
                                    <small class="text-muted">1 <?php echo t('hour_ago'); ?></small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-success-light text-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0"><?php echo t('database_backup_completed'); ?></p>
                                    <small class="text-muted">2 <?php echo t('hours_ago'); ?></small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center small text-muted" href="#"><?php echo t('show_all_notifications'); ?></a>
                    </div>
                </li>
                <li class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $admin['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $admin['name']; ?>">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?php echo $admin['name']; ?></h6>
                            <small><?php echo $admin['role']; ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="admin_profile.php">
                            <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                        </a>
                        <a class="dropdown-item" href="admin_settings.php">
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
            <h1 class="page-title"><?php echo t('system_settings'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_system_settings_and_configurations'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($general_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('general_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($email_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('email_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($security_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('security_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($file_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('file_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($notification_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('notification_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($firebase_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('firebase_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($general_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $general_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($email_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $email_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($security_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $security_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($file_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $file_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($notification_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $notification_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($firebase_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $firebase_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- علامات التبويب -->
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                    <i class="fas fa-cog settings-tab-icon"></i> <?php echo t('general'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">
                    <i class="fas fa-envelope settings-tab-icon"></i> <?php echo t('email'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                    <i class="fas fa-shield-alt settings-tab-icon"></i> <?php echo t('security'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">
                    <i class="fas fa-file settings-tab-icon"></i> <?php echo t('files'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                    <i class="fas fa-bell settings-tab-icon"></i> <?php echo t('notifications'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="firebase-tab" data-bs-toggle="tab" data-bs-target="#firebase" type="button" role="tab" aria-controls="firebase" aria-selected="false">
                    <i class="fas fa-fire settings-tab-icon"></i> <?php echo t('firebase'); ?>
                </button>
            </li>
        </ul>
        
        <!-- محتوى علامات التبويب -->
        <div class="tab-content" id="settingsTabsContent">
            <!-- الإعدادات العامة -->
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('general_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_general_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <?php echo t('site_information'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_name" class="form-label"><?php echo t('site_name'); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo $general_settings['site_name']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="site_email" class="form-label"><?php echo t('site_email'); ?> <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="site_email" name="site_email" value="<?php echo $general_settings['site_email']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label"><?php echo t('site_description'); ?></label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo $general_settings['site_description']; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <?php echo t('localization'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="default_lang" class="form-label"><?php echo t('default_language'); ?></label>
                                        <select class="form-select" id="default_lang" name="default_lang">
                                            <option value="ar" <?php echo $general_settings['default_lang'] === 'ar' ? 'selected' : ''; ?>>العربية</option>
                                            <option value="en" <?php echo $general_settings['default_lang'] === 'en' ? 'selected' : ''; ?>>English</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="default_theme" class="form-label"><?php echo t('default_theme'); ?></label>
                                        <select class="form-select" id="default_theme" name="default_theme">
                                            <option value="light" <?php echo $general_settings['default_theme'] === 'light' ? 'selected' : ''; ?>><?php echo t('light'); ?></option>
                                            <option value="dark" <?php echo $general_settings['default_theme'] === 'dark' ? 'selected' : ''; ?>><?php echo t('dark'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <?php echo t('maintenance'); ?>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?php echo $general_settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="maintenance_mode"><?php echo t('maintenance_mode'); ?></label>
                                    </div>
                                    <div class="settings-help-text"><?php echo t('maintenance_mode_help'); ?></div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-info"></i>
                                    </div>
                                    <?php echo t('system_information'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><?php echo t('version'); ?></label>
                                        <p class="form-control-plaintext"><?php echo $general_settings['version']; ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><?php echo t('last_update'); ?></label>
                                        <p class="form-control-plaintext"><?php echo $general_settings['last_update']; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات البريد الإلكتروني -->
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('email_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_email_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-server"></i>
                                    </div>
                                    <?php echo t('smtp_server'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_host" class="form-label"><?php echo t('smtp_host'); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo $email_settings['smtp_host']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_port" class="form-label"><?php echo t('smtp_port'); ?> <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo $email_settings['smtp_port']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_username" class="form-label"><?php echo t('smtp_username'); ?></label>
                                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo $email_settings['smtp_username']; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_password" class="form-label"><?php echo t('smtp_password'); ?></label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo $email_settings['smtp_password']; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="smtp_encryption" class="form-label"><?php echo t('smtp_encryption'); ?></label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                        <option value="none" <?php echo $email_settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>><?php echo t('none'); ?></option>
                                        <option value="ssl" <?php echo $email_settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="tls" <?php echo $email_settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <?php echo t('sender_information'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="from_email" class="form-label"><?php echo t('from_email'); ?> <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="from_email" name="from_email" value="<?php echo $email_settings['from_email']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="from_name" class="form-label"><?php echo t('from_name'); ?></label>
                                        <input type="text" class="form-control" id="from_name" name="from_name" value="<?php echo $email_settings['from_name']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <?php echo t('test_email'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="test_email" class="form-label"><?php echo t('test_email_address'); ?></label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" id="test_email" name="test_email" placeholder="<?php echo t('enter_email_address'); ?>">
                                            <button class="btn btn-outline-primary" type="button" id="sendTestEmail"><?php echo t('send_test'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
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
                        <h5 class="card-title"><?php echo t('security_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_security_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <?php echo t('password_policy'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password_min_length" class="form-label"><?php echo t('password_min_length'); ?> <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="password_min_length" name="password_min_length" value="<?php echo $security_settings['password_min_length']; ?>" min="6" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_expiry_days" class="form-label"><?php echo t('password_expiry_days'); ?></label>
                                        <input type="number" class="form-control" id="password_expiry_days" name="password_expiry_days" value="<?php echo $security_settings['password_expiry_days']; ?>" min="0">
                                        <div class="settings-help-text"><?php echo t('password_expiry_days_help'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="password_require_uppercase" name="password_require_uppercase" value="1" <?php echo $security_settings['password_require_uppercase'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="password_require_uppercase"><?php echo t('password_require_uppercase'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="password_require_lowercase" name="password_require_lowercase" value="1" <?php echo $security_settings['password_require_lowercase'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="password_require_lowercase"><?php echo t('password_require_lowercase'); ?></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="password_require_number" name="password_require_number" value="1" <?php echo $security_settings['password_require_number'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="password_require_number"><?php echo t('password_require_number'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="password_require_special" name="password_require_special" value="1" <?php echo $security_settings['password_require_special'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="password_require_special"><?php echo t('password_require_special'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <?php echo t('login_security'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="max_login_attempts" class="form-label"><?php echo t('max_login_attempts'); ?> <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" value="<?php echo $security_settings['max_login_attempts']; ?>" min="1" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lockout_time" class="form-label"><?php echo t('lockout_time'); ?> <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="lockout_time" name="lockout_time" value="<?php echo $security_settings['lockout_time']; ?>" min="1" required>
                                        <div class="settings-help-text"><?php echo t('lockout_time_help'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="session_timeout" class="form-label"><?php echo t('session_timeout'); ?> <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="session_timeout" name="session_timeout" value="<?php echo $security_settings['session_timeout']; ?>" min="1" required>
                                        <div class="settings-help-text"><?php echo t('session_timeout_help'); ?></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa" value="1" <?php echo $security_settings['enable_2fa'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_2fa"><?php echo t('enable_2fa'); ?></label>
                                        </div>
                                        <div class="settings-help-text"><?php echo t('enable_2fa_help'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات الملفات -->
            <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('file_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_file_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                    <?php echo t('upload_settings'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="max_upload_size" class="form-label"><?php echo t('max_upload_size'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" value="<?php echo $file_settings['max_upload_size']; ?>" min="1" required>
                                            <span class="input-group-text">MB</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="allowed_file_types" class="form-label"><?php echo t('allowed_file_types'); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" value="<?php echo $file_settings['allowed_file_types']; ?>" required>
                                        <div class="settings-help-text"><?php echo t('allowed_file_types_help'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="storage_path" class="form-label"><?php echo t('storage_path'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="storage_path" name="storage_path" value="<?php echo $file_settings['storage_path']; ?>" required>
                                    <div class="settings-help-text"><?php echo t('storage_path_help'); ?></div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-cloud"></i>
                                    </div>
                                    <?php echo t('cloud_storage'); ?>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_cloud_storage" name="enable_cloud_storage" value="1" <?php echo $file_settings['enable_cloud_storage'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_cloud_storage"><?php echo t('enable_cloud_storage'); ?></label>
                                    </div>
                                    <div class="settings-help-text"><?php echo t('enable_cloud_storage_help'); ?></div>
                                </div>
                                
                                <div class="cloud-storage-settings" <?php echo $file_settings['enable_cloud_storage'] ? '' : 'style="display: none;"'; ?>>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="cloud_provider" class="form-label"><?php echo t('cloud_provider'); ?></label>
                                            <select class="form-select" id="cloud_provider" name="cloud_provider">
                                                <option value="aws" <?php echo $file_settings['cloud_provider'] === 'aws' ? 'selected' : ''; ?>>Amazon S3</option>
                                                <option value="gcp" <?php echo $file_settings['cloud_provider'] === 'gcp' ? 'selected' : ''; ?>>Google Cloud Storage</option>
                                                <option value="azure" <?php echo $file_settings['cloud_provider'] === 'azure' ? 'selected' : ''; ?>>Microsoft Azure</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="cloud_bucket" class="form-label"><?php echo t('cloud_bucket'); ?></label>
                                            <input type="text" class="form-control" id="cloud_bucket" name="cloud_bucket" value="<?php echo $file_settings['cloud_bucket']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="cloud_api_key" class="form-label"><?php echo t('cloud_api_key'); ?></label>
                                            <input type="text" class="form-control" id="cloud_api_key" name="cloud_api_key" value="<?php echo $file_settings['cloud_api_key']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="cloud_secret" class="form-label"><?php echo t('cloud_secret'); ?></label>
                                            <input type="password" class="form-control" id="cloud_secret" name="cloud_secret" value="<?php echo $file_settings['cloud_secret']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
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
                        <h5 class="card-title"><?php echo t('notification_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_notification_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <?php echo t('notification_channels'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_email_notifications" name="enable_email_notifications" value="1" <?php echo $notification_settings['enable_email_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_email_notifications"><?php echo t('enable_email_notifications'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_sms_notifications" name="enable_sms_notifications" value="1" <?php echo $notification_settings['enable_sms_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_sms_notifications"><?php echo t('enable_sms_notifications'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_push_notifications" name="enable_push_notifications" value="1" <?php echo $notification_settings['enable_push_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="enable_push_notifications"><?php echo t('enable_push_notifications'); ?></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notification_frequency" class="form-label"><?php echo t('notification_frequency'); ?></label>
                                    <select class="form-select" id="notification_frequency" name="notification_frequency">
                                        <option value="immediate" <?php echo $notification_settings['notification_frequency'] === 'immediate' ? 'selected' : ''; ?>><?php echo t('immediate'); ?></option>
                                        <option value="hourly" <?php echo $notification_settings['notification_frequency'] === 'hourly' ? 'selected' : ''; ?>><?php echo t('hourly'); ?></option>
                                        <option value="daily" <?php echo $notification_settings['notification_frequency'] === 'daily' ? 'selected' : ''; ?>><?php echo t('daily'); ?></option>
                                        <option value="weekly" <?php echo $notification_settings['notification_frequency'] === 'weekly' ? 'selected' : ''; ?>><?php echo t('weekly'); ?></option>
                                    </select>
                                    <div class="settings-help-text"><?php echo t('notification_frequency_help'); ?></div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-sms"></i>
                                    </div>
                                    <?php echo t('sms_settings'); ?>
                                </div>
                                
                                <div class="sms-settings" <?php echo $notification_settings['enable_sms_notifications'] ? '' : 'style="display: none;"'; ?>>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="sms_provider" class="form-label"><?php echo t('sms_provider'); ?></label>
                                            <select class="form-select" id="sms_provider" name="sms_provider">
                                                <option value="twilio" <?php echo $notification_settings['sms_provider'] === 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                                                <option value="nexmo" <?php echo $notification_settings['sms_provider'] === 'nexmo' ? 'selected' : ''; ?>>Nexmo</option>
                                                <option value="messagebird" <?php echo $notification_settings['sms_provider'] === 'messagebird' ? 'selected' : ''; ?>>MessageBird</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="sms_sender_id" class="form-label"><?php echo t('sms_sender_id'); ?></label>
                                            <input type="text" class="form-control" id="sms_sender_id" name="sms_sender_id" value="<?php echo $notification_settings['sms_sender_id']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="sms_api_key" class="form-label"><?php echo t('sms_api_key'); ?></label>
                                        <input type="text" class="form-control" id="sms_api_key" name="sms_api_key" value="<?php echo $notification_settings['sms_api_key']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-divider"></div>
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <?php echo t('push_notification_settings'); ?>
                                </div>
                                
                                <div class="push-settings" <?php echo $notification_settings['enable_push_notifications'] ? '' : 'style="display: none;"'; ?>>
                                    <div class="mb-3">
                                        <label for="push_api_key" class="form-label"><?php echo t('push_api_key'); ?></label>
                                        <input type="text" class="form-control" id="push_api_key" name="push_api_key" value="<?php echo $notification_settings['push_api_key']; ?>">
                                        <div class="settings-help-text"><?php echo t('push_api_key_help'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات Firebase -->
            <div class="tab-pane fade" id="firebase" role="tabpanel" aria-labelledby="firebase-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('firebase_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_firebase_settings">
                            
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <div class="settings-icon">
                                        <i class="fas fa-fire"></i>
                                    </div>
                                    <?php echo t('firebase_configuration'); ?>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> <?php echo t('firebase_settings_info'); ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firebase_api_key" class="form-label"><?php echo t('firebase_api_key'); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="firebase_api_key" name="firebase_api_key" value="<?php echo $firebase_settings['firebase_api_key']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="firebase_auth_domain" class="form-label"><?php echo t('firebase_auth_domain'); ?></label>
                                        <input type="text" class="form-control" id="firebase_auth_domain" name="firebase_auth_domain" value="<?php echo $firebase_settings['firebase_auth_domain']; ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firebase_project_id" class="form-label"><?php echo t('firebase_project_id'); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="firebase_project_id" name="firebase_project_id" value="<?php echo $firebase_settings['firebase_project_id']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="firebase_storage_bucket" class="form-label"><?php echo t('firebase_storage_bucket'); ?></label>
                                        <input type="text" class="form-control" id="firebase_storage_bucket" name="firebase_storage_bucket" value="<?php echo $firebase_settings['firebase_storage_bucket']; ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firebase_messaging_sender_id" class="form-label"><?php echo t('firebase_messaging_sender_id'); ?></label>
                                        <input type="text" class="form-control" id="firebase_messaging_sender_id" name="firebase_messaging_sender_id" value="<?php echo $firebase_settings['firebase_messaging_sender_id']; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="firebase_app_id" class="form-label"><?php echo t('firebase_app_id'); ?></label>
                                        <input type="text" class="form-control" id="firebase_app_id" name="firebase_app_id" value="<?php echo $firebase_settings['firebase_app_id']; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="firebase_measurement_id" class="form-label"><?php echo t('firebase_measurement_id'); ?></label>
                                    <input type="text" class="form-control" id="firebase_measurement_id" name="firebase_measurement_id" value="<?php echo $firebase_settings['firebase_measurement_id']; ?>">
                                </div>
                            </div>
                            
                            <div class="text-end">
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
                // تحديث شعار القائمة الجانبية
                const logoImg = document.querySelector('.sidebar-logo img');
                logoImg.src = `assets/images/logo${newTheme === 'dark' ? '-white' : ''}.png`;
            });
            
            // إظهار/إخفاء إعدادات التخزين السحابي
            document.getElementById('enable_cloud_storage').addEventListener('change', function() {
                const cloudStorageSettings = document.querySelector('.cloud-storage-settings');
                cloudStorageSettings.style.display = this.checked ? 'block' : 'none';
            });
            
            // إظهار/إخفاء إعدادات الرسائل القصيرة
            document.getElementById('enable_sms_notifications').addEventListener('change', function() {
                const smsSettings = document.querySelector('.sms-settings');
                smsSettings.style.display = this.checked ? 'block' : 'none';
            });
            
            // إظهار/إخفاء إعدادات الإشعارات الفورية
            document.getElementById('enable_push_notifications').addEventListener('change', function() {
                const pushSettings = document.querySelector('.push-settings');
                pushSettings.style.display = this.checked ? 'block' : 'none';
            });
            
            // إرسال بريد إلكتروني تجريبي
            document.getElementById('sendTestEmail').addEventListener('click', function() {
                const testEmail = document.getElementById('test_email').value;
                
                if (!testEmail) {
                    alert('<?php echo t('please_enter_email_address'); ?>');
                    return;
                }
                
                // إظهار رسالة تحميل
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo t('sending'); ?>';
                this.disabled = true;
                
                // محاكاة إرسال البريد الإلكتروني
                setTimeout(() => {
                    alert('<?php echo t('test_email_sent_successfully'); ?>');
                    this.innerHTML = '<?php echo t('send_test'); ?>';
                    this.disabled = false;
                }, 2000);
            });
            
            // الحفاظ على علامة التبويب النشطة بعد إعادة تحميل الصفحة
            const hash = window.location.hash;
            if (hash) {
                const tab = document.querySelector(`[data-bs-target="${hash}"]`);
                if (tab) {
                    const tabInstance = new bootstrap.Tab(tab);
                    tabInstance.show();
                }
            }
            
            // تحديث عنوان URL عند تغيير علامة التبويب
            const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    const target = e.target.getAttribute('data-bs-target');
                    history.replaceState(null, null, target);
                });
            });
        });
    </script>
</body>
</html>
