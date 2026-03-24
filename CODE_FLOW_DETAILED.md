# Code Flow - Deposit → Pre-filled Form

## Complete Code Path

### 1️⃣ USER SUBMITS TASK FORM

**File:** `resources/views/tasks/create.blade.php`

```javascript
// Form submission event
document.querySelector("form").addEventListener("submit", function (e) {
    // ... validation checks ...
    // Form POST to route('tasks.store')
});
```

### 2️⃣ STORE METHOD VALIDATES BUDGET

**File:** `app/Http/Controllers/TaskController.php`

```php
public function store(Request $request)
{
    $user = Auth::user();
    $validated = $request->validate([...]);

    // Call service to create task
    $result = $this->earnDeskService->createTask($user, $validated);

    if ($result['success']) {
        // ✅ Task created successfully
        return redirect()->route('tasks.my-tasks')
            ->with('success', '🎉 Task created successfully! Budget: ₦...');
    }

    // ❌ Check if error is insufficient balance
    if (strpos($result['message'], 'Insufficient balance') !== false) {
        // SAVE FORM DATA TO SESSION ← KEY STEP!
        session()->put('pending_task_form', $validated);

        // Extract required amount from error message
        preg_match('/₦[\d,]+(?:\.\d{2})?/', $result['message'], $matches);
        $requiredAmount = $matches[0] ?? $result['message'];

        // SAVE REQUIRED AMOUNT
        session()->put('insufficient_balance_required', $requiredAmount);

        // SET SUCCESS REDIRECT ROUTE
        session()->put('deposit_success_redirect', 'tasks.create.resume');

        // 🚀 REDIRECT TO DEPOSIT
        return redirect()->route('wallet.deposit')
            ->with('warning', "Insufficient balance. You need {$requiredAmount}...");
    }

    // Other errors
    return redirect()->route('tasks.create')
        ->with('error', $result['message'])
        ->withInput();
}
```

### 3️⃣ DEPOSIT PAGE DISPLAYS

**File:** `app/Http/Controllers/WalletController.php` (GET)

```php
public function deposit(Request $request)
{
    // GET request - show deposit form
    if ($request->isMethod('GET')) {
        return view('wallet.deposit', compact('wallet'));
    }
}
```

**File:** `resources/views/wallet/deposit.blade.php`

```blade
<!-- Show context about pending task -->
@if(session('insufficient_balance_required'))
    <div class="bg-blue-50 p-6">
        <p class="font-semibold">Task Creation on Hold</p>
        <p>You need {{ session('insufficient_balance_required') }} to create your task.</p>
    </div>
@endif

<!-- Pre-fill amount field -->
<input
    type="number"
    name="amount"
    id="amount"
    @if(session('insufficient_balance_required'))
        value="{{ preg_replace('/[^0-9]/', '', session('insufficient_balance_required')) }}"
    @endif
    required>
```

### 4️⃣ USER DEPOSITS FUNDS

**File:** `app/Http/Controllers/WalletController.php` (POST)

```php
public function deposit(Request $request)
{
    // POST request - process deposit
    $request->validate(['amount' => 'required|numeric|min:100']);

    // Add funds to wallet
    $wallet->addWithdrawable($request->amount, 'deposit');

    // Create transaction record
    Transaction::create([...]);

    // Create ledger entry
    WalletLedger::createEntry([...]);

    // ✅ CHECK IF RETURNING FROM TASK CREATION
    $redirectRoute = session('deposit_success_redirect');

    if ($redirectRoute && $redirectRoute === route('tasks.create.resume')) {
        // CLEAR DEPOSIT CONTEXT (but keep form data!)
        session()->forget(['deposit_success_redirect']);

        // 🚀 REDIRECT TO RESUME HANDLER
        return redirect($redirectRoute)
            ->with('success', '💰 Deposit successful! Your form is ready to submit.');
    }

    // Normal deposit (not from task creation)
    return redirect()->route('wallet.index')
        ->with('success', 'Deposit successful!');
}
```

### 5️⃣ RESUME HANDLER CHECKS BALANCE

**File:** `app/Http/Controllers/TaskController.php` (NEW METHOD)

```php
public function resumeCreate()
{
    $user = Auth::user();

    // GET FORM DATA FROM SESSION
    $pendingForm = session('pending_task_form');

    if (!$pendingForm) {
        return redirect()->route('tasks.create')
            ->with('info', 'Ready to create a task!');
    }

    // GET WALLET
    $wallet = $user->wallet;
    $budget = floatval($pendingForm['budget'] ?? 0);

    // ✅ CHECK IF BALANCE NOW SUFFICIENT
    if ($wallet && $wallet->canAffordTotal($budget)) {

        // CLEAR SESSION (form data done its job)
        session()->forget([
            'pending_task_form',
            'insufficient_balance_required',
            'deposit_success_redirect'
        ]);

        // 🚀 REDIRECT TO CREATE FORM WITH WITHPUT() ← MAGIC!
        return redirect()->route('tasks.create')
            ->with('success', '💰 Deposit successful! Your form is pre-filled. Review and submit.')
            ->withInput($pendingForm);  // ← THIS MAKES IT WORK!
            //   ↑ Tells Laravel to populate old() helper
            //   ↑ ALL form fields get values
            //   ↑ User returns to PRE-FILLED FORM!
    }

    // ❌ STILL INSUFFICIENT
    return redirect()->route('wallet.deposit')
        ->with('warning', 'Please deposit more funds...')
        ->with('deposit_success_redirect', route('tasks.create.resume'));
}
```

### 6️⃣ CREATE FORM DISPLAYS WITH PRE-FILLED DATA

**File:** `resources/views/tasks/create.blade.php`

```blade
@php
    $wallet = Auth::user()->wallet;
    $totalBalance = $wallet ? $wallet->withdrawable_balance + $wallet->promo_credit_balance : 0;
    $hasFormData = (bool) old('title');  // ← Check if returning with data
@endphp

<!-- Show pre-fill notification -->
@if(session('success') && $hasFormData)
    <div class="bg-blue-50 p-4">
        <p class="font-semibold">✨ Your form is pre-filled and ready!</p>
        <p>Your wallet now has sufficient balance. Review and click "Create Task".</p>
    </div>
@endif

<!-- All form fields use old() for pre-filling -->

<!-- Title Input -->
<input
    type="text"
    id="title"
    name="title"
    value="{{ old('title') }}"  ← PRE-FILLED!
    required>

<!-- Description Textarea -->
<textarea
    id="description"
    name="description"
    required>{{ old('description') }}</textarea>  ← PRE-FILLED!

<!-- Budget Input -->
<input
    type="number"
    id="budget"
    name="budget"
    value="{{ old('budget') }}"  ← PRE-FILLED!
    required>

<!-- Quantity Input -->
<input
    type="number"
    id="quantity"
    name="quantity"
    value="{{ old('quantity') }}"  ← PRE-FILLED!
    required>

<!-- Category Hidden Input -->
<input
    type="hidden"
    id="category_id"
    name="category_id"
    value="{{ old('category_id') }}">  ← PRE-FILLED!

<!-- JavaScript Restores Selection State -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryId = "{{ old('category_id') }}";

        if (categoryId) {
            // Select the option in dropdown
            document.getElementById('task_type').value = categoryId;

            // Find and trigger change event to restore state
            const event = new Event('change');
            document.getElementById('task_type').dispatchEvent(event);
        }
    });
</script>
```

### 7️⃣ USER REVIEWS FORM (PRE-FILLED) AND SUBMITS

```
✓ Title: "Get 100 likes on my post"
✓ Description: "I need engagement on..."
✓ Category: "Instagram - Likes" (selected)
✓ Budget: "₦2,500"
✓ Quantity: "187"
✓ Platform: "instagram" (selected)
✓ All other fields: Filled

User clicks "Create Task" button
```

### 8️⃣ TASK CREATED SUCCESSFULLY

**Back to store() method:**

```php
if ($result['success']) {
    // Task created successfully
    return redirect()->route('tasks.my-tasks')
        ->with('success', '🎉 Task created successfully! Budget: ₦' .
               number_format($result['budget'], 2));
}
```

**User sees:**

- Redirected to "My Tasks" page
- Success message shown
- New task visible in list
- All session data cleared

---

## Session Data Throughout Flow

### Timeline of Session Values

```
STEP 1: After form submission with insufficient balance
session = {
    'pending_task_form': {
        'title': 'Get 100 likes...',
        'description': '...',
        'budget': 2500,
        'quantity': 187,
        'category_id': 5,
        'platform': 'instagram',
        'proof_type': 'screenshot',
        ... all other fields
    },
    'insufficient_balance_required': '₦2,500.00',
    'deposit_success_redirect': 'tasks.create.resume'
}

STEP 2: After deposit (in deposit() method)
session = {
    'pending_task_form': { ... },  ← KEPT!
    'insufficient_balance_required': '₦2,500.00',
    'deposit_success_redirect': 'tasks.create.resume'
}

STEP 3: After redirect to resumeCreate()
session = {
    'pending_task_form': { ... },  ← STILL HERE!
    'insufficient_balance_required': '₦2,500.00',
    'deposit_success_redirect': 'tasks.create.resume'
}

STEP 4: In resumeCreate() method
// Clear deposit-specific data
session()->forget([
    'insufficient_balance_required',
    'deposit_success_redirect'
]);

session = {
    'pending_task_form': { ... },  ← KEPT FOR withInput()!
}

STEP 5: withInput() in create form
->withInput($pendingForm)
// Laravel automatically:
// 1. Puts $pendingForm data into session flash
// 2. old() helper retrieves values
// 3. All form fields populated

STEP 6: After task creation
session()->forget('pending_task_form');

session = {}  ← Clean!
```

---

## The Magic: withInput()

```php
// This single line makes everything work:
->withInput($pendingForm)

// What it does:
// 1. Takes the array $pendingForm
// 2. Stores it in session flash data
// 3. Makes old() helper available in view
// 4. ALL fields populate with old() values
// 5. Gets cleared after request

// In the view:
value="{{ old('title') }}"     // ← Gets value from withInput()
value="{{ old('budget') }}"    // ← Gets value from withInput()
```

This is why the form comes back pre-filled! 🎉
