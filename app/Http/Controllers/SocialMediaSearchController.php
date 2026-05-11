<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Services\SocialMediaSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SocialMediaSearchController extends Controller
{
    protected SocialMediaSearchService $searchService;

    public function __construct(SocialMediaSearchService $searchService)
    {
        $this->searchService = $searchService;

        $this->middleware(function ($request, $next) {
            // Only allow admin users
            if (!Auth::check() || !Auth::user()->is_admin) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access the admin area.');
            }
            return $next($request);
        });
    }

    public function index(): View
    {
        $platforms = $this->searchService->getSupportedPlatforms();
        $microTaskTypes = SocialMediaSearchService::getMicroTaskTypes();
        
        $categories = TaskCategory::where('task_type', 'micro')
            ->where('is_active', true)
            ->get()
            ->groupBy('platform');
        
        return view('tasks.social-search', compact('platforms', 'microTaskTypes', 'categories'));
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'platform' => 'nullable|string|in:instagram,twitter,facebook,tiktok,youtube,linkedin,snapchat',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('query');
        $platform = $request->input('platform');
        $limit = $request->input('limit', 20);

        $profiles = $this->searchService->searchProfiles($query, $platform, $limit);

        return response()->json([
            'success' => true,
            'profiles' => $profiles,
            'count' => count($profiles),
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'profiles' => 'required|array|min:1',
            'task_type' => 'required|string|in:follow,like,comment,share,view,retweet',
            'reward_per_task' => 'required|numeric|min:1',
            'quantity_per_profile' => 'required|integer|min:1',
            'task_title' => 'required|string|min:5|max:200',
            'task_description' => 'required|string|min:10|max:1000',
        ]);

        $profiles = $request->input('profiles');
        $taskType = $request->input('task_type');
        $rewardPerTask = (float) $request->input('reward_per_task');
        $quantityPerProfile = (int) $request->input('quantity_per_profile');
        $taskTitle = $request->input('task_title');
        $taskDescription = $request->input('task_description');

        $createdTasks = $this->searchService->createTasksFromProfiles(
            $profiles,
            $taskType,
            $rewardPerTask,
            $quantityPerProfile,
            $taskTitle,
            $taskDescription,
            auth()->id()
        );

        $totalBudget = collect($createdTasks)->sum('budget');
        $totalQuantity = collect($createdTasks)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Successfully created ' . count($createdTasks) . ' tasks',
            'tasks' => array_map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'platform' => $task->platform,
                    'budget' => $task->budget,
                    'quantity' => $task->quantity,
                ];
            }, $createdTasks),
            'summary' => [
                'total_tasks' => count($createdTasks),
                'total_budget' => $totalBudget,
                'total_quantity' => $totalQuantity,
            ],
            'redirect' => route('tasks.my-tasks'),
        ]);
    }

    public function getTaskTypes(Request $request): JsonResponse
    {
        $platform = $request->input('platform');
        
        $categories = TaskCategory::where('task_type', 'micro')
            ->when($platform, fn($q) => $q->where('platform', $platform))
            ->where('is_active', true)
            ->get(['id', 'name', 'platform', 'base_price']);

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'task_types' => SocialMediaSearchService::getMicroTaskTypes(),
        ]);
    }
}