<?php
/**
 * صفحة الملف الشخصي للمشرف في نظام UniverBoard
 * تتيح للمشرف عرض وتعديل معلوماته الشخصية
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
$db = getDbConnection();
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

// معالجة تحديث المعلومات الشخصية
$profile_success = false;
$profile_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($name)) {
        $profile_error = t('name_required');
    } elseif (empty($email)) {
        $profile_error = t('email_required');
    } else {
        // تحديث المعلومات الشخصية
        $result = update_admin_profile($db, $admin_id, $name, $email, $phone, $bio);
        
        if ($result) {
            $profile_success = true;
            // تحديث معلومات المشرف بعد التحديث
            $admin = get_admin_info($db, $admin_id);
        } else {
            $profile_error = t('profile_update_failed');
        }
    }
}

// معالجة تحديث كلمة المرور
$password_success = false;
$password_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($current_password)) {
        $password_error = t('current_password_required');
    } elseif (empty($new_password)) {
        $password_error = t('new_password_required');
    } elseif ($new_password !== $confirm_password) {
        $password_error = t('passwords_not_match');
    } elseif (strlen($new_password) < 8) {
        $password_error = t('password_too_short');
    } else {
        // التحقق من كلمة المرور الحالية
        $is_valid = verify_admin_password($db, $admin_id, $current_password);
        
        if (!$is_valid) {
            $password_error = t('current_password_incorrect');
        } else {
            // تحديث كلمة المرور
            $result = update_admin_password($db, $admin_id, $new_password);
            
            if ($result) {
                $password_success = true;
            } else {
                $password_error = t('password_update_failed');
            }
        }
    }
}

// معالجة تحديث الصورة الشخصية
$avatar_success = false;
$avatar_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
    // التحقق من وجود ملف مرفوع
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['avatar']['name'];
        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_size = $_FILES['avatar']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // التحقق من نوع الملف
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $avatar_error = t('avatar_invalid_format');
        } elseif ($file_size > 2097152) { // 2 ميجابايت
            $avatar_error = t('avatar_too_large');
        } else {
            // إنشاء اسم فريد للملف
            $new_file_name = 'admin_' . $admin_id . '_' . time() . '.' . $file_ext;
            $upload_path = 'uploads/avatars/' . $new_file_name;
            
            // التأكد من وجود المجلد
            if (!file_exists('uploads/avatars')) {
                mkdir('uploads/avatars', 0777, true);
            }
            
            // نقل الملف المرفوع
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // تحديث مسار الصورة في قاعدة البيانات
                $result = update_admin_avatar($db, $admin_id, $upload_path);
                
                if ($result) {
                    $avatar_success = true;
                    // تحديث معلومات المشرف بعد التحديث
                    $admin = get_admin_info($db, $admin_id);
                } else {
                    $avatar_error = t('avatar_update_failed');
                }
            } else {
                $avatar_error = t('avatar_upload_failed');
            }
        }
    } else {
        $avatar_error = t('avatar_required');
    }
}

// معالجة تحديث إعدادات الحساب
$account_success = false;
$account_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_account_settings') {
    $preferred_lang = filter_input(INPUT_POST, 'preferred_lang', FILTER_SANITIZE_STRING);
    $preferred_theme = filter_input(INPUT_POST, 'preferred_theme', FILTER_SANITIZE_STRING);
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    
    // تحديث إعدادات الحساب
    $result = update_admin_account_settings($db, $admin_id, $preferred_lang, $preferred_theme, $enable_2fa, $email_notifications);
    
    if ($result) {
        $account_success = true;
        
        // تحديث ملفات تعريف الارتباط للغة والمظهر
        if ($preferred_lang) {
            setcookie('lang', $preferred_lang, time() + 31536000, '/');
            $lang = $preferred_lang;
        }
        
        if ($preferred_theme) {
            setcookie('theme', $preferred_theme, time() + 31536000, '/');
            $theme = $preferred_theme;
        }
        
        // تحديث معلومات المشرف بعد التحديث
        $admin = get_admin_info($db, $admin_id);
    } else {
        $account_error = t('account_settings_update_failed');
    }
}

// الحصول على سجل تسجيل الدخول
$login_history = get_admin_login_history($db, $admin_id);

// إغلاق اتصال قاعدة البيانات
$db->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_admin_info($db, $admin_id) {
    return [
        'id' => $admin_id,
        'name' => 'أحمد محمد',
        'email' => 'admin@univerboard.com',
        'phone' => '+966 50 123 4567',
        'bio' => 'مشرف نظام UniverBoard مع خبرة أكثر من 10 سنوات في مجال تقنية المعلومات وإدارة أنظمة التعليم الإلكتروني.',
        'profile_image' => 'assets/images/admin.jpg',
        'role' => 'مشرف النظام',
        'last_login' => '2025-05-20 14:30:45',
        'created_at' => '2024-01-15 09:00:00',
        'preferred_lang' => 'ar',
        'preferred_theme' => 'light',
        'enable_2fa' => 0,
        'email_notifications' => 1
    ];
}

function get_admin_login_history($db, $admin_id) {
    return [
        [
            'id' => 1,
            'ip_address' => '192.168.1.1',
            'device' => 'Chrome 120.0.0.0 on Windows 10',
            'location' => 'الرياض، المملكة العربية السعودية',
            'status' => 'success',
            'timestamp' => '2025-05-20 14:30:45'
        ],
        [
            'id' => 2,
            'ip_address' => '192.168.1.1',
            'device' => 'Chrome 120.0.0.0 on Windows 10',
            'location' => 'الرياض، المملكة العربية السعودية',
            'status' => 'success',
            'timestamp' => '2025-05-19 09:15:22'
        ],
        [
            'id' => 3,
            'ip_address' => '192.168.1.1',
            'device' => 'Safari 17.0 on macOS',
            'location' => 'الرياض، المملكة العربية السعودية',
            'status' => 'success',
            'timestamp' => '2025-05-18 11:45:10'
        ],
        [
            'id' => 4,
            'ip_address' => '192.168.1.1',
            'device' => 'Chrome 120.0.0.0 on Windows 10',
            'location' => 'الرياض، المملكة العربية السعودية',
            'status' => 'success',
            'timestamp' => '2025-05-17 16:20:33'
        ],
        [
            'id' => 5,
            'ip_address' => '192.168.1.1',
            'device' => 'Firefox 115.0 on Windows 10',
            'location' => 'جدة، المملكة العربية السعودية',
            'status' => 'failed',
            'timestamp' => '2025-05-17 10:05:18'
        ]
    ];
}

function update_admin_profile($db, $admin_id, $name, $email, $phone, $bio) {
    // في الواقع، يجب تحديث المعلومات في قاعدة البيانات
    return true;
}

function update_admin_password($db, $admin_id, $new_password) {
    // في الواقع، يجب تحديث كلمة المرور في قاعدة البيانات
    return true;
}

function verify_admin_password($db, $admin_id, $current_password) {
    // في الواقع، يجب التحقق من كلمة المرور في قاعدة البيانات
    return true;
}

function update_admin_avatar($db, $admin_id, $avatar_path) {
    // في الواقع، يجب تحديث مسار الصورة في قاعدة البيانات
    return true;
}

function update_admin_account_settings($db, $admin_id, $preferred_lang, $preferred_theme, $enable_2fa, $email_notifications) {
    // في الواقع، يجب تحديث الإعدادات في قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('profile'); ?></title>
    
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
        
        /* تنسيقات خاصة بالملف الشخصي */
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .profile-avatar {
            border-color: var(--dark-bg);
        }
        
        .profile-info {
            margin-left: 2rem;
        }
        
        [dir="rtl"] .profile-info {
            margin-left: 0;
            margin-right: 2rem;
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .profile-role {
            font-size: 1rem;
            color: var(--gray-color);
            margin-bottom: 0.5rem;
        }
        
        .profile-meta {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }
        
        .profile-meta i {
            width: 20px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .profile-meta i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .avatar-upload {
            position: relative;
            width: 120px;
            margin: 0 auto;
        }
        
        .avatar-edit {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        [dir="rtl"] .avatar-edit {
            right: auto;
            left: 0;
        }
        
        .avatar-edit input {
            display: none;
        }
        
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .avatar-preview {
            border-color: var(--dark-bg);
        }
        
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* تنسيقات الجدول */
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--text-color);
        }
        
        .theme-dark .table th {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            color: var(--text-color);
        }
        
        .table tr:not(:last-child) td {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .table tr:not(:last-child) td {
            border-color: rgba(255, 255, 255, 0.05);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        /* تنسيقات الشارات */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            font-size: 0.75rem;
            border-radius: 0.25rem;
        }
        
        .badge-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .theme-dark .badge-success {
            background-color: rgba(25, 135, 84, 0.2);
        }
        
        .badge-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .theme-dark .badge-danger {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        /* تنسيقات الأيقونات */
        .profile-tab-icon {
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .profile-tab-icon {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        /* تنسيقات مفتاح التبديل */
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
                    <a class="nav-link" href="admin_settings.php">
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
                        <a class="dropdown-item active" href="admin_profile.php">
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
            <h1 class="page-title"><?php echo t('profile'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_your_personal_information_and_account_settings'); ?></p>
        </div>
        
        <!-- رأس الملف الشخصي -->
        <div class="profile-header">
            <img src="<?php echo $admin['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $admin['name']; ?>" class="profile-avatar">
            <div class="profile-info">
                <h2 class="profile-name"><?php echo $admin['name']; ?></h2>
                <div class="profile-role"><?php echo $admin['role']; ?></div>
                <div class="profile-meta">
                    <i class="fas fa-envelope"></i> <?php echo $admin['email']; ?>
                </div>
                <div class="profile-meta">
                    <i class="fas fa-phone"></i> <?php echo $admin['phone']; ?>
                </div>
                <div class="profile-meta">
                    <i class="fas fa-clock"></i> <?php echo t('last_login'); ?>: <?php echo date('Y-m-d H:i', strtotime($admin['last_login'])); ?>
                </div>
            </div>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($profile_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('profile_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($password_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('password_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($avatar_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('avatar_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($account_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('account_settings_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($profile_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $profile_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($password_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $password_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($avatar_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $avatar_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($account_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $account_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- علامات التبويب -->
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
                    <i class="fas fa-user profile-tab-icon"></i> <?php echo t('personal_information'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">
                    <i class="fas fa-key profile-tab-icon"></i> <?php echo t('change_password'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="avatar-tab" data-bs-toggle="tab" data-bs-target="#avatar" type="button" role="tab" aria-controls="avatar" aria-selected="false">
                    <i class="fas fa-image profile-tab-icon"></i> <?php echo t('profile_picture'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="false">
                    <i class="fas fa-cog profile-tab-icon"></i> <?php echo t('account_settings'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="login-history-tab" data-bs-toggle="tab" data-bs-target="#login-history" type="button" role="tab" aria-controls="login-history" aria-selected="false">
                    <i class="fas fa-history profile-tab-icon"></i> <?php echo t('login_history'); ?>
                </button>
            </li>
        </ul>
        
        <!-- محتوى علامات التبويب -->
        <div class="tab-content" id="profileTabsContent">
            <!-- المعلومات الشخصية -->
            <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('personal_information'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label"><?php echo t('full_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $admin['name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label"><?php echo t('email'); ?> <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $admin['email']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label"><?php echo t('phone'); ?></label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $admin['phone']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label"><?php echo t('role'); ?></label>
                                    <input type="text" class="form-control" id="role" value="<?php echo $admin['role']; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label"><?php echo t('bio'); ?></label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo $admin['bio']; ?></textarea>
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
            
            <!-- تغيير كلمة المرور -->
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('change_password'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label"><?php echo t('current_password'); ?> <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label"><?php echo t('new_password'); ?> <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text"><?php echo t('password_requirements'); ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label"><?php echo t('confirm_password'); ?> <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i> <?php echo t('update_password'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- الصورة الشخصية -->
            <div class="tab-pane fade" id="avatar" role="tabpanel" aria-labelledby="avatar-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('profile_picture'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_avatar">
                            
                            <div class="text-center mb-4">
                                <div class="avatar-upload">
                                    <div class="avatar-edit">
                                        <label for="avatar">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input type="file" id="avatar" name="avatar" accept="image/*">
                                    </div>
                                    <div class="avatar-preview">
                                        <img src="<?php echo $admin['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $admin['name']; ?>" id="avatar-preview-img">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <p class="form-text"><?php echo t('avatar_requirements'); ?></p>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i> <?php echo t('upload_avatar'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- إعدادات الحساب -->
            <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="account-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('account_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_account_settings">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="preferred_lang" class="form-label"><?php echo t('preferred_language'); ?></label>
                                    <select class="form-select" id="preferred_lang" name="preferred_lang">
                                        <option value="ar" <?php echo $admin['preferred_lang'] === 'ar' ? 'selected' : ''; ?>>العربية</option>
                                        <option value="en" <?php echo $admin['preferred_lang'] === 'en' ? 'selected' : ''; ?>>English</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="preferred_theme" class="form-label"><?php echo t('preferred_theme'); ?></label>
                                    <select class="form-select" id="preferred_theme" name="preferred_theme">
                                        <option value="light" <?php echo $admin['preferred_theme'] === 'light' ? 'selected' : ''; ?>><?php echo t('light'); ?></option>
                                        <option value="dark" <?php echo $admin['preferred_theme'] === 'dark' ? 'selected' : ''; ?>><?php echo t('dark'); ?></option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa" value="1" <?php echo $admin['enable_2fa'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_2fa"><?php echo t('enable_2fa'); ?></label>
                                </div>
                                <div class="form-text"><?php echo t('enable_2fa_help'); ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" <?php echo $admin['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications"><?php echo t('email_notifications'); ?></label>
                                </div>
                                <div class="form-text"><?php echo t('email_notifications_help'); ?></div>
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
            
            <!-- سجل تسجيل الدخول -->
            <div class="tab-pane fade" id="login-history" role="tabpanel" aria-labelledby="login-history-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('login_history'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('date_time'); ?></th>
                                        <th><?php echo t('ip_address'); ?></th>
                                        <th><?php echo t('device'); ?></th>
                                        <th><?php echo t('location'); ?></th>
                                        <th><?php echo t('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($login_history)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center"><?php echo t('no_login_history'); ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($login_history as $login): ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d H:i:s', strtotime($login['timestamp'])); ?></td>
                                                <td><?php echo $login['ip_address']; ?></td>
                                                <td><?php echo $login['device']; ?></td>
                                                <td><?php echo $login['location']; ?></td>
                                                <td>
                                                    <?php if ($login['status'] === 'success'): ?>
                                                        <span class="badge badge-success"><?php echo t('success'); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger"><?php echo t('failed'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
            
            // معاينة الصورة الشخصية قبل الرفع
            document.getElementById('avatar').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('avatar-preview-img').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
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
