<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ProcessNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process 
                          {--workers=3 : Number of concurrent queue workers} 
                          {--sleep=3 : Seconds to sleep when queue is empty} 
                          {--timeout=60 : Timeout in seconds} 
                          {--queue=notifications : Queue to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process notification queue with multiple concurrent workers for high throughput';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workers = $this->option('workers');
        $queue = $this->option('queue');
        $sleep = $this->option('sleep');
        $timeout = $this->option('timeout');

        $this->info(sprintf(
            'Starting notification queue processor: %d workers, queue "%s", sleep %ds, timeout %ds',
            $workers,
            $queue,
            $sleep,
            $timeout
        ));

        $processes = [];

        // Start multiple workers for parallel processing
        for ($i = 0; $i < $workers; $i++) {
            $process = Process::fromShellCommandline(sprintf(
                'php artisan queue:work database --queue=%s --sleep=%d --timeout=%d --tries=3 --max-jobs=100 --max-time=3600 --stop-when-empty',
                $queue,
                $sleep,
                $timeout
            ));

            $process->start();
            $processes[] = $process;
            $this->info(sprintf('Started worker %d (PID: %d)', $i + 1, $process->getPid()));
        }

        // Monitor processes
        while (true) {
            foreach ($processes as $index => $process) {
                if (!$process->isRunning()) {
                    if ($process->getExitCode() !== 0) {
                        $this->error(sprintf('Worker %d exited with code %d: %s', $index + 1, $process->getExitCode(), $process->getErrorOutput()));
                        // Restart failed worker
                        $this->info(sprintf('Restarting worker %d...', $index + 1));
                        $newProcess = Process::fromShellCommandline($process->getCommandLine());
                        $newProcess->start();
                        $processes[$index] = $newProcess;
                        $this->info(sprintf('Worker %d restarted (PID: %d)', $index + 1, $newProcess->getPid()));
                    }
                }
            }

            sleep(5);
        }
    }
}
