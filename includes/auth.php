<?php
/**
 * ملف المصادقة المبسط لنظام UniLearns
 */

// استيراد الملفات اللازمة
require_once 'config.php';

/**
 * دالة تسجيل الدخول
 */
function login($email, $password, $userType) {
    global $pdo;
    
    try {
        // التحقق من وجود المستخدم
        $query = "SELECT * FROM users WHERE email = :email AND user_type = :userType AND is_active = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'email' => $email,
            'userType' => $userType
        ]);
        
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ];
        }
        
        // التحقق من كلمة المرور
        if ( $password!= $user['password']) {
            return [
                'success' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ];
        }
        
        // تحديث آخر تسجيل دخول
        $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute(['id' => $user['id']]);
        
        // إنشاء الجلسة
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['college_id'] = $user['college_id'];
        $_SESSION['department_id'] = $user['department_id'];
        $_SESSION['is_logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        return [
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'redirect' => getDashboardUrl($user['user_type'])
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'حدث خطأ أثناء تسجيل الدخول: ' . $e->getMessage()
        ];
    }
}

/**
 * دالة تسجيل الخروج
 */
function logout() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'تم تسجيل الخروج بنجاح'
    ];
}

/**
 * دالة التحقق من تسجيل الدخول
 */
function isLoggedIn() {
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * دالة الحصول على رابط لوحة التحكم حسب نوع المستخدم
 */
function getDashboardUrl($userType) {
    switch ($userType) {
        case 'student':
            return SITE_URL . '/student/dashboard.php';
        case 'teacher':
            return SITE_URL . '/teacher/dashboard.php';
        case 'college_admin':
            return SITE_URL . '/college/dashboard.php';
        case 'system_admin':
            return SITE_URL . '/admin/dashboard.php';
        default:
            return SITE_URL . '/login.php';
    }
}

/**
 * دالة التحقق من الصلاحيات
 */
function checkPermission($requiredType) {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
    
    $userType = $_SESSION['user_type'];
    $hierarchy = ['student' => 1, 'teacher' => 2, 'college_admin' => 3, 'system_admin' => 4];
    
    if ($hierarchy[$userType] < $hierarchy[$requiredType]) {
        header('Location: ' . SITE_URL . '/unauthorized.php');
        exit();
    }
}

/**
 * دالة الحصول على معلومات المستخدم الحالي
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    
    try {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}
?>

