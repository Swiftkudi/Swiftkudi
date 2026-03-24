<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RevenueAggregator;
use Carbon\Carbon;

class AggregateRevenue extends Command
{
    protected $signature = 'revenue:aggregate {--date=}';
    protected $description = 'Aggregate revenue for a given date (defaults to yesterday)';

    public function handle()
    {
        $date = $this->option('date') ?? Carbon::yesterday()->toDateString();

        $this->info('Aggregating revenue for ' . $date);

        try {
            RevenueAggregator::aggregateForDate($date);
            $this->info('Aggregation completed for ' . $date);
            return 0;
        } catch (\Exception $e) {
            $this->error('Aggregation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
