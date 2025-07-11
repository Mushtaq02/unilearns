<?php
/**
 * صفحة الرسائل للمعلم في نظام UniverBoard
 * تتيح للمعلم عرض وإدارة الرسائل الخاصة به
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

// معالجة إرسال رسالة جديدة
if (isset($_POST['send_message'])) {
    $recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    // التحقق من صحة البيانات
    if (!empty($recipient_id) && !empty($subject) && !empty($message)) {
        // إرسال الرسالة
        $result = send_message($db, $teacher_id, $sender_type, $recipient_id, $recipient_type, $subject, $message);
        
        if ($result) {
            $success_message = t('message_sent_successfully');
        } else {
            $error_message = t('failed_to_send_message');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// معالجة حذف رسالة
if (isset($_POST['delete_message'])) {
    $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);
    
    // حذف الرسالة
    $result = delete_message($db, $message_id, $teacher_id);
    
    if ($result) {
        $success_message = t('message_deleted_successfully');
    } else {
        $error_message = t('failed_to_delete_message');
    }
}

// معالجة تحديث حالة الرسالة
if (isset($_POST['mark_read'])) {
    $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);
    
    // تحديث حالة الرسالة
    $result = mark_message_as_read($db, $message_id, $teacher_id);
    
    if ($result) {
        $success_message = t('message_marked_as_read');
    } else {
        $error_message = t('failed_to_update_message');
    }
}

// الحصول على الرسائل
$inbox_messages = get_inbox_messages($db, $teacher_id);
$sent_messages = get_sent_messages($db, $teacher_id);
$archived_messages = get_archived_messages($db, $teacher_id);

// الحصول على قائمة المستلمين المحتملين (الطلاب، المعلمين، المشرفين)
$recipients = get_message_recipients($db, $teacher_id);

// إغلاق اتصال قاعدة البيانات
$dsn->close();
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
        
        .message-container {
            display: flex;
            height: calc(100vh - 200px);
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .theme-dark .message-container {
            background-color: var(--dark-bg);
        }
        
        .message-sidebar {
            width: 300px;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        [dir="rtl"] .message-sidebar {
            border-right: none;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .message-sidebar {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-sidebar-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .message-sidebar-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-sidebar-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .message-sidebar-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .message-sidebar-actions button {
            background: none;
            border: none;
            color: var(--primary-color);
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
        
        .message-sidebar-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .message-sidebar-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .message-sidebar-search {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .message-sidebar-search {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-sidebar-search input {
            width: 100%;
            padding: 0.5rem 1rem;
            border-radius: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .theme-dark .message-sidebar-search input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .message-sidebar-search input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .message-sidebar-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .message-sidebar-tabs {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-sidebar-tab {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            color: var(--gray-color);
            border-bottom: 3px solid transparent;
        }
        
        .message-sidebar-tab:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .message-sidebar-tab:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .message-sidebar-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .message-list {
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        
        .theme-dark .message-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .theme-dark .message-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
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
            background-color: rgba(0, 48, 73, 0.05);
        }
        
        .message-item-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        [dir="rtl"] .message-item-avatar {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        .message-item-content {
            flex-grow: 1;
            min-width: 0;
        }
        
        .message-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }
        
        .message-item-name {
            font-weight: 600;
            margin-bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-item-time {
            font-size: 0.75rem;
            color: var(--gray-color);
            white-space: nowrap;
        }
        
        .message-item-subject {
            font-weight: 500;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-item-preview {
            font-size: 0.85rem;
            color: var(--gray-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-item-badge {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--primary-color);
            margin-left: 0.5rem;
            flex-shrink: 0;
        }
        
        [dir="rtl"] .message-item-badge {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        
        .message-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .message-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .message-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .message-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .message-actions button {
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
        
        .message-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .message-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .message-body {
            padding: 1.5rem;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .message-meta {
            margin-bottom: 1.5rem;
        }
        
        .message-meta-item {
            display: flex;
            margin-bottom: 0.5rem;
        }
        
        .message-meta-label {
            font-weight: 600;
            width: 100px;
            flex-shrink: 0;
        }
        
        .message-meta-value {
            flex-grow: 1;
        }
        
        .message-text {
            line-height: 1.6;
        }
        
        .message-reply {
            padding: 1.25rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .theme-dark .message-reply {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-reply-textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            resize: none;
            margin-bottom: 0.75rem;
            min-height: 100px;
        }
        
        .theme-dark .message-reply-textarea {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .message-reply-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .message-reply-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .message-reply-attachments {
            display: flex;
            gap: 0.5rem;
        }
        
        .message-reply-attachments button {
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
        
        .message-reply-attachments button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .message-reply-attachments button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .message-compose {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .message-compose-header {
            margin-bottom: 1.5rem;
        }
        
        .message-compose-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .message-compose-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            flex-grow: 1;
        }
        
        .message-compose-group {
            display: flex;
            flex-direction: column;
        }
        
        .message-compose-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .message-compose-input {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .message-compose-input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .message-compose-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .message-compose-textarea {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            resize: none;
            flex-grow: 1;
            min-height: 200px;
        }
        
        .theme-dark .message-compose-textarea {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .message-compose-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .message-compose-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .message-compose-attachments {
            display: flex;
            gap: 0.5rem;
        }
        
        .message-compose-attachments button {
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
        
        .message-compose-attachments button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .message-compose-attachments button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .message-empty {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }
        
        .message-empty-icon {
            font-size: 4rem;
            color: var(--gray-color);
            margin-bottom: 1.5rem;
        }
        
        .message-empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .message-empty-text {
            color: var(--gray-color);
            margin-bottom: 1.5rem;
            max-width: 400px;
        }
        
        @media (max-width: 992px) {
            .message-container {
                flex-direction: column;
                height: auto;
            }
            
            .message-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            }
            
            [dir="rtl"] .message-sidebar {
                border-left: none;
            }
            
            .theme-dark .message-sidebar {
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            .message-list {
                max-height: 300px;
            }
            
            .message-content {
                min-height: 500px;
            }
        }
        
        .message-attachment {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: rgba(0, 0, 0, 0.05);
            margin-bottom: 0.75rem;
        }
        
        .theme-dark .message-attachment {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .message-attachment-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .message-attachment-icon {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        .message-attachment-info {
            flex-grow: 1;
        }
        
        .message-attachment-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .message-attachment-size {
            font-size: 0.75rem;
            color: var(--gray-color);
        }
        
        .message-attachment-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .message-attachment-actions button {
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
        
        .message-attachment-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .message-attachment-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .message-compose-select {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        
        .theme-dark .message-compose-select {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .message-compose-select:focus {
            outline: none;
            border-color: var(--primary-color);
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
                    <a class="nav-link active" href="teacher_messages.php">
                        <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher_notifications.php">
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
                <h1 class="h3"><?php echo t('messages'); ?></h1>
                <p class="text-muted"><?php echo t('manage_your_messages'); ?></p>
            </div>
            <div>
                <button class="btn btn-primary" id="composeBtn">
                    <i class="fas fa-pen me-1"></i> <?php echo t('compose_new_message'); ?>
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
        
        <!-- نظام الرسائل -->
        <div class="message-container">
            <!-- القائمة الجانبية للرسائل -->
            <div class="message-sidebar">
                <div class="message-sidebar-header">
                    <h5 class="message-sidebar-title"><?php echo t('messages'); ?></h5>
                    <div class="message-sidebar-actions">
                        <button id="refreshBtn" title="<?php echo t('refresh'); ?>">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <div class="message-sidebar-search">
                    <input type="text" id="searchMessages" placeholder="<?php echo t('search_messages'); ?>">
                </div>
                
                <div class="message-sidebar-tabs">
                    <div class="message-sidebar-tab active" data-tab="inbox">
                        <?php echo t('inbox'); ?> (<?php echo count($inbox_messages); ?>)
                    </div>
                    <div class="message-sidebar-tab" data-tab="sent">
                        <?php echo t('sent'); ?> (<?php echo count($sent_messages); ?>)
                    </div>
                    <div class="message-sidebar-tab" data-tab="archived">
                        <?php echo t('archived'); ?> (<?php echo count($archived_messages); ?>)
                    </div>
                </div>
                
                <div class="message-list" id="inboxList">
                    <?php if (count($inbox_messages) > 0): ?>
                        <?php foreach ($inbox_messages as $message): ?>
                            <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>" data-message-id="<?php echo $message['id']; ?>" data-tab="inbox">
                                <img src="<?php echo $message['sender_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $message['sender_name']; ?>" class="message-item-avatar">
                                <div class="message-item-content">
                                    <div class="message-item-header">
                                        <h6 class="message-item-name"><?php echo $message['sender_name']; ?></h6>
                                        <span class="message-item-time"><?php echo $message['time_ago']; ?></span>
                                    </div>
                                    <div class="message-item-subject"><?php echo $message['subject']; ?></div>
                                    <div class="message-item-preview"><?php echo substr($message['content'], 0, 50); ?>...</div>
                                </div>
                                <?php if (!$message['is_read']): ?>
                                    <div class="message-item-badge"></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <?php echo t('no_messages_in_inbox'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="message-list d-none" id="sentList">
                    <?php if (count($sent_messages) > 0): ?>
                        <?php foreach ($sent_messages as $message): ?>
                            <div class="message-item" data-message-id="<?php echo $message['id']; ?>" data-tab="sent">
                                <img src="<?php echo $message['recipient_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $message['recipient_name']; ?>" class="message-item-avatar">
                                <div class="message-item-content">
                                    <div class="message-item-header">
                                        <h6 class="message-item-name"><?php echo t('to'); ?>: <?php echo $message['recipient_name']; ?></h6>
                                        <span class="message-item-time"><?php echo $message['time_ago']; ?></span>
                                    </div>
                                    <div class="message-item-subject"><?php echo $message['subject']; ?></div>
                                    <div class="message-item-preview"><?php echo substr($message['content'], 0, 50); ?>...</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <?php echo t('no_sent_messages'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="message-list d-none" id="archivedList">
                    <?php if (count($archived_messages) > 0): ?>
                        <?php foreach ($archived_messages as $message): ?>
                            <div class="message-item" data-message-id="<?php echo $message['id']; ?>" data-tab="archived">
                                <img src="<?php echo $message['sender_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $message['sender_name']; ?>" class="message-item-avatar">
                                <div class="message-item-content">
                                    <div class="message-item-header">
                                        <h6 class="message-item-name"><?php echo $message['sender_name']; ?></h6>
                                        <span class="message-item-time"><?php echo $message['time_ago']; ?></span>
                                    </div>
                                    <div class="message-item-subject"><?php echo $message['subject']; ?></div>
                                    <div class="message-item-preview"><?php echo substr($message['content'], 0, 50); ?>...</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <?php echo t('no_archived_messages'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- محتوى الرسالة -->
            <div class="message-content">
                <!-- حالة عدم تحديد رسالة -->
                <div class="message-empty" id="messageEmpty">
                    <div class="message-empty-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h3 class="message-empty-title"><?php echo t('select_message'); ?></h3>
                    <p class="message-empty-text"><?php echo t('select_message_to_view'); ?></p>
                    <button class="btn btn-primary" id="composeEmptyBtn">
                        <i class="fas fa-pen me-1"></i> <?php echo t('compose_new_message'); ?>
                    </button>
                </div>
                
                <!-- عرض الرسالة -->
                <div class="d-none" id="messageView">
                    <div class="message-header">
                        <h5 class="message-title" id="messageViewSubject"></h5>
                        <div class="message-actions">
                            <button id="replyBtn" title="<?php echo t('reply'); ?>">
                                <i class="fas fa-reply"></i>
                            </button>
                            <button id="forwardBtn" title="<?php echo t('forward'); ?>">
                                <i class="fas fa-share"></i>
                            </button>
                            <button id="archiveBtn" title="<?php echo t('archive'); ?>">
                                <i class="fas fa-archive"></i>
                            </button>
                            <button id="deleteBtn" title="<?php echo t('delete'); ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="message-body">
                        <div class="message-meta">
                            <div class="message-meta-item">
                                <div class="message-meta-label"><?php echo t('from'); ?>:</div>
                                <div class="message-meta-value" id="messageViewFrom"></div>
                            </div>
                            <div class="message-meta-item">
                                <div class="message-meta-label"><?php echo t('to'); ?>:</div>
                                <div class="message-meta-value" id="messageViewTo"></div>
                            </div>
                            <div class="message-meta-item">
                                <div class="message-meta-label"><?php echo t('date'); ?>:</div>
                                <div class="message-meta-value" id="messageViewDate"></div>
                            </div>
                        </div>
                        
                        <div class="message-text" id="messageViewContent"></div>
                        
                        <div class="message-attachments mt-4" id="messageViewAttachments">
                            <!-- سيتم إضافة المرفقات هنا ديناميكيًا -->
                        </div>
                    </div>
                    
                    <div class="message-reply d-none" id="messageReply">
                        <textarea class="message-reply-textarea" id="replyText" placeholder="<?php echo t('write_your_reply'); ?>"></textarea>
                        <div class="message-reply-actions">
                            <div class="message-reply-attachments">
                                <button title="<?php echo t('attach_file'); ?>">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button title="<?php echo t('insert_image'); ?>">
                                    <i class="fas fa-image"></i>
                                </button>
                            </div>
                            <div>
                                <button class="btn btn-secondary btn-sm" id="cancelReplyBtn"><?php echo t('cancel'); ?></button>
                                <button class="btn btn-primary btn-sm" id="sendReplyBtn"><?php echo t('send'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- إنشاء رسالة جديدة -->
                <div class="d-none" id="messageCompose">
                    <div class="message-header">
                        <h5 class="message-title"><?php echo t('new_message'); ?></h5>
                        <div class="message-actions">
                            <button id="cancelComposeBtn" title="<?php echo t('cancel'); ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="message-compose">
                        <form id="composeForm" method="post" action="">
                            <div class="message-compose-form">
                                <div class="message-compose-group">
                                    <label for="recipient" class="message-compose-label"><?php echo t('to'); ?>:</label>
                                    <select name="recipient_id" id="recipient" class="message-compose-select" required>
                                        <option value=""><?php echo t('select_recipient'); ?></option>
                                        <?php if (count($recipients) > 0): ?>
                                            <?php foreach ($recipients as $category => $users): ?>
                                                <optgroup label="<?php echo t($category); ?>">
                                                    <?php foreach ($users as $user): ?>
                                                        <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?> (<?php echo $user['role']; ?>)</option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="message-compose-group">
                                    <label for="subject" class="message-compose-label"><?php echo t('subject'); ?>:</label>
                                    <input type="text" name="subject" id="subject" class="message-compose-input" required>
                                </div>
                                
                                <div class="message-compose-group flex-grow-1">
                                    <label for="message" class="message-compose-label"><?php echo t('message'); ?>:</label>
                                    <textarea name="message" id="message" class="message-compose-textarea" required></textarea>
                                </div>
                                
                                <div class="message-compose-actions">
                                    <div class="message-compose-attachments">
                                        <button type="button" title="<?php echo t('attach_file'); ?>">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        <button type="button" title="<?php echo t('insert_image'); ?>">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-secondary" id="cancelComposeFormBtn"><?php echo t('cancel'); ?></button>
                                        <button type="submit" name="send_message" class="btn btn-primary"><?php echo t('send'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف الرسالة -->
    <div class="modal fade" id="deleteMessageModal" tabindex="-1" aria-labelledby="deleteMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMessageModalLabel"><?php echo t('delete_message'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo t('delete_message_confirmation'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <form action="" method="post">
                        <input type="hidden" name="message_id" id="deleteMessageId" value="">
                        <button type="submit" name="delete_message" class="btn btn-danger"><?php echo t('delete'); ?></button>
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
            
            // تبديل علامات التبويب في القائمة الجانبية للرسائل
            const messageTabs = document.querySelectorAll('.message-sidebar-tab');
            const messageLists = document.querySelectorAll('.message-list');
            
            messageTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // إزالة الفئة النشطة من جميع علامات التبويب
                    messageTabs.forEach(t => t.classList.remove('active'));
                    
                    // إضافة الفئة النشطة إلى علامة التبويب المحددة
                    this.classList.add('active');
                    
                    // إخفاء جميع قوائم الرسائل
                    messageLists.forEach(list => list.classList.add('d-none'));
                    
                    // إظهار قائمة الرسائل المناسبة
                    const tabName = this.getAttribute('data-tab');
                    document.getElementById(`${tabName}List`).classList.remove('d-none');
                });
            });
            
            // البحث في الرسائل
            const searchInput = document.getElementById('searchMessages');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const activeTab = document.querySelector('.message-sidebar-tab.active').getAttribute('data-tab');
                const messageItems = document.querySelectorAll(`#${activeTab}List .message-item`);
                
                messageItems.forEach(item => {
                    const name = item.querySelector('.message-item-name').textContent.toLowerCase();
                    const subject = item.querySelector('.message-item-subject').textContent.toLowerCase();
                    const preview = item.querySelector('.message-item-preview').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || subject.includes(searchTerm) || preview.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
            
            // عرض الرسالة عند النقر عليها
            const messageItems = document.querySelectorAll('.message-item');
            
            messageItems.forEach(item => {
                item.addEventListener('click', function() {
                    // إزالة الفئة النشطة من جميع عناصر الرسائل
                    messageItems.forEach(i => i.classList.remove('active'));
                    
                    // إضافة الفئة النشطة إلى عنصر الرسالة المحدد
                    this.classList.add('active');
                    
                    // إزالة فئة غير مقروء إذا كانت موجودة
                    this.classList.remove('unread');
                    
                    // إزالة شارة غير مقروء إذا كانت موجودة
                    const badge = this.querySelector('.message-item-badge');
                    if (badge) {
                        badge.remove();
                    }
                    
                    // إخفاء حالة عدم تحديد رسالة
                    document.getElementById('messageEmpty').classList.add('d-none');
                    
                    // إخفاء نموذج إنشاء رسالة جديدة
                    document.getElementById('messageCompose').classList.add('d-none');
                    
                    // إظهار عرض الرسالة
                    document.getElementById('messageView').classList.remove('d-none');
                    
                    // إخفاء نموذج الرد
                    document.getElementById('messageReply').classList.add('d-none');
                    
                    // تحديث بيانات الرسالة
                    const messageId = this.getAttribute('data-message-id');
                    const tab = this.getAttribute('data-tab');
                    
                    // هنا يمكن إضافة كود لجلب بيانات الرسالة من الخادم
                    // ولكن في هذا المثال سنستخدم بيانات وهمية
                    
                    if (tab === 'inbox') {
                        document.getElementById('messageViewSubject').textContent = this.querySelector('.message-item-subject').textContent;
                        document.getElementById('messageViewFrom').textContent = this.querySelector('.message-item-name').textContent;
                        document.getElementById('messageViewTo').textContent = '<?php echo $teacher['name']; ?>';
                        document.getElementById('messageViewDate').textContent = this.querySelector('.message-item-time').textContent;
                        document.getElementById('messageViewContent').textContent = 'محتوى الرسالة سيظهر هنا. هذا مثال لمحتوى رسالة طويل يمكن أن يحتوي على فقرات متعددة ومعلومات مفصلة. يمكن أن تحتوي الرسالة أيضًا على روابط ومرفقات وتنسيقات مختلفة.';
                        
                        // إظهار أزرار الرد والأرشفة
                        document.getElementById('replyBtn').style.display = 'flex';
                        document.getElementById('archiveBtn').style.display = 'flex';
                    } else if (tab === 'sent') {
                        document.getElementById('messageViewSubject').textContent = this.querySelector('.message-item-subject').textContent;
                        document.getElementById('messageViewFrom').textContent = '<?php echo $teacher['name']; ?>';
                        document.getElementById('messageViewTo').textContent = this.querySelector('.message-item-name').textContent.replace('إلى: ', '');
                        document.getElementById('messageViewDate').textContent = this.querySelector('.message-item-time').textContent;
                        document.getElementById('messageViewContent').textContent = 'محتوى الرسالة المرسلة سيظهر هنا. هذا مثال لمحتوى رسالة طويل يمكن أن يحتوي على فقرات متعددة ومعلومات مفصلة. يمكن أن تحتوي الرسالة أيضًا على روابط ومرفقات وتنسيقات مختلفة.';
                        
                        // إخفاء أزرار الرد والأرشفة
                        document.getElementById('replyBtn').style.display = 'none';
                        document.getElementById('archiveBtn').style.display = 'none';
                    } else if (tab === 'archived') {
                        document.getElementById('messageViewSubject').textContent = this.querySelector('.message-item-subject').textContent;
                        document.getElementById('messageViewFrom').textContent = this.querySelector('.message-item-name').textContent;
                        document.getElementById('messageViewTo').textContent = '<?php echo $teacher['name']; ?>';
                        document.getElementById('messageViewDate').textContent = this.querySelector('.message-item-time').textContent;
                        document.getElementById('messageViewContent').textContent = 'محتوى الرسالة المؤرشفة سيظهر هنا. هذا مثال لمحتوى رسالة طويل يمكن أن يحتوي على فقرات متعددة ومعلومات مفصلة. يمكن أن تحتوي الرسالة أيضًا على روابط ومرفقات وتنسيقات مختلفة.';
                        
                        // إظهار زر الرد وإخفاء زر الأرشفة
                        document.getElementById('replyBtn').style.display = 'flex';
                        document.getElementById('archiveBtn').style.display = 'none';
                    }
                    
                    // تحديث معرف الرسالة للحذف
                    document.getElementById('deleteMessageId').value = messageId;
                    
                    // تحديث المرفقات
                    const attachmentsContainer = document.getElementById('messageViewAttachments');
                    attachmentsContainer.innerHTML = '';
                    
                    // إضافة مرفقات وهمية
                    if (Math.random() > 0.5) {
                        const attachment = document.createElement('div');
                        attachment.className = 'message-attachment';
                        attachment.innerHTML = `
                            <div class="message-attachment-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="message-attachment-info">
                                <div class="message-attachment-name">ملف_مرفق.pdf</div>
                                <div class="message-attachment-size">2.5 MB</div>
                            </div>
                            <div class="message-attachment-actions">
                                <button title="${t('download')}">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        `;
                        attachmentsContainer.appendChild(attachment);
                    }
                    
                    // تحديث حالة الرسالة كمقروءة
                    if (tab === 'inbox' && this.classList.contains('unread')) {
                        // هنا يمكن إضافة كود لتحديث حالة الرسالة في الخادم
                        const form = document.createElement('form');
                        form.method = 'post';
                        form.style.display = 'none';
                        
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'message_id';
                        input.value = messageId;
                        
                        const submitBtn = document.createElement('button');
                        submitBtn.type = 'submit';
                        submitBtn.name = 'mark_read';
                        
                        form.appendChild(input);
                        form.appendChild(submitBtn);
                        document.body.appendChild(form);
                        
                        // تأخير إرسال النموذج لتجنب إعادة تحميل الصفحة فورًا
                        setTimeout(() => {
                            // form.submit();
                            // تم تعليق الإرسال لتجنب إعادة تحميل الصفحة في هذا المثال
                        }, 1000);
                    }
                });
            });
            
            // زر إنشاء رسالة جديدة
            const composeBtns = document.querySelectorAll('#composeBtn, #composeEmptyBtn');
            
            composeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // إخفاء حالة عدم تحديد رسالة
                    document.getElementById('messageEmpty').classList.add('d-none');
                    
                    // إخفاء عرض الرسالة
                    document.getElementById('messageView').classList.add('d-none');
                    
                    // إظهار نموذج إنشاء رسالة جديدة
                    document.getElementById('messageCompose').classList.remove('d-none');
                    
                    // إعادة تعيين نموذج إنشاء رسالة جديدة
                    document.getElementById('composeForm').reset();
                });
            });
            
            // زر إلغاء إنشاء رسالة جديدة
            const cancelComposeBtns = document.querySelectorAll('#cancelComposeBtn, #cancelComposeFormBtn');
            
            cancelComposeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // إظهار حالة عدم تحديد رسالة
                    document.getElementById('messageEmpty').classList.remove('d-none');
                    
                    // إخفاء نموذج إنشاء رسالة جديدة
                    document.getElementById('messageCompose').classList.add('d-none');
                });
            });
            
            // زر الرد على الرسالة
            document.getElementById('replyBtn').addEventListener('click', function() {
                // إظهار نموذج الرد
                document.getElementById('messageReply').classList.remove('d-none');
                
                // التركيز على حقل الرد
                document.getElementById('replyText').focus();
            });
            
            // زر إلغاء الرد
            document.getElementById('cancelReplyBtn').addEventListener('click', function() {
                // إخفاء نموذج الرد
                document.getElementById('messageReply').classList.add('d-none');
                
                // إعادة تعيين نص الرد
                document.getElementById('replyText').value = '';
            });
            
            // زر إرسال الرد
            document.getElementById('sendReplyBtn').addEventListener('click', function() {
                const replyText = document.getElementById('replyText').value;
                
                if (replyText.trim() === '') {
                    alert('الرجاء كتابة رد قبل الإرسال.');
                    return;
                }
                
                // هنا يمكن إضافة كود لإرسال الرد إلى الخادم
                alert('تم إرسال الرد بنجاح!');
                
                // إخفاء نموذج الرد
                document.getElementById('messageReply').classList.add('d-none');
                
                // إعادة تعيين نص الرد
                document.getElementById('replyText').value = '';
            });
            
            // زر حذف الرسالة
            document.getElementById('deleteBtn').addEventListener('click', function() {
                // إظهار مودال حذف الرسالة
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteMessageModal'));
                deleteModal.show();
            });
            
            // زر أرشفة الرسالة
            document.getElementById('archiveBtn').addEventListener('click', function() {
                // هنا يمكن إضافة كود لأرشفة الرسالة
                alert('تم أرشفة الرسالة بنجاح!');
                
                // إظهار حالة عدم تحديد رسالة
                document.getElementById('messageEmpty').classList.remove('d-none');
                
                // إخفاء عرض الرسالة
                document.getElementById('messageView').classList.add('d-none');
                
                // إزالة الرسالة من قائمة البريد الوارد
                const activeMessageItem = document.querySelector('.message-item.active');
                if (activeMessageItem) {
                    activeMessageItem.remove();
                }
            });
            
            // زر إعادة تحميل الرسائل
            document.getElementById('refreshBtn').addEventListener('click', function() {
                // هنا يمكن إضافة كود لإعادة تحميل الرسائل من الخادم
                alert('جاري إعادة تحميل الرسائل...');
                
                // إعادة تحميل الصفحة
                // window.location.reload();
            });
        });
    </script>
</body>
</html>
