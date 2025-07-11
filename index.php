<?php
/**
 * الصفحة الرئيسية لنظام UniverBoard
 * تعرض واجهة الموقع الرئيسية للزوار
 */

// استيراد ملفات الإعدادات والدوال
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

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
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('home_title'); ?></title>
    
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
    <header class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <!-- الشعار -->
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
                <?php echo SITE_NAME; ?>
            </a>
            
            <!-- زر القائمة للشاشات الصغيرة -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- القائمة الرئيسية -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home"><?php echo t('nav_home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features"><?php echo t('nav_features'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#systems"><?php echo t('nav_systems'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about"><?php echo t('nav_about'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact"><?php echo t('nav_contact'); ?></a>
                    </li>
                </ul>
                
                <!-- أزرار اللغة والمظهر وتسجيل الدخول -->
                <div class="d-flex">
                    <!-- زر تبديل اللغة -->
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-globe"></i> <?php echo $lang === 'ar' ? 'العربية' : 'English'; ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                            <li><a class="dropdown-item" href="?lang=en">English</a></li>
                        </ul>
                    </div>
                    
                    <!-- زر تبديل المظهر -->
                    <button class="btn btn-outline-light me-2" id="themeToggle">
                        <i class="fas <?php echo $theme === 'light' ? 'fa-moon' : 'fa-sun'; ?>"></i>
                    </button>
                    
                    <!-- زر تسجيل الدخول -->
                    <a href="login.php" class="btn btn-light">
                        <i class="fas fa-sign-in-alt"></i> <?php echo t('login_button'); ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- قسم الترحيب (Hero Section) -->
    <section id="home" class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4"><?php echo t('hero_title'); ?></h1>
                    <p class="lead mb-4"><?php echo t('hero_description'); ?></p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="login.php" class="btn btn-light btn-lg px-4 me-md-2">
                            <i class="fas fa-sign-in-alt"></i> <?php echo t('login_button'); ?>
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg px-4">
                            <?php echo t('learn_more_button'); ?> <i class="fas fa-arrow-down"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                    <img src="assets/images/hero-image.png" alt="<?php echo t('hero_image_alt'); ?>" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- قسم المميزات (Features Section) -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo t('features_title'); ?></h2>
                <p class="section-description"><?php echo t('features_description'); ?></p>
            </div>
            
            <div class="row g-4">
                <!-- ميزة 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white mb-3">
                                <i class="fas fa-graduation-cap fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('feature_1_title'); ?></h3>
                            <p class="card-text"><?php echo t('feature_1_description'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- ميزة 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white mb-3">
                                <i class="fas fa-laptop-code fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('feature_2_title'); ?></h3>
                            <p class="card-text"><?php echo t('feature_2_description'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- ميزة 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white mb-3">
                                <i class="fas fa-mobile-alt fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('feature_3_title'); ?></h3>
                            <p class="card-text"><?php echo t('feature_3_description'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- ميزة 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white mb-3">
                                <i class="fas fa-bell fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('feature_4_title'); ?></h3>
                            <p class="card-text"><?php echo t('feature_4_description'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- ميزة 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white mb-3">
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('feature_5_title'); ?></h3>
                            <p class="card-text"><?php echo t('feature_5_description'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- ميزة 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary text-white mb-3">
                                <i class="fas fa-tasks fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('feature_6_title'); ?></h3>
                            <p class="card-text"><?php echo t('feature_6_description'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم الأنظمة (Systems Section) -->
    <section id="systems" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo t('systems_title'); ?></h2>
                <p class="section-description"><?php echo t('systems_description'); ?></p>
            </div>
            
            <div class="row g-4">
                <!-- نظام الطلاب -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="system-icon bg-info text-white mb-3">
                                <i class="fas fa-user-graduate fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('system_student_title'); ?></h3>
                            <p class="card-text"><?php echo t('system_student_description'); ?></p>
                            <a href="login.php?type=student" class="btn btn-outline-primary mt-3">
                                <?php echo t('system_login_button'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- نظام المعلمين -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="system-icon bg-success text-white mb-3">
                                <i class="fas fa-chalkboard-teacher fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('system_teacher_title'); ?></h3>
                            <p class="card-text"><?php echo t('system_teacher_description'); ?></p>
                            <a href="login.php?type=teacher" class="btn btn-outline-primary mt-3">
                                <?php echo t('system_login_button'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- نظام الكليات -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="system-icon bg-warning text-white mb-3">
                                <i class="fas fa-university fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('system_college_title'); ?></h3>
                            <p class="card-text"><?php echo t('system_college_description'); ?></p>
                            <a href="login.php?type=college_admin" class="btn btn-outline-primary mt-3">
                                <?php echo t('system_login_button'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- نظام المشرف -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="system-icon bg-danger text-white mb-3">
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                            <h3 class="card-title h5"><?php echo t('system_admin_title'); ?></h3>
                            <p class="card-text"><?php echo t('system_admin_description'); ?></p>
                            <a href="login.php?type=system_admin" class="btn btn-outline-primary mt-3">
                                <?php echo t('system_login_button'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم الإحصائيات (Statistics Section) -->
    <section id="statistics" class="py-5 bg-primary text-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo t('statistics_title'); ?></h2>
                <p class="section-description"><?php echo t('statistics_description'); ?></p>
            </div>
            
            <div class="row g-4 text-center">
                <!-- إحصائية 1 -->
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number display-4 fw-bold">50+</div>
                        <div class="stat-label"><?php echo t('stat_colleges'); ?></div>
                    </div>
                </div>
                
                <!-- إحصائية 2 -->
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number display-4 fw-bold">10K+</div>
                        <div class="stat-label"><?php echo t('stat_students'); ?></div>
                    </div>
                </div>
                
                <!-- إحصائية 3 -->
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number display-4 fw-bold">500+</div>
                        <div class="stat-label"><?php echo t('stat_teachers'); ?></div>
                    </div>
                </div>
                
                <!-- إحصائية 4 -->
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number display-4 fw-bold">1K+</div>
                        <div class="stat-label"><?php echo t('stat_courses'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم التحميل (Download Section) -->
    <section id="download" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo t('download_title'); ?></h2>
                <p class="section-description"><?php echo t('download_description'); ?></p>
            </div>
            
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center">
                    <img src="assets/images/app-preview.png" alt="<?php echo t('app_preview_alt'); ?>" class="img-fluid rounded shadow">
                </div>
                <div class="col-md-6">
                    <div class="row g-4">
                        <!-- تطبيق الطلاب -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="app-icon bg-primary text-white me-3">
                                            <i class="fas fa-user-graduate fa-2x"></i>
                                        </div>
                                        <div>
                                            <h3 class="card-title h5 mb-1"><?php echo t('app_student_title'); ?></h3>
                                            <p class="card-text mb-2"><?php echo t('app_student_description'); ?></p>
                                            <a href="#" class="btn btn-primary">
                                                <i class="fab fa-android"></i> <?php echo t('download_button'); ?>
                                            </a>
                                            <div class="mt-2">
                                                <img src="assets/images/qr-student.png" alt="QR Code" height="80" class="qr-code">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- تطبيق المعلمين -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="app-icon bg-success text-white me-3">
                                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                        </div>
                                        <div>
                                            <h3 class="card-title h5 mb-1"><?php echo t('app_teacher_title'); ?></h3>
                                            <p class="card-text mb-2"><?php echo t('app_teacher_description'); ?></p>
                                            <a href="#" class="btn btn-success">
                                                <i class="fab fa-android"></i> <?php echo t('download_button'); ?>
                                            </a>
                                            <div class="mt-2">
                                                <img src="assets/images/qr-teacher.png" alt="QR Code" height="80" class="qr-code">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم التواصل (Contact Section) -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo t('contact_title'); ?></h2>
                <p class="section-description"><?php echo t('contact_description'); ?></p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="h5 mb-4"><?php echo t('contact_form_title'); ?></h3>
                            <form id="contactForm">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?php echo t('contact_name'); ?></label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo t('contact_email'); ?></label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label"><?php echo t('contact_subject'); ?></label>
                                    <input type="text" class="form-control" id="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label"><?php echo t('contact_message'); ?></label>
                                    <textarea class="form-control" id="message" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> <?php echo t('contact_send_button'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="h5 mb-4"><?php echo t('contact_info_title'); ?></h3>
                            <div class="d-flex mb-3">
                                <div class="contact-icon bg-primary text-white me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h4 class="h6"><?php echo t('contact_email_label'); ?></h4>
                                    <p class="mb-0">info@univerboard.com</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="contact-icon bg-primary text-white me-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h4 class="h6"><?php echo t('contact_phone_label'); ?></h4>
                                    <p class="mb-0">+966 12 345 6789</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="contact-icon bg-primary text-white me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h4 class="h6"><?php echo t('contact_address_label'); ?></h4>
                                    <p class="mb-0"><?php echo t('contact_address'); ?></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h4 class="h6"><?php echo t('contact_social_label'); ?></h4>
                                <div class="social-icons">
                                    <a href="#" class="social-icon bg-primary text-white">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" class="social-icon bg-info text-white">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="social-icon bg-danger text-white">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="#" class="social-icon bg-dark text-white">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- تذييل الصفحة (Footer) -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="footer-brand mb-3">
                        <img src="assets/images/logo-white.png" alt="<?php echo SITE_NAME; ?>" height="40">
                        <span class="ms-2"><?php echo SITE_NAME; ?></span>
                    </div>
                    <p><?php echo t('footer_description'); ?></p>
                </div>
                <div class="col-md-2">
                    <h5 class="footer-title"><?php echo t('footer_links_title'); ?></h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#home"><?php echo t('nav_home'); ?></a></li>
                        <li><a href="#features"><?php echo t('nav_features'); ?></a></li>
                        <li><a href="#systems"><?php echo t('nav_systems'); ?></a></li>
                        <li><a href="#about"><?php echo t('nav_about'); ?></a></li>
                        <li><a href="#contact"><?php echo t('nav_contact'); ?></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="footer-title"><?php echo t('footer_systems_title'); ?></h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="login.php?type=student"><?php echo t('system_student_title'); ?></a></li>
                        <li><a href="login.php?type=teacher"><?php echo t('system_teacher_title'); ?></a></li>
                        <li><a href="login.php?type=college_admin"><?php echo t('system_college_title'); ?></a></li>
                        <li><a href="login.php?type=system_admin"><?php echo t('system_admin_title'); ?></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="footer-title"><?php echo t('footer_legal_title'); ?></h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="privacy.php"><?php echo t('footer_privacy'); ?></a></li>
                        <li><a href="terms.php"><?php echo t('footer_terms'); ?></a></li>
                        <li><a href="faq.php"><?php echo t('footer_faq'); ?></a></li>
                        <li><a href="help.php"><?php echo t('footer_help'); ?></a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0"><?php echo t('footer_copyright'); ?> &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. <?php echo t('footer_rights'); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-globe"></i> <?php echo $lang === 'ar' ? 'العربية' : 'English'; ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                            <li><a class="dropdown-item" href="?lang=en">English</a></li>
                        </ul>
                    </div>
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
            location.reload();
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
                    const lang = this.href.split("=")[1];
                    switchLanguage(lang);
                });
            });
            
            // نموذج الاتصال
            const contactForm = document.getElementById("contactForm");
            if (contactForm) {
                contactForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    alert("<?php echo t('contact_form_success'); ?>");
                    contactForm.reset();
                });
            }
        });
    </script>
</body>
</html>
