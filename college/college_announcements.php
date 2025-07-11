<?php
/**
 * صفحة الإعلانات في نظام UniverBoard
 * تتيح لمسؤول الكلية إدارة الإعلانات الخاصة بالكلية
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

// معالجة إضافة إعلان جديد
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_announcement') {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        $target_audience = filter_input(INPUT_POST, 'target_audience', FILTER_SANITIZE_STRING);
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $is_important = isset($_POST['is_important']) ? 1 : 0;
        
        // التحقق من صحة البيانات
        if (empty($title) || empty($content) || empty($target_audience) || empty($start_date)) {
            $error_message = t('please_fill_all_required_fields');
        } else {
            // إضافة الإعلان إلى قاعدة البيانات
            $result = add_announcement($db, $college_id, $admin_id, $title, $content, $target_audience, $start_date, $end_date, $is_important);
            
            if ($result) {
                $success_message = t('announcement_added_successfully');
            } else {
                $error_message = t('error_adding_announcement');
            }
        }
    } elseif ($_POST['action'] === 'edit_announcement') {
        $announcement_id = filter_input(INPUT_POST, 'announcement_id', FILTER_SANITIZE_NUMBER_INT);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        $target_audience = filter_input(INPUT_POST, 'target_audience', FILTER_SANITIZE_STRING);
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $is_important = isset($_POST['is_important']) ? 1 : 0;
        
        // التحقق من صحة البيانات
        if (empty($announcement_id) || empty($title) || empty($content) || empty($target_audience) || empty($start_date)) {
            $error_message = t('please_fill_all_required_fields');
        } else {
            // تحديث الإعلان في قاعدة البيانات
            $result = update_announcement($db, $announcement_id, $title, $content, $target_audience, $start_date, $end_date, $is_important);
            
            if ($result) {
                $success_message = t('announcement_updated_successfully');
            } else {
                $error_message = t('error_updating_announcement');
            }
        }
    } elseif ($_POST['action'] === 'delete_announcement') {
        $announcement_id = filter_input(INPUT_POST, 'announcement_id', FILTER_SANITIZE_NUMBER_INT);
        
        // التحقق من صحة البيانات
        if (empty($announcement_id)) {
            $error_message = t('invalid_announcement_id');
        } else {
            // حذف الإعلان من قاعدة البيانات
            $result = delete_announcement($db, $announcement_id);
            
            if ($result) {
                $success_message = t('announcement_deleted_successfully');
            } else {
                $error_message = t('error_deleting_announcement');
            }
        }
    }
}

// الحصول على قائمة الإعلانات
$announcements = get_college_announcements($db, $college_id);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للتعامل مع الإعلانات (يجب استبدالها بالدوال الفعلية)
function add_announcement($db, $college_id, $admin_id, $title, $content, $target_audience, $start_date, $end_date, $is_important) {
    // في الواقع، يجب إضافة الإعلان إلى قاعدة البيانات
    return true;
}

function update_announcement($db, $announcement_id, $title, $content, $target_audience, $start_date, $end_date, $is_important) {
    // في الواقع، يجب تحديث الإعلان في قاعدة البيانات
    return true;
}

function delete_announcement($db, $announcement_id) {
    // في الواقع، يجب حذف الإعلان من قاعدة البيانات
    return true;
}

function get_college_announcements($db, $college_id) {
    // في الواقع، يجب استرجاع الإعلانات من قاعدة البيانات
    // بيانات وهمية للعرض
    return [
        [
            'id' => 1,
            'title' => 'بدء التسجيل للفصل الدراسي الثاني 2024',
            'content' => 'نود إعلام جميع الطلاب بأن التسجيل للفصل الدراسي الثاني للعام الدراسي 2024 سيبدأ يوم الأحد الموافق 15 يناير 2024 وينتهي يوم الخميس الموافق 25 يناير 2024. يرجى مراجعة الجدول الدراسي المعلن ومراجعة المرشد الأكاديمي قبل التسجيل.',
            'target_audience' => 'students',
            'created_at' => '2024-01-10 09:30:00',
            'start_date' => '2024-01-10',
            'end_date' => '2024-01-25',
            'is_important' => 1,
            'created_by' => 'د. محمد العمري',
            'views_count' => 450
        ],
        [
            'id' => 2,
            'title' => 'ورشة عمل: تطوير مهارات البحث العلمي',
            'content' => 'تنظم كلية علوم الحاسب ورشة عمل بعنوان "تطوير مهارات البحث العلمي" يوم الثلاثاء الموافق 20 فبراير 2024 من الساعة 10 صباحاً حتى 2 ظهراً في قاعة المؤتمرات الرئيسية. الورشة موجهة لأعضاء هيئة التدريس وطلاب الدراسات العليا. يرجى التسجيل المسبق عبر البوابة الإلكترونية.',
            'target_audience' => 'teachers,graduate_students',
            'created_at' => '2024-02-05 14:15:00',
            'start_date' => '2024-02-05',
            'end_date' => '2024-02-20',
            'is_important' => 0,
            'created_by' => 'د. سارة الأحمد',
            'views_count' => 120
        ],
        [
            'id' => 3,
            'title' => 'تغيير مواعيد الاختبارات النهائية',
            'content' => 'نظراً للظروف الطارئة، تم تعديل جدول الاختبارات النهائية للفصل الدراسي الحالي. يرجى الاطلاع على الجدول المحدث المنشور على موقع الكلية. لأي استفسارات، يرجى التواصل مع مكتب شؤون الطلاب.',
            'target_audience' => 'all',
            'created_at' => '2024-04-20 11:00:00',
            'start_date' => '2024-04-20',
            'end_date' => '2024-05-10',
            'is_important' => 1,
            'created_by' => 'د. فهد السالم',
            'views_count' => 680
        ],
        [
            'id' => 4,
            'title' => 'دعوة لحضور حفل تخرج الدفعة 15',
            'content' => 'يسر كلية علوم الحاسب دعوتكم لحضور حفل تخرج الدفعة 15 من طلاب وطالبات الكلية، والذي سيقام يوم الخميس الموافق 15 يونيو 2024 في الساعة 7 مساءً بالقاعة الكبرى بمركز المؤتمرات بالجامعة. نرجو تأكيد الحضور عبر الرابط المرفق.',
            'target_audience' => 'all',
            'created_at' => '2024-05-25 10:30:00',
            'start_date' => '2024-05-25',
            'end_date' => '2024-06-15',
            'is_important' => 0,
            'created_by' => 'د. نورة القحطاني',
            'views_count' => 320
        ],
        [
            'id' => 5,
            'title' => 'إغلاق مبنى الكلية للصيانة',
            'content' => 'نود إعلامكم بأنه سيتم إغلاق مبنى الكلية الرئيسي للصيانة خلال الفترة من 1 إلى 10 يوليو 2024. سيتم نقل جميع المكاتب والخدمات مؤقتاً إلى المبنى الفرعي B. نعتذر عن أي إزعاج قد يسببه ذلك.',
            'target_audience' => 'all',
            'created_at' => '2024-06-20 09:00:00',
            'start_date' => '2024-06-20',
            'end_date' => '2024-07-10',
            'is_important' => 1,
            'created_by' => 'د. عبدالله العمري',
            'views_count' => 410
        ]
    ];
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

// دالة مساعدة لتنسيق الوقت المنقضي
function time_elapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' ' . ($diff->y > 1 ? t('years_ago') : t('year_ago'));
    } elseif ($diff->m > 0) {
        return $diff->m . ' ' . ($diff->m > 1 ? t('months_ago') : t('month_ago'));
    } elseif ($diff->d > 0) {
        return $diff->d . ' ' . ($diff->d > 1 ? t('days_ago') : t('day_ago'));
    } elseif ($diff->h > 0) {
        return $diff->h . ' ' . ($diff->h > 1 ? t('hours_ago') : t('hour_ago'));
    } elseif ($diff->i > 0) {
        return $diff->i . ' ' . ($diff->i > 1 ? t('minutes_ago') : t('minute_ago'));
    } else {
        return t('just_now');
    }
}

// دالة مساعدة لتحويل الجمهور المستهدف إلى نص مقروء
function format_target_audience($target_audience) {
    $audiences = explode(',', $target_audience);
    $formatted = [];
    
    foreach ($audiences as $audience) {
        switch ($audience) {
            case 'all':
                $formatted[] = t('all_users');
                break;
            case 'students':
                $formatted[] = t('students');
                break;
            case 'teachers':
                $formatted[] = t('teachers');
                break;
            case 'staff':
                $formatted[] = t('staff');
                break;
            case 'graduate_students':
                $formatted[] = t('graduate_students');
                break;
            default:
                $formatted[] = $audience;
        }
    }
    
    return implode(', ', $formatted);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('announcements'); ?></title>
    
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
    
    <!-- Summernote (محرر النصوص) -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    
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
        
        /* تنسيقات خاصة بصفحة الإعلانات */
        .announcement-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        [dir="rtl"] .announcement-card {
            border-left: none;
            border-right: 4px solid transparent;
        }
        
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .announcement-card.important {
            border-color: #dc3545;
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .announcement-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .announcement-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--gray-color);
            font-size: 0.875rem;
        }
        
        .announcement-meta-item {
            display: flex;
            align-items: center;
        }
        
        .announcement-meta-item i {
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .announcement-meta-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .announcement-content {
            margin-bottom: 1rem;
        }
        
        .announcement-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .announcement-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .announcement-badge-important {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .announcement-badge-active {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .announcement-badge-expired {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .announcement-badge-scheduled {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .announcement-badge-audience {
            background-color: rgba(102, 155, 188, 0.1);
            color: #669bbc;
        }
        
        .announcement-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .announcement-stats {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .announcement-stat {
            display: flex;
            align-items: center;
            color: var(--gray-color);
            font-size: 0.875rem;
        }
        
        .announcement-stat i {
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .announcement-stat i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        @media (max-width: 768px) {
            .filter-item {
                min-width: 100%;
            }
        }
        
        .summernote-container {
            margin-bottom: 1.5rem;
        }
        
        .note-editor {
            border-radius: 0.25rem;
        }
        
        .theme-dark .note-editor {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .note-toolbar {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .theme-dark .note-editable {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .modal-content {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .modal-content {
            background-color: var(--dark-bg);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
        }
        
        .theme-dark .modal-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .modal-title {
            font-size: 1.25rem;
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
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
        
        .alert {
            border-radius: 0.5rem;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .theme-dark .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
        }
        
        .theme-dark .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
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
                    <a class="nav-link" href="college_reports_attendance.php">
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
                    <a class="nav-link active" href="college_announcements.php">
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
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger">5</span>
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
                                    <p class="mb-0">تم تسجيل 15 طالب جديد في قسم علوم الحاسب</p>
                                    <small class="text-muted">منذ 30 دقيقة</small>
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
                                    <p class="mb-0">تم تحديث جدول الامتحانات النهائية</p>
                                    <small class="text-muted">منذ ساعة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-sm bg-info text-white rounded-circle">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">تم تعيين د. محمد أحمد كرئيس لقسم الهندسة المدنية</p>
                                    <small class="text-muted">منذ 3 ساعات</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="college_notifications.php"><?php echo t('view_all_notifications'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesDropdown">
                        <div class="dropdown-header"><?php echo t('messages'); ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/dean.jpg" alt="Dean" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">د. عبدالله العمري</p>
                                    <small class="text-muted">نرجو مراجعة الميزانية المقترحة للعام القادم</small>
                                    <small class="text-muted d-block">منذ 20 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/department_head.jpg" alt="Department Head" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">د. سارة الأحمد</p>
                                    <small class="text-muted">هل يمكننا مناقشة توزيع المقررات للفصل القادم؟</small>
                                    <small class="text-muted d-block">منذ ساعتين</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="college_messages.php"><?php echo t('view_all_messages'); ?></a>
                    </div>
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
                    <h1 class="page-title"><?php echo t('announcements'); ?></h1>
                    <p class="page-subtitle"><?php echo t('manage_college_announcements'); ?></p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                    <i class="fas fa-plus me-1"></i> <?php echo t('add_announcement'); ?>
                </button>
            </div>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- فلتر الإعلانات -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="filter-bar">
                    <div class="filter-item">
                        <label for="statusFilter" class="form-label"><?php echo t('filter_by_status'); ?></label>
                        <select class="form-select" id="statusFilter">
                            <option value="all"><?php echo t('all_announcements'); ?></option>
                            <option value="active"><?php echo t('active'); ?></option>
                            <option value="scheduled"><?php echo t('scheduled'); ?></option>
                            <option value="expired"><?php echo t('expired'); ?></option>
                            <option value="important"><?php echo t('important'); ?></option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="audienceFilter" class="form-label"><?php echo t('filter_by_audience'); ?></label>
                        <select class="form-select" id="audienceFilter">
                            <option value="all"><?php echo t('all_audiences'); ?></option>
                            <option value="students"><?php echo t('students'); ?></option>
                            <option value="teachers"><?php echo t('teachers'); ?></option>
                            <option value="staff"><?php echo t('staff'); ?></option>
                            <option value="graduate_students"><?php echo t('graduate_students'); ?></option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="dateFilter" class="form-label"><?php echo t('filter_by_date'); ?></label>
                        <select class="form-select" id="dateFilter">
                            <option value="all"><?php echo t('all_dates'); ?></option>
                            <option value="today"><?php echo t('today'); ?></option>
                            <option value="this_week"><?php echo t('this_week'); ?></option>
                            <option value="this_month"><?php echo t('this_month'); ?></option>
                            <option value="last_month"><?php echo t('last_month'); ?></option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="searchAnnouncement" class="form-label"><?php echo t('search'); ?></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchAnnouncement" placeholder="<?php echo t('search_announcements'); ?>">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- قائمة الإعلانات -->
        <div class="row" id="announcementsList">
            <?php foreach ($announcements as $announcement): ?>
                <?php
                // تحديد حالة الإعلان
                $now = new DateTime();
                $start_date = new DateTime($announcement['start_date']);
                $end_date = !empty($announcement['end_date']) ? new DateTime($announcement['end_date']) : null;
                
                $status = 'active';
                if ($start_date > $now) {
                    $status = 'scheduled';
                } elseif ($end_date && $end_date < $now) {
                    $status = 'expired';
                }
                ?>
                <div class="col-md-6 announcement-item" 
                     data-status="<?php echo $status; ?>" 
                     data-important="<?php echo $announcement['is_important']; ?>" 
                     data-audience="<?php echo $announcement['target_audience']; ?>">
                    <div class="card announcement-card <?php echo $announcement['is_important'] ? 'important' : ''; ?>">
                        <div class="card-body">
                            <div class="announcement-header">
                                <div>
                                    <?php if ($announcement['is_important']): ?>
                                        <span class="announcement-badge announcement-badge-important"><?php echo t('important'); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($status === 'active'): ?>
                                        <span class="announcement-badge announcement-badge-active"><?php echo t('active'); ?></span>
                                    <?php elseif ($status === 'scheduled'): ?>
                                        <span class="announcement-badge announcement-badge-scheduled"><?php echo t('scheduled'); ?></span>
                                    <?php elseif ($status === 'expired'): ?>
                                        <span class="announcement-badge announcement-badge-expired"><?php echo t('expired'); ?></span>
                                    <?php endif; ?>
                                    
                                    <span class="announcement-badge announcement-badge-audience">
                                        <?php echo format_target_audience($announcement['target_audience']); ?>
                                    </span>
                                    
                                    <h5 class="announcement-title"><?php echo $announcement['title']; ?></h5>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" id="announcementActions<?php echo $announcement['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="announcementActions<?php echo $announcement['id']; ?>">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editAnnouncementModal" data-announcement-id="<?php echo $announcement['id']; ?>">
                                                <i class="fas fa-edit me-2"></i> <?php echo t('edit'); ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewAnnouncementModal" data-announcement-id="<?php echo $announcement['id']; ?>">
                                                <i class="fas fa-eye me-2"></i> <?php echo t('view'); ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteAnnouncementModal" data-announcement-id="<?php echo $announcement['id']; ?>">
                                                <i class="fas fa-trash-alt me-2"></i> <?php echo t('delete'); ?>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <i class="fas fa-share-alt me-2"></i> <?php echo t('share'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="announcement-meta">
                                <div class="announcement-meta-item">
                                    <i class="fas fa-user"></i> <?php echo $announcement['created_by']; ?>
                                </div>
                                <div class="announcement-meta-item">
                                    <i class="fas fa-clock"></i> <?php echo time_elapsed($announcement['created_at']); ?>
                                </div>
                                <div class="announcement-meta-item">
                                    <i class="fas fa-calendar-alt"></i> <?php echo format_date($announcement['start_date']); ?>
                                    <?php if (!empty($announcement['end_date'])): ?>
                                        - <?php echo format_date($announcement['end_date']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="announcement-content">
                                <?php echo mb_substr($announcement['content'], 0, 150) . (mb_strlen($announcement['content']) > 150 ? '...' : ''); ?>
                            </div>
                            
                            <div class="announcement-stats">
                                <div class="announcement-stat">
                                    <i class="fas fa-eye"></i> <?php echo $announcement['views_count']; ?> <?php echo t('views'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- ترقيم الصفحات -->
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="<?php echo t('announcements_pagination'); ?>">
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
    
    <!-- مودال إضافة إعلان جديد -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAnnouncementModalLabel"><?php echo t('add_new_announcement'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_announcement">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label"><?php echo t('title'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label"><?php echo t('content'); ?> <span class="text-danger">*</span></label>
                            <div class="summernote-container">
                                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="target_audience" class="form-label"><?php echo t('target_audience'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="target_audience" name="target_audience" required>
                                        <option value="all"><?php echo t('all_users'); ?></option>
                                        <option value="students"><?php echo t('students'); ?></option>
                                        <option value="teachers"><?php echo t('teachers'); ?></option>
                                        <option value="staff"><?php echo t('staff'); ?></option>
                                        <option value="graduate_students"><?php echo t('graduate_students'); ?></option>
                                        <option value="students,teachers"><?php echo t('students_and_teachers'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_important" class="form-label"><?php echo t('importance'); ?></label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="is_important" name="is_important">
                                        <label class="form-check-label" for="is_important"><?php echo t('mark_as_important'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label"><?php echo t('start_date'); ?> <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label"><?php echo t('end_date'); ?></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('save'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل إعلان -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAnnouncementModalLabel"><?php echo t('edit_announcement'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="edit_announcement">
                    <input type="hidden" name="announcement_id" id="edit_announcement_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label"><?php echo t('title'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_content" class="form-label"><?php echo t('content'); ?> <span class="text-danger">*</span></label>
                            <div class="summernote-container">
                                <textarea class="form-control" id="edit_content" name="content" rows="5" required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_target_audience" class="form-label"><?php echo t('target_audience'); ?> <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_target_audience" name="target_audience" required>
                                        <option value="all"><?php echo t('all_users'); ?></option>
                                        <option value="students"><?php echo t('students'); ?></option>
                                        <option value="teachers"><?php echo t('teachers'); ?></option>
                                        <option value="staff"><?php echo t('staff'); ?></option>
                                        <option value="graduate_students"><?php echo t('graduate_students'); ?></option>
                                        <option value="students,teachers"><?php echo t('students_and_teachers'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_is_important" class="form-label"><?php echo t('importance'); ?></label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="edit_is_important" name="is_important">
                                        <label class="form-check-label" for="edit_is_important"><?php echo t('mark_as_important'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label"><?php echo t('start_date'); ?> <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label"><?php echo t('end_date'); ?></label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date">
                                </div>
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
    
    <!-- مودال عرض إعلان -->
    <div class="modal fade" id="viewAnnouncementModal" tabindex="-1" aria-labelledby="viewAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAnnouncementModalLabel"><?php echo t('view_announcement'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 id="view_title"></h4>
                            <div>
                                <span id="view_badge_important" class="announcement-badge announcement-badge-important"><?php echo t('important'); ?></span>
                                <span id="view_badge_status" class="announcement-badge"></span>
                                <span id="view_badge_audience" class="announcement-badge announcement-badge-audience"></span>
                            </div>
                        </div>
                        <div class="announcement-meta mb-3">
                            <div class="announcement-meta-item">
                                <i class="fas fa-user"></i> <span id="view_created_by"></span>
                            </div>
                            <div class="announcement-meta-item">
                                <i class="fas fa-clock"></i> <span id="view_created_at"></span>
                            </div>
                            <div class="announcement-meta-item">
                                <i class="fas fa-calendar-alt"></i> <span id="view_date_range"></span>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="view_content"></div>
                            </div>
                        </div>
                    </div>
                    <div class="announcement-stats">
                        <div class="announcement-stat">
                            <i class="fas fa-eye"></i> <span id="view_views_count"></span> <?php echo t('views'); ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="editAnnouncementBtn">
                        <i class="fas fa-edit me-1"></i> <?php echo t('edit'); ?>
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteAnnouncementBtn">
                        <i class="fas fa-trash-alt me-1"></i> <?php echo t('delete'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف إعلان -->
    <div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAnnouncementModalLabel"><?php echo t('delete_announcement'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_announcement">
                    <input type="hidden" name="announcement_id" id="delete_announcement_id">
                    <div class="modal-body">
                        <p><?php echo t('delete_announcement_confirm'); ?></p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo t('delete_announcement_warning'); ?>
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
    
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    
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
            
            // تهيئة محرر النصوص Summernote
            $('#content, #edit_content').summernote({
                placeholder: '<?php echo t('write_announcement_content_here'); ?>',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        // يمكن إضافة وظيفة رفع الصور هنا
                        alert('<?php echo t('image_upload_not_supported'); ?>');
                    }
                }
            });
            
            // فلترة الإعلانات
            function filterAnnouncements() {
                const statusFilter = document.getElementById('statusFilter').value;
                const audienceFilter = document.getElementById('audienceFilter').value;
                const dateFilter = document.getElementById('dateFilter').value;
                const searchFilter = document.getElementById('searchAnnouncement').value.toLowerCase();
                
                const announcements = document.querySelectorAll('.announcement-item');
                
                announcements.forEach(announcement => {
                    let showAnnouncement = true;
                    
                    // فلتر الحالة
                    if (statusFilter !== 'all') {
                        if (statusFilter === 'important' && announcement.dataset.important !== '1') {
                            showAnnouncement = false;
                        } else if (statusFilter !== 'important' && announcement.dataset.status !== statusFilter) {
                            showAnnouncement = false;
                        }
                    }
                    
                    // فلتر الجمهور المستهدف
                    if (audienceFilter !== 'all' && !announcement.dataset.audience.includes(audienceFilter)) {
                        showAnnouncement = false;
                    }
                    
                    // فلتر البحث
                    if (searchFilter) {
                        const title = announcement.querySelector('.announcement-title').textContent.toLowerCase();
                        const content = announcement.querySelector('.announcement-content').textContent.toLowerCase();
                        
                        if (!title.includes(searchFilter) && !content.includes(searchFilter)) {
                            showAnnouncement = false;
                        }
                    }
                    
                    // تطبيق الفلتر
                    announcement.style.display = showAnnouncement ? 'block' : 'none';
                });
            }
            
            // تطبيق الفلتر عند تغيير أي من عناصر الفلترة
            document.getElementById('statusFilter').addEventListener('change', filterAnnouncements);
            document.getElementById('audienceFilter').addEventListener('change', filterAnnouncements);
            document.getElementById('dateFilter').addEventListener('change', filterAnnouncements);
            document.getElementById('searchAnnouncement').addEventListener('input', filterAnnouncements);
            
            // بيانات الإعلانات (للعرض والتعديل)
            const announcementsData = <?php echo json_encode($announcements); ?>;
            
            // تعبئة بيانات الإعلان في مودال العرض
            $('#viewAnnouncementModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const announcementId = button.data('announcement-id');
                const announcement = announcementsData.find(a => a.id == announcementId);
                
                if (announcement) {
                    $('#view_title').text(announcement.title);
                    $('#view_content').html(announcement.content);
                    $('#view_created_by').text(announcement.created_by);
                    $('#view_created_at').text('<?php echo t('created'); ?> ' + timeElapsed(announcement.created_at));
                    
                    let dateRange = formatDate(announcement.start_date);
                    if (announcement.end_date) {
                        dateRange += ' - ' + formatDate(announcement.end_date);
                    }
                    $('#view_date_range').text(dateRange);
                    
                    $('#view_views_count').text(announcement.views_count);
                    
                    // تحديد حالة الإعلان
                    const now = new Date();
                    const startDate = new Date(announcement.start_date);
                    const endDate = announcement.end_date ? new Date(announcement.end_date) : null;
                    
                    let status = 'active';
                    let statusText = '<?php echo t('active'); ?>';
                    let statusClass = 'announcement-badge-active';
                    
                    if (startDate > now) {
                        status = 'scheduled';
                        statusText = '<?php echo t('scheduled'); ?>';
                        statusClass = 'announcement-badge-scheduled';
                    } else if (endDate && endDate < now) {
                        status = 'expired';
                        statusText = '<?php echo t('expired'); ?>';
                        statusClass = 'announcement-badge-expired';
                    }
                    
                    $('#view_badge_status').text(statusText);
                    $('#view_badge_status').attr('class', 'announcement-badge ' + statusClass);
                    
                    // عرض أو إخفاء شارة "مهم"
                    if (announcement.is_important == 1) {
                        $('#view_badge_important').show();
                    } else {
                        $('#view_badge_important').hide();
                    }
                    
                    // تعيين الجمهور المستهدف
                    $('#view_badge_audience').text(formatTargetAudience(announcement.target_audience));
                    
                    // تعيين معرف الإعلان لأزرار التعديل والحذف
                    $('#editAnnouncementBtn').data('announcement-id', announcement.id);
                    $('#deleteAnnouncementBtn').data('announcement-id', announcement.id);
                }
            });
            
            // فتح مودال التعديل من مودال العرض
            $('#editAnnouncementBtn').on('click', function() {
                const announcementId = $(this).data('announcement-id');
                $('#viewAnnouncementModal').modal('hide');
                
                // فتح مودال التعديل بعد إغلاق مودال العرض
                $('#viewAnnouncementModal').on('hidden.bs.modal', function() {
                    $(`[data-bs-target="#editAnnouncementModal"][data-announcement-id="${announcementId}"]`).click();
                    $('#viewAnnouncementModal').off('hidden.bs.modal');
                });
            });
            
            // فتح مودال الحذف من مودال العرض
            $('#deleteAnnouncementBtn').on('click', function() {
                const announcementId = $(this).data('announcement-id');
                $('#viewAnnouncementModal').modal('hide');
                
                // فتح مودال الحذف بعد إغلاق مودال العرض
                $('#viewAnnouncementModal').on('hidden.bs.modal', function() {
                    $(`[data-bs-target="#deleteAnnouncementModal"][data-announcement-id="${announcementId}"]`).click();
                    $('#viewAnnouncementModal').off('hidden.bs.modal');
                });
            });
            
            // تعبئة بيانات الإعلان في مودال التعديل
            $('#editAnnouncementModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const announcementId = button.data('announcement-id');
                const announcement = announcementsData.find(a => a.id == announcementId);
                
                if (announcement) {
                    $('#edit_announcement_id').val(announcement.id);
                    $('#edit_title').val(announcement.title);
                    $('#edit_content').summernote('code', announcement.content);
                    $('#edit_target_audience').val(announcement.target_audience);
                    $('#edit_is_important').prop('checked', announcement.is_important == 1);
                    $('#edit_start_date').val(announcement.start_date.split(' ')[0]);
                    
                    if (announcement.end_date) {
                        $('#edit_end_date').val(announcement.end_date.split(' ')[0]);
                    } else {
                        $('#edit_end_date').val('');
                    }
                }
            });
            
            // تعيين معرف الإعلان في مودال الحذف
            $('#deleteAnnouncementModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const announcementId = button.data('announcement-id');
                $('#delete_announcement_id').val(announcementId);
            });
            
            // دالة لتنسيق التاريخ
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('ar-SA');
            }
            
            // دالة لتنسيق الوقت المنقضي
            function timeElapsed(datetime) {
                const now = new Date();
                const ago = new Date(datetime);
                const diff = Math.floor((now - ago) / 1000);
                
                if (diff < 60) {
                    return '<?php echo t('just_now'); ?>';
                } else if (diff < 3600) {
                    const minutes = Math.floor(diff / 60);
                    return minutes + ' ' + (minutes > 1 ? '<?php echo t('minutes_ago'); ?>' : '<?php echo t('minute_ago'); ?>');
                } else if (diff < 86400) {
                    const hours = Math.floor(diff / 3600);
                    return hours + ' ' + (hours > 1 ? '<?php echo t('hours_ago'); ?>' : '<?php echo t('hour_ago'); ?>');
                } else if (diff < 2592000) {
                    const days = Math.floor(diff / 86400);
                    return days + ' ' + (days > 1 ? '<?php echo t('days_ago'); ?>' : '<?php echo t('day_ago'); ?>');
                } else if (diff < 31536000) {
                    const months = Math.floor(diff / 2592000);
                    return months + ' ' + (months > 1 ? '<?php echo t('months_ago'); ?>' : '<?php echo t('month_ago'); ?>');
                } else {
                    const years = Math.floor(diff / 31536000);
                    return years + ' ' + (years > 1 ? '<?php echo t('years_ago'); ?>' : '<?php echo t('year_ago'); ?>');
                }
            }
            
            // دالة لتحويل الجمهور المستهدف إلى نص مقروء
            function formatTargetAudience(targetAudience) {
                const audiences = targetAudience.split(',');
                const formatted = [];
                
                audiences.forEach(audience => {
                    switch (audience) {
                        case 'all':
                            formatted.push('<?php echo t('all_users'); ?>');
                            break;
                        case 'students':
                            formatted.push('<?php echo t('students'); ?>');
                            break;
                        case 'teachers':
                            formatted.push('<?php echo t('teachers'); ?>');
                            break;
                        case 'staff':
                            formatted.push('<?php echo t('staff'); ?>');
                            break;
                        case 'graduate_students':
                            formatted.push('<?php echo t('graduate_students'); ?>');
                            break;
                        default:
                            formatted.push(audience);
                    }
                });
                
                return formatted.join(', ');
            }
        });
    </script>
</body>
</html>
