<?php
/**
 * صفحة إدارة السجلات في نظام UniverBoard
 * تتيح للمشرف عرض وتصفية سجلات النظام المختلفة
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

// تحديد نوع السجلات المطلوبة
$log_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// تحديد عدد العناصر في الصفحة
$items_per_page = 20;

// تحديد رقم الصفحة الحالية
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// تحديد نطاق التاريخ
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'today';

// تحديد مستوى الأهمية
$severity = isset($_GET['severity']) ? $_GET['severity'] : 'all';

// تحديد كلمة البحث
$search = isset($_GET['search']) ? $_GET['search'] : '';

// تحديد المستخدم
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// الحصول على قائمة السجلات
$logs = get_logs($db, $log_type, $page, $items_per_page, $date_range, $severity, $search, $user_id);
$total_logs = get_total_logs($db, $log_type, $date_range, $severity, $search, $user_id);
$total_pages = ceil($total_logs / $items_per_page);

// الحصول على إحصائيات السجلات
$logs_stats = get_logs_stats($db);

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

function get_logs($db, $log_type, $page, $items_per_page, $date_range, $severity, $search, $user_id) {
    $offset = ($page - 1) * $items_per_page;
    
    // في الواقع، يجب استرجاع السجلات من قاعدة البيانات مع تطبيق المرشحات
    $logs = [
        [
            'id' => 1,
            'type' => 'login',
            'message' => 'تسجيل دخول ناجح',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 10:30:45'
        ],
        [
            'id' => 2,
            'type' => 'login',
            'message' => 'محاولة تسجيل دخول فاشلة',
            'user_id' => null,
            'user_name' => null,
            'user_type' => null,
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'warning',
            'created_at' => '2025-05-21 10:35:12'
        ],
        [
            'id' => 3,
            'type' => 'user',
            'message' => 'تم إنشاء مستخدم جديد',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 11:15:30'
        ],
        [
            'id' => 4,
            'type' => 'user',
            'message' => 'تم تحديث بيانات المستخدم',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 11:20:15'
        ],
        [
            'id' => 5,
            'type' => 'system',
            'message' => 'تم تحديث إعدادات النظام',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 11:45:22'
        ],
        [
            'id' => 6,
            'type' => 'system',
            'message' => 'خطأ في النظام: فشل الاتصال بقاعدة البيانات',
            'user_id' => null,
            'user_name' => null,
            'user_type' => null,
            'ip_address' => null,
            'user_agent' => null,
            'severity' => 'error',
            'created_at' => '2025-05-21 12:10:05'
        ],
        [
            'id' => 7,
            'type' => 'login',
            'message' => 'تسجيل دخول ناجح',
            'user_id' => 2,
            'user_name' => 'محمد علي',
            'user_type' => 'teacher',
            'ip_address' => '192.168.1.3',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 13:05:30'
        ],
        [
            'id' => 8,
            'type' => 'course',
            'message' => 'تم إنشاء مقرر دراسي جديد',
            'user_id' => 2,
            'user_name' => 'محمد علي',
            'user_type' => 'teacher',
            'ip_address' => '192.168.1.3',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 13:20:45'
        ],
        [
            'id' => 9,
            'type' => 'login',
            'message' => 'تسجيل دخول ناجح',
            'user_id' => 3,
            'user_name' => 'فاطمة أحمد',
            'user_type' => 'student',
            'ip_address' => '192.168.1.4',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
            'severity' => 'info',
            'created_at' => '2025-05-21 14:10:15'
        ],
        [
            'id' => 10,
            'type' => 'assignment',
            'message' => 'تم تسليم واجب',
            'user_id' => 3,
            'user_name' => 'فاطمة أحمد',
            'user_type' => 'student',
            'ip_address' => '192.168.1.4',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
            'severity' => 'info',
            'created_at' => '2025-05-21 14:30:20'
        ],
        [
            'id' => 11,
            'type' => 'security',
            'message' => 'محاولة وصول غير مصرح به',
            'user_id' => null,
            'user_name' => null,
            'user_type' => null,
            'ip_address' => '203.0.113.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'critical',
            'created_at' => '2025-05-21 15:45:10'
        ],
        [
            'id' => 12,
            'type' => 'backup',
            'message' => 'تم إنشاء نسخة احتياطية بنجاح',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 16:00:00'
        ],
        [
            'id' => 13,
            'type' => 'system',
            'message' => 'تم تحديث النظام إلى الإصدار 2.0.1',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 16:30:45'
        ],
        [
            'id' => 14,
            'type' => 'login',
            'message' => 'تسجيل خروج',
            'user_id' => 3,
            'user_name' => 'فاطمة أحمد',
            'user_type' => 'student',
            'ip_address' => '192.168.1.4',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
            'severity' => 'info',
            'created_at' => '2025-05-21 17:15:30'
        ],
        [
            'id' => 15,
            'type' => 'login',
            'message' => 'تسجيل خروج',
            'user_id' => 2,
            'user_name' => 'محمد علي',
            'user_type' => 'teacher',
            'ip_address' => '192.168.1.3',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 17:30:10'
        ],
        [
            'id' => 16,
            'type' => 'login',
            'message' => 'تسجيل خروج',
            'user_id' => 1,
            'user_name' => 'أحمد محمد',
            'user_type' => 'admin',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 18:00:00'
        ],
        [
            'id' => 17,
            'type' => 'security',
            'message' => 'تم تغيير كلمة المرور',
            'user_id' => 2,
            'user_name' => 'محمد علي',
            'user_type' => 'teacher',
            'ip_address' => '192.168.1.3',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'severity' => 'info',
            'created_at' => '2025-05-21 19:15:30'
        ],
        [
            'id' => 18,
            'type' => 'system',
            'message' => 'تم تشغيل المهمة المجدولة: تنظيف الملفات المؤقتة',
            'user_id' => null,
            'user_name' => null,
            'user_type' => null,
            'ip_address' => null,
            'user_agent' => null,
            'severity' => 'info',
            'created_at' => '2025-05-21 20:00:00'
        ],
        [
            'id' => 19,
            'type' => 'system',
            'message' => 'تم تشغيل المهمة المجدولة: إرسال إشعارات المواعيد النهائية',
            'user_id' => null,
            'user_name' => null,
            'user_type' => null,
            'ip_address' => null,
            'user_agent' => null,
            'severity' => 'info',
            'created_at' => '2025-05-21 20:15:00'
        ],
        [
            'id' => 20,
            'type' => 'system',
            'message' => 'تم تشغيل المهمة المجدولة: تحديث إحصائيات النظام',
            'user_id' => null,
            'user_name' => null,
            'user_type' => null,
            'ip_address' => null,
            'user_agent' => null,
            'severity' => 'info',
            'created_at' => '2025-05-21 20:30:00'
        ]
    ];
    
    // تطبيق مرشح نوع السجل
    if ($log_type !== 'all') {
        $logs = array_filter($logs, function($log) use ($log_type) {
            return $log['type'] === $log_type;
        });
    }
    
    // تطبيق مرشح نطاق التاريخ
    $logs = array_filter($logs, function($log) use ($date_range) {
        $log_date = strtotime($log['created_at']);
        $now = time();
        
        switch ($date_range) {
            case 'today':
                return date('Y-m-d', $log_date) === date('Y-m-d', $now);
            case 'yesterday':
                return date('Y-m-d', $log_date) === date('Y-m-d', strtotime('-1 day', $now));
            case 'this_week':
                return date('W Y', $log_date) === date('W Y', $now);
            case 'last_week':
                return date('W Y', $log_date) === date('W Y', strtotime('-1 week', $now));
            case 'this_month':
                return date('m Y', $log_date) === date('m Y', $now);
            case 'last_month':
                return date('m Y', $log_date) === date('m Y', strtotime('-1 month', $now));
            case 'custom':
                // يمكن تنفيذ نطاق تاريخ مخصص هنا
                return true;
            default:
                return true;
        }
    });
    
    // تطبيق مرشح مستوى الأهمية
    if ($severity !== 'all') {
        $logs = array_filter($logs, function($log) use ($severity) {
            return $log['severity'] === $severity;
        });
    }
    
    // تطبيق مرشح البحث
    if (!empty($search)) {
        $logs = array_filter($logs, function($log) use ($search) {
            return stripos($log['message'], $search) !== false ||
                   (isset($log['user_name']) && stripos($log['user_name'], $search) !== false) ||
                   (isset($log['ip_address']) && stripos($log['ip_address'], $search) !== false);
        });
    }
    
    // تطبيق مرشح المستخدم
    if ($user_id > 0) {
        $logs = array_filter($logs, function($log) use ($user_id) {
            return isset($log['user_id']) && $log['user_id'] === $user_id;
        });
    }
    
    // ترتيب السجلات حسب التاريخ (من الأحدث إلى الأقدم)
    usort($logs, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // تطبيق الصفحات
    $logs = array_slice($logs, $offset, $items_per_page);
    
    return $logs;
}

function get_total_logs($db, $log_type, $date_range, $severity, $search, $user_id) {
    // في الواقع، يجب استرجاع عدد السجلات من قاعدة البيانات مع تطبيق المرشحات
    $total = 20;
    
    // تقليل العدد حسب المرشحات
    if ($log_type !== 'all') {
        $total = $total * 0.5; // تقريباً 50% من السجلات تطابق نوع السجل
    }
    
    if ($date_range !== 'all') {
        $total = $total * 0.8; // تقريباً 80% من السجلات تطابق نطاق التاريخ
    }
    
    if ($severity !== 'all') {
        $total = $total * 0.3; // تقريباً 30% من السجلات تطابق مستوى الأهمية
    }
    
    if (!empty($search)) {
        $total = $total * 0.2; // تقريباً 20% من السجلات تطابق البحث
    }
    
    if ($user_id > 0) {
        $total = $total * 0.4; // تقريباً 40% من السجلات تطابق المستخدم
    }
    
    return ceil($total);
}

function get_logs_stats($db) {
    // في الواقع، يجب استرجاع إحصائيات السجلات من قاعدة البيانات
    return [
        'total' => 1250,
        'today' => 120,
        'this_week' => 450,
        'this_month' => 1250,
        'by_type' => [
            'login' => 500,
            'user' => 200,
            'system' => 300,
            'course' => 100,
            'assignment' => 80,
            'security' => 50,
            'backup' => 20
        ],
        'by_severity' => [
            'info' => 900,
            'warning' => 250,
            'error' => 80,
            'critical' => 20
        ]
    ];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('system_logs'); ?></title>
    
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
        
        /* تنسيقات خاصة بالسجلات */
        .log-type-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            min-width: 80px;
        }
        
        .log-type-login {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }
        
        .theme-dark .log-type-login {
            background-color: rgba(13, 202, 240, 0.2);
        }
        
        .log-type-user {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .theme-dark .log-type-user {
            background-color: rgba(102, 155, 188, 0.2);
        }
        
        .log-type-system {
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
        }
        
        .theme-dark .log-type-system {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .log-type-course {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .theme-dark .log-type-course {
            background-color: rgba(25, 135, 84, 0.2);
        }
        
        .log-type-assignment {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .theme-dark .log-type-assignment {
            background-color: rgba(108, 117, 125, 0.2);
        }
        
        .log-type-security {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .theme-dark .log-type-security {
            background-color: rgba(220, 53, 69, 0.2);
        }
        
        .log-type-backup {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .theme-dark .log-type-backup {
            background-color: rgba(255, 193, 7, 0.2);
        }
        
        .log-severity {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .log-severity {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .log-severity-info {
            background-color: #0dcaf0;
        }
        
        .log-severity-warning {
            background-color: #ffc107;
        }
        
        .log-severity-error {
            background-color: #dc3545;
        }
        
        .log-severity-critical {
            background-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.5);
        }
        
        .log-user {
            display: flex;
            align-items: center;
        }
        
        .log-user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .log-user-avatar {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .log-user-info {
            display: flex;
            flex-direction: column;
        }
        
        .log-user-name {
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .log-user-type {
            font-size: 0.75rem;
            color: var(--gray-color);
        }
        
        .log-details-toggle {
            cursor: pointer;
            color: var(--primary-color);
        }
        
        .log-details {
            display: none;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        
        .theme-dark .log-details {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .log-details-row {
            display: flex;
            margin-bottom: 0.25rem;
        }
        
        .log-details-row:last-child {
            margin-bottom: 0;
        }
        
        .log-details-label {
            font-weight: 500;
            min-width: 100px;
        }
        
        .log-details-value {
            flex: 1;
        }
        
        /* تنسيقات البطاقات الإحصائية */
        .stats-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .stats-card-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .theme-dark .stats-card-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .stats-card-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }
        
        .stats-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(0, 48, 73, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .theme-dark .stats-card-icon {
            background-color: rgba(0, 48, 73, 0.2);
        }
        
        .stats-card-body {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .stats-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card-label {
            font-size: 0.875rem;
            color: var(--gray-color);
        }
        
        .stats-card-footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
        }
        
        .theme-dark .stats-card-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .stats-card-footer a {
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stats-card-footer a i {
            margin-left: 0.25rem;
        }
        
        [dir="rtl"] .stats-card-footer a i {
            margin-left: 0;
            margin-right: 0.25rem;
            transform: rotate(180deg);
        }
        
        /* تنسيقات المخطط البياني */
        .chart-container {
            position: relative;
            height: 300px;
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
                    <a class="nav-link active" href="admin_logs.php">
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
            <h1 class="page-title"><?php echo t('system_logs'); ?></h1>
            <p class="page-subtitle"><?php echo t('view_and_analyze_system_logs'); ?></p>
        </div>
        
        <!-- البطاقات الإحصائية -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('total_logs'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo number_format($logs_stats['total']); ?></div>
                        <div class="stats-card-label"><?php echo t('all_time'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('today_logs'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo number_format($logs_stats['today']); ?></div>
                        <div class="stats-card-label"><?php echo t('today'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('critical_logs'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo number_format($logs_stats['by_severity']['critical']); ?></div>
                        <div class="stats-card-label"><?php echo t('critical_events'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card">
                    <div class="stats-card-header">
                        <h5 class="stats-card-title"><?php echo t('security_logs'); ?></h5>
                        <div class="stats-card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="stats-card-body">
                        <div class="stats-card-value"><?php echo number_format($logs_stats['by_type']['security']); ?></div>
                        <div class="stats-card-label"><?php echo t('security_events'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- أدوات التصفية والبحث -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-filter me-2"></i> <?php echo t('filter_logs'); ?></h5>
            </div>
            <div class="card-body">
                <form action="" method="get" id="filterForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="log_type" class="form-label"><?php echo t('log_type'); ?></label>
                            <select class="form-select" id="log_type" name="type">
                                <option value="all" <?php echo $log_type === 'all' ? 'selected' : ''; ?>><?php echo t('all_types'); ?></option>
                                <option value="login" <?php echo $log_type === 'login' ? 'selected' : ''; ?>><?php echo t('login_events'); ?></option>
                                <option value="user" <?php echo $log_type === 'user' ? 'selected' : ''; ?>><?php echo t('user_events'); ?></option>
                                <option value="system" <?php echo $log_type === 'system' ? 'selected' : ''; ?>><?php echo t('system_events'); ?></option>
                                <option value="course" <?php echo $log_type === 'course' ? 'selected' : ''; ?>><?php echo t('course_events'); ?></option>
                                <option value="assignment" <?php echo $log_type === 'assignment' ? 'selected' : ''; ?>><?php echo t('assignment_events'); ?></option>
                                <option value="security" <?php echo $log_type === 'security' ? 'selected' : ''; ?>><?php echo t('security_events'); ?></option>
                                <option value="backup" <?php echo $log_type === 'backup' ? 'selected' : ''; ?>><?php echo t('backup_events'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_range" class="form-label"><?php echo t('date_range'); ?></label>
                            <select class="form-select" id="date_range" name="date_range">
                                <option value="all" <?php echo $date_range === 'all' ? 'selected' : ''; ?>><?php echo t('all_time'); ?></option>
                                <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>><?php echo t('today'); ?></option>
                                <option value="yesterday" <?php echo $date_range === 'yesterday' ? 'selected' : ''; ?>><?php echo t('yesterday'); ?></option>
                                <option value="this_week" <?php echo $date_range === 'this_week' ? 'selected' : ''; ?>><?php echo t('this_week'); ?></option>
                                <option value="last_week" <?php echo $date_range === 'last_week' ? 'selected' : ''; ?>><?php echo t('last_week'); ?></option>
                                <option value="this_month" <?php echo $date_range === 'this_month' ? 'selected' : ''; ?>><?php echo t('this_month'); ?></option>
                                <option value="last_month" <?php echo $date_range === 'last_month' ? 'selected' : ''; ?>><?php echo t('last_month'); ?></option>
                                <option value="custom" <?php echo $date_range === 'custom' ? 'selected' : ''; ?>><?php echo t('custom_range'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="severity" class="form-label"><?php echo t('severity'); ?></label>
                            <select class="form-select" id="severity" name="severity">
                                <option value="all" <?php echo $severity === 'all' ? 'selected' : ''; ?>><?php echo t('all_severities'); ?></option>
                                <option value="info" <?php echo $severity === 'info' ? 'selected' : ''; ?>><?php echo t('info'); ?></option>
                                <option value="warning" <?php echo $severity === 'warning' ? 'selected' : ''; ?>><?php echo t('warning'); ?></option>
                                <option value="error" <?php echo $severity === 'error' ? 'selected' : ''; ?>><?php echo t('error'); ?></option>
                                <option value="critical" <?php echo $severity === 'critical' ? 'selected' : ''; ?>><?php echo t('critical'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="search" class="form-label"><?php echo t('search'); ?></label>
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control" id="search" name="search" placeholder="<?php echo t('search_logs'); ?>" value="<?php echo $search; ?>">
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i> <?php echo t('apply_filters'); ?>
                            </button>
                            <a href="admin_logs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> <?php echo t('clear_filters'); ?>
                            </a>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportLogsModal">
                                <i class="fas fa-download me-2"></i> <?php echo t('export_logs'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- جدول السجلات -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-history me-2"></i> <?php echo t('logs_list'); ?></h5>
                <div>
                    <span class="text-muted"><?php echo t('showing'); ?> <?php echo count($logs); ?> <?php echo t('of'); ?> <?php echo $total_logs; ?> <?php echo t('logs'); ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo t('date_time'); ?></th>
                                <th><?php echo t('type'); ?></th>
                                <th><?php echo t('message'); ?></th>
                                <th><?php echo t('user'); ?></th>
                                <th><?php echo t('ip_address'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php echo t('no_logs_found'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="log-severity log-severity-<?php echo $log['severity']; ?>" title="<?php echo t($log['severity']); ?>"></span>
                                                <span><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="log-type-badge log-type-<?php echo $log['type']; ?>"><?php echo t($log['type']); ?></span>
                                        </td>
                                        <td><?php echo $log['message']; ?></td>
                                        <td>
                                            <?php if (isset($log['user_id']) && $log['user_id']): ?>
                                                <div class="log-user">
                                                    <div class="log-user-avatar">
                                                        <?php echo mb_substr($log['user_name'], 0, 1, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="log-user-info">
                                                        <div class="log-user-name"><?php echo $log['user_name']; ?></div>
                                                        <div class="log-user-type"><?php echo t($log['user_type']); ?></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $log['ip_address'] ?: '-'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary log-details-toggle" data-log-id="<?php echo $log['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- تفاصيل السجل -->
                                            <div class="log-details" id="log-details-<?php echo $log['id']; ?>">
                                                <div class="log-details-row">
                                                    <div class="log-details-label"><?php echo t('log_id'); ?>:</div>
                                                    <div class="log-details-value"><?php echo $log['id']; ?></div>
                                                </div>
                                                <div class="log-details-row">
                                                    <div class="log-details-label"><?php echo t('date_time'); ?>:</div>
                                                    <div class="log-details-value"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></div>
                                                </div>
                                                <div class="log-details-row">
                                                    <div class="log-details-label"><?php echo t('type'); ?>:</div>
                                                    <div class="log-details-value"><?php echo t($log['type']); ?></div>
                                                </div>
                                                <div class="log-details-row">
                                                    <div class="log-details-label"><?php echo t('severity'); ?>:</div>
                                                    <div class="log-details-value"><?php echo t($log['severity']); ?></div>
                                                </div>
                                                <div class="log-details-row">
                                                    <div class="log-details-label"><?php echo t('message'); ?>:</div>
                                                    <div class="log-details-value"><?php echo $log['message']; ?></div>
                                                </div>
                                                <?php if (isset($log['user_id']) && $log['user_id']): ?>
                                                    <div class="log-details-row">
                                                        <div class="log-details-label"><?php echo t('user'); ?>:</div>
                                                        <div class="log-details-value"><?php echo $log['user_name']; ?> (<?php echo t($log['user_type']); ?>)</div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($log['ip_address']) && $log['ip_address']): ?>
                                                    <div class="log-details-row">
                                                        <div class="log-details-label"><?php echo t('ip_address'); ?>:</div>
                                                        <div class="log-details-value"><?php echo $log['ip_address']; ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($log['user_agent']) && $log['user_agent']): ?>
                                                    <div class="log-details-row">
                                                        <div class="log-details-label"><?php echo t('user_agent'); ?>:</div>
                                                        <div class="log-details-value"><?php echo $log['user_agent']; ?></div>
                                                    </div>
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
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $log_type; ?>&date_range=<?php echo $date_range; ?>&severity=<?php echo $severity; ?>&search=<?php echo $search; ?>&user_id=<?php echo $user_id; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $log_type; ?>&date_range=<?php echo $date_range; ?>&severity=<?php echo $severity; ?>&search=<?php echo $search; ?>&user_id=<?php echo $user_id; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $log_type; ?>&date_range=<?php echo $date_range; ?>&severity=<?php echo $severity; ?>&search=<?php echo $search; ?>&user_id=<?php echo $user_id; ?>" aria-label="Next">
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
    
    <!-- مودال تصدير السجلات -->
    <div class="modal fade" id="exportLogsModal" tabindex="-1" aria-labelledby="exportLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportLogsModalLabel"><?php echo t('export_logs'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="admin_logs_export.php" method="post">
                    <input type="hidden" name="type" value="<?php echo $log_type; ?>">
                    <input type="hidden" name="date_range" value="<?php echo $date_range; ?>">
                    <input type="hidden" name="severity" value="<?php echo $severity; ?>">
                    <input type="hidden" name="search" value="<?php echo $search; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="export_format" class="form-label"><?php echo t('export_format'); ?></label>
                            <select class="form-select" id="export_format" name="format">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="export_logs_count" class="form-label"><?php echo t('logs_count'); ?></label>
                            <select class="form-select" id="export_logs_count" name="count">
                                <option value="all"><?php echo t('all_logs'); ?> (<?php echo $total_logs; ?>)</option>
                                <option value="100">100 <?php echo t('logs'); ?></option>
                                <option value="500">500 <?php echo t('logs'); ?></option>
                                <option value="1000">1000 <?php echo t('logs'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_details" name="include_details" value="1" checked>
                                <label class="form-check-label" for="include_details">
                                    <?php echo t('include_details'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i> <?php echo t('export'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            });
            
            // تبديل عرض تفاصيل السجل
            document.querySelectorAll('.log-details-toggle').forEach(function(button) {
                button.addEventListener('click', function() {
                    const logId = this.getAttribute('data-log-id');
                    const detailsElement = document.getElementById(`log-details-${logId}`);
                    
                    if (detailsElement.style.display === 'block') {
                        detailsElement.style.display = 'none';
                        this.innerHTML = '<i class="fas fa-eye"></i>';
                    } else {
                        detailsElement.style.display = 'block';
                        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    }
                });
            });
            
            // تقديم نموذج البحث عند الكتابة
            document.querySelector('input[name="search"]').addEventListener('input', function() {
                if (this.value.length >= 3 || this.value.length === 0) {
                    document.getElementById('filterForm').submit();
                }
            });
            
            // تقديم النموذج عند تغيير أي من عناصر التصفية
            document.getElementById('log_type').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            
            document.getElementById('date_range').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            
            document.getElementById('severity').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</body>
</html>
