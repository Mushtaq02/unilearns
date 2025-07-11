<?php
/**
 * صفحة إدارة الكليات في نظام UniverBoard
 * تتيح للمشرف إدارة جميع الكليات في النظام
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

// الحصول على قائمة الكليات
$colleges = get_colleges($db, $page, $items_per_page, $search);
$total_colleges = get_total_colleges($db, $search);
$total_pages = ceil($total_colleges / $items_per_page);

// معالجة إضافة كلية جديدة
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_college') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $dean_name = filter_input(INPUT_POST, 'dean_name', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($name) || empty($code)) {
        $add_error = t('name_and_code_required');
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_error = t('invalid_email');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً
        if (college_code_exists($db, $code)) {
            $add_error = t('college_code_already_exists');
        } else {
            // إضافة الكلية الجديدة
            $result = add_college($db, $name, $code, $dean_name, $location, $phone, $email, $website, $description);
            
            if ($result) {
                $add_success = true;
                // تحديث قائمة الكليات
                $colleges = get_colleges($db, $page, $items_per_page, $search);
                $total_colleges = get_total_colleges($db, $search);
                $total_pages = ceil($total_colleges / $items_per_page);
            } else {
                $add_error = t('add_college_failed');
            }
        }
    }
}

// معالجة تعديل كلية
$edit_success = false;
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_college') {
    $college_id = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $dean_name = filter_input(INPUT_POST, 'dean_name', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($college_id) || empty($name) || empty($code) || empty($status)) {
        $edit_error = t('required_fields_missing');
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $edit_error = t('invalid_email');
    } else {
        // التحقق من عدم وجود الرمز مسبقاً (لكلية أخرى)
        if (college_code_exists_for_other_college($db, $code, $college_id)) {
            $edit_error = t('college_code_already_exists');
        } else {
            // تعديل الكلية
            $result = update_college($db, $college_id, $name, $code, $dean_name, $location, $phone, $email, $website, $description, $status);
            
            if ($result) {
                $edit_success = true;
                // تحديث قائمة الكليات
                $colleges = get_colleges($db, $page, $items_per_page, $search);
                $total_colleges = get_total_colleges($db, $search);
                $total_pages = ceil($total_colleges / $items_per_page);
            } else {
                $edit_error = t('edit_college_failed');
            }
        }
    }
}

// معالجة حذف كلية
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_college') {
    $college_id = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($college_id)) {
        $delete_error = t('college_id_required');
    } else {
        // التحقق من عدم وجود أقسام أو برامج أو مقررات مرتبطة بالكلية
        if (college_has_dependencies($db, $college_id)) {
            $delete_error = t('college_has_dependencies');
        } else {
            // حذف الكلية
            $result = delete_college($db, $college_id);
            
            if ($result) {
                $delete_success = true;
                // تحديث قائمة الكليات
                $colleges = get_colleges($db, $page, $items_per_page, $search);
                $total_colleges = get_total_colleges($db, $search);
                $total_pages = ceil($total_colleges / $items_per_page);
            } else {
                $delete_error = t('delete_college_failed');
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

function get_colleges($db, $page, $items_per_page, $search) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع الكليات من قاعدة البيانات مع تطبيق البحث
    $colleges = [
        [
            'id' => 1,
            'name' => 'كلية علوم الحاسب والمعلومات',
            'code' => 'CSIT',
            'dean_name' => 'د. محمد عبدالله',
            'location' => 'المبنى الرئيسي - الطابق الثالث',
            'phone' => '0123456789',
            'email' => 'csit@univerboard.com',
            'website' => 'https://csit.univerboard.com',
            'description' => 'كلية علوم الحاسب والمعلومات هي إحدى الكليات الرائدة في مجال تكنولوجيا المعلومات والحوسبة.',
            'departments_count' => 4,
            'programs_count' => 8,
            'courses_count' => 45,
            'teachers_count' => 30,
            'students_count' => 500,
            'status' => 'active',
            'created_at' => '2025-01-01 10:00:00'
        ],
        [
            'id' => 2,
            'name' => 'كلية الهندسة',
            'code' => 'ENG',
            'dean_name' => 'د. خالد العمري',
            'location' => 'مجمع الكليات العلمية - المبنى B',
            'phone' => '0123456790',
            'email' => 'engineering@univerboard.com',
            'website' => 'https://eng.univerboard.com',
            'description' => 'كلية الهندسة هي إحدى الكليات العريقة التي تقدم برامج متميزة في مختلف التخصصات الهندسية.',
            'departments_count' => 6,
            'programs_count' => 12,
            'courses_count' => 80,
            'teachers_count' => 45,
            'students_count' => 700,
            'status' => 'active',
            'created_at' => '2025-01-02 11:30:00'
        ],
        [
            'id' => 3,
            'name' => 'كلية العلوم',
            'code' => 'SCI',
            'dean_name' => 'د. فاطمة محمد',
            'location' => 'مجمع الكليات العلمية - المبنى C',
            'phone' => '0123456791',
            'email' => 'science@univerboard.com',
            'website' => 'https://sci.univerboard.com',
            'description' => 'كلية العلوم تقدم برامج متنوعة في العلوم الأساسية والتطبيقية لإعداد الكوادر العلمية المتميزة.',
            'departments_count' => 5,
            'programs_count' => 10,
            'courses_count' => 60,
            'teachers_count' => 35,
            'students_count' => 600,
            'status' => 'active',
            'created_at' => '2025-01-03 09:15:00'
        ],
        [
            'id' => 4,
            'name' => 'كلية الطب',
            'code' => 'MED',
            'dean_name' => 'د. عبدالرحمن سعد',
            'location' => 'المجمع الطبي',
            'phone' => '0123456792',
            'email' => 'medicine@univerboard.com',
            'website' => 'https://med.univerboard.com',
            'description' => 'كلية الطب تسعى لتخريج أطباء متميزين قادرين على تقديم الرعاية الصحية وفق أعلى المعايير العالمية.',
            'departments_count' => 8,
            'programs_count' => 5,
            'courses_count' => 70,
            'teachers_count' => 60,
            'students_count' => 400,
            'status' => 'active',
            'created_at' => '2025-01-04 14:20:00'
        ],
        [
            'id' => 5,
            'name' => 'كلية إدارة الأعمال',
            'code' => 'BUS',
            'dean_name' => 'د. سارة أحمد',
            'location' => 'مجمع الكليات الإدارية - المبنى A',
            'phone' => '0123456793',
            'email' => 'business@univerboard.com',
            'website' => 'https://bus.univerboard.com',
            'description' => 'كلية إدارة الأعمال تهدف إلى إعداد قادة المستقبل في مجال الأعمال والإدارة والاقتصاد.',
            'departments_count' => 4,
            'programs_count' => 7,
            'courses_count' => 55,
            'teachers_count' => 25,
            'students_count' => 550,
            'status' => 'active',
            'created_at' => '2025-01-05 16:45:00'
        ],
        [
            'id' => 6,
            'name' => 'كلية التربية',
            'code' => 'EDU',
            'dean_name' => 'د. نورة سعيد',
            'location' => 'مجمع الكليات الإنسانية - المبنى B',
            'phone' => '0123456794',
            'email' => 'education@univerboard.com',
            'website' => 'https://edu.univerboard.com',
            'description' => 'كلية التربية تسعى لإعداد معلمين ومعلمات مؤهلين تأهيلاً عالياً للمساهمة في تطوير العملية التعليمية.',
            'departments_count' => 3,
            'programs_count' => 6,
            'courses_count' => 40,
            'teachers_count' => 20,
            'students_count' => 450,
            'status' => 'inactive',
            'created_at' => '2025-01-06 13:10:00'
        ],
        [
            'id' => 7,
            'name' => 'كلية الآداب',
            'code' => 'ARTS',
            'dean_name' => 'د. عمر خالد',
            'location' => 'مجمع الكليات الإنسانية - المبنى C',
            'phone' => '0123456795',
            'email' => 'arts@univerboard.com',
            'website' => 'https://arts.univerboard.com',
            'description' => 'كلية الآداب تقدم برامج متنوعة في العلوم الإنسانية والاجتماعية لتنمية المهارات الفكرية والثقافية.',
            'departments_count' => 5,
            'programs_count' => 9,
            'courses_count' => 65,
            'teachers_count' => 30,
            'students_count' => 500,
            'status' => 'active',
            'created_at' => '2025-01-07 10:30:00'
        ]
    ];
    
    // تطبيق البحث
    if (!empty($search)) {
        $colleges = array_filter($colleges, function($college) use ($search) {
            return stripos($college['name'], $search) !== false || 
                   stripos($college['code'], $search) !== false ||
                   stripos($college['dean_name'], $search) !== false;
        });
    }
    
    // ترتيب الكليات حسب تاريخ الإنشاء (من الأحدث إلى الأقدم)
    usort($colleges, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $colleges = array_slice($colleges, $offset, $items_per_page);
    
    return $colleges;
}

function get_total_colleges($db, $search) {
    // في الواقع، يجب استرجاع عدد الكليات من قاعدة البيانات مع تطبيق البحث
    $total = 7;
    
    // تقليل العدد حسب البحث
    if (!empty($search)) {
        $total = $total * 0.5; // تقريباً 50% من الكليات تطابق البحث
    }
    
    return ceil($total);
}

function college_code_exists($db, $code) {
    // في الواقع، يجب التحقق من وجود رمز الكلية في قاعدة البيانات
    return false;
}

function college_code_exists_for_other_college($db, $code, $college_id) {
    // في الواقع، يجب التحقق من وجود رمز الكلية لكلية أخرى في قاعدة البيانات
    return false;
}

function college_has_dependencies($db, $college_id) {
    // في الواقع، يجب التحقق من وجود أقسام أو برامج أو مقررات مرتبطة بالكلية في قاعدة البيانات
    return false;
}

function add_college($db, $name, $code, $dean_name, $location, $phone, $email, $website, $description) {
    // في الواقع، يجب إضافة الكلية إلى قاعدة البيانات
    return true;
}

function update_college($db, $college_id, $name, $code, $dean_name, $location, $phone, $email, $website, $description, $status) {
    // في الواقع، يجب تحديث معلومات الكلية في قاعدة البيانات
    return true;
}

function delete_college($db, $college_id) {
    // في الواقع، يجب حذف الكلية من قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('colleges_management'); ?></title>
    
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
        
        /* تنسيقات حالة الكلية */
        .status-active {
            color: #198754;
        }
        
        .status-inactive {
            color: #dc3545;
        }
        
        /* تنسيقات بطاقات الكليات */
        .college-card {
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            background-color: white;
            transition: all 0.3s;
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .theme-dark .college-card {
            background-color: var(--dark-bg);
        }
        
        .college-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .college-card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        
        .college-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .college-card-code {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .college-card-body {
            padding: 1.5rem;
            flex: 1;
        }
        
        .college-card-info {
            margin-bottom: 1rem;
        }
        
        .college-card-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .college-card-info-item i {
            width: 20px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .college-card-info-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .college-card-stats {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }
        
        .college-card-stat {
            flex: 1;
            min-width: calc(33.333% - 1rem);
            margin: 0.5rem;
            padding: 0.75rem;
            background-color: rgba(0, 48, 73, 0.05);
            border-radius: 0.5rem;
            text-align: center;
        }
        
        .theme-dark .college-card-stat {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .college-card-stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .college-card-stat-label {
            font-size: 0.8rem;
            color: var(--gray-color);
        }
        
        .college-card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .college-card-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .college-card-status {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .college-card-actions .btn {
            padding: 0.4rem 0.6rem;
            font-size: 0.9rem;
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
                    <a class="nav-link active" href="admin_colleges.php">
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
            <h1 class="page-title"><?php echo t('colleges_management'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_all_colleges_in_the_system'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($add_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('college_added_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($edit_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('college_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('college_deleted_successfully'); ?>
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
        
        <!-- أزرار التحكم -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="search-box" style="width: 300px;">
                <i class="fas fa-search search-icon"></i>
                <form action="" method="get">
                    <input type="text" class="form-control" name="search" placeholder="<?php echo t('search_colleges'); ?>" value="<?php echo $search; ?>">
                </form>
            </div>
            
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCollegeModal">
                    <i class="fas fa-plus me-2"></i> <?php echo t('add_college'); ?>
                </button>
                <button class="btn btn-outline-primary ms-2" id="toggleViewBtn">
                    <i class="fas fa-th-large me-2"></i> <span id="toggleViewText"><?php echo t('grid_view'); ?></span>
                </button>
            </div>
        </div>
        
        <!-- عرض الكليات (جدول) -->
        <div id="tableView" class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-university me-2"></i> <?php echo t('colleges_list'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('name'); ?></th>
                                <th><?php echo t('code'); ?></th>
                                <th><?php echo t('dean'); ?></th>
                                <th><?php echo t('departments'); ?></th>
                                <th><?php echo t('programs'); ?></th>
                                <th><?php echo t('students'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($colleges)): ?>
                                <tr>
                                    <td colspan="8" class="text-center"><?php echo t('no_colleges_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($colleges as $college): ?>
                                    <tr>
                                        <td><?php echo $college['name']; ?></td>
                                        <td><?php echo $college['code']; ?></td>
                                        <td><?php echo $college['dean_name']; ?></td>
                                        <td><?php echo $college['departments_count']; ?></td>
                                        <td><?php echo $college['programs_count']; ?></td>
                                        <td><?php echo $college['students_count']; ?></td>
                                        <td>
                                            <span class="status-<?php echo $college['status']; ?>">
                                                <i class="fas <?php echo $college['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                                <?php echo $college['status'] === 'active' ? t('active') : t('inactive'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewCollegeModal" onclick="prepareViewCollege(<?php echo htmlspecialchars(json_encode($college)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCollegeModal" onclick="prepareEditCollege(<?php echo htmlspecialchars(json_encode($college)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCollegeModal" onclick="prepareDeleteCollege(<?php echo $college['id']; ?>, '<?php echo $college['name']; ?>')">
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
                            <?php echo t('showing'); ?> <?php echo count($colleges); ?> <?php echo t('of'); ?> <?php echo $total_colleges; ?> <?php echo t('colleges'); ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- عرض الكليات (بطاقات) -->
        <div id="gridView" class="row" style="display: none;">
            <?php if (empty($colleges)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> <?php echo t('no_colleges_found'); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($colleges as $college): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="college-card">
                            <div class="college-card-header">
                                <h5 class="college-card-title"><?php echo $college['name']; ?></h5>
                                <div class="college-card-code"><?php echo $college['code']; ?></div>
                            </div>
                            <div class="college-card-body">
                                <div class="college-card-info">
                                    <div class="college-card-info-item">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo $college['dean_name']; ?></span>
                                    </div>
                                    <div class="college-card-info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo $college['location']; ?></span>
                                    </div>
                                    <div class="college-card-info-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo $college['phone']; ?></span>
                                    </div>
                                    <div class="college-card-info-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo $college['email']; ?></span>
                                    </div>
                                </div>
                                <div class="college-card-stats">
                                    <div class="college-card-stat">
                                        <div class="college-card-stat-value"><?php echo $college['departments_count']; ?></div>
                                        <div class="college-card-stat-label"><?php echo t('departments'); ?></div>
                                    </div>
                                    <div class="college-card-stat">
                                        <div class="college-card-stat-value"><?php echo $college['programs_count']; ?></div>
                                        <div class="college-card-stat-label"><?php echo t('programs'); ?></div>
                                    </div>
                                    <div class="college-card-stat">
                                        <div class="college-card-stat-value"><?php echo $college['courses_count']; ?></div>
                                        <div class="college-card-stat-label"><?php echo t('courses'); ?></div>
                                    </div>
                                    <div class="college-card-stat">
                                        <div class="college-card-stat-value"><?php echo $college['teachers_count']; ?></div>
                                        <div class="college-card-stat-label"><?php echo t('teachers'); ?></div>
                                    </div>
                                    <div class="college-card-stat">
                                        <div class="college-card-stat-value"><?php echo $college['students_count']; ?></div>
                                        <div class="college-card-stat-label"><?php echo t('students'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="college-card-footer">
                                <div class="college-card-status">
                                    <span class="status-<?php echo $college['status']; ?>">
                                        <i class="fas <?php echo $college['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                        <?php echo $college['status'] === 'active' ? t('active') : t('inactive'); ?>
                                    </span>
                                </div>
                                <div class="college-card-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewCollegeModal" onclick="prepareViewCollege(<?php echo htmlspecialchars(json_encode($college)); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCollegeModal" onclick="prepareEditCollege(<?php echo htmlspecialchars(json_encode($college)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCollegeModal" onclick="prepareDeleteCollege(<?php echo $college['id']; ?>, '<?php echo $college['name']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- التنقل بين الصفحات -->
                <?php if ($total_pages > 1): ?>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div>
                                <?php echo t('showing'); ?> <?php echo count($colleges); ?> <?php echo t('of'); ?> <?php echo $total_colleges; ?> <?php echo t('colleges'); ?>
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- مودال إضافة كلية -->
    <div class="modal fade" id="addCollegeModal" tabindex="-1" aria-labelledby="addCollegeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCollegeModalLabel"><?php echo t('add_new_college'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_college">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label"><?php echo t('college_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label"><?php echo t('college_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required>
                                <div class="form-text"><?php echo t('college_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dean_name" class="form-label"><?php echo t('dean_name'); ?></label>
                                <input type="text" class="form-control" id="dean_name" name="dean_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label"><?php echo t('location'); ?></label>
                                <input type="text" class="form-control" id="location" name="location">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label"><?php echo t('phone'); ?></label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label"><?php echo t('email'); ?></label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="website" class="form-label"><?php echo t('website'); ?></label>
                            <input type="url" class="form-control" id="website" name="website">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('add_college'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض كلية -->
    <div class="modal fade" id="viewCollegeModal" tabindex="-1" aria-labelledby="viewCollegeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCollegeModalLabel"><?php echo t('college_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('college_name'); ?></h6>
                            <p id="view_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('college_code'); ?></h6>
                            <p id="view_code"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('dean_name'); ?></h6>
                            <p id="view_dean_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('location'); ?></h6>
                            <p id="view_location"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('phone'); ?></h6>
                            <p id="view_phone"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('email'); ?></h6>
                            <p id="view_email"></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('website'); ?></h6>
                        <p id="view_website"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('description'); ?></h6>
                        <p id="view_description"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('status'); ?></h6>
                        <p id="view_status"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_departments_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('departments'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_programs_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('programs'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_courses_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('courses'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_teachers_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('teachers'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 id="view_students_count" class="text-primary mb-2"></h3>
                                    <p class="mb-0"><?php echo t('students'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="viewEditBtn"><?php echo t('edit'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل كلية -->
    <div class="modal fade" id="editCollegeModal" tabindex="-1" aria-labelledby="editCollegeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCollegeModalLabel"><?php echo t('edit_college'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_college">
                    <input type="hidden" name="college_id" id="edit_college_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_name" class="form-label"><?php echo t('college_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_code" class="form-label"><?php echo t('college_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_code" name="code" required>
                                <div class="form-text"><?php echo t('college_code_hint'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_dean_name" class="form-label"><?php echo t('dean_name'); ?></label>
                                <input type="text" class="form-control" id="edit_dean_name" name="dean_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_location" class="form-label"><?php echo t('location'); ?></label>
                                <input type="text" class="form-control" id="edit_location" name="location">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_phone" class="form-label"><?php echo t('phone'); ?></label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label"><?php echo t('email'); ?></label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_website" class="form-label"><?php echo t('website'); ?></label>
                            <input type="url" class="form-control" id="edit_website" name="website">
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label"><?php echo t('status'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
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
    
    <!-- مودال حذف كلية -->
    <div class="modal fade" id="deleteCollegeModal" tabindex="-1" aria-labelledby="deleteCollegeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCollegeModalLabel"><?php echo t('delete_college'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_college">
                    <input type="hidden" name="college_id" id="delete_college_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_college'); ?>: <strong id="delete_college_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_college_warning'); ?>
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
            
            // تبديل طريقة العرض (جدول/بطاقات)
            document.getElementById('toggleViewBtn').addEventListener('click', function() {
                const tableView = document.getElementById('tableView');
                const gridView = document.getElementById('gridView');
                const toggleViewText = document.getElementById('toggleViewText');
                
                if (tableView.style.display !== 'none') {
                    tableView.style.display = 'none';
                    gridView.style.display = 'flex';
                    toggleViewText.textContent = '<?php echo t('table_view'); ?>';
                    this.innerHTML = '<i class="fas fa-list me-2"></i> <span id="toggleViewText"><?php echo t('table_view'); ?></span>';
                } else {
                    tableView.style.display = 'block';
                    gridView.style.display = 'none';
                    toggleViewText.textContent = '<?php echo t('grid_view'); ?>';
                    this.innerHTML = '<i class="fas fa-th-large me-2"></i> <span id="toggleViewText"><?php echo t('grid_view'); ?></span>';
                }
            });
            
            // زر التعديل في مودال العرض
            document.getElementById('viewEditBtn').addEventListener('click', function() {
                $('#viewCollegeModal').modal('hide');
                $('#editCollegeModal').modal('show');
            });
        });
        
        // دالة تحضير مودال عرض الكلية
        function prepareViewCollege(college) {
            document.getElementById('view_name').textContent = college.name;
            document.getElementById('view_code').textContent = college.code;
            document.getElementById('view_dean_name').textContent = college.dean_name || '-';
            document.getElementById('view_location').textContent = college.location || '-';
            document.getElementById('view_phone').textContent = college.phone || '-';
            document.getElementById('view_email').textContent = college.email || '-';
            document.getElementById('view_website').textContent = college.website || '-';
            document.getElementById('view_description').textContent = college.description || '-';
            document.getElementById('view_status').innerHTML = `<span class="status-${college.status}"><i class="fas ${college.status === 'active' ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${college.status === 'active' ? '<?php echo t('active'); ?>' : '<?php echo t('inactive'); ?>'}</span>`;
            document.getElementById('view_departments_count').textContent = college.departments_count;
            document.getElementById('view_programs_count').textContent = college.programs_count;
            document.getElementById('view_courses_count').textContent = college.courses_count;
            document.getElementById('view_teachers_count').textContent = college.teachers_count;
            document.getElementById('view_students_count').textContent = college.students_count;
            
            // تحضير بيانات التعديل أيضاً
            prepareEditCollege(college);
        }
        
        // دالة تحضير مودال تعديل الكلية
        function prepareEditCollege(college) {
            document.getElementById('edit_college_id').value = college.id;
            document.getElementById('edit_name').value = college.name;
            document.getElementById('edit_code').value = college.code;
            document.getElementById('edit_dean_name').value = college.dean_name || '';
            document.getElementById('edit_location').value = college.location || '';
            document.getElementById('edit_phone').value = college.phone || '';
            document.getElementById('edit_email').value = college.email || '';
            document.getElementById('edit_website').value = college.website || '';
            document.getElementById('edit_description').value = college.description || '';
            document.getElementById('edit_status').value = college.status;
        }
        
        // دالة تحضير مودال حذف الكلية
        function prepareDeleteCollege(collegeId, collegeName) {
            document.getElementById('delete_college_id').value = collegeId;
            document.getElementById('delete_college_name').textContent = collegeName;
        }
    </script>
</body>
</html>
