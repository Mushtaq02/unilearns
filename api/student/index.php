<?php
/**
 * واجهة برمجة التطبيقات للطلاب في نظام UniverBoard
 * تتيح الوصول إلى بيانات الطالب والمقررات والواجبات والاختبارات
 */

// استيراد ملف إعدادات API
require_once '../api_config.php';

// تحديد الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : '';

// التحقق من المصادقة
$user = authenticateApi();

// التحقق من صلاحيات الوصول
if (!checkApiPermission($user, ['student', 'teacher', 'college_admin', 'system_admin'])) {
    exit;
}

switch ($action) {
    case 'profile':
        handleProfile();
        break;
        
    case 'courses':
        handleCourses();
        break;
        
    case 'course_details':
        handleCourseDetails();
        break;
        
    case 'assignments':
        handleAssignments();
        break;
        
    case 'assignment_details':
        handleAssignmentDetails();
        break;
        
    case 'submit_assignment':
        handleSubmitAssignment();
        break;
        
    case 'quizzes':
        handleQuizzes();
        break;
        
    case 'quiz_details':
        handleQuizDetails();
        break;
        
    case 'submit_quiz':
        handleSubmitQuiz();
        break;
        
    case 'grades':
        handleGrades();
        break;
        
    case 'schedule':
        handleSchedule();
        break;
        
    case 'notifications':
        handleNotifications();
        break;
        
    default:
        sendApiError('إجراء غير صالح', 400);
        break;
}

/**
 * معالجة طلب الحصول على الملف الشخصي
 */
function handleProfile() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف الطالب
    $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;
    
    // إذا كان المستخدم طالباً ولم يتم تحديد معرف، استخدم معرف الطالب الحالي
    if ($user['type'] === 'student' && empty($studentId)) {
        $studentId = $user['student_id'];
    }
    
    // التحقق من وجود معرف الطالب
    if (empty($studentId)) {
        sendApiError('معرف الطالب مطلوب', 400);
    }
    
    // التحقق من الصلاحيات
    if ($user['type'] === 'student' && $user['student_id'] !== $studentId) {
        sendApiError('غير مصرح لك بالوصول إلى هذا الملف الشخصي', 403);
    }
    
    $db = getDbConnection();
    
    // الحصول على بيانات الطالب
    $query = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_picture,
              c.name AS college_name, d.name AS department_name, p.name AS program_name
              FROM students s
              JOIN users u ON s.user_id = u.id
              JOIN colleges c ON s.college_id = c.id
              JOIN departments d ON s.department_id = d.id
              JOIN academic_programs p ON s.program_id = p.id
              WHERE s.student_id = :studentId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['studentId' => $studentId]);
    
    $student = $stmt->fetch();
    
    if (!$student) {
        sendApiError('الطالب غير موجود', 404);
    }
    
    // تنسيق البيانات
    $profile = [
        'student_id' => $student['student_id'],
        'name' => $student['first_name'] . ' ' . $student['last_name'],
        'email' => $student['email'],
        'phone' => $student['phone'],
        'profile_picture' => $student['profile_picture'] ? BASE_URL . '/uploads/profile_pictures/' . $student['profile_picture'] : null,
        'college' => $student['college_name'],
        'department' => $student['department_name'],
        'program' => $student['program_name'],
        'academic_level' => $student['academic_level'],
        'admission_date' => $student['admission_date'],
        'expected_graduation_date' => $student['expected_graduation_date'],
        'gpa' => $student['gpa'],
        'status' => $student['status']
    ];
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'profile' => $profile
    ]);
}

/**
 * معالجة طلب الحصول على المقررات
 */
function handleCourses() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف الطالب
    $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;
    
    // إذا كان المستخدم طالباً ولم يتم تحديد معرف، استخدم معرف الطالب الحالي
    if ($user['type'] === 'student' && empty($studentId)) {
        $studentId = $user['student_id'];
    }
    
    // التحقق من وجود معرف الطالب
    if (empty($studentId)) {
        sendApiError('معرف الطالب مطلوب', 400);
    }
    
    // التحقق من الصلاحيات
    if ($user['type'] === 'student' && $user['student_id'] !== $studentId) {
        sendApiError('غير مصرح لك بالوصول إلى مقررات هذا الطالب', 403);
    }
    
    $db = getDbConnection();
    
    // الحصول على معرف الطالب الداخلي
    $studentQuery = "SELECT id FROM students WHERE student_id = :studentId";
    $studentStmt = $db->prepare($studentQuery);
    $studentStmt->execute(['studentId' => $studentId]);
    $studentRow = $studentStmt->fetch();
    
    if (!$studentRow) {
        sendApiError('الطالب غير موجود', 404);
    }
    
    $internalStudentId = $studentRow['id'];
    
    // الحصول على الفصل الدراسي الحالي
    $termQuery = "SELECT id FROM academic_terms WHERE is_current = 1";
    $termStmt = $db->query($termQuery);
    $termRow = $termStmt->fetch();
    
    if (!$termRow) {
        sendApiError('لا يوجد فصل دراسي حالي', 404);
    }
    
    $currentTermId = $termRow['id'];
    
    // الحصول على المقررات المسجلة
    $query = "SELECT c.id, c.code, c.name, c.credit_hours, cs.section_number,
              t.first_name AS teacher_first_name, t.last_name AS teacher_last_name,
              cs.days, cs.start_time, cs.end_time, cs.location, cr.status, cr.grade
              FROM course_registrations cr
              JOIN course_sections cs ON cr.section_id = cs.id
              JOIN courses c ON cs.course_id = c.id
              JOIN teachers te ON cs.teacher_id = te.id
              JOIN users t ON te.user_id = t.id
              WHERE cr.student_id = :studentId AND cs.term_id = :termId
              ORDER BY c.name";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        'studentId' => $internalStudentId,
        'termId' => $currentTermId
    ]);
    
    $courses = [];
    
    while ($row = $stmt->fetch()) {
        $courses[] = [
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'credit_hours' => $row['credit_hours'],
            'section' => $row['section_number'],
            'teacher' => $row['teacher_first_name'] . ' ' . $row['teacher_last_name'],
            'schedule' => [
                'days' => $row['days'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'location' => $row['location']
            ],
            'status' => $row['status'],
            'grade' => $row['grade']
        ];
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'courses' => $courses
    ]);
}

/**
 * معالجة طلب الحصول على تفاصيل المقرر
 */
function handleCourseDetails() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف المقرر
    $courseId = isset($_GET['course_id']) ? $_GET['course_id'] : null;
    
    // التحقق من وجود معرف المقرر
    if (empty($courseId)) {
        sendApiError('معرف المقرر مطلوب', 400);
    }
    
    $db = getDbConnection();
    
    // الحصول على تفاصيل المقرر
    $query = "SELECT c.*, d.name AS department_name
              FROM courses c
              JOIN departments d ON c.department_id = d.id
              WHERE c.id = :courseId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['courseId' => $courseId]);
    
    $course = $stmt->fetch();
    
    if (!$course) {
        sendApiError('المقرر غير موجود', 404);
    }
    
    // الحصول على المتطلبات السابقة
    $prerequisitesQuery = "SELECT c.id, c.code, c.name
                          FROM course_prerequisites cp
                          JOIN courses c ON cp.prerequisite_course_id = c.id
                          WHERE cp.course_id = :courseId";
    
    $prerequisitesStmt = $db->prepare($prerequisitesQuery);
    $prerequisitesStmt->execute(['courseId' => $courseId]);
    
    $prerequisites = [];
    
    while ($row = $prerequisitesStmt->fetch()) {
        $prerequisites[] = [
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name']
        ];
    }
    
    // تنسيق البيانات
    $courseDetails = [
        'id' => $course['id'],
        'code' => $course['code'],
        'name' => $course['name'],
        'description' => $course['description'],
        'credit_hours' => $course['credit_hours'],
        'lecture_hours' => $course['lecture_hours'],
        'lab_hours' => $course['lab_hours'],
        'level' => $course['level'],
        'department' => $course['department_name'],
        'prerequisites' => $prerequisites
    ];
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'course' => $courseDetails
    ]);
}

/**
 * معالجة طلب الحصول على الواجبات
 */
function handleAssignments() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف الطالب
    $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;
    
    // إذا كان المستخدم طالباً ولم يتم تحديد معرف، استخدم معرف الطالب الحالي
    if ($user['type'] === 'student' && empty($studentId)) {
        $studentId = $user['student_id'];
    }
    
    // التحقق من وجود معرف الطالب
    if (empty($studentId)) {
        sendApiError('معرف الطالب مطلوب', 400);
    }
    
    // التحقق من الصلاحيات
    if ($user['type'] === 'student' && $user['student_id'] !== $studentId) {
        sendApiError('غير مصرح لك بالوصول إلى واجبات هذا الطالب', 403);
    }
    
    $db = getDbConnection();
    
    // الحصول على معرف الطالب الداخلي
    $studentQuery = "SELECT id FROM students WHERE student_id = :studentId";
    $studentStmt = $db->prepare($studentQuery);
    $studentStmt->execute(['studentId' => $studentId]);
    $studentRow = $studentStmt->fetch();
    
    if (!$studentRow) {
        sendApiError('الطالب غير موجود', 404);
    }
    
    $internalStudentId = $studentRow['id'];
    
    // الحصول على الواجبات
    $query = "SELECT a.id, a.title, a.description, a.due_date, a.total_marks,
              c.code AS course_code, c.name AS course_name,
              CASE WHEN sa.id IS NULL THEN 'not_submitted' ELSE sa.status END AS submission_status,
              sa.submission_date, sa.marks
              FROM assignments a
              JOIN course_sections cs ON a.section_id = cs.id
              JOIN courses c ON cs.course_id = c.id
              JOIN course_registrations cr ON cs.id = cr.section_id
              LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.student_id = cr.student_id
              WHERE cr.student_id = :studentId
              ORDER BY a.due_date";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['studentId' => $internalStudentId]);
    
    $assignments = [];
    
    while ($row = $stmt->fetch()) {
        $assignments[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'due_date' => $row['due_date'],
            'total_marks' => $row['total_marks'],
            'course' => [
                'code' => $row['course_code'],
                'name' => $row['course_name']
            ],
            'submission' => [
                'status' => $row['submission_status'],
                'date' => $row['submission_date'],
                'marks' => $row['marks']
            ]
        ];
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'assignments' => $assignments
    ]);
}

// باقي الدوال ستكون مشابهة للدوال السابقة، مع تعديل الاستعلامات والبيانات المرجعة
// لتناسب كل إجراء (تفاصيل الواجب، تسليم الواجب، الاختبارات، إلخ)
