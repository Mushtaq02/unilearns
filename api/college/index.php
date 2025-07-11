<?php
/**
 * واجهة برمجة التطبيقات للكليات في نظام UniverBoard
 * تتيح الوصول إلى بيانات الكليات والأقسام والبرامج الأكاديمية
 */

// استيراد ملف إعدادات API
require_once '../api_config.php';

// تحديد الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : '';

// التحقق من المصادقة
$user = authenticateApi();

// التحقق من صلاحيات الوصول
if (!checkApiPermission($user, ['college_admin', 'system_admin'])) {
    exit;
}

switch ($action) {
    case 'college_info':
        handleCollegeInfo();
        break;
        
    case 'departments':
        handleDepartments();
        break;
        
    case 'department_details':
        handleDepartmentDetails();
        break;
        
    case 'programs':
        handlePrograms();
        break;
        
    case 'program_details':
        handleProgramDetails();
        break;
        
    case 'study_plans':
        handleStudyPlans();
        break;
        
    case 'study_plan_details':
        handleStudyPlanDetails();
        break;
        
    case 'teachers':
        handleTeachers();
        break;
        
    case 'students':
        handleStudents();
        break;
        
    case 'courses':
        handleCourses();
        break;
        
    case 'course_sections':
        handleCourseSections();
        break;
        
    case 'create_course_section':
        handleCreateCourseSection();
        break;
        
    case 'update_course_section':
        handleUpdateCourseSection();
        break;
        
    case 'academic_terms':
        handleAcademicTerms();
        break;
        
    case 'statistics':
        handleStatistics();
        break;
        
    default:
        sendApiError('إجراء غير صالح', 400);
        break;
}

/**
 * معالجة طلب الحصول على معلومات الكلية
 */
function handleCollegeInfo() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف الكلية
    $collegeId = isset($_GET['college_id']) ? $_GET['college_id'] : null;
    
    // إذا كان المستخدم إداري كلية ولم يتم تحديد معرف، استخدم معرف الكلية الحالي
    if ($user['type'] === 'college_admin' && empty($collegeId)) {
        $collegeId = $user['college_id'];
    }
    
    // التحقق من وجود معرف الكلية
    if (empty($collegeId)) {
        sendApiError('معرف الكلية مطلوب', 400);
    }
    
    // التحقق من الصلاحيات
    if ($user['type'] === 'college_admin' && $user['college_id'] != $collegeId) {
        sendApiError('غير مصرح لك بالوصول إلى معلومات هذه الكلية', 403);
    }
    
    $db = getDbConnection();
    
    // الحصول على معلومات الكلية
    $query = "SELECT c.*, u.first_name AS dean_first_name, u.last_name AS dean_last_name
              FROM colleges c
              LEFT JOIN teachers t ON c.dean_id = t.id
              LEFT JOIN users u ON t.user_id = u.id
              WHERE c.id = :collegeId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['collegeId' => $collegeId]);
    
    $college = $stmt->fetch();
    
    if (!$college) {
        sendApiError('الكلية غير موجودة', 404);
    }
    
    // تنسيق البيانات
    $collegeInfo = [
        'id' => $college['id'],
        'name' => $college['name'],
        'name_en' => $college['name_en'],
        'code' => $college['code'],
        'description' => $college['description'],
        'description_en' => $college['description_en'],
        'dean' => $college['dean_id'] ? $college['dean_first_name'] . ' ' . $college['dean_last_name'] : null,
        'location' => $college['location'],
        'contact_email' => $college['contact_email'],
        'contact_phone' => $college['contact_phone'],
        'website' => $college['website'],
        'logo' => $college['logo'] ? BASE_URL . '/uploads/logos/' . $college['logo'] : null,
        'is_active' => (bool) $college['is_active']
    ];
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'college' => $collegeInfo
    ]);
}

/**
 * معالجة طلب الحصول على الأقسام
 */
function handleDepartments() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف الكلية
    $collegeId = isset($_GET['college_id']) ? $_GET['college_id'] : null;
    
    // إذا كان المستخدم إداري كلية ولم يتم تحديد معرف، استخدم معرف الكلية الحالي
    if ($user['type'] === 'college_admin' && empty($collegeId)) {
        $collegeId = $user['college_id'];
    }
    
    // التحقق من وجود معرف الكلية
    if (empty($collegeId)) {
        sendApiError('معرف الكلية مطلوب', 400);
    }
    
    // التحقق من الصلاحيات
    if ($user['type'] === 'college_admin' && $user['college_id'] != $collegeId) {
        sendApiError('غير مصرح لك بالوصول إلى أقسام هذه الكلية', 403);
    }
    
    $db = getDbConnection();
    
    // الحصول على الأقسام
    $query = "SELECT d.*, u.first_name AS head_first_name, u.last_name AS head_last_name
              FROM departments d
              LEFT JOIN teachers t ON d.head_id = t.id
              LEFT JOIN users u ON t.user_id = u.id
              WHERE d.college_id = :collegeId
              ORDER BY d.name";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['collegeId' => $collegeId]);
    
    $departments = [];
    
    while ($row = $stmt->fetch()) {
        $departments[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'name_en' => $row['name_en'],
            'code' => $row['code'],
            'description' => $row['description'],
            'description_en' => $row['description_en'],
            'head' => $row['head_id'] ? $row['head_first_name'] . ' ' . $row['head_last_name'] : null,
            'location' => $row['location'],
            'contact_email' => $row['contact_email'],
            'contact_phone' => $row['contact_phone'],
            'is_active' => (bool) $row['is_active']
        ];
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'departments' => $departments
    ]);
}

/**
 * معالجة طلب الحصول على البرامج الأكاديمية
 */
function handlePrograms() {
    global $user;
    
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معرف القسم
    $departmentId = isset($_GET['department_id']) ? $_GET['department_id'] : null;
    
    // التحقق من وجود معرف القسم
    if (empty($departmentId)) {
        sendApiError('معرف القسم مطلوب', 400);
    }
    
    $db = getDbConnection();
    
    // التحقق من الصلاحيات
    if ($user['type'] === 'college_admin') {
        $departmentQuery = "SELECT college_id FROM departments WHERE id = :departmentId";
        $departmentStmt = $db->prepare($departmentQuery);
        $departmentStmt->execute(['departmentId' => $departmentId]);
        $departmentRow = $departmentStmt->fetch();
        
        if (!$departmentRow) {
            sendApiError('القسم غير موجود', 404);
        }
        
        if ($departmentRow['college_id'] != $user['college_id']) {
            sendApiError('غير مصرح لك بالوصول إلى برامج هذا القسم', 403);
        }
    }
    
    // الحصول على البرامج الأكاديمية
    $query = "SELECT p.*, u.first_name AS coordinator_first_name, u.last_name AS coordinator_last_name
              FROM academic_programs p
              LEFT JOIN teachers t ON p.coordinator_id = t.id
              LEFT JOIN users u ON t.user_id = u.id
              WHERE p.department_id = :departmentId
              ORDER BY p.name";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['departmentId' => $departmentId]);
    
    $programs = [];
    
    while ($row = $stmt->fetch()) {
        $programs[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'name_en' => $row['name_en'],
            'code' => $row['code'],
            'description' => $row['description'],
            'description_en' => $row['description_en'],
            'degree' => $row['degree'],
            'credit_hours' => $row['credit_hours'],
            'duration_years' => $row['duration_years'],
            'coordinator' => $row['coordinator_id'] ? $row['coordinator_first_name'] . ' ' . $row['coordinator_last_name'] : null,
            'is_active' => (bool) $row['is_active']
        ];
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'programs' => $programs
    ]);
}

// باقي الدوال ستكون مشابهة للدوال السابقة، مع تعديل الاستعلامات والبيانات المرجعة
// لتناسب كل إجراء (تفاصيل البرنامج، الخطط الدراسية، المعلمين، الطلاب، إلخ)
