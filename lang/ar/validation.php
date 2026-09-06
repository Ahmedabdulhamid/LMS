<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صالحًا.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'url' => 'يجب أن يكون :attribute رابطًا صالحًا.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'max' => [
        'numeric' => 'يجب ألا تزيد قيمة :attribute عن :max.',
        'file' => 'يجب ألا يزيد حجم :attribute عن :max كيلوبايت.',
        'string' => 'يجب ألا يزيد :attribute عن :max حرفًا.',
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عنصرًا.',
    ],
    'min' => [
        'numeric' => 'يجب ألا تقل قيمة :attribute عن :min.',
        'file' => 'يجب ألا يقل حجم :attribute عن :min كيلوبايت.',
        'string' => 'يجب ألا يقل :attribute عن :min أحرف.',
        'array' => 'يجب ألا يحتوي :attribute على أقل من :min عناصر.',
    ],
    'lt' => ['numeric' => 'يجب أن تكون قيمة :attribute أقل من :value.'],
    'after_or_equal' => 'يجب أن يكون :attribute تاريخًا لاحقًا أو مساويًا لـ :date.',
    'attributes' => [
        'title' => 'العنوان', 'category_id' => 'التصنيف', 'lang' => 'اللغة', 'level' => 'المستوى',
        'price' => 'السعر', 'price_after_discount' => 'السعر بعد الخصم', 'description' => 'الوصف',
        'thumbnail' => 'الصورة المصغرة', 'name' => 'الاسم', 'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور', 'password_confirmation' => 'تأكيد كلمة المرور',
        'current_password' => 'كلمة المرور الحالية', 'bio' => 'النبذة التعريفية',
    ],
];
