<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchEnginePinger;

class PingSearchEngines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:ping {url? : Specific URL to ping}
                            {--sitemap : Ping sitemap instead of URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping search engines with sitemap or URL updates';

    /**
     * Execute the console command.
     */
    public function handle(SearchEnginePinger $pinger): int
    {
        $url = $this->argument('url');
        $pingSitemap = $this->option('sitemap');

        if ($pingSitemap || !$url) {
            $this->info('Pinging search engines with sitemap...');
            $results = $pinger->pingSitemap();
        } else {
            $this->info("Pinging search engines with URL: {$url}");
            $results = $pinger->pingUrl($url);
        }

        foreach ($results as $engine => $result) {
            if (is_array($result) && isset($result['success'])) {
                $status = $result['success'] ? '✅' : '❌';
                $this->line("{$status} {$engine}: " . ($result['status'] ?? $result['error'] ?? 'OK'));
            } elseif (is_array($result) && isset($result['sitemap'])) {
                foreach ($result as $type => $typeResults) {
                    $this->info("{$type}:");
                    foreach ($typeResults as $se => $seResult) {
                        $status = $seResult['success'] ? '✅' : '❌';
                        $this->line("  {$status} {$se}: " . ($seResult['status'] ?? $seResult['error'] ?? 'OK'));
                    }
                }
            }
        }

        $this->info('Done!');
        
        return Command::SUCCESS;
    }
}
