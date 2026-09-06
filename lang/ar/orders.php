<?php

return [
    'navigation' => ['orders' => 'الطلبات', 'archived_orders' => 'الطلبات المؤرشفة'],
    'resources' => ['order' => 'طلب', 'orders' => 'الطلبات', 'archived_order' => 'طلب مؤرشف', 'archived_orders' => 'الطلبات المؤرشفة'],
    'sections' => [
        'information' => 'معلومات الطلب', 'items' => 'عناصر الطلب',
        'payment_transactions' => 'معاملات الدفع', 'webhook_events' => 'أحداث Webhook',
    ],
    'fields' => [
        'original_order_id' => 'معرّف الطلب الأصلي', 'number' => 'رقم الطلب', 'student' => 'الطالب',
        'email' => 'البريد الإلكتروني', 'course' => 'اسم الدورة', 'price' => 'السعر', 'quantity' => 'الكمية',
        'total' => 'المبلغ الإجمالي', 'amount' => 'المبلغ', 'currency' => 'العملة',
        'payment_status' => 'حالة الدفع', 'order_status' => 'حالة الطلب', 'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء', 'paid_at' => 'تاريخ الدفع', 'archive_reason' => 'سبب الأرشفة',
        'original_created_at' => 'تاريخ الإنشاء الأصلي', 'archived_at' => 'تاريخ الأرشفة',
        'provider' => 'مزود الدفع', 'transaction_id' => 'رقم المعاملة', 'event_type' => 'نوع الحدث',
        'received_at' => 'تاريخ الاستلام',
    ],
    'filters' => [
        'order_status' => 'حالة الطلب', 'payment_status' => 'حالة الدفع', 'date_range' => 'نطاق التاريخ',
        'archive_date' => 'تاريخ الأرشفة', 'user' => 'المستخدم', 'from' => 'من', 'until' => 'إلى',
    ],
    'actions' => ['view' => 'عرض'],
    'statuses' => [
        'pending' => 'قيد الانتظار', 'paid' => 'مدفوع', 'cancelled' => 'ملغي', 'refunded' => 'مسترد',
        'completed' => 'مكتمل', 'failed' => 'فشل', 'unknown' => 'غير معروف',
    ],
];
