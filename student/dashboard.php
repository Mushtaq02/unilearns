<?php
/**
 * صفحة لوحة تحكم الطالب المبسطة
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول الطالب
if (!isLoggedIn() || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// الحصول على معلومات الطالب
$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];
$student_email = $_SESSION['user_email'];

// تعيين اللغة الافتراضية
$lang = 'ar';
$theme = 'light';

// الحصول على بعض الإحصائيات البسيطة
try {
    // عدد المقررات المسجل فيها الطالب
    $courses_query = "SELECT COUNT(*) as course_count FROM enrollments WHERE student_id = :student_id";
    $courses_stmt = $pdo->prepare($courses_query);
    $courses_stmt->execute(['student_id' => $student_id]);
    $course_count = $courses_stmt->fetch()['course_count'] ?? 0;
    
    // عدد الواجبات المعلقة
    $assignments_query = "SELECT COUNT(*) as assignment_count FROM assignments a 
                         JOIN enrollments e ON a.course_id = e.course_id 
                         WHERE e.student_id = :student_id AND a.due_date > NOW()";
    $assignments_stmt = $pdo->prepare($assignments_query);
    $assignments_stmt->execute(['student_id' => $student_id]);
    $assignment_count = $assignments_stmt->fetch()['assignment_count'] ?? 0;
    
} catch (Exception $e) {
    $course_count = 0;
    $assignment_count = 0;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - لوحة تحكم الطالب</title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- خط Cairo -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .card-body {
            padding: 2rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p class="mb-0">مرحباً، <?php echo htmlspecialchars($student_name); ?></p>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        لوحة التحكم
                    </a>
                    <a class="nav-link" href="student_courses.php">
                        <i class="fas fa-book me-2"></i>
                        المقررات
                    </a>
                    <a class="nav-link" href="student_assignments.php">
                        <i class="fas fa-tasks me-2"></i>
                        الواجبات
                    </a>
                    <a class="nav-link" href="student_grades.php">
                        <i class="fas fa-chart-line me-2"></i>
                        الدرجات
                    </a>
                    <a class="nav-link" href="student_schedule.php">
                        <i class="fas fa-calendar me-2"></i>
                        الجدول الدراسي
                    </a>
                    <a class="nav-link" href="student_notifications.php">
                        <i class="fas fa-bell me-2"></i>
                        الإشعارات
                    </a>
                    <a class="nav-link" href="student_messages.php">
                        <i class="fas fa-envelope me-2"></i>
                        الرسائل
                    </a>
                    <a class="nav-link" href="student_forums.php">
                        <i class="fas fa-comments me-2"></i>
                        المنتديات
                    </a>
                    <a class="nav-link" href="student_profile.php">
                        <i class="fas fa-user me-2"></i>
                        الملف الشخصي
                    </a>
                    <a class="nav-link" href="student_settings.php">
                        <i class="fas fa-cog me-2"></i>
                        الإعدادات
                    </a>
                    <hr>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        تسجيل الخروج
                    </a>
                </nav>
            </div>
            
            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- رأس الصفحة -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">لوحة تحكم الطالب</h1>
                    <div>
                        <span class="text-muted"><?php echo date('Y-m-d H:i'); ?></span>
                    </div>
                </div>
                
                <!-- بطاقات الإحصائيات -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x mb-3"></i>
                                <div class="stat-number"><?php echo $course_count; ?></div>
                                <div>المقررات المسجلة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks fa-2x mb-3"></i>
                                <div class="stat-number"><?php echo $assignment_count; ?></div>
                                <div>الواجبات المعلقة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-3"></i>
                                <div class="stat-number">85%</div>
                                <div>المعدل التراكمي</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-bell fa-2x mb-3"></i>
                                <div class="stat-number">3</div>
                                <div>إشعارات جديدة</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- المحتوى الرئيسي -->
                <div class="row">
                    <div class="col-lg-8">
                        <!-- الواجبات القادمة -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tasks me-2"></i>
                                    الواجبات القادمة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    لا توجد واجبات معلقة حالياً
                                </div>
                            </div>
                        </div>
                        
                        <!-- الجدول الدراسي اليوم -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    جدول اليوم
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    لا توجد محاضرات اليوم
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- الإشعارات الحديثة -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bell me-2"></i>
                                    الإشعارات الحديثة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    لا توجد إشعارات جديدة
                                </div>
                            </div>
                        </div>
                        
                        <!-- الروابط السريعة -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-link me-2"></i>
                                    روابط سريعة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="student_courses.php" class="btn btn-outline-primary">
                                        <i class="fas fa-book me-2"></i>
                                        عرض المقررات
                                    </a>
                                    <a href="student_assignments.php" class="btn btn-outline-success">
                                        <i class="fas fa-tasks me-2"></i>
                                        عرض الواجبات
                                    </a>
                                    <a href="student_grades.php" class="btn btn-outline-info">
                                        <i class="fas fa-chart-line me-2"></i>
                                        عرض الدرجات
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

