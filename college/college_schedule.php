<?php
session_start();
require_once __DIR__ . 
'/../includes/config.php';
require_once __DIR__ . 
'/../includes/functions.php';

// التحقق من تسجيل الدخول ونوع المستخدم
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'college_admin') {
    redirect('/unilearns/index.php');
}

$db = get_db_connection();

$college_id = $_SESSION['college_id']; // افترض أن معرف الكلية مخزن في الجلسة

// معالجة طلبات إضافة/تعديل/حذف المواعيد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_class':
            $course_id = sanitizeInput($_POST['course_id']);
            $teacher_id = sanitizeInput($_POST['teacher_id']);
            $day_of_week = sanitizeInput($_POST['day_of_week']);
            $start_time = sanitizeInput($_POST['start_time']);
            $end_time = sanitizeInput($_POST['end_time']);
            $location = sanitizeInput($_POST['location']);

            if (add_class_schedule($db, $college_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $location)) {
                showSuccess('تم إضافة موعد المحاضرة بنجاح.');
            } else {
                showError('حدث خطأ أثناء إضافة موعد المحاضرة.');
            }
            break;
        case 'edit_class':
            $event_id = sanitizeInput($_POST['event_id']);
            $course_id = sanitizeInput($_POST['course_id']);
            $teacher_id = sanitizeInput($_POST['teacher_id']);
            $day_of_week = sanitizeInput($_POST['day_of_week']);
            $start_time = sanitizeInput($_POST['start_time']);
            $end_time = sanitizeInput($_POST['end_time']);
            $location = sanitizeInput($_POST['location']);

            if (update_class_schedule($db, $event_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $location)) {
                showSuccess('تم تعديل موعد المحاضرة بنجاح.');
            } else {
                showError('حدث خطأ أثناء تعديل موعد المحاضرة.');
            }
            break;
        case 'delete_class':
            $event_id = sanitizeInput($_POST['event_id']);
            if (delete_class_schedule($db, $event_id)) {
                showSuccess('تم حذف موعد المحاضرة بنجاح.');
            } else {
                showError('حدث خطأ أثناء حذف موعد المحاضرة.');
            }
            break;
        case 'add_exam':
            $course_id = sanitizeInput($_POST['course_id']);
            $teacher_id = sanitizeInput($_POST['teacher_id']);
            $exam_date = sanitizeInput($_POST['exam_date']);
            $start_time = sanitizeInput($_POST['start_time']);
            $end_time = sanitizeInput($_POST['end_time']);
            $location = sanitizeInput($_POST['location']);
            $exam_duration = sanitizeInput($_POST['exam_duration']);

            if (add_exam_schedule($db, $college_id, $course_id, $teacher_id, $exam_date, $start_time, $end_time, $location, $exam_duration)) {
                showSuccess('تم إضافة موعد الاختبار بنجاح.');
            } else {
                showError('حدث خطأ أثناء إضافة موعد الاختبار.');
            }
            break;
        case 'edit_exam':
            $event_id = sanitizeInput($_POST['event_id']);
            $course_id = sanitizeInput($_POST['course_id']);
            $teacher_id = sanitizeInput($_POST['teacher_id']);
            $exam_date = sanitizeInput($_POST['exam_date']);
            $start_time = sanitizeInput($_POST['start_time']);
            $end_time = sanitizeInput($_POST['end_time']);
            $location = sanitizeInput($_POST['location']);
            $exam_duration = sanitizeInput($_POST['exam_duration']);

            if (update_exam_schedule($db, $event_id, $course_id, $teacher_id, $exam_date, $start_time, $end_time, $location, $exam_duration)) {
                showSuccess('تم تعديل موعد الاختبار بنجاح.');
            } else {
                showError('حدث خطأ أثناء تعديل موعد الاختبار.');
            }
            break;
        case 'delete_exam':
            $event_id = sanitizeInput($_POST['event_id']);
            if (delete_exam_schedule($db, $event_id)) {
                showSuccess('تم حذف موعد الاختبار بنجاح.');
            } else {
                showError('حدث خطأ أثناء حذف موعد الاختبار.');
            }
            break;
    }
}

// جلب البيانات لعرضها
$courses = get_college_courses($db, $college_id); // دالة افتراضية، يجب تعريفها
$teachers = get_college_teachers($db, $college_id);
$class_schedule = get_class_schedule($db, ['college_id' => $college_id]);
$exam_schedule = get_exam_schedule($db, ['college_id' => $college_id]);

$db->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الجدول الدراسي - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/unilearns/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/unilearns/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container content-container">
        <h1 class="text-center mb-4">إدارة الجدول الدراسي للكلية</h1>

        <?php displayMessages(); ?>

        <!-- تبويبات المحاضرات والاختبارات -->
        <ul class="nav nav-tabs mb-4" id="scheduleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button" role="tab" aria-controls="classes" aria-selected="true">المحاضرات</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="exams-tab" data-bs-toggle="tab" data-bs-target="#exams" type="button" role="tab" aria-controls="exams" aria-selected="false">الاختبارات</button>
            </li>
        </ul>

        <div class="tab-content" id="scheduleTabsContent">
            <!-- تبويب المحاضرات -->
            <div class="tab-pane fade show active" id="classes" role="tabpanel" aria-labelledby="classes-tab">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title m-0">مواعيد المحاضرات</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal"><i class="fas fa-plus"></i> إضافة محاضرة</button>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($class_schedule)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>المقرر</th>
                                            <th>المعلم</th>
                                            <th>اليوم</th>
                                            <th>وقت البدء</th>
                                            <th>وقت الانتهاء</th>
                                            <th>الموقع</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($class_schedule as $event): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($event['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($event['teacher_name']); ?></td>
                                                <td><?php echo get_day_name($event['day_of_week']); ?></td>
                                                <td><?php echo htmlspecialchars($event['start_time']); ?></td>
                                                <td><?php echo htmlspecialchars($event['end_time']); ?></td>
                                                <td><?php echo htmlspecialchars($event['classroom_name']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-class-btn" data-id="<?php echo $event['id']; ?>" data-course-id="<?php echo $event['course_id']; ?>" data-teacher-id="<?php echo $event['teacher_id']; ?>" data-day="<?php echo $event['day_of_week']; ?>" data-start-time="<?php echo $event['start_time']; ?>" data-end-time="<?php echo $event['end_time']; ?>" data-location="<?php echo $event['classroom_name']; ?>" data-bs-toggle="modal" data-bs-target="#editClassModal"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-danger delete-class-btn" data-id="<?php echo $event['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteClassModal"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">لا توجد مواعيد محاضرات مسجلة حالياً.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- تبويب الاختبارات -->
            <div class="tab-pane fade" id="exams" role="tabpanel" aria-labelledby="exams-tab">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title m-0">مواعيد الاختبارات</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal"><i class="fas fa-plus"></i> إضافة اختبار</button>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($exam_schedule)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>المقرر</th>
                                            <th>المعلم</th>
                                            <th>التاريخ</th>
                                            <th>وقت البدء</th>
                                            <th>وقت الانتهاء</th>
                                            <th>المدة (دقيقة)</th>
                                            <th>الموقع</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($exam_schedule as $event): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($event['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($event['teacher_name']); ?></td>
                                                <td><?php echo htmlspecialchars($event['exam_date']); ?></td>
                                                <td><?php echo htmlspecialchars($event['start_time']); ?></td>
                                                <td><?php echo htmlspecialchars($event['end_time']); ?></td>
                                                <td><?php echo htmlspecialchars($event['duration_minutes']); ?></td>
                                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-exam-btn" data-id="<?php echo $event['id']; ?>" data-course-id="<?php echo $event['course_id']; ?>" data-teacher-id="<?php echo $event['teacher_id']; ?>" data-exam-date="<?php echo $event['exam_date']; ?>" data-start-time="<?php echo $event['start_time']; ?>" data-end-time="<?php echo $event['end_time']; ?>" data-duration="<?php echo $event['duration_minutes']; ?>" data-location="<?php echo $event['location']; ?>" data-bs-toggle="modal" data-bs-target="#editExamModal"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-danger delete-exam-btn" data-id="<?php echo $event['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteExamModal"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">لا توجد مواعيد اختبارات مسجلة حالياً.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for Add/Edit/Delete Class -->
    <!-- Add Class Modal -->
    <div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClassModalLabel">إضافة موعد محاضرة جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_class">
                        <div class="mb-3">
                            <label for="addClassCourse" class="form-label">المقرر</label>
                            <select class="form-select" id="addClassCourse" name="course_id" required>
                                <option value="">اختر المقرر</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addClassTeacher" class="form-label">المعلم</label>
                            <select class="form-select" id="addClassTeacher" name="teacher_id" required>
                                <option value="">اختر المعلم</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addClassDay" class="form-label">اليوم</label>
                            <select class="form-select" id="addClassDay" name="day_of_week" required>
                                <option value="">اختر اليوم</option>
                                <option value="0">الأحد</option>
                                <option value="1">الاثنين</option>
                                <option value="2">الثلاثاء</option>
                                <option value="3">الأربعاء</option>
                                <option value="4">الخميس</option>
                                <option value="5">الجمعة</option>
                                <option value="6">السبت</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addClassStartTime" class="form-label">وقت البدء</label>
                            <input type="time" class="form-control" id="addClassStartTime" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="addClassEndTime" class="form-label">وقت الانتهاء</label>
                            <input type="time" class="form-control" id="addClassEndTime" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="addClassLocation" class="form-label">الموقع/القاعة</label>
                            <input type="text" class="form-control" id="addClassLocation" name="location" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassModalLabel">تعديل موعد محاضرة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_class">
                        <input type="hidden" name="event_id" id="editClassId">
                        <div class="mb-3">
                            <label for="editClassCourse" class="form-label">المقرر</label>
                            <select class="form-select" id="editClassCourse" name="course_id" required>
                                <option value="">اختر المقرر</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editClassTeacher" class="form-label">المعلم</label>
                            <select class="form-select" id="editClassTeacher" name="teacher_id" required>
                                <option value="">اختر المعلم</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editClassDay" class="form-label">اليوم</label>
                            <select class="form-select" id="editClassDay" name="day_of_week" required>
                                <option value="">اختر اليوم</option>
                                <option value="0">الأحد</option>
                                <option value="1">الاثنين</option>
                                <option value="2">الثلاثاء</option>
                                <option value="3">الأربعاء</option>
                                <option value="4">الخميس</option>
                                <option value="5">الجمعة</option>
                                <option value="6">السبت</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editClassStartTime" class="form-label">وقت البدء</label>
                            <input type="time" class="form-control" id="editClassStartTime" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="editClassEndTime" class="form-label">وقت الانتهاء</label>
                            <input type="time" class="form-control" id="editClassEndTime" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="editClassLocation" class="form-label">الموقع/القاعة</label>
                            <input type="text" class="form-control" id="editClassLocation" name="location" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Class Modal -->
    <div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteClassModalLabel">تأكيد حذف موعد محاضرة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_class">
                        <input type="hidden" name="event_id" id="deleteClassId">
                        <p>هل أنت متأكد أنك تريد حذف موعد المحاضرة هذا؟</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals for Add/Edit/Delete Exam -->
    <!-- Add Exam Modal -->
    <div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExamModalLabel">إضافة موعد اختبار جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_exam">
                        <div class="mb-3">
                            <label for="addExamCourse" class="form-label">المقرر</label>
                            <select class="form-select" id="addExamCourse" name="course_id" required>
                                <option value="">اختر المقرر</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addExamTeacher" class="form-label">المعلم</label>
                            <select class="form-select" id="addExamTeacher" name="teacher_id" required>
                                <option value="">اختر المعلم</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addExamDate" class="form-label">التاريخ</label>
                            <input type="date" class="form-control" id="addExamDate" name="exam_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="addExamStartTime" class="form-label">وقت البدء</label>
                            <input type="time" class="form-control" id="addExamStartTime" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="addExamEndTime" class="form-label">وقت الانتهاء</label>
                            <input type="time" class="form-control" id="addExamEndTime" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="addExamDuration" class="form-label">المدة (بالدقائق)</label>
                            <input type="number" class="form-control" id="addExamDuration" name="exam_duration" required>
                        </div>
                        <div class="mb-3">
                            <label for="addExamLocation" class="form-label">الموقع/القاعة</label>
                            <input type="text" class="form-control" id="addExamLocation" name="location" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Exam Modal -->
    <div class="modal fade" id="editExamModal" tabindex="-1" aria-labelledby="editExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExamModalLabel">تعديل موعد اختبار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_exam">
                        <input type="hidden" name="event_id" id="editExamId">
                        <div class="mb-3">
                            <label for="editExamCourse" class="form-label">المقرر</label>
                            <select class="form-select" id="editExamCourse" name="course_id" required>
                                <option value="">اختر المقرر</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editExamTeacher" class="form-label">المعلم</label>
                            <select class="form-select" id="editExamTeacher" name="teacher_id" required>
                                <option value="">اختر المعلم</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editExamDate" class="form-label">التاريخ</label>
                            <input type="date" class="form-control" id="editExamDate" name="exam_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="editExamStartTime" class="form-label">وقت البدء</label>
                            <input type="time" class="form-control" id="editExamStartTime" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="editExamEndTime" class="form-label">وقت الانتهاء</label>
                            <input type="time" class="form-control" id="editExamEndTime" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="editExamDuration" class="form-label">المدة (بالدقائق)</label>
                            <input type="number" class="form-control" id="editExamDuration" name="exam_duration" required>
                        </div>
                        <div class="mb-3">
                            <label for="editExamLocation" class="form-label">الموقع/القاعة</label>
                            <input type="text" class="form-control" id="editExamLocation" name="location" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Exam Modal -->
    <div class="modal fade" id="deleteExamModal" tabindex="-1" aria-labelledby="deleteExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteExamModalLabel">تأكيد حذف موعد اختبار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_exam">
                        <input type="hidden" name="event_id" id="deleteExamId">
                        <p>هل أنت متأكد أنك تريد حذف موعد الاختبار هذا؟</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="/unilearns/assets/js/jquery-3.6.0.min.js"></script>
    <script src="/unilearns/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Populate Edit Class Modal
            $(".edit-class-btn").on("click", function() {
                var id = $(this).data("id");
                var courseId = $(this).data("course-id");
                var teacherId = $(this).data("teacher-id");
                var day = $(this).data("day");
                var startTime = $(this).data("start-time");
                var endTime = $(this).data("end-time");
                var location = $(this).data("location");

                $("#editClassId").val(id);
                $("#editClassCourse").val(courseId);
                $("#editClassTeacher").val(teacherId);
                $("#editClassDay").val(day);
                $("#editClassStartTime").val(startTime);
                $("#editClassEndTime").val(endTime);
                $("#editClassLocation").val(location);
            });

            // Populate Delete Class Modal
            $(".delete-class-btn").on("click", function() {
                var id = $(this).data("id");
                $("#deleteClassId").val(id);
            });

            // Populate Edit Exam Modal
            $(".edit-exam-btn").on("click", function() {
                var id = $(this).data("id");
                var courseId = $(this).data("course-id");
                var teacherId = $(this).data("teacher-id");
                var examDate = $(this).data("exam-date");
                var startTime = $(this).data("start-time");
                var endTime = $(this).data("end-time");
                var duration = $(this).data("duration");
                var location = $(this).data("location");

                $("#editExamId").val(id);
                $("#editExamCourse").val(courseId);
                $("#editExamTeacher").val(teacherId);
                $("#editExamDate").val(examDate);
                $("#editExamStartTime").val(startTime);
                $("#editExamEndTime").val(endTime);
                $("#editExamDuration").val(duration);
                $("#editExamLocation").val(location);
            });

            // Populate Delete Exam Modal
            $(".delete-exam-btn").on("click", function() {
                var id = $(this).data("id");
                $("#deleteExamId").val(id);
            });
        });

        function get_day_name(day_of_week) {
            const days = ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"];
            return days[day_of_week];
        }
    </script>
</body>
</html>

