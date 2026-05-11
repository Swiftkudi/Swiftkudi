# Email Verification System Configuration

## Overview
The email verification system is now fully configurable through the admin settings and respects special user types (admins and Google OAuth users).

## How It Works

### 1. **Admin Setting Location**
- **Path:** Admin Panel → Settings → Registration → "Email Verification Required"
- **Toggle:** Can be turned ON (enabled) or OFF (disabled)
- **Database:** Stored in `system_settings` table with key `email_verification_required`

### 2. **Setting Storage**
- Values stored as strings: `'true'` or `'false'`
- Accessed via `SystemSetting::isEmailVerificationRequired()` which returns a boolean
- `SystemSetting::get('email_verification_required')` returns the string value

### 3. **Middleware Processing**
The custom middleware `CheckEmailVerificationRequired` handles three scenarios:

#### Scenario A: Email Verification is DISABLED globally
```php
if (!SystemSetting::isEmailVerificationRequired()) {
    return $next($request);  // Allow access for all authenticated users
}
```

#### Scenario B: Email Verification is ENABLED but user is ADMIN
```php
if ($user->is_admin || !is_null($user->admin_role_id)) {
    return $next($request);  // Allow admins without verification requirement
}
```

#### Scenario C: Email Verification is ENABLED and user is GOOGLE OAUTH USER
```php
if (!empty($user->google_id)) {
    return $next($request);  // Allow Google OAuth users without verification
}
```

#### Scenario D: All other cases
```php
if (!$user->hasVerifiedEmail()) {
    return redirect()->route('verification.notice');  // Require email verification
}
```

## Routes Protected by Email Verification Middleware

All authenticated routes now use the `check.email.required` middleware:

1. **Main Application Routes**
   - `/` (dashboard, onboarding, tasks, etc.)
   - All admin routes
   - Admin settings
   - Chat/messaging

2. **Feature Routes**
   - Professional Services (`/services/`)
   - Growth Listing (`/growth/`)
   - Digital Products (`/products/`)
   - Job Board (`/jobs/`)

3. **User Management Routes**
   - Escrow (`/escrow/`)
   - Disputes (`/disputes/`)
   - Verification Center (`/verification/`)
   - Boost & Promotion (`/boost/`)

## Configuration Matrix

| Setting Value | User Type | Requires Verification? |
|---|---|---|
| **OFF** | All users | ❌ No |
| **ON** | Admin user | ❌ No |
| **ON** | Google OAuth user | ❌ No |
| **ON** | Regular user | ✅ Yes |

## Testing the Configuration

### Test Case 1: Disable Email Verification
1. Go to Admin Settings → Registration
2. Toggle OFF "Email Verification Required"
3. Save settings
4. Log in with any user account
5. ✅ Should access all protected routes without email verification

### Test Case 2: Enable Email Verification with Admin
1. Go to Admin Settings → Registration
2. Toggle ON "Email Verification Required"
3. Save settings
4. Log in with admin account
5. ✅ Should access all protected routes without email verification

### Test Case 3: Enable Email Verification with Google OAuth User
1. Go to Admin Settings → Registration
2. Toggle ON "Email Verification Required"
3. Save settings
4. Log in with Google OAuth account (user with `google_id` field populated)
5. ✅ Should access all protected routes without email verification

### Test Case 4: Enable Email Verification with Regular User
1. Go to Admin Settings → Registration
2. Toggle ON "Email Verification Required"
3. Save settings
4. Log in with regular user account (no admin flag, no google_id)
5. ❌ Should be redirected to email verification notice
6. After email verification: ✅ Should access all protected routes

## Implementation Files

| File | Purpose |
|---|---|
| `app/Http/Middleware/CheckEmailVerificationRequired.php` | Main middleware handling verification logic |
| `app/Http/Kernel.php` | Registers middleware as `check.email.required` |
| `routes/web.php` | All protected routes use the middleware |
| `app/Models/SystemSetting.php` | `isEmailVerificationRequired()` method |
| `resources/views/admin/settings/registration.blade.php` | Admin UI toggle |
| `app/Http/Controllers/SettingsController.php` | Saves/validates the setting |

## Troubleshooting

### Issue: Toggle doesn't seem to save
**Solution:** 
1. Check browser console for JavaScript errors
2. Verify form is POST method to `admin.settings.update` route
3. Check database: `SELECT * FROM system_settings WHERE key = 'email_verification_required'`
4. Run `php artisan optimize:clear` to clear caches

### Issue: Setting saves but doesn't affect access
**Solution:**
1. Clear all caches: `php artisan optimize:clear`
2. Verify middleware is registered in `app/Http/Kernel.php`
3. Check routes use `check.email.required` middleware
4. Log out and log back in to refresh middleware state

### Issue: Admins still required to verify email
**Solution:**
1. Verify user has `is_admin = 1` OR non-null `admin_role_id` in database
2. Check the setting is turned ON (verification required)
3. Clear caches and re-login

## Notes

- The middleware checks the system setting on EVERY REQUEST
- No caching issues - setting is retrieved fresh from database each time
- Google OAuth users identified by checking `user->google_id` field
- Admins identified by checking `is_admin` flag OR `admin_role_id` relationship
- Regular users with email verification disabled can still manually verify email
- The system respects whatever state the email verification is in when disabled

