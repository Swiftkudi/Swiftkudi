<?php

/*
|--------------------------------------------------------------------------
| SwiftKudi Configuration
|--------------------------------------------------------------------------
|
| This file contains application-specific configuration for SwiftKudi.
| Values here can be overridden in the .env file.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for task creation and other operations.
    |
    */
    'rate_limits' => [
        // Maximum tasks a user can create per day
        'tasks_per_day' => env('TASK_RATE_LIMIT_DAY', 50),
        
        // Maximum tasks a user can create per hour
        'tasks_per_hour' => env('TASK_RATE_LIMIT_HOUR', 10),
        
        // Maximum tasks a user can create per minute (prevents rapid submissions)
        'tasks_per_minute' => env('TASK_RATE_LIMIT_MINUTE', 2),
        
        // Maximum draft saves per hour
        'draft_saves_per_hour' => env('DRAFT_RATE_LIMIT_HOUR', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Task Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for task creation.
    |
    */
    'tasks' => [
        // Default platform margin (percentage)
        'default_platform_margin' => 25,
        
        // Worker reward percentage (100 - margin)
        'worker_reward_percentage' => 75,
        
        // Minimum budget for a task
        'min_budget' => env('TASK_MIN_BUDGET', 100),
        
        // Maximum budget for a single task
        'max_budget' => env('TASK_MAX_BUDGET', 1000000),
        
        // Minimum quantity
        'min_quantity' => 1,
        
        // Maximum quantity
        'max_quantity' => env('TASK_MAX_QUANTITY', 10000),
        
        // Minimum reward per task (to prevent spam)
        'min_reward_per_task' => env('TASK_MIN_REWARD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Idempotency Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for idempotency token handling.
    |
    */
    'idempotency' => [
        // Token validity duration in minutes
        'token_validity_minutes' => env('IDEMPOTENCY_TOKEN_MINUTES', 60),
        
        // Whether to track duplicate submissions
        'track_duplicates' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Draft Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for autosave draft functionality.
    |
    */
    'drafts' => [
        // Draft expiration in hours
        'expiration_hours' => 24,
        
        // Maximum drafts per user
        'max_drafts_per_user' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for performance optimization.
    |
    */
    'performance' => [
        // Enable queue processing for task creation
        'use_queue_for_creation' => env('TASK_USE_QUEUE', false),
        
        // Queue name for task creation jobs
        'queue_name' => 'task-creation',
        
        // Enable query caching for categories
        'cache_categories' => true,
        
        // Category cache duration in minutes
        'category_cache_duration' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Toggle features on/off.
    |
    */
    'features' => [
        // Enable step-based task creation wizard
        'wizard_mode' => env('TASK_WIZARD_ENABLED', true),
        
        // Enable autosave draft
        'autosave_draft' => env('TASK_AUTOSAVE_ENABLED', true),
        
        // Enable idempotency checks
        'idempotency_check' => env('TASK_IDEMPOTENCY_ENABLED', true),
        
        // Enable rate limiting
        'rate_limiting' => env('TASK_RATE_LIMITING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Activation Fee
    |--------------------------------------------------------------------------
    |
    | Settings for user activation fee.
    |
    */
    'activation_fee' => env('ACTIVATION_FEE', 1000),

];
