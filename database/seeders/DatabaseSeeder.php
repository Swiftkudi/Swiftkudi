<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Currency;
use App\Models\TaskCategory;
use App\Models\SystemSetting;
use App\Models\AdminRole;
use App\Models\User;
use App\Models\Wallet;
use App\Models\MarketplaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed admin roles
        $this->seedAdminRoles();
        
        // Initialize system settings
        $this->initializeSystemSettings();

        // Apply a few environment-friendly defaults that may be edited later via admin
        // Set default gateway per currency only if not already set
        if (!SystemSetting::get('default_gateway_ngn')) {
            SystemSetting::set('default_gateway_ngn', 'paystack', SystemSetting::GROUP_PAYMENT, 'text');
        }
        if (!SystemSetting::get('default_gateway_usd')) {
            SystemSetting::set('default_gateway_usd', 'stripe', SystemSetting::GROUP_PAYMENT, 'text');
        }
        if (!SystemSetting::get('default_gateway_usdt')) {
            SystemSetting::set('default_gateway_usdt', 'stripe', SystemSetting::GROUP_PAYMENT, 'text');
        }

        // Ensure conversion rate aligns with seeded currencies
        SystemSetting::set('ngn_to_usd_rate', 1550, SystemSetting::GROUP_CURRENCY, 'number');

        // Seed currencies
        $this->seedCurrencies();
        
        // Seed task categories
        $this->seedTaskCategories();
        
        // Seed marketplace categories
        $this->call([
            MarketplaceCategorySeeder::class,
        ]);
        
        // Seed professional service categories
        $this->call([
            ProfessionalServiceCategorySeeder::class,
        ]);
        
        // Seed boost packages
        $this->call([
            BoostPackageSeeder::class,
        ]);
        
        // Seed badges
        $this->seedBadges();
        
        // Create sample users with wallets
        $this->createSampleUsers();
        
        // Seed sample tasks
        $this->seedSampleTasks();

        // Seed lightweight sample data (dev only)
        $this->call(\Database\Seeders\SampleDataSeeder::class);
    }

    /**
     * Seed admin roles
     */
    protected function seedAdminRoles(): void
    {
        AdminRole::createDefaults();
    }

    /**
     * Initialize system settings
     */
    protected function initializeSystemSettings(): void
    {
        SystemSetting::initializeDefaults();
    }

    /**
     * Seed currencies
     */
    protected function seedCurrencies(): void
    {
        $currencies = [
            [
                'code' => 'NGN',
                'name' => 'Nigerian Naira',
                'symbol' => '₦',
                'rate_to_ngn' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate_to_ngn' => 1550,
                'is_active' => true,
            ],
            [
                'code' => 'USDT',
                'name' => 'Tether',
                'symbol' => '₮',
                'rate_to_ngn' => 1550,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }

        $this->command->info('Currencies seeded successfully!');
    }

    /**
     * Seed task categories with pricing
     */
    protected function seedTaskCategories(): void
    {
        $categories = [
            // Micro Social Media Tasks
            [
                'name' => 'Instagram Likes',
                'slug' => 'instagram-likes',
                'description' => 'Like posts on Instagram to earn rewards',
                'icon' => 'instagram',
                'base_price' => 30,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'Instagram Comments',
                'slug' => 'instagram-comments',
                'description' => 'Comment on Instagram posts',
                'icon' => 'instagram',
                'base_price' => 50,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'Instagram Follows',
                'slug' => 'instagram-follows',
                'description' => 'Follow accounts on Instagram',
                'icon' => 'instagram',
                'base_price' => 100,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'TikTok Likes',
                'slug' => 'tiktok-likes',
                'description' => 'Like videos on TikTok',
                'icon' => 'tiktok',
                'base_price' => 30,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'TikTok Follows',
                'slug' => 'tiktok-follows',
                'description' => 'Follow accounts on TikTok',
                'icon' => 'tiktok',
                'base_price' => 100,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'Twitter/X Follows',
                'slug' => 'twitter-follows',
                'description' => 'Follow accounts on Twitter/X',
                'icon' => 'twitter',
                'base_price' => 100,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'Twitter/X Retweets',
                'slug' => 'twitter-retweets',
                'description' => 'Retweet posts on Twitter/X',
                'icon' => 'twitter',
                'base_price' => 150,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'YouTube Subscribes',
                'slug' => 'youtube-subscribes',
                'description' => 'Subscribe to YouTube channels',
                'icon' => 'youtube',
                'base_price' => 200,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'Facebook Likes',
                'slug' => 'facebook-likes',
                'description' => 'Like posts on Facebook',
                'icon' => 'facebook',
                'base_price' => 50,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            [
                'name' => 'Facebook Shares',
                'slug' => 'facebook-shares',
                'description' => 'Share posts on Facebook',
                'icon' => 'facebook',
                'base_price' => 100,
                'platform_margin' => 25,
                'task_type' => 'micro',
            ],
            
            // UGC / High-Value Tasks
            [
                'name' => 'Video Testimonials',
                'slug' => 'video-testimonials',
                'description' => 'Record video testimonials or reviews',
                'icon' => 'video',
                'base_price' => 3000,
                'platform_margin' => 25,
                'task_type' => 'ugc',
            ],
            [
                'name' => 'TikTok Product Videos',
                'slug' => 'tiktok-product-videos',
                'description' => 'Create promotional TikTok videos using products',
                'icon' => 'tiktok',
                'base_price' => 3500,
                'platform_margin' => 25,
                'task_type' => 'ugc',
            ],
            [
                'name' => 'Instagram Stories',
                'slug' => 'instagram-stories',
                'description' => 'Post Instagram Stories promoting products',
                'icon' => 'instagram',
                'base_price' => 2500,
                'platform_margin' => 25,
                'task_type' => 'ugc',
            ],
            [
                'name' => 'Product Reviews',
                'slug' => 'product-reviews',
                'description' => 'Write detailed product reviews',
                'icon' => 'star',
                'base_price' => 3000,
                'platform_margin' => 25,
                'task_type' => 'ugc',
            ],
            
            // Referral / Growth Tasks
            [
                'name' => 'Invite Users',
                'slug' => 'invite-users',
                'description' => 'Invite new users to join the platform',
                'icon' => 'users',
                'base_price' => 150,
                'platform_margin' => 25,
                'task_type' => 'referral',
            ],
            [
                'name' => 'Join Telegram',
                'slug' => 'join-telegram',
                'description' => 'Join Telegram groups/channels',
                'icon' => 'telegram',
                'base_price' => 100,
                'platform_margin' => 25,
                'task_type' => 'referral',
            ],
            [
                'name' => 'Join Discord',
                'slug' => 'join-discord',
                'description' => 'Join Discord servers',
                'icon' => 'discord',
                'base_price' => 100,
                'platform_margin' => 25,
                'task_type' => 'referral',
            ],
            
            // Premium Tasks (Level 2+)
            [
                'name' => 'Affiliate Signups',
                'slug' => 'affiliate-signups',
                'description' => 'Sign up for affiliate programs',
                'icon' => 'link',
                'base_price' => 500,
                'platform_margin' => 25,
                'task_type' => 'premium',
            ],
            [
                'name' => 'Influencer Campaigns',
                'slug' => 'influencer-campaigns',
                'description' => 'Micro-influencer promotional campaigns',
                'icon' => 'megaphone',
                'base_price' => 700,
                'platform_margin' => 25,
                'task_type' => 'premium',
            ],
            [
                'name' => 'Product Demo Videos',
                'slug' => 'product-demo-videos',
                'description' => 'Create product demonstration videos',
                'icon' => 'video',
                'base_price' => 500,
                'platform_margin' => 25,
                'task_type' => 'premium',
            ],
        ];

        foreach ($categories as $category) {
            TaskCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Task categories seeded successfully!');
    }

    /**
     * Seed badges
     */
    protected function seedBadges(): void
    {
        $badges = [
            [
                'name' => 'First Steps',
                'slug' => 'first-steps',
                'description' => 'Complete your first task',
                'icon' => 'star',
                'xp_reward' => 50,
                'requirements' => json_encode(['tasks_completed' => 1]),
            ],
            [
                'name' => 'Rising Star',
                'slug' => 'rising-star',
                'description' => 'Complete 10 tasks',
                'icon' => 'star',
                'xp_reward' => 100,
                'requirements' => json_encode(['tasks_completed' => 10]),
            ],
            [
                'name' => 'Super Worker',
                'slug' => 'super-worker',
                'description' => 'Complete 50 tasks',
                'icon' => 'medal',
                'xp_reward' => 250,
                'requirements' => json_encode(['tasks_completed' => 50]),
            ],
            [
                'name' => 'Master Tasker',
                'slug' => 'master-tasker',
                'description' => 'Complete 100 tasks',
                'icon' => 'trophy',
                'xp_reward' => 500,
                'requirements' => json_encode(['tasks_completed' => 100]),
            ],
            [
                'name' => '7-Day Streak',
                'slug' => '7-day-streak',
                'description' => 'Maintain a 7-day activity streak',
                'icon' => 'fire',
                'xp_reward' => 150,
                'requirements' => json_encode(['streak' => 7]),
            ],
            [
                'name' => '30-Day Streak',
                'slug' => '30-day-streak',
                'description' => 'Maintain a 30-day activity streak',
                'icon' => 'fire',
                'xp_reward' => 500,
                'requirements' => json_encode(['streak' => 30]),
            ],
            [
                'name' => 'Networker',
                'slug' => 'networker',
                'description' => 'Refer 3 users who activate',
                'icon' => 'users',
                'xp_reward' => 200,
                'requirements' => json_encode(['referrals' => 3]),
            ],
            [
                'name' => 'Connector',
                'slug' => 'connector',
                'description' => 'Refer 10 users who activate',
                'icon' => 'users',
                'xp_reward' => 500,
                'requirements' => json_encode(['referrals' => 10]),
            ],
            [
                'name' => 'Level 5',
                'slug' => 'level-5',
                'description' => 'Reach Level 5',
                'icon' => 'level-up',
                'xp_reward' => 300,
                'requirements' => json_encode(['level' => 5]),
            ],
            [
                'name' => 'Level 10',
                'slug' => 'level-10',
                'description' => 'Reach Level 10',
                'icon' => 'crown',
                'xp_reward' => 1000,
                'requirements' => json_encode(['level' => 10]),
            ],
            [
                'name' => 'Big Earner',
                'slug' => 'big-earner',
                'description' => 'Earn ₦10,000 or more',
                'icon' => 'naira',
                'xp_reward' => 200,
                'requirements' => json_encode(['total_earned' => 10000]),
            ],
            [
                'name' => 'Top Earner',
                'slug' => 'top-earner',
                'description' => 'Earn ₦50,000 or more',
                'icon' => 'trophy',
                'xp_reward' => 500,
                'requirements' => json_encode(['total_earned' => 50000]),
            ],
        ];

        foreach ($badges as $badge) {
            $badge['requirements'] = json_encode($badge['requirements']);
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }

        $this->command->info('Badges seeded successfully!');
    }

    /**
     * Create sample users with wallets
     */
    protected function createSampleUsers(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@swiftkudi.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'referral_code' => User::generateReferralCode(),
                'is_admin' => true,
                'level' => 10,
                'experience_points' => 10000,
            ]
        );

        // Assign super admin role if available
        $superRole = AdminRole::where('name', AdminRole::ROLE_SUPER_ADMIN)->first();
        if ($superRole && !$admin->admin_role_id) {
            $admin->admin_role_id = $superRole->id;
            $admin->save();
        }
        
        Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'withdrawable_balance' => 0,
                'promo_credit_balance' => 0,
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );

        // Create demo client
        $client = User::firstOrCreate(
            ['email' => 'client@swiftkudi.com'],
            [
                'name' => 'Demo Client',
                'password' => Hash::make('password123'),
                'referral_code' => User::generateReferralCode(),
            ]
        );
        
        Wallet::firstOrCreate(
            ['user_id' => $client->id],
            [
                'withdrawable_balance' => 50000,
                'promo_credit_balance' => 5000,
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );

        // Create demo worker
        $worker = User::firstOrCreate(
            ['email' => 'worker@swiftkudi.com'],
            [
                'name' => 'Demo Worker',
                'password' => Hash::make('password123'),
                'referral_code' => User::generateReferralCode(),
                'level' => 3,
                'experience_points' => 250,
                'daily_streak' => 5,
            ]
        );
        
        Wallet::firstOrCreate(
            ['user_id' => $worker->id],
            [
                'withdrawable_balance' => 2500,
                'promo_credit_balance' => 500,
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );

        $this->command->info('Sample users created successfully!');
        $this->command->info('Admin: admin@swiftkudi.com / password123');
        $this->command->info('Client: client@swiftkudi.com / password123');
        $this->command->info('Worker: worker@swiftkudi.com / password123');
    }

    /**
     * Seed sample tasks
     */
    protected function seedSampleTasks(): void
    {
        $client = User::where('email', 'client@swiftkudi.com')->first();
        if (!$client) {
            $this->command->warn('Client user not found. Skip seeding tasks.');
            return;
        }
        
        $categories = TaskCategory::all();
        
        $sampleTasks = [
            // Micro Tasks
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'instagram-likes')->first()->id ?? 1,
                'title' => 'Get 100 Likes on Our New Product Post',
                'description' => 'Like our latest Instagram post showing our new product collection. Must be a genuine like from an active account.',
                'platform' => 'instagram',
                'task_type' => 'like',
                'proof_type' => 'screenshot',
                'budget' => 3000,
                'quantity' => 100,
                'worker_reward_per_task' => 22.50,
                'platform_commission' => 750,
                'escrow_amount' => 3000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 0,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'instagram-follows')->first()->id ?? 3,
                'title' => 'Follow Our Brand Account',
                'description' => 'Follow our official Instagram brand account. Easy task, quick reward!',
                'platform' => 'instagram',
                'task_type' => 'follow',
                'proof_type' => 'screenshot',
                'budget' => 2000,
                'quantity' => 20,
                'worker_reward_per_task' => 75,
                'platform_commission' => 500,
                'escrow_amount' => 2000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => true,
                'completed_count' => 5,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'tiktok-likes')->first()->id ?? 4,
                'title' => 'Like TikTok Video Campaign',
                'description' => 'Watch and like our TikTok promotional video. Help us reach more people!',
                'platform' => 'tiktok',
                'task_type' => 'like',
                'proof_type' => 'screenshot',
                'budget' => 2500,
                'quantity' => 50,
                'worker_reward_per_task' => 37.50,
                'platform_commission' => 625,
                'escrow_amount' => 2500,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 12,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'twitter-retweets')->first()->id ?? 7,
                'title' => 'Retweet Our Announcement',
                'description' => 'Retweet our big announcement tweet. Help spread the word!',
                'platform' => 'twitter',
                'task_type' => 'retweet',
                'proof_type' => 'screenshot',
                'budget' => 3000,
                'quantity' => 20,
                'worker_reward_per_task' => 112.50,
                'platform_commission' => 750,
                'escrow_amount' => 3000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => true,
                'completed_count' => 8,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'youtube-subscribes')->first()->id ?? 8,
                'title' => 'Subscribe to Our YouTube Channel',
                'description' => 'Subscribe to our YouTube channel and hit the notification bell!',
                'platform' => 'youtube',
                'task_type' => 'subscribe',
                'proof_type' => 'screenshot',
                'budget' => 4000,
                'quantity' => 20,
                'worker_reward_per_task' => 150,
                'platform_commission' => 1000,
                'escrow_amount' => 4000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 3,
            ],
            // UGC / High-Value Tasks
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'video-testimonials')->first()->id ?? 11,
                'title' => 'Record Video Testimonial',
                'description' => 'Record a 30-second video testimonial about your experience with our product. Must be original content.',
                'platform' => 'general',
                'task_type' => 'testimonial',
                'proof_type' => 'video',
                'budget' => 15000,
                'quantity' => 3,
                'worker_reward_per_task' => 3375,
                'platform_commission' => 3750,
                'escrow_amount' => 15000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => true,
                'completed_count' => 1,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'tiktok-product-videos')->first()->id ?? 12,
                'title' => 'Create TikTok Product Video',
                'description' => 'Create an engaging TikTok video featuring our product. Must be original and have our hashtag.',
                'platform' => 'tiktok',
                'task_type' => 'promo_video',
                'proof_type' => 'video',
                'budget' => 20000,
                'quantity' => 4,
                'worker_reward_per_task' => 3750,
                'platform_commission' => 5000,
                'escrow_amount' => 20000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => true,
                'completed_count' => 0,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'instagram-stories')->first()->id ?? 13,
                'title' => 'Post Instagram Story Promo',
                'description' => 'Post an Instagram Story featuring our product. Include our tag and link.',
                'platform' => 'instagram',
                'task_type' => 'story',
                'proof_type' => 'screenshot',
                'budget' => 10000,
                'quantity' => 4,
                'worker_reward_per_task' => 1875,
                'platform_commission' => 2500,
                'escrow_amount' => 10000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 2,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'product-reviews')->first()->id ?? 14,
                'title' => 'Write Product Review',
                'description' => 'Write a detailed product review (minimum 200 words) sharing your honest experience.',
                'platform' => 'general',
                'task_type' => 'review',
                'proof_type' => 'link',
                'budget' => 12000,
                'quantity' => 4,
                'worker_reward_per_task' => 2250,
                'platform_commission' => 3000,
                'escrow_amount' => 12000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 1,
            ],
            // Growth Tasks
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'invite-users')->first()->id ?? 17,
                'title' => 'Invite 3 New Users',
                'description' => 'Invite 3 friends using your referral code. They must register and activate their accounts.',
                'platform' => 'general',
                'task_type' => 'referral',
                'proof_type' => 'link',
                'budget' => 450,
                'quantity' => 1,
                'worker_reward_per_task' => 337.50,
                'platform_commission' => 112.50,
                'escrow_amount' => 450,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 0,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'join-telegram')->first()->id ?? 18,
                'title' => 'Join Our Telegram Channel',
                'description' => 'Join our official Telegram channel and stay updated with latest news.',
                'platform' => 'telegram',
                'task_type' => 'join',
                'proof_type' => 'screenshot',
                'budget' => 500,
                'quantity' => 5,
                'worker_reward_per_task' => 75,
                'platform_commission' => 125,
                'escrow_amount' => 500,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 2,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'join-discord')->first()->id ?? 19,
                'title' => 'Join Our Discord Server',
                'description' => 'Join our Discord community server and say hello in the #introductions channel.',
                'platform' => 'discord',
                'task_type' => 'join',
                'proof_type' => 'screenshot',
                'budget' => 500,
                'quantity' => 5,
                'worker_reward_per_task' => 75,
                'platform_commission' => 125,
                'escrow_amount' => 500,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 3,
            ],
            // Premium Tasks
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'affiliate-signups')->first()->id ?? 21,
                'title' => 'Sign Up for Affiliate Program',
                'description' => 'Sign up for our affiliate program and get your unique affiliate link.',
                'platform' => 'general',
                'task_type' => 'affiliate',
                'proof_type' => 'screenshot',
                'budget' => 1000,
                'quantity' => 2,
                'worker_reward_per_task' => 375,
                'platform_commission' => 250,
                'escrow_amount' => 1000,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 0,
                'min_level' => 2,
            ],
            [
                'user_id' => $client->id,
                'category_id' => $categories->where('slug', 'influencer-campaigns')->first()->id ?? 22,
                'title' => 'Micro Influencer Campaign',
                'description' => 'Run a micro-campaign on your social media. Post about our product to your followers.',
                'platform' => 'general',
                'task_type' => 'campaign',
                'proof_type' => 'link',
                'budget' => 3500,
                'quantity' => 5,
                'worker_reward_per_task' => 525,
                'platform_commission' => 875,
                'escrow_amount' => 3500,
                'is_active' => true,
                'is_approved' => true,
                'is_featured' => false,
                'completed_count' => 1,
                'min_level' => 2,
            ],
        ];
        
        $taskModel = new \App\Models\Task();
        foreach ($sampleTasks as $taskData) {
            // Calculate worker reward and commission
            $budget = $taskData['budget'];
            $quantity = $taskData['quantity'];
            $commission = round(($budget * 25) / 100, 2);
            $workerReward = $budget - $commission;
            $rewardPerTask = round($workerReward / $quantity, 2);
            
            $taskData['worker_reward_per_task'] = $rewardPerTask;
            $taskData['platform_commission'] = $commission;
            $taskData['escrow_amount'] = $budget;
            $taskData['is_sample'] = true;
            
            $taskModel->create($taskData);
        }
        
        $this->command->info('Sample tasks seeded successfully!');
    }
}
