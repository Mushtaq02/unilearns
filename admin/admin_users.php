<?php
/**
 * صفحة إدارة المستخدمين في نظام UniverBoard
 * تتيح للمشرف إدارة جميع المستخدمين في النظام
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

// تحديد نوع المستخدم للتصفية
$user_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// تحديد حالة المستخدم للتصفية
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// تحديد كلمة البحث
$search = isset($_GET['search']) ? $_GET['search'] : '';

// الحصول على قائمة المستخدمين
$users = get_users($db, $page, $items_per_page, $user_type, $status, $search);
$total_users = get_total_users($db, $user_type, $status, $search);
$total_pages = ceil($total_users / $items_per_page);

// معالجة إضافة مستخدم جديد
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $user_type = filter_input(INPUT_POST, 'user_type', FILTER_SANITIZE_STRING);
    $college_id = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($name) || empty($email) || empty($password) || empty($user_type)) {
        $add_error = t('all_fields_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_error = t('invalid_email');
    } else {
        // التحقق من عدم وجود البريد الإلكتروني مسبقاً
        if (email_exists($db, $email)) {
            $add_error = t('email_already_exists');
        } else {
            // إضافة المستخدم الجديد
            $result = add_user($db, $name, $email, $password, $user_type, $college_id);
            
            if ($result) {
                $add_success = true;
                // تحديث قائمة المستخدمين
                $users = get_users($db, $page, $items_per_page, $user_type, $status, $search);
                $total_users = get_total_users($db, $user_type, $status, $search);
                $total_pages = ceil($total_users / $items_per_page);
            } else {
                $add_error = t('add_user_failed');
            }
        }
    }
}

// معالجة تعديل مستخدم
$edit_success = false;
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $user_type = filter_input(INPUT_POST, 'user_type', FILTER_SANITIZE_STRING);
    $college_id = filter_input(INPUT_POST, 'college_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($user_id) || empty($name) || empty($email) || empty($user_type) || empty($status)) {
        $edit_error = t('all_fields_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $edit_error = t('invalid_email');
    } else {
        // التحقق من عدم وجود البريد الإلكتروني مسبقاً (لمستخدم آخر)
        if (email_exists_for_other_user($db, $email, $user_id)) {
            $edit_error = t('email_already_exists');
        } else {
            // تعديل المستخدم
            $result = update_user($db, $user_id, $name, $email, $user_type, $college_id, $status);
            
            if ($result) {
                $edit_success = true;
                // تحديث قائمة المستخدمين
                $users = get_users($db, $page, $items_per_page, $user_type, $status, $search);
                $total_users = get_total_users($db, $user_type, $status, $search);
                $total_pages = ceil($total_users / $items_per_page);
            } else {
                $edit_error = t('edit_user_failed');
            }
        }
    }
}

// معالجة تغيير كلمة المرور
$password_success = false;
$password_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    
    // التحقق من البيانات
    if (empty($user_id) || empty($new_password)) {
        $password_error = t('all_fields_required');
    } elseif (strlen($new_password) < 6) {
        $password_error = t('password_too_short');
    } else {
        // تغيير كلمة المرور
        $result = change_user_password($db, $user_id, $new_password);
        
        if ($result) {
            $password_success = true;
        } else {
            $password_error = t('change_password_failed');
        }
    }
}

// معالجة حذف مستخدم
$delete_success = false;
$delete_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    
    // التحقق من البيانات
    if (empty($user_id)) {
        $delete_error = t('user_id_required');
    } else {
        // حذف المستخدم
        $result = delete_user($db, $user_id);
        
        if ($result) {
            $delete_success = true;
            // تحديث قائمة المستخدمين
            $users = get_users($db, $page, $items_per_page, $user_type, $status, $search);
            $total_users = get_total_users($db, $user_type, $status, $search);
            $total_pages = ceil($total_users / $items_per_page);
        } else {
            $delete_error = t('delete_user_failed');
        }
    }
}

// الحصول على قائمة الكليات
$colleges = get_colleges($db);

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

function get_users($db, $page, $items_per_page, $user_type, $status, $search) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع المستخدمين من قاعدة البيانات مع تطبيق التصفية والبحث
    $users = [
        [
            'id' => 1,
            'name' => 'أحمد محمد',
            'email' => 'admin@univerboard.com',
            'user_type' => 'admin',
            'college_id' => null,
            'college_name' => null,
            'status' => 'active',
            'created_at' => '2025-01-01 10:00:00',
            'last_login' => '2025-05-20 14:30:45'
        ],
        [
            'id' => 2,
            'name' => 'د. خالد العمري',
            'email' => 'khalid@univerboard.com',
            'user_type' => 'teacher',
            'college_id' => 1,
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'status' => 'active',
            'created_at' => '2025-01-02 11:30:00',
            'last_login' => '2025-05-20 10:45:12'
        ],
        [
            'id' => 3,
            'name' => 'سارة أحمد',
            'email' => 'sara@univerboard.com',
            'user_type' => 'student',
            'college_id' => 1,
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'status' => 'active',
            'created_at' => '2025-01-03 09:15:00',
            'last_login' => '2025-05-20 10:30:22'
        ],
        [
            'id' => 4,
            'name' => 'د. محمد علي',
            'email' => 'mohammed@univerboard.com',
            'user_type' => 'college_admin',
            'college_id' => 1,
            'college_name' => 'كلية علوم الحاسب والمعلومات',
            'status' => 'active',
            'created_at' => '2025-01-04 14:20:00',
            'last_login' => '2025-05-20 09:50:18'
        ],
        [
            'id' => 5,
            'name' => 'عمر خالد',
            'email' => 'omar@univerboard.com',
            'user_type' => 'student',
            'college_id' => 2,
            'college_name' => 'كلية الهندسة',
            'status' => 'active',
            'created_at' => '2025-01-05 16:45:00',
            'last_login' => '2025-05-20 09:30:05'
        ],
        [
            'id' => 6,
            'name' => 'نورة سعيد',
            'email' => 'noura@univerboard.com',
            'user_type' => 'student',
            'college_id' => 3,
            'college_name' => 'كلية العلوم',
            'status' => 'inactive',
            'created_at' => '2025-01-06 13:10:00',
            'last_login' => '2025-05-15 11:20:30'
        ],
        [
            'id' => 7,
            'name' => 'د. فاطمة محمد',
            'email' => 'fatima@univerboard.com',
            'user_type' => 'teacher',
            'college_id' => 3,
            'college_name' => 'كلية العلوم',
            'status' => 'active',
            'created_at' => '2025-01-07 10:30:00',
            'last_login' => '2025-05-19 15:40:22'
        ],
        [
            'id' => 8,
            'name' => 'سلطان عبدالله',
            'email' => 'sultan@univerboard.com',
            'user_type' => 'student',
            'college_id' => 2,
            'college_name' => 'كلية الهندسة',
            'status' => 'pending',
            'created_at' => '2025-05-18 09:20:00',
            'last_login' => null
        ],
        [
            'id' => 9,
            'name' => 'د. عبدالرحمن سعد',
            'email' => 'abdulrahman@univerboard.com',
            'user_type' => 'college_admin',
            'college_id' => 2,
            'college_name' => 'كلية الهندسة',
            'status' => 'active',
            'created_at' => '2025-01-08 08:45:00',
            'last_login' => '2025-05-20 08:15:10'
        ],
        [
            'id' => 10,
            'name' => 'منى علي',
            'email' => 'mona@univerboard.com',
            'user_type' => 'teacher',
            'college_id' => 4,
            'college_name' => 'كلية الطب',
            'status' => 'active',
            'created_at' => '2025-01-09 11:50:00',
            'last_login' => '2025-05-19 14:25:40'
        ]
    ];
    
    // تطبيق التصفية حسب نوع المستخدم
    if ($user_type !== 'all') {
        $users = array_filter($users, function($user) use ($user_type) {
            return $user['user_type'] === $user_type;
        });
    }
    
    // تطبيق التصفية حسب الحالة
    if ($status !== 'all') {
        $users = array_filter($users, function($user) use ($status) {
            return $user['status'] === $status;
        });
    }
    
    // تطبيق البحث
    if (!empty($search)) {
        $users = array_filter($users, function($user) use ($search) {
            return stripos($user['name'], $search) !== false || 
                   stripos($user['email'], $search) !== false;
        });
    }
    
    // ترتيب المستخدمين حسب تاريخ الإنشاء (من الأحدث إلى الأقدم)
    usort($users, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $users = array_slice($users, $offset, $items_per_page);
    
    return $users;
}

function get_total_users($db, $user_type, $status, $search) {
    // في الواقع، يجب استرجاع عدد المستخدمين من قاعدة البيانات مع تطبيق التصفية والبحث
    $total = 50;
    
    // تقليل العدد حسب التصفية
    if ($user_type !== 'all') {
        $total = $total * 0.4; // تقريباً 40% من المستخدمين لكل نوع
    }
    
    if ($status !== 'all') {
        if ($status === 'active') {
            $total = $total * 0.8; // تقريباً 80% من المستخدمين نشطين
        } elseif ($status === 'inactive') {
            $total = $total * 0.15; // تقريباً 15% من المستخدمين غير نشطين
        } else {
            $total = $total * 0.05; // تقريباً 5% من المستخدمين في حالة انتظار
        }
    }
    
    // تقليل العدد حسب البحث
    if (!empty($search)) {
        $total = $total * 0.2; // تقريباً 20% من المستخدمين يطابقون البحث
    }
    
    return ceil($total);
}

function get_colleges($db) {
    // في الواقع، يجب استرجاع الكليات من قاعدة البيانات
    return [
        [
            'id' => 1,
            'name' => 'كلية علوم الحاسب والمعلومات'
        ],
        [
            'id' => 2,
            'name' => 'كلية الهندسة'
        ],
        [
            'id' => 3,
            'name' => 'كلية العلوم'
        ],
        [
            'id' => 4,
            'name' => 'كلية الطب'
        ],
        [
            'id' => 5,
            'name' => 'كلية إدارة الأعمال'
        ]
    ];
}

function email_exists($db, $email) {
    // في الواقع، يجب التحقق من وجود البريد الإلكتروني في قاعدة البيانات
    return false;
}

function email_exists_for_other_user($db, $email, $user_id) {
    // في الواقع، يجب التحقق من وجود البريد الإلكتروني لمستخدم آخر في قاعدة البيانات
    return false;
}

function add_user($db, $name, $email, $password, $user_type, $college_id) {
    // في الواقع، يجب إضافة المستخدم إلى قاعدة البيانات
    return true;
}

function update_user($db, $user_id, $name, $email, $user_type, $college_id, $status) {
    // في الواقع، يجب تحديث معلومات المستخدم في قاعدة البيانات
    return true;
}

function change_user_password($db, $user_id, $new_password) {
    // في الواقع، يجب تحديث كلمة مرور المستخدم في قاعدة البيانات
    return true;
}

function delete_user($db, $user_id) {
    // في الواقع، يجب حذف المستخدم من قاعدة البيانات
    return true;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('user_management'); ?></title>
    
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
        
        /* تنسيقات البحث والتصفية */
        .filter-row {
            margin-bottom: 1.5rem;
        }
        
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
        
        /* تنسيقات حالة المستخدم */
        .status-active {
            color: #198754;
        }
        
        .status-inactive {
            color: #dc3545;
        }
        
        .status-pending {
            color: #ffc107;
        }
        
        /* تنسيقات نوع المستخدم */
        .user-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .user-type-admin {
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        .user-type-college_admin {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .user-type-teacher {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .user-type-student {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
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
                    <a class="nav-link active" href="admin_users.php">
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
            <h1 class="page-title"><?php echo t('user_management'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_all_users_in_the_system'); ?></p>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($add_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('user_added_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($edit_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('user_updated_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($password_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('password_changed_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('user_deleted_successfully'); ?>
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
        
        <?php if (!empty($password_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $password_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($delete_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $delete_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- بطاقة المستخدمين -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-users me-2"></i> <?php echo t('users_list'); ?></h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i> <?php echo t('add_user'); ?>
                </button>
            </div>
            <div class="card-body">
                <!-- البحث والتصفية -->
                <div class="row filter-row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <form action="" method="get">
                                <input type="hidden" name="type" value="<?php echo $user_type; ?>">
                                <input type="hidden" name="status" value="<?php echo $status; ?>">
                                <input type="text" class="form-control" name="search" placeholder="<?php echo t('search_users'); ?>" value="<?php echo $search; ?>">
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-md-end">
                            <div class="me-3">
                                <select class="form-select" id="userTypeFilter" onchange="filterUsers()">
                                    <option value="all" <?php echo $user_type === 'all' ? 'selected' : ''; ?>><?php echo t('all_user_types'); ?></option>
                                    <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>><?php echo t('admins'); ?></option>
                                    <option value="college_admin" <?php echo $user_type === 'college_admin' ? 'selected' : ''; ?>><?php echo t('college_admins'); ?></option>
                                    <option value="teacher" <?php echo $user_type === 'teacher' ? 'selected' : ''; ?>><?php echo t('teachers'); ?></option>
                                    <option value="student" <?php echo $user_type === 'student' ? 'selected' : ''; ?>><?php echo t('students'); ?></option>
                                </select>
                            </div>
                            <div>
                                <select class="form-select" id="statusFilter" onchange="filterUsers()">
                                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>><?php echo t('all_statuses'); ?></option>
                                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>><?php echo t('active'); ?></option>
                                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>><?php echo t('inactive'); ?></option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>><?php echo t('pending'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- جدول المستخدمين -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('name'); ?></th>
                                <th><?php echo t('email'); ?></th>
                                <th><?php echo t('user_type'); ?></th>
                                <th><?php echo t('college'); ?></th>
                                <th><?php echo t('status'); ?></th>
                                <th><?php echo t('last_login'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center"><?php echo t('no_users_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <span class="user-type user-type-<?php echo $user['user_type']; ?>">
                                                <?php 
                                                    if ($user['user_type'] === 'admin') echo t('admin');
                                                    elseif ($user['user_type'] === 'college_admin') echo t('college_admin');
                                                    elseif ($user['user_type'] === 'teacher') echo t('teacher');
                                                    elseif ($user['user_type'] === 'student') echo t('student');
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['college_name'] ?: '-'; ?></td>
                                        <td>
                                            <span class="status-<?php echo $user['status']; ?>">
                                                <i class="fas <?php 
                                                    if ($user['status'] === 'active') echo 'fa-check-circle';
                                                    elseif ($user['status'] === 'inactive') echo 'fa-times-circle';
                                                    elseif ($user['status'] === 'pending') echo 'fa-clock';
                                                ?>"></i>
                                                <?php 
                                                    if ($user['status'] === 'active') echo t('active');
                                                    elseif ($user['status'] === 'inactive') echo t('inactive');
                                                    elseif ($user['status'] === 'pending') echo t('pending');
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['last_login'] ?: t('never'); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="prepareEditUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal" onclick="prepareChangePassword(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>')">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" onclick="prepareDeleteUser(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>')">
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
                            <?php echo t('showing'); ?> <?php echo count($users); ?> <?php echo t('of'); ?> <?php echo $total_users; ?> <?php echo t('users'); ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $user_type; ?>&status=<?php echo $status; ?>&search=<?php echo $search; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $user_type; ?>&status=<?php echo $status; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $user_type; ?>&status=<?php echo $status; ?>&search=<?php echo $search; ?>" aria-label="Next">
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
    
    <!-- مودال إضافة مستخدم -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel"><?php echo t('add_new_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label"><?php echo t('name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo t('email'); ?> <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo t('password'); ?> <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text"><?php echo t('password_requirements'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="user_type" class="form-label"><?php echo t('user_type'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_type" name="user_type" required onchange="toggleCollegeField()">
                                <option value="admin"><?php echo t('admin'); ?></option>
                                <option value="college_admin"><?php echo t('college_admin'); ?></option>
                                <option value="teacher"><?php echo t('teacher'); ?></option>
                                <option value="student"><?php echo t('student'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3" id="college_field" style="display: none;">
                            <label for="college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="college_id" name="college_id">
                                <option value=""><?php echo t('select_college'); ?></option>
                                <?php foreach ($colleges as $college): ?>
                                    <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('add_user'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل مستخدم -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel"><?php echo t('edit_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label"><?php echo t('name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label"><?php echo t('email'); ?> <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_user_type" class="form-label"><?php echo t('user_type'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_user_type" name="user_type" required onchange="toggleEditCollegeField()">
                                <option value="admin"><?php echo t('admin'); ?></option>
                                <option value="college_admin"><?php echo t('college_admin'); ?></option>
                                <option value="teacher"><?php echo t('teacher'); ?></option>
                                <option value="student"><?php echo t('student'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3" id="edit_college_field" style="display: none;">
                            <label for="edit_college_id" class="form-label"><?php echo t('college'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_college_id" name="college_id">
                                <option value=""><?php echo t('select_college'); ?></option>
                                <?php foreach ($colleges as $college): ?>
                                    <option value="<?php echo $college['id']; ?>"><?php echo $college['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label"><?php echo t('status'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active"><?php echo t('active'); ?></option>
                                <option value="inactive"><?php echo t('inactive'); ?></option>
                                <option value="pending"><?php echo t('pending'); ?></option>
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
    
    <!-- مودال تغيير كلمة المرور -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel"><?php echo t('change_password'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" id="password_user_id">
                    <div class="modal-body">
                        <p><?php echo t('changing_password_for'); ?>: <strong id="password_user_name"></strong></p>
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><?php echo t('new_password'); ?> <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text"><?php echo t('password_requirements'); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('change_password'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف مستخدم -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel"><?php echo t('delete_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <div class="modal-body">
                        <p><?php echo t('confirm_delete_user'); ?>: <strong id="delete_user_name"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_user_warning'); ?>
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
            
            // إظهار/إخفاء حقل الكلية عند إضافة مستخدم جديد
            toggleCollegeField();
        });
        
        // دالة تصفية المستخدمين
        function filterUsers() {
            const userType = document.getElementById('userTypeFilter').value;
            const status = document.getElementById('statusFilter').value;
            const searchParams = new URLSearchParams(window.location.search);
            const search = searchParams.get('search') || '';
            
            window.location.href = `?page=1&type=${userType}&status=${status}&search=${search}`;
        }
        
        // دالة إظهار/إخفاء حقل الكلية عند إضافة مستخدم جديد
        function toggleCollegeField() {
            const userType = document.getElementById('user_type').value;
            const collegeField = document.getElementById('college_field');
            
            if (userType === 'admin') {
                collegeField.style.display = 'none';
                document.getElementById('college_id').removeAttribute('required');
            } else {
                collegeField.style.display = 'block';
                document.getElementById('college_id').setAttribute('required', 'required');
            }
        }
        
        // دالة إظهار/إخفاء حقل الكلية عند تعديل مستخدم
        function toggleEditCollegeField() {
            const userType = document.getElementById('edit_user_type').value;
            const collegeField = document.getElementById('edit_college_field');
            
            if (userType === 'admin') {
                collegeField.style.display = 'none';
                document.getElementById('edit_college_id').removeAttribute('required');
            } else {
                collegeField.style.display = 'block';
                document.getElementById('edit_college_id').setAttribute('required', 'required');
            }
        }
        
        // دالة تحضير نموذج تعديل المستخدم
        function prepareEditUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_user_type').value = user.user_type;
            document.getElementById('edit_status').value = user.status;
            
            if (user.college_id) {
                document.getElementById('edit_college_id').value = user.college_id;
            } else {
                document.getElementById('edit_college_id').value = '';
            }
            
            toggleEditCollegeField();
        }
        
        // دالة تحضير نموذج تغيير كلمة المرور
        function prepareChangePassword(userId, userName) {
            document.getElementById('password_user_id').value = userId;
            document.getElementById('password_user_name').textContent = userName;
        }
        
        // دالة تحضير نموذج حذف المستخدم
        function prepareDeleteUser(userId, userName) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_user_name').textContent = userName;
        }
    </script>
</body>
</html>
