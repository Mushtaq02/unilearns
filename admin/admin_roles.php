<?php
/**
 * صفحة إدارة الأدوار والصلاحيات في نظام UniverBoard
 * تتيح للمشرف إدارة أدوار المستخدمين وصلاحياتهم
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

// الحصول على قائمة الأدوار
$roles = get_roles($db, $page, $items_per_page, $search);
$total_roles = get_total_roles($db, $search);
$total_pages = ceil($total_roles / $items_per_page);

// معالجة إضافة دور جديد
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_role') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    // التحقق من البيانات
    if (empty($name)) {
        $add_error = t('role_name_required');
    } else {
        // التحقق من عدم وجود الاسم مسبقاً
        if (role_name_exists($db, $name)) {
            $add_error = t('role_name_already_exists');
        } else {
            // إضافة الدور الجديد
            $result = add_role($db, $name, $description, $permissions);
            
            if ($result) {
                $add_success = true;
                // تحديث قائمة الأدوار
                $roles = get_roles($db, $page, $items_per_page, $search);
                $total_roles = get_total_roles($db, $search);
                $total_pages = ceil($total_roles / $items_per_page);
            } else {
                $add_error = t('add_role_failed');
            }
        }
    }
}

// معالجة تعديل دور
$edit_success = false;
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_role') {
    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    // التحقق من البيانات
    if (empty($role_id) || empty($name)) {
        $edit_error = t('role_name_required');
    } else {
        // التحقق من عدم وجود الاسم مسبقاً (لدور آخر)
        if (role_name_exists_for_other_role($db, $name, $role_id)) {
            $edit_error = t('role_name_already_exists');
        } else {
            // تعديل الدور
            $result = update_role($db, $role_id, $name, $description, $permissions);
            
            if ($result) {
                $edit_success = true;
                // تحديث قائمة الأدوار
                $roles = get_roles($db, $page, $items_per_page, $search);
                $total_roles = get_total_roles($db, $search);
                $total_pages = ceil($total_roles / $items_per_page);
            } else {
                $edit_error = t('edit_role_failed');
            }
        }
    }
}

// معالجة حذف دور
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_role') {
    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($role_id)) {
        $delete_error = t('role_id_required');
    } else {
        // التحقق من عدم وجود مستخدمين مرتبطين بالدور
        if (role_has_users($db, $role_id)) {
            $delete_error = t('role_has_users');
        } else {
            // حذف الدور
            $result = delete_role($db, $role_id);
            
            if ($result) {
                $delete_success = true;
                // تحديث قائمة الأدوار
                $roles = get_roles($db, $page, $items_per_page, $search);
                $total_roles = get_total_roles($db, $search);
                $total_pages = ceil($total_roles / $items_per_page);
            } else {
                $delete_error = t('delete_role_failed');
            }
        }
    }
}

// الحصول على قائمة الصلاحيات
$all_permissions = get_all_permissions($db);

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

function get_roles($db, $page, $items_per_page, $search) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع الأدوار من قاعدة البيانات مع تطبيق البحث
    $roles = [
        [
            'id' => 1,
            'name' => 'مشرف النظام',
            'description' => 'يمتلك جميع الصلاحيات في النظام',
            'users_count' => 2,
            'permissions' => ['admin_dashboard', 'admin_users', 'admin_roles', 'admin_colleges', 'admin_departments', 'admin_programs', 'admin_courses', 'admin_settings', 'admin_logs', 'admin_backup', 'admin_reports'],
            'created_at' => '2025-01-01 10:00:00',
            'is_system' => true
        ],
        [
            'id' => 2,
            'name' => 'مدير كلية',
            'description' => 'يدير كلية محددة وأقسامها وبرامجها',
            'users_count' => 5,
            'permissions' => ['college_dashboard', 'college_departments', 'college_programs', 'college_courses', 'college_teachers', 'college_students', 'college_schedule', 'college_reports'],
            'created_at' => '2025-01-02 11:30:00',
            'is_system' => true
        ],
        [
            'id' => 3,
            'name' => 'رئيس قسم',
            'description' => 'يدير قسم محدد وبرامجه ومقرراته',
            'users_count' => 10,
            'permissions' => ['department_dashboard', 'department_programs', 'department_courses', 'department_teachers', 'department_students', 'department_schedule', 'department_reports'],
            'created_at' => '2025-01-03 12:45:00',
            'is_system' => true
        ],
        [
            'id' => 4,
            'name' => 'معلم',
            'description' => 'يدير المقررات والواجبات والاختبارات',
            'users_count' => 50,
            'permissions' => ['teacher_dashboard', 'teacher_courses', 'teacher_assignments', 'teacher_grades', 'teacher_exams', 'teacher_students', 'teacher_schedule', 'teacher_messages', 'teacher_forums'],
            'created_at' => '2025-01-04 14:20:00',
            'is_system' => true
        ],
        [
            'id' => 5,
            'name' => 'طالب',
            'description' => 'يستعرض المقررات والواجبات والدرجات',
            'users_count' => 500,
            'permissions' => ['student_dashboard', 'student_courses', 'student_assignments', 'student_grades', 'student_schedule', 'student_messages', 'student_forums'],
            'created_at' => '2025-01-05 15:10:00',
            'is_system' => true
        ],
        [
            'id' => 6,
            'name' => 'مساعد إداري',
            'description' => 'يساعد في الأعمال الإدارية مع صلاحيات محدودة',
            'users_count' => 8,
            'permissions' => ['admin_dashboard', 'admin_users', 'admin_colleges', 'admin_departments', 'admin_programs', 'admin_courses'],
            'created_at' => '2025-01-10 09:30:00',
            'is_system' => false
        ],
    ];
    
    // تطبيق البحث
    if (!empty($search)) {
        $roles = array_filter($roles, function($role) use ($search) {
            return stripos($role['name'], $search) !== false || 
                   stripos($role['description'], $search) !== false;
        });
    }
    
    // ترتيب الأدوار حسب تاريخ الإنشاء (من الأحدث إلى الأقدم)
    usort($roles, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $roles = array_slice($roles, $offset, $items_per_page);
    
    return $roles;
}

function get_total_roles($db, $search) {
    // في الواقع، يجب استرجاع عدد الأدوار من قاعدة البيانات مع تطبيق البحث
    $total = 6;
    
    // تقليل العدد حسب البحث
    if (!empty($search)) {
        $total = $total * 0.5; // تقريباً 50% من الأدوار تطابق البحث
    }
    
    return ceil($total);
}

function get_all_permissions($db) {
    // في الواقع، يجب استرجاع جميع الصلاحيات من قاعدة البيانات
    return [
        // صلاحيات المشرف
        ['id' => 'admin_dashboard', 'name' => 'لوحة تحكم المشرف', 'category' => 'admin'],
        ['id' => 'admin_users', 'name' => 'إدارة المستخدمين', 'category' => 'admin'],
        ['id' => 'admin_roles', 'name' => 'إدارة الأدوار والصلاحيات', 'category' => 'admin'],
        ['id' => 'admin_colleges', 'name' => 'إدارة الكليات', 'category' => 'admin'],
        ['id' => 'admin_departments', 'name' => 'إدارة الأقسام', 'category' => 'admin'],
        ['id' => 'admin_programs', 'name' => 'إدارة البرامج الأكاديمية', 'category' => 'admin'],
        ['id' => 'admin_courses', 'name' => 'إدارة المقررات الدراسية', 'category' => 'admin'],
        ['id' => 'admin_settings', 'name' => 'إعدادات النظام', 'category' => 'admin'],
        ['id' => 'admin_logs', 'name' => 'سجلات النظام', 'category' => 'admin'],
        ['id' => 'admin_backup', 'name' => 'النسخ الاحتياطي واستعادة البيانات', 'category' => 'admin'],
        ['id' => 'admin_reports', 'name' => 'التقارير', 'category' => 'admin'],
        
        // صلاحيات الكلية
        ['id' => 'college_dashboard', 'name' => 'لوحة تحكم الكلية', 'category' => 'college'],
        ['id' => 'college_departments', 'name' => 'إدارة أقسام الكلية', 'category' => 'college'],
        ['id' => 'college_programs', 'name' => 'إدارة برامج الكلية', 'category' => 'college'],
        ['id' => 'college_courses', 'name' => 'إدارة مقررات الكلية', 'category' => 'college'],
        ['id' => 'college_teachers', 'name' => 'إدارة معلمي الكلية', 'category' => 'college'],
        ['id' => 'college_students', 'name' => 'إدارة طلاب الكلية', 'category' => 'college'],
        ['id' => 'college_schedule', 'name' => 'إدارة جدول الكلية', 'category' => 'college'],
        ['id' => 'college_reports', 'name' => 'تقارير الكلية', 'category' => 'college'],
        
        // صلاحيات القسم
        ['id' => 'department_dashboard', 'name' => 'لوحة تحكم القسم', 'category' => 'department'],
        ['id' => 'department_programs', 'name' => 'إدارة برامج القسم', 'category' => 'department'],
        ['id' => 'department_courses', 'name' => 'إدارة مقررات القسم', 'category' => 'department'],
        ['id' => 'department_teachers', 'name' => 'إدارة معلمي القسم', 'category' => 'department'],
        ['id' => 'department_students', 'name' => 'إدارة طلاب القسم', 'category' => 'department'],
        ['id' => 'department_schedule', 'name' => 'إدارة جدول القسم', 'category' => 'department'],
        ['id' => 'department_reports', 'name' => 'تقارير القسم', 'category' => 'department'],
        
        // صلاحيات المعلم
        ['id' => 'teacher_dashboard', 'name' => 'لوحة تحكم المعلم', 'category' => 'teacher'],
        ['id' => 'teacher_courses', 'name' => 'إدارة المقررات', 'category' => 'teacher'],
        ['id' => 'teacher_assignments', 'name' => 'إدارة الواجبات', 'category' => 'teacher'],
        ['id' => 'teacher_grades', 'name' => 'إدارة الدرجات', 'category' => 'teacher'],
        ['id' => 'teacher_exams', 'name' => 'إدارة الاختبارات', 'category' => 'teacher'],
        ['id' => 'teacher_students', 'name' => 'إدارة الطلاب', 'category' => 'teacher'],
        ['id' => 'teacher_schedule', 'name' => 'جدول المعلم', 'category' => 'teacher'],
        ['id' => 'teacher_messages', 'name' => 'الرسائل', 'category' => 'teacher'],
        ['id' => 'teacher_forums', 'name' => 'المنتديات', 'category' => 'teacher'],
        
        // صلاحيات الطالب
        ['id' => 'student_dashboard', 'name' => 'لوحة تحكم الطالب', 'category' => 'student'],
        ['id' => 'student_courses', 'name' => 'المقررات الدراسية', 'category' => 'student'],
        ['id' => 'student_assignments', 'name' => 'الواجبات', 'category' => 'student'],
        ['id' => 'student_grades', 'name' => 'الدرجات', 'category' => 'student'],
        ['id' => 'student_schedule', 'name' => 'الجدول الدراسي', 'category' => 'student'],
        ['id' => 'student_messages', 'name' => 'الرسائل', 'category' => 'student'],
        ['id' => 'student_forums', 'name' => 'المنتديات', 'category' => 'student'],
    ];
}

function role_name_exists($db, $name) {
    // في الواقع، يجب التحقق من وجود اسم الدور في قاعدة البيانات
    return false;
}

function role_name_exists_for_other_role($db, $name, $role_id) {
    // في الواقع، يجب التحقق من وجود اسم الدور لدور آخر في قاعدة البيانات
    return false;
}

function role_has_users($db, $role_id) {
    // في الواقع، يجب التحقق من وجود مستخدمين مرتبطين بالدور في قاعدة البيانات
    return $role_id <= 5; // الأدوار الافتراضية لها مستخدمين
}

function add_role($db, $name, $description, $permissions) {
    // في الواقع، يجب إضافة الدور والصلاحيات إلى قاعدة البيانات
    return true;
}

function update_role($db, $role_id, $name, $description, $permissions) {
    // في الواقع، يجب تحديث معلومات الدور والصلاحيات في قاعدة البيانات
    return true;
}

function delete_role($db, $role_id) {
    // في الواقع، يجب حذف الدور من قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('roles_permissions_management'); ?></title>
    
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
        
        /* تنسيقات خاصة بالأدوار والصلاحيات */
        .permissions-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
        }
        
        .theme-dark .permissions-container {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .permission-category {
            margin-bottom: 1.5rem;
        }
        
        .permission-category:last-child {
            margin-bottom: 0;
        }
        
        .permission-category-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .permission-item {
            margin-bottom: 0.5rem;
        }
        
        .permission-item:last-child {
            margin-bottom: 0;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .theme-dark .form-check-input {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .theme-dark .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        [dir="rtl"] .role-badge {
            margin-right: 0;
            margin-left: 0.25rem;
        }
        
        .theme-dark .role-badge {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .system-role {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .theme-dark .system-role {
            background-color: rgba(25, 135, 84, 0.2);
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
                    <a class="nav-link active" href="admin_roles.php">
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
            <h1 class="page-title"><?php echo t('roles_permissions_management'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_user_roles_and_permissions'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($add_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('role_added_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($edit_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('role_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('role_deleted_successfully'); ?>
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
        
        <!-- أزرار التحكم والبحث -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="search-box mb-2" style="width: 300px;">
                <i class="fas fa-search search-icon"></i>
                <form action="" method="get" id="searchForm">
                    <input type="text" class="form-control" name="search" placeholder="<?php echo t('search_roles'); ?>" value="<?php echo $search; ?>">
                </form>
            </div>
            
            <div class="mb-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="fas fa-plus me-2"></i> <?php echo t('add_role'); ?>
                </button>
            </div>
        </div>
        
        <!-- عرض الأدوار (جدول) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-user-tag me-2"></i> <?php echo t('roles_list'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('role_name'); ?></th>
                                <th><?php echo t('description'); ?></th>
                                <th><?php echo t('users_count'); ?></th>
                                <th><?php echo t('permissions'); ?></th>
                                <th><?php echo t('type'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($roles)): ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php echo t('no_roles_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?php echo $role['name']; ?></td>
                                        <td><?php echo $role['description']; ?></td>
                                        <td><?php echo $role['users_count']; ?></td>
                                        <td>
                                            <?php if (!empty($role['permissions'])): ?>
                                                <span class="badge bg-primary"><?php echo count($role['permissions']); ?> <?php echo t('permissions'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo t('no_permissions'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($role['is_system']): ?>
                                                <span class="badge bg-success"><?php echo t('system_role'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo t('custom_role'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewRoleModal" onclick="prepareViewRole(<?php echo htmlspecialchars(json_encode($role)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoleModal" onclick="prepareEditRole(<?php echo htmlspecialchars(json_encode($role)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if (!$role['is_system']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRoleModal" onclick="prepareDeleteRole(<?php echo $role['id']; ?>, '<?php echo $role['name']; ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled title="<?php echo t('system_role_cannot_be_deleted'); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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
                            <?php echo t('showing'); ?> <?php echo count($roles); ?> <?php echo t('of'); ?> <?php echo $total_roles; ?> <?php echo t('roles'); ?>
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
    </div>
    
    <!-- مودال إضافة دور -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel"><?php echo t('add_new_role'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_role">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_role_name" class="form-label"><?php echo t('role_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_role_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_role_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="add_role_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo t('permissions'); ?> <span class="text-danger">*</span></label>
                            <div class="permissions-container">
                                <?php
                                // تجميع الصلاحيات حسب الفئة
                                $permissions_by_category = [];
                                foreach ($all_permissions as $permission) {
                                    $category = $permission['category'];
                                    if (!isset($permissions_by_category[$category])) {
                                        $permissions_by_category[$category] = [];
                                    }
                                    $permissions_by_category[$category][] = $permission;
                                }
                                
                                // عرض الصلاحيات حسب الفئة
                                foreach ($permissions_by_category as $category => $permissions):
                                    $category_name = '';
                                    switch ($category) {
                                        case 'admin':
                                            $category_name = t('admin_permissions');
                                            break;
                                        case 'college':
                                            $category_name = t('college_permissions');
                                            break;
                                        case 'department':
                                            $category_name = t('department_permissions');
                                            break;
                                        case 'teacher':
                                            $category_name = t('teacher_permissions');
                                            break;
                                        case 'student':
                                            $category_name = t('student_permissions');
                                            break;
                                        default:
                                            $category_name = $category;
                                    }
                                ?>
                                    <div class="permission-category">
                                        <div class="permission-category-title"><?php echo $category_name; ?></div>
                                        <?php foreach ($permissions as $permission): ?>
                                            <div class="permission-item">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" id="add_permission_<?php echo $permission['id']; ?>">
                                                    <label class="form-check-label" for="add_permission_<?php echo $permission['id']; ?>">
                                                        <?php echo $permission['name']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('add_role'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال عرض دور -->
    <div class="modal fade" id="viewRoleModal" tabindex="-1" aria-labelledby="viewRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRoleModalLabel"><?php echo t('role_details'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('role_name'); ?></h6>
                            <p id="view_role_name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><?php echo t('type'); ?></h6>
                            <p id="view_role_type"></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('description'); ?></h6>
                        <p id="view_role_description"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('users_count'); ?></h6>
                        <p id="view_role_users_count"></p>
                    </div>
                    <div class="mb-3">
                        <h6><?php echo t('permissions'); ?></h6>
                        <div id="view_role_permissions"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="viewRoleEditBtn"><?php echo t('edit'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل دور -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel"><?php echo t('edit_role'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_role">
                    <input type="hidden" name="role_id" id="edit_role_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_role_name" class="form-label"><?php echo t('role_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_role_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role_description" class="form-label"><?php echo t('description'); ?></label>
                            <textarea class="form-control" id="edit_role_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo t('permissions'); ?> <span class="text-danger">*</span></label>
                            <div class="permissions-container">
                                <?php
                                // عرض الصلاحيات حسب الفئة (نفس الكود السابق)
                                foreach ($permissions_by_category as $category => $permissions):
                                    $category_name = '';
                                    switch ($category) {
                                        case 'admin':
                                            $category_name = t('admin_permissions');
                                            break;
                                        case 'college':
                                            $category_name = t('college_permissions');
                                            break;
                                        case 'department':
                                            $category_name = t('department_permissions');
                                            break;
                                        case 'teacher':
                                            $category_name = t('teacher_permissions');
                                            break;
                                        case 'student':
                                            $category_name = t('student_permissions');
                                            break;
                                        default:
                                            $category_name = $category;
                                    }
                                ?>
                                    <div class="permission-category">
                                        <div class="permission-category-title"><?php echo $category_name; ?></div>
                                        <?php foreach ($permissions as $permission): ?>
                                            <div class="permission-item">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" id="edit_permission_<?php echo $permission['id']; ?>">
                                                    <label class="form-check-label" for="edit_permission_<?php echo $permission['id']; ?>">
                                                        <?php echo $permission['name']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
    
    <!-- مودال حذف دور -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRoleModalLabel"><?php echo t('delete_role'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_role">
                    <input type="hidden" name="role_id" id="delete_role_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_role'); ?>: <strong id="delete_role_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_role_warning'); ?>
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
        // مصفوفة وهمية للصلاحيات (يجب استبدالها بـ API call)
        const allPermissionsData = <?php echo json_encode($all_permissions); ?>;
        
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
            });
            
            // زر التعديل في مودال العرض
            document.getElementById('viewRoleEditBtn').addEventListener('click', function() {
                $('#viewRoleModal').modal('hide');
                $('#editRoleModal').modal('show');
            });
            
            // تقديم نموذج البحث عند الكتابة
            document.querySelector('input[name="search"]').addEventListener('input', function() {
                if (this.value.length >= 3 || this.value.length === 0) {
                    document.getElementById('searchForm').submit();
                }
            });
        });
        
        // دالة تحضير مودال عرض الدور
        function prepareViewRole(role) {
            document.getElementById('view_role_name').textContent = role.name;
            document.getElementById('view_role_description').textContent = role.description || '-';
            document.getElementById('view_role_users_count').textContent = role.users_count;
            
            // عرض نوع الدور
            const roleTypeElement = document.getElementById('view_role_type');
            if (role.is_system) {
                roleTypeElement.innerHTML = '<span class="badge bg-success"><?php echo t("system_role"); ?></span>';
            } else {
                roleTypeElement.innerHTML = '<span class="badge bg-secondary"><?php echo t("custom_role"); ?></span>';
            }
            
            // عرض الصلاحيات
            const permissionsContainer = document.getElementById('view_role_permissions');
            permissionsContainer.innerHTML = '';
            
            if (role.permissions && role.permissions.length > 0) {
                // تجميع الصلاحيات حسب الفئة
                const permissionsByCategory = {};
                role.permissions.forEach(permId => {
                    const permission = allPermissionsData.find(p => p.id === permId);
                    if (permission) {
                        const category = permission.category;
                        if (!permissionsByCategory[category]) {
                            permissionsByCategory[category] = [];
                        }
                        permissionsByCategory[category].push(permission);
                    }
                });
                
                // إنشاء عناصر HTML للصلاحيات
                for (const category in permissionsByCategory) {
                    const categoryDiv = document.createElement('div');
                    categoryDiv.className = 'mb-3';
                    
                    let categoryName = '';
                    switch (category) {
                        case 'admin':
                            categoryName = '<?php echo t("admin_permissions"); ?>';
                            break;
                        case 'college':
                            categoryName = '<?php echo t("college_permissions"); ?>';
                            break;
                        case 'department':
                            categoryName = '<?php echo t("department_permissions"); ?>';
                            break;
                        case 'teacher':
                            categoryName = '<?php echo t("teacher_permissions"); ?>';
                            break;
                        case 'student':
                            categoryName = '<?php echo t("student_permissions"); ?>';
                            break;
                        default:
                            categoryName = category;
                    }
                    
                    categoryDiv.innerHTML = `<h6 class="text-primary">${categoryName}</h6>`;
                    
                    const badgesDiv = document.createElement('div');
                    permissionsByCategory[category].forEach(permission => {
                        const badge = document.createElement('span');
                        badge.className = 'role-badge';
                        badge.textContent = permission.name;
                        badgesDiv.appendChild(badge);
                    });
                    
                    categoryDiv.appendChild(badgesDiv);
                    permissionsContainer.appendChild(categoryDiv);
                }
            } else {
                permissionsContainer.innerHTML = '<p><?php echo t("no_permissions_assigned"); ?></p>';
            }
            
            // تحضير بيانات التعديل أيضاً
            prepareEditRole(role);
        }
        
        // دالة تحضير مودال تعديل الدور
        function prepareEditRole(role) {
            document.getElementById('edit_role_id').value = role.id;
            document.getElementById('edit_role_name').value = role.name;
            document.getElementById('edit_role_description').value = role.description || '';
            
            // إعادة تعيين جميع خانات الاختيار
            const checkboxes = document.querySelectorAll('#editRoleModal input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // تحديد الصلاحيات المختارة
            if (role.permissions && role.permissions.length > 0) {
                role.permissions.forEach(permId => {
                    const checkbox = document.getElementById(`edit_permission_${permId}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            // تعطيل حقل الاسم للأدوار النظامية
            const nameInput = document.getElementById('edit_role_name');
            if (role.is_system) {
                nameInput.readOnly = true;
                nameInput.classList.add('bg-light');
            } else {
                nameInput.readOnly = false;
                nameInput.classList.remove('bg-light');
            }
        }
        
        // دالة تحضير مودال حذف الدور
        function prepareDeleteRole(roleId, roleName) {
            document.getElementById('delete_role_id').value = roleId;
            document.getElementById('delete_role_name').textContent = roleName;
        }
        
    </script>
</body>
</html>
