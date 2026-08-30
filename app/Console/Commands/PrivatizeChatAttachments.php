<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeChatAttachments extends Command
{
    protected $signature = 'chat:privatize-attachments {--keep-public : Copy files but keep the old public originals}';
    protected $description = 'Move legacy chat attachments from the public disk to protected local storage.';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $files = $public->allFiles('chat/attachments');

        if (empty($files)) {
            $this->info('No legacy public chat attachments were found.');
            return self::SUCCESS;
        }

        $moved = 0;
        $failed = 0;
        foreach ($files as $path) {
            try {
                if (!$private->exists($path)) {
                    $stream = $public->readStream($path);
                    if ($stream === false) {
                        throw new \RuntimeException('Unable to read source file.');
                    }
                    $private->put($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                if (!$this->option('keep-public')) {
                    $public->delete($path);
                }
                $moved++;
            } catch (\Throwable $e) {
                $failed++;
                $this->warn($path . ': ' . $e->getMessage());
            }
        }

        $this->info("Processed {$moved} attachment(s); {$failed} failed.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
