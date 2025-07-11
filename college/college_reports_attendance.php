<?php
/**
 * صفحة تقارير الحضور والانصراف في نظام UniverBoard
 * تتيح لمسؤول الكلية عرض وتحليل بيانات حضور الطلاب والمعلمين
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

// معالجة طلبات الفلترة
$filter_type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?: 'students'; // students or teachers
$filter_department = filter_input(INPUT_GET, 'department', FILTER_SANITIZE_NUMBER_INT);
$filter_program = filter_input(INPUT_GET, 'program', FILTER_SANITIZE_NUMBER_INT);
$filter_course = filter_input(INPUT_GET, 'course', FILTER_SANITIZE_NUMBER_INT);
$filter_teacher = filter_input(INPUT_GET, 'teacher', FILTER_SANITIZE_NUMBER_INT);
$filter_student = filter_input(INPUT_GET, 'student', FILTER_SANITIZE_NUMBER_INT);
$filter_date_from = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_STRING);
$filter_date_to = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_STRING);

// الحصول على بيانات الحضور بناءً على الفلاتر
$attendance_data = get_attendance_report($db, $college_id, $filter_type, $filter_department, $filter_program, $filter_course, $filter_teacher, $filter_student, $filter_date_from, $filter_date_to);

// الحصول على قوائم الفلاتر (الأقسام، البرامج، المقررات، المعلمين، الطلاب)
$departments = get_college_departments($db, $college_id);
$programs = $filter_department ? get_department_programs($db, $filter_department) : [];
$courses = $filter_program ? get_program_courses($db, $filter_program) : ($filter_department ? get_department_courses($db, $filter_department) : []);
$teachers = get_college_teachers_list($db, $college_id, $filter_department);
$students = get_college_students_list($db, $college_id, $filter_department, $filter_program);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_attendance_report($db, $college_id, $type, $department_id, $program_id, $course_id, $teacher_id, $student_id, $date_from, $date_to) {
    // في الواقع، يجب استرجاع بيانات الحضور من قاعدة البيانات بناءً على الفلاتر
    // بيانات وهمية للعرض
    $data = [];
    $start_date = $date_from ? new DateTime($date_from) : new DateTime('-1 month');
    $end_date = $date_to ? new DateTime($date_to) : new DateTime();
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));

    if ($type === 'students') {
        $students_list = get_college_students_list($db, $college_id, $department_id, $program_id);
        $target_students = $student_id ? array_filter($students_list, function($s) use ($student_id) { return $s['id'] == $student_id; }) : $students_list;
        
        foreach ($target_students as $student) {
            foreach ($period as $date) {
                if ($date->format('N') >= 6) continue; // Skip weekends
                $courses_for_day = get_student_courses_for_day($db, $student['id'], $date->format('Y-m-d'));
                if ($course_id) {
                    $courses_for_day = array_filter($courses_for_day, function($c) use ($course_id) { return $c['id'] == $course_id; });
                }
                
                foreach ($courses_for_day as $course) {
                    $status = ['present', 'absent', 'late', 'excused'][rand(0, 3)];
                    $data[] = [
                        'date' => $date->format('Y-m-d'),
                        'user_id' => $student['id'],
                        'user_name' => $student['name'],
                        'user_type' => 'student',
                        'course_id' => $course['id'],
                        'course_name' => $course['name'],
                        'teacher_name' => $course['teacher_name'],
                        'status' => $status,
                        'notes' => $status === 'excused' ? 'عذر طبي' : ($status === 'late' ? 'تأخر 15 دقيقة' : '')
                    ];
                }
            }
        }
    } else { // teachers
        $teachers_list = get_college_teachers_list($db, $college_id, $department_id);
        $target_teachers = $teacher_id ? array_filter($teachers_list, function($t) use ($teacher_id) { return $t['id'] == $teacher_id; }) : $teachers_list;
        
        foreach ($target_teachers as $teacher) {
            foreach ($period as $date) {
                if ($date->format('N') >= 6) continue; // Skip weekends
                $courses_for_day = get_teacher_courses_for_day($db, $teacher['id'], $date->format('Y-m-d'));
                if ($course_id) {
                    $courses_for_day = array_filter($courses_for_day, function($c) use ($course_id) { return $c['id'] == $course_id; });
                }
                
                foreach ($courses_for_day as $course) {
                    $status = ['present', 'absent', 'late', 'excused'][rand(0, 3)];
                    $data[] = [
                        'date' => $date->format('Y-m-d'),
                        'user_id' => $teacher['id'],
                        'user_name' => $teacher['name'],
                        'user_type' => 'teacher',
                        'course_id' => $course['id'],
                        'course_name' => $course['name'],
                        'teacher_name' => $teacher['name'], // Redundant but for consistency
                        'status' => $status,
                        'notes' => $status === 'excused' ? 'إجازة مرضية' : ($status === 'late' ? 'تأخر 10 دقائق' : '')
                    ];
                }
            }
        }
    }
    
    // Sort data by date
    usort($data, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    return $data;
}

function get_college_departments($db, $college_id) {
    // في الواقع، يجب استرجاع الأقسام من قاعدة البيانات
    return [
        ['id' => 1, 'name' => 'قسم علوم الحاسب'],
        ['id' => 2, 'name' => 'قسم نظم المعلومات'],
        ['id' => 3, 'name' => 'قسم هندسة البرمجيات']
    ];
}

function get_department_programs($db, $department_id) {
    // في الواقع، يجب استرجاع البرامج من قاعدة البيانات
    if ($department_id == 1) {
        return [['id' => 1, 'name' => 'بكالوريوس علوم الحاسب'], ['id' => 2, 'name' => 'ماجستير علوم الحاسب']];
    } elseif ($department_id == 2) {
        return [['id' => 3, 'name' => 'بكالوريوس نظم المعلومات الإدارية']];
    } else {
        return [];
    }
}

function get_program_courses($db, $program_id) {
    // في الواقع، يجب استرجاع المقررات من قاعدة البيانات
    if ($program_id == 1) {
        return [['id' => 101, 'name' => 'مقدمة في البرمجة'], ['id' => 102, 'name' => 'هياكل البيانات']];
    } elseif ($program_id == 3) {
        return [['id' => 201, 'name' => 'تحليل وتصميم النظم'], ['id' => 202, 'name' => 'قواعد البيانات']];
    } else {
        return [];
    }
}

function get_department_courses($db, $department_id) {
    // في الواقع، يجب استرجاع المقررات من قاعدة البيانات
    if ($department_id == 1) {
        return [['id' => 101, 'name' => 'مقدمة في البرمجة'], ['id' => 102, 'name' => 'هياكل البيانات'], ['id' => 103, 'name' => 'الذكاء الاصطناعي']];
    } elseif ($department_id == 2) {
        return [['id' => 201, 'name' => 'تحليل وتصميم النظم'], ['id' => 202, 'name' => 'قواعد البيانات']];
    } else {
        return [];
    }
}

function get_college_teachers_list($db, $college_id, $department_id = null) {
    // في الواقع، يجب استرجاع المعلمين من قاعدة البيانات
    $teachers = [
        ['id' => 1, 'name' => 'د. محمد العمري', 'department_id' => 1],
        ['id' => 2, 'name' => 'د. سارة الأحمد', 'department_id' => 1],
        ['id' => 3, 'name' => 'د. فهد السالم', 'department_id' => 2],
        ['id' => 4, 'name' => 'د. نورة القحطاني', 'department_id' => 2]
    ];
    if ($department_id) {
        return array_filter($teachers, function($t) use ($department_id) { return $t['department_id'] == $department_id; });
    }
    return $teachers;
}

function get_college_students_list($db, $college_id, $department_id = null, $program_id = null) {
    // في الواقع، يجب استرجاع الطلاب من قاعدة البيانات
    $students = [
        ['id' => 1001, 'name' => 'أحمد عبدالله', 'department_id' => 1, 'program_id' => 1],
        ['id' => 1002, 'name' => 'فاطمة خالد', 'department_id' => 1, 'program_id' => 1],
        ['id' => 1003, 'name' => 'علي حسن', 'department_id' => 1, 'program_id' => 2],
        ['id' => 1004, 'name' => 'مريم يوسف', 'department_id' => 2, 'program_id' => 3],
        ['id' => 1005, 'name' => 'خالد إبراهيم', 'department_id' => 2, 'program_id' => 3]
    ];
    if ($program_id) {
        return array_filter($students, function($s) use ($program_id) { return $s['program_id'] == $program_id; });
    } elseif ($department_id) {
        return array_filter($students, function($s) use ($department_id) { return $s['department_id'] == $department_id; });
    }
    return $students;
}

function get_student_courses_for_day($db, $student_id, $date) {
    // في الواقع، يجب استرجاع مقررات الطالب لهذا اليوم من الجدول الدراسي
    return [
        ['id' => 101, 'name' => 'مقدمة في البرمجة', 'teacher_name' => 'د. محمد العمري'],
        ['id' => 102, 'name' => 'هياكل البيانات', 'teacher_name' => 'د. سارة الأحمد']
    ];
}

function get_teacher_courses_for_day($db, $teacher_id, $date) {
    // في الواقع، يجب استرجاع مقررات المعلم لهذا اليوم من الجدول الدراسي
    if ($teacher_id == 1) {
        return [['id' => 101, 'name' => 'مقدمة في البرمجة']];
    } elseif ($teacher_id == 2) {
        return [['id' => 102, 'name' => 'هياكل البيانات']];
    } else {
        return [];
    }
}

function get_college_admin_info($db, $admin_id) {
    // في الواقع، يجب استرجاع معلومات المسؤول من قاعدة البيانات
    return [
        'id' => $admin_id,
        'name' => 'د. عبدالله العمري',
        'email' => 'admin@example.com',
        'college_id' => 1,
        'profile_image' => 'assets/images/admin.jpg'
    ];
}

function get_college_info($db, $college_id) {
    // في الواقع، يجب استرجاع معلومات الكلية من قاعدة البيانات
    return [
        'id' => $college_id,
        'name' => 'كلية علوم الحاسب والمعلومات',
        'code' => 'CS',
        'description' => 'كلية متخصصة في علوم الحاسب وتقنية المعلومات'
    ];
}

// دالة مساعدة لتنسيق التاريخ
function format_date($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// دالة مساعدة لترجمة حالة الحضور
function translate_attendance_status($status) {
    switch ($status) {
        case 'present': return t('present');
        case 'absent': return t('absent');
        case 'late': return t('late');
        case 'excused': return t('excused');
        default: return $status;
    }
}

// حساب إحصائيات الحضور
$stats = [
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'excused' => 0,
    'total' => count($attendance_data)
];
foreach ($attendance_data as $record) {
    if (isset($stats[$record['status']])) {
        $stats[$record['status']]++;
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('attendance_reports'); ?></title>
    
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    
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
        
        /* تنسيقات خاصة بصفحة تقارير الحضور */
        .filter-form .row > div {
            margin-bottom: 1rem;
        }
        
        .stats-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 0.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stats-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card p {
            font-size: 0.9rem;
            margin-bottom: 0;
            opacity: 0.8;
        }
        
        .stats-present {
            background-color: #28a745;
        }
        
        .stats-absent {
            background-color: #dc3545;
        }
        
        .stats-late {
            background-color: #ffc107;
            color: #333;
        }
        
        .stats-excused {
            background-color: #17a2b8;
        }
        
        .stats-total {
            background-color: #6c757d;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-bottom: 2rem;
        }
        
        .table-responsive {
            margin-top: 2rem;
        }
        
        .table th, .table td {
            vertical-align: middle;
        }
        
        .table thead th {
            font-weight: 600;
            background-color: rgba(0, 48, 73, 0.05);
            border-bottom-width: 1px;
        }
        
        .theme-dark .table thead th {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-color);
        }
        
        .theme-dark .table {
            color: var(--text-color);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-accent-bg: rgba(255, 255, 255, 0.03);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
            min-width: 70px;
            text-align: center;
        }
        
        .status-present {
            background-color: #28a745;
        }
        
        .status-absent {
            background-color: #dc3545;
        }
        
        .status-late {
            background-color: #ffc107;
            color: #333;
        }
        
        .status-excused {
            background-color: #17a2b8;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .page-link {
            color: var(--primary-color);
            border-color: rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .page-link {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-item.disabled .page-link {
            color: var(--gray-color);
        }
        
        .theme-dark .page-item.disabled .page-link {
            background-color: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.5);
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
                    <a class="nav-link" href="college_reports_academic.php">
                        <i class="fas fa-chart-line"></i> <?php echo t('academic_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="college_reports_attendance.php">
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
                    <!-- قائمة الإشعارات -->
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">3</span>
                    </a>
                    <!-- قائمة الرسائل -->
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title"><?php echo t('attendance_reports'); ?></h1>
                    <p class="page-subtitle"><?php echo t('view_and_analyze_attendance_data'); ?></p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary me-2">
                        <i class="fas fa-print me-1"></i> <?php echo t('print_report'); ?>
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> <?php echo t('export_to_excel'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- نموذج الفلترة -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i> <?php echo t('filter_options'); ?></h5>
            </div>
            <div class="card-body">
                <form action="" method="get" class="filter-form">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="type" class="form-label"><?php echo t('report_type'); ?></label>
                            <select class="form-select" id="type" name="type">
                                <option value="students" <?php echo $filter_type === 'students' ? 'selected' : ''; ?>><?php echo t('students_attendance'); ?></option>
                                <option value="teachers" <?php echo $filter_type === 'teachers' ? 'selected' : ''; ?>><?php echo t('teachers_attendance'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="department" class="form-label"><?php echo t('department'); ?></label>
                            <select class="form-select" id="department" name="department">
                                <option value=""><?php echo t('all_departments'); ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>><?php echo $dept['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="program" class="form-label"><?php echo t('academic_program'); ?></label>
                            <select class="form-select" id="program" name="program" <?php echo empty($programs) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_programs'); ?></option>
                                <?php foreach ($programs as $prog): ?>
                                    <option value="<?php echo $prog['id']; ?>" <?php echo $filter_program == $prog['id'] ? 'selected' : ''; ?>><?php echo $prog['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="course" class="form-label"><?php echo t('course'); ?></label>
                            <select class="form-select" id="course" name="course" <?php echo empty($courses) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_courses'); ?></option>
                                <?php foreach ($courses as $crs): ?>
                                    <option value="<?php echo $crs['id']; ?>" <?php echo $filter_course == $crs['id'] ? 'selected' : ''; ?>><?php echo $crs['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3" id="teacherFilterContainer" style="display: <?php echo $filter_type === 'teachers' ? 'block' : 'none'; ?>;">
                            <label for="teacher" class="form-label"><?php echo t('teacher'); ?></label>
                            <select class="form-select" id="teacher" name="teacher" <?php echo empty($teachers) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_teachers'); ?></option>
                                <?php foreach ($teachers as $teach): ?>
                                    <option value="<?php echo $teach['id']; ?>" <?php echo $filter_teacher == $teach['id'] ? 'selected' : ''; ?>><?php echo $teach['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3" id="studentFilterContainer" style="display: <?php echo $filter_type === 'students' ? 'block' : 'none'; ?>;">
                            <label for="student" class="form-label"><?php echo t('student'); ?></label>
                            <select class="form-select" id="student" name="student" <?php echo empty($students) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_students'); ?></option>
                                <?php foreach ($students as $stud): ?>
                                    <option value="<?php echo $stud['id']; ?>" <?php echo $filter_student == $stud['id'] ? 'selected' : ''; ?>><?php echo $stud['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label"><?php echo t('date_from'); ?></label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $filter_date_from; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label"><?php echo t('date_to'); ?></label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $filter_date_to; ?>">
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> <?php echo t('apply_filters'); ?>
                        </button>
                        <a href="college_reports_attendance.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times me-1"></i> <?php echo t('reset_filters'); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- إحصائيات الحضور -->
        <div class="row mb-4">
            <div class="col-md">
                <div class="stats-card stats-present">
                    <h3><?php echo $stats['present']; ?></h3>
                    <p><?php echo t('present'); ?></p>
                </div>
            </div>
            <div class="col-md">
                <div class="stats-card stats-absent">
                    <h3><?php echo $stats['absent']; ?></h3>
                    <p><?php echo t('absent'); ?></p>
                </div>
            </div>
            <div class="col-md">
                <div class="stats-card stats-late">
                    <h3><?php echo $stats['late']; ?></h3>
                    <p><?php echo t('late'); ?></p>
                </div>
            </div>
            <div class="col-md">
                <div class="stats-card stats-excused">
                    <h3><?php echo $stats['excused']; ?></h3>
                    <p><?php echo t('excused'); ?></p>
                </div>
            </div>
            <div class="col-md">
                <div class="stats-card stats-total">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p><?php echo t('total_records'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- الرسوم البيانية -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i> <?php echo t('attendance_distribution'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attendancePieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i> <?php echo t('attendance_trend'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attendanceLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- جدول بيانات الحضور -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i> <?php echo t('attendance_details'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('date'); ?></th>
                                <th><?php echo $filter_type === 'students' ? t('student_name') : t('teacher_name'); ?></th>
                                <th><?php echo t('course'); ?></th>
                                <?php if ($filter_type === 'students'): ?>
                                    <th><?php echo t('teacher'); ?></th>
                                <?php endif; ?>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('notes'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance_data)): ?>
                                <tr>
                                    <td colspan="<?php echo $filter_type === 'students' ? 7 : 6; ?>" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>
                                        <?php echo t('no_attendance_data_found'); ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendance_data as $record): ?>
                                    <tr>
                                        <td><?php echo format_date($record['date']); ?></td>
                                        <td><?php echo $record['user_name']; ?></td>
                                        <td><?php echo $record['course_name']; ?></td>
                                        <?php if ($filter_type === 'students'): ?>
                                            <td><?php echo $record['teacher_name']; ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="status-badge status-<?php echo $record['status']; ?>">
                                                <?php echo translate_attendance_status($record['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $record['notes'] ?: '-'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" title="<?php echo t('edit'); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="<?php echo t('view_details'); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ترقيم الصفحات -->
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="<?php echo t('attendance_pagination'); ?>">
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
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
                
                // إعادة رسم الرسوم البيانية عند تغيير المظهر
                renderCharts();
            });
            
            // تحديث قوائم الفلاتر عند تغيير القسم
            document.getElementById('department').addEventListener('change', function() {
                const departmentId = this.value;
                const programSelect = document.getElementById('program');
                const courseSelect = document.getElementById('course');
                const teacherSelect = document.getElementById('teacher');
                const studentSelect = document.getElementById('student');
                
                // تحديث البرامج
                // في الواقع، يجب جلب البرامج عبر AJAX
                programSelect.innerHTML = '<option value=""><?php echo t('all_programs'); ?></option>';
                if (departmentId) {
                    // بيانات وهمية
                    const programs = <?php echo json_encode($programs); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    programs.forEach(prog => {
                        if (prog.department_id == departmentId) { // افتراض وجود department_id
                            const option = document.createElement('option');
                            option.value = prog.id;
                            option.text = prog.name;
                            programSelect.appendChild(option);
                        }
                    });
                    programSelect.disabled = false;
                } else {
                    programSelect.disabled = true;
                }
                
                // تحديث المقررات
                // في الواقع، يجب جلب المقررات عبر AJAX
                courseSelect.innerHTML = '<option value=""><?php echo t('all_courses'); ?></option>';
                if (departmentId) {
                    // بيانات وهمية
                    const courses = <?php echo json_encode($courses); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    courses.forEach(crs => {
                        if (crs.department_id == departmentId) { // افتراض وجود department_id
                            const option = document.createElement('option');
                            option.value = crs.id;
                            option.text = crs.name;
                            courseSelect.appendChild(option);
                        }
                    });
                    courseSelect.disabled = false;
                } else {
                    courseSelect.disabled = true;
                }
                
                // تحديث المعلمين
                // في الواقع، يجب جلب المعلمين عبر AJAX
                teacherSelect.innerHTML = '<option value=""><?php echo t('all_teachers'); ?></option>';
                if (departmentId) {
                    const teachers = <?php echo json_encode($teachers); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    teachers.forEach(teach => {
                        if (teach.department_id == departmentId) {
                            const option = document.createElement('option');
                            option.value = teach.id;
                            option.text = teach.name;
                            teacherSelect.appendChild(option);
                        }
                    });
                    teacherSelect.disabled = false;
                } else {
                    teacherSelect.disabled = true;
                }
                
                // تحديث الطلاب
                // في الواقع، يجب جلب الطلاب عبر AJAX
                studentSelect.innerHTML = '<option value=""><?php echo t('all_students'); ?></option>';
                if (departmentId) {
                    const students = <?php echo json_encode($students); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    students.forEach(stud => {
                        if (stud.department_id == departmentId) {
                            const option = document.createElement('option');
                            option.value = stud.id;
                            option.text = stud.name;
                            studentSelect.appendChild(option);
                        }
                    });
                    studentSelect.disabled = false;
                } else {
                    studentSelect.disabled = true;
                }
            });
            
            // تحديث المقررات والطلاب عند تغيير البرنامج
            document.getElementById('program').addEventListener('change', function() {
                const programId = this.value;
                const courseSelect = document.getElementById('course');
                const studentSelect = document.getElementById('student');
                const departmentId = document.getElementById('department').value;
                
                // تحديث المقررات
                courseSelect.innerHTML = '<option value=""><?php echo t('all_courses'); ?></option>';
                if (programId) {
                    // بيانات وهمية
                    const courses = <?php echo json_encode($courses); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    courses.forEach(crs => {
                        if (crs.program_id == programId) { // افتراض وجود program_id
                            const option = document.createElement('option');
                            option.value = crs.id;
                            option.text = crs.name;
                            courseSelect.appendChild(option);
                        }
                    });
                    courseSelect.disabled = false;
                } else if (departmentId) {
                    // إذا لم يتم تحديد برنامج، اعرض مقررات القسم
                    const courses = <?php echo json_encode($courses); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    courses.forEach(crs => {
                        if (crs.department_id == departmentId) {
                            const option = document.createElement('option');
                            option.value = crs.id;
                            option.text = crs.name;
                            courseSelect.appendChild(option);
                        }
                    });
                    courseSelect.disabled = false;
                } else {
                    courseSelect.disabled = true;
                }
                
                // تحديث الطلاب
                studentSelect.innerHTML = '<option value=""><?php echo t('all_students'); ?></option>';
                if (programId) {
                    const students = <?php echo json_encode($students); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    students.forEach(stud => {
                        if (stud.program_id == programId) {
                            const option = document.createElement('option');
                            option.value = stud.id;
                            option.text = stud.name;
                            studentSelect.appendChild(option);
                        }
                    });
                    studentSelect.disabled = false;
                } else if (departmentId) {
                    // إذا لم يتم تحديد برنامج، اعرض طلاب القسم
                    const students = <?php echo json_encode($students); ?>; // يجب أن تكون هذه البيانات ديناميكية
                    students.forEach(stud => {
                        if (stud.department_id == departmentId) {
                            const option = document.createElement('option');
                            option.value = stud.id;
                            option.text = stud.name;
                            studentSelect.appendChild(option);
                        }
                    });
                    studentSelect.disabled = false;
                } else {
                    studentSelect.disabled = true;
                }
            });
            
            // تبديل عرض فلتر الطالب/المعلم بناءً على نوع التقرير
            document.getElementById('type').addEventListener('change', function() {
                const type = this.value;
                document.getElementById('studentFilterContainer').style.display = type === 'students' ? 'block' : 'none';
                document.getElementById('teacherFilterContainer').style.display = type === 'teachers' ? 'block' : 'none';
            });
            
            // بيانات الرسوم البيانية
            const attendanceStats = <?php echo json_encode($stats); ?>;
            const attendanceTrendData = <?php 
                $trend = [];
                $dates = [];
                $present_counts = [];
                $absent_counts = [];
                
                // تجميع البيانات حسب التاريخ
                $grouped_data = [];
                foreach ($attendance_data as $record) {
                    $date = $record['date'];
                    if (!isset($grouped_data[$date])) {
                        $grouped_data[$date] = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
                    }
                    if (isset($grouped_data[$date][$record['status']])) {
                        $grouped_data[$date][$record['status']]++;
                    }
                }
                ksort($grouped_data);
                
                foreach ($grouped_data as $date => $counts) {
                    $dates[] = $date;
                    $present_counts[] = $counts['present'] + $counts['late'] + $counts['excused']; // اعتبار الحاضر والمتأخر والمعذور كحضور
                    $absent_counts[] = $counts['absent'];
                }
                
                echo json_encode(['dates' => $dates, 'present' => $present_counts, 'absent' => $absent_counts]); 
            ?>;
            
            let pieChartInstance = null;
            let lineChartInstance = null;
            
            function renderCharts() {
                const theme = document.body.className.includes('theme-dark') ? 'dark' : 'light';
                const textColor = theme === 'dark' ? '#ffffff' : '#333333';
                const gridColor = theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                
                // تدمير الرسوم البيانية القديمة إذا كانت موجودة
                if (pieChartInstance) pieChartInstance.destroy();
                if (lineChartInstance) lineChartInstance.destroy();
                
                // رسم مخطط الدائرة
                const pieCtx = document.getElementById('attendancePieChart').getContext('2d');
                pieChartInstance = new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['<?php echo t('present'); ?>', '<?php echo t('absent'); ?>', '<?php echo t('late'); ?>', '<?php echo t('excused'); ?>'],
                        datasets: [{
                            data: [
                                attendanceStats.present,
                                attendanceStats.absent,
                                attendanceStats.late,
                                attendanceStats.excused
                            ],
                            backgroundColor: [
                                '#28a745', // Present
                                '#dc3545', // Absent
                                '#ffc107', // Late
                                '#17a2b8'  // Excused
                            ],
                            borderColor: theme === 'dark' ? 'var(--dark-bg)' : '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += context.parsed;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) + '%' : '0%';
                                            label += ` (${percentage})`;
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم مخطط الخط الزمني
                const lineCtx = document.getElementById('attendanceLineChart').getContext('2d');
                lineChartInstance = new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: attendanceTrendData.dates,
                        datasets: [
                            {
                                label: '<?php echo t('present'); ?>',
                                data: attendanceTrendData.present,
                                borderColor: '#28a745',
                                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: '<?php echo t('absent'); ?>',
                                data: attendanceTrendData.absent,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    tooltipFormat: 'dd/MM/yyyy',
                                    displayFormats: {
                                        day: 'dd/MM'
                                    }
                                },
                                ticks: {
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: textColor,
                                    stepSize: 1 // إظهار الأعداد الصحيحة فقط
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        },
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
                        }
                    }
                });
            }
            
            // رسم الرسوم البيانية عند تحميل الصفحة
            renderCharts();
        });
    </script>
</body>
</html>
