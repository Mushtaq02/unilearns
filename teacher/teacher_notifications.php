<?php
/**
 * صفحة الإشعارات للمعلم في نظام UniverBoard
 * تتيح للمعلم عرض وإدارة الإشعارات الخاصة به
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول المعلم
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    header('Location: ../login.php');
    exit;
}

// الحصول على معلومات المعلم
$teacher_id = $_SESSION['user_id'];
$db = get_db_connection();
$teacher = get_teacher_info($db, $teacher_id);

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

// معالجة تحديث حالة الإشعارات
if (isset($_POST['mark_all_read'])) {
    // تحديث جميع الإشعارات كمقروءة
    mark_all_notifications_as_read($db, $teacher_id);
    
    // إعادة توجيه لتحديث الصفحة
    header('Location: teacher_notifications.php');
    exit;
}

if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    // تحديث إشعار محدد كمقروء
    $notification_id = filter_input(INPUT_POST, 'notification_id', FILTER_SANITIZE_NUMBER_INT);
    mark_notification_as_read($db, $notification_id, $teacher_id);
    
    // إعادة توجيه لتحديث الصفحة
    header('Location: teacher_notifications.php');
    exit;
}

if (isset($_POST['delete']) && isset($_POST['notification_id'])) {
    // حذف إشعار محدد
    $notification_id = filter_input(INPUT_POST, 'notification_id', FILTER_SANITIZE_NUMBER_INT);
    delete_notification($db, $notification_id, $teacher_id);
    
    // إعادة توجيه لتحديث الصفحة
    header('Location: teacher_notifications.php');
    exit;
}

// الحصول على الإشعارات
$notifications = get_teacher_notifications($db, $teacher_id);

// تصنيف الإشعارات
$unread_notifications = [];
$read_notifications = [];

foreach ($notifications as $notification) {
    if ($notification['is_read']) {
        $read_notifications[] = $notification;
    } else {
        $unread_notifications[] = $notification;
    }
}

// إغلاق اتصال قاعدة البيانات
$dsn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('notifications'); ?></title>
    
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
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
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
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }
        
        .theme-dark .empty-state {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: var(--gray-color);
            margin-bottom: 1.5rem;
        }
        
        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .empty-state-text {
            color: var(--gray-color);
            margin-bottom: 1.5rem;
        }
        
        .notification-list {
            margin-bottom: 2rem;
        }
        
        .notification-item {
            display: flex;
            padding: 1.25rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .theme-dark .notification-item {
            background-color: var(--dark-bg);
        }
        
        .notification-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .notification-item.unread {
            border-right: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .notification-item.unread {
            border-right: none;
            border-left: 4px solid var(--primary-color);
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.25rem;
            flex-shrink: 0;
        }
        
        [dir="rtl"] .notification-icon {
            margin-right: 0;
            margin-left: 1.25rem;
        }
        
        .notification-icon.assignment {
            background-color: #28a745;
        }
        
        .notification-icon.exam {
            background-color: #dc3545;
        }
        
        .notification-icon.course {
            background-color: #17a2b8;
        }
        
        .notification-icon.system {
            background-color: #6c757d;
        }
        
        .notification-icon.message {
            background-color: #ffc107;
        }
        
        .notification-content {
            flex-grow: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }
        
        .notification-text {
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        
        .notification-meta {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .notification-time {
            display: flex;
            align-items: center;
        }
        
        .notification-time i {
            margin-right: 0.5rem;
            opacity: 0.7;
        }
        
        [dir="rtl"] .notification-time i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .notification-actions {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
        }
        
        [dir="rtl"] .notification-actions {
            right: auto;
            left: 1.25rem;
        }
        
        .notification-actions .dropdown-toggle {
            background: none;
            border: none;
            color: var(--gray-color);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0;
        }
        
        .notification-actions .dropdown-toggle::after {
            display: none;
        }
        
        .notification-actions .dropdown-menu {
            min-width: 200px;
        }
        
        .notification-actions .dropdown-item {
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
        }
        
        .notification-actions .dropdown-item i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .notification-actions .dropdown-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .notification-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.75rem;
        }
        
        [dir="rtl"] .notification-badge {
            margin-left: 0;
            margin-right: 0.75rem;
        }
        
        .notification-badge.assignment {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .notification-badge.exam {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .notification-badge.course {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        .notification-badge.system {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .notification-badge.message {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .notification-title-section h4 {
            margin-bottom: 0.25rem;
        }
        
        .notification-title-section p {
            color: var(--gray-color);
            margin-bottom: 0;
        }
        
        .notification-actions-section {
            display: flex;
            gap: 0.5rem;
        }
        
        .notification-filter {
            display: flex;
            margin-bottom: 1.5rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .theme-dark .notification-filter {
            background-color: var(--dark-bg);
        }
        
        .notification-filter-item {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 3px solid transparent;
        }
        
        .notification-filter-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .notification-filter-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .notification-filter-item.active {
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        
        .notification-filter-item i {
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .notification-filter-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .notification-count {
            display: inline-block;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 0.5rem;
        }
        
        [dir="rtl"] .notification-count {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        
        .notification-section {
            margin-bottom: 2rem;
        }
        
        .notification-section-title {
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .notification-section-title {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .notification-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .notification-link:hover {
            text-decoration: underline;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination .page-item {
            margin: 0 0.25rem;
        }
        
        .pagination .page-link {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--text-color);
            background-color: white;
            text-decoration: none;
        }
        
        .theme-dark .pagination .page-link {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .pagination .page-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .pagination .page-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--gray-color);
            pointer-events: none;
            cursor: default;
        }
    </style>
</head>
<body class="theme-<?php echo $theme; ?>">
    <!-- القائمة الجانبية -->
    <nav class="sidebar bg-white">
        <div class="sidebar-sticky">
            <div class="sidebar-logo">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link" href="teacher_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_courses.php">
                        <i class="fas fa-book"></i> <?php echo t('my_courses'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_assignments.php">
                        <i class="fas fa-tasks"></i> <?php echo t('assignments'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_exams.php">
                        <i class="fas fa-file-alt"></i> <?php echo t('exams'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_grades.php">
                        <i class="fas fa-chart-line"></i> <?php echo t('grades'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_schedule.php">
                        <i class="fas fa-calendar-alt"></i> <?php echo t('schedule'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_students.php">
                        <i class="fas fa-user-graduate"></i> <?php echo t('students'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('communication'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="teacher_messages.php">
                        <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="teacher_notifications.php">
                        <i class="fas fa-bell"></i> <?php echo t('notifications'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_forums.php">
                        <i class="fas fa-comments"></i> <?php echo t('forums'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('account'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="teacher_profile.php">
                        <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_settings.php">
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
                        <span class="badge bg-danger"><?php echo count($unread_notifications); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                        <div class="dropdown-header"><?php echo t('notifications'); ?></div>
                        <div class="dropdown-divider"></div>
                        <?php if (count($unread_notifications) > 0): ?>
                            <?php foreach (array_slice($unread_notifications, 0, 3) as $notification): ?>
                                <a class="dropdown-item" href="#">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar avatar-sm bg-primary text-white rounded-circle">
                                                <i class="fas fa-<?php echo $notification['icon']; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0"><?php echo $notification['title']; ?></p>
                                            <small class="text-muted"><?php echo $notification['time_ago']; ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="mb-0"><?php echo t('no_new_notifications'); ?></p>
                                    </div>
                                </div>
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="teacher_notifications.php"><?php echo t('view_all_notifications'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">2</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesDropdown">
                        <div class="dropdown-header"><?php echo t('messages'); ?></div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/student1.jpg" alt="Student" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">سارة أحمد</p>
                                    <small class="text-muted">هل يمكنني الحصول على مساعدة في المشروع النهائي؟</small>
                                    <small class="text-muted d-block">منذ 15 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/student2.jpg" alt="Student" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <p class="mb-1">محمد علي</p>
                                    <small class="text-muted">أستاذ، هل يمكنني تأجيل موعد تسليم الواجب؟</small>
                                    <small class="text-muted d-block">منذ ساعة</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="teacher_messages.php"><?php echo t('view_all_messages'); ?></a>
                    </div>
                </li>
                
                <li class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $teacher['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $teacher['name']; ?>">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?php echo $teacher['name']; ?></h6>
                            <small><?php echo $teacher['title']; ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="teacher_profile.php">
                            <i class="fas fa-user"></i> <?php echo t('profile'); ?>
                        </a>
                        <a class="dropdown-item" href="teacher_settings.php">
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
        <div class="notification-header mt-4 mb-4">
            <div class="notification-title-section">
                <h1 class="h3"><?php echo t('notifications'); ?></h1>
                <p class="text-muted"><?php echo t('manage_your_notifications'); ?></p>
            </div>
            <div class="notification-actions-section">
                <form action="" method="post">
                    <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
                        <i class="fas fa-check-double me-1"></i> <?php echo t('mark_all_as_read'); ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- فلتر الإشعارات -->
        <div class="notification-filter">
            <div class="notification-filter-item active" data-filter="all">
                <i class="fas fa-bell"></i> <?php echo t('all'); ?>
                <span class="notification-count"><?php echo count($notifications); ?></span>
            </div>
            <div class="notification-filter-item" data-filter="unread">
                <i class="fas fa-envelope"></i> <?php echo t('unread'); ?>
                <span class="notification-count"><?php echo count($unread_notifications); ?></span>
            </div>
            <div class="notification-filter-item" data-filter="assignment">
                <i class="fas fa-tasks"></i> <?php echo t('assignments'); ?>
            </div>
            <div class="notification-filter-item" data-filter="exam">
                <i class="fas fa-file-alt"></i> <?php echo t('exams'); ?>
            </div>
            <div class="notification-filter-item" data-filter="course">
                <i class="fas fa-book"></i> <?php echo t('courses'); ?>
            </div>
            <div class="notification-filter-item" data-filter="system">
                <i class="fas fa-cog"></i> <?php echo t('system'); ?>
            </div>
        </div>
        
        <?php if (count($unread_notifications) > 0): ?>
            <!-- الإشعارات غير المقروءة -->
            <div class="notification-section" id="unread-notifications">
                <h4 class="notification-section-title"><?php echo t('unread_notifications'); ?></h4>
                <div class="notification-list">
                    <?php foreach ($unread_notifications as $notification): ?>
                        <div class="notification-item unread" data-type="<?php echo $notification['type']; ?>">
                            <div class="notification-icon <?php echo $notification['type']; ?>">
                                <i class="fas fa-<?php echo $notification['icon']; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <h5 class="notification-title"><?php echo $notification['title']; ?></h5>
                                <p class="notification-text"><?php echo $notification['content']; ?></p>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i> <?php echo $notification['time_ago']; ?>
                                    </div>
                                    <span class="notification-badge <?php echo $notification['type']; ?>"><?php echo t($notification['type']); ?></span>
                                </div>
                                <?php if (!empty($notification['link'])): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo $notification['link']; ?>" class="notification-link"><?php echo t('view_details'); ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="notification-actions">
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button" id="notificationActions<?php echo $notification['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="notificationActions<?php echo $notification['id']; ?>">
                                        <li>
                                            <form action="" method="post">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="mark_read" class="dropdown-item">
                                                    <i class="fas fa-check"></i> <?php echo t('mark_as_read'); ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="" method="post">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="delete" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt"></i> <?php echo t('delete'); ?>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (count($read_notifications) > 0): ?>
            <!-- الإشعارات المقروءة -->
            <div class="notification-section" id="read-notifications">
                <h4 class="notification-section-title"><?php echo t('read_notifications'); ?></h4>
                <div class="notification-list">
                    <?php foreach ($read_notifications as $notification): ?>
                        <div class="notification-item" data-type="<?php echo $notification['type']; ?>">
                            <div class="notification-icon <?php echo $notification['type']; ?>">
                                <i class="fas fa-<?php echo $notification['icon']; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <h5 class="notification-title"><?php echo $notification['title']; ?></h5>
                                <p class="notification-text"><?php echo $notification['content']; ?></p>
                                <div class="notification-meta">
                                    <div class="notification-time">
                                        <i class="fas fa-clock"></i> <?php echo $notification['time_ago']; ?>
                                    </div>
                                    <span class="notification-badge <?php echo $notification['type']; ?>"><?php echo t($notification['type']); ?></span>
                                </div>
                                <?php if (!empty($notification['link'])): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo $notification['link']; ?>" class="notification-link"><?php echo t('view_details'); ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="notification-actions">
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button" id="notificationActions<?php echo $notification['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="notificationActions<?php echo $notification['id']; ?>">
                                        <li>
                                            <form action="" method="post">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="delete" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt"></i> <?php echo t('delete'); ?>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (count($notifications) === 0): ?>
            <!-- حالة عدم وجود إشعارات -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3 class="empty-state-title"><?php echo t('no_notifications'); ?></h3>
                <p class="empty-state-text"><?php echo t('no_notifications_message'); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- ترقيم الصفحات -->
        <?php if (count($notifications) > 10): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?php echo t('previous'); ?></a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#"><?php echo t('next'); ?></a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
            
            // فلترة الإشعارات
            const filterItems = document.querySelectorAll('.notification-filter-item');
            const notificationItems = document.querySelectorAll('.notification-item');
            
            filterItems.forEach(item => {
                item.addEventListener('click', function() {
                    // إزالة الفئة النشطة من جميع عناصر الفلتر
                    filterItems.forEach(filterItem => {
                        filterItem.classList.remove('active');
                    });
                    
                    // إضافة الفئة النشطة إلى العنصر المحدد
                    this.classList.add('active');
                    
                    // الحصول على نوع الفلتر
                    const filter = this.getAttribute('data-filter');
                    
                    // فلترة الإشعارات
                    notificationItems.forEach(notification => {
                        if (filter === 'all') {
                            notification.style.display = 'flex';
                        } else if (filter === 'unread') {
                            if (notification.classList.contains('unread')) {
                                notification.style.display = 'flex';
                            } else {
                                notification.style.display = 'none';
                            }
                        } else {
                            if (notification.getAttribute('data-type') === filter) {
                                notification.style.display = 'flex';
                            } else {
                                notification.style.display = 'none';
                            }
                        }
                    });
                    
                    // إظهار أو إخفاء أقسام الإشعارات
                    const unreadSection = document.getElementById('unread-notifications');
                    const readSection = document.getElementById('read-notifications');
                    
                    if (filter === 'unread') {
                        if (unreadSection) unreadSection.style.display = 'block';
                        if (readSection) readSection.style.display = 'none';
                    } else {
                        if (unreadSection) unreadSection.style.display = 'block';
                        if (readSection) readSection.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>
