<?php
/**
 * صفحة التقارير الأكاديمية في نظام UniverBoard
 * تتيح للمشرف عرض وتحليل البيانات الأكاديمية
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
$period = isset($_GET['period']) ? $_GET['period'] : 'semester';
$valid_periods = ['semester', 'year', 'all'];
if (!in_array($period, $valid_periods)) {
    $period = 'semester';
}

// تحديد الكلية للتقارير
$college_id = isset($_GET['college_id']) ? intval($_GET['college_id']) : 0;
$colleges = get_all_colleges($db);

// الحصول على إحصائيات أكاديمية
$academic_stats = get_academic_statistics($db, $period, $college_id);
$course_enrollment = get_course_enrollment_statistics($db, $period, $college_id);
$grade_distribution = get_grade_distribution($db, $period, $college_id);
$attendance_rates = get_attendance_rates($db, $period, $college_id);
$top_courses = get_top_courses($db, $period, $college_id);
$academic_performance = get_academic_performance_trend($db, $period, $college_id);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_all_colleges($db) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على الكليات
    return [
        ['id' => 1, 'name' => 'كلية الهندسة'],
        ['id' => 2, 'name' => 'كلية العلوم'],
        ['id' => 3, 'name' => 'كلية الطب'],
        ['id' => 4, 'name' => 'كلية الحاسب'],
        ['id' => 5, 'name' => 'كلية الآداب']
    ];
}

function get_academic_statistics($db, $period, $college_id) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على الإحصائيات
    return [
        'total_students' => 3250,
        'total_teachers' => 320,
        'total_courses' => 185,
        'total_programs' => 42,
        'average_gpa' => 3.45,
        'passing_rate' => 92.5,
        'graduation_rate' => 87.2,
        'dropout_rate' => 4.8
    ];
}

function get_course_enrollment_statistics($db, $period, $college_id) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على إحصائيات التسجيل
    return [
        ['college' => 'كلية الهندسة', 'enrollment' => 850],
        ['college' => 'كلية العلوم', 'enrollment' => 720],
        ['college' => 'كلية الطب', 'enrollment' => 480],
        ['college' => 'كلية الحاسب', 'enrollment' => 650],
        ['college' => 'كلية الآداب', 'enrollment' => 550]
    ];
}

function get_grade_distribution($db, $period, $college_id) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على توزيع الدرجات
    return [
        ['grade' => 'A', 'percentage' => 18],
        ['grade' => 'B+', 'percentage' => 22],
        ['grade' => 'B', 'percentage' => 25],
        ['grade' => 'C+', 'percentage' => 15],
        ['grade' => 'C', 'percentage' => 12],
        ['grade' => 'D', 'percentage' => 5],
        ['grade' => 'F', 'percentage' => 3]
    ];
}

function get_attendance_rates($db, $period, $college_id) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على معدلات الحضور
    return [
        ['college' => 'كلية الهندسة', 'rate' => 88],
        ['college' => 'كلية العلوم', 'rate' => 92],
        ['college' => 'كلية الطب', 'rate' => 95],
        ['college' => 'كلية الحاسب', 'rate' => 85],
        ['college' => 'كلية الآداب', 'rate' => 82]
    ];
}

function get_top_courses($db, $period, $college_id) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على أفضل المقررات
    return [
        [
            'id' => 1,
            'code' => 'CS101',
            'name' => 'مقدمة في علوم الحاسب',
            'college' => 'كلية الحاسب',
            'enrollment' => 120,
            'avg_grade' => 3.8,
            'satisfaction' => 92
        ],
        [
            'id' => 2,
            'code' => 'ENG205',
            'name' => 'ميكانيكا الموائع',
            'college' => 'كلية الهندسة',
            'enrollment' => 95,
            'avg_grade' => 3.6,
            'satisfaction' => 88
        ],
        [
            'id' => 3,
            'code' => 'MED301',
            'name' => 'علم التشريح',
            'college' => 'كلية الطب',
            'enrollment' => 85,
            'avg_grade' => 3.9,
            'satisfaction' => 94
        ],
        [
            'id' => 4,
            'code' => 'SCI202',
            'name' => 'الكيمياء العضوية',
            'college' => 'كلية العلوم',
            'enrollment' => 110,
            'avg_grade' => 3.5,
            'satisfaction' => 86
        ],
        [
            'id' => 5,
            'code' => 'ART150',
            'name' => 'الأدب العربي الحديث',
            'college' => 'كلية الآداب',
            'enrollment' => 75,
            'avg_grade' => 3.7,
            'satisfaction' => 90
        ]
    ];
}

function get_academic_performance_trend($db, $period, $college_id) {
    // في الواقع، يجب استعلام قاعدة البيانات للحصول على اتجاه الأداء الأكاديمي
    if ($period === 'semester') {
        return [
            ['period' => 'الأسبوع 1', 'avg_grade' => 3.2, 'attendance' => 90],
            ['period' => 'الأسبوع 2', 'avg_grade' => 3.3, 'attendance' => 88],
            ['period' => 'الأسبوع 3', 'avg_grade' => 3.4, 'attendance' => 85],
            ['period' => 'الأسبوع 4', 'avg_grade' => 3.3, 'attendance' => 82],
            ['period' => 'الأسبوع 5', 'avg_grade' => 3.5, 'attendance' => 80],
            ['period' => 'الأسبوع 6', 'avg_grade' => 3.6, 'attendance' => 83],
            ['period' => 'الأسبوع 7', 'avg_grade' => 3.5, 'attendance' => 85],
            ['period' => 'الأسبوع 8', 'avg_grade' => 3.7, 'attendance' => 88],
            ['period' => 'الأسبوع 9', 'avg_grade' => 3.6, 'attendance' => 90],
            ['period' => 'الأسبوع 10', 'avg_grade' => 3.8, 'attendance' => 92],
            ['period' => 'الأسبوع 11', 'avg_grade' => 3.7, 'attendance' => 90],
            ['period' => 'الأسبوع 12', 'avg_grade' => 3.9, 'attendance' => 88]
        ];
    } else {
        return [
            ['period' => 'الفصل الأول', 'avg_grade' => 3.4, 'attendance' => 88],
            ['period' => 'الفصل الثاني', 'avg_grade' => 3.5, 'attendance' => 85],
            ['period' => 'الفصل الصيفي', 'avg_grade' => 3.6, 'attendance' => 82],
            ['period' => 'الفصل الأول', 'avg_grade' => 3.7, 'attendance' => 86],
            ['period' => 'الفصل الثاني', 'avg_grade' => 3.8, 'attendance' => 90]
        ];
    }
}

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
            flex-wrap: wrap;
        }
        
        .filter-group {
            margin-right: 2rem;
            margin-bottom: 1rem;
        }
        
        [dir="rtl"] .filter-group {
            margin-right: 0;
            margin-left: 2rem;
        }
        
        .filter-label {
            font-weight: 500;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
            display: block;
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
        
        .filter-select {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 500;
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--text-color);
            min-width: 200px;
        }
        
        .theme-dark .filter-select {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
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
        
        /* تنسيقات النجوم */
        .stars {
            display: flex;
            align-items: center;
        }
        
        .stars i {
            color: #ffc107;
            font-size: 0.875rem;
            margin-right: 0.125rem;
        }
        
        [dir="rtl"] .stars i {
            margin-right: 0;
            margin-left: 0.125rem;
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
                    <a class="nav-link active" href="admin_reports_academic.php">
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
            <h1 class="page-title"><?php echo t('academic_reports'); ?></h1>
            <p class="page-subtitle"><?php echo t('analyze_academic_performance_and_trends'); ?></p>
        </div>
        
        <!-- فلاتر التقارير -->
        <div class="filter-bar">
            <div class="filter-group">
                <div class="filter-label"><?php echo t('time_period'); ?>:</div>
                <div class="filter-options">
                    <a href="?period=semester<?php echo $college_id ? '&college_id=' . $college_id : ''; ?>" class="filter-option <?php echo $period === 'semester' ? 'active' : ''; ?>"><?php echo t('current_semester'); ?></a>
                    <a href="?period=year<?php echo $college_id ? '&college_id=' . $college_id : ''; ?>" class="filter-option <?php echo $period === 'year' ? 'active' : ''; ?>"><?php echo t('academic_year'); ?></a>
                    <a href="?period=all<?php echo $college_id ? '&college_id=' . $college_id : ''; ?>" class="filter-option <?php echo $period === 'all' ? 'active' : ''; ?>"><?php echo t('all_time'); ?></a>
                </div>
            </div>
            
            <div class="filter-group">
                <div class="filter-label"><?php echo t('college'); ?>:</div>
                <select class="filter-select" id="collegeFilter">
                    <option value="0"><?php echo t('all_colleges'); ?></option>
                    <?php foreach ($colleges as $college): ?>
                        <option value="<?php echo $college['id']; ?>" <?php echo $college_id === $college['id'] ? 'selected' : ''; ?>><?php echo $college['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- إحصائيات أكاديمية -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('total_students'); ?></div>
                        <div class="stats-value"><?php echo number_format($academic_stats['total_students']); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo $academic_stats['graduation_rate']; ?>% <?php echo t('graduation_rate'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon secondary">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('total_teachers'); ?></div>
                        <div class="stats-value"><?php echo number_format($academic_stats['total_teachers']); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo round($academic_stats['total_students'] / $academic_stats['total_teachers'], 1); ?> <?php echo t('students_per_teacher'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon success">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('total_courses'); ?></div>
                        <div class="stats-value"><?php echo number_format($academic_stats['total_courses']); ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i> <?php echo $academic_stats['passing_rate']; ?>% <?php echo t('passing_rate'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon warning">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-title"><?php echo t('average_gpa'); ?></div>
                        <div class="stats-value"><?php echo $academic_stats['average_gpa']; ?></div>
                        <div class="stats-change negative">
                            <i class="fas fa-arrow-down"></i> <?php echo $academic_stats['dropout_rate']; ?>% <?php echo t('dropout_rate'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- توزيع التسجيل والدرجات -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('course_enrollment_by_college'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="enrollmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('grade_distribution'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="gradeDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- معدلات الحضور والأداء الأكاديمي -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('attendance_rates_by_college'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('academic_performance_trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- أفضل المقررات -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo t('top_performing_courses'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo t('course_code'); ?></th>
                                        <th><?php echo t('course_name'); ?></th>
                                        <th><?php echo t('college'); ?></th>
                                        <th><?php echo t('enrollment'); ?></th>
                                        <th><?php echo t('avg_grade'); ?></th>
                                        <th><?php echo t('satisfaction'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_courses as $course): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $course['code']; ?></span>
                                            </td>
                                            <td><?php echo $course['name']; ?></td>
                                            <td><?php echo $course['college']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $course['enrollment']; ?></span>
                                                    <div class="progress flex-grow-1" style="width: 100px;">
                                                        <div class="progress-bar progress-bar-primary" role="progressbar" style="width: <?php echo min(100, ($course['enrollment'] / 150) * 100); ?>%" aria-valuenow="<?php echo $course['enrollment']; ?>" aria-valuemin="0" aria-valuemax="150"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $course['avg_grade']; ?></span>
                                                    <div class="stars">
                                                        <?php for ($i = 0; $i < floor($course['avg_grade']); $i++): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php endfor; ?>
                                                        <?php if ($course['avg_grade'] - floor($course['avg_grade']) >= 0.5): ?>
                                                            <i class="fas fa-star-half-alt"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $course['satisfaction']; ?>%</span>
                                                    <div class="progress flex-grow-1" style="width: 100px;">
                                                        <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php echo $course['satisfaction']; ?>%" aria-valuenow="<?php echo $course['satisfaction']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
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
            
            // تغيير الكلية
            document.getElementById('collegeFilter').addEventListener('change', function() {
                const collegeId = this.value;
                const currentUrl = new URL(window.location.href);
                
                if (collegeId === '0') {
                    currentUrl.searchParams.delete('college_id');
                } else {
                    currentUrl.searchParams.set('college_id', collegeId);
                }
                
                window.location.href = currentUrl.toString();
            });
            
            // تهيئة الرسوم البيانية
            initCharts();
            
            function initCharts() {
                // رسم بياني لتوزيع التسجيل حسب الكلية
                const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
                const enrollmentData = <?php echo json_encode($course_enrollment); ?>;
                
                window.enrollmentChart = new Chart(enrollmentCtx, {
                    type: 'bar',
                    data: {
                        labels: enrollmentData.map(item => item.college),
                        datasets: [{
                            label: '<?php echo t('enrollment'); ?>',
                            data: enrollmentData.map(item => item.enrollment),
                            backgroundColor: '#003049',
                            borderWidth: 0
                        }]
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
                                display: false
                            }
                        }
                    }
                });
                
                // رسم بياني لتوزيع الدرجات
                const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
                const gradeDistributionData = <?php echo json_encode($grade_distribution); ?>;
                
                window.gradeDistributionChart = new Chart(gradeDistributionCtx, {
                    type: 'pie',
                    data: {
                        labels: gradeDistributionData.map(item => item.grade),
                        datasets: [{
                            data: gradeDistributionData.map(item => item.percentage),
                            backgroundColor: [
                                '#198754',
                                '#20c997',
                                '#0dcaf0',
                                '#0d6efd',
                                '#6610f2',
                                '#fd7e14',
                                '#dc3545'
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
                                        return `${label}: ${value}%`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم بياني لمعدلات الحضور حسب الكلية
                const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
                const attendanceData = <?php echo json_encode($attendance_rates); ?>;
                
                window.attendanceChart = new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: attendanceData.map(item => item.college),
                        datasets: [{
                            label: '<?php echo t('attendance_rate'); ?>',
                            data: attendanceData.map(item => item.rate),
                            backgroundColor: '#669bbc',
                            borderWidth: 0
                        }]
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
                                    },
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.dataset.label || '';
                                        const value = context.raw || 0;
                                        return `${label}: ${value}%`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم بياني لاتجاه الأداء الأكاديمي
                const performanceCtx = document.getElementById('performanceChart').getContext('2d');
                const performanceData = <?php echo json_encode($academic_performance); ?>;
                
                window.performanceChart = new Chart(performanceCtx, {
                    type: 'line',
                    data: {
                        labels: performanceData.map(item => item.period),
                        datasets: [
                            {
                                label: '<?php echo t('avg_grade'); ?>',
                                data: performanceData.map(item => item.avg_grade),
                                borderColor: '#003049',
                                backgroundColor: 'rgba(0, 48, 73, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y'
                            },
                            {
                                label: '<?php echo t('attendance'); ?>',
                                data: performanceData.map(item => item.attendance),
                                borderColor: '#669bbc',
                                backgroundColor: 'rgba(102, 155, 188, 0.1)',
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
                                        return value.toFixed(1);
                                    }
                                },
                                min: 0,
                                max: 5
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
                                    },
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                min: 0,
                                max: 100
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
                                        if (context.dataset.yAxisID === 'y1') {
                                            return `${label}: ${value}%`;
                                        }
                                        return `${label}: ${value}`;
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
                
                // تحديث رسم بياني التسجيل
                if (window.enrollmentChart) {
                    window.enrollmentChart.options.scales.x.grid.color = gridColor;
                    window.enrollmentChart.options.scales.y.grid.color = gridColor;
                    window.enrollmentChart.options.scales.x.ticks.color = textColor;
                    window.enrollmentChart.options.scales.y.ticks.color = textColor;
                    window.enrollmentChart.update();
                }
                
                // تحديث رسم بياني توزيع الدرجات
                if (window.gradeDistributionChart) {
                    window.gradeDistributionChart.options.plugins.legend.labels.color = textColor;
                    window.gradeDistributionChart.update();
                }
                
                // تحديث رسم بياني معدلات الحضور
                if (window.attendanceChart) {
                    window.attendanceChart.options.scales.x.grid.color = gridColor;
                    window.attendanceChart.options.scales.y.grid.color = gridColor;
                    window.attendanceChart.options.scales.x.ticks.color = textColor;
                    window.attendanceChart.options.scales.y.ticks.color = textColor;
                    window.attendanceChart.update();
                }
                
                // تحديث رسم بياني الأداء الأكاديمي
                if (window.performanceChart) {
                    window.performanceChart.options.scales.x.grid.color = gridColor;
                    window.performanceChart.options.scales.y.grid.color = gridColor;
                    window.performanceChart.options.scales.x.ticks.color = textColor;
                    window.performanceChart.options.scales.y.ticks.color = textColor;
                    window.performanceChart.options.scales.y1.ticks.color = textColor;
                    window.performanceChart.options.plugins.legend.labels.color = textColor;
                    window.performanceChart.update();
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
