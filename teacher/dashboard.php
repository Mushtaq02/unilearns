<?php
/**
 * صفحة لوحة تحكم المعلم
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول المعلم
if (!isLoggedIn() || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'];

// الحصول على إحصائيات المعلم
$stats = [
    'courses' => 0,
    'students' => 0,
    'assignments' => 0,
    'notifications' => 0
];

try {
    // عدد المقررات
    $query = "SELECT COUNT(*) as count FROM courses WHERE teacher_id = :teacher_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['teacher_id' => $teacher_id]);
    $stats['courses'] = $stmt->fetch()['count'] ?? 0;
    
    // عدد الطلاب (من خلال التسجيلات)
    $query = "SELECT COUNT(DISTINCT student_id) as count FROM enrollments e 
              JOIN courses c ON e.course_id = c.id 
              WHERE c.teacher_id = :teacher_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['teacher_id' => $teacher_id]);
    $stats['students'] = $stmt->fetch()['count'] ?? 0;
    
    // عدد الواجبات
    $query = "SELECT COUNT(*) as count FROM assignments a 
              JOIN courses c ON a.course_id = c.id 
              WHERE c.teacher_id = :teacher_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['teacher_id' => $teacher_id]);
    $stats['assignments'] = $stmt->fetch()['count'] ?? 0;
    
    // عدد الإشعارات
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id OR user_id IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $teacher_id]);
    $stats['notifications'] = $stmt->fetch()['count'] ?? 0;
    
} catch (Exception $e) {
    // في حالة الخطأ، استخدم القيم الافتراضية
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - لوحة تحكم المعلم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); border-radius: 8px; margin: 2px 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card .card-body { padding: 2rem; }
        .stat-number { font-size: 2.5rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p class="mb-0">مرحباً، <?php echo htmlspecialchars($teacher_name); ?></p>
                    <small>معلم</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a>
                    <a class="nav-link" href="teacher_courses.php"><i class="fas fa-book me-2"></i>المقررات</a>
                    <a class="nav-link" href="teacher_students.php"><i class="fas fa-users me-2"></i>الطلاب</a>
                    <a class="nav-link" href="teacher_assignments.php"><i class="fas fa-tasks me-2"></i>الواجبات</a>
                    <a class="nav-link" href="teacher_exams.php"><i class="fas fa-file-alt me-2"></i>الاختبارات</a>
                    <a class="nav-link" href="teacher_grades.php"><i class="fas fa-chart-line me-2"></i>الدرجات</a>
                    <a class="nav-link" href="teacher_schedule.php"><i class="fas fa-calendar me-2"></i>الجدول الدراسي</a>
                    <a class="nav-link" href="teacher_notifications.php"><i class="fas fa-bell me-2"></i>الإشعارات</a>
                    <a class="nav-link" href="teacher_messages.php"><i class="fas fa-envelope me-2"></i>الرسائل</a>
                    <a class="nav-link" href="teacher_forums.php"><i class="fas fa-comments me-2"></i>المنتديات</a>
                    <a class="nav-link" href="teacher_profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a>
                    <a class="nav-link" href="teacher_settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a>
                    <hr>
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a>
                </nav>
            </div>
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">لوحة تحكم المعلم</h1>
                    <div class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo date('Y-m-d H:i'); ?>
                    </div>
                </div>
                
                <!-- الإحصائيات -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x mb-3"></i>
                                <div class="stat-number"><?php echo $stats['courses']; ?></div>
                                <div>المقررات المسندة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <div class="stat-number"><?php echo $stats['students']; ?></div>
                                <div>الطلاب المسجلين</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks fa-2x mb-3"></i>
                                <div class="stat-number"><?php echo $stats['assignments']; ?></div>
                                <div>الواجبات المعلقة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-bell fa-2x mb-3"></i>
                                <div class="stat-number"><?php echo $stats['notifications']; ?></div>
                                <div>إشعارات جديدة</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- المقررات الحديثة -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="fas fa-book me-2"></i>المقررات الحديثة</h5>
                                <span class="badge bg-primary"><?php echo $stats['courses']; ?> مقرر</span>
                            </div>
                            <div class="card-body">
                                <?php if ($stats['courses'] == 0): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-book fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">لا توجد مقررات مسندة حالياً</p>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">يمكنك إدارة مقرراتك من قسم المقررات</p>
                                    <a href="teacher_courses.php" class="btn btn-primary btn-sm">عرض جميع المقررات</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- الواجبات القادمة -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>الواجبات القادمة</h5>
                                <span class="badge bg-warning"><?php echo $stats['assignments']; ?> واجب</span>
                            </div>
                            <div class="card-body">
                                <?php if ($stats['assignments'] == 0): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-tasks fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">لا توجد واجبات معلقة</p>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">يمكنك إدارة الواجبات من قسم الواجبات</p>
                                    <a href="teacher_assignments.php" class="btn btn-warning btn-sm">عرض جميع الواجبات</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- روابط سريعة -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-link me-2"></i>روابط سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <a href="teacher_courses.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-book me-2"></i>إدارة المقررات
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="teacher_assignments.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-tasks me-2"></i>إدارة الواجبات
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="teacher_grades.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-chart-line me-2"></i>إدارة الدرجات
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

