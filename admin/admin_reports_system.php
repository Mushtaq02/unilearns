<?php
/**
 * صفحة تقارير النظام في نظام UniverBoard
 * تتيح للمشرف عرض وتحليل بيانات أداء النظام
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
$valid_periods = ['day', 'week', 'month', 'year'];
if (!in_array($period, $valid_periods)) {
    $period = 'month';
}

// الحصول على إحصائيات النظام
$system_stats = get_system_statistics($db, $period);
$server_load = get_server_load($db, $period);
$error_logs = get_error_logs($db, $period);
$api_usage = get_api_usage($db, $period);
$storage_usage = get_storage_usage($db);
$backup_history = get_backup_history($db, $period);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_system_statistics($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على الإحصائيات
    return [
        'total_requests' => 1250000,
        'average_response_time' => 0.85,
        'error_rate' => 0.42,
        'uptime' => 99.98,
        'active_sessions' => 320,
        'peak_sessions' => 580,
        'database_size' => 1.8,
        'total_files' => 12500
    ];
}

function get_server_load($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على بيانات الحمل
    if ($period === 'day') {
        return [
            ['time' => '00:00', 'cpu' => 15, 'memory' => 35, 'requests' => 120],
            ['time' => '02:00', 'cpu' => 12, 'memory' => 32, 'requests' => 80],
            ['time' => '04:00', 'cpu' => 10, 'memory' => 30, 'requests' => 60],
            ['time' => '06:00', 'cpu' => 18, 'memory' => 38, 'requests' => 150],
            ['time' => '08:00', 'cpu' => 45, 'memory' => 55, 'requests' => 450],
            ['time' => '10:00', 'cpu' => 65, 'memory' => 68, 'requests' => 720],
            ['time' => '12:00', 'cpu' => 70, 'memory' => 72, 'requests' => 850],
            ['time' => '14:00', 'cpu' => 68, 'memory' => 70, 'requests' => 780],
            ['time' => '16:00', 'cpu' => 55, 'memory' => 65, 'requests' => 650],
            ['time' => '18:00', 'cpu' => 40, 'memory' => 58, 'requests' => 480],
            ['time' => '20:00', 'cpu' => 30, 'memory' => 50, 'requests' => 320],
            ['time' => '22:00', 'cpu' => 20, 'memory' => 42, 'requests' => 220]
        ];
    } else {
        return [
            ['time' => 'الأحد', 'cpu' => 35, 'memory' => 48, 'requests' => 4200],
            ['time' => 'الإثنين', 'cpu' => 55, 'memory' => 62, 'requests' => 6500],
            ['time' => 'الثلاثاء', 'cpu' => 58, 'memory' => 65, 'requests' => 6800],
            ['time' => 'الأربعاء', 'cpu' => 62, 'memory' => 68, 'requests' => 7200],
            ['time' => 'الخميس', 'cpu' => 60, 'memory' => 66, 'requests' => 7000],
            ['time' => 'الجمعة', 'cpu' => 25, 'memory' => 40, 'requests' => 2800],
            ['time' => 'السبت', 'cpu' => 20, 'memory' => 38, 'requests' => 2200]
        ];
    }
}

function get_error_logs($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على سجلات الأخطاء
    return [
        [
            'id' => 1,
            'error_code' => 500,
            'message' => 'Internal Server Error: Database connection failed',
            'url' => '/api/student/courses',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'ip' => '192.168.1.1',
            'timestamp' => '2025-05-21 10:30:45',
            'count' => 3
        ],
        [
            'id' => 2,
            'error_code' => 404,
            'message' => 'Not Found: The requested resource was not found',
            'url' => '/api/teacher/assignments/123',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
            'ip' => '192.168.1.2',
            'timestamp' => '2025-05-21 09:15:22',
            'count' => 12
        ],
        [
            'id' => 3,
            'error_code' => 403,
            'message' => 'Forbidden: Access denied to the requested resource',
            'url' => '/api/admin/users',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'ip' => '192.168.1.3',
            'timestamp' => '2025-05-20 14:45:10',
            'count' => 5
        ],
        [
            'id' => 4,
            'error_code' => 401,
            'message' => 'Unauthorized: Authentication required',
            'url' => '/api/college/reports',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'ip' => '192.168.1.4',
            'timestamp' => '2025-05-20 11:20:33',
            'count' => 8
        ],
        [
            'id' => 5,
            'error_code' => 500,
            'message' => 'Internal Server Error: File upload failed',
            'url' => '/api/student/assignments/submit',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'ip' => '192.168.1.5',
            'timestamp' => '2025-05-19 16:05:18',
            'count' => 4
        ]
    ];
}

function get_api_usage($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على استخدام واجهة برمجة التطبيقات
    return [
        ['endpoint' => '/api/auth/login', 'requests' => 12500, 'avg_response_time' => 0.45, 'error_rate' => 0.2],
        ['endpoint' => '/api/student/courses', 'requests' => 8700, 'avg_response_time' => 0.85, 'error_rate' => 0.3],
        ['endpoint' => '/api/student/assignments', 'requests' => 7200, 'avg_response_time' => 0.92, 'error_rate' => 0.5],
        ['endpoint' => '/api/teacher/courses', 'requests' => 5400, 'avg_response_time' => 0.78, 'error_rate' => 0.4],
        ['endpoint' => '/api/teacher/grades', 'requests' => 4800, 'avg_response_time' => 1.25, 'error_rate' => 0.6],
        ['endpoint' => '/api/college/reports', 'requests' => 3200, 'avg_response_time' => 1.85, 'error_rate' => 0.8],
        ['endpoint' => '/api/admin/users', 'requests' => 2100, 'avg_response_time' => 0.65, 'error_rate' => 0.3],
        ['endpoint' => '/api/admin/logs', 'requests' => 1800, 'avg_response_time' => 2.15, 'error_rate' => 0.4]
    ];
}

function get_storage_usage($db) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على استخدام التخزين
    return [
        ['type' => 'قاعدة البيانات', 'size' => 1.8, 'percentage' => 25],
        ['type' => 'ملفات المستخدمين', 'size' => 3.5, 'percentage' => 48],
        ['type' => 'ملفات النظام', 'size' => 0.8, 'percentage' => 11],
        ['type' => 'النسخ الاحتياطية', 'size' => 1.2, 'percentage' => 16]
    ];
}

function get_backup_history($db, $period) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على سجل النسخ الاحتياطي
    return [
        [
            'id' => 1,
            'name' => 'backup_20250521_full.zip',
            'type' => 'كامل',
            'size' => 2.8,
            'status' => 'مكتمل',
            'duration' => 185,
            'timestamp' => '2025-05-21 02:00:00'
        ],
        [
            'id' => 2,
            'name' => 'backup_20250520_incremental.zip',
            'type' => 'تزايدي',
            'size' => 0.5,
            'status' => 'مكتمل',
            'duration' => 45,
            'timestamp' => '2025-05-20 02:00:00'
        ],
        [
            'id' => 3,
            'name' => 'backup_20250519_incremental.zip',
            'type' => 'تزايدي',
            'size' => 0.4,
            'status' => 'مكتمل',
            'duration' => 42,
            'timestamp' => '2025-05-19 02:00:00'
        ],
        [
            'id' => 4,
            'name' => 'backup_20250518_incremental.zip',
            'type' => 'تزايدي',
            'size' => 0.6,
            'status' => 'مكتمل',
            'duration' => 50,
            'timestamp' => '2025-05-18 02:00:00'
        ],
        [
            'id' => 5,
            'name' => 'backup_20250517_incremental.zip',
            'type' => 'تزايدي',
            'size' => 0.3,
            'status' => 'مكتمل',
            'duration' => 38,
            'timestamp' => '2025-05-17 02:00:00'
        ]
    ];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('system_reports'); ?></title>
    
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
        
        .stats-icon.danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
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
        
        /* تنسيقات شريط التقدم */
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: rgba(0, 0, 0, 0.05);
            margin-top: 0.5rem;
        }
        
        .theme-dark .progress {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .progress-bar {
            border-radius: 4px;
        }
        
        .progress-bar-primary {
            background-color: var(--primary-color);
        }
        
        .progress-bar-secondary {
            background-color: #669bbc;
        }
        
        .progress-bar-success {
            background-color: #198754;
        }
        
        .progress-bar-warning {
            background-color: #ffc107;
        }
        
        .progress-bar-danger {
            background-color: #dc3545;
        }
        
        /* تنسيقات الدوائر النسبية */
        .progress-circle {
            position: relative;
            width: 120px;
            height: 120px;
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
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .progress-circle-label {
            font-size: 0.875rem;
            color: var(--gray-color);
            margin-top: 0.5rem;
            text-align: center;
        }
        
        .progress-circle-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            clip: rect(0, 120px, 120px, 60px);
        }
        
        .progress-circle-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            clip: rect(0, 60px, 120px, 0);
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
        
        /* تنسيقات مخطط الدائرة */
        .storage-chart-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }
        
        .storage-chart-info {
            margin-top: 2rem;
        }
        
        .storage-chart-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .storage-chart-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            margin-right: 0.75rem;
        }
        
        [dir="rtl"] .storage-chart-color {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        .storage-chart-label {
            flex: 1;
            font-weight: 500;
        }
        
        .storage-chart-value {
            font-weight: 600;
            margin-left: 0.75rem;
        }
        
        [dir="rtl"] .storage-chart-value {
            margin-left: 0;
            margin-right: 0.75rem;
        }
        
        .storage-chart-progress {
            flex: 1;
            height: 6px;
            margin: 0 0.75rem;
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
                    <a class="nav-link active" href="admin_reports_system.php">
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
            <h1 class="page-title"><?php echo t('system_reports'); ?></h1>
            <p class="page-subtitle"><?php echo t('monitor_system_performance_and_health'); ?></p>
        </div>
        
        <!-- فلاتر التقارير -->
        <div class="filter-bar">
            <div class="filter-label"><?php echo t('time_period'); ?>:</div>
            <div class="filter-options">
                <a href="?period=day" class="filter-option <?php echo $period === 'day' ? 'active' : ''; ?>"><?php echo t('last_24_hours'); ?></a>
                <a href="?period=week" class="filter-option <?php echo $period === 'week' ? 'active' : ''; ?>"><?php echo t('last_week'); ?></a>
                <a href="?period=month" class="filter-option <?php echo $period === 'month' ? 'active' : ''; ?>"><?php echo t('last_month'); ?></a>
                <a href="?period=year" class="filter-option <?php echo $period === 'year' ? 'active' : ''; ?>"><?php echo t('last_year'); ?></a>
            </div>
        </div>
        
        <!-- إحصائيات النظام -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('uptime'); ?></div>
                        <div class="stats-value"><?php echo $system_stats['uptime']; ?>%</div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo $system_stats['active_sessions']; ?> <?php echo t('active_sessions'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon secondary">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('avg_response_time'); ?></div>
                        <div class="stats-value"><?php echo $system_stats['average_response_time']; ?> <?php echo t('seconds'); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo number_format($system_stats['total_requests']); ?> <?php echo t('total_requests'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('error_rate'); ?></div>
                        <div class="stats-value"><?php echo $system_stats['error_rate']; ?>%</div>
                        <div class="stats-change negative">
                            <i class="fas fa-arrow-down"></i> <?php echo $system_stats['peak_sessions']; ?> <?php echo t('peak_sessions'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon success">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('database_size'); ?></div>
                        <div class="stats-value"><?php echo $system_stats['database_size']; ?> <?php echo t('gb'); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo number_format($system_stats['total_files']); ?> <?php echo t('total_files'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- حمل الخادم واستخدام التخزين -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('server_load'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="serverLoadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('storage_usage'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="storage-chart-container">
                            <canvas id="storageChart"></canvas>
                        </div>
                        <div class="storage-chart-info">
                            <?php foreach ($storage_usage as $index => $item): ?>
                                <div class="storage-chart-item">
                                    <div class="storage-chart-color" style="background-color: <?php echo getStorageColor($index); ?>"></div>
                                    <div class="storage-chart-label"><?php echo $item['type']; ?></div>
                                    <div class="progress storage-chart-progress">
                                        <div class="progress-bar" style="width: <?php echo $item['percentage']; ?>%; background-color: <?php echo getStorageColor($index); ?>"></div>
                                    </div>
                                    <div class="storage-chart-value"><?php echo $item['size']; ?> <?php echo t('gb'); ?></div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <span class="badge badge-primary"><?php echo array_sum(array_column($storage_usage, 'size')); ?> <?php echo t('gb_used'); ?></span>
                                <span class="badge badge-secondary">7.3 <?php echo t('gb_total'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- استخدام واجهة برمجة التطبيقات وسجلات الأخطاء -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('api_usage'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('endpoint'); ?></th>
                                        <th><?php echo t('requests'); ?></th>
                                        <th><?php echo t('avg_response_time'); ?></th>
                                        <th><?php echo t('error_rate'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($api_usage as $api): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle primary me-2">
                                                        <i class="fas fa-link"></i>
                                                    </div>
                                                    <span><?php echo $api['endpoint']; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo number_format($api['requests']); ?></span>
                                                    <div class="progress flex-grow-1" style="width: 100px;">
                                                        <div class="progress-bar progress-bar-primary" role="progressbar" style="width: <?php echo min(100, ($api['requests'] / 15000) * 100); ?>%" aria-valuenow="<?php echo $api['requests']; ?>" aria-valuemin="0" aria-valuemax="15000"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $api['avg_response_time']; ?> <?php echo t('seconds'); ?></span>
                                                    <div class="progress flex-grow-1" style="width: 100px;">
                                                        <div class="progress-bar <?php echo getResponseTimeClass($api['avg_response_time']); ?>" role="progressbar" style="width: <?php echo min(100, ($api['avg_response_time'] / 3) * 100); ?>%" aria-valuenow="<?php echo $api['avg_response_time']; ?>" aria-valuemin="0" aria-valuemax="3"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $api['error_rate']; ?>%</span>
                                                    <div class="progress flex-grow-1" style="width: 100px;">
                                                        <div class="progress-bar <?php echo getErrorRateClass($api['error_rate']); ?>" role="progressbar" style="width: <?php echo min(100, ($api['error_rate'] / 2) * 100); ?>%" aria-valuenow="<?php echo $api['error_rate']; ?>" aria-valuemin="0" aria-valuemax="2"></div>
                                                    </div>
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
        </div>
        
        <!-- سجلات الأخطاء وسجل النسخ الاحتياطي -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('error_logs'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('error_code'); ?></th>
                                        <th><?php echo t('message'); ?></th>
                                        <th><?php echo t('count'); ?></th>
                                        <th><?php echo t('timestamp'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($error_logs as $error): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php echo getErrorCodeClass($error['error_code']); ?>"><?php echo $error['error_code']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="fw-medium"><?php echo $error['message']; ?></div>
                                                        <div class="small text-muted"><?php echo $error['url']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $error['count']; ?></span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($error['timestamp'])); ?></td>
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
                        <h5 class="card-title"><?php echo t('backup_history'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('name'); ?></th>
                                        <th><?php echo t('type'); ?></th>
                                        <th><?php echo t('size'); ?></th>
                                        <th><?php echo t('timestamp'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backup_history as $backup): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-circle secondary me-2">
                                                        <i class="fas fa-file-archive"></i>
                                                    </div>
                                                    <span><?php echo $backup['name']; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $backup['type'] === 'كامل' ? 'badge-primary' : 'badge-secondary'; ?>"><?php echo $backup['type']; ?></span>
                                            </td>
                                            <td><?php echo $backup['size']; ?> <?php echo t('gb'); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($backup['timestamp'])); ?></td>
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
        // دوال مساعدة للحصول على الألوان
        <?php
        function getStorageColor($index) {
            $colors = ['#003049', '#669bbc', '#9bc1bc', '#ed6a5a'];
            return $colors[$index % count($colors)];
        }
        
        function getResponseTimeClass($time) {
            if ($time < 0.5) return 'progress-bar-success';
            if ($time < 1.0) return 'progress-bar-primary';
            if ($time < 1.5) return 'progress-bar-secondary';
            if ($time < 2.0) return 'progress-bar-warning';
            return 'progress-bar-danger';
        }
        
        function getErrorRateClass($rate) {
            if ($rate < 0.3) return 'progress-bar-success';
            if ($rate < 0.6) return 'progress-bar-primary';
            if ($rate < 0.9) return 'progress-bar-warning';
            return 'progress-bar-danger';
        }
        
        function getErrorCodeClass($code) {
            if ($code >= 500) return 'badge-danger';
            if ($code >= 400) return 'badge-warning';
            if ($code >= 300) return 'badge-secondary';
            return 'badge-success';
        }
        ?>
        
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
                // رسم بياني لحمل الخادم
                const serverLoadCtx = document.getElementById('serverLoadChart').getContext('2d');
                const serverLoadData = <?php echo json_encode($server_load); ?>;
                
                window.serverLoadChart = new Chart(serverLoadCtx, {
                    type: 'line',
                    data: {
                        labels: serverLoadData.map(item => item.time),
                        datasets: [
                            {
                                label: '<?php echo t('cpu_usage'); ?>',
                                data: serverLoadData.map(item => item.cpu),
                                borderColor: '#003049',
                                backgroundColor: 'rgba(0, 48, 73, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y'
                            },
                            {
                                label: '<?php echo t('memory_usage'); ?>',
                                data: serverLoadData.map(item => item.memory),
                                borderColor: '#669bbc',
                                backgroundColor: 'rgba(102, 155, 188, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y'
                            },
                            {
                                label: '<?php echo t('requests'); ?>',
                                data: serverLoadData.map(item => item.requests),
                                borderColor: '#ed6a5a',
                                backgroundColor: 'rgba(237, 106, 90, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y1'
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
                                position: 'left',
                                grid: {
                                    color: getGridColor()
                                },
                                ticks: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    },
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                min: 0,
                                max: 100
                            },
                            y1: {
                                position: 'right',
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: getTextColor(),
                                    font: {
                                        family: 'Cairo'
                                    }
                                },
                                min: 0,
                                max: Math.max(...serverLoadData.map(item => item.requests)) * 1.2
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
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.dataset.label || '';
                                        const value = context.raw || 0;
                                        if (context.dataset.yAxisID === 'y') {
                                            return `${label}: ${value}%`;
                                        }
                                        return `${label}: ${value}`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم بياني لاستخدام التخزين
                const storageCtx = document.getElementById('storageChart').getContext('2d');
                const storageData = <?php echo json_encode($storage_usage); ?>;
                
                window.storageChart = new Chart(storageCtx, {
                    type: 'doughnut',
                    data: {
                        labels: storageData.map(item => item.type),
                        datasets: [{
                            data: storageData.map(item => item.size),
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
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} GB (${percentage}%)`;
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
                
                // تحديث رسم بياني حمل الخادم
                if (window.serverLoadChart) {
                    window.serverLoadChart.options.scales.x.grid.color = gridColor;
                    window.serverLoadChart.options.scales.y.grid.color = gridColor;
                    window.serverLoadChart.options.scales.x.ticks.color = textColor;
                    window.serverLoadChart.options.scales.y.ticks.color = textColor;
                    window.serverLoadChart.options.scales.y1.ticks.color = textColor;
                    window.serverLoadChart.options.plugins.legend.labels.color = textColor;
                    window.serverLoadChart.update();
                }
                
                // تحديث رسم بياني استخدام التخزين
                if (window.storageChart) {
                    window.storageChart.update();
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
