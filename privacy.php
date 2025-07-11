<?php
/**
 * صفحة سياسة الخصوصية لنظام UniverBoard
 * توضح كيفية جمع واستخدام وحماية بيانات المستخدمين
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
    <title><?php echo SITE_NAME; ?> - <?php echo t('privacy_title'); ?></title>
    
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
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="text-center mb-4"><?php echo t('privacy_heading'); ?></h1>
                        <p class="text-center text-muted mb-5"><?php echo t('privacy_subheading'); ?></p>
                        
                        <div class="privacy-content">
                            <section class="mb-5">
                                <h2>1. مقدمة</h2>
                                <p>نحن في يونيفر بورد نقدر خصوصيتك ونلتزم بحماية بياناتك الشخصية. توضح سياسة الخصوصية هذه كيفية جمع واستخدام وحماية معلوماتك الشخصية عند استخدام منصتنا التعليمية.</p>
                                <p>من خلال استخدام نظام يونيفر بورد، فإنك توافق على الممارسات الموضحة في سياسة الخصوصية هذه. نحن نراجع سياستنا بانتظام للتأكد من أنها تعكس أفضل الممارسات في حماية البيانات.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>2. المعلومات التي نجمعها</h2>
                                <p>نحن نجمع المعلومات التالية لتقديم خدماتنا وتحسينها:</p>
                                
                                <h3 class="h5 mt-4">2.1 المعلومات الشخصية</h3>
                                <ul>
                                    <li>الاسم الكامل</li>
                                    <li>عنوان البريد الإلكتروني</li>
                                    <li>رقم الهاتف</li>
                                    <li>تاريخ الميلاد</li>
                                    <li>الرقم الأكاديمي أو الوظيفي</li>
                                    <li>الصورة الشخصية (اختيارية)</li>
                                </ul>
                                
                                <h3 class="h5 mt-4">2.2 المعلومات الأكاديمية</h3>
                                <ul>
                                    <li>الكلية والقسم والبرنامج الأكاديمي</li>
                                    <li>المقررات المسجلة</li>
                                    <li>الدرجات والتقييمات</li>
                                    <li>سجل الحضور</li>
                                    <li>المشاركات والواجبات المقدمة</li>
                                </ul>
                                
                                <h3 class="h5 mt-4">2.3 معلومات الاستخدام</h3>
                                <ul>
                                    <li>عنوان IP</li>
                                    <li>نوع المتصفح وإصداره</li>
                                    <li>نظام التشغيل</li>
                                    <li>تاريخ ووقت الوصول</li>
                                    <li>الصفحات التي تمت زيارتها</li>
                                    <li>سجل النشاط على المنصة</li>
                                </ul>
                            </section>
                            
                            <section class="mb-5">
                                <h2>3. كيفية استخدام المعلومات</h2>
                                <p>نستخدم المعلومات التي نجمعها للأغراض التالية:</p>
                                
                                <ul>
                                    <li>توفير وإدارة الخدمات التعليمية المقدمة عبر المنصة</li>
                                    <li>إنشاء وإدارة حسابك الشخصي</li>
                                    <li>تسهيل التواصل بين الطلاب والمعلمين والإدارة</li>
                                    <li>تتبع التقدم الأكاديمي وتقديم التقارير</li>
                                    <li>تحسين وتطوير المنصة وخدماتها</li>
                                    <li>تحليل استخدام المنصة وتحسين تجربة المستخدم</li>
                                    <li>الامتثال للمتطلبات القانونية والتنظيمية</li>
                                </ul>
                            </section>
                            
                            <section class="mb-5">
                                <h2>4. مشاركة المعلومات</h2>
                                <p>نحن نحترم خصوصيتك ولا نشارك معلوماتك الشخصية مع أطراف ثالثة إلا في الحالات التالية:</p>
                                
                                <ul>
                                    <li>مع المؤسسة التعليمية التي تنتمي إليها لأغراض إدارية وأكاديمية</li>
                                    <li>مع مقدمي الخدمات الذين يساعدوننا في تشغيل المنصة (مثل استضافة البيانات، خدمات الدعم)</li>
                                    <li>عندما يكون ذلك مطلوبًا بموجب القانون أو في إطار إجراءات قانونية</li>
                                    <li>لحماية حقوقنا أو ممتلكاتنا أو سلامة مستخدمينا أو الجمهور</li>
                                </ul>
                                
                                <p>نحن لا نبيع أو نؤجر معلوماتك الشخصية لأطراف ثالثة لأغراض تسويقية.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>5. أمان البيانات</h2>
                                <p>نحن نتخذ تدابير أمنية مناسبة لحماية معلوماتك الشخصية من الوصول غير المصرح به أو التعديل أو الإفصاح أو الإتلاف. تشمل هذه التدابير:</p>
                                
                                <ul>
                                    <li>تشفير البيانات أثناء النقل باستخدام بروتوكول SSL</li>
                                    <li>تخزين كلمات المرور بطريقة مشفرة</li>
                                    <li>تقييد الوصول إلى المعلومات الشخصية للموظفين المصرح لهم فقط</li>
                                    <li>استخدام جدران الحماية وأنظمة كشف التسلل</li>
                                    <li>إجراء مراجعات أمنية دورية</li>
                                </ul>
                                
                                <p>على الرغم من جهودنا، لا يمكن ضمان أمان البيانات بشكل مطلق عبر الإنترنت. نحن نشجعك على اتخاذ الاحتياطات اللازمة لحماية معلوماتك الشخصية، مثل استخدام كلمات مرور قوية وعدم مشاركتها مع الآخرين.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>6. الاحتفاظ بالبيانات</h2>
                                <p>نحتفظ بمعلوماتك الشخصية طالما كان ذلك ضروريًا لتقديم خدماتنا وتحقيق الأغراض المذكورة في سياسة الخصوصية هذه. بعد انتهاء علاقتك بالمؤسسة التعليمية، قد نستمر في الاحتفاظ ببعض المعلومات للأغراض التالية:</p>
                                
                                <ul>
                                    <li>الامتثال للمتطلبات القانونية والتنظيمية</li>
                                    <li>حل النزاعات المحتملة</li>
                                    <li>منع الاحتيال وإساءة الاستخدام</li>
                                    <li>حماية مصالحنا المشروعة</li>
                                </ul>
                            </section>
                            
                            <section class="mb-5">
                                <h2>7. حقوقك</h2>
                                <p>تمنحك قوانين حماية البيانات حقوقًا معينة فيما يتعلق بمعلوماتك الشخصية. وفقًا للقانون المعمول به، قد تشمل هذه الحقوق:</p>
                                
                                <ul>
                                    <li>الوصول إلى معلوماتك الشخصية</li>
                                    <li>تصحيح المعلومات غير الدقيقة</li>
                                    <li>حذف معلوماتك (في ظروف معينة)</li>
                                    <li>تقييد معالجة معلوماتك</li>
                                    <li>الاعتراض على معالجة معلوماتك</li>
                                    <li>نقل معلوماتك (قابلية نقل البيانات)</li>
                                </ul>
                                
                                <p>إذا كنت ترغب في ممارسة أي من هذه الحقوق، يرجى التواصل معنا باستخدام معلومات الاتصال المذكورة أدناه.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>8. ملفات تعريف الارتباط</h2>
                                <p>نستخدم ملفات تعريف الارتباط وتقنيات مماثلة لتحسين تجربتك على منصتنا. ملفات تعريف الارتباط هي ملفات نصية صغيرة يتم تخزينها على جهازك عند زيارة موقعنا.</p>
                                
                                <p>نستخدم ملفات تعريف الارتباط للأغراض التالية:</p>
                                
                                <ul>
                                    <li>تذكر تفضيلاتك وإعداداتك</li>
                                    <li>تسجيل الدخول التلقائي (إذا اخترت ذلك)</li>
                                    <li>تحليل كيفية استخدام المنصة</li>
                                    <li>تحسين أداء وفعالية المنصة</li>
                                </ul>
                                
                                <p>يمكنك التحكم في ملفات تعريف الارتباط من خلال إعدادات المتصفح الخاص بك. ومع ذلك، قد يؤدي حظر بعض ملفات تعريف الارتباط إلى التأثير على تجربتك وقدرتك على استخدام بعض ميزات المنصة.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>9. التغييرات على سياسة الخصوصية</h2>
                                <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر لتعكس التغييرات في ممارساتنا أو لأسباب تشغيلية أو قانونية أو تنظيمية. سنقوم بإخطارك بأي تغييرات جوهرية من خلال إشعار على موقعنا أو عبر البريد الإلكتروني.</p>
                                
                                <p>نشجعك على مراجعة سياسة الخصوصية بانتظام للبقاء على اطلاع بكيفية حماية معلوماتك الشخصية.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>10. اتصل بنا</h2>
                                <p>إذا كانت لديك أي أسئلة أو مخاوف بشأن سياسة الخصوصية هذه أو ممارسات الخصوصية لدينا، يرجى التواصل معنا على:</p>
                                
                                <div class="contact-info mt-3">
                                    <p><strong>البريد الإلكتروني:</strong> privacy@univerboard.com</p>
                                    <p><strong>الهاتف:</strong> +966 12 345 6789</p>
                                    <p><strong>العنوان:</strong> المملكة العربية السعودية، الرياض، حي الجامعة</p>
                                </div>
                            </section>
                            
                            <div class="text-center mt-5">
                                <p class="text-muted">آخر تحديث: مايو 2025</p>
                            </div>
                        </div>
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
        });
    </script>
</body>
</html>
