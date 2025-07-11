<?php
/**
 * صفحة إدارة البرامج الأكاديمية في نظام UniverBoard
 * تتيح للمشرف إدارة جميع البرامج الأكاديمية في الأقسام المختلفة
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

// تحديد فلتر الكلية والقسم
$college_filter = isset($_GET['college_id']) ? intval($_GET['college_id']) : 0;
$department_filter = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

// الحصول على قائمة الكليات والأقسام للفلتر
$all_colleges = get_all_colleges_for_filter($db);
$all_departments = get_all_departments_for_filter($db, $college_filter); // يعتمد على الكلية المختارة

// الحصول على قائمة البرامج الأكاديمية
$programs = get_programs($db, $page, $items_per_page, $search, $college_filter, $department_filter);
$total_programs = get_total_programs($db, $search, $college_filter, $department_filter);
$total_pages = ceil($total_programs / $items_per_page);

// معالجة إضافة برنامج جديد
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_program') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $duration_years = filter_input(INPUT_POST, 'duration_years', FILTER_VALIDATE_INT);
    $total_credits = filter_input(INPUT_POST, 'total_credits', FILTER_VALIDATE_INT);
    $degree_type = filter_input(INPUT_POST, 'degree_type', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($name) || empty($code) || empty($department_id) || empty($duration_years) || empty($total_credits) || empty($degree_type)) {
        $add_error = t('required_fields_missing');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً في نفس القسم
        if (program_code_exists_in_department($db, $code, $department_id)) {
            $add_error = t('program_code_already_exists_in_department');
        } else {
            // إضافة البرنامج الجديد
            $result = add_program($db, $name, $code, $department_id, $duration_years, $total_credits, $degree_type, $description);
            
            if ($result) {
                $add_success = true;
                // تحديث قائمة البرامج
                $programs = get_programs($db, $page, $items_per_page, $search, $college_filter, $department_filter);
                $total_programs = get_total_programs($db, $search, $college_filter, $department_filter);
                $total_pages = ceil($total_programs / $items_per_page);
            } else {
                $add_error = t('add_program_failed');
            }
        }
    }
}

// معالجة تعديل برنامج
$edit_success = false;
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_program') {
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $duration_years = filter_input(INPUT_POST, 'duration_years', FILTER_VALIDATE_INT);
    $total_credits = filter_input(INPUT_POST, 'total_credits', FILTER_VALIDATE_INT);
    $degree_type = filter_input(INPUT_POST, 'degree_type', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($program_id) || empty($name) || empty($code) || empty($department_id) || empty($duration_years) || empty($total_credits) || empty($degree_type) || empty($status)) {
        $edit_error = t('required_fields_missing');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً (لبرنامج آخر في نفس القسم)
        if (program_code_exists_for_other_program($db, $code, $department_id, $program_id)) {
            $edit_error = t('program_code_already_exists_in_department');
        } else {
            // تعديل البرنامج
            $result = update_program($db, $program_id, $name, $code, $department_id, $duration_years, $total_credits, $degree_type, $description, $status);
            
            if ($result) {
                $edit_success = true;
                // تحديث قائمة البرامج
                $programs = get_programs($db, $page, $items_per_page, $search, $college_filter, $department_filter);
                $total_programs = get_total_programs($db, $search, $college_filter, $department_filter);
                $total_pages = ceil($total_programs / $items_per_page);
            } else {
                $edit_error = t('edit_program_failed');
            }
        }
    }
}

// معالجة حذف برنامج
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_program') {
    $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($program_id)) {
        $delete_error = t('program_id_required');
    } else {
        // التحقق من عدم وجود مقررات أو طلاب مرتبطين بالبرنامج
        if (program_has_dependencies($db, $program_id)) {
            $delete_error = t('program_has_dependencies');
        } else {
            // حذف البرنامج
            $result = delete_program($db, $program_id);
            
            if ($result) {
                $delete_success = true;
                // تحديث قائمة البرامج
                $programs = get_programs($db, $page, $items_per_page, $search, $college_filter, $department_filter);
                $total_programs = get_total_programs($db, $search, $college_filter, $department_filter);
                $total_pages = ceil($total_programs / $items_per_page);
            } else {
                $delete_error = t('delete_program_failed');
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
    // في الواقع، يجب استرجاع قائمة الكليات من قاعدة البيانات
    return [
        ['id' => 1, 'name' => 'كلية علوم الحاسب والمعلومات'],
        ['id' => 2, 'name' => 'كلية الهندسة'],
        ['id' => 3, 'name' => 'كلية العلوم'],
        ['id' => 4, 'name' => 'كلية الطب'],
        ['id' => 5, 'name' => 'كلية إدارة الأعمال'],
        ['id' => 6, 'name' => 'كلية التربية'],
        ['id' => 7, 'name' => 'كلية الآداب'],
    ];
}

function get_all_departments_for_filter($db, $college_id) {
    // في الواقع، يجب استرجاع قائمة الأقسام بناءً على الكلية المحددة
    $departments = [
        ['id' => 1, 'name' => 'قسم علوم الحاسب', 'college_id' => 1],
        ['id' => 2, 'name' => 'قسم نظم المعلومات', 'college_id' => 1],
        ['id' => 3, 'name' => 'قسم هندسة البرمجيات', 'college_id' => 1],
        ['id' => 4, 'name' => 'قسم الهندسة المدنية', 'college_id' => 2],
        ['id' => 5, 'name' => 'قسم الهندسة الكهربائية', 'college_id' => 2],
        ['id' => 6, 'name' => 'قسم الفيزياء', 'college_id' => 3],
        ['id' => 7, 'name' => 'قسم الكيمياء', 'college_id' => 3],
    ];
    
    if ($college_id > 0) {
        return array_filter($departments, function($dept) use ($college_id) {
            return $dept['college_id'] == $college_id;
        });
    } else {
        return $departments;
    }
}

function get_programs($db, $page, $items_per_page, $search, $college_filter, $department_filter) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع البرامج من قاعدة البيانات مع تطبيق البحث والفلتر
    $programs = [
        [
            'id' => 1,
            'name' => 'بكالوريوس علوم الحاسب',
            'code' => 'BCS',
            'department_id' => 1,
            'department_name' => 'قسم علوم الحاسب',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'duration_years' => 4,
            'total_credits' => 130,
            'degree_type' => 'بكالوريوس',
            'description' => 'برنامج يهدف إلى تزويد الطلاب بالمعرفة والمهارات اللازمة في مجال علوم الحاسب.',
            'courses_count' => 40,
            'students_count' => 250,
            'status' => 'active',
            'created_at' => '2025-02-01 10:00:00'
        ],
        [
            'id' => 2,
            'name' => 'ماجستير نظم المعلومات الإدارية',
            'code' => 'MMIS',
            'department_id' => 2,
            'department_name' => 'قسم نظم المعلومات',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'duration_years' => 2,
            'total_credits' => 45,
            'degree_type' => 'ماجستير',
            'description' => 'برنامج دراسات عليا يركز على الجوانب الإدارية والتكنولوجية لنظم المعلومات.',
            'courses_count' => 15,
            'students_count' => 50,
            'status' => 'active',
            'created_at' => '2025-02-05 11:30:00'
        ],
        [
            'id' => 3,
            'name' => 'بكالوريوس هندسة البرمجيات',
            'code' => 'BSE',
            'department_id' => 3,
            'department_name' => 'قسم هندسة البرمجيات',
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'college_id' => 1,
            'duration_years' => 4,
            'total_credits' => 135,
            'degree_type' => 'بكالوريوس',
            'description' => 'برنامج يركز على تصميم وتطوير واختبار وصيانة البرمجيات.',
            'courses_count' => 42,
            'students_count' => 200,
            'status' => 'active',
            'created_at' => '2025-02-10 09:15:00'
        ],
        [
            'id' => 4,
            'name' => 'بكالوريوس الهندسة المدنية',
            'code' => 'BCE',
            'department_id' => 4,
            'department_name' => 'قسم الهندسة المدنية',
            'college_name' => 'كلية الهندسة',
            'college_id' => 2,
            'duration_years' => 5,
            'total_credits' => 160,
            'degree_type' => 'بكالوريوس',
            'description' => 'برنامج شامل يغطي مختلف مجالات الهندسة المدنية.',
            'courses_count' => 55,
            'students_count' => 300,
            'status' => 'active',
            'created_at' => '2025-02-15 14:00:00'
        ],
        [
            'id' => 5,
            'name' => 'بكالوريوس الهندسة الكهربائية',
            'code' => 'BEE',
            'department_id' => 5,
            'department_name' => 'قسم الهندسة الكهربائية',
            'college_name' => 'كلية الهندسة',
            'college_id' => 2,
            'duration_years' => 5,
            'total_credits' => 165,
            'degree_type' => 'بكالوريوس',
            'description' => 'برنامج يركز على مجالات الطاقة والاتصالات والإلكترونيات.',
            'courses_count' => 60,
            'students_count' => 350,
            'status' => 'active',
            'created_at' => '2025-02-20 16:20:00'
        ],
        [
            'id' => 6,
            'name' => 'بكالوريوس الفيزياء',
            'code' => 'BPHY',
            'department_id' => 6,
            'department_name' => 'قسم الفيزياء',
            'college_name' => 'كلية العلوم',
            'college_id' => 3,
            'duration_years' => 4,
            'total_credits' => 120,
            'degree_type' => 'بكالوريوس',
            'description' => 'برنامج يهدف إلى فهم المبادئ الأساسية للفيزياء وتطبيقاتها.',
            'courses_count' => 35,
            'students_count' => 180,
            'status' => 'inactive',
            'created_at' => '2025-02-25 10:45:00'
        ]
    ];
    
    // تطبيق فلتر الكلية والقسم
    if ($college_filter > 0) {
        $programs = array_filter($programs, function($program) use ($college_filter) {
            return $program['college_id'] == $college_filter;
        });
    }
    if ($department_filter > 0) {
        $programs = array_filter($programs, function($program) use ($department_filter) {
            return $program['department_id'] == $department_filter;
        });
    }
    
    // تطبيق البحث
    if (!empty($search)) {
        $programs = array_filter($programs, function($program) use ($search) {
            return stripos($program['name'], $search) !== false || 
                   stripos($program['code'], $search) !== false ||
                   stripos($program['department_name'], $search) !== false ||
                   stripos($program['college_name'], $search) !== false ||
                   stripos($program['degree_type'], $search) !== false;
        });
    }
    
    // ترتيب البرامج حسب تاريخ الإنشاء (من الأحدث إلى الأقدم)
    usort($programs, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $programs = array_slice($programs, $offset, $items_per_page);
    
    return $programs;
}

function get_total_programs($db, $search, $college_filter, $department_filter) {
    // في الواقع، يجب استرجاع عدد البرامج من قاعدة البيانات مع تطبيق البحث والفلتر
    $total = 6;
    
    // تقليل العدد حسب الفلتر والبحث
    if ($college_filter > 0) {
        $total = $total * 0.4; // تقريباً 40% من البرامج في الكلية المحددة
    }
    if ($department_filter > 0) {
        $total = $total * 0.6; // تقريباً 60% من البرامج في القسم المحدد
    }
    if (!empty($search)) {
        $total = $total * 0.5; // تقريباً 50% من البرامج تطابق البحث
    }
    
    return ceil($total);
}

function program_code_exists_in_department($db, $code, $department_id) {
    // في الواقع، يجب التحقق من وجود رمز البرنامج في القسم المحدد في قاعدة البيانات
    return false;
}

function program_code_exists_for_other_program($db, $code, $department_id, $program_id) {
    // في الواقع، يجب التحقق من وجود رمز البرنامج لبرنامج آخر في نفس القسم في قاعدة البيانات
    return false;
}

function program_has_dependencies($db, $program_id) {
    // في الواقع، يجب التحقق من وجود مقررات أو طلاب مرتبطين بالبرنامج في قاعدة البيانات
    return false;
}

function add_program($db, $name, $code, $department_id, $duration_years, $total_credits, $degree_type, $description) {
    // في الواقع، يجب إضافة البرنامج إلى قاعدة البيانات
    return true;
}

function update_program($db, $program_id, $name, $code, $department_id, $duration_years, $total_credits, $degree_type, $description, $status) {
    // في الواقع، يجب تحديث معلومات البرنامج في قاعدة البيانات
    return true;
}

function delete_program($db, $program_id) {
    // في الواقع، يجب حذف البرنامج من قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('academic_programs_management'); ?></title>
    
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
        /* (نفس تنسيقات CSS من admin_colleges.php و admin_departments.php) */
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
        
        /* تنسيقات حالة البرنامج */
        .status-active {
            color: #198754;
        }
        
        .status-inactive {
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
                    <a class="nav-link active" href="admin_programs.php">
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
            <h1 class="page-title"><?php echo t('academic_programs_management'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_all_academic_programs_in_departments'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($add_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('program_added_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($edit_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('program_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('program_deleted_successfully'); ?>
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
                        <input type="text" class="form-control" name="search" placeholder="<?php echo t('search_programs'); ?>" value="<?php echo $search; ?>">
                        <input type="hidden" name="college_id" value="<?php echo $college_filter; ?>">
                        <input type="hidden" name="department_id" value="<?php echo $department_filter; ?>">
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
                    </form>
                </div>
                <div class="me-2 mb-2" style="width: 200px;">
                    <form action="" method="get" id="deptFilterForm">
                        <select class="form-select" name="department_id" onchange="document.getElementById('deptFilterForm').submit();">
                            <option value="0"><?php echo t('all_departments'); ?></option>
                            <?php foreach ($all_departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>" <?php echo $department_filter == $department['id'] ? 'selected' : ''; ?>><?php echo $department['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <input type="hidden" name="college_id" value="<?php echo $college_filter; ?>">
                    </form>
                </div>
            </div>
            
            <div class="mb-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                    <i class="fas fa-plus me-2"></i> <?php echo t('add_program'); ?>
                </button>
            </div>
        </div>
        
        <!-- عرض البرامج (جدول) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-graduation-cap me-2"></i> <?php echo t('programs_list'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('program_name'); ?></th>
                                <th><?php echo t('program_code'); ?></th>
                                <th><?php echo t('department'); ?></th>
                                <th><?php echo t('college'); ?></th>
                                <th><?php echo t('degree_type'); ?></th>
                                <th><?php echo t('duration_years'); ?></th>
                                <th><?php echo t('total_credits'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs)): ?>
                                <tr>
                                    <td colspan="9" class="text-center"><?php echo t('no_programs_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($programs as $program): ?>
                                    <tr>
                                        <td><?php echo $program['name']; ?></td>
                                        <td><?php echo $program['code']; ?></td>
                                        <td><?php echo $program['department_name']; ?></td>
                                        <td><?php echo $program['college_name']; ?></td>
                                        <td><?php echo $program['degree_type']; ?></td>
                                        <td><?php echo $program['duration_years']; ?></td>
                                        <td><?php echo $program['total_credits']; ?></td>
                                        <td>
                                            <span class="status-<?php echo $program['status']; ?>">
                                                <i class="fas <?php echo $program['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                                <?php echo $program['status'] === 'active' ? t('active') : t('inactive'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewProgramModal" onclick="prepareViewProgram(<?php echo htmlspecialchars(json_encode($program)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProgramModal" onclick="prepareEditProgram(<?php echo htmlspecialchars(json_encode($program)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProgramModal" onclick="prepareDeleteProgram(<?php echo $program['id']; ?>, '<?php echo $program['name']; ?>')">
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
                            <?php echo t('showing'); ?> <?php echo count($programs); ?> <?php echo t('of'); ?> <?php echo $total_programs; ?> <?php echo t('programs'); ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>&department_id=<?php echo $department_filter; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>&department_id=<?php echo $department_filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>&department_id=<?php echo $department_filter; ?>" aria-label="Next">
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
    
    <!-- مودال إضافة برنامج -->
    <div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProgramModalLabel"><?php echo t('add_new_program'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_program">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_prog_name" class="form-label"><?php echo t('program_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_prog_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_prog_code" class="form-label"><?php echo t('program_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_prog_code" name="code" required>
                                <div class="form-text"><?php echo t('program_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_prog_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_prog_college_id" name="college_id_temp" required onchange="loadDepartments('add_prog_college_id', 'add_prog_department_id')">
                                    <option value=""><?php echo t('select_college'); ?></option>
                                    <?php foreach ($all_colleges as $college): ?>
                                        <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_prog_department_id" class="form-label"><?php echo t('department'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_prog_department_id" name="department_id" required>
                                    <option value=""><?php echo t('select_department'); ?></option>
                                    <!-- سيتم ملء الأقسام بواسطة JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="add_prog_duration_years" class="form-label"><?php echo t('duration_years'); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="add_prog_duration_years" name="duration_years" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="add_prog_total_credits" class="form-label"><?php echo t('total_credits'); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="add_prog_total_credits" name="total_credits" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="add_prog_degree_type" class="form-label"><?php echo t('degree_type'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_prog_degree_type" name="degree_type" required>
                                    <option value=""><?php echo t('select_degree_type'); ?></option>
                                    <option value="دبلوم"><?php echo t('diploma'); ?></option>
                                    <option value="بكالوريوس"><?php echo t('bachelor'); ?></option>
                                    <option value="ماجستير"><?php echo t('master'); ?></option>
                                    <option value="دكتوراه"><?php echo t('phd'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="add_prog_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="add_prog_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('add_program'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض برنامج -->
    <div class="modal fade" id="viewProgramModal" tabindex="-1" aria-labelledby="viewProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewProgramModalLabel"><?php echo t('program_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('program_name'); ?></h6>
                            <p id="view_prog_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('program_code'); ?></h6>
                            <p id="view_prog_code"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('college'); ?></h6>
                            <p id="view_prog_college_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('department'); ?></h6>
                            <p id="view_prog_department_name"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6><?php echo t('degree_type'); ?></h6>
                            <p id="view_prog_degree_type"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6><?php echo t('duration_years'); ?></h6>
                            <p id="view_prog_duration_years"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6><?php echo t('total_credits'); ?></h6>
                            <p id="view_prog_total_credits"></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('description'); ?></h6>
                        <p id="view_prog_description"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('status'); ?></h6>
                        <p id="view_prog_status"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_prog_courses_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('courses'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_prog_students_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('students'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="viewProgEditBtn"><?php echo t('edit'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل برنامج -->
    <div class="modal fade" id="editProgramModal" tabindex="-1" aria-labelledby="editProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProgramModalLabel"><?php echo t('edit_program'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_program">
                    <input type="hidden" name="program_id" id="edit_program_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_prog_name" class="form-label"><?php echo t('program_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_prog_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_prog_code" class="form-label"><?php echo t('program_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_prog_code" name="code" required>
                                <div class="form-text"><?php echo t('program_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_prog_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_prog_college_id" name="college_id_temp" required onchange="loadDepartments('edit_prog_college_id', 'edit_prog_department_id')">
                                    <option value=""><?php echo t('select_college'); ?></option>
                                    <?php foreach ($all_colleges as $college): ?>
                                        <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_prog_department_id" class="form-label"><?php echo t('department'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_prog_department_id" name="department_id" required>
                                    <option value=""><?php echo t('select_department'); ?></option>
                                    <!-- سيتم ملء الأقسام بواسطة JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_prog_duration_years" class="form-label"><?php echo t('duration_years'); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_prog_duration_years" name="duration_years" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_prog_total_credits" class="form-label"><?php echo t('total_credits'); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_prog_total_credits" name="total_credits" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_prog_degree_type" class="form-label"><?php echo t('degree_type'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_prog_degree_type" name="degree_type" required>
                                    <option value=""><?php echo t('select_degree_type'); ?></option>
                                    <option value="دبلوم"><?php echo t('diploma'); ?></option>
                                    <option value="بكالوريوس"><?php echo t('bachelor'); ?></option>
                                    <option value="ماجستير"><?php echo t('master'); ?></option>
                                    <option value="دكتوراه"><?php echo t('phd'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_prog_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="edit_prog_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_prog_status" class="form-label"><?php echo t('status'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prog_status" name="status" required>
                                <option value="active"><?php echo t('active'); ?></option>
                                <option value="inactive"><?php echo t('inactive'); ?></option>
                            </select>
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
    
    <!-- مودال حذف برنامج -->
    <div class="modal fade" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProgramModalLabel"><?php echo t('delete_program'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_program">
                    <input type="hidden" name="program_id" id="delete_program_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_program'); ?>: <strong id="delete_program_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_program_warning'); ?>
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
    
    <!-- ملف JavaScript الرئيسي -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // مصفوفة وهمية للأقسام (يجب استبدالها بـ API call)
        const allDepartmentsData = <?php echo json_encode(get_all_departments_for_filter($db, 0)); ?>;
        
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
            
            // زر التعديل في مودال العرض
            document.getElementById('viewProgEditBtn').addEventListener('click', function() {
                $('#viewProgramModal').modal('hide');
                $('#editProgramModal').modal('show');
            });
            
            // تغيير فلتر الكلية
            document.getElementById('collegeFilterSelect').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
        
        // دالة تحميل الأقسام بناءً على الكلية المختارة
        function loadDepartments(collegeSelectId, departmentSelectId, selectedDepartmentId = null) {
            const collegeId = document.getElementById(collegeSelectId).value;
            const departmentSelect = document.getElementById(departmentSelectId);
            
            // مسح الخيارات الحالية
            departmentSelect.innerHTML = '<option value=""><?php echo t('select_department'); ?></option>';
            
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
        
        // دالة تحضير مودال عرض البرنامج
        function prepareViewProgram(program) {
            document.getElementById('view_prog_name').textContent = program.name;
            document.getElementById('view_prog_code').textContent = program.code;
            document.getElementById('view_prog_college_name').textContent = program.college_name;
            document.getElementById('view_prog_department_name').textContent = program.department_name;
            document.getElementById('view_prog_degree_type').textContent = program.degree_type;
            document.getElementById('view_prog_duration_years').textContent = program.duration_years;
            document.getElementById('view_prog_total_credits').textContent = program.total_credits;
            document.getElementById('view_prog_description').textContent = program.description || '-';
            document.getElementById('view_prog_status').innerHTML = `<span class="status-${program.status}"><i class="fas ${program.status === 'active' ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${program.status === 'active' ? '<?php echo t('active'); ?>' : '<?php echo t('inactive'); ?>'}</span>`;
            document.getElementById('view_prog_courses_count').textContent = program.courses_count;
            document.getElementById('view_prog_students_count').textContent = program.students_count;
            
            // تحضير بيانات التعديل أيضاً
            prepareEditProgram(program);
        }
        
        // دالة تحضير مودال تعديل البرنامج
        function prepareEditProgram(program) {
            document.getElementById('edit_program_id').value = program.id;
            document.getElementById('edit_prog_name').value = program.name;
            document.getElementById('edit_prog_code').value = program.code;
            document.getElementById('edit_prog_college_id').value = program.college_id;
            // تحميل الأقسام للكلية المحددة وتحديد القسم الصحيح
            loadDepartments('edit_prog_college_id', 'edit_prog_department_id', program.department_id);
            document.getElementById('edit_prog_duration_years').value = program.duration_years;
            document.getElementById('edit_prog_total_credits').value = program.total_credits;
            document.getElementById('edit_prog_degree_type').value = program.degree_type;
            document.getElementById('edit_prog_description').value = program.description || '';
            document.getElementById('edit_prog_status').value = program.status;
        }
        
        // دالة تحضير مودال حذف البرنامج
        function prepareDeleteProgram(programId, programName) {
            document.getElementById('delete_program_id').value = programId;
            document.getElementById('delete_program_name').textContent = programName;
        }
        
        // تحميل الأقسام عند فتح مودال الإضافة لأول مرة (إذا كانت هناك كلية محددة)
        const addProgramModal = document.getElementById('addProgramModal');
        addProgramModal.addEventListener('show.bs.modal', function (event) {
            loadDepartments('add_prog_college_id', 'add_prog_department_id');
        });
        
        // تحميل الأقسام عند فتح مودال التعديل لأول مرة (يتم استدعاؤها من prepareEditProgram)
        // لا حاجة لحدث show.bs.modal هنا
        
    </script>
</body>
</html>
