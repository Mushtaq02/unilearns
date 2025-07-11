<?php
/**
 * ملف الدوال المساعدة لنظام unilearns
 * يحتوي على دوال عامة تستخدم في مختلف أجزاء النظام
 */

// استيراد ملف الإعدادات
require_once 'config.php';

/**
 * دالة تنظيف وتأمين المدخلات
 * @param string $data البيانات المراد تنظيفها
 * @return string البيانات بعد التنظيف
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * دالة تشفير كلمة المرور
 * @param string $password كلمة المرور المراد تشفيرها
 * @return string كلمة المرور المشفرة
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * دالة التحقق من كلمة المرور
 * @param string $password كلمة المرور المدخلة
 * @param string $hash كلمة المرور المشفرة المخزنة
 * @return bool نتيجة التحقق
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * دالة إنشاء رمز JWT للمصادقة
 * @param array $payload البيانات المراد تضمينها في الرمز
 * @return string رمز JWT
 */
function generateJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = base64_encode($header);
    
    $payload['iat'] = time(); // وقت الإصدار
    $payload['exp'] = time() + JWT_EXPIRY; // وقت انتهاء الصلاحية
    $payload = json_encode($payload);
    $payload = base64_encode($payload);
    
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

/**
 * دالة التحقق من صحة رمز JWT
 * @param string $jwt رمز JWT المراد التحقق منه
 * @return array|bool البيانات المضمنة في الرمز أو false في حالة الفشل
 */
function verifyJWT($jwt) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false;
    }
    
    list($header, $payload, $signature) = $parts;
    
    $verifySignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $verifySignature = base64_encode($verifySignature);
    
    if ($signature !== $verifySignature) {
        return false;
    }
    
    $payload = json_decode(base64_decode($payload), true);
    
    // التحقق من انتهاء الصلاحية
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * دالة إنشاء رمز عشوائي
 * @param int $length طول الرمز
 * @return string الرمز العشوائي
 */
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * دالة تحويل التاريخ إلى الصيغة العربية
 * @param string $date التاريخ بصيغة Y-m-d
 * @return string التاريخ بالصيغة العربية
 */
function formatDateArabic($date) {
    if (empty($date)) return '';
    
    $months = [
        '01' => 'يناير',
        '02' => 'فبراير',
        '03' => 'مارس',
        '04' => 'أبريل',
        '05' => 'مايو',
        '06' => 'يونيو',
        '07' => 'يوليو',
        '08' => 'أغسطس',
        '09' => 'سبتمبر',
        '10' => 'أكتوبر',
        '11' => 'نوفمبر',
        '12' => 'ديسمبر'
    ];
    
    $dateParts = explode('-', $date);
    if (count($dateParts) !== 3) return $date;
    
    $year = $dateParts[0];
    $month = $dateParts[1];
    $day = intval($dateParts[2]);
    
    return $day . ' ' . $months[$month] . ' ' . $year;
}

/**
 * دالة التحقق من صلاحيات المستخدم
 * @param int $userId معرف المستخدم
 * @param string $permission الصلاحية المطلوبة
 * @return bool نتيجة التحقق
 */
function hasPermission($userId, $permission) {
    $db = get_db_connection();
    
    $query = "SELECT r.permissions FROM users u
              JOIN user_roles ur ON u.id = ur.user_id
              JOIN roles r ON ur.role_id = r.id
              WHERE u.id = :userId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['userId' => $userId]);
    
    while ($row = $stmt->fetch()) {
        $permissions = json_decode($row['permissions'], true);
        if (in_array($permission, $permissions)) {
            return true;
        }
    }
    
    return false;
}

/**
 * دالة إرسال رسالة بريد إلكتروني
 * @param string $to البريد الإلكتروني للمستلم
 * @param string $subject عنوان الرسالة
 * @param string $body محتوى الرسالة
 * @return bool نتيجة الإرسال
 */
function sendEmail($to, $subject, $body) {
    // في بيئة الإنتاج، يجب استخدام مكتبة مثل PHPMailer
    // هذه دالة بسيطة للتوضيح فقط
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_USERNAME . ">" . "\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * دالة تحويل النص إلى URL صديق
 * @param string $text النص المراد تحويله
 * @return string النص بعد التحويل
 */
function slugify($text) {
    // إزالة الأحرف الخاصة
    $text = preg_replace('~[^\p{L}\p{N}]+~u', '-', $text);
    // إزالة الشرطات المتكررة
    $text = preg_replace('~-+~', '-', $text);
    // إزالة الشرطات من البداية والنهاية
    $text = trim($text, '-');
    // تحويل إلى أحرف صغيرة
    $text = strtolower($text);
    
    return $text;
}

/**
 * دالة التحقق من نوع المستخدم
 * @param string $userType نوع المستخدم المطلوب
 * @return bool نتيجة التحقق
 */
function checkUserType($userType) {
    if (!isset($_SESSION['user_type'])) {
        return false;
    }
    
    return $_SESSION['user_type'] === $userType;
}

/**
 * دالة إعادة التوجيه
 * @param string $url المسار المراد التوجيه إليه
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * دالة عرض رسالة خطأ
 * @param string $message نص الرسالة
 */
function showError($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * دالة عرض رسالة نجاح
 * @param string $message نص الرسالة
 */
function showSuccess($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * دالة التحقق من وجود رسائل
 * @return bool نتيجة التحقق
 */
function hasMessages() {
    return isset($_SESSION['error_message']) || isset($_SESSION['success_message']);
}

/**
 * دالة عرض الرسائل
 * @return string HTML الرسائل
 */
function displayMessages() {
    $html = '';
    
    if (isset($_SESSION['error_message'])) {
        $html .= '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    
    if (isset($_SESSION['success_message'])) {
        $html .= '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    
    return $html;
}

/**
 * دالة تحميل ملف
 * @param array $file معلومات الملف ($_FILES['file'])
 * @param string $destination مسار الوجهة
 * @param array $allowedTypes أنواع الملفات المسموح بها
 * @param int $maxSize الحجم الأقصى بالبايت
 * @return string|bool اسم الملف بعد التحميل أو false في حالة الفشل
 */
function uploadFile($file, $destination, $allowedTypes = [], $maxSize = 5242880) {
    // التحقق من وجود أخطاء
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // التحقق من الحجم
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // التحقق من النوع
    $fileType = mime_content_type($file['tmp_name']);
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // إنشاء اسم فريد للملف
    $fileName = uniqid() . '_' . basename($file['name']);
    $filePath = $destination . '/' . $fileName;
    
    // نقل الملف
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $fileName;
    }
    
    return false;
}



// ==========================================================================
// == الدوال المضافة حديثاً بناءً على طلب المستخدم (نظام الطلاب) ==
// ==========================================================================

/**
 * دالة جلب معلومات الطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array|null معلومات الطالب أو null إذا لم يتم العثور عليه
 */
function get_student_info($db, $student_id) {
    // استعلام وهمي - يجب استبداله بالاستعلام الفعلي
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'student'");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    return $result;
}

/**
 * دالة جلب المقررات المسجلة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المقررات
 */
function get_student_courses($db, $student_id) {
    // استعلام وهمي - يجب استبداله بالاستعلام الفعلي
    $stmt = $db->prepare("SELECT c.* FROM courses c JOIN study_plan_courses spc ON c.id = spc.course_id WHERE spc.id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

/**
 * دالة جلب الواجبات القادمة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات القادمة
 */
function get_upcoming_assignments($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT a.* FROM assignments a JOIN study_plan_courses sc ON a.section_id = sc.course_id WHERE sc.id = ? AND a.due_date >= CURDATE() ORDER BY a.due_date ASC LIMIT 5");
    $stmt->execute([$student_id]);
    $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

/**
 * دالة جلب الاختبارات القادمة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الاختبارات القادمة
 */
function get_upcoming_exams($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT e.* FROM exams e JOIN study_plan_courses sc ON e.course_id = sc.course_id WHERE sc.student_id = ? AND e.exam_date >= CURDATE() ORDER BY e.exam_date ASC LIMIT 5");
    $stmt->execute([$student_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

/**
 * دالة جلب الإشعارات الأخيرة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الإشعارات
 */
function get_recent_notifications($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$student_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

/**
 * دالة جلب إحصائيات الطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array إحصائيات الطالب
 */
function get_student_stats($db, $student_id) {
    // دوال وهمية لحساب الإحصائيات
    $courses_count_stmt = $db->prepare("SELECT COUNT(*) FROM course_registrations WHERE student_id = ? AND status = 'registered'");
    $courses_count_stmt->execute([$student_id]);
    $courses_count = $courses_count_stmt->fetchColumn();

    $assignments_pending_stmt = $db->prepare("SELECT COUNT(a.id) 
                                             FROM assignments a 
                                             JOIN course_sections cs ON a.section_id = cs.id 
                                             JOIN course_registrations cr ON cs.id = cr.section_id 
                                             LEFT JOIN assignment_submissions sub ON a.id = sub.assignment_id AND sub.student_id = cr.student_id 
                                             WHERE cr.student_id = ? AND a.due_date >= CURDATE() AND sub.id IS NULL");
    $assignments_pending_stmt->execute([$student_id]);
    $assignments_pending = $assignments_pending_stmt->fetchColumn();

    $gpa_stmt = $db->prepare("SELECT gpa FROM students WHERE user_id = ?");
    $gpa_stmt->execute([$student_id]);
    $gpa = $gpa_stmt->fetchColumn();

    return [
        'courses_count' => $courses_count,
        'assignments_pending' => $assignments_pending,
        'gpa' => $gpa ?: 'N/A'
    ];
}

/**
 * دالة جلب الواجبات المعلقة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات المعلقة
 */
function get_pending_assignments($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT a.* FROM assignments a JOIN student_courses sc ON a.course_id = sc.course_id LEFT JOIN assignment_submissions sub ON a.id = sub.assignment_id AND sub.student_id = ? WHERE sc.student_id = ? AND a.due_date >= CURDATE() AND sub.id IS NULL ORDER BY a.due_date ASC");
    $stmt->bind_param("ii", $student_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب الواجبات المسلمة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات المسلمة
 */
function get_submitted_assignments($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT a.*, sub.submission_date, sub.grade FROM assignments a JOIN assignment_submissions sub ON a.id = sub.assignment_id WHERE sub.student_id = ? AND sub.grade IS NULL ORDER BY sub.submission_date DESC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب الواجبات المقيمة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات المقيمة
 */
function get_graded_assignments($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT a.*, sub.submission_date, sub.grade FROM assignments a JOIN assignment_submissions sub ON a.id = sub.assignment_id WHERE sub.student_id = ? AND sub.grade IS NOT NULL ORDER BY sub.submission_date DESC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب المقررات المتاحة للطالب (للتسجيل)
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المقررات المتاحة
 */
function get_available_courses($db, $student_id) {
    // استعلام وهمي - يجب تحسينه ليشمل متطلبات التسجيل ومستوى الطالب
    $stmt = $db->prepare("SELECT c.* FROM courses c WHERE c.id NOT IN (SELECT course_id FROM student_courses WHERE student_id = ?)");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب المنتديات التي يشارك فيها الطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المنتديات
 */
function get_student_forums($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT DISTINCT f.* FROM forums f JOIN student_courses sc ON f.course_id = sc.course_id WHERE sc.student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب مواضيع منتدى معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $forum_id معرف المنتدى
 * @return array قائمة المواضيع
 */
function get_forum_topics($db, $forum_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT ft.*, u.name as author_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id WHERE ft.forum_id = ? ORDER BY ft.last_reply_at DESC, ft.created_at DESC");
    $stmt->bind_param("i", $forum_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب معلومات منتدى معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $forum_id معرف المنتدى
 * @return array|null معلومات المنتدى
 */
function get_forum_info($db, $forum_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT f.*, c.name as course_name FROM forums f JOIN courses c ON f.course_id = c.id WHERE f.id = ?");
    $stmt->bind_param("i", $forum_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * دالة جلب ردود موضوع معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return array قائمة الردود
 */
function get_topic_posts($db, $topic_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT fp.*, u.name as author_name, u.profile_image FROM forum_posts fp JOIN users u ON fp.user_id = u.id WHERE fp.topic_id = ? ORDER BY fp.created_at ASC");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب معلومات موضوع معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return array|null معلومات الموضوع
 */
function get_topic_info($db, $topic_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT ft.*, u.name as author_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id WHERE ft.id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * دالة تحديث عدد مشاهدات الموضوع
 * @param object $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return bool نتيجة التحديث
 */
function update_topic_views($db, $topic_id) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    return $stmt->execute();
}

/**
 * دالة جلب درجات الطالب للفصل الحالي
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الدرجات
 */
function get_student_current_semester_grades($db, $student_id) {
    // استعلام وهمي - يفترض وجود جدول للفصول الدراسية
    $current_semester = get_current_semester($db);
    if (!$current_semester) return [];
    $stmt = $db->prepare("SELECT c.name as course_name, g.grade, g.points FROM grades g JOIN courses c ON g.course_id = c.id WHERE g.student_id = ? AND g.semester_id = ?");
    $stmt->bind_param("ii", $student_id, $current_semester['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب درجات الطالب لجميع الفصول
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الدرجات مجمعة حسب الفصل
 */
function get_student_all_semesters_grades($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT s.name as semester_name, s.year, c.name as course_name, g.grade, g.points FROM grades g JOIN courses c ON g.course_id = c.id JOIN semesters s ON g.semester_id = s.id WHERE g.student_id = ? ORDER BY s.year DESC, s.name DESC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[$row['semester_name'] . ' ' . $row['year']][] = $row;
    }
    return $grades;
}

/**
 * دالة حساب المعدل التراكمي للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array|null المعدل التراكمي وعدد الساعات
 */
function get_student_gpa($db, $student_id) {
    // استعلام وهمي لحساب المعدل التراكمي
    $stmt = $db->prepare("SELECT SUM(g.points * c.credits) as total_points, SUM(c.credits) as total_credits FROM grades g JOIN courses c ON g.course_id = c.id WHERE g.student_id = ? AND g.points IS NOT NULL");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && $result['total_credits'] > 0) {
        return [
            'cumulative_gpa' => round($result['total_points'] / $result['total_credits'], 2),
            'total_credits' => $result['total_credits']
        ];
    }
    return null;
}

/**
 * دالة حساب المعدل الفصلي للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param int $semester_id معرف الفصل الدراسي (اختياري، الافتراضي هو الحالي)
 * @return array|null المعدل الفصلي وعدد الساعات
 */
function get_student_semester_gpa($db, $student_id, $semester_id = null) {
    // استعلام وهمي
    if ($semester_id === null) {
        $current_semester = get_current_semester($db);
        if (!$current_semester) return null;
        $semester_id = $current_semester['id'];
    }
    $stmt = $db->prepare("SELECT SUM(g.points * c.credits) as total_points, SUM(c.credits) as total_credits FROM grades g JOIN courses c ON g.course_id = c.id WHERE g.student_id = ? AND g.semester_id = ? AND g.points IS NOT NULL");
    $stmt->bind_param("ii", $student_id, $semester_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && $result['total_credits'] > 0) {
        return [
            'semester_gpa' => round($result['total_points'] / $result['total_credits'], 2),
            'semester_credits' => $result['total_credits']
        ];
    }
    return null;
}

/**
 * دالة جلب محادثات الطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المحادثات
 */
function get_student_conversations($db, $student_id) {
    // استعلام وهمي - يجب تحسينه ليشمل معلومات الطرف الآخر وآخر رسالة
    $stmt = $db->prepare("SELECT c.*, u.name as other_user_name, u.profile_image as other_user_image, lm.message as last_message, lm.created_at as last_message_time FROM conversations c JOIN conversation_participants cp ON c.id = cp.conversation_id JOIN users u ON cp.user_id = u.id LEFT JOIN (SELECT conversation_id, message, created_at FROM messages ORDER BY created_at DESC) lm ON c.id = lm.conversation_id WHERE c.id IN (SELECT conversation_id FROM conversation_participants WHERE user_id = ?) AND cp.user_id != ? GROUP BY c.id ORDER BY lm.created_at DESC");
    $stmt->bind_param("ii", $student_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب رسائل محادثة معينة
 * @param object $db اتصال قاعدة البيانات
 * @param int $conversation_id معرف المحادثة
 * @param int $student_id معرف الطالب (للتحقق من الصلاحية)
 * @return array قائمة الرسائل
 */
function get_conversation_messages($db, $conversation_id, $student_id) {
    // التحقق من أن الطالب مشارك في المحادثة
    $check_stmt = $db->prepare("SELECT COUNT(*) as count FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $conversation_id, $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();
    if ($check_result['count'] == 0) {
        return []; // الطالب ليس جزءًا من هذه المحادثة
    }

    // استعلام وهمي لجلب الرسائل
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.conversation_id = ? ORDER BY m.created_at ASC");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب معلومات محادثة معينة
 * @param object $db اتصال قاعدة البيانات
 * @param int $conversation_id معرف المحادثة
 * @return array|null معلومات المحادثة
 */
function get_conversation_info($db, $conversation_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM conversations WHERE id = ?");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * دالة تحديث حالة الرسائل إلى مقروءة
 * @param object $db اتصال قاعدة البيانات
 * @param int $conversation_id معرف المحادثة
 * @param int $student_id معرف الطالب
 * @return bool نتيجة التحديث
 */
function update_messages_status($db, $conversation_id, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND recipient_id = ? AND is_read = 0");
    $stmt->bind_param("ii", $conversation_id, $student_id);
    return $stmt->execute();
}

/**
 * دالة جلب الإشعارات غير المقروءة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الإشعارات
 */
function get_unread_notifications($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب الإشعارات المقروءة للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الإشعارات
 */
function get_read_notifications($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 1 ORDER BY created_at DESC LIMIT 20"); // Limit for performance
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة تحديث الملف الشخصي للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param string $name الاسم
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param string $address العنوان
 * @param string $bio نبذة شخصية
 * @return bool نتيجة التحديث
 */
function update_student_profile($db, $student_id, $name, $email, $phone, $address, $bio) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, bio = ? WHERE id = ? AND user_type = 'student'");
    $stmt->bind_param("sssssi", $name, $email, $phone, $address, $bio, $student_id);
    return $stmt->execute();
}

/**
 * دالة التحقق من كلمة مرور الطالب الحالية
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param string $current_password كلمة المرور الحالية
 * @return bool نتيجة التحقق
 */
function verify_student_password($db, $student_id, $current_password) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ? AND user_type = 'student'");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        return password_verify($current_password, $result['password']);
    }
    return false;
}

/**
 * دالة تحديث كلمة مرور الطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param string $new_password كلمة المرور الجديدة
 * @return bool نتيجة التحديث
 */
function update_student_password($db, $student_id, $new_password) {
    // استعلام وهمي
    $hashed_password = hashPassword($new_password);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ? AND user_type = 'student'");
    $stmt->bind_param("si", $hashed_password, $student_id);
    return $stmt->execute();
}

/**
 * دالة تحديث صورة الملف الشخصي للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param string $file_path مسار الصورة الجديد
 * @return bool نتيجة التحديث
 */
function update_student_profile_image($db, $student_id, $file_path) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ? AND user_type = 'student'");
    $stmt->bind_param("si", $file_path, $student_id);
    return $stmt->execute();
}

/**
 * دالة جلب الجدول الدراسي للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array الجدول الدراسي
 */
function get_student_schedule($db, $student_id) {
    // استعلام وهمي - يجب تحسينه ليشمل معلومات المدرس والموقع
    $stmt = $db->prepare("SELECT sch.*, c.name as course_name, t.name as teacher_name FROM schedule sch JOIN courses c ON sch.course_id = c.id JOIN teachers t ON sch.teacher_id = t.id JOIN student_courses sc ON sch.course_id = sc.course_id WHERE sc.student_id = ? ORDER BY sch.day_of_week, sch.start_time");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = [];
    while ($row = $result->fetch_assoc()) {
        $schedule[$row['day_of_week']][] = $row;
    }
    return $schedule;
}

/**
 * دالة جلب الفصل الدراسي الحالي
 * @param object $db اتصال قاعدة البيانات
 * @return array|null معلومات الفصل الحالي
 */
function get_current_semester($db) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM semesters WHERE start_date <= CURDATE() AND end_date >= CURDATE() LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * دالة جلب التواريخ الهامة للفصل الدراسي
 * @param object $db اتصال قاعدة البيانات
 * @param int $semester_id معرف الفصل الدراسي
 * @return array قائمة التواريخ الهامة
 */
function get_important_dates($db, $semester_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM important_dates WHERE semester_id = ? ORDER BY date ASC");
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة تحديث إعدادات الإشعارات للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param bool $email_notifications
 * @param bool $sms_notifications
 * @param bool $browser_notifications
 * @return bool نتيجة التحديث
 */
function update_student_notification_settings($db, $student_id, $email_notifications, $sms_notifications, $browser_notifications) {
    // استعلام وهمي - يفترض وجود جدول لإعدادات المستخدمين
    $stmt = $db->prepare("UPDATE user_settings SET email_notifications = ?, sms_notifications = ?, browser_notifications = ? WHERE user_id = ?");
    $stmt->bind_param("iiii", $email_notifications, $sms_notifications, $browser_notifications, $student_id);
    // قد تحتاج إلى INSERT إذا لم يكن السجل موجودًا
    return $stmt->execute();
}

/**
 * دالة تحديث إعدادات الخصوصية للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param string $profile_visibility
 * @param bool $show_email
 * @param bool $show_phone
 * @return bool نتيجة التحديث
 */
function update_student_privacy_settings($db, $student_id, $profile_visibility, $show_email, $show_phone) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE user_settings SET profile_visibility = ?, show_email = ?, show_phone = ? WHERE user_id = ?");
    $stmt->bind_param("siii", $profile_visibility, $show_email, $show_phone, $student_id);
    return $stmt->execute();
}

/**
 * دالة تحديث إعدادات الأمان للطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param bool $two_factor_auth
 * @param bool $login_notifications
 * @return bool نتيجة التحديث
 */
function update_student_security_settings($db, $student_id, $two_factor_auth, $login_notifications) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE user_settings SET two_factor_auth = ?, login_notifications = ? WHERE user_id = ?");
    $stmt->bind_param("iii", $two_factor_auth, $login_notifications, $student_id);
    return $stmt->execute();
}

/**
 * دالة جلب إعدادات الطالب
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array|null إعدادات الطالب
 */
function get_student_settings($db, $student_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// ملاحظة: دالة $db->close() مذكورة في الملف النصي ولكن لا يجب تعريفها كدالة منفصلة هنا،
// بل يجب استدعاؤها في نهاية كل سكربت PHP عند الانتهاء من استخدام اتصال قاعدة البيانات.





// ==========================================================================
// == الدوال المضافة حديثاً بناءً على طلب المستخدم (نظام المعلمين) ==
// ==========================================================================

/**
 * دالة جلب معلومات المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array|null معلومات المعلم أو null إذا لم يتم العثور عليه
 */
function get_teacher_info($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT u.*, t.title as teacher_title, t.specialization, t.office_hours, t.office_location FROM users u LEFT JOIN teachers t ON u.id = t.user_id WHERE u.id = ? AND u.user_type = 'teacher'");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * دالة جلب المقررات التي يدرسها المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة المقررات
 */
function get_teacher_courses($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT c.* FROM courses c WHERE c.teacher_id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب إحصائيات المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array إحصائيات المعلم
 */
function get_teacher_stats($db, $teacher_id) {
    // استعلامات وهمية لحساب الإحصائيات
    $courses_count = $db->query("SELECT COUNT(*) as count FROM courses WHERE teacher_id = $teacher_id")->fetch_assoc()["count"];
    $students_count = $db->query("SELECT COUNT(DISTINCT sc.student_id) as count FROM student_courses sc JOIN courses c ON sc.course_id = c.id WHERE c.teacher_id = $teacher_id")->fetch_assoc()["count"];
    $assignments_count = $db->query("SELECT COUNT(*) as count FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.teacher_id = $teacher_id")->fetch_assoc()["count"];
    return [
        'courses_count' => $courses_count,
        'students_count' => $students_count,
        'assignments_count' => $assignments_count
    ];
}

/**
 * دالة جلب المهام القادمة للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة المهام القادمة (واجبات، اختبارات)
 */
function get_teacher_upcoming_tasks($db, $teacher_id) {
    // استعلام وهمي يجمع الواجبات والاختبارات القادمة
    $assignments = $db->query("SELECT id, title, due_date as date, 'assignment' as type FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.teacher_id = $teacher_id AND due_date >= CURDATE() ORDER BY due_date ASC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
    $exams = $db->query("SELECT id, title, exam_date as date, 'exam' as type FROM exams e JOIN courses c ON e.course_id = c.id WHERE c.teacher_id = $teacher_id AND exam_date >= CURDATE() ORDER BY exam_date ASC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
    $tasks = array_merge($assignments, $exams);
    // فرز حسب التاريخ
    usort($tasks, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    return array_slice($tasks, 0, 5);
}

/**
 * دالة جلب إشعارات المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param int|null $limit عدد الإشعارات (اختياري)
 * @return array قائمة الإشعارات
 */
function get_teacher_notifications($db, $teacher_id, $limit = null) {
    // استعلام وهمي
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $teacher_id, $limit);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب واجبات المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة الواجبات
 */
function get_teacher_assignments($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT a.*, c.name as course_name FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.teacher_id = ? ORDER BY a.due_date DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب اختبارات المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة الاختبارات
 */
function get_teacher_exams($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT e.*, c.name as course_name FROM exams e JOIN courses c ON e.course_id = c.id WHERE c.teacher_id = ? ORDER BY e.exam_date DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة إضافة اختبار جديد
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param int $course_id معرف المقرر
 * @param string $title عنوان الاختبار
 * @param string $description وصف الاختبار
 * @param string $exam_date تاريخ الاختبار
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param int $total_marks الدرجة الكلية
 * @return bool|int معرف الاختبار المضاف أو false عند الفشل
 */
function add_exam($db, $teacher_id, $course_id, $title, $description, $exam_date, $start_time, $end_time, $total_marks) {
    // التحقق من أن المعلم يدرس هذا المقرر
    $check_stmt = $db->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    $check_stmt->bind_param("ii", $course_id, $teacher_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        return false; // المعلم لا يملك صلاحية لهذا المقرر
    }

    // استعلام وهمي للإضافة
    $stmt = $db->prepare("INSERT INTO exams (course_id, title, description, exam_date, start_time, end_time, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $course_id, $title, $description, $exam_date, $start_time, $end_time, $total_marks);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

/**
 * دالة حذف اختبار
 * @param object $db اتصال قاعدة البيانات
 * @param int $exam_id معرف الاختبار
 * @param int $teacher_id معرف المعلم (للتحقق من الصلاحية)
 * @return bool نتيجة الحذف
 */
function delete_exam($db, $exam_id, $teacher_id) {
    // استعلام وهمي للحذف مع التحقق من الملكية
    $stmt = $db->prepare("DELETE e FROM exams e JOIN courses c ON e.course_id = c.id WHERE e.id = ? AND c.teacher_id = ?");
    $stmt->bind_param("ii", $exam_id, $teacher_id);
    return $stmt->execute();
}

/**
 * دالة تعديل اختبار
 * @param object $db اتصال قاعدة البيانات
 * @param int $exam_id معرف الاختبار
 * @param int $teacher_id معرف المعلم
 * @param int $course_id معرف المقرر
 * @param string $title عنوان الاختبار
 * @param string $description وصف الاختبار
 * @param string $exam_date تاريخ الاختبار
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param int $total_marks الدرجة الكلية
 * @return bool نتيجة التعديل
 */
function edit_exam($db, $exam_id, $teacher_id, $course_id, $title, $description, $exam_date, $start_time, $end_time, $total_marks) {
    // التحقق من أن المعلم يدرس هذا المقرر
    $check_stmt = $db->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    $check_stmt->bind_param("ii", $course_id, $teacher_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        return false; // المعلم لا يملك صلاحية لهذا المقرر
    }

    // استعلام وهمي للتعديل
    $stmt = $db->prepare("UPDATE exams SET course_id = ?, title = ?, description = ?, exam_date = ?, start_time = ?, end_time = ?, total_marks = ? WHERE id = ?");
    $stmt->bind_param("isssssii", $course_id, $title, $description, $exam_date, $start_time, $end_time, $total_marks, $exam_id);
    return $stmt->execute();
}

/**
 * دالة إنشاء منتدى لمقرر
 * @param object $db اتصال قاعدة البيانات
 * @param int $course_id معرف المقرر
 * @param int $teacher_id معرف المعلم (للتحقق)
 * @param string $title عنوان المنتدى
 * @param string $description وصف المنتدى
 * @return bool|int معرف المنتدى أو false
 */
function create_forum($db, $course_id, $teacher_id, $title, $description) {
    // التحقق من أن المعلم يدرس هذا المقرر
    $check_stmt = $db->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    $check_stmt->bind_param("ii", $course_id, $teacher_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        return false;
    }
    // استعلام وهمي
    $stmt = $db->prepare("INSERT INTO forums (course_id, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $course_id, $title, $description);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

/**
 * دالة إنشاء موضوع في منتدى
 * @param object $db اتصال قاعدة البيانات
 * @param int $forum_id معرف المنتدى
 * @param int $teacher_id معرف المعلم (الكاتب)
 * @param string $title عنوان الموضوع
 * @param string $content محتوى الموضوع
 * @return bool|int معرف الموضوع أو false
 */
function create_topic($db, $forum_id, $teacher_id, $title, $content) {
    // استعلام وهمي
    $stmt = $db->prepare("INSERT INTO forum_topics (forum_id, user_id, title, created_at, last_reply_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("iis", $forum_id, $teacher_id, $title);
    if ($stmt->execute()) {
        $topic_id = $stmt->insert_id;
        // إضافة المشاركة الأولى (محتوى الموضوع)
        add_reply($db, $topic_id, $teacher_id, $content);
        return $topic_id;
    }
    return false;
}

/**
 * دالة إضافة رد في موضوع
 * @param object $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @param int $user_id معرف المستخدم (الكاتب)
 * @param string $content محتوى الرد
 * @return bool|int معرف الرد أو false
 */
function add_reply($db, $topic_id, $user_id, $content) {
    // استعلام وهمي
    $stmt = $db->prepare("INSERT INTO forum_posts (topic_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $topic_id, $user_id, $content);
    if ($stmt->execute()) {
        // تحديث وقت آخر رد في الموضوع
        $update_stmt = $db->prepare("UPDATE forum_topics SET last_reply_at = NOW(), replies = replies + 1 WHERE id = ?");
        $update_stmt->bind_param("i", $topic_id);
        $update_stmt->execute();
        return $stmt->insert_id;
    }
    return false;
}

/**
 * دالة جلب منتديات مقرر معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $course_id معرف المقرر
 * @return array قائمة المنتديات
 */
function get_course_forums($db, $course_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT * FROM forums WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب ردود موضوع معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return array قائمة الردود
 */
function get_topic_replies($db, $topic_id) {
    // استعلام وهمي - نفس دالة get_topic_posts
    return get_topic_posts($db, $topic_id);
}

/**
 * دالة جلب طلاب مقرر معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $course_id معرف المقرر
 * @return array قائمة الطلاب
 */
function get_course_students($db, $course_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT u.* FROM users u JOIN student_courses sc ON u.id = sc.student_id WHERE sc.course_id = ? AND u.user_type = 'student'");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب درجات مقرر معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $course_id معرف المقرر
 * @return array قائمة الدرجات
 */
function get_course_grades($db, $course_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT g.*, u.name as student_name FROM grades g JOIN users u ON g.student_id = u.id WHERE g.course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب رسائل البريد الوارد للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة الرسائل
 */
function get_inbox_messages($db, $teacher_id) {
    // استعلام وهمي - يفترض وجود نظام رسائل أكثر تفصيلاً
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.recipient_id = ? AND m.status != 'archived' ORDER BY m.created_at DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب رسائل البريد الصادر للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة الرسائل
 */
function get_sent_messages($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT m.*, u.name as recipient_name FROM messages m JOIN users u ON m.recipient_id = u.id WHERE m.sender_id = ? ORDER BY m.created_at DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب رسائل الأرشيف للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة الرسائل
 */
function get_archived_messages($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.recipient_id = ? AND m.status = 'archived' ORDER BY m.created_at DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب المستلمين المحتملين للرسائل (طلاب المعلم)
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة المستلمين
 */
function get_message_recipients($db, $teacher_id) {
    // استعلام وهمي لجلب طلاب المعلم
    $stmt = $db->prepare("SELECT DISTINCT u.id, u.name FROM users u JOIN student_courses sc ON u.id = sc.student_id JOIN courses c ON sc.course_id = c.id WHERE c.teacher_id = ? AND u.user_type = 'student' ORDER BY u.name ASC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة تعليم كل الإشعارات كمقروءة للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return bool نتيجة التحديث
 */
function mark_all_notifications_as_read($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $teacher_id);
    return $stmt->execute();
}

/**
 * دالة تعليم إشعار معين كمقروء للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $notification_id معرف الإشعار
 * @param int $teacher_id معرف المعلم
 * @return bool نتيجة التحديث
 */
function mark_notification_as_read($db, $notification_id, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $teacher_id);
    return $stmt->execute();
}

/**
 * دالة حذف إشعار معين للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $notification_id معرف الإشعار
 * @param int $teacher_id معرف المعلم
 * @return bool نتيجة الحذف
 */
function delete_notification($db, $notification_id, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $teacher_id);
    return $stmt->execute();
}

/**
 * دالة جلب منشورات المعلم (مثال: أبحاث)
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة المنشورات
 */
function get_teacher_publications($db, $teacher_id) {
    // استعلام وهمي - يفترض وجود جدول للمنشورات
    $stmt = $db->prepare("SELECT * FROM publications WHERE user_id = ? ORDER BY publication_date DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة تحديث كلمة مرور المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param string $new_password كلمة المرور الجديدة
 * @return bool نتيجة التحديث
 */
function update_password($db, $teacher_id, $new_password) {
    // استعلام وهمي - نفس دالة الطالب
    return update_student_password($db, $teacher_id, $new_password);
}

/**
 * دالة التحقق من كلمة مرور المعلم الحالية
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param string $current_password كلمة المرور الحالية
 * @return bool نتيجة التحقق
 */
// تم تعريفها سابقا verifyPassword($password, $hash)
// لكن يمكن إنشاء دالة مخصصة للمعلم إذا لزم الأمر
// function verify_teacher_password($db, $teacher_id, $current_password) { ... }

/**
 * دالة تحديث صورة الملف الشخصي للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param string $upload_path مسار الصورة الجديد
 * @return bool نتيجة التحديث
 */
function update_teacher_profile_image($db, $teacher_id, $upload_path) {
    // استعلام وهمي - نفس دالة الطالب
    return update_student_profile_image($db, $teacher_id, $upload_path);
}

/**
 * دالة تحديث الملف الشخصي للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param string $name الاسم
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param string $title اللقب العلمي
 * @param string $bio نبذة شخصية
 * @param string $office_hours ساعات العمل المكتبية
 * @param string $office_location موقع المكتب
 * @return bool نتيجة التحديث
 */
function update_teacher_profile($db, $teacher_id, $name, $email, $phone, $title, $bio, $office_hours, $office_location) {
    // استعلام وهمي لتحديث جدول users
    $stmt_user = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ? WHERE id = ? AND user_type = 'teacher'");
    $stmt_user->bind_param("ssssi", $name, $email, $phone, $bio, $teacher_id);
    $user_updated = $stmt_user->execute();

    // استعلام وهمي لتحديث جدول teachers (أو إضافته إذا لم يكن موجودًا)
    $stmt_teacher = $db->prepare("INSERT INTO teachers (user_id, title, office_hours, office_location) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), office_hours = VALUES(office_hours), office_location = VALUES(office_location)");
    $stmt_teacher->bind_param("isss", $teacher_id, $title, $office_hours, $office_location);
    $teacher_updated = $stmt_teacher->execute();

    return $user_updated && $teacher_updated;
}

/**
 * دالة جلب الجدول الدراسي للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array الجدول الدراسي
 */
function get_teacher_schedule($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT sch.*, c.name as course_name, cr.name as classroom_name FROM schedule sch JOIN courses c ON sch.course_id = c.id LEFT JOIN classrooms cr ON sch.classroom_id = cr.id WHERE sch.teacher_id = ? ORDER BY sch.day_of_week, sch.start_time");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = [];
    while ($row = $result->fetch_assoc()) {
        $schedule[$row['day_of_week']][] = $row;
    }
    return $schedule;
}

/**
 * دالة جلب القاعات الدراسية المتاحة
 * @param object $db اتصال قاعدة البيانات
 * @return array قائمة القاعات
 */
function get_classrooms($db) {
    // استعلام وهمي
    $result = $db->query("SELECT * FROM classrooms ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة إضافة موعد في جدول المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param int $course_id معرف المقرر
 * @param int $day يوم الأسبوع (0 للأحد - 6 للسبت)
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param int|null $classroom_id معرف القاعة (اختياري)
 * @param string $type نوع الموعد (lecture, lab, office_hours)
 * @return bool|int معرف الموعد أو false
 */
function add_schedule($db, $teacher_id, $course_id, $day, $start_time, $end_time, $classroom_id, $type) {
    // استعلام وهمي
    $stmt = $db->prepare("INSERT INTO schedule (teacher_id, course_id, day_of_week, start_time, end_time, classroom_id, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissis", $teacher_id, $course_id, $day, $start_time, $end_time, $classroom_id, $type);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

/**
 * دالة حذف موعد من جدول المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $schedule_id معرف الموعد
 * @param int $teacher_id معرف المعلم (للتحقق)
 * @return bool نتيجة الحذف
 */
function delete_schedule($db, $schedule_id, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("DELETE FROM schedule WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $schedule_id, $teacher_id);
    return $stmt->execute();
}

/**
 * دالة تعديل موعد في جدول المعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $schedule_id معرف الموعد
 * @param int $teacher_id معرف المعلم
 * @param int $course_id معرف المقرر
 * @param int $day يوم الأسبوع
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param int|null $classroom_id معرف القاعة
 * @param string $type نوع الموعد
 * @return bool نتيجة التعديل
 */
function edit_schedule($db, $schedule_id, $teacher_id, $course_id, $day, $start_time, $end_time, $classroom_id, $type) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE schedule SET course_id = ?, day_of_week = ?, start_time = ?, end_time = ?, classroom_id = ?, type = ? WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("iisssiii", $course_id, $day, $start_time, $end_time, $classroom_id, $type, $schedule_id, $teacher_id);
    return $stmt->execute();
}

/**
 * دالة تحديث لغة المستخدم
 * @param object $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم
 * @param string $new_lang اللغة الجديدة ('ar' أو 'en')
 * @return bool نتيجة التحديث
 */
function update_user_language($db, $user_id, $new_lang) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE user_settings SET language = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_lang, $user_id);
    return $stmt->execute();
}

/**
 * دالة تحديث مظهر المستخدم
 * @param object $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم
 * @param string $new_theme المظهر الجديد ('light' أو 'dark')
 * @return bool نتيجة التحديث
 */
function update_user_theme($db, $user_id, $new_theme) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE user_settings SET theme = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_theme, $user_id);
    return $stmt->execute();
}

/**
 * دالة تحديث إعدادات الخصوصية للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param bool $show_email
 * @param bool $show_phone
 * @param bool $show_profile
 * @return bool نتيجة التحديث
 */
function update_privacy_settings($db, $teacher_id, $show_email, $show_phone, $show_profile) {
    // استعلام وهمي - يفترض وجود حقل show_profile في user_settings
    $stmt = $db->prepare("UPDATE user_settings SET show_email = ?, show_phone = ?, show_profile = ? WHERE user_id = ?");
    $stmt->bind_param("iiii", $show_email, $show_phone, $show_profile, $teacher_id);
    return $stmt->execute();
}

/**
 * دالة جلب طلاب المعلم (لصفحة إدارة الطلاب)
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return array قائمة الطلاب
 */
function get_teacher_students($db, $teacher_id) {
    // استعلام وهمي - يجمع الطلاب من جميع مقررات المعلم
    $stmt = $db->prepare("SELECT DISTINCT u.*, c.name as course_name FROM users u JOIN student_courses sc ON u.id = sc.student_id JOIN courses c ON sc.course_id = c.id WHERE c.teacher_id = ? AND u.user_type = 'student' ORDER BY u.name ASC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}





// ==========================================================================
// == الدوال المضافة حديثاً بناءً على طلب المستخدم (نظام الكليات والمساعدات) ==
// ==========================================================================

/**
 * دالة جلب برامج الكلية
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة البرامج
 */
function get_college_programs($db, $college_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT p.*, d.name as department_name FROM programs p JOIN departments d ON p.department_id = d.id WHERE d.college_id = ? ORDER BY p.name ASC");
    $stmt->bind_param("i", $college_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب معلمي الكلية
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة المعلمين
 */
function get_college_teachers($db, $college_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT u.*, t.title as teacher_title, d.name as department_name FROM users u JOIN teachers t ON u.id = t.user_id JOIN departments d ON t.department_id = d.id WHERE d.college_id = ? AND u.user_type = 'teacher' ORDER BY u.name ASC");
    $stmt->bind_param("i", $college_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة إضافة موعد محاضرة لجدول الكلية
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية (للتحقق)
 * @param int $course_id معرف المقرر
 * @param int $teacher_id معرف المعلم
 * @param int $day_of_week يوم الأسبوع
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param string $location الموقع/القاعة
 * @return bool|int معرف الموعد أو false
 */
function add_class_schedule($db, $college_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $location) {
    // يمكن إضافة تحقق من أن المقرر والمعلم يتبعان للكلية
    // استعلام وهمي
    $stmt = $db->prepare("INSERT INTO schedule (course_id, teacher_id, day_of_week, start_time, end_time, location, type) VALUES (?, ?, ?, ?, ?, ?, 'class')");
    $stmt->bind_param("iiisss", $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $location);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

/**
 * دالة إضافة موعد اختبار لجدول الكلية
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية (للتحقق)
 * @param int $course_id معرف المقرر
 * @param int $teacher_id معرف المعلم
 * @param string $exam_date تاريخ الاختبار
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param string $location الموقع/القاعة
 * @param int $exam_duration مدة الاختبار (بالدقائق)
 * @return bool|int معرف الموعد أو false
 */
function add_exam_schedule($db, $college_id, $course_id, $teacher_id, $exam_date, $start_time, $end_time, $location, $exam_duration) {
    // يمكن إضافة تحقق من أن المقرر والمعلم يتبعان للكلية
    // استعلام وهمي - يفترض وجود جدول منفصل لمواعيد الاختبارات أو حقول إضافية في جدول schedule
    $stmt = $db->prepare("INSERT INTO exam_schedule (course_id, teacher_id, exam_date, start_time, end_time, location, duration_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssi", $course_id, $teacher_id, $exam_date, $start_time, $end_time, $location, $exam_duration);
     if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

/**
 * دالة تعديل موعد محاضرة
 * @param object $db اتصال قاعدة البيانات
 * @param int $event_id معرف الموعد
 * @param int $course_id معرف المقرر
 * @param int $teacher_id معرف المعلم
 * @param int $day_of_week يوم الأسبوع
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param string $location الموقع/القاعة
 * @return bool نتيجة التعديل
 */
function update_class_schedule($db, $event_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $location) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE schedule SET course_id = ?, teacher_id = ?, day_of_week = ?, start_time = ?, end_time = ?, location = ? WHERE id = ? AND type = 'class'");
    $stmt->bind_param("iiisssi", $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $location, $event_id);
    return $stmt->execute();
}

/**
 * دالة تعديل موعد اختبار
 * @param object $db اتصال قاعدة البيانات
 * @param int $event_id معرف الموعد
 * @param int $course_id معرف المقرر
 * @param int $teacher_id معرف المعلم
 * @param string $exam_date تاريخ الاختبار
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param string $location الموقع/القاعة
 * @param int $exam_duration مدة الاختبار
 * @return bool نتيجة التعديل
 */
function update_exam_schedule($db, $event_id, $course_id, $teacher_id, $exam_date, $start_time, $end_time, $location, $exam_duration) {
    // استعلام وهمي
    $stmt = $db->prepare("UPDATE exam_schedule SET course_id = ?, teacher_id = ?, exam_date = ?, start_time = ?, end_time = ?, location = ?, duration_minutes = ? WHERE id = ?");
    $stmt->bind_param("iissssii", $course_id, $teacher_id, $exam_date, $start_time, $end_time, $location, $exam_duration, $event_id);
    return $stmt->execute();
}

/**
 * دالة حذف موعد محاضرة
 * @param object $db اتصال قاعدة البيانات
 * @param int $event_id معرف الموعد
 * @return bool نتيجة الحذف
 */
function delete_class_schedule($db, $event_id) {
    // استعلام وهمي
    $stmt = $db->prepare("DELETE FROM schedule WHERE id = ? AND type = 'class'");
    $stmt->bind_param("i", $event_id);
    return $stmt->execute();
}

/**
 * دالة حذف موعد اختبار
 * @param object $db اتصال قاعدة البيانات
 * @param int $event_id معرف الموعد
 * @return bool نتيجة الحذف
 */
function delete_exam_schedule($db, $event_id) {
    // استعلام وهمي
    $stmt = $db->prepare("DELETE FROM exam_schedule WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    return $stmt->execute();
}

/**
 * دالة جلب معلمي قسم معين
 * @param object $db اتصال قاعدة البيانات
 * @param int $department_id معرف القسم
 * @return array قائمة المعلمين
 */
function get_department_teachers($db, $department_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT u.*, t.title as teacher_title FROM users u JOIN teachers t ON u.id = t.user_id WHERE t.department_id = ? AND u.user_type = 'teacher' ORDER BY u.name ASC");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب جدول المحاضرات بناءً على الفلاتر
 * @param object $db اتصال قاعدة البيانات
 * @param array $filters فلاتر البحث (مثل program_id, teacher_id, day_of_week)
 * @return array قائمة مواعيد المحاضرات
 */
function get_class_schedule($db, $filters = []) {
    // بناء الاستعلام بناءً على الفلاتر
    $sql = "SELECT sch.*, c.name as course_name, t.name as teacher_name, cr.name as classroom_name FROM schedule sch JOIN courses c ON sch.course_id = c.id JOIN users t ON sch.teacher_id = t.id LEFT JOIN classrooms cr ON sch.classroom_id = cr.id WHERE sch.type = 'class'";
    $params = [];
    $types = "";

    if (!empty($filters['program_id'])) {
        // يتطلب ربط إضافي مع جدول programs أو courses
        // $sql .= " AND c.program_id = ?";
        // $params[] = $filters['program_id'];
        // $types .= "i";
    }
    if (!empty($filters['teacher_id'])) {
        $sql .= " AND sch.teacher_id = ?";
        $params[] = $filters['teacher_id'];
        $types .= "i";
    }
     if (!empty($filters['day_of_week']) && is_numeric($filters['day_of_week'])) {
        $sql .= " AND sch.day_of_week = ?";
        $params[] = $filters['day_of_week'];
        $types .= "i";
    }
    // ... إضافة فلاتر أخرى حسب الحاجة

    $sql .= " ORDER BY sch.day_of_week, sch.start_time";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب جدول الاختبارات بناءً على الفلاتر
 * @param object $db اتصال قاعدة البيانات
 * @param array $filters فلاتر البحث
 * @return array قائمة مواعيد الاختبارات
 */
function get_exam_schedule($db, $filters = []) {
    // بناء الاستعلام بناءً على الفلاتر
    $sql = "SELECT es.*, c.name as course_name, t.name as teacher_name FROM exam_schedule es JOIN courses c ON es.course_id = c.id JOIN users t ON es.teacher_id = t.id WHERE 1=1";
    $params = [];
    $types = "";

     if (!empty($filters['program_id'])) {
        // $sql .= " AND c.program_id = ?";
        // $params[] = $filters['program_id'];
        // $types .= "i";
    }
     if (!empty($filters['teacher_id'])) {
        $sql .= " AND es.teacher_id = ?";
        $params[] = $filters['teacher_id'];
        $types .= "i";
    }
    // ... إضافة فلاتر أخرى

    $sql .= " ORDER BY es.exam_date, es.start_time";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة بناء سلسلة استعلام URL
 * @param array $exclude المفاتيح المراد استثناؤها
 * @return string سلسلة الاستعلام
 */
function build_query_string($exclude = []) {
    $params = $_GET;
    foreach ($exclude as $key) {
        unset($params[$key]);
    }
    return http_build_query($params);
}

/**
 * دالة التحقق من وجود بريد إلكتروني
 * @param object $db اتصال قاعدة البيانات
 * @param string $email البريد الإلكتروني
 * @param int|null $user_id معرف المستخدم الحالي (لتجاهله عند التعديل)
 * @return bool هل البريد موجود؟
 */
function check_email_exists($db, $email, $user_id = null) {
    $sql = "SELECT id FROM users WHERE email = ?";
    $params = [$email];
    $types = "s";
    if ($user_id !== null) {
        $sql .= " AND id != ?";
        $params[] = $user_id;
        $types .= "i";
    }
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * دالة التحقق من وجود رقم جامعي
 * @param object $db اتصال قاعدة البيانات
 * @param string $student_id_number الرقم الجامعي
 * @param int|null $student_id معرف الطالب الحالي (لتجاهله عند التعديل)
 * @return bool هل الرقم موجود؟
 */
function check_student_id_exists($db, $student_id_number, $student_id = null) {
    // يفترض وجود حقل student_id_number في جدول students أو users
    $sql = "SELECT user_id FROM students WHERE student_id_number = ?";
    $params = [$student_id_number];
    $types = "s";
    if ($student_id !== null) {
        $sql .= " AND user_id != ?";
        $params[] = $student_id;
        $types .= "i";
    }
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * دالة إضافة طالب جديد (من قبل الكلية)
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @param int $program_id معرف البرنامج
 * @param string $name الاسم
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param string $student_id_number الرقم الجامعي
 * @param int $level المستوى
 * @param string $enrollment_date تاريخ الالتحاق
 * @param string $address العنوان
 * @param string $date_of_birth تاريخ الميلاد
 * @param string $gender الجنس
 * @param string $password كلمة المرور
 * @return bool|int معرف المستخدم المضاف أو false
 */
function add_student($db, $college_id, $program_id, $name, $email, $phone, $student_id_number, $level, $enrollment_date, $address, $date_of_birth, $gender, $password) {
    // التحقق من عدم وجود البريد أو الرقم الجامعي
    if (check_email_exists($db, $email) || check_student_id_exists($db, $student_id_number)) {
        return false; // البريد أو الرقم الجامعي مستخدم
    }

    $hashed_password = hashPassword($password);
    // إضافة المستخدم الأساسي
    $stmt_user = $db->prepare("INSERT INTO users (name, email, password, user_type, phone, address, date_of_birth, gender, created_at) VALUES (?, ?, ?, 'student', ?, ?, ?, ?, NOW())");
    $stmt_user->bind_param("sssssss", $name, $email, $hashed_password, $phone, $address, $date_of_birth, $gender);
    if ($stmt_user->execute()) {
        $user_id = $stmt_user->insert_id;
        // إضافة معلومات الطالب الإضافية
        $stmt_student = $db->prepare("INSERT INTO students (user_id, college_id, program_id, student_id_number, level, enrollment_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_student->bind_param("iiisis", $user_id, $college_id, $program_id, $student_id_number, $level, $enrollment_date);
        if ($stmt_student->execute()) {
            // إضافة دور الطالب
            // $role_id = get_role_id_by_name($db, 'Student'); // دالة افتراضية
            // add_user_role($db, $user_id, $role_id); // دالة افتراضية
            return $user_id;
        }
         // إذا فشلت إضافة معلومات الطالب، احذف المستخدم الأساسي (Rollback)
        $db->query("DELETE FROM users WHERE id = $user_id");
    }
    return false;
}

/**
 * دالة رفع صورة الملف الشخصي
 * @param int $user_id معرف المستخدم
 * @param string $user_type نوع المستخدم (student, teacher)
 * @param array $file معلومات الملف ($_FILES['profile_image'])
 * @return string|bool مسار الصورة أو false عند الفشل
 */
function upload_profile_image($user_id, $user_type, $file) {
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profile_images/' . $user_type . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            showError('نوع الصورة غير مسموح به.');
            return false;
        }
        if ($file['size'] > $maxSize) {
            showError('حجم الصورة كبير جداً.');
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = $user_id . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // حذف الصورة القديمة إذا وجدت
            // ... (تحتاج إلى جلب المسار القديم من قاعدة البيانات)
            return '/uploads/profile_images/' . $user_type . '/' . $newFileName; // المسار النسبي للتخزين في قاعدة البيانات
        }
    }
    return false;
}

/**
 * دالة تعديل بيانات الطالب (من قبل الكلية)
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param int $program_id معرف البرنامج
 * @param string $name الاسم
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param string $student_id_number الرقم الجامعي
 * @param int $level المستوى
 * @param string $enrollment_date تاريخ الالتحاق
 * @param string $address العنوان
 * @param string $date_of_birth تاريخ الميلاد
 * @param string $gender الجنس
 * @param string $status حالة الحساب (active, inactive, graduated)
 * @param string|null $password كلمة المرور الجديدة (اختياري)
 * @return bool نتيجة التعديل
 */
function update_student($db, $student_id, $program_id, $name, $email, $phone, $student_id_number, $level, $enrollment_date, $address, $date_of_birth, $gender, $status, $password = null) {
    // التحقق من عدم وجود البريد أو الرقم الجامعي لمستخدم آخر
    if (check_email_exists($db, $email, $student_id) || check_student_id_exists($db, $student_id_number, $student_id)) {
        return false;
    }

    // تحديث جدول users
    $sql_user = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, date_of_birth = ?, gender = ?, status = ?";
    $params_user = [$name, $email, $phone, $address, $date_of_birth, $gender, $status];
    $types_user = "sssssss";
    if ($password) {
        $sql_user .= ", password = ?";
        $params_user[] = hashPassword($password);
        $types_user .= "s";
    }
    $sql_user .= " WHERE id = ? AND user_type = 'student'";
    $params_user[] = $student_id;
    $types_user .= "i";

    $stmt_user = $db->prepare($sql_user);
    $stmt_user->bind_param($types_user, ...$params_user);
    $user_updated = $stmt_user->execute();

    // تحديث جدول students
    $stmt_student = $db->prepare("UPDATE students SET program_id = ?, student_id_number = ?, level = ?, enrollment_date = ? WHERE user_id = ?");
    $stmt_student->bind_param("isisi", $program_id, $student_id_number, $level, $enrollment_date, $student_id);
    $student_updated = $stmt_student->execute();

    return $user_updated && $student_updated;
}

/**
 * دالة التحقق من وجود سجلات مرتبطة بالطالب قبل الحذف
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return bool هل توجد سجلات مرتبطة؟
 */
function check_student_related_records($db, $student_id) {
    // استعلامات وهمية للتحقق من الجداول المرتبطة
    $tables = ['student_courses', 'grades', 'assignment_submissions', 'exam_submissions', 'attendance']; // أضف جداول أخرى حسب الحاجة
    foreach ($tables as $table) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
            return true; // توجد سجلات مرتبطة
        }
    }
    return false;
}

/**
 * دالة حذف طالب (من قبل الكلية)
 * @param object $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return bool نتيجة الحذف
 */
function delete_student($db, $student_id) {
    // يمكن إضافة تحقق إضافي هنا أو السماح بالحذف مع تحويل السجلات المرتبطة
    // if (check_student_related_records($db, $student_id)) {
    //     showError('لا يمكن حذف الطالب لوجود سجلات مرتبطة.');
    //     return false;
    // }

    // البدء بالمعاملة (Transaction) لضمان الحذف الكامل
    $db->begin_transaction();
    try {
        // حذف السجلات المرتبطة أولاً (مثال)
        $db->query("DELETE FROM student_courses WHERE student_id = $student_id");
        $db->query("DELETE FROM grades WHERE student_id = $student_id");
        $db->query("DELETE FROM assignment_submissions WHERE student_id = $student_id");
        // ... حذف من جداول أخرى

        // حذف من جدول students
        $stmt_student = $db->prepare("DELETE FROM students WHERE user_id = ?");
        $stmt_student->bind_param("i", $student_id);
        $stmt_student->execute();

        // حذف من جدول users
        $stmt_user = $db->prepare("DELETE FROM users WHERE id = ? AND user_type = 'student'");
        $stmt_user->bind_param("i", $student_id);
        $stmt_user->execute();

        // حذف الأدوار المرتبطة
        // $db->query("DELETE FROM user_roles WHERE user_id = $student_id");

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        showError('حدث خطأ أثناء حذف الطالب: ' . $e->getMessage());
        return false;
    }
}

/**
 * دالة جلب طلاب الكلية بناءً على الفلاتر
 * @param object $db اتصال قاعدة البيانات
 * @param array $filters فلاتر البحث (college_id, program_id, level, status, name)
 * @return array قائمة الطلاب
 */
function get_college_students($db, $filters = []) {
    $sql = "SELECT u.*, s.student_id_number, s.level, s.enrollment_date, p.name as program_name FROM users u JOIN students s ON u.id = s.user_id JOIN programs p ON s.program_id = p.id WHERE u.user_type = 'student'";
    $params = [];
    $types = "";

    if (!empty($filters['college_id'])) {
        $sql .= " AND s.college_id = ?";
        $params[] = $filters['college_id'];
        $types .= "i";
    }
    if (!empty($filters['program_id'])) {
        $sql .= " AND s.program_id = ?";
        $params[] = $filters['program_id'];
        $types .= "i";
    }
    if (!empty($filters['level'])) {
        $sql .= " AND s.level = ?";
        $params[] = $filters['level'];
        $types .= "i";
    }
    if (!empty($filters['status'])) {
        $sql .= " AND u.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    if (!empty($filters['name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
        $types .= "s";
    }

    $sql .= " ORDER BY u.name ASC";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة إضافة معلم جديد (من قبل الكلية)
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @param string $name الاسم
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param int $department_id معرف القسم
 * @param string $title اللقب العلمي
 * @param string $specialization التخصص
 * @param string $office_hours ساعات العمل
 * @param string $office_location موقع المكتب
 * @param string $bio نبذة
 * @param string $password كلمة المرور
 * @return bool|int معرف المستخدم المضاف أو false
 */
function add_teacher($db, $college_id, $name, $email, $phone, $department_id, $title, $specialization, $office_hours, $office_location, $bio, $password) {
    // التحقق من عدم وجود البريد
    if (check_email_exists($db, $email)) {
        return false;
    }

    $hashed_password = hashPassword($password);
    // إضافة المستخدم الأساسي
    $stmt_user = $db->prepare("INSERT INTO users (name, email, password, user_type, phone, bio, created_at) VALUES (?, ?, ?, 'teacher', ?, ?, NOW())");
    $stmt_user->bind_param("sssss", $name, $email, $hashed_password, $phone, $bio);
    if ($stmt_user->execute()) {
        $user_id = $stmt_user->insert_id;
        // إضافة معلومات المعلم الإضافية
        $stmt_teacher = $db->prepare("INSERT INTO teachers (user_id, department_id, title, specialization, office_hours, office_location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_teacher->bind_param("iissss", $user_id, $department_id, $title, $specialization, $office_hours, $office_location);
        if ($stmt_teacher->execute()) {
             // إضافة دور المعلم
            // $role_id = get_role_id_by_name($db, 'Teacher');
            // add_user_role($db, $user_id, $role_id);
            return $user_id;
        }
        // Rollback
        $db->query("DELETE FROM users WHERE id = $user_id");
    }
    return false;
}

/**
 * دالة تعديل بيانات المعلم (من قبل الكلية)
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @param string $name الاسم
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param int $department_id معرف القسم
 * @param string $title اللقب العلمي
 * @param string $specialization التخصص
 * @param string $office_hours ساعات العمل
 * @param string $office_location موقع المكتب
 * @param string $bio نبذة
 * @param string $status حالة الحساب
 * @param string|null $password كلمة المرور الجديدة (اختياري)
 * @return bool نتيجة التعديل
 */
function update_teacher($db, $teacher_id, $name, $email, $phone, $department_id, $title, $specialization, $office_hours, $office_location, $bio, $status, $password = null) {
     // التحقق من عدم وجود البريد لمستخدم آخر
    if (check_email_exists($db, $email, $teacher_id)) {
        return false;
    }

    // تحديث جدول users
    $sql_user = "UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, status = ?";
    $params_user = [$name, $email, $phone, $bio, $status];
    $types_user = "sssss";
    if ($password) {
        $sql_user .= ", password = ?";
        $params_user[] = hashPassword($password);
        $types_user .= "s";
    }
    $sql_user .= " WHERE id = ? AND user_type = 'teacher'";
    $params_user[] = $teacher_id;
    $types_user .= "i";

    $stmt_user = $db->prepare($sql_user);
    $stmt_user->bind_param($types_user, ...$params_user);
    $user_updated = $stmt_user->execute();

    // تحديث جدول teachers
    $stmt_teacher = $db->prepare("UPDATE teachers SET department_id = ?, title = ?, specialization = ?, office_hours = ?, office_location = ? WHERE user_id = ?");
    $stmt_teacher->bind_param("issssi", $department_id, $title, $specialization, $office_hours, $office_location, $teacher_id);
    $teacher_updated = $stmt_teacher->execute();

    return $user_updated && $teacher_updated;
}

/**
 * دالة جلب عدد المقررات المسندة للمعلم
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return int عدد المقررات
 */
function get_teacher_courses_count($db, $teacher_id) {
    // استعلام وهمي
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM courses WHERE teacher_id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

/**
 * دالة حذف معلم (من قبل الكلية)
 * @param object $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم
 * @return bool نتيجة الحذف
 */
function delete_teacher($db, $teacher_id) {
    // التحقق مما إذا كان المعلم مسنداً لمقررات حالية
    if (get_teacher_courses_count($db, $teacher_id) > 0) {
        showError('لا يمكن حذف المعلم لأنه مسند لمقررات دراسية. يرجى إعادة إسناد المقررات أولاً.');
        return false;
    }

    // البدء بالمعاملة
    $db->begin_transaction();
    try {
        // حذف من جدول teachers
        $stmt_teacher = $db->prepare("DELETE FROM teachers WHERE user_id = ?");
        $stmt_teacher->bind_param("i", $teacher_id);
        $stmt_teacher->execute();

        // حذف من جدول users
        $stmt_user = $db->prepare("DELETE FROM users WHERE id = ? AND user_type = 'teacher'");
        $stmt_user->bind_param("i", $teacher_id);
        $stmt_user->execute();

        // حذف الأدوار
        // $db->query("DELETE FROM user_roles WHERE user_id = $teacher_id");

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        showError('حدث خطأ أثناء حذف المعلم: ' . $e->getMessage());
        return false;
    }
}

/**
 * دالة جلب إحصائيات الكلية
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array إحصائيات الكلية
 */
function get_college_statistics($db, $college_id) {
    // استعلامات وهمية
    $departments_count = $db->query("SELECT COUNT(*) as count FROM departments WHERE college_id = $college_id")->fetch_assoc()['count'];
    $programs_count = $db->query("SELECT COUNT(*) as count FROM programs p JOIN departments d ON p.department_id = d.id WHERE d.college_id = $college_id")->fetch_assoc()['count'];
    $teachers_count = $db->query("SELECT COUNT(*) as count FROM teachers t JOIN departments d ON t.department_id = d.id WHERE d.college_id = $college_id")->fetch_assoc()['count'];
    $students_count = $db->query("SELECT COUNT(*) as count FROM students WHERE college_id = $college_id")->fetch_assoc()['count'];

    return [
        'departments_count' => $departments_count,
        'programs_count' => $programs_count,
        'teachers_count' => $teachers_count,
        'students_count' => $students_count,
    ];
}

/**
 * دالة جلب الأنشطة الأخيرة في الكلية
 * @param object $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @param int $limit عدد الأنشطة
 * @return array قائمة الأنشطة
 */
function get_college_recent_activities($db, $college_id, $limit = 10) {
    // استعلام وهمي - يفترض وجود جدول لتسجيل الأنشطة (logs)
    $stmt = $db->prepare("SELECT * FROM activity_log WHERE college_id = ? ORDER BY timestamp DESC LIMIT ?");
    $stmt->bind_param("ii", $college_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * دالة جلب أيقونة النشاط بناءً على نوعه
 * @param string $activity_type نوع النشاط
 * @return string كلاس الأيقونة (مثال: Font Awesome)
 */
function get_activity_icon($activity_type) {
    switch ($activity_type) {
        case 'add_student': return 'fas fa-user-plus';
        case 'add_teacher': return 'fas fa-chalkboard-teacher';
        case 'add_course': return 'fas fa-book';
        case 'update_program': return 'fas fa-edit';
        // ... أضف أيقونات أخرى
        default: return 'fas fa-info-circle';
    }
}

/**
 * دالة تنسيق الوقت المنقضي (مثال: منذ 5 دقائق)
 * @param string $timestamp الوقت بصيغة Y-m-d H:i:s
 * @return string الوقت المنقضي
 */
function format_time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds <= 60) return "الآن";
    else if ($minutes <= 60) return ($minutes == 1) ? "منذ دقيقة واحدة" : "منذ $minutes دقائق";
    else if ($hours <= 24) return ($hours == 1) ? "منذ ساعة واحدة" : "منذ $hours ساعات";
    else if ($days <= 7) return ($days == 1) ? "أمس" : "منذ $days أيام";
    else if ($weeks <= 4.3) return ($weeks == 1) ? "منذ أسبوع واحد" : "منذ $weeks أسابيع";
    else if ($months <= 12) return ($months == 1) ? "منذ شهر واحد" : "منذ $months أشهر";
    else return ($years == 1) ? "منذ سنة واحدة" : "منذ $years سنوات";
}


