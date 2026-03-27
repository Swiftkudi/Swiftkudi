<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTaskCreationGate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            return $next($request);
        }

        // Skip if user is admin
        if ($user->is_admin) {
            return $next($request);
        }

        // Check if mandatory task creation gate is enabled
        $gateEnabled = SystemSetting::isMandatoryTaskCreationEnabled();

        if (!$gateEnabled) {
            return $next($request);
        }

        if ($user->account_type !== 'task_creator') {
            return $next($request);
        }
            


        // Skip if user has already completed mandatory creation
        if ($user->has_completed_mandatory_creation) {
            return $next($request);
        }

        // Task creators must create first task before listing access (no budget requirement)
        if ($user->account_type === 'task_creator') {
            // allow these routes for onboarding / first task creation
            $allowedForTaskCreator = [
                'start-your-journey',
                'tasks.index',
                'tasks.create',
                'tasks.create.new',
                'tasks.store',
                'tasks.bundles',
                'tasks.suggest-bundles',
                'dashboard',
                'logout',
                'wallet.index',
                'wallet.deposit',
                'wallet.transactions',
                'wallet.activate',
                'wallet.activate.process',
                'referrals.index',
                'new-tasks.index',
                'new-tasks.create',
                'new-tasks.store',
            ];

            $route = $request->route();
            $currentRoute = $route ? $route->getName() : null;

            if ($currentRoute && in_array($currentRoute, $allowedForTaskCreator)) {
                return $next($request);
            }

            return redirect()->route('start-your-journey')
                ->with('warning', 'Create your first task to activate marketplace access.');
        }

        // Get minimum required budget for earners
        $minimumBudget = SystemSetting::get('minimum_required_budget', 1000);

        // Check if user has met the requirement
        $userBudget = $user->total_created_task_budget ?? 0;

        if ($userBudget >= $minimumBudget) {
            // Update user status to unlocked
            $user->has_completed_mandatory_creation = true;
            $user->task_creation_unlocked_at = now();
            $user->save();

            return $next($request);
        }

        // Check if this is an allowed route (task creation, start journey, dashboard, wallet, etc.)
        $allowedRoutes = [
            'start-your-journey',
            'tasks.create',
            'tasks.create.new',
            'tasks.store',
            'tasks.bundles',
            'tasks.suggest-bundles',
            'dashboard',
            'wallet.index',
            'wallet.deposit',
            'wallet.transactions',
            'wallet.activate',
            'wallet.activate.process',
            'logout',
            'referrals.index',
            // allow owners to review/approve/reject submissions while gate is not satisfied
            'tasks.submission',
            'tasks.submission.',
        ];

        // Block withdrawal routes until task creation is complete
        $blockedRoutes = [
            'wallet.withdraw',
            'wallet.process-withdrawal',
        ];

        $route = $request->route();
        $currentRoute = $route ? $route->getName() : null;

        // Allow access if it's the start-your-journey page itself
        if ($currentRoute === 'start-your-journey') {
            return $next($request);
        }

        // Check if current route is in blocked routes
        $isBlocked = false;
        foreach ($blockedRoutes as $blocked) {
            if ($currentRoute && str_starts_with($currentRoute, $blocked)) {
                $isBlocked = true;
                break;
            }
        }

        if ($isBlocked) {
            // Redirect to start-your-journey with message
            return redirect()->route('start-your-journey')
                ->with('warning', 'Complete your first campaign to unlock withdrawals.');
        }

        // Check if current route is in allowed routes
        $isAllowed = false;
        foreach ($allowedRoutes as $allowed) {
            if ($currentRoute && str_starts_with($currentRoute, $allowed)) {
                $isAllowed = true;
                break;
            }
        }

        // Allow API routes for task creation
        if ($request->is('api/tasks*') && in_array($currentRoute, ['tasks.store', 'tasks.suggest-bundles'])) {
            $isAllowed = true;
        }

        if (!$isAllowed) {
            // Redirect to start-your-journey page
            return redirect()->route('start-your-journey')
                ->with('warning', 'Create your first campaign to unlock earnings.');
        }

        return $next($request);
    }
}
