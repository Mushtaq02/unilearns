<?php
/**
 * صفحة الرسائل في نظام الكليات لمنصة UniverBoard
 * تتيح لمسؤول الكلية إدارة الرسائل والتواصل مع الطلاب والمعلمين
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

// معالجة إرسال رسالة جديدة
$message_sent = false;
$message_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $recipient_type = filter_input(INPUT_POST, 'recipient_type', FILTER_SANITIZE_STRING);
    $recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_SANITIZE_NUMBER_INT);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message_content = filter_input(INPUT_POST, 'message_content', FILTER_SANITIZE_STRING);
    
    if (empty($recipient_type) || empty($recipient_id) || empty($subject) || empty($message_content)) {
        $message_error = t('all_fields_required');
    } else {
        // إرسال الرسالة (دالة وهمية، يجب استبدالها بالدالة الفعلية)
        $result = send_message($db, $admin_id, 'college_admin', $recipient_id, $recipient_type, $subject, $message_content);
        
        if ($result) {
            $message_sent = true;
        } else {
            $message_error = t('message_send_failed');
        }
    }
}

// معالجة حذف الرسائل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!empty($message_id)) {
        // حذف الرسالة (دالة وهمية، يجب استبدالها بالدالة الفعلية)
        delete_message($db, $message_id, $admin_id);
    }
}

// معالجة وضع علامة "مقروءة" على الرسائل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_as_read') {
    $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (!empty($message_id)) {
        // وضع علامة "مقروءة" على الرسالة (دالة وهمية، يجب استبدالها بالدالة الفعلية)
        mark_message_as_read($db, $message_id, $admin_id);
    }
}

// الحصول على قائمة الرسائل
$filter_folder = filter_input(INPUT_GET, 'folder', FILTER_SANITIZE_STRING) ?: 'inbox';
$filter_search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$filter_unread_only = filter_input(INPUT_GET, 'unread_only', FILTER_VALIDATE_BOOLEAN);
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
$per_page = 10;

// الحصول على الرسائل بناءً على الفلاتر (دالة وهمية، يجب استبدالها بالدالة الفعلية)
$messages = get_messages($db, $admin_id, 'college_admin', $filter_folder, $filter_search, $filter_unread_only, $page, $per_page);
$total_messages = get_total_messages($db, $admin_id, 'college_admin', $filter_folder, $filter_search, $filter_unread_only);
$total_pages = ceil($total_messages / $per_page);

// الحصول على عدد الرسائل غير المقروءة
$unread_count = get_unread_messages_count($db, $admin_id, 'college_admin');

// الحصول على قائمة المستلمين المحتملين
$teachers = get_college_teachers_list($db, $college_id);
$students = get_college_students_list($db, $college_id);
$departments = get_college_departments($db, $college_id);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function send_message($db, $sender_id, $sender_type, $recipient_id, $recipient_type, $subject, $message_content) {
    // في الواقع، يجب حفظ الرسالة في قاعدة البيانات
    return true;
}

function delete_message($db, $message_id, $user_id) {
    // في الواقع، يجب حذف الرسالة من قاعدة البيانات أو وضع علامة "محذوفة"
    return true;
}

function mark_message_as_read($db, $message_id, $user_id) {
    // في الواقع، يجب وضع علامة "مقروءة" على الرسالة في قاعدة البيانات
    return true;
}

function get_messages($db, $user_id, $user_type, $folder, $search = null, $unread_only = false, $page = 1, $per_page = 10) {
    // في الواقع، يجب استرجاع الرسائل من قاعدة البيانات بناءً على الفلاتر
    $offset = ($page - 1) * $per_page;
    
    // بيانات وهمية للعرض
    $messages = [];
    
    if ($folder === 'inbox') {
        $messages = [
            [
                'id' => 1,
                'sender_id' => 1001,
                'sender_name' => 'أحمد عبدالله',
                'sender_type' => 'student',
                'recipient_id' => $user_id,
                'recipient_type' => $user_type,
                'subject' => 'استفسار حول موعد الاختبارات النهائية',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

أرجو التكرم بإفادتي عن موعد الاختبارات النهائية للفصل الدراسي الحالي، حيث أنني لم أجد الجدول على موقع الكلية.

وتفضلوا بقبول فائق الاحترام والتقدير،
أحمد عبدالله',
                'date_sent' => '2025-05-15 10:30:45',
                'is_read' => true,
                'has_attachment' => false
            ],
            [
                'id' => 2,
                'sender_id' => 2,
                'sender_name' => 'د. سارة الأحمد',
                'sender_type' => 'teacher',
                'recipient_id' => $user_id,
                'recipient_type' => $user_type,
                'subject' => 'طلب تعديل جدول المحاضرات',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

أرجو النظر في إمكانية تعديل جدول محاضراتي ليوم الثلاثاء، حيث أنني سأشارك في مؤتمر علمي خلال الأسبوع القادم.

أقترح نقل المحاضرة إلى يوم الخميس في نفس الوقت إذا كان ذلك ممكناً.

وتفضلوا بقبول فائق الاحترام والتقدير،
د. سارة الأحمد',
                'date_sent' => '2025-05-18 14:15:22',
                'is_read' => false,
                'has_attachment' => false
            ],
            [
                'id' => 3,
                'sender_id' => 1,
                'sender_name' => 'د. محمد العمري',
                'sender_type' => 'teacher',
                'recipient_id' => $user_id,
                'recipient_type' => $user_type,
                'subject' => 'تقرير عن سير الاختبارات النصفية',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

أرفق لكم تقريراً عن سير الاختبارات النصفية لمقرر "مقدمة في البرمجة" للفصل الدراسي الحالي.

تم إجراء الاختبار بنجاح، وكانت نسبة الحضور 95%، ومتوسط الدرجات 78%.

وتفضلوا بقبول فائق الاحترام والتقدير،
د. محمد العمري',
                'date_sent' => '2025-05-10 09:45:30',
                'is_read' => true,
                'has_attachment' => true,
                'attachment_name' => 'تقرير_الاختبارات_النصفية.pdf'
            ]
        ];
    } elseif ($folder === 'sent') {
        $messages = [
            [
                'id' => 4,
                'sender_id' => $user_id,
                'sender_name' => 'د. عبدالله العمري',
                'sender_type' => $user_type,
                'recipient_id' => 1001,
                'recipient_name' => 'أحمد عبدالله',
                'recipient_type' => 'student',
                'subject' => 'رد: استفسار حول موعد الاختبارات النهائية',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

تم نشر جدول الاختبارات النهائية على موقع الكلية، يمكنك الاطلاع عليه من خلال الرابط التالي:
https://example.com/exams-schedule

وتفضلوا بقبول فائق الاحترام والتقدير،
د. عبدالله العمري
عميد كلية علوم الحاسب والمعلومات',
                'date_sent' => '2025-05-15 11:20:15',
                'is_read' => true,
                'has_attachment' => false
            ],
            [
                'id' => 5,
                'sender_id' => $user_id,
                'sender_name' => 'د. عبدالله العمري',
                'sender_type' => $user_type,
                'recipient_id' => 0,
                'recipient_name' => 'جميع المعلمين',
                'recipient_type' => 'all_teachers',
                'subject' => 'اجتماع مجلس الكلية',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

نود إعلامكم بأنه سيتم عقد اجتماع مجلس الكلية يوم الأربعاء القادم الموافق 25 مايو 2025 في تمام الساعة 10:00 صباحاً بقاعة الاجتماعات الرئيسية.

يرجى الالتزام بالحضور والاطلاع على جدول الأعمال المرفق.

وتفضلوا بقبول فائق الاحترام والتقدير،
د. عبدالله العمري
عميد كلية علوم الحاسب والمعلومات',
                'date_sent' => '2025-05-17 16:30:00',
                'is_read' => false,
                'has_attachment' => true,
                'attachment_name' => 'جدول_أعمال_اجتماع_مجلس_الكلية.pdf'
            ]
        ];
    } elseif ($folder === 'drafts') {
        $messages = [
            [
                'id' => 6,
                'sender_id' => $user_id,
                'sender_name' => 'د. عبدالله العمري',
                'sender_type' => $user_type,
                'recipient_id' => 0,
                'recipient_name' => 'جميع الطلاب',
                'recipient_type' => 'all_students',
                'subject' => 'إعلان هام بخصوص التسجيل للفصل الدراسي القادم',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

نود إعلامكم بأن التسجيل للفصل الدراسي القادم سيبدأ يوم الأحد الموافق 1 يونيو 2025 وينتهي يوم الخميس الموافق 12 يونيو 2025.

يرجى مراجعة المرشد الأكاديمي قبل التسجيل.

[مسودة - لم يتم الانتهاء من كتابة الرسالة]',
                'date_sent' => null,
                'is_read' => true,
                'has_attachment' => false,
                'is_draft' => true
            ]
        ];
    } elseif ($folder === 'trash') {
        $messages = [
            [
                'id' => 7,
                'sender_id' => 1005,
                'sender_name' => 'خالد إبراهيم',
                'sender_type' => 'student',
                'recipient_id' => $user_id,
                'recipient_type' => $user_type,
                'subject' => 'طلب تأجيل اختبار',
                'content' => 'السلام عليكم ورحمة الله وبركاته،

أرجو التكرم بالموافقة على تأجيل اختبار مادة "قواعد البيانات" المقرر يوم الأربعاء القادم، وذلك لظروف صحية طارئة.

مرفق التقرير الطبي.

وتفضلوا بقبول فائق الاحترام والتقدير،
خالد إبراهيم',
                'date_sent' => '2025-05-05 08:20:10',
                'is_read' => true,
                'has_attachment' => true,
                'attachment_name' => 'تقرير_طبي.pdf',
                'is_deleted' => true
            ]
        ];
    }
    
    // تطبيق فلتر البحث
    if (!empty($search)) {
        $messages = array_filter($messages, function($message) use ($search) {
            return (stripos($message['subject'], $search) !== false || 
                    stripos($message['content'], $search) !== false || 
                    stripos($message['sender_name'], $search) !== false || 
                    (isset($message['recipient_name']) && stripos($message['recipient_name'], $search) !== false));
        });
    }
    
    // تطبيق فلتر الرسائل غير المقروءة
    if ($unread_only) {
        $messages = array_filter($messages, function($message) {
            return !$message['is_read'];
        });
    }
    
    // ترتيب الرسائل حسب التاريخ (الأحدث أولاً)
    usort($messages, function($a, $b) {
        $date_a = $a['date_sent'] ?? '9999-12-31';
        $date_b = $b['date_sent'] ?? '9999-12-31';
        return strcmp($date_b, $date_a);
    });
    
    // تطبيق الصفحات
    $messages = array_slice($messages, $offset, $per_page);
    
    return $messages;
}

function get_total_messages($db, $user_id, $user_type, $folder, $search = null, $unread_only = false) {
    // في الواقع، يجب حساب إجمالي عدد الرسائل من قاعدة البيانات
    if ($folder === 'inbox') return 15;
    if ($folder === 'sent') return 8;
    if ($folder === 'drafts') return 1;
    if ($folder === 'trash') return 3;
    return 0;
}

function get_unread_messages_count($db, $user_id, $user_type) {
    // في الواقع، يجب حساب عدد الرسائل غير المقروءة من قاعدة البيانات
    return 3;
}

// دوال وهمية أخرى من الصفحات السابقة
function get_college_departments($db, $college_id) {
    return [
        ['id' => 1, 'name' => 'قسم علوم الحاسب'],
        ['id' => 2, 'name' => 'قسم نظم المعلومات'],
        ['id' => 3, 'name' => 'قسم هندسة البرمجيات']
    ];
}

function get_college_teachers_list($db, $college_id, $department_id = null) {
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

function get_college_admin_info($db, $admin_id) {
    return [
        'id' => $admin_id,
        'name' => 'د. عبدالله العمري',
        'email' => 'admin@example.com',
        'college_id' => 1,
        'profile_image' => 'assets/images/admin.jpg'
    ];
}

function get_college_info($db, $college_id) {
    return [
        'id' => $college_id,
        'name' => 'كلية علوم الحاسب والمعلومات',
        'code' => 'CS',
        'description' => 'كلية متخصصة في علوم الحاسب وتقنية المعلومات'
    ];
}

// دالة مساعدة لتنسيق التاريخ
function format_date($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return t('not_sent_yet');
    return date($format, strtotime($date));
}

// دالة مساعدة لاقتطاع النص
function truncate_text($text, $length = 100) {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('messages'); ?></title>
    
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
        
        /* تنسيقات خاصة بصفحة الرسائل */
        .messages-container {
            display: flex;
            height: calc(100vh - 200px);
            min-height: 500px;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .messages-sidebar {
            width: 250px;
            background-color: white;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .theme-dark .messages-sidebar {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        [dir="rtl"] .messages-sidebar {
            border-right: none;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark[dir="rtl"] .messages-sidebar {
            border-left-color: rgba(255, 255, 255, 0.1);
        }
        
        .messages-compose {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .messages-compose {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .messages-folders {
            flex: 1;
            overflow-y: auto;
        }
        
        .messages-folders .nav-link {
            padding: 0.75rem 1rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .messages-folders .nav-link {
            border-color: rgba(255, 255, 255, 0.05);
        }
        
        .messages-folders .nav-link.active {
            background-color: rgba(0, 48, 73, 0.05);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .messages-folders .nav-link.active {
            border-left: none;
            border-right: 4px solid var(--primary-color);
        }
        
        .messages-folders .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .messages-folders .nav-link i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .messages-folders .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .messages-list {
            width: 350px;
            background-color: #f8f9fa;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .theme-dark .messages-list {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        [dir="rtl"] .messages-list {
            border-right: none;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark[dir="rtl"] .messages-list {
            border-left-color: rgba(255, 255, 255, 0.1);
        }
        
        .messages-search {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .messages-search {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .messages-items {
            flex: 1;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .theme-dark .message-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-item:hover {
            background-color: rgba(0, 48, 73, 0.03);
        }
        
        .message-item.active {
            background-color: rgba(0, 48, 73, 0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .message-item.active {
            border-left: none;
            border-right: 4px solid var(--primary-color);
        }
        
        .message-item.unread {
            background-color: rgba(102, 155, 188, 0.1);
        }
        
        .message-item.unread .message-subject {
            font-weight: 600;
        }
        
        .message-sender {
            font-weight: 500;
            margin-bottom: 0.25rem;
            display: flex;
            justify-content: space-between;
        }
        
        .message-date {
            font-size: 0.8rem;
            color: var(--gray-color);
        }
        
        .message-subject {
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-preview {
            font-size: 0.85rem;
            color: var(--gray-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-content {
            flex: 1;
            background-color: white;
            display: flex;
            flex-direction: column;
        }
        
        .theme-dark .message-content {
            background-color: var(--dark-bg);
        }
        
        .message-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .message-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .message-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .message-from {
            font-weight: 500;
        }
        
        .message-to {
            color: var(--gray-color);
        }
        
        .message-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .message-body {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            white-space: pre-line;
        }
        
        .message-attachment {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        
        .theme-dark .message-attachment {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-attachment i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .message-attachment i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .message-reply {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .message-reply {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-reply textarea {
            resize: none;
            border-radius: 0.5rem;
        }
        
        .theme-dark .message-reply textarea {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .message-reply-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
        }
        
        .message-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: var(--gray-color);
            text-align: center;
        }
        
        .message-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .compose-modal .modal-body {
            padding: 1.5rem;
        }
        
        .compose-modal .form-group {
            margin-bottom: 1rem;
        }
        
        .compose-modal .form-label {
            font-weight: 500;
        }
        
        .compose-modal textarea {
            resize: none;
            min-height: 200px;
        }
        
        .theme-dark .compose-modal .modal-content {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .theme-dark .compose-modal .modal-header,
        .theme-dark .compose-modal .modal-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .compose-modal .form-control {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        @media (max-width: 992px) {
            .messages-container {
                flex-direction: column;
                height: auto;
            }
            
            .messages-sidebar,
            .messages-list,
            .message-content {
                width: 100%;
                border: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            }
            
            .theme-dark .messages-sidebar,
            .theme-dark .messages-list,
            .theme-dark .message-content {
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            .messages-items,
            .message-body {
                max-height: 300px;
            }
        }
        
        /* تنسيقات إضافية للمظهر الداكن */
        .theme-dark .form-control {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .theme-dark .form-select {
            background-color: var(--dark-bg-alt);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .theme-dark .input-group-text {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .theme-dark .modal-content {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .theme-dark .modal-header,
        .theme-dark .modal-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .close {
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
                    <a class="nav-link" href="college_announcements.php">
                        <i class="fas fa-bullhorn"></i> <?php echo t('announcements'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="college_messages.php">
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
                        <span class="badge bg-success"><?php echo $unread_count; ?></span>
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
            <h1 class="page-title"><?php echo t('messages'); ?></h1>
            <p class="page-subtitle"><?php echo t('manage_messages_and_communication'); ?></p>
        </div>
        
        <!-- رسالة نجاح إرسال الرسالة -->
        <?php if ($message_sent): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('message_sent_successfully'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- رسالة خطأ إرسال الرسالة -->
        <?php if (!empty($message_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $message_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- نظام الرسائل -->
        <div class="messages-container">
            <!-- القائمة الجانبية للرسائل -->
            <div class="messages-sidebar">
                <div class="messages-compose">
                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#composeModal">
                        <i class="fas fa-pen me-2"></i> <?php echo t('compose_message'); ?>
                    </button>
                </div>
                <div class="messages-folders">
                    <a href="?folder=inbox" class="nav-link <?php echo $filter_folder === 'inbox' ? 'active' : ''; ?>">
                        <div>
                            <i class="fas fa-inbox"></i> <?php echo t('inbox'); ?>
                        </div>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?folder=sent" class="nav-link <?php echo $filter_folder === 'sent' ? 'active' : ''; ?>">
                        <div>
                            <i class="fas fa-paper-plane"></i> <?php echo t('sent'); ?>
                        </div>
                    </a>
                    <a href="?folder=drafts" class="nav-link <?php echo $filter_folder === 'drafts' ? 'active' : ''; ?>">
                        <div>
                            <i class="fas fa-save"></i> <?php echo t('drafts'); ?>
                        </div>
                        <?php if (get_total_messages(null, $admin_id, 'college_admin', 'drafts') > 0): ?>
                            <span class="badge bg-secondary rounded-pill"><?php echo get_total_messages(null, $admin_id, 'college_admin', 'drafts'); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?folder=trash" class="nav-link <?php echo $filter_folder === 'trash' ? 'active' : ''; ?>">
                        <div>
                            <i class="fas fa-trash"></i> <?php echo t('trash'); ?>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- قائمة الرسائل -->
            <div class="messages-list">
                <div class="messages-search">
                    <form action="" method="get">
                        <input type="hidden" name="folder" value="<?php echo $filter_folder; ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="<?php echo t('search_messages'); ?>" name="search" value="<?php echo $filter_search; ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="unreadOnly" name="unread_only" value="1" <?php echo $filter_unread_only ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="form-check-label" for="unreadOnly">
                                <?php echo t('show_unread_only'); ?>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="messages-items">
                    <?php if (empty($messages)): ?>
                        <div class="message-empty">
                            <i class="fas fa-inbox"></i>
                            <p><?php echo t('no_messages_found'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $index => $message): ?>
                            <div class="message-item <?php echo !$message['is_read'] ? 'unread' : ''; ?>" data-message-id="<?php echo $message['id']; ?>" onclick="showMessage(<?php echo $index; ?>)">
                                <div class="message-sender">
                                    <?php if ($filter_folder === 'sent' || $filter_folder === 'drafts'): ?>
                                        <span><?php echo t('to'); ?>: <?php echo $message['recipient_name']; ?></span>
                                    <?php else: ?>
                                        <span><?php echo $message['sender_name']; ?></span>
                                    <?php endif; ?>
                                    <span class="message-date"><?php echo format_date($message['date_sent'], 'd/m'); ?></span>
                                </div>
                                <div class="message-subject">
                                    <?php echo $message['subject']; ?>
                                    <?php if ($message['has_attachment']): ?>
                                        <i class="fas fa-paperclip ms-1 text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="message-preview">
                                    <?php echo truncate_text(strip_tags($message['content']), 50); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- ترقيم الصفحات -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center p-2 border-top">
                        <nav aria-label="<?php echo t('message_pagination'); ?>">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?folder=<?php echo $filter_folder; ?>&search=<?php echo $filter_search; ?>&unread_only=<?php echo $filter_unread_only ? '1' : '0'; ?>&page=<?php echo $page - 1; ?>" aria-label="<?php echo t('previous'); ?>">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?folder=<?php echo $filter_folder; ?>&search=<?php echo $filter_search; ?>&unread_only=<?php echo $filter_unread_only ? '1' : '0'; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?folder=<?php echo $filter_folder; ?>&search=<?php echo $filter_search; ?>&unread_only=<?php echo $filter_unread_only ? '1' : '0'; ?>&page=<?php echo $page + 1; ?>" aria-label="<?php echo t('next'); ?>">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- محتوى الرسالة -->
            <div class="message-content" id="messageContent">
                <!-- سيتم تحميل محتوى الرسالة هنا عبر JavaScript -->
                <div class="message-empty">
                    <i class="fas fa-envelope-open"></i>
                    <p><?php echo t('select_message_to_view'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- نموذج إنشاء رسالة جديدة -->
    <div class="modal fade compose-modal" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="composeModalLabel"><?php echo t('compose_new_message'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="send_message">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="recipient_type" class="form-label"><?php echo t('recipient_type'); ?></label>
                                <select class="form-select" id="recipient_type" name="recipient_type" required>
                                    <option value=""><?php echo t('select_recipient_type'); ?></option>
                                    <option value="student"><?php echo t('student'); ?></option>
                                    <option value="teacher"><?php echo t('teacher'); ?></option>
                                    <option value="department"><?php echo t('department'); ?></option>
                                    <option value="all_students"><?php echo t('all_students'); ?></option>
                                    <option value="all_teachers"><?php echo t('all_teachers'); ?></option>
                                    <option value="all"><?php echo t('all_college_members'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="recipient_id" class="form-label"><?php echo t('recipient'); ?></label>
                                <select class="form-select" id="recipient_id" name="recipient_id" disabled>
                                    <option value=""><?php echo t('select_recipient_type_first'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label"><?php echo t('subject'); ?></label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message_content" class="form-label"><?php echo t('message'); ?></label>
                            <textarea class="form-control" id="message_content" name="message_content" rows="10" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="attachment" class="form-label"><?php echo t('attachment'); ?> (<?php echo t('optional'); ?>)</label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text"><?php echo t('max_file_size'); ?>: 5MB</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('send_message'); ?></button>
                        <button type="button" class="btn btn-outline-primary" id="saveDraft"><?php echo t('save_as_draft'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- نموذج حذف الرسالة -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel"><?php echo t('confirm_delete'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo t('confirm_delete_message'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="message_id" id="deleteMessageId" value="">
                        <button type="submit" class="btn btn-danger"><?php echo t('delete'); ?></button>
                    </form>
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
        // بيانات الرسائل
        const messages = <?php echo json_encode($messages); ?>;
        const currentFolder = '<?php echo $filter_folder; ?>';
        
        // عرض الرسالة المحددة
        function showMessage(index) {
            const message = messages[index];
            
            // تحديد الرسالة النشطة في القائمة
            document.querySelectorAll('.message-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`.message-item[data-message-id="${message.id}"]`).classList.add('active');
            
            // إذا كانت الرسالة غير مقروءة، وضع علامة "مقروءة"
            if (!message.is_read && currentFolder === 'inbox') {
                // إرسال طلب AJAX لوضع علامة "مقروءة"
                fetch('college_messages.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_as_read&message_id=${message.id}`
                });
                
                // تحديث واجهة المستخدم
                document.querySelector(`.message-item[data-message-id="${message.id}"]`).classList.remove('unread');
                message.is_read = true;
            }
            
            // إنشاء محتوى الرسالة
            let content = '';
            
            if (currentFolder === 'drafts') {
                // محتوى مسودة الرسالة (مع زر التحرير)
                content = `
                    <div class="message-header">
                        <div class="message-title">${message.subject}</div>
                        <div class="message-info">
                            <div>
                                <div class="message-to"><strong>${t('to')}:</strong> ${message.recipient_name}</div>
                                <div class="message-date"><strong>${t('date')}:</strong> ${message.date_sent ? formatDate(message.date_sent) : t('not_sent_yet')}</div>
                            </div>
                        </div>
                        <div class="message-actions">
                            <button class="btn btn-primary" onclick="editDraft(${message.id})">
                                <i class="fas fa-edit me-1"></i> ${t('edit_draft')}
                            </button>
                            <button class="btn btn-danger" onclick="confirmDelete(${message.id})">
                                <i class="fas fa-trash me-1"></i> ${t('delete')}
                            </button>
                        </div>
                    </div>
                    <div class="message-body">
                        ${message.content}
                    </div>
                `;
            } else if (currentFolder === 'sent') {
                // محتوى الرسالة المرسلة
                content = `
                    <div class="message-header">
                        <div class="message-title">${message.subject}</div>
                        <div class="message-info">
                            <div>
                                <div class="message-to"><strong>${t('to')}:</strong> ${message.recipient_name}</div>
                                <div class="message-date"><strong>${t('date')}:</strong> ${formatDate(message.date_sent)}</div>
                            </div>
                        </div>
                        <div class="message-actions">
                            <button class="btn btn-primary" onclick="forwardMessage(${message.id})">
                                <i class="fas fa-share me-1"></i> ${t('forward')}
                            </button>
                            <button class="btn btn-danger" onclick="confirmDelete(${message.id})">
                                <i class="fas fa-trash me-1"></i> ${t('delete')}
                            </button>
                        </div>
                    </div>
                    <div class="message-body">
                        ${message.content}
                    </div>
                    ${message.has_attachment ? `
                        <div class="message-attachment">
                            <i class="fas fa-paperclip"></i>
                            <a href="download_attachment.php?message_id=${message.id}" target="_blank">${message.attachment_name}</a>
                        </div>
                    ` : ''}
                `;
            } else {
                // محتوى الرسالة الواردة أو المحذوفة
                content = `
                    <div class="message-header">
                        <div class="message-title">${message.subject}</div>
                        <div class="message-info">
                            <div>
                                <div class="message-from"><strong>${t('from')}:</strong> ${message.sender_name}</div>
                                <div class="message-date"><strong>${t('date')}:</strong> ${formatDate(message.date_sent)}</div>
                            </div>
                        </div>
                        <div class="message-actions">
                            ${currentFolder !== 'trash' ? `
                                <button class="btn btn-primary" onclick="replyMessage(${message.id})">
                                    <i class="fas fa-reply me-1"></i> ${t('reply')}
                                </button>
                                <button class="btn btn-outline-primary" onclick="forwardMessage(${message.id})">
                                    <i class="fas fa-share me-1"></i> ${t('forward')}
                                </button>
                            ` : ''}
                            <button class="btn btn-danger" onclick="confirmDelete(${message.id})">
                                <i class="fas fa-trash me-1"></i> ${t('delete')}
                            </button>
                        </div>
                    </div>
                    <div class="message-body">
                        ${message.content}
                    </div>
                    ${message.has_attachment ? `
                        <div class="message-attachment">
                            <i class="fas fa-paperclip"></i>
                            <a href="download_attachment.php?message_id=${message.id}" target="_blank">${message.attachment_name}</a>
                        </div>
                    ` : ''}
                    ${currentFolder !== 'trash' ? `
                        <div class="message-reply">
                            <textarea class="form-control" placeholder="${t('write_reply')}" id="replyText"></textarea>
                            <div class="message-reply-actions">
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                </div>
                                <button class="btn btn-primary" onclick="sendReply(${message.id})">
                                    <i class="fas fa-paper-plane me-1"></i> ${t('send')}
                                </button>
                            </div>
                        </div>
                    ` : ''}
                `;
            }
            
            // تحديث محتوى الرسالة
            document.getElementById('messageContent').innerHTML = content;
        }
        
        // تأكيد حذف الرسالة
        function confirmDelete(messageId) {
            document.getElementById('deleteMessageId').value = messageId;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
        
        // الرد على الرسالة
        function replyMessage(messageId) {
            document.getElementById('replyText').focus();
        }
        
        // إرسال الرد
        function sendReply(messageId) {
            const replyText = document.getElementById('replyText').value;
            if (replyText.trim() === '') {
                alert(t('reply_cannot_be_empty'));
                return;
            }
            
            // في الواقع، يجب إرسال الرد عبر AJAX
            alert(t('reply_sent'));
            document.getElementById('replyText').value = '';
        }
        
        // إعادة توجيه الرسالة
        function forwardMessage(messageId) {
            // فتح نموذج إنشاء رسالة جديدة مع محتوى الرسالة المحددة
            const message = messages.find(m => m.id === messageId);
            if (!message) return;
            
            const composeModal = new bootstrap.Modal(document.getElementById('composeModal'));
            composeModal.show();
            
            // ملء النموذج بمحتوى الرسالة المعاد توجيهها
            document.getElementById('subject').value = `FW: ${message.subject}`;
            document.getElementById('message_content').value = `\n\n-------- ${t('forwarded_message')} --------\n${t('from')}: ${message.sender_name}\n${t('date')}: ${formatDate(message.date_sent)}\n${t('subject')}: ${message.subject}\n\n${message.content}`;
        }
        
        // تحرير مسودة الرسالة
        function editDraft(messageId) {
            // فتح نموذج إنشاء رسالة جديدة مع محتوى المسودة
            const message = messages.find(m => m.id === messageId);
            if (!message) return;
            
            const composeModal = new bootstrap.Modal(document.getElementById('composeModal'));
            composeModal.show();
            
            // ملء النموذج بمحتوى المسودة
            document.getElementById('recipient_type').value = message.recipient_type;
            updateRecipients();
            setTimeout(() => {
                document.getElementById('recipient_id').value = message.recipient_id;
                document.getElementById('subject').value = message.subject;
                document.getElementById('message_content').value = message.content;
            }, 500);
        }
        
        // تحديث قائمة المستلمين بناءً على نوع المستلم
        function updateRecipients() {
            const recipientType = document.getElementById('recipient_type').value;
            const recipientIdSelect = document.getElementById('recipient_id');
            
            // تفريغ القائمة
            recipientIdSelect.innerHTML = '';
            
            if (recipientType === '') {
                recipientIdSelect.disabled = true;
                recipientIdSelect.innerHTML = `<option value="">${t('select_recipient_type_first')}</option>`;
                return;
            }
            
            recipientIdSelect.disabled = false;
            
            if (recipientType === 'student') {
                // إضافة الطلاب
                const students = <?php echo json_encode($students); ?>;
                recipientIdSelect.innerHTML = `<option value="">${t('select_student')}</option>`;
                students.forEach(student => {
                    recipientIdSelect.innerHTML += `<option value="${student.id}">${student.name}</option>`;
                });
            } else if (recipientType === 'teacher') {
                // إضافة المعلمين
                const teachers = <?php echo json_encode($teachers); ?>;
                recipientIdSelect.innerHTML = `<option value="">${t('select_teacher')}</option>`;
                teachers.forEach(teacher => {
                    recipientIdSelect.innerHTML += `<option value="${teacher.id}">${teacher.name}</option>`;
                });
            } else if (recipientType === 'department') {
                // إضافة الأقسام
                const departments = <?php echo json_encode($departments); ?>;
                recipientIdSelect.innerHTML = `<option value="">${t('select_department')}</option>`;
                departments.forEach(department => {
                    recipientIdSelect.innerHTML += `<option value="${department.id}">${department.name}</option>`;
                });
            } else {
                // للمستلمين الجماعيين (جميع الطلاب، جميع المعلمين، الجميع)
                recipientIdSelect.innerHTML = `<option value="0">${t('all_selected')}</option>`;
                recipientIdSelect.value = '0';
            }
        }
        
        // حفظ الرسالة كمسودة
        document.getElementById('saveDraft').addEventListener('click', function() {
            // في الواقع، يجب حفظ المسودة عبر AJAX
            alert(t('draft_saved'));
            const composeModal = bootstrap.Modal.getInstance(document.getElementById('composeModal'));
            composeModal.hide();
        });
        
        // تنسيق التاريخ
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString();
        }
        
        // ترجمة النصوص
        function t(key) {
            const translations = {
                'to': '<?php echo t('to'); ?>',
                'from': '<?php echo t('from'); ?>',
                'date': '<?php echo t('date'); ?>',
                'not_sent_yet': '<?php echo t('not_sent_yet'); ?>',
                'edit_draft': '<?php echo t('edit_draft'); ?>',
                'delete': '<?php echo t('delete'); ?>',
                'forward': '<?php echo t('forward'); ?>',
                'reply': '<?php echo t('reply'); ?>',
                'send': '<?php echo t('send'); ?>',
                'write_reply': '<?php echo t('write_reply'); ?>',
                'reply_cannot_be_empty': '<?php echo t('reply_cannot_be_empty'); ?>',
                'reply_sent': '<?php echo t('reply_sent'); ?>',
                'forwarded_message': '<?php echo t('forwarded_message'); ?>',
                'subject': '<?php echo t('subject'); ?>',
                'select_recipient_type_first': '<?php echo t('select_recipient_type_first'); ?>',
                'select_student': '<?php echo t('select_student'); ?>',
                'select_teacher': '<?php echo t('select_teacher'); ?>',
                'select_department': '<?php echo t('select_department'); ?>',
                'all_selected': '<?php echo t('all_selected'); ?>',
                'draft_saved': '<?php echo t('draft_saved'); ?>'
            };
            return translations[key] || key;
        }
        
        // تحديث قائمة المستلمين عند تغيير نوع المستلم
        document.getElementById('recipient_type').addEventListener('change', updateRecipients);
        
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
        
        // عرض أول رسالة عند تحميل الصفحة (إذا كانت هناك رسائل)
        document.addEventListener('DOMContentLoaded', function() {
            if (messages.length > 0) {
                showMessage(0);
            }
        });
    </script>
</body>
</html>
