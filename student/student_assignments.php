<?php
/**
 * صفحة الواجبات للطالب
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

// الحصول على الواجبات
$assignments = [];
try {
    $query = "SELECT a.*, c.name as course_name, c.code as course_code,
                     s.submission_date, s.grade, s.status as submission_status
              FROM assignments a 
              JOIN courses c ON a.course_id = c.id
              JOIN enrollments e ON c.id = e.course_id 
              LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = :student_id
              WHERE e.student_id = :student_id 
              ORDER BY a.due_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['student_id' => $student_id]);
    $assignments = $stmt->fetchAll();
} catch (Exception $e) {
    $assignments = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الواجبات</title>
    
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
        .assignment-card {
            transition: transform 0.2s;
        }
        .assignment-card:hover {
            transform: translateY(-5px);
        }
        .due-soon {
            border-left: 4px solid #dc3545;
        }
        .due-normal {
            border-left: 4px solid #28a745;
        }
        .overdue {
            border-left: 4px solid #ffc107;
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
                    <a class="nav-link" href="student_courses.php">
                        <i class="fas fa-book me-2"></i>
                        المقررات
                    </a>
                    <a class="nav-link active" href="student_assignments.php">
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
                        <i class="fas fa-tasks me-2"></i>
                        الواجبات
                    </h1>
                    <div>
                        <span class="badge bg-primary"><?php echo count($assignments); ?> واجب</span>
                    </div>
                </div>
                
                <?php if (empty($assignments)): ?>
                    <!-- رسالة عدم وجود واجبات -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد واجبات</h4>
                            <p class="text-muted">لا توجد واجبات مطلوبة حالياً. سيتم إشعارك عند إضافة واجبات جديدة.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- قائمة الواجبات -->
                    <div class="row">
                        <?php foreach ($assignments as $assignment): 
                            $due_date = new DateTime($assignment['due_date']);
                            $now = new DateTime();
                            $diff = $now->diff($due_date);
                            
                            if ($due_date < $now) {
                                $status_class = 'overdue';
                                $status_text = 'متأخر';
                                $status_badge = 'danger';
                            } elseif ($diff->days <= 3) {
                                $status_class = 'due-soon';
                                $status_text = 'مستحق قريباً';
                                $status_badge = 'warning';
                            } else {
                                $status_class = 'due-normal';
                                $status_text = 'عادي';
                                $status_badge = 'success';
                            }
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card assignment-card h-100 <?php echo $status_class; ?>">
                                    <div class="card-header">
                                        <h5 class="card-title mb-1">
                                            <?php echo htmlspecialchars($assignment['title']); ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($assignment['course_name']); ?>
                                            (<?php echo htmlspecialchars($assignment['course_code']); ?>)
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($assignment['description'] ?? 'لا يوجد وصف متاح'); ?>
                                        </p>
                                        
                                        <div class="mb-2">
                                            <strong>تاريخ الاستحقاق:</strong>
                                            <?php echo $due_date->format('Y-m-d H:i'); ?>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <strong>الدرجة الكاملة:</strong>
                                            <?php echo htmlspecialchars($assignment['max_grade'] ?? '100'); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="badge bg-<?php echo $status_badge; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                            
                                            <?php if ($assignment['submission_status']): ?>
                                                <span class="badge bg-info">
                                                    <?php 
                                                    switch($assignment['submission_status']) {
                                                        case 'submitted': echo 'تم التسليم'; break;
                                                        case 'graded': echo 'تم التقييم'; break;
                                                        case 'late': echo 'متأخر'; break;
                                                        default: echo $assignment['submission_status'];
                                                    }
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($assignment['grade']): ?>
                                            <div class="mb-2">
                                                <strong>الدرجة:</strong>
                                                <span class="badge bg-success">
                                                    <?php echo htmlspecialchars($assignment['grade']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-grid gap-2">
                                            <?php if (!$assignment['submission_status']): ?>
                                                <a href="submit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload me-1"></i>
                                                    تسليم الواجب
                                                </a>
                                            <?php else: ?>
                                                <a href="view_submission.php?id=<?php echo $assignment['id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    عرض التسليم
                                                </a>
                                            <?php endif; ?>
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

