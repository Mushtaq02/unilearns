<?php
/**
 * صفحة إدارة الاختبارات للمعلم في نظام UniverBoard
 * تتيح للمعلم إنشاء وإدارة الاختبارات للمقررات التي يدرسها
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

// الحصول على المقررات التي يدرسها المعلم
$courses = get_teacher_courses($db, $teacher_id);

// الحصول على الاختبارات التي أنشأها المعلم
$exams = get_teacher_exams($db, $teacher_id);

// معالجة إضافة اختبار جديد
if (isset($_POST['add_exam'])) {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $exam_date = filter_input(INPUT_POST, 'exam_date', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    $total_marks = filter_input(INPUT_POST, 'total_marks', FILTER_SANITIZE_NUMBER_INT);
    
    // التحقق من صحة البيانات
    if (!empty($course_id) && !empty($title) && !empty($exam_date) && !empty($start_time) && !empty($end_time) && !empty($total_marks)) {
        // إضافة اختبار جديد
        $result = add_exam($db, $teacher_id, $course_id, $title, $description, $exam_date, $start_time, $end_time, $total_marks);
        
        if ($result) {
            $success_message = t('exam_added_successfully');
            // تحديث قائمة الاختبارات
            $exams = get_teacher_exams($db, $teacher_id);
        } else {
            $error_message = t('failed_to_add_exam');
        }
    } else {
        $error_message = t('all_fields_required');
    }
}

// معالجة حذف اختبار
if (isset($_POST['delete_exam'])) {
    $exam_id = filter_input(INPUT_POST, 'exam_id', FILTER_SANITIZE_NUMBER_INT);
    
    // حذف الاختبار
    $result = delete_exam($db, $exam_id, $teacher_id);
    
    if ($result) {
        $success_message = t('exam_deleted_successfully');
        // تحديث قائمة الاختبارات
        $exams = get_teacher_exams($db, $teacher_id);
    } else {
        $error_message = t('failed_to_delete_exam');
    }
}

// معالجة تعديل اختبار
if (isset($_POST['edit_exam'])) {
    $exam_id = filter_input(INPUT_POST, 'exam_id', FILTER_SANITIZE_NUMBER_INT);
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $exam_date = filter_input(INPUT_POST, 'exam_date', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    $total_marks = filter_input(INPUT_POST, 'total_marks', FILTER_SANITIZE_NUMBER_INT);
    
    // التحقق من صحة البيانات
    if (!empty($exam_id) && !empty($course_id) && !empty($title) && !empty($exam_date) && !empty($start_time) && !empty($end_time) && !empty($total_marks)) {
        // تعديل الاختبار
        $result = edit_exam($db, $exam_id, $teacher_id, $course_id, $title, $description, $exam_date, $start_time, $end_time, $total_marks);
        
        if ($result) {
            $success_message = t('exam_updated_successfully');
            // تحديث قائمة الاختبارات
            $exams = get_teacher_exams($db, $teacher_id);
        } else {
            $error_message = t('failed_to_update_exam');
        }
    } else {
        $error_message = t('all_fields_required');
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
    <title><?php echo SITE_NAME; ?> - <?php echo t('exams'); ?></title>
    
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
        
        .exam-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }
        
        .theme-dark .exam-card {
            background-color: var(--dark-bg);
        }
        
        .exam-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .exam-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .theme-dark .exam-card-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .exam-card-title {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .exam-card-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .exam-card-actions button {
            background: none;
            border: none;
            color: var(--gray-color);
            font-size: 1rem;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .exam-card-actions button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }
        
        .theme-dark .exam-card-actions button:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .exam-card-actions button.delete:hover {
            color: #dc3545;
        }
        
        .exam-card-body {
            padding: 1.5rem;
        }
        
        .exam-card-description {
            margin-bottom: 1rem;
            color: var(--gray-color);
        }
        
        .exam-card-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            font-size: 0.9rem;
        }
        
        .exam-card-detail {
            display: flex;
            align-items: center;
        }
        
        .exam-card-detail i {
            margin-right: 0.5rem;
            opacity: 0.7;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .exam-card-detail i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .exam-card-detail-label {
            font-weight: 500;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .exam-card-detail-label {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .exam-form-group {
            margin-bottom: 1rem;
        }
        
        .exam-form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .exam-form-input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .exam-form-input {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .exam-form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .exam-form-select {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        
        .theme-dark .exam-form-select {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .exam-form-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .exam-form-textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            min-height: 100px;
        }
        
        .theme-dark .exam-form-textarea {
            border-color: rgba(255, 255, 255, 0.1);
            background-color: var(--dark-bg);
            color: white;
        }
        
        .exam-form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .exam-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
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
                    <a class="nav-link active" href="teacher_exams.php">
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
                <h1 class="h3"><?php echo t('exams'); ?></h1>
                <p class="text-muted"><?php echo t('manage_your_exams'); ?></p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
                    <i class="fas fa-plus me-1"></i> <?php echo t('add_new_exam'); ?>
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
        
        <!-- قائمة الاختبارات -->
        <div class="row">
            <?php if (count($exams) > 0): ?>
                <?php foreach ($exams as $exam): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="exam-card">
                            <div class="exam-card-header">
                                <h5 class="exam-card-title"><?php echo $exam['title']; ?></h5>
                                <div class="exam-card-actions">
                                    <button type="button" class="edit-exam" data-bs-toggle="modal" data-bs-target="#editExamModal" 
                                            data-id="<?php echo $exam['id']; ?>" 
                                            data-course="<?php echo $exam['course_id']; ?>" 
                                            data-title="<?php echo htmlspecialchars($exam['title']); ?>" 
                                            data-description="<?php echo htmlspecialchars($exam['description']); ?>" 
                                            data-date="<?php echo $exam['exam_date']; ?>" 
                                            data-start="<?php echo $exam['start_time']; ?>" 
                                            data-end="<?php echo $exam['end_time']; ?>" 
                                            data-marks="<?php echo $exam['total_marks']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="delete-exam delete" data-bs-toggle="modal" data-bs-target="#deleteExamModal" data-id="<?php echo $exam['id']; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="exam-card-body">
                                <p class="exam-card-description"><?php echo $exam['description'] ?: t('no_description_provided'); ?></p>
                                <div class="exam-card-details">
                                    <div class="exam-card-detail">
                                        <i class="fas fa-book"></i>
                                        <span class="exam-card-detail-label"><?php echo t('course'); ?>:</span>
                                        <span><?php echo $exam['course_name']; ?></span>
                                    </div>
                                    <div class="exam-card-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span class="exam-card-detail-label"><?php echo t('date'); ?>:</span>
                                        <span><?php echo $exam['exam_date']; ?></span>
                                    </div>
                                    <div class="exam-card-detail">
                                        <i class="fas fa-clock"></i>
                                        <span class="exam-card-detail-label"><?php echo t('time'); ?>:</span>
                                        <span><?php echo $exam['start_time']; ?> - <?php echo $exam['end_time']; ?></span>
                                    </div>
                                    <div class="exam-card-detail">
                                        <i class="fas fa-star"></i>
                                        <span class="exam-card-detail-label"><?php echo t('total_marks'); ?>:</span>
                                        <span><?php echo $exam['total_marks']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="empty-state-title"><?php echo t('no_exams_yet'); ?></h3>
                        <p class="empty-state-text"><?php echo t('no_exams_message'); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
                            <i class="fas fa-plus me-1"></i> <?php echo t('add_new_exam'); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- مودال إضافة اختبار جديد -->
    <div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExamModalLabel"><?php echo t('add_new_exam'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="exam-form-group">
                            <label for="course_id" class="exam-form-label"><?php echo t('course'); ?>:</label>
                            <select name="course_id" id="course_id" class="exam-form-select" required>
                                <option value=""><?php echo t('select_course'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="exam-form-group">
                            <label for="title" class="exam-form-label"><?php echo t('exam_title'); ?>:</label>
                            <input type="text" name="title" id="title" class="exam-form-input" required>
                        </div>
                        <div class="exam-form-group">
                            <label for="description" class="exam-form-label"><?php echo t('description'); ?>:</label>
                            <textarea name="description" id="description" class="exam-form-textarea"></textarea>
                        </div>
                        <div class="exam-form-group">
                            <label for="exam_date" class="exam-form-label"><?php echo t('exam_date'); ?>:</label>
                            <input type="date" name="exam_date" id="exam_date" class="exam-form-input" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="exam-form-group">
                                    <label for="start_time" class="exam-form-label"><?php echo t('start_time'); ?>:</label>
                                    <input type="time" name="start_time" id="start_time" class="exam-form-input" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="exam-form-group">
                                    <label for="end_time" class="exam-form-label"><?php echo t('end_time'); ?>:</label>
                                    <input type="time" name="end_time" id="end_time" class="exam-form-input" required>
                                </div>
                            </div>
                        </div>
                        <div class="exam-form-group">
                            <label for="total_marks" class="exam-form-label"><?php echo t('total_marks'); ?>:</label>
                            <input type="number" name="total_marks" id="total_marks" class="exam-form-input" min="0" required>
                        </div>
                        <div class="exam-form-actions">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                            <button type="submit" name="add_exam" class="btn btn-primary"><?php echo t('add_exam'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تعديل اختبار -->
    <div class="modal fade" id="editExamModal" tabindex="-1" aria-labelledby="editExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExamModalLabel"><?php echo t('edit_exam'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <input type="hidden" name="exam_id" id="edit_exam_id">
                        <div class="exam-form-group">
                            <label for="edit_course_id" class="exam-form-label"><?php echo t('course'); ?>:</label>
                            <select name="course_id" id="edit_course_id" class="exam-form-select" required>
                                <option value=""><?php echo t('select_course'); ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="exam-form-group">
                            <label for="edit_title" class="exam-form-label"><?php echo t('exam_title'); ?>:</label>
                            <input type="text" name="title" id="edit_title" class="exam-form-input" required>
                        </div>
                        <div class="exam-form-group">
                            <label for="edit_description" class="exam-form-label"><?php echo t('description'); ?>:</label>
                            <textarea name="description" id="edit_description" class="exam-form-textarea"></textarea>
                        </div>
                        <div class="exam-form-group">
                            <label for="edit_exam_date" class="exam-form-label"><?php echo t('exam_date'); ?>:</label>
                            <input type="date" name="exam_date" id="edit_exam_date" class="exam-form-input" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="exam-form-group">
                                    <label for="edit_start_time" class="exam-form-label"><?php echo t('start_time'); ?>:</label>
                                    <input type="time" name="start_time" id="edit_start_time" class="exam-form-input" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="exam-form-group">
                                    <label for="edit_end_time" class="exam-form-label"><?php echo t('end_time'); ?>:</label>
                                    <input type="time" name="end_time" id="edit_end_time" class="exam-form-input" required>
                                </div>
                            </div>
                        </div>
                        <div class="exam-form-group">
                            <label for="edit_total_marks" class="exam-form-label"><?php echo t('total_marks'); ?>:</label>
                            <input type="number" name="total_marks" id="edit_total_marks" class="exam-form-input" min="0" required>
                        </div>
                        <div class="exam-form-actions">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                            <button type="submit" name="edit_exam" class="btn btn-primary"><?php echo t('save_changes'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال حذف اختبار -->
    <div class="modal fade" id="deleteExamModal" tabindex="-1" aria-labelledby="deleteExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteExamModalLabel"><?php echo t('delete_exam'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo t('delete_exam_confirmation'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <form action="" method="post">
                        <input type="hidden" name="exam_id" id="delete_exam_id">
                        <button type="submit" name="delete_exam" class="btn btn-danger"><?php echo t('delete'); ?></button>
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
            
            // تعبئة بيانات مودال تعديل الاختبار
            const editButtons = document.querySelectorAll('.edit-exam');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const course = this.getAttribute('data-course');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const date = this.getAttribute('data-date');
                    const start = this.getAttribute('data-start');
                    const end = this.getAttribute('data-end');
                    const marks = this.getAttribute('data-marks');
                    
                    document.getElementById('edit_exam_id').value = id;
                    document.getElementById('edit_course_id').value = course;
                    document.getElementById('edit_title').value = title;
                    document.getElementById('edit_description').value = description;
                    document.getElementById('edit_exam_date').value = date;
                    document.getElementById('edit_start_time').value = start;
                    document.getElementById('edit_end_time').value = end;
                    document.getElementById('edit_total_marks').value = marks;
                });
            });
            
            // تعبئة بيانات مودال حذف الاختبار
            const deleteButtons = document.querySelectorAll('.delete-exam');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    document.getElementById('delete_exam_id').value = id;
                });
            });
            
            // التحقق من صحة وقت البداية والنهاية
            const startTimeInputs = document.querySelectorAll('#start_time, #edit_start_time');
            const endTimeInputs = document.querySelectorAll('#end_time, #edit_end_time');
            
            startTimeInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    const startTime = this.value;
                    const endTime = endTimeInputs[index].value;
                    
                    if (startTime && endTime && startTime >= endTime) {
                        alert('<?php echo t("start_time_before_end_time"); ?>');
                        this.value = '';
                    }
                });
            });
            
            endTimeInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    const startTime = startTimeInputs[index].value;
                    const endTime = this.value;
                    
                    if (startTime && endTime && startTime >= endTime) {
                        alert('<?php echo t("end_time_after_start_time"); ?>');
                        this.value = '';
                    }
                });
            });
        });
    </script>
</body>
</html>
