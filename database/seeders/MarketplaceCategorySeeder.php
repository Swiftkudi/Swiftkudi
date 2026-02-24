<?php

namespace Database\Seeders;

use App\Models\MarketplaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MarketplaceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if table exists
        if (!Schema::hasTable('marketplace_categories')) {
            $this->command->info('marketplace_categories table does not exist, skipping...');
            return;
        }

        // Clear existing categories to avoid duplicates (disable FK checks temporarily)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        MarketplaceCategory::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // =====================
        // TASK CATEGORIES
        // =====================
        $taskCategories = [
            'Data Entry' => ['Data Entry', 'Transcription', 'Copy Paste'],
            'Content Writing' => ['Blog Writing', 'Article Writing', 'Technical Writing'],
            'Virtual Assistant' => ['Admin Support', 'Customer Service', 'Research'],
            'Graphic Design' => ['Logo Design', 'Banner Design', 'Illustration'],
            'Web Development' => ['Frontend', 'Backend', 'Full Stack', 'WordPress'],
            'Social Media' => ['Management', 'Marketing', 'Content Creation'],
            'Video Editing' => ['YouTube', 'Shorts', 'Promotional'],
            'Mobile Apps' => ['Android', 'iOS', 'Cross Platform'],
        ];

        $taskOrder = 0;
        foreach ($taskCategories as $parent => $subs) {
            $parentSlug = 'task-' . \Illuminate\Support\Str::slug($parent);
            $parentCat = MarketplaceCategory::create([
                'name' => $parent,
                'slug' => $parentSlug,
                'description' => $parent . ' tasks and gigs',
                'type' => 'task',
                'is_active' => true,
                'order' => $taskOrder++,
            ]);
            $subOrder = 0;
            foreach ($subs as $sub) {
                MarketplaceCategory::create([
                    'name' => $sub,
                    'slug' => $parentSlug . '-' . \Illuminate\Support\Str::slug($sub),
                    'description' => $sub . ' tasks',
                    'type' => 'task',
                    'parent_id' => $parentCat->id,
                    'is_active' => true,
                    'order' => $taskOrder++,
                ]);
            }
        }

        // =====================
        // PROFESSIONAL SERVICES CATEGORIES
        // =====================
        $professionalCategories = [
            'Web Development' => ['Frontend Development', 'Backend Development', 'Full Stack Development', 'WordPress', 'E-commerce Development'],
            'Mobile App Development' => ['Android', 'iOS', 'Flutter', 'React Native'],
            'Design & Creative' => ['UI/UX Design', 'Graphic Design', 'Logo Design', 'Video Editing', 'Animation'],
            'AI & Automation' => ['AI Chatbots', 'Prompt Engineering', 'Automation Scripts', 'Machine Learning'],
            'Marketing' => ['SEO', 'Social Media Marketing', 'Ads Management', 'Email Marketing'],
            'Writing & Content' => ['Blog Writing', 'Copywriting', 'Technical Writing', 'Script Writing'],
            'Business Services' => ['Virtual Assistant', 'Data Entry', 'Bookkeeping', 'Consulting'],
        ];

        $profOrder = 0;
        foreach ($professionalCategories as $parent => $subs) {
            $parentSlug = 'professional-' . \Illuminate\Support\Str::slug($parent);
            $parentCat = MarketplaceCategory::create([
                'name' => $parent,
                'slug' => $parentSlug,
                'description' => $parent . ' services',
                'type' => 'professional',
                'is_active' => true,
                'order' => $profOrder++,
            ]);
            foreach ($subs as $sub) {
                MarketplaceCategory::create([
                    'name' => $sub,
                    'slug' => $parentSlug . '-' . \Illuminate\Support\Str::slug($sub),
                    'description' => $sub . ' service',
                    'type' => 'professional',
                    'parent_id' => $parentCat->id,
                    'is_active' => true,
                    'order' => $profOrder++,
                ]);
            }
        }

        // =====================
        // GROWTH CATEGORIES (with type field)
        // =====================
        $growthCategories = [
            'backlinks' => [
                'name' => 'Backlinks',
                'subs' => ['Tech Blogs', 'Business Blogs', 'Crypto', 'SaaS', 'General Niche']
            ],
            'influencer' => [
                'name' => 'Influencer Promotions',
                'subs' => ['Instagram', 'TikTok', 'YouTube', 'Twitter/X', 'Facebook']
            ],
            'newsletter' => [
                'name' => 'Newsletter Promotions',
                'subs' => ['Tech', 'Finance', 'Crypto', 'Startup', 'General']
            ],
            'leads' => [
                'name' => 'Lead Generation',
                'subs' => ['B2B Leads', 'B2C Leads', 'Real Estate', 'Finance', 'SaaS', 'E-commerce']
            ],
        ];

        $growthOrder = 0;
        foreach ($growthCategories as $type => $data) {
            $parentSlug = 'growth-' . \Illuminate\Support\Str::slug($data['name']);
            $parentCat = MarketplaceCategory::create([
                'name' => $data['name'],
                'slug' => $parentSlug,
                'description' => $data['name'] . ' services',
                'type' => 'growth',
                'is_active' => true,
                'order' => $growthOrder++,
            ]);
            foreach ($data['subs'] as $sub) {
                MarketplaceCategory::create([
                    'name' => $sub,
                    'slug' => $parentSlug . '-' . \Illuminate\Support\Str::slug($sub),
                    'description' => $sub . ' growth service',
                    'type' => 'growth',
                    'parent_id' => $parentCat->id,
                    'is_active' => true,
                    'order' => $growthOrder++,
                ]);
            }
        }

        // =====================
        // DIGITAL PRODUCTS CATEGORIES
        // =====================
        $productCategories = [
            'SaaS Applications' => [],
            'Web Applications' => [],
            'Mobile Apps' => [],
            'Templates' => ['Admin Templates', 'Website Templates', 'Email Templates'],
            'Scripts & Plugins' => [],
            'AI Tools' => [],
            'Bots & Automation' => [],
            'E-commerce Projects' => [],
            'Fintech Projects' => [],
        ];

        $prodOrder = 0;
        foreach ($productCategories as $parent => $subs) {
            $parentSlug = 'digital-' . \Illuminate\Support\Str::slug($parent);
            $parentCat = MarketplaceCategory::create([
                'name' => $parent,
                'slug' => $parentSlug,
                'description' => $parent . ' digital products',
                'type' => 'digital_product',
                'is_active' => true,
                'order' => $prodOrder++,
            ]);
            if (!empty($subs)) {
                foreach ($subs as $sub) {
                    MarketplaceCategory::create([
                        'name' => $sub,
                        'slug' => $parentSlug . '-' . \Illuminate\Support\Str::slug($sub),
                        'description' => $sub . ' templates',
                        'type' => 'digital_product',
                        'parent_id' => $parentCat->id,
                        'is_active' => true,
                        'order' => $prodOrder++,
                    ]);
                }
            }
        }

        // =====================
        // JOB CATEGORIES
        // =====================
        $jobCategories = [
            'Software Development',
            'Design',
            'Marketing',
            'AI & Data',
            'Sales',
            'Operations',
            'Remote Jobs',
            'Part-Time',
            'Internship',
        ];

        $jobOrder = 0;
        foreach ($jobCategories as $name) {
            MarketplaceCategory::create([
                'name' => $name,
                'slug' => 'job-' . \Illuminate\Support\Str::slug($name),
                'description' => $name . ' jobs',
                'type' => 'job',
                'is_active' => true,
                'order' => $jobOrder++,
            ]);
        }

        // Summary
        $counts = [
            'task' => MarketplaceCategory::where('type', 'task')->count(),
            'professional' => MarketplaceCategory::where('type', 'professional')->count(),
            'growth' => MarketplaceCategory::where('type', 'growth')->count(),
            'digital_product' => MarketplaceCategory::where('type', 'digital_product')->count(),
            'job' => MarketplaceCategory::where('type', 'job')->count(),
        ];

        echo "Categories seeded successfully!\n";
        echo "Task: {$counts['task']}, Professional: {$counts['professional']}, Growth: {$counts['growth']}, Digital Product: {$counts['digital_product']}, Job: {$counts['job']}\n";
    }
}
