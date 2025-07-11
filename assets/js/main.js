<?php
/**
 * الملف الرئيسي لنظام JavaScript في UniverBoard
 * يحتوي على الدوال المشتركة المستخدمة في جميع صفحات النظام
 */

// عند تحميل المستند
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة التلميحات (Tooltips)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // تهيئة النوافذ المنبثقة (Popovers)
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // تبديل اللغة
    setupLanguageSwitcher();
    
    // تبديل المظهر
    setupThemeSwitcher();
    
    // إعداد التحقق من صحة النماذج
    setupFormValidation();
    
    // إعداد الرسوم البيانية إذا كانت موجودة
    setupCharts();
    
    // إعداد الجداول القابلة للفرز والبحث
    setupDataTables();
    
    // إعداد محرر النصوص المنسق إذا كان موجوداً
    setupRichTextEditor();
    
    // إعداد تحميل الملفات
    setupFileUpload();
    
    // إعداد الإشعارات
    setupNotifications();
});

/**
 * إعداد مبدل اللغة
 */
function setupLanguageSwitcher() {
    const langLinks = document.querySelectorAll("a[href^='?lang=']");
    langLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            const lang = this.href.split("=")[1].split("&")[0];
            switchLanguage(lang);
        });
    });
}

/**
 * تبديل اللغة
 * @param {string} lang - رمز اللغة (ar أو en)
 */
function switchLanguage(lang) {
    document.cookie = "lang=" + lang + "; path=/; max-age=31536000";
    
    // الحفاظ على معلمات URL الحالية
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('lang', lang);
    
    // إعادة تحميل الصفحة مع المعلمات الجديدة
    window.location.search = urlParams.toString();
}

/**
 * إعداد مبدل المظهر
 */
function setupThemeSwitcher() {
    const themeToggle = document.getElementById("themeToggle");
    if (themeToggle) {
        themeToggle.addEventListener("click", function() {
            const currentTheme = document.body.className.includes("theme-light") ? "light" : "dark";
            const newTheme = currentTheme === "light" ? "dark" : "light";
            switchTheme(newTheme);
        });
    }
}

/**
 * تبديل المظهر
 * @param {string} theme - اسم المظهر (light أو dark)
 */
function switchTheme(theme) {
    document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
    document.body.className = "theme-" + theme;
    
    // تغيير أيقونة الزر
    const themeIcon = document.querySelector("#themeToggle i");
    if (themeIcon) {
        themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    // تحميل ملف CSS المناسب
    const themeLink = document.querySelector("link[href^='assets/css/theme-']");
    if (themeLink) {
        themeLink.href = "assets/css/theme-" + theme + ".css";
    }
    
    // تخزين المظهر في التخزين المحلي أيضاً
    localStorage.setItem('theme', theme);
}

/**
 * إعداد التحقق من صحة النماذج
 */
function setupFormValidation() {
    // تحديد جميع النماذج التي تحتاج إلى تحقق
    const forms = document.querySelectorAll('.needs-validation');
    
    // حلقة على كل نموذج وإضافة مستمع الحدث
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // إعداد التحقق من تطابق كلمات المرور
    const passwordConfirmFields = document.querySelectorAll('input[data-match-password]');
    passwordConfirmFields.forEach(field => {
        field.addEventListener('input', function() {
            const passwordField = document.querySelector(this.getAttribute('data-match-password'));
            if (passwordField && this.value !== passwordField.value) {
                this.setCustomValidity('كلمات المرور غير متطابقة');
            } else {
                this.setCustomValidity('');
            }
        });
    });
}

/**
 * إعداد الرسوم البيانية
 */
function setupCharts() {
    // التحقق من وجود عنصر الرسم البياني
    const chartElements = document.querySelectorAll('[data-chart]');
    if (chartElements.length === 0) return;
    
    // التحقق من وجود مكتبة Chart.js
    if (typeof Chart === 'undefined') {
        console.warn('مكتبة Chart.js غير موجودة. الرسوم البيانية لن تعمل.');
        return;
    }
    
    // تهيئة كل رسم بياني
    chartElements.forEach(element => {
        const chartType = element.getAttribute('data-chart');
        const chartData = JSON.parse(element.getAttribute('data-chart-data'));
        const chartOptions = JSON.parse(element.getAttribute('data-chart-options') || '{}');
        
        // تعيين الألوان المناسبة للمظهر الحالي
        const theme = document.body.className.includes('theme-light') ? 'light' : 'dark';
        const textColor = theme === 'light' ? '#212529' : '#e0e0e0';
        const gridColor = theme === 'light' ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)';
        
        // دمج خيارات الرسم البياني مع الخيارات الافتراضية
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: textColor
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                }
            }
        };
        
        const mergedOptions = { ...defaultOptions, ...chartOptions };
        
        // إنشاء الرسم البياني
        new Chart(element, {
            type: chartType,
            data: chartData,
            options: mergedOptions
        });
    });
}

/**
 * إعداد الجداول القابلة للفرز والبحث
 */
function setupDataTables() {
    // التحقق من وجود عنصر الجدول
    const dataTableElements = document.querySelectorAll('[data-datatable]');
    if (dataTableElements.length === 0) return;
    
    // التحقق من وجود مكتبة DataTables
    if (typeof $.fn.DataTable === 'undefined') {
        console.warn('مكتبة DataTables غير موجودة. الجداول القابلة للفرز لن تعمل.');
        return;
    }
    
    // تهيئة كل جدول
    dataTableElements.forEach(element => {
        const options = JSON.parse(element.getAttribute('data-datatable-options') || '{}');
        
        // تحديد اللغة الحالية
        const lang = document.documentElement.lang || 'ar';
        
        // دمج خيارات الجدول مع الخيارات الافتراضية
        const defaultOptions = {
            responsive: true,
            language: lang === 'ar' ? {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/ar.json'
            } : undefined
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        // إنشاء الجدول
        $(element).DataTable(mergedOptions);
    });
}

/**
 * إعداد محرر النصوص المنسق
 */
function setupRichTextEditor() {
    // التحقق من وجود عنصر المحرر
    const editorElements = document.querySelectorAll('[data-editor]');
    if (editorElements.length === 0) return;
    
    // التحقق من وجود مكتبة TinyMCE
    if (typeof tinymce === 'undefined') {
        console.warn('مكتبة TinyMCE غير موجودة. محرر النصوص المنسق لن يعمل.');
        return;
    }
    
    // تهيئة كل محرر
    editorElements.forEach(element => {
        const options = JSON.parse(element.getAttribute('data-editor-options') || '{}');
        
        // تحديد اللغة الحالية
        const lang = document.documentElement.lang || 'ar';
        
        // دمج خيارات المحرر مع الخيارات الافتراضية
        const defaultOptions = {
            selector: '#' + element.id,
            directionality: lang === 'ar' ? 'rtl' : 'ltr',
            language: lang,
            plugins: 'link image table lists media',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
            menubar: false
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        // إنشاء المحرر
        tinymce.init(mergedOptions);
    });
}

/**
 * إعداد تحميل الملفات
 */
function setupFileUpload() {
    // التحقق من وجود عنصر تحميل الملفات
    const fileUploadElements = document.querySelectorAll('[data-file-upload]');
    if (fileUploadElements.length === 0) return;
    
    // تهيئة كل عنصر تحميل
    fileUploadElements.forEach(element => {
        const fileInput = element.querySelector('input[type="file"]');
        const fileLabel = element.querySelector('.custom-file-label');
        const filePreview = element.querySelector('.file-preview');
        
        if (!fileInput) return;
        
        // تحديث اسم الملف عند اختياره
        fileInput.addEventListener('change', function() {
            if (fileLabel) {
                fileLabel.textContent = this.files.length > 0 ? 
                    (this.files.length > 1 ? `${this.files.length} ملفات محددة` : this.files[0].name) : 
                    'اختر ملفاً';
            }
            
            // عرض معاينة للصور إذا كانت موجودة
            if (filePreview && this.files.length > 0 && this.files[0].type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    filePreview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" alt="معاينة الصورة">`;
                };
                reader.readAsDataURL(this.files[0]);
            } else if (filePreview) {
                filePreview.innerHTML = '';
            }
        });
    });
}

/**
 * إعداد الإشعارات
 */
function setupNotifications() {
    // التحقق من وجود عنصر الإشعارات
    const notificationElement = document.getElementById('notifications-dropdown');
    if (!notificationElement) return;
    
    // تحديث عدد الإشعارات غير المقروءة
    function updateNotificationCount() {
        const countElement = document.getElementById('notification-count');
        if (!countElement) return;
        
        // استدعاء API للحصول على عدد الإشعارات غير المقروءة
        fetch('api/notifications/count.php')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    countElement.textContent = data.count;
                    countElement.classList.remove('d-none');
                } else {
                    countElement.classList.add('d-none');
                }
            })
            .catch(error => console.error('خطأ في تحديث عدد الإشعارات:', error));
    }
    
    // تحميل الإشعارات
    function loadNotifications() {
        const notificationList = document.getElementById('notification-list');
        if (!notificationList) return;
        
        // عرض مؤشر التحميل
        notificationList.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';
        
        // استدعاء API للحصول على الإشعارات
        fetch('api/notifications/list.php')
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    let html = '';
                    data.notifications.forEach(notification => {
                        const isUnread = notification.is_read === 0 ? 'unread' : '';
                        html += `
                            <a href="${notification.link}" class="dropdown-item notification-item ${isUnread}">
                                <div class="d-flex align-items-center">
                                    <div class="notification-icon bg-${notification.type}">
                                        <i class="fas fa-${getNotificationIcon(notification.type)}"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p class="mb-1">${notification.title}</p>
                                        <small class="text-muted">${notification.time_ago}</small>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    html += `
                        <div class="dropdown-divider"></div>
                        <a href="notifications.php" class="dropdown-item text-center">عرض جميع الإشعارات</a>
                    `;
                    notificationList.innerHTML = html;
                } else {
                    notificationList.innerHTML = '<div class="text-center p-3">لا توجد إشعارات جديدة</div>';
                }
            })
            .catch(error => {
                console.error('خطأ في تحميل الإشعارات:', error);
                notificationList.innerHTML = '<div class="text-center p-3">حدث خطأ أثناء تحميل الإشعارات</div>';
            });
    }
    
    // الحصول على أيقونة الإشعار بناءً على النوع
    function getNotificationIcon(type) {
        switch (type) {
            case 'primary': return 'info-circle';
            case 'success': return 'check-circle';
            case 'danger': return 'exclamation-circle';
            case 'warning': return 'exclamation-triangle';
            default: return 'bell';
        }
    }
    
    // تحديث عدد الإشعارات عند تحميل الصفحة
    updateNotificationCount();
    
    // تحميل الإشعارات عند النقر على زر الإشعارات
    notificationElement.addEventListener('show.bs.dropdown', loadNotifications);
    
    // تحديث عدد الإشعارات كل دقيقة
    setInterval(updateNotificationCount, 60000);
}

/**
 * عرض رسالة تنبيه
 * @param {string} message - نص الرسالة
 * @param {string} type - نوع التنبيه (success, danger, warning, info)
 * @param {number} duration - مدة ظهور التنبيه بالمللي ثانية
 */
function showAlert(message, type = 'info', duration = 5000) {
    // إنشاء عنصر التنبيه
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type} alert-dismissible fade show custom-alert`;
    alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    `;
    
    // إضافة التنبيه إلى الصفحة
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.appendChild(alertElement);
    } else {
        // إنشاء حاوية التنبيهات إذا لم تكن موجودة
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'alert-container';
        container.appendChild(alertElement);
        document.body.appendChild(container);
    }
    
    // إضافة نمط CSS للتنبيه
    if (!document.getElementById('alert-styles')) {
        const style = document.createElement('style');
        style.id = 'alert-styles';
        style.textContent = `
            .alert-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 350px;
            }
            
            .custom-alert {
                margin-bottom: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            
            [dir="rtl"] .alert-container {
                right: auto;
                left: 20px;
            }
        `;
        document.head.appendChild(style);
    }
    
    // إزالة التنبيه بعد المدة المحددة
    if (duration > 0) {
        setTimeout(() => {
            alertElement.classList.remove('show');
            setTimeout(() => {
                alertElement.remove();
            }, 150);
        }, duration);
    }
    
    return alertElement;
}

/**
 * تأكيد العملية
 * @param {string} message - رسالة التأكيد
 * @param {Function} callback - الدالة التي سيتم استدعاؤها عند التأكيد
 * @param {string} title - عنوان مربع الحوار
 */
function confirmAction(message, callback, title = 'تأكيد العملية') {
    // التحقق من وجود مكتبة Bootstrap
    if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
        // استخدام تأكيد المتصفح الافتراضي إذا لم تكن مكتبة Bootstrap متوفرة
        if (confirm(message)) {
            callback();
        }
        return;
    }
    
    // إنشاء معرف فريد لمربع الحوار
    const modalId = 'confirm-modal-' + Math.random().toString(36).substr(2, 9);
    
    // إنشاء عنصر مربع الحوار
    const modalElement = document.createElement('div');
    modalElement.className = 'modal fade';
    modalElement.id = modalId;
    modalElement.tabIndex = -1;
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="${modalId}-confirm">تأكيد</button>
                </div>
            </div>
        </div>
    `;
    
    // إضافة مربع الحوار إلى الصفحة
    document.body.appendChild(modalElement);
    
    // إنشاء كائن مربع الحوار
    const modal = new bootstrap.Modal(modalElement);
    
    // إضافة مستمع الحدث لزر التأكيد
    document.getElementById(`${modalId}-confirm`).addEventListener('click', function() {
        modal.hide();
        callback();
    });
    
    // إضافة مستمع الحدث لإزالة مربع الحوار من DOM بعد إغلاقه
    modalElement.addEventListener('hidden.bs.modal', function() {
        modalElement.remove();
    });
    
    // عرض مربع الحوار
    modal.show();
}

/**
 * تنسيق التاريخ والوقت
 * @param {string|Date} date - التاريخ المراد تنسيقه
 * @param {string} format - صيغة التنسيق (datetime, date, time)
 * @param {string} locale - اللغة (ar, en)
 * @returns {string} - التاريخ المنسق
 */
function formatDateTime(date, format = 'datetime', locale = 'ar') {
    const dateObj = date instanceof Date ? date : new Date(date);
    
    // التحقق من صحة التاريخ
    if (isNaN(dateObj.getTime())) {
        return 'تاريخ غير صالح';
    }
    
    // خيارات التنسيق
    const options = {
        datetime: { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        },
        date: { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        },
        time: { 
            hour: '2-digit', 
            minute: '2-digit' 
        }
    };
    
    // تنسيق التاريخ حسب اللغة والصيغة المطلوبة
    return dateObj.toLocaleDateString(locale === 'ar' ? 'ar-SA' : 'en-US', options[format]);
}

/**
 * حساب الوقت المنقضي
 * @param {string|Date} date - التاريخ المراد حساب الوقت المنقضي منه
 * @param {string} locale - اللغة (ar, en)
 * @returns {string} - الوقت المنقضي
 */
function timeAgo(date, locale = 'ar') {
    const dateObj = date instanceof Date ? date : new Date(date);
    const now = new Date();
    const diffInSeconds = Math.floor((now - dateObj) / 1000);
    
    // التحقق من صحة التاريخ
    if (isNaN(dateObj.getTime())) {
        return 'تاريخ غير صالح';
    }
    
    // تحديد النصوص حسب اللغة
    const texts = {
        ar: {
            now: 'الآن',
            seconds: 'منذ {count} ثانية',
            minute: 'منذ دقيقة',
            minutes: 'منذ {count} دقائق',
            hour: 'منذ ساعة',
            hours: 'منذ {count} ساعات',
            day: 'منذ يوم',
            days: 'منذ {count} أيام',
            week: 'منذ أسبوع',
            weeks: 'منذ {count} أسابيع',
            month: 'منذ شهر',
            months: 'منذ {count} أشهر',
            year: 'منذ سنة',
            years: 'منذ {count} سنوات'
        },
        en: {
            now: 'just now',
            seconds: '{count} seconds ago',
            minute: 'a minute ago',
            minutes: '{count} minutes ago',
            hour: 'an hour ago',
            hours: '{count} hours ago',
            day: 'a day ago',
            days: '{count} days ago',
            week: 'a week ago',
            weeks: '{count} weeks ago',
            month: 'a month ago',
            months: '{count} months ago',
            year: 'a year ago',
            years: '{count} years ago'
        }
    };
    
    const text = texts[locale] || texts.ar;
    
    // حساب الوقت المنقضي
    if (diffInSeconds < 5) {
        return text.now;
    } else if (diffInSeconds < 60) {
        return text.seconds.replace('{count}', diffInSeconds);
    } else if (diffInSeconds < 120) {
        return text.minute;
    } else if (diffInSeconds < 3600) {
        return text.minutes.replace('{count}', Math.floor(diffInSeconds / 60));
    } else if (diffInSeconds < 7200) {
        return text.hour;
    } else if (diffInSeconds < 86400) {
        return text.hours.replace('{count}', Math.floor(diffInSeconds / 3600));
    } else if (diffInSeconds < 172800) {
        return text.day;
    } else if (diffInSeconds < 604800) {
        return text.days.replace('{count}', Math.floor(diffInSeconds / 86400));
    } else if (diffInSeconds < 1209600) {
        return text.week;
    } else if (diffInSeconds < 2592000) {
        return text.weeks.replace('{count}', Math.floor(diffInSeconds / 604800));
    } else if (diffInSeconds < 5184000) {
        return text.month;
    } else if (diffInSeconds < 31536000) {
        return text.months.replace('{count}', Math.floor(diffInSeconds / 2592000));
    } else if (diffInSeconds < 63072000) {
        return text.year;
    } else {
        return text.years.replace('{count}', Math.floor(diffInSeconds / 31536000));
    }
}

/**
 * تنسيق الأرقام
 * @param {number} number - الرقم المراد تنسيقه
 * @param {number} decimals - عدد الأرقام العشرية
 * @param {string} locale - اللغة (ar, en)
 * @returns {string} - الرقم المنسق
 */
function formatNumber(number, decimals = 0, locale = 'ar') {
    // التحقق من صحة الرقم
    if (isNaN(number)) {
        return 'رقم غير صالح';
    }
    
    // تنسيق الرقم حسب اللغة
    return number.toLocaleString(locale === 'ar' ? 'ar-SA' : 'en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * تنسيق العملة
 * @param {number} amount - المبلغ المراد تنسيقه
 * @param {string} currency - رمز العملة (SAR, USD)
 * @param {string} locale - اللغة (ar, en)
 * @returns {string} - المبلغ المنسق
 */
function formatCurrency(amount, currency = 'SAR', locale = 'ar') {
    // التحقق من صحة المبلغ
    if (isNaN(amount)) {
        return 'مبلغ غير صالح';
    }
    
    // تنسيق المبلغ حسب اللغة والعملة
    return amount.toLocaleString(locale === 'ar' ? 'ar-SA' : 'en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * تحويل النص إلى Slug
 * @param {string} text - النص المراد تحويله
 * @returns {string} - النص المحول
 */
function slugify(text) {
    return text
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-');
}

/**
 * التحقق من صحة البريد الإلكتروني
 * @param {string} email - البريد الإلكتروني المراد التحقق منه
 * @returns {boolean} - نتيجة التحقق
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * التحقق من قوة كلمة المرور
 * @param {string} password - كلمة المرور المراد التحقق منها
 * @returns {object} - نتيجة التحقق وقوة كلمة المرور
 */
function checkPasswordStrength(password) {
    // التحقق من طول كلمة المرور
    if (password.length < 8) {
        return {
            valid: false,
            strength: 'weak',
            message: 'كلمة المرور قصيرة جداً (يجب أن تكون 8 أحرف على الأقل)'
        };
    }
    
    // التحقق من تنوع كلمة المرور
    let strength = 0;
    
    // التحقق من وجود أحرف صغيرة
    if (/[a-z]/.test(password)) {
        strength += 1;
    }
    
    // التحقق من وجود أحرف كبيرة
    if (/[A-Z]/.test(password)) {
        strength += 1;
    }
    
    // التحقق من وجود أرقام
    if (/[0-9]/.test(password)) {
        strength += 1;
    }
    
    // التحقق من وجود رموز خاصة
    if (/[^a-zA-Z0-9]/.test(password)) {
        strength += 1;
    }
    
    // تحديد قوة كلمة المرور
    let strengthText = '';
    let message = '';
    let valid = false;
    
    switch (strength) {
        case 1:
            strengthText = 'weak';
            message = 'كلمة المرور ضعيفة (يجب أن تحتوي على أحرف كبيرة وصغيرة وأرقام ورموز)';
            break;
        case 2:
            strengthText = 'medium';
            message = 'كلمة المرور متوسطة (يفضل إضافة المزيد من التنوع)';
            valid = true;
            break;
        case 3:
            strengthText = 'strong';
            message = 'كلمة المرور قوية';
            valid = true;
            break;
        case 4:
            strengthText = 'very-strong';
            message = 'كلمة المرور قوية جداً';
            valid = true;
            break;
    }
    
    return {
        valid: valid,
        strength: strengthText,
        message: message
    };
}

/**
 * تحميل محتوى عبر AJAX
 * @param {string} url - عنوان URL للمحتوى
 * @param {string} targetSelector - محدد العنصر الهدف
 * @param {object} data - بيانات الطلب
 * @param {Function} callback - دالة الاستدعاء بعد التحميل
 */
function loadContent(url, targetSelector, data = {}, callback = null) {
    const targetElement = document.querySelector(targetSelector);
    if (!targetElement) {
        console.error('العنصر الهدف غير موجود:', targetSelector);
        return;
    }
    
    // عرض مؤشر التحميل
    targetElement.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div></div>';
    
    // إعداد بيانات الطلب
    const formData = new FormData();
    for (const key in data) {
        formData.append(key, data[key]);
    }
    
    // إرسال الطلب
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('حدث خطأ أثناء تحميل المحتوى');
        }
        return response.text();
    })
    .then(html => {
        targetElement.innerHTML = html;
        if (callback && typeof callback === 'function') {
            callback(html);
        }
    })
    .catch(error => {
        console.error('خطأ في تحميل المحتوى:', error);
        targetElement.innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء تحميل المحتوى. يرجى المحاولة مرة أخرى.</div>';
    });
}

/**
 * إرسال نموذج عبر AJAX
 * @param {string} formSelector - محدد النموذج
 * @param {Function} successCallback - دالة الاستدعاء عند النجاح
 * @param {Function} errorCallback - دالة الاستدعاء عند الخطأ
 */
function submitForm(formSelector, successCallback = null, errorCallback = null) {
    const form = document.querySelector(formSelector);
    if (!form) {
        console.error('النموذج غير موجود:', formSelector);
        return;
    }
    
    // التحقق من صحة النموذج
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        if (errorCallback && typeof errorCallback === 'function') {
            errorCallback('يرجى ملء جميع الحقول المطلوبة بشكل صحيح');
        }
        return;
    }
    
    // تعطيل زر الإرسال
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جارٍ الإرسال...';
    }
    
    // إعداد بيانات النموذج
    const formData = new FormData(form);
    
    // إرسال النموذج
    fetch(form.action, {
        method: form.method || 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('حدث خطأ أثناء إرسال النموذج');
        }
        return response.json();
    })
    .then(data => {
        // إعادة تمكين زر الإرسال
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'إرسال';
        }
        
        // استدعاء دالة النجاح
        if (successCallback && typeof successCallback === 'function') {
            successCallback(data);
        } else {
            // عرض رسالة نجاح افتراضية
            showAlert(data.message || 'تم إرسال النموذج بنجاح', 'success');
        }
    })
    .catch(error => {
        console.error('خطأ في إرسال النموذج:', error);
        
        // إعادة تمكين زر الإرسال
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'إرسال';
        }
        
        // استدعاء دالة الخطأ
        if (errorCallback && typeof errorCallback === 'function') {
            errorCallback(error.message);
        } else {
            // عرض رسالة خطأ افتراضية
            showAlert('حدث خطأ أثناء إرسال النموذج. يرجى المحاولة مرة أخرى.', 'danger');
        }
    });
}
