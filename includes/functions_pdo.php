<?php
/**
 * ملف الدوال المساعدة لنظام UniLearns
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
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $userId معرف المستخدم
 * @param string $permission الصلاحية المطلوبة
 * @return bool نتيجة التحقق
 */
function hasPermission($db, $userId, $permission) {
    $query = "SELECT r.permissions FROM users u
              JOIN user_roles ur ON u.id = ur.user_id
              JOIN roles r ON ur.role_id = r.id
              WHERE u.id = :userId";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['userId' => $userId]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $permissions = json_decode($row['permissions'], true);
        if (is_array($permissions) && (isset($permissions['all']) && $permissions['all'] === true || in_array($permission, $permissions))) {
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
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array|null معلومات الطالب أو null إذا لم يتم العثور عليه
 */
function get_student_info($db, $student_id) {
    $stmt = $db->prepare("SELECT u.*, s.student_id as student_reg_id, s.academic_level, s.gpa, s.status as student_status, 
                           c.name as college_name, d.name as department_name, p.name as program_name 
                           FROM users u 
                           JOIN students s ON u.id = s.user_id 
                           JOIN colleges c ON s.college_id = c.id 
                           JOIN departments d ON s.department_id = d.id 
                           JOIN academic_programs p ON s.program_id = p.id 
                           WHERE u.id = ? AND u.user_type = 'student'");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب المقررات المسجلة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المقررات
 */
function get_student_courses($db, $student_id) {
    $stmt = $db->prepare("SELECT c.*, cs.section_number, t.first_name as teacher_first_name, t.last_name as teacher_last_name, cr.grade 
                           FROM courses c 
                           JOIN course_sections cs ON c.id = cs.course_id 
                           JOIN course_registrations cr ON cs.id = cr.section_id 
                           JOIN teachers tch ON cs.teacher_id = tch.id 
                           JOIN users t ON tch.user_id = t.id 
                           WHERE cr.student_id = ? AND cr.status = 'registered'");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الواجبات القادمة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات القادمة
 */
function get_upcoming_assignments($db, $student_id) {
    $stmt = $db->prepare("SELECT a.*, c.name as course_name 
                           FROM assignments a 
                           JOIN course_sections cs ON a.section_id = cs.id 
                           JOIN course_registrations cr ON cs.id = cr.section_id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE cr.student_id = ? AND a.due_date >= CURDATE() 
                           ORDER BY a.due_date ASC LIMIT 5");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الاختبارات القادمة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الاختبارات القادمة
 */
function get_upcoming_exams($db, $student_id) {
    $stmt = $db->prepare("SELECT e.*, c.name as course_name 
                           FROM exams e 
                           JOIN course_sections cs ON e.section_id = cs.id 
                           JOIN course_registrations cr ON cs.id = cr.section_id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE cr.student_id = ? AND e.exam_date >= CURDATE() 
                           ORDER BY e.exam_date ASC LIMIT 5");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الإشعارات الأخيرة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الإشعارات
 */
function get_recent_notifications($db, $student_id) {
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب إحصائيات الطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array إحصائيات الطالب
 */
function get_student_stats($db, $student_id) {
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
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات المعلقة
 */
function get_pending_assignments($db, $student_id) {
    $stmt = $db->prepare("SELECT a.*, c.name as course_name 
                           FROM assignments a 
                           JOIN course_sections cs ON a.section_id = cs.id 
                           JOIN course_registrations cr ON cs.id = cr.section_id 
                           JOIN courses c ON cs.course_id = c.id 
                           LEFT JOIN assignment_submissions sub ON a.id = sub.assignment_id AND sub.student_id = cr.student_id 
                           WHERE cr.student_id = ? AND a.due_date >= CURDATE() AND sub.id IS NULL 
                           ORDER BY a.due_date ASC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الواجبات المسلمة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات المسلمة
 */
function get_submitted_assignments($db, $student_id) {
    $stmt = $db->prepare("SELECT a.*, c.name as course_name, sub.submission_date, sub.grade 
                           FROM assignments a 
                           JOIN assignment_submissions sub ON a.id = sub.assignment_id 
                           JOIN course_sections cs ON a.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE sub.student_id = ? AND sub.grade IS NULL 
                           ORDER BY sub.submission_date DESC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الواجبات المقيمة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الواجبات المقيمة
 */
function get_graded_assignments($db, $student_id) {
    $stmt = $db->prepare("SELECT a.*, c.name as course_name, sub.submission_date, sub.grade 
                           FROM assignments a 
                           JOIN assignment_submissions sub ON a.id = sub.assignment_id 
                           JOIN course_sections cs ON a.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE sub.student_id = ? AND sub.grade IS NOT NULL 
                           ORDER BY sub.submission_date DESC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب المقررات المتاحة للطالب (للتسجيل)
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المقررات المتاحة
 */
function get_available_courses($db, $student_id) {
    // استعلام وهمي - يجب تحسينه ليشمل متطلبات التسجيل ومستوى الطالب والخطة الدراسية
    $stmt = $db->prepare("SELECT c.*, d.name as department_name 
                           FROM courses c 
                           JOIN departments d ON c.department_id = d.id 
                           WHERE c.is_active = 1 AND c.id NOT IN (SELECT cs.course_id FROM course_registrations cr JOIN course_sections cs ON cr.section_id = cs.id WHERE cr.student_id = ?)");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب المنتديات التي يشارك فيها الطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة المنتديات
 */
function get_student_forums($db, $student_id) {
    $stmt = $db->prepare("SELECT DISTINCT f.*, c.name as course_name 
                           FROM forums f 
                           JOIN course_sections cs ON f.section_id = cs.id 
                           JOIN course_registrations cr ON cs.id = cr.section_id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE cr.student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب مواضيع منتدى معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $forum_id معرف المنتدى
 * @return array قائمة المواضيع
 */
function get_forum_topics($db, $forum_id) {
    $stmt = $db->prepare("SELECT ft.*, u.first_name as author_first_name, u.last_name as author_last_name 
                           FROM forum_topics ft 
                           JOIN users u ON ft.user_id = u.id 
                           WHERE ft.forum_id = ? 
                           ORDER BY ft.is_pinned DESC, ft.last_reply_at DESC, ft.created_at DESC");
    $stmt->execute([$forum_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب معلومات منتدى معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $forum_id معرف المنتدى
 * @return array|null معلومات المنتدى
 */
function get_forum_info($db, $forum_id) {
    $stmt = $db->prepare("SELECT f.*, c.name as course_name 
                           FROM forums f 
                           JOIN course_sections cs ON f.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE f.id = ?");
    $stmt->execute([$forum_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب ردود موضوع معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return array قائمة الردود
 */
function get_topic_posts($db, $topic_id) {
    $stmt = $db->prepare("SELECT fp.*, u.first_name as author_first_name, u.last_name as author_last_name, u.profile_picture 
                           FROM forum_posts fp 
                           JOIN users u ON fp.user_id = u.id 
                           WHERE fp.topic_id = ? 
                           ORDER BY fp.created_at ASC");
    $stmt->execute([$topic_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب معلومات موضوع معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return array|null معلومات الموضوع
 */
function get_topic_info($db, $topic_id) {
    $stmt = $db->prepare("SELECT ft.*, u.first_name as author_first_name, u.last_name as author_last_name 
                           FROM forum_topics ft 
                           JOIN users u ON ft.user_id = u.id 
                           WHERE ft.id = ?");
    $stmt->execute([$topic_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة تحديث عدد مشاهدات الموضوع
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $topic_id معرف الموضوع
 * @return bool نتيجة التحديث
 */
function update_topic_views($db, $topic_id) {
    $stmt = $db->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?");
    return $stmt->execute([$topic_id]);
}

/**
 * دالة جلب الفصل الدراسي الحالي
 * @param PDO $db اتصال قاعدة البيانات
 * @return array|null معلومات الفصل الحالي
 */
function get_current_semester($db) {
    $stmt = $db->prepare("SELECT * FROM academic_terms WHERE is_current = 1 LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب درجات الطالب للفصل الحالي
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الدرجات
 */
function get_student_current_semester_grades($db, $student_id) {
    $current_semester = get_current_semester($db);
    if (!$current_semester) return [];
    
    $stmt = $db->prepare("SELECT c.name as course_name, cr.grade, cr.grade_points 
                           FROM course_registrations cr 
                           JOIN course_sections cs ON cr.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE cr.student_id = ? AND cs.term_id = ?");
    $stmt->execute([$student_id, $current_semester['id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب درجات الطالب لجميع الفصول
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الدرجات مجمعة حسب الفصل
 */
function get_student_all_semesters_grades($db, $student_id) {
    $stmt = $db->prepare("SELECT at.name as semester_name, at.academic_year, c.name as course_name, cr.grade, cr.grade_points 
                           FROM course_registrations cr 
                           JOIN course_sections cs ON cr.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           JOIN academic_terms at ON cs.term_id = at.id 
                           WHERE cr.student_id = ? 
                           ORDER BY at.start_date DESC, c.name ASC");
    $stmt->execute([$student_id]);
    $grades = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $grades[$row['semester_name'] . ' ' . $row['academic_year']][] = $row;
    }
    return $grades;
}

/**
 * دالة حساب المعدل التراكمي للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array|null المعدل التراكمي وعدد الساعات
 */
function get_student_gpa($db, $student_id) {
    $stmt = $db->prepare("SELECT s.gpa, SUM(c.credit_hours) as total_credits 
                           FROM students s 
                           JOIN course_registrations cr ON s.id = cr.student_id 
                           JOIN course_sections cs ON cr.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE s.id = ? AND cr.grade IS NOT NULL AND cr.grade != 'W' -- Exclude withdrawn courses
                           GROUP BY s.id"); 
                           // Note: GPA is usually stored directly in students table, this recalculates based on completed courses.
                           // A more accurate calculation might be needed based on specific university rules.
    $stmt->execute([$student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
         // Recalculate GPA based on registered courses and grades for better accuracy
        $gpa_calc_stmt = $db->prepare("SELECT SUM(cr.grade_points * c.credit_hours) as total_points, SUM(c.credit_hours) as total_credits 
                                     FROM course_registrations cr 
                                     JOIN course_sections cs ON cr.section_id = cs.id 
                                     JOIN courses c ON cs.course_id = c.id 
                                     WHERE cr.student_id = ? AND cr.grade_points IS NOT NULL");
        $gpa_calc_stmt->execute([$student_id]);
        $gpa_result = $gpa_calc_stmt->fetch(PDO::FETCH_ASSOC);

        if ($gpa_result && $gpa_result['total_credits'] > 0) {
            return [
                'cumulative_gpa' => round($gpa_result['total_points'] / $gpa_result['total_credits'], 2),
                'total_credits' => $gpa_result['total_credits']
            ];
        } else {
             return [
                'cumulative_gpa' => $result['gpa'] ?: 0.00, // Fallback to stored GPA if calculation fails
                'total_credits' => $result['total_credits'] ?: 0
            ];
        }
    }
    return null;
}

/**
 * دالة حساب المعدل الفصلي للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @param int $term_id معرف الفصل الدراسي (اختياري، الافتراضي هو الحالي)
 * @return array|null المعدل الفصلي وعدد الساعات
 */
function get_student_semester_gpa($db, $student_id, $term_id = null) {
    if ($term_id === null) {
        $current_semester = get_current_semester($db);
        if (!$current_semester) return null;
        $term_id = $current_semester['id'];
    }
    $stmt = $db->prepare("SELECT SUM(cr.grade_points * c.credit_hours) as total_points, SUM(c.credit_hours) as total_credits 
                           FROM course_registrations cr 
                           JOIN course_sections cs ON cr.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE cr.student_id = ? AND cs.term_id = ? AND cr.grade_points IS NOT NULL");
    $stmt->execute([$student_id, $term_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب (المستخدم الحالي)
 * @return array قائمة المحادثات
 */
function get_student_conversations($db, $student_id) {
    // يجلب المحادثات التي يشارك فيها المستخدم الحالي
    // ويجلب اسم وصورة الطرف الآخر في المحادثة (إذا كانت محادثة ثنائية)
    // ويجلب آخر رسالة في المحادثة
    $stmt = $db->prepare("SELECT c.id as conversation_id, c.title as conversation_title, 
                           other_user.first_name as other_user_first_name, 
                           other_user.last_name as other_user_last_name, 
                           other_user.profile_picture as other_user_picture, 
                           lm.message as last_message, lm.created_at as last_message_time, lm.sender_id as last_message_sender_id, 
                           (SELECT COUNT(*) FROM messages msg WHERE msg.conversation_id = c.id AND msg.recipient_id = ? AND msg.is_read = 0) as unread_count
                           FROM conversations c 
                           JOIN conversation_participants cp ON c.id = cp.conversation_id
                           -- Join to get the other participant's info (assuming 1-on-1 chat for now)
                           JOIN conversation_participants cp_other ON c.id = cp_other.conversation_id AND cp_other.user_id != ?
                           JOIN users other_user ON cp_other.user_id = other_user.id
                           -- Left Join to get the last message
                           LEFT JOIN (SELECT m1.* FROM messages m1 LEFT JOIN messages m2 ON (m1.conversation_id = m2.conversation_id AND m1.created_at < m2.created_at) WHERE m2.id IS NULL) lm ON c.id = lm.conversation_id
                           WHERE cp.user_id = ? 
                           GROUP BY c.id
                           ORDER BY lm.created_at DESC");
    $stmt->execute([$student_id, $student_id, $student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب رسائل محادثة معينة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $conversation_id معرف المحادثة
 * @param int $student_id معرف الطالب (للتحقق من الصلاحية)
 * @return array قائمة الرسائل
 */
function get_conversation_messages($db, $conversation_id, $student_id) {
    // التحقق من أن الطالب مشارك في المحادثة
    $check_stmt = $db->prepare("SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
    $check_stmt->execute([$conversation_id, $student_id]);
    if ($check_stmt->fetchColumn() == 0) {
        return []; // الطالب ليس جزءًا من هذه المحادثة
    }

    // جلب الرسائل
    $stmt = $db->prepare("SELECT m.*, u.first_name as sender_first_name, u.last_name as sender_last_name, u.profile_picture as sender_picture 
                           FROM messages m 
                           JOIN users u ON m.sender_id = u.id 
                           WHERE m.conversation_id = ? 
                           ORDER BY m.created_at ASC");
    $stmt->execute([$conversation_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب معلومات محادثة معينة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $conversation_id معرف المحادثة
 * @return array|null معلومات المحادثة
 */
function get_conversation_info($db, $conversation_id) {
    $stmt = $db->prepare("SELECT * FROM conversations WHERE id = ?");
    $stmt->execute([$conversation_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة تحديث حالة الرسائل إلى مقروءة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $conversation_id معرف المحادثة
 * @param int $student_id معرف الطالب
 * @return bool نتيجة التحديث
 */
function update_messages_status($db, $conversation_id, $student_id) {
    $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND recipient_id = ? AND is_read = 0");
    return $stmt->execute([$conversation_id, $student_id]);
}

/**
 * دالة جلب الإشعارات غير المقروءة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الإشعارات
 */
function get_unread_notifications($db, $student_id) {
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الإشعارات المقروءة للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array قائمة الإشعارات
 */
function get_read_notifications($db, $student_id) {
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 1 ORDER BY created_at DESC LIMIT 20"); // Limit for performance
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة تحديث الملف الشخصي للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم (الطالب)
 * @param string $first_name الاسم الأول
 * @param string $last_name الاسم الأخير
 * @param string $email البريد الإلكتروني
 * @param string $phone الهاتف
 * @param string $address العنوان
 * @param string $date_of_birth تاريخ الميلاد
 * @param string $gender الجنس
 * @return bool نتيجة التحديث
 */
function update_student_profile($db, $user_id, $first_name, $last_name, $email, $phone, $address, $date_of_birth, $gender) {
    $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, date_of_birth = ?, gender = ? 
                           WHERE id = ? AND user_type = 'student'");
    return $stmt->execute([$first_name, $last_name, $email, $phone, $address, $date_of_birth, $gender, $user_id]);
}

/**
 * دالة التحقق من كلمة مرور الطالب الحالية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم (الطالب)
 * @param string $current_password كلمة المرور الحالية
 * @return bool نتيجة التحقق
 */
function verify_student_password($db, $user_id, $current_password) {
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ? AND user_type = 'student'");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return password_verify($current_password, $result['password']);
    }
    return false;
}

/**
 * دالة تحديث كلمة مرور الطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم (الطالب)
 * @param string $new_password كلمة المرور الجديدة
 * @return bool نتيجة التحديث
 */
function update_student_password($db, $user_id, $new_password) {
    $hashed_password = hashPassword($new_password);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ? AND user_type = 'student'");
    return $stmt->execute([$hashed_password, $user_id]);
}

/**
 * دالة تحديث صورة الملف الشخصي للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم (الطالب)
 * @param string $file_path مسار الصورة الجديد
 * @return bool نتيجة التحديث
 */
function update_student_profile_image($db, $user_id, $file_path) {
    $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE id = ? AND user_type = 'student'");
    return $stmt->execute([$file_path, $user_id]);
}

/**
 * دالة جلب الجدول الدراسي للطالب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $student_id معرف الطالب
 * @return array الجدول الدراسي
 */
function get_student_schedule($db, $student_id) {
    $current_term = get_current_semester($db);
    if (!$current_term) return [];

    $stmt = $db->prepare("SELECT cs.days, cs.start_time, cs.end_time, cs.location, c.name as course_name, c.code as course_code, 
                           t.first_name as teacher_first_name, t.last_name as teacher_last_name 
                           FROM course_sections cs 
                           JOIN courses c ON cs.course_id = c.id 
                           JOIN teachers tch ON cs.teacher_id = tch.id 
                           JOIN users t ON tch.user_id = t.id 
                           JOIN course_registrations cr ON cs.id = cr.section_id 
                           WHERE cr.student_id = ? AND cs.term_id = ? AND cr.status = 'registered' 
                           ORDER BY cs.days, cs.start_time");
    $stmt->execute([$student_id, $current_term['id']]);
    $schedule_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تنظيم الجدول حسب اليوم
    $schedule = [];
    $days_map = ['Sun' => 'الأحد', 'Mon' => 'الاثنين', 'Tue' => 'الثلاثاء', 'Wed' => 'الأربعاء', 'Thu' => 'الخميس', 'Fri' => 'الجمعة', 'Sat' => 'السبت'];
    
    foreach ($schedule_data as $item) {
        $days = explode(',', $item['days']);
        foreach ($days as $day_en) {
            $day_ar = $days_map[trim($day_en)] ?? trim($day_en);
            if (!isset($schedule[$day_ar])) {
                $schedule[$day_ar] = [];
            }
            $schedule[$day_ar][] = $item;
        }
    }
    
    // فرز كل يوم حسب وقت البدء
    foreach ($schedule as $day => $items) {
        usort($schedule[$day], function($a, $b) {
            return strtotime($a['start_time']) - strtotime($b['start_time']);
        });
    }

    return $schedule;
}


// ==========================================================================
// == الدوال المضافة حديثاً بناءً على طلب المستخدم (نظام المعلمين) ==
// ==========================================================================

/**
 * دالة جلب معلومات المعلم
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $teacher_user_id معرف مستخدم المعلم
 * @return array|null معلومات المعلم
 */
function get_teacher_info($db, $teacher_user_id) {
    $stmt = $db->prepare("SELECT u.*, t.teacher_id as teacher_reg_id, t.position, t.specialization, t.qualification, t.office_location, t.office_hours, 
                           c.name as college_name, d.name as department_name 
                           FROM users u 
                           JOIN teachers t ON u.id = t.user_id 
                           JOIN colleges c ON t.college_id = c.id 
                           JOIN departments d ON t.department_id = d.id 
                           WHERE u.id = ? AND u.user_type = 'teacher'");
    $stmt->execute([$teacher_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب المقررات التي يدرسها المعلم
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم (من جدول teachers)
 * @return array قائمة المقررات
 */
function get_teacher_courses($db, $teacher_id) {
    $current_term = get_current_semester($db);
    if (!$current_term) return [];

    $stmt = $db->prepare("SELECT c.*, cs.section_number, cs.id as section_id, COUNT(cr.id) as enrolled_students 
                           FROM courses c 
                           JOIN course_sections cs ON c.id = cs.course_id 
                           LEFT JOIN course_registrations cr ON cs.id = cr.section_id AND cr.status = 'registered' 
                           WHERE cs.teacher_id = ? AND cs.term_id = ? 
                           GROUP BY cs.id 
                           ORDER BY c.code, cs.section_number");
    $stmt->execute([$teacher_id, $current_term['id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الواجبات الخاصة بشعبة معينة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $section_id معرف الشعبة
 * @return array قائمة الواجبات
 */
function get_section_assignments($db, $section_id) {
    $stmt = $db->prepare("SELECT * FROM assignments WHERE section_id = ? ORDER BY due_date DESC");
    $stmt->execute([$section_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الاختبارات الخاصة بشعبة معينة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $section_id معرف الشعبة
 * @return array قائمة الاختبارات
 */
function get_section_exams($db, $section_id) {
    $stmt = $db->prepare("SELECT * FROM exams WHERE section_id = ? ORDER BY exam_date DESC");
    $stmt->execute([$section_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الطلاب المسجلين في شعبة معينة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $section_id معرف الشعبة
 * @return array قائمة الطلاب
 */
function get_section_students($db, $section_id) {
    $stmt = $db->prepare("SELECT u.id as user_id, u.first_name, u.last_name, u.email, s.student_id as student_reg_id, cr.grade 
                           FROM users u 
                           JOIN students s ON u.id = s.user_id 
                           JOIN course_registrations cr ON s.id = cr.student_id 
                           WHERE cr.section_id = ? AND cr.status = 'registered' 
                           ORDER BY u.last_name, u.first_name");
    $stmt->execute([$section_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب تسليمات واجب معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $assignment_id معرف الواجب
 * @return array قائمة التسليمات
 */
function get_assignment_submissions($db, $assignment_id) {
    $stmt = $db->prepare("SELECT sub.*, u.first_name, u.last_name, s.student_id as student_reg_id 
                           FROM assignment_submissions sub 
                           JOIN students s ON sub.student_id = s.id 
                           JOIN users u ON s.user_id = u.id 
                           WHERE sub.assignment_id = ? 
                           ORDER BY sub.submission_date DESC");
    $stmt->execute([$assignment_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة إضافة واجب جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $section_id معرف الشعبة
 * @param string $title عنوان الواجب
 * @param string $description وصف الواجب
 * @param string $due_date تاريخ التسليم
 * @param float $max_grade الدرجة القصوى
 * @return bool نتيجة الإضافة
 */
function add_assignment($db, $section_id, $title, $description, $due_date, $max_grade) {
    $stmt = $db->prepare("INSERT INTO assignments (section_id, title, description, due_date, max_grade) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$section_id, $title, $description, $due_date, $max_grade]);
}

/**
 * دالة إضافة اختبار جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $section_id معرف الشعبة
 * @param string $title عنوان الاختبار
 * @param string $description وصف الاختبار
 * @param string $exam_date تاريخ الاختبار
 * @param string $start_time وقت البدء
 * @param string $end_time وقت الانتهاء
 * @param string $location مكان الاختبار
 * @param float $max_grade الدرجة القصوى
 * @return bool نتيجة الإضافة
 */
function add_exam($db, $section_id, $title, $description, $exam_date, $start_time, $end_time, $location, $max_grade) {
    $stmt = $db->prepare("INSERT INTO exams (section_id, title, description, exam_date, start_time, end_time, location, max_grade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$section_id, $title, $description, $exam_date, $start_time, $end_time, $location, $max_grade]);
}

/**
 * دالة تحديث درجة طالب في واجب
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $submission_id معرف التسليم
 * @param float $grade الدرجة
 * @param string $feedback ملاحظات
 * @return bool نتيجة التحديث
 */
function update_assignment_grade($db, $submission_id, $grade, $feedback) {
    $stmt = $db->prepare("UPDATE assignment_submissions SET grade = ?, feedback = ?, graded_at = NOW() WHERE id = ?");
    return $stmt->execute([$grade, $feedback, $submission_id]);
}

/**
 * دالة تحديث درجة طالب في مقرر
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $registration_id معرف التسجيل
 * @param string $grade الدرجة النهائية (A+, B, etc.)
 * @param float $grade_points النقاط المقابلة
 * @return bool نتيجة التحديث
 */
function update_course_grade($db, $registration_id, $grade, $grade_points) {
    $stmt = $db->prepare("UPDATE course_registrations SET grade = ?, grade_points = ? WHERE id = ?");
    return $stmt->execute([$grade, $grade_points, $registration_id]);
}

/**
 * دالة جلب معلومات مقرر معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $course_id معرف المقرر
 * @return array|null معلومات المقرر
 */
function get_course_info($db, $course_id) {
    $stmt = $db->prepare("SELECT c.*, d.name as department_name FROM courses c JOIN departments d ON c.department_id = d.id WHERE c.id = ?");
    $stmt->execute([$course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب معلومات شعبة معينة
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $section_id معرف الشعبة
 * @return array|null معلومات الشعبة
 */
function get_section_info($db, $section_id) {
    $stmt = $db->prepare("SELECT cs.*, c.name as course_name, c.code as course_code, at.name as term_name 
                           FROM course_sections cs 
                           JOIN courses c ON cs.course_id = c.id 
                           JOIN academic_terms at ON cs.term_id = at.id 
                           WHERE cs.id = ?");
    $stmt->execute([$section_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب معلومات واجب معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $assignment_id معرف الواجب
 * @return array|null معلومات الواجب
 */
function get_assignment_info($db, $assignment_id) {
    $stmt = $db->prepare("SELECT a.*, c.name as course_name, cs.section_number 
                           FROM assignments a 
                           JOIN course_sections cs ON a.section_id = cs.id 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE a.id = ?");
    $stmt->execute([$assignment_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب معلومات تسليم واجب معين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $submission_id معرف التسليم
 * @return array|null معلومات التسليم
 */
function get_submission_info($db, $submission_id) {
    $stmt = $db->prepare("SELECT sub.*, u.first_name, u.last_name, s.student_id as student_reg_id, a.title as assignment_title 
                           FROM assignment_submissions sub 
                           JOIN students s ON sub.student_id = s.id 
                           JOIN users u ON s.user_id = u.id 
                           JOIN assignments a ON sub.assignment_id = a.id 
                           WHERE sub.id = ?");
    $stmt->execute([$submission_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب الجدول الدراسي للمعلم
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $teacher_id معرف المعلم (من جدول teachers)
 * @return array الجدول الدراسي
 */
function get_teacher_schedule($db, $teacher_id) {
    $current_term = get_current_semester($db);
    if (!$current_term) return [];

    $stmt = $db->prepare("SELECT cs.days, cs.start_time, cs.end_time, cs.location, c.name as course_name, c.code as course_code, cs.section_number 
                           FROM course_sections cs 
                           JOIN courses c ON cs.course_id = c.id 
                           WHERE cs.teacher_id = ? AND cs.term_id = ? 
                           ORDER BY cs.days, cs.start_time");
    $stmt->execute([$teacher_id, $current_term['id']]);
    $schedule_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تنظيم الجدول حسب اليوم
    $schedule = [];
    $days_map = ['Sun' => 'الأحد', 'Mon' => 'الاثنين', 'Tue' => 'الثلاثاء', 'Wed' => 'الأربعاء', 'Thu' => 'الخميس', 'Fri' => 'الجمعة', 'Sat' => 'السبت'];
    
    foreach ($schedule_data as $item) {
        $days = explode(',', $item['days']);
        foreach ($days as $day_en) {
            $day_ar = $days_map[trim($day_en)] ?? trim($day_en);
            if (!isset($schedule[$day_ar])) {
                $schedule[$day_ar] = [];
            }
            $schedule[$day_ar][] = $item;
        }
    }
    
    // فرز كل يوم حسب وقت البدء
    foreach ($schedule as $day => $items) {
        usort($schedule[$day], function($a, $b) {
            return strtotime($a['start_time']) - strtotime($b['start_time']);
        });
    }

    return $schedule;
}


// ==========================================================================
// == الدوال المضافة حديثاً بناءً على طلب المستخدم (نظام الكليات) ==
// ==========================================================================

/**
 * دالة جلب معلومات مسؤول الكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $admin_user_id معرف مستخدم مسؤول الكلية
 * @return array|null معلومات المسؤول والكلية
 */
function get_college_admin_info($db, $admin_user_id) {
    $stmt = $db->prepare("SELECT u.*, ca.admin_id as admin_reg_id, ca.position, ca.college_id, 
                           c.name as college_name, c.code as college_code 
                           FROM users u 
                           JOIN college_admins ca ON u.id = ca.user_id 
                           JOIN colleges c ON ca.college_id = c.id 
                           WHERE u.id = ? AND u.user_type = 'college_admin'");
    $stmt->execute([$admin_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب الأقسام التابعة للكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة الأقسام
 */
function get_college_departments($db, $college_id) {
    $stmt = $db->prepare("SELECT d.*, u.first_name as head_first_name, u.last_name as head_last_name 
                           FROM departments d 
                           LEFT JOIN teachers t ON d.head_id = t.id 
                           LEFT JOIN users u ON t.user_id = u.id 
                           WHERE d.college_id = ? 
                           ORDER BY d.name");
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب البرامج الأكاديمية التابعة للكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة البرامج
 */
function get_college_programs($db, $college_id) {
    $stmt = $db->prepare("SELECT p.*, d.name as department_name, u.first_name as coordinator_first_name, u.last_name as coordinator_last_name 
                           FROM academic_programs p 
                           JOIN departments d ON p.department_id = d.id 
                           LEFT JOIN teachers t ON p.coordinator_id = t.id 
                           LEFT JOIN users u ON t.user_id = u.id 
                           WHERE d.college_id = ? 
                           ORDER BY d.name, p.name");
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب المقررات التابعة للكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة المقررات
 */
function get_college_courses($db, $college_id) {
    $stmt = $db->prepare("SELECT c.*, d.name as department_name 
                           FROM courses c 
                           JOIN departments d ON c.department_id = d.id 
                           WHERE d.college_id = ? 
                           ORDER BY d.name, c.code");
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب المعلمين التابعين للكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة المعلمين
 */
function get_college_teachers($db, $college_id) {
    $stmt = $db->prepare("SELECT u.id as user_id, u.first_name, u.last_name, u.email, t.teacher_id as teacher_reg_id, t.position, t.specialization, d.name as department_name 
                           FROM users u 
                           JOIN teachers t ON u.id = t.user_id 
                           JOIN departments d ON t.department_id = d.id 
                           WHERE t.college_id = ? 
                           ORDER BY d.name, u.last_name, u.first_name");
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الطلاب التابعين للكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة الطلاب
 */
function get_college_students($db, $college_id) {
    $stmt = $db->prepare("SELECT u.id as user_id, u.first_name, u.last_name, u.email, s.student_id as student_reg_id, s.academic_level, s.gpa, d.name as department_name, p.name as program_name 
                           FROM users u 
                           JOIN students s ON u.id = s.user_id 
                           JOIN departments d ON s.department_id = d.id 
                           JOIN academic_programs p ON s.program_id = p.id 
                           WHERE s.college_id = ? 
                           ORDER BY d.name, p.name, u.last_name, u.first_name");
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب الشعب الدراسية للكلية للفصل الحالي
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة الشعب
 */
function get_college_schedule($db, $college_id) {
    $current_term = get_current_semester($db);
    if (!$current_term) return [];

    $stmt = $db->prepare("SELECT cs.*, c.name as course_name, c.code as course_code, d.name as department_name, u.first_name as teacher_first_name, u.last_name as teacher_last_name 
                           FROM course_sections cs 
                           JOIN courses c ON cs.course_id = c.id 
                           JOIN departments d ON c.department_id = d.id 
                           JOIN teachers t ON cs.teacher_id = t.id 
                           JOIN users u ON t.user_id = u.id 
                           WHERE d.college_id = ? AND cs.term_id = ? 
                           ORDER BY d.name, c.code, cs.section_number");
    $stmt->execute([$college_id, $current_term['id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب إحصائيات الكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array إحصائيات الكلية
 */
function get_college_stats($db, $college_id) {
    $dept_stmt = $db->prepare("SELECT COUNT(*) FROM departments WHERE college_id = ?");
    $dept_stmt->execute([$college_id]);
    $departments_count = $dept_stmt->fetchColumn();

    $prog_stmt = $db->prepare("SELECT COUNT(*) FROM academic_programs p JOIN departments d ON p.department_id = d.id WHERE d.college_id = ?");
    $prog_stmt->execute([$college_id]);
    $programs_count = $prog_stmt->fetchColumn();

    $teacher_stmt = $db->prepare("SELECT COUNT(*) FROM teachers WHERE college_id = ?");
    $teacher_stmt->execute([$college_id]);
    $teachers_count = $teacher_stmt->fetchColumn();

    $student_stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE college_id = ?");
    $student_stmt->execute([$college_id]);
    $students_count = $student_stmt->fetchColumn();

    return [
        'departments_count' => $departments_count,
        'programs_count' => $programs_count,
        'teachers_count' => $teachers_count,
        'students_count' => $students_count
    ];
}

/**
 * دالة جلب الإعلانات الخاصة بالكلية
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @return array قائمة الإعلانات
 */
function get_college_announcements($db, $college_id) {
    $stmt = $db->prepare("SELECT * FROM announcements WHERE college_id = ? ORDER BY created_at DESC");
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة إضافة قسم جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @param string $name اسم القسم (عربي)
 * @param string $name_en اسم القسم (إنجليزي)
 * @param string $code رمز القسم
 * @param string $description وصف القسم (عربي)
 * @param string $description_en وصف القسم (إنجليزي)
 * @param int|null $head_id معرف رئيس القسم (اختياري)
 * @param string $location الموقع
 * @param string $contact_email بريد التواصل
 * @param string $contact_phone هاتف التواصل
 * @return bool نتيجة الإضافة
 */
function add_department($db, $college_id, $name, $name_en, $code, $description, $description_en, $head_id, $location, $contact_email, $contact_phone) {
    $stmt = $db->prepare("INSERT INTO departments (college_id, name, name_en, code, description, description_en, head_id, location, contact_email, contact_phone) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$college_id, $name, $name_en, $code, $description, $description_en, $head_id, $location, $contact_email, $contact_phone]);
}

/**
 * دالة إضافة برنامج أكاديمي جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $department_id معرف القسم
 * @param string $name اسم البرنامج (عربي)
 * @param string $name_en اسم البرنامج (إنجليزي)
 * @param string $code رمز البرنامج
 * @param string $description وصف البرنامج (عربي)
 * @param string $description_en وصف البرنامج (إنجليزي)
 * @param string $degree الدرجة العلمية
 * @param int $credit_hours عدد الساعات المعتمدة
 * @param int $duration_years مدة الدراسة بالسنوات
 * @param int|null $coordinator_id معرف منسق البرنامج (اختياري)
 * @return bool نتيجة الإضافة
 */
function add_academic_program($db, $department_id, $name, $name_en, $code, $description, $description_en, $degree, $credit_hours, $duration_years, $coordinator_id) {
    $stmt = $db->prepare("INSERT INTO academic_programs (department_id, name, name_en, code, description, description_en, degree, credit_hours, duration_years, coordinator_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$department_id, $name, $name_en, $code, $description, $description_en, $degree, $credit_hours, $duration_years, $coordinator_id]);
}

/**
 * دالة إضافة مقرر دراسي جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $department_id معرف القسم
 * @param string $code رمز المقرر
 * @param string $name اسم المقرر (عربي)
 * @param string $name_en اسم المقرر (إنجليزي)
 * @param string $description وصف المقرر (عربي)
 * @param string $description_en وصف المقرر (إنجليزي)
 * @param int $credit_hours عدد الساعات المعتمدة
 * @param int $lecture_hours ساعات المحاضرات
 * @param int|null $lab_hours ساعات المعمل (اختياري)
 * @param int $level المستوى الدراسي
 * @return bool نتيجة الإضافة
 */
function add_course($db, $department_id, $code, $name, $name_en, $description, $description_en, $credit_hours, $lecture_hours, $lab_hours, $level) {
    $stmt = $db->prepare("INSERT INTO courses (department_id, code, name, name_en, description, description_en, credit_hours, lecture_hours, lab_hours, level) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$department_id, $code, $name, $name_en, $description, $description_en, $credit_hours, $lecture_hours, $lab_hours, $level]);
}

/**
 * دالة إضافة إعلان جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $college_id معرف الكلية
 * @param int $user_id معرف المستخدم (الناشر)
 * @param string $title عنوان الإعلان
 * @param string $content محتوى الإعلان
 * @param string $target_audience الجمهور المستهدف
 * @return bool نتيجة الإضافة
 */
function add_announcement($db, $college_id, $user_id, $title, $content, $target_audience) {
    $stmt = $db->prepare("INSERT INTO announcements (college_id, user_id, title, content, target_audience) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$college_id, $user_id, $title, $content, $target_audience]);
}


// ==========================================================================
// == الدوال المضافة حديثاً بناءً على طلب المستخدم (نظام المشرف) ==
// ==========================================================================

/**
 * دالة جلب معلومات مشرف النظام
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $admin_user_id معرف مستخدم المشرف
 * @return array|null معلومات المشرف
 */
function get_system_admin_info($db, $admin_user_id) {
    $stmt = $db->prepare("SELECT u.*, sa.admin_id as admin_reg_id, sa.role 
                           FROM users u 
                           JOIN system_admins sa ON u.id = sa.user_id 
                           WHERE u.id = ? AND u.user_type = 'system_admin'");
    $stmt->execute([$admin_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * دالة جلب جميع المستخدمين
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $limit عدد السجلات لكل صفحة
 * @param int $offset بداية السجلات
 * @param string $search مصطلح البحث (اختياري)
 * @param string $user_type نوع المستخدم (اختياري)
 * @return array قائمة المستخدمين وإجمالي العدد
 */
function get_all_users($db, $limit = 10, $offset = 0, $search = '', $user_type = '') {
    $params = [];
    $sql = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.user_type, u.is_active, u.created_at FROM users u WHERE 1=1";
    
    if (!empty($search)) {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    
    if (!empty($user_type)) {
        $sql .= " AND u.user_type = ?";
        $params[] = $user_type;
    }
    
    // جلب إجمالي العدد للترقيم
    $count_sql = str_replace("SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.user_type, u.is_active, u.created_at FROM users u", "SELECT COUNT(*) FROM users u", $sql);
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetchColumn();
    
    // إضافة الترتيب والترقيم
    $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    // يجب ربط المعاملات بالنوع الصحيح
    for ($i = 1; $i <= count($params); $i++) {
        $type = PDO::PARAM_STR;
        if ($i == count($params) - 1 || $i == count($params)) { // Limit and Offset are integers
            $type = PDO::PARAM_INT;
        }
        $stmt->bindValue($i, $params[$i-1], $type);
    }
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return ['users' => $users, 'total_count' => $total_count];
}

/**
 * دالة جلب جميع الكليات
 * @param PDO $db اتصال قاعدة البيانات
 * @return array قائمة الكليات
 */
function get_all_colleges($db) {
    $stmt = $db->prepare("SELECT c.*, u.first_name as dean_first_name, u.last_name as dean_last_name 
                           FROM colleges c 
                           LEFT JOIN teachers t ON c.dean_id = t.id 
                           LEFT JOIN users u ON t.user_id = u.id 
                           ORDER BY c.name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب جميع الأقسام
 * @param PDO $db اتصال قاعدة البيانات
 * @return array قائمة الأقسام
 */
function get_all_departments($db) {
    $stmt = $db->prepare("SELECT d.*, c.name as college_name, u.first_name as head_first_name, u.last_name as head_last_name 
                           FROM departments d 
                           JOIN colleges c ON d.college_id = c.id 
                           LEFT JOIN teachers t ON d.head_id = t.id 
                           LEFT JOIN users u ON t.user_id = u.id 
                           ORDER BY c.name, d.name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب جميع البرامج الأكاديمية
 * @param PDO $db اتصال قاعدة البيانات
 * @return array قائمة البرامج
 */
function get_all_programs($db) {
    $stmt = $db->prepare("SELECT p.*, d.name as department_name, c.name as college_name, u.first_name as coordinator_first_name, u.last_name as coordinator_last_name 
                           FROM academic_programs p 
                           JOIN departments d ON p.department_id = d.id 
                           JOIN colleges c ON d.college_id = c.id 
                           LEFT JOIN teachers t ON p.coordinator_id = t.id 
                           LEFT JOIN users u ON t.user_id = u.id 
                           ORDER BY c.name, d.name, p.name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب جميع المقررات الدراسية
 * @param PDO $db اتصال قاعدة البيانات
 * @return array قائمة المقررات
 */
function get_all_courses($db) {
    $stmt = $db->prepare("SELECT c.*, d.name as department_name, col.name as college_name 
                           FROM courses c 
                           JOIN departments d ON c.department_id = d.id 
                           JOIN colleges col ON d.college_id = col.id 
                           ORDER BY col.name, d.name, c.code");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة جلب جميع الأدوار
 * @param PDO $db اتصال قاعدة البيانات
 * @return array قائمة الأدوار
 */
function get_all_roles($db) {
    $stmt = $db->prepare("SELECT * FROM roles ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة إضافة مستخدم جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param string $username اسم المستخدم
 * @param string $email البريد الإلكتروني
 * @param string $password كلمة المرور
 * @param string $user_type نوع المستخدم
 * @param string $first_name الاسم الأول
 * @param string $last_name الاسم الأخير
 * @param int $role_id معرف الدور
 * @return int|bool معرف المستخدم الجديد أو false
 */
function add_user($db, $username, $email, $password, $user_type, $first_name, $last_name, $role_id) {
    $hashed_password = hashPassword($password);
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, user_type, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $user_type, $first_name, $last_name]);
        $user_id = $db->lastInsertId();

        $role_stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $role_stmt->execute([$user_id, $role_id]);
        
        // قد تحتاج لإضافة سجلات في جداول الطلاب/المعلمين/المسؤولين بناءً على user_type
        // ... (Add logic here based on user_type)

        $db->commit();
        return $user_id;
    } catch (Exception $e) {
        $db->rollBack();
        // Log error $e->getMessage();
        return false;
    }
}

/**
 * دالة تحديث بيانات مستخدم
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم
 * @param string $username اسم المستخدم
 * @param string $email البريد الإلكتروني
 * @param string $user_type نوع المستخدم
 * @param string $first_name الاسم الأول
 * @param string $last_name الاسم الأخير
 * @param bool $is_active حالة الحساب
 * @param int $role_id معرف الدور
 * @return bool نتيجة التحديث
 */
function update_user($db, $user_id, $username, $email, $user_type, $first_name, $last_name, $is_active, $role_id) {
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, first_name = ?, last_name = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$username, $email, $user_type, $first_name, $last_name, $is_active, $user_id]);

        // تحديث الدور (حذف القديم وإضافة الجديد)
        $del_role_stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $del_role_stmt->execute([$user_id]);
        
        $add_role_stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $add_role_stmt->execute([$user_id, $role_id]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        // Log error $e->getMessage();
        return false;
    }
}

/**
 * دالة حذف مستخدم
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم
 * @return bool نتيجة الحذف
 */
function delete_user($db, $user_id) {
    // الحذف سيتم تلقائياً من الجداول المرتبطة بسبب ON DELETE CASCADE
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$user_id]);
}

/**
 * دالة إضافة كلية جديدة
 * @param PDO $db اتصال قاعدة البيانات
 * @param string $name اسم الكلية (عربي)
 * @param string $name_en اسم الكلية (إنجليزي)
 * @param string $code رمز الكلية
 * @param string $description وصف الكلية (عربي)
 * @param string $description_en وصف الكلية (إنجليزي)
 * @param int|null $dean_id معرف العميد (اختياري)
 * @param string $location الموقع
 * @param string $contact_email بريد التواصل
 * @param string $contact_phone هاتف التواصل
 * @param string $website الموقع الإلكتروني
 * @return bool نتيجة الإضافة
 */
function add_college($db, $name, $name_en, $code, $description, $description_en, $dean_id, $location, $contact_email, $contact_phone, $website) {
    $stmt = $db->prepare("INSERT INTO colleges (name, name_en, code, description, description_en, dean_id, location, contact_email, contact_phone, website) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $name_en, $code, $description, $description_en, $dean_id, $location, $contact_email, $contact_phone, $website]);
}

/**
 * دالة إضافة دور جديد
 * @param PDO $db اتصال قاعدة البيانات
 * @param string $name اسم الدور
 * @param string $description وصف الدور
 * @param string $permissions الصلاحيات (JSON)
 * @return bool نتيجة الإضافة
 */
function add_role($db, $name, $description, $permissions) {
    $stmt = $db->prepare("INSERT INTO roles (name, description, permissions) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $description, $permissions]);
}

/**
 * دالة جلب سجلات النظام
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $limit عدد السجلات
 * @param int $offset بداية السجلات
 * @return array قائمة السجلات
 */
function get_system_logs($db, $limit = 50, $offset = 0) {
    $stmt = $db->prepare("SELECT * FROM system_logs ORDER BY timestamp DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * دالة تسجيل حدث في سجلات النظام
 * @param PDO $db اتصال قاعدة البيانات
 * @param int $user_id معرف المستخدم (إذا كان متوفراً)
 * @param string $action الإجراء الذي تم
 * @param string $details تفاصيل إضافية
 * @return bool نتيجة التسجيل
 */
function log_system_event($db, $user_id, $action, $details) {
    $stmt = $db->prepare("INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    return $stmt->execute([$user_id, $action, $details, $ip_address]);
}

/**
 * دالة جلب إعدادات النظام
 * @param PDO $db اتصال قاعدة البيانات
 * @return array مصفوفة الإعدادات
 */
function get_system_settings($db) {
    $settings = [];
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

/**
 * دالة تحديث إعدادات النظام
 * @param PDO $db اتصال قاعدة البيانات
 * @param array $settings مصفوفة الإعدادات الجديدة (key => value)
 * @return bool نتيجة التحديث
 */
function update_system_settings($db, $settings) {
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        // Log error $e->getMessage();
        return false;
    }
}


// ==========================================================================
// == دوال API (تعريفات وهمية - يجب تطويرها لاحقاً) ==
// ==========================================================================

// --- Student API Handlers ---
function handleAssignmentDetails($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleSubmitAssignment($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleCourseMaterials($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleRegisterCourse($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleDropCourse($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleSendMessage($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleCreateForumTopic($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleCreateForumPost($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleUpdateStudentSettings($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }

// --- Teacher API Handlers ---
function handleCreateAssignment($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleGradeAssignment($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleCreateExam($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleRecordAttendance($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleUploadCourseMaterial($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleTeacherSendMessage($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleUpdateTeacherSettings($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }

// --- College API Handlers ---
function handleCollegeReports($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleManageDepartments($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleManagePrograms($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleManageCollegeTeachers($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleManageCollegeStudents($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleCollegeAnnouncements($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleUpdateCollegeSettings($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }

// --- Admin API Handlers ---
function handleManageUsers($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleManageColleges($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleManageRoles($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleSystemBackup($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }
function handleUpdateAdminSettings($db, $params) { /* ... Logic ... */ echo json_encode(['status' => 'error', 'message' => 'Not implemented']); }

?>
