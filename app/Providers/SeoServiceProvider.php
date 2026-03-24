<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class SeoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share SEO defaults with all views
        View::share('seo', new class {
            /**
             * Default SEO configuration for SwiftKudi
             */
            public function defaults()
            {
                return [
                    'site_name' => 'SwiftKudi',
                    'site_url' => 'https://swiftkudi.com',
                    'site_description' => 'The premier task posting marketplace for businesses. Post tasks, hire vetted freelancers, manage projects with secure escrow payments.',
                    'twitter_handle' => '@swiftkudi',
                    'locale' => 'en_NG',
                    'timezone' => 'Africa/Lagos',
                ];
            }

            /**
             * Generate meta tags array for Blade templates
             */
            public function meta($title, $description = null, $image = null, $url = null)
            {
                $defaults = $this->defaults();
                
                return [
                    'title' => $title . ' | ' . $defaults['site_name'],
                    'description' => $description ?? $defaults['site_description'],
                    'image' => $image ?? 'https://swiftkudi.com/og-image.png',
                    'url' => $url ?? $defaults['site_url'],
                    'site_name' => $defaults['site_name'],
                    'twitter_handle' => $defaults['twitter_handle'],
                ];
            }

            /**
             * Generate Organization Schema
             */
            public function organizationSchema()
            {
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => 'SwiftKudi',
                    'url' => 'https://swiftkudi.com',
                    'logo' => 'https://swiftkudi.com/logo.png',
                    'description' => 'Premier freelance marketplace connecting businesses with skilled freelancers.',
                    'foundingDate' => '2023',
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'Nigeria'
                    ],
                    'serviceType' => [
                        'Freelance Marketplace',
                        'Escrow Services', 
                        'Task Posting',
                        'Professional Services'
                    ],
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'contactType' => 'Customer Support',
                        'email' => 'support@swiftkudi.com',
                        'availableLanguage' => ['English']
                    ],
                    'sameAs' => [
                        'https://twitter.com/swiftkudi',
                        'https://facebook.com/swiftkudi',
                        'https://instagram.com/swiftkudi',
                        'https://linkedin.com/company/swiftkudi'
                    ]
                ];
            }

            /**
             * Generate FAQ Schema
             */
            public function faqSchema($faqs)
            {
                $schema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => []
                ];

                foreach ($faqs as $faq) {
                    $schema['mainEntity'][] = [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['answer']
                        ]
                    ];
                }

                return $schema;
            }

            /**
             * Generate Breadcrumb Schema
             */
            public function breadcrumbSchema($items)
            {
                $schema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => []
                ];

                foreach ($items as $index => $item) {
                    $schema['itemListElement'][] = [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $item['name'],
                        'item' => $item['url'] ?? null
                    ];
                }

                return $schema;
            }
        });
    }
}
