<?php
/**
 * صفحة تقارير المستخدمين في نظام UniverBoard
 * تتيح للمشرف عرض وتحليل بيانات المستخدمين
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

// تحديد الفترة الزمنية للتقارير
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$valid_periods = ['week', 'month', 'quarter', 'year', 'all'];
if (!in_array($period, $valid_periods)) {
    $period = 'month';
}

// الحصول على إحصائيات المستخدمين
$user_stats = get_user_statistics($db, $period);
$user_types = get_user_types_distribution($db);
$user_activity = get_user_activity($db, $period);
$new_users = get_new_users($db, $period);
$active_users = get_most_active_users($db, $period);
$login_attempts = get_login_attempts($db, $period);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_user_statistics($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على الإحصائيات
    return [
        'total_users' => 1250,
        'active_users' => 980,
        'inactive_users' => 270,
        'new_users' => 45,
        'growth_rate' => 3.6,
        'average_session_time' => 28.5,
        'average_sessions_per_user' => 12.3,
        'average_pages_per_session' => 5.8
    ];
}

function get_user_types_distribution($db) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على التوزيع
    return [
        ['type' => 'طلاب', 'count' => 850, 'percentage' => 68],
        ['type' => 'معلمين', 'count' => 320, 'percentage' => 25.6],
        ['type' => 'إداريين', 'count' => 65, 'percentage' => 5.2],
        ['type' => 'مشرفين', 'count' => 15, 'percentage' => 1.2]
    ];
}

function get_user_activity($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على النشاط
    if ($period === 'week') {
        return [
            ['date' => '2025-05-15', 'active_users' => 720, 'new_users' => 8],
            ['date' => '2025-05-16', 'active_users' => 680, 'new_users' => 5],
            ['date' => '2025-05-17', 'active_users' => 450, 'new_users' => 3],
            ['date' => '2025-05-18', 'active_users' => 420, 'new_users' => 2],
            ['date' => '2025-05-19', 'active_users' => 750, 'new_users' => 12],
            ['date' => '2025-05-20', 'active_users' => 820, 'new_users' => 10],
            ['date' => '2025-05-21', 'active_users' => 780, 'new_users' => 5]
        ];
    } else {
        return [
            ['date' => '2025-04-21', 'active_users' => 720, 'new_users' => 25],
            ['date' => '2025-04-28', 'active_users' => 750, 'new_users' => 30],
            ['date' => '2025-05-05', 'active_users' => 780, 'new_users' => 28],
            ['date' => '2025-05-12', 'active_users' => 800, 'new_users' => 32],
            ['date' => '2025-05-19', 'active_users' => 820, 'new_users' => 35]
        ];
    }
}

function get_new_users($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على المستخدمين الجدد
    return [
        [
            'id' => 1,
            'name' => 'محمد أحمد',
            'email' => 'mohammed@example.com',
            'type' => 'طالب',
            'college' => 'كلية الهندسة',
            'registration_date' => '2025-05-20 14:30:45'
        ],
        [
            'id' => 2,
            'name' => 'سارة علي',
            'email' => 'sara@example.com',
            'type' => 'طالب',
            'college' => 'كلية العلوم',
            'registration_date' => '2025-05-20 10:15:22'
        ],
        [
            'id' => 3,
            'name' => 'أحمد محمود',
            'email' => 'ahmed@example.com',
            'type' => 'معلم',
            'college' => 'كلية الطب',
            'registration_date' => '2025-05-19 11:45:10'
        ],
        [
            'id' => 4,
            'name' => 'فاطمة حسن',
            'email' => 'fatima@example.com',
            'type' => 'طالب',
            'college' => 'كلية الآداب',
            'registration_date' => '2025-05-19 09:20:33'
        ],
        [
            'id' => 5,
            'name' => 'خالد عبدالله',
            'email' => 'khaled@example.com',
            'type' => 'معلم',
            'college' => 'كلية الحاسب',
            'registration_date' => '2025-05-18 16:05:18'
        ]
    ];
}

function get_most_active_users($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على المستخدمين الأكثر نشاطًا
    return [
        [
            'id' => 1,
            'name' => 'عبدالرحمن محمد',
            'email' => 'abdulrahman@example.com',
            'type' => 'طالب',
            'college' => 'كلية الهندسة',
            'sessions' => 45,
            'last_activity' => '2025-05-21 10:30:45'
        ],
        [
            'id' => 2,
            'name' => 'نورة سعد',
            'email' => 'noura@example.com',
            'type' => 'طالب',
            'college' => 'كلية العلوم',
            'sessions' => 42,
            'last_activity' => '2025-05-21 09:15:22'
        ],
        [
            'id' => 3,
            'name' => 'محمد عبدالعزيز',
            'email' => 'mohammed.a@example.com',
            'type' => 'معلم',
            'college' => 'كلية الطب',
            'sessions' => 38,
            'last_activity' => '2025-05-21 11:45:10'
        ],
        [
            'id' => 4,
            'name' => 'سارة خالد',
            'email' => 'sara.k@example.com',
            'type' => 'طالب',
            'college' => 'كلية الآداب',
            'sessions' => 36,
            'last_activity' => '2025-05-20 16:20:33'
        ],
        [
            'id' => 5,
            'name' => 'فهد سعود',
            'email' => 'fahad@example.com',
            'type' => 'معلم',
            'college' => 'كلية الحاسب',
            'sessions' => 35,
            'last_activity' => '2025-05-20 14:05:18'
        ]
    ];
}

function get_login_attempts($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على محاولات تسجيل الدخول
    return [
        ['date' => '2025-05-15', 'successful' => 520, 'failed' => 45],
        ['date' => '2025-05-16', 'successful' => 480, 'failed' => 38],
        ['date' => '2025-05-17', 'successful' => 320, 'failed' => 25],
        ['date' => '2025-05-18', 'successful' => 290, 'failed' => 20],
        ['date' => '2025-05-19', 'successful' => 550, 'failed' => 42],
        ['date' => '2025-05-20', 'successful' => 620, 'failed' => 50],
        ['date' => '2025-05-21', 'successful' => 580, 'failed' => 48]
    ];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('user_reports'); ?></title>
    
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- ملف CSS الرئيسي -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- ملف CSS للمظهر -->
    <link rel="stylesheet" href="assets/css/theme-<?php echo $theme; ?>.css">
    
    <style>
        /* تنسيقات القائمة الجانبية */
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
        
        /* تنسيقات الإحصائيات */
        .stats-card {
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .theme-dark .stats-card {
            background-color: var(--dark-bg);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .stats-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .stats-icon.primary {
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        .stats-icon.secondary {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .stats-icon.success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .stats-icon.warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .stats-content {
            flex: 1;
        }
        
        .stats-title {
            font-size: 0.875rem;
            color: var(--gray-color);
            margin-bottom: 0.25rem;
        }
        
        .stats-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stats-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
        }
        
        .stats-change.positive {
            color: #198754;
        }
        
        .stats-change.negative {
            color: #dc3545;
        }
        
        .stats-change i {
            margin-right: 0.25rem;
        }
        
        [dir="rtl"] .stats-change i {
            margin-right: 0;
            margin-left: 0.25rem;
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
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        .theme-dark .badge-primary {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .badge-secondary {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .theme-dark .badge-secondary {
            background-color: rgba(102, 155, 188, 0.2);
        }
        
        .badge-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .theme-dark .badge-success {
            background-color: rgba(25, 135, 84, 0.2);
        }
        
        .badge-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .theme-dark .badge-warning {
            background-color: rgba(255, 193, 7, 0.2);
        }
        
        .badge-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .theme-dark .badge-danger {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        /* تنسيقات الرسوم البيانية */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .chart-legend-item {
            display: flex;
            align-items: center;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
        }
        
        [dir="rtl"] .chart-legend-item {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .chart-legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .chart-legend-color {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .chart-legend-label {
            font-size: 0.875rem;
            color: var(--text-color);
        }
        
        /* تنسيقات الفلاتر */
        .filter-bar {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .filter-label {
            font-weight: 500;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .filter-label {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .filter-options {
            display: flex;
            flex-wrap: wrap;
        }
        
        .filter-option {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-color);
            transition: all 0.2s;
        }
        
        [dir="rtl"] .filter-option {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .theme-dark .filter-option {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .filter-option:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .filter-option:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .filter-option.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .filter-option.active:hover {
            background-color: #002135;
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
        
        /* تنسيقات الصور المصغرة */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .avatar-sm {
            width: 32px;
            height: 32px;
        }
        
        .avatar-lg {
            width: 48px;
            height: 48px;
        }
        
        /* تنسيقات الدوائر النسبية */
        .progress-circle {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .theme-dark .progress-circle {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .progress-circle-value {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .progress-circle-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            clip: rect(0, 80px, 80px, 40px);
        }
        
        .progress-circle-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            clip: rect(0, 40px, 80px, 0);
            background-color: var(--primary-color);
            transform: rotate(var(--progress-value));
        }
        
        /* تنسيقات الأيقونات */
        .icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }
        
        .icon-circle.primary {
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        .icon-circle.secondary {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .icon-circle.success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .icon-circle.warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .icon-circle.danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
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
                    <a class="nav-link active" href="admin_reports_users.php">
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
                        <img src="assets/images/admin.jpg" alt="Admin">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0">أحمد محمد</h6>
                            <small>مشرف النظام</small>
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
            <h1 class="page-title"><?php echo t('user_reports'); ?></h1>
            <p class="page-subtitle"><?php echo t('analyze_user_data_and_activity_patterns'); ?></p>
        </div>
        
        <!-- فلاتر التقارير -->
        <div class="filter-bar">
            <div class="filter-label"><?php echo t('time_period'); ?>:</div>
            <div class="filter-options">
                <a href="?period=week" class="filter-option <?php echo $period === 'week' ? 'active' : ''; ?>"><?php echo t('last_week'); ?></a>
                <a href="?period=month" class="filter-option <?php echo $period === 'month' ? 'active' : ''; ?>"><?php echo t('last_month'); ?></a>
                <a href="?period=quarter" class="filter-option <?php echo $period === 'quarter' ? 'active' : ''; ?>"><?php echo t('last_quarter'); ?></a>
                <a href="?period=year" class="filter-option <?php echo $period === 'year' ? 'active' : ''; ?>"><?php echo t('last_year'); ?></a>
                <a href="?period=all" class="filter-option <?php echo $period === 'all' ? 'active' : ''; ?>"><?php echo t('all_time'); ?></a>
            </div>
        </div>
        
        <!-- إحصائيات المستخدمين -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('total_users'); ?></div>
                        <div class="stats-value"><?php echo number_format($user_stats['total_users']); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo $user_stats['growth_rate']; ?>% <?php echo t('since_last_period'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('active_users'); ?></div>
                        <div class="stats-value"><?php echo number_format($user_stats['active_users']); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo round(($user_stats['active_users'] / $user_stats['total_users']) * 100); ?>% <?php echo t('of_total_users'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon secondary">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('new_users'); ?></div>
                        <div class="stats-value"><?php echo number_format($user_stats['new_users']); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo round(($user_stats['new_users'] / $user_stats['total_users']) * 100, 1); ?>% <?php echo t('of_total_users'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('avg_session_time'); ?></div>
                        <div class="stats-value"><?php echo $user_stats['average_session_time']; ?> <?php echo t('minutes'); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo $user_stats['average_sessions_per_user']; ?> <?php echo t('sessions_per_user'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- توزيع أنواع المستخدمين -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('user_types_distribution'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userTypesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('user_activity_trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- محاولات تسجيل الدخول -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('login_attempts'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="loginAttemptsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- المستخدمين الجدد -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('new_users'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('user'); ?></th>
                                        <th><?php echo t('type'); ?></th>
                                        <th><?php echo t('college'); ?></th>
                                        <th><?php echo t('registration_date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($new_users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/default-user.png" alt="<?php echo $user['name']; ?>" class="avatar-sm me-2">
                                                    <div>
                                                        <div class="fw-medium"><?php echo $user['name']; ?></div>
                                                        <div class="small text-muted"><?php echo $user['email']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($user['type'] === 'طالب'): ?>
                                                    <span class="badge badge-primary"><?php echo $user['type']; ?></span>
                                                <?php elseif ($user['type'] === 'معلم'): ?>
                                                    <span class="badge badge-secondary"><?php echo $user['type']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-success"><?php echo $user['type']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $user['college']; ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($user['registration_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('most_active_users'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('user'); ?></th>
                                        <th><?php echo t('type'); ?></th>
                                        <th><?php echo t('sessions'); ?></th>
                                        <th><?php echo t('last_activity'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/default-user.png" alt="<?php echo $user['name']; ?>" class="avatar-sm me-2">
                                                    <div>
                                                        <div class="fw-medium"><?php echo $user['name']; ?></div>
                                                        <div class="small text-muted"><?php echo $user['email']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($user['type'] === 'طالب'): ?>
                                                    <span class="badge badge-primary"><?php echo $user['type']; ?></span>
                                                <?php elseif ($user['type'] === 'معلم'): ?>
                                                    <span class="badge badge-secondary"><?php echo $user['type']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-success"><?php echo $user['type']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle success me-2">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                    <?php echo $user['sessions']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($user['last_activity'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- زر تصدير التقرير -->
        <div class="text-center mt-4 mb-5">
            <button class="btn btn-primary">
                <i class="fas fa-file-export me-2"></i> <?php echo t('export_report'); ?>
            </button>
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
                
                // تحديث الرسوم البيانية
                updateChartsTheme(newTheme);
            });
            
            // تهيئة الرسوم البيانية
            initCharts();
            
            function initCharts() {
                // رسم بياني لتوزيع أنواع المستخدمين
                const userTypesCtx = document.getElementById('userTypesChart').getContext('2d');
                const userTypesData = <?php echo json_encode(array_column($user_types, 'count')); ?>;
                const userTypesLabels = <?php echo json_encode(array_column($user_types, 'type')); ?>;
                
                window.userTypesChart = new Chart(userTypesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: userTypesLabels,
                        datasets: [{
                            data: userTypesData,
                            backgroundColor: [
                                '#003049',
                                '#669bbc',
                                '#9bc1bc',
                                '#ed6a5a'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم بياني لنشاط المستخدمين
                const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
                const userActivityData = <?php echo json_encode($user_activity); ?>;
                
                window.userActivityChart = new Chart(userActivityCtx, {
                    type: 'line',
                    data: {
                        labels: userActivityData.map(item => item.date),
                        datasets: [
                            {
                                label: '<?php echo t('active_users'); ?>',
                                data: userActivityData.map(item => item.active_users),
                                borderColor: '#003049',
                                backgroundColor: 'rgba(0, 48, 73, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: '<?php echo t('new_users'); ?>',
                                data: userActivityData.map(item => item.new_users),
                                borderColor: '#669bbc',
                                backgroundColor: 'rgba(102, 155, 188, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    color: getGridColor()
                                },
                                ticks: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: getGridColor()
                                },
                                ticks: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم بياني لمحاولات تسجيل الدخول
                const loginAttemptsCtx = document.getElementById('loginAttemptsChart').getContext('2d');
                const loginAttemptsData = <?php echo json_encode($login_attempts); ?>;
                
                window.loginAttemptsChart = new Chart(loginAttemptsCtx, {
                    type: 'bar',
                    data: {
                        labels: loginAttemptsData.map(item => item.date),
                        datasets: [
                            {
                                label: '<?php echo t('successful_logins'); ?>',
                                data: loginAttemptsData.map(item => item.successful),
                                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                                borderWidth: 0
                            },
                            {
                                label: '<?php echo t('failed_logins'); ?>',
                                data: loginAttemptsData.map(item => item.failed),
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderWidth: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    color: getGridColor()
                                },
                                ticks: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: getGridColor()
                                },
                                ticks: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            function updateChartsTheme(theme) {
                const textColor = theme === 'dark' ? '#e9ecef' : '#212529';
                const gridColor = theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                
                // تحديث رسم بياني توزيع أنواع المستخدمين
                if (window.userTypesChart) {
                    window.userTypesChart.options.plugins.legend.labels.color = textColor;
                    window.userTypesChart.update();
                }
                
                // تحديث رسم بياني نشاط المستخدمين
                if (window.userActivityChart) {
                    window.userActivityChart.options.scales.x.grid.color = gridColor;
                    window.userActivityChart.options.scales.y.grid.color = gridColor;
                    window.userActivityChart.options.scales.x.ticks.color = textColor;
                    window.userActivityChart.options.scales.y.ticks.color = textColor;
                    window.userActivityChart.options.plugins.legend.labels.color = textColor;
                    window.userActivityChart.update();
                }
                
                // تحديث رسم بياني محاولات تسجيل الدخول
                if (window.loginAttemptsChart) {
                    window.loginAttemptsChart.options.scales.x.grid.color = gridColor;
                    window.loginAttemptsChart.options.scales.y.grid.color = gridColor;
                    window.loginAttemptsChart.options.scales.x.ticks.color = textColor;
                    window.loginAttemptsChart.options.scales.y.ticks.color = textColor;
                    window.loginAttemptsChart.options.plugins.legend.labels.color = textColor;
                    window.loginAttemptsChart.update();
                }
            }
            
            function getTextColor() {
                return document.body.className.includes('theme-dark') ? '#e9ecef' : '#212529';
            }
            
            function getGridColor() {
                return document.body.className.includes('theme-dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>
