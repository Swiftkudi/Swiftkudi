<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IndexNowService;

class IndexNowSubmit extends Command
{
    protected $signature = 'indexnow:submit 
                            {url : The URL to submit}
                            {--type=page : Content type (page, task, service, product)}
                            {--batch : Submit multiple URLs from input}';

    protected $description = 'Submit URLs to IndexNow for instant indexing';

    public function handle(IndexNowService $indexNow): int
    {
        $url = $this->argument('url');
        $type = $this->option('type');
        
        // Verify key first
        if (!$indexNow->verifyKey()) {
            $this->error('IndexNow key not verified. Please ensure indexnow-key.txt exists in public folder.');
            return Command::FAILURE;
        }

        if ($this->option('batch')) {
            // Batch submission - urls should be newline separated
            $urls = array_filter(explode("\n", $url));
            $this->info("Submitting " . count($urls) . " URLs to IndexNow...");
            
            $results = $indexNow->submitUrls($urls);
            
            foreach ($results as $url => $result) {
                $this->line("URL: {$url}");
                foreach ($result as $engine => $status) {
                    $icon = ($status['success'] ?? false) ? '✅' : '❌';
                    $this->line("  {$icon} {$engine}");
                }
            }
        } else {
            // Single URL submission
            $this->info("Submitting to IndexNow: {$url}");
            
            $results = $indexNow->submitUrl($url);
            
            foreach ($results as $engine => $status) {
                $icon = ($status['success'] ?? false) ? '✅' : '❌';
                $this->line("{$icon} {$engine}: " . ($status['status'] ?? $status['error'] ?? 'OK'));
            }
        }

        $this->info('Done!');
        return Command::SUCCESS;
    }
}
