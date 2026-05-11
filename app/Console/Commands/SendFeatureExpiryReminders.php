<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SendFeatureExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'features:send-expiry-reminders 
                            {--days=7 : Number of days before expiry to send "expiring soon" notification}
                            {--dry-run : Run without actually sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send feature expiry reminders to users (7 days before expiry and when expired)';

    /**
     * Execute the console command.
     */
    public function handle(NotificationManager $notificationManager): int
    {
        $daysBefore = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode – no notifications will be sent.');
        }

        $this->info("Scanning users for feature expiries ({$daysBefore} days before threshold)...");

        $users = User::where('is_admin', false)
            ->where('is_suspended', false)
            ->whereNotNull('account_type')
            ->get();

        $expiringCount = 0;
        $expiredCount = 0;
        $skippedNoFeatures = 0;

        foreach ($users as $user) {
            $featuresData = $this->getUserFeatures($user);
            
            foreach ($featuresData as $featureKey => $expiresAt) {
                if (!$expiresAt) {
                    continue;
                }

                // Ensure Carbon instance
                if (!($expiresAt instanceof Carbon)) {
                    $expiresAt = Carbon::parse($expiresAt);
                }

                $now = Carbon::now();
                $daysUntilExpiry = $now->diffInDays($expiresAt, false); // negative if past

                // Check if already notified recently (avoid spam)
                $notificationType = $daysUntilExpiry <= 0 
                    ? \App\Models\Notification::TYPE_FEATURE_EXPIRED
                    : \App\Models\Notification::TYPE_FEATURE_EXPIRING_SOON;

                if ($this->hasRecentNotification($user->id, $featureKey, $notificationType, $daysBefore)) {
                    continue; // skip duplicate
                }

                if ($daysUntilExpiry <= 0) {
                    // Already expired
                    if (!$dryRun) {
                        $notificationManager->notify(
                            NotificationManager::EVENT_FEATURE_EXPIRED,
                            $user,
                            [
                                'feature' => $featureKey,
                                'feature_label' => $this->getFeatureLabel($featureKey),
                                'expires_at' => $expiresAt->format('F j, Y'),
                            ]
                        );
                    }
                    $expiredCount++;
                } elseif ($daysUntilExpiry <= $daysBefore) {
                    // Expiring soon
                    if (!$dryRun) {
                        $notificationManager->notify(
                            NotificationManager::EVENT_FEATURE_EXPIRING_SOON,
                            $user,
                            [
                                'feature' => $featureKey,
                                'feature_label' => $this->getFeatureLabel($featureKey),
                                'expires_at' => $expiresAt->format('F j, Y'),
                                'days_left' => $daysUntilExpiry,
                            ]
                        );
                    }
                    $expiringCount++;
                }
            }
        }

        $this->info("Summary: Sent {$expiringCount} expiring-soon alerts, {$expiredCount} expired alerts.");
        Log::info('Feature expiry reminders sent', [
            'expiring_soon' => $expiringCount,
            'expired' => $expiredCount,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }

    /**
     * Get all feature expiries for a user based on their account type.
     *
     * @return array<string, \Carbon\Carbon|null>  feature_key => expiry_carbon
     */
    protected function getUserFeatures(User $user): array
    {
        $accountType = $user->account_type;
        $features = [];

        switch ($accountType) {
            case 'buyer':
                $featureKeys = ['professional_services', 'task_creation', 'available_tasks'];
                foreach ($featureKeys as $key) {
                    $features[$key] = $user->getBuyerFeatureExpiry($key);
                }
                break;

            case 'earner':
                $featureKeys = ['professional_services', 'digital_products', 'growth_marketplace', 'task_creation'];
                foreach ($featureKeys as $key) {
                    $features[$key] = $user->getEarnerFeatureExpiry($key);
                }
                break;

            case 'task_creator':
                $featureKeys = ['available_tasks', 'professional_services', 'growth_listings', 'digital_products'];
                foreach ($featureKeys as $key) {
                    $features[$key] = $user->getTaskCreatorFeatureExpiry($key);
                }
                break;

            case 'freelancer':
                $featureKeys = ['task_creation', 'available_tasks', 'growth_listings', 'digital_products'];
                foreach ($featureKeys as $key) {
                    $features[$key] = $user->getFreelancerFeatureExpiry($key);
                }
                break;

            case 'digital_seller':
                $featureKeys = ['task_creation', 'available_tasks', 'professional_services', 'growth_listings'];
                foreach ($featureKeys as $key) {
                    $features[$key] = $user->getDigitalSellerFeatureExpiry($key);
                }
                break;

            case 'growth_seller':
                $featureKeys = ['task_creation', 'available_tasks', 'professional_services', 'digital_products'];
                foreach ($featureKeys as $key) {
                    $features[$key] = $user->getGrowthSellerFeatureExpiry($key);
                }
                break;
        }

        return $features;
    }

    /**
     * Check if a recent notification of this type was already sent for this feature.
     */
    protected function hasRecentNotification(int $userId, string $featureKey, string $notificationType, int $daysThreshold): bool
    {
        $lookback = max($daysThreshold, 3); // check up to N days back
        $recent = \App\Models\Notification::where('user_id', $userId)
            ->where('type', $notificationType)
            ->where('created_at', '>=', now()->subDays($lookback))
            ->whereJsonContains('data->feature', $featureKey)
            ->exists();

        return $recent;
    }

    /**
     * Get human-readable label for a feature key.
     */
    protected function getFeatureLabel(string $featureKey): string
    {
        return config("features.features.{$featureKey}.label", ucfirst(str_replace('_', ' ', $featureKey)));
    }
}
