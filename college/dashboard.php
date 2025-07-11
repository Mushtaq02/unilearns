<?php
/**
 * صفحة لوحة تحكم إدارة الكلية
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول إدارة الكلية
if (!isLoggedIn() || $_SESSION['user_type'] !== 'college_admin') {
    header('Location: ../login.php');
    exit;
}

$admin_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - لوحة تحكم إدارة الكلية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%); min-height: 100vh; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); border-radius: 8px; margin: 2px 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); color: white; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .stat-card-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .stat-card-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p class="mb-0">مرحباً، <?php echo htmlspecialchars($admin_name); ?></p>
                    <small>إدارة الكلية</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a>
                    <a class="nav-link" href="college_departments.php"><i class="fas fa-building me-2"></i>الأقسام</a>
                    <a class="nav-link" href="college_programs.php"><i class="fas fa-graduation-cap me-2"></i>البرامج</a>
                    <a class="nav-link" href="college_courses.php"><i class="fas fa-book me-2"></i>المقررات</a>
                    <a class="nav-link" href="college_teachers.php"><i class="fas fa-chalkboard-teacher me-2"></i>المعلمين</a>
                    <a class="nav-link" href="college_students.php"><i class="fas fa-users me-2"></i>الطلاب</a>
                    <a class="nav-link" href="college_schedule.php"><i class="fas fa-calendar me-2"></i>الجداول</a>
                    <a class="nav-link" href="college_reports_academic.php"><i class="fas fa-chart-bar me-2"></i>التقارير الأكاديمية</a>
                    <a class="nav-link" href="college_reports_attendance.php"><i class="fas fa-clipboard-check me-2"></i>تقارير الحضور</a>
                    <a class="nav-link" href="college_reports_performance.php"><i class="fas fa-chart-line me-2"></i>تقارير الأداء</a>
                    <a class="nav-link" href="college_announcements.php"><i class="fas fa-bullhorn me-2"></i>الإعلانات</a>
                    <a class="nav-link" href="college_messages.php"><i class="fas fa-envelope me-2"></i>الرسائل</a>
                    <a class="nav-link" href="college_profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a>
                    <a class="nav-link" href="college_settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a>
                    <hr>
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a>
                </nav>
            </div>
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">لوحة تحكم إدارة الكلية</h1>
                    <div class="text-muted">
                        <i class="fas fa-calendar me-2"></i><?php echo date('Y-m-d'); ?>
                    </div>
                </div>
                
                <!-- الإحصائيات -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-2x mb-2"></i>
                                <h4>0</h4>
                                <p class="mb-0">الأقسام</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card-2">
                            <div class="card-body text-center">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                <h4>0</h4>
                                <p class="mb-0">المعلمين</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card-3">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4>0</h4>
                                <p class="mb-0">الطلاب</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card-4">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <h4>0</h4>
                                <p class="mb-0">المقررات</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- الروابط السريعة -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-rocket me-2"></i>الروابط السريعة</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <a href="college_departments.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-building me-2"></i>إدارة الأقسام
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="college_teachers.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>إدارة المعلمين
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="college_students.php" class="btn btn-outline-info w-100">
                                            <i class="fas fa-users me-2"></i>إدارة الطلاب
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="college_courses.php" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-book me-2"></i>إدارة المقررات
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>التقارير</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <a href="college_reports_academic.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-chart-bar me-2"></i>التقارير الأكاديمية
                                        </a>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <a href="college_reports_attendance.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-clipboard-check me-2"></i>تقارير الحضور
                                        </a>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <a href="college_reports_performance.php" class="btn btn-outline-info w-100">
                                            <i class="fas fa-chart-line me-2"></i>تقارير الأداء
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

