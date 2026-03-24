<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\ProfessionalService;
use App\Models\ProfessionalServiceCategory;
use App\Models\ServiceProviderProfile;
use App\Models\GrowthListing;
use App\Models\DigitalProduct;
use App\Models\MarketplaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MarketplaceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create service providers
        $this->createServiceProviders();
        
        // Create professional services
        $this->createProfessionalServices();
        
        // Create growth listings
        $this->createGrowthListings();
        
        // Create digital products
        $this->createDigitalProducts();
        
        $this->command->info('Marketplace data seeded successfully!');
    }

    /**
     * Create service provider users and profiles
     */
    protected function createServiceProviders(): void
    {
        $providers = [
            [
                'name' => 'Chioma Design Studio',
                'email' => 'chioma@swiftkudi.com',
                'bio' => 'Professional graphic designer with 5+ years experience. Specializing in brand identity, social media graphics, and UI/UX design.',
                'skills' => ['Graphic Design', 'Logo Design', 'Brand Identity', 'UI/UX', 'Adobe Photoshop', 'Figma'],
                'hourly_rate' => 5000,
                'portfolio_links' => [
                    'https://behance.net/chiomadesign',
                    'https://dribbble.com/chiomadesign',
                ],
                'certifications' => ['Adobe Certified Expert', 'Google UX Design Certificate'],
            ],
            [
                'name' => 'TechPro Developers',
                'email' => 'techpro@swiftkudi.com',
                'bio' => 'Full-stack development team specializing in web applications, mobile apps, and e-commerce solutions. Fast delivery and clean code.',
                'skills' => ['Web Development', 'Mobile Apps', 'Laravel', 'React', 'Node.js', 'WordPress'],
                'hourly_rate' => 8000,
                'portfolio_links' => [
                    'https://github.com/techpro-dev',
                    'https://techpro.dev/portfolio',
                ],
                'certifications' => ['AWS Certified Developer', 'Google Cloud Professional'],
            ],
            [
                'name' => 'Content Kings NG',
                'email' => 'contentkings@swiftkudi.com',
                'bio' => 'Professional content writers and copywriters. We create engaging blog posts, website copy, product descriptions, and SEO content.',
                'skills' => ['Content Writing', 'Copywriting', 'SEO Writing', 'Blog Posts', 'Technical Writing'],
                'hourly_rate' => 3000,
                'portfolio_links' => [
                    'https://medium.com/@contentkings',
                    'https://contentkings.ng/samples',
                ],
                'certifications' => ['HubSpot Content Marketing', 'Google Digital Marketing'],
            ],
            [
                'name' => 'Video Magic Studios',
                'email' => 'videomagic@swiftkudi.com',
                'bio' => 'Professional video editing and motion graphics. We bring your vision to life with stunning visual effects and seamless editing.',
                'skills' => ['Video Editing', 'Motion Graphics', 'After Effects', 'Premiere Pro', 'Color Grading'],
                'hourly_rate' => 7500,
                'portfolio_links' => [
                    'https://vimeo.com/videomagic',
                    'https://youtube.com/videomagicstudios',
                ],
                'certifications' => ['Adobe Certified Professional in Video'],
            ],
            [
                'name' => 'Social Media Gurus',
                'email' => 'socialgurus@swiftkudi.com',
                'bio' => 'Social media management and marketing experts. We help businesses grow their online presence and engage with their audience.',
                'skills' => ['Social Media Management', 'Facebook Ads', 'Instagram Marketing', 'TikTok', 'Content Strategy'],
                'hourly_rate' => 4000,
                'portfolio_links' => [
                    'https://instagram.com/socialgurus',
                ],
                'certifications' => ['Meta Blueprint Certified', 'Hootsuite Certified'],
            ],
        ];

        foreach ($providers as $providerData) {
            $user = User::firstOrCreate(
                ['email' => $providerData['email']],
                [
                    'name' => $providerData['name'],
                    'password' => Hash::make('password123'),
                    'referral_code' => User::generateReferralCode(),
                    'level' => rand(3, 7),
                    'experience_points' => rand(500, 3000),
                ]
            );

            // Create wallet
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'withdrawable_balance' => rand(10000, 50000),
                    'promo_credit_balance' => rand(1000, 5000),
                    'is_activated' => true,
                    'activated_at' => now(),
                ]
            );

            // Create provider profile
            ServiceProviderProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'is_available' => true,
                    'bio' => $providerData['bio'],
                    'skills' => json_encode($providerData['skills']),
                    'hourly_rate' => $providerData['hourly_rate'],
                    'portfolio_links' => json_encode($providerData['portfolio_links']),
                    'certifications' => json_encode($providerData['certifications']),
                ]
            );

            $this->command->info("Created provider: {$providerData['name']}");
        }
    }

    /**
     * Create professional services
     */
    protected function createProfessionalServices(): void
    {
        $categoryMap = ProfessionalServiceCategory::pluck('id', 'slug')->toArray();
        
        $services = [
            // Graphic Design Services
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'graphic-design',
                'title' => 'Professional Logo Design',
                'description' => 'Get a unique, professional logo for your brand. Includes 3 initial concepts, unlimited revisions, and all file formats (AI, PNG, JPG, PDF). Delivery in 3-5 business days.',
                'price' => 15000,
                'delivery_time' => 5,
                'features' => ['3 Logo Concepts', 'Unlimited Revisions', 'All File Formats', 'Source Files', 'Vector Files'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'graphic-design',
                'title' => 'Social Media Graphics Pack',
                'description' => 'Complete social media graphics package including profile picture, cover photo, and 10 post templates. Perfect for Instagram, Facebook, and Twitter.',
                'price' => 20000,
                'delivery_time' => 7,
                'features' => ['Profile Picture', 'Cover Photo', '10 Post Templates', 'Editable Source Files', 'Multiple Sizes'],
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'graphic-design',
                'title' => 'Business Card Design',
                'description' => 'Professional business card design that makes a lasting impression. Double-sided design with print-ready files.',
                'price' => 5000,
                'delivery_time' => 3,
                'features' => ['Double-Sided Design', 'Print-Ready Files', '2 Revisions', 'Multiple Formats'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // Web Development Services
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'web-development',
                'title' => 'Landing Page Development',
                'description' => 'High-converting landing page built with modern technologies. Responsive design, SEO optimized, and fast loading. Perfect for product launches and lead generation.',
                'price' => 50000,
                'delivery_time' => 7,
                'features' => ['Responsive Design', 'SEO Optimized', 'Fast Loading', 'Contact Form', '1 Week Support'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'web-development',
                'title' => 'E-commerce Website Setup',
                'description' => 'Complete e-commerce website with product catalog, shopping cart, payment integration, and admin dashboard. Ready to start selling!',
                'price' => 150000,
                'delivery_time' => 14,
                'features' => ['Product Catalog', 'Shopping Cart', 'Payment Gateway', 'Admin Dashboard', 'Mobile Responsive', '1 Month Support'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'web-development',
                'title' => 'WordPress Website Design',
                'description' => 'Professional WordPress website with custom theme, essential plugins, and basic SEO setup. Perfect for blogs, portfolios, and small businesses.',
                'price' => 35000,
                'delivery_time' => 5,
                'features' => ['Custom Theme', 'Essential Plugins', 'SEO Setup', 'Responsive Design', 'Training Session'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // Content Writing Services
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'content-writing',
                'title' => 'SEO Blog Post Writing',
                'description' => 'Engaging, SEO-optimized blog posts that drive traffic and convert readers. Includes keyword research, internal linking, and meta descriptions.',
                'price' => 8000,
                'delivery_time' => 3,
                'features' => ['1000-1500 Words', 'SEO Optimized', 'Keyword Research', 'Meta Description', '1 Revision'],
                'status' => 'active',
                'is_featured' => false,
            ],
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'content-writing',
                'title' => 'Website Copywriting Package',
                'description' => 'Complete website copy for your business. Includes homepage, about page, services page, and contact page. Compelling copy that converts.',
                'price' => 45000,
                'delivery_time' => 7,
                'features' => ['4 Pages of Copy', 'SEO Optimized', 'Call-to-Action', '2 Revisions', 'Tone Matching'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'content-writing',
                'title' => 'Product Description Writing',
                'description' => 'Compelling product descriptions that sell. Perfect for e-commerce stores. Each description is unique, SEO-friendly, and highlights key benefits.',
                'price' => 2500,
                'delivery_time' => 2,
                'features' => ['150-200 Words Each', 'SEO Friendly', 'Benefit-Focused', 'Unique Content'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // Video Editing Services
            [
                'seller_email' => 'videomagic@swiftkudi.com',
                'category_slug' => 'video-editing',
                'title' => 'YouTube Video Editing',
                'description' => 'Professional YouTube video editing with transitions, effects, color grading, and sound design. Turn your raw footage into engaging content.',
                'price' => 15000,
                'delivery_time' => 5,
                'features' => ['Up to 15 Minutes', 'Color Grading', 'Transitions', 'Sound Design', 'Thumbnail'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'videomagic@swiftkudi.com',
                'category_slug' => 'video-editing',
                'title' => 'Social Media Video Ads',
                'description' => 'Eye-catching video ads for Instagram, TikTok, and Facebook. Short-form content optimized for maximum engagement and conversions.',
                'price' => 25000,
                'delivery_time' => 4,
                'features' => ['15-60 Seconds', 'Platform Optimized', 'Captions/Subtitles', 'Music & Effects', '2 Ad Variations'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // Digital Marketing Services
            [
                'seller_email' => 'socialgurus@swiftkudi.com',
                'category_slug' => 'digital-marketing',
                'title' => 'Social Media Management',
                'description' => 'Complete social media management for your business. We create content, schedule posts, engage with followers, and grow your audience.',
                'price' => 75000,
                'delivery_time' => 30,
                'features' => ['Daily Posts', 'Content Creation', 'Community Management', 'Monthly Report', '3 Platforms'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'socialgurus@swiftkudi.com',
                'category_slug' => 'digital-marketing',
                'title' => 'Facebook/Instagram Ads Setup',
                'description' => 'Professional Facebook and Instagram advertising campaign setup. Target the right audience and maximize your ROI.',
                'price' => 30000,
                'delivery_time' => 5,
                'features' => ['Campaign Setup', 'Audience Research', 'Ad Creative', 'Pixel Setup', '7-Day Optimization'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // Pending services for admin approval testing
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'graphic-design',
                'title' => 'Brand Identity Package',
                'description' => 'Complete brand identity including logo, color palette, typography, and brand guidelines. Everything you need for a consistent brand image.',
                'price' => 75000,
                'delivery_time' => 10,
                'features' => ['Logo Design', 'Color Palette', 'Typography Guide', 'Brand Guidelines PDF', 'Business Cards'],
                'status' => 'pending',
                'is_featured' => false,
            ],
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'web-development',
                'title' => 'Mobile App Development',
                'description' => 'Custom mobile app development for iOS and Android. From concept to deployment, we build apps that users love.',
                'price' => 500000,
                'delivery_time' => 30,
                'features' => ['iOS & Android', 'UI/UX Design', 'Backend Development', 'App Store Submission', '3 Months Support'],
                'status' => 'pending',
                'is_featured' => false,
            ],
        ];

        foreach ($services as $serviceData) {
            $seller = User::where('email', $serviceData['seller_email'])->first();
            $categoryId = $categoryMap[$serviceData['category_slug']] ?? 1;

            if ($seller) {
                ProfessionalService::firstOrCreate(
                    [
                        'user_id' => $seller->id,
                        'title' => $serviceData['title'],
                    ],
                    [
                        'category_id' => $categoryId,
                        'description' => $serviceData['description'],
                        'price' => $serviceData['price'],
                        'delivery_days' => $serviceData['delivery_time'],
                        'portfolio_links' => json_encode($serviceData['features']),
                        'status' => $serviceData['status'],
                        'is_featured' => $serviceData['is_featured'],
                    ]
                );
            }
        }

        $this->command->info('Professional services created!');
    }

    /**
     * Create growth listings
     */
    protected function createGrowthListings(): void
    {
        $categoryMap = MarketplaceCategory::where('type', 'growth')->pluck('id', 'slug')->toArray();
        
        $listings = [
            // Backlinks
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'growth-backlinks',
                'title' => 'High DA Backlink - Tech Blog (DA 50+)',
                'description' => 'Get a high-quality dofollow backlink from our established tech blog with Domain Authority 50+. Perfect for boosting your SEO rankings.',
                'price' => 15000,
                'delivery_time' => 7,
                'type' => 'backlinks',
                'metrics' => ['da' => 52, 'pa' => 45, 'traffic' => '50K/month'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'growth-backlinks',
                'title' => 'Guest Post on Finance Blog (DA 40+)',
                'description' => 'Publish your guest post on our finance blog. Includes 2 dofollow links and social media promotion. Great for finance, crypto, and business niches.',
                'price' => 25000,
                'delivery_time' => 10,
                'type' => 'backlinks',
                'metrics' => ['da' => 43, 'pa' => 38, 'traffic' => '80K/month'],
                'status' => 'active',
            ],
            
            // Influencers
            [
                'seller_email' => 'socialgurus@swiftkudi.com',
                'category_slug' => 'growth-influencer-promotions',
                'title' => 'Instagram Influencer Shoutout - 100K Followers',
                'description' => 'Get a shoutout on our Instagram page with 100K+ engaged followers in the lifestyle and fashion niche. Great for brand awareness.',
                'price' => 35000,
                'delivery_time' => 3,
                'type' => 'influencer',
                'metrics' => ['followers' => '120K', 'engagement' => '4.5%', 'niche' => 'Lifestyle/Fashion'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'videomagic@swiftkudi.com',
                'category_slug' => 'growth-influencer-promotions',
                'title' => 'TikTok Video Promotion - 500K Followers',
                'description' => 'Get your product or service featured in a TikTok video on our channel with 500K+ followers. High engagement and viral potential.',
                'price' => 75000,
                'delivery_time' => 5,
                'type' => 'influencer',
                'metrics' => ['followers' => '580K', 'engagement' => '8%', 'niche' => 'Entertainment'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'growth-influencer-promotions',
                'title' => 'YouTube Channel Collaboration - 50K Subs',
                'description' => 'Collaborate with our YouTube channel focused on tech reviews and tutorials. Perfect for software, apps, and tech products.',
                'price' => 100000,
                'delivery_time' => 10,
                'type' => 'influencer',
                'metrics' => ['subscribers' => '52K', 'views_per_video' => '15K', 'niche' => 'Tech/Reviews'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'growth-influencer-promotions',
                'title' => 'Twitter/X Thread Promotion - 80K Followers',
                'description' => 'Get your product featured in a viral Twitter thread. We create engaging content that drives traffic and conversions.',
                'price' => 45000,
                'delivery_time' => 4,
                'type' => 'influencer',
                'metrics' => ['followers' => '85K', 'avg_impressions' => '200K', 'niche' => 'Business/Tech'],
                'status' => 'active',
            ],
            
            // Newsletters
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'growth-newsletter-promotions',
                'title' => 'Newsletter Sponsorship - 25K Subscribers',
                'description' => 'Sponsor our weekly tech newsletter with 25K engaged subscribers. Includes banner ad and dedicated section about your product.',
                'price' => 40000,
                'delivery_time' => 7,
                'type' => 'newsletter',
                'metrics' => ['subscribers' => '25K', 'open_rate' => '35%', 'click_rate' => '8%'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'growth-newsletter-promotions',
                'title' => 'Developer Newsletter Ad - 40K Subscribers',
                'description' => 'Reach 40K+ developers with your tool, service, or product. Our newsletter covers web development, DevOps, and programming tutorials.',
                'price' => 60000,
                'delivery_time' => 7,
                'type' => 'newsletter',
                'metrics' => ['subscribers' => '42K', 'open_rate' => '42%', 'click_rate' => '12%'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'socialgurus@swiftkudi.com',
                'category_slug' => 'growth-newsletter-promotions',
                'title' => 'Marketing Newsletter - 15K Marketers',
                'description' => 'Promote your marketing tool or service to 15K+ marketing professionals. High-quality audience of decision makers.',
                'price' => 35000,
                'delivery_time' => 5,
                'type' => 'newsletter',
                'metrics' => ['subscribers' => '15K', 'open_rate' => '38%', 'click_rate' => '10%'],
                'status' => 'active',
            ],
            
            // Leads
            [
                'seller_email' => 'socialgurus@swiftkudi.com',
                'category_slug' => 'growth-lead-generation',
                'title' => '100 Verified Business Leads - Lagos SMEs',
                'description' => 'Get 100 verified leads of small and medium business owners in Lagos. Includes company name, contact person, email, phone, and industry.',
                'price' => 20000,
                'delivery_time' => 3,
                'type' => 'leads',
                'metrics' => ['count' => 100, 'location' => 'Lagos', 'verified' => true],
                'status' => 'active',
            ],
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'growth-lead-generation',
                'title' => '50 Startup Founder Leads - Tech Industry',
                'description' => 'Connect with 50 startup founders in the tech industry. Verified email addresses and LinkedIn profiles included.',
                'price' => 30000,
                'delivery_time' => 5,
                'type' => 'leads',
                'metrics' => ['count' => 50, 'industry' => 'Tech', 'decision_makers' => true],
                'status' => 'active',
            ],
            [
                'seller_email' => 'contentkings@swiftkudi.com',
                'category_slug' => 'growth-lead-generation',
                'title' => '200 E-commerce Customer Leads - Fashion',
                'description' => '200 verified leads of fashion-conscious consumers who have made online purchases in the last 6 months. Perfect for fashion brands.',
                'price' => 25000,
                'delivery_time' => 4,
                'type' => 'leads',
                'metrics' => ['count' => 200, 'category' => 'Fashion', 'recent_buyers' => true],
                'status' => 'active',
            ],
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'growth-lead-generation',
                'title' => 'Real Estate Investor Leads - 75 Contacts',
                'description' => '75 verified real estate investors looking for properties in Lagos and Abuja. Includes phone, email, and investment budget range.',
                'price' => 40000,
                'delivery_time' => 5,
                'type' => 'leads',
                'metrics' => ['count' => 75, 'industry' => 'Real Estate', 'investors' => true],
                'status' => 'active',
            ],
            
            // More Backlinks
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'growth-backlinks',
                'title' => 'Fashion Blog Backlink - DA 35+',
                'description' => 'Get a contextual backlink from our fashion and lifestyle blog. Great for fashion, beauty, and lifestyle brands.',
                'price' => 12000,
                'delivery_time' => 5,
                'type' => 'backlinks',
                'metrics' => ['da' => 37, 'pa' => 32, 'traffic' => '30K/month'],
                'status' => 'active',
            ],
            [
                'seller_email' => 'videomagic@swiftkudi.com',
                'category_slug' => 'growth-backlinks',
                'title' => 'Entertainment Blog Backlink - DA 45+',
                'description' => 'Premium backlink from our entertainment and pop culture blog. High traffic and engagement.',
                'price' => 20000,
                'delivery_time' => 7,
                'type' => 'backlinks',
                'metrics' => ['da' => 48, 'pa' => 42, 'traffic' => '100K/month'],
                'status' => 'active',
            ],
        ];

        foreach ($listings as $listingData) {
            $seller = User::where('email', $listingData['seller_email'])->first();
            $categoryId = $categoryMap[$listingData['category_slug']] ?? null;

            if ($seller && $categoryId) {
                GrowthListing::firstOrCreate(
                    [
                        'user_id' => $seller->id,
                        'title' => $listingData['title'],
                    ],
                    [
                        'description' => $listingData['description'],
                        'price' => $listingData['price'],
                        'delivery_days' => $listingData['delivery_time'],
                        'type' => $listingData['type'],
                        'specs' => json_encode($listingData['metrics']),
                        'status' => $listingData['status'],
                    ]
                );
            }
        }

        $this->command->info('Growth listings created!');
    }

    /**
     * Create digital products
     */
    protected function createDigitalProducts(): void
    {
        $categoryMap = MarketplaceCategory::where('type', 'digital_product')->pluck('id', 'slug')->toArray();
        
        $products = [
            // Templates
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'digital-templates',
                'title' => 'Social Media Templates Bundle - 50+ Designs',
                'description' => 'Complete bundle of 50+ social media templates for Instagram, Facebook, and Twitter. Fully editable in Canva. Perfect for businesses and influencers.',
                'price' => 5000,
                'type' => 'digital',
                'file_path' => 'products/social-media-templates-bundle.zip',
                'preview_images' => ['templates-preview-1.jpg', 'templates-preview-2.jpg'],
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'seller_email' => 'chioma@swiftkudi.com',
                'category_slug' => 'digital-templates',
                'title' => 'Professional Resume Template Pack',
                'description' => 'Set of 5 professional resume templates in Word and Pages format. Clean, modern designs that get you noticed by recruiters.',
                'price' => 3000,
                'type' => 'digital',
                'file_path' => 'products/resume-templates.zip',
                'preview_images' => ['resume-preview.jpg'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // SaaS Applications
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-saas-applications',
                'title' => 'SaaS Starter Kit - Laravel + Vue',
                'description' => 'Complete SaaS starter kit with authentication, billing, teams, and admin panel. Built with Laravel and Vue.js. Save months of development time.',
                'price' => 75000,
                'type' => 'digital',
                'file_path' => 'products/saas-starter-kit.zip',
                'preview_images' => ['saas-preview.jpg'],
                'status' => 'active',
                'is_featured' => true,
            ],
            
            // Web Applications
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-web-applications',
                'title' => 'Project Management App - Full Source',
                'description' => 'Complete project management application with tasks, teams, time tracking, and reports. Built with Laravel. Full source code included.',
                'price' => 50000,
                'type' => 'digital',
                'file_path' => 'products/project-management-app.zip',
                'preview_images' => ['pm-app-preview.jpg'],
                'status' => 'active',
                'is_featured' => true,
            ],
            
            // Scripts & Plugins
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-scripts-plugins',
                'title' => 'WordPress SEO Plugin Pro',
                'description' => 'Advanced SEO plugin for WordPress. Auto-generate meta tags, sitemaps, schema markup, and more. Boost your search rankings effortlessly.',
                'price' => 15000,
                'type' => 'digital',
                'file_path' => 'products/wp-seo-plugin-pro.zip',
                'preview_images' => ['plugin-screenshot.jpg'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // AI Tools
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-ai-tools',
                'title' => 'AI Content Generator Script',
                'description' => 'AI-powered content generation script using OpenAI API. Generate blog posts, product descriptions, and social media content automatically.',
                'price' => 35000,
                'type' => 'digital',
                'file_path' => 'products/ai-content-generator.zip',
                'preview_images' => ['ai-tool-preview.jpg'],
                'status' => 'active',
                'is_featured' => true,
            ],
            
            // Bots & Automation
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-bots-automation',
                'title' => 'Telegram Bot Framework',
                'description' => 'Complete Telegram bot framework with commands, payments, and admin panel. Easy to customize for any use case.',
                'price' => 25000,
                'type' => 'digital',
                'file_path' => 'products/telegram-bot-framework.zip',
                'preview_images' => ['bot-preview.jpg'],
                'status' => 'active',
                'is_featured' => false,
            ],
            
            // E-commerce Projects
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-e-commerce-projects',
                'title' => 'Multi-Vendor Marketplace Script',
                'description' => 'Complete multi-vendor marketplace like Amazon or eBay. Vendor dashboard, admin panel, payment integration, and more.',
                'price' => 100000,
                'type' => 'digital',
                'file_path' => 'products/multi-vendor-marketplace.zip',
                'preview_images' => ['marketplace-preview.jpg'],
                'status' => 'active',
                'is_featured' => true,
            ],
            
            // Fintech Projects
            [
                'seller_email' => 'techpro@swiftkudi.com',
                'category_slug' => 'digital-fintech-projects',
                'title' => 'Digital Wallet & Payment System',
                'description' => 'Complete digital wallet system with transfers, bill payments, and QR codes. Built with Laravel. Perfect for fintech startups.',
                'price' => 150000,
                'type' => 'digital',
                'file_path' => 'products/digital-wallet-system.zip',
                'preview_images' => ['wallet-preview.jpg'],
                'status' => 'active',
                'is_featured' => true,
            ],
        ];

        foreach ($products as $productData) {
            $seller = User::where('email', $productData['seller_email'])->first();
            // Note: digital_products uses task_categories, not marketplace_categories
            // So we skip category for now or use null

            if ($seller) {
                DigitalProduct::firstOrCreate(
                    [
                        'user_id' => $seller->id,
                        'title' => $productData['title'],
                    ],
                    [
                        'category_id' => null, // Digital products use task_categories, skip for now
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'file_path' => $productData['file_path'],
                        'thumbnail' => $productData['preview_images'][0] ?? null,
                        'is_active' => $productData['status'] === 'active',
                        'is_featured' => $productData['is_featured'],
                    ]
                );
            }
        }

        $this->command->info('Digital products created!');
    }
}
