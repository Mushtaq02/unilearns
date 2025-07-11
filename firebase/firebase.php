<?php
/**
 * ملف تكامل Firebase لنظام UniverBoard
 * يحتوي على دوال التكامل مع خدمات Firebase المختلفة
 */

// استيراد ملف الإعدادات
require_once dirname(__DIR__) . '/includes/config.php';

/**
 * دالة تهيئة Firebase
 * @return array إعدادات Firebase
 */
function getFirebaseConfig() {
    return [
        'apiKey' => FIREBASE_API_KEY,
        'authDomain' => FIREBASE_AUTH_DOMAIN,
        'projectId' => FIREBASE_PROJECT_ID,
        'storageBucket' => FIREBASE_STORAGE_BUCKET,
        'messagingSenderId' => FIREBASE_MESSAGING_SENDER_ID,
        'appId' => FIREBASE_APP_ID
    ];
}

/**
 * دالة إرسال إشعار لمستخدم محدد
 * @param int $userId معرف المستخدم
 * @param string $title عنوان الإشعار
 * @param string $body نص الإشعار
 * @param array $data بيانات إضافية للإشعار
 * @return bool نتيجة الإرسال
 */
function sendNotificationToUser($userId, $title, $body, $data = []) {
    // في بيئة الإنتاج، يجب استخدام مكتبة Firebase Admin SDK
    // هذه دالة بسيطة للتوضيح فقط
    
    $db = get_db_connection();
    
    // إضافة الإشعار إلى قاعدة البيانات
    $query = "INSERT INTO notifications (user_id, title, body, data, created_at)
              VALUES (:userId, :title, :body, :data, NOW())";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        'userId' => $userId,
        'title' => $title,
        'body' => $body,
        'data' => json_encode($data)
    ]);
    
    return $result;
}

/**
 * دالة إرسال إشعار لمجموعة من المستخدمين
 * @param array $userIds معرفات المستخدمين
 * @param string $title عنوان الإشعار
 * @param string $body نص الإشعار
 * @param array $data بيانات إضافية للإشعار
 * @return bool نتيجة الإرسال
 */
function sendNotificationToUsers($userIds, $title, $body, $data = []) {
    $success = true;
    
    foreach ($userIds as $userId) {
        $result = sendNotificationToUser($userId, $title, $body, $data);
        if (!$result) {
            $success = false;
        }
    }
    
    return $success;
}

/**
 * دالة إرسال إشعار لجميع طلاب مقرر معين
 * @param int $sectionId معرف الشعبة
 * @param string $title عنوان الإشعار
 * @param string $body نص الإشعار
 * @param array $data بيانات إضافية للإشعار
 * @return bool نتيجة الإرسال
 */
function sendNotificationToSection($sectionId, $title, $body, $data = []) {
    $db = get_db_connection();
    
    // الحصول على معرفات المستخدمين للطلاب في الشعبة
    $query = "SELECT u.id
              FROM users u
              JOIN students s ON u.id = s.user_id
              JOIN course_registrations cr ON s.id = cr.student_id
              WHERE cr.section_id = :sectionId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['sectionId' => $sectionId]);
    
    $userIds = [];
    
    while ($row = $stmt->fetch()) {
        $userIds[] = $row['id'];
    }
    
    if (empty($userIds)) {
        return false;
    }
    
    return sendNotificationToUsers($userIds, $title, $body, $data);
}

/**
 * دالة إرسال إشعار لجميع المعلمين في قسم معين
 * @param int $departmentId معرف القسم
 * @param string $title عنوان الإشعار
 * @param string $body نص الإشعار
 * @param array $data بيانات إضافية للإشعار
 * @return bool نتيجة الإرسال
 */
function sendNotificationToDepartmentTeachers($departmentId, $title, $body, $data = []) {
    $db = get_db_connection();
    
    // الحصول على معرفات المستخدمين للمعلمين في القسم
    $query = "SELECT u.id
              FROM users u
              JOIN teachers t ON u.id = t.user_id
              WHERE t.department_id = :departmentId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['departmentId' => $departmentId]);
    
    $userIds = [];
    
    while ($row = $stmt->fetch()) {
        $userIds[] = $row['id'];
    }
    
    if (empty($userIds)) {
        return false;
    }
    
    return sendNotificationToUsers($userIds, $title, $body, $data);
}

/**
 * دالة الحصول على إشعارات المستخدم
 * @param int $userId معرف المستخدم
 * @param int $limit عدد الإشعارات المطلوبة
 * @param int $offset بداية الإشعارات
 * @return array الإشعارات
 */
function getUserNotifications($userId, $limit = 20, $offset = 0) {
    $db = get_db_connection();
    
    $query = "SELECT id, title, body, data, is_read, created_at
              FROM notifications
              WHERE user_id = :userId
              ORDER BY created_at DESC
              LIMIT :offset, :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':userId', $userId);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $notifications = [];
    
    while ($row = $stmt->fetch()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'body' => $row['body'],
            'data' => json_decode($row['data'], true),
            'is_read' => (bool) $row['is_read'],
            'created_at' => $row['created_at']
        ];
    }
    
    return $notifications;
}

/**
 * دالة تحديث حالة قراءة الإشعار
 * @param int $notificationId معرف الإشعار
 * @param int $userId معرف المستخدم
 * @param bool $isRead حالة القراءة
 * @return bool نتيجة التحديث
 */
function markNotificationAsRead($notificationId, $userId, $isRead = true) {
    $db = get_db_connection();
    
    $query = "UPDATE notifications
              SET is_read = :isRead
              WHERE id = :notificationId AND user_id = :userId";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        'isRead' => $isRead ? 1 : 0,
        'notificationId' => $notificationId,
        'userId' => $userId
    ]);
    
    return $result;
}

/**
 * دالة تحديث حالة قراءة جميع إشعارات المستخدم
 * @param int $userId معرف المستخدم
 * @param bool $isRead حالة القراءة
 * @return bool نتيجة التحديث
 */
function markAllNotificationsAsRead($userId, $isRead = true) {
    $db = get_db_connection();
    
    $query = "UPDATE notifications
              SET is_read = :isRead
              WHERE user_id = :userId";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        'isRead' => $isRead ? 1 : 0,
        'userId' => $userId
    ]);
    
    return $result;
}

/**
 * دالة حذف إشعار
 * @param int $notificationId معرف الإشعار
 * @param int $userId معرف المستخدم
 * @return bool نتيجة الحذف
 */
function deleteNotification($notificationId, $userId) {
    $db = get_db_connection();
    
    $query = "DELETE FROM notifications
              WHERE id = :notificationId AND user_id = :userId";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        'notificationId' => $notificationId,
        'userId' => $userId
    ]);
    
    return $result;
}

/**
 * دالة حذف جميع إشعارات المستخدم
 * @param int $userId معرف المستخدم
 * @return bool نتيجة الحذف
 */
function deleteAllNotifications($userId) {
    $db = get_db_connection();
    
    $query = "DELETE FROM notifications
              WHERE user_id = :userId";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute(['userId' => $userId]);
    
    return $result;
}
