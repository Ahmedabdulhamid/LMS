<?php

return [
    'course' => [
        'success' => ['subject' => 'Course purchase confirmed · :order', 'title' => 'Your course is ready!', 'message' => 'Your payment was confirmed and the course has been added to your learning library.'],
        'failure' => ['subject' => 'Course payment was unsuccessful · :order', 'title' => 'Your payment didn’t go through', 'message' => 'We couldn’t confirm your payment. You haven’t been charged by us and you can safely try again.'],
    ],
    'plan' => [
        'success' => ['subject' => 'Subscription activated · :order', 'title' => 'Your plan is now active!', 'message' => 'Your payment was confirmed and your subscription benefits are ready to use.'],
        'failure' => ['subject' => 'Subscription payment was unsuccessful · :order', 'title' => 'Your subscription wasn’t activated', 'message' => 'We couldn’t confirm your payment, so the plan remains inactive. You can safely try again.'],
    ],
    'hello' => 'Hello :name,',
    'summary' => 'Order summary',
    'item' => 'Item',
    'amount' => 'Amount',
    'order' => 'Order number',
    'status' => 'Status',
    'paid' => 'Paid',
    'failed' => 'Failed',
    'cta_success' => 'Start learning',
    'cta_failure' => 'Try again',
    'help' => 'If you need help, reply to this email and our support team will assist you.',
    'footer' => 'Secure payment notification',
];
