<?php
/**
 * واجهة برمجة التطبيقات للمشرفين في نظام UniverBoard
 * تتيح الوصول إلى بيانات النظام وإدارة المستخدمين والكليات والأقسام
 */

// استيراد ملف إعدادات API
require_once '../api_config.php';

// تحديد الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : '';

// التحقق من المصادقة
$user = authenticateApi();

// التحقق من صلاحيات الوصول
if (!checkApiPermission($user, ['system_admin'])) {
    exit;
}

switch ($action) {
    case 'dashboard_stats':
        handleDashboardStats();
        break;
        
    case 'users':
        handleUsers();
        break;
        
    case 'user_details':
        handleUserDetails();
        break;
        
    case 'create_user':
        handleCreateUser();
        break;
        
    case 'update_user':
        handleUpdateUser();
        break;
        
    case 'delete_user':
        handleDeleteUser();
        break;
        
    case 'colleges':
        handleColleges();
        break;
        
    case 'college_details':
        handleCollegeDetails();
        break;
        
    case 'create_college':
        handleCreateCollege();
        break;
        
    case 'update_college':
        handleUpdateCollege();
        break;
        
    case 'departments':
        handleDepartments();
        break;
        
    case 'department_details':
        handleDepartmentDetails();
        break;
        
    case 'create_department':
        handleCreateDepartment();
        break;
        
    case 'update_department':
        handleUpdateDepartment();
        break;
        
    case 'academic_terms':
        handleAcademicTerms();
        break;
        
    case 'create_academic_term':
        handleCreateAcademicTerm();
        break;
        
    case 'update_academic_term':
        handleUpdateAcademicTerm();
        break;
        
    case 'roles':
        handleRoles();
        break;
        
    case 'role_details':
        handleRoleDetails();
        break;
        
    case 'create_role':
        handleCreateRole();
        break;
        
    case 'update_role':
        handleUpdateRole();
        break;
        
    case 'system_logs':
        handleSystemLogs();
        break;
        
    default:
        sendApiError('إجراء غير صالح', 400);
        break;
}

/**
 * معالجة طلب الحصول على إحصائيات لوحة التحكم
 */
function handleDashboardStats() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    $db = getDbConnection();
    
    // إحصائيات المستخدمين
    $usersQuery = "SELECT 
                    COUNT(*) AS total_users,
                    SUM(CASE WHEN user_type = 'student' THEN 1 ELSE 0 END) AS students_count,
                    SUM(CASE WHEN user_type = 'teacher' THEN 1 ELSE 0 END) AS teachers_count,
                    SUM(CASE WHEN user_type = 'college_admin' THEN 1 ELSE 0 END) AS college_admins_count,
                    SUM(CASE WHEN user_type = 'system_admin' THEN 1 ELSE 0 END) AS system_admins_count
                  FROM users";
    $usersStmt = $db->query($usersQuery);
    $usersStats = $usersStmt->fetch();
    
    // إحصائيات الكليات والأقسام
    $collegesQuery = "SELECT COUNT(*) AS total_colleges FROM colleges";
    $collegesStmt = $db->query($collegesQuery);
    $collegesCount = $collegesStmt->fetchColumn();
    
    $departmentsQuery = "SELECT COUNT(*) AS total_departments FROM departments";
    $departmentsStmt = $db->query($departmentsQuery);
    $departmentsCount = $departmentsStmt->fetchColumn();
    
    // إحصائيات المقررات
    $coursesQuery = "SELECT COUNT(*) AS total_courses FROM courses";
    $coursesStmt = $db->query($coursesQuery);
    $coursesCount = $coursesStmt->fetchColumn();
    
    // إحصائيات الشعب الدراسية
    $sectionsQuery = "SELECT COUNT(*) AS total_sections FROM course_sections";
    $sectionsStmt = $db->query($sectionsQuery);
    $sectionsCount = $sectionsStmt->fetchColumn();
    
    // إحصائيات التسجيل
    $registrationsQuery = "SELECT COUNT(*) AS total_registrations FROM course_registrations";
    $registrationsStmt = $db->query($registrationsQuery);
    $registrationsCount = $registrationsStmt->fetchColumn();
    
    // تنسيق البيانات
    $stats = [
        'users' => [
            'total' => (int) $usersStats['total_users'],
            'students' => (int) $usersStats['students_count'],
            'teachers' => (int) $usersStats['teachers_count'],
            'college_admins' => (int) $usersStats['college_admins_count'],
            'system_admins' => (int) $usersStats['system_admins_count']
        ],
        'academic' => [
            'colleges' => (int) $collegesCount,
            'departments' => (int) $departmentsCount,
            'courses' => (int) $coursesCount,
            'sections' => (int) $sectionsCount,
            'registrations' => (int) $registrationsCount
        ]
    ];
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'stats' => $stats
    ]);
}

/**
 * معالجة طلب الحصول على المستخدمين
 */
function handleUsers() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على معايير البحث
    $userType = isset($_GET['user_type']) ? $_GET['user_type'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
    
    // التحقق من صحة المعايير
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 20;
    
    $db = getDbConnection();
    
    // بناء الاستعلام
    $query = "SELECT id, username, email, user_type, first_name, last_name, profile_picture, is_active, created_at
              FROM users
              WHERE 1=1";
    
    $params = [];
    
    if ($userType) {
        $query .= " AND user_type = :userType";
        $params['userType'] = $userType;
    }
    
    if ($search) {
        $query .= " AND (username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    // حساب إجمالي عدد النتائج
    $countQuery = str_replace("SELECT id, username, email, user_type, first_name, last_name, profile_picture, is_active, created_at", "SELECT COUNT(*)", $query);
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    
    // إضافة الترتيب والحد
    $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
    $params['offset'] = ($page - 1) * $limit;
    $params['limit'] = $limit;
    
    $stmt = $db->prepare($query);
    
    // تعيين نوع المعلمات
    foreach ($params as $key => $value) {
        if ($key === 'offset' || $key === 'limit') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(":$key", $value);
        }
    }
    
    $stmt->execute();
    
    $users = [];
    
    while ($row = $stmt->fetch()) {
        $users[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'user_type' => $row['user_type'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'profile_picture' => $row['profile_picture'] ? BASE_URL . '/uploads/profile_pictures/' . $row['profile_picture'] : null,
            'is_active' => (bool) $row['is_active'],
            'created_at' => $row['created_at']
        ];
    }
    
    // حساب معلومات الصفحات
    $totalPages = ceil($totalCount / $limit);
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'users' => $users,
        'pagination' => [
            'total' => $totalCount,
            'per_page' => $limit,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]
    ]);
}

// باقي الدوال ستكون مشابهة للدوال السابقة، مع تعديل الاستعلامات والبيانات المرجعة
// لتناسب كل إجراء (تفاصيل المستخدم، إنشاء مستخدم، تحديث مستخدم، إلخ)
