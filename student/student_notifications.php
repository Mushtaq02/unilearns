<?php
/**
 * صفحة الإشعارات للطالب
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول الطالب
if (!isLoggedIn() || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];

// الحصول على الإشعارات
$notifications = [];
try {
    $query = "SELECT * FROM notifications WHERE user_id = :user_id OR user_id IS NULL ORDER BY created_at DESC LIMIT 20";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $student_id]);
    $notifications = $stmt->fetchAll();
} catch (Exception $e) {
    $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الإشعارات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); border-radius: 8px; margin: 2px 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p class="mb-0">مرحباً، <?php echo htmlspecialchars($student_name); ?></p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a>
                    <a class="nav-link" href="student_courses.php"><i class="fas fa-book me-2"></i>المقررات</a>
                    <a class="nav-link" href="student_assignments.php"><i class="fas fa-tasks me-2"></i>الواجبات</a>
                    <a class="nav-link" href="student_grades.php"><i class="fas fa-chart-line me-2"></i>الدرجات</a>
                    <a class="nav-link" href="student_schedule.php"><i class="fas fa-calendar me-2"></i>الجدول الدراسي</a>
                    <a class="nav-link active" href="student_notifications.php"><i class="fas fa-bell me-2"></i>الإشعارات</a>
                    <a class="nav-link" href="student_messages.php"><i class="fas fa-envelope me-2"></i>الرسائل</a>
                    <a class="nav-link" href="student_forums.php"><i class="fas fa-comments me-2"></i>المنتديات</a>
                    <a class="nav-link" href="student_profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a>
                    <a class="nav-link" href="student_settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a>
                    <hr>
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a>
                </nav>
            </div>
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="fas fa-bell me-2"></i>الإشعارات</h1>
                    <span class="badge bg-primary"><?php echo count($notifications); ?> إشعار</span>
                </div>
                
                <?php if (empty($notifications)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد إشعارات</h4>
                            <p class="text-muted">لا توجد إشعارات جديدة. ستظهر الإشعارات هنا عند وصولها.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <h6><?php echo htmlspecialchars($notification['title'] ?? 'إشعار'); ?></h6>
                                        <small class="text-muted"><?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo htmlspecialchars($notification['message'] ?? 'محتوى الإشعار'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

