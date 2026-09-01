# Paymob Payment Lifecycle - Complete Implementation Guide

## Overview
This document provides a complete implementation of the Paymob payment lifecycle for the Laravel LMS application. The implementation follows professional Laravel practices with proper error handling, logging, security, and duplicate prevention.

## Architecture Flow
```
Student -> CheckoutOrder (Livewire) 
  -> PaymobService (generates token & iframe)
  -> Paymob iframe (payment processing)
  -> User completes/cancels payment
  -> Redirect to /api/payment/callback (for user feedback)
  -> Paymob sends webhook to /api/paymob/webhook (truth source)
  -> PaymentSuccessHandler (activates courses/subscriptions)
```

## Files Created/Modified

### 1. Database Migration
**File:** `database/migrations/2026_08_25_051000_add_paymob_payment_fields_to_orders_table.php`

**Purpose:** Adds Paymob-specific columns to orders table

**Columns Added:**
- `payment_status` (nullable string, default: 'pending')
  - Values: 'pending', 'paid', 'failed'
  - Indexed for efficient queries
  
- `paymob_transaction_id` (nullable string)
  - Stores Paymob transaction ID from webhook
  
- Index on `payment_status` for payment queries

**Previous Migration:**
- `paymob_order_id` already exists from previous migration

**Migration Execution:**
```bash
php artisan migrate
```

### 2. Model Updates
**File:** `app/Models/Order.php`

**Changes:**
- Added `payment_status` to `$casts` for type casting
- No schema changes needed (already has `paid_at` timestamp)

**Casts:**
```php
'payment_status' => 'string',
```

### 3. Improved PaymobService
**File:** `app/Services/PaymobService.php`

**Key Improvements:**

#### Duplicate Prevention
- Checks if order already has `paymob_order_id`
- Reuses existing order instead of creating duplicate
- Useful for page refreshes and retries

#### Enhanced Error Handling
- Try-catch blocks around all API calls
- Comprehensive error logging for debugging
- Throws exceptions for controller to handle

#### Logging
- Logs all major operations (order creation, payment key generation)
- Logs all errors with context for debugging
- Request/response logging for Paymob API

#### Configuration Support
- Uses `config/paymob.php` for all settings
- Supports environment variables:
  - `PAYMOB_API_KEY`
  - `PAYMOB_IFRAME_ID`
  - `PAYMOB_INTEGRATION_CARD_ID`
  - `PAYMOB_SECRET_KEY` (for HMAC)
  - `PAYMOB_BASE_URL`

#### HMAC Signature Verification
- New method: `verifyPaymobSignature()`
- Validates webhook authenticity
- Prevents unauthorized webhook access

**New Methods:**
- `getPaymentToken()` - Main entry point with duplicate prevention
- `recreatePaymentKey()` - Gets new payment key for existing order
- `getAuthToken()` - With proper error handling and logging
- `createOrder()` - With proper error handling and logging
- `createPaymentKey()` - With proper error handling and logging
- `verifyPaymobSignature()` - Validates HMAC signature
- `buildSignatureSource()` - Builds signature string for verification

### 4. PaymentSuccessHandler Service
**File:** `app/Services/PaymentSuccessHandler.php`

**Purpose:** Handles all post-payment activation logic

**Responsibilities:**
- Marks order as paid with timestamp
- Activates purchased courses (creates enrollments)
- Activates subscription plans
- Prevents duplicate activation (idempotent)

**Key Features:**

#### Transactional Processing
- Uses database transactions
- Rollback on error
- Atomic operations

#### Course Enrollment
- Creates `Enrollment` record
- Sets source as `Order`
- Status: `Active`
- Prevents duplicate enrollment via unique check

#### Subscription Activation
- Creates `Subscription` record
- Calculates end date based on plan duration
- Supports: day, week, month, year durations
- Status: `Active`
- Prevents duplicate subscription via unique check

#### Logging
- Logs all activations with context
- Logs errors with details for debugging

**Methods:**
- `handle()` - Main entry point, coordinates activation
- `processOrderItem()` - Routes to course or subscription handler
- `enrollStudentInCourse()` - Course enrollment logic
- `activateSubscription()` - Subscription activation logic
- `calculateSubscriptionEndDate()` - Duration calculation

### 5. PaymobWebhookController
**File:** `app/Http/Controllers/PaymobWebhookController.php`

**Purpose:** Handles incoming webhooks from Paymob

**Security:**
- Verifies HMAC signature
- Rejects unsigned/invalid signatures
- Validates required fields

**Workflow:**
1. Receives webhook payload
2. Logs complete payload for debugging
3. Verifies HMAC signature (if configured)
4. Extracts payment information
5. Finds order by `paymob_order_id`
6. Checks for duplicate processing
7. Updates order with transaction ID
8. Calls `PaymentSuccessHandler` for activation
9. Returns JSON response

**Error Handling:**
- Missing required fields: 400 Bad Request
- Order not found: 404 Not Found
- Signature invalid: 401 Unauthorized
- Processing error: 500 Internal Server Error

**Prevents Duplicate Processing:**
- Checks if `payment_status` already 'paid'
- Logs duplicate attempt
- Returns success response (idempotent)

### 6. PaymentController
**File:** `app/Http/Controllers/PaymentController.php`

**Purpose:** Handles Paymob redirect callback

**Important Notes:**
- Does NOT confirm payment here
- Only shows payment status page
- Webhook is the only source of truth
- User sees pending/checking status while webhook processes

**Workflow:**
1. User redirected from Paymob after payment attempt
2. Controller fetches order
3. Verifies ownership (student authentication)
4. Shows payment callback view
5. Frontend can poll status or wait for webhook

**Security:**
- Requires `student` authentication
- Validates order ownership

### 7. CheckoutOrder Livewire Component
**File:** `app/Livewire/CheckoutOrder.php`

**Improvements:**
- Generates payment token in `mount()`
- Stores iframe URL in component property
- Better error handling
- User-friendly error messages
- Session flash messages

**Properties:**
- `paymentToken` - Payment token from PaymobService
- `iframeUrl` - Full iframe URL for rendering
- `order` - Current order being checked out

**Error Handling:**
- Try-catch around payment generation
- Flashes error message to session
- Redirects back on failure

### 8. Routes
**File:** `routes/api.php` (newly created)

**Routes:**

#### POST /api/paymob/webhook
- **Controller:** `PaymobWebhookController@handle`
- **Authentication:** None (public)
- **Rate Limit:** Disabled (webhook calls shouldn't be throttled)
- **Method:** `PaymobWebhookController::handle()`
- **Response:** JSON success/failure

#### GET /api/payment/callback
- **Controller:** `PaymentController@callback`
- **Authentication:** Required (`auth:student`)
- **Rate Limit:** Standard throttle applied
- **Method:** `PaymentController::callback()`
- **Response:** Blade view with order status

## Environment Configuration

**Required .env variables:**
```env
PAYMOB_API_KEY=your_api_key
PAYMOB_IFRAME_ID=your_iframe_id
PAYMOB_INTEGRATION_CARD_ID=your_integration_id
PAYMOB_SECRET_KEY=your_secret_key
PAYMOB_BASE_URL=https://api.paymob.com/v1
PAYMOB_HMAC=true
```

**Optional .env variables:**
```env
PAYMOB_INTEGRATION_WALLET_ID=wallet_id
```

## Blade View Updates

Update `resources/views/livewire/checkout-order.blade.php`:

```blade
<div>
    @if ($iframeUrl)
        <!-- Paymob iframe -->
        <iframe src="{{ $iframeUrl }}" 
                style="width: 100%; height: 600px; border: none;">
        </iframe>
    @else
        <p class="alert alert-danger">
            Failed to load payment form. Please refresh and try again.
        </p>
    @endif
</div>
```

Create `resources/views/payment/callback.blade.php`:

```blade
@extends('layouts.course-public')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Payment Status</h3>
                </div>
                <div class="card-body">
                    @if ($order->payment_status === 'paid')
                        <div class="alert alert-success">
                            <h4>Payment Successful!</h4>
                            <p>Your payment has been confirmed.</p>
                        </div>
                    @elseif ($order->payment_status === 'failed')
                        <div class="alert alert-danger">
                            <h4>Payment Failed</h4>
                            <p>Your payment could not be processed.</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h4>Processing Payment</h4>
                            <p>Please wait while we confirm your payment...</p>
                        </div>
                    @endif
                    
                    <p class="mt-3">
                        <strong>Order ID:</strong> {{ $order->number }}<br>
                        <strong>Amount:</strong> {{ $order->total }} EGP<br>
                        <strong>Status:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## Paymob Webhook Configuration

1. Log in to Paymob Dashboard
2. Navigate to: Settings → Webhooks
3. Add webhook URL: `https://your-domain.com/api/paymob/webhook`
4. Subscribe to: Transaction successful/failed events
5. Copy HMAC secret key
6. Add to `.env`: `PAYMOB_SECRET_KEY=your_secret_key`

## Testing the Implementation

### 1. Manual Testing Flow

```bash
# 1. Run migration
php artisan migrate

# 2. Create test order (via admin or seeder)
# Ensure order has items before checkout

# 3. Visit checkout page
# http://localhost/checkout/orders/ORD-20260825-XXXXX

# 4. Fill payment details in iframe
# Use test card numbers from Paymob docs

# 5. Webhook will be called automatically
# Check logs for webhook processing

# 6. Check order status
# order->payment_status should be 'paid'
# Enrollments should be created
# Subscriptions should be activated
```

### 2. Check Logs

```bash
# View payment-related logs
tail -f storage/logs/laravel.log | grep -i "paymob\|payment"
```

### 3. Test Duplicate Prevention

```bash
# Refresh checkout page multiple times
# Should reuse existing paymob_order_id
# No duplicate orders created
```

### 4. Verify Database

```sql
-- Check order with payment fields
SELECT id, number, paymob_order_id, payment_status, paymob_transaction_id, paid_at 
FROM orders 
WHERE number = 'ORD-20260825-XXXXX';

-- Check enrollments created
SELECT * FROM enrollments WHERE order_id = ORDER_ID;

-- Check subscriptions created
SELECT * FROM subscriptions WHERE order_id = ORDER_ID;
```

## Important Notes

### Security Considerations
1. **HMAC Verification:** Always enable HMAC verification in production
2. **Webhook Validation:** Never trust webhook data without signature verification
3. **Order Ownership:** Always verify order belongs to authenticated user
4. **Payment Status:** Only webhook is source of truth for payment status
5. **Rate Limiting:** Disable rate limiting for webhook endpoint only

### Error Handling Strategy
1. All exceptions are caught and logged
2. Payment failures don't crash the app
3. Webhook retries are idempotent (duplicate-safe)
4. User gets friendly error messages
5. Admin gets detailed logs for debugging

### Monitoring
Monitor these logs for issues:
- Paymob API errors
- Signature verification failures
- Duplicate processing attempts
- Payment processing errors

### Common Issues & Solutions

**Issue: Duplicate Paymob orders on page refresh**
- ✅ Fixed by checking `paymob_order_id` before creating new order

**Issue: Payment not activated after webhook**
- Check logs for webhook processing errors
- Verify HMAC signature configuration
- Ensure order exists in database

**Issue: Duplicate enrollments/subscriptions**
- ✅ Fixed by checking existing records before creating new ones
- Webhook is idempotent

**Issue: Webhook not being called**
- Verify webhook URL in Paymob dashboard
- Check server can receive external requests
- Verify HMAC secret matches
- Check firewall/network rules

## Deployment Checklist

- [ ] Create and run migration: `php artisan migrate`
- [ ] Update `.env` with Paymob credentials
- [ ] Configure webhook URL in Paymob dashboard
- [ ] Copy HMAC secret to `.env`
- [ ] Update blade views (checkout and callback)
- [ ] Test with Paymob test cards
- [ ] Verify logs are being written
- [ ] Check enrollment/subscription creation
- [ ] Test webhook retry handling (duplicate prevention)
- [ ] Monitor logs in production
- [ ] Set up error alerts (payment failures)

## Code Quality

**Standards Followed:**
- ✅ Type hints on all parameters and returns
- ✅ Proper use of Laravel facades
- ✅ Database transactions for data consistency
- ✅ Comprehensive error handling
- ✅ Detailed logging throughout
- ✅ Configuration-driven (no hardcoded values)
- ✅ Security best practices (signature verification)
- ✅ Idempotent operations (safe retries)
- ✅ Separation of concerns (handlers, services, controllers)
- ✅ Model relationships properly used

## Summary

This implementation provides:
1. ✅ Complete Paymob payment lifecycle
2. ✅ Duplicate prevention and idempotency
3. ✅ Comprehensive error handling and logging
4. ✅ HMAC signature verification
5. ✅ Automatic course enrollment
6. ✅ Automatic subscription activation
7. ✅ Professional code structure
8. ✅ Production-ready security

The system is ready for production deployment after testing with Paymob credentials.
