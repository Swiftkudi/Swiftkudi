# Task Creation Flow - Insufficient Balance Handling

## Overview

Implemented a seamless user experience where if a user has insufficient balance when creating a task, they are redirected to deposit funds, and upon successful deposit, the task creation is automatically completed.

## Flow Diagram

```
User Creates Task
    ↓
[Validate Budget]
    ↓
    ├─ Sufficient Balance → Create Task Successfully ✓
    │
    └─ Insufficient Balance →
        ├─ Save Form Data to Session
        ├─ Save Required Amount to Session
        └─ Redirect to Wallet/Deposit
              ↓
        [Deposit Form Shows]
        ├─ Display Context Alert (Task on Hold)
        ├─ Pre-fill Amount Field with Required Amount
        └─ User Deposits Funds
              ↓
        [Deposit Successful]
        ├─ Set Success Message
        └─ Auto-Redirect to Task Resume
              ↓
        [Task Resume Handler]
        ├─ Check Balance Again
        ├─ If Sufficient → Create Task Automatically ✓
        └─ If Insufficient → Redirect Back to Deposit
```

## Implementation Details

### 1. **Task Creation Check** (`TaskController.php::store()`)

- When a user submits the task creation form with insufficient balance
- The error message contains "Insufficient balance"
- System extracts the required amount from the error message
- Form data is stored in session under `pending_task_form`
- Required amount is stored under `insufficient_balance_required`
- User is redirected to wallet deposit with context

```php
// Check if error is due to insufficient balance
if (strpos($result['message'], 'Insufficient balance') !== false) {
    // Store form data for post-deposit continuation
    session()->put('pending_task_form', $validated);
    session()->put('insufficient_balance_required', $requiredAmount);

    return redirect()->route('wallet.deposit')
        ->with('warning', "Insufficient balance. You need {$requiredAmount}...");
}
```

### 2. **Deposit Page Enhancement** (`wallet/deposit.blade.php`)

- Shows blue alert box explaining task is on hold
- Displays required amount prominently
- Pre-fills the deposit amount field with the exact amount needed
- User can deposit more if they want

```blade
@if(session('insufficient_balance_required'))
    <div class="bg-blue-50 dark:bg-blue-500/10 border-l-4 border-blue-500 p-6">
        <p>You need to deposit at least {{ session('insufficient_balance_required') }}
           to create your task. After depositing, your task will be created automatically!</p>
    </div>
@endif
```

### 3. **Deposit Processing** (`WalletController.php::deposit()`)

- After successful deposit
- Checks if there's a pending task creation redirect in session
- If `deposit_success_redirect` is set to `tasks.create.resume`
- Redirects to task resume handler instead of wallet page
- Displays success message and auto-continues task creation

```php
// Check if user has a pending task creation
$redirectRoute = session('deposit_success_redirect');

if ($redirectRoute && $redirectRoute === route('tasks.create.resume')) {
    return redirect($redirectRoute)
        ->with('success', '💰 Deposit successful! Your task is being created...');
}
```

### 4. **Task Resume Handler** (`TaskController.php::resumeCreate()`)

- Called after successful deposit
- Retrieves pending form data from session
- Checks if wallet now has sufficient balance
- If YES:
    - Clears session data
    - Automatically creates the task with saved form data
    - Notifies workers
    - Shows success message with emoji
- If NO:
    - Redirects back to deposit (e.g., amount was insufficient)

```php
public function resumeCreate()
{
    $pendingForm = session('pending_task_form');

    if ($wallet && $wallet->canAffordTotal($budget)) {
        // Create task automatically
        $result = $this->earnDeskService->createTask($user, $validated);

        if ($result['success']) {
            return redirect()->route('tasks.my-tasks')
                ->with('success', '🎉 Task created successfully after deposit!');
        }
    }
}
```

### 5. **Create Task Form Updates** (`tasks/create.blade.php`)

- Added wallet balance display at the top of the form
- Shows total available balance (withdrawable + promo)
- "Add Funds" button appears if balance < ₦2,500
- Links directly to deposit page for quick access

```blade
<div class="mb-6 flex items-center justify-between ...">
    <div>
        <p class="text-sm font-semibold ...">Wallet Balance</p>
        <p class="text-lg font-bold ...">₦{{ number_format($totalBalance, 2) }}</p>
    </div>
    @if($totalBalance < 2500)
    <a href="{{ route('wallet.deposit') }}" class="...">
        <i class="fas fa-plus-circle mr-1"></i> Add Funds
    </a>
    @endif
</div>
```

### 6. **New Route** (`routes/web.php`)

- Added new route: `GET /tasks/create/resume`
- Points to `TaskController::resumeCreate()`
- Protected by auth middleware
- Only accessible with pending task data in session

```php
Route::get('/create/resume', [TaskController::class, 'resumeCreate'])->name('create.resume');
```

## Session Data Structure

### `pending_task_form` (Array)

- Contains all validated form data from task creation
- Includes: title, description, budget, quantity, platform, category_id, etc.
- Retrieved and used to create task after deposit

### `insufficient_balance_required` (String)

- Displays required amount in error message
- Pre-fills deposit field
- Shows in wallet context alert

### `deposit_success_redirect` (String)

- Set to task resume route: `tasks.create.resume`
- Checked after deposit to determine next action
- Cleared after redirect to prevent loops

## User Experience Flow

### Scenario: User with ₦500 balance tries to create ₦2,500 task

1. **Form Submission**
    - User fills all task details
    - Budget: ₦2,500
    - Clicks "Create Task"

2. **Balance Check Fails**
    - Server validates: ₦500 < ₦2,500
    - Shows error: "Insufficient balance. You need ₦2,500.00"
    - Form data saved to session

3. **Redirect to Deposit**
    - User lands on deposit page
    - Sees blue alert: "Task Creation on Hold"
    - Amount field shows: ₦2500 (pre-filled)
    - "Add Funds" button in header for quick navigation

4. **Deposit Funds**
    - User enters ₦2,500 or more
    - Clicks "Deposit ₦2,500"
    - Shows: "💰 Deposit successful! Your task is being created..."

5. **Automatic Task Creation**
    - System checks balance (now ₦2,500 + ₦500 = ₦3,000)
    - Creates task automatically with saved data
    - Clears session data
    - Redirects to "My Tasks"
    - Shows: "🎉 Task created successfully after deposit! Budget: ₦2,500.00"

6. **Task Complete**
    - User sees newly created task in dashboard
    - No need to re-fill the form
    - Seamless experience ✓

## Error Handling

### Edge Cases Covered

1. **Session Expired**
    - User takes too long to deposit
    - Session data cleared
    - Redirects to create form
    - Shows: "Ready to create a task!"

2. **Incomplete Deposit**
    - User deposits less than required
    - System detects insufficient balance
    - Redirects back to deposit
    - Form data preserved

3. **Form Validation Failure**
    - After deposit, some validation fails
    - Task not created
    - Redirects to create form
    - Shows form data + error message
    - User can edit and resubmit

4. **Multiple Deposits**
    - User can deposit multiple times
    - Each deposit restarts the check
    - Eventually creates task when balance sufficient

## Benefits

✅ **User-Friendly**: No lost form data  
✅ **Automatic Completion**: Task created without re-entering  
✅ **Clear Context**: User knows why they're depositing  
✅ **Flexible**: Can deposit more than required  
✅ **Resilient**: Handles edge cases gracefully  
✅ **Smooth**: Single continuous flow instead of multiple steps

## Testing Checklist

- [ ] Create task with sufficient balance (normal path)
- [ ] Create task with insufficient balance (deposit path)
- [ ] Deposit exact amount needed
- [ ] Deposit more than amount needed
- [ ] Return to form before deposit (session preserved)
- [ ] Session expiry (after 2 hours)
- [ ] Form validation after deposit fails
- [ ] Multiple deposit attempts
- [ ] Browser back button during flow
- [ ] Mobile responsiveness of deposit page
