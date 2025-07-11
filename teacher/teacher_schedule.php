<?php
/**
 * صفحة الجدول الدراسي للمعلم في نظام UniverBoard
 * تتيح للمعلم عرض وإدارة جدوله الدراسي
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

// الحصول على الجدول الدراسي للمعلم
$schedule = get_teacher_schedule($db, $teacher_id);

// الحصول على المقررات التي يدرسها المعلم
$courses = get_teacher_courses($db, $teacher_id);

// الحصول على القاعات الدراسية
$classrooms = get_classrooms($db);

// معالجة إضافة موعد جديد
if (isset($_POST['add_schedule'])) {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    $day = filter_input(INPUT_POST, 'day', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    $classroom_id = filter_input(INPUT_POST, 'classroom_id', FILTER_SANITIZE_NUMBER_INT);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البيانات
    if (!empty($course_id) && !empty($day) && !empty($start_time) && !empty($end_time) && !empty($classroom_id) && !empty($type)) {
        // إضافة موعد جديد
        $result = add_schedule($db, $teacher_id, $course_id, $day, $start_time, $end_time, $classroom_id, $type);
        
        if ($result) {
            $success_message = t('schedule_added_successfully');
            // تحديث الجدول الدراسي
            $schedule = get_teacher_schedule($db, $teacher_id);
        } else {
            $error_message = t('failed_to_add_schedule');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// معالجة حذف موعد
if (isset($_POST['delete_schedule'])) {
    $schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_SANITIZE_NUMBER_INT);
    
    // حذف الموعد
    $result = delete_schedule($db, $schedule_id, $teacher_id);
    
    if ($result) {
        $success_message = t('schedule_deleted_successfully');
        // تحديث الجدول الدراسي
        $schedule = get_teacher_schedule($db, $teacher_id);
    } else {
        $error_message = t('failed_to_delete_schedule');
    }
}

// معالجة تعديل موعد
if (isset($_POST['edit_schedule'])) {
    $schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_SANITIZE_NUMBER_INT);
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    $day = filter_input(INPUT_POST, 'day', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    $classroom_id = filter_input(INPUT_POST, 'classroom_id', FILTER_SANITIZE_NUMBER_INT);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البيانات
    if (!empty($schedule_id) && !empty($course_id) && !empty($day) && !empty($start_time) && !empty($end_time) && !empty($classroom_id) && !empty($type)) {
        // تعديل الموعد
        $result = edit_schedule($db, $schedule_id, $teacher_id, $course_id, $day, $start_time, $end_time, $classroom_id, $type);
        
        if ($result) {
            $success_message = t('schedule_updated_successfully');
            // تحديث الجدول الدراسي
            $schedule = get_teacher_schedule($db, $teacher_id);
        } else {
            $error_message = t('failed_to_update_schedule');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// تنظيم الجدول الدراسي حسب اليوم
$schedule_by_day = [
    'sunday' => [],
    'monday' => [],
    'tuesday' => [],
    'wednesday' => [],
    'thursday' => [],
    'friday' => [],
    'saturday' => []
];

foreach ($schedule as $item) {
    $schedule_by_day[$item['day']][] = $item;
}

// إغلاق اتصال قاعدة البيانات
$dsn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('schedule'); ?></title>
    
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
        
        .schedule-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .theme-dark .schedule-container {
            background-color: var(--dark-bg);
        }
        
        .schedule-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .schedule-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .schedule-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .schedule-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .schedule-tabs {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            color: var(--gray-color);
            border-bottom: 3px solid transparent;
        }
        
        .schedule-tab:hover {
            color: var(--text-color);
        }
        
        .schedule-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .schedule-content {
            padding: 1.5rem;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .schedule-table th,
        .schedule-table td {
            padding: 1rem;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .schedule-table th,
        .theme-dark .schedule-table td {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-table th {
            background-color: rgba(0, 0, 0, 0.02);
            font-weight: 600;
        }
        
        .theme-dark .schedule-table th {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .schedule-table th:first-child {
            width: 100px;
        }
        
        .schedule-item {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .schedule-item:last-child {
            margin-bottom: 0;
        }
        
        .schedule-item.lecture {
            background-color: #003049;
        }
        
        .schedule-item.lab {
            background-color: #669bbc;
        }
        
        .schedule-item.tutorial {
            background-color: #f77f00;
        }
        
        .schedule-item.exam {
            background-color: #d62828;
        }
        
        .schedule-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .schedule-item-time {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .schedule-item-location {
            font-size: 0.85rem;
            margin-bottom: 0;
        }
        
        .schedule-item-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
        }
        
        [dir="rtl"] .schedule-item-actions {
            right: auto;
            left: 0.5rem;
        }
        
        .schedule-item-actions button {
            background: none;
            border: none;
            color: white;
            font-size: 0.85rem;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .schedule-item-actions button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .schedule-list {
            margin-top: 2rem;
        }
        
        .schedule-list-title {
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .schedule-list-title {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-list-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .theme-dark .schedule-list-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-list-item:last-child {
            border-bottom: none;
        }
        
        .schedule-list-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .schedule-list-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .schedule-list-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        [dir="rtl"] .schedule-list-item-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .schedule-list-item-icon.lecture {
            background-color: #003049;
        }
        
        .schedule-list-item-icon.lab {
            background-color: #669bbc;
        }
        
        .schedule-list-item-icon.tutorial {
            background-color: #f77f00;
        }
        
        .schedule-list-item-icon.exam {
            background-color: #d62828;
        }
        
        .schedule-list-item-content {
            flex-grow: 1;
        }
        
        .schedule-list-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .schedule-list-item-details {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .schedule-list-item-detail {
            display: flex;
            align-items: center;
        }
        
        .schedule-list-item-detail i {
            margin-right: 0.5rem;
            opacity: 0.7;
        }
        
        [dir="rtl"] .schedule-list-item-detail i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .schedule-list-item-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .schedule-list-item-actions button {
            background: none;
            border: none;
            color: var(--gray-color);
            font-size: 1rem;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .schedule-list-item-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .schedule-list-item-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .schedule-list-item-actions button.delete:hover {
            color: #dc3545;
        }
        
        .schedule-calendar {
            margin-top: 2rem;
        }
        
        .schedule-calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .schedule-calendar-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .schedule-calendar-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .schedule-calendar-actions button {
            background: none;
            border: none;
            color: var(--gray-color);
            font-size: 1rem;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .schedule-calendar-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .schedule-calendar-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .schedule-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }
        
        .schedule-calendar-day {
            padding: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            min-height: 100px;
        }
        
        .theme-dark .schedule-calendar-day {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-calendar-day-header {
            text-align: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .schedule-calendar-day-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .schedule-calendar-day-content {
            font-size: 0.85rem;
        }
        
        .schedule-calendar-item {
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            color: white;
        }
        
        .schedule-calendar-item:last-child {
            margin-bottom: 0;
        }
        
        .schedule-calendar-item.lecture {
            background-color: #003049;
        }
        
        .schedule-calendar-item.lab {
            background-color: #669bbc;
        }
        
        .schedule-calendar-item.tutorial {
            background-color: #f77f00;
        }
        
        .schedule-calendar-item.exam {
            background-color: #d62828;
        }
        
        .schedule-calendar-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .schedule-calendar-item-time {
            margin-bottom: 0;
        }
        
        .schedule-form-group {
            margin-bottom: 1rem;
        }
        
        .schedule-form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .schedule-form-input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .schedule-form-input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .schedule-form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .schedule-form-select {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        
        .theme-dark .schedule-form-select {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .schedule-form-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .schedule-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        
        .schedule-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .schedule-legend-item {
            display: flex;
            align-items: center;
        }
        
        .schedule-legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .schedule-legend-color {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .schedule-legend-color.lecture {
            background-color: #003049;
        }
        
        .schedule-legend-color.lab {
            background-color: #669bbc;
        }
        
        .schedule-legend-color.tutorial {
            background-color: #f77f00;
        }
        
        .schedule-legend-color.exam {
            background-color: #d62828;
        }
        
        .schedule-legend-label {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .schedule-print {
            margin-top: 2rem;
            text-align: center;
        }
        
        .schedule-print-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .schedule-print-button i {
            font-size: 1.25rem;
        }
        
        @media print {
            .sidebar,
            .navbar-top,
            .schedule-actions,
            .schedule-tabs,
            .schedule-form,
            .schedule-print {
                display: none !important;
            }
            
            .content {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .schedule-container {
                box-shadow: none !important;
            }
            
            .schedule-header {
                border-bottom: 2px solid #000 !important;
            }
            
            .schedule-table th,
            .schedule-table td {
                border: 1px solid #000 !important;
            }
            
            .schedule-item {
                break-inside: avoid;
            }
            
            .schedule-item-actions {
                display: none !important;
            }
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
                    <a class="nav-link active" href="teacher_schedule.php">
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
                <h1 class="h3"><?php echo t('schedule'); ?></h1>
                <p class="text-muted"><?php echo t('manage_your_teaching_schedule'); ?></p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <i class="fas fa-plus me-1"></i> <?php echo t('add_schedule'); ?>
                </button>
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
        
        <!-- الجدول الدراسي -->
        <div class="schedule-container">
            <div class="schedule-header">
                <h2 class="schedule-title"><?php echo t('weekly_schedule'); ?></h2>
                <div class="schedule-actions">
                    <button class="btn btn-outline-primary btn-sm" id="printScheduleBtn">
                        <i class="fas fa-print me-1"></i> <?php echo t('print_schedule'); ?>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i> <?php echo t('export'); ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                            <li><a class="dropdown-item" href="#" id="exportPDF"><?php echo t('export_as_pdf'); ?></a></li>
                            <li><a class="dropdown-item" href="#" id="exportICS"><?php echo t('export_to_calendar'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="schedule-tabs">
                <div class="schedule-tab active" data-tab="table"><?php echo t('table_view'); ?></div>
                <div class="schedule-tab" data-tab="list"><?php echo t('list_view'); ?></div>
                <div class="schedule-tab" data-tab="calendar"><?php echo t('calendar_view'); ?></div>
            </div>
            
            <div class="schedule-content" id="tableView">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th><?php echo t('time'); ?></th>
                            <th><?php echo t('sunday'); ?></th>
                            <th><?php echo t('monday'); ?></th>
                            <th><?php echo t('tuesday'); ?></th>
                            <th><?php echo t('wednesday'); ?></th>
                            <th><?php echo t('thursday'); ?></th>
                            <th><?php echo t('friday'); ?></th>
                            <th><?php echo t('saturday'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // تحديد أوقات اليوم
                        $times = [
                            '08:00 - 09:00',
                            '09:00 - 10:00',
                            '10:00 - 11:00',
                            '11:00 - 12:00',
                            '12:00 - 13:00',
                            '13:00 - 14:00',
                            '14:00 - 15:00',
                            '15:00 - 16:00',
                            '16:00 - 17:00',
                            '17:00 - 18:00'
                        ];
                        
                        // تحديد أيام الأسبوع
                        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                        
                        // إنشاء صفوف الجدول
                        foreach ($times as $time) {
                            echo '<tr>';
                            echo '<td>' . $time . '</td>';
                            
                            // استخراج وقت البداية والنهاية
                            list($start_time, $end_time) = explode(' - ', $time);
                            
                            // إنشاء أعمدة الجدول
                            foreach ($days as $day) {
                                echo '<td>';
                                
                                // البحث عن المواعيد في هذا اليوم والوقت
                                $found = false;
                                foreach ($schedule_by_day[$day] as $item) {
                                    // التحقق مما إذا كان الموعد يقع في هذا الوقت
                                    if (($item['start_time'] <= $start_time && $item['end_time'] > $start_time) || 
                                        ($item['start_time'] >= $start_time && $item['start_time'] < $end_time)) {
                                        $found = true;
                                        echo '<div class="schedule-item ' . $item['type'] . '">';
                                        echo '<div class="schedule-item-title">' . $item['course_name'] . '</div>';
                                        echo '<div class="schedule-item-time">' . $item['start_time'] . ' - ' . $item['end_time'] . '</div>';
                                        echo '<div class="schedule-item-location">' . $item['classroom_name'] . '</div>';
                                        echo '<div class="schedule-item-actions">';
                                        echo '<button type="button" class="edit-schedule" data-bs-toggle="modal" data-bs-target="#editScheduleModal" data-id="' . $item['id'] . '" data-course="' . $item['course_id'] . '" data-day="' . $item['day'] . '" data-start="' . $item['start_time'] . '" data-end="' . $item['end_time'] . '" data-classroom="' . $item['classroom_id'] . '" data-type="' . $item['type'] . '"><i class="fas fa-edit"></i></button>';
                                        echo '<button type="button" class="delete-schedule" data-bs-toggle="modal" data-bs-target="#deleteScheduleModal" data-id="' . $item['id'] . '"><i class="fas fa-trash-alt"></i></button>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                                
                                if (!$found) {
                                    echo '&nbsp;';
                                }
                                
                                echo '</td>';
                            }
                            
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
                
                <div class="schedule-legend">
                    <div class="schedule-legend-item">
                        <div class="schedule-legend-color lecture"></div>
                        <div class="schedule-legend-label"><?php echo t('lecture'); ?></div>
                    </div>
                    <div class="schedule-legend-item">
                        <div class="schedule-legend-color lab"></div>
                        <div class="schedule-legend-label"><?php echo t('lab'); ?></div>
                    </div>
                    <div class="schedule-legend-item">
                        <div class="schedule-legend-color tutorial"></div>
                        <div class="schedule-legend-label"><?php echo t('tutorial'); ?></div>
                    </div>
                    <div class="schedule-legend-item">
                        <div class="schedule-legend-color exam"></div>
                        <div class="schedule-legend-label"><?php echo t('exam'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="schedule-content d-none" id="listView">
                <?php
                // تحديد أيام الأسبوع
                $days_translated = [
                    'sunday' => t('sunday'),
                    'monday' => t('monday'),
                    'tuesday' => t('tuesday'),
                    'wednesday' => t('wednesday'),
                    'thursday' => t('thursday'),
                    'friday' => t('friday'),
                    'saturday' => t('saturday')
                ];
                
                // عرض المواعيد حسب اليوم
                foreach ($days as $day) {
                    if (count($schedule_by_day[$day]) > 0) {
                        echo '<div class="schedule-list">';
                        echo '<h3 class="schedule-list-title">' . $days_translated[$day] . '</h3>';
                        
                        // ترتيب المواعيد حسب وقت البداية
                        usort($schedule_by_day[$day], function($a, $b) {
                            return strcmp($a['start_time'], $b['start_time']);
                        });
                        
                        foreach ($schedule_by_day[$day] as $item) {
                            echo '<div class="schedule-list-item">';
                            echo '<div class="schedule-list-item-icon ' . $item['type'] . '">';
                            
                            // تحديد الأيقونة حسب النوع
                            if ($item['type'] === 'lecture') {
                                echo '<i class="fas fa-chalkboard-teacher"></i>';
                            } elseif ($item['type'] === 'lab') {
                                echo '<i class="fas fa-flask"></i>';
                            } elseif ($item['type'] === 'tutorial') {
                                echo '<i class="fas fa-users"></i>';
                            } elseif ($item['type'] === 'exam') {
                                echo '<i class="fas fa-file-alt"></i>';
                            }
                            
                            echo '</div>';
                            echo '<div class="schedule-list-item-content">';
                            echo '<h4 class="schedule-list-item-title">' . $item['course_name'] . '</h4>';
                            echo '<div class="schedule-list-item-details">';
                            echo '<div class="schedule-list-item-detail"><i class="fas fa-clock"></i> ' . $item['start_time'] . ' - ' . $item['end_time'] . '</div>';
                            echo '<div class="schedule-list-item-detail"><i class="fas fa-map-marker-alt"></i> ' . $item['classroom_name'] . '</div>';
                            echo '<div class="schedule-list-item-detail"><i class="fas fa-tag"></i> ' . t($item['type']) . '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '<div class="schedule-list-item-actions">';
                            echo '<button type="button" class="edit-schedule" data-bs-toggle="modal" data-bs-target="#editScheduleModal" data-id="' . $item['id'] . '" data-course="' . $item['course_id'] . '" data-day="' . $item['day'] . '" data-start="' . $item['start_time'] . '" data-end="' . $item['end_time'] . '" data-classroom="' . $item['classroom_id'] . '" data-type="' . $item['type'] . '"><i class="fas fa-edit"></i></button>';
                            echo '<button type="button" class="delete-schedule delete" data-bs-toggle="modal" data-bs-target="#deleteScheduleModal" data-id="' . $item['id'] . '"><i class="fas fa-trash-alt"></i></button>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                }
                
                // إذا لم يكن هناك مواعيد
                $total_schedule = 0;
                foreach ($schedule_by_day as $day_schedule) {
                    $total_schedule += count($day_schedule);
                }
                
                if ($total_schedule === 0) {
                    echo '<div class="empty-state">';
                    echo '<div class="empty-state-icon">';
                    echo '<i class="fas fa-calendar-alt"></i>';
                    echo '</div>';
                    echo '<h3 class="empty-state-title">' . t('no_schedule_yet') . '</h3>';
                    echo '<p class="empty-state-text">' . t('no_schedule_message') . '</p>';
                    echo '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">';
                    echo '<i class="fas fa-plus me-1"></i> ' . t('add_schedule') . '</button>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="schedule-content d-none" id="calendarView">
                <div class="schedule-calendar">
                    <div class="schedule-calendar-header">
                        <h3 class="schedule-calendar-title"><?php echo t('weekly_view'); ?></h3>
                        <div class="schedule-calendar-actions">
                            <button type="button" id="prevWeek"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" id="nextWeek"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    
                    <div class="schedule-calendar-grid">
                        <?php
                        // عرض أيام الأسبوع
                        foreach ($days as $day) {
                            echo '<div class="schedule-calendar-day">';
                            echo '<div class="schedule-calendar-day-header">' . $days_translated[$day] . '</div>';
                            echo '<div class="schedule-calendar-day-content">';
                            
                            // ترتيب المواعيد حسب وقت البداية
                            usort($schedule_by_day[$day], function($a, $b) {
                                return strcmp($a['start_time'], $b['start_time']);
                            });
                            
                            foreach ($schedule_by_day[$day] as $item) {
                                echo '<div class="schedule-calendar-item ' . $item['type'] . '">';
                                echo '<div class="schedule-calendar-item-title">' . $item['course_name'] . '</div>';
                                echo '<div class="schedule-calendar-item-time">' . $item['start_time'] . ' - ' . $item['end_time'] . '</div>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- زر الطباعة -->
        <div class="schedule-print">
            <button class="btn btn-outline-primary schedule-print-button" id="printScheduleBtn2">
                <i class="fas fa-print"></i> <?php echo t('print_schedule'); ?>
            </button>
        </div>
    </div>
    
    <!-- مودال إضافة موعد جديد -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addScheduleModalLabel"><?php echo t('add_new_schedule'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="schedule-form-group">
                            <label for="course_id" class="schedule-form-label"><?php echo t('course'); ?>:</label>
                            <select name="course_id" id="course_id" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_course'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="schedule-form-group">
                            <label for="day" class="schedule-form-label"><?php echo t('day'); ?>:</label>
                            <select name="day" id="day" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_day'); ?></option>
                                <option value="sunday"><?php echo t('sunday'); ?></option>
                                <option value="monday"><?php echo t('monday'); ?></option>
                                <option value="tuesday"><?php echo t('tuesday'); ?></option>
                                <option value="wednesday"><?php echo t('wednesday'); ?></option>
                                <option value="thursday"><?php echo t('thursday'); ?></option>
                                <option value="friday"><?php echo t('friday'); ?></option>
                                <option value="saturday"><?php echo t('saturday'); ?></option>
                            </select>
                        </div>
                        <div class="schedule-form-group">
                            <label for="start_time" class="schedule-form-label"><?php echo t('start_time'); ?>:</label>
                            <input type="time" name="start_time" id="start_time" class="schedule-form-input" required>
                        </div>
                        <div class="schedule-form-group">
                            <label for="end_time" class="schedule-form-label"><?php echo t('end_time'); ?>:</label>
                            <input type="time" name="end_time" id="end_time" class="schedule-form-input" required>
                        </div>
                        <div class="schedule-form-group">
                            <label for="classroom_id" class="schedule-form-label"><?php echo t('classroom'); ?>:</label>
                            <select name="classroom_id" id="classroom_id" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_classroom'); ?></option>
                                <?php foreach ($classrooms as $classroom): ?>
                                    <option value="<?php echo $classroom['id']; ?>"><?php echo $classroom['name']; ?> (<?php echo $classroom['building']; ?> - <?php echo $classroom['floor']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="schedule-form-group">
                            <label for="type" class="schedule-form-label"><?php echo t('type'); ?>:</label>
                            <select name="type" id="type" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_type'); ?></option>
                                <option value="lecture"><?php echo t('lecture'); ?></option>
                                <option value="lab"><?php echo t('lab'); ?></option>
                                <option value="tutorial"><?php echo t('tutorial'); ?></option>
                                <option value="exam"><?php echo t('exam'); ?></option>
                            </select>
                        </div>
                        <div class="schedule-form-actions">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                            <button type="submit" name="add_schedule" class="btn btn-primary"><?php echo t('add'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل موعد -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel"><?php echo t('edit_schedule'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <input type="hidden" name="schedule_id" id="edit_schedule_id">
                        <div class="schedule-form-group">
                            <label for="edit_course_id" class="schedule-form-label"><?php echo t('course'); ?>:</label>
                            <select name="course_id" id="edit_course_id" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_course'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="schedule-form-group">
                            <label for="edit_day" class="schedule-form-label"><?php echo t('day'); ?>:</label>
                            <select name="day" id="edit_day" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_day'); ?></option>
                                <option value="sunday"><?php echo t('sunday'); ?></option>
                                <option value="monday"><?php echo t('monday'); ?></option>
                                <option value="tuesday"><?php echo t('tuesday'); ?></option>
                                <option value="wednesday"><?php echo t('wednesday'); ?></option>
                                <option value="thursday"><?php echo t('thursday'); ?></option>
                                <option value="friday"><?php echo t('friday'); ?></option>
                                <option value="saturday"><?php echo t('saturday'); ?></option>
                            </select>
                        </div>
                        <div class="schedule-form-group">
                            <label for="edit_start_time" class="schedule-form-label"><?php echo t('start_time'); ?>:</label>
                            <input type="time" name="start_time" id="edit_start_time" class="schedule-form-input" required>
                        </div>
                        <div class="schedule-form-group">
                            <label for="edit_end_time" class="schedule-form-label"><?php echo t('end_time'); ?>:</label>
                            <input type="time" name="end_time" id="edit_end_time" class="schedule-form-input" required>
                        </div>
                        <div class="schedule-form-group">
                            <label for="edit_classroom_id" class="schedule-form-label"><?php echo t('classroom'); ?>:</label>
                            <select name="classroom_id" id="edit_classroom_id" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_classroom'); ?></option>
                                <?php foreach ($classrooms as $classroom): ?>
                                    <option value="<?php echo $classroom['id']; ?>"><?php echo $classroom['name']; ?> (<?php echo $classroom['building']; ?> - <?php echo $classroom['floor']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="schedule-form-group">
                            <label for="edit_type" class="schedule-form-label"><?php echo t('type'); ?>:</label>
                            <select name="type" id="edit_type" class="schedule-form-select" required>
                                <option value=""><?php echo t('select_type'); ?></option>
                                <option value="lecture"><?php echo t('lecture'); ?></option>
                                <option value="lab"><?php echo t('lab'); ?></option>
                                <option value="tutorial"><?php echo t('tutorial'); ?></option>
                                <option value="exam"><?php echo t('exam'); ?></option>
                            </select>
                        </div>
                        <div class="schedule-form-actions">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                            <button type="submit" name="edit_schedule" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف موعد -->
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-labelledby="deleteScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteScheduleModalLabel"><?php echo t('delete_schedule'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo t('delete_schedule_confirmation'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <form action="" method="post">
                        <input type="hidden" name="schedule_id" id="delete_schedule_id">
                        <button type="submit" name="delete_schedule" class="btn btn-danger"><?php echo t('delete'); ?></button>
                    </form>
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
            
            // تبديل علامات التبويب في الجدول الدراسي
            const scheduleTabs = document.querySelectorAll('.schedule-tab');
            const scheduleContents = document.querySelectorAll('.schedule-content');
            
            scheduleTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // إزالة الفئة النشطة من جميع علامات التبويب
                    scheduleTabs.forEach(t => t.classList.remove('active'));
                    
                    // إضافة الفئة النشطة إلى علامة التبويب المحددة
                    this.classList.add('active');
                    
                    // إخفاء جميع محتويات الجدول
                    scheduleContents.forEach(content => content.classList.add('d-none'));
                    
                    // إظهار محتوى الجدول المناسب
                    const tabName = this.getAttribute('data-tab');
                    document.getElementById(`${tabName}View`).classList.remove('d-none');
                });
            });
            
            // تعبئة بيانات مودال تعديل الموعد
            const editButtons = document.querySelectorAll('.edit-schedule');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const course = this.getAttribute('data-course');
                    const day = this.getAttribute('data-day');
                    const start = this.getAttribute('data-start');
                    const end = this.getAttribute('data-end');
                    const classroom = this.getAttribute('data-classroom');
                    const type = this.getAttribute('data-type');
                    
                    document.getElementById('edit_schedule_id').value = id;
                    document.getElementById('edit_course_id').value = course;
                    document.getElementById('edit_day').value = day;
                    document.getElementById('edit_start_time').value = start;
                    document.getElementById('edit_end_time').value = end;
                    document.getElementById('edit_classroom_id').value = classroom;
                    document.getElementById('edit_type').value = type;
                });
            });
            
            // تعبئة بيانات مودال حذف الموعد
            const deleteButtons = document.querySelectorAll('.delete-schedule');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    document.getElementById('delete_schedule_id').value = id;
                });
            });
            
            // طباعة الجدول الدراسي
            const printButtons = document.querySelectorAll('#printScheduleBtn, #printScheduleBtn2');
            
            printButtons.forEach(button => {
                button.addEventListener('click', function() {
                    window.print();
                });
            });
            
            // تصدير الجدول الدراسي كملف PDF
            document.getElementById('exportPDF').addEventListener('click', function(e) {
                e.preventDefault();
                alert('سيتم تصدير الجدول الدراسي كملف PDF قريبًا.');
            });
            
            // تصدير الجدول الدراسي إلى التقويم
            document.getElementById('exportICS').addEventListener('click', function(e) {
                e.preventDefault();
                alert('سيتم تصدير الجدول الدراسي إلى التقويم قريبًا.');
            });
            
            // التحقق من صحة وقت البداية والنهاية
            const startTimeInputs = document.querySelectorAll('#start_time, #edit_start_time');
            const endTimeInputs = document.querySelectorAll('#end_time, #edit_end_time');
            
            startTimeInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    const startTime = this.value;
                    const endTime = endTimeInputs[index].value;
                    
                    if (startTime && endTime && startTime >= endTime) {
                        alert('يجب أن يكون وقت البداية قبل وقت النهاية.');
                        this.value = '';
                    }
                });
            });
            
            endTimeInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    const startTime = startTimeInputs[index].value;
                    const endTime = this.value;
                    
                    if (startTime && endTime && startTime >= endTime) {
                        alert('يجب أن يكون وقت النهاية بعد وقت البداية.');
                        this.value = '';
                    }
                });
            });
        });
    </script>
</body>
</html>
