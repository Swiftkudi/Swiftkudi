<?php

namespace App\Console\Commands;

use App\Services\Marketplace\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarketplaceAutoRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:auto-release
                            {--dry-run : Show what would be released without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-release escrow funds for delivered marketplace orders that have passed the holding period';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Dry run mode — no changes will be made.');
        }

        try {
            if (!$dryRun) {
                $orderService->processAutoReleases();
            }

            $this->info('Marketplace auto-release completed successfully.');
            Log::info('Marketplace auto-release cron executed', ['dry_run' => $dryRun]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Marketplace auto-release failed: ' . $e->getMessage());
            Log::error('Marketplace auto-release failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}