<?php
/**
 * صفحة إدارة الأقسام في نظام UniverBoard
 * تتيح للمشرف إدارة جميع الأقسام في الكليات المختلفة
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

// تحديد فلتر الكلية
$college_filter = isset($_GET['college_id']) ? intval($_GET['college_id']) : 0;

// الحصول على قائمة الكليات للفلتر
$all_colleges = get_all_colleges_for_filter($db);

// الحصول على قائمة الأقسام
$departments = get_departments($db, $page, $items_per_page, $search, $college_filter);
$total_departments = get_total_departments($db, $search, $college_filter);
$total_pages = ceil($total_departments / $items_per_page);

// معالجة إضافة قسم جديد
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_department') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $college_id = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
    $head_name = filter_input(INPUT_POST, 'head_name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($name) || empty($code) || empty($college_id)) {
        $add_error = t('name_code_college_required');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً في نفس الكلية
        if (department_code_exists_in_college($db, $code, $college_id)) {
            $add_error = t('department_code_already_exists_in_college');
        } else {
            // إضافة القسم الجديد
            $result = add_department($db, $name, $code, $college_id, $head_name, $description);
            
            if ($result) {
                $add_success = true;
                // تحديث قائمة الأقسام
                $departments = get_departments($db, $page, $items_per_page, $search, $college_filter);
                $total_departments = get_total_departments($db, $search, $college_filter);
                $total_pages = ceil($total_departments / $items_per_page);
            } else {
                $add_error = t('add_department_failed');
            }
        }
    }
}

// معالجة تعديل قسم
$edit_success = false;
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_department') {
    $department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $college_id = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
    $head_name = filter_input(INPUT_POST, 'head_name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($department_id) || empty($name) || empty($code) || empty($college_id) || empty($status)) {
        $edit_error = t('required_fields_missing');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً (لقسم آخر في نفس الكلية)
        if (department_code_exists_for_other_department($db, $code, $college_id, $department_id)) {
            $edit_error = t('department_code_already_exists_in_college');
        } else {
            // تعديل القسم
            $result = update_department($db, $department_id, $name, $code, $college_id, $head_name, $description, $status);
            
            if ($result) {
                $edit_success = true;
                // تحديث قائمة الأقسام
                $departments = get_departments($db, $page, $items_per_page, $search, $college_filter);
                $total_departments = get_total_departments($db, $search, $college_filter);
                $total_pages = ceil($total_departments / $items_per_page);
            } else {
                $edit_error = t('edit_department_failed');
            }
        }
    }
}

// معالجة حذف قسم
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_department') {
    $department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($department_id)) {
        $delete_error = t('department_id_required');
    } else {
        // التحقق من عدم وجود برامج أو مقررات مرتبطة بالقسم
        if (department_has_dependencies($db, $department_id)) {
            $delete_error = t('department_has_dependencies');
        } else {
            // حذف القسم
            $result = delete_department($db, $department_id);
            
            if ($result) {
                $delete_success = true;
                // تحديث قائمة الأقسام
                $departments = get_departments($db, $page, $items_per_page, $search, $college_filter);
                $total_departments = get_total_departments($db, $search, $college_filter);
                $total_pages = ceil($total_departments / $items_per_page);
            } else {
                $delete_error = t('delete_department_failed');
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

function get_departments($db, $page, $items_per_page, $search, $college_filter) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع الأقسام من قاعدة البيانات مع تطبيق البحث والفلتر
    $departments = [
        [
            'id' => 1,
            'name' => 'قسم علوم الحاسب',
            'code' => 'CS',
            'college_id' => 1,
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'head_name' => 'د. علي حسن',
            'description' => 'قسم متخصص في دراسة علوم الحاسب الأساسية وتطبيقاتها.',
            'programs_count' => 3,
            'courses_count' => 20,
            'teachers_count' => 10,
            'students_count' => 150,
            'status' => 'active',
            'created_at' => '2025-01-10 09:00:00'
        ],
        [
            'id' => 2,
            'name' => 'قسم نظم المعلومات',
            'code' => 'IS',
            'college_id' => 1,
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'head_name' => 'د. منى صالح',
            'description' => 'قسم يركز على تحليل وتصميم وتطوير نظم المعلومات.',
            'programs_count' => 2,
            'courses_count' => 15,
            'teachers_count' => 8,
            'students_count' => 120,
            'status' => 'active',
            'created_at' => '2025-01-11 10:30:00'
        ],
        [
            'id' => 3,
            'name' => 'قسم هندسة البرمجيات',
            'code' => 'SE',
            'college_id' => 1,
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'head_name' => 'د. يوسف أحمد',
            'description' => 'قسم متخصص في هندسة وتطوير البرمجيات عالية الجودة.',
            'programs_count' => 2,
            'courses_count' => 18,
            'teachers_count' => 7,
            'students_count' => 100,
            'status' => 'active',
            'created_at' => '2025-01-12 11:45:00'
        ],
        [
            'id' => 4,
            'name' => 'قسم الهندسة المدنية',
            'code' => 'CE',
            'college_id' => 2,
            'college_name' => 'كلية الهندسة',
            'head_name' => 'د. إبراهيم محمود',
            'description' => 'قسم متخصص في تصميم وإنشاء وصيانة البنية التحتية.',
            'programs_count' => 1,
            'courses_count' => 25,
            'teachers_count' => 12,
            'students_count' => 200,
            'status' => 'active',
            'created_at' => '2025-01-15 08:30:00'
        ],
        [
            'id' => 5,
            'name' => 'قسم الهندسة الكهربائية',
            'code' => 'EE',
            'college_id' => 2,
            'college_name' => 'كلية الهندسة',
            'head_name' => 'د. سمير قاسم',
            'description' => 'قسم يغطي مجالات الطاقة والاتصالات والإلكترونيات.',
            'programs_count' => 2,
            'courses_count' => 30,
            'teachers_count' => 15,
            'students_count' => 250,
            'status' => 'active',
            'created_at' => '2025-01-16 13:00:00'
        ],
        [
            'id' => 6,
            'name' => 'قسم الفيزياء',
            'code' => 'PHY',
            'college_id' => 3,
            'college_name' => 'كلية العلوم',
            'head_name' => 'د. ليلى عبدالله',
            'description' => 'قسم يدرس المبادئ الأساسية للفيزياء وتطبيقاتها.',
            'programs_count' => 1,
            'courses_count' => 15,
            'teachers_count' => 9,
            'students_count' => 180,
            'status' => 'inactive',
            'created_at' => '2025-01-18 10:00:00'
        ],
        [
            'id' => 7,
            'name' => 'قسم الكيمياء',
            'code' => 'CHEM',
            'college_id' => 3,
            'college_name' => 'كلية العلوم',
            'head_name' => 'د. رائد سالم',
            'description' => 'قسم متخصص في دراسة تركيب المواد وخصائصها وتفاعلاتها.',
            'programs_count' => 1,
            'courses_count' => 16,
            'teachers_count' => 10,
            'students_count' => 170,
            'status' => 'active',
            'created_at' => '2025-01-19 11:15:00'
        ]
    ];
    
    // تطبيق فلتر الكلية
    if ($college_filter > 0) {
        $departments = array_filter($departments, function($department) use ($college_filter) {
            return $department['college_id'] == $college_filter;
        });
    }
    
    // تطبيق البحث
    if (!empty($search)) {
        $departments = array_filter($departments, function($department) use ($search) {
            return stripos($department['name'], $search) !== false || 
                   stripos($department['code'], $search) !== false ||
                   stripos($department['college_name'], $search) !== false ||
                   stripos($department['head_name'], $search) !== false;
        });
    }
    
    // ترتيب الأقسام حسب تاريخ الإنشاء (من الأحدث إلى الأقدم)
    usort($departments, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $departments = array_slice($departments, $offset, $items_per_page);
    
    return $departments;
}

function get_total_departments($db, $search, $college_filter) {
    // في الواقع، يجب استرجاع عدد الأقسام من قاعدة البيانات مع تطبيق البحث والفلتر
    $total = 7;
    
    // تقليل العدد حسب الفلتر والبحث
    if ($college_filter > 0) {
        $total = $total * 0.3; // تقريباً 30% من الأقسام في الكلية المحددة
    }
    if (!empty($search)) {
        $total = $total * 0.5; // تقريباً 50% من الأقسام تطابق البحث
    }
    
    return ceil($total);
}

function department_code_exists_in_college($db, $code, $college_id) {
    // في الواقع، يجب التحقق من وجود رمز القسم في الكلية المحددة في قاعدة البيانات
    return false;
}

function department_code_exists_for_other_department($db, $code, $college_id, $department_id) {
    // في الواقع، يجب التحقق من وجود رمز القسم لقسم آخر في نفس الكلية في قاعدة البيانات
    return false;
}

function department_has_dependencies($db, $department_id) {
    // في الواقع، يجب التحقق من وجود برامج أو مقررات مرتبطة بالقسم في قاعدة البيانات
    return false;
}

function add_department($db, $name, $code, $college_id, $head_name, $description) {
    // في الواقع، يجب إضافة القسم إلى قاعدة البيانات
    return true;
}

function update_department($db, $department_id, $name, $code, $college_id, $head_name, $description, $status) {
    // في الواقع، يجب تحديث معلومات القسم في قاعدة البيانات
    return true;
}

function delete_department($db, $department_id) {
    // في الواقع، يجب حذف القسم من قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('departments_management'); ?></title>
    
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
        /* (نفس تنسيقات CSS من admin_colleges.php) */
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
        
        /* تنسيقات حالة القسم */
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
                    <a class="nav-link active" href="admin_departments.php">
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
            <h1 class="page-title"><?php echo t('departments_management'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_all_departments_in_colleges'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($add_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('department_added_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($edit_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('department_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('department_deleted_successfully'); ?>
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
                <div class="search-box me-2 mb-2" style="width: 250px;">
                    <i class="fas fa-search search-icon"></i>
                    <form action="" method="get" id="searchForm">
                        <input type="text" class="form-control" name="search" placeholder="<?php echo t('search_departments'); ?>" value="<?php echo $search; ?>">
                        <input type="hidden" name="college_id" value="<?php echo $college_filter; ?>">
                    </form>
                </div>
                <div class="me-2 mb-2" style="width: 250px;">
                    <form action="" method="get" id="filterForm">
                        <select class="form-select" name="college_id" onchange="document.getElementById('filterForm').submit();">
                            <option value="0"><?php echo t('all_colleges'); ?></option>
                            <?php foreach ($all_colleges as $college): ?>
                                <option value="<?php echo $college['id']; ?>" <?php echo $college_filter == $college['id'] ? 'selected' : ''; ?>><?php echo $college['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                    </form>
                </div>
            </div>
            
            <div class="mb-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                    <i class="fas fa-plus me-2"></i> <?php echo t('add_department'); ?>
                </button>
            </div>
        </div>
        
        <!-- عرض الأقسام (جدول) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-building me-2"></i> <?php echo t('departments_list'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('department_name'); ?></th>
                                <th><?php echo t('department_code'); ?></th>
                                <th><?php echo t('college'); ?></th>
                                <th><?php echo t('department_head'); ?></th>
                                <th><?php echo t('programs'); ?></th>
                                <th><?php echo t('courses'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($departments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center"><?php echo t('no_departments_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($departments as $department): ?>
                                    <tr>
                                        <td><?php echo $department['name']; ?></td>
                                        <td><?php echo $department['code']; ?></td>
                                        <td><?php echo $department['college_name']; ?></td>
                                        <td><?php echo $department['head_name'] ?: '-'; ?></td>
                                        <td><?php echo $department['programs_count']; ?></td>
                                        <td><?php echo $department['courses_count']; ?></td>
                                        <td>
                                            <span class="status-<?php echo $department['status']; ?>">
                                                <i class="fas <?php echo $department['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                                <?php echo $department['status'] === 'active' ? t('active') : t('inactive'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewDepartmentModal" onclick="prepareViewDepartment(<?php echo htmlspecialchars(json_encode($department)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDepartmentModal" onclick="prepareEditDepartment(<?php echo htmlspecialchars(json_encode($department)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDepartmentModal" onclick="prepareDeleteDepartment(<?php echo $department['id']; ?>, '<?php echo $department['name']; ?>')">
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
                            <?php echo t('showing'); ?> <?php echo count($departments); ?> <?php echo t('of'); ?> <?php echo $total_departments; ?> <?php echo t('departments'); ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&college_id=<?php echo $college_filter; ?>" aria-label="Next">
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
    
    <!-- مودال إضافة قسم -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel"><?php echo t('add_new_department'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_department">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_name" class="form-label"><?php echo t('department_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_code" class="form-label"><?php echo t('department_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_code" name="code" required>
                                <div class="form-text"><?php echo t('department_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="add_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_college_id" name="college_id" required>
                                <option value=""><?php echo t('select_college'); ?></option>
                                <?php foreach ($all_colleges as $college): ?>
                                    <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add_head_name" class="form-label"><?php echo t('department_head'); ?></label>
                            <input type="text" class="form-control" id="add_head_name" name="head_name">
                        </div>
                        <div class="mb-3">
                            <label for="add_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('add_department'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض قسم -->
    <div class="modal fade" id="viewDepartmentModal" tabindex="-1" aria-labelledby="viewDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDepartmentModalLabel"><?php echo t('department_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('department_name'); ?></h6>
                            <p id="view_dept_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('department_code'); ?></h6>
                            <p id="view_dept_code"></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('college'); ?></h6>
                        <p id="view_dept_college_name"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('department_head'); ?></h6>
                        <p id="view_dept_head_name"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('description'); ?></h6>
                        <p id="view_dept_description"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('status'); ?></h6>
                        <p id="view_dept_status"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_dept_programs_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('programs'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_dept_courses_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('courses'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_dept_teachers_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('teachers'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_dept_students_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('students'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="viewDeptEditBtn"><?php echo t('edit'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل قسم -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDepartmentModalLabel"><?php echo t('edit_department'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_department">
                    <input type="hidden" name="department_id" id="edit_department_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_dept_name" class="form-label"><?php echo t('department_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_dept_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_dept_code" class="form-label"><?php echo t('department_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_dept_code" name="code" required>
                                <div class="form-text"><?php echo t('department_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_dept_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_dept_college_id" name="college_id" required>
                                <option value=""><?php echo t('select_college'); ?></option>
                                <?php foreach ($all_colleges as $college): ?>
                                    <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_dept_head_name" class="form-label"><?php echo t('department_head'); ?></label>
                            <input type="text" class="form-control" id="edit_dept_head_name" name="head_name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_dept_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="edit_dept_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_dept_status" class="form-label"><?php echo t('status'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_dept_status" name="status" required>
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
    
    <!-- مودال حذف قسم -->
    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDepartmentModalLabel"><?php echo t('delete_department'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_department">
                    <input type="hidden" name="department_id" id="delete_department_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_department'); ?>: <strong id="delete_department_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_department_warning'); ?>
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
            document.getElementById('viewDeptEditBtn').addEventListener('click', function() {
                $('#viewDepartmentModal').modal('hide');
                $('#editDepartmentModal').modal('show');
            });
        });
        
        // دالة تحضير مودال عرض القسم
        function prepareViewDepartment(department) {
            document.getElementById('view_dept_name').textContent = department.name;
            document.getElementById('view_dept_code').textContent = department.code;
            document.getElementById('view_dept_college_name').textContent = department.college_name;
            document.getElementById('view_dept_head_name').textContent = department.head_name || '-';
            document.getElementById('view_dept_description').textContent = department.description || '-';
            document.getElementById('view_dept_status').innerHTML = `<span class="status-${department.status}"><i class="fas ${department.status === 'active' ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${department.status === 'active' ? '<?php echo t('active'); ?>' : '<?php echo t('inactive'); ?>'}</span>`;
            document.getElementById('view_dept_programs_count').textContent = department.programs_count;
            document.getElementById('view_dept_courses_count').textContent = department.courses_count;
            document.getElementById('view_dept_teachers_count').textContent = department.teachers_count;
            document.getElementById('view_dept_students_count').textContent = department.students_count;
            
            // تحضير بيانات التعديل أيضاً
            prepareEditDepartment(department);
        }
        
        // دالة تحضير مودال تعديل القسم
        function prepareEditDepartment(department) {
            document.getElementById('edit_department_id').value = department.id;
            document.getElementById('edit_dept_name').value = department.name;
            document.getElementById('edit_dept_code').value = department.code;
            document.getElementById('edit_dept_college_id').value = department.college_id;
            document.getElementById('edit_dept_head_name').value = department.head_name || '';
            document.getElementById('edit_dept_description').value = department.description || '';
            document.getElementById('edit_dept_status').value = department.status;
        }
        
        // دالة تحضير مودال حذف القسم
        function prepareDeleteDepartment(departmentId, departmentName) {
            document.getElementById('delete_department_id').value = departmentId;
            document.getElementById('delete_department_name').textContent = departmentName;
        }
    </script>
</body>
</html>
