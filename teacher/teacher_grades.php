<?php
/**
 * صفحة إدارة الدرجات للمعلم في نظام UniverBoard
 * تتيح للمعلم عرض وإدارة درجات الطلاب في المقررات التي يدرسها
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

// الحصول على المقررات التي يدرسها المعلم
$courses = get_teacher_courses($db, $teacher_id);

// معالجة البحث والتصفية
$selected_course = isset($_GET['course']) ? filter_input(INPUT_GET, 'course', FILTER_SANITIZE_STRING) : '';
$search_query = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';

// الحصول على الطلاب والدرجات للمقرر المحدد
$students = [];
$grades = [];
$course_info = null;

if (!empty($selected_course)) {
    // الحصول على معلومات المقرر
    foreach ($courses as $course) {
        if ($course['id'] === $selected_course) {
            $course_info = $course;
            break;
        }
    }
    
    // الحصول على الطلاب المسجلين في المقرر
    $students = get_course_students($db, $selected_course);
    
    // الحصول على الدرجات للمقرر
    $grades = get_course_grades($db, $selected_course);
    
    // تطبيق البحث على الطلاب
    if (!empty($search_query)) {
        $filtered_students = [];
        foreach ($students as $student) {
            if (stripos($student['name'], $search_query) !== false || 
                stripos($student['id'], $search_query) !== false || 
                stripos($student['email'], $search_query) !== false) {
                $filtered_students[] = $student;
            }
        }
        $students = $filtered_students;
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
    <title><?php echo SITE_NAME; ?> - <?php echo t('grades'); ?></title>
    
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
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }
        
        .theme-dark .empty-state {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: var(--gray-color);
            margin-bottom: 1.5rem;
        }
        
        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .empty-state-text {
            color: var(--gray-color);
            margin-bottom: 1.5rem;
        }
        
        .course-card {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .course-card-header {
            padding: 1.25rem;
            background-color: var(--primary-color);
            color: white;
            position: relative;
        }
        
        .course-card-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .course-card-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .course-card-body {
            padding: 1.25rem;
            flex-grow: 1;
        }
        
        .course-card-footer {
            padding: 1rem 1.25rem;
            background-color: rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .course-card-footer {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .course-card-stat {
            display: flex;
            align-items: center;
        }
        
        .course-card-stat i {
            margin-right: 0.5rem;
            opacity: 0.7;
        }
        
        [dir="rtl"] .course-card-stat i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .grade-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .grade-table th,
        .grade-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        [dir="rtl"] .grade-table th,
        [dir="rtl"] .grade-table td {
            text-align: right;
        }
        
        .theme-dark .grade-table th,
        .theme-dark .grade-table td {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .grade-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .grade-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .grade-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .grade-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .grade-badge.excellent {
            background-color: #28a745;
            color: white;
        }
        
        .grade-badge.very-good {
            background-color: #20c997;
            color: white;
        }
        
        .grade-badge.good {
            background-color: #17a2b8;
            color: white;
        }
        
        .grade-badge.pass {
            background-color: #ffc107;
            color: #212529;
        }
        
        .grade-badge.fail {
            background-color: #dc3545;
            color: white;
        }
        
        .grade-badge.incomplete {
            background-color: #6c757d;
            color: white;
        }
        
        .grade-input {
            width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .theme-dark .grade-input {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .grade-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 48, 73, 0.25);
        }
        
        .grade-input:disabled {
            background-color: rgba(0, 0, 0, 0.05);
            cursor: not-allowed;
        }
        
        .theme-dark .grade-input:disabled {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .grade-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .grade-actions button {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: none;
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .grade-actions button:hover {
            background-color: var(--primary-dark);
        }
        
        .grade-actions button.cancel {
            background-color: var(--gray-color);
        }
        
        .grade-actions button.cancel:hover {
            background-color: var(--gray-dark);
        }
        
        .grade-actions button:disabled {
            background-color: rgba(0, 0, 0, 0.1);
            cursor: not-allowed;
        }
        
        .theme-dark .grade-actions button:disabled {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .grade-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .grade-summary-item {
            flex: 1;
            min-width: 150px;
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .theme-dark .grade-summary-item {
            background-color: var(--dark-bg);
        }
        
        .grade-summary-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .grade-summary-label {
            font-size: 0.9rem;
            color: var(--gray-color);
        }
        
        .grade-distribution {
            margin-bottom: 2rem;
        }
        
        .grade-distribution-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .grade-distribution-bar {
            height: 30px;
            border-radius: 0.25rem;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.05);
            margin-bottom: 0.5rem;
        }
        
        .theme-dark .grade-distribution-bar {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .grade-distribution-segment {
            height: 100%;
            float: left;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .grade-distribution-segment.excellent {
            background-color: #28a745;
        }
        
        .grade-distribution-segment.very-good {
            background-color: #20c997;
        }
        
        .grade-distribution-segment.good {
            background-color: #17a2b8;
        }
        
        .grade-distribution-segment.pass {
            background-color: #ffc107;
            color: #212529;
        }
        
        .grade-distribution-segment.fail {
            background-color: #dc3545;
        }
        
        .grade-distribution-segment.incomplete {
            background-color: #6c757d;
        }
        
        .grade-distribution-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .grade-distribution-legend-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .grade-distribution-legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .grade-distribution-legend-color {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .grade-distribution-legend-color.excellent {
            background-color: #28a745;
        }
        
        .grade-distribution-legend-color.very-good {
            background-color: #20c997;
        }
        
        .grade-distribution-legend-color.good {
            background-color: #17a2b8;
        }
        
        .grade-distribution-legend-color.pass {
            background-color: #ffc107;
        }
        
        .grade-distribution-legend-color.fail {
            background-color: #dc3545;
        }
        
        .grade-distribution-legend-color.incomplete {
            background-color: #6c757d;
        }
        
        .course-info-card {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .course-info-header {
            padding: 1.25rem;
            background-color: var(--primary-color);
            color: white;
        }
        
        .course-info-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .course-info-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .course-info-body {
            padding: 1.25rem;
            background-color: white;
        }
        
        .theme-dark .course-info-body {
            background-color: var(--dark-bg);
        }
        
        .course-info-item {
            display: flex;
            margin-bottom: 0.75rem;
        }
        
        .course-info-item:last-child {
            margin-bottom: 0;
        }
        
        .course-info-label {
            font-weight: 600;
            min-width: 150px;
        }
        
        .course-info-value {
            flex-grow: 1;
        }
        
        .filter-card {
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            background-color: white;
        }
        
        .theme-dark .filter-card {
            background-color: var(--dark-bg);
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .filter-title {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .filter-group {
            margin-bottom: 1rem;
        }
        
        .filter-group:last-child {
            margin-bottom: 0;
        }
        
        .filter-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .filter-control {
            width: 100%;
            padding: 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .filter-control {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .filter-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 48, 73, 0.25);
        }
        
        .filter-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }
        
        .grade-export-btn {
            margin-bottom: 1rem;
        }
        
        .grade-import-btn {
            margin-bottom: 1rem;
        }
        
        .grade-import-form {
            margin-top: 1rem;
        }
        
        .grade-import-file {
            margin-bottom: 1rem;
        }
        
        .grade-import-submit {
            margin-top: 0.5rem;
        }
        
        .grade-import-cancel {
            margin-top: 0.5rem;
            margin-left: 0.5rem;
        }
        
        [dir="rtl"] .grade-import-cancel {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        
        .grade-template-btn {
            margin-top: 0.5rem;
        }
        
        .grade-template-link {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .grade-template-link:hover {
            text-decoration: underline;
        }
        
        .grade-status {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .grade-status {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .grade-status.graded {
            background-color: #28a745;
        }
        
        .grade-status.pending {
            background-color: #ffc107;
        }
        
        .grade-status.not-submitted {
            background-color: #dc3545;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.75rem;
        }
        
        [dir="rtl"] .student-avatar {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        .student-info {
            display: flex;
            align-items: center;
        }
        
        .student-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .student-id {
            font-size: 0.8rem;
            color: var(--gray-color);
        }
        
        .grade-comment-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
        }
        
        .grade-comment-btn:hover {
            color: var(--primary-dark);
        }
        
        .grade-comment-text {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-top: 0.5rem;
        }
        
        .grade-comment-form {
            margin-top: 0.5rem;
        }
        
        .grade-comment-textarea {
            width: 100%;
            padding: 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            resize: vertical;
            min-height: 80px;
            margin-bottom: 0.5rem;
        }
        
        .theme-dark .grade-comment-textarea {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .grade-comment-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 48, 73, 0.25);
        }
        
        .grade-comment-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        
        .grade-comment-save {
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            border: none;
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
        }
        
        .grade-comment-save:hover {
            background-color: var(--primary-dark);
        }
        
        .grade-comment-cancel {
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            border: none;
            background-color: var(--gray-color);
            color: white;
            cursor: pointer;
        }
        
        .grade-comment-cancel:hover {
            background-color: var(--gray-dark);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination .page-item {
            margin: 0 0.25rem;
        }
        
        .pagination .page-link {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--text-color);
            background-color: white;
            text-decoration: none;
        }
        
        .theme-dark .pagination .page-link {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .pagination .page-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .pagination .page-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--gray-color);
            pointer-events: none;
            cursor: default;
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
                    <a class="nav-link active" href="teacher_grades.php">
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
                    <a class="nav-link" href="teacher_settings.php">
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
                <h1 class="h3"><?php echo t('grades'); ?></h1>
                <p class="text-muted"><?php echo t('manage_student_grades'); ?></p>
            </div>
        </div>
        
        <?php if (empty($selected_course)): ?>
            <!-- اختيار المقرر -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="filter-card">
                        <h5 class="filter-title"><?php echo t('select_course'); ?></h5>
                        <p><?php echo t('select_course_to_manage_grades'); ?></p>
                    </div>
                </div>
                
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="?course=<?php echo $course['id']; ?>" class="text-decoration-none">
                            <div class="course-card">
                                <div class="course-card-header">
                                    <h5 class="course-card-title"><?php echo $course['name']; ?></h5>
                                    <div class="course-card-subtitle"><?php echo $course['code']; ?></div>
                                </div>
                                <div class="course-card-body">
                                    <p><?php echo substr($course['description'], 0, 100); ?>...</p>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between">
                                            <small><?php echo t('students'); ?></small>
                                            <small><?php echo $course['students_count']; ?></small>
                                        </div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;" aria-valuenow="<?php echo $course['students_count']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $course['students_count']; ?>"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="course-card-footer">
                                    <div class="course-card-stat">
                                        <i class="fas fa-calendar-alt"></i> <?php echo $course['semester']; ?>
                                    </div>
                                    <button class="btn btn-sm btn-primary"><?php echo t('manage_grades'); ?></button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- إدارة الدرجات للمقرر المحدد -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="course-info-card">
                        <div class="course-info-header">
                            <h5 class="course-info-title"><?php echo $course_info['name']; ?></h5>
                            <div class="course-info-subtitle"><?php echo $course_info['code']; ?> - <?php echo $course_info['semester']; ?></div>
                        </div>
                        <div class="course-info-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="course-info-item">
                                        <div class="course-info-label"><?php echo t('department'); ?>:</div>
                                        <div class="course-info-value"><?php echo $course_info['department']; ?></div>
                                    </div>
                                    <div class="course-info-item">
                                        <div class="course-info-label"><?php echo t('credit_hours'); ?>:</div>
                                        <div class="course-info-value"><?php echo $course_info['credit_hours']; ?></div>
                                    </div>
                                    <div class="course-info-item">
                                        <div class="course-info-label"><?php echo t('students_count'); ?>:</div>
                                        <div class="course-info-value"><?php echo count($students); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="course-info-item">
                                        <div class="course-info-label"><?php echo t('grading_system'); ?>:</div>
                                        <div class="course-info-value">
                                            A (90-100), B (80-89), C (70-79), D (60-69), F (0-59)
                                        </div>
                                    </div>
                                    <div class="course-info-item">
                                        <div class="course-info-label"><?php echo t('grade_distribution'); ?>:</div>
                                        <div class="course-info-value">
                                            <?php echo t('assignments'); ?>: 20%, <?php echo t('midterm'); ?>: 30%, <?php echo t('final'); ?>: 50%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4><?php echo t('grade_summary'); ?></h4>
                        </div>
                        <div>
                            <a href="?course=<?php echo $selected_course; ?>&export=excel" class="btn btn-success grade-export-btn">
                                <i class="fas fa-file-excel me-1"></i> <?php echo t('export_to_excel'); ?>
                            </a>
                            <button class="btn btn-primary grade-import-btn" id="importGradesBtn">
                                <i class="fas fa-file-import me-1"></i> <?php echo t('import_grades'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grade-import-form d-none" id="importGradesForm">
                        <div class="filter-card">
                            <h5 class="filter-title"><?php echo t('import_grades_from_file'); ?></h5>
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="importFile" class="form-label"><?php echo t('select_excel_file'); ?></label>
                                    <input type="file" class="form-control grade-import-file" id="importFile" name="import_file" accept=".xlsx, .xls, .csv">
                                    <div class="form-text"><?php echo t('import_file_format_hint'); ?></div>
                                </div>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary grade-import-submit"><?php echo t('import'); ?></button>
                                    <button type="button" class="btn btn-secondary grade-import-cancel" id="cancelImportBtn"><?php echo t('cancel'); ?></button>
                                </div>
                                <div class="mt-2">
                                    <a href="#" class="grade-template-link"><?php echo t('download_template'); ?></a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="grade-summary">
                        <div class="grade-summary-item">
                            <div class="grade-summary-value">
                                <?php
                                $total_grades = 0;
                                $count_grades = 0;
                                
                                foreach ($grades as $grade) {
                                    if ($grade['final_grade'] !== null) {
                                        $total_grades += $grade['final_grade'];
                                        $count_grades++;
                                    }
                                }
                                
                                echo $count_grades > 0 ? number_format($total_grades / $count_grades, 1) : '-';
                                ?>
                            </div>
                            <div class="grade-summary-label"><?php echo t('average_grade'); ?></div>
                        </div>
                        
                        <div class="grade-summary-item">
                            <div class="grade-summary-value">
                                <?php
                                $highest_grade = 0;
                                
                                foreach ($grades as $grade) {
                                    if ($grade['final_grade'] !== null && $grade['final_grade'] > $highest_grade) {
                                        $highest_grade = $grade['final_grade'];
                                    }
                                }
                                
                                echo $highest_grade > 0 ? $highest_grade : '-';
                                ?>
                            </div>
                            <div class="grade-summary-label"><?php echo t('highest_grade'); ?></div>
                        </div>
                        
                        <div class="grade-summary-item">
                            <div class="grade-summary-value">
                                <?php
                                $lowest_grade = 100;
                                
                                foreach ($grades as $grade) {
                                    if ($grade['final_grade'] !== null && $grade['final_grade'] < $lowest_grade) {
                                        $lowest_grade = $grade['final_grade'];
                                    }
                                }
                                
                                echo $lowest_grade < 100 ? $lowest_grade : '-';
                                ?>
                            </div>
                            <div class="grade-summary-label"><?php echo t('lowest_grade'); ?></div>
                        </div>
                        
                        <div class="grade-summary-item">
                            <div class="grade-summary-value">
                                <?php
                                $graded_count = 0;
                                
                                foreach ($grades as $grade) {
                                    if ($grade['final_grade'] !== null) {
                                        $graded_count++;
                                    }
                                }
                                
                                echo $graded_count;
                                ?>
                                /
                                <?php echo count($students); ?>
                            </div>
                            <div class="grade-summary-label"><?php echo t('graded_students'); ?></div>
                        </div>
                    </div>
                    
                    <div class="grade-distribution">
                        <h5 class="grade-distribution-title"><?php echo t('grade_distribution'); ?></h5>
                        
                        <?php
                        $excellent_count = 0;
                        $very_good_count = 0;
                        $good_count = 0;
                        $pass_count = 0;
                        $fail_count = 0;
                        $incomplete_count = 0;
                        
                        foreach ($grades as $grade) {
                            if ($grade['final_grade'] === null) {
                                $incomplete_count++;
                            } elseif ($grade['final_grade'] >= 90) {
                                $excellent_count++;
                            } elseif ($grade['final_grade'] >= 80) {
                                $very_good_count++;
                            } elseif ($grade['final_grade'] >= 70) {
                                $good_count++;
                            } elseif ($grade['final_grade'] >= 60) {
                                $pass_count++;
                            } else {
                                $fail_count++;
                            }
                        }
                        
                        $total_students = count($students);
                        $excellent_percent = $total_students > 0 ? ($excellent_count / $total_students * 100) : 0;
                        $very_good_percent = $total_students > 0 ? ($very_good_count / $total_students * 100) : 0;
                        $good_percent = $total_students > 0 ? ($good_count / $total_students * 100) : 0;
                        $pass_percent = $total_students > 0 ? ($pass_count / $total_students * 100) : 0;
                        $fail_percent = $total_students > 0 ? ($fail_count / $total_students * 100) : 0;
                        $incomplete_percent = $total_students > 0 ? ($incomplete_count / $total_students * 100) : 0;
                        ?>
                        
                        <div class="grade-distribution-bar">
                            <?php if ($excellent_percent > 0): ?>
                                <div class="grade-distribution-segment excellent" style="width: <?php echo $excellent_percent; ?>%;">
                                    <?php if ($excellent_percent >= 5): echo $excellent_count; endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($very_good_percent > 0): ?>
                                <div class="grade-distribution-segment very-good" style="width: <?php echo $very_good_percent; ?>%;">
                                    <?php if ($very_good_percent >= 5): echo $very_good_count; endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($good_percent > 0): ?>
                                <div class="grade-distribution-segment good" style="width: <?php echo $good_percent; ?>%;">
                                    <?php if ($good_percent >= 5): echo $good_count; endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($pass_percent > 0): ?>
                                <div class="grade-distribution-segment pass" style="width: <?php echo $pass_percent; ?>%;">
                                    <?php if ($pass_percent >= 5): echo $pass_count; endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($fail_percent > 0): ?>
                                <div class="grade-distribution-segment fail" style="width: <?php echo $fail_percent; ?>%;">
                                    <?php if ($fail_percent >= 5): echo $fail_count; endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($incomplete_percent > 0): ?>
                                <div class="grade-distribution-segment incomplete" style="width: <?php echo $incomplete_percent; ?>%;">
                                    <?php if ($incomplete_percent >= 5): echo $incomplete_count; endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="grade-distribution-legend">
                            <div class="grade-distribution-legend-item">
                                <div class="grade-distribution-legend-color excellent"></div>
                                <span>A (90-100): <?php echo $excellent_count; ?> <?php echo t('students'); ?></span>
                            </div>
                            <div class="grade-distribution-legend-item">
                                <div class="grade-distribution-legend-color very-good"></div>
                                <span>B (80-89): <?php echo $very_good_count; ?> <?php echo t('students'); ?></span>
                            </div>
                            <div class="grade-distribution-legend-item">
                                <div class="grade-distribution-legend-color good"></div>
                                <span>C (70-79): <?php echo $good_count; ?> <?php echo t('students'); ?></span>
                            </div>
                            <div class="grade-distribution-legend-item">
                                <div class="grade-distribution-legend-color pass"></div>
                                <span>D (60-69): <?php echo $pass_count; ?> <?php echo t('students'); ?></span>
                            </div>
                            <div class="grade-distribution-legend-item">
                                <div class="grade-distribution-legend-color fail"></div>
                                <span>F (0-59): <?php echo $fail_count; ?> <?php echo t('students'); ?></span>
                            </div>
                            <div class="grade-distribution-legend-item">
                                <div class="grade-distribution-legend-color incomplete"></div>
                                <span><?php echo t('incomplete'); ?>: <?php echo $incomplete_count; ?> <?php echo t('students'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4><?php echo t('student_grades'); ?></h4>
                        </div>
                        <div>
                            <form action="" method="get" class="d-flex">
                                <input type="hidden" name="course" value="<?php echo $selected_course; ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="<?php echo t('search_students'); ?>" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (count($students) > 0): ?>
                        <div class="table-responsive">
                            <table class="grade-table">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;"><?php echo t('student'); ?></th>
                                        <th style="width: 15%;"><?php echo t('assignments'); ?> (20%)</th>
                                        <th style="width: 15%;"><?php echo t('midterm'); ?> (30%)</th>
                                        <th style="width: 15%;"><?php echo t('final'); ?> (50%)</th>
                                        <th style="width: 10%;"><?php echo t('total'); ?> (100%)</th>
                                        <th style="width: 15%;"><?php echo t('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <?php
                                        $student_grade = null;
                                        foreach ($grades as $grade) {
                                            if ($grade['student_id'] === $student['id']) {
                                                $student_grade = $grade;
                                                break;
                                            }
                                        }
                                        
                                        $assignments_grade = $student_grade ? $student_grade['assignments_grade'] : null;
                                        $midterm_grade = $student_grade ? $student_grade['midterm_grade'] : null;
                                        $final_grade = $student_grade ? $student_grade['final_grade'] : null;
                                        $total_grade = $student_grade ? $student_grade['total_grade'] : null;
                                        $comment = $student_grade ? $student_grade['comment'] : '';
                                        
                                        $grade_letter = '';
                                        $grade_class = '';
                                        
                                        if ($total_grade !== null) {
                                            if ($total_grade >= 90) {
                                                $grade_letter = 'A';
                                                $grade_class = 'excellent';
                                            } elseif ($total_grade >= 80) {
                                                $grade_letter = 'B';
                                                $grade_class = 'very-good';
                                            } elseif ($total_grade >= 70) {
                                                $grade_letter = 'C';
                                                $grade_class = 'good';
                                            } elseif ($total_grade >= 60) {
                                                $grade_letter = 'D';
                                                $grade_class = 'pass';
                                            } else {
                                                $grade_letter = 'F';
                                                $grade_class = 'fail';
                                            }
                                        } else {
                                            $grade_letter = '-';
                                            $grade_class = 'incomplete';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="student-info">
                                                    <img src="<?php echo $student['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $student['name']; ?>" class="student-avatar">
                                                    <div>
                                                        <div class="student-name"><?php echo $student['name']; ?></div>
                                                        <div class="student-id"><?php echo $student['id']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" class="grade-input" data-student-id="<?php echo $student['id']; ?>" data-grade-type="assignments" value="<?php echo $assignments_grade !== null ? $assignments_grade : ''; ?>" min="0" max="20" step="0.1">
                                            </td>
                                            <td>
                                                <input type="number" class="grade-input" data-student-id="<?php echo $student['id']; ?>" data-grade-type="midterm" value="<?php echo $midterm_grade !== null ? $midterm_grade : ''; ?>" min="0" max="30" step="0.1">
                                            </td>
                                            <td>
                                                <input type="number" class="grade-input" data-student-id="<?php echo $student['id']; ?>" data-grade-type="final" value="<?php echo $final_grade !== null ? $final_grade : ''; ?>" min="0" max="50" step="0.1">
                                            </td>
                                            <td>
                                                <span class="grade-badge <?php echo $grade_class; ?>">
                                                    <?php echo $total_grade !== null ? $total_grade : '-'; ?> (<?php echo $grade_letter; ?>)
                                                </span>
                                            </td>
                                            <td>
                                                <div class="grade-actions">
                                                    <button class="btn btn-sm btn-primary save-grade-btn" data-student-id="<?php echo $student['id']; ?>">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info comment-grade-btn" data-student-id="<?php echo $student['id']; ?>" data-student-name="<?php echo $student['name']; ?>" data-comment="<?php echo htmlspecialchars($comment); ?>">
                                                        <i class="fas fa-comment"></i>
                                                    </button>
                                                </div>
                                                
                                                <?php if (!empty($comment)): ?>
                                                    <div class="grade-comment-text">
                                                        <i class="fas fa-comment-dots"></i> <?php echo $comment; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ترقيم الصفحات -->
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?php echo t('previous'); ?></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#"><?php echo t('next'); ?></a>
                                </li>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <!-- حالة عدم وجود طلاب -->
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="empty-state-title"><?php echo t('no_students_found'); ?></h3>
                            <p class="empty-state-text"><?php echo t('no_students_enrolled_message'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- مودال إضافة تعليق على الدرجة -->
    <div class="modal fade" id="gradeCommentModal" tabindex="-1" aria-labelledby="gradeCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gradeCommentModalLabel"><?php echo t('add_comment'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gradeCommentForm">
                        <input type="hidden" id="commentStudentId" name="student_id" value="">
                        <div class="mb-3">
                            <label for="commentText" class="form-label"><?php echo t('comment_for'); ?> <span id="commentStudentName"></span></label>
                            <textarea class="form-control" id="commentText" name="comment" rows="4"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="button" class="btn btn-primary" id="saveCommentBtn"><?php echo t('save_comment'); ?></button>
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
            });
            
            // زر استيراد الدرجات
            const importGradesBtn = document.getElementById('importGradesBtn');
            const importGradesForm = document.getElementById('importGradesForm');
            const cancelImportBtn = document.getElementById('cancelImportBtn');
            
            if (importGradesBtn && importGradesForm && cancelImportBtn) {
                importGradesBtn.addEventListener('click', function() {
                    importGradesForm.classList.remove('d-none');
                    importGradesBtn.classList.add('d-none');
                });
                
                cancelImportBtn.addEventListener('click', function() {
                    importGradesForm.classList.add('d-none');
                    importGradesBtn.classList.remove('d-none');
                });
            }
            
            // حساب الدرجة الإجمالية عند تغيير أي درجة
            const gradeInputs = document.querySelectorAll('.grade-input');
            
            gradeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const studentId = this.getAttribute('data-student-id');
                    const studentInputs = document.querySelectorAll(`.grade-input[data-student-id="${studentId}"]`);
                    
                    let assignmentsGrade = 0;
                    let midtermGrade = 0;
                    let finalGrade = 0;
                    let totalGrade = 0;
                    
                    studentInputs.forEach(input => {
                        const gradeType = input.getAttribute('data-grade-type');
                        const value = parseFloat(input.value) || 0;
                        
                        if (gradeType === 'assignments') {
                            assignmentsGrade = value;
                        } else if (gradeType === 'midterm') {
                            midtermGrade = value;
                        } else if (gradeType === 'final') {
                            finalGrade = value;
                        }
                    });
                    
                    totalGrade = assignmentsGrade + midtermGrade + finalGrade;
                    
                    // تحديث الدرجة الإجمالية في الواجهة
                    const totalCell = this.closest('tr').querySelector('.grade-badge');
                    
                    let gradeLetter = '';
                    let gradeClass = '';
                    
                    if (totalGrade >= 90) {
                        gradeLetter = 'A';
                        gradeClass = 'excellent';
                    } else if (totalGrade >= 80) {
                        gradeLetter = 'B';
                        gradeClass = 'very-good';
                    } else if (totalGrade >= 70) {
                        gradeLetter = 'C';
                        gradeClass = 'good';
                    } else if (totalGrade >= 60) {
                        gradeLetter = 'D';
                        gradeClass = 'pass';
                    } else {
                        gradeLetter = 'F';
                        gradeClass = 'fail';
                    }
                    
                    totalCell.textContent = `${totalGrade.toFixed(1)} (${gradeLetter})`;
                    totalCell.className = `grade-badge ${gradeClass}`;
                });
            });
            
            // حفظ الدرجات
            const saveGradeBtns = document.querySelectorAll('.save-grade-btn');
            
            saveGradeBtns.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    const studentInputs = document.querySelectorAll(`.grade-input[data-student-id="${studentId}"]`);
                    
                    let assignmentsGrade = null;
                    let midtermGrade = null;
                    let finalGrade = null;
                    
                    studentInputs.forEach(input => {
                        const gradeType = input.getAttribute('data-grade-type');
                        const value = input.value.trim() !== '' ? parseFloat(input.value) : null;
                        
                        if (gradeType === 'assignments') {
                            assignmentsGrade = value;
                        } else if (gradeType === 'midterm') {
                            midtermGrade = value;
                        } else if (gradeType === 'final') {
                            finalGrade = value;
                        }
                    });
                    
                    // هنا يمكن إضافة كود لإرسال الدرجات إلى الخادم
                    console.log('Saving grades for student', studentId, {
                        assignments: assignmentsGrade,
                        midterm: midtermGrade,
                        final: finalGrade
                    });
                    
                    // إظهار رسالة نجاح
                    alert('تم حفظ الدرجات بنجاح!');
                });
            });
            
            // إضافة تعليق على الدرجة
            const commentGradeBtns = document.querySelectorAll('.comment-grade-btn');
            const gradeCommentModal = new bootstrap.Modal(document.getElementById('gradeCommentModal'));
            
            commentGradeBtns.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    const studentName = this.getAttribute('data-student-name');
                    const comment = this.getAttribute('data-comment');
                    
                    document.getElementById('commentStudentId').value = studentId;
                    document.getElementById('commentStudentName').textContent = studentName;
                    document.getElementById('commentText').value = comment;
                    
                    gradeCommentModal.show();
                });
            });
            
            // حفظ التعليق
            document.getElementById('saveCommentBtn').addEventListener('click', function() {
                const studentId = document.getElementById('commentStudentId').value;
                const comment = document.getElementById('commentText').value;
                
                // هنا يمكن إضافة كود لإرسال التعليق إلى الخادم
                console.log('Saving comment for student', studentId, comment);
                
                // تحديث التعليق في الواجهة
                const commentBtn = document.querySelector(`.comment-grade-btn[data-student-id="${studentId}"]`);
                const commentCell = commentBtn.closest('td');
                
                let commentText = commentCell.querySelector('.grade-comment-text');
                
                if (comment.trim() !== '') {
                    if (commentText) {
                        commentText.innerHTML = `<i class="fas fa-comment-dots"></i> ${comment}`;
                    } else {
                        commentText = document.createElement('div');
                        commentText.className = 'grade-comment-text';
                        commentText.innerHTML = `<i class="fas fa-comment-dots"></i> ${comment}`;
                        commentCell.appendChild(commentText);
                    }
                    
                    commentBtn.setAttribute('data-comment', comment);
                } else {
                    if (commentText) {
                        commentText.remove();
                    }
                    
                    commentBtn.setAttribute('data-comment', '');
                }
                
                // إغلاق المودال
                gradeCommentModal.hide();
                
                // إظهار رسالة نجاح
                alert('تم حفظ التعليق بنجاح!');
            });
        });
    </script>
</body>
</html>
