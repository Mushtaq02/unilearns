<?php
/**
 * صفحة تسجيل الخروج
 */

// بدء الجلسة
session_start();

// تدمير الجلسة
session_destroy();

// إعادة التوجيه إلى الصفحة الرئيسية
header('Location: index.php');
exit;
?>

