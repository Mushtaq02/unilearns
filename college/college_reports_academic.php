<?php
/**
 * صفحة التقارير الأكاديمية في نظام UniverBoard
 * تتيح لمسؤول الكلية عرض وتحليل التقارير الأكاديمية المختلفة
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

// تحديد نوع التقرير المطلوب
$report_type = isset($_GET['type']) ? filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) : 'gpa';

// تحديد الفلاتر (القسم، البرنامج، المستوى، الفصل الدراسي)
$selected_department_id = isset($_GET['department_id']) ? filter_input(INPUT_GET, 'department_id', FILTER_SANITIZE_NUMBER_INT) : null;
$selected_program_id = isset($_GET['program_id']) ? filter_input(INPUT_GET, 'program_id', FILTER_SANITIZE_NUMBER_INT) : null;
$selected_level = isset($_GET['level']) ? filter_input(INPUT_GET, 'level', FILTER_SANITIZE_NUMBER_INT) : null;
$selected_semester = isset($_GET['semester']) ? filter_input(INPUT_GET, 'semester', FILTER_SANITIZE_STRING) : null;

// الحصول على قائمة الأقسام في الكلية
$departments = get_college_departments($db, $college_id);

// الحصول على قائمة البرامج في الكلية (بناءً على القسم المحدد إن وجد)
if ($selected_department_id) {
    $programs = get_department_programs($db, $selected_department_id);
} else {
    $programs = get_college_programs($db, $college_id);
}

// الحصول على بيانات التقرير بناءً على الفلاتر ونوع التقرير
$report_filters = [
    'college_id' => $college_id,
    'department_id' => $selected_department_id,
    'program_id' => $selected_program_id,
    'level' => $selected_level,
    'semester' => $selected_semester
];

// بيانات وهمية للتقارير (يجب استبدالها بالبيانات الفعلية من قاعدة البيانات)
// تقرير المعدل التراكمي
$gpa_report_data = [
    'average_gpa' => 3.45,
    'highest_gpa' => 4.00,
    'lowest_gpa' => 2.10,
    'gpa_distribution' => [
        '4.00 - 3.75' => 15,
        '3.74 - 3.50' => 22,
        '3.49 - 3.00' => 35,
        '2.99 - 2.50' => 18,
        '2.49 - 2.00' => 8,
        'أقل من 2.00' => 2
    ],
    'gpa_by_level' => [
        '1' => 3.25,
        '2' => 3.35,
        '3' => 3.48,
        '4' => 3.52,
        '5' => 3.60,
        '6' => 3.65,
        '7' => 3.70,
        '8' => 3.75
    ],
    'gpa_by_gender' => [
        'ذكر' => 3.40,
        'أنثى' => 3.55
    ],
    'top_students' => [
        ['id' => 1001, 'name' => 'أحمد محمد علي', 'gpa' => 4.00, 'level' => 4, 'program' => 'علوم الحاسب'],
        ['id' => 1002, 'name' => 'سارة خالد العمري', 'gpa' => 3.98, 'level' => 5, 'program' => 'هندسة البرمجيات'],
        ['id' => 1003, 'name' => 'عبدالله فهد السالم', 'gpa' => 3.95, 'level' => 6, 'program' => 'نظم المعلومات'],
        ['id' => 1004, 'name' => 'نورة سعد القحطاني', 'gpa' => 3.93, 'level' => 3, 'program' => 'علوم الحاسب'],
        ['id' => 1005, 'name' => 'محمد عبدالرحمن الشهري', 'gpa' => 3.90, 'level' => 7, 'program' => 'هندسة البرمجيات']
    ]
];

// تقرير معدلات النجاح والرسوب
$pass_fail_report_data = [
    'overall_pass_rate' => 92.5,
    'overall_fail_rate' => 7.5,
    'pass_fail_by_level' => [
        '1' => ['pass' => 88.0, 'fail' => 12.0],
        '2' => ['pass' => 90.5, 'fail' => 9.5],
        '3' => ['pass' => 92.0, 'fail' => 8.0],
        '4' => ['pass' => 93.5, 'fail' => 6.5],
        '5' => ['pass' => 94.0, 'fail' => 6.0],
        '6' => ['pass' => 95.5, 'fail' => 4.5],
        '7' => ['pass' => 96.0, 'fail' => 4.0],
        '8' => ['pass' => 97.5, 'fail' => 2.5]
    ],
    'pass_fail_by_gender' => [
        'ذكر' => ['pass' => 91.0, 'fail' => 9.0],
        'أنثى' => ['pass' => 94.0, 'fail' => 6.0]
    ],
    'courses_with_highest_fail_rates' => [
        ['code' => 'MATH101', 'name' => 'حساب التفاضل والتكامل 1', 'fail_rate' => 18.5],
        ['code' => 'PHYS101', 'name' => 'الفيزياء العامة 1', 'fail_rate' => 15.2],
        ['code' => 'CS240', 'name' => 'تراكيب البيانات', 'fail_rate' => 14.8],
        ['code' => 'CS380', 'name' => 'نظرية الحوسبة', 'fail_rate' => 13.5],
        ['code' => 'MATH241', 'name' => 'الجبر الخطي', 'fail_rate' => 12.9]
    ]
];

// تقرير توزيع الدرجات
$grades_distribution_report_data = [
    'overall_distribution' => [
        'A+' => 12,
        'A' => 18,
        'B+' => 22,
        'B' => 25,
        'C+' => 15,
        'C' => 10,
        'D+' => 5,
        'D' => 3,
        'F' => 5
    ],
    'distribution_by_level' => [
        '1' => ['A+' => 8, 'A' => 15, 'B+' => 20, 'B' => 25, 'C+' => 18, 'C' => 12, 'D+' => 7, 'D' => 5, 'F' => 8],
        '2' => ['A+' => 10, 'A' => 16, 'B+' => 21, 'B' => 24, 'C+' => 16, 'C' => 11, 'D+' => 6, 'D' => 4, 'F' => 7],
        '3' => ['A+' => 11, 'A' => 17, 'B+' => 22, 'B' => 25, 'C+' => 15, 'C' => 10, 'D+' => 5, 'D' => 3, 'F' => 6],
        '4' => ['A+' => 12, 'A' => 18, 'B+' => 23, 'B' => 24, 'C+' => 14, 'C' => 9, 'D+' => 4, 'D' => 3, 'F' => 5],
        '5' => ['A+' => 13, 'A' => 19, 'B+' => 24, 'B' => 23, 'C+' => 13, 'C' => 8, 'D+' => 4, 'D' => 2, 'F' => 4],
        '6' => ['A+' => 14, 'A' => 20, 'B+' => 25, 'B' => 22, 'C+' => 12, 'C' => 7, 'D+' => 3, 'D' => 2, 'F' => 3],
        '7' => ['A+' => 15, 'A' => 21, 'B+' => 26, 'B' => 21, 'C+' => 11, 'C' => 6, 'D+' => 3, 'D' => 1, 'F' => 2],
        '8' => ['A+' => 16, 'A' => 22, 'B+' => 27, 'B' => 20, 'C+' => 10, 'C' => 5, 'D+' => 2, 'D' => 1, 'F' => 1]
    ],
    'courses_with_highest_a_rates' => [
        ['code' => 'CS499', 'name' => 'مشروع التخرج', 'a_rate' => 45.5],
        ['code' => 'CS490', 'name' => 'تدريب تعاوني', 'a_rate' => 42.8],
        ['code' => 'CS460', 'name' => 'الذكاء الاصطناعي', 'a_rate' => 38.5],
        ['code' => 'CS430', 'name' => 'تطوير تطبيقات الويب', 'a_rate' => 36.2],
        ['code' => 'CS450', 'name' => 'أمن المعلومات', 'a_rate' => 35.0]
    ]
];

// تقرير التقدم الأكاديمي
$academic_progress_report_data = [
    'average_completion_rate' => 85.5,
    'students_at_risk' => [
        ['id' => 2001, 'name' => 'خالد محمد العتيبي', 'gpa' => 1.95, 'level' => 3, 'program' => 'علوم الحاسب', 'failed_courses' => 4],
        ['id' => 2002, 'name' => 'فهد سعد الدوسري', 'gpa' => 1.85, 'level' => 2, 'program' => 'نظم المعلومات', 'failed_courses' => 5],
        ['id' => 2003, 'name' => 'منيرة خالد العنزي', 'gpa' => 1.90, 'level' => 4, 'program' => 'هندسة البرمجيات', 'failed_courses' => 3],
        ['id' => 2004, 'name' => 'عبدالعزيز فهد الحربي', 'gpa' => 1.80, 'level' => 3, 'program' => 'علوم الحاسب', 'failed_courses' => 6],
        ['id' => 2005, 'name' => 'نوف سلطان القحطاني', 'gpa' => 1.92, 'level' => 5, 'program' => 'نظم المعلومات', 'failed_courses' => 4]
    ],
    'progress_by_level' => [
        '1' => 75.0,
        '2' => 78.5,
        '3' => 82.0,
        '4' => 85.5,
        '5' => 88.0,
        '6' => 90.5,
        '7' => 93.0,
        '8' => 95.5
    ],
    'graduation_rate' => 88.5,
    'average_time_to_graduate' => 4.5 // بالسنوات
];

// إغلاق اتصال قاعدة البيانات
$dsn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('academic_reports'); ?></title>
    
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
        
        .filter-container {
            margin-bottom: 1.5rem;
        }
        
        .nav-tabs .nav-link {
            color: var(--gray-color);
            border: none;
            border-bottom: 2px solid transparent;
            padding: 0.75rem 1.25rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background-color: transparent;
        }
        
        .tab-content {
            padding-top: 1.5rem;
        }
        
        .stat-card {
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            height: 100%;
        }
        
        .theme-dark .stat-card {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .stat-card-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .stat-card-label {
            font-size: 1rem;
            color: var(--gray-color);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1.5rem;
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
        
        .progress {
            height: 0.75rem;
            border-radius: 0.5rem;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
        
        .progress-bar-success {
            background-color: #28a745;
        }
        
        .progress-bar-warning {
            background-color: #ffc107;
        }
        
        .progress-bar-danger {
            background-color: #dc3545;
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
        
        .badge-gpa-high {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-gpa-high {
            background-color: rgba(40, 167, 69, 0.3);
        }
        
        .badge-gpa-medium {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-gpa-medium {
            background-color: rgba(255, 193, 7, 0.3);
        }
        
        .badge-gpa-low {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .theme-dark .badge-gpa-low {
            background-color: rgba(220, 53, 69, 0.3);
        }
        
        .report-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .report-action {
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
        
        .report-action:hover {
            transform: translateY(-2px);
        }
        
        .report-action-print {
            background-color: #17a2b8;
        }
        
        .report-action-export {
            background-color: #28a745;
        }
        
        .report-action-share {
            background-color: #6c757d;
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
                    <a class="nav-link active" href="college_reports_academic.php">
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
                <!-- نفس عناصر شريط التنقل العلوي من الصفحات السابقة -->
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
                            <li><a class="dropdown-item" href="?lang=ar&type=<?php echo $report_type; ?>">العربية</a></li>
                            <li><a class="dropdown-item" href="?lang=en&type=<?php echo $report_type; ?>">English</a></li>
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
                    <h1 class="page-title"><?php echo t('academic_reports'); ?></h1>
                    <p class="page-subtitle"><?php echo t('view_and_analyze_academic_reports'); ?></p>
                </div>
                <div class="report-actions">
                    <a href="#" class="report-action report-action-print" title="<?php echo t('print_report'); ?>">
                        <i class="fas fa-print"></i>
                    </a>
                    <a href="#" class="report-action report-action-export" title="<?php echo t('export_report'); ?>">
                        <i class="fas fa-file-export"></i>
                    </a>
                    <a href="#" class="report-action report-action-share" title="<?php echo t('share_report'); ?>">
                        <i class="fas fa-share-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- فلتر التقارير -->
        <div class="filter-container">
            <div class="card">
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <input type="hidden" name="type" value="<?php echo $report_type; ?>">
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
                            <label for="semester_filter" class="form-label"><?php echo t('filter_by_semester'); ?></label>
                            <select class="form-select" id="semester_filter" name="semester">
                                <option value=""><?php echo t('all_semesters'); ?></option>
                                <option value="first_2024" <?php echo $selected_semester == 'first_2024' ? 'selected' : ''; ?>><?php echo t('first_semester_2024'); ?></option>
                                <option value="second_2024" <?php echo $selected_semester == 'second_2024' ? 'selected' : ''; ?>><?php echo t('second_semester_2024'); ?></option>
                                <option value="summer_2024" <?php echo $selected_semester == 'summer_2024' ? 'selected' : ''; ?>><?php echo t('summer_semester_2024'); ?></option>
                                <option value="first_2023" <?php echo $selected_semester == 'first_2023' ? 'selected' : ''; ?>><?php echo t('first_semester_2023'); ?></option>
                                <option value="second_2023" <?php echo $selected_semester == 'second_2023' ? 'selected' : ''; ?>><?php echo t('second_semester_2023'); ?></option>
                                <option value="summer_2023" <?php echo $selected_semester == 'summer_2023' ? 'selected' : ''; ?>><?php echo t('summer_semester_2023'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> <?php echo t('filter'); ?>
                            </button>
                            <a href="college_reports_academic.php?type=<?php echo $report_type; ?>" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> <?php echo t('reset'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- أنواع التقارير -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $report_type === 'gpa' ? 'active' : ''; ?>" href="?type=gpa<?php echo build_query_string(['type']); ?>">
                            <?php echo t('gpa_report'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $report_type === 'pass_fail' ? 'active' : ''; ?>" href="?type=pass_fail<?php echo build_query_string(['type']); ?>">
                            <?php echo t('pass_fail_report'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $report_type === 'grades_distribution' ? 'active' : ''; ?>" href="?type=grades_distribution<?php echo build_query_string(['type']); ?>">
                            <?php echo t('grades_distribution_report'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $report_type === 'academic_progress' ? 'active' : ''; ?>" href="?type=academic_progress<?php echo build_query_string(['type']); ?>">
                            <?php echo t('academic_progress_report'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- تقرير المعدل التراكمي -->
                    <div class="tab-pane fade <?php echo $report_type === 'gpa' ? 'show active' : ''; ?>" id="gpaReportTab">
                        <?php if ($report_type === 'gpa'): ?>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $gpa_report_data['average_gpa']; ?></div>
                                        <div class="stat-card-label"><?php echo t('average_gpa'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $gpa_report_data['highest_gpa']; ?></div>
                                        <div class="stat-card-label"><?php echo t('highest_gpa'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $gpa_report_data['lowest_gpa']; ?></div>
                                        <div class="stat-card-label"><?php echo t('lowest_gpa'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo t('gpa_distribution'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="gpaDistributionChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo t('gpa_by_level'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="gpaByLevelChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo t('gpa_by_gender'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="gpaByGenderChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo t('top_students'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo t('student_id'); ?></th>
                                                            <th><?php echo t('name'); ?></th>
                                                            <th><?php echo t('gpa'); ?></th>
                                                            <th><?php echo t('level'); ?></th>
                                                            <th><?php echo t('program'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($gpa_report_data['top_students'] as $student): ?>
                                                            <tr>
                                                                <td><?php echo $student['id']; ?></td>
                                                                <td><?php echo $student['name']; ?></td>
                                                                <td><span class="badge-gpa-high"><?php echo $student['gpa']; ?></span></td>
                                                                <td><span class="badge-level"><?php echo t('level') . ' ' . $student['level']; ?></span></td>
                                                                <td><span class="badge-program"><?php echo $student['program']; ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- تقرير معدلات النجاح والرسوب -->
                    <div class="tab-pane fade <?php echo $report_type === 'pass_fail' ? 'show active' : ''; ?>" id="passFailReportTab">
                        <?php if ($report_type === 'pass_fail'): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $pass_fail_report_data['overall_pass_rate']; ?>%</div>
                                        <div class="stat-card-label"><?php echo t('overall_pass_rate'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $pass_fail_report_data['overall_fail_rate']; ?>%</div>
                                        <div class="stat-card-label"><?php echo t('overall_fail_rate'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo t('pass_fail_by_level'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="passFailByLevelChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo t('pass_fail_by_gender'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="passFailByGenderChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('courses_with_highest_fail_rates'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo t('course_code'); ?></th>
                                                    <th><?php echo t('course_name'); ?></th>
                                                    <th><?php echo t('fail_rate'); ?></th>
                                                    <th><?php echo t('visualization'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pass_fail_report_data['courses_with_highest_fail_rates'] as $course): ?>
                                                    <tr>
                                                        <td><?php echo $course['code']; ?></td>
                                                        <td><?php echo $course['name']; ?></td>
                                                        <td><?php echo $course['fail_rate']; ?>%</td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $course['fail_rate']; ?>%" aria-valuenow="<?php echo $course['fail_rate']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- تقرير توزيع الدرجات -->
                    <div class="tab-pane fade <?php echo $report_type === 'grades_distribution' ? 'show active' : ''; ?>" id="gradesDistributionReportTab">
                        <?php if ($report_type === 'grades_distribution'): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('overall_grades_distribution'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="overallGradesDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('grades_distribution_by_level'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <select class="form-select mb-3" id="levelSelector" onchange="updateGradesDistributionByLevelChart()">
                                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo t('level') . ' ' . $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="gradesDistributionByLevelChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('courses_with_highest_a_rates'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo t('course_code'); ?></th>
                                                    <th><?php echo t('course_name'); ?></th>
                                                    <th><?php echo t('a_rate'); ?></th>
                                                    <th><?php echo t('visualization'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($grades_distribution_report_data['courses_with_highest_a_rates'] as $course): ?>
                                                    <tr>
                                                        <td><?php echo $course['code']; ?></td>
                                                        <td><?php echo $course['name']; ?></td>
                                                        <td><?php echo $course['a_rate']; ?>%</td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $course['a_rate']; ?>%" aria-valuenow="<?php echo $course['a_rate']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- تقرير التقدم الأكاديمي -->
                    <div class="tab-pane fade <?php echo $report_type === 'academic_progress' ? 'show active' : ''; ?>" id="academicProgressReportTab">
                        <?php if ($report_type === 'academic_progress'): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $academic_progress_report_data['average_completion_rate']; ?>%</div>
                                        <div class="stat-card-label"><?php echo t('average_completion_rate'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $academic_progress_report_data['graduation_rate']; ?>%</div>
                                        <div class="stat-card-label"><?php echo t('graduation_rate'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('progress_by_level'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="progressByLevelChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('students_at_risk'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo t('student_id'); ?></th>
                                                    <th><?php echo t('name'); ?></th>
                                                    <th><?php echo t('gpa'); ?></th>
                                                    <th><?php echo t('level'); ?></th>
                                                    <th><?php echo t('program'); ?></th>
                                                    <th><?php echo t('failed_courses'); ?></th>
                                                    <th><?php echo t('actions'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($academic_progress_report_data['students_at_risk'] as $student): ?>
                                                    <tr>
                                                        <td><?php echo $student['id']; ?></td>
                                                        <td><?php echo $student['name']; ?></td>
                                                        <td><span class="badge-gpa-low"><?php echo $student['gpa']; ?></span></td>
                                                        <td><span class="badge-level"><?php echo t('level') . ' ' . $student['level']; ?></span></td>
                                                        <td><span class="badge-program"><?php echo $student['program']; ?></span></td>
                                                        <td><?php echo $student['failed_courses']; ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="#" class="btn btn-outline-primary" title="<?php echo t('view_details'); ?>">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="#" class="btn btn-outline-warning" title="<?php echo t('send_warning'); ?>">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                </a>
                                                                <a href="#" class="btn btn-outline-info" title="<?php echo t('schedule_meeting'); ?>">
                                                                    <i class="fas fa-calendar-plus"></i>
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
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo t('average_time_to_graduate'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mx-auto text-center">
                                            <div class="stat-card">
                                                <div class="stat-card-value"><?php echo $academic_progress_report_data['average_time_to_graduate']; ?></div>
                                                <div class="stat-card-label"><?php echo t('years'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
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
                
                // إعادة تهيئة الرسوم البيانية بألوان المظهر الجديد
                initializeCharts();
            });
            
            // تهيئة الرسوم البيانية
            initializeCharts();
        });
        
        // دالة لتهيئة الرسوم البيانية
        function initializeCharts() {
            const isDarkTheme = document.body.className.includes('theme-dark');
            const textColor = isDarkTheme ? '#ffffff' : '#333333';
            const gridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            // إعدادات عامة للرسوم البيانية
            Chart.defaults.color = textColor;
            Chart.defaults.borderColor = gridColor;
            
            // تهيئة الرسوم البيانية حسب نوع التقرير
            const reportType = '<?php echo $report_type; ?>';
            
            if (reportType === 'gpa') {
                initializeGpaCharts();
            } else if (reportType === 'pass_fail') {
                initializePassFailCharts();
            } else if (reportType === 'grades_distribution') {
                initializeGradesDistributionCharts();
            } else if (reportType === 'academic_progress') {
                initializeAcademicProgressCharts();
            }
        }
        
        // دالة لتهيئة رسوم تقرير المعدل التراكمي
        function initializeGpaCharts() {
            // رسم توزيع المعدل التراكمي
            const gpaDistributionCtx = document.getElementById('gpaDistributionChart');
            if (gpaDistributionCtx) {
                const gpaDistributionData = <?php echo json_encode($gpa_report_data['gpa_distribution']); ?>;
                
                new Chart(gpaDistributionCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(gpaDistributionData),
                        datasets: [{
                            label: '<?php echo t('number_of_students'); ?>',
                            data: Object.values(gpaDistributionData),
                            backgroundColor: '#003049',
                            borderColor: '#003049',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: '<?php echo t('number_of_students'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('gpa_range'); ?>'
                                }
                            }
                        }
                    }
                });
            }
            
            // رسم المعدل التراكمي حسب المستوى
            const gpaByLevelCtx = document.getElementById('gpaByLevelChart');
            if (gpaByLevelCtx) {
                const gpaByLevelData = <?php echo json_encode($gpa_report_data['gpa_by_level']); ?>;
                
                new Chart(gpaByLevelCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys(gpaByLevelData).map(level => '<?php echo t('level'); ?> ' + level),
                        datasets: [{
                            label: '<?php echo t('average_gpa'); ?>',
                            data: Object.values(gpaByLevelData),
                            backgroundColor: '#669bbc',
                            borderColor: '#669bbc',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                min: 2,
                                max: 4,
                                title: {
                                    display: true,
                                    text: '<?php echo t('average_gpa'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('level'); ?>'
                                }
                            }
                        }
                    }
                });
            }
            
            // رسم المعدل التراكمي حسب الجنس
            const gpaByGenderCtx = document.getElementById('gpaByGenderChart');
            if (gpaByGenderCtx) {
                const gpaByGenderData = <?php echo json_encode($gpa_report_data['gpa_by_gender']); ?>;
                
                new Chart(gpaByGenderCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(gpaByGenderData),
                        datasets: [{
                            label: '<?php echo t('average_gpa'); ?>',
                            data: Object.values(gpaByGenderData),
                            backgroundColor: ['#003049', '#669bbc'],
                            borderColor: ['#003049', '#669bbc'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                min: 2,
                                max: 4,
                                title: {
                                    display: true,
                                    text: '<?php echo t('average_gpa'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('gender'); ?>'
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // دالة لتهيئة رسوم تقرير معدلات النجاح والرسوب
        function initializePassFailCharts() {
            // رسم معدلات النجاح والرسوب حسب المستوى
            const passFailByLevelCtx = document.getElementById('passFailByLevelChart');
            if (passFailByLevelCtx) {
                const passFailByLevelData = <?php echo json_encode($pass_fail_report_data['pass_fail_by_level']); ?>;
                
                const levels = Object.keys(passFailByLevelData).map(level => '<?php echo t('level'); ?> ' + level);
                const passRates = Object.values(passFailByLevelData).map(data => data.pass);
                const failRates = Object.values(passFailByLevelData).map(data => data.fail);
                
                new Chart(passFailByLevelCtx, {
                    type: 'bar',
                    data: {
                        labels: levels,
                        datasets: [
                            {
                                label: '<?php echo t('pass_rate'); ?>',
                                data: passRates,
                                backgroundColor: '#28a745',
                                borderColor: '#28a745',
                                borderWidth: 1
                            },
                            {
                                label: '<?php echo t('fail_rate'); ?>',
                                data: failRates,
                                backgroundColor: '#dc3545',
                                borderColor: '#dc3545',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: '<?php echo t('percentage'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('level'); ?>'
                                }
                            }
                        }
                    }
                });
            }
            
            // رسم معدلات النجاح والرسوب حسب الجنس
            const passFailByGenderCtx = document.getElementById('passFailByGenderChart');
            if (passFailByGenderCtx) {
                const passFailByGenderData = <?php echo json_encode($pass_fail_report_data['pass_fail_by_gender']); ?>;
                
                const genders = Object.keys(passFailByGenderData);
                const passRates = Object.values(passFailByGenderData).map(data => data.pass);
                const failRates = Object.values(passFailByGenderData).map(data => data.fail);
                
                new Chart(passFailByGenderCtx, {
                    type: 'bar',
                    data: {
                        labels: genders,
                        datasets: [
                            {
                                label: '<?php echo t('pass_rate'); ?>',
                                data: passRates,
                                backgroundColor: '#28a745',
                                borderColor: '#28a745',
                                borderWidth: 1
                            },
                            {
                                label: '<?php echo t('fail_rate'); ?>',
                                data: failRates,
                                backgroundColor: '#dc3545',
                                borderColor: '#dc3545',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: '<?php echo t('percentage'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('gender'); ?>'
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // دالة لتهيئة رسوم تقرير توزيع الدرجات
        function initializeGradesDistributionCharts() {
            // رسم توزيع الدرجات الكلي
            const overallGradesDistributionCtx = document.getElementById('overallGradesDistributionChart');
            if (overallGradesDistributionCtx) {
                const overallDistributionData = <?php echo json_encode($grades_distribution_report_data['overall_distribution']); ?>;
                
                new Chart(overallGradesDistributionCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(overallDistributionData),
                        datasets: [{
                            data: Object.values(overallDistributionData),
                            backgroundColor: [
                                '#28a745', // A+
                                '#5cb85c', // A
                                '#5bc0de', // B+
                                '#17a2b8', // B
                                '#ffc107', // C+
                                '#fd7e14', // C
                                '#f0ad4e', // D+
                                '#d9534f', // D
                                '#dc3545'  // F
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }
            
            // رسم توزيع الدرجات حسب المستوى
            updateGradesDistributionByLevelChart();
        }
        
        // دالة لتحديث رسم توزيع الدرجات حسب المستوى
        function updateGradesDistributionByLevelChart() {
            const gradesDistributionByLevelCtx = document.getElementById('gradesDistributionByLevelChart');
            if (gradesDistributionByLevelCtx) {
                const levelSelector = document.getElementById('levelSelector');
                const selectedLevel = levelSelector ? levelSelector.value : '1';
                
                const distributionByLevelData = <?php echo json_encode($grades_distribution_report_data['distribution_by_level']); ?>;
                const selectedLevelData = distributionByLevelData[selectedLevel];
                
                // تدمير الرسم البياني السابق إذا وجد
                if (window.gradesDistributionByLevelChart) {
                    window.gradesDistributionByLevelChart.destroy();
                }
                
                window.gradesDistributionByLevelChart = new Chart(gradesDistributionByLevelCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(selectedLevelData),
                        datasets: [{
                            label: '<?php echo t('number_of_students'); ?>',
                            data: Object.values(selectedLevelData),
                            backgroundColor: [
                                '#28a745', // A+
                                '#5cb85c', // A
                                '#5bc0de', // B+
                                '#17a2b8', // B
                                '#ffc107', // C+
                                '#fd7e14', // C
                                '#f0ad4e', // D+
                                '#d9534f', // D
                                '#dc3545'  // F
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: '<?php echo t('number_of_students'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('grade'); ?>'
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // دالة لتهيئة رسوم تقرير التقدم الأكاديمي
        function initializeAcademicProgressCharts() {
            // رسم التقدم حسب المستوى
            const progressByLevelCtx = document.getElementById('progressByLevelChart');
            if (progressByLevelCtx) {
                const progressByLevelData = <?php echo json_encode($academic_progress_report_data['progress_by_level']); ?>;
                
                new Chart(progressByLevelCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys(progressByLevelData).map(level => '<?php echo t('level'); ?> ' + level),
                        datasets: [{
                            label: '<?php echo t('completion_rate'); ?>',
                            data: Object.values(progressByLevelData),
                            backgroundColor: '#003049',
                            borderColor: '#003049',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: '<?php echo t('percentage'); ?>'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?php echo t('level'); ?>'
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // تحديث قائمة البرامج بناءً على القسم المحدد (للفلتر)
        function updateProgramsFilter() {
            const departmentId = document.getElementById('department_filter').value;
            const programSelect = document.getElementById('program_filter');
            
            // إعادة تعيين قائمة البرامج
            programSelect.innerHTML = `<option value=""><?php echo t('all_programs'); ?></option>`;
            
            if (departmentId) {
                // هنا يجب إضافة طلب AJAX للحصول على البرامج حسب القسم
                // لأغراض العرض، سنستخدم بيانات وهمية
                
                const allPrograms = <?php echo json_encode($programs); ?>;
                const filteredPrograms = allPrograms.filter(p => p.department_id == departmentId);
                
                filteredPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            } else {
                // إذا لم يتم تحديد قسم، عرض جميع البرامج المتاحة للكلية
                const allPrograms = <?php echo json_encode($programs); ?>;
                allPrograms.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            }
        }
        
        // دالة لبناء query string مع الحفاظ على الفلاتر الحالية
        function build_query_string(exclude = []) {
            const params = new URLSearchParams(window.location.search);
            let queryString = '';
            for (const [key, value] of params.entries()) {
                if (!exclude.includes(key)) {
                    queryString += `&${key}=${value}`;
                }
            }
            return queryString;
        }
    </script>
</body>
</html>
