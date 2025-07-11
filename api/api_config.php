<?php
/**
 * ملف إعدادات API لنظام UniverBoard
 * يحتوي على الإعدادات والدوال المشتركة لجميع واجهات برمجة التطبيقات
 */

// استيراد الملفات اللازمة
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// تعيين رأس الاستجابة
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// معالجة طلب OPTIONS (للتحقق من CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * دالة التحقق من مصادقة API
 * @return array معلومات المستخدم أو null في حالة الفشل
 */
function authenticateApi() {
    // الحصول على رأس Authorization
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // التحقق من وجود الرأس
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return null;
    }
    
    $token = $matches[1];
    $payload = verifyJWT($token);
    
    return $payload;
}

/**
 * دالة إرسال استجابة API
 * @param array $data البيانات المراد إرسالها
 * @param int $statusCode رمز الحالة HTTP
 */
function sendApiResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * دالة إرسال استجابة خطأ API
 * @param string $message رسالة الخطأ
 * @param int $statusCode رمز الحالة HTTP
 */
function sendApiError($message, $statusCode = 400) {
    sendApiResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * دالة الحصول على بيانات الطلب
 * @return array بيانات الطلب
 */
function getRequestData() {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendApiError('Invalid JSON data', 400);
        }
        
        return $data;
    }
    
    return $_POST;
}

/**
 * دالة التحقق من وجود الحقول المطلوبة
 * @param array $data البيانات
 * @param array $requiredFields الحقول المطلوبة
 * @return bool نتيجة التحقق
 */
function validateRequiredFields($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            sendApiError("الحقل '$field' مطلوب", 400);
            return false;
        }
    }
    
    return true;
}

/**
 * دالة التحقق من صلاحيات API
 * @param array $user معلومات المستخدم
 * @param array $allowedTypes أنواع المستخدمين المسموح لهم
 * @return bool نتيجة التحقق
 */
function checkApiPermission($user, $allowedTypes) {
    if (!$user) {
        sendApiError('غير مصرح لك بالوصول', 401);
        return false;
    }
    
    if (!in_array($user['type'], $allowedTypes)) {
        sendApiError('غير مصرح لك بالوصول', 403);
        return false;
    }
    
    return true;
}
