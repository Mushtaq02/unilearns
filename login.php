<?php
/**
 * صفحة تسجيل الدخول لنظام UniverBoard
 * تتيح للمستخدمين تسجيل الدخول إلى الأنظمة المختلفة
 */

// استيراد ملفات الإعدادات والدوال
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// التحقق من وجود جلسة نشطة
if (isLoggedIn()) {
    // إعادة توجيه المستخدم إلى لوحة التحكم المناسبة
    switch ($_SESSION['user_type']) {
        case 'student':
            redirect('student/dashboard.php');
            break;
        case 'teacher':
            redirect('teacher/dashboard.php');
            break;
        case 'college_admin':
            redirect('college/dashboard.php');
            break;
        case 'system_admin':
            redirect('admin/dashboard.php');
            break;
    }
}

// تعيين نوع المستخدم الافتراضي
$userType = isset($_GET['type']) ? $_GET['type'] : 'student';

// التحقق من صحة نوع المستخدم
$validUserTypes = ['student', 'teacher', 'college_admin', 'system_admin'];
if (!in_array($userType, $validUserTypes)) {
    $userType = 'student';
}

// تعيين اللغة الافتراضية
$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : SITE_LANG;

// تعيين المظهر الافتراضي
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : SITE_THEME;

// تحميل ملفات اللغة (سيتم إنشاؤها لاحقاً)
$translations = [];
if ($lang === 'ar') {
    include 'includes/lang/ar.php';
} else {
    include 'includes/lang/en.php';
}

// دالة ترجمة النصوص
function t($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}

// معالجة نموذج تسجيل الدخول
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من وجود البيانات المطلوبة
    if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['user_type'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $userType = $_POST['user_type'];
        
        // التحقق من صحة نوع المستخدم
        if (!in_array($userType, $validUserTypes)) {
            $error = t('login_invalid_user_type');
        } else {
            // محاولة تسجيل الدخول
            $result = login($email, $password, $userType);
            
            if ($result['success']) {
                // إعادة توجيه المستخدم إلى لوحة التحكم المناسبة
                switch ($userType) {
                    case 'student':
                        redirect('student/dashboard.php');
                        break;
                    case 'teacher':
                        redirect('teacher/dashboard.php');
                        break;
                    case 'college_admin':
                        redirect('college/dashboard.php');
                        break;
                    case 'system_admin':
                        redirect('admin/dashboard.php');
                        break;
                }
            } else {
                $error = $result['message'];
            }
        }
    } else {
        $error = t('login_missing_fields');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('login_title'); ?></title>
    
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
</head>
<body class="theme-<?php echo $theme; ?>">
    <!-- الشريط العلوي (Header) -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- الشعار -->
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
                <?php echo SITE_NAME; ?>
            </a>
            
            <!-- أزرار اللغة والمظهر -->
            <div class="ms-auto d-flex">
                <!-- زر تبديل اللغة -->
                <div class="dropdown me-2">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe"></i> <?php echo $lang === 'ar' ? 'العربية' : 'English'; ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?lang=ar<?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>">العربية</a></li>
                        <li><a class="dropdown-item" href="?lang=en<?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>">English</a></li>
                    </ul>
                </div>
                
                <!-- زر تبديل المظهر -->
                <button class="btn btn-outline-light" id="themeToggle">
                    <i class="fas <?php echo $theme === 'light' ? 'fa-moon' : 'fa-sun'; ?>"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- قسم تسجيل الدخول -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="80" class="mb-3">
                                <h1 class="h3"><?php echo t('login_heading'); ?></h1>
                                <p class="text-muted"><?php echo t('login_subheading'); ?></p>
                            </div>
                            
                            <!-- عرض رسائل الخطأ والنجاح -->
                            <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- أزرار اختيار نوع المستخدم -->
                            <div class="user-type-tabs mb-4">
                                <div class="nav nav-pills nav-fill" id="userTypeTabs" role="tablist">
                                    <a class="nav-link <?php echo $userType === 'student' ? 'active' : ''; ?>" href="login.php?type=student">
                                        <i class="fas fa-user-graduate me-2"></i> <?php echo t('user_type_student'); ?>
                                    </a>
                                    <a class="nav-link <?php echo $userType === 'teacher' ? 'active' : ''; ?>" href="login.php?type=teacher">
                                        <i class="fas fa-chalkboard-teacher me-2"></i> <?php echo t('user_type_teacher'); ?>
                                    </a>
                                    <a class="nav-link <?php echo $userType === 'college_admin' ? 'active' : ''; ?>" href="login.php?type=college_admin">
                                        <i class="fas fa-university me-2"></i> <?php echo t('user_type_college'); ?>
                                    </a>
                                    <a class="nav-link <?php echo $userType === 'system_admin' ? 'active' : ''; ?>" href="login.php?type=system_admin">
                                        <i class="fas fa-user-shield me-2"></i> <?php echo t('user_type_admin'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- نموذج تسجيل الدخول -->
                            <form method="post" action="login.php">
                                <input type="hidden" name="user_type" value="<?php echo $userType; ?>">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo t('login_email'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?php echo t('login_password'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                                        <label class="form-check-label" for="rememberMe">
                                            <?php echo t('login_remember_me'); ?>
                                        </label>
                                    </div>
                                    <a href="forgot-password.php" class="text-decoration-none">
                                        <?php echo t('login_forgot_password'); ?>
                                    </a>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i> <?php echo t('login_button'); ?>
                                    </button>
                                </div>
                            </form>
                            
                            <!-- روابط إضافية -->
                            <div class="text-center mt-4">
                                <p class="mb-0">
                                    <?php echo t('login_need_help'); ?> <a href="help.php" class="text-decoration-none"><?php echo t('login_help_link'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- تذييل الصفحة (Footer) -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0"><?php echo t('footer_copyright'); ?> &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. <?php echo t('footer_rights'); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white text-decoration-none me-3"><?php echo t('footer_privacy'); ?></a>
                    <a href="terms.php" class="text-white text-decoration-none me-3"><?php echo t('footer_terms'); ?></a>
                    <a href="help.php" class="text-white text-decoration-none"><?php echo t('footer_help'); ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- ملف JavaScript الرئيسي -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // تبديل اللغة
        function switchLanguage(lang) {
            document.cookie = "lang=" + lang + "; path=/; max-age=31536000";
            
            // الحفاظ على معلمة نوع المستخدم عند تغيير اللغة
            const urlParams = new URLSearchParams(window.location.search);
            const userType = urlParams.get('type');
            
            if (userType) {
                window.location.href = "login.php?lang=" + lang + "&type=" + userType;
            } else {
                window.location.href = "login.php?lang=" + lang;
            }
        }
        
        // تبديل المظهر
        function switchTheme(theme) {
            document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
            document.body.className = "theme-" + theme;
            
            // تغيير أيقونة الزر
            const themeIcon = document.querySelector("#themeToggle i");
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            
            // تحميل ملف CSS المناسب
            const themeLink = document.querySelector("link[href^='assets/css/theme-']");
            themeLink.href = "assets/css/theme-" + theme + ".css";
        }
        
        // إعداد مستمعي الأحداث
        document.addEventListener("DOMContentLoaded", function() {
            // زر تبديل المظهر
            const themeToggle = document.getElementById("themeToggle");
            themeToggle.addEventListener("click", function() {
                const currentTheme = document.body.className.includes("theme-light") ? "light" : "dark";
                const newTheme = currentTheme === "light" ? "dark" : "light";
                switchTheme(newTheme);
            });
            
            // روابط تبديل اللغة
            const langLinks = document.querySelectorAll("a[href^='?lang=']");
            langLinks.forEach(link => {
                link.addEventListener("click", function(e) {
                    e.preventDefault();
                    const lang = this.href.split("=")[1].split("&")[0];
                    switchLanguage(lang);
                });
            });
            
            // زر إظهار/إخفاء كلمة المرور
            const togglePassword = document.getElementById("togglePassword");
            const passwordInput = document.getElementById("password");
            
            togglePassword.addEventListener("click", function() {
                const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                passwordInput.setAttribute("type", type);
                
                // تغيير الأيقونة
                const icon = this.querySelector("i");
                icon.className = type === "password" ? "fas fa-eye" : "fas fa-eye-slash";
            });
        });
    </script>
</body>
</html>
