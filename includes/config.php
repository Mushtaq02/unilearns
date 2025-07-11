<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'unilearns');

// إعدادات النظام
define('SITE_URL', 'http://localhost/unilearns');
define('SITE_NAME', 'UniLearns');
define('SITE_NAME_AR', 'يونيليرنز');
define('SITE_LANG', 'ar');
define('SITE_THEME', 'light');

// إعدادات الأمان
define('ENCRYPTION_KEY', 'your-secret-key-here');
define('SESSION_TIMEOUT', 3600); // ساعة واحدة

// إعدادات الملفات
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 10485760); // 10 ميجابايت

// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-password');
define('FROM_EMAIL', 'noreply@univerboard.edu');
define('FROM_NAME', 'UniLearns System');

// إعدادات اللغة
define('DEFAULT_LANGUAGE', 'ar');
define('SUPPORTED_LANGUAGES', ['ar', 'en']);

// إعدادات المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>

