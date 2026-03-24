<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature   = 'webpush:vapid';
    protected $description = 'Generate VAPID public/private key pair for Web Push notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->info('VAPID keys generated successfully!');
        $this->newLine();
        $this->line('<options=bold>Add these to your .env file:</options=bold>');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->newLine();
        $this->warn('Keep the private key secret. The public key is shared with browsers.');

        // Auto-write to .env if it exists and keys are not already set
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            $updated = false;

            if (!str_contains($envContent, 'VAPID_PUBLIC_KEY=')) {
                $envContent .= "\nVAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
                $updated = true;
            }

            if (!str_contains($envContent, 'VAPID_PRIVATE_KEY=')) {
                $envContent .= "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";
                $updated = true;
            }

            if ($updated) {
                file_put_contents($envPath, $envContent);
                $this->info('.env file updated with VAPID keys.');
            } else {
                $this->comment('Keys already present in .env — not overwritten.');
            }
        }

        return self::SUCCESS;
    }
}
