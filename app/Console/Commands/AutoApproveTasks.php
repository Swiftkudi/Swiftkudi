<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskCompletion;
use App\Models\SystemSetting;
use App\Services\SwiftKudiService;
use Illuminate\Support\Facades\Log;

class AutoApproveTasks extends Command
{
    protected $signature = 'tasks:auto-approve';
    protected $description = 'Auto-approve or expire task completions based on configured settings';

    protected $earnDeskService;

    public function __construct(SwiftKudiService $earnDeskService)
    {
        parent::__construct();
        $this->earnDeskService = $earnDeskService;
    }

    public function handle()
    {
        // Use the configurable settings for expiry processing
        $result = $this->earnDeskService->processExpiredTaskCompletions();
        
        $this->info($result['message']);
        if (isset($result['auto_approved']) && $result['auto_approved'] > 0) {
            $this->info("Auto-approved: {$result['auto_approved']}");
        }
        if (isset($result['expired']) && $result['expired'] > 0) {
            $this->info("Expired: {$result['expired']}");
        }

        return 0;
    }
}
