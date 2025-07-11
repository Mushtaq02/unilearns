<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php');
    exit;
}
$student_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?> - الإعدادات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a class="nav-link" href="student_notifications.php"><i class="fas fa-bell me-2"></i>الإشعارات</a>
                    <a class="nav-link" href="student_messages.php"><i class="fas fa-envelope me-2"></i>الرسائل</a>
                    <a class="nav-link" href="student_forums.php"><i class="fas fa-comments me-2"></i>المنتديات</a>
                    <a class="nav-link" href="student_profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a>
                    <a class="nav-link active" href="student_settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a>
                    <hr>
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a>
                </nav>
            </div>
            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="h3 mb-4"><i class="fas fa-cog me-2"></i>الإعدادات</h1>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">إعدادات الحساب</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>تغيير كلمة المرور</h6>
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">كلمة المرور الحالية</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">كلمة المرور الجديدة</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">تأكيد كلمة المرور</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h6>إعدادات الإشعارات</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">إشعارات الواجبات</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">إشعارات الدرجات</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox">
                                    <label class="form-check-label">إشعارات الرسائل</label>
                                </div>
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
