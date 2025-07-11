<?php
/**
 * صفحة لوحة تحكم المشرف في نظام UniverBoard
 * تتيح للمشرف الوصول إلى جميع وظائف إدارة النظام
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

// الحصول على إحصائيات النظام
$stats = get_system_stats($db);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_admin_info($db, $admin_id) {
    return [
        'id' => $admin_id,
        'name' => 'أحمد محمد',
        'email' => 'admin@univerboard.com',
        'profile_image' => '../assets/images/admin.jpg',
        'role' => 'مشرف النظام',
        'last_login' => '2025-05-20 14:30:45'
    ];
}

function get_system_stats($db) {
    return [
        'colleges_count' => 5,
        'departments_count' => 25,
        'programs_count' => 45,
        'courses_count' => 320,
        'teachers_count' => 180,
        'students_count' => 3500,
        'active_users_count' => 2800,
        'pending_users_count' => 120,
        'total_logins_today' => 850,
        'system_uptime' => '99.98%',
        'disk_usage' => '42%',
        'memory_usage' => '38%',
        'cpu_usage' => '25%',
        'recent_activities' => [
            [
                'user' => 'د. خالد العمري',
                'action' => 'تسجيل دخول',
                'time' => '10:45 صباحاً',
                'date' => 'اليوم'
            ],
            [
                'user' => 'سارة أحمد',
                'action' => 'تحديث ملف شخصي',
                'time' => '10:30 صباحاً',
                'date' => 'اليوم'
            ],
            [
                'user' => 'كلية الهندسة',
                'action' => 'إضافة مقرر جديد',
                'time' => '10:15 صباحاً',
                'date' => 'اليوم'
            ],
            [
                'user' => 'د. محمد علي',
                'action' => 'تحديث درجات',
                'time' => '09:50 صباحاً',
                'date' => 'اليوم'
            ],
            [
                'user' => 'عمر خالد',
                'action' => 'تسجيل في مقرر',
                'time' => '09:30 صباحاً',
                'date' => 'اليوم'
            ]
        ],
        'system_alerts' => [
            [
                'type' => 'warning',
                'message' => 'تحديث النظام متوفر (الإصدار 2.5.1)',
                'time' => '11:00 صباحاً',
                'date' => 'اليوم'
            ],
            [
                'type' => 'info',
                'message' => 'تم إكمال النسخ الاحتياطي اليومي بنجاح',
                'time' => '03:00 صباحاً',
                'date' => 'اليوم'
            ],
            [
                'type' => 'success',
                'message' => 'تم تحديث قاعدة البيانات بنجاح',
                'time' => '02:30 صباحاً',
                'date' => 'اليوم'
            ]
        ]
    ];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('dashboard'); ?></title>
    
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    
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
        
        /* تنسيقات لوحة التحكم */
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
        
        /* تنسيقات البطاقات الإحصائية */
        .stats-card {
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            background-color: white;
            height: 100%;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .theme-dark .stats-card {
            background-color: var(--dark-bg);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stats-icon {
            font-size: 2rem;
            margin-right: 1rem;
            color: var(--primary-color);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(0, 48, 73, 0.1);
        }
        
        [dir="rtl"] .stats-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .stats-info {
            flex: 1;
        }
        
        .stats-number {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }
        
        .stats-title {
            font-size: 1rem;
            color: var(--gray-color);
            margin: 0;
        }
        
        /* تنسيقات الرسوم البيانية */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* تنسيقات جدول الأنشطة الأخيرة */
        .activity-table {
            width: 100%;
        }
        
        .activity-table th {
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .activity-table th {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .activity-table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }
        
        .activity-table tr:not(:last-child) td {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .activity-table tr:not(:last-child) td {
            border-color: rgba(255, 255, 255, 0.05);
        }
        
        .activity-user {
            font-weight: 500;
        }
        
        .activity-action {
            color: var(--gray-color);
        }
        
        .activity-time {
            color: var(--gray-color);
            font-size: 0.9rem;
        }
        
        /* تنسيقات التنبيهات */
        .alert-item {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .alert-item:last-child {
            margin-bottom: 0;
        }
        
        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
        }
        
        [dir="rtl"] .alert-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .alert-info {
            flex: 1;
        }
        
        .alert-message {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .alert-time {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .alert-warning .alert-icon {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .alert-info-item {
            background-color: rgba(13, 202, 240, 0.1);
        }
        
        .alert-info-item .alert-icon {
            background-color: rgba(13, 202, 240, 0.2);
            color: #0dcaf0;
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
        }
        
        .alert-success .alert-icon {
            background-color: rgba(25, 135, 84, 0.2);
            color: #198754;
        }
        
        /* تنسيقات مؤشرات الأداء */
        .performance-item {
            margin-bottom: 1.5rem;
        }
        
        .performance-item:last-child {
            margin-bottom: 0;
        }
        
        .performance-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .performance-title {
            font-weight: 500;
            margin: 0;
        }
        
        .performance-value {
            font-weight: 600;
        }
        
        .progress {
            height: 0.75rem;
            border-radius: 1rem;
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .progress {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .progress-bar {
            border-radius: 1rem;
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
        
        /* تنسيقات الشارات */
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
        
        /* تنسيقات الروابط السريعة */
        .quick-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            height: 100%;
            text-decoration: none;
            color: var(--text-color);
        }
        
        .theme-dark .quick-link {
            background-color: var(--dark-bg);
        }
        
        .quick-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }
        
        .quick-link-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .quick-link-title {
            font-weight: 600;
            text-align: center;
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
                    <a class="nav-link active" href="admin_dashboard.php">
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
            <h1 class="page-title"><?php echo t('admin_dashboard'); ?></h1>
            <p class="page-subtitle"><?php echo t('welcome_back'); ?>, <?php echo $admin['name']; ?>! <?php echo t('last_login'); ?>: <?php echo $admin['last_login']; ?></p>
        </div>
        
        <!-- الروابط السريعة -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <a href="admin_users.php" class="quick-link">
                    <div class="quick-link-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="quick-link-title"><?php echo t('manage_users'); ?></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <a href="admin_colleges.php" class="quick-link">
                    <div class="quick-link-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="quick-link-title"><?php echo t('manage_colleges'); ?></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <a href="admin_reports_academic.php" class="quick-link">
                    <div class="quick-link-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="quick-link-title"><?php echo t('view_reports'); ?></div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <a href="admin_settings.php" class="quick-link">
                    <div class="quick-link-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="quick-link-title"><?php echo t('system_settings'); ?></div>
                </a>
            </div>
        </div>
        
        <!-- الإحصائيات -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo $stats['colleges_count']; ?></div>
                        <p class="stats-title"><?php echo t('colleges'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo $stats['teachers_count']; ?></div>
                        <p class="stats-title"><?php echo t('teachers'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo $stats['students_count']; ?></div>
                        <p class="stats-title"><?php echo t('students'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo $stats['courses_count']; ?></div>
                        <p class="stats-title"><?php echo t('courses'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- الرسوم البيانية -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i> <?php echo t('user_activity_overview'); ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartPeriodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo t('last_7_days'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartPeriodDropdown">
                                <li><a class="dropdown-item" href="#"><?php echo t('today'); ?></a></li>
                                <li><a class="dropdown-item active" href="#"><?php echo t('last_7_days'); ?></a></li>
                                <li><a class="dropdown-item" href="#"><?php echo t('last_30_days'); ?></a></li>
                                <li><a class="dropdown-item" href="#"><?php echo t('this_month'); ?></a></li>
                                <li><a class="dropdown-item" href="#"><?php echo t('this_year'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- تنبيهات النظام -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-bell me-2"></i> <?php echo t('system_alerts'); ?></h5>
                        <a href="#" class="btn btn-sm btn-outline-primary"><?php echo t('view_all'); ?></a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($stats['system_alerts'] as $alert): ?>
                            <div class="alert-item alert-<?php echo $alert['type']; ?>">
                                <div class="alert-icon">
                                    <i class="fas <?php 
                                        if ($alert['type'] === 'warning') echo 'fa-exclamation-triangle';
                                        elseif ($alert['type'] === 'info') echo 'fa-info-circle';
                                        elseif ($alert['type'] === 'success') echo 'fa-check-circle';
                                        else echo 'fa-bell';
                                    ?>"></i>
                                </div>
                                <div class="alert-info">
                                    <div class="alert-message"><?php echo $alert['message']; ?></div>
                                    <div class="alert-time"><?php echo $alert['time']; ?> - <?php echo $alert['date']; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- الأنشطة الأخيرة -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-history me-2"></i> <?php echo t('recent_activities'); ?></h5>
                        <a href="#" class="btn btn-sm btn-outline-primary"><?php echo t('view_all'); ?></a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th><?php echo t('user'); ?></th>
                                        <th><?php echo t('action'); ?></th>
                                        <th><?php echo t('time'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recent_activities'] as $activity): ?>
                                        <tr>
                                            <td class="activity-user"><?php echo $activity['user']; ?></td>
                                            <td class="activity-action"><?php echo $activity['action']; ?></td>
                                            <td class="activity-time"><?php echo $activity['time']; ?> - <?php echo $activity['date']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- مؤشرات أداء النظام -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-server me-2"></i> <?php echo t('system_performance'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="performance-item">
                            <div class="performance-header">
                                <h6 class="performance-title"><?php echo t('cpu_usage'); ?></h6>
                                <span class="performance-value"><?php echo $stats['cpu_usage']; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $stats['cpu_usage']; ?>" aria-valuenow="<?php echo intval($stats['cpu_usage']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        
                        <div class="performance-item">
                            <div class="performance-header">
                                <h6 class="performance-title"><?php echo t('memory_usage'); ?></h6>
                                <span class="performance-value"><?php echo $stats['memory_usage']; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['memory_usage']; ?>" aria-valuenow="<?php echo intval($stats['memory_usage']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        
                        <div class="performance-item">
                            <div class="performance-header">
                                <h6 class="performance-title"><?php echo t('disk_usage'); ?></h6>
                                <span class="performance-value"><?php echo $stats['disk_usage']; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $stats['disk_usage']; ?>" aria-valuenow="<?php echo intval($stats['disk_usage']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        
                        <div class="performance-item">
                            <div class="performance-header">
                                <h6 class="performance-title"><?php echo t('system_uptime'); ?></h6>
                                <span class="performance-value"><?php echo $stats['system_uptime']; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 99.98%" aria-valuenow="99.98" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <a href="admin_reports_system.php" class="btn btn-sm btn-outline-primary"><?php echo t('view_detailed_report'); ?></a>
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
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
                
                // إعادة رسم الرسوم البيانية عند تغيير المظهر
                renderCharts();
            });
            
            // رسم الرسوم البيانية
            renderCharts();
            
            function renderCharts() {
                const isDarkTheme = document.body.className.includes('theme-dark');
                const textColor = isDarkTheme ? '#ffffff' : '#333333';
                const gridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                
                // رسم بياني لنشاط المستخدمين
                const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
                
                // تدمير الرسم البياني السابق إذا كان موجوداً
                if (window.userActivityChart) {
                    window.userActivityChart.destroy();
                }
                
                window.userActivityChart = new Chart(userActivityCtx, {
                    type: 'line',
                    data: {
                        labels: ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                        datasets: [
                            {
                                label: 'الطلاب',
                                data: [650, 590, 800, 810, 760, 550, 400],
                                borderColor: '#003049',
                                backgroundColor: 'rgba(0, 48, 73, 0.1)',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'المعلمين',
                                data: [150, 200, 220, 210, 190, 120, 80],
                                borderColor: '#669bbc',
                                backgroundColor: 'rgba(102, 155, 188, 0.1)',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'الإداريين',
                                data: [50, 70, 65, 60, 55, 30, 20],
                                borderColor: '#ffc107',
                                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: textColor
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            y: {
                                grid: {
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                },
                                beginAtZero: true
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
