<?php

return [
    'navigation' => [
        'dashboard' => 'لوحة التحكم', 'courses' => 'الدورات', 'students' => 'الطلاب',
        'payments' => 'المدفوعات', 'reviews' => 'التقييمات', 'profile' => 'الملف الشخصي',
    ],
    'dashboard' => [
        'title' => 'لوحة التحكم',
        'stats' => ['courses' => 'إجمالي الدورات', 'students' => 'إجمالي الطلاب', 'revenue' => 'إجمالي الإيرادات', 'rating' => 'متوسط التقييم'],
        'recent_enrollments' => 'أحدث التسجيلات',
        'course_performance' => 'أداء الدورات',
        'columns' => ['student' => 'الطالب', 'course' => 'الدورة', 'enrollment_date' => 'تاريخ التسجيل', 'course_title' => 'عنوان الدورة', 'students' => 'الطلاب', 'completion' => 'نسبة الإكمال', 'rating' => 'متوسط التقييم'],
        'empty' => ['enrollments' => 'لا توجد تسجيلات حتى الآن', 'courses' => 'لا توجد بيانات لأداء الدورات حتى الآن'],
    ],
    'resources' => [
        'course' => ['singular' => 'دورة', 'plural' => 'الدورات'],
        'student' => ['singular' => 'طالب', 'plural' => 'الطلاب'],
        'payment' => ['singular' => 'دفعة', 'plural' => 'المدفوعات'],
        'review' => ['singular' => 'تقييم', 'plural' => 'التقييمات'],
    ],
    'tables' => [
        'common' => ['student' => 'الطالب', 'course' => 'الدورة', 'email' => 'البريد الإلكتروني', 'status' => 'الحالة', 'created_at' => 'تاريخ الإنشاء'],
        'students' => ['courses' => 'الدورات', 'joined' => 'تاريخ الانضمام', 'empty_heading' => 'لا يوجد طلاب حتى الآن', 'empty_description' => 'سيظهر هنا الطلاب المسجلون في دوراتك.'],
        'payments' => ['price' => 'المبلغ', 'date' => 'تاريخ الدفع', 'empty_heading' => 'لا توجد مدفوعات حتى الآن', 'empty_description' => 'ستظهر مدفوعات الدورات هنا.'],
        'reviews' => ['rating' => 'التقييم', 'comment' => 'التعليق', 'approved' => 'معتمد', 'reviewed' => 'تاريخ التقييم', 'empty_heading' => 'لا توجد تقييمات حتى الآن', 'empty_description' => 'ستظهر تقييمات الطلاب هنا.'],
    ],
    'statuses' => ['completed' => 'مكتملة', 'pending' => 'قيد الانتظار', 'failed' => 'فشلت'],
    'languages' => ['ar' => 'العربية', 'en' => 'الإنجليزية', 'fr' => 'الفرنسية'],
    'levels' => ['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'],
    'courses' => [
        'sections' => ['details' => 'تفاصيل الدورة', 'learning_goals' => 'أهداف التعلم', 'requirements' => 'المتطلبات', 'curriculum' => 'محتوى الدورة'],
        'fields' => ['title' => 'العنوان', 'category' => 'التصنيف', 'language' => 'اللغة', 'level' => 'المستوى', 'published' => 'منشورة', 'price' => 'السعر', 'discount_price' => 'السعر بعد الخصم', 'description' => 'الوصف', 'thumbnail' => 'الصورة المصغرة', 'goal' => 'هدف التعلم', 'requirement' => 'المتطلب', 'section_title' => 'عنوان القسم', 'videos' => 'الفيديوهات', 'video_title' => 'عنوان الفيديو', 'video_description' => 'وصف الفيديو', 'video_file' => 'ملف الفيديو', 'is_free' => 'معاينة مجانية', 'lessons' => 'الدروس', 'duration' => 'المدة', 'updated' => 'آخر تحديث'],
        'help' => ['r2_upload_hint' => 'احفظ الدورة أولاً، ثم عدّلها لرفع الفيديوهات الطويلة مباشرة إلى R2.'],
        'empty_heading' => 'لا توجد دورات حتى الآن', 'empty_description' => 'أنشئ دورتك الأولى لبدء التدريس.',
        'actions' => ['create' => 'إنشاء دورة', 'edit' => 'تعديل الدورة', 'view' => 'عرض الدورة', 'delete' => 'حذف الدورة', 'delete_confirm' => 'هل أنت متأكد من حذف :course؟', 'delete_success' => 'تم حذف الدورة بنجاح.', 'add_goal' => 'إضافة هدف', 'add_requirement' => 'إضافة متطلب', 'add_section' => 'إضافة قسم', 'add_video' => 'إضافة فيديو'],
        'notifications' => ['created' => 'تم إنشاء الدورة بنجاح.'],
    ],
    'profile' => ['navigation' => 'الملف الشخصي', 'title' => 'ملفي كمدرس'],
    'uploader' => ['choose' => 'اختر ملف الفيديو', 'upload' => 'رفع الفيديو إلى R2', 'uploading' => 'جارٍ الرفع :progress٪', 'save_first' => 'احفظ الدورة أولاً، ثم عدّلها لرفع الفيديوهات الطويلة مباشرة إلى R2.'],
    'language_switcher' => ['label' => 'اللغة', 'english' => 'English', 'arabic' => 'العربية'],
];
