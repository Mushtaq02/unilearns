<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}
$teacher_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الطلاب</title>
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
                    <p class="mb-0">مرحباً، <?php echo htmlspecialchars($teacher_name); ?></p>
                    <small>معلم</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a>
                    <a class="nav-link" href="teacher_courses.php"><i class="fas fa-book me-2"></i>المقررات</a>
                    <a class="nav-link active" href="teacher_students.php"><i class="fas fa-users me-2"></i>الطلاب</a>
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
                    <h1 class="h3">الطلاب المسجلين</h1>
                    <span class="badge bg-primary fs-6">0 طالب</span>
                </div>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">لا يوجد طلاب مسجلين</h4>
                        <p class="text-muted">لا يوجد طلاب مسجلين في مقرراتك حالياً.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
