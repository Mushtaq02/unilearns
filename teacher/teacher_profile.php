<?php
/**
 * صفحة الملف الشخصي للمعلم
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

// الحصول على معلومات المعلم
$teacher_info = null;
try {
    $query = "SELECT * FROM users WHERE id = :teacher_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['teacher_id' => $teacher_id]);
    $teacher_info = $stmt->fetch();
} catch (Exception $e) {
    // في حالة الخطأ، استخدم البيانات من الجلسة
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الملف الشخصي</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); border-radius: 8px; margin: 2px 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .profile-avatar { width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
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
                    <a class="nav-link" href="teacher_students.php"><i class="fas fa-users me-2"></i>الطلاب</a>
                    <a class="nav-link" href="teacher_assignments.php"><i class="fas fa-tasks me-2"></i>الواجبات</a>
                    <a class="nav-link" href="teacher_exams.php"><i class="fas fa-file-alt me-2"></i>الاختبارات</a>
                    <a class="nav-link" href="teacher_grades.php"><i class="fas fa-chart-line me-2"></i>الدرجات</a>
                    <a class="nav-link" href="teacher_schedule.php"><i class="fas fa-calendar me-2"></i>الجدول الدراسي</a>
                    <a class="nav-link" href="teacher_notifications.php"><i class="fas fa-bell me-2"></i>الإشعارات</a>
                    <a class="nav-link" href="teacher_messages.php"><i class="fas fa-envelope me-2"></i>الرسائل</a>
                    <a class="nav-link" href="teacher_forums.php"><i class="fas fa-comments me-2"></i>المنتديات</a>
                    <a class="nav-link active" href="teacher_profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a>
                    <a class="nav-link" href="teacher_settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a>
                    <hr>
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a>
                </nav>
            </div>
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">الملف الشخصي</h1>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="profile-avatar rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($teacher_info['name'] ?? $teacher_name); ?></h4>
                                <p class="text-muted">معلم</p>
                                <span class="badge bg-success">نشط</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>المعلومات الشخصية</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">الاسم الكامل</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($teacher_info['name'] ?? $teacher_name); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">البريد الإلكتروني</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($teacher_info['email'] ?? 'غير محدد'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">رقم الهاتف</label>
                                        <p class="fw-bold"><?php echo htmlspecialchars($teacher_info['phone'] ?? 'غير محدد'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">تاريخ التسجيل</label>
                                        <p class="fw-bold"><?php echo $teacher_info['created_at'] ? date('Y-m-d', strtotime($teacher_info['created_at'])) : 'غير محدد'; ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">الحالة</label>
                                        <p class="fw-bold">
                                            <?php if ($teacher_info['is_active'] ?? 1): ?>
                                                <span class="badge bg-success">نشط</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">غير نشط</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">نوع المستخدم</label>
                                        <p class="fw-bold">معلم</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>إجراءات</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-primary w-100">
                                    <i class="fas fa-edit me-2"></i>تعديل المعلومات
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="teacher_settings.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-cog me-2"></i>إعدادات الحساب
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

