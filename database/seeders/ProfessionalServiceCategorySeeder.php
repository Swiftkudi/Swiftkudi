<?php

namespace Database\Seeders;

use App\Models\ProfessionalServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProfessionalServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if table exists
        if (!Schema::hasTable('professional_service_categories')) {
            $this->command->info('professional_service_categories table does not exist, skipping...');
            return;
        }

        $categories = [
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Website creation, development, and maintenance services',
                'icon' => 'fa-globe',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Mobile App Development',
                'slug' => 'mobile-app-development',
                'description' => 'iOS, Android, and cross-platform mobile app development',
                'icon' => 'fa-mobile-alt',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Graphic Design',
                'slug' => 'graphic-design',
                'description' => 'Logo design, branding, illustrations, and visual design',
                'icon' => 'fa-palette',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Content Writing',
                'slug' => 'content-writing',
                'description' => 'Blog posts, articles, copywriting, and content creation',
                'icon' => 'fa-pen-fancy',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'description' => 'SEO, social media marketing, and online advertising',
                'icon' => 'fa-bullhorn',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Video & Animation',
                'slug' => 'video-animation',
                'description' => 'Video editing, animation, and motion graphics',
                'icon' => 'fa-video',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Data Science',
                'slug' => 'data-science',
                'description' => 'Data analysis, machine learning, and AI services',
                'icon' => 'fa-chart-bar',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Virtual Assistant',
                'slug' => 'virtual-assistant',
                'description' => 'Administrative support, research, and task management',
                'icon' => 'fa-headset',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Consulting',
                'slug' => 'consulting',
                'description' => 'Business, career, and technical consulting services',
                'icon' => 'fa-briefcase',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Other Services',
                'slug' => 'other-services',
                'description' => 'Other professional services not listed above',
                'icon' => 'fa-ellipsis-h',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ProfessionalServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
