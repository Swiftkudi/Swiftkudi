<?php

namespace App\Services;

/**
 * SEO Landing Page Generator for Marketplace Listings
 * Automatically generates SEO-optimized content for tasks, services, and products
 */
class SeoGeneratorService
{
    /**
     * Base URL for the platform
     */
    protected string $baseUrl;

    /**
     * Platform name
     */
    protected string $platformName = 'SwiftKudi';

    public function __construct()
    {
        $this->baseUrl = config('app.url', 'https://swiftkudi.com');
    }

    /**
     * Generate SEO title for a listing
     */
    public function generateTitle(string $title, string $type, ?string $category = null): string
    {
        $suffix = match($type) {
            'task' => 'Task - Earn Money',
            'service' => 'Service - Hire Expert',
            'product' => 'Digital Product - Buy Now',
            default => ''
        };

        if ($category) {
            return "{$title} | {$category} {$suffix} | {$this->platformName}";
        }

        return "{$title} | {$suffix} | {$this->platformName}";
    }

    /**
     * Generate meta description
     */
    public function generateMetaDescription(string $title, string $description, int $budget = null): string
    {
        $budgetText = $budget ? " Budget: ₦" . number_format($budget) . "." : "";
        
        $truncated = strlen($description) > 150 
            ? substr($description, 0, 147) . '...' 
            : $description;
            
        return "{$title} - {$truncated}{$budgetText} Available on {$this->platformName}, Nigeria's leading earning marketplace.";
    }

    /**
     * Generate expanded SEO content (300+ words)
     */
    public function generateSeoContent(string $title, string $shortDescription, string $type, array $details = []): array
    {
        $intro = $this->generateIntro($title, $shortDescription, $type);
        $benefits = $this->generateBenefits($type, $details);
        $faq = $this->generateFaq($title, $type);
        
        $fullContent = $intro . "\n\n" . $benefits;
        
        return [
            'intro' => $intro,
            'benefits' => $benefits,
            'faq' => $faq,
            'full_content' => $fullContent,
            'word_count' => str_word_count($fullContent)
        ];
    }

    /**
     * Generate introduction paragraph
     */
    protected function generateIntro(string $title, string $description, string $type): string
    {
        $action = match($type) {
            'task' => 'complete this task',
            'service' => 'hire for this service',
            'product' => 'purchase this digital product',
            default => 'get this'
        };

        return <<<INTRO
{$title} is now available on {$this->platformName}, Nigeria's premier earning and freelance marketplace. 

{$description}

This {$type} offers an excellent opportunity to {$action} and start earning money online in Nigeria. Whether you're looking to make money on the side or build a full-time income, {$this->platformName} provides a secure platform with escrow protection and instant withdrawals.

INTRO;
    }

    /**
     * Generate benefits section
     */
    protected function generateBenefits(string $type, array $details): string
    {
        $benefits = match($type) {
            'task' => <<<BENEFITS
## Why Complete This Task?

- **Flexible Work**: Complete tasks on your own schedule
- **Secure Payments**: Escrow protection ensures you get paid
- **Quick Earnings**: Many tasks offer fast payment upon completion
- **Skill Building**: Gain experience in various digital tasks
- **Nigeria Focus**: Perfect for Nigerians looking to earn online

## How It Works

1. Browse available tasks on {$this->platformName}
2. Select a task that matches your skills
3. Complete the task following the instructions
4. Submit your work for review
5. Get paid once approved

BENEFITS,
            'service' => <<<BENEFITS
## Why Hire This Service?

- **Professional Quality**: Work with verified experts
- **Secure Transactions**: Escrow protects both parties
- **Direct Communication**: Chat with service providers
- **Review System**: Make informed decisions
- **Dispute Resolution**: Fair mediation if needed

## Our Promise

{$this->platformName} verifies all service providers and offers:
- Secure escrow payments
- Milestone-based releases
- 24/7 customer support
- Money-back guarantees on select services

BENEFITS,
            'product' => <<<BENEFITS
## Why Buy This Digital Product?

- **Instant Delivery**: Download immediately after purchase
- **Quality Assured**: Verified by {$this->platformName}
- **Lifetime Access**: Use the product forever
- **Regular Updates**: Get improvements for free
- **Secure Purchase**: Protected by buyer guarantees

## What's Included

- Full source files/documentations
- Step-by-step usage guide
- Customer support access
- Future updates

BENEFITS,
            default => "## Benefits\n\nJoin thousands of users on {$this->platformName} today."
        };

        return $benefits;
    }

    /**
     * Generate FAQ section
     */
    public function generateFaq(string $title, string $type): array
    {
        $commonFaqs = [
            [
                'question' => "Is {$title} legitimate?",
                'answer' => "Yes, {$title} is listed on {$this->platformName}, a verified Nigerian marketplace. All listings go through our verification process."
            ],
            [
                'question' => "How do I get started?",
                'answer' => "Create a free account on {$this->platformName}, browse the marketplace, and start applying or purchasing. It's that simple!"
            ],
            [
                'question' => "Is my payment secure?",
                'answer' => "Yes! {$this->platformName} uses escrow protection. Funds are held securely until the work is completed and approved."
            ],
            [
                'question' => "How do withdrawals work?",
                'answer' => "Withdraw your earnings instantly to any Nigerian bank account. {$this->platformName} supports instant withdrawals."
            ]
        ];

        $typeSpecificFaq = match($type) {
            'task' => [
                'question' => "How much can I earn from tasks?",
                'answer' => "Earnings vary by task complexity. Simple tasks may pay ₦500-₦5,000 while specialized tasks can pay ₦50,000+."
            ],
            'service' => [
                'question' => "What if I'm not satisfied with the service?",
                'answer' => "You can request revisions or initiate a dispute. Our team will mediate to ensure fair resolution."
            ],
            'product' => [
                'question' => "Can I get a refund?",
                'answer' => "Yes, {$this->platformName} offers buyer protection. Refund policies vary by product - check before purchasing."
            ],
            default => []
        };

        return array_merge($commonFaqs, $typeSpecificFaq);
    }

    /**
     * Generate JSON-LD schema for listing
     */
    public function generateListingSchema(string $title, string $description, float $price, string $type, string $url, array $additional = []): array
    {
        $baseSchema = [
            '@context' => 'https://schema.org',
            '@type' => match($type) {
                'task' => 'TaskAction',
                'service' => 'Service',
                'product' => 'Product',
                default => 'Thing'
            },
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'datePublished' => $additional['created_at'] ?? date('Y-m-d'),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->platformName,
                'url' => $this->baseUrl
            ]
        ];

        if (in_array($type, ['service', 'product'])) {
            $baseSchema['offers'] = [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'NGN',
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $this->platformName
                ]
            ];
        }

        return $baseSchema;
    }

    /**
     * Generate complete SEO package for a listing
     */
    public function generateSeoPackage(array $listing): array
    {
        $type = $listing['type'] ?? 'task';
        $title = $listing['title'];
        $description = $listing['description'];
        $budget = $listing['budget'] ?? $listing['price'] ?? null;
        $category = $listing['category'] ?? null;
        $slug = $listing['slug'] ?? $listing['id'];
        
        $url = $this->baseUrl . '/' . $type . 's/' . $slug;

        return [
            'seo' => [
                'title' => $this->generateTitle($title, $type, $category),
                'meta_description' => $this->generateMetaDescription($title, $description, $budget),
                'canonical_url' => $url,
                'index' => true,
                'follow' => true,
            ],
            'content' => $this->generateSeoContent($title, $description, $type, $listing),
            'faq' => $this->generateFaq($title, $type),
            'schema' => $this->generateListingSchema(
                $title,
                $description,
                (float) ($budget ?? 0),
                $type,
                $url,
                $listing
            ),
            'sitemap' => [
                'priority' => $type === 'task' ? '0.8' : '0.7',
                'changefreq' => 'weekly'
            ]
        ];
    }
}
