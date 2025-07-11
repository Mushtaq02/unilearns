<?php
/**
 * صفحة المنتدى للمعلم في نظام UniverBoard
 * تتيح للمعلم عرض وإدارة المنتديات الخاصة بمقرراته
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

// معالجة إنشاء منتدى جديد
if (isset($_POST['create_forum'])) {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البيانات
    if (!empty($course_id) && !empty($title) && !empty($description)) {
        // إنشاء منتدى جديد
        $result = create_forum($db, $course_id, $teacher_id, $title, $description);
        
        if ($result) {
            $success_message = t('forum_created_successfully');
        } else {
            $error_message = t('failed_to_create_forum');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// معالجة إنشاء موضوع جديد
if (isset($_POST['create_topic'])) {
    $forum_id = filter_input(INPUT_POST, 'forum_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البيانات
    if (!empty($forum_id) && !empty($title) && !empty($content)) {
        // إنشاء موضوع جديد
        $result = create_topic($db, $forum_id, $teacher_id, $title, $content);
        
        if ($result) {
            $success_message = t('topic_created_successfully');
        } else {
            $error_message = t('failed_to_create_topic');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// معالجة إضافة رد جديد
if (isset($_POST['add_reply'])) {
    $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_SANITIZE_NUMBER_INT);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البيانات
    if (!empty($topic_id) && !empty($content)) {
        // إضافة رد جديد
        $result = add_reply($db, $topic_id, $teacher_id, $content);
        
        if ($result) {
            $success_message = t('reply_added_successfully');
        } else {
            $error_message = t('failed_to_add_reply');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// الحصول على المقررات التي يدرسها المعلم
$courses = get_teacher_courses($db, $teacher_id);

// الحصول على المنتديات
$forums = [];
foreach ($courses as $course) {
    $course_forums = get_course_forums($db, $course['id']);
    $forums = array_merge($forums, $course_forums);
}

// الحصول على المواضيع والردود إذا تم تحديد منتدى
$selected_forum = null;
$topics = [];
$selected_topic = null;
$replies = [];

if (isset($_GET['forum_id'])) {
    $forum_id = filter_input(INPUT_GET, 'forum_id', FILTER_SANITIZE_NUMBER_INT);
    $selected_forum = get_forum_info($db, $forum_id);
    $topics = get_forum_topics($db, $forum_id);
    
    if (isset($_GET['topic_id'])) {
        $topic_id = filter_input(INPUT_GET, 'topic_id', FILTER_SANITIZE_NUMBER_INT);
        $selected_topic = get_topic_info($db, $topic_id);
        $replies = get_topic_replies($db, $topic_id);
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
    <title><?php echo SITE_NAME; ?> - <?php echo t('forums'); ?></title>
    
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
        
        .forum-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .theme-dark .forum-container {
            background-color: var(--dark-bg);
        }
        
        .forum-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .forum-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .forum-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .forum-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .forum-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .forum-item {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .theme-dark .forum-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .forum-item:last-child {
            border-bottom: none;
        }
        
        .forum-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .forum-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .forum-icon {
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
        
        [dir="rtl"] .forum-icon {
            margin-right: 0;
            margin-left: 1.25rem;
        }
        
        .forum-content {
            flex-grow: 1;
        }
        
        .forum-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }
        
        .forum-item-title a {
            color: var(--text-color);
            text-decoration: none;
        }
        
        .forum-item-title a:hover {
            color: var(--primary-color);
        }
        
        .forum-item-description {
            color: var(--gray-color);
            margin-bottom: 0.5rem;
        }
        
        .forum-item-meta {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .forum-item-meta-item {
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .forum-item-meta-item {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .forum-item-meta-item i {
            margin-right: 0.5rem;
            opacity: 0.7;
        }
        
        [dir="rtl"] .forum-item-meta-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .forum-item-stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 1.25rem;
            min-width: 80px;
        }
        
        [dir="rtl"] .forum-item-stats {
            margin-left: 0;
            margin-right: 1.25rem;
        }
        
        .forum-item-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .forum-item-stat:last-child {
            margin-bottom: 0;
        }
        
        .forum-item-stat-value {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .forum-item-stat-label {
            font-size: 0.75rem;
            color: var(--gray-color);
        }
        
        .forum-breadcrumb {
            margin-bottom: 1.5rem;
        }
        
        .forum-breadcrumb .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .forum-breadcrumb .breadcrumb-item.active {
            color: var(--text-color);
        }
        
        .topic-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .topic-item {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .theme-dark .topic-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .topic-item:last-child {
            border-bottom: none;
        }
        
        .topic-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .topic-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .topic-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        [dir="rtl"] .topic-icon {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .topic-content {
            flex-grow: 1;
        }
        
        .topic-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }
        
        .topic-item-title a {
            color: var(--text-color);
            text-decoration: none;
        }
        
        .topic-item-title a:hover {
            color: var(--primary-color);
        }
        
        .topic-item-meta {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .topic-item-meta-item {
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .topic-item-meta-item {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .topic-item-meta-item i {
            margin-right: 0.5rem;
            opacity: 0.7;
        }
        
        [dir="rtl"] .topic-item-meta-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .topic-item-stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 1rem;
            min-width: 60px;
        }
        
        [dir="rtl"] .topic-item-stats {
            margin-left: 0;
            margin-right: 1rem;
        }
        
        .topic-item-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .topic-item-stat:last-child {
            margin-bottom: 0;
        }
        
        .topic-item-stat-value {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .topic-item-stat-label {
            font-size: 0.75rem;
            color: var(--gray-color);
        }
        
        .topic-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .theme-dark .topic-container {
            background-color: var(--dark-bg);
        }
        
        .topic-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .topic-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .topic-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .topic-meta {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .topic-meta-item {
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }
        
        [dir="rtl"] .topic-meta-item {
            margin-right: 0;
            margin-left: 1rem;
        }
        
        .topic-meta-item i {
            margin-right: 0.5rem;
            opacity: 0.7;
        }
        
        [dir="rtl"] .topic-meta-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .post-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .post-item {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
        }
        
        .theme-dark .post-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-author {
            width: 150px;
            padding-right: 1.5rem;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        [dir="rtl"] .post-author {
            padding-right: 0;
            padding-left: 1.5rem;
            border-right: none;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .post-author {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .post-author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.75rem;
        }
        
        .post-author-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .post-author-role {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-bottom: 0.75rem;
        }
        
        .post-author-stats {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .post-content {
            flex-grow: 1;
            padding-left: 1.5rem;
        }
        
        [dir="rtl"] .post-content {
            padding-left: 0;
            padding-right: 1.5rem;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .post-meta {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .post-date {
            font-size: 0.85rem;
            color: var(--gray-color);
        }
        
        .post-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .post-actions button {
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .post-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .post-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .post-text {
            line-height: 1.6;
        }
        
        .reply-form {
            padding: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .reply-form {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .reply-form-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .reply-form-textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            resize: none;
            margin-bottom: 1rem;
            min-height: 150px;
        }
        
        .theme-dark .reply-form-textarea {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .reply-form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .reply-form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .reply-form-attachments {
            display: flex;
            gap: 0.5rem;
        }
        
        .reply-form-attachments button {
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .reply-form-attachments button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .reply-form-attachments button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .create-forum-form {
            padding: 1.5rem;
        }
        
        .create-forum-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .create-forum-group {
            margin-bottom: 1rem;
        }
        
        .create-forum-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .create-forum-input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .create-forum-input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .create-forum-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .create-forum-textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            resize: none;
            min-height: 150px;
        }
        
        .theme-dark .create-forum-textarea {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .create-forum-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .create-forum-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        
        .create-topic-form {
            padding: 1.5rem;
        }
        
        .create-topic-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .create-topic-group {
            margin-bottom: 1rem;
        }
        
        .create-topic-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .create-topic-input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .create-topic-input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .create-topic-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .create-topic-textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            resize: none;
            min-height: 200px;
        }
        
        .theme-dark .create-topic-textarea {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .create-topic-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .create-topic-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
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
        
        .forum-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .theme-dark .forum-tabs {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .forum-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            color: var(--gray-color);
            border-bottom: 3px solid transparent;
        }
        
        .forum-tab:hover {
            color: var(--text-color);
        }
        
        .forum-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .forum-tab-content {
            display: none;
        }
        
        .forum-tab-content.active {
            display: block;
        }
        
        .forum-search {
            margin-bottom: 1.5rem;
        }
        
        .forum-search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        
        .theme-dark .forum-search-input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .forum-search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .forum-filter {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .forum-filter-item {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: white;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .theme-dark .forum-filter-item {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .forum-filter-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .forum-filter-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .forum-filter-item.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .forum-sort {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .forum-sort-select {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: white;
            color: var(--text-color);
        }
        
        .theme-dark .forum-sort-select {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .forum-sort-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .topic-tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        [dir="rtl"] .topic-tag {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .topic-tag.announcement {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .topic-tag.question {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .topic-tag.discussion {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        .topic-tag.solved {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .topic-tag.closed {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .topic-tag.pinned {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
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
                    <a class="nav-link" href="teacher_notifications.php">
                        <i class="fas fa-bell"></i> <?php echo t('notifications'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="teacher_forums.php">
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
                        <span class="badge bg-danger">3</span>
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
                                    <p class="mb-0">قام الطالب أحمد محمد بتسليم واجب جديد</p>
                                    <small class="text-muted">منذ 10 دقائق</small>
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
                                    <p class="mb-0">تذكير: موعد محاضرة برمجة الويب غداً</p>
                                    <small class="text-muted">منذ 30 دقيقة</small>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-sm bg-info text-white rounded-circle">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">رسالة جديدة من رئيس القسم</p>
                                    <small class="text-muted">منذ ساعة</small>
                                </div>
                            </div>
                        </a>
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
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <div>
                <h1 class="h3"><?php echo t('forums'); ?></h1>
                <p class="text-muted"><?php echo t('manage_course_forums'); ?></p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createForumModal">
                    <i class="fas fa-plus me-1"></i> <?php echo t('create_new_forum'); ?>
                </button>
            </div>
        </div>
        
        <!-- رسائل النجاح والخطأ -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($selected_forum) && isset($selected_topic)): ?>
            <!-- عرض موضوع محدد -->
            <nav aria-label="breadcrumb" class="forum-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="teacher_forums.php"><?php echo t('forums'); ?></a></li>
                    <li class="breadcrumb-item"><a href="teacher_forums.php?forum_id=<?php echo $selected_forum['id']; ?>"><?php echo $selected_forum['title']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $selected_topic['title']; ?></li>
                </ol>
            </nav>
            
            <div class="topic-container">
                <div class="topic-header">
                    <h2 class="topic-title"><?php echo $selected_topic['title']; ?></h2>
                    <div class="topic-meta">
                        <div class="topic-meta-item">
                            <i class="fas fa-user"></i> <?php echo $selected_topic['author_name']; ?>
                        </div>
                        <div class="topic-meta-item">
                            <i class="fas fa-clock"></i> <?php echo $selected_topic['created_at']; ?>
                        </div>
                        <div class="topic-meta-item">
                            <i class="fas fa-eye"></i> <?php echo $selected_topic['views']; ?> <?php echo t('views'); ?>
                        </div>
                        <div class="topic-meta-item">
                            <i class="fas fa-comments"></i> <?php echo count($replies); ?> <?php echo t('replies'); ?>
                        </div>
                    </div>
                </div>
                
                <ul class="post-list">
                    <!-- المنشور الأصلي -->
                    <li class="post-item">
                        <div class="post-author">
                            <img src="<?php echo $selected_topic['author_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $selected_topic['author_name']; ?>" class="post-author-avatar">
                            <div class="post-author-name"><?php echo $selected_topic['author_name']; ?></div>
                            <div class="post-author-role"><?php echo $selected_topic['author_role']; ?></div>
                            <div class="post-author-stats">
                                <div><?php echo t('posts'); ?>: <?php echo $selected_topic['author_posts']; ?></div>
                                <div><?php echo t('joined'); ?>: <?php echo $selected_topic['author_joined']; ?></div>
                            </div>
                        </div>
                        <div class="post-content">
                            <div class="post-meta">
                                <div class="post-date">
                                    <i class="fas fa-clock"></i> <?php echo $selected_topic['created_at']; ?>
                                </div>
                                <div class="post-actions">
                                    <button title="<?php echo t('quote'); ?>">
                                        <i class="fas fa-quote-right"></i>
                                    </button>
                                    <button title="<?php echo t('edit'); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button title="<?php echo t('report'); ?>">
                                        <i class="fas fa-flag"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="post-text">
                                <?php echo $selected_topic['content']; ?>
                            </div>
                        </div>
                    </li>
                    
                    <!-- الردود -->
                    <?php foreach ($replies as $reply): ?>
                        <li class="post-item">
                            <div class="post-author">
                                <img src="<?php echo $reply['author_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $reply['author_name']; ?>" class="post-author-avatar">
                                <div class="post-author-name"><?php echo $reply['author_name']; ?></div>
                                <div class="post-author-role"><?php echo $reply['author_role']; ?></div>
                                <div class="post-author-stats">
                                    <div><?php echo t('posts'); ?>: <?php echo $reply['author_posts']; ?></div>
                                    <div><?php echo t('joined'); ?>: <?php echo $reply['author_joined']; ?></div>
                                </div>
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <div class="post-date">
                                        <i class="fas fa-clock"></i> <?php echo $reply['created_at']; ?>
                                    </div>
                                    <div class="post-actions">
                                        <button title="<?php echo t('quote'); ?>">
                                            <i class="fas fa-quote-right"></i>
                                        </button>
                                        <button title="<?php echo t('edit'); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button title="<?php echo t('report'); ?>">
                                            <i class="fas fa-flag"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="post-text">
                                    <?php echo $reply['content']; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- نموذج الرد -->
                <div class="reply-form">
                    <h4 class="reply-form-title"><?php echo t('add_reply'); ?></h4>
                    <form action="" method="post">
                        <input type="hidden" name="topic_id" value="<?php echo $selected_topic['id']; ?>">
                        <textarea name="content" class="reply-form-textarea" placeholder="<?php echo t('write_your_reply'); ?>" required></textarea>
                        <div class="reply-form-actions">
                            <div class="reply-form-attachments">
                                <button type="button" title="<?php echo t('attach_file'); ?>">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button type="button" title="<?php echo t('insert_image'); ?>">
                                    <i class="fas fa-image"></i>
                                </button>
                                <button type="button" title="<?php echo t('insert_code'); ?>">
                                    <i class="fas fa-code"></i>
                                </button>
                            </div>
                            <div>
                                <button type="submit" name="add_reply" class="btn btn-primary"><?php echo t('post_reply'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif (isset($selected_forum)): ?>
            <!-- عرض منتدى محدد -->
            <nav aria-label="breadcrumb" class="forum-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="teacher_forums.php"><?php echo t('forums'); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $selected_forum['title']; ?></li>
                </ol>
            </nav>
            
            <div class="forum-container">
                <div class="forum-header">
                    <h2 class="forum-title"><?php echo $selected_forum['title']; ?></h2>
                    <div class="forum-actions">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                            <i class="fas fa-plus me-1"></i> <?php echo t('create_new_topic'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="forum-tabs">
                    <div class="forum-tab active" data-tab="all"><?php echo t('all_topics'); ?></div>
                    <div class="forum-tab" data-tab="announcements"><?php echo t('announcements'); ?></div>
                    <div class="forum-tab" data-tab="questions"><?php echo t('questions'); ?></div>
                    <div class="forum-tab" data-tab="discussions"><?php echo t('discussions'); ?></div>
                </div>
                
                <div class="forum-search">
                    <input type="text" class="forum-search-input" placeholder="<?php echo t('search_topics'); ?>">
                </div>
                
                <div class="forum-filter">
                    <div class="forum-filter-item active" data-filter="all"><?php echo t('all'); ?></div>
                    <div class="forum-filter-item" data-filter="solved"><?php echo t('solved'); ?></div>
                    <div class="forum-filter-item" data-filter="unsolved"><?php echo t('unsolved'); ?></div>
                    <div class="forum-filter-item" data-filter="pinned"><?php echo t('pinned'); ?></div>
                    <div class="forum-filter-item" data-filter="closed"><?php echo t('closed'); ?></div>
                </div>
                
                <div class="forum-sort">
                    <select class="forum-sort-select">
                        <option value="newest"><?php echo t('newest_first'); ?></option>
                        <option value="oldest"><?php echo t('oldest_first'); ?></option>
                        <option value="most_replies"><?php echo t('most_replies'); ?></option>
                        <option value="most_views"><?php echo t('most_views'); ?></option>
                    </select>
                </div>
                
                <?php if (count($topics) > 0): ?>
                    <ul class="topic-list">
                        <?php foreach ($topics as $topic): ?>
                            <li class="topic-item">
                                <div class="topic-icon">
                                    <i class="fas fa-<?php echo $topic['icon']; ?>"></i>
                                </div>
                                <div class="topic-content">
                                    <h3 class="topic-item-title">
                                        <a href="teacher_forums.php?forum_id=<?php echo $selected_forum['id']; ?>&topic_id=<?php echo $topic['id']; ?>"><?php echo $topic['title']; ?></a>
                                        <?php if ($topic['is_pinned']): ?>
                                            <span class="topic-tag pinned"><?php echo t('pinned'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($topic['is_closed']): ?>
                                            <span class="topic-tag closed"><?php echo t('closed'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($topic['is_solved']): ?>
                                            <span class="topic-tag solved"><?php echo t('solved'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($topic['type'] === 'announcement'): ?>
                                            <span class="topic-tag announcement"><?php echo t('announcement'); ?></span>
                                        <?php elseif ($topic['type'] === 'question'): ?>
                                            <span class="topic-tag question"><?php echo t('question'); ?></span>
                                        <?php elseif ($topic['type'] === 'discussion'): ?>
                                            <span class="topic-tag discussion"><?php echo t('discussion'); ?></span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="topic-item-meta">
                                        <div class="topic-item-meta-item">
                                            <i class="fas fa-user"></i> <?php echo $topic['author_name']; ?>
                                        </div>
                                        <div class="topic-item-meta-item">
                                            <i class="fas fa-clock"></i> <?php echo $topic['created_at']; ?>
                                        </div>
                                        <div class="topic-item-meta-item">
                                            <i class="fas fa-reply"></i> <?php echo t('last_reply'); ?>: <?php echo $topic['last_reply']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="topic-item-stats">
                                    <div class="topic-item-stat">
                                        <div class="topic-item-stat-value"><?php echo $topic['replies']; ?></div>
                                        <div class="topic-item-stat-label"><?php echo t('replies'); ?></div>
                                    </div>
                                    <div class="topic-item-stat">
                                        <div class="topic-item-stat-value"><?php echo $topic['views']; ?></div>
                                        <div class="topic-item-stat-label"><?php echo t('views'); ?></div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- ترقيم الصفحات -->
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
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="empty-state-title"><?php echo t('no_topics_yet'); ?></h3>
                        <p class="empty-state-text"><?php echo t('no_topics_message'); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                            <i class="fas fa-plus me-1"></i> <?php echo t('create_first_topic'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- عرض جميع المنتديات -->
            <div class="forum-container">
                <div class="forum-header">
                    <h2 class="forum-title"><?php echo t('course_forums'); ?></h2>
                    <div class="forum-actions">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createForumModal">
                            <i class="fas fa-plus me-1"></i> <?php echo t('create_new_forum'); ?>
                        </button>
                    </div>
                </div>
                
                <?php if (count($forums) > 0): ?>
                    <ul class="forum-list">
                        <?php foreach ($forums as $forum): ?>
                            <li class="forum-item">
                                <div class="forum-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="forum-content">
                                    <h3 class="forum-item-title">
                                        <a href="teacher_forums.php?forum_id=<?php echo $forum['id']; ?>"><?php echo $forum['title']; ?></a>
                                    </h3>
                                    <div class="forum-item-description"><?php echo $forum['description']; ?></div>
                                    <div class="forum-item-meta">
                                        <div class="forum-item-meta-item">
                                            <i class="fas fa-book"></i> <?php echo $forum['course_name']; ?>
                                        </div>
                                        <div class="forum-item-meta-item">
                                            <i class="fas fa-clock"></i> <?php echo t('created'); ?>: <?php echo $forum['created_at']; ?>
                                        </div>
                                        <div class="forum-item-meta-item">
                                            <i class="fas fa-reply"></i> <?php echo t('last_activity'); ?>: <?php echo $forum['last_activity']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="forum-item-stats">
                                    <div class="forum-item-stat">
                                        <div class="forum-item-stat-value"><?php echo $forum['topics']; ?></div>
                                        <div class="forum-item-stat-label"><?php echo t('topics'); ?></div>
                                    </div>
                                    <div class="forum-item-stat">
                                        <div class="forum-item-stat-value"><?php echo $forum['posts']; ?></div>
                                        <div class="forum-item-stat-label"><?php echo t('posts'); ?></div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="empty-state-title"><?php echo t('no_forums_yet'); ?></h3>
                        <p class="empty-state-text"><?php echo t('no_forums_message'); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createForumModal">
                            <i class="fas fa-plus me-1"></i> <?php echo t('create_first_forum'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- مودال إنشاء منتدى جديد -->
    <div class="modal fade" id="createForumModal" tabindex="-1" aria-labelledby="createForumModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createForumModalLabel"><?php echo t('create_new_forum'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="create-forum-group">
                            <label for="course_id" class="create-forum-label"><?php echo t('course'); ?>:</label>
                            <select name="course_id" id="course_id" class="create-forum-input" required>
                                <option value=""><?php echo t('select_course'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="create-forum-group">
                            <label for="title" class="create-forum-label"><?php echo t('forum_title'); ?>:</label>
                            <input type="text" name="title" id="title" class="create-forum-input" required>
                        </div>
                        <div class="create-forum-group">
                            <label for="description" class="create-forum-label"><?php echo t('forum_description'); ?>:</label>
                            <textarea name="description" id="description" class="create-forum-textarea" required></textarea>
                        </div>
                        <div class="create-forum-actions">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                            <button type="submit" name="create_forum" class="btn btn-primary"><?php echo t('create_forum'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال إنشاء موضوع جديد -->
    <div class="modal fade" id="createTopicModal" tabindex="-1" aria-labelledby="createTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTopicModalLabel"><?php echo t('create_new_topic'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <?php if (isset($selected_forum)): ?>
                            <input type="hidden" name="forum_id" value="<?php echo $selected_forum['id']; ?>">
                        <?php else: ?>
                            <div class="create-topic-group">
                                <label for="forum_id" class="create-topic-label"><?php echo t('forum'); ?>:</label>
                                <select name="forum_id" id="forum_id" class="create-topic-input" required>
                                    <option value=""><?php echo t('select_forum'); ?></option>
                                    <?php foreach ($forums as $forum): ?>
                                        <option value="<?php echo $forum['id']; ?>"><?php echo $forum['title']; ?> (<?php echo $forum['course_name']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="create-topic-group">
                            <label for="topic_type" class="create-topic-label"><?php echo t('topic_type'); ?>:</label>
                            <select name="topic_type" id="topic_type" class="create-topic-input" required>
                                <option value="discussion"><?php echo t('discussion'); ?></option>
                                <option value="question"><?php echo t('question'); ?></option>
                                <option value="announcement"><?php echo t('announcement'); ?></option>
                            </select>
                        </div>
                        <div class="create-topic-group">
                            <label for="title" class="create-topic-label"><?php echo t('topic_title'); ?>:</label>
                            <input type="text" name="title" id="title" class="create-topic-input" required>
                        </div>
                        <div class="create-topic-group">
                            <label for="content" class="create-topic-label"><?php echo t('topic_content'); ?>:</label>
                            <textarea name="content" id="content" class="create-topic-textarea" required></textarea>
                        </div>
                        <div class="create-topic-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned">
                                <label class="form-check-label" for="is_pinned">
                                    <?php echo t('pin_topic'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="create-topic-actions">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                            <button type="submit" name="create_topic" class="btn btn-primary"><?php echo t('create_topic'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
            
            // تبديل علامات التبويب في المنتدى
            const forumTabs = document.querySelectorAll('.forum-tab');
            
            if (forumTabs.length > 0) {
                forumTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // إزالة الفئة النشطة من جميع علامات التبويب
                        forumTabs.forEach(t => t.classList.remove('active'));
                        
                        // إضافة الفئة النشطة إلى علامة التبويب المحددة
                        this.classList.add('active');
                        
                        // تصفية المواضيع حسب النوع
                        const tabName = this.getAttribute('data-tab');
                        const topicItems = document.querySelectorAll('.topic-item');
                        
                        topicItems.forEach(item => {
                            if (tabName === 'all') {
                                item.style.display = 'flex';
                            } else {
                                const topicType = item.querySelector(`.topic-tag.${tabName.slice(0, -1)}`) ? tabName.slice(0, -1) : '';
                                
                                if (topicType === tabName.slice(0, -1)) {
                                    item.style.display = 'flex';
                                } else {
                                    item.style.display = 'none';
                                }
                            }
                        });
                    });
                });
            }
            
            // تصفية المواضيع
            const forumFilterItems = document.querySelectorAll('.forum-filter-item');
            
            if (forumFilterItems.length > 0) {
                forumFilterItems.forEach(item => {
                    item.addEventListener('click', function() {
                        // إزالة الفئة النشطة من جميع عناصر التصفية
                        forumFilterItems.forEach(i => i.classList.remove('active'));
                        
                        // إضافة الفئة النشطة إلى عنصر التصفية المحدد
                        this.classList.add('active');
                        
                        // تصفية المواضيع حسب الحالة
                        const filterName = this.getAttribute('data-filter');
                        const topicItems = document.querySelectorAll('.topic-item');
                        
                        topicItems.forEach(item => {
                            if (filterName === 'all') {
                                item.style.display = 'flex';
                            } else {
                                const hasSolved = item.querySelector('.topic-tag.solved') !== null;
                                const hasPinned = item.querySelector('.topic-tag.pinned') !== null;
                                const hasClosed = item.querySelector('.topic-tag.closed') !== null;
                                
                                if (filterName === 'solved' && hasSolved) {
                                    item.style.display = 'flex';
                                } else if (filterName === 'unsolved' && !hasSolved) {
                                    item.style.display = 'flex';
                                } else if (filterName === 'pinned' && hasPinned) {
                                    item.style.display = 'flex';
                                } else if (filterName === 'closed' && hasClosed) {
                                    item.style.display = 'flex';
                                } else {
                                    item.style.display = 'none';
                                }
                            }
                        });
                    });
                });
            }
            
            // البحث في المواضيع
            const searchInput = document.querySelector('.forum-search-input');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const topicItems = document.querySelectorAll('.topic-item');
                    
                    topicItems.forEach(item => {
                        const title = item.querySelector('.topic-item-title').textContent.toLowerCase();
                        const author = item.querySelector('.topic-item-meta-item:first-child').textContent.toLowerCase();
                        
                        if (title.includes(searchTerm) || author.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
            
            // ترتيب المواضيع
            const sortSelect = document.querySelector('.forum-sort-select');
            
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const sortValue = this.value;
                    const topicList = document.querySelector('.topic-list');
                    const topicItems = Array.from(document.querySelectorAll('.topic-item'));
                    
                    // ترتيب المواضيع حسب القيمة المحددة
                    topicItems.sort((a, b) => {
                        if (sortValue === 'newest') {
                            const dateA = a.querySelector('.topic-item-meta-item:nth-child(2)').textContent;
                            const dateB = b.querySelector('.topic-item-meta-item:nth-child(2)').textContent;
                            return dateB.localeCompare(dateA);
                        } else if (sortValue === 'oldest') {
                            const dateA = a.querySelector('.topic-item-meta-item:nth-child(2)').textContent;
                            const dateB = b.querySelector('.topic-item-meta-item:nth-child(2)').textContent;
                            return dateA.localeCompare(dateB);
                        } else if (sortValue === 'most_replies') {
                            const repliesA = parseInt(a.querySelector('.topic-item-stat-value').textContent);
                            const repliesB = parseInt(b.querySelector('.topic-item-stat-value').textContent);
                            return repliesB - repliesA;
                        } else if (sortValue === 'most_views') {
                            const viewsA = parseInt(a.querySelector('.topic-item-stat:last-child .topic-item-stat-value').textContent);
                            const viewsB = parseInt(b.querySelector('.topic-item-stat:last-child .topic-item-stat-value').textContent);
                            return viewsB - viewsA;
                        }
                        return 0;
                    });
                    
                    // إعادة ترتيب المواضيع في القائمة
                    topicItems.forEach(item => {
                        topicList.appendChild(item);
                    });
                });
            }
        });
    </script>
</body>
</html>
