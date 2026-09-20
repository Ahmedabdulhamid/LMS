<?php
return [
    'page_title' => 'Payment status',
    'states' => [
        'processing' => ['badge' => 'Processing', 'title' => 'Payment received — almost there!', 'message' => 'Your payment is being verified and your access is being activated. This usually takes only a few moments. Please keep this page open.', 'note' => 'You do not need to pay again. We will update this page automatically.'],
        'success' => ['badge' => 'Completed', 'title' => 'You’re all set!', 'message' => 'Your payment was confirmed and your course or subscription is now ready to use.', 'note' => 'You can start learning right away.'],
        'failed' => ['badge' => 'Unsuccessful', 'title' => 'Payment wasn’t completed', 'message' => 'We could not complete this payment. Please check your payment details or try again with another method.', 'note' => 'If an amount appears as pending, your bank will normally release it automatically.'],
    ],
    'order' => 'Order number', 'amount' => 'Amount', 'transaction' => 'Transaction ID', 'status' => 'Status', 'not_available' => 'Not available',
    'back_home' => 'Back to home', 'view_order' => 'View order', 'try_again' => 'Try again', 'my_courses' => 'Go to my courses', 'secure_payment' => 'Secure payment',
];
