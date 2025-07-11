<?php
/**
 * صفحة الدرجات للطالب
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

// الحصول على الدرجات
$grades = [];
try {
    $query = "SELECT c.name as course_name, c.code as course_code,
                     a.title as assignment_title, s.grade, s.submission_date,
                     a.max_grade, s.feedback
              FROM submissions s
              JOIN assignments a ON s.assignment_id = a.id
              JOIN courses c ON a.course_id = c.id
              WHERE s.student_id = :student_id AND s.grade IS NOT NULL
              ORDER BY s.submission_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['student_id' => $student_id]);
    $grades = $stmt->fetchAll();
} catch (Exception $e) {
    $grades = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الدرجات</title>
    
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
        .grade-excellent { background-color: #d4edda; }
        .grade-good { background-color: #d1ecf1; }
        .grade-average { background-color: #fff3cd; }
        .grade-poor { background-color: #f8d7da; }
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
                    <a class="nav-link" href="student_assignments.php">
                        <i class="fas fa-tasks me-2"></i>
                        الواجبات
                    </a>
                    <a class="nav-link active" href="student_grades.php">
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
                        <i class="fas fa-chart-line me-2"></i>
                        الدرجات والتقييمات
                    </h1>
                    <div>
                        <span class="badge bg-primary"><?php echo count($grades); ?> تقييم</span>
                    </div>
                </div>
                
                <?php if (empty($grades)): ?>
                    <!-- رسالة عدم وجود درجات -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد درجات</h4>
                            <p class="text-muted">لم يتم تقييم أي من أعمالك حتى الآن. ستظهر الدرجات هنا بعد تقييم المعلمين لأعمالك.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- جدول الدرجات -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table me-2"></i>
                                سجل الدرجات
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>المقرر</th>
                                            <th>الواجب/الاختبار</th>
                                            <th>الدرجة</th>
                                            <th>النسبة المئوية</th>
                                            <th>التقدير</th>
                                            <th>تاريخ التقييم</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grades as $grade): 
                                            $percentage = ($grade['grade'] / $grade['max_grade']) * 100;
                                            
                                            if ($percentage >= 90) {
                                                $grade_class = 'grade-excellent';
                                                $grade_text = 'ممتاز';
                                                $badge_class = 'success';
                                            } elseif ($percentage >= 80) {
                                                $grade_class = 'grade-good';
                                                $grade_text = 'جيد جداً';
                                                $badge_class = 'info';
                                            } elseif ($percentage >= 70) {
                                                $grade_class = 'grade-average';
                                                $grade_text = 'جيد';
                                                $badge_class = 'warning';
                                            } else {
                                                $grade_class = 'grade-poor';
                                                $grade_text = 'مقبول';
                                                $badge_class = 'danger';
                                            }
                                        ?>
                                            <tr class="<?php echo $grade_class; ?>">
                                                <td>
                                                    <strong><?php echo htmlspecialchars($grade['course_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($grade['course_code']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($grade['assignment_title']); ?></td>
                                                <td>
                                                    <strong><?php echo $grade['grade']; ?></strong> / <?php echo $grade['max_grade']; ?>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php echo $badge_class; ?>" 
                                                             style="width: <?php echo $percentage; ?>%">
                                                            <?php echo round($percentage, 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo $grade_text; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($grade['submission_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- إحصائيات الدرجات -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        إحصائيات الأداء
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $total_grades = count($grades);
                                    $excellent = 0; $good = 0; $average = 0; $poor = 0;
                                    
                                    foreach ($grades as $grade) {
                                        $percentage = ($grade['grade'] / $grade['max_grade']) * 100;
                                        if ($percentage >= 90) $excellent++;
                                        elseif ($percentage >= 80) $good++;
                                        elseif ($percentage >= 70) $average++;
                                        else $poor++;
                                    }
                                    ?>
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="badge bg-success p-3 w-100">
                                                <div class="h4 mb-0"><?php echo $excellent; ?></div>
                                                <div>ممتاز</div>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="badge bg-info p-3 w-100">
                                                <div class="h4 mb-0"><?php echo $good; ?></div>
                                                <div>جيد جداً</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="badge bg-warning p-3 w-100">
                                                <div class="h4 mb-0"><?php echo $average; ?></div>
                                                <div>جيد</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="badge bg-danger p-3 w-100">
                                                <div class="h4 mb-0"><?php echo $poor; ?></div>
                                                <div>مقبول</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calculator me-2"></i>
                                        المعدل العام
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <?php
                                    $total_points = 0;
                                    $total_max = 0;
                                    foreach ($grades as $grade) {
                                        $total_points += $grade['grade'];
                                        $total_max += $grade['max_grade'];
                                    }
                                    $overall_percentage = $total_max > 0 ? ($total_points / $total_max) * 100 : 0;
                                    ?>
                                    <div class="display-4 text-primary mb-3">
                                        <?php echo round($overall_percentage, 1); ?>%
                                    </div>
                                    <p class="text-muted">المعدل التراكمي</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

