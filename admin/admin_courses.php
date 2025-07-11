<?php
/**
 * صفحة إدارة المقررات الدراسية في نظام UniverBoard
 * تتيح للمشرف إدارة جميع المقررات الدراسية في البرامج المختلفة
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

// تحديد عدد العناصر في الصفحة
$items_per_page = 10;

// تحديد رقم الصفحة الحالية
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// تحديد كلمة البحث
$search = isset($_GET['search']) ? $_GET['search'] : '';

// تحديد فلتر الكلية والقسم والبرنامج
$college_filter = isset($_GET['college_id']) ? intval($_GET['college_id']) : 0;
$department_filter = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$program_filter = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

// الحصول على قائمة الكليات والأقسام والبرامج للفلتر
$all_colleges = get_all_colleges_for_filter($db);
$all_departments = get_all_departments_for_filter($db, $college_filter);
$all_programs = get_all_programs_for_filter($db, $department_filter);

// الحصول على قائمة المقررات الدراسية
$courses = get_courses($db, $page, $items_per_page, $search, $college_filter, $department_filter, $program_filter);
$total_courses = get_total_courses($db, $search, $college_filter, $department_filter, $program_filter);
$total_pages = ceil($total_courses / $items_per_page);

// معالجة إضافة مقرر جديد
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    $credits = filter_input(INPUT_POST, 'credits', FILTER_VALIDATE_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : []; // مصفوفة من IDs
    
    // التحقق من البيانات
    if (empty($name) || empty($code) || empty($program_id) || empty($credits)) {
        $add_error = t('required_fields_missing');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً في نفس البرنامج
        if (course_code_exists_in_program($db, $code, $program_id)) {
            $add_error = t('course_code_already_exists_in_program');
        } else {
            // إضافة المقرر الجديد
            $result = add_course($db, $name, $code, $program_id, $credits, $description, $prerequisites);
            
            if ($result) {
                $add_success = true;
                // تحديث قائمة المقررات
                $courses = get_courses($db, $page, $items_per_page, $search, $college_filter, $department_filter, $program_filter);
                $total_courses = get_total_courses($db, $search, $college_filter, $department_filter, $program_filter);
                $total_pages = ceil($total_courses / $items_per_page);
            } else {
                $add_error = t('add_course_failed');
            }
        }
    }
}

// معالجة تعديل مقرر
$edit_success = false;
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_course') {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    $credits = filter_input(INPUT_POST, 'credits', FILTER_VALIDATE_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : []; // مصفوفة من IDs
    
    // التحقق من البيانات
    if (empty($course_id) || empty($name) || empty($code) || empty($program_id) || empty($credits) || empty($status)) {
        $edit_error = t('required_fields_missing');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً (لمقرر آخر في نفس البرنامج)
        if (course_code_exists_for_other_course($db, $code, $program_id, $course_id)) {
            $edit_error = t('course_code_already_exists_in_program');
        } else {
            // تعديل المقرر
            $result = update_course($db, $course_id, $name, $code, $program_id, $credits, $description, $status, $prerequisites);
            
            if ($result) {
                $edit_success = true;
                // تحديث قائمة المقررات
                $courses = get_courses($db, $page, $items_per_page, $search, $college_filter, $department_filter, $program_filter);
                $total_courses = get_total_courses($db, $search, $college_filter, $department_filter, $program_filter);
                $total_pages = ceil($total_courses / $items_per_page);
            } else {
                $edit_error = t('edit_course_failed');
            }
        }
    }
}

// معالجة حذف مقرر
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_course') {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($course_id)) {
        $delete_error = t('course_id_required');
    } else {
        // التحقق من عدم وجود تبعيات (مثل تسجيل طلاب، واجبات، ...)
        if (course_has_dependencies($db, $course_id)) {
            $delete_error = t('course_has_dependencies');
        } else {
            // حذف المقرر
            $result = delete_course($db, $course_id);
            
            if ($result) {
                $delete_success = true;
                // تحديث قائمة المقررات
                $courses = get_courses($db, $page, $items_per_page, $search, $college_filter, $department_filter, $program_filter);
                $total_courses = get_total_courses($db, $search, $college_filter, $department_filter, $program_filter);
                $total_pages = ceil($total_courses / $items_per_page);
            } else {
                $delete_error = t('delete_course_failed');
            }
        }
    }
}

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

function get_all_colleges_for_filter($db) {
    return [
        ['id' => 1, 'name' => 'كلية علوم الحاسب والمعلومات'],
        ['id' => 2, 'name' => 'كلية الهندسة'],
        ['id' => 3, 'name' => 'كلية العلوم'],
    ];
}

function get_all_departments_for_filter($db, $college_id) {
    $departments = [
        ['id' => 1, 'name' => 'قسم علوم الحاسب', 'college_id' => 1],
        ['id' => 2, 'name' => 'قسم نظم المعلومات', 'college_id' => 1],
        ['id' => 3, 'name' => 'قسم هندسة البرمجيات', 'college_id' => 1],
        ['id' => 4, 'name' => 'قسم الهندسة المدنية', 'college_id' => 2],
        ['id' => 5, 'name' => 'قسم الهندسة الكهربائية', 'college_id' => 2],
        ['id' => 6, 'name' => 'قسم الفيزياء', 'college_id' => 3],
    ];
    
    if ($college_id > 0) {
        return array_filter($departments, function($dept) use ($college_id) {
            return $dept['college_id'] == $college_id;
        });
    } else {
        return $departments;
    }
}

function get_all_programs_for_filter($db, $department_id) {
    $programs = [
        ['id' => 1, 'name' => 'بكالوريوس علوم الحاسب', 'department_id' => 1],
        ['id' => 2, 'name' => 'ماجستير نظم المعلومات الإدارية', 'department_id' => 2],
        ['id' => 3, 'name' => 'بكالوريوس هندسة البرمجيات', 'department_id' => 3],
        ['id' => 4, 'name' => 'بكالوريوس الهندسة المدنية', 'department_id' => 4],
        ['id' => 5, 'name' => 'بكالوريوس الهندسة الكهربائية', 'department_id' => 5],
        ['id' => 6, 'name' => 'بكالوريوس الفيزياء', 'department_id' => 6],
    ];
    
    if ($department_id > 0) {
        return array_filter($programs, function($prog) use ($department_id) {
            return $prog['department_id'] == $department_id;
        });
    } else {
        return $programs;
    }
}

function get_courses($db, $page, $items_per_page, $search, $college_filter, $department_filter, $program_filter) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع المقررات من قاعدة البيانات مع تطبيق البحث والفلتر
    $courses = [
        [
            'id' => 101,
            'name' => 'مقدمة في البرمجة',
            'code' => 'CS101',
            'program_id' => 1,
            'program_name' => 'بكالوريوس علوم الحاسب',
            'department_name' => 'قسم علوم الحاسب',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'department_id' => 1,
            'credits' => 3,
            'description' => 'مقدمة إلى مفاهيم البرمجة الأساسية باستخدام لغة Python.',
            'status' => 'active',
            'prerequisites' => [],
            'created_at' => '2025-03-01 09:00:00'
        ],
        [
            'id' => 102,
            'name' => 'هياكل البيانات',
            'code' => 'CS201',
            'program_id' => 1,
            'program_name' => 'بكالوريوس علوم الحاسب',
            'department_name' => 'قسم علوم الحاسب',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'department_id' => 1,
            'credits' => 4,
            'description' => 'دراسة هياكل البيانات المختلفة وتطبيقاتها.',
            'status' => 'active',
            'prerequisites' => [101],
            'created_at' => '2025-03-05 10:30:00'
        ],
        [
            'id' => 201,
            'name' => 'تحليل وتصميم النظم',
            'code' => 'IS201',
            'program_id' => 2,
            'program_name' => 'ماجستير نظم المعلومات الإدارية',
            'department_name' => 'قسم نظم المعلومات',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'department_id' => 2,
            'credits' => 3,
            'description' => 'مفاهيم ومنهجيات تحليل وتصميم نظم المعلومات.',
            'status' => 'active',
            'prerequisites' => [],
            'created_at' => '2025-03-10 11:00:00'
        ],
        [
            'id' => 301,
            'name' => 'هندسة المتطلبات',
            'code' => 'SE301',
            'program_id' => 3,
            'program_name' => 'بكالوريوس هندسة البرمجيات',
            'department_name' => 'قسم هندسة البرمجيات',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'department_id' => 3,
            'credits' => 3,
            'description' => 'تقنيات جمع وتحليل وتوثيق متطلبات البرمجيات.',
            'status' => 'active',
            'prerequisites' => [],
            'created_at' => '2025-03-15 14:15:00'
        ],
        [
            'id' => 401,
            'name' => 'ميكانيكا المواد',
            'code' => 'CE201',
            'program_id' => 4,
            'program_name' => 'بكالوريوس الهندسة المدنية',
            'department_name' => 'قسم الهندسة المدنية',
            'college_name' => 'كلية الهندسة',
            'college_id' => 2,
            'department_id' => 4,
            'credits' => 4,
            'description' => 'دراسة سلوك المواد الصلبة تحت تأثير القوى المختلفة.',
            'status' => 'inactive',
            'prerequisites' => [],
            'created_at' => '2025-03-20 08:45:00'
        ],
    ];
    
    // تطبيق الفلاتر
    if ($college_filter > 0) {
        $courses = array_filter($courses, function($course) use ($college_filter) {
            return $course['college_id'] == $college_filter;
        });
    }
    if ($department_filter > 0) {
        $courses = array_filter($courses, function($course) use ($department_filter) {
            return $course['department_id'] == $department_filter;
        });
    }
    if ($program_filter > 0) {
        $courses = array_filter($courses, function($course) use ($program_filter) {
            return $course['program_id'] == $program_filter;
        });
    }
    
    // تطبيق البحث
    if (!empty($search)) {
        $courses = array_filter($courses, function($course) use ($search) {
            return stripos($course['name'], $search) !== false || 
                   stripos($course['code'], $search) !== false ||
                   stripos($course['program_name'], $search) !== false ||
                   stripos($course['department_name'], $search) !== false ||
                   stripos($course['college_name'], $search) !== false;
        });
    }
    
    // ترتيب المقررات حسب تاريخ الإنشاء (من الأحدث إلى الأقدم)
    usort($courses, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $courses = array_slice($courses, $offset, $items_per_page);
    
    return $courses;
}

function get_total_courses($db, $search, $college_filter, $department_filter, $program_filter) {
    // في الواقع، يجب استرجاع عدد المقررات من قاعدة البيانات مع تطبيق البحث والفلتر
    $total = 5;
    
    // تقليل العدد حسب الفلتر والبحث
    if ($college_filter > 0) $total *= 0.6;
    if ($department_filter > 0) $total *= 0.7;
    if ($program_filter > 0) $total *= 0.8;
    if (!empty($search)) $total *= 0.5;
    
    return ceil($total);
}

function course_code_exists_in_program($db, $code, $program_id) {
    // في الواقع، يجب التحقق من وجود رمز المقرر في البرنامج المحدد في قاعدة البيانات
    return false;
}

function course_code_exists_for_other_course($db, $code, $program_id, $course_id) {
    // في الواقع، يجب التحقق من وجود رمز المقرر لمقرر آخر في نفس البرنامج في قاعدة البيانات
    return false;
}

function course_has_dependencies($db, $course_id) {
    // في الواقع، يجب التحقق من وجود تبعيات للمقرر في قاعدة البيانات
    return false;
}

function add_course($db, $name, $code, $program_id, $credits, $description, $prerequisites) {
    // في الواقع، يجب إضافة المقرر والمتطلبات السابقة إلى قاعدة البيانات
    return true;
}

function update_course($db, $course_id, $name, $code, $program_id, $credits, $description, $status, $prerequisites) {
    // في الواقع، يجب تحديث معلومات المقرر والمتطلبات السابقة في قاعدة البيانات
    return true;
}

function delete_course($db, $course_id) {
    // في الواقع، يجب حذف المقرر من قاعدة البيانات
    return true;
}

function get_all_courses_for_prerequisites($db, $program_id, $exclude_course_id = null) {
    // دالة وهمية لجلب المقررات كمتطلبات سابقة (يجب استبدالها)
    $all_courses = [
        ['id' => 101, 'name' => 'مقدمة في البرمجة (CS101)', 'program_id' => 1],
        ['id' => 102, 'name' => 'هياكل البيانات (CS201)', 'program_id' => 1],
        ['id' => 201, 'name' => 'تحليل وتصميم النظم (IS201)', 'program_id' => 2],
        ['id' => 301, 'name' => 'هندسة المتطلبات (SE301)', 'program_id' => 3],
        ['id' => 401, 'name' => 'ميكانيكا المواد (CE201)', 'program_id' => 4],
    ];
    
    $filtered = array_filter($all_courses, function($course) use ($program_id, $exclude_course_id) {
        return $course['program_id'] == $program_id && $course['id'] != $exclude_course_id;
    });
    
    return $filtered;
}

function get_course_prerequisites($db, $course_id) {
    // دالة وهمية لجلب المتطلبات السابقة لمقرر معين
    if ($course_id == 102) return [101];
    return [];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('courses_management'); ?></title>
    
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
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
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
        
        /* تنسيقات التنقل بين الصفحات */
        .pagination {
            margin-bottom: 0;
        }
        
        .pagination .page-item .page-link {
            color: var(--primary-color);
            padding: 0.5rem 0.75rem;
            border-color: rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .pagination .page-item .page-link {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--gray-color);
        }
        
        /* تنسيقات البحث */
        .search-box {
            position: relative;
        }
        
        .search-box .form-control {
            padding-left: 2.5rem;
        }
        
        [dir="rtl"] .search-box .form-control {
            padding-left: 1rem;
            padding-right: 2.5rem;
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
        }
        
        [dir="rtl"] .search-box .search-icon {
            left: auto;
            right: 1rem;
        }
        
        /* تنسيقات المودال */
        .modal-content {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .modal-content {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
        }
        
        .theme-dark .modal-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
        }
        
        .theme-dark .modal-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        /* تنسيقات حالة المقرر */
        .status-active {
            color: #198754;
        }
        
        .status-inactive {
            color: #dc3545;
        }
        
        /* تنسيقات Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            height: auto;
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-results__option {
            color: var(--text-color);
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--primary-color);
            color: white;
        }
        
        .theme-dark .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
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
                    <a class="nav-link active" href="admin_courses.php">
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
            <h1 class="page-title"><?php echo t('courses_management'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_all_courses_in_programs'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($add_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('course_added_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($edit_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('course_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('course_deleted_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($add_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $add_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($edit_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $edit_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($delete_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $delete_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- أزرار التحكم والفلاتر -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex flex-wrap align-items-center">
                <div class="search-box me-2 mb-2" style="width: 200px;">
                    <i class="fas fa-search search-icon"></i>
                    <form action="" method="get" id="searchForm">
                        <input type="text" class="form-control" name="search" placeholder="<?php echo t('search_courses'); ?>" value="<?php echo $search; ?>">
                        <input type="hidden" name="college_id" value="<?php echo $college_filter; ?>">
                        <input type="hidden" name="department_id" value="<?php echo $department_filter; ?>">
                        <input type="hidden" name="program_id" value="<?php echo $program_filter; ?>">
                    </form>
                </div>
                <div class="me-2 mb-2" style="width: 200px;">
                    <form action="" method="get" id="filterForm">
                        <select class="form-select" name="college_id" id="collegeFilterSelect">
                            <option value="0"><?php echo t('all_colleges'); ?></option>
                            <?php foreach ($all_colleges as $college): ?>
                                <option value="<?php echo $college['id']; ?>" <?php echo $college_filter == $college['id'] ? 'selected' : ''; ?>><?php echo $college['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <input type="hidden" name="department_id" value="0"> <!-- Reset department on college change -->
                        <input type="hidden" name="program_id" value="0"> <!-- Reset program on college change -->
                    </form>
                </div>
                <div class="me-2 mb-2" style="width: 200px;">
                    <form action="" method="get" id="deptFilterForm">
                        <select class="form-select" name="department_id" id="departmentFilterSelect">
                            <option value="0"><?php echo t('all_departments'); ?></option>
                            <?php foreach ($all_departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>" <?php echo $department_filter == $department['id'] ? 'selected' : ''; ?>><?php echo $department['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <input type="hidden" name="college_id" value="<?php echo $college_filter; ?>">
                        <input type="hidden" name="program_id" value="0"> <!-- Reset program on department change -->
                    </form>
                </div>
                <div class="me-2 mb-2" style="width: 200px;">
                    <form action="" method="get" id="progFilterForm">
                        <select class="form-select" name="program_id" onchange="document.getElementById('progFilterForm').submit();">
                            <option value="0"><?php echo t('all_programs'); ?></option>
                            <?php foreach ($all_programs as $program): ?>
                                <option value="<?php echo $program['id']; ?>" <?php echo $program_filter == $program['id'] ? 'selected' : ''; ?>><?php echo $program['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <input type="hidden" name="college_id" value="<?php echo $college_filter; ?>">
                        <input type="hidden" name="department_id" value="<?php echo $department_filter; ?>">
                    </form>
                </div>
            </div>
            
            <div class="mb-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                    <i class="fas fa-plus me-2"></i> <?php echo t('add_course'); ?>
                </button>
            </div>
        </div>
        
        <!-- عرض المقررات (جدول) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-book me-2"></i> <?php echo t('courses_list'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('course_name'); ?></th>
                                <th><?php echo t('course_code'); ?></th>
                                <th><?php echo t('program'); ?></th>
                                <th><?php echo t('department'); ?></th>
                                <th><?php echo t('college'); ?></th>
                                <th><?php echo t('credits'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr>
                                    <td colspan="8" class="text-center"><?php echo t('no_courses_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?php echo $course['name']; ?></td>
                                        <td><?php echo $course['code']; ?></td>
                                        <td><?php echo $course['program_name']; ?></td>
                                        <td><?php echo $course['department_name']; ?></td>
                                        <td><?php echo $course['college_name']; ?></td>
                                        <td><?php echo $course['credits']; ?></td>
                                        <td>
                                            <span class="status-<?php echo $course['status']; ?>">
                                                <i class="fas <?php echo $course['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                                <?php echo $course['status'] === 'active' ? t('active') : t('inactive'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewCourseModal" onclick="prepareViewCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCourseModal" onclick="prepareEditCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCourseModal" onclick="prepareDeleteCourse(<?php echo $course['id']; ?>, '<?php echo $course['name']; ?>')">
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
                
                <!-- التنقل بين الصفحات -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <?php echo t('showing'); ?> <?php echo count($courses); ?> <?php echo t('of'); ?> <?php echo $total_courses; ?> <?php echo t('courses'); ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>&department_id=<?php echo $department_filter; ?>&program_id=<?php echo $program_filter; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>&department_id=<?php echo $department_filter; ?>&program_id=<?php echo $program_filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>&department_id=<?php echo $department_filter; ?>&program_id=<?php echo $program_filter; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- مودال إضافة مقرر -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel"><?php echo t('add_new_course'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_course">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_course_name" class="form-label"><?php echo t('course_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_course_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_course_code" class="form-label"><?php echo t('course_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_course_code" name="code" required>
                                <div class="form-text"><?php echo t('course_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="add_course_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_course_college_id" name="college_id_temp" required onchange="loadDepartmentsAndPrograms('add_course_college_id', 'add_course_department_id', 'add_course_program_id')">
                                    <option value=""><?php echo t('select_college'); ?></option>
                                    <?php foreach ($all_colleges as $college): ?>
                                        <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="add_course_department_id" class="form-label"><?php echo t('department'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_course_department_id" name="department_id_temp" required onchange="loadPrograms('add_course_department_id', 'add_course_program_id')">
                                    <option value=""><?php echo t('select_department'); ?></option>
                                    <!-- سيتم ملء الأقسام بواسطة JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="add_course_program_id" class="form-label"><?php echo t('program'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_course_program_id" name="program_id" required onchange="loadPrerequisites('add_course_program_id', 'add_course_prerequisites')">
                                    <option value=""><?php echo t('select_program'); ?></option>
                                    <!-- سيتم ملء البرامج بواسطة JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_course_credits" class="form-label"><?php echo t('credits'); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="add_course_credits" name="credits" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_course_prerequisites" class="form-label"><?php echo t('prerequisites'); ?></label>
                                <select class="form-select select2-multiple" id="add_course_prerequisites" name="prerequisites[]" multiple="multiple">
                                    <!-- سيتم ملء المتطلبات بواسطة JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="add_course_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="add_course_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('add_course'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض مقرر -->
    <div class="modal fade" id="viewCourseModal" tabindex="-1" aria-labelledby="viewCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCourseModalLabel"><?php echo t('course_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('course_name'); ?></h6>
                            <p id="view_course_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('course_code'); ?></h6>
                            <p id="view_course_code"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6><?php echo t('college'); ?></h6>
                            <p id="view_course_college_name"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6><?php echo t('department'); ?></h6>
                            <p id="view_course_department_name"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6><?php echo t('program'); ?></h6>
                            <p id="view_course_program_name"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('credits'); ?></h6>
                            <p id="view_course_credits"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('status'); ?></h6>
                            <p id="view_course_status"></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('description'); ?></h6>
                        <p id="view_course_description"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('prerequisites'); ?></h6>
                        <ul id="view_course_prerequisites_list" class="list-unstyled"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="viewCourseEditBtn"><?php echo t('edit'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل مقرر -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCourseModalLabel"><?php echo t('edit_course'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_course">
                    <input type="hidden" name="course_id" id="edit_course_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_course_name" class="form-label"><?php echo t('course_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_course_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_course_code" class="form-label"><?php echo t('course_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_course_code" name="code" required>
                                <div class="form-text"><?php echo t('course_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_course_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_course_college_id" name="college_id_temp" required onchange="loadDepartmentsAndPrograms('edit_course_college_id', 'edit_course_department_id', 'edit_course_program_id')">
                                    <option value=""><?php echo t('select_college'); ?></option>
                                    <?php foreach ($all_colleges as $college): ?>
                                        <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_course_department_id" class="form-label"><?php echo t('department'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_course_department_id" name="department_id_temp" required onchange="loadPrograms('edit_course_department_id', 'edit_course_program_id')">
                                    <option value=""><?php echo t('select_department'); ?></option>
                                    <!-- سيتم ملء الأقسام بواسطة JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_course_program_id" class="form-label"><?php echo t('program'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_course_program_id" name="program_id" required onchange="loadPrerequisites('edit_course_program_id', 'edit_course_prerequisites', document.getElementById('edit_course_id').value)">
                                    <option value=""><?php echo t('select_program'); ?></option>
                                    <!-- سيتم ملء البرامج بواسطة JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_course_credits" class="form-label"><?php echo t('credits'); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_course_credits" name="credits" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_course_status" class="form-label"><?php echo t('status'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_course_status" name="status" required>
                                    <option value="active"><?php echo t('active'); ?></option>
                                    <option value="inactive"><?php echo t('inactive'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_course_prerequisites" class="form-label"><?php echo t('prerequisites'); ?></label>
                                <select class="form-select select2-multiple" id="edit_course_prerequisites" name="prerequisites[]" multiple="multiple">
                                    <!-- سيتم ملء المتطلبات بواسطة JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_course_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="edit_course_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف مقرر -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCourseModalLabel"><?php echo t('delete_course'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="course_id" id="delete_course_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_course'); ?>: <strong id="delete_course_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_course_warning'); ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-danger"><?php echo t('delete'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- ملف JavaScript الرئيسي -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // مصفوفات وهمية للبيانات (يجب استبدالها بـ API calls)
        const allDepartmentsData = <?php echo json_encode(get_all_departments_for_filter($db, 0)); ?>;
        const allProgramsData = <?php echo json_encode(get_all_programs_for_filter($db, 0)); ?>;
        const allCoursesData = <?php echo json_encode(get_all_courses_for_prerequisites($db, 0)); ?>; // لجلب كل المقررات للمتطلبات
        
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة Select2
            $('.select2-multiple').select2({
                theme: 'bootstrap-5',
                placeholder: '<?php echo t("select_prerequisites"); ?>',
                allowClear: true,
                dropdownParent: $("#addCourseModal, #editCourseModal") // لضمان ظهور القائمة المنسدلة فوق المودال
            });
            
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
            
            // زر التعديل في مودال العرض
            document.getElementById('viewCourseEditBtn').addEventListener('click', function() {
                $('#viewCourseModal').modal('hide');
                $('#editCourseModal').modal('show');
            });
            
            // تغيير فلتر الكلية
            document.getElementById('collegeFilterSelect').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            
            // تغيير فلتر القسم
            document.getElementById('departmentFilterSelect').addEventListener('change', function() {
                document.getElementById('deptFilterForm').submit();
            });
        });
        
        // دالة تحميل الأقسام بناءً على الكلية المختارة
        function loadDepartments(collegeSelectId, departmentSelectId, selectedDepartmentId = null) {
            const collegeId = document.getElementById(collegeSelectId).value;
            const departmentSelect = document.getElementById(departmentSelectId);
            
            departmentSelect.innerHTML = '<option value=""><?php echo t("select_department"); ?></option>'; // مسح الخيارات
            
            if (collegeId) {
                const filteredDepartments = allDepartmentsData.filter(dept => dept.college_id == collegeId);
                filteredDepartments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.name;
                    if (selectedDepartmentId && dept.id == selectedDepartmentId) {
                        option.selected = true;
                    }
                    departmentSelect.appendChild(option);
                });
            }
        }
        
        // دالة تحميل البرامج بناءً على القسم المختار
        function loadPrograms(departmentSelectId, programSelectId, selectedProgramId = null) {
            const departmentId = document.getElementById(departmentSelectId).value;
            const programSelect = document.getElementById(programSelectId);
            
            programSelect.innerHTML = '<option value=""><?php echo t("select_program"); ?></option>'; // مسح الخيارات
            
            if (departmentId) {
                const filteredPrograms = allProgramsData.filter(prog => prog.department_id == departmentId);
                filteredPrograms.forEach(prog => {
                    const option = document.createElement('option');
                    option.value = prog.id;
                    option.textContent = prog.name;
                    if (selectedProgramId && prog.id == selectedProgramId) {
                        option.selected = true;
                    }
                    programSelect.appendChild(option);
                });
            }
        }
        
        // دالة تحميل الأقسام ثم البرامج
        function loadDepartmentsAndPrograms(collegeSelectId, departmentSelectId, programSelectId, selectedDepartmentId = null, selectedProgramId = null) {
            loadDepartments(collegeSelectId, departmentSelectId, selectedDepartmentId);
            // تأخير بسيط لضمان تحميل الأقسام قبل محاولة تحميل البرامج
            setTimeout(() => {
                loadPrograms(departmentSelectId, programSelectId, selectedProgramId);
            }, 100);
        }
        
        // دالة تحميل المتطلبات السابقة بناءً على البرنامج المختار
        function loadPrerequisites(programSelectId, prerequisitesSelectId, excludeCourseId = null, selectedPrerequisites = []) {
            const programId = document.getElementById(programSelectId).value;
            const prerequisitesSelect = $(`#${prerequisitesSelectId}`);
            
            prerequisitesSelect.empty(); // مسح الخيارات الحالية
            
            if (programId) {
                const filteredCourses = allCoursesData.filter(course => course.program_id == programId && course.id != excludeCourseId);
                filteredCourses.forEach(course => {
                    const option = new Option(course.name, course.id, false, selectedPrerequisites.includes(course.id));
                    prerequisitesSelect.append(option);
                });
            }
            
            prerequisitesSelect.trigger('change'); // تحديث Select2
        }
        
        // دالة تحضير مودال عرض المقرر
        function prepareViewCourse(course) {
            document.getElementById('view_course_name').textContent = course.name;
            document.getElementById('view_course_code').textContent = course.code;
            document.getElementById('view_course_college_name').textContent = course.college_name;
            document.getElementById('view_course_department_name').textContent = course.department_name;
            document.getElementById('view_course_program_name').textContent = course.program_name;
            document.getElementById('view_course_credits').textContent = course.credits;
            document.getElementById('view_course_description').textContent = course.description || '-';
            document.getElementById('view_course_status').innerHTML = `<span class="status-${course.status}"><i class="fas ${course.status === 'active' ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${course.status === 'active' ? '<?php echo t("active"); ?>' : '<?php echo t("inactive"); ?>'}</span>`;
            
            // عرض المتطلبات السابقة
            const prereqList = document.getElementById('view_course_prerequisites_list');
            prereqList.innerHTML = ''; // مسح القائمة
            const prerequisites = course.prerequisites || []; // يجب جلبها من API
            if (prerequisites.length > 0) {
                prerequisites.forEach(prereqId => {
                    const prereqCourse = allCoursesData.find(c => c.id == prereqId);
                    if (prereqCourse) {
                        const li = document.createElement('li');
                        li.textContent = prereqCourse.name;
                        prereqList.appendChild(li);
                    }
                });
            } else {
                prereqList.innerHTML = '<li><?php echo t("no_prerequisites"); ?></li>';
            }
            
            // تحضير بيانات التعديل أيضاً
            prepareEditCourse(course);
        }
        
        // دالة تحضير مودال تعديل المقرر
        function prepareEditCourse(course) {
            document.getElementById('edit_course_id').value = course.id;
            document.getElementById('edit_course_name').value = course.name;
            document.getElementById('edit_course_code').value = course.code;
            document.getElementById('edit_course_college_id').value = course.college_id;
            document.getElementById('edit_course_credits').value = course.credits;
            document.getElementById('edit_course_description').value = course.description || '';
            document.getElementById('edit_course_status').value = course.status;
            
            // تحميل الأقسام والبرامج وتحديد القيم الصحيحة
            loadDepartmentsAndPrograms('edit_course_college_id', 'edit_course_department_id', 'edit_course_program_id', course.department_id, course.program_id);
            
            // تحميل المتطلبات السابقة وتحديد القيم الصحيحة (بعد تحميل البرامج)
            const currentPrerequisites = course.prerequisites || []; // يجب جلبها من API
            setTimeout(() => {
                loadPrerequisites('edit_course_program_id', 'edit_course_prerequisites', course.id, currentPrerequisites);
            }, 200); // تأخير إضافي لضمان تحميل البرامج
        }
        
        // دالة تحضير مودال حذف المقرر
        function prepareDeleteCourse(courseId, courseName) {
            document.getElementById('delete_course_id').value = courseId;
            document.getElementById('delete_course_name').textContent = courseName;
        }
        
        // تحميل الأقسام والبرامج والمتطلبات عند فتح مودال الإضافة لأول مرة
        const addCourseModal = document.getElementById('addCourseModal');
        addCourseModal.addEventListener('show.bs.modal', function (event) {
            loadDepartmentsAndPrograms('add_course_college_id', 'add_course_department_id', 'add_course_program_id');
            // مسح المتطلبات عند فتح المودال
            loadPrerequisites('add_course_program_id', 'add_course_prerequisites');
        });
        
    </script>
</body>
</html>
