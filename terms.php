<?php
/**
 * صفحة الشروط والأحكام لنظام UniverBoard
 * توضح شروط استخدام النظام وحقوق ومسؤوليات المستخدمين
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
    <title><?php echo SITE_NAME; ?> - <?php echo t('terms_title'); ?></title>
    
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
                        <h1 class="text-center mb-4"><?php echo t('terms_heading'); ?></h1>
                        <p class="text-center text-muted mb-5"><?php echo t('terms_subheading'); ?></p>
                        
                        <div class="terms-content">
                            <section class="mb-5">
                                <h2>1. مقدمة</h2>
                                <p>مرحبًا بك في نظام يونيفر بورد ("النظام"). يتم تشغيل هذا النظام بواسطة شركة يونيفر بورد ("نحن"، "لنا") ويتم توفيره للمؤسسات التعليمية ومستخدميها ("أنت"، "المستخدم").</p>
                                <p>باستخدامك للنظام، فإنك توافق على الالتزام بهذه الشروط والأحكام. إذا كنت لا توافق على هذه الشروط، يرجى عدم استخدام النظام.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>2. تعريفات</h2>
                                <ul>
                                    <li><strong>النظام:</strong> منصة يونيفر بورد التعليمية وجميع خدماتها وميزاتها.</li>
                                    <li><strong>المستخدم:</strong> أي شخص يستخدم النظام، بما في ذلك الطلاب والمعلمين وإداريي الكليات ومشرفي النظام.</li>
                                    <li><strong>المحتوى:</strong> جميع البيانات والمعلومات والمواد التي يتم إنشاؤها أو تحميلها أو مشاركتها عبر النظام.</li>
                                    <li><strong>المؤسسة التعليمية:</strong> الجامعة أو الكلية أو المعهد الذي يستخدم النظام لإدارة العملية التعليمية.</li>
                                </ul>
                            </section>
                            
                            <section class="mb-5">
                                <h2>3. التسجيل والحسابات</h2>
                                <p>للوصول إلى النظام واستخدامه، يجب أن تكون مسجلاً في مؤسسة تعليمية تستخدم النظام. عند إنشاء حساب، أنت توافق على:</p>
                                
                                <ul>
                                    <li>تقديم معلومات دقيقة وكاملة وحديثة.</li>
                                    <li>الحفاظ على سرية بيانات تسجيل الدخول الخاصة بك.</li>
                                    <li>تحمل المسؤولية الكاملة عن جميع الأنشطة التي تتم باستخدام حسابك.</li>
                                    <li>إخطارنا فوراً بأي استخدام غير مصرح به لحسابك.</li>
                                </ul>
                                
                                <p>نحتفظ بالحق في تعليق أو إنهاء حسابك إذا كان هناك انتهاك لهذه الشروط أو أي سلوك نعتبره ضاراً بالنظام أو المستخدمين الآخرين.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>4. حقوق الملكية الفكرية</h2>
                                
                                <h3 class="h5 mt-4">4.1 ملكية النظام</h3>
                                <p>النظام وجميع حقوق الملكية الفكرية المتعلقة به، بما في ذلك على سبيل المثال لا الحصر، حقوق النشر والعلامات التجارية وبراءات الاختراع والأسرار التجارية، هي ملك لنا أو لمرخصينا. لا يمنحك استخدام النظام أي حقوق ملكية فيه.</p>
                                
                                <h3 class="h5 mt-4">4.2 محتوى المستخدم</h3>
                                <p>أنت تحتفظ بحقوق الملكية الفكرية للمحتوى الذي تقوم بإنشائه أو تحميله على النظام. ومع ذلك، من خلال تحميل المحتوى، فإنك تمنحنا ترخيصاً عالمياً غير حصري وخالٍ من حقوق الملكية لاستخدام ونسخ وتعديل وتوزيع ونشر وترجمة وإنشاء أعمال مشتقة من هذا المحتوى لأغراض تشغيل النظام وتحسينه.</p>
                                
                                <h3 class="h5 mt-4">4.3 محتوى المؤسسة التعليمية</h3>
                                <p>المحتوى المقدم من المؤسسة التعليمية، مثل المناهج الدراسية والمواد التعليمية، يخضع لحقوق الملكية الفكرية الخاصة بالمؤسسة أو مرخصيها. يجب عليك احترام هذه الحقوق واستخدام المحتوى فقط للأغراض التعليمية المقصودة.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>5. قواعد السلوك</h2>
                                <p>عند استخدام النظام، يجب عليك الالتزام بالقواعد التالية:</p>
                                
                                <ul>
                                    <li>الامتثال لجميع القوانين واللوائح المعمول بها.</li>
                                    <li>احترام حقوق وخصوصية المستخدمين الآخرين.</li>
                                    <li>عدم نشر أو مشاركة محتوى غير قانوني أو ضار أو مسيء أو تشهيري أو إباحي أو تهديدي.</li>
                                    <li>عدم انتحال شخصية أي فرد أو كيان آخر.</li>
                                    <li>عدم جمع معلومات المستخدمين دون موافقتهم.</li>
                                    <li>عدم استخدام النظام لأي نشاط احتيالي أو غير مصرح به.</li>
                                    <li>عدم التدخل في عمل النظام أو محاولة الوصول إليه بطرق غير مصرح بها.</li>
                                    <li>عدم نشر أو مشاركة برامج ضارة أو فيروسات.</li>
                                </ul>
                                
                                <p>نحتفظ بالحق في إزالة أي محتوى ينتهك هذه القواعد واتخاذ الإجراءات المناسبة، بما في ذلك تعليق أو إنهاء حسابك.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>6. الخصوصية</h2>
                                <p>نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية. يرجى الاطلاع على <a href="privacy.php">سياسة الخصوصية</a> الخاصة بنا لفهم كيفية جمع واستخدام وحماية معلوماتك.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>7. التوفر والصيانة</h2>
                                <p>نحن نبذل جهوداً معقولة للحفاظ على توفر النظام وموثوقيته. ومع ذلك، قد يخضع النظام للتوقف المؤقت للصيانة المجدولة أو التحديثات أو الإصلاحات الطارئة. سنسعى لتقديم إشعار مسبق بأي انقطاع مجدول، ولكن قد لا يكون ذلك ممكناً دائماً.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>8. التغييرات في الخدمة</h2>
                                <p>نحتفظ بالحق في تعديل أو تعليق أو إنهاء النظام أو أي جزء منه في أي وقت، مع أو بدون إشعار. لن نكون مسؤولين تجاهك أو تجاه أي طرف ثالث عن أي تعديل أو تعليق أو إنهاء للنظام.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>9. إخلاء المسؤولية</h2>
                                <p>يتم توفير النظام "كما هو" و"كما هو متاح" دون أي ضمانات من أي نوع، صريحة أو ضمنية. لا نضمن أن النظام سيكون خالياً من الأخطاء أو متاحاً بشكل مستمر أو آمناً أو خالياً من الفيروسات أو البرامج الضارة الأخرى.</p>
                                
                                <p>لا نقدم أي ضمانات بشأن دقة أو موثوقية أو اكتمال أو ملاءمة المحتوى المتاح عبر النظام. أنت تتحمل المسؤولية الكاملة عن استخدامك للنظام والمحتوى الذي تصل إليه من خلاله.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>10. حدود المسؤولية</h2>
                                <p>إلى أقصى حد يسمح به القانون، لن نكون مسؤولين عن أي أضرار مباشرة أو غير مباشرة أو عرضية أو خاصة أو تبعية أو عقابية، بما في ذلك على سبيل المثال لا الحصر، فقدان الأرباح أو البيانات أو فرص العمل أو السمعة، الناشئة عن استخدامك للنظام أو عدم القدرة على استخدامه.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>11. التعويض</h2>
                                <p>أنت توافق على تعويضنا وحمايتنا والدفاع عنا ضد أي مطالبات أو مسؤوليات أو أضرار أو خسائر أو نفقات، بما في ذلك أتعاب المحاماة المعقولة، الناشئة عن استخدامك للنظام أو انتهاكك لهذه الشروط أو انتهاكك لحقوق أي طرف ثالث.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>12. القانون الحاكم</h2>
                                <p>تخضع هذه الشروط وتفسر وفقاً لقوانين المملكة العربية السعودية، دون اعتبار لمبادئ تنازع القوانين. أي نزاع ينشأ عن هذه الشروط أو يتعلق بها سيخضع للاختصاص القضائي الحصري للمحاكم المختصة في المملكة العربية السعودية.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>13. التغييرات في الشروط</h2>
                                <p>نحتفظ بالحق في تعديل هذه الشروط في أي وقت. سنقوم بإخطارك بأي تغييرات جوهرية من خلال إشعار على النظام أو عبر البريد الإلكتروني. استمرارك في استخدام النظام بعد نشر التغييرات يشكل قبولاً لهذه التغييرات.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>14. أحكام عامة</h2>
                                <p>إذا تم اعتبار أي حكم من هذه الشروط غير قانوني أو باطل أو غير قابل للتنفيذ لأي سبب، فسيتم اعتبار هذا الحكم قابلاً للفصل عن هذه الشروط ولن يؤثر على صحة وقابلية تنفيذ أي من الأحكام المتبقية.</p>
                                
                                <p>لا يشكل تنازلنا عن أي حق أو حكم من هذه الشروط تنازلاً مستمراً عن هذا الحق أو الحكم، ولا يشكل تنازلنا عن أي انتهاك تنازلاً عن أي انتهاكات لاحقة أو سابقة.</p>
                            </section>
                            
                            <section class="mb-5">
                                <h2>15. اتصل بنا</h2>
                                <p>إذا كانت لديك أي أسئلة أو استفسارات حول هذه الشروط، يرجى التواصل معنا على:</p>
                                
                                <div class="contact-info mt-3">
                                    <p><strong>البريد الإلكتروني:</strong> terms@univerboard.com</p>
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
