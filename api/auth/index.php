<?php
/**
 * واجهة برمجة التطبيقات للمصادقة في نظام UniverBoard
 * تتيح تسجيل الدخول والخروج وإدارة الجلسات
 */

// استيراد ملف إعدادات API
require_once '../api_config.php';

// تحديد الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
        
    case 'logout':
        handleLogout();
        break;
        
    case 'reset_password_request':
        handleResetPasswordRequest();
        break;
        
    case 'reset_password':
        handleResetPassword();
        break;
        
    case 'check_auth':
        handleCheckAuth();
        break;
        
    default:
        sendApiError('إجراء غير صالح', 400);
        break;
}

/**
 * معالجة طلب تسجيل الدخول
 */
function handleLogin() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على بيانات الطلب
    $data = getRequestData();
    
    // التحقق من وجود الحقول المطلوبة
    validateRequiredFields($data, ['email', 'password', 'user_type']);
    
    // تنظيف البيانات
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    $userType = sanitizeInput($data['user_type']);
    
    // التحقق من نوع المستخدم
    $validUserTypes = ['student', 'teacher', 'college_admin', 'system_admin'];
    if (!in_array($userType, $validUserTypes)) {
        sendApiError('نوع المستخدم غير صالح', 400);
    }
    
    // محاولة تسجيل الدخول
    $result = login($email, $password, $userType);
    
    if (!$result['success']) {
        sendApiError($result['message'], 401);
    }
    
    // إنشاء رمز API
    $token = generateApiToken($result['user']['id']);
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'message' => $result['message'],
        'user' => $result['user'],
        'token' => $token
    ]);
}

/**
 * معالجة طلب تسجيل الخروج
 */
function handleLogout() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // التحقق من المصادقة
    $user = authenticateApi();
    
    if (!$user) {
        sendApiError('غير مصرح لك بالوصول', 401);
    }
    
    // تسجيل الخروج
    logout();
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}

/**
 * معالجة طلب إعادة تعيين كلمة المرور
 */
function handleResetPasswordRequest() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على بيانات الطلب
    $data = getRequestData();
    
    // التحقق من وجود الحقول المطلوبة
    validateRequiredFields($data, ['email']);
    
    // تنظيف البيانات
    $email = sanitizeInput($data['email']);
    
    // إرسال رمز إعادة التعيين
    $result = sendPasswordResetToken($email);
    
    if (!$result['success']) {
        sendApiError($result['message'], 400);
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'message' => $result['message']
    ]);
}

/**
 * معالجة طلب إعادة تعيين كلمة المرور
 */
function handleResetPassword() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // الحصول على بيانات الطلب
    $data = getRequestData();
    
    // التحقق من وجود الحقول المطلوبة
    validateRequiredFields($data, ['token', 'password', 'confirm_password']);
    
    // تنظيف البيانات
    $token = sanitizeInput($data['token']);
    $password = $data['password'];
    $confirmPassword = $data['confirm_password'];
    
    // التحقق من تطابق كلمتي المرور
    if ($password !== $confirmPassword) {
        sendApiError('كلمتا المرور غير متطابقتين', 400);
    }
    
    // التحقق من قوة كلمة المرور
    if (strlen($password) < 8) {
        sendApiError('يجب أن تتكون كلمة المرور من 8 أحرف على الأقل', 400);
    }
    
    // إعادة تعيين كلمة المرور
    $result = resetPassword($token, $password);
    
    if (!$result['success']) {
        sendApiError($result['message'], 400);
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'message' => $result['message']
    ]);
}

/**
 * معالجة طلب التحقق من المصادقة
 */
function handleCheckAuth() {
    // التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendApiError('طريقة الطلب غير مدعومة', 405);
    }
    
    // التحقق من المصادقة
    $user = authenticateApi();
    
    if (!$user) {
        sendApiError('غير مصرح لك بالوصول', 401);
    }
    
    // إرسال الاستجابة
    sendApiResponse([
        'success' => true,
        'user' => $user
    ]);
}
