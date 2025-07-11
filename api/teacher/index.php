<?php
/**
 * واجهة برمجة التطبيقات للمعلمين في نظام UniverBoard
 * تتيح الوصول إلى بيانات المعلم والمقررات والواجبات والاختبارات
 */

// استيراد ملف إعدادات API
require_once '../api_config.php';

// تحديد الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : '';

// التحقق من المصادقة
$user = authenticateApi();

// التحقق من صلاحيات الوصول
if (!checkApiPermission($user, ['teacher', 'college_admin', 'system_admin'])) {
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
        
    case 'students':
        handleStudents();
        break;
        
    case 'assignments':
        handleAssignments();
        break;
        
    case 'create_assignment':
        handleCreateAssignment();
        break;
        
    case 'update_assignment':
        handleUpdateAssignment();
        break;
        
    case 'assignment_submissions':
        handleAssignmentSubmissions();
        break;
        
    case 'grade_assignment':
        handleGradeAssignment();
        break;
        
    case 'quizzes':
        handleQuizzes();
        break;
        
    case 'create_quiz':
        handleCreateQuiz();
        break;
        
    case 'update_quiz':
        handleUpdateQuiz();
        break;
        
    case 'quiz_submissions':
        handleQuizSubmissions();
        break;
        
    case 'grade_quiz':
        handleGradeQuiz();
        break;
        
    case 'attendance':
        handleAttendance();
        break;
        
    case 'update_attendance':
        handleUpdateAttendance();
        break;
        
    case 'grades':
        handleGrades();
        break;
        
    case 'update_grades':
        handleUpdateGrades();
        break;
        
    case 'announcements':
        handleAnnouncements();
        break;
        
    case 'create_announcement':
        handleCreateAnnouncement();
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
    
    // الحصول على معرف المعلم
    $teacherId = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;
    
    // إذا كان المستخدم معلماً ولم يتم تحديد معرف، استخدم معرف المعلم الحالي
    if ($user['type'] === 'teacher' && empty($teacherId)) {
        $teacherId = $user['teacher_id'];
    }
    
    // التحقق من وجود معرف المعلم
    if (empty($teacherId)) {
        sendApiError('معرف المعلم مطلوب', 400);
    }
    
    $db = getDbConnection();
    
    // الحصول على بيانات المعلم
    $query = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_picture,
              c.name AS college_name, d.name AS department_name
              FROM teachers t
              JOIN users u ON t.user_id = u.id
              JOIN colleges c ON t.college_id = c.id
              JOIN departments d ON t.department_id = d.id
              WHERE t.teacher_id = :teacherId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['teacherId' => $teacherId]);
    
    $teacher = $stmt->fetch();
    
    if (!$teacher) {
        sendApiError('المعلم غير موجود', 404);
    }
    
    // تنسيق البيانات
    $profile = [
        'teacher_id' => $teacher['teacher_id'],
        'name' => $teacher['first_name'] . ' ' . $teacher['last_name'],
        'email' => $teacher['email'],
        'phone' => $teacher['phone'],
        'profile_picture' => $teacher['profile_picture'] ? BASE_URL . '/uploads/profile_pictures/' . $teacher['profile_picture'] : null,
        'college' => $teacher['college_name'],
        'department' => $teacher['department_name'],
        'position' => $teacher['position'],
        'specialization' => $teacher['specialization'],
        'qualification' => $teacher['qualification'],
        'hire_date' => $teacher['hire_date'],
        'office_location' => $teacher['office_location'],
        'office_hours' => $teacher['office_hours'],
        'status' => $teacher['status']
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
    
    // الحصول على معرف المعلم
    $teacherId = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;
    
    // إذا كان المستخدم معلماً ولم يتم تحديد معرف، استخدم معرف المعلم الحالي
    if ($user['type'] === 'teacher' && empty($teacherId)) {
        $teacherId = $user['teacher_id'];
    }
    
    // التحقق من وجود معرف المعلم
    if (empty($teacherId)) {
        sendApiError('معرف المعلم مطلوب', 400);
    }
    
    $db = getDbConnection();
    
    // الحصول على معرف المعلم الداخلي
    $teacherQuery = "SELECT id FROM teachers WHERE teacher_id = :teacherId";
    $teacherStmt = $db->prepare($teacherQuery);
    $teacherStmt->execute(['teacherId' => $teacherId]);
    $teacherRow = $teacherStmt->fetch();
    
    if (!$teacherRow) {
        sendApiError('المعلم غير موجود', 404);
    }
    
    $internalTeacherId = $teacherRow['id'];
    
    // الحصول على الفصل الدراسي الحالي
    $termQuery = "SELECT id FROM academic_terms WHERE is_current = 1";
    $termStmt = $db->query($termQuery);
    $termRow = $termStmt->fetch();
    
    if (!$termRow) {
        sendApiError('لا يوجد فصل دراسي حالي', 404);
    }
    
    $currentTermId = $termRow['id'];
    
    // الحصول على المقررات
    $query = "SELECT cs.id AS section_id, c.id AS course_id, c.code, c.name, c.credit_hours,
              cs.section_number, cs.capacity, cs.enrolled_count, cs.location,
              cs.days, cs.start_time, cs.end_time, cs.status
              FROM course_sections cs
              JOIN courses c ON cs.course_id = c.id
              WHERE cs.teacher_id = :teacherId AND cs.term_id = :termId
              ORDER BY c.name";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        'teacherId' => $internalTeacherId,
        'termId' => $currentTermId
    ]);
    
    $courses = [];
    
    while ($row = $stmt->fetch()) {
        $courses[] = [
            'section_id' => $row['section_id'],
            'course_id' => $row['course_id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'credit_hours' => $row['credit_hours'],
            'section' => $row['section_number'],
            'capacity' => $row['capacity'],
            'enrolled_count' => $row['enrolled_count'],
            'schedule' => [
                'days' => $row['days'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'location' => $row['location']
            ],
            'status' => $row['status']
        ];
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'courses' => $courses
    ]);
}

/**
 * معالجة طلب الحصول على طلاب المقرر
 */
function handleStudents() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف الشعبة
    $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : null;
    
    // التحقق من وجود معرف الشعبة
    if (empty($sectionId)) {
        sendApiError('معرف الشعبة مطلوب', 400);
    }
    
    $db = getDbConnection();
    
    // التحقق من صلاحية الوصول للشعبة
    if ($user['type'] === 'teacher') {
        $teacherQuery = "SELECT id FROM teachers WHERE teacher_id = :teacherId";
        $teacherStmt = $db->prepare($teacherQuery);
        $teacherStmt->execute(['teacherId' => $user['teacher_id']]);
        $teacherRow = $teacherStmt->fetch();
        
        if (!$teacherRow) {
            sendApiError('المعلم غير موجود', 404);
        }
        
        $internalTeacherId = $teacherRow['id'];
        
        $sectionQuery = "SELECT id FROM course_sections WHERE id = :sectionId AND teacher_id = :teacherId";
        $sectionStmt = $db->prepare($sectionQuery);
        $sectionStmt->execute([
            'sectionId' => $sectionId,
            'teacherId' => $internalTeacherId
        ]);
        
        if (!$sectionStmt->fetch()) {
            sendApiError('غير مصرح لك بالوصول إلى هذه الشعبة', 403);
        }
    }
    
    // الحصول على الطلاب المسجلين في الشعبة
    $query = "SELECT s.student_id, u.first_name, u.last_name, u.email, u.profile_picture,
              cr.status, cr.grade, cr.grade_points
              FROM course_registrations cr
              JOIN students s ON cr.student_id = s.id
              JOIN users u ON s.user_id = u.id
              WHERE cr.section_id = :sectionId
              ORDER BY u.last_name, u.first_name";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['sectionId' => $sectionId]);
    
    $students = [];
    
    while ($row = $stmt->fetch()) {
        $students[] = [
            'student_id' => $row['student_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'profile_picture' => $row['profile_picture'] ? BASE_URL . '/uploads/profile_pictures/' . $row['profile_picture'] : null,
            'status' => $row['status'],
            'grade' => $row['grade'],
            'grade_points' => $row['grade_points']
        ];
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'students' => $students
    ]);
}

// باقي الدوال ستكون مشابهة للدوال السابقة، مع تعديل الاستعلامات والبيانات المرجعة
// لتناسب كل إجراء (إنشاء واجب، تحديث واجب، تقييم واجب، إلخ)
