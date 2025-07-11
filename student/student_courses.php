<?php
/**
 * صفحة المقررات للطالب
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

// الحصول على المقررات المسجل فيها الطالب
$courses = [];
try {
    $query = "SELECT c.*, e.enrollment_date, e.status as enrollment_status,
                     u.first_name as teacher_first_name, u.last_name as teacher_last_name
              FROM courses c 
              JOIN enrollments e ON c.id = e.course_id 
              LEFT JOIN users u ON c.teacher_id = u.id
              WHERE e.student_id = :student_id 
              ORDER BY c.name";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['student_id' => $student_id]);
    $courses = $stmt->fetchAll();
} catch (Exception $e) {
    $courses = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - المقررات</title>
    
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
        .course-card {
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-5px);
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
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        لوحة التحكم
                    </a>
                    <a class="nav-link active" href="student_courses.php">
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
                    <h1 class="h3">
                        <i class="fas fa-book me-2"></i>
                        المقررات الدراسية
                    </h1>
                    <div>
                        <span class="badge bg-primary"><?php echo count($courses); ?> مقرر</span>
                    </div>
                </div>
                
                <?php if (empty($courses)): ?>
                    <!-- رسالة عدم وجود مقررات -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد مقررات مسجلة</h4>
                            <p class="text-muted">لم يتم تسجيلك في أي مقرر حتى الآن. يرجى التواصل مع إدارة الكلية لتسجيل المقررات.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- قائمة المقررات -->
                    <div class="row">
                        <?php foreach ($courses as $course): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card course-card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <?php echo htmlspecialchars($course['name']); ?>
                                        </h5>
                                        <small><?php echo htmlspecialchars($course['code']); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($course['description'] ?? 'لا يوجد وصف متاح'); ?>
                                        </p>
                                        
                                        <div class="mb-2">
                                            <strong>المدرس:</strong>
                                            <?php 
                                            if ($course['teacher_first_name'] && $course['teacher_last_name']) {
                                                echo htmlspecialchars($course['teacher_first_name'] . ' ' . $course['teacher_last_name']);
                                            } else {
                                                echo 'غير محدد';
                                            }
                                            ?>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <strong>الساعات المعتمدة:</strong>
                                            <?php echo htmlspecialchars($course['credit_hours'] ?? '0'); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>تاريخ التسجيل:</strong>
                                            <?php echo date('Y-m-d', strtotime($course['enrollment_date'])); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="badge bg-<?php echo $course['enrollment_status'] === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo $course['enrollment_status'] === 'active' ? 'نشط' : 'معلق'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-grid gap-2">
                                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                عرض التفاصيل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

