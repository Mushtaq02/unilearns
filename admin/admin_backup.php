<?php
/**
 * صفحة إدارة النسخ الاحتياطي واستعادة البيانات في نظام UniverBoard
 * تتيح للمشرف إنشاء نسخ احتياطية واستعادة البيانات
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

// معالجة إنشاء نسخة احتياطية
$backup_success = false;
$backup_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_backup') {
    $backup_name = filter_input(INPUT_POST, 'backup_name', FILTER_SANITIZE_STRING);
    $backup_type = filter_input(INPUT_POST, 'backup_type', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($backup_name)) {
        $backup_error = t('backup_name_required');
    } else {
        // إنشاء النسخة الاحتياطية
        $result = create_backup($db, $backup_name, $backup_type);
        
        if ($result) {
            $backup_success = true;
        } else {
            $backup_error = t('backup_creation_failed');
        }
    }
}

// معالجة استعادة نسخة احتياطية
$restore_success = false;
$restore_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore_backup') {
    $backup_id = filter_input(INPUT_POST, 'backup_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($backup_id)) {
        $restore_error = t('backup_id_required');
    } else {
        // استعادة النسخة الاحتياطية
        $result = restore_backup($db, $backup_id);
        
        if ($result) {
            $restore_success = true;
        } else {
            $restore_error = t('backup_restore_failed');
        }
    }
}

// معالجة حذف نسخة احتياطية
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_backup') {
    $backup_id = filter_input(INPUT_POST, 'backup_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($backup_id)) {
        $delete_error = t('backup_id_required');
    } else {
        // حذف النسخة الاحتياطية
        $result = delete_backup($db, $backup_id);
        
        if ($result) {
            $delete_success = true;
        } else {
            $delete_error = t('backup_delete_failed');
        }
    }
}

// معالجة تنزيل نسخة احتياطية
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $backup_id = filter_input(INPUT_GET, 'download', FILTER_VALIDATE_INT);
    
    if (!empty($backup_id)) {
        // تنزيل النسخة الاحتياطية
        download_backup($db, $backup_id);
        exit;
    }
}

// الحصول على قائمة النسخ الاحتياطية
$backups = get_backups($db);

// الحصول على إحصائيات النسخ الاحتياطية
$backup_stats = get_backup_stats($db);

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

function get_backups($db) {
    // في الواقع، يجب استرجاع النسخ الاحتياطية من قاعدة البيانات
    return [
        [
            'id' => 1,
            'name' => 'نسخة احتياطية كاملة - مايو 2025',
            'type' => 'full',
            'size' => 15728640, // 15 ميجابايت
            'created_by' => 'أحمد محمد',
            'created_at' => '2025-05-15 10:30:00',
            'status' => 'completed',
            'file_path' => '/backups/full_backup_20250515103000.sql.gz'
        ],
        [
            'id' => 2,
            'name' => 'نسخة احتياطية للمستخدمين',
            'type' => 'users',
            'size' => 2097152, // 2 ميجابايت
            'created_by' => 'أحمد محمد',
            'created_at' => '2025-05-16 14:45:00',
            'status' => 'completed',
            'file_path' => '/backups/users_backup_20250516144500.sql.gz'
        ],
        [
            'id' => 3,
            'name' => 'نسخة احتياطية للمقررات',
            'type' => 'courses',
            'size' => 3145728, // 3 ميجابايت
            'created_by' => 'أحمد محمد',
            'created_at' => '2025-05-17 09:15:00',
            'status' => 'completed',
            'file_path' => '/backups/courses_backup_20250517091500.sql.gz'
        ],
        [
            'id' => 4,
            'name' => 'نسخة احتياطية للواجبات والدرجات',
            'type' => 'assignments',
            'size' => 5242880, // 5 ميجابايت
            'created_by' => 'أحمد محمد',
            'created_at' => '2025-05-18 16:20:00',
            'status' => 'completed',
            'file_path' => '/backups/assignments_backup_20250518162000.sql.gz'
        ],
        [
            'id' => 5,
            'name' => 'نسخة احتياطية للإعدادات',
            'type' => 'settings',
            'size' => 524288, // 512 كيلوبايت
            'created_by' => 'أحمد محمد',
            'created_at' => '2025-05-19 11:10:00',
            'status' => 'completed',
            'file_path' => '/backups/settings_backup_20250519111000.sql.gz'
        ],
        [
            'id' => 6,
            'name' => 'نسخة احتياطية كاملة - أسبوعية',
            'type' => 'full',
            'size' => 16777216, // 16 ميجابايت
            'created_by' => 'النظام (مجدولة)',
            'created_at' => '2025-05-20 03:00:00',
            'status' => 'completed',
            'file_path' => '/backups/full_backup_20250520030000.sql.gz'
        ],
        [
            'id' => 7,
            'name' => 'نسخة احتياطية للملفات المرفقة',
            'type' => 'files',
            'size' => 104857600, // 100 ميجابايت
            'created_by' => 'أحمد محمد',
            'created_at' => '2025-05-21 08:45:00',
            'status' => 'completed',
            'file_path' => '/backups/files_backup_20250521084500.zip'
        ]
    ];
}

function get_backup_stats($db) {
    // في الواقع، يجب استرجاع إحصائيات النسخ الاحتياطية من قاعدة البيانات
    return [
        'total_backups' => 7,
        'total_size' => 143654912, // حوالي 137 ميجابايت
        'last_backup' => '2025-05-21 08:45:00',
        'next_scheduled_backup' => '2025-05-27 03:00:00',
        'backup_types' => [
            'full' => 2,
            'users' => 1,
            'courses' => 1,
            'assignments' => 1,
            'settings' => 1,
            'files' => 1
        ]
    ];
}

function create_backup($db, $backup_name, $backup_type) {
    // في الواقع، يجب إنشاء النسخة الاحتياطية وحفظها
    return true;
}

function restore_backup($db, $backup_id) {
    // في الواقع، يجب استعادة النسخة الاحتياطية
    return true;
}

function delete_backup($db, $backup_id) {
    // في الواقع، يجب حذف النسخة الاحتياطية
    return true;
}

function download_backup($db, $backup_id) {
    // في الواقع، يجب تنزيل النسخة الاحتياطية
    // هذه الدالة تقوم بتوجيه المتصفح لتنزيل الملف
    exit;
}

// دالة تحويل حجم الملف إلى صيغة مقروءة
function format_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('backup_restore'); ?></title>
    
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
        
        .badge-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .badge-secondary {
            background-color: #669bbc;
            color: white;
        }
        
        .badge-success {
            background-color: #198754;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-info {
            background-color: #0dcaf0;
            color: #212529;
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
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
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
        
        /* تنسيقات خاصة بالنسخ الاحتياطية */
        .backup-type-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            min-width: 80px;
        }
        
        .backup-type-full {
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        .theme-dark .backup-type-full {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .backup-type-users {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .theme-dark .backup-type-users {
            background-color: rgba(102, 155, 188, 0.2);
        }
        
        .backup-type-courses {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .theme-dark .backup-type-courses {
            background-color: rgba(25, 135, 84, 0.2);
        }
        
        .backup-type-assignments {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .theme-dark .backup-type-assignments {
            background-color: rgba(255, 193, 7, 0.2);
        }
        
        .backup-type-settings {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }
        
        .theme-dark .backup-type-settings {
            background-color: rgba(13, 202, 240, 0.2);
        }
        
        .backup-type-files {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .theme-dark .backup-type-files {
            background-color: rgba(108, 117, 125, 0.2);
        }
        
        .backup-status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            min-width: 80px;
        }
        
        .backup-status-completed {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .theme-dark .backup-status-completed {
            background-color: rgba(25, 135, 84, 0.2);
        }
        
        .backup-status-in-progress {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .theme-dark .backup-status-in-progress {
            background-color: rgba(255, 193, 7, 0.2);
        }
        
        .backup-status-failed {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .theme-dark .backup-status-failed {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        /* تنسيقات البطاقات الإحصائية */
        .stats-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .stats-card-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .theme-dark .stats-card-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .stats-card-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }
        
        .stats-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .theme-dark .stats-card-icon {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .stats-card-body {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .stats-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card-label {
            font-size: 0.875rem;
            color: var(--gray-color);
        }
        
        /* تنسيقات الجدول الزمني */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        [dir="rtl"] .timeline {
            padding-left: 0;
            padding-right: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0.5rem;
            width: 2px;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        [dir="rtl"] .timeline::before {
            left: auto;
            right: 0.5rem;
        }
        
        .theme-dark .timeline::before {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            top: 0.25rem;
            left: -1.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background-color: var(--primary-color);
        }
        
        [dir="rtl"] .timeline-item::before {
            left: auto;
            right: -1.5rem;
        }
        
        .timeline-item.future::before {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .timeline-item.future::before {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .timeline-date {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .timeline-content {
            font-size: 0.875rem;
        }
        
        /* تنسيقات شريط التقدم */
        .progress {
            height: 0.5rem;
            border-radius: 0.25rem;
            background-color: rgba(0, 0, 0, 0.05);
            margin-top: 0.5rem;
        }
        
        .theme-dark .progress {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
        
        /* تنسيقات الأيقونات */
        .backup-icon {
            font-size: 1.25rem;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .backup-icon {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .backup-icon-full {
            color: var(--primary-color);
        }
        
        .backup-icon-users {
            color: #669bbc;
        }
        
        .backup-icon-courses {
            color: #198754;
        }
        
        .backup-icon-assignments {
            color: #ffc107;
        }
        
        .backup-icon-settings {
            color: #0dcaf0;
        }
        
        .backup-icon-files {
            color: #6c757d;
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
                    <a class="nav-link active" href="admin_backup.php">
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
            <h1 class="page-title"><?php echo t('backup_restore'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_system_backups_and_restore_data'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($backup_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('backup_created_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($restore_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('backup_restored_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('backup_deleted_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($backup_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $backup_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($restore_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $restore_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($delete_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $delete_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- البطاقات الإحصائية -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('total_backups'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo $backup_stats['total_backups']; ?></div>
                        <div class="stats-card-label"><?php echo t('backups'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('total_size'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo format_size($backup_stats['total_size']); ?></div>
                        <div class="stats-card-label"><?php echo t('disk_space'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('last_backup'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo date('Y-m-d', strtotime($backup_stats['last_backup'])); ?></div>
                        <div class="stats-card-label"><?php echo date('H:i:s', strtotime($backup_stats['last_backup'])); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('next_backup'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo date('Y-m-d', strtotime($backup_stats['next_scheduled_backup'])); ?></div>
                        <div class="stats-card-label"><?php echo date('H:i:s', strtotime($backup_stats['next_scheduled_backup'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- الجدول الزمني للنسخ الاحتياطية -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-calendar-alt me-2"></i> <?php echo t('backup_schedule'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime('-7 days')); ?></div>
                                <div class="timeline-content"><?php echo t('weekly_full_backup_completed'); ?></div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime('-1 day')); ?></div>
                                <div class="timeline-content"><?php echo t('daily_incremental_backup_completed'); ?></div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime('now')); ?></div>
                                <div class="timeline-content"><?php echo t('manual_backup_completed'); ?></div>
                            </div>
                            <div class="timeline-item future">
                                <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime('+1 day')); ?></div>
                                <div class="timeline-content"><?php echo t('daily_incremental_backup_scheduled'); ?></div>
                            </div>
                            <div class="timeline-item future">
                                <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime('+7 days')); ?></div>
                                <div class="timeline-content"><?php echo t('weekly_full_backup_scheduled'); ?></div>
                            </div>
                            <div class="timeline-item future">
                                <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime('+30 days')); ?></div>
                                <div class="timeline-content"><?php echo t('monthly_full_backup_scheduled'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- إنشاء نسخة احتياطية جديدة -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-plus-circle me-2"></i> <?php echo t('create_new_backup'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="create_backup">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="backup_name" class="form-label"><?php echo t('backup_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="backup_name" name="backup_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="backup_type" class="form-label"><?php echo t('backup_type'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="backup_type" name="backup_type" required>
                                        <option value="full"><?php echo t('full_backup'); ?></option>
                                        <option value="users"><?php echo t('users_backup'); ?></option>
                                        <option value="courses"><?php echo t('courses_backup'); ?></option>
                                        <option value="assignments"><?php echo t('assignments_backup'); ?></option>
                                        <option value="settings"><?php echo t('settings_backup'); ?></option>
                                        <option value="files"><?php echo t('files_backup'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_files" name="include_files" value="1" checked>
                                        <label class="form-check-label" for="include_files">
                                            <?php echo t('include_uploaded_files'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="compress_backup" name="compress_backup" value="1" checked>
                                        <label class="form-check-label" for="compress_backup">
                                            <?php echo t('compress_backup'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> <?php echo t('backup_info_message'); ?>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-database me-2"></i> <?php echo t('create_backup'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- قائمة النسخ الاحتياطية -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-list me-2"></i> <?php echo t('backups_list'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('name'); ?></th>
                                <th><?php echo t('type'); ?></th>
                                <th><?php echo t('size'); ?></th>
                                <th><?php echo t('created_by'); ?></th>
                                <th><?php echo t('created_at'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($backups)): ?>
                                <tr>
                                    <td colspan="7" class="text-center"><?php echo t('no_backups_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="backup-icon backup-icon-<?php echo $backup['type']; ?> fas <?php echo get_backup_icon($backup['type']); ?>"></i>
                                                <span><?php echo $backup['name']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="backup-type-badge backup-type-<?php echo $backup['type']; ?>"><?php echo t($backup['type']); ?></span>
                                        </td>
                                        <td><?php echo format_size($backup['size']); ?></td>
                                        <td><?php echo $backup['created_by']; ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($backup['created_at'])); ?></td>
                                        <td>
                                            <span class="backup-status-badge backup-status-<?php echo $backup['status']; ?>"><?php echo t($backup['status']); ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="?download=<?php echo $backup['id']; ?>" class="btn btn-sm btn-outline-primary" title="<?php echo t('download'); ?>">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#restoreBackupModal" onclick="prepareRestoreBackup(<?php echo $backup['id']; ?>, '<?php echo $backup['name']; ?>')" title="<?php echo t('restore'); ?>">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteBackupModal" onclick="prepareDeleteBackup(<?php echo $backup['id']; ?>, '<?php echo $backup['name']; ?>')" title="<?php echo t('delete'); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- إعدادات النسخ الاحتياطي -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-cog me-2"></i> <?php echo t('backup_settings'); ?></h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <input type="hidden" name="action" value="update_backup_settings">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="auto_backup" class="form-label"><?php echo t('automatic_backups'); ?></label>
                            <select class="form-select" id="auto_backup" name="auto_backup">
                                <option value="daily"><?php echo t('daily'); ?></option>
                                <option value="weekly" selected><?php echo t('weekly'); ?></option>
                                <option value="monthly"><?php echo t('monthly'); ?></option>
                                <option value="disabled"><?php echo t('disabled'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="backup_time" class="form-label"><?php echo t('backup_time'); ?></label>
                            <input type="time" class="form-control" id="backup_time" name="backup_time" value="03:00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="retention_period" class="form-label"><?php echo t('retention_period'); ?></label>
                            <select class="form-select" id="retention_period" name="retention_period">
                                <option value="7"><?php echo t('7_days'); ?></option>
                                <option value="30" selected><?php echo t('30_days'); ?></option>
                                <option value="90"><?php echo t('90_days'); ?></option>
                                <option value="365"><?php echo t('365_days'); ?></option>
                                <option value="0"><?php echo t('keep_indefinitely'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="backup_location" class="form-label"><?php echo t('backup_location'); ?></label>
                            <select class="form-select" id="backup_location" name="backup_location">
                                <option value="local" selected><?php echo t('local_storage'); ?></option>
                                <option value="cloud"><?php echo t('cloud_storage'); ?></option>
                                <option value="both"><?php echo t('both_local_and_cloud'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_on_completion" name="notify_on_completion" value="1" checked>
                                <label class="form-check-label" for="notify_on_completion">
                                    <?php echo t('notify_on_completion'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_on_failure" name="notify_on_failure" value="1" checked>
                                <label class="form-check-label" for="notify_on_failure">
                                    <?php echo t('notify_on_failure'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> <?php echo t('save_settings'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال استعادة النسخة الاحتياطية -->
    <div class="modal fade" id="restoreBackupModal" tabindex="-1" aria-labelledby="restoreBackupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreBackupModalLabel"><?php echo t('restore_backup'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="restore_backup">
                    <input type="hidden" name="backup_id" id="restore_backup_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_restore_backup'); ?>: <strong id="restore_backup_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('restore_backup_warning'); ?>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_restore" name="confirm_restore" value="1" required>
                            <label class="form-check-label" for="confirm_restore">
                                <?php echo t('confirm_restore_checkbox'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-undo me-2"></i> <?php echo t('restore'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف النسخة الاحتياطية -->
    <div class="modal fade" id="deleteBackupModal" tabindex="-1" aria-labelledby="deleteBackupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBackupModalLabel"><?php echo t('delete_backup'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_backup">
                    <input type="hidden" name="backup_id" id="delete_backup_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_backup'); ?>: <strong id="delete_backup_name"></strong>?</p>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_backup_warning'); ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> <?php echo t('delete'); ?>
                        </button>
                    </div>
                </form>
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
        // دالة للحصول على أيقونة النسخة الاحتياطية حسب النوع
        function get_backup_icon(type) {
            switch (type) {
                case 'full':
                    return 'fa-database';
                case 'users':
                    return 'fa-users';
                case 'courses':
                    return 'fa-book';
                case 'assignments':
                    return 'fa-tasks';
                case 'settings':
                    return 'fa-cog';
                case 'files':
                    return 'fa-file-archive';
                default:
                    return 'fa-database';
            }
        }
        
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
            
            // تحديث اسم النسخة الاحتياطية تلقائياً
            document.getElementById('backup_type').addEventListener('change', function() {
                const backupType = this.value;
                const backupName = document.getElementById('backup_name');
                const currentDate = new Date();
                const dateString = currentDate.getFullYear() + '-' + 
                                  String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(currentDate.getDate()).padStart(2, '0');
                
                let typeName = '';
                switch (backupType) {
                    case 'full':
                        typeName = '<?php echo t('full_backup'); ?>';
                        break;
                    case 'users':
                        typeName = '<?php echo t('users_backup'); ?>';
                        break;
                    case 'courses':
                        typeName = '<?php echo t('courses_backup'); ?>';
                        break;
                    case 'assignments':
                        typeName = '<?php echo t('assignments_backup'); ?>';
                        break;
                    case 'settings':
                        typeName = '<?php echo t('settings_backup'); ?>';
                        break;
                    case 'files':
                        typeName = '<?php echo t('files_backup'); ?>';
                        break;
                }
                
                backupName.value = typeName + ' - ' + dateString;
            });
            
            // تعيين اسم النسخة الاحتياطية الافتراضي
            const backupTypeSelect = document.getElementById('backup_type');
            if (backupTypeSelect) {
                backupTypeSelect.dispatchEvent(new Event('change'));
            }
        });
        
        // دالة تحضير مودال استعادة النسخة الاحتياطية
        function prepareRestoreBackup(backupId, backupName) {
            document.getElementById('restore_backup_id').value = backupId;
            document.getElementById('restore_backup_name').textContent = backupName;
        }
        
        // دالة تحضير مودال حذف النسخة الاحتياطية
        function prepareDeleteBackup(backupId, backupName) {
            document.getElementById('delete_backup_id').value = backupId;
            document.getElementById('delete_backup_name').textContent = backupName;
        }
    </script>
</body>
</html>
<?php
// دالة للحصول على أيقونة النسخة الاحتياطية حسب النوع
function get_backup_icon($type) {
    switch ($type) {
        case 'full':
            return 'fa-database';
        case 'users':
            return 'fa-users';
        case 'courses':
            return 'fa-book';
        case 'assignments':
            return 'fa-tasks';
        case 'settings':
            return 'fa-cog';
        case 'files':
            return 'fa-file-archive';
        default:
            return 'fa-database';
    }
}
?>
