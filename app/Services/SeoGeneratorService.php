<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * SEO metadata/schema generator for public marketplace listings.
 *
 * This service deliberately avoids invented trust, income, verification, support,
 * refund, or ranking claims. Search copy is derived from the actual listing data.
 */
class SeoGeneratorService
{
    protected string $baseUrl;
    protected string $platformName = 'SwiftKudi';

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('app.url', 'https://swiftkudi.com'), '/');
    }

    public function generateTitle(string $title, string $type, ?string $category = null): string
    {
        $label = match ($type) {
            'task' => 'Task',
            'service' => 'Freelance Service',
            'product' => 'Digital Product',
            'job' => 'Freelance Job',
            default => 'Marketplace Listing',
        };

        return Str::limit(trim($title . ($category ? " | {$category}" : '') . " | {$label} | {$this->platformName}"), 65, '');
    }

    public function generateMetaDescription(string $title, string $description, int|float|null $budget = null): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($description)) ?? '');
        $budgetText = $budget !== null && $budget > 0 ? ' Budget: ₦' . number_format((float) $budget) . '.' : '';

        return Str::limit(trim("{$title}. {$plain}{$budgetText}"), 155, '…');
    }

    /**
     * Retained for backwards compatibility with existing templates, but content
     * remains grounded in listing attributes instead of auto-generated marketing claims.
     */
    public function generateSeoContent(string $title, string $shortDescription, string $type, array $details = []): array
    {
        $description = trim(strip_tags($shortDescription));
        $facts = [];

        foreach (['category', 'delivery_days', 'duration', 'experience_level', 'location'] as $key) {
            if (!empty($details[$key])) {
                $facts[] = Str::headline($key) . ': ' . (string) $details[$key];
            }
        }

        $intro = "{$title}. {$description}";
        $benefits = $facts ? implode("\n", array_map(fn ($fact) => '- ' . $fact, $facts)) : '';
        $fullContent = trim($intro . ($benefits ? "\n\n" . $benefits : ''));

        return [
            'intro' => $intro,
            'benefits' => $benefits,
            'faq' => [],
            'full_content' => $fullContent,
            'word_count' => str_word_count($fullContent),
        ];
    }

    public function generateFaq(string $title, string $type): array
    {
        // FAQs should come from actual product/service policies or listing data.
        // Returning none is safer than creating repetitive or unsupported SEO copy.
        return [];
    }

    public function generateListingSchema(string $title, string $description, float $price, string $type, string $url, array $additional = []): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => match ($type) {
                'service' => 'Service',
                'product' => 'Product',
                default => 'Thing',
            },
            'name' => $title,
            'description' => Str::limit(trim(strip_tags($description)), 500, '…'),
            'url' => $url,
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->platformName,
                'url' => $this->baseUrl,
            ],
        ];

        if (!empty($additional['created_at'])) {
            $schema['datePublished'] = (string) $additional['created_at'];
        }

        if (in_array($type, ['service', 'product'], true) && $price > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => (string) ($additional['currency'] ?? 'NGN'),
                'url' => $url,
            ];
        }

        return $schema;
    }

    public function generateSeoPackage(array $listing): array
    {
        $type = (string) ($listing['type'] ?? 'task');
        $title = (string) ($listing['title'] ?? 'Marketplace listing');
        $description = (string) ($listing['description'] ?? '');
        $budget = $listing['budget'] ?? $listing['price'] ?? null;
        $category = $listing['category'] ?? null;
        $slug = $listing['slug'] ?? $listing['id'] ?? '';
        $url = $listing['url'] ?? ($this->baseUrl . '/' . Str::plural($type) . '/' . $slug);

        return [
            'seo' => [
                'title' => $this->generateTitle($title, $type, $category),
                'meta_description' => $this->generateMetaDescription($title, $description, is_numeric($budget) ? (float) $budget : null),
                'canonical_url' => $url,
                'index' => true,
                'follow' => true,
            ],
            'content' => $this->generateSeoContent($title, $description, $type, $listing),
            'faq' => [],
            'schema' => $this->generateListingSchema(
                $title,
                $description,
                (float) ($budget ?? 0),
                $type,
                $url,
                $listing
            ),
            'sitemap' => [
                'priority' => in_array($type, ['job', 'service'], true) ? '0.8' : '0.7',
                'changefreq' => 'weekly',
            ],
        ];
    }
}
