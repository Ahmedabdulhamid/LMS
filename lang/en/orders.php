<?php

return [
    'navigation' => ['orders' => 'Orders', 'archived_orders' => 'Archived Orders'],
    'resources' => ['order' => 'Order', 'orders' => 'Orders', 'archived_order' => 'Archived Order', 'archived_orders' => 'Archived Orders'],
    'sections' => [
        'information' => 'Order Information', 'items' => 'Order Items',
        'payment_transactions' => 'Payment Transactions', 'webhook_events' => 'Webhook Events',
    ],
    'fields' => [
        'original_order_id' => 'Original Order ID', 'number' => 'Order Number', 'student' => 'Student',
        'email' => 'Email', 'course' => 'Course Name', 'price' => 'Price', 'quantity' => 'Quantity',
        'total' => 'Total Amount', 'amount' => 'Amount', 'currency' => 'Currency',
        'payment_status' => 'Payment Status', 'order_status' => 'Order Status', 'status' => 'Status',
        'created_at' => 'Created At', 'paid_at' => 'Paid At', 'archive_reason' => 'Archive Reason',
        'original_created_at' => 'Original Created Date', 'archived_at' => 'Archived At',
        'provider' => 'Provider', 'transaction_id' => 'Transaction ID', 'event_type' => 'Event Type',
        'received_at' => 'Received At',
    ],
    'filters' => [
        'order_status' => 'Order Status', 'payment_status' => 'Payment Status', 'date_range' => 'Date Range',
        'archive_date' => 'Archive Date', 'user' => 'User', 'from' => 'From', 'until' => 'Until',
    ],
    'actions' => ['view' => 'View'],
    'statuses' => [
        'pending' => 'Pending', 'paid' => 'Paid', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded',
        'completed' => 'Completed', 'failed' => 'Failed', 'unknown' => 'Unknown',
    ],
];
