<?php
/**
 * صفحة الملف الشخصي للكلية في نظام UniverBoard
 * تتيح لمسؤول الكلية عرض وتعديل معلومات الكلية
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

// معالجة تحديث معلومات الكلية
$update_success = false;
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_college') {
    $college_name = filter_input(INPUT_POST, 'college_name', FILTER_SANITIZE_STRING);
    $college_code = filter_input(INPUT_POST, 'college_code', FILTER_SANITIZE_STRING);
    $college_description = filter_input(INPUT_POST, 'college_description', FILTER_SANITIZE_STRING);
    $college_address = filter_input(INPUT_POST, 'college_address', FILTER_SANITIZE_STRING);
    $college_phone = filter_input(INPUT_POST, 'college_phone', FILTER_SANITIZE_STRING);
    $college_email = filter_input(INPUT_POST, 'college_email', FILTER_SANITIZE_EMAIL);
    $college_website = filter_input(INPUT_POST, 'college_website', FILTER_SANITIZE_URL);
    
    // التحقق من البيانات
    if (empty($college_name) || empty($college_code)) {
        $update_error = t('name_and_code_required');
    } else {
        // تحديث معلومات الكلية (دالة وهمية، يجب استبدالها بالدالة الفعلية)
        $result = update_college_info($db, $college_id, $college_name, $college_code, $college_description, $college_address, $college_phone, $college_email, $college_website);
        
        if ($result) {
            $update_success = true;
            // تحديث المعلومات المحلية
            $college['name'] = $college_name;
            $college['code'] = $college_code;
            $college['description'] = $college_description;
            $college['address'] = $college_address;
            $college['phone'] = $college_phone;
            $college['email'] = $college_email;
            $college['website'] = $college_website;
        } else {
            $update_error = t('update_failed');
        }
    }
}

// معالجة تحديث شعار الكلية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_logo') {
    if (isset($_FILES['college_logo']) && $_FILES['college_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2 ميجابايت
        
        if (!in_array($_FILES['college_logo']['type'], $allowed_types)) {
            $update_error = t('invalid_image_type');
        } elseif ($_FILES['college_logo']['size'] > $max_size) {
            $update_error = t('image_too_large');
        } else {
            // تحميل الشعار (دالة وهمية، يجب استبدالها بالدالة الفعلية)
            $result = upload_college_logo($db, $college_id, $_FILES['college_logo']);
            
            if ($result) {
                $update_success = true;
                // تحديث مسار الشعار المحلي
                $college['logo'] = $result;
            } else {
                $update_error = t('logo_upload_failed');
            }
        }
    } else {
        $update_error = t('logo_upload_error');
    }
}

// الحصول على إحصائيات الكلية
$stats = get_college_stats($db, $college_id);

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function update_college_info($db, $college_id, $name, $code, $description, $address, $phone, $email, $website) {
    // في الواقع، يجب تحديث معلومات الكلية في قاعدة البيانات
    return true;
}

function upload_college_logo($db, $college_id, $file) {
    // في الواقع، يجب تحميل الشعار وحفظه في المجلد المناسب وتحديث المسار في قاعدة البيانات
    return 'assets/images/college_logo.png';
}

function get_college_stats($db, $college_id) {
    // في الواقع، يجب استرجاع إحصائيات الكلية من قاعدة البيانات
    return [
        'departments_count' => 3,
        'programs_count' => 5,
        'courses_count' => 45,
        'teachers_count' => 28,
        'students_count' => 450,
        'male_students_count' => 280,
        'female_students_count' => 170,
        'active_students_count' => 430,
        'graduated_students_count' => 120,
        'current_semester' => 'الفصل الثاني 2024-2025'
    ];
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
        'description' => 'كلية متخصصة في علوم الحاسب وتقنية المعلومات',
        'address' => 'المدينة الجامعية، طريق الملك عبدالله، الرياض',
        'phone' => '+966 11 4567890',
        'email' => 'cs-college@example.edu.sa',
        'website' => 'https://cs.example.edu.sa',
        'logo' => 'assets/images/college_logo.png',
        'established_date' => '2005-09-01',
        'dean_name' => 'د. عبدالله العمري'
    ];
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('college_profile'); ?></title>
    
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
        
        /* تنسيقات خاصة بصفحة الملف الشخصي للكلية */
        .college-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .college-logo {
            width: 120px;
            height: 120px;
            border-radius: 0.5rem;
            object-fit: contain;
            background-color: white;
            padding: 0.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-right: 1.5rem;
        }
        
        [dir="rtl"] .college-logo {
            margin-right: 0;
            margin-left: 1.5rem;
        }
        
        .theme-dark .college-logo {
            background-color: var(--dark-bg-alt);
        }
        
        .college-info h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .college-info p {
            color: var(--gray-color);
            margin-bottom: 0.25rem;
        }
        
        .college-info .badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            margin-top: 0.5rem;
        }
        
        .stats-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            background-color: white;
            height: 100%;
            transition: all 0.3s;
        }
        
        .theme-dark .stats-card {
            background-color: var(--dark-bg);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .stats-title {
            font-size: 1rem;
            color: var(--gray-color);
        }
        
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
        
        .logo-preview {
            width: 150px;
            height: 150px;
            border-radius: 0.5rem;
            object-fit: contain;
            background-color: white;
            padding: 0.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .theme-dark .logo-preview {
            background-color: var(--dark-bg-alt);
        }
        
        .nav-tabs {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .theme-dark .nav-tabs {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .nav-tabs .nav-link:hover {
            border-color: rgba(0, 48, 73, 0.3);
        }
        
        .nav-tabs .nav-link.active {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
        }
        
        .tab-content {
            padding-top: 1rem;
        }
        
        .gender-chart {
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 0 0.5rem;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 0.5rem;
        }
        
        [dir="rtl"] .legend-color {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .legend-text {
            font-size: 0.9rem;
        }
        
        .contact-info {
            margin-bottom: 1.5rem;
        }
        
        .contact-info i {
            width: 20px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .contact-info i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .contact-info a {
            color: var(--text-color);
            text-decoration: none;
        }
        
        .contact-info a:hover {
            color: var(--primary-color);
        }
        
        .established-date {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: rgba(0, 48, 73, 0.05);
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        
        .theme-dark .established-date {
            background-color: rgba(255, 255, 255, 0.05);
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
                    <a class="nav-link" href="college_messages.php">
                        <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('settings'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link active" href="college_profile.php">
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
                        <span class="badge bg-success">3</span>
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
            <h1 class="page-title"><?php echo t('college_profile'); ?></h1>
            <p class="page-subtitle"><?php echo t('view_and_edit_college_information'); ?></p>
        </div>
        
        <!-- رسالة نجاح التحديث -->
        <?php if ($update_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo t('update_success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- رسالة خطأ التحديث -->
        <?php if (!empty($update_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $update_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- معلومات الكلية -->
        <div class="college-header">
            <img src="<?php echo $college['logo']; ?>" alt="<?php echo $college['name']; ?>" class="college-logo">
            <div class="college-info">
                <h1><?php echo $college['name']; ?></h1>
                <p><?php echo $college['description']; ?></p>
                <p><i class="fas fa-code me-2"></i> <?php echo t('college_code'); ?>: <strong><?php echo $college['code']; ?></strong></p>
                <span class="badge bg-primary"><?php echo t('current_semester'); ?>: <?php echo $stats['current_semester']; ?></span>
            </div>
        </div>
        
        <!-- علامات التبويب -->
        <ul class="nav nav-tabs" id="collegeProfileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="fas fa-info-circle me-2"></i> <?php echo t('overview'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button" role="tab" aria-controls="statistics" aria-selected="false">
                    <i class="fas fa-chart-pie me-2"></i> <?php echo t('statistics'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab" aria-controls="edit" aria-selected="false">
                    <i class="fas fa-edit me-2"></i> <?php echo t('edit_information'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab" aria-controls="logo" aria-selected="false">
                    <i class="fas fa-image me-2"></i> <?php echo t('change_logo'); ?>
                </button>
            </li>
        </ul>
        
        <!-- محتوى علامات التبويب -->
        <div class="tab-content" id="collegeProfileTabsContent">
            <!-- نظرة عامة -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i> <?php echo t('college_information'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="contact-info">
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $college['address']; ?></p>
                                </div>
                                <div class="contact-info">
                                    <p><i class="fas fa-phone"></i> <a href="tel:<?php echo $college['phone']; ?>"><?php echo $college['phone']; ?></a></p>
                                </div>
                                <div class="contact-info">
                                    <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $college['email']; ?>"><?php echo $college['email']; ?></a></p>
                                </div>
                                <div class="contact-info">
                                    <p><i class="fas fa-globe"></i> <a href="<?php echo $college['website']; ?>" target="_blank"><?php echo $college['website']; ?></a></p>
                                </div>
                                <div class="contact-info">
                                    <p><i class="fas fa-user-tie"></i> <?php echo t('dean'); ?>: <?php echo $college['dean_name']; ?></p>
                                </div>
                                <div class="established-date">
                                    <i class="fas fa-calendar-alt me-2"></i> <?php echo t('established_date'); ?>: <?php echo date('d/m/Y', strtotime($college['established_date'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> <?php echo t('quick_stats'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div>
                                                <div class="stats-number"><?php echo $stats['departments_count']; ?></div>
                                                <div class="stats-title"><?php echo t('departments'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div>
                                                <div class="stats-number"><?php echo $stats['programs_count']; ?></div>
                                                <div class="stats-title"><?php echo t('programs'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div>
                                                <div class="stats-number"><?php echo $stats['courses_count']; ?></div>
                                                <div class="stats-title"><?php echo t('courses'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                            <div>
                                                <div class="stats-number"><?php echo $stats['teachers_count']; ?></div>
                                                <div class="stats-title"><?php echo t('teachers'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <div>
                                                <div class="stats-number"><?php echo $stats['students_count']; ?></div>
                                                <div class="stats-title"><?php echo t('students'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                            <div>
                                                <div class="stats-number"><?php echo $stats['graduated_students_count']; ?></div>
                                                <div class="stats-title"><?php echo t('graduates'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- الإحصائيات -->
            <div class="tab-pane fade" id="statistics" role="tabpanel" aria-labelledby="statistics-tab">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-users me-2"></i> <?php echo t('students_by_gender'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="gender-chart">
                                    <canvas id="genderChart"></canvas>
                                </div>
                                <div class="chart-legend">
                                    <div class="legend-item">
                                        <div class="legend-color" style="background-color: #003049;"></div>
                                        <div class="legend-text"><?php echo t('male'); ?> (<?php echo $stats['male_students_count']; ?>)</div>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background-color: #669bbc;"></div>
                                        <div class="legend-text"><?php echo t('female'); ?> (<?php echo $stats['female_students_count']; ?>)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-user-graduate me-2"></i> <?php echo t('students_status'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="gender-chart">
                                    <canvas id="statusChart"></canvas>
                                </div>
                                <div class="chart-legend">
                                    <div class="legend-item">
                                        <div class="legend-color" style="background-color: #28a745;"></div>
                                        <div class="legend-text"><?php echo t('active'); ?> (<?php echo $stats['active_students_count']; ?>)</div>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background-color: #ffc107;"></div>
                                        <div class="legend-text"><?php echo t('graduated'); ?> (<?php echo $stats['graduated_students_count']; ?>)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['departments_count']; ?></div>
                            <div class="stats-title"><?php echo t('departments'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['programs_count']; ?></div>
                            <div class="stats-title"><?php echo t('academic_programs'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['courses_count']; ?></div>
                            <div class="stats-title"><?php echo t('courses'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['teachers_count']; ?></div>
                            <div class="stats-title"><?php echo t('teachers'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stats-number"><?php echo $stats['students_count']; ?></div>
                            <div class="stats-title"><?php echo t('students'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- تعديل المعلومات -->
            <div class="tab-pane fade" id="edit" role="tabpanel" aria-labelledby="edit-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-edit me-2"></i> <?php echo t('edit_college_information'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="update_college">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="college_name" class="form-label"><?php echo t('college_name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="college_name" name="college_name" value="<?php echo $college['name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="college_code" class="form-label"><?php echo t('college_code'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="college_code" name="college_code" value="<?php echo $college['code']; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="college_description" class="form-label"><?php echo t('description'); ?></label>
                                <textarea class="form-control" id="college_description" name="college_description" rows="3"><?php echo $college['description']; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="college_address" class="form-label"><?php echo t('address'); ?></label>
                                <input type="text" class="form-control" id="college_address" name="college_address" value="<?php echo $college['address']; ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="college_phone" class="form-label"><?php echo t('phone'); ?></label>
                                    <input type="text" class="form-control" id="college_phone" name="college_phone" value="<?php echo $college['phone']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="college_email" class="form-label"><?php echo t('email'); ?></label>
                                    <input type="email" class="form-control" id="college_email" name="college_email" value="<?php echo $college['email']; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="college_website" class="form-label"><?php echo t('website'); ?></label>
                                <input type="url" class="form-control" id="college_website" name="college_website" value="<?php echo $college['website']; ?>">
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo t('save_changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- تغيير الشعار -->
            <div class="tab-pane fade" id="logo" role="tabpanel" aria-labelledby="logo-tab">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-image me-2"></i> <?php echo t('change_college_logo'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <p><?php echo t('current_logo'); ?>:</p>
                                    <img src="<?php echo $college['logo']; ?>" alt="<?php echo $college['name']; ?>" class="logo-preview">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_logo">
                                    <div class="mb-3">
                                        <label for="college_logo" class="form-label"><?php echo t('upload_new_logo'); ?></label>
                                        <input type="file" class="form-control" id="college_logo" name="college_logo" accept="image/*" required>
                                        <div class="form-text"><?php echo t('logo_requirements'); ?></div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i> <?php echo t('upload_logo'); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
                
                // إعادة رسم الرسوم البيانية عند تغيير المظهر
                renderCharts();
            });
            
            // رسم الرسوم البيانية
            renderCharts();
            
            function renderCharts() {
                const isDarkTheme = document.body.className.includes('theme-dark');
                const textColor = isDarkTheme ? '#ffffff' : '#333333';
                
                // رسم بياني للطلاب حسب الجنس
                const genderCtx = document.getElementById('genderChart').getContext('2d');
                const genderData = {
                    labels: ['<?php echo t('male'); ?>', '<?php echo t('female'); ?>'],
                    datasets: [{
                        data: [<?php echo $stats['male_students_count']; ?>, <?php echo $stats['female_students_count']; ?>],
                        backgroundColor: ['#003049', '#669bbc'],
                        borderWidth: 0
                    }]
                };
                
                if (window.genderChart) {
                    window.genderChart.destroy();
                }
                
                window.genderChart = new Chart(genderCtx, {
                    type: 'doughnut',
                    data: genderData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // رسم بياني لحالة الطلاب
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                const statusData = {
                    labels: ['<?php echo t('active'); ?>', '<?php echo t('graduated'); ?>'],
                    datasets: [{
                        data: [<?php echo $stats['active_students_count']; ?>, <?php echo $stats['graduated_students_count']; ?>],
                        backgroundColor: ['#28a745', '#ffc107'],
                        borderWidth: 0
                    }]
                };
                
                if (window.statusChart) {
                    window.statusChart.destroy();
                }
                
                window.statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // معاينة الشعار قبل التحميل
            document.getElementById('college_logo').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const logoPreview = document.querySelector('.logo-preview');
                        logoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>
