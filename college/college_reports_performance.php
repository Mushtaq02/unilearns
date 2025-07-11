<?php
/**
 * صفحة تقارير الأداء في نظام UniverBoard
 * تتيح لمسؤول الكلية عرض وتحليل بيانات أداء الطلاب والمعلمين والمقررات
 */

// استيراد ملفات الإعدادات والدوال
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// التحقق من تسجيل دخول مسؤول الكلية
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'college_admin') {
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    header('Location: ../login.php');
    exit;
}

// الحصول على معلومات مسؤول الكلية
$admin_id = $_SESSION['user_id'];
$db = get_db_connection();
$admin = get_college_admin_info($db, $admin_id);
$college_id = $admin['college_id'];
$college = get_college_info($db, $college_id);

// تعيين اللغة الافتراضية
$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : SITE_LANG;

// تعيين المظهر الافتراضي
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : SITE_THEME;

// تحميل ملفات اللغة
$translations = [];
if ($lang === 'ar') {
    include '../includes/lang/ar.php';
} else {
    include '../includes/lang/en.php';
}

// دالة ترجمة النصوص
function t($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}

// معالجة طلبات الفلترة
$filter_report_type = filter_input(INPUT_GET, 'report_type', FILTER_SANITIZE_STRING) ?: 'student_performance'; // student_performance, teacher_performance, course_performance
$filter_department = filter_input(INPUT_GET, 'department', FILTER_SANITIZE_NUMBER_INT);
$filter_program = filter_input(INPUT_GET, 'program', FILTER_SANITIZE_NUMBER_INT);
$filter_course = filter_input(INPUT_GET, 'course', FILTER_SANITIZE_NUMBER_INT);
$filter_teacher = filter_input(INPUT_GET, 'teacher', FILTER_SANITIZE_NUMBER_INT);
$filter_student = filter_input(INPUT_GET, 'student', FILTER_SANITIZE_NUMBER_INT);
$filter_academic_year = filter_input(INPUT_GET, 'academic_year', FILTER_SANITIZE_STRING);
$filter_semester = filter_input(INPUT_GET, 'semester', FILTER_SANITIZE_STRING);

// الحصول على بيانات الأداء بناءً على الفلاتر ونوع التقرير
$performance_data = get_performance_report($db, $college_id, $filter_report_type, $filter_department, $filter_program, $filter_course, $filter_teacher, $filter_student, $filter_academic_year, $filter_semester);

// الحصول على قوائم الفلاتر (الأقسام، البرامج، المقررات، المعلمين، الطلاب، السنوات الدراسية)
$departments = get_college_departments($db, $college_id);
$programs = $filter_department ? get_department_programs($db, $filter_department) : [];
$courses = $filter_program ? get_program_courses($db, $filter_program) : ($filter_department ? get_department_courses($db, $filter_department) : []);
$teachers = get_college_teachers_list($db, $college_id, $filter_department);
$students = get_college_students_list($db, $college_id, $filter_department, $filter_program);
$academic_years = get_academic_years($db); // دالة افتراضية لجلب السنوات الدراسية

// إغلاق اتصال قاعدة البيانات
$dsn->close();

// دوال وهمية للحصول على البيانات (يجب استبدالها بالدوال الفعلية)
function get_performance_report($db, $college_id, $report_type, $department_id, $program_id, $course_id, $teacher_id, $student_id, $academic_year, $semester) {
    // في الواقع، يجب استرجاع بيانات الأداء من قاعدة البيانات بناءً على الفلاتر ونوع التقرير
    // بيانات وهمية للعرض
    $data = [];
    
    if ($report_type === 'student_performance') {
        $students_list = get_college_students_list($db, $college_id, $department_id, $program_id);
        $target_students = $student_id ? array_filter($students_list, function($s) use ($student_id) { return $s['id'] == $student_id; }) : $students_list;
        
        foreach ($target_students as $student) {
            $courses_taken = get_student_courses_taken($db, $student['id'], $academic_year, $semester);
            if ($course_id) {
                $courses_taken = array_filter($courses_taken, function($c) use ($course_id) { return $c['id'] == $course_id; });
            }
            
            $total_credits = 0;
            $total_points = 0;
            $courses_details = [];
            
            foreach ($courses_taken as $course) {
                $grade = ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'][rand(0, 8)];
                $points = grade_to_points($grade);
                $credits = $course['credits'];
                $total_credits += $credits;
                $total_points += $points * $credits;
                $courses_details[] = [
                    'course_id' => $course['id'],
                    'course_name' => $course['name'],
                    'credits' => $credits,
                    'grade' => $grade,
                    'points' => $points
                ];
            }
            
            $gpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;
            
            $data[] = [
                'student_id' => $student['id'],
                'student_name' => $student['name'],
                'department_name' => get_department_name($db, $student['department_id']), // دالة افتراضية
                'program_name' => get_program_name($db, $student['program_id']), // دالة افتراضية
                'gpa' => $gpa,
                'total_credits' => $total_credits,
                'courses_details' => $courses_details
            ];
        }
        // ترتيب الطلاب حسب المعدل التراكمي
        usort($data, function($a, $b) { return $b['gpa'] <=> $a['gpa']; });
        
    } elseif ($report_type === 'teacher_performance') {
        $teachers_list = get_college_teachers_list($db, $college_id, $department_id);
        $target_teachers = $teacher_id ? array_filter($teachers_list, function($t) use ($teacher_id) { return $t['id'] == $teacher_id; }) : $teachers_list;
        
        foreach ($target_teachers as $teacher) {
            $courses_taught = get_teacher_courses_taught($db, $teacher['id'], $academic_year, $semester);
            if ($course_id) {
                $courses_taught = array_filter($courses_taught, function($c) use ($course_id) { return $c['id'] == $course_id; });
            }
            
            $total_students = 0;
            $average_gpa = 0;
            $pass_rate = 0;
            $courses_details = [];
            $total_course_gpa = 0;
            $total_course_pass_rate = 0;
            $course_count = count($courses_taught);
            
            foreach ($courses_taught as $course) {
                $course_stats = get_course_stats($db, $course['id'], $academic_year, $semester); // دالة افتراضية
                $total_students += $course_stats['student_count'];
                $total_course_gpa += $course_stats['average_gpa'];
                $total_course_pass_rate += $course_stats['pass_rate'];
                $courses_details[] = [
                    'course_id' => $course['id'],
                    'course_name' => $course['name'],
                    'student_count' => $course_stats['student_count'],
                    'average_gpa' => $course_stats['average_gpa'],
                    'pass_rate' => $course_stats['pass_rate']
                ];
            }
            
            $average_gpa = $course_count > 0 ? round($total_course_gpa / $course_count, 2) : 0;
            $pass_rate = $course_count > 0 ? round($total_course_pass_rate / $course_count, 1) : 0;
            
            $data[] = [
                'teacher_id' => $teacher['id'],
                'teacher_name' => $teacher['name'],
                'department_name' => get_department_name($db, $teacher['department_id']), // دالة افتراضية
                'total_students' => $total_students,
                'average_gpa' => $average_gpa,
                'pass_rate' => $pass_rate,
                'courses_details' => $courses_details
            ];
        }
        // ترتيب المعلمين حسب متوسط المعدل
        usort($data, function($a, $b) { return $b['average_gpa'] <=> $a['average_gpa']; });
        
    } elseif ($report_type === 'course_performance') {
        $courses_list = get_college_courses($db, $college_id, $department_id, $program_id);
        $target_courses = $course_id ? array_filter($courses_list, function($c) use ($course_id) { return $c['id'] == $course_id; }) : $courses_list;
        
        foreach ($target_courses as $course) {
            $course_stats = get_course_stats($db, $course['id'], $academic_year, $semester); // دالة افتراضية
            $teacher_info = get_course_teacher($db, $course['id'], $academic_year, $semester); // دالة افتراضية
            
            $data[] = [
                'course_id' => $course['id'],
                'course_name' => $course['name'],
                'course_code' => $course['code'],
                'department_name' => get_department_name($db, $course['department_id']), // دالة افتراضية
                'teacher_name' => $teacher_info ? $teacher_info['name'] : t('not_assigned'),
                'student_count' => $course_stats['student_count'],
                'average_gpa' => $course_stats['average_gpa'],
                'pass_rate' => $course_stats['pass_rate'],
                'grade_distribution' => $course_stats['grade_distribution'] // ['A+' => 5, 'A' => 10, ...]
            ];
        }
        // ترتيب المقررات حسب متوسط المعدل
        usort($data, function($a, $b) { return $b['average_gpa'] <=> $a['average_gpa']; });
    }

    return $data;
}

// دوال مساعدة وهمية إضافية
function get_student_courses_taken($db, $student_id, $academic_year, $semester) {
    // استرجاع المقررات التي أخذها الطالب
    return [
        ['id' => 101, 'name' => 'مقدمة في البرمجة', 'credits' => 3],
        ['id' => 102, 'name' => 'هياكل البيانات', 'credits' => 3],
        ['id' => 201, 'name' => 'تحليل وتصميم النظم', 'credits' => 3]
    ];
}

function get_teacher_courses_taught($db, $teacher_id, $academic_year, $semester) {
    // استرجاع المقررات التي درسها المعلم
    if ($teacher_id == 1) return [['id' => 101, 'name' => 'مقدمة في البرمجة']];
    if ($teacher_id == 2) return [['id' => 102, 'name' => 'هياكل البيانات']];
    return [];
}

function get_course_stats($db, $course_id, $academic_year, $semester) {
    // حساب إحصائيات المقرر
    return [
        'student_count' => rand(20, 50),
        'average_gpa' => round(rand(250, 400) / 100, 2),
        'pass_rate' => round(rand(700, 950) / 10, 1),
        'grade_distribution' => [
            'A+' => rand(1, 5), 'A' => rand(3, 8), 'B+' => rand(5, 10), 'B' => rand(5, 12),
            'C+' => rand(3, 7), 'C' => rand(2, 6), 'D+' => rand(1, 4), 'D' => rand(0, 3), 'F' => rand(0, 2)
        ]
    ];
}

function get_course_teacher($db, $course_id, $academic_year, $semester) {
    // جلب معلومات مدرس المقرر
    if ($course_id == 101) return ['id' => 1, 'name' => 'د. محمد العمري'];
    if ($course_id == 102) return ['id' => 2, 'name' => 'د. سارة الأحمد'];
    return null;
}

function get_department_name($db, $department_id) {
    $depts = get_college_departments($db, 0); // Assume college_id 0 gets all
    foreach ($depts as $dept) {
        if ($dept['id'] == $department_id) return $dept['name'];
    }
    return t('unknown');
}

function get_program_name($db, $program_id) {
    // يجب جلب اسم البرنامج الفعلي
    if ($program_id == 1) return 'بكالوريوس علوم الحاسب';
    if ($program_id == 3) return 'بكالوريوس نظم المعلومات الإدارية';
    return t('unknown');
}

function get_academic_years($db) {
    // جلب السنوات الدراسية المتاحة
    return ['2023-2024', '2022-2023', '2021-2022'];
}

function grade_to_points($grade) {
    $points_map = ['A+' => 4.0, 'A' => 3.75, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D+' => 1.5, 'D' => 1.0, 'F' => 0.0];
    return isset($points_map[$grade]) ? $points_map[$grade] : 0.0;
}

// دوال وهمية أخرى من الصفحات السابقة
function get_college_departments($db, $college_id) {
    return [
        ['id' => 1, 'name' => 'قسم علوم الحاسب'],
        ['id' => 2, 'name' => 'قسم نظم المعلومات'],
        ['id' => 3, 'name' => 'قسم هندسة البرمجيات']
    ];
}

function get_department_programs($db, $department_id) {
    if ($department_id == 1) {
        return [['id' => 1, 'name' => 'بكالوريوس علوم الحاسب', 'department_id' => 1], ['id' => 2, 'name' => 'ماجستير علوم الحاسب', 'department_id' => 1]];
    } elseif ($department_id == 2) {
        return [['id' => 3, 'name' => 'بكالوريوس نظم المعلومات الإدارية', 'department_id' => 2]];
    } else {
        return [];
    }
}

function get_program_courses($db, $program_id) {
    if ($program_id == 1) {
        return [['id' => 101, 'name' => 'مقدمة في البرمجة', 'code' => 'CS101', 'department_id' => 1, 'program_id' => 1, 'credits' => 3],
                ['id' => 102, 'name' => 'هياكل البيانات', 'code' => 'CS201', 'department_id' => 1, 'program_id' => 1, 'credits' => 3]];
    } elseif ($program_id == 3) {
        return [['id' => 201, 'name' => 'تحليل وتصميم النظم', 'code' => 'IS301', 'department_id' => 2, 'program_id' => 3, 'credits' => 3],
                ['id' => 202, 'name' => 'قواعد البيانات', 'code' => 'IS305', 'department_id' => 2, 'program_id' => 3, 'credits' => 3]];
    } else {
        return [];
    }
}

function get_department_courses($db, $department_id) {
    if ($department_id == 1) {
        return [['id' => 101, 'name' => 'مقدمة في البرمجة', 'code' => 'CS101', 'department_id' => 1, 'program_id' => 1, 'credits' => 3],
                ['id' => 102, 'name' => 'هياكل البيانات', 'code' => 'CS201', 'department_id' => 1, 'program_id' => 1, 'credits' => 3],
                ['id' => 103, 'name' => 'الذكاء الاصطناعي', 'code' => 'CS405', 'department_id' => 1, 'program_id' => 1, 'credits' => 3]];
    } elseif ($department_id == 2) {
        return [['id' => 201, 'name' => 'تحليل وتصميم النظم', 'code' => 'IS301', 'department_id' => 2, 'program_id' => 3, 'credits' => 3],
                ['id' => 202, 'name' => 'قواعد البيانات', 'code' => 'IS305', 'department_id' => 2, 'program_id' => 3, 'credits' => 3]];
    } else {
        return [];
    }
}

function get_college_courses($db, $college_id, $department_id = null, $program_id = null) {
    $all_courses = [];
    $depts = get_college_departments($db, $college_id);
    foreach ($depts as $dept) {
        $progs = get_department_programs($db, $dept['id']);
        foreach ($progs as $prog) {
            $all_courses = array_merge($all_courses, get_program_courses($db, $prog['id']));
        }
    }
    if ($program_id) {
        return array_filter($all_courses, function($c) use ($program_id) { return $c['program_id'] == $program_id; });
    } elseif ($department_id) {
        return array_filter($all_courses, function($c) use ($department_id) { return $c['department_id'] == $department_id; });
    }
    return $all_courses;
}

function get_college_teachers_list($db, $college_id, $department_id = null) {
    $teachers = [
        ['id' => 1, 'name' => 'د. محمد العمري', 'department_id' => 1],
        ['id' => 2, 'name' => 'د. سارة الأحمد', 'department_id' => 1],
        ['id' => 3, 'name' => 'د. فهد السالم', 'department_id' => 2],
        ['id' => 4, 'name' => 'د. نورة القحطاني', 'department_id' => 2]
    ];
    if ($department_id) {
        return array_filter($teachers, function($t) use ($department_id) { return $t['department_id'] == $department_id; });
    }
    return $teachers;
}

function get_college_students_list($db, $college_id, $department_id = null, $program_id = null) {
    $students = [
        ['id' => 1001, 'name' => 'أحمد عبدالله', 'department_id' => 1, 'program_id' => 1],
        ['id' => 1002, 'name' => 'فاطمة خالد', 'department_id' => 1, 'program_id' => 1],
        ['id' => 1003, 'name' => 'علي حسن', 'department_id' => 1, 'program_id' => 2],
        ['id' => 1004, 'name' => 'مريم يوسف', 'department_id' => 2, 'program_id' => 3],
        ['id' => 1005, 'name' => 'خالد إبراهيم', 'department_id' => 2, 'program_id' => 3]
    ];
    if ($program_id) {
        return array_filter($students, function($s) use ($program_id) { return $s['program_id'] == $program_id; });
    } elseif ($department_id) {
        return array_filter($students, function($s) use ($department_id) { return $s['department_id'] == $department_id; });
    }
    return $students;
}

function get_college_admin_info($db, $admin_id) {
    return [
        'id' => $admin_id,
        'name' => 'د. عبدالله العمري',
        'email' => 'admin@example.com',
        'college_id' => 1,
        'profile_image' => 'assets/images/admin.jpg'
    ];
}

function get_college_info($db, $college_id) {
    return [
        'id' => $college_id,
        'name' => 'كلية علوم الحاسب والمعلومات',
        'code' => 'CS',
        'description' => 'كلية متخصصة في علوم الحاسب وتقنية المعلومات'
    ];
}

// دالة مساعدة لتنسيق التاريخ
function format_date($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo t('performance_reports'); ?></title>
    
    <!-- Bootstrap RTL إذا كانت اللغة العربية -->
    <?php if ($lang === 'ar'): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- خط Cairo -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- ملف CSS الرئيسي -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- ملف CSS للمظهر -->
    <link rel="stylesheet" href="assets/css/theme-<?php echo $theme; ?>.css">
    
    <style>
        /* نفس تنسيقات القائمة الجانبية وشريط التنقل العلوي من الصفحات السابقة */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            width: 250px;
        }
        
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
        }
        
        .sidebar-sticky {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .sidebar .nav-link i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .sidebar .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(0, 48, 73, 0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .sidebar .nav-link.active {
            border-left: none;
            border-right: 4px solid var(--primary-color);
        }
        
        .sidebar-heading {
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 1rem 1.5rem 0.5rem;
            color: var(--gray-color);
        }
        
        .sidebar-logo {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-logo img {
            height: 40px;
        }
        
        .sidebar-logo span {
            font-size: 1.2rem;
            font-weight: 700;
            margin-left: 0.5rem;
            color: var(--primary-color);
        }
        
        [dir="rtl"] .sidebar-logo span {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        
        .content {
            margin-left: 250px;
            padding: 2rem;
            transition: all 0.3s;
        }
        
        [dir="rtl"] .content {
            margin-left: 0;
            margin-right: 250px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .content {
                margin-left: 0;
                margin-right: 0;
            }
            
            [dir="rtl"] .content {
                margin-left: 0;
                margin-right: 0;
            }
            
            .sidebar-sticky {
                height: auto;
            }
        }
        
        .navbar-top {
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        
        .theme-dark .navbar-top {
            background-color: var(--dark-bg);
        }
        
        .navbar-top .navbar-nav {
            display: flex;
            align-items: center;
        }
        
        .navbar-top .nav-item {
            margin-left: 1rem;
        }
        
        [dir="rtl"] .navbar-top .nav-item {
            margin-left: 0;
            margin-right: 1rem;
        }
        
        .navbar-top .nav-link {
            color: var(--text-color);
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            position: relative;
        }
        
        .navbar-top .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .navbar-top .nav-link .badge {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 0.6rem;
        }
        
        [dir="rtl"] .navbar-top .nav-link .badge {
            right: auto;
            left: 0;
        }
        
        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-color);
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .toggle-sidebar {
                display: block;
            }
        }
        
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-dropdown .dropdown-toggle img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-dropdown .dropdown-menu {
            min-width: 200px;
            padding: 0;
        }
        
        .user-dropdown .dropdown-header {
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
        }
        
        .user-dropdown .dropdown-item {
            padding: 0.75rem 1rem;
        }
        
        .user-dropdown .dropdown-item i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        [dir="rtl"] .user-dropdown .dropdown-item i {
            margin-right: 0;
            margin-left: 0.5rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: var(--gray-color);
        }
        
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border: none;
        }
        
        .theme-dark .card {
            background-color: var(--dark-bg);
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .theme-dark .card-header {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #002135;
            border-color: #002135;
        }
        
        .btn-secondary {
            background-color: #669bbc;
            border-color: #669bbc;
        }
        
        .btn-secondary:hover {
            background-color: #5589a7;
            border-color: #5589a7;
        }
        
        /* تنسيقات خاصة بصفحة تقارير الأداء */
        .filter-form .row > div {
            margin-bottom: 1rem;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-bottom: 2rem;
        }
        
        .table-responsive {
            margin-top: 2rem;
        }
        
        .table th, .table td {
            vertical-align: middle;
        }
        
        .table thead th {
            font-weight: 600;
            background-color: rgba(0, 48, 73, 0.05);
            border-bottom-width: 1px;
        }
        
        .theme-dark .table thead th {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-color);
        }
        
        .theme-dark .table {
            color: var(--text-color);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-accent-bg: rgba(255, 255, 255, 0.03);
        }
        
        .gpa-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
        }
        
        .gpa-high {
            background-color: #28a745;
        }
        
        .gpa-medium {
            background-color: #ffc107;
            color: #333;
        }
        
        .gpa-low {
            background-color: #dc3545;
        }
        
        .pass-rate-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .pass-rate-high {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .pass-rate-medium {
            background-color: rgba(255, 193, 7, 0.1);
            color: #b8860b;
        }
        
        .pass-rate-low {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .accordion-button:not(.collapsed) {
            color: var(--primary-color);
            background-color: rgba(0, 48, 73, 0.05);
        }
        
        .theme-dark .accordion-button:not(.collapsed) {
            color: #669bbc;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .theme-dark .accordion-item {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .theme-dark .accordion-button {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .theme-dark .accordion-body {
            background-color: var(--dark-bg);
        }
        
        .grade-distribution-chart-container {
            height: 200px;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .page-link {
            color: var(--primary-color);
            border-color: rgba(0, 0, 0, 0.1);
        }
        
        .theme-dark .page-link {
            background-color: var(--dark-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-item.disabled .page-link {
            color: var(--gray-color);
        }
        
        .theme-dark .page-item.disabled .page-link {
            background-color: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="theme-<?php echo $theme; ?>">
    <!-- القائمة الجانبية -->
    <nav class="sidebar bg-white">
        <div class="sidebar-sticky">
            <div class="sidebar-logo">
                <img src="assets/images/logo<?php echo $theme === 'dark' ? '-white' : ''; ?>.png" alt="<?php echo SITE_NAME; ?>">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link" href="college_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_departments.php">
                        <i class="fas fa-building"></i> <?php echo t('departments'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_programs.php">
                        <i class="fas fa-graduation-cap"></i> <?php echo t('academic_programs'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_courses.php">
                        <i class="fas fa-book"></i> <?php echo t('courses'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_teachers.php">
                        <i class="fas fa-chalkboard-teacher"></i> <?php echo t('teachers'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_students.php">
                        <i class="fas fa-user-graduate"></i> <?php echo t('students'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_schedule.php">
                        <i class="fas fa-calendar-alt"></i> <?php echo t('schedule'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('reports'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="college_reports_academic.php">
                        <i class="fas fa-chart-line"></i> <?php echo t('academic_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_reports_attendance.php">
                        <i class="fas fa-clipboard-check"></i> <?php echo t('attendance_reports'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="college_reports_performance.php">
                        <i class="fas fa-chart-bar"></i> <?php echo t('performance_reports'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('communication'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="college_announcements.php">
                        <i class="fas fa-bullhorn"></i> <?php echo t('announcements'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_messages.php">
                        <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                    </a>
                </li>
                
                <li class="sidebar-heading"><?php echo t('settings'); ?></li>
                
                <li class="nav-item">
                    <a class="nav-link" href="college_profile.php">
                        <i class="fas fa-university"></i> <?php echo t('college_profile'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="college_settings.php">
                        <i class="fas fa-cog"></i> <?php echo t('settings'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- المحتوى الرئيسي -->
    <div class="content">
        <!-- شريط التنقل العلوي -->
        <nav class="navbar-top">
            <button class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="navbar-nav">
                <!-- نفس عناصر شريط التنقل العلوي من الصفحات السابقة -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger">5</span>
                    </a>
                    <!-- قائمة الإشعارات -->
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                        <span class="badge bg-success">3</span>
                    </a>
                    <!-- قائمة الرسائل -->
                </li>
                <li class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $admin['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="<?php echo $admin['name']; ?>">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><?php echo $admin['name']; ?></h6>
                            <small><?php echo t('college_admin'); ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="college_profile.php">
                            <i class="fas fa-university"></i> <?php echo t('college_profile'); ?>
                        </a>
                        <a class="dropdown-item" href="college_settings.php">
                            <i class="fas fa-cog"></i> <?php echo t('settings'); ?>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <div class="dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                            <li><a class="dropdown-item" href="?lang=en">English</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <button class="btn btn-link nav-link" id="themeToggle">
                        <i class="fas <?php echo $theme === 'light' ? 'fa-moon' : 'fa-sun'; ?>"></i>
                    </button>
                </li>
            </ul>
        </nav>
        
        <!-- عنوان الصفحة -->
        <div class="page-header mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title"><?php echo t('performance_reports'); ?></h1>
                    <p class="page-subtitle"><?php echo t('analyze_performance_data'); ?></p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary me-2">
                        <i class="fas fa-print me-1"></i> <?php echo t('print_report'); ?>
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> <?php echo t('export_to_excel'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- نموذج الفلترة -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i> <?php echo t('filter_options'); ?></h5>
            </div>
            <div class="card-body">
                <form action="" method="get" class="filter-form">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="report_type" class="form-label"><?php echo t('report_type'); ?></label>
                            <select class="form-select" id="report_type" name="report_type">
                                <option value="student_performance" <?php echo $filter_report_type === 'student_performance' ? 'selected' : ''; ?>><?php echo t('student_performance'); ?></option>
                                <option value="teacher_performance" <?php echo $filter_report_type === 'teacher_performance' ? 'selected' : ''; ?>><?php echo t('teacher_performance'); ?></option>
                                <option value="course_performance" <?php echo $filter_report_type === 'course_performance' ? 'selected' : ''; ?>><?php echo t('course_performance'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="department" class="form-label"><?php echo t('department'); ?></label>
                            <select class="form-select" id="department" name="department">
                                <option value=""><?php echo t('all_departments'); ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>><?php echo $dept['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="program" class="form-label"><?php echo t('academic_program'); ?></label>
                            <select class="form-select" id="program" name="program" <?php echo empty($programs) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_programs'); ?></option>
                                <?php foreach ($programs as $prog): ?>
                                    <option value="<?php echo $prog['id']; ?>" <?php echo $filter_program == $prog['id'] ? 'selected' : ''; ?>><?php echo $prog['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="course" class="form-label"><?php echo t('course'); ?></label>
                            <select class="form-select" id="course" name="course" <?php echo empty($courses) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_courses'); ?></option>
                                <?php foreach ($courses as $crs): ?>
                                    <option value="<?php echo $crs['id']; ?>" <?php echo $filter_course == $crs['id'] ? 'selected' : ''; ?>><?php echo $crs['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3" id="teacherFilterContainer" style="display: <?php echo $filter_report_type === 'teacher_performance' ? 'block' : 'none'; ?>;">
                            <label for="teacher" class="form-label"><?php echo t('teacher'); ?></label>
                            <select class="form-select" id="teacher" name="teacher" <?php echo empty($teachers) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_teachers'); ?></option>
                                <?php foreach ($teachers as $teach): ?>
                                    <option value="<?php echo $teach['id']; ?>" <?php echo $filter_teacher == $teach['id'] ? 'selected' : ''; ?>><?php echo $teach['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3" id="studentFilterContainer" style="display: <?php echo $filter_report_type === 'student_performance' ? 'block' : 'none'; ?>;">
                            <label for="student" class="form-label"><?php echo t('student'); ?></label>
                            <select class="form-select" id="student" name="student" <?php echo empty($students) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo t('all_students'); ?></option>
                                <?php foreach ($students as $stud): ?>
                                    <option value="<?php echo $stud['id']; ?>" <?php echo $filter_student == $stud['id'] ? 'selected' : ''; ?>><?php echo $stud['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="academic_year" class="form-label"><?php echo t('academic_year'); ?></label>
                            <select class="form-select" id="academic_year" name="academic_year">
                                <option value=""><?php echo t('all_years'); ?></option>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $filter_academic_year == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="semester" class="form-label"><?php echo t('semester'); ?></label>
                            <select class="form-select" id="semester" name="semester">
                                <option value=""><?php echo t('all_semesters'); ?></option>
                                <option value="1" <?php echo $filter_semester == '1' ? 'selected' : ''; ?>><?php echo t('first_semester'); ?></option>
                                <option value="2" <?php echo $filter_semester == '2' ? 'selected' : ''; ?>><?php echo t('second_semester'); ?></option>
                                <option value="3" <?php echo $filter_semester == '3' ? 'selected' : ''; ?>><?php echo t('summer_semester'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> <?php echo t('apply_filters'); ?>
                        </button>
                        <a href="college_reports_performance.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times me-1"></i> <?php echo t('reset_filters'); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- عرض التقرير -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i> 
                    <?php 
                    if ($filter_report_type === 'student_performance') echo t('student_performance_report');
                    elseif ($filter_report_type === 'teacher_performance') echo t('teacher_performance_report');
                    else echo t('course_performance_report');
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($performance_data)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p><?php echo t('no_performance_data_found'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <?php if ($filter_report_type === 'student_performance'): ?>
                                    <tr>
                                        <th><?php echo t('student_name'); ?></th>
                                        <th><?php echo t('department'); ?></th>
                                        <th><?php echo t('academic_program'); ?></th>
                                        <th><?php echo t('gpa'); ?></th>
                                        <th><?php echo t('total_credits'); ?></th>
                                        <th><?php echo t('actions'); ?></th>
                                    </tr>
                                <?php elseif ($filter_report_type === 'teacher_performance'): ?>
                                    <tr>
                                        <th><?php echo t('teacher_name'); ?></th>
                                        <th><?php echo t('department'); ?></th>
                                        <th><?php echo t('total_students_taught'); ?></th>
                                        <th><?php echo t('average_gpa_courses'); ?></th>
                                        <th><?php echo t('average_pass_rate'); ?></th>
                                        <th><?php echo t('actions'); ?></th>
                                    </tr>
                                <?php else: // course_performance ?>
                                    <tr>
                                        <th><?php echo t('course_name'); ?></th>
                                        <th><?php echo t('course_code'); ?></th>
                                        <th><?php echo t('department'); ?></th>
                                        <th><?php echo t('teacher'); ?></th>
                                        <th><?php echo t('student_count'); ?></th>
                                        <th><?php echo t('average_gpa'); ?></th>
                                        <th><?php echo t('pass_rate'); ?></th>
                                        <th><?php echo t('actions'); ?></th>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php foreach ($performance_data as $index => $record): ?>
                                    <tr>
                                        <?php if ($filter_report_type === 'student_performance'): ?>
                                            <td><?php echo $record['student_name']; ?></td>
                                            <td><?php echo $record['department_name']; ?></td>
                                            <td><?php echo $record['program_name']; ?></td>
                                            <td>
                                                <span class="gpa-badge <?php echo $record['gpa'] >= 3.5 ? 'gpa-high' : ($record['gpa'] >= 2.5 ? 'gpa-medium' : 'gpa-low'); ?>">
                                                    <?php echo number_format($record['gpa'], 2); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $record['total_credits']; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#studentDetails<?php echo $index; ?>" aria-expanded="false" aria-controls="studentDetails<?php echo $index; ?>" title="<?php echo t('view_details'); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="student_profile.php?id=<?php echo $record['student_id']; ?>" class="btn btn-sm btn-outline-primary ms-1" title="<?php echo t('view_profile'); ?>">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                            </td>
                                        <?php elseif ($filter_report_type === 'teacher_performance'): ?>
                                            <td><?php echo $record['teacher_name']; ?></td>
                                            <td><?php echo $record['department_name']; ?></td>
                                            <td><?php echo $record['total_students']; ?></td>
                                            <td>
                                                <span class="gpa-badge <?php echo $record['average_gpa'] >= 3.5 ? 'gpa-high' : ($record['average_gpa'] >= 2.5 ? 'gpa-medium' : 'gpa-low'); ?>">
                                                    <?php echo number_format($record['average_gpa'], 2); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="pass-rate-badge <?php echo $record['pass_rate'] >= 85 ? 'pass-rate-high' : ($record['pass_rate'] >= 70 ? 'pass-rate-medium' : 'pass-rate-low'); ?>">
                                                    <?php echo number_format($record['pass_rate'], 1); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#teacherDetails<?php echo $index; ?>" aria-expanded="false" aria-controls="teacherDetails<?php echo $index; ?>" title="<?php echo t('view_details'); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="teacher_profile.php?id=<?php echo $record['teacher_id']; ?>" class="btn btn-sm btn-outline-primary ms-1" title="<?php echo t('view_profile'); ?>">
                                                    <i class="fas fa-user-tie"></i>
                                                </a>
                                            </td>
                                        <?php else: // course_performance ?>
                                            <td><?php echo $record['course_name']; ?></td>
                                            <td><?php echo $record['course_code']; ?></td>
                                            <td><?php echo $record['department_name']; ?></td>
                                            <td><?php echo $record['teacher_name']; ?></td>
                                            <td><?php echo $record['student_count']; ?></td>
                                            <td>
                                                <span class="gpa-badge <?php echo $record['average_gpa'] >= 3.5 ? 'gpa-high' : ($record['average_gpa'] >= 2.5 ? 'gpa-medium' : 'gpa-low'); ?>">
                                                    <?php echo number_format($record['average_gpa'], 2); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="pass-rate-badge <?php echo $record['pass_rate'] >= 85 ? 'pass-rate-high' : ($record['pass_rate'] >= 70 ? 'pass-rate-medium' : 'pass-rate-low'); ?>">
                                                    <?php echo number_format($record['pass_rate'], 1); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#courseDetails<?php echo $index; ?>" aria-expanded="false" aria-controls="courseDetails<?php echo $index; ?>" title="<?php echo t('view_details'); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="course_details.php?id=<?php echo $record['course_id']; ?>" class="btn btn-sm btn-outline-primary ms-1" title="<?php echo t('view_course_page'); ?>">
                                                    <i class="fas fa-book-open"></i>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <!-- تفاصيل إضافية (منسدلة) -->
                                    <tr>
                                        <td colspan="<?php echo $filter_report_type === 'student_performance' ? 6 : ($filter_report_type === 'teacher_performance' ? 6 : 8); ?>" class="p-0">
                                            <div class="collapse" id="<?php echo $filter_report_type === 'student_performance' ? 'studentDetails' : ($filter_report_type === 'teacher_performance' ? 'teacherDetails' : 'courseDetails'); ?><?php echo $index; ?>">
                                                <div class="p-3 bg-light theme-dark:bg-dark-alt">
                                                    <?php if ($filter_report_type === 'student_performance'): ?>
                                                        <h6><?php echo t('courses_taken'); ?>:</h6>
                                                        <ul class="list-group list-group-flush">
                                                            <?php foreach ($record['courses_details'] as $course_detail): ?>
                                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                                    <?php echo $course_detail['course_name']; ?> (<?php echo $course_detail['credits']; ?> <?php echo t('credits'); ?>)
                                                                    <span class="badge bg-secondary rounded-pill"><?php echo t('grade'); ?>: <?php echo $course_detail['grade']; ?></span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php elseif ($filter_report_type === 'teacher_performance'): ?>
                                                        <h6><?php echo t('courses_taught'); ?>:</h6>
                                                        <ul class="list-group list-group-flush">
                                                            <?php foreach ($record['courses_details'] as $course_detail): ?>
                                                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                                                    <?php echo $course_detail['course_name']; ?>
                                                                    <div>
                                                                        <span class="badge bg-info rounded-pill me-1"><?php echo t('students'); ?>: <?php echo $course_detail['student_count']; ?></span>
                                                                        <span class="badge bg-warning text-dark rounded-pill me-1"><?php echo t('avg_gpa'); ?>: <?php echo number_format($course_detail['average_gpa'], 2); ?></span>
                                                                        <span class="badge bg-success rounded-pill"><?php echo t('pass_rate'); ?>: <?php echo number_format($course_detail['pass_rate'], 1); ?>%</span>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: // course_performance ?>
                                                        <h6><?php echo t('grade_distribution'); ?>:</h6>
                                                        <div class="chart-container grade-distribution-chart-container">
                                                            <canvas id="gradeDistChart<?php echo $index; ?>"></canvas>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- ترقيم الصفحات -->
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="<?php echo t('performance_pagination'); ?>">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- ملف JavaScript الرئيسي -->
    <script src="assets/js/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تبديل القائمة الجانبية في الشاشات الصغيرة
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('d-none');
            });
            
            // زر تبديل المظهر
            document.getElementById('themeToggle').addEventListener('click', function() {
                const currentTheme = document.body.className.includes('theme-dark') ? 'dark' : 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.cookie = `theme=${newTheme}; path=/; max-age=31536000`;
                document.body.className = `theme-${newTheme}`;
                
                this.innerHTML = `<i class="fas fa-${newTheme === 'dark' ? 'sun' : 'moon'}"></i>`;
                
                // إعادة رسم الرسوم البيانية عند تغيير المظهر
                renderGradeDistributionCharts();
            });
            
            // تحديث قوائم الفلاتر عند تغيير القسم (نفس الكود من صفحة تقارير الحضور)
            document.getElementById('department').addEventListener('change', function() {
                const departmentId = this.value;
                const programSelect = document.getElementById('program');
                const courseSelect = document.getElementById('course');
                const teacherSelect = document.getElementById('teacher');
                const studentSelect = document.getElementById('student');
                
                // تحديث البرامج (AJAX needed in real app)
                programSelect.innerHTML = '<option value=""><?php echo t('all_programs'); ?></option>';
                const programs = <?php echo json_encode(get_department_programs(null, 0)); ?>; // Fetch all for demo
                programs.forEach(prog => {
                    if (!departmentId || prog.department_id == departmentId) {
                        const option = document.createElement('option');
                        option.value = prog.id;
                        option.text = prog.name;
                        programSelect.appendChild(option);
                    }
                });
                programSelect.disabled = !departmentId && !programs.length;
                programSelect.value = '<?php echo $filter_program; ?>';

                // تحديث المقررات (AJAX needed in real app)
                courseSelect.innerHTML = '<option value=""><?php echo t('all_courses'); ?></option>';
                const courses = <?php echo json_encode(get_college_courses(null, 0)); ?>; // Fetch all for demo
                courses.forEach(crs => {
                    if (!departmentId || crs.department_id == departmentId) {
                        const option = document.createElement('option');
                        option.value = crs.id;
                        option.text = crs.name;
                        courseSelect.appendChild(option);
                    }
                });
                courseSelect.disabled = !departmentId && !courses.length;
                courseSelect.value = '<?php echo $filter_course; ?>';

                // تحديث المعلمين (AJAX needed in real app)
                teacherSelect.innerHTML = '<option value=""><?php echo t('all_teachers'); ?></option>';
                const teachers = <?php echo json_encode(get_college_teachers_list(null, 0)); ?>; // Fetch all for demo
                teachers.forEach(teach => {
                    if (!departmentId || teach.department_id == departmentId) {
                        const option = document.createElement('option');
                        option.value = teach.id;
                        option.text = teach.name;
                        teacherSelect.appendChild(option);
                    }
                });
                teacherSelect.disabled = !departmentId && !teachers.length;
                teacherSelect.value = '<?php echo $filter_teacher; ?>';

                // تحديث الطلاب (AJAX needed in real app)
                studentSelect.innerHTML = '<option value=""><?php echo t('all_students'); ?></option>';
                const students = <?php echo json_encode(get_college_students_list(null, 0)); ?>; // Fetch all for demo
                students.forEach(stud => {
                    if (!departmentId || stud.department_id == departmentId) {
                        const option = document.createElement('option');
                        option.value = stud.id;
                        option.text = stud.name;
                        studentSelect.appendChild(option);
                    }
                });
                studentSelect.disabled = !departmentId && !students.length;
                studentSelect.value = '<?php echo $filter_student; ?>';
            });
            
            // تحديث المقررات والطلاب عند تغيير البرنامج (نفس الكود من صفحة تقارير الحضور)
            document.getElementById('program').addEventListener('change', function() {
                const programId = this.value;
                const courseSelect = document.getElementById('course');
                const studentSelect = document.getElementById('student');
                const departmentId = document.getElementById('department').value;

                // تحديث المقررات (AJAX needed)
                courseSelect.innerHTML = '<option value=""><?php echo t('all_courses'); ?></option>';
                const courses = <?php echo json_encode(get_college_courses(null, 0)); ?>; // Fetch all for demo
                courses.forEach(crs => {
                    if ((!programId && (!departmentId || crs.department_id == departmentId)) || (programId && crs.program_id == programId)) {
                        const option = document.createElement('option');
                        option.value = crs.id;
                        option.text = crs.name;
                        courseSelect.appendChild(option);
                    }
                });
                courseSelect.disabled = !programId && !departmentId && !courses.length;
                courseSelect.value = '<?php echo $filter_course; ?>';

                // تحديث الطلاب (AJAX needed)
                studentSelect.innerHTML = '<option value=""><?php echo t('all_students'); ?></option>';
                const students = <?php echo json_encode(get_college_students_list(null, 0)); ?>; // Fetch all for demo
                students.forEach(stud => {
                    if ((!programId && (!departmentId || stud.department_id == departmentId)) || (programId && stud.program_id == programId)) {
                        const option = document.createElement('option');
                        option.value = stud.id;
                        option.text = stud.name;
                        studentSelect.appendChild(option);
                    }
                });
                studentSelect.disabled = !programId && !departmentId && !students.length;
                studentSelect.value = '<?php echo $filter_student; ?>';
            });
            
            // تبديل عرض فلاتر الطالب/المعلم بناءً على نوع التقرير
            document.getElementById('report_type').addEventListener('change', function() {
                const type = this.value;
                document.getElementById('studentFilterContainer').style.display = type === 'student_performance' ? 'block' : 'none';
                document.getElementById('teacherFilterContainer').style.display = type === 'teacher_performance' ? 'block' : 'none';
                // قد تحتاج لإخفاء/إظهار فلاتر أخرى بناءً على نوع التقرير
                document.getElementById('program').closest('.col-md-3').style.display = type === 'teacher_performance' ? 'none' : 'block';
                document.getElementById('student').closest('.col-md-3').style.display = type === 'student_performance' ? 'block' : 'none';
                document.getElementById('teacher').closest('.col-md-3').style.display = type === 'teacher_performance' ? 'block' : 'none';
            });
            
            // استدعاء لتحديث الفلاتر عند تحميل الصفحة لأول مرة
            document.getElementById('department').dispatchEvent(new Event('change'));
            document.getElementById('program').dispatchEvent(new Event('change'));
            document.getElementById('report_type').dispatchEvent(new Event('change'));
            
            // بيانات الأداء للرسوم البيانية
            const performanceData = <?php echo json_encode($performance_data); ?>;
            const gradeLabels = ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'];
            const gradeColors = [
                '#28a745', '#5cb85c', '#8bc34a', '#cddc39', 
                '#ffeb3b', '#ffc107', '#ff9800', '#f57c00', '#dc3545'
            ];
            let gradeDistributionCharts = {};

            function renderGradeDistributionCharts() {
                const theme = document.body.className.includes('theme-dark') ? 'dark' : 'light';
                const textColor = theme === 'dark' ? '#ffffff' : '#333333';
                const gridColor = theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

                // تدمير الرسوم البيانية القديمة
                Object.values(gradeDistributionCharts).forEach(chart => chart.destroy());
                gradeDistributionCharts = {};

                if ('<?php echo $filter_report_type; ?>' === 'course_performance') {
                    performanceData.forEach((record, index) => {
                        const ctx = document.getElementById(`gradeDistChart${index}`)?.getContext('2d');
                        if (ctx && record.grade_distribution) {
                            const distributionData = gradeLabels.map(label => record.grade_distribution[label] || 0);
                            
                            gradeDistributionCharts[index] = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: gradeLabels,
                                    datasets: [{
                                        label: '<?php echo t('number_of_students'); ?>',
                                        data: distributionData,
                                        backgroundColor: gradeColors,
                                        borderColor: theme === 'dark' ? 'var(--dark-bg)' : '#ffffff',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    indexAxis: 'y',
                                    scales: {
                                        x: {
                                            beginAtZero: true,
                                            ticks: {
                                                color: textColor,
                                                stepSize: 1
                                            },
                                            grid: {
                                                color: gridColor
                                            }
                                        },
                                        y: {
                                            ticks: {
                                                color: textColor
                                            },
                                            grid: {
                                                display: false
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return `${context.dataset.label}: ${context.raw}`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            }

            // رسم رسوم توزيع الدرجات عند تحميل الصفحة
            renderGradeDistributionCharts();

            // إعادة رسم الرسوم عند فتح/إغلاق التفاصيل المنسدلة
            document.querySelectorAll('.collapse').forEach(collapseElement => {
                collapseElement.addEventListener('shown.bs.collapse', function () {
                    const chartId = this.querySelector('canvas')?.id;
                    if (chartId && chartId.startsWith('gradeDistChart')) {
                        // قد تحتاج لإعادة الرسم هنا إذا كان الرسم البياني لا يظهر بشكل صحيح
                        // renderGradeDistributionCharts(); // أو استهداف الرسم البياني المحدد
                    }
                });
            });
        });
    </script>
</body>
</html>
