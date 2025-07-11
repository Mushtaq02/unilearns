<?php
/**
 * صفحة المساعدة لنظام UniverBoard
 * توفر دليل استخدام النظام والإجابة على الأسئلة الشائعة
 */

// استيراد ملفات الإعدادات والدوال
require_once 'includes/config.php';
require_once 'includes/functions.php';

// تعيين اللغة الافتراضية
$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : SITE_LANG;

// تعيين المظهر الافتراضي
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : SITE_THEME;

// تحميل ملفات اللغة
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
    <title><?php echo SITE_NAME; ?> - <?php echo t('help_title'); ?></title>
    
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
        .help-category {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .help-category:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .help-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .faq-item {
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .faq-question {
            cursor: pointer;
            padding: 1rem;
            background-color: rgba(0, 48, 73, 0.05);
            border-left: 4px solid var(--primary-color);
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-answer {
            padding: 1rem;
            display: none;
            border-left: 4px solid var(--secondary-color);
        }
        
        .faq-answer.show {
            display: block;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .search-box input {
            padding-left: 3rem;
            padding-right: 3rem;
            height: 50px;
            border-radius: 25px;
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
        
        .search-box .clear-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
            cursor: pointer;
            display: none;
        }
        
        [dir="rtl"] .search-box .clear-icon {
            right: auto;
            left: 1rem;
        }
        
        .video-tutorial {
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .video-thumbnail {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
        }
        
        .video-thumbnail img {
            transition: transform 0.3s ease;
        }
        
        .video-thumbnail:hover img {
            transform: scale(1.05);
        }
        
        .video-thumbnail .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background-color: rgba(0, 48, 73, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .video-thumbnail:hover .play-icon {
            background-color: var(--primary-color);
        }
    </style>
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
            
            <!-- زر القائمة للشاشات الصغيرة -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- القائمة الرئيسية -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><?php echo t('nav_home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features"><?php echo t('nav_features'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#systems"><?php echo t('nav_systems'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about"><?php echo t('nav_about'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact"><?php echo t('nav_contact'); ?></a>
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

    <!-- محتوى الصفحة -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold"><?php echo t('help_heading'); ?></h1>
                    <p class="lead text-muted"><?php echo t('help_subheading'); ?></p>
                    
                    <!-- مربع البحث -->
                    <div class="search-box mt-4">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control form-control-lg" id="helpSearch" placeholder="ابحث عن المساعدة...">
                        <i class="fas fa-times clear-icon" id="clearSearch"></i>
                    </div>
                </div>
                
                <!-- فئات المساعدة -->
                <div class="row mb-5">
                    <div class="col-md-4 mb-4">
                        <div class="help-category card h-100 text-center p-4">
                            <div class="card-body">
                                <div class="help-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <h3 class="h4">دليل المستخدم</h3>
                                <p class="text-muted">دليل شامل لاستخدام جميع ميزات النظام</p>
                                <a href="#userGuide" class="btn btn-outline-primary mt-2">استعرض الدليل</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="help-category card h-100 text-center p-4">
                            <div class="card-body">
                                <div class="help-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <h3 class="h4">الأسئلة الشائعة</h3>
                                <p class="text-muted">إجابات على الأسئلة المتكررة من المستخدمين</p>
                                <a href="#faq" class="btn btn-outline-primary mt-2">استعرض الأسئلة</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="help-category card h-100 text-center p-4">
                            <div class="card-body">
                                <div class="help-icon">
                                    <i class="fas fa-video"></i>
                                </div>
                                <h3 class="h4">فيديوهات تعليمية</h3>
                                <p class="text-muted">شروحات مرئية لاستخدام ميزات النظام</p>
                                <a href="#videoTutorials" class="btn btn-outline-primary mt-2">شاهد الفيديوهات</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- دليل المستخدم -->
                <div id="userGuide" class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="mb-4">دليل المستخدم</h2>
                        
                        <div class="user-guide-content">
                            <div class="mb-5">
                                <h3 class="h4 mb-3">1. البدء باستخدام النظام</h3>
                                
                                <div class="mb-4">
                                    <h4 class="h5">1.1 تسجيل الدخول</h4>
                                    <p>للوصول إلى نظام يونيفر بورد، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>انتقل إلى صفحة تسجيل الدخول من خلال النقر على زر "تسجيل الدخول" في الشريط العلوي.</li>
                                        <li>حدد نوع المستخدم المناسب (طالب، معلم، إدارة الكلية، مشرف النظام).</li>
                                        <li>أدخل بريدك الإلكتروني وكلمة المرور.</li>
                                        <li>انقر على زر "تسجيل الدخول".</li>
                                    </ol>
                                    <p>إذا نسيت كلمة المرور، يمكنك النقر على رابط "نسيت كلمة المرور؟" واتباع التعليمات لإعادة تعيينها.</p>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">1.2 تخصيص الإعدادات</h4>
                                    <p>يمكنك تخصيص إعدادات النظام وفقًا لتفضيلاتك:</p>
                                    <ul>
                                        <li><strong>تغيير اللغة:</strong> انقر على زر اللغة في الشريط العلوي واختر اللغة المفضلة لديك (العربية أو الإنجليزية).</li>
                                        <li><strong>تغيير المظهر:</strong> انقر على زر المظهر في الشريط العلوي للتبديل بين المظهر الفاتح والداكن.</li>
                                        <li><strong>تحديث الملف الشخصي:</strong> بعد تسجيل الدخول، يمكنك تحديث معلومات ملفك الشخصي من خلال الانتقال إلى صفحة "الملف الشخصي".</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-5">
                                <h3 class="h4 mb-3">2. نظام الطلاب</h3>
                                
                                <div class="mb-4">
                                    <h4 class="h5">2.1 لوحة التحكم</h4>
                                    <p>بعد تسجيل الدخول كطالب، ستظهر لوحة التحكم الرئيسية التي تعرض:</p>
                                    <ul>
                                        <li>ملخص المقررات المسجلة</li>
                                        <li>الواجبات والاختبارات القادمة</li>
                                        <li>الإشعارات والتنبيهات المهمة</li>
                                        <li>التقويم الأكاديمي</li>
                                        <li>إحصائيات الأداء</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">2.2 المقررات الدراسية</h4>
                                    <p>للوصول إلى المقررات الدراسية واستعراضها:</p>
                                    <ol>
                                        <li>انقر على "المقررات" في القائمة الجانبية.</li>
                                        <li>اختر المقرر الذي ترغب في استعراضه.</li>
                                        <li>ستظهر صفحة المقرر التي تعرض المحتوى التعليمي، الواجبات، الاختبارات، والمنتدى.</li>
                                    </ol>
                                    <p>يمكنك تنزيل المواد التعليمية، تقديم الواجبات، والمشاركة في المناقشات من خلال صفحة المقرر.</p>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">2.3 الواجبات والاختبارات</h4>
                                    <p>لإدارة الواجبات والاختبارات:</p>
                                    <ul>
                                        <li><strong>عرض الواجبات:</strong> انقر على "الواجبات" في القائمة الجانبية لعرض جميع الواجبات المطلوبة والمواعيد النهائية.</li>
                                        <li><strong>تقديم واجب:</strong> انقر على الواجب المطلوب، ثم انقر على "تقديم الواجب" وارفع الملفات المطلوبة.</li>
                                        <li><strong>أداء اختبار:</strong> انقر على "الاختبارات" في القائمة الجانبية، ثم اختر الاختبار المطلوب وانقر على "بدء الاختبار".</li>
                                        <li><strong>عرض النتائج:</strong> بعد تصحيح الواجبات والاختبارات، يمكنك عرض النتائج والتعليقات من خلال الانتقال إلى صفحة "النتائج".</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-5">
                                <h3 class="h4 mb-3">3. نظام المعلمين</h3>
                                
                                <div class="mb-4">
                                    <h4 class="h5">3.1 إدارة المقررات</h4>
                                    <p>كمعلم، يمكنك إدارة المقررات الدراسية من خلال:</p>
                                    <ul>
                                        <li><strong>إضافة محتوى:</strong> انقر على "المقررات" في القائمة الجانبية، اختر المقرر، ثم انقر على "إضافة محتوى" لرفع المواد التعليمية.</li>
                                        <li><strong>تنظيم الوحدات:</strong> يمكنك تنظيم المحتوى في وحدات وأقسام لتسهيل الوصول إليه.</li>
                                        <li><strong>إدارة المنتدى:</strong> يمكنك إنشاء مواضيع للنقاش والرد على استفسارات الطلاب.</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">3.2 إنشاء الواجبات والاختبارات</h4>
                                    <p>لإنشاء وإدارة الواجبات والاختبارات:</p>
                                    <ol>
                                        <li>انقر على "الواجبات" أو "الاختبارات" في القائمة الجانبية.</li>
                                        <li>انقر على "إضافة جديد" لإنشاء واجب أو اختبار جديد.</li>
                                        <li>أدخل العنوان، الوصف، المواعيد النهائية، والدرجات.</li>
                                        <li>أضف الأسئلة أو المتطلبات.</li>
                                        <li>انقر على "حفظ" لنشر الواجب أو الاختبار.</li>
                                    </ol>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">3.3 تقييم الطلاب</h4>
                                    <p>لتقييم أعمال الطلاب:</p>
                                    <ul>
                                        <li><strong>تصحيح الواجبات:</strong> انقر على "الواجبات المقدمة" لعرض واجبات الطلاب، ثم انقر على واجب معين لتصحيحه وإضافة تعليقات.</li>
                                        <li><strong>تصحيح الاختبارات:</strong> الاختبارات الموضوعية يتم تصحيحها تلقائيًا، بينما يمكنك تصحيح الأسئلة المقالية يدويًا.</li>
                                        <li><strong>رصد الدرجات:</strong> يمكنك رصد الدرجات وإضافة ملاحظات من خلال صفحة "الدرجات".</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-5">
                                <h3 class="h4 mb-3">4. نظام إدارة الكليات</h3>
                                
                                <div class="mb-4">
                                    <h4 class="h5">4.1 إدارة الأقسام والبرامج</h4>
                                    <p>كمدير كلية، يمكنك إدارة الأقسام والبرامج الأكاديمية من خلال:</p>
                                    <ul>
                                        <li><strong>إضافة قسم:</strong> انقر على "الأقسام" في القائمة الجانبية، ثم انقر على "إضافة قسم جديد".</li>
                                        <li><strong>إضافة برنامج:</strong> انقر على "البرامج الأكاديمية"، ثم انقر على "إضافة برنامج جديد".</li>
                                        <li><strong>تعيين رؤساء الأقسام:</strong> يمكنك تعيين رؤساء الأقسام من خلال صفحة "إدارة الموظفين".</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">4.2 إدارة المقررات والجداول</h4>
                                    <p>لإدارة المقررات والجداول الدراسية:</p>
                                    <ul>
                                        <li><strong>إضافة مقرر:</strong> انقر على "المقررات" في القائمة الجانبية، ثم انقر على "إضافة مقرر جديد".</li>
                                        <li><strong>تعيين المعلمين:</strong> يمكنك تعيين معلمين للمقررات من خلال صفحة "إدارة المقررات".</li>
                                        <li><strong>إنشاء الجداول:</strong> انقر على "الجداول الدراسية"، ثم انقر على "إنشاء جدول جديد" لإنشاء جداول الفصول الدراسية.</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">4.3 إدارة الطلاب والمعلمين</h4>
                                    <p>لإدارة الطلاب والمعلمين:</p>
                                    <ul>
                                        <li><strong>إضافة طالب:</strong> انقر على "الطلاب" في القائمة الجانبية، ثم انقر على "إضافة طالب جديد".</li>
                                        <li><strong>إضافة معلم:</strong> انقر على "المعلمين"، ثم انقر على "إضافة معلم جديد".</li>
                                        <li><strong>تسجيل الطلاب:</strong> يمكنك تسجيل الطلاب في المقررات من خلال صفحة "التسجيل".</li>
                                        <li><strong>إدارة الحضور:</strong> يمكنك متابعة حضور الطلاب والمعلمين من خلال صفحة "الحضور".</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-5">
                                <h3 class="h4 mb-3">5. نظام المشرف</h3>
                                
                                <div class="mb-4">
                                    <h4 class="h5">5.1 إدارة النظام</h4>
                                    <p>كمشرف للنظام، يمكنك إدارة النظام بالكامل من خلال:</p>
                                    <ul>
                                        <li><strong>إدارة المستخدمين:</strong> إضافة، تعديل، وحذف حسابات المستخدمين.</li>
                                        <li><strong>إدارة الصلاحيات:</strong> تحديد صلاحيات الوصول لمختلف أنواع المستخدمين.</li>
                                        <li><strong>إدارة الكليات:</strong> إضافة وتعديل معلومات الكليات والأقسام.</li>
                                        <li><strong>إعدادات النظام:</strong> ضبط إعدادات النظام العامة مثل اللغة الافتراضية والمظهر.</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="h5">5.2 المراقبة والتقارير</h4>
                                    <p>لمراقبة النظام واستخراج التقارير:</p>
                                    <ul>
                                        <li><strong>لوحة المراقبة:</strong> عرض إحصائيات النظام ومؤشرات الأداء الرئيسية.</li>
                                        <li><strong>سجلات النظام:</strong> مراجعة سجلات النظام لتتبع الأنشطة والأحداث.</li>
                                        <li><strong>التقارير:</strong> إنشاء تقارير مخصصة عن مختلف جوانب النظام.</li>
                                        <li><strong>النسخ الاحتياطي:</strong> إدارة النسخ الاحتياطي واستعادة البيانات.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- الأسئلة الشائعة -->
                <div id="faq" class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="mb-4">الأسئلة الشائعة</h2>
                        
                        <div class="faq-content">
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    كيف يمكنني تغيير كلمة المرور الخاصة بي؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq1" class="faq-answer collapse">
                                    <p>لتغيير كلمة المرور الخاصة بك، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>قم بتسجيل الدخول إلى حسابك.</li>
                                        <li>انقر على اسم المستخدم الخاص بك في الزاوية العلوية اليمنى.</li>
                                        <li>اختر "الملف الشخصي" من القائمة المنسدلة.</li>
                                        <li>انقر على علامة التبويب "الأمان".</li>
                                        <li>انقر على "تغيير كلمة المرور".</li>
                                        <li>أدخل كلمة المرور الحالية وكلمة المرور الجديدة مرتين.</li>
                                        <li>انقر على "حفظ التغييرات".</li>
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    ماذا أفعل إذا نسيت كلمة المرور الخاصة بي؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq2" class="faq-answer collapse">
                                    <p>إذا نسيت كلمة المرور الخاصة بك، يمكنك استعادتها باتباع الخطوات التالية:</p>
                                    <ol>
                                        <li>انتقل إلى صفحة تسجيل الدخول.</li>
                                        <li>انقر على رابط "نسيت كلمة المرور؟".</li>
                                        <li>أدخل عنوان البريد الإلكتروني المرتبط بحسابك.</li>
                                        <li>انقر على "إرسال رابط الاستعادة".</li>
                                        <li>ستتلقى بريدًا إلكترونيًا يحتوي على رابط لإعادة تعيين كلمة المرور.</li>
                                        <li>انقر على الرابط واتبع التعليمات لإنشاء كلمة مرور جديدة.</li>
                                    </ol>
                                    <p>إذا لم تتلق البريد الإلكتروني، تحقق من مجلد البريد العشوائي أو اتصل بالدعم الفني.</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    كيف يمكنني تسجيل مقرر دراسي؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq3" class="faq-answer collapse">
                                    <p>لتسجيل مقرر دراسي، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>قم بتسجيل الدخول إلى حسابك كطالب.</li>
                                        <li>انتقل إلى "التسجيل" في القائمة الجانبية.</li>
                                        <li>اختر الفصل الدراسي المطلوب.</li>
                                        <li>استعرض المقررات المتاحة للتسجيل.</li>
                                        <li>حدد المقررات التي ترغب في تسجيلها بالنقر على زر "إضافة".</li>
                                        <li>راجع المقررات المختارة في سلة التسجيل.</li>
                                        <li>انقر على "تأكيد التسجيل" لإكمال العملية.</li>
                                    </ol>
                                    <p>ملاحظة: يجب الالتزام بالمواعيد المحددة للتسجيل والتأكد من استيفاء المتطلبات السابقة للمقررات.</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    كيف يمكنني تقديم واجب؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq4" class="faq-answer collapse">
                                    <p>لتقديم واجب، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>قم بتسجيل الدخول إلى حسابك كطالب.</li>
                                        <li>انتقل إلى "المقررات" واختر المقرر المطلوب.</li>
                                        <li>انقر على علامة التبويب "الواجبات".</li>
                                        <li>اختر الواجب الذي ترغب في تقديمه.</li>
                                        <li>انقر على زر "تقديم الواجب".</li>
                                        <li>ارفع الملفات المطلوبة أو أدخل النص المطلوب.</li>
                                        <li>انقر على "تقديم" لإكمال العملية.</li>
                                    </ol>
                                    <p>تأكد من تقديم الواجب قبل الموعد النهائي وفي التنسيق المطلوب.</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    كيف يمكنني الاطلاع على درجاتي؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq5" class="faq-answer collapse">
                                    <p>للاطلاع على درجاتك، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>قم بتسجيل الدخول إلى حسابك كطالب.</li>
                                        <li>انتقل إلى "الدرجات" في القائمة الجانبية.</li>
                                        <li>اختر الفصل الدراسي المطلوب.</li>
                                        <li>ستظهر قائمة بجميع المقررات ودرجاتك فيها.</li>
                                        <li>انقر على اسم المقرر لعرض تفاصيل الدرجات (الواجبات، الاختبارات، إلخ).</li>
                                    </ol>
                                    <p>يمكنك أيضًا الاطلاع على درجات مقرر معين من خلال صفحة المقرر نفسه بالنقر على علامة التبويب "الدرجات".</p>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    كيف يمكنني التواصل مع المعلم؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq6" class="faq-answer collapse">
                                    <p>للتواصل مع المعلم، يمكنك استخدام إحدى الطرق التالية:</p>
                                    <ul>
                                        <li><strong>الرسائل الخاصة:</strong> انتقل إلى "الرسائل" في القائمة الجانبية، انقر على "رسالة جديدة"، اختر المعلم من قائمة المستلمين، واكتب رسالتك.</li>
                                        <li><strong>منتدى المقرر:</strong> انتقل إلى صفحة المقرر، انقر على علامة التبويب "المنتدى"، وشارك في المناقشات أو اطرح سؤالًا.</li>
                                        <li><strong>ساعات المكتب الافتراضية:</strong> يمكنك حضور ساعات المكتب الافتراضية التي يحددها المعلم للتواصل المباشر.</li>
                                        <li><strong>البريد الإلكتروني:</strong> يمكنك إرسال بريد إلكتروني مباشرة إلى المعلم باستخدام عنوان البريد الإلكتروني المتوفر في صفحة المقرر.</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    كيف يمكنني إنشاء اختبار كمعلم؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq7" class="faq-answer collapse">
                                    <p>لإنشاء اختبار كمعلم، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>قم بتسجيل الدخول إلى حسابك كمعلم.</li>
                                        <li>انتقل إلى المقرر الذي ترغب في إنشاء اختبار له.</li>
                                        <li>انقر على علامة التبويب "الاختبارات".</li>
                                        <li>انقر على "إنشاء اختبار جديد".</li>
                                        <li>أدخل عنوان الاختبار والوصف والتعليمات.</li>
                                        <li>حدد تاريخ ووقت بدء الاختبار وانتهائه.</li>
                                        <li>حدد مدة الاختبار والدرجة الكلية.</li>
                                        <li>انقر على "إضافة أسئلة" لإضافة أسئلة الاختبار.</li>
                                        <li>يمكنك إضافة أنواع مختلفة من الأسئلة (اختيار من متعدد، صح/خطأ، مقالي، إلخ).</li>
                                        <li>حدد خيارات الاختبار الإضافية (ترتيب عشوائي للأسئلة، عرض نتيجة فورية، إلخ).</li>
                                        <li>انقر على "حفظ ونشر" لإتاحة الاختبار للطلاب.</li>
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    كيف يمكنني تصدير تقرير الدرجات؟
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="faq8" class="faq-answer collapse">
                                    <p>لتصدير تقرير الدرجات كمعلم أو مدير كلية، اتبع الخطوات التالية:</p>
                                    <ol>
                                        <li>قم بتسجيل الدخول إلى حسابك.</li>
                                        <li>انتقل إلى "الدرجات" أو "التقارير" في القائمة الجانبية.</li>
                                        <li>حدد المقرر أو الفصل الدراسي المطلوب.</li>
                                        <li>انقر على "تصدير" أو "إنشاء تقرير".</li>
                                        <li>حدد تنسيق التصدير المطلوب (Excel، PDF، CSV).</li>
                                        <li>حدد المعلومات التي ترغب في تضمينها في التقرير.</li>
                                        <li>انقر على "تصدير" لتنزيل التقرير.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- فيديوهات تعليمية -->
                <div id="videoTutorials" class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="mb-4">فيديوهات تعليمية</h2>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="video-tutorial">
                                    <div class="video-thumbnail">
                                        <img src="assets/images/video-thumbnail-1.jpg" alt="مقدمة في نظام يونيفر بورد" class="img-fluid">
                                        <div class="play-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="h5">مقدمة في نظام يونيفر بورد</h3>
                                        <p class="text-muted">تعرف على الميزات الأساسية للنظام وكيفية استخدامها</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small"><i class="fas fa-clock me-1"></i> 5:30</span>
                                            <a href="#" class="btn btn-sm btn-outline-primary">مشاهدة</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="video-tutorial">
                                    <div class="video-thumbnail">
                                        <img src="assets/images/video-thumbnail-2.jpg" alt="كيفية تسجيل المقررات" class="img-fluid">
                                        <div class="play-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="h5">كيفية تسجيل المقررات</h3>
                                        <p class="text-muted">دليل خطوة بخطوة لتسجيل المقررات الدراسية</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small"><i class="fas fa-clock me-1"></i> 4:15</span>
                                            <a href="#" class="btn btn-sm btn-outline-primary">مشاهدة</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="video-tutorial">
                                    <div class="video-thumbnail">
                                        <img src="assets/images/video-thumbnail-3.jpg" alt="إدارة الواجبات والاختبارات" class="img-fluid">
                                        <div class="play-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="h5">إدارة الواجبات والاختبارات</h3>
                                        <p class="text-muted">كيفية تقديم الواجبات وأداء الاختبارات الإلكترونية</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small"><i class="fas fa-clock me-1"></i> 7:20</span>
                                            <a href="#" class="btn btn-sm btn-outline-primary">مشاهدة</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="video-tutorial">
                                    <div class="video-thumbnail">
                                        <img src="assets/images/video-thumbnail-4.jpg" alt="دليل المعلمين لإدارة المقررات" class="img-fluid">
                                        <div class="play-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <h3 class="h5">دليل المعلمين لإدارة المقررات</h3>
                                        <p class="text-muted">كيفية إنشاء وإدارة المقررات الدراسية للمعلمين</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small"><i class="fas fa-clock me-1"></i> 8:45</span>
                                            <a href="#" class="btn btn-sm btn-outline-primary">مشاهدة</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-primary">عرض جميع الفيديوهات التعليمية</a>
                        </div>
                    </div>
                </div>
                
                <!-- قسم الاتصال بالدعم -->
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-4 p-md-5 text-center">
                        <h2 class="mb-3">لم تجد ما تبحث عنه؟</h2>
                        <p class="lead mb-4">فريق الدعم الفني متاح للإجابة على جميع استفساراتك</p>
                        <a href="contact.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-headset me-2"></i> اتصل بالدعم الفني
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تذييل الصفحة (Footer) -->
    <footer class="bg-dark text-white py-4 mt-5">
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
        
        // البحث في صفحة المساعدة
        function searchHelp() {
            const searchTerm = document.getElementById('helpSearch').value.toLowerCase();
            const content = document.querySelectorAll('.user-guide-content h3, .user-guide-content h4, .user-guide-content p, .faq-question, .faq-answer p');
            
            if (searchTerm.length > 2) {
                document.getElementById('clearSearch').style.display = 'block';
                
                content.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const parent = item.closest('.faq-item') || item.closest('div');
                    
                    if (text.includes(searchTerm)) {
                        if (item.classList.contains('faq-question')) {
                            item.classList.add('bg-light');
                            const answer = item.nextElementSibling;
                            answer.classList.add('show');
                        }
                        
                        if (parent) {
                            parent.style.display = 'block';
                            
                            // إذا كان العنصر هو عنوان فرعي، تأكد من إظهار العنوان الرئيسي
                            if (item.tagName === 'H4' || item.tagName === 'P') {
                                const mainSection = item.closest('div.mb-5');
                                if (mainSection) {
                                    mainSection.style.display = 'block';
                                }
                            }
                        }
                    } else {
                        if (item.classList.contains('faq-question')) {
                            item.classList.remove('bg-light');
                        }
                        
                        // لا تخفي العناصر الرئيسية
                        if (parent && !item.tagName.match(/^H[1-3]$/)) {
                            // تحقق إذا كان هناك أي عناصر فرعية تطابق البحث
                            const hasMatch = Array.from(parent.querySelectorAll('*')).some(el => 
                                el.textContent.toLowerCase().includes(searchTerm) && el !== item
                            );
                            
                            if (!hasMatch && !item.tagName.match(/^H[1-3]$/)) {
                                parent.style.display = 'none';
                            }
                        }
                    }
                });
            } else {
                document.getElementById('clearSearch').style.display = 'none';
                
                // إعادة عرض جميع العناصر
                content.forEach(item => {
                    const parent = item.closest('.faq-item') || item.closest('div');
                    if (parent) {
                        parent.style.display = 'block';
                    }
                    
                    if (item.classList.contains('faq-question')) {
                        item.classList.remove('bg-light');
                        const answer = item.nextElementSibling;
                        answer.classList.remove('show');
                    }
                });
            }
        }
        
        // مسح البحث
        function clearSearch() {
            document.getElementById('helpSearch').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            
            // إعادة عرض جميع العناصر
            const content = document.querySelectorAll('.user-guide-content h3, .user-guide-content h4, .user-guide-content p, .faq-question, .faq-answer p');
            content.forEach(item => {
                const parent = item.closest('.faq-item') || item.closest('div');
                if (parent) {
                    parent.style.display = 'block';
                }
                
                if (item.classList.contains('faq-question')) {
                    item.classList.remove('bg-light');
                    const answer = item.nextElementSibling;
                    answer.classList.remove('show');
                }
            });
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
            
            // البحث في صفحة المساعدة
            const searchInput = document.getElementById('helpSearch');
            if (searchInput) {
                searchInput.addEventListener('input', searchHelp);
                
                // زر مسح البحث
                const clearButton = document.getElementById('clearSearch');
                if (clearButton) {
                    clearButton.addEventListener('click', clearSearch);
                }
            }
            
            // تبديل الأسئلة الشائعة
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const answer = this.nextElementSibling;
                    const isOpen = answer.classList.contains('show');
                    
                    // تغيير أيقونة السهم
                    const icon = this.querySelector('i');
                    icon.className = isOpen ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
                });
            });
        });
    </script>
</body>
</html>
